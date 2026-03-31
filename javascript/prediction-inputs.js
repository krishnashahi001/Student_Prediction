// prediction-inputs.js - Validation for prediction input form

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('predictionForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            let errors = [];
            const lastMarks = parseFloat(document.getElementById('last-marks').value);
            const lastAssignment = parseInt(document.getElementById('last-assignment').value);
            const lastAttendence = parseFloat(document.getElementById('last-attendence').value);
            const currentAttendence = parseFloat(document.getElementById('current-attendence').value);
            const currentAssignment = parseInt(document.getElementById('current-assignment').value);

            if (lastMarks < 0 || lastMarks > 100) errors.push('Past Score must be between 0 and 100.');
            if (lastAttendence < 0 || lastAttendence > 100) errors.push('Last Semester Attendance must be between 0 and 100.');
            if (currentAttendence < 0 || currentAttendence > 100) errors.push('Current Attendance must be between 0 and 100.');
            if (lastAssignment < 5 || lastAssignment > 10) errors.push('Last Semester Assignments must be between 5 and 10.');
            if (currentAssignment < 5 || currentAssignment > 10) errors.push('Current Assignments must be between 5 and 10.');

            if (errors.length > 0) {
                alert(errors.join('\n'));
                e.preventDefault();
            }
        });
    }
});