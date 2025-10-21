var map = L.map('map').setView([-34.579, -58.381], 7);
L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

map.on('click', async function (e) {
    var lat = e.latlng.lat;
    var lon = e.latlng.lng;

    if (typeof marker !== 'undefined') {
        marker.setLatLng(e.latlng);
    } else {
        marker = L.marker(e.latlng).addTo(map);
    }
    console.log(await getLocation(lat, lon));
});

const getLocation = async (lat, lon) => {

    const baseUrl = "http://localhost/services/proxy_nominatim.php";

    if (typeof lat !== 'number' || typeof lon !== 'number') {
        throw new Error("Las latitudes y longitudes deben ser números");
    }

    const params = new URLSearchParams({
        lat: lat,
        lon: lon
    });

    try {
        const response = await fetch(`${baseUrl}?${params.toString()}`);

        if (!response.ok) {
            throw new Error("HTTP error " + response.status);
        }

        const data = await response.json();
        console.log(data);
        return data;

    } catch (error) {
        console.error("Error al obtener los datos de la API:", error);
    }
}