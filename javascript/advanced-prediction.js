// Load prediction data from backend
function loadPredictionFromBackend() {
    const params = new URLSearchParams(window.location.search);
    const backendUrl = `backend/prediction.php?${params.toString()}`;

    fetch(backendUrl)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayAllAnalysis(data);
            } else {
                document.getElementById('stats').innerHTML = '<p style="color: red;">Error loading prediction data.</p>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            // Fallback to client-side calculation
            analyzePerformanceClient();
        });
}

// Display all analysis components
function displayAllAnalysis(data) {
    const { prediction, inputData, trend, risks, forecast, comparative } = data;

    // Display main score
    displayMainScore(prediction);

    // Render charts
    renderPieChart(inputData);
    renderLineChart(inputData, prediction.score);

    // Display trend analysis
    displayTrendAnalysis(trend);

    // Display risk assessment
    displayRiskAssessment(risks);

    // Display forecast
    displayForecast(forecast, prediction.score);

    // Display comparative analysis
    displayComparativeAnalysis(comparative, prediction);

    // Display detailed stats
    displayDetailedStats(inputData, prediction);
}

// Display main prediction score
function displayMainScore(prediction) {
    const mainDiv = document.getElementById('mainPrediction');
    mainDiv.style.borderColor = prediction.color;

    document.getElementById('scoreDisplay').innerHTML = `${prediction.score}%`;
    document.getElementById('gradeDisplay').innerHTML = prediction.grade;
    document.getElementById('gradeDisplay').style.color = prediction.color;
    document.getElementById('categoryDisplay').innerHTML = prediction.category;
}

// Pie Chart - Score Distribution
function renderPieChart(data) {
    const ctx = document.getElementById('pieChart').getContext('2d');
    const totalValue = data.lastMarks + data.lastAssignment * 6.67 + data.lastAttendence + 
                       data.currentAttendence + data.currentAssignment * 6.67;

    const weights = {
        'Past Score': ((data.lastMarks / totalValue) * 100).toFixed(2),
        'Last Assignment': ((data.lastAssignment * 6.67 / totalValue) * 100).toFixed(2),
        'Attendance Avg': (((data.lastAttendence + data.currentAttendence) / 2 / totalValue) * 100).toFixed(2),
        'Current Assignment': ((data.currentAssignment * 6.67 / totalValue) * 100).toFixed(2)
    };

    if (window.pieChartInstance) {
        window.pieChartInstance.destroy();
    }

    window.pieChartInstance = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: Object.keys(weights),
            datasets: [{
                data: Object.values(weights),
                backgroundColor: [
                    '#FF6384',
                    '#36A2EB',
                    '#FFCE56',
                    '#4BC0C0'
                ],
                borderColor: '#fff',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
}

// Line Chart - Performance Trend
function renderLineChart(data, predictedScore) {
    const ctx = document.getElementById('lineChart').getContext('2d');

    const labels = ['Past Score', 'Last Attend', 'Current Attend', 'Predicted'];
    const values = [
        data.lastMarks,
        data.lastAttendence,
        data.currentAttendence,
        predictedScore
    ];

    if (window.lineChartInstance) {
        window.lineChartInstance.destroy();
    }

    window.lineChartInstance = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Performance %',
                data: values,
                borderColor: '#667eea',
                backgroundColor: 'rgba(102, 126, 234, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointRadius: 6,
                pointBackgroundColor: '#667eea',
                pointBorderColor: '#fff',
                pointBorderWidth: 2
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100
                }
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                }
            }
        }
    });
}

