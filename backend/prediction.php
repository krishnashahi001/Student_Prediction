<?php
session_start();

// Protect page (only logged-in users)
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Include the PerformancePredictor class
require_once __DIR__ . '/../classes/PerformancePredictor.php';

// Database connection for saving history
require_once __DIR__ . '/config.php';
// config.php defines global variables $DB_HOST, $DB_USER, $DB_PASS, $DB_NAME
try {
    $pdo = new PDO(
        "mysql:host={$DB_HOST};dbname={$DB_NAME}",
        $DB_USER,
        $DB_PASS
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Continue without database if connection fails
    $pdo = null;
}

// Check if POST request (form submission)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Receive data from POST
    $data = [
        'lastMarks' => isset($_POST['lastMarks']) ? floatval($_POST['lastMarks']) : 0,
        'lastAssignment' => isset($_POST['lastAssignment']) ? intval($_POST['lastAssignment']) : 0,
        'lastAttendence' => isset($_POST['lastAttendence']) ? floatval($_POST['lastAttendence']) : 0,
        'currentAttendence' => isset($_POST['currentAttendence']) ? floatval($_POST['currentAttendence']) : 0,
        'currentAssignment' => isset($_POST['currentAssignment']) ? intval($_POST['currentAssignment']) : 0
    ];

    // Server-side validation
    $errors = [];
    if ($data['lastMarks'] < 0 || $data['lastMarks'] > 100) {
        $errors[] = 'Past Score must be between 0 and 100.';
    }
    if ($data['lastAttendence'] < 0 || $data['lastAttendence'] > 100) {
        $errors[] = 'Last Semester Attendance must be between 0 and 100.';
    }
    if ($data['currentAttendence'] < 0 || $data['currentAttendence'] > 100) {
        $errors[] = 'Current Attendance must be between 0 and 100.';
    }
    if ($data['lastAssignment'] < 5 || $data['lastAssignment'] > 10) {
        $errors[] = 'Last Semester Assignments must be between 5 and 10.';
    }
    if ($data['currentAssignment'] < 5 || $data['currentAssignment'] > 10) {
        $errors[] = 'Current Assignments must be between 5 and 10.';
    }

    if (!empty($errors)) {
        $_SESSION['prediction_result'] = [
            'success' => false,
            'error' => implode(' ', $errors)
        ];
        header("Location: ../Templates/prediction.html");
        exit;
    }

    // Execute Prediction
    $predictor = new PerformancePredictor();
    $predictedScore = $predictor->predictScore($data);
    $gradeInfo = $predictor->getGrade($predictedScore);
    $trend = $predictor->calculateTrend($data);
    $risks = $predictor->assessRisk($data, $predictedScore);
    $forecast = $predictor->forecastNextSemester($data, $predictedScore);
    $comparative = $predictor->getComparativeMetrics($data, $predictedScore);

    // Save to database if connection available
    $analysis_id = null;
    if ($pdo) {
        try {
            $sql = "INSERT INTO prediction_history (user_id, last_marks, last_assignment, last_attendence, 
                    current_attendence, current_assignment, predicted_score, grade, category, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $_SESSION['user_id'],
                $data['lastMarks'],
                $data['lastAssignment'],
                $data['lastAttendence'],
                $data['currentAttendence'],
                $data['currentAssignment'],
                $predictedScore,
                $gradeInfo['grade'],
                $gradeInfo['category']
            ]);
            
            $analysis_id = $pdo->lastInsertId();
        } catch (PDOException $e) {
            // Silently fail - still return results
        }
    }

    // Store result in session
    $_SESSION['prediction_result'] = [
        'success' => true,
        'prediction' => [
            'score' => $predictedScore,
            'grade' => $gradeInfo['grade'],
            'category' => $gradeInfo['category'],
            'color' => $gradeInfo['color']
        ],
        'inputData' => $data,
        'trend' => $trend,
        'risks' => $risks,
        'forecast' => $forecast,
        'comparative' => $comparative
    ];

    // Redirect to prediction page
    header("Location: ../Templates/prediction.html");
    exit;
}

// If GET request, return the stored result as JSON
if (isset($_SESSION['prediction_result'])) {
    header('Content-Type: application/json');
    echo json_encode($_SESSION['prediction_result']);
    // Optionally clear the session after sending
    unset($_SESSION['prediction_result']);
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'No prediction data available']);
}
?>
