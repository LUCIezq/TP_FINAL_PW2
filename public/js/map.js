const url = "https://nominatim.openstreetmap.org/reverse?";

var map = L.map('map').setView([-34.579, -58.381], 7);
L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

map.on('click', function (e) {
    var lat = e.latlng.lat;
    var lon = e.latlng.lng;

    if (typeof marker !== 'undefined') {
        marker.setLatLng(e.latlng);
    } else {
        marker = L.marker(e.latlng).addTo(map);
    }
});

const getLocation = async (lat, lon) => {
    try {
        const response = await fetch(url + `lat=${lat}&lon=${lon}&format=json`);

        if (!response.ok) {
            throw new Error("HTTP error " + response.status);
        }
        const data = await response.json();
        return data;
    } catch (error) {
        console.error("Error fetching location:", error);
    }
}