// Display Trend Analysis
function displayTrendAnalysis(trend) {
    const trendDiv = document.getElementById('trendAnalysis');
    const attendanceTrendClass = trend.attendanceTrend >= 0 ? 'trend-up' : 'trend-down';
    const assignmentTrendClass = trend.assignmentTrend >= 0 ? 'trend-up' : 'trend-down';
    const trendDirectionClass = trend.overallTrendDirection === 'Improving' ? 'trend-up' : 'trend-down';

    trendDiv.innerHTML = `
        <div class="metric">
            <strong>Attendance Trend:</strong> 
            <span class="${attendanceTrendClass}">
                ${trend.attendanceTrend >= 0 ? '↑' : '↓'} ${Math.abs(trend.attendanceTrend).toFixed(1)}%
            </span>
        </div>
        <div class="metric">
            <strong>Assignment Trend:</strong> 
            <span class="${assignmentTrendClass}">
                ${trend.assignmentTrend >= 0 ? '↑' : '↓'} ${Math.abs(trend.assignmentTrend)}
            </span>
        </div>
        <div class="metric">
            <strong>Overall Direction:</strong> 
            <span class="${trendDirectionClass}">
                ${trend.overallTrendDirection}
            </span>
        </div>
    `;
}

// Display Risk Assessment
function displayRiskAssessment(risks) {
    const riskDiv = document.getElementById('riskAssessment');
    
    if (risks.length === 0) {
        riskDiv.innerHTML = '<p style="color: #28a745;">✅ No significant risks detected!</p>';
        return;
    }

    let riskHTML = '';
    risks.forEach(risk => {
        const riskClass = risk.level === 'High' ? 'risk-high' : 'risk-medium';
        riskHTML += `
            <div class="metric">
                <strong class="${riskClass}">⚠️ ${risk.level} Risk:</strong> ${risk.factor}
                <div style="font-size: 12px; color: #666; margin-top: 5px;">Impact Score: ${risk.score}/100</div>
            </div>
        `;
    });

    riskDiv.innerHTML = riskHTML;
}

// Display Next Semester Forecast
function displayForecast(forecast, currentScore) {
    const forecastDiv = document.getElementById('forecastData');
    const improvementClass = forecast.potentialImprovement >= 0 ? 'trend-up' : 'trend-down';

    forecastDiv.innerHTML = `
        <div style="color: white;">
            <h3>Projected Score</h3>
            <div style="font-size: 36px; font-weight: bold; margin: 10px 0;">
                ${forecast.forecast}%
            </div>
            <p>Forecast Confidence: ${forecast.confidence}</p>
        </div>
        <div style="color: white;">
            <h3>Improvement Potential</h3>
            <div style="font-size: 36px; font-weight: bold; margin: 10px 0; class="${improvementClass}">
                ${forecast.potentialImprovement >= 0 ? '↑' : '↓'} ${Math.abs(forecast.potentialImprovement)}%
            </div>
            <p>From current: ${currentScore}%</p>
        </div>
    `;
}

// Display Comparative Analysis
function displayComparativeAnalysis(comparative, prediction) {
    const compDiv = document.getElementById('comparativeAnalysis');
    const differenceClass = comparative.studentVsClassDifference >= 0 ? 'trend-up' : 'trend-down';

    compDiv.innerHTML = `
        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px;">
            <div class="metric">
                <strong>Performance Percentile:</strong>
                <div style="font-size: 18px; font-weight: bold; color: #667eea;">
                    ${comparative.performancePercentile}
                </div>
            </div>
            <div class="metric">
                <strong>Class Comparison:</strong>
                <div style="font-size: 18px; font-weight: bold;" class="${differenceClass}">
                    ${comparative.studentVsClassDifference >= 0 ? '+' : ''}${comparative.studentVsClassDifference.toFixed(1)} pts
                </div>
                <small>vs class avg: 72%</small>
            </div>
            <div class="metric">
                <strong>Performance Areas:</strong>
                <div style="margin-top: 10px;">
                    <div>💪 Strength: ${comparative.strengthArea}</div>
                    <div>⚡ Weakness: ${comparative.weaknessArea}</div>
                </div>
            </div>
        </div>
    `;
}

