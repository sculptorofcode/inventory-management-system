<?php
if(!isset($title)) {
    $title = 'Page Not Found';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?> - <?= APP_NAME ?></title>
    <style>
        .error-container {
            max-width: 800px;
            margin: 50px auto;
            text-align: center;
            font-family: 'Arial', sans-serif;
            padding: 20px;
        }
        
        .error-heading {
            font-size: 2.5rem;
            color: #2c3e50;
            margin-bottom: 20px;
        }
        
        .error-image {
            max-width: 400px;
            margin: 30px auto;
        }
        
        .error-message {
            font-size: 1.2rem;
            color: #7f8c8d;
            margin-bottom: 30px;
        }
        
        .return-button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        
        .return-button:hover {
            background-color: #2980b9;
        }

        .warehouse-icon {
            font-size: 100px;
            color: #34495e;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="warehouse-icon">
            📦
        </div>
        <h1 class="error-heading">Uh-oh! This item is out of stock... or maybe just lost?</h1>
        <p class="error-message">
            Looks like this page got misplaced somewhere in our virtual warehouse.<br>
            Our inventory specialists are searching through all the shelves, but maybe you'd like to head back to safety?
        </p>
        <video src="assets/videos/0_Office_Workspace_720x720.mp4" autoplay loop muted style="max-width: 100%;"></video>
        <br>
        <br>
        <div>
            <a href="dashboard.php" class="return-button">
                <span style="margin-right: 8px;">🏠</span> Return to Dashboard
            </a>
        </div>
    </div>
</body>
</html>
