
const url = "http://localhost/usuario/getCountryAndCity";

const fetchData = async () => {
    try {

        const response = await fetch(url);

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();

        setMap(data);

    } catch (e) {
        console.log('There was a problem with the fetch operation: ' + e.message);
    }
}



const setMap = async (data) => {
    const baseUrl = "http://localhost/service/get_location_reverse.php";

    const params = new URLSearchParams({
        city: data.ciudad,
        country: data.pais
    });

    try {

        const response = await fetch(
            `${baseUrl}?${params.toString()}&format=json&limit=1`,
        );

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const locationData = await response.json();

        const lat = parseFloat(locationData[0].lat);
        const lon = parseFloat(locationData[0].lon);

        map.setView([lat, lon], 7);

        L.marker([lat, lon]).addTo(map);

        map.off('click');

    } catch (e) {
        console.log('There was a problem with the fetch operation: ' + e.message);
    }
}

fetchData();