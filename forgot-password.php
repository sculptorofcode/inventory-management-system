<?php include_once 'includes/config/database.php';
$title = "Forgot Password";
if (isset($_POST['reset_password'])) {
    $email = filtervar($_POST['email']);
    $sql = "SELECT * FROM `tbl_customers` WHERE email = :email";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        $token = bin2hex(random_bytes(50));
        $otp = substr($token, 0, 6);
        $sql = "UPDATE `tbl_customers` SET token = :token WHERE email = :email";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':token', $otp);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $subject = "Reset your password";
        $body = '
                <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>OTP Verification</title>
                <style>
                    body {
                        font-family: \'Segoe UI\', Tahoma, Geneva, Verdana, sans-serif;
                        color: #333;
                        line-height: 1.6;
                        background-color: #f0f2f5;
                        margin: 0;
                        padding: 0;
                    }
                    .container {
                        background-color: #ffffff;
                        max-width: 600px;
                        margin: 20px auto;
                        padding: 40px;
                        border-radius: 8px;
                        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                    }
                    h1 {
                        color: #2c3e50;
                        font-size: 28px;
                        margin-bottom: 20px;
                        text-align: center;
                    }
                    .otp {
                        font-size: 36px;
                        font-weight: bold;
                        color: #3498db;
                        text-align: center;
                        margin: 30px 0;
                        letter-spacing: 5px;
                    }
                    .button {
                        display: block;
                        width: 200px;
                        margin: 30px auto;
                        padding: 15px 25px;
                        background-color: #2ecc71;
                        color: white;
                        text-align: center;
                        text-decoration: none;
                        border-radius: 5px;
                        font-weight: bold;
                        transition: background-color 0.3s ease;
                    }
                    .button:hover {
                        background-color: #27ae60;
                    }
                    .footer {
                        margin-top: 40px;
                        font-size: 14px;
                        color: #7f8c8d;
                        text-align: center;
                    }
                    @media only screen and (max-width: 600px) {
                        .container {
                            padding: 20px;
                        }
                    }
                </style>
            </head>
            <body>
                <div class="container">
                    <h1>OTP Verification</h1>
                    <p>Hello,</p>
                    <p>Your One-Time Password (OTP) for password reset is:</p>
                    <div class="otp">' . $otp . '</div>
                    <p>Please use this OTP to complete your password reset process. This code will expire in 10 minutes.</p>
                    <p>If you didn\'t request this OTP, please ignore this email and contact our support team immediately.</p>
                    <div class="footer">
                        <p>This is an automated message. Please do not reply to this email.</p>
                        <p>If you have any questions, please contact our <a href="mailto:' . APP_EMAIL . '">support team</a>.</p>
                    </div>
                </div>
            </body>
            </html>
            ';
        $mailer->sendMail($email, $subject, $body);
        $res = ['status' => 'success', 'message' => 'An OTP has been sent to your email.', 'email' => $email];
    } else {
        $res = ['status' => 'error', 'message' => 'Email not found.', 'errors' => ['email' => 'Email not found.']];
    }
    echo json_encode($res);
    exit;
}

if (isset($_POST['verify_otp'])) {
    $email = filtervar($_POST['email']);
    $otp = filtervar($_POST['otp']);

    $sql = "SELECT * FROM `tbl_customers` WHERE email = :email AND token = :otp";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':otp', $otp);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        echo json_encode(['status' => 'success', 'message' => 'OTP verified.', 'email' => $email]);
        exit;
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid OTP.']);
        exit;
    }
}

if (isset($_POST['set_password'])) {

    $email = filtervar($_POST['email']);
    $new_password = password_hash(filtervar($_POST['new_password']), PASSWORD_DEFAULT);

    $sql = "UPDATE `tbl_customers` SET `password_hash` = :password, `token` = NULL WHERE `email` = :email";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':password', $new_password);
    $stmt->bindParam(':email', $email);

    if ($stmt->execute()) {
        $redirect = 'login';
        $delay = 3000;
        $subject = "Password Change Notification";
        $body = password_changed_email_template();

        if ($mailer->sendMail($email, $subject, $body)) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Password updated successfully and notification email sent.',
                'redirect' => $redirect,
                'delay' => $delay
            ]);
        } else {
            echo json_encode(['status' => 'success', 'message' => 'Password updated successfully, but failed to send notification email.', 'redirect' => $redirect, 'delay' => $delay]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update password.']);
    }
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
                    <p>Forgot your password? No worries, we'll help you reset it.</p>
                </div>

                <!-- Password Reset Form -->
                <div id="resetForm">
                    <form id="forgotPasswordForm" class="form" data-on-success="firstComplete" data-reset="false">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email address</label>
                            <input type="email" class="form-control" id="email" name="email"
                                placeholder="Enter Email Address" required>
                            <span class="invalid-feedback fw-bold"></span>
                        </div>
                        <div class="text-center">
                            <button type="submit" name="reset_password" class="btn btn-primary w-100">Reset
                                Password</button>
                        </div>
                    </form>
                </div>

                <!-- OTP Verification Form -->
                <div id="otpForm" style="display: none;">
                    <form id="otpVerificationForm" class="form" data-on-success="secondComplete" data-reset="false">
                        <div class="mb-3">
                            <label for="otp" class="form-label">OTP</label>
                            <input type="text" class="form-control" id="otp" name="otp" placeholder="Enter OTP"
                                required>
                            <span class="invalid-feedback fw-bold"></span>
                        </div>
                        <input type="hidden" id="otpEmail" name="email">
                        <div class="text-center">
                            <button type="submit" name="verify_otp" class="btn btn-primary w-100">Verify OTP</button>
                        </div>
                    </form>
                </div>

                <!-- New Password Form -->
                <div id="newPasswordForm" style="display: none;">
                    <form id="setPasswordForm" class="form">
                        <input type="hidden" id="newPasswordEmail" name="email">
                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" required>
                            <span class="invalid-feedback fw-bold"></span>
                        </div>
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                                required>
                            <span class="invalid-feedback fw-bold"></span>
                        </div>
                        <div class="text-center">
                            <button type="submit" name="set_password" class="btn btn-primary w-100">Set
                                Password</button>
                        </div>
                    </form>
                </div>

                <div class="mt-3 text-center">
                    <a href="login" class="forgot-password">Back to Login</a>
                </div>
            </div>
        </div>
    </section>
    <?php include_once 'includes/layouts/footer.php' ?>
    <script>
    function firstComplete(data = null) {
        $('[name="reset_password"]').hide();
        $("#forgotPasswordForm").find('input').prop('readonly', true);
        $('#otpEmail').val(data.email);
        $('#otpForm').show();
    }

    function secondComplete(data = null) {
        $('[name="verify_otp"]').hide();
        $("#otpVerificationForm").find('input').prop('readonly', true);
        $('#newPasswordForm').show();
        $('#newPasswordEmail').val(data.email);
    }

    $("#confirm_password").on('input', function() {
        if ($(this).val() !== $("#new_password").val()) {
            $(this).addClass('is-invalid');
            $(this).next().text('Passwords do not match.');
            $('[name="set_password"]').prop('disabled', true);
        } else {
            $(this).removeClass('is-invalid');
            $(this).next().text('');
            $('[name="set_password"]').prop('disabled', false);
        }
    });
    </script>
</body>

</html>