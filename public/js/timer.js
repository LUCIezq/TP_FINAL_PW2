const tiempo_limite = Number(document.getElementById('tiempo_limite').value);
let tiempo = Number(document.getElementById('tiempo_restante').value);
if (isNaN(tiempo) || tiempo < 0) tiempo = 0;

let botones = Array.from(document.querySelectorAll(".botones-respuesta"));

const duracion = tiempo_limite;
const tiempoDiv = document.getElementById("timer");
const barraTiempo = document.getElementById("barra-tiempo");
const mensajeDiv = document.getElementById("mensaje");
let intervalo;


function actualizarTimer() {
    tiempoDiv.innerText = tiempo + "s";
    let porcentaje = (tiempo / duracion) * 100;
    barraTiempo.style.width = porcentaje + "%";
    barraTiempo.style.backgroundColor = (tiempo <= 3) ? 'red' : (tiempo <= 7) ? 'orange' : 'green';

    if (tiempo <= 0) {
        barraTiempo.style.width = "0%";
        tiempoDiv.innerText = "⏳ Tiempo agotado";
        desactivarBotones();
        clearInterval(intervalo);
        procesarRespuestaUsuario(null);
        return;
    }
    tiempo--;
}

intervalo = setInterval(actualizarTimer, 1000);
actualizarTimer();


function desactivarBotones() {
    botones.forEach(b => b.disabled = true);
}

async function procesarRespuestaUsuario(respuesta_id = null) {
    desactivarBotones();
    clearInterval(intervalo);

    const pregunta_id = document.getElementById('pregunta_id').value;

    try {
        const response = await fetch('/game/responder', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                pregunta_id,
                respuesta_id
            })
        });

        if (!response.ok) throw new Error('Error en la petición');

        const result = await response.json();

        pintarRespuestas(result.correcta, respuesta_id);

        if (result.status === 'time_out' || result.status === 'incorrecta' || result.status === 'finalizada' || result.status === 'no_more_questions') {
            mensajeDiv.innerText = result.mensaje || "Fin de la partida.";
            setTimeout(() => window.location.href = result.redirect || '/home/index', 2000);

        } else if (result.status === 'correcta') {
            mensajeDiv.innerText = "✅ Respuesta correcta!";
            setTimeout(() => actualizarPregunta(result.siguiente_pregunta), 500)
        }

    } catch (error) {
        console.error("Error crítico:", error);
        setTimeout(() => window.location.href = '/home/index', 2000);
    }
}

function pintarRespuestas(idCorrecta, respuestaUsuario = null) {
    const idCorrectaNum = Number(idCorrecta);

    console.log(idCorrectaNum, respuestaUsuario);

    botones.forEach(b => {
        const id = Number(b.dataset.id);

        if (id === idCorrectaNum) {
            b.style.backgroundColor = "green";
            b.style.color = "white";
            b.style.opacity = 1.0;

        }
        else if (respuestaUsuario !== null && id === Number(respuestaUsuario)) {
            b.style.backgroundColor = "red";
            b.style.color = "white";
            b.style.opacity = 1.0;
        }
        else {
            b.style.opacity = 0.4;
        }
    });
}

function actualizarPregunta(pregunta) {
    document.getElementById('pregunta_id').value = pregunta.id;

    const preguntaTexto = document.getElementById('preguntaTexto');

    preguntaTexto.innerText = pregunta.texto;

    const container = document.getElementById('respuestas-container');
    container.innerHTML = pregunta.respuestas.map(r => `
        <button type="button" class="btn btn-outline-primary btn-lg opcion-respuesta botones-respuesta" data-id="${r.id}">
            ${r.texto}
        </button>
    `).join('');

    mensajeDiv.innerText = "";

    tiempo = duracion;
    clearInterval(intervalo);
    intervalo = setInterval(actualizarTimer, 1000);

    botones = Array.from(document.querySelectorAll(".botones-respuesta"));

    botones.forEach(boton => {
        boton.addEventListener('click', () => procesarRespuestaUsuario(boton.dataset.id));
    });
}

botones.forEach(boton => {
    boton.addEventListener('click', () => procesarRespuestaUsuario(parseInt(boton.dataset.id)))
})
