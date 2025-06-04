<?php
/**
 * Maintenance Page
 * 
 * This page is displayed when a feature is temporarily disabled
 */
require_once 'includes/config/after-login.php';
$title = "Maintenance";

// Get the referring page
$referrer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
$feature = isset($_GET['feature']) ? $_GET['feature'] : 'This feature';
?>
<!DOCTYPE html>
<html lang="en" class="light-style layout-menu-fixed" dir="ltr" data-theme="theme-default" data-assets-path="assets/"
    data-template="vertical-menu-template-free">

<head>
    <meta charset="utf-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <?php include './includes/layouts/styles.php'; ?>
    <style>
        .maintenance-icon {
            font-size: 72px;
            color: #696cff;
        }
    </style>
</head>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <?php include './includes/layouts/sidebar.php'; ?>
            <div class="layout-page">
                <?php include './includes/layouts/navbar.php'; ?>
                <div class="content-wrapper">
                    <div class="container-fluid flex-grow-1 container-p-y">
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body text-center py-5">
                                        <div class="maintenance-icon mb-4">
                                            <i class="bx bx-time"></i>
                                        </div>
                                        <h2 class="mb-3">Temporarily Unavailable</h2>
                                        <p class="mb-4"><?= htmlspecialchars($feature) ?> is currently disabled for maintenance.</p>
                                        <p class="text-muted mb-4">We apologize for the inconvenience. Please check back later.</p>
                                        <a href="dashboard" class="btn btn-primary">Go to Dashboard</a>
                                        <?php if ($referrer): ?>
                                            <a href="<?= htmlspecialchars($referrer) ?>" class="btn btn-outline-secondary ms-2">Go Back</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php include './includes/layouts/footer.php'; ?>
            </div>
        </div>
    </div>
    <?php include 'includes/layouts/scripts.php'; ?>
</body>

</html>
