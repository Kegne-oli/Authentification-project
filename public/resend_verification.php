<?php
require_once __DIR__ . '/../includes/bootstrap.php';

$token = trim($_GET['token'] ?? $_POST['token'] ?? '');
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $otp   = implode('', array_map('trim', $_POST['otp'] ?? []));
    $result = $auth->validateOTP($token, $otp);

    if (isset($result['success'])) {
        // Store validated token in session to carry to step 3
        session_name(SESSION_NAME);
        session_start();
        $_SESSION['reset_token'] = $token;
        $_SESSION['reset_otp']   = $otp;
        header('Location: reset_password.php'); exit;
    } else {
        $error = $result['error'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Enter Code · <?= APP_NAME ?></title>
  <link rel="stylesheet" href="../assets/css/main.css">
  <style>
    .timer { font-size: 13px; color: var(--text-muted); text-align:center; margin-bottom: 20px; }
    .timer span { color: var(--primary-2); font-weight: 600; }
  </style>
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
        <div class="step active">
          <div class="step-dot">2</div>
          <div class="step-label">Code</div>
        </div>
        <div class="step-line"></div>
        <div class="step">
          <div class="step-dot">3</div>
          <div class="step-label">Reset</div>
        </div>
      </div>

      <h1 class="card-title" style="text-align:center;">Enter the code</h1>
      <p class="card-subtitle" style="text-align:center;margin-bottom:28px;">
        We sent a 6-digit code to your inbox. It expires in <?= OTP_EXPIRY_MINS ?> minutes.
      </p>

      <?php if ($error): ?>
        <div class="alert alert-error">⚠ <?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <div class="timer">Code expires in <span id="countdown"></span></div>

      <form method="POST" id="otp-form" novalidate>
        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

        <!-- 6 OTP digit boxes -->
        <div class="otp-grid">
          <?php for ($i = 0; $i < 6; $i++): ?>
            <input type="text" name="otp[]" maxlength="1" pattern="[0-9]"
                   inputmode="numeric" autocomplete="one-time-code"
                   class="otp-input"
                   <?= $i === 0 ? 'autofocus' : '' ?>>
          <?php endfor; ?>
        </div>

        <button type="submit" class="btn btn-primary" id="verify-btn" disabled>
          Verify Code →
        </button>
      </form>

      <div class="form-footer" style="margin-top:20px;">
        <a class="link" href="forgot_password.php">← Resend code</a>
      </div>
    </div>
  </div>

  <script>
    // ── OTP input UX ──────────────────────────────────────────
    const inputs = document.querySelectorAll('.otp-input');
    const btn    = document.getElementById('verify-btn');

    inputs.forEach((inp, i) => {
      inp.addEventListener('input', () => {
        inp.value = inp.value.replace(/\D/g, '');
        if (inp.value && i < inputs.length - 1) inputs[i + 1].focus();
        checkComplete();
      });
      inp.addEventListener('keydown', (e) => {
        if (e.key === 'Backspace' && !inp.value && i > 0) inputs[i - 1].focus();
      });
      inp.addEventListener('paste', (e) => {
        e.preventDefault();
        const paste = (e.clipboardData || window.clipboardData)
                       .getData('text').replace(/\D/g, '').slice(0, 6);
        paste.split('').forEach((d, j) => { if (inputs[j]) inputs[j].value = d; });
        if (inputs[paste.length - 1]) inputs[paste.length - 1].focus();
        checkComplete();
      });
    });

    function checkComplete() {
      const all = [...inputs].every(i => i.value.length === 1);
      btn.disabled = !all;
    }

    // ── Countdown timer ───────────────────────────────────────
    let secs = <?= OTP_EXPIRY_MINS * 60 ?>;
    const el = document.getElementById('countdown');
    const tick = () => {
      if (secs <= 0) { el.textContent = 'expired'; el.style.color = 'var(--danger)'; return; }
      const m = String(Math.floor(secs / 60)).padStart(2,'0');
      const s = String(secs % 60).padStart(2,'0');
      el.textContent = m + ':' + s;
      secs--;
      setTimeout(tick, 1000);
    };
    tick();

    // Auto-submit when all digits filled
    document.getElementById('otp-form').addEventListener('input', () => {
      if ([...inputs].every(i => i.value.length === 1)) {
        setTimeout(() => document.getElementById('otp-form').submit(), 300);
      }
    });
  </script>
</body>
</html>