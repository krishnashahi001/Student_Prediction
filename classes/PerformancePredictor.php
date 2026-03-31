<?php
// PerformancePredictor.php - Class for predicting student performance

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
        // Assignments must be between 5 and 10 (already validated)
        $normalizedLastAssign = (($data['lastAssignment'] - 5) / 5) * 100; // 5=0%, 10=100%
        $normalizedCurrentAssign = (($data['currentAssignment'] - 5) / 5) * 100;

        // Clamp normalization
        $normalizedLastAssign = max(0, min($normalizedLastAssign, 100));
        $normalizedCurrentAssign = max(0, min($normalizedCurrentAssign, 100));

        $score = 
            ($data['lastMarks'] * $this->weights['pastScore']) +
            ($normalizedLastAssign * $this->weights['lastAssignment']) +
            ($data['lastAttendence'] * $this->weights['lastAttendence']) +
            ($data['currentAttendence'] * $this->weights['currentAttendence']) +
            ($normalizedCurrentAssign * $this->weights['currentAssignment']);

        // Clamp score to 0-100
        $score = max(0, min(round($score), 100));
        return $score;
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
?>