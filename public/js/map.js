var map = L.map('map').setView([-34.579, -58.381], 7);

L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

let marker = L.marker([-34.579, -58.381]).addTo(map);

map.on('click', async function (e) {

    var lat = e.latlng.lat;
    var lon = e.latlng.lng;


    if (marker) {
        marker.setLatLng(e.latlng);
    }

    const data = await getLocation(lat, lon);

    const paisInput = document.getElementById('pais');
    const ciudadInput = document.getElementById('ciudad');

    if (data) {
        paisInput.value = data.address.country || 'Desconocido';
        ciudadInput.value = data.address.state || 'Desconocida';
    }
});

const getLocation = async (lat, lon) => {

    const baseUrl = "https://nominatim.openstreetmap.org/reverse";


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

