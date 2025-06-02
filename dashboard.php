<?php
session_start();

// Database configuration
$base_dir = __DIR__;
$datacon_path = $base_dir . "/datacon.php";

if (!file_exists($datacon_path)) {
    die("Error: Database configuration file not found at: " . $datacon_path);
}

include_once $datacon_path;

// Check if user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: ../login");
    exit();
}

// Verify database connection
if (!$conn) {
    die("Error: Could not connect to the database!");
}

// Fetch user details
$email = $_SESSION['email'];
$query = "SELECT * FROM user_register_details WHERE email = '$email'";
$result = mysqli_query($conn, $query);

if (!$result) {
    die("Database query error: " . mysqli_error($conn));
}

if (mysqli_num_rows($result) == 1) {
    $user = mysqli_fetch_assoc($result);
    $user_id = $user['table_id'];
} else {
    echo "<script>alert('User not found!'); window.location.href='../login';</script>";
    exit();
}

// Initialize variables
$workout_result = null;
$sessions_result = null;
$current_progress = null;
$messages = null;
$selected_workouts = null;
$progress_history = null;

// Fetch user's fitness details
$fitness_details_query = "SELECT * FROM user_fitness_details WHERE table_id = $user_id";
$fitness_details_result = mysqli_query($conn, $fitness_details_query);

if ($fitness_details_result && mysqli_num_rows($fitness_details_result) > 0) {
    $fitness_details = mysqli_fetch_assoc($fitness_details_result);
    
    $selected_workout_ids = [
        $fitness_details['preffered_workout_plan_1'],
        $fitness_details['preffered_workout_plan_2'],
        $fitness_details['preffered_workout_plan_3']
    ];
    
    $selected_workouts_query = "SELECT * FROM workout_plan WHERE table_id IN (" . implode(',', $selected_workout_ids) . ")";
    $selected_workouts = mysqli_query($conn, $selected_workouts_query);
}

// Fetch messages
$messages_query = "SELECT * FROM trainer_messages WHERE user_id = $user_id ORDER BY message_date DESC LIMIT 5";
$messages_result = mysqli_query($conn, $messages_query);
if ($messages_result && mysqli_num_rows($messages_result) > 0) {
    $messages = mysqli_fetch_all($messages_result, MYSQLI_ASSOC);
}

// Fetch workout sessions
$sessions_query = "SELECT ws.*, wp.workout_name FROM workout_sessions ws
                   JOIN workout_plan wp ON ws.workout_plan_id = wp.table_id
                   WHERE ws.user_id = $user_id
                   ORDER BY ws.start_time DESC LIMIT 3";
$sessions_result = mysqli_query($conn, $sessions_query);

// Fetch progress data
$progress_query = "SELECT * FROM user_progress 
                   WHERE user_id = $user_id
                   ORDER BY date_recorded DESC LIMIT 1";
$progress_result = mysqli_query($conn, $progress_query);
if ($progress_result && mysqli_num_rows($progress_result) > 0) {
    $current_progress = mysqli_fetch_assoc($progress_result);
}

// Fetch progress history
$history_query = "SELECT * FROM user_progress 
                  WHERE user_id = $user_id
                  ORDER BY date_recorded DESC";
$history_result = mysqli_query($conn, $history_query);
if ($history_result && mysqli_num_rows($history_result) > 0) {
    $progress_history = mysqli_fetch_all($history_result, MYSQLI_ASSOC);
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['progress_submit'])) {
        $date = mysqli_real_escape_string($conn, $_POST['progress_date']);
        $weight = (float)$_POST['weight'];
        $body_fat = !empty($_POST['body_fat']) ? (float)$_POST['body_fat'] : NULL;
        $muscle_mass = !empty($_POST['muscle_mass']) ? (float)$_POST['muscle_mass'] : NULL;
        
        $insert_query = "INSERT INTO user_progress 
                        (user_id, date_recorded, weight, body_fat_percentage, muscle_mass) 
                        VALUES ($user_id, '$date', $weight, " . ($body_fat ?: 'NULL') . ", " . ($muscle_mass ?: 'NULL') . ")";
        
        if (mysqli_query($conn, $insert_query)) {
            echo "<script>alert('Progress recorded successfully!'); window.location.href='dashboard.php?section=progress';</script>";
        } else {
            echo "<script>alert('Error recording progress: " . mysqli_error($conn) . "');</script>";
        }
    } else {
        $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
        $last_name = mysqli_real_escape_string($conn, $_POST['last_name']);
        $contact_number = mysqli_real_escape_string($conn, $_POST['contact_number']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);

        $update_query = "UPDATE user_register_details SET 
                         first_name = '$first_name', 
                         last_name = '$last_name', 
                         contact_number = '$contact_number', 
                         email = '$email' 
                         WHERE email = '{$_SESSION['email']}'";

        if (mysqli_query($conn, $update_query)) {
            $_SESSION['email'] = $email;
            echo "<script>alert('Profile updated successfully!'); window.location.href='dashboard.php';</script>";
        } else {
            echo "<script>alert('Error updating profile: " . mysqli_error($conn) . "');</script>";
        }
    }
}

