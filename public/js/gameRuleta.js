const canvas = document.getElementById("ruletaCanvas");
const ctx = canvas.getContext("2d");
const btn = document.getElementById("btnGirar");
const resultado = document.getElementById("resultado");

const generos = [
    "Historia",
    "Ciencia",
    "Geografía",
    "Deportes",
    "Arte",
    "Entretenimiento"
];

const colors = ["#f94144","#f3722c","#f9c74f","#90be6d","#43aa8b","#577590"];
const numSectores = generos.length;
const arcSize = (2 * Math.PI) / numSectores;

function dibujarRuleta() {
    for (let i = 0; i < numSectores; i++) {
        ctx.beginPath();
        ctx.fillStyle = colors[i];
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
        ctx.fillText(generos[i], 170, 10);
        ctx.restore();
    }
}

dibujarRuleta();

let anguloActual = 0;
let girando = false;

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
        dibujarRuleta();
        ctx.restore();

        if (progreso < 1) {
            requestAnimationFrame(animarRuleta);
        } else {
            anguloActual = anguloFinal % (2 * Math.PI);
            girando = false;
            //btn.disabled = false;
            btn.disabled = true;

            const TWO_PI = 2 * Math.PI;
            const EPS = 1e-9;

            const angNorm = ((anguloActual % TWO_PI) + TWO_PI) % TWO_PI;
            const relative = ((-Math.PI / 2) - angNorm + TWO_PI) % TWO_PI;
            let indiceGanador = Math.floor((relative + EPS) / arcSize) % numSectores;
            
            if (indiceGanador < 0) indiceGanador += numSectores;

            const genero = generos[indiceGanador];

            resultado.innerHTML = `🎯 Te tocó: <strong>${genero}</strong>`;

            document.getElementById("generoInput").value = genero;
            setTimeout(() =>{
                document.getElementById("formGenero").submit();
            }, 1000);
        }
    }

    requestAnimationFrame(animarRuleta);
});
