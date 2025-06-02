<?php
session_start();
include_once 'datacon.php';

if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

// Function to get workout details with proper error handling
function getWorkoutDetails($conn, $workout_plan_id) {
    $query = "SELECT DISTINCT * FROM workout_plan_details 
              WHERE workout_plan_id = $workout_plan_id
              ORDER BY week_number";
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        die("Database error: " . mysqli_error($conn));
    }
    
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

// Function to get user workout progress with proper error handling
function getUserWorkoutProgress($conn, $user_id, $workout_plan_id) {
    $query = "SELECT * FROM user_workout_progress 
              WHERE user_id = $user_id AND workout_plan_id = $workout_plan_id";
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        die("Database error: " . mysqli_error($conn));
    }
    
    return mysqli_fetch_assoc($result);
}

// Function to start a workout session with proper validation
function startWorkoutSession($conn, $user_id, $workout_plan_id, $week_number) {
    $start_time = date('Y-m-d H:i:s');
    $query = "INSERT INTO workout_sessions 
              (user_id, workout_plan_id, week_number, start_time)
              VALUES ($user_id, $workout_plan_id, $week_number, '$start_time')";
    
    if (!mysqli_query($conn, $query)) {
        die("Error starting workout: " . mysqli_error($conn));
    }
    
    return mysqli_insert_id($conn);
}

// Function to end a workout session with proper validation
function endWorkoutSession($conn, $session_id) {
    $end_time = date('Y-m-d H:i:s');
    $query = "UPDATE workout_sessions 
              SET end_time = '$end_time',
                  duration_minutes = TIMESTAMPDIFF(MINUTE, start_time, '$end_time')
              WHERE session_id = $session_id";
    
    if (!mysqli_query($conn, $query)) {
        die("Error ending workout: " . mysqli_error($conn));
    }
    
    return true;
}

// Function to get active session with proper error handling
function getActiveSession($conn, $user_id) {
    $query = "SELECT * FROM workout_sessions 
              WHERE user_id = $user_id AND end_time IS NULL
              ORDER BY start_time DESC LIMIT 1";
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        die("Database error: " . mysqli_error($conn));
    }
    
    return mysqli_fetch_assoc($result);
}

// Function to get total weeks for a workout plan with proper error handling
function getTotalWeeks($conn, $workout_plan_id) {
    $query = "SELECT COUNT(*) as total_weeks FROM workout_plan_details 
              WHERE workout_plan_id = $workout_plan_id";
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        die("Database error: " . mysqli_error($conn));
    }
    
    $data = mysqli_fetch_assoc($result);
    return $data['total_weeks'];
}

// Get workout plan ID from URL
$workout_plan_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($workout_plan_id <= 0) {
    die("Invalid workout plan ID");
}

$email = $_SESSION['email'];

// Get user ID
$user_query = "SELECT table_id FROM user_register_details WHERE email = '$email'";
$user_result = mysqli_query($conn, $user_query);
if (!$user_result || mysqli_num_rows($user_result) == 0) {
    die("User not found");
}

$user = mysqli_fetch_assoc($user_result);
$user_id = $user['table_id'];

// Get workout plan name and description
$workout_query = "SELECT * FROM workout_plan WHERE table_id = $workout_plan_id";
$workout_result = mysqli_query($conn, $workout_query);
if (!$workout_result || mysqli_num_rows($workout_result) == 0) {
    die("Workout plan not found");
}

$workout = mysqli_fetch_assoc($workout_result);

// Get workout details
$details = getWorkoutDetails($conn, $workout_plan_id);
if (empty($details)) {
    die("No workout details found for this plan");
}

// Get user progress
$progress = getUserWorkoutProgress($conn, $user_id, $workout_plan_id);

// Get active session if any
$active_session = getActiveSession($conn, $user_id);

// Get total weeks for this workout plan
$total_weeks = getTotalWeeks($conn, $workout_plan_id);

