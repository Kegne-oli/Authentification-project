<?php
/**
 * includes/Auth.php
 * Core authentication logic: register, login, logout,
 * email verification, password reset (OTP flow).
 */
class Auth
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // =========================================================
    // REGISTRATION
    // =========================================================

    /**
     * Register a new user. Returns ['success'=>true] or ['error'=>'...'].
     */
    public function register(string $fullName, string $email, string $password): array
    {
        $fullName = trim($fullName);
        $email    = strtolower(trim($email));

        // Validate
        if (strlen($fullName) < 2) {
            return ['error' => 'Full name must be at least 2 characters.'];
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['error' => 'Invalid email address.'];
        }
        $pwCheck = $this->validatePassword($password);
        if ($pwCheck !== true) {
            return ['error' => $pwCheck];
        }

        // Duplicate check
        $stmt = $this->db->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            return ['error' => 'An account with this email already exists.'];
        }

        // Insert user
        $hash = password_hash($password, HASH_ALGO, ['cost' => HASH_COST]);
        $stmt = $this->db->prepare(
            'INSERT INTO users (full_name, email, password_hash) VALUES (?, ?, ?)'
        );
        $stmt->execute([$fullName, $email, $hash]);
        $userId = (int) $this->db->lastInsertId();

        // Create verification token and send email
        $token = $this->createEmailVerificationToken($userId);
        Mailer::sendVerification($email, $fullName, $token);

        return ['success' => true, 'user_id' => $userId];
    }

    // =========================================================
    // EMAIL VERIFICATION
    // =========================================================

    private function createEmailVerificationToken(int $userId): string
    {
        $token     = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));

        $stmt = $this->db->prepare(
            'INSERT INTO email_verifications (user_id, token, expires_at) VALUES (?, ?, ?)'
        );
        $stmt->execute([$userId, $token, $expiresAt]);
        return $token;
    }

    /**
     * Verify a user's email from the link token.
     */
    public function verifyEmail(string $token): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM email_verifications
             WHERE token = ? AND used_at IS NULL AND expires_at > NOW()'
        );
        $stmt->execute([$token]);
        $row = $stmt->fetch();

        if (!$row) {
            return ['error' => 'This verification link is invalid or has expired.'];
        }

        // Mark user as verified
        $this->db->prepare('UPDATE users SET is_verified = 1 WHERE id = ?')
                 ->execute([$row['user_id']]);

        // Mark token used
        $this->db->prepare('UPDATE email_verifications SET used_at = NOW() WHERE id = ?')
                 ->execute([$row['id']]);

        return ['success' => true];
    }

    /**
     * Re-send verification email.
     */
    public function resendVerification(string $email): array
    {
        $email = strtolower(trim($email));
        $stmt  = $this->db->prepare(
            'SELECT id, full_name, is_verified FROM users WHERE email = ? AND is_active = 1'
        );
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            // Don't reveal whether email exists
            return ['success' => true];
        }
        if ($user['is_verified']) {
            return ['error' => 'This account is already verified. You can log in.'];
        }

        $token = $this->createEmailVerificationToken((int) $user['id']);
        Mailer::sendVerification($email, $user['full_name'], $token);

        return ['success' => true];
    }

    // =========================================================
    // LOGIN
    // =========================================================

    /**
     * Attempt login. On success writes to $_SESSION.
     */
    public function login(string $email, string $password, bool $remember = false): array
    {
        $email = strtolower(trim($email));
        $ip    = $this->getClientIp();

        // Brute-force protection
        if ($this->isLockedOut($email, $ip)) {
            return ['error' => 'Too many failed attempts. Please try again in ' . LOCKOUT_MINUTES . ' minutes.'];
        }

        $stmt = $this->db->prepare(
            'SELECT id, full_name, email, password_hash, is_verified, is_active, role
             FROM users WHERE email = ?'
        );
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            $this->recordFailedAttempt($email, $ip);
            return ['error' => 'Invalid email or password.'];
        }

        if (!$user['is_active']) {
            return ['error' => 'Your account has been deactivated. Contact support.'];
        }

        if (!$user['is_verified']) {
            return [
                'error'          => 'Please verify your email before logging in.',
                'unverified'     => true,
                'email'          => $email,
            ];
        }

        // Rehash if needed
        if (password_needs_rehash($user['password_hash'], HASH_ALGO, ['cost' => HASH_COST])) {
            $newHash = password_hash($password, HASH_ALGO, ['cost' => HASH_COST]);
            $this->db->prepare('UPDATE users SET password_hash = ? WHERE id = ?')
                     ->execute([$newHash, $user['id']]);
        }

        // Update last login
        $this->db->prepare('UPDATE users SET last_login_at = NOW(), last_login_ip = ? WHERE id = ?')
                 ->execute([$ip, $user['id']]);

        // Clear failed attempts
        $this->clearAttempts($email, $ip);

        // Write session
        $this->startSecureSession();
        $_SESSION['user_id']    = $user['id'];
        $_SESSION['user_name']  = $user['full_name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role']  = $user['role'];
        $_SESSION['logged_in']  = true;
        session_regenerate_id(true);

        return ['success' => true, 'user' => $user];
    }

    // =========================================================
    // LOGOUT
    // =========================================================

    public function logout(): void
    {
        $this->startSecureSession();
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
    }

    // =========================================================
    // FORGOT PASSWORD (OTP Flow)
    // =========================================================

    /**
     * Step 1: Generate OTP and email it.
     */
    public function forgotPassword(string $email): array
    {
        $email = strtolower(trim($email));
        $stmt  = $this->db->prepare(
            'SELECT id, full_name FROM users WHERE email = ? AND is_active = 1'
        );
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // Always return success to avoid enumeration
        if (!$user) {
            return ['success' => true];
        }

        $otp       = str_pad((string) random_int(0, 999999), OTP_LENGTH, '0', STR_PAD_LEFT);
        $token     = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+' . OTP_EXPIRY_MINS . ' minutes'));

        // Invalidate old codes
        $this->db->prepare('UPDATE password_resets SET used_at = NOW() WHERE user_id = ? AND used_at IS NULL')
                 ->execute([$user['id']]);

        $this->db->prepare(
            'INSERT INTO password_resets (user_id, otp_code, token, expires_at) VALUES (?, ?, ?, ?)'
        )->execute([$user['id'], $otp, $token, $expiresAt]);

        Mailer::sendOTP($email, $user['full_name'], $otp);

        return ['success' => true, 'token' => $token];
    }

    /**
     * Step 2: Validate OTP code.
     */
    public function validateOTP(string $token, string $otp): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM password_resets
             WHERE token = ? AND used_at IS NULL AND expires_at > NOW()'
        );
        $stmt->execute([$token]);
        $reset = $stmt->fetch();

        if (!$reset) {
            return ['error' => 'This session has expired. Please request a new code.'];
        }

        if ((int) $reset['attempts'] >= 5) {
            return ['error' => 'Too many incorrect attempts. Please request a new code.'];
        }

        if (!hash_equals($reset['otp_code'], $otp)) {
            $this->db->prepare('UPDATE password_resets SET attempts = attempts + 1 WHERE id = ?')
                     ->execute([$reset['id']]);
            return ['error' => 'Incorrect code. Please try again.'];
        }

        return ['success' => true, 'reset_id' => $reset['id'], 'user_id' => $reset['user_id']];
    }

    /**
     * Step 3: Set new password.
     */
    public function resetPassword(string $token, string $otp, string $newPassword): array
    {
        $validate = $this->validateOTP($token, $otp);
        if (!isset($validate['success'])) {
            return $validate;
        }

        $pwCheck = $this->validatePassword($newPassword);
        if ($pwCheck !== true) {
            return ['error' => $pwCheck];
        }

        $hash = password_hash($newPassword, HASH_ALGO, ['cost' => HASH_COST]);
        $this->db->prepare('UPDATE users SET password_hash = ? WHERE id = ?')
                 ->execute([$hash, $validate['user_id']]);

        $this->db->prepare('UPDATE password_resets SET used_at = NOW() WHERE id = ?')
                 ->execute([$validate['reset_id']]);

        return ['success' => true];
    }

    // =========================================================
    // SESSION HELPERS
    // =========================================================

    public function isLoggedIn(): bool
    {
        $this->startSecureSession();
        return !empty($_SESSION['logged_in']) && !empty($_SESSION['user_id']);
    }

    public function requireLogin(): void
    {
        if (!$this->isLoggedIn()) {
            header('Location: ' . APP_URL . '/pages/login.php');
            exit;
        }
    }

    public function getCurrentUser(): ?array
    {
        if (!$this->isLoggedIn()) return null;
        return [
            'id'    => $_SESSION['user_id'],
            'name'  => $_SESSION['user_name'],
            'email' => $_SESSION['user_email'],
            'role'  => $_SESSION['user_role'],
        ];
    }

    // =========================================================
    // PRIVATE HELPERS
    // =========================================================

    private function startSecureSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_name(SESSION_NAME);
            session_set_cookie_params([
                'lifetime' => SESSION_LIFETIME,
                'path'     => '/',
                'domain'   => '',
                'secure'   => isset($_SERVER['HTTPS']),
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            session_start();
        }
    }

    private function validatePassword(string $password): bool|string
    {
        if (strlen($password) < 8) {
            return 'Password must be at least 8 characters.';
        }
        if (!preg_match('/[A-Z]/', $password)) {
            return 'Password must contain at least one uppercase letter.';
        }
        if (!preg_match('/[0-9]/', $password)) {
            return 'Password must contain at least one number.';
        }
        return true;
    }

    private function isLockedOut(string $email, string $ip): bool
    {
        $since = date('Y-m-d H:i:s', strtotime('-' . LOCKOUT_MINUTES . ' minutes'));
        $stmt  = $this->db->prepare(
            'SELECT COUNT(*) FROM login_attempts
             WHERE (email = ? OR ip_address = ?) AND attempted_at > ?'
        );
        $stmt->execute([$email, $ip, $since]);
        return (int) $stmt->fetchColumn() >= MAX_LOGIN_ATTEMPTS;
    }

    private function recordFailedAttempt(string $email, string $ip): void
    {
        $this->db->prepare('INSERT INTO login_attempts (email, ip_address) VALUES (?, ?)')
                 ->execute([$email, $ip]);
    }

    private function clearAttempts(string $email, string $ip): void
    {
        $this->db->prepare('DELETE FROM login_attempts WHERE email = ? OR ip_address = ?')
                 ->execute([$email, $ip]);
    }

    private function getClientIp(): string
    {
        foreach (['HTTP_CF_CONNECTING_IP','HTTP_X_FORWARDED_FOR','REMOTE_ADDR'] as $key) {
            if (!empty($_SERVER[$key])) {
                return trim(explode(',', $_SERVER[$key])[0]);
            }
        }
        return '0.0.0.0';
    }
}