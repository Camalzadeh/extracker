

let watchID = null;
let totalDistance = 0;
let routePoints = [];
let lastLat = null;
let lastLon = null;
let startTime = null;

$(document).ready(function () {

    $('#start-btn').on('click', function (e) {
        e.preventDefault();
        console.log('Start button clicked');

        if (!navigator.geolocation) {
            showStatus("Geolocation is not supported by your browser.", "red");
            return;
        }

        totalDistance = 0;
        routePoints = [];
        lastLat = null;
        lastLon = null;
        startTime = new Date().toISOString();
        $('#start-time').val(startTime);

        $(this).hide();
        $('#stop-btn').show();
        showStatus('Thinking...', '#ffc107');

        watchID = navigator.geolocation.watchPosition(updatePosition, handleError, {
            enableHighAccuracy: true,
            timeout: 20000,
            maximumAge: 0
        });
        console.log('WatchPosition initialized', watchID);
    });

    $('#stop-btn').click(function () {
        if (watchID !== null) {
            navigator.geolocation.clearWatch(watchID);
            watchID = null;
        }

        if (totalDistance <= 0 && routePoints.length === 0) {
            showStatus('Error: No location data recorded. Cannot save empty trip.', 'red');

            $('#stop-btn').hide().text('Stop & Save').prop('disabled', false);
            $('#start-btn').show();
            return;
        }

        let endTime = new Date().toISOString();
        $('#end-time').val(endTime);
        $('#distance-input').val(totalDistance.toFixed(2));
        $('#route-points').val(JSON.stringify(routePoints));

        showStatus('Trip Stopped. Saving...', '#dc3545');
        $('#stop-btn').text('Saved').prop('disabled', true);

        $('#live-form').submit();
    });
});

function updatePosition(position) {
    let lat = position.coords.latitude;
    let lon = position.coords.longitude;
    let timestamp = new Date().toISOString();

    if (lastLat !== null && lastLon !== null) {
        let dist = calculateDistance(lastLat, lastLon, lat, lon);

        if (dist > 0.005) {
            totalDistance += dist;
            $('#distance-display').text(totalDistance.toFixed(2) + ' km');
        }
    }

    lastLat = lat;
    lastLon = lon;

    routePoints.push({
        lat: lat,
        lng: lon,
        time: timestamp
    });

    showStatus('Tracking active: ' + routePoints.length + ' points', '#28a745');
}

function handleError(error) {
    let msg = "Location error.";
    switch (error.code) {
        case error.PERMISSION_DENIED:
            msg = "Location permission denied. Please enable GPS.";
            break;
        case error.POSITION_UNAVAILABLE:
            msg = "Location information is unavailable.";
            break;
        case error.TIMEOUT:
            msg = "The request to get user location timed out.";
            break;
        case error.UNKNOWN_ERROR:
            msg = "An unknown error occurred.";
            break;
    }
    console.warn('Geo Error: ' + msg);
    showStatus(msg + " (Tracking stopped)", "red");

    if (watchID !== null) {
        navigator.geolocation.clearWatch(watchID);
        watchID = null;
        $('#start-btn').show();
        $('#stop-btn').hide();
    }
}

function calculateDistance(lat1, lon1, lat2, lon2) {
    const R = 6371;
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;
    const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
        Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
        Math.sin(dLon / 2) * Math.sin(dLon / 2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    return R * c;
}

function showStatus(text, color) {
    $('#status-display').text(text).css('color', color);
}