// Handle workout actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['start_workout'])) {
        $week_number = (int)$_POST['week_number'];
        if ($week_number < 1 || $week_number > $total_weeks) {
            die("Invalid week number");
        }
        
        // Start new session
        $session_id = startWorkoutSession($conn, $user_id, $workout_plan_id, $week_number);
        $active_session = getActiveSession($conn, $user_id);
    } 
    elseif (isset($_POST['end_workout'])) {
        $session_id = (int)$_POST['session_id'];
        $week_number = (int)$_POST['week_number'];
        
        if ($week_number < 1 || $week_number > $total_weeks) {
            die("Invalid week number");
        }
        
        // End current session
        endWorkoutSession($conn, $session_id);
        
        // Update progress
        if (!$progress) {
            // Create new progress record if none exists
            $insert_query = "INSERT INTO user_workout_progress 
                            (user_id, workout_plan_id, current_week, completed_weeks) 
                            VALUES ($user_id, $workout_plan_id, $week_number + 1, '$week_number')";
            mysqli_query($conn, $insert_query);
        } else {
            // Update existing progress
            $completed_weeks = $progress['completed_weeks'] ? explode(',', $progress['completed_weeks']) : [];
            if (!in_array($week_number, $completed_weeks)) {
                $completed_weeks[] = $week_number;
                $new_completed = implode(',', $completed_weeks);
                $update_query = "UPDATE user_workout_progress 
                                SET current_week = $week_number + 1, 
                                    completed_weeks = '$new_completed'
                                WHERE user_id = $user_id AND workout_plan_id = $workout_plan_id";
                mysqli_query($conn, $update_query);
            }
        }
        
        // Refresh progress data
        $progress = getUserWorkoutProgress($conn, $user_id, $workout_plan_id);
        $active_session = null;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($workout['workout_name']); ?> Details</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f9ff;
            color: #333;
            padding: 20px;
            margin: 0;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }
        
        h1 {
            color: #1a3a8f;
            margin-bottom: 10px;
        }
        
        .workout-description {
            color: #666;
            margin-bottom: 20px;
        }
        
        .progress-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .progress-container {
            background: #e9f0ff;
            border-radius: 10px;
            height: 10px;
            width: 100%;
            margin: 15px 0;
            overflow: hidden;
        }
        
        .progress-bar {
            background: linear-gradient(90deg, #4fc3f7, #1a3a8f);
            height: 100%;
            border-radius: 10px;
            transition: width 0.5s ease;
        }
        
        .workout-detail {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            position: relative;
        }
        
        .workout-detail.completed {
            background: #e8f5e9;
            border-left: 4px solid #4caf50;
        }
        
        .workout-detail.current {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
        }
        
        .workout-detail.locked {
            background: #f5f5f5;
            color: #999;
            border-left: 4px solid #ccc;
        }
        
        .workout-detail h2 {
            color: #1a3a8f;
            margin-bottom: 10px;
        }
        
        .workout-detail.locked h2 {
            color: #999;
        }
        
        .workout-detail p {
            margin-bottom: 10px;
            color: #555;
        }
        
        .workout-detail.locked p {
            color: #aaa;
        }
        
        .workout-detail .goal {
            font-weight: 600;
            color: #333;
        }
        
        .workout-detail.locked .goal {
            color: #999;
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
        }
        
        .action-btn:hover {
            background: #142e6f;
            transform: translateY(-2px);
        }
        
        .action-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
        }
        
        .action-btn.danger {
            background: #e53935;
        }
        
        .action-btn.danger:hover {
            background: #c62828;
        }
        
        .action-btn.success {
            background: #4caf50;
        }
        
        .action-btn.success:hover {
            background: #3d8b40;
        }
        
        .completed-badge {
            color: #4caf50;
            font-weight: 600;
        }
        
        .timer-display {
            font-size: 18px;
            font-weight: bold;
            margin: 10px 0;
            color: #1a3a8f;
        }
        
        .active-session-banner {
            background: #1a3a8f;
            color: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .active-session-banner p {
            margin: 0;
            color: white;
        }
        
        .back-btn {
            display: inline-block;
            margin-top: 20px;
            text-decoration: none;
            color: #1a3a8f;
            font-weight: 600;
        }
        
        .back-btn i {
            margin-right: 8px;
        }
        
        .week-status {
            position: absolute;
            top: 15px;
            right: 15px;
            font-weight: bold;
            color: #1a3a8f;
        }
        
        .week-status.completed {
            color: #4caf50;
        }
        
        .week-status.current {
            color: #2196f3;
        }
        
        .week-status.locked {
            color: #999;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><?php echo htmlspecialchars($workout['workout_name']); ?></h1>
        
        <?php if (!empty($workout['description'])): ?>
            <p class="workout-description"><?php echo htmlspecialchars($workout['description']); ?></p>
        <?php endif; ?>
        
        <div class="progress-header">
            <div>
                <h3>Workout Progress</h3>
                <div class="progress-container">
                    <div class="progress-bar" style="width: <?php 
                        echo $progress ? round(($progress['current_week'] - 1) / $total_weeks * 100) : 0; 
                    ?>%"></div>
                </div>
            </div>
            <div>
                <span><?php echo $progress ? $progress['current_week'] - 1 : 0; ?> of <?php echo $total_weeks; ?> weeks completed</span>
            </div>
        </div>
        
        <?php if ($active_session): ?>
            <div class="active-session-banner">
                <p>
                    <i class="fas fa-running"></i> 
                    Workout in progress (Week <?php echo $active_session['week_number']; ?>) - 
                    Started at <?php echo date('h:i A', strtotime($active_session['start_time'])); ?>
                </p>
                <form method="POST">
                    <input type="hidden" name="session_id" value="<?php echo $active_session['session_id']; ?>">
                    <input type="hidden" name="week_number" value="<?php echo $active_session['week_number']; ?>">
                    <button type="submit" name="end_workout" class="action-btn danger">
                        <i class="fas fa-stop"></i> End Workout
                    </button>
                </form>
            </div>
        <?php endif; ?>
        
        <?php foreach ($details as $detail): ?>
            <?php 
            $is_completed = $progress && in_array($detail['week_number'], explode(',', $progress['completed_weeks']));
            $is_current = $progress && $detail['week_number'] == $progress['current_week'];
            $is_locked = !$is_completed && !$is_current && $progress && $detail['week_number'] > $progress['current_week'];
            $is_active_session = $active_session && $active_session['week_number'] == $detail['week_number'];
            ?>
            
            <div class="workout-detail 
                <?php echo $is_completed ? 'completed' : ''; ?> 
                <?php echo $is_current ? 'current' : ''; ?>
                <?php echo $is_locked ? 'locked' : ''; ?>">
                
                <span class="week-status <?php 
                    echo $is_completed ? 'completed' : ''; 
                    echo $is_current ? 'current' : '';
                    echo $is_locked ? 'locked' : '';
                ?>">
                    <?php if ($is_completed): ?>
                        <i class="fas fa-check-circle"></i> Completed
                    <?php elseif ($is_current): ?>
                        <i class="fas fa-play-circle"></i> Current Week
                    <?php elseif ($is_locked): ?>
                        <i class="fas fa-lock"></i> Locked
                    <?php else: ?>
                        Week <?php echo $detail['week_number']; ?>
                    <?php endif; ?>
                </span>
                
                <h2><?php echo htmlspecialchars($detail['title']); ?></h2>
                <p><strong>Duration:</strong> <?php echo $detail['duration_weeks'] > 1 ? $detail['duration_weeks'] . ' weeks' : '1 week'; ?></p>
                <p class="goal"><strong>Goal:</strong> <?php echo htmlspecialchars($detail['goal']); ?></p>
                <p><strong>Activities:</strong></p>
                <div><?php echo nl2br(htmlspecialchars($detail['activities'])); ?></div>
                
                <?php if ($is_active_session): ?>
                    <div class="timer-display">
                        <i class="fas fa-clock"></i> Session in progress
                    </div>
                    <form method="POST">
                        <input type="hidden" name="session_id" value="<?php echo $active_session['session_id']; ?>">
                        <input type="hidden" name="week_number" value="<?php echo $detail['week_number']; ?>">
                        <button type="submit" name="end_workout" class="action-btn danger">
                            <i class="fas fa-stop"></i> Complete Workout
                        </button>
                    </form>
                <?php elseif ($is_current && !$is_completed): ?>
                    <form method="POST">
                        <input type="hidden" name="week_number" value="<?php echo $detail['week_number']; ?>">
                        <button type="submit" name="start_workout" class="action-btn">
                            <i class="fas fa-play"></i> Start Workout
                        </button>
                    </form>
                <?php elseif ($is_completed): ?>
                    <?php 
                    // Get session details for this completed workout
                    $session_query = "SELECT * FROM workout_sessions 
                                    WHERE user_id = $user_id 
                                    AND workout_plan_id = $workout_plan_id
                                    AND week_number = {$detail['week_number']}
                                    ORDER BY end_time DESC LIMIT 1";
                    $session_result = mysqli_query($conn, $session_query);
                    $session = $session_result ? mysqli_fetch_assoc($session_result) : null;
                    ?>
                    <?php if ($session): ?>
                        <p><small>Completed on <?php echo date('M j, Y', strtotime($session['end_time'])); ?> 
                        (Duration: <?php echo $session['duration_minutes'] ?? 'N/A'; ?> minutes)</small></p>
                    <?php endif; ?>
                <?php elseif ($is_locked): ?>
                    <button class="action-btn" disabled>
                        <i class="fas fa-lock"></i> Complete previous weeks to unlock
                    </button>
                <?php else: ?>
                    <form method="POST">
                        <input type="hidden" name="week_number" value="<?php echo $detail['week_number']; ?>">
                        <button type="submit" name="start_workout" class="action-btn">
                            <i class="fas fa-play"></i> Start Workout
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
        
        <a href="dashboard.php" class="back-btn">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>

    <script>
        // Timer functionality for active sessions
        <?php if ($active_session): ?>
            const startTime = new Date("<?php echo $active_session['start_time']; ?>").getTime();
            
            function updateTimer() {
                const now = new Date().getTime();
                const distance = now - startTime;
                
                const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);
                
                const timerDisplay = document.querySelector('.timer-display');
                if (timerDisplay) {
                    timerDisplay.innerHTML = `<i class="fas fa-clock"></i> ${hours}h ${minutes}m ${seconds}s`;
                }
            }
            
            updateTimer();
            setInterval(updateTimer, 1000);
        <?php endif; ?>
    </script>
</body>
</html>