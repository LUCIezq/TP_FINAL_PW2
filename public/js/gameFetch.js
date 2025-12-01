
const tiempoLimite = Number(document.getElementById('tiempo_limite').value);
let tiempoRestante = Number(document.getElementById('tiempo_restante').value);
if (isNaN(tiempoRestante) || tiempoRestante < 0) tiempoRestante = 0;

const tiempoDiv = document.getElementById("timer");
const barraTiempo = document.getElementById("barra-tiempo");
let botonesRespuestas = document.querySelectorAll(".botones-respuesta");

let intervalo;

// Actualiza la barra y colores
function actualizarBarra() {
    tiempoDiv.innerText = tiempoRestante + "s";
    const porcentaje = (tiempoRestante / tiempoLimite) * 100;
    barraTiempo.style.width = porcentaje + "%";

    if (tiempoRestante <= 3) barraTiempo.style.backgroundColor = "red";
    else if (tiempoRestante <= 7) barraTiempo.style.backgroundColor = "orange";
    else barraTiempo.style.backgroundColor = "green";
}

// Manejo cuando se responde
async function enviarRespuesta(respuestaId = null) {
    clearInterval(intervalo);
    const preguntaId = document.getElementById('pregunta_id').value;

    try {
        const response = await fetch('/game/responder', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                pregunta_id: preguntaId,
                ...(respuestaId !== null && { respuesta_id: respuestaId })
            })
        });

        if (!response.ok) throw new Error('Error en la petición');

        const data = await response.json();
        const idCorrecta = data.correcta.id || data.correcta;

        // Pintar botones
        botonesRespuestas.forEach(b => {
            const id = parseInt(b.value);
            if (id === idCorrecta) {
                b.style.backgroundColor = "green";
                b.style.color = "white";
            } else if (id === respuestaId) {
                b.style.backgroundColor = "red";
                b.style.color = "white";
            } else {
                b.style.opacity = 0.6;
            }
            b.disabled = true;
        });

        // Redirigir si la partida terminó
        if (data.status === "time_out" || data.status === "incorrecta" || data.status === "no_more_questions") {
            setTimeout(() => window.location.href = data.redirect || '/home/index', 3000);
            return;
        }

        // Actualizar siguiente pregunta si fue correcta
        if (data.status === "correcta") {
            actualizarPregunta(data.siguiente_pregunta, data.correcta.id);
        }

    } catch (err) {
        console.error("Error al procesar la respuesta:", err);
        setTimeout(() => window.location.href = '/home/index', 3000);
    }
}

// Manejo del timeout
function timeoutHandler() {
    tiempoDiv.innerText = "⏳ Tiempo agotado";
    botonesRespuestas.forEach(b => b.disabled = true);
    enviarRespuesta(); // Sin id => backend interpreta como timeout
}

// Inicializa timer
function startTimer() {
    actualizarBarra();
    intervalo = setInterval(() => {
        tiempoRestante--;
        actualizarBarra();
        if (tiempoRestante <= 0) {
            clearInterval(intervalo);
            timeoutHandler();
        }
    }, 1000);
}

// Actualiza la pregunta en pantalla (recrea los botones)
function actualizarPregunta(pregunta, correctaId) {
    document.getElementById('pregunta_id').value = pregunta.id;

    const contenedor = document.querySelector(".d-grid.gap-3");
    contenedor.innerHTML = pregunta.respuestas.map(r => `
        <button type="button" class="btn btn-outline-primary btn-lg opcion-respuesta botones-respuesta" value="${r.id}">
            ${r.texto}
        </button>
    `).join("");

    botonesRespuestas = document.querySelectorAll(".botones-respuesta");
    botonesRespuestas.forEach(b => b.addEventListener("click", botonClickHandler));

    tiempoRestante = tiempoLimite;
    startTimer();
}

// Handler para clicks en respuestas
function botonClickHandler(e) {
    const respuestaId = parseInt(e.currentTarget.value);
    enviarRespuesta(respuestaId);
}

// Inicializa todo
botonesRespuestas.forEach(b => b.addEventListener("click", botonClickHandler));
startTimer();
