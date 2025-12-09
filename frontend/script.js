document.addEventListener("DOMContentLoaded", () => {

    function populateDropdown(selectId, options) {
        const select = document.getElementById(selectId);
        select.innerHTML = '<option value="">--- Select ---</option>';
        options.forEach((opt) => {
            const el = document.createElement("option");
            el.value = opt.id;
            el.textContent = opt.label;
            select.appendChild(el);
        });
    }

    async function initDrivingForm() {
        try {
            const response = await fetch('../backend/get_options.php');
            const data = await response.json();

            if (data.success) {
                populateDropdown("weather", data.weatherOptions);
                populateDropdown("roadCondition", data.roadConditionOptions);
                populateDropdown("visibility", data.visibilityOptions);
                populateDropdown("traffic", data.trafficOptions);
            } else {
                alert("Error loading options: " + data.message);
            }
        } catch (error) {
            console.error(error);
        }
    }

    initDrivingForm();
    displaySavedExperiences();

    // --- 2. Form Submit ---
    const form = document.getElementById("drivingExperienceForm");

    form.addEventListener("submit", async (event) => {
        event.preventDefault();

        // Elementləri alırıq
        const date = document.getElementById("date").value;
        const startTime = document.getElementById("startTime").value;
        const endTime = document.getElementById("endTime").value;
        const mileage = parseFloat(document.getElementById("mileage").value);

        const weatherId = document.getElementById("weather").value;
        const roadId = document.getElementById("roadCondition").value;
        const visibilityId = document.getElementById("visibility").value; // YENİ
        const trafficId = document.getElementById("traffic").value;       // YENİ

        if (!date || !startTime || !endTime || isNaN(mileage) ||
            !weatherId || !roadId || !visibilityId || !trafficId) {
            alert("Please fill out all fields!");
            return;
        }

        const newExperience = {
            date, startTime, endTime, mileage,
            weatherId, roadTypeId: roadId, visibilityId, trafficId
        };

        const saved = await saveDrivingExperience(newExperience);

        if (saved) {
            saveToLocalStorage({
                id: "DB-" + saved,
                date: date,
                startTime: startTime,
                endTime: endTime,
                mileage: mileage,
                // ID yox, seçilmiş mətni saxlayırıq ki, gözəl görünsün
                weather: getSelectedText("weather"),
                roadCondition: getSelectedText("roadCondition"),
                visibility: getSelectedText("visibility"),
                traffic: getSelectedText("traffic")
            });
            form.reset();
            displaySavedExperiences();
        }
    });

    // Köməkçi: Dropdown-da seçilən mətni tapmaq üçün
    function getSelectedText(id) {
        const sel = document.getElementById(id);
        return sel.options[sel.selectedIndex].text;
    }

    // --- 3. Backendə Göndər ---
    async function saveDrivingExperience(data) {
        const payload = {
            startDateTime: moment(`${data.date}T${data.startTime}`).format("YYYY-MM-DD HH:mm:ss"),
            endDateTime: moment(`${data.date}T${data.endTime}`).format("YYYY-MM-DD HH:mm:ss"),
            mileage: data.mileage,
            weatherId: data.weatherId,
            roadTypeId: data.roadTypeId,
            visibilityId: data.visibilityId, // YENİ
            trafficId: data.trafficId        // YENİ
        };

        try {
            const response = await fetch('../backend/save_session.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload),
            });

            const responseText = await response.text();
            console.log("Response:", responseText); // Debug üçün

            const result = JSON.parse(responseText);
            if (result.success) {
                alert(`Saved! Session ID: ${result.session_id}`);
                return result.session_id;
            } else {
                alert(`Server Error: ${result.message}`);
                return false;
            }
        } catch (e) {
            alert("Connection error.");
            return false;
        }
    }

    // --- 4. Digər funksiyalar (Local Storage və Geolocation) ---
    // Bunları olduğu kimi saxlayırsan
    function getFromLocalStorage() {
        return JSON.parse(localStorage.getItem("drivingExperiences")) || [];
    }
    function saveToLocalStorage(data) {
        const exps = getFromLocalStorage();
        exps.push(data);
        localStorage.setItem("drivingExperiences", JSON.stringify(exps));
    }
    function displaySavedExperiences() {
        const list = document.getElementById("savedExperiences");
        const exps = getFromLocalStorage();
        list.innerHTML = "";
        exps.forEach(e => {
            const div = document.createElement("div");
            div.className = "experience";
            div.innerHTML = `<b>${e.date}</b> (${e.startTime}-${e.endTime}) - ${e.mileage}km<br>
                             Weather: ${e.weather}, Road: ${e.roadCondition}<br>
                             Vis: ${e.visibility}, Traffic: ${e.traffic}<hr>`;
            list.appendChild(div);
        });
    }

    document.getElementById("resetButton").onclick = () => {
        localStorage.removeItem("drivingExperiences");
        displaySavedExperiences();
    };

    const startDrivingButton = document.getElementById("startDrivingButton");
    const stopDrivingButton = document.getElementById("stopDrivingButton");
    const trackingStatus = document.getElementById("trackingStatus");
    let watchId = null;
    let startLocation = null;
    let startTimeGeo, distanceTraveled = 0;

    startDrivingButton.addEventListener("click", () => {
        if (!navigator.geolocation) return alert("No Geo supported");
        trackingStatus.textContent = "Tracking...";
        startDrivingButton.style.display = "none";
        stopDrivingButton.style.display = "inline";
        startTimeGeo = new Date();
        distanceTraveled = 0;
        watchId = navigator.geolocation.watchPosition(pos => {
            const {latitude, longitude} = pos.coords;
            if(!startLocation) startLocation = {latitude, longitude};
            else {
                distanceTraveled += calculateDistance(startLocation.latitude, startLocation.longitude, latitude, longitude);
                startLocation = {latitude, longitude};
            }
        }, err => alert(err.message));
    });

    stopDrivingButton.addEventListener("click", () => {
        navigator.geolocation.clearWatch(watchId);
        trackingStatus.textContent = "Stopped.";
        startDrivingButton.style.display = "inline";
        stopDrivingButton.style.display = "none";
        document.getElementById("date").value = moment(startTimeGeo).format("YYYY-MM-DD");
        document.getElementById("startTime").value = moment(startTimeGeo).format("HH:mm");
        document.getElementById("endTime").value = moment(new Date()).format("HH:mm");
        document.getElementById("mileage").value = distanceTraveled.toFixed(2);
    });

    function calculateDistance(lat1, lon1, lat2, lon2) {
        const R = 6371;
        const dLat = ((lat2 - lat1) * Math.PI) / 180;
        const dLon = ((lon2 - lon1) * Math.PI) / 180;
        const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) + Math.cos((lat1 * Math.PI) / 180) * Math.cos((lat2 * Math.PI) / 180) * Math.sin(dLon / 2) * Math.sin(dLon / 2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        return R * c;
    }
});