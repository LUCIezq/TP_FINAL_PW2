
const canvas = document.getElementById("ruletaCanvas");
const ctx = canvas.getContext("2d");
const btn = document.getElementById("btnGirar");
const resultado = document.getElementById("resultado");

const generosFetch = async () => {
    try {
        const response = await fetch('/genero/listarGenerosJSON');

        if (!response.ok) {
            throw new Error('Error en la solicitud: ' + response.status);
        }

        const data = await response.json();
        return data;
    } catch (error) {
        console.log(error);
        return [];
    }
}

async function dibujarRuleta(generos) {
    const arcSize = (2 * Math.PI) / generos.length;

    for (let i = 0; i < generos.length; i++) {
        ctx.beginPath();
        ctx.fillStyle = generos[i].color;
        ctx.moveTo(200, 200);
        ctx.arc(200, 200, 200, i * arcSize, (i + 1) * arcSize);
        ctx.lineTo(200, 200);
        ctx.fill();

        ctx.save();
        ctx.translate(200, 200);
        ctx.rotate(i * arcSize + arcSize / 2);
        ctx.textAlign = "right";
        ctx.fillStyle = "white";
        ctx.font = "bold 18px Arial";
        ctx.fillText(generos[i].nombre, 170, 10);
        ctx.restore();
    }
}

let anguloActual = 0;
let girando = false;

async function iniciarRuleta() {
    const generos = await generosFetch();
    const arcSize = (2 * Math.PI) / generos.length;
    const numSectores = generos.length;

    dibujarRuleta(generos);

    btn.addEventListener("click", () => {
        if (girando) return;

        girando = true;
        btn.disabled = true;
        resultado.innerHTML = "";

        const vueltas = Math.floor(Math.random() * 3) + 4;
        const anguloFinal = anguloActual + vueltas * 2 * Math.PI + Math.random() * 2 * Math.PI;

        const duracion = 4000;
        const inicio = performance.now();

        function animarRuleta(timestamp) {
            const progreso = Math.min((timestamp - inicio) / duracion, 1);
            const easing = 1 - Math.pow(1 - progreso, 3);
            const angulo = anguloActual + easing * (anguloFinal - anguloActual);

            ctx.clearRect(0, 0, 400, 400);
            ctx.save();
            ctx.translate(200, 200);
            ctx.rotate(angulo);
            ctx.translate(-200, -200);
            dibujarRuleta(generos);
            ctx.restore();

            if (progreso < 1) {
                requestAnimationFrame(animarRuleta);
            } else {
                anguloActual = anguloFinal % (2 * Math.PI);
                girando = false;
                btn.disabled = false;

                const TWO_PI = 2 * Math.PI;
                const EPS = 1e-9;

                const angNorm = ((anguloActual % TWO_PI) + TWO_PI) % TWO_PI;
                const relative = ((-Math.PI / 2) - angNorm + TWO_PI) % TWO_PI;
                let indiceGanador = Math.floor((relative + EPS) / arcSize) % numSectores;

                if (indiceGanador < 0) indiceGanador += numSectores;

                const genero = generos[indiceGanador];

                resultado.innerHTML = `🎯 Te tocó: <strong>${genero.nombre}</strong>`;

                document.getElementById("generoInput").value = genero.id;
                setTimeout(() => {
                    document.getElementById("formGenero").submit();
                }, 1000);
            }
        }

        requestAnimationFrame(animarRuleta);
    });
}

iniciarRuleta();
