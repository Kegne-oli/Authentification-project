<?php
require_once __DIR__ . '/../includes/bootstrap.php';

if ($auth->isLoggedIn()) {
    header('Location: ' . APP_URL . '/pages/dashboard.php'); exit;
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = $_POST['full_name'] ?? ''; 
    $email    = $_POST['email']     ?? '';
    $password = $_POST['password']  ?? '';
    $confirm  = $_POST['confirm']   ?? '';

    if ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $result = $auth->register($fullName, $email, $password);
        if (isset($result['success'])) {
            $success = 'Account created! Check your email to verify your address before signing in.';
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
  <title>Create Account · <?= APP_NAME ?></title>
  <link rel="stylesheet" href="../assets/css/main.css">
</head>
<body>
  <div class="blob blob-1"></div>
  <div class="blob blob-2"></div>

  <div class="page-center">
    <div class="card">
      <!-- Brand -->
      <div class="brand">
        <a href="../index.php" style="display:flex;align-items:center;gap:12px;text-decoration:none;">
          <div class="brand-icon">⚡</div>
          <span class="brand-name"><?= APP_NAME ?></span>
        </a>
      </div>

      <h1 class="card-title">Create account</h1>
      <p class="card-subtitle">Join us — it only takes a moment.</p>

      <?php if ($error): ?>
        <div class="alert alert-error">⚠ <?= htmlspecialchars($error) ?></div>
      <?php endif; ?>
      <?php if ($success): ?>
        <div class="alert alert-success">✓ <?= htmlspecialchars($success) ?></div>
      <?php endif; ?>

      <?php if (!$success): ?>
      <form method="POST" novalidate>
        <!-- Full Name -->
        <div class="form-group">
          <label for="full_name">Full Name</label>
          <div class="input-wrap">
            <span class="input-icon">👤</span>
            <input type="text" id="full_name" name="full_name"
                   value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>"
                   placeholder="Your full name" required autofocus>
          </div>
        </div>

        <!-- Email -->
        <div class="form-group">
          <label for="email">Email Address</label>
          <div class="input-wrap">
            <span class="input-icon">✉</span>
            <input type="email" id="email" name="email"
                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                   placeholder="you@example.com" required>
          </div>
        </div>

        <!-- Password -->
        <div class="form-group">
          <label for="password">Password</label>
          <div class="input-wrap">
            <span class="input-icon">🔒</span>
            <input type="password" id="password" name="password"
                   placeholder="Min 8 chars, 1 uppercase, 1 number"
                   required autocomplete="new-password"
                   oninput="updateStrength(this.value)">
            <button type="button" class="toggle-pw" onclick="togglePw('password',this)" title="Show/hide">👁</button>
          </div>
          <!-- Strength bar -->
          <div class="strength-bar-wrap" id="strength-bar">
            <div class="strength-seg" id="s1"></div>
            <div class="strength-seg" id="s2"></div>
            <div class="strength-seg" id="s3"></div>
            <div class="strength-seg" id="s4"></div>
          </div>
          <div class="strength-label" id="strength-label"></div>
        </div>

        <!-- Confirm -->
        <div class="form-group">
          <label for="confirm">Confirm Password</label>
          <div class="input-wrap">
            <span class="input-icon">🔒</span>
            <input type="password" id="confirm" name="confirm"
                   placeholder="Repeat your password"
                   required autocomplete="new-password">
            <button type="button" class="toggle-pw" onclick="togglePw('confirm',this)" title="Show/hide">👁</button>
          </div>
        </div>

        <button type="submit" class="btn btn-primary" style="margin-top:8px;">
          Create Account →
        </button>
      </form>

      <div class="form-footer">
        Already have an account? <a class="link" href="login.php">Sign in</a>
      </div>
      <?php else: ?>
        <div style="text-align:center;margin-top:16px;">
          <a class="btn btn-primary" href="login.php" style="display:inline-flex;width:auto;padding:14px 32px;">
            Go to Sign In →
          </a>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <script>
    function togglePw(id, btn) {
      const inp = document.getElementById(id);
      inp.type = inp.type === 'password' ? 'text' : 'password';
      btn.textContent = inp.type === 'password' ? '👁' : '🙈';
    }

    function updateStrength(pw) {
      const segs   = ['s1','s2','s3','s4'];
      const label  = document.getElementById('strength-label');
      let score    = 0;
      if (pw.length >= 8)              score++;
      if (/[A-Z]/.test(pw))            score++;
      if (/[0-9]/.test(pw))            score++;
      if (/[^A-Za-z0-9]/.test(pw))     score++;

      const colors  = ['','#ff5c6c','#ffaa00','#6c63ff','#3ecfcf'];
      const labels  = ['','Weak','Fair','Good','Strong'];
      segs.forEach((id,i) => {
        document.getElementById(id).style.background =
          i < score ? colors[score] : 'var(--border)';
      });
      label.textContent = pw.length ? labels[score] : '';
      label.style.color = colors[score];
    }
  </script>
</body>
</html>