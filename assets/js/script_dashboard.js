// ── Calendario ──
// diasEntrenados, detalleSesiones, horasDia, horasSemana, horasMes, horasAnio
// are global variables injected by dashboard.php via an inline <script> block.

const hoy   = new Date();
const year  = hoy.getFullYear();
const month = hoy.getMonth();

document.getElementById('mes-nombre').textContent =
    hoy.toLocaleString('es-ES', { month: 'long', year: 'numeric' });

const primerDia         = new Date(year, month, 1).getDay();
const primerDiaAjustado = primerDia === 0 ? 6 : primerDia - 1;
const diasEnMes         = new Date(year, month + 1, 0).getDate();
const grid              = document.getElementById('calendario-grid');

for (let i = 0; i < primerDiaAjustado; i++) {
    const vacio = document.createElement('div');
    vacio.classList.add('dia', 'vacio');
    grid.appendChild(vacio);
}

for (let d = 1; d <= diasEnMes; d++) {
    const div = document.createElement('div');
    div.classList.add('dia');
    div.textContent = d;

    const fechaStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`;

    if (diasEntrenados.includes(fechaStr)) {
        div.classList.add('entrenado');
        div.addEventListener('click', () => abrirPopup(fechaStr));
    }
    if (d === hoy.getDate()) div.classList.add('hoy');

    grid.appendChild(div);
}

// ── Popup ──
function abrirPopup(fecha) {
    const sesiones = detalleSesiones[fecha];
    if (!sesiones) return;

    const [y, m, d] = fecha.split('-');
    const fechaBonita = new Date(y, m - 1, d).toLocaleDateString('es-ES', {
        weekday: 'long', day: 'numeric', month: 'long'
    });
    document.getElementById('popup-fecha').textContent =
        fechaBonita.charAt(0).toUpperCase() + fechaBonita.slice(1);

    let html = '';
    sesiones.forEach(s => {
        const duracion   = s.duracion_min ? `${s.duracion_min} min` : 'Sin duración';
        const ejercicios = s.ejercicios
            ? s.ejercicios.split('||').map(e => `<li>${e}</li>`).join('')
            : '<li style="color:var(--text3)">Sin ejercicios registrados</li>';

        html += `
            <div class="popup-sesion">
                <div class="popup-sesion-top">
                    <p class="popup-rutina-nombre">${s.rutina || 'Entrenamiento libre'}</p>
                    <span class="popup-duracion">${duracion}</span>
                </div>
                <ul class="popup-ejercicios">${ejercicios}</ul>
            </div>`;
    });

    document.getElementById('popup-contenido').innerHTML = html;
    document.getElementById('popup-dia').style.display = 'flex';
}

function cerrarPopup() {
    document.getElementById('popup-dia').style.display = 'none';
}

document.getElementById('popup-dia').addEventListener('click', function(e) {
    if (e.target === this) cerrarPopup();
});

// ── Gráfica horas con toggle ──
let graficaHoras = null;

const configBase = {
    plugins: { legend: { display: false } },
    scales: {
        y: {
            beginAtZero: true,
            grid:   { color: 'rgba(114,196,66,0.06)' },
            border: { color: 'transparent' },
            ticks:  { color: '#4a6044', font: { family: "'DM Mono'" }, callback: v => v + 'h' }
        },
        x: {
            grid:   { display: false },
            border: { color: 'transparent' },
            ticks:  { color: '#4a6044', font: { family: "'DM Mono'", size: 10 }, maxRotation: 45 }
        }
    }
};

function cambiarGraficaHoras(tipo) {
    document.getElementById('btn-dia').classList.toggle('active',    tipo === 'dia');
    document.getElementById('btn-semana').classList.toggle('active', tipo === 'semana');
    document.getElementById('btn-mes').classList.toggle('active',    tipo === 'mes');
    document.getElementById('btn-anio').classList.toggle('active',   tipo === 'anio');

    if (graficaHoras) graficaHoras.destroy();

    let datos, labels;

    if (tipo === 'dia') {
        datos  = horasDia;
        labels = datos.map(r => {
            const [y, m, d] = r.fecha.split('-');
            return new Date(y, m - 1, d).toLocaleDateString('es-ES', { day: 'numeric', month: 'short' });
        });
    } else if (tipo === 'semana') {
        datos  = horasSemana;
        labels = datos.map(r => `S${r.num_semana} ${r.anio}`);
    } else if (tipo === 'mes') {
        datos  = horasMes;
        labels = datos.map(r => {
            const [y, m] = r.mes.split('-');
            return new Date(y, m - 1, 1).toLocaleDateString('es-ES', { month: 'short', year: '2-digit' });
        });
    } else if (tipo === 'anio') {
        datos  = horasAnio;
        labels = datos.map(r => String(r.anio));
    }

    graficaHoras = new Chart(document.getElementById('grafica-horas'), {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                data: datos.map(r => r.horas),
                backgroundColor: 'rgba(114,196,66,0.25)',
                borderColor: '#72c442',
                borderWidth: 1.5,
                borderRadius: 6,
                borderSkipped: false
            }]
        },
        options: configBase
    });
}

// ── Tabs ──
function cambiarTab(tab) {
    document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('activo'));
    const destino = document.getElementById('tab-' + tab);
    if (destino) destino.classList.add('activo');
    // Si se activa horas y la gráfica no está creada, inicializarla
    if (tab === 'horas' && !graficaHoras) cambiarGraficaHoras('dia');
}

// ── Inicialización ──
(function () {
    const hash = window.location.hash.replace('#', '');
    const tabs  = ['horas', 'entrenos'];
    if (tabs.includes(hash)) {
        cambiarTab(hash);
        const el = document.getElementById('tab-' + hash);
        if (el) setTimeout(() => el.scrollIntoView({ behavior: 'smooth' }), 100);
    } else {
        // Sin hash: mostrar todas las secciones y arrancar la gráfica
        document.querySelectorAll('.tab-content').forEach(el => el.classList.add('activo'));
        cambiarGraficaHoras('dia');
    }
})();