// === ADD OTP LOGIC HERE ===
if (isset($_POST['send_otp'])) {
    $otp = rand(100000, 999999);
    $_SESSION['otp'] = $otp;
    $_SESSION['otp_expiry'] = time() + 600; // 10 minutes expiry

    if (sendOTP($user['email'], $otp)) {
        $otp_message = "OTP sent to your email.";
    } else {
        $otp_message = "Failed to send OTP. Please try again.";
    }
}

if (isset($_POST['verify_otp'])) {
    $entered_otp = $_POST['otp_input'];
    if (isset($_SESSION['otp'], $_SESSION['otp_expiry']) && time() < $_SESSION['otp_expiry']) {
        if ($entered_otp == $_SESSION['otp']) {
            $otp_message = "OTP verified successfully!";
            unset($_SESSION['otp'], $_SESSION['otp_expiry']);
            // You can add any action here after successful verification
        } else {
            $otp_message = "Invalid OTP!";
        }
    } else {
        $otp_message = "OTP expired. Please request a new one.";
    }
}
// Updated workout progress function with completion tracking
function getWorkoutProgressPercentage($conn, $user_id, $workout_plan_id) {
    $progress_query = "SELECT * FROM user_workout_progress 
                      WHERE user_id = $user_id AND workout_plan_id = $workout_plan_id";
    $progress_result = mysqli_query($conn, $progress_query);
    
    if ($progress_result && mysqli_num_rows($progress_result) > 0) {
        $progress = mysqli_fetch_assoc($progress_result);
        
        $weeks_query = "SELECT COUNT(*) as total_weeks FROM workout_plan_details 
                       WHERE workout_plan_id = $workout_plan_id";
        $weeks_result = mysqli_query($conn, $weeks_query);
        $weeks_data = mysqli_fetch_assoc($weeks_result);
        $total_weeks = $weeks_data['total_weeks'];
        
        if ($total_weeks > 0) {
            $percentage = round(($progress['current_week'] - 1) / $total_weeks * 100);
            return [
                'percentage' => $percentage,
                'completed' => ($percentage >= 100)
            ];
        }
    }
    
    return [
        'percentage' => 0,
        'completed' => false
    ];
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/vendor/autoload.php'; // Adjust path if needed

function sendOTP($toEmail, $otp) {
    $mail = new PHPMailer(true);
    try {
        // SMTP configuration
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Change to your SMTP server
        $mail->SMTPAuth = true;
        $mail->Username = 'juliusonyawli@gmail.com'; // Your SMTP username
        $mail->Password = 'kcbr kred ipva ywyl'; // Your SMTP password or app password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('your_email@gmail.com', 'EliteFit OTP');
        $mail->addAddress($toEmail);

        $mail->isHTML(true);
        $mail->Subject = 'Your EliteFit OTP Code';
        $mail->Body    = "<h2>Your OTP is: <b>$otp</b></h2><p>This code is valid for 10 minutes.</p>";

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Fitness Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background-color: #f5f9ff;
            display: flex;
            min-height: 100vh;
            color: #333;
        }

        .sidebar {
            width: 250px;
            background: #1a3a8f;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            padding: 20px 0;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            color: white;
            overflow-y: auto;
        }

        .sidebar h2 {
            color: white;
            text-align: center;
            margin-bottom: 30px;
            padding: 0 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            justify-content: center;
        }

        .sidebar h2 i {
            font-size: 28px;
            color: #4fc3f7;
        }

        .sidebar ul {
            list-style: none;
        }

        .sidebar ul li a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            display: flex;
            align-items: center;
            padding: 15px 25px;
            margin: 5px 0;
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
        }

        .sidebar ul li a:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border-left: 4px solid #4fc3f7;
        }

        .sidebar ul li a.active {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border-left: 4px solid #4fc3f7;
        }

        .sidebar ul li a i {
            font-size: 20px;
            margin-right: 15px;
            width: 24px;
            text-align: center;
        }

        .messages-container {
            padding: 15px;
            margin-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .messages-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 15px;
            color: white;
        }

        .messages-header h3 {
            font-size: 16px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .message-item {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 10px;
            transition: all 0.3s ease;
        }

        .message-item:hover {
            background: rgba(255, 255, 255, 0.15);
        }

        .message-title {
            font-weight: 600;
            margin-bottom: 5px;
            color: white;
        }

        .message-preview {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.7);
            display: -webkit-box;
            -webkit-line-clamp: 2;
            line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .message-date {
            font-size: 11px;
            color: rgba(255, 255, 255, 0.5);
            margin-top: 5px;
        }

        .logout-btn {
            position: absolute;
            bottom: 20px;
            left: 0;
            right: 0;
            margin: 0 25px;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border: none;
            padding: 12px;
            border-radius: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .logout-btn:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .dashboard-container {
            flex: 1;
            margin-left: 250px;
            padding: 30px;
        }

        .dashboard-container h2 {
            color: #1a3a8f;
            margin-bottom: 10px;
            font-size: 28px;
        }

        .dashboard-container p {
            color: #666;
            margin-bottom: 30px;
        }

        .home-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
            margin-top: 20px;
        }

        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            padding: 25px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }

        .card-header i {
            font-size: 40px;
            color: #1a3a8f;
            margin-right: 15px;
        }

        .card-header h3 {
            color: #1a3a8f;
            font-size: 18px;
        }

        .stat {
            display: flex;
            align-items: center;
            margin: 15px 0;
        }

        .stat i {
            font-size: 24px;
            color: #4fc3f7;
            margin-right: 15px;
            width: 30px;
        }

        .session-card {
            background: #f9fbfe;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            border-left: 4px solid #4fc3f7;
        }

        .session-card h4 {
            color: #1a3a8f;
            margin-bottom: 5px;
        }

        .session-card p {
            color: #666;
            font-size: 14px;
            margin: 3px 0;
        }

        .action-btn {
            background: #1a3a8f;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            margin-top: 15px;
            transition: all 0.3s ease;
            display: inline-block;
        }

        .action-btn:hover {
            background: #142e6f;
            transform: translateY(-2px);
        }

        .workout-plans {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .workout-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            position: relative;
        }

        .workout-card.locked {
            opacity: 0.6;
            filter: grayscale(80%);
        }

        .workout-card h3 {
            color: #1a3a8f;
            margin-bottom: 15px;
        }

        .progress-container {
            background: #e9f0ff;
            border-radius: 10px;
            height: 10px;
            margin: 15px 0;
            overflow: hidden;
            position: relative;
        }

        .progress-bar {
            background: linear-gradient(90deg, #4fc3f7, #1a3a8f);
            height: 100%;
            border-radius: 10px;
            transition: width 0.5s ease;
        }

        .progress-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 12px;
            font-weight: bold;
            color: #1a3a8f;
        }

        .workout-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .start-btn {
            background: #1a3a8f;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
            flex: 1;
            text-align: center;
        }

        .start-btn.disabled {
            background: #cccccc !important;
            cursor: not-allowed;
        }

        .start-btn:hover:not(.disabled) {
            background: #142e6f;
            transform: translateY(-2px);
        }

        .details-btn {
            background: transparent;
            border: 1px solid #1a3a8f;
            color: #1a3a8f;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
            flex: 1;
            text-align: center;
        }

        .details-btn:hover {
            background: #f0f5ff;
            transform: translateY(-2px);
        }

        .lock-icon {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 20px;
            color: #ff6b6b;
        }

        .locked-message {
            color: #ff6b6b;
            margin-top: 10px;
            font-size: 14px;
            text-align: center;
        }

        .profile-form {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            margin-top: 20px;
        }

        .profile-form label {
            display: block;
            margin: 15px 0 5px;
            color: #1a3a8f;
            font-weight: 500;
        }

        .profile-form input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .profile-form input:focus {
            border-color: #4fc3f7;
            box-shadow: 0 0 0 3px rgba(79, 195, 247, 0.2);
            outline: none;
        }

        .profile-picture {
            margin-top: 30px;
        }

        .profile-picture h3 {
            color: #1a3a8f;
            margin-bottom: 15px;
        }

        .progress-history {
            margin-top: 30px;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        .progress-history table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .progress-history th {
            background-color: #1a3a8f;
            color: white;
            padding: 10px;
            text-align: left;
        }

        .progress-history td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }

        .progress-history tr:hover {
            background-color: #f5f9ff;
        }

        .hidden {
            display: none;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 80px;
            }
            .sidebar h2 span {
                display: none;
            }
            .sidebar ul li a span {
                display: none;
            }
            .sidebar ul li a i {
                margin-right: 0;
            }
            .dashboard-container {
                margin-left: 80px;
            }
            .logout-btn span {
                display: none;
            }
            .messages-container {
                display: none;
            }
        }
    </style>
