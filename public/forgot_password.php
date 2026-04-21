<?php
require_once __DIR__ . '/../includes/bootstrap.php';

$error   = '';
$success = false;
$token   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email  = $_POST['email'] ?? '';
    $result = $auth->forgotPassword($email);
    if (isset($result['success'])) {
        $success = true;
        $token   = $result['token'] ?? '';
    } else {
        $error = $result['error'] ?? 'Something went wrong.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Forgot Password · <?= APP_NAME ?></title>
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

      <!-- Progress Steps -->
      <div class="steps">
        <div class="step active">
          <div class="step-dot">1</div>
          <div class="step-label">Email</div>
        </div>
        <div class="step-line"></div>
        <div class="step">
          <div class="step-dot">2</div>
          <div class="step-label">Code</div>
        </div>
        <div class="step-line"></div>
        <div class="step">
          <div class="step-dot">3</div>
          <div class="step-label">Reset</div>
        </div>
      </div>

      <?php if ($success): ?>
        <div style="text-align:center;">
          <div style="font-size:56px;margin-bottom:20px;">📬</div>
          <h1 class="card-title">Check your email</h1>
          <p class="card-subtitle" style="margin-bottom:32px;">
            If that address has an account, we've sent a 6-digit code. It expires in <?= OTP_EXPIRY_MINS ?> minutes.
          </p>
          <?php if ($token): ?>
            <!-- In development, the token is passed via URL. In production, only email. -->
            <a href="verify_otp.php?token=<?= urlencode($token) ?>" class="btn btn-primary">
              Enter Code →
            </a>
          <?php else: ?>
            <a href="verify_otp.php" class="btn btn-primary">Enter Code →</a>
          <?php endif; ?>
        </div>
      <?php else: ?>
        <h1 class="card-title">Forgot password?</h1>
        <p class="card-subtitle">No worries — we'll email you a reset code.</p>

        <?php if ($error): ?>
          <div class="alert alert-error">⚠ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" novalidate>
          <div class="form-group">
            <label for="email">Email Address</label>
            <div class="input-wrap">
              <span class="input-icon">✉</span>
              <input type="email" id="email" name="email"
                     value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                     placeholder="you@example.com" required autofocus>
            </div>
          </div>
          <button type="submit" class="btn btn-primary">Send Reset Code →</button>
        </form>

        <div class="form-footer">
          Remember it? <a class="link" href="login.php">Sign in</a>
        </div>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>