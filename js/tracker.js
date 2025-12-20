let watchId = null;
let routePoints = [];
let totalDistance = 0;
let lastLat = null;
let lastLng = null;

const startBtn = document.getElementById('start-btn');
const stopBtn = document.getElementById('stop-btn');
const statusDisplay = document.getElementById('status-display');
const distanceDisplay = document.getElementById('distance-display');
const routeInput = document.getElementById('route-points');
const distanceInput = document.getElementById('distance-input');
const startTimeInput = document.getElementById('start-time');
const endTimeInput = document.getElementById('end-time');

// Haversine Formula for distance (in km)
function calculateDistance(lat1, lon1, lat2, lon2) {
    const R = 6371; // Radius of the earth in km
    const dLat = deg2rad(lat2 - lat1);
    const dLon = deg2rad(lon2 - lon1);
    const a =
        Math.sin(dLat / 2) * Math.sin(dLat / 2) +
        Math.cos(deg2rad(lat1)) * Math.cos(deg2rad(lat2)) *
        Math.sin(dLon / 2) * Math.sin(dLon / 2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    return R * c;
}

function deg2rad(deg) {
    return deg * (Math.PI / 180);
}

startBtn.addEventListener('click', () => {
    if (!navigator.geolocation) {
        alert("Geolocation is not supported by your browser.");
        return;
    }

    // Reset
    routePoints = [];
    totalDistance = 0;
    lastLat = null;
    lastLng = null;

    // UI Update
    startBtn.style.display = 'none';
    stopBtn.style.display = 'inline-block';
    statusDisplay.textContent = "Tracking Active...";
    statusDisplay.style.color = "#4ade80"; // Green

    // Set Start Time
    const now = new Date();
    // Format YYYY-MM-DD HH:MM:SS for MySQL
    const formatted = now.toISOString().slice(0, 19).replace('T', ' ');
    startTimeInput.value = formatted;

    watchId = navigator.geolocation.watchPosition((position) => {
        const { latitude, longitude } = position.coords;
        const timestamp = new Date().toISOString().slice(0, 19).replace('T', ' ');

        // Add point
        routePoints.push({ lat: latitude, lng: longitude, timestamp: timestamp });

        // Calculate distance if we have a previous point
        if (lastLat !== null) {
            const dist = calculateDistance(lastLat, lastLng, latitude, longitude);
            totalDistance += dist;
            distanceDisplay.textContent = totalDistance.toFixed(2) + " km";
        }

        lastLat = latitude;
        lastLng = longitude;

        console.log("Point recorded:", latitude, longitude);

    }, (error) => {
        console.error("Error watching position:", error);
        statusDisplay.textContent = "GPS Error: " + error.message;
    }, {
        enableHighAccuracy: true,
        timeout: 5000,
        maximumAge: 0
    });
});

stopBtn.addEventListener('click', () => {
    if (watchId !== null) {
        navigator.geolocation.clearWatch(watchId);
        watchId = null;
    }

    // UI Update
    startBtn.style.display = 'inline-block';
    stopBtn.style.display = 'none';
    statusDisplay.textContent = "Tracking Stopped";
    statusDisplay.style.color = "#ef4444"; // Red
    startBtn.textContent = "Restart Tracking"; // Change text to Restart

    // Set End Time
    const now = new Date();
    const formatted = now.toISOString().slice(0, 19).replace('T', ' ');
    endTimeInput.value = formatted;

    // Populate Hidden Inputs
    routeInput.value = JSON.stringify(routePoints);
    distanceInput.value = totalDistance.toFixed(2);
});
