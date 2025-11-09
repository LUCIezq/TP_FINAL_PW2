const canvas = document.getElementById("ruletaCanvas");
const ctx = canvas.getContext("2d");
const btn = document.getElementById("btnGirar");
const resultado = document.getElementById("resultado");

// ⚠️ Nombres exactamente como en la base de datos:
const generos = [
    "Historia",
    "Ciencia",
    "Geografia",
    "Deportes",
    "Arte",
    "Entretenimiento"
];

const colors = ["#f94144","#f3722c","#f9c74f","#90be6d","#43aa8b","#577590"];
const numSectores = generos.length;
const arcSize = (2 * Math.PI) / numSectores;

// 🎨 Dibuja la ruleta
function dibujarRuleta() {
    for (let i = 0; i < numSectores; i++) {
        ctx.beginPath();
        ctx.fillStyle = colors[i];
        ctx.moveTo(200, 200);
        ctx.arc(200, 200, 200, i * arcSize, (i + 1) * arcSize);
        ctx.lineTo(200, 200);
        ctx.fill();

        // Texto
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

// Dibujo inicial
dibujarRuleta();

let anguloActual = 0;
let girando = false;

btn.addEventListener("click", () => {
    if (girando) return;

    girando = true;
    btn.disabled = true;
    resultado.innerHTML = "";

    const vueltas = Math.floor(Math.random() * 3) + 4; // 4–6 vueltas
    const anguloFinal = anguloActual + vueltas * 2 * Math.PI + Math.random() * 2 * Math.PI;

    const duracion = 4000; // ms
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
            // ✅ Fin del giro
            anguloActual = anguloFinal % (2 * Math.PI);
            girando = false;
            btn.disabled = false;

            // 🧭 Calcular el género ganador según la flecha superior (arriba)
            const TWO_PI = 2 * Math.PI;
            const EPS = 1e-9;

            const angNorm = ((anguloActual % TWO_PI) + TWO_PI) % TWO_PI;
            const relative = ((-Math.PI / 2) - angNorm + TWO_PI) % TWO_PI;

            let indiceGanador = Math.floor((relative + EPS) / arcSize) % numSectores;
            if (indiceGanador < 0) indiceGanador += numSectores;

            const genero = generos[indiceGanador];

            resultado.innerHTML = `
        🎯 Te toco: <strong>${genero}</strong><br><br>
        <form method="POST" action="/game/start">
          <input type="hidden" name="genero" value="${genero}">
          <button type="submit" class="btn btn-success mt-3">Comenzar partida</button>
        </form>
      `;
        }
    }

    requestAnimationFrame(animarRuleta);
});
