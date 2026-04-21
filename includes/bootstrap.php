<?php
/**
 * includes/bootstrap.php
 * Load config + all classes. Include this at the top of every page.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '../../config/Database.php';
require_once __DIR__ . '../../classes/Auth.php';
require_once __DIR__ . '../../classes/Mailer.php';

// Global Auth instance
$auth = new Auth();