<?php
// logout.php
session_start();
session_unset();
session_destroy();

// Determine the base URL dynamically
$base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
$project_path = str_replace($_SERVER['DOCUMENT_ROOT'], '', str_replace('\\', '/', realpath(__DIR__)));
$redirect_url = rtrim($base_url . $project_path, '/') . '/login/index.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logging Out - EliteFit</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #1a3a8f, #4fc3f7);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            color: white;
            overflow: hidden;
        }

        .logout-container {
            text-align: center;
            max-width: 500px;
            padding: 40px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            animation: fadeIn 0.8s ease-out;
            position: relative;
            overflow: hidden;
        }

        .logout-container::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 70%);
            animation: pulse 6s infinite linear;
            z-index: -1;
        }

        .logout-icon {
            font-size: 80px;
            margin-bottom: 20px;
            color: white;
            animation: bounce 2s infinite;
        }

        h1 {
            font-size: 28px;
            margin-bottom: 15px;
            font-weight: 600;
        }

        p {
            font-size: 16px;
            margin-bottom: 30px;
            opacity: 0.9;
        }

        .countdown {
            font-size: 24px;
            font-weight: bold;
            margin: 20px 0;
            color: #4fc3f7;
        }

        .progress-bar {
            height: 6px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 3px;
            margin: 20px 0;
            overflow: hidden;
        }

        .progress {
            height: 100%;
            background: white;
            width: 0;
            border-radius: 3px;
            transition: width 0.3s ease;
        }

        .btn {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 30px;
            background: white;
            color: #1a3a8f;
            border: none;
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-15px); }
        }

        @keyframes pulse {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="logout-container">
        <div class="logout-icon">
            <i class="fas fa-sign-out-alt"></i>
        </div>
        <h1>You're being logged out</h1>
        <p>Thank you for using EliteFit. You'll be redirected to the login page shortly.</p>
        
        <div class="countdown" id="countdown">5</div>
        <div class="progress-bar">
            <div class="progress" id="progress"></div>
        </div>
        
        <a href="<?php echo $redirect_url; ?>" class="btn">Return to Login Now</a>
    </div>

    <script>
        // Countdown and redirect
        let seconds = 5;
        const countdownElement = document.getElementById('countdown');
        const progressElement = document.getElementById('progress');
        const redirectUrl = "<?php echo $redirect_url; ?>";
        
        const countdownInterval = setInterval(() => {
            seconds--;
            countdownElement.textContent = seconds;
            progressElement.style.width = `${(5 - seconds) * 20}%`;
            
            if (seconds <= 0) {
                clearInterval(countdownInterval);
                window.location.href = redirectUrl;
            }
        }, 1000);
    </script>
</body>
</html>