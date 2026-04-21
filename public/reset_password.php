<?php
require_once __DIR__ . '/../includes/bootstrap.php';

session_name(SESSION_NAME);
session_start();

$token = $_SESSION['reset_token'] ?? '';
$otp   = $_SESSION['reset_otp']   ?? '';

if (!$token || !$otp) {
    header('Location: forgot_password.php'); exit;
}

$error   = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newPw  = $_POST['password'] ?? '';
    $confirm = $_POST['confirm']  ?? '';

    if ($newPw !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $result = $auth->resetPassword($token, $otp, $newPw);
        if (isset($result['success'])) {
            $success = true;
            unset($_SESSION['reset_token'], $_SESSION['reset_otp']);
        } else {
            $error = $result['error'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reset Password · <?= APP_NAME ?></title>
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

      <!-- Steps -->
      <div class="steps">
        <div class="step done">
          <div class="step-dot">✓</div>
          <div class="step-label">Email</div>
        </div>
        <div class="step-line done"></div>
        <div class="step done">
          <div class="step-dot">✓</div>
          <div class="step-label">Code</div>
        </div>
        <div class="step-line done"></div>
        <div class="step <?= $success ? 'done' : 'active' ?>">
          <div class="step-dot"><?= $success ? '✓' : '3' ?></div>
          <div class="step-label">Reset</div>
        </div>
      </div>

      <?php if ($success): ?>
        <div style="text-align:center;">
          <div style="font-size:56px;margin-bottom:20px;">🎉</div>
          <h1 class="card-title">Password updated!</h1>
          <p class="card-subtitle" style="margin-bottom:32px;">
            Your password has been changed. You can now sign in with your new credentials.
          </p>
          <a href="login.php" class="btn btn-primary">Sign In Now →</a>
        </div>
      <?php else: ?>
        <h1 class="card-title">Set new password</h1>
        <p class="card-subtitle">Choose a strong password for your account.</p>

        <?php if ($error): ?>
          <div class="alert alert-error">⚠ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" novalidate>
          <div class="form-group">
            <label for="password">New Password</label>
            <div class="input-wrap">
              <span class="input-icon">🔒</span>
              <input type="password" id="password" name="password"
                     placeholder="Min 8 chars, 1 uppercase, 1 number"
                     required autocomplete="new-password"
                     autofocus oninput="updateStrength(this.value)">
              <button type="button" class="toggle-pw" onclick="togglePw('password',this)">👁</button>
            </div>
            <div class="strength-bar-wrap">
              <div class="strength-seg" id="s1"></div>
              <div class="strength-seg" id="s2"></div>
              <div class="strength-seg" id="s3"></div>
              <div class="strength-seg" id="s4"></div>
            </div>
            <div class="strength-label" id="strength-label"></div>
          </div>

          <div class="form-group">
            <label for="confirm">Confirm Password</label>
            <div class="input-wrap">
              <span class="input-icon">🔒</span>
              <input type="password" id="confirm" name="confirm"
                     placeholder="Repeat your new password"
                     required autocomplete="new-password">
              <button type="button" class="toggle-pw" onclick="togglePw('confirm',this)">👁</button>
            </div>
          </div>

          <button type="submit" class="btn btn-primary">Update Password →</button>
        </form>
      <?php endif; ?>
    </div>
  </div>

  <script>
    function togglePw(id, btn) {
      const inp = document.getElementById(id);
      inp.type  = inp.type === 'password' ? 'text' : 'password';
      btn.textContent = inp.type === 'password' ? '👁' : '🙈';
    }
    function updateStrength(pw) {
      let score = 0;
      if (pw.length >= 8)          score++;
      if (/[A-Z]/.test(pw))        score++;
      if (/[0-9]/.test(pw))        score++;
      if (/[^A-Za-z0-9]/.test(pw)) score++;
      const colors = ['','#ff5c6c','#ffaa00','#6c63ff','#3ecfcf'];
      const labels = ['','Weak','Fair','Good','Strong'];
      ['s1','s2','s3','s4'].forEach((id,i) => {
        document.getElementById(id).style.background = i < score ? colors[score] : 'var(--border)';
      });
      const lbl = document.getElementById('strength-label');
      lbl.textContent = pw.length ? labels[score] : '';
      lbl.style.color = colors[score];
    }
  </script>
</body>
</html>