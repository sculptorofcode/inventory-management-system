<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTP Verification</title>
    <style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
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
        <div class="otp"><?= $otp ?></div>
        <p>Please use this OTP to complete your password reset process. This code will expire in 10 minutes.</p>
        <p>If you didn\'t request this OTP, please ignore this email and contact our support team immediately.</p>
        <div class="footer">
            <p>This is an automated message. Please do not reply to this email.</p>
            <p>If you have any questions, please contact our <a href="mailto:<?= APP_EMAIL ?>">support team</a>.</p>
        </div>
    </div>
</body>

</html>