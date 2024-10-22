<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'ims');

define('SMSAPI_AUTHORIZATION', 'ijVXwPtmXKo8gv55kfnATsqdKJHw98GwGNL2jGm9syjhigBN5t4goHWTinmS');

define('SITE_URL', 'http://localhost/ims-project');
define('LOGIN_URL', 'http://localhost/ims-project/login');

// App settings
define('APP_NAME', 'Inventory Management System');
define('APP_VERSION', '1.0.0');
define('APP_LOGO_LANDSCAPE', SITE_URL . "/assets/images/logo/transparent/logo.png");
define('APP_LOGO_PORTRAIT', SITE_URL . "/assets/images/logo/transparent/1.png");
define('APP_LOGO_ICON', SITE_URL . "/assets/images/logo/transparent/4.png");
define('APP_LOGO', SITE_URL . "/assets/images/logo/transparent/logo.png");
define('APP_EMAIL', 'softwaredevsaikat@gmail.com');
define('APP_EMAIL_PASSWORD', 'bpta fnsb ipsx vtvg');

// Debug mode (set to true for development, false for production)
define('DEBUG_MODE', true);

// Session settings
session_start();

date_default_timezone_set('Asia/Kolkata');


// Error reporting (toggle based on DEBUG_MODE)
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Example of a global function
function base_url($path = '')
{
    return SITE_URL . '/' . ltrim($path, '/');
}