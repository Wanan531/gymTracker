// ── Globals injected by entreno.php ──
// RUTINA_ID, TOTAL_EJERCICIOS, duracionesEjercicio

let actual = 0;
const total = TOTAL_EJERCICIOS;
const seriesCompletadas = {};
let timerDescansoInterval = null;
let timerEjercicioInterval = null;

// ── Descanso: global + overrides por ejercicio ──
let TIEMPO_DESCANSO = 90;
const tiemposEj = {};

function setDescansoGlobal(seg, btn) {
    TIEMPO_DESCANSO = seg;
    document.querySelectorAll('.descanso-global-bar .descanso-btns button')
        .forEach(b => b.classList.remove('activo'));
    btn.classList.add('activo');
}

function setDescansoEj(index, seg, btn) {
    tiemposEj[index] = seg;
    btn.closest('.descanso-btns').querySelectorAll('button')
        .forEach(b => b.classList.remove('activo'));
    btn.classList.add('activo');
}

function getDescanso(index) {
    return tiemposEj[index] ?? TIEMPO_DESCANSO;
}

// ── Cronómetro global ──
let segundos = 0;
const cronometro = setInterval(() => {
    segundos++;
    document.getElementById('segundos_totales').value = segundos;
    const h = Math.floor(segundos / 3600);
    const m = Math.floor((segundos % 3600) / 60);
    const s = segundos % 60;
    document.getElementById('display-tiempo').textContent =
        `${h.toString().padStart(2,'0')}:${m.toString().padStart(2,'0')}:${s.toString().padStart(2,'0')}`;
}, 1000);

// ── Navegación entre ejercicios ──
function irA(index) {
    document.querySelectorAll('.ejercicio-panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.progress-dots span').forEach((d, i) => {
        d.classList.remove('active');
        if (i < index) d.classList.add('done');
    });
    document.querySelectorAll('.ejercicio-panel')[index].classList.add('active');
    document.querySelectorAll('.progress-dots span')[index].classList.add('active');
    document.getElementById('contador-ejercicio').textContent = `Ejercicio ${index + 1} de ${total}`;
    actual = index;
    if (index === total - 1) document.getElementById('btn-finalizar').style.display = 'block';
}

// ── Completar serie (tipo reps) ──
function completarSerie(btn, ejercicioId, numSerie) {
    btn.classList.toggle('completada');
    const key  = `${ejercicioId}-${numSerie}`;
    const fila = btn.closest('tr');

    if (btn.classList.contains('completada')) {
        seriesCompletadas[key] = {
            ejercicioId,
            numSerie,
            peso: fila.querySelector('.input-peso').value,
            reps: fila.querySelector('.input-reps').value
        };
        if (duracionesEjercicio[actual] !== undefined) {
            iniciarTimerEjercicio(actual, duracionesEjercicio[actual]);
        } else {
            iniciarTimerDescanso(actual);
        }
    } else {
        delete seriesCompletadas[key];
        detenerTimerEjercicio(actual);
        detenerTimerDescanso(actual);
    }
}

// ── Timer de EJERCICIO (amarillo) ──
function iniciarTimerEjercicio(index, duracion, onFin = null) {
    detenerTimerEjercicio(index);
    detenerTimerDescanso(index);

    const box     = document.getElementById(`timer-ej-${index}`);
    const display = document.getElementById(`timer-ej-display-${index}`);
    const fill    = document.getElementById(`timer-ej-fill-${index}`);
    if (!box) return;

    box.classList.add('visible');
    let restante = duracion;
    display.textContent = formatTiempo(restante);
    fill.style.width = '100%';

    timerEjercicioInterval = setInterval(() => {
        restante--;
        display.textContent = formatTiempo(restante);
        fill.style.width = (restante / duracion * 100) + '%';
        if (restante <= 0) {
            clearInterval(timerEjercicioInterval);
            display.textContent = '¡Hecho!';
            if (onFin) onFin();
            setTimeout(() => {
                detenerTimerEjercicio(index);
                iniciarTimerDescanso(index);
            }, 700);
        }
    }, 1000);
}

function detenerTimerEjercicio(index) {
    clearInterval(timerEjercicioInterval);
    const box = document.getElementById(`timer-ej-${index}`);
    if (box) box.classList.remove('visible');
}

let callbackEjercicioActivo = null;

function saltarEjercicio(index) {
    if (callbackEjercicioActivo) {
        callbackEjercicioActivo();
        callbackEjercicioActivo = null;
    }
    detenerTimerEjercicio(index);
    iniciarTimerDescanso(index);
}

// ── Timer de DESCANSO (verde) ──
function iniciarTimerDescanso(index) {
    detenerTimerDescanso(index);

    const box     = document.getElementById(`timer-${index}`);
    const display = document.getElementById(`timer-display-${index}`);
    const fill    = document.getElementById(`timer-fill-${index}`);
    if (!box) return;

    box.classList.add('visible');
    const durDescanso = getDescanso(index);
    let restante = durDescanso;
    display.textContent = formatTiempo(restante);
    fill.style.width = '100%';

    timerDescansoInterval = setInterval(() => {
        restante--;
        display.textContent = formatTiempo(restante);
        fill.style.width = (restante / durDescanso * 100) + '%';
        if (restante <= 0) {
            detenerTimerDescanso(index);
            display.textContent = '¡Listo!';
        }
    }, 1000);
}

