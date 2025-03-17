const drivingData = {
    weatherOptions: [
        { id: "sunny", label: "Sunny" },
        { id: "rainy", label: "Rainy" },
        { id: "cloudy", label: "Cloudy" },
        { id: "snowy", label: "Snowy" },
        { id: "windy", label: "Windy" },
    ],
    roadConditionOptions: [
        { id: "dry", label: "Dry" },
        { id: "wet", label: "Wet" },
        { id: "icy", label: "Icy" },
        { id: "snow", label: "Snow" },
    ],
};

document.addEventListener("DOMContentLoaded", () => {
    function populateDropdown(selectId, options) {
        const select = document.getElementById(selectId);
        options.forEach((option) => {
            const opt = document.createElement("option");
            opt.value = option.id;
            opt.textContent = option.label;
            select.appendChild(opt);
        });
    }

    populateDropdown("weather", drivingData.weatherOptions);
    populateDropdown("roadCondition", drivingData.roadConditionOptions);

    displaySavedExperiences();

    const form = document.getElementById("drivingExperienceForm");
    form.addEventListener("submit", (event) => {
        event.preventDefault();

        const date = document.getElementById("date").value;
        const startTime = document.getElementById("startTime").value;
        const endTime = document.getElementById("endTime").value;
        const mileage = parseFloat(document.getElementById("mileage").value);
        const weather = document.getElementById("weather").value;
        const roadCondition = document.getElementById("roadCondition").value;

        if (!moment(date, "YYYY-MM-DD", true).isValid()) {
            alert("Please enter a valid date (YYYY-MM-DD)!");
            return;
        }

        if (!moment(startTime, "HH:mm", true).isValid() || !moment(endTime, "HH:mm", true).isValid()) {
            alert("Please enter valid times (HH:mm)!");
            return;
        }

        if (!date || !startTime || !endTime || isNaN(mileage) || !weather || !roadCondition) {
            alert("Please fill out all fields!");
            return;
        }

        const startMoment = moment(`${date}T${startTime}`);
        const endMoment = moment(`${date}T${endTime}`);
        if (!endMoment.isAfter(startMoment)) {
            alert("End time must be after start time!");
            return;
        }

        const newExperience = {
            id: generateNewId(),
            date: moment(date).format("YYYY-MM-DD"),
            startTime: moment(startTime, "HH:mm").format("hh:mm A"),
            endTime: moment(endTime, "HH:mm").format("hh:mm A"),
            mileage,
            weather,
            roadCondition,
        };

        saveToLocalStorage(newExperience);

        form.reset();

        displaySavedExperiences();
        alert("Driving experience saved successfully!");
    });

    function generateNewId() {
        const experiences = getFromLocalStorage();
        return experiences.length > 0 ? experiences[experiences.length - 1].id + 1 : 1;
    }

    function saveToLocalStorage(data) {
        const experiences = getFromLocalStorage();
        experiences.push(data);
        localStorage.setItem("drivingExperiences", JSON.stringify(experiences));
    }

    function getFromLocalStorage() {
        return JSON.parse(localStorage.getItem("drivingExperiences")) || [];
    }


    const startDrivingButton = document.getElementById("startDrivingButton");
    const stopDrivingButton = document.getElementById("stopDrivingButton");
    const trackingStatus = document.getElementById("trackingStatus");

    startDrivingButton.addEventListener("click", () => {
        if (!navigator.geolocation) {
            alert("Geolocation is not supported by your browser.");
            return;
        }

        trackingStatus.textContent = "Tracking started...";
        startDrivingButton.style.display = "none";
        stopDrivingButton.style.display = "inline";
        startTime = new Date();
        distanceTraveled = 0;

        watchId = navigator.geolocation.watchPosition(
            (position) => {
                const { latitude, longitude } = position.coords;

                if (!startLocation) {
                    startLocation = { latitude, longitude };
                } else {
                    distanceTraveled += calculateDistance(
                        startLocation.latitude,
                        startLocation.longitude,
                        latitude,
                        longitude
                    );
                    startLocation = { latitude, longitude };
                }
            },
            (error) => {
                alert("Error occurred while tracking location: " + error.message);
            },
            {
                enableHighAccuracy: true,
                maximumAge: 0,
                timeout: 5000,
            }
        );
    });

    stopDrivingButton.addEventListener("click", () => {
        if (watchId !== null) {
            navigator.geolocation.clearWatch(watchId);
            watchId = null;
        }

        endTime = new Date();
        trackingStatus.textContent = "Tracking stopped.";
        startDrivingButton.style.display = "inline";
        stopDrivingButton.style.display = "none";

        // Update the form fields with tracked data
        document.getElementById("date").value = moment(startTime).format("YYYY-MM-DD");
        document.getElementById("startTime").value = moment(startTime).format("HH:mm");
        document.getElementById("endTime").value = moment(endTime).format("HH:mm");
        document.getElementById("mileage").value = distanceTraveled.toFixed(2);

        alert("Tracking completed! Fill in the remaining details to save.");
    });

    function calculateDistance(lat1, lon1, lat2, lon2) {
        const R = 6371;
        const dLat = ((lat2 - lat1) * Math.PI) / 180;
        const dLon = ((lon2 - lon1) * Math.PI) / 180;
        const a =
            Math.sin(dLat / 2) * Math.sin(dLat / 2) +
            Math.cos((lat1 * Math.PI) / 180) *
            Math.cos((lat2 * Math.PI) / 180) *
            Math.sin(dLon / 2) *
            Math.sin(dLon / 2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        return R * c;
    }





    function displaySavedExperiences() {
        const experiences = getFromLocalStorage();
        const displayContainer = document.getElementById("savedExperiences");
        displayContainer.innerHTML = "";

        if (experiences.length === 0) {
            displayContainer.innerHTML = "<p>No driving experiences saved yet.</p>";
            return;
        }

        experiences.forEach((exp, index) => {
            if (index < 2) {
                const experienceDiv = document.createElement("div");
                experienceDiv.classList.add("experience");
                experienceDiv.innerHTML = `
                    <p><strong>ID:</strong> ${exp.id}</p>
                    <p><strong>Date:</strong> ${exp.date}</p>
                    <p><strong>Start Time:</strong> ${exp.startTime}</p>
                    <p><strong>End Time:</strong> ${exp.endTime}</p>
                    <p><strong>Mileage:</strong> ${exp.mileage} km</p>
                    <p><strong>Weather:</strong> ${exp.weather}</p>
                    <p><strong>Road Condition:</strong> ${exp.roadCondition}</p>
                    <hr>
                `;
                displayContainer.appendChild(experienceDiv);
            }
        });

        if (experiences.length > 2) {
            const moreExperiencesDiv = document.createElement("div");
            moreExperiencesDiv.classList.add("more-experiences");
            moreExperiencesDiv.innerHTML = `
                <p>... and ${experiences.length - 2} more experience(s)</p>
            `;
            displayContainer.appendChild(moreExperiencesDiv);
        }
    }

    const resetButton = document.getElementById("resetButton");
    resetButton.addEventListener("click", () => {
        localStorage.removeItem("drivingExperiences");

        const displayContainer = document.getElementById("savedExperiences");
        displayContainer.innerHTML = "<p>No driving experiences saved yet.</p>";

        document.getElementById("totalDistance").textContent = 0;
        document.getElementById("experienceCount").textContent = 0;

        alert("All data has been reset!");
    });
});



