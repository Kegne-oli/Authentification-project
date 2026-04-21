<?php
require_once __DIR__ . '/../includes/bootstrap.php';

if ($auth->isLoggedIn()) {
    header('Location: ' . APP_URL . '/pages/dashboard.php'); exit;
}

$error      = '';
$unverified = false;
$emailVal   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = $_POST['email']    ?? '';
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    $result = $auth->login($email, $password, $remember);

    if (isset($result['success'])) {
        header('Location: ' . APP_URL . '/pages/dashboard.php'); exit;
    } else {
        $error      = $result['error'];
        $unverified = !empty($result['unverified']);
        $emailVal   = $result['email'] ?? $email;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign In · <?= APP_NAME ?></title>
  <link rel="stylesheet" href="../assets/css/main.css">
</head>
<body>
  <div class="blob blob-1"></div>
  <div class="blob blob-2"></div>

  <div class="page-center">
    <div class="card">
      <div class="brand">
        <a href="../index.php" style="display:flex;align-items:center;gap:12px;text-decoration:none;">
          <div class="brand-icon">⚡</div>
          <span class="brand-name"><?= APP_NAME ?></span>
        </a>
      </div>

      <h1 class="card-title">Welcome back</h1>
      <p class="card-subtitle">Sign in to your account.</p>

      <?php if ($error): ?>
        <div class="alert alert-error">
          ⚠ <?= htmlspecialchars($error) ?>
          <?php if ($unverified): ?>
            &nbsp;·&nbsp;
            <a class="link" href="resend_verification.php?email=<?= urlencode($emailVal) ?>">Resend verification</a>
          <?php endif; ?>
        </div>
      <?php endif; ?>

      <form method="POST" novalidate>
        <div class="form-group">
          <label for="email">Email Address</label>
          <div class="input-wrap">
            <span class="input-icon">✉</span>
            <input type="email" id="email" name="email"
                   value="<?= htmlspecialchars($emailVal) ?>"
                   placeholder="you@example.com" required autofocus>
          </div>
        </div>

        <div class="form-group">
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
            <label for="password" style="margin:0;">Password</label>
            <a class="link" href="forgot_password.php" style="font-size:13px;">Forgot password?</a>
          </div>
          <div class="input-wrap">
            <span class="input-icon">🔒</span>
            <input type="password" id="password" name="password"
                   placeholder="Your password" required autocomplete="current-password">
            <button type="button" class="toggle-pw" onclick="togglePw('password',this)" title="Show/hide">👁</button>
          </div>
        </div>

        <label class="checkbox-row" style="margin-bottom:24px;">
          <input type="checkbox" name="remember" <?= isset($_POST['remember']) ? 'checked' : '' ?>>
          Keep me signed in
        </label>

        <button type="submit" class="btn btn-primary">Sign In →</button>
      </form>

      <div class="form-footer">
        Don't have an account? <a class="link" href="register.php">Create one</a>
      </div>
    </div>
  </div>

  <script>
    function togglePw(id, btn) {
      const inp = document.getElementById(id);
      inp.type  = inp.type === 'password' ? 'text' : 'password';
      btn.textContent = inp.type === 'password' ? '👁' : '🙈';
    }
  </script>
</body>
</html>