function detenerTimerDescanso(index) {
    clearInterval(timerDescansoInterval);
    const box = document.getElementById(`timer-${index}`);
    if (box) box.classList.remove('visible');
}

function saltarDescanso(index) {
    detenerTimerDescanso(index);
}

// ── Serie de tipo tiempo ──
function iniciarSerie(btn, ejercicioId, numSerie, duracion) {
    if (btn.classList.contains('completada')) return;
    if (btn.classList.contains('corriendo')) return;

    btn.classList.add('corriendo');
    btn.textContent = '…';
    btn.onclick = null;

    iniciarTimerEjercicio(actual, duracion, () => {
        btn.classList.remove('corriendo');
        btn.classList.add('completada');
        btn.textContent = '✓';

        const key  = `${ejercicioId}-${numSerie}`;
        const fila = btn.closest('tr');
        seriesCompletadas[key] = {
            ejercicioId,
            numSerie,
            peso: fila.querySelector('.input-peso').value,
            reps: fila.querySelector('.input-reps').value
        };
    });
}

// ── Utilidades ──
function formatTiempo(seg) {
    return `${Math.floor(seg / 60)}:${(seg % 60).toString().padStart(2, '0')}`;
}

// ── Comparativas ──
(function() {
    const ITEMS = [
        { emoji: '🍎', label: 'Manzana',          unit: 'manzanas',   kg: 0.2   },
        { emoji: '📦', label: 'Caja mudanza',      unit: 'cajas',      kg: 20    },
        { emoji: '🐕', label: 'Perro mediano',     unit: 'perros',     kg: 30    },
        { emoji: '🧑', label: 'Persona adulta',    unit: 'personas',   kg: 75    },
        { emoji: '🛁', label: 'Bañera llena',      unit: 'bañeras',    kg: 230   },
        { emoji: '🚗', label: 'Coche pequeño',     unit: 'coches',     kg: 1000  },
        { emoji: '🐘', label: 'Elefante',          unit: 'elefantes',  kg: 5000  },
        { emoji: '✈️', label: 'Avión pasajeros',   unit: 'aviones',    kg: 80000 },
    ];

    function fmtNum(n) {
        if (n < 0.01) return n.toFixed(3);
        if (n < 1)    return n.toFixed(2);
        if (n < 10)   return n.toFixed(1);
        return n.toLocaleString('es-ES', { maximumFractionDigits: 1 });
    }

    window.renderComparativas = function(totalKg) {
        const grid = document.getElementById('comp-grid');
        if (!grid) return;
        grid.innerHTML = '';
        ITEMS.forEach(function(item) {
            const count    = totalKg / item.kg;
            const active   = count >= 1;
            const fraccion = count % 1 === 0 ? 1 : count % 1;
            const barPct   = active
                ? Math.min(100, fraccion * 100).toFixed(1)
                : Math.min(100, count * 100).toFixed(1);

            const card = document.createElement('div');
            card.className = 'comp-card' + (active ? ' activa' : '');
            card.innerHTML =
                '<span class="c-emoji">' + item.emoji + '</span>' +
                '<div class="c-label">'  + item.label + '</div>' +
                '<div class="c-val">'    + fmtNum(count) + '</div>' +
                '<div class="c-unit">'   + item.unit + '</div>' +
                '<div class="c-bar-bg"><div class="c-bar-fill" style="width:' + barPct + '%"></div></div>';
            grid.appendChild(card);
        });
    };
})();

// ── Finalizar ──
function finalizarEntreno() {
    let totalKg = 0;
    Object.values(seriesCompletadas).forEach(s => {
        totalKg += parseFloat(s.peso || 0) * parseFloat(s.reps || 0);
    });

    document.getElementById('celebracion-kg').textContent =
        Math.round(totalKg).toLocaleString('es-ES') + ' kg';
    document.getElementById('cel-series').textContent   = Object.keys(seriesCompletadas).length;
    document.getElementById('cel-duracion').textContent = Math.round(segundos / 60);

    renderComparativas(totalKg);

    document.getElementById('celebracion-overlay').style.display = 'flex';
}

function confirmarFin() {
    const duracion = Math.round(segundos / 60);
    fetch('../api/guardar_sesion.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            rutina_id: RUTINA_ID,
            duracion_min: duracion,
            series: Object.values(seriesCompletadas)
        })
    }).then(r => r.json()).then(data => {
        if (data.ok) window.location.href = 'dashboard.php?entreno=completado';
    });
}

function cancelarEntreno() {
    if (confirm('¿Seguro que quieres cancelar? No se guardará ningún dato.')) {
        clearInterval(cronometro);
        window.location.href = 'rutinas.php';
    }
}