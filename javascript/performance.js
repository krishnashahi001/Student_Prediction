function analyzePerformance() {

    // Get inputs
    const pastScore = parseFloat(document.getElementById("pastScore").value);
    const lastAssign = parseInt(document.getElementById("lastAssign").value);
    const lastAttendance = parseFloat(document.getElementById("lastAttendance").value);
    const currentAttendance = parseFloat(document.getElementById("currentAttendance").value);
    const currentAssign = parseInt(document.getElementById("currentAssign").value);

    // Normalize assignments (out of 10)
    const assignScore = ((lastAssign + currentAssign) / 20) * 100;

    // Weighted score logic (NO AI)
    const performanceScore =
        (pastScore * 0.3) +
        (lastAttendance * 0.15) +
        (currentAttendance * 0.25) +
        (assignScore * 0.3);

    // Grade Logic
    let grade = "";
    if (performanceScore >= 85) grade = "Excellent";
    else if (performanceScore >= 70) grade = "Good";
    else if (performanceScore >= 50) grade = "Average";
    else grade = "Needs Improvement";

    // Show Stats
    document.getElementById("stats").innerHTML = `
        <h3>Performance Analysis</h3>
        <p><strong>Predicted Score:</strong> ${performanceScore.toFixed(2)}%</p>
        <p><strong>Performance Level:</strong> ${grade}</p>
    `;

    drawPieChart(performanceScore);
    drawLineChart(pastScore, lastAttendance, currentAttendance, assignScore);
}
function drawPieChart(score) {

    const ctx = document.getElementById('pieChart').getContext('2d');

    if (window.pieChart) window.pieChart.destroy();

    window.pieChart = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: ['Achieved', 'Remaining'],
            datasets: [{
                data: [score, 100 - score],
            }]
        }
    });
}
function drawLineChart(past, lastAttend, currentAttend, assignScore) {

    const ctx = document.getElementById('lineChart').getContext('2d');

    if (window.lineChart) window.lineChart.destroy();

    window.lineChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Past Score', 'Last Attendance', 'Current Attendance', 'Assignments'],
            datasets: [{
                label: 'Performance Trend',
                data: [past, lastAttend, currentAttend, assignScore],
                fill: false,
                tension: 0.4
            }]
        }
    });
}