// Display Detailed Statistics
function displayDetailedStats(data, prediction) {
    const statsDiv = document.getElementById('stats');
    const avgAttendance = ((data.lastAttendence + data.currentAttendence) / 2).toFixed(2);

    statsDiv.innerHTML = `
        <h4>📋 Detailed Performance Breakdown</h4>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 15px;">
            <div>
                <strong>Input Metrics:</strong>
                <ul>
                    <li>Past Score: ${data.lastMarks}%</li>
                    <li>Last Semester Assignments: ${data.lastAssignment}/15</li>
                    <li>Last Semester Attendance: ${data.lastAttendence}%</li>
                    <li>Current Attendance: ${data.currentAttendence}%</li>
                    <li>Current Assignments: ${data.currentAssignment}/15</li>
                </ul>
            </div>
            <div>
                <strong>Calculated Metrics:</strong>
                <ul>
                    <li>Average Attendance: ${avgAttendance}%</li>
                    <li>Total Assignments Submitted: ${data.lastAssignment + data.currentAssignment}/30</li>
                    <li>Predicted Score: ${prediction.score}%</li>
                    <li>Grade: ${prediction.grade}</li>
                    <li>Category: ${prediction.category}</li>
                </ul>
            </div>
        </div>

        <h4 style="margin-top: 20px;">💡 Recommendations</h4>
        <div style="background: #f0f8ff; padding: 15px; border-radius: 8px;">
            ${getAdvancedRecommendations(data, prediction.score)}
            <h5 style="margin-top:10px">Insights</h5>
            <ul>
                ${generateInsights ? generateInsights(data, prediction.score) : ''}
            </ul>
        </div>
    `;
}

// Generate advanced recommendations
function getAdvancedRecommendations(data, score) {
    let recommendations = [];

    // Past score recommendations
    if (data.lastMarks < 60) {
        recommendations.push('🎯 <strong>Critical:</strong> Focus on strengthening fundamentals. Consider additional study sessions or tutoring.');
    } else if (data.lastMarks < 75) {
        recommendations.push('📚 <strong>Important:</strong> Review weak topics from previous semester before exams.');
    }

    // Attendance recommendations
    const avgAttend = (data.lastAttendence + data.currentAttendence) / 2;
    if (avgAttend < 75) {
        recommendations.push('📅 <strong>Critical:</strong> Improve attendance immediately. Classes are essential for understanding concepts.');
    } else if (avgAttend < 85) {
        recommendations.push('📌 <strong>Important:</strong> Maintain or improve your attendance rate for better performance.');
    }

    // Assignment recommendations
    const assignRatio = (data.currentAssignment / 15) * 100;
    if (assignRatio < 60) {
        recommendations.push('✏️ <strong>Critical:</strong> Submit remaining assignments. They significantly impact your grade.');
    } else if (assignRatio < 80) {
        recommendations.push('📝 <strong>Important:</strong> Complete more assignments to demonstrate your understanding.');
    }

    // Performance-based recommendations
    if (score >= 90) {
        recommendations.push('🌟 <strong>Excellent:</strong> Maintain your excellent work! Consider helping peers or participating in advanced activities.');
    } else if (score >= 75) {
        recommendations.push('🚀 <strong>Good Progress:</strong> You\'re on the right track. Small improvements can push you to the next level.');
    } else {
        recommendations.push('⚡ <strong>Action Required:</strong> You need immediate action in multiple areas. Create a focused study plan.');
    }

    return recommendations.map(rec => `<p>• ${rec}</p>`).join('');
}

// Fallback client-side analysis
function analyzePerformanceClient() {
    const params = new URLSearchParams(window.location.search);
    const data = {
        lastMarks: parseFloat(params.get('lastMarks')) || 0,
        lastAssignment: parseInt(params.get('lastAssignment')) || 0,
        lastAttendence: parseFloat(params.get('lastAttendence')) || 0,
        currentAttendence: parseFloat(params.get('currentAttendence')) || 0,
        currentAssignment: parseInt(params.get('currentAssignment')) || 0,
        studyHoursPerWeek: parseFloat(params.get('studyHoursPerWeek')) || 0,
        previousGPA: parseFloat(params.get('previousGPA')) || 0
    };

    const score = calculatePredictionScore(data);
    const grade = getGrade(score);

    displayMainScore(grade);
    renderPieChart(data, score);
    renderLineChart(data, score);
    displayDetailedStats(data, { score: score, ...grade });
}

// calculation moved to `javascript/common.js`

// Note: `getGrade` moved to `javascript/common.js`