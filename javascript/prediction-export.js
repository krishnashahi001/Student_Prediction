// prediction-export.js - Export functions for prediction results

// Load prediction data on page load
document.addEventListener('DOMContentLoaded', function() {
    loadPredictionFromBackend();
});

// Export as PDF
function exportAsPDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();

    html2canvas(document.querySelector('.prediction-container')).then(canvas => {
        const imgData = canvas.toDataURL('image/png');
        const imgWidth = 210; // A4 width in mm
        const pageHeight = 295; // A4 height in mm
        const imgHeight = canvas.height * imgWidth / canvas.width;
        let heightLeft = imgHeight;

        let position = 0;

        doc.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
        heightLeft -= pageHeight;

        while (heightLeft >= 0) {
            position = heightLeft - imgHeight;
            doc.addPage();
            doc.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
            heightLeft -= pageHeight;
        }

        doc.save('performance-prediction.pdf');
    });
}

// Export as Image
function exportAsImage() {
    html2canvas(document.querySelector('.prediction-container')).then(canvas => {
        const link = document.createElement('a');
        link.download = 'performance-prediction.png';
        link.href = canvas.toDataURL();
        link.click();
    });
}