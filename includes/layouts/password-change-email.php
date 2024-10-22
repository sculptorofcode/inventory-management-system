<?php include_once '../config/config.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Updated Successfully</title>
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

    .icon {
        text-align: center;
        margin-bottom: 20px;
    }

    .message {
        font-size: 18px;
        color: #27ae60;
        text-align: center;
        margin: 30px 0;
    }

    .button {
        display: block;
        width: 200px;
        margin: 30px auto;
        padding: 15px 25px;
        background-color: #3498db;
        color: white;
        text-align: center;
        text-decoration: none;
        border-radius: 5px;
        font-weight: bold;
        transition: background-color 0.3s ease;
    }

    .button:hover {
        background-color: #2980b9;
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
        <h1>Password Updated</h1>
        <div class="icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none"
                stroke="#27ae60" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
        </div>
        <div class="message">
            <p>Your password has been updated successfully.</p>
        </div>
        <p>Your account security is important to us. Here are some recommendations:</p>
        <ul>
            <li>Use a unique password for each of your online accounts.</li>
            <li>Enable two-factor authentication for added security.</li>
            <li>Regularly update your password to maintain account safety.</li>
        </ul>
        <a href="<?= LOGIN_URL ?>" class="button">Go to My Account</a>
        <div class="footer">
            <p>This is an automated message. Please do not reply to this email.</p>
            <p>If you didn't make this change or if you believe an unauthorized person has accessed your account,
                please
                contact our <a href="mailto:<?= APP_EMAIL ?>">support team</a> immediately.</p>
        </div>
    </div>
</body>

</html>