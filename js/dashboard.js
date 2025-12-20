function initCharts(weatherLabels, weatherData, roadLabels, roadData, lineLabels, lineData, visLabels, visData, trafficLabels, trafficData) {

    // Helper to safely destroy chart if it exists
    const destroyChart = (id) => {
        const canvas = document.getElementById(id);
        if (canvas) {
            const chartInstance = Chart.getChart(canvas);
            if (chartInstance) chartInstance.destroy();
        }
    };

    if (document.getElementById('weatherChart')) {
        destroyChart('weatherChart');
        new Chart(document.getElementById('weatherChart'), {
            type: 'pie',
            data: {
                labels: weatherLabels,
                datasets: [{
                    data: weatherData,
                    backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4CAF50', '#9966FF']
                }]
            },
            options: { responsive: true, plugins: { title: { display: true, text: 'Weather' } } }
        });
    }

    if (document.getElementById('roadChart')) {
        destroyChart('roadChart');
        new Chart(document.getElementById('roadChart'), {
            type: 'doughnut',
            data: {
                labels: roadLabels,
                datasets: [{
                    data: roadData,
                    backgroundColor: ['#FF9F40', '#4BC0C0', '#C9CBCF', '#E7E9ED', '#36A2EB']
                }]
            },
            options: { responsive: true, plugins: { title: { display: true, text: 'Road Type' } } }
        });
    }

    if (document.getElementById('visibilityChart')) {
        destroyChart('visibilityChart');
        new Chart(document.getElementById('visibilityChart'), {
            type: 'pie',
            data: {
                labels: visLabels,
                datasets: [{
                    data: visData,
                    backgroundColor: ['#8e44ad', '#3498db', '#95a5a6', '#e74c3c', '#2ecc71']
                }]
            },
            options: { responsive: true, plugins: { title: { display: true, text: 'Visibility' } } }
        });
    }

    if (document.getElementById('trafficChart')) {
        destroyChart('trafficChart');
        new Chart(document.getElementById('trafficChart'), {
            type: 'polarArea',
            data: {
                labels: trafficLabels,
                datasets: [{
                    data: trafficData,
                    backgroundColor: ['#f1c40f', '#e67e22', '#e74c3c', '#9b59b6', '#34495e']
                }]
            },
            options: { responsive: true, plugins: { title: { display: true, text: 'Traffic Conditions' } } }
        });
    }

    if (document.getElementById('experiencesChart')) {
        destroyChart('experiencesChart');
        new Chart(document.getElementById('experiencesChart'), {
            type: 'line',
            data: {
                labels: lineLabels,
                datasets: [{
                    label: 'Cumulative Distance (km)',
                    data: lineData,
                    borderColor: '#36A2EB',
                    fill: false,
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { title: { display: true, text: 'Distance Over Time' } },
                scales: { y: { beginAtZero: true } }
            }
        });
    }
}

$(document).ready(function () {
    if ($('#experiencesTable').length) {
        if ($.fn.DataTable.isDataTable('#experiencesTable')) {
            $('#experiencesTable').DataTable().destroy();
        }
        $('#experiencesTable').DataTable({ responsive: true, order: [[0, "desc"]] });
    }

    $("#menu-toggle").click(function (e) {
        e.preventDefault();
        console.log("Toggling sidebar");
        $("#wrapper").toggleClass("toggled");
    });
});

// Validate Manual Form
function validateManualForm() {
    const distInput = document.querySelector('#tab-manual input[name="distance"]');
    const roadTypes = document.querySelectorAll('#tab-manual input[name="road_type[]"]:checked');
    const traffic = document.querySelectorAll('#tab-manual input[name="traffic[]"]:checked');

    // Check Distance > 0
    if (!distInput.value || parseFloat(distInput.value) <= 0) {
        showManualError('Error: Distance must be greater than 0 km.');
        return false;
    }

    // Check Road Type selected
    if (roadTypes.length === 0) {
        showManualError('Error: Please select at least one Road Type.');
        return false;
    }

    // Check Traffic selected
    if (traffic.length === 0) {
        showManualError('Error: Please select at least one Traffic condition.');
        return false;
    }

    return true; // Allow submit
}

function showManualError(msg) {
    const errorDiv = document.getElementById('manual-error-msg');
    if (errorDiv) {
        errorDiv.textContent = msg;
        errorDiv.style.display = 'block';

        // Hide after 5 seconds
        setTimeout(() => { errorDiv.style.display = 'none'; }, 5000);
    } else {
        alert(msg);
    }
}

function showTab(mode) {
    $('.tab-content').hide();
    $('.tab-btn').removeClass('active');
    $('#tab-' + mode).show();

    // Select based on the onclick attribute to be robust against order changes
    $(`.tab-btn[onclick="showTab('${mode}')"]`).addClass('active');
}
