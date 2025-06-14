<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to <?= APP_NAME ?></title>
    <style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f4f4f4;
        margin: 0;
        padding: 0;
    }

    .container {
        width: 100%;
        max-width: 600px;
        margin: auto;
        background: white;
        padding: 20px;
        border-radius: 5px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    .header {
        background-color: #00A859;
        color: white;
        padding: 15px;
        text-align: center;
        border-radius: 5px 5px 0 0;
    }

    .content {
        margin: 20px 0;
        line-height: 1.6;
    }

    .footer {
        text-align: center;
        font-size: 12px;
        color: #777;
        margin-top: 20px;
    }

    a {
        color: #00A859;
        text-decoration: none;
    }

    a:hover {
        text-decoration: underline;
    }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>Welcome to <?= APP_NAME ?>!</h1>
        </div>
        <div class="content">
            <p>Dear <?= $full_name ?>,</p>
            <p>Thank you for creating an account with us! We're excited to have you as part of our community.</p>

            <h3>Your Account Details:</h3>
            <p><strong>Email:</strong> <?= $email ?></p>
            <p><strong>Account Creation Date:</strong> <?= $creation_date ?></p>

            <h3>Get Started:</h3>
            <p>You can now log in to your account using the link below:</p>
            <p><a href="<?= SITE_URL ?>/login">Login to Your Account</a></p>

            <h3>Need Help?</h3>
            <p>If you have any questions, feel free to reach out to our support team at <a
                    href="mailto:<?= APP_EMAIL ?>"><?= APP_EMAIL ?></a>.</p>

            <p>Once again, welcome to <strong><?= APP_EMAIL ?></strong>! We look forward to serving you.</p>
        </div>
        <div class="footer">
            <p>Best regards,</p>
            <p>The <?= APP_EMAIL ?> Team</p>
        </div>
    </div>
</body>

</html>