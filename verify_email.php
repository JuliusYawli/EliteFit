<?php
session_start();
require_once 'datacon.php';
require_once 'includes/email_functions.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_otp'])) {
    $email = $_SESSION['verification_email'] ?? '';
    $otp = trim($_POST['otp']);
    
    if (empty($email) || empty($otp)) {
        $error = 'Invalid request. Please try registering again.';
    } else {
        // Check OTP in database
        $stmt = $conn->prepare("SELECT table_id, verification_otp, otp_expiry FROM user_register_details WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            $current_time = date('Y-m-d H:i:s');
            
            if ($user['verification_otp'] === $otp && $user['otp_expiry'] > $current_time) {
                // OTP is valid and not expired
                $update = $conn->prepare("UPDATE user_register_details SET is_verified = 1, verification_otp = NULL, otp_expiry = NULL WHERE email = ?");
                $update->bind_param("s", $email);
                
                if ($update->execute()) {
                    // Check if login credentials already exist
                    $check = $conn->prepare("SELECT table_id FROM user_login_details WHERE table_id = ?");
                    $check->bind_param("i", $user['table_id']);
                    $check->execute();
                    
                    if ($check->get_result()->num_rows === 0) {
                        // Only create login credentials if they don't exist
                        $password = $_SESSION['temp_password'] ?? '';
                        $hashed_password = hash('sha256', $password);
                        
                        $insert = $conn->prepare("INSERT INTO user_login_details (username, user_password, table_id) VALUES (?, ?, ?)");
                        $insert->bind_param("ssi", $email, $hashed_password, $user['table_id']);
                        
                        if (!$insert->execute()) {
                            error_log("Error creating login credentials: " . $conn->error);
                            $error = 'Error creating your login credentials. Please contact support.';
                            $update->close();
                            $check->close();
                            header('Location: verify_email.php');
                            exit();
                        }
                        $insert->close();
                    }
                    $check->close();
                    
                    // Clear session variables
                    unset($_SESSION['verification_email']);
                    
                    // Set success message in session before redirect
                    $_SESSION['success'] = 'Email verified successfully! You can now login with your credentials.';
                    
                    // Clear the temp password from session after hashing
                    unset($_SESSION['temp_password']);
                    
                    // Ensure headers are sent before any output
                    header('Location: login/index.php');
                    exit();
                } else {
                    $error = 'Error updating verification status. Please try again.';
                }
            } else {
                $error = 'Invalid or expired OTP. Please try again.';
            }
        } else {
            $error = 'User not found. Please register again.';
        }
    }
} elseif (isset($_GET['resend']) && isset($_SESSION['verification_email'])) {
    // Resend OPP
    $email = $_SESSION['verification_email'];
    $otp = generateOTP();
    $expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));
    
    $stmt = $conn->prepare("UPDATE user_register_details SET verification_otp = ?, otp_expiry = ? WHERE email = ?");
    $stmt->bind_param("sss", $otp, $expiry, $email);
    
    if ($stmt->execute()) {
        // Get user's name for email
        $userStmt = $conn->prepare("SELECT first_name FROM user_register_details WHERE email = ?");
        $userStmt->bind_param("s", $email);
        $userStmt->execute();
        $user = $userStmt->get_result()->fetch_assoc();
        
        if (sendVerificationEmail($email, $otp, $user['first_name'])) {
            $message = 'A new OTP has been sent to your email.';
        } else {
            $error = 'Failed to send OTP. Please try again.';
        }
    } else {
        $error = 'Error generating new OTP. Please try again.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Email - EliteFit</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(to right, #1e3c72, #2a5298);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .verification-container {
            background: rgba(255, 255, 255, 0.95);
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            max-width: 500px;
            width: 100%;
        }

        .logo {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 30px;
            color: #1e3c72;
        }

        .logo i {
            font-size: 32px;
            margin-right: 10px;
        }

        .logo span {
            font-size: 24px;
            font-weight: 700;
        }

        h2 {
            text-align: center;
            color: #1e3c72;
            margin-bottom: 20px;
        }

        .message {
            text-align: center;
            margin-bottom: 20px;
            padding: 10px;
            border-radius: 5px;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
        }

        input[type="text"] {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.3s;
        }

        input[type="text"]:focus {
            outline: none;
            border-color: #4fc3f7;
            box-shadow: 0 0 0 2px rgba(79, 195, 247, 0.25);
        }

        .otp-input {
            letter-spacing: 5px;
            font-size: 24px;
            text-align: center;
            font-weight: bold;
        }

        .btn {
            display: block;
            width: 100%;
            padding: 12px;
            background-color: #1e3c72;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .btn:hover {
            background-color: #2a5298;
        }

        .resend-link {
            text-align: center;
            margin-top: 20px;
        }

        .resend-link a {
            color: #4fc3f7;
            text-decoration: none;
            font-weight: 500;
        }

        .resend-link a:hover {
            text-decoration: underline;
        }

        @media (max-width: 576px) {
            .verification-container {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="verification-container">
        <div class="logo">
            <i class="fas fa-dumbbell"></i>
            <span>EliteFit</span>
        </div>
        
        <h2>Verify Your Email</h2>
        
        <?php if (!empty($message)): ?>
            <div class="message success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
            <div class="message error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <p style="text-align: center; margin-bottom: 20px;">
            We've sent a 6-digit verification code to <strong><?php echo isset($_SESSION['verification_email']) ? htmlspecialchars($_SESSION['verification_email']) : 'your email'; ?></strong>.
            Please enter it below to verify your email address.
        </p>
        
        <form action="verify_email.php" method="POST">
            <div class="form-group">
                <label for="otp">Verification Code</label>
                <input type="text" id="otp" name="otp" class="otp-input" maxlength="6" required autofocus>
            </div>
            
            <button type="submit" name="verify_otp" class="btn">Verify Email</button>
        </form>
        
        <div class="resend-link">
            Didn't receive the code? <a href="?resend=1">Resend OTP</a>
        </div>
    </div>
    
    <script>
        // Allow only numbers in OTP input
        document.getElementById('otp').addEventListener('input', function(e) {
            // Allow only numbers
            this.value = this.value.replace(/[^0-9]/g, '');
        });
        
        // Focus on input when page loads
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('otp').focus();
        });
        
        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const otpInput = document.getElementById('otp');
            if (otpInput.value.length !== 6) {
                e.preventDefault();
                alert('Please enter a 6-digit verification code');
                otpInput.focus();
            }
        });
    </script>
</body>
</html>
