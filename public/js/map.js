// ===============================
//   Solo ejecutar si existe #map
// ===============================
if (document.getElementById("map")) {

    // Crear el mapa centrado en Buenos Aires
    var map = L.map('map').setView([-34.579, -58.381], 7);

    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

    let marker = null;

    // Evento: clic en el mapa
    map.on('click', async function (e) {
        const lat = e.latlng.lat;
        const lon = e.latlng.lng;

        // Crear o mover marcador
        if (marker) {
            marker.setLatLng(e.latlng);
        } else {
            marker = L.marker([lat, lon]).addTo(map);
        }

        // Obtener país y ciudad desde API propia
        const data = await getLocation(lat, lon);

        const paisInput = document.getElementById('pais');
        const ciudadInput = document.getElementById('ciudad');

        if (data && data.address) {
            paisInput.value = data.address.country || 'Desconocido';
            ciudadInput.value =
                data.address.city ||
                data.address.town ||
                data.address.village ||
                data.address.state ||
                'Desconocida';
        }
    });

    // ===============================
    //   Función: obtener ubicación
    // ===============================
    const getLocation = async (lat, lon) => {

        if (typeof lat !== 'number' || typeof lon !== 'number') {
            throw new Error("Las latitudes y longitudes deben ser números");
        }

        const baseUrl = "/service/get_location.php";
        const params = new URLSearchParams({
            lat: String(lat),
            lon: String(lon),
        });

        try {
            const response = await fetch(`${baseUrl}?${params.toString()}`);

            if (!response.ok) {
                throw new Error("HTTP error " + response.status);
            }

            return await response.json();

        } catch (error) {
            console.error("Error al obtener los datos de la API:", error);
            return null;
        }
    };

} // <-- FIN del IF que evita errores en vistas sin mapa