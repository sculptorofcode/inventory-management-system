<?php
require_once 'includes/config/after-login.php';
$session->destroy();
header('Location: login.php');