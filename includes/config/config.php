<?php
const DB_HOST = 'localhost';
const DB_USER = 'root';
const DB_PASS = '';
const DB_NAME = 'ims';

const SMSAPI_AUTHORIZATION = 'ijVXwPtmXKo8gv55kfnATsqdKJHw98GwGNL2jGm9syjhigBN5t4goHWTinmS';

const SITE_URL = 'http://localhost/ims-project';
const LOGIN_URL = 'http://localhost/ims-project/login';

// App settings
const APP_NAME = 'Inventory Management System';
const APP_VERSION = '1.0.0';
const APP_LOGO_LANDSCAPE = SITE_URL . "/assets/images/logo/transparent/logo.png";
const APP_LOGO_PORTRAIT = SITE_URL . "/assets/images/logo/transparent/1.png";
const APP_LOGO_ICON = SITE_URL . "/assets/images/logo/transparent/4.png";
const APP_LOGO = SITE_URL . "/assets/images/logo/transparent/logo.png";
const APP_EMAIL = 'softwaredevsaikat@gmail.com';
const APP_EMAIL_PASSWORD = 'bpta fnsb ipsx vtvg';

// Debug mode (set to true for development, false for production)
const DEBUG_MODE = true;

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