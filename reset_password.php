<?php
session_start();
require_once 'datacon.php';

$error = '';
$success = '';

// Check if email is in session
if (!isset($_SESSION['reset_email'])) {
    header('Location: forgot_password.php');
    exit();
}

$email = $_SESSION['reset_email'];

// Verify OTP and update password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['otp'], $_POST['new_password'], $_POST['confirm_password'])) {
    $otp = trim($_POST['otp']);
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate passwords match
    if ($new_password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif (strlen($new_password) < 8) {
        $error = 'Password must be at least 8 characters long.';
    } else {
        // Verify OTP and check expiry
        $current_time = date('Y-m-d H:i:s');
        $stmt = $conn->prepare("SELECT table_id FROM user_register_details WHERE email = ? AND reset_otp = ? AND reset_otp_expiry > ?");
        $stmt->bind_param("sss", $email, $otp, $current_time);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Update password in user_login_details
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update = $conn->prepare("UPDATE user_login_details SET user_password = ? WHERE table_id = ?");
            $update->bind_param("si", $hashed_password, $user['table_id']);
            
            if ($update->execute()) {
                // Clear reset OTP and expiry
                $clear_otp = $conn->prepare("UPDATE user_register_details SET reset_otp = NULL, reset_otp_expiry = NULL WHERE email = ?");
                $clear_otp->bind_param("s", $email);
                $clear_otp->execute();
                
                // Clear session
                unset($_SESSION['reset_email']);
                
                $_SESSION['success'] = 'Your password has been reset successfully. You can now login with your new password.';
                header('Location: login/index.php');
                exit();
            } else {
                $error = 'Error updating password. Please try again.';
            }
        } else {
            $error = 'Invalid or expired verification code.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - EliteFit</title>
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

        .container {
            width: 100%;
            max-width: 500px;
            background: rgba(255, 255, 255, 0.95);
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0px 10px 30px rgba(0, 0, 0, 0.2);
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

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            margin-bottom: 10px;
        }

        .btn {
            width: 100%;
            padding: 12px;
            background: #1e3c72;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
            margin-top: 10px;
        }

        .btn:hover {
            background: #2a5298;
        }

        .back-to-login {
            text-align: center;
            margin-top: 20px;
        }

        .back-to-login a {
            color: #1e3c72;
            text-decoration: none;
            font-weight: 500;
        }

        .back-to-login a:hover {
            text-decoration: underline;
        }

        .alert {
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
        }

        .alert-error {
            background-color: #ffebee;
            color: #c62828;
            border: 1px solid #ffcdd2;
        }

        .password-requirements {
            font-size: 14px;
            color: #666;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <i class="fas fa-dumbbell"></i>
            <span>EliteFit</span>
        </div>
        
        <h2>Reset Password</h2>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <p style="text-align: center; margin-bottom: 20px;">Enter the verification code sent to your email and create a new password.</p>
        
        <form method="POST" action="reset_password.php">
            <div class="form-group">
                <label for="otp">Verification Code</label>
                <input type="text" id="otp" name="otp" required placeholder="Enter verification code" maxlength="6" pattern="\d{6}" title="Please enter a 6-digit code">
            </div>
            
            <div class="form-group">
                <label for="new_password">New Password</label>
                <input type="password" id="new_password" name="new_password" required minlength="8" placeholder="Enter new password">
                <div class="password-requirements">At least 8 characters long</div>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm New Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required minlength="8" placeholder="Confirm new password">
            </div>
            
            <button type="submit" class="btn">Reset Password</button>
        </form>
        
        <div class="back-to-login">
            <a href="login/index.php">Back to Login</a>
        </div>
    </div>
    
    <script>
        // Auto-advance OTP input
        document.getElementById('otp').addEventListener('input', function(e) {
            // Allow only numbers
            this.value = this.value.replace(/[^0-9]/g, '');
            
            // Auto-focus next input (if implementing multi-input OTP)
            if (this.value.length === 6) {
                document.getElementById('new_password').focus();
            }
        });
        
        // Focus on OTP input when page loads
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('otp').focus();
        });
    </script>
</body>
</html>
