<?php
session_start();

// Protect page (only logged-in users)
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Database connection for saving history
require_once __DIR__ . '/config.php';
// config.php defines global variables $DB_HOST, $DB_USER, $DB_PASS, $DB_NAME
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

// Receive data from prediction-inputs
$data = [
    'lastMarks' => isset($_GET['lastMarks']) ? floatval($_GET['lastMarks']) : 0,
    'lastAssignment' => isset($_GET['lastAssignment']) ? intval($_GET['lastAssignment']) : 0,
    'lastAttendence' => isset($_GET['lastAttendence']) ? floatval($_GET['lastAttendence']) : 0,
    'currentAttendence' => isset($_GET['currentAttendence']) ? floatval($_GET['currentAttendence']) : 0,
    'currentAssignment' => isset($_GET['currentAssignment']) ? intval($_GET['currentAssignment']) : 0
];

// Prediction Model Class
class PerformancePredictor {
    private $weights = [
        'pastScore' => 0.30,
        'lastAssignment' => 0.15,
        'lastAttendence' => 0.15,
        'currentAttendence' => 0.25,
        'currentAssignment' => 0.15
    ];

    public function __construct($customWeights = null) {
        if ($customWeights) {
            $this->weights = array_merge($this->weights, $customWeights);
        }
    }

    // Calculate base prediction
    public function predictScore($data) {
        $normalizedLastAssign = min(($data['lastAssignment'] / 15) * 100, 100);
        $normalizedCurrentAssign = min(($data['currentAssignment'] / 15) * 100, 100);

        $score = 
            ($data['lastMarks'] * $this->weights['pastScore']) +
            ($normalizedLastAssign * $this->weights['lastAssignment']) +
            ($data['lastAttendence'] * $this->weights['lastAttendence']) +
            ($data['currentAttendence'] * $this->weights['currentAttendence']) +
            ($normalizedCurrentAssign * $this->weights['currentAssignment']);

        return round($score);
    }

    // Trend Analysis
    public function calculateTrend($data) {
        $attendanceTrend = $data['currentAttendence'] - $data['lastAttendence'];
        $assignmentTrend = $data['currentAssignment'] - $data['lastAssignment'];

        return [
            'attendanceTrend' => $attendanceTrend,
            'assignmentTrend' => $assignmentTrend,
            'overallTrendDirection' => ($attendanceTrend + $assignmentTrend) > 0 ? 'Improving' : 'Declining'
        ];
    }

    // Risk Assessment
    public function assessRisk($data, $predictedScore) {
        $risks = [];

        if ($data['lastMarks'] < 60) {
            $risks[] = ['level' => 'High', 'factor' => 'Low Past Marks', 'score' => 20];
        } elseif ($data['lastMarks'] < 75) {
            $risks[] = ['level' => 'Medium', 'factor' => 'Average Past Marks', 'score' => 10];
        }

        if ($data['currentAttendence'] < 75) {
            $risks[] = ['level' => 'High', 'factor' => 'Low Attendance', 'score' => 25];
        }

        if ($data['currentAssignment'] < 8) {
            $risks[] = ['level' => 'High', 'factor' => 'Low Submission Rate', 'score' => 20];
        }

        return $risks;
    }

    // Next Semester Forecast
    public function forecastNextSemester($data, $predictedScore) {
        $trend = $this->calculateTrend($data);
        
        // Project improvement based on current trend
        $improvementRate = 0;
        if ($trend['attendanceTrend'] > 0) $improvementRate += 2;
        if ($trend['assignmentTrend'] > 0) $improvementRate += 3;

        $forecastScore = min($predictedScore + $improvementRate, 100);

        return [
            'forecast' => round($forecastScore),
            'potentialImprovement' => round($forecastScore - $predictedScore),
            'confidence' => $trend['overallTrendDirection'] === 'Improving' ? 'High' : 'Low'
        ];
    }

    // Comparative Analysis
    public function getComparativeMetrics($data, $predictedScore) {
        $avgAttendance = ($data['lastAttendence'] + $data['currentAttendence']) / 2;
        $avgAssignments = ($data['lastAssignment'] + $data['currentAssignment']) / 2;

        return [
            'classAverageScore' => 72, // Example benchmark
            'studentVsClassDifference' => $predictedScore - 72,
            'performancePercentile' => $predictedScore >= 90 ? 'Top 10%' : ($predictedScore >= 80 ? 'Top 25%' : 'Average'),
            'strengthArea' => $avgAssignments > $avgAttendance ? 'Assignments' : 'Attendance',
            'weaknessArea' => $avgAssignments < $avgAttendance ? 'Assignments' : 'Attendance'
        ];
    }

    // Get grade
    public function getGrade($score) {
        if ($score >= 90) return ['grade' => 'A+', 'color' => '#28a745', 'category' => 'Excellent'];
        if ($score >= 80) return ['grade' => 'A', 'color' => '#20c997', 'category' => 'Very Good'];
        if ($score >= 70) return ['grade' => 'B', 'color' => '#ffc107', 'category' => 'Good'];
        if ($score >= 60) return ['grade' => 'C', 'color' => '#fd7e14', 'category' => 'Average'];
        return ['grade' => 'F', 'color' => '#dc3545', 'category' => 'Below Average'];
    }
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

// Return JSON for frontend
header('Content-Type: application/json');
echo json_encode([
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
]);
?>
