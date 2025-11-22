const dataPais = window.dataPais || [];
const dataSexo = window.dataSexo || [];
const dataEdad = window.dataEdad || [];
const dataPrecision = window.dataPrecision || [];

// ----------- GRÁFICO PAÍS -----------
new Chart(document.getElementById("chartPais"), {
    type: 'bar',
    data: {
        labels: dataPais.map(i => i.pais),
        datasets: [{
            label: "Usuarios",
            data: dataPais.map(i => i.cantidad),
            backgroundColor: '#1fb286'
        }]
    }
});

// ----------- GRÁFICO SEXO -----------
new Chart(document.getElementById("chartSexo"), {
    type: 'pie',
    data: {
        labels: dataSexo.map(i => i.sexo),
        datasets: [{
            data: dataSexo.map(i => i.cantidad),
            backgroundColor: ["#1fb286", "#5a4ee3", "#f6c344"]
        }]
    }
});

// ----------- GRÁFICO EDAD -----------
new Chart(document.getElementById("chartEdad"), {
    type: 'bar',
    data: {
        labels: dataEdad.map(i => i.grupo),
        datasets: [{
            label: "Usuarios",
            data: dataEdad.map(i => i.cantidad),
            backgroundColor: "#5a4ee3"
        }]
    }
});

// ----------- GRÁFICO PRECISIÓN -----------
new Chart(document.getElementById("chartPrecision"), {
    type: 'line',
    data: {
        labels: dataPrecision.map(i => i.nombre_usuario),
        datasets: [{
            label: "% correctas",
            data: dataPrecision.map(i => i.porcentaje),
            borderColor: '#1fb286',
            borderWidth: 3,
            fill: false,
            tension: 0.3
        }]
    }
});