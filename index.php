<?php include_once 'includes/config/config.php' ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?></title>
    <?php include_once 'includes/layouts/header.php' ?>
</head>

<body>
    <!-- Header Section -->
    <header class="bg-dark text-white py-3">
        <div class="container d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <img src="<?= APP_LOGO_ICON ?>" alt="IMS Logo" class="me-2" width="50">
                <h1 class="h4 mb-0"><?= APP_NAME ?></h1>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero-section text-center text-white d-flex align-items-center"
        style="background-image: url('assets/images/hero-bg.png');">
        <div class="container">
            <h2 class="display-4 fw-bold mb-4">Welcome to the Inventory Management System</h2>
            <p class="lead mb-5">Efficiently manage your products, suppliers, and customer orders in one place.</p>
            <a href="login" class="btn btn-primary btn-lg px-5 shadow-lg">Get Started</a>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <h2 class="text-center mb-5">Our Features</h2>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card border-0 feature-card shadow h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-box-open fa-3x mb-3 text-primary"></i>
                            <h3 class="card-title">Product Management</h3>
                            <p class="card-text">Track inventory, manage stock levels, and ensure product availability.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card border-0 feature-card shadow h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-shopping-cart fa-3x mb-3 text-primary"></i>
                            <h3 class="card-title">Order Processing</h3>
                            <p class="card-text">Simplify order creation, tracking, and fulfillment for customers and
                                suppliers.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card border-0 feature-card shadow h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-chart-line fa-3x mb-3 text-primary"></i>
                            <h3 class="card-title">Reports & Analytics</h3>
                            <p class="card-text">Gain insights into your inventory and sales with comprehensive
                                reporting tools.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer Section -->
    <footer class="bg-dark text-white py-4">
        <div class="container text-center">
            <p>&copy; 2024 <?= APP_NAME ?>. All rights reserved.</p>
            <div class="social-icons mt-3">
                <a href="#" class="text-white me-3"><i class="fab fa-facebook fa-lg"></i></a>
                <a href="#" class="text-white me-3"><i class="fab fa-twitter fa-lg"></i></a>
                <a href="#" class="text-white"><i class="fab fa-linkedin fa-lg"></i></a>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS and dependencies -->
    <script src="assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>