<?php
require_once __DIR__ . '/../includes/bootstrap.php';

$auth->requireLogin();
$user = $auth->getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard · <?= APP_NAME ?></title>
  <link rel="stylesheet" href="../assets/css/main.css">
  <style>
    .dashboard { position: relative; z-index: 1; min-height: 100vh; }

    /* Top bar */
    .topbar {
      display: flex; align-items: center; justify-content: space-between;
      padding: 20px 48px;
      border-bottom: 1px solid var(--border);
    }
    .topbar-brand { display: flex; align-items: center; gap: 12px; }
    .topbar-brand-icon {
      width: 36px; height: 36px;
      background: linear-gradient(135deg,#6c63ff,#3ecfcf);
      border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 16px;
    }
    .topbar-brand-name {
      font-family: 'Syne', sans-serif; font-weight: 700; font-size: 17px;
      background: linear-gradient(90deg,#fff,#a0a0d0);
      -webkit-background-clip: text; -webkit-text-fill-color: transparent;
    }
    .topbar-user { display: flex; align-items: center; gap: 16px; }
    .avatar {
      width: 40px; height: 40px; border-radius: 50%;
      background: linear-gradient(135deg,#6c63ff,#3ecfcf);
      display: flex; align-items: center; justify-content: center;
      font-weight: 700; color: #fff; font-size: 15px;
      font-family: 'Syne', sans-serif;
    }
    .topbar-name { font-size: 14px; font-weight: 500; color: var(--text); }
    .topbar-logout {
      padding: 9px 20px; border-radius: 50px;
      border: 1px solid var(--border); background: none;
      color: var(--text-muted); font-family: 'DM Sans', sans-serif;
      font-size: 13px; cursor: pointer; text-decoration: none;
      transition: all 0.2s;
    }
    .topbar-logout:hover { border-color: rgba(255,92,108,0.4); color: #ff8090; }

    /* Main content */
    .main { max-width: 960px; margin: 0 auto; padding: 60px 24px; }

    .welcome-banner {
      background: var(--bg-card);
      border: 1px solid var(--border);
      border-radius: var(--radius-lg);
      padding: 48px;
      margin-bottom: 36px;
      display: flex; align-items: center; justify-content: space-between;
      gap: 24px;
      animation: cardIn 0.5s cubic-bezier(0.22,1,0.36,1) both;
    }
    .welcome-text h2 {
      font-family: 'Syne', sans-serif; font-size: 30px; font-weight: 700;
      color: #fff; margin-bottom: 10px;
    }
    .welcome-text p { color: var(--text-muted); font-size: 15px; line-height: 1.6; }
    .welcome-emoji { font-size: 72px; flex-shrink: 0; }

    /* Stats */
    .stats { display: grid; grid-template-columns: repeat(auto-fit,minmax(200px,1fr)); gap: 20px; margin-bottom: 36px; }
    .stat-card {
      background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius);
      padding: 28px 24px;
      animation: cardIn 0.5s cubic-bezier(0.22,1,0.36,1) both;
    }
    .stat-card:nth-child(1) { animation-delay: 0.1s; }
    .stat-card:nth-child(2) { animation-delay: 0.15s; }
    .stat-card:nth-child(3) { animation-delay: 0.2s; }
    .stat-label { font-size: 12px; text-transform: uppercase; letter-spacing: 0.08em; color: var(--text-muted); margin-bottom: 12px; }
    .stat-value { font-family: 'Syne', sans-serif; font-size: 22px; font-weight: 700; color: #fff; }
    .stat-icon { font-size: 28px; margin-bottom: 14px; }

    /* Info card */
    .info-grid { display: grid; grid-template-columns: repeat(auto-fit,minmax(280px,1fr)); gap: 20px; }
    .info-card {
      background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius);
      padding: 28px 24px;
      animation: cardIn 0.5s cubic-bezier(0.22,1,0.36,1) both 0.25s;
    }
    .info-card h3 { font-family: 'Syne', sans-serif; font-size: 15px; font-weight: 700; color: #fff; margin-bottom: 16px; }
    .info-row { display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid var(--border); font-size: 14px; }
    .info-row:last-child { border-bottom: none; }
    .info-row-label { color: var(--text-muted); }
    .info-row-value { color: var(--text); font-weight: 500; }
    .badge {
      display: inline-block; padding: 3px 12px; border-radius: 50px; font-size: 11px; font-weight: 600;
    }
    .badge-success { background: rgba(62,207,207,0.15); color: var(--success); border: 1px solid rgba(62,207,207,0.25); }
    .badge-role { background: rgba(108,99,255,0.15); color: var(--primary); border: 1px solid rgba(108,99,255,0.25); }

    @media (max-width: 600px) {
      .topbar { padding: 20px 20px; }
      .welcome-banner { flex-direction: column; text-align: center; padding: 32px 24px; }
      .welcome-emoji { font-size: 56px; }
    }
  </style>
</head>
<body>
  <div class="blob blob-1"></div>
  <div class="blob blob-2"></div>

  <div class="dashboard">
    <!-- Top Bar -->
    <nav class="topbar">
      <div class="topbar-brand">
        <div class="topbar-brand-icon">⚡</div>
        <span class="topbar-brand-name"><?= APP_NAME ?></span>
      </div>
      <div class="topbar-user">
        <div class="avatar"><?= strtoupper(substr($user['name'], 0, 1)) ?></div>
        <span class="topbar-name"><?= htmlspecialchars($user['name']) ?></span>
        <a href="logout.php" class="topbar-logout">Sign out</a>
      </div>
    </nav>

    <!-- Main -->
    <div class="main">
      <!-- Welcome Banner -->
      <div class="welcome-banner">
        <div class="welcome-text">
          <h2>Welcome back, <?= htmlspecialchars(explode(' ', $user['name'])[0]) ?>! 👋</h2>
          <p>You're successfully authenticated. Your session is active and secure.<br>
             This is your protected dashboard — only logged-in users can see this page.</p>
        </div>
        <div class="welcome-emoji">🔐</div>
      </div>

      <!-- Stats -->
      <div class="stats">
        <div class="stat-card">
          <div class="stat-icon">✅</div>
          <div class="stat-label">Account Status</div>
          <div class="stat-value" style="color: var(--success);">Active</div>
        </div>
        <div class="stat-card">
          <div class="stat-icon">🛡️</div>
          <div class="stat-label">Security Level</div>
          <div class="stat-value" style="color: var(--primary);">High</div>
        </div>
        <div class="stat-card">
          <div class="stat-icon">🔑</div>
          <div class="stat-label">Session</div>
          <div class="stat-value" style="font-size:16px;color:#a0a0d0;">Active</div>
        </div>
      </div>

      <!-- Account Details -->
      <div class="info-grid">
        <div class="info-card">
          <h3>Account Details</h3>
          <div class="info-row">
            <span class="info-row-label">Full Name</span>
            <span class="info-row-value"><?= htmlspecialchars($user['name']) ?></span>
          </div>
          <div class="info-row">
            <span class="info-row-label">Email</span>
            <span class="info-row-value"><?= htmlspecialchars($user['email']) ?></span>
          </div>
          <div class="info-row">
            <span class="info-row-label">Role</span>
            <span class="badge badge-role"><?= htmlspecialchars(ucfirst($user['role'])) ?></span>
          </div>
          <div class="info-row">
            <span class="info-row-label">Email Verified</span>
            <span class="badge badge-success">✓ Verified</span>
          </div>
        </div>

        <div class="info-card">
          <h3>Security Info</h3>
          <div class="info-row">
            <span class="info-row-label">Password Hashing</span>
            <span class="info-row-value">Bcrypt (cost <?= HASH_COST ?>)</span>
          </div>
          <div class="info-row">
            <span class="info-row-label">Session Cookie</span>
            <span class="info-row-value">HttpOnly + SameSite</span>
          </div>
          <div class="info-row">
            <span class="info-row-label">Brute Force Lock</span>
            <span class="info-row-value">After <?= MAX_LOGIN_ATTEMPTS ?> attempts</span>
          </div>
          <div class="info-row">
            <span class="info-row-label">OTP Expiry</span>
            <span class="info-row-value"><?= OTP_EXPIRY_MINS ?> minutes</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>