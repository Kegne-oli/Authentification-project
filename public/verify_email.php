<?php
require_once __DIR__ . '/../includes/bootstrap.php';

$token  = trim($_GET['token'] ?? '');
$result = ['error' => 'No verification token provided.'];

if ($token !== '') {
    $result = $auth->verifyEmail($token);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Email Verification · <?= APP_NAME ?></title>
  <link rel="stylesheet" href="../assets/css/main.css">
</head>
<body>
  <div class="blob blob-1"></div>
  <div class="blob blob-2"></div>

  <div class="page-center">
    <div class="card" style="text-align:center;">
      <div class="brand" style="justify-content:center;">
        <div class="brand-icon">⚡</div>
        <span class="brand-name"><?= APP_NAME ?></span>
      </div>

      <?php if (isset($result['success'])): ?>
        <div style="font-size:64px;margin:20px 0;">✅</div>
        <h1 class="card-title">Email verified!</h1>
        <p class="card-subtitle" style="margin-bottom:32px;">
          Your account is now active. You can sign in.
        </p>
        <a href="login.php" class="btn btn-primary">Go to Sign In →</a>
      <?php else: ?>
        <div style="font-size:64px;margin:20px 0;">❌</div>
        <h1 class="card-title">Verification failed</h1>
        <p class="card-subtitle" style="margin-bottom:32px;">
          <?= htmlspecialchars($result['error']) ?>
        </p>
        <a href="resend_verification.php" class="btn btn-ghost">Request new link</a>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>