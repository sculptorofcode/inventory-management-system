<?php
// Sample configuration file - RENAME TO config.php AND UPDATE WITH YOUR SETTINGS
const DB_HOST = 'localhost';
const DB_USER = 'your_username';
const DB_PASS = 'your_password';
const DB_NAME = 'ims';

const SMSAPI_AUTHORIZATION = 'your_smsapi_authorization_key';

const SITE_URL = 'http://localhost/ims-project';
const LOGIN_URL = 'http://localhost/ims-project/login';

// App settings
const APP_NAME = 'Inventory Management System';
const APP_VERSION = '1.0.0';
const APP_LOGO_LANDSCAPE = SITE_URL . "/assets/images/logo/transparent/logo.png";
const APP_LOGO_PORTRAIT = SITE_URL . "/assets/images/logo/transparent/1.png";
const APP_LOGO_ICON = SITE_URL . "/assets/images/logo/transparent/4.png";
const APP_LOGO = SITE_URL . "/assets/images/logo/transparent/logo.png";
const APP_EMAIL = 'your_email@example.com';
const APP_EMAIL_PASSWORD = 'your_email_password';


const DEBUG_MODE = true;

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