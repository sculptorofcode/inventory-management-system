<?php
include_once 'includes/config/database.php';
$title = "Login";

if(isset($_SESSION['logged_in']) && $_SESSION['logged_in'] == true) {
    header('Location: dashboard');
    exit;
}

if (isset($_POST['login'])) {
    $email = filtervar($_POST['email']);
    $password = filtervar($_POST['password']);

    $sql = "SELECT * FROM $table_customers WHERE email = :email";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $db_password = $row['password_hash'];
        if (password_verify($password, $db_password)) {
            $_SESSION['customer_id'] = $row['customer_id'];
            $_SESSION['logged_in'] = true;
            $res = [
                'status' => 'success',
                'message' => 'Login successful! Redirecting...',
                'redirect' => 'dashboard',
                'delay' => 1000
            ];
        } else {
            $res = [
                'status' => 'error',
                'message' => 'Invalid email or password',
                'errors' => [
                    'email' => 'Invalid email or password',
                    'password' => 'Invalid email or password'
                ]
            ];
        }
    } else {
        $res = [
            'status' => 'error',
            'message' => 'Invalid email or password',
            'errors' => [
                'email' => 'Invalid email or password',
                'password' => 'Invalid email or password'
            ]
        ];
    }
    echo json_encode($res);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include_once 'includes/layouts/header.php' ?>
</head>

<body class="login-body">
    <section class="vh-100 d-flex align-items-center justify-content-center">
        <div class="container">
            <div class="login-container">
                <div class="login-header">
                    <a href="index" class="text-decoration-none">
                        <h1><?= APP_NAME ?></h1>
                    </a>
                    <p>Welcome back! Please login to your account.</p>
                </div>
                <form action="" method="POST" id="loginForm" class="form">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email address</label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="Enter Email"
                            required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="●●●●●●●●"
                            required>
                    </div>
                    <div class="text-center">
                        <button type="submit" name="login" class="btn btn-primary w-75">Login</button>
                    </div>
                </form>
                <div class="mt-3 d-flex justify-content-between">
                    <a href="register" class="register">Create an account</a>
                    <a href="forgot-password" class="forgot-password">Forgot Password?</a>
                </div>
            </div>
        </div>
    </section>
    <?php include_once 'includes/layouts/footer.php' ?>
</body>

</html>