</head>
<body>
<div class="sidebar">
        <h2><i class="fas fa-dumbbell"></i><span>EliteFit</span></h2>
        <ul>
            <li><a href="#" class="active" data-tab="home"><i class="fas fa-home"></i><span>Dashboard</span></a></li>
            <li><a href="#" data-tab="workouts"><i class="fas fa-dumbbell"></i><span>My Workouts</span></a></li>
            <li><a href="#" data-tab="progress"><i class="fas fa-chart-line"></i><span>Progress</span></a></li>
            <li><a href="#" data-tab="profile"><i class="fas fa-user"></i><span>Profile</span></a></li>
        </ul>
        
        <div class="messages-container">
            <div class="messages-header">
                <h3><i class="fas fa-envelope"></i> Notifications</h3>
            </div>
            <?php if ($messages && count($messages) > 0): ?>
                <?php foreach ($messages as $message): ?>
                    <div class="message-item">
                        <div class="message-title"><?php echo htmlspecialchars($message['message_title']); ?></div>
                        <div class="message-preview"><?php echo htmlspecialchars(substr($message['message_content'], 0, 60)); ?>...</div>
                        <div class="message-date"><?php echo date('M j, Y', strtotime($message['message_date'])); ?></div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="color: rgba(255,255,255,0.7); font-size: 14px;">No messages yet</p>
            <?php endif; ?>
        </div>
        
        <a href="logout.php" class="logout-btn">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </div>

    <div class="dashboard-container">
        <!-- Dashboard Home Section -->
        <div id="home" class="dashboard-section">
            <h2>Welcome back, <?php echo htmlspecialchars($user['first_name']); ?>!</h2>
            <p>"The only bad workout is the one you didn't do."</p>

            <div class="home-cards">
                <!-- Current Progress Card -->
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-chart-line"></i>
                        <h3>My Progress</h3>
                    </div>
                    <div class="card-body">
                        <?php if ($current_progress): ?>
                            <div class="stat">
                                <i class="fas fa-weight"></i>
                                <span>Weight: <?php echo $current_progress['weight']; ?> kg</span>
                            </div>
                            <div class="stat">
                                <i class="fas fa-heart"></i>
                                <span>Body Fat: <?php echo $current_progress['body_fat_percentage'] ?? 'N/A'; ?>%</span>
                            </div>
                        <?php else: ?>
                            <p>No progress data recorded yet.</p>
                        <?php endif; ?>
                        <button class="action-btn" onclick="showSection('progress')">View Progress</button>
                    </div>
                </div>

                <!-- Recent Workouts Card -->
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-history"></i>
                        <h3>Recent Workouts</h3>
                    </div>
                    <div class="card-body">
                        <?php if ($sessions_result && mysqli_num_rows($sessions_result) > 0): ?>
                            <?php while ($session = mysqli_fetch_assoc($sessions_result)): ?>
                                <div class="session-card">
                                    <h4><?php echo htmlspecialchars($session['workout_name']); ?></h4>
                                    <p><?php echo date('M j, Y', strtotime($session['start_time'])); ?></p>
                                    <p>Duration: <?php echo $session['duration_minutes'] ?? 'N/A'; ?> mins</p>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p>No recent workouts found.</p>
                        <?php endif; ?>
                        <button class="action-btn" onclick="showSection('workouts')">View All Workouts</button>
                    </div>
                </div>

                <!-- Quick Actions Card -->
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-bolt"></i>
                        <h3>Quick Actions</h3>
                    </div>
                    <div class="card-body">
                        <button class="action-btn" onclick="startWorkout()">Start New Workout</button>
                        <button class="action-btn" onclick="showSection('progress')">Log Progress</button>
                        <button class="action-btn" onclick="showSection('workouts')">View Workout Plans</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Workouts Section with Progression Logic -->
        <div id="workouts" class="dashboard-section hidden">
            <h2>My Workout Plans</h2>
            
            <div class="workout-plans">
                <?php if ($selected_workouts && mysqli_num_rows($selected_workouts) > 0): ?>
                    <?php 
                    mysqli_data_seek($selected_workouts, 0);
                    $previous_completed = true; // First workout is always available
                    $workout_number = 1;
                    ?>
                    <?php while ($workout = mysqli_fetch_assoc($selected_workouts)): ?>
                        <?php
                        $progress_data = getWorkoutProgressPercentage($conn, $user_id, $workout['table_id']);
                        $is_available = ($previous_completed || $progress_data['percentage'] > 0);
                        $previous_completed = $progress_data['completed'];
                        ?>
                        <div class="workout-card <?= !$is_available ? 'locked' : '' ?>">
                            <?php if (!$is_available): ?>
                                <i class="fas fa-lock lock-icon"></i>
                            <?php endif; ?>
                            <h3><?php echo htmlspecialchars($workout['workout_name']); ?></h3>
                            <p>Workout <?= $workout_number++ ?></p>
                            <div class="progress-container">
                                <div class="progress-bar" style="width: <?= $progress_data['percentage'] ?>%"></div>
                                <div class="progress-text"><?= $progress_data['percentage'] ?>%</div>
                            </div>
                            <div class="workout-actions">
                                <button class="start-btn <?= !$is_available ? 'disabled' : '' ?>" 
                                    <?= !$is_available ? 'disabled' : '' ?>
                                    onclick="<?= $is_available ? "window.location.href='workout_details.php?id={$workout['table_id']}'" : "alert('Please complete the previous workout first!')" ?>">
                                    <i class="fas fa-play"></i> Start
                                </button>
                                <button class="details-btn" onclick="window.location.href='workout_details.php?id=<?= $workout['table_id'] ?>'">
                                    <i class="fas fa-info-circle"></i> Details
                                </button>
                            </div>
                            <?php if (!$is_available): ?>
                                <p class="locked-message">Complete previous workout to unlock</p>
                            <?php endif; ?>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No workout plans assigned yet. Please complete your fitness profile.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Progress Section -->
        <div id="progress" class="dashboard-section hidden">
            <h2>My Fitness Progress</h2>
            
            <div class="progress-form">
                <h3>Record New Progress</h3>
                <form id="progressForm" method="POST" action="">
                    <input type="hidden" name="progress_submit" value="1">
                    
                    <label for="progress_date">Date:</label>
                    <input type="date" id="progress_date" name="progress_date" required value="<?php echo date('Y-m-d'); ?>">
                    
                    <label for="weight">Weight (kg):</label>
                    <input type="number" id="weight" name="weight" step="0.1" required 
                           value="<?php echo $current_progress['weight'] ?? ''; ?>">
                    
                    <label for="body_fat">Body Fat Percentage:</label>
                    <input type="number" id="body_fat" name="body_fat" step="0.1"
                           value="<?php echo $current_progress['body_fat_percentage'] ?? ''; ?>">
                    
                    <label for="muscle_mass">Muscle Mass (kg):</label>
                    <input type="number" id="muscle_mass" name="muscle_mass" step="0.1"
                           value="<?php echo $current_progress['muscle_mass'] ?? ''; ?>">
                    
                    <button type="submit" class="action-btn">Save Progress</button>
                </form>
                
                <div class="progress-history">
                    <h3>Progress History</h3>
                    <?php if ($progress_history && count($progress_history) > 0): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Weight (kg)</th>
                                    <th>Body Fat (%)</th>
                                    <th>Muscle Mass (kg)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($progress_history as $progress): ?>
                                    <tr>
                                        <td><?php echo date('M j, Y', strtotime($progress['date_recorded'])); ?></td>
                                        <td><?php echo $progress['weight']; ?></td>
                                        <td><?php echo $progress['body_fat_percentage'] ?? 'N/A'; ?></td>
                                        <td><?php echo $progress['muscle_mass'] ?? 'N/A'; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>No progress history found. Start by recording your first progress entry.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Profile Section -->
        <div id="profile" class="dashboard-section hidden">
            <h2>My Profile</h2>
            <div class="profile-form">
                <!-- ADD OTP FORM HERE -->
                <?php if (isset($otp_message)) echo "<p style='color: #1a3a8f;'>$otp_message</p>"; ?>
                <form method="POST" action="">
                    <button type="submit" name="send_otp" class="action-btn">Send OTP to Email</button>
                </form>
                <form method="POST" action="" style="margin-top:10px;">
                    <input type="text" name="otp_input" placeholder="Enter OTP" required>
                    <button type="submit" name="verify_otp" class="action-btn">Verify OTP</button>
                </form>
                <!-- END OTP FORM -->

                <form method="POST" action="">
                    <label for="first_name">First Name:</label>
                    <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>

                    <label for="last_name">Last Name:</label>
                    <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>

                    <label for="contact_number">Contact Number:</label>
                    <input type="text" id="contact_number" name="contact_number" value="<?php echo htmlspecialchars($user['contact_number']); ?>" required>

                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>

                    <button type="submit" class="action-btn">Save Changes</button>
                </form>
                
                <div class="profile-picture">
                    <h3>Profile Picture</h3>
                    <?php if (!empty($user['profile_picture'])): ?>
                        <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile Picture" style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover;">
                    <?php else: ?>
                        <div style="width: 150px; height: 150px; background: #ccc; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-user" style="font-size: 50px; color: #666;"></i>
                        </div>
                    <?php endif; ?>
                    <form method="POST" action="update_picture.php" enctype="multipart/form-data" style="margin-top: 15px;">
                        <input type="file" name="new_picture" accept="image/*" required>
                        <button type="submit" class="action-btn">Update Picture</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Highlight active tab and show corresponding section
        const sidebarLinks = document.querySelectorAll('.sidebar ul li a');
        const sections = document.querySelectorAll('.dashboard-container > div');

        sidebarLinks.forEach(link => {
            link.addEventListener('click', function() {
                // Remove active class from all links
                sidebarLinks.forEach(l => l.classList.remove('active'));
                // Add active class to the clicked link
                this.classList.add('active');

                // Hide all sections
                sections.forEach(section => section.classList.add('hidden'));
                // Show the corresponding section
                const target = this.getAttribute('data-tab');
                document.getElementById(target).classList.remove('hidden');
                
                // Update URL without reloading
                history.pushState(null, null, `?section=${target}`);
            });
        });

        // Show section based on URL parameter
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const section = urlParams.get('section');
            if (section) {
                showSection(section);
            } else {
                // Show home section by default
                document.getElementById('home').classList.remove('hidden');
            }
        });

        // Additional functions for the user dashboard
        function startWorkout() {
            // Implementation for starting a new workout
            showSection('workouts');
            alert('Select a workout plan to begin');
        }
        
        function showSection(sectionId) {
            // Hide all sections
            document.querySelectorAll('.dashboard-section').forEach(section => {
                section.classList.add('hidden');
            });
            
            // Show the selected section
            document.getElementById(sectionId).classList.remove('hidden');
            
            // Update active tab in sidebar
            document.querySelectorAll('.sidebar ul li a').forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('data-tab') === sectionId) {
                    link.classList.add('active');
                }
            });
        }
    </script>
</body>
</html>