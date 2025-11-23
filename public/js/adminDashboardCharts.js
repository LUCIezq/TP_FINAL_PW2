
 // UTILIDAD: verificar si un array tiene datos

function hasData(arr) {
    return Array.isArray(arr) && arr.length > 0;
}


//  DATOS RECIBIDOS DESDE PHP
const dataPais = window.dataPais || [];
const dataSexo = window.dataSexo || [];
const dataEdad = window.dataEdad || [];
const dataPrecision = window.dataPrecision || [];

//   GRÁFICO: USUARIOS POR PAÍS

if (hasData(dataPais)) {
    new Chart(document.getElementById("chartPais"), {
        type: 'bar',
        data: {
            labels: dataPais.map(i => i.pais),
            datasets: [{
                label: "Usuarios",
                data: dataPais.map(i => i.cantidad),
                backgroundColor: '#1fb286'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false }
            }
        }
    });
} else {
    document.getElementById("chartPais").parentElement.innerHTML =
        "<p class='nodata'>No hay datos disponibles</p>";
}




//   GRÁFICO: SEXO


if (hasData(dataSexo)) {
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
} else {
    document.getElementById("chartSexo").parentElement.innerHTML =
        "<p class='nodata'>No hay datos disponibles</p>";
}



//   GRÁFICO: EDAD

if (hasData(dataEdad)) {
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
} else {
    document.getElementById("chartEdad").parentElement.innerHTML =
        "<p class='nodata'>No hay datos disponibles</p>";
}




//   GRÁFICO: PRECISIÓN / PORCENTAJE

if (hasData(dataPrecision)) {
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
} else {
    document.getElementById("chartPrecision").parentElement.innerHTML =
        "<p class='nodata'>No hay datos disponibles</p>";
}
