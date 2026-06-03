// ── Tabs ──
function cambiarTab(tab, btn) {
    document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.tab').forEach(b => b.classList.remove('active'));
    document.getElementById('tab-' + tab).classList.add('active');
    btn.classList.add('active');
    history.replaceState(null, '', '#' + tab);
}

(function () {
    const hash = window.location.hash.replace('#', '');
    const tabs  = ['peso', 'medidas', 'marcas'];
    if (tabs.includes(hash)) {
        const btn = document.querySelector(`.tab[onclick*="'${hash}'"]`);
        if (btn) cambiarTab(hash, btn);
    }
})();

// ── Modal ──
function abrirModal()  { document.getElementById('modal-peso').classList.add('visible'); }
function cerrarModal() { document.getElementById('modal-peso').classList.remove('visible'); }

// ── Vista completa de marcas ──
function mostrarTodos() {
    document.getElementById('vista-top5').classList.add('oculto');
    document.getElementById('vista-todos').classList.remove('oculto');
}
function ocultarTodos() {
    document.getElementById('vista-todos').classList.add('oculto');
    document.getElementById('vista-top5').classList.remove('oculto');
}

// ── Acordeón por músculo ──
function toggleMusculo(header) {
    const contenido = header.nextElementSibling;
    const flecha    = header.querySelector('.musculo-flecha');
    const abierto   = contenido.style.maxHeight && contenido.style.maxHeight !== '0px';
    if (abierto) {
        contenido.style.maxHeight = '0';
        flecha.classList.remove('abierto');
    } else {
        contenido.style.maxHeight = contenido.scrollHeight + 'px';
        flecha.classList.add('abierto');
    }
}

function verMasGrupo(grupo, btn) {
    document.querySelectorAll('.ejercicio-extra-' + grupo).forEach(el => el.classList.remove('oculto'));
    btn.style.display = 'none';
    const contenido = btn.closest('.musculo-contenido');
    if (contenido) contenido.style.maxHeight = contenido.scrollHeight + 'px';
}

function verMasPesomax(grupo, btn) {
    document.querySelectorAll('.pesomax-extra-' + grupo).forEach(el => el.classList.remove('oculto'));
    btn.style.display = 'none';
}

// ════════════════════════════════════════════════
//  GRÁFICAS
//  allPeso, allMedidas, HAY_PESO, HAY_MEDIDAS
//  are global variables injected by progreso.php
// ════════════════════════════════════════════════

const chartDefaults = {
    plugins: { legend: { display: false } },
    scales: {
        y: {
            grid:   { color: 'rgba(114,196,66,0.06)' },
            border: { color: 'transparent' },
            ticks:  { color: '#4a6044', font: { family: "'DM Mono'", size: 11 } }
        },
        x: {
            grid:   { display: false },
            border: { color: 'transparent' },
            ticks:  { color: '#4a6044', font: { family: "'DM Mono'", size: 11 }, maxRotation: 0, autoSkip: true }
        }
    }
};

function diasRango(range) {
    return { week: 7, month: 30, year: 365 }[range] ?? 30;
}

function maxTicks(range) {
    return { week: 7, month: 8, year: 12 }[range] ?? 8;
}

function filtrarRango(data, range) {
    const cutoff = new Date();
    cutoff.setDate(cutoff.getDate() - diasRango(range));
    return data.filter(r => new Date(r.fecha) >= cutoff);
}

function fmtFecha(fechaStr, range) {
    const d = new Date(fechaStr);
    return range === 'year'
        ? d.toLocaleDateString('es-ES', { month: 'short', year: '2-digit' })
        : d.toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit' });
}

function buildOptions(range, unit) {
    return {
        ...chartDefaults,
        scales: {
            ...chartDefaults.scales,
            x: {
                ...chartDefaults.scales.x,
                ticks: { ...chartDefaults.scales.x.ticks, maxTicksLimit: maxTicks(range) }
            },
            y: {
                ...chartDefaults.scales.y,
                ticks: { ...chartDefaults.scales.y.ticks, callback: v => v + ' ' + unit }
            }
        }
    };
}

function bindFilters(containerId, onChange) {
    const container = document.getElementById(containerId);
    if (!container) return;
    container.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            container.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            onChange(btn.dataset.range);
        });
    });
}

// ── Gráfica PESO ──
if (HAY_PESO) {
    let chartPeso = null;

    function renderPeso(range) {
        const canvas = document.getElementById('grafica-peso');
        if (!canvas) return;
        const filtered = filtrarRango(allPeso, range);
        if (chartPeso) chartPeso.destroy();
        chartPeso = new Chart(canvas, {
            type: 'line',
            data: {
                labels: filtered.map(r => fmtFecha(r.fecha, range)),
                datasets: [{
                    data: filtered.map(r => r.val),
                    borderColor: '#72c442',
                    backgroundColor: 'rgba(114,196,66,0.08)',
                    borderWidth: 2,
                    pointRadius: 4,
                    pointBackgroundColor: '#72c442',
                    tension: 0.3,
                    fill: true
                }]
            },
            options: buildOptions(range, 'kg')
        });
    }

    bindFilters('filters-peso', renderPeso);
    renderPeso('month');
}

// ── Gráficas MEDIDAS ──
if (HAY_MEDIDAS) {
    const medidasCharts  = { pecho: null, cintura: null, brazo: null };
    const medidasColores = { pecho: '#72c442', cintura: '#e84e1b', brazo: '#8aaa80' };

    function renderMedida(campo, range) {
        const canvas = document.getElementById('grafica-' + campo);
        if (!canvas) return;
        const filtered = filtrarRango(allMedidas, range).filter(r => r[campo] !== null);
        if (medidasCharts[campo]) medidasCharts[campo].destroy();
        medidasCharts[campo] = new Chart(canvas, {
            type: 'line',
            data: {
                labels: filtered.map(r => fmtFecha(r.fecha, range)),
                datasets: [{
                    data: filtered.map(r => r[campo]),
                    borderColor: medidasColores[campo],
                    backgroundColor: medidasColores[campo] + '15',
                    borderWidth: 2,
                    pointRadius: 4,
                    pointBackgroundColor: medidasColores[campo],
                    tension: 0.3,
                    fill: true,
                    spanGaps: true
                }]
            },
            options: buildOptions(range, 'cm')
        });
    }

    ['pecho', 'cintura', 'brazo'].forEach(campo => {
        bindFilters('filters-' + campo, range => renderMedida(campo, range));
        renderMedida(campo, 'month');
    });
}