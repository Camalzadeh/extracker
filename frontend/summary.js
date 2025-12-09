document.addEventListener("DOMContentLoaded", () => {
    function getFromLocalStorage() {
        return JSON.parse(localStorage.getItem("drivingExperiences")) || [];
    }

    function displaySummary() {
        const experiences = getFromLocalStorage();
        const totalDistance = experiences.reduce((sum, exp) => sum + exp.mileage, 0);
        const totalExperiences = experiences.length;

        const weatherStats = {};
        experiences.forEach((exp) => {
            if (weatherStats[exp.weather]) {
                weatherStats[exp.weather]++;
            } else {
                weatherStats[exp.weather] = 1;
            }
        });

        const summaryContent = document.getElementById("summaryContent");
        summaryContent.innerHTML = `
            <h3>Statistics</h3>
            <p><strong>Total Distance Traveled:</strong> ${totalDistance} km</p>
            <p><strong>Total Experiences Recorded:</strong> ${totalExperiences}</p>
        `;

        renderCharts( weatherStats, experiences);
    }
    function comp(a, b) {
        return new Date(a.date) - new Date(b.date);
    }

    function renderCharts( weatherStats, experiences) {
        const weatherLabels = Object.keys(weatherStats);
        const weatherData = Object.values(weatherStats);

        const weatherCtx = document.getElementById("weatherChart").getContext("2d");
        new Chart(weatherCtx, {
            type: "pie",
            data: {
                labels: weatherLabels,
                datasets: [
                    {
                        label: "Weather Distribution",
                        data: weatherData,
                        backgroundColor: [
                            "#FF6384",
                            "#36A2EB",
                            "#FFCE56",
                            "#4CAF50",
                            "#FF9F40",
                        ],
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: "top" },
                },
            },
        });

        const experiencesCtx = document.getElementById("experiencesChart").getContext("2d");
        const groupedByDate = experiences.reduce((acc, exp) => {
            if (!acc[exp.date]) {
                acc[exp.date] = 0;
            }
            acc[exp.date] += exp.mileage;
            return acc;
        }, {});

        const sortedDates = Object.keys(groupedByDate).sort();
        const experienceLabels = sortedDates;

        const cumulativeDistances = sortedDates.reduce((acc, date) => {
            const lastValue = acc.length > 0 ? acc[acc.length - 1] : 0;
            acc.push(lastValue + groupedByDate[date]);
            return acc;
        }, []);

        new Chart(experiencesCtx, {
            type: "line",
            data: {
                labels: experienceLabels,
                datasets: [
                    {
                        label: "Cumulative Distance (km)",
                        data: cumulativeDistances,
                        borderColor: "#36A2EB",
                        fill: false,
                        tension: 0.1,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: "top" },
                },
                scales: {
                    x: { title: { display: true, text: "Driving Date" } },
                    y: { beginAtZero: true, title: { display: true, text: "Cumulative Distance (km)" } },
                },
            },
        });
    }

    function initializeDataTable() {
        const experiences = getFromLocalStorage();

        const tableBody = document.querySelector("#experiencesTable tbody");
        tableBody.innerHTML = "";

        experiences.forEach((exp) => {
            const row = `
                <tr>
                    <td>${exp.id}</td>
                    <td>${exp.date}</td>
                    <td>${exp.startTime}</td>
                    <td>${exp.endTime}</td>
                    <td>${exp.mileage} km</td>
                    <td>${exp.weather}</td>
                    <td>${exp.roadCondition}</td>
                    <td>
                        <button class="delete-btn" data-id="${exp.id}">🗑️ Delete</button>
                    </td>
                </tr>
            `;
            tableBody.insertAdjacentHTML("beforeend", row);
        });

        $("#experiencesTable").DataTable({
            pageLength: 10,
            lengthMenu: [5, 10, 20],
            responsive: true,
            autoWidth: false,
        });
        tableBody.addEventListener("click", (event) => {
            if (event.target.classList.contains("delete-btn")) {
                const experienceId = event.target.getAttribute("data-id");
                deleteExperience(experienceId);

                dataTable.row($(event.target).closest("tr")).remove().draw();
            }
        });
    }
    function deleteExperience(id) {
        const experiences = getFromLocalStorage();
        const updatedExperiences = experiences.filter((exp) => exp.id !== parseInt(id));
        localStorage.setItem("drivingExperiences", JSON.stringify(updatedExperiences));
        alert(`Experience with ID ${id} has been deleted.`);

        window.location.reload();
    }

    displaySummary();
    initializeDataTable();
});
