// Shared helper utilities for SPPA

// Parse query parameters used across prediction pages
function getQueryParams() {
    const params = new URLSearchParams(window.location.search);
    return {
        lastMarks: parseFloat(params.get('lastMarks')) || 0,
        lastAssignment: parseInt(params.get('lastAssignment')) || 0,
        lastAttendence: parseFloat(params.get('lastAttendence')) || 0,
        currentAttendence: parseFloat(params.get('currentAttendence')) || 0,
        currentAssignment: parseInt(params.get('currentAssignment')) || 0,
        // Additional inputs to improve prediction accuracy (optional)
        studyHoursPerWeek: parseFloat(params.get('studyHoursPerWeek')) || 0,
        previousGPA: parseFloat(params.get('previousGPA')) || 0
    };
}

// Centralized prediction calculator — returns a score 0-100
function calculatePredictionScore(data) {
    // Base weights (sum = 1.0)
    // Rebalanced weights after removing some optional inputs
    const weights = {
        pastScore: 0.30,
        lastAssignment: 0.08,
        lastAttendence: 0.10,
        currentAttendence: 0.25,
        currentAssignment: 0.08,
        studyHours: 0.14,
        previousGPA: 0.05
    };

    // normalize assignment counts to percent (assuming 15 max)
    const normLastAssign = Math.min((data.lastAssignment / 15) * 100, 100);
    const normCurrentAssign = Math.min((data.currentAssignment / 15) * 100, 100);

    // studyHoursPerWeek: assume 0-40 scale, convert to percent
    const normStudyHours = Math.min((data.studyHoursPerWeek / 40) * 100, 100);

    // previousGPA: assume 0-10 or 0-4 scale — clamp to 0-10 then scale to percent
    const normPrevGPA = Math.min(data.previousGPA, 10) / 10 * 100;

    const score =
        (data.lastMarks * weights.pastScore) +
        (normLastAssign * weights.lastAssignment) +
        (data.lastAttendence * weights.lastAttendence) +
        (data.currentAttendence * weights.currentAttendence) +
        (normCurrentAssign * weights.currentAssignment) +
        (normStudyHours * weights.studyHours) +
        (normPrevGPA * weights.previousGPA);

    return Math.round(score);
}

// Generate small, actionable insights based on additional inputs
function generateInsights(data, score) {
    const insights = [];

    if (data.studyHoursPerWeek && data.studyHoursPerWeek < 10) {
        insights.push('Increase weekly study hours — aim for at least 10–15 hours focused study.');
    } else if (data.studyHoursPerWeek && data.studyHoursPerWeek >= 20) {
        insights.push('Great study habit — maintain consistency and focused practice.');
    }

    if (data.previousGPA && data.previousGPA < 5) {
        insights.push('Consider tutoring or study groups to raise your baseline GPA.');
    }

    // removed insights for participation/quiz/assignmentQuality/labScore per request

    // Score-based quick tips
    if (score >= 90) insights.push('Maintain current strategies — you are performing excellently.');
    else if (score >= 75) insights.push('Small targeted improvements could push you to the next grade band.');
    else if (score < 50) insights.push('Create a focused improvement plan: prioritize weak topics and regular practice.');

    if (insights.length === 0) return '<li>Keep maintaining your current performance.</li>';
    return insights.map(i => `<li>${i}</li>`).join('\n');
}

// Convert numeric score into grade, color and category
function getGrade(score) {
    if (score >= 90) return { grade: 'A+', color: '#28a745', category: 'Excellent' };
    if (score >= 80) return { grade: 'A', color: '#20c997', category: 'Very Good' };
    if (score >= 70) return { grade: 'B', color: '#ffc107', category: 'Good' };
    if (score >= 60) return { grade: 'C', color: '#fd7e14', category: 'Average' };
    return { grade: 'F', color: '#dc3545', category: 'Below Average' };
}
