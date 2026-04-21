<?php
require_once __DIR__ . '/../includes/bootstrap.php';
$auth->logout();
header('Location: ' . APP_URL . '/pages/login.php?logged_out=1');
exit;