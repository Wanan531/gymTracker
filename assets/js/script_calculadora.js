// ── Tabs ──
function cambiarTab(tab, btn) {
    document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.tab').forEach(b => b.classList.remove('active'));
    document.getElementById('tab-' + tab).classList.add('active');
    btn.classList.add('active');
}

// ══════════════ 1RM ══════════════
function calcular1RM() {
    const peso    = parseFloat(document.getElementById('rm-peso').value);
    const reps    = parseInt(document.getElementById('rm-reps').value);
    const formula = document.getElementById('rm-formula').value;

    if (!peso || !reps || reps < 1) return;

    // Brzycki no es válida con 37 o más repeticiones (denominador 0 o negativo)
    if (formula === 'brzycki' && reps >= 37) {
        alert('La fórmula Brzycki no es válida con 37 o más repeticiones. Elige otra fórmula o reduce las repeticiones.');
        return;
    }

    let rm;
    if      (formula === 'epley')    rm = peso * (1 + reps / 30);
    else if (formula === 'brzycki')  rm = peso * (36 / (37 - reps));
    else if (formula === 'lombardi') rm = peso * Math.pow(reps, 0.10);
    else if (formula === 'mayhew')   rm = (100 * peso) / (52.2 + 41.9 * Math.exp(-0.055 * reps));

    rm = Math.round(rm * 10) / 10;

    document.getElementById('res-1rm-valor').textContent = rm + ' kg';

    const porcentajes = [100, 95, 90, 85, 80, 75, 70];
    let html = '';
    porcentajes.forEach(p => {
        const val = Math.round(rm * p / 10) / 10;
        html += `
            <div class="resultado-item">
                <p class="resultado-item-valor">${val} kg</p>
                <p class="resultado-item-label">${p}% 1RM</p>
            </div>`;
    });
    document.getElementById('res-1rm-tabla').innerHTML = html;
    document.getElementById('res-1rm').classList.add('visible');
}

// ══════════════ IMC ══════════════
function calcularIMC() {
    const peso   = parseFloat(document.getElementById('imc-peso').value);
    const altura = parseFloat(document.getElementById('imc-altura').value);
    if (!peso || !altura) return;

    const h   = altura / 100;
    const imc = Math.round((peso / (h * h)) * 10) / 10;

    let cat, color;
    if      (imc < 18.5) { cat = 'Bajo peso';   color = '#378ADD'; }
    else if (imc < 25)   { cat = 'Peso normal';  color = '#72c442'; }
    else if (imc < 30)   { cat = 'Sobrepeso';    color = '#e8c41b'; }
    else if (imc < 35)   { cat = 'Obesidad I';   color = '#e84e1b'; }
    else                 { cat = 'Obesidad II+';  color = '#a32d2d'; }

    document.getElementById('res-imc-valor').textContent = imc;
    document.getElementById('res-imc-valor').style.color = color;
    document.getElementById('res-imc-cat').textContent   = cat;

    // Rango ampliado a IMC 15-45 para evitar que el marcador se salga en obesidad severa
    const pct = Math.min(100, Math.max(0, ((imc - 15) / 30) * 100));
    document.getElementById('imc-marcador').style.left = pct + '%';

    const pesoIdeal = Math.round(22   * h * h * 10) / 10;
    const pesoMin   = Math.round(18.5 * h * h * 10) / 10;
    const pesoMax   = Math.round(24.9 * h * h * 10) / 10;

    document.getElementById('imc-peso-ideal').textContent = pesoIdeal + ' kg';
    document.getElementById('imc-rango').textContent      = pesoMin + '–' + pesoMax + ' kg';

    document.getElementById('res-imc').classList.add('visible');
}

// ══════════════ TDEE ══════════════
let generoTDEE = 'hombre';

function seleccionarGenero(g) {
    generoTDEE = g;
    document.getElementById('tdee-hombre').classList.toggle('selected', g === 'hombre');
    document.getElementById('tdee-mujer').classList.toggle('selected',  g === 'mujer');
}

function calcularTDEE() {
    const edad   = parseInt(document.getElementById('tdee-edad').value);
    const peso   = parseFloat(document.getElementById('tdee-peso').value);
    const altura = parseFloat(document.getElementById('tdee-altura').value);
    const factor = parseFloat(document.getElementById('tdee-actividad').value);
    if (!edad || !peso || !altura) return;

    let tmb;
    if (generoTDEE === 'hombre') {
        tmb = 10 * peso + 6.25 * altura - 5 * edad + 5;
    } else {
        tmb = 10 * peso + 6.25 * altura - 5 * edad - 161;
    }
    const tdee = Math.round(tmb * factor);
    tmb = Math.round(tmb);

    document.getElementById('res-tdee-valor').textContent = tdee.toLocaleString('es-ES');

    document.getElementById('res-tdee-grid').innerHTML = `
        <div class="resultado-item">
            <p class="resultado-item-valor">${tmb.toLocaleString('es-ES')}</p>
            <p class="resultado-item-label">TMB (basal)</p>
        </div>
        <div class="resultado-item">
            <p class="resultado-item-valor">${(tdee - 500).toLocaleString('es-ES')}</p>
            <p class="resultado-item-label">Déficit (−500)</p>
        </div>
        <div class="resultado-item">
            <p class="resultado-item-valor">${tdee.toLocaleString('es-ES')}</p>
            <p class="resultado-item-label">Mantenimiento</p>
        </div>
        <div class="resultado-item">
            <p class="resultado-item-valor">${(tdee + 300).toLocaleString('es-ES')}</p>
            <p class="resultado-item-label">Superávit (+300)</p>
        </div>
    `;

    document.getElementById('res-tdee-nota').textContent =
        'Basado en la fórmula Mifflin-St Jeor, considerada la más precisa para la población general. ' +
        'El déficit calórico de −500 kcal/día produce una pérdida aproximada de 0,5 kg/semana.';

    document.getElementById('res-tdee').classList.add('visible');
}

// ══════════════ MACROS ══════════════
let chartMacros = null;

function calcularMacros() {
    const calorias = parseInt(document.getElementById('mac-calorias').value);
    const peso     = parseFloat(document.getElementById('mac-peso').value);
    const objetivo = document.getElementById('mac-objetivo').value;
    if (!calorias || !peso) return;

    let protRatio, grasaRatio, nota;
    if (objetivo === 'volumen') {
        protRatio  = 2.2;
        grasaRatio = 0.25;
        nota = 'En volumen se prioriza alta proteína para maximizar la síntesis muscular. Los carbohidratos cubren el resto para tener energía en los entrenos.';
    } else if (objetivo === 'definicion') {
        protRatio  = 2.5;
        grasaRatio = 0.25;
        nota = 'En definición se eleva la proteína para preservar músculo en déficit calórico. Las grasas se mantienen y los carbohidratos se reducen.';
    } else {
        protRatio  = 1.8;
        grasaRatio = 0.30;
        nota = 'En mantenimiento se busca un equilibrio entre los tres macros para sostener el rendimiento y la composición corporal.';
    }

    const protG    = Math.round(protRatio * peso);
    const protCal  = protG * 4;
    const grasaCal = Math.round(calorias * grasaRatio);
    const grasaG   = Math.round(grasaCal / 9);
    const carbCal  = Math.max(0, calorias - protCal - grasaCal);
    const carbG    = Math.round(carbCal / 4);

    const pctProt  = Math.round((protCal  / calorias) * 100);
    const pctGrasa = Math.round((grasaCal / calorias) * 100);
    const pctCarb  = Math.round((carbCal  / calorias) * 100);

    const COLORES = {
        prot:  '#72c442',
        carb:  '#378ADD',
        grasa: '#e84e1b'
    };

    // Gráfica doughnut
    document.getElementById('mac-chart-total').textContent = calorias.toLocaleString('es-ES');

    const canvas = document.getElementById('grafica-macros');
    if (chartMacros) chartMacros.destroy();

    chartMacros = new Chart(canvas, {
        type: 'doughnut',
        data: {
            labels: ['Proteína', 'Carbos', 'Grasas'],
            datasets: [{
                data: [protCal, carbCal, grasaCal],
                backgroundColor: [COLORES.prot, COLORES.carb, COLORES.grasa],
                borderColor: 'transparent',
                borderWidth: 0,
                hoverOffset: 6
            }]
        },
        options: {
            cutout: '68%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => ` ${ctx.label}: ${ctx.parsed} kcal (${Math.round(ctx.parsed / calorias * 100)}%)`
                    }
                }
            },
            animation: { animateRotate: true, duration: 600 }
        }
    });

    // Leyenda personalizada
    document.getElementById('mac-leyenda').innerHTML = `
        <div class="macro-leyenda-item">
            <div class="macro-leyenda-dot" style="background:${COLORES.prot}"></div>
            <span class="macro-leyenda-texto">Proteína</span>
            <span class="macro-leyenda-valor">${protG}g · ${pctProt}%</span>
        </div>
        <div class="macro-leyenda-item">
            <div class="macro-leyenda-dot" style="background:${COLORES.carb}"></div>
            <span class="macro-leyenda-texto">Carbohidratos</span>
            <span class="macro-leyenda-valor">${carbG}g · ${pctCarb}%</span>
        </div>
        <div class="macro-leyenda-item">
            <div class="macro-leyenda-dot" style="background:${COLORES.grasa}"></div>
            <span class="macro-leyenda-texto">Grasas</span>
            <span class="macro-leyenda-valor">${grasaG}g · ${pctGrasa}%</span>
        </div>
    `;

    // Barras de porcentaje
    document.getElementById('res-mac-barras').innerHTML = `
        <div class="macro-fila">
            <span class="macro-nombre">Proteína</span>
            <div class="macro-barra-wrap">
                <div class="macro-barra-fill" style="width:${pctProt}%;background:${COLORES.prot};"></div>
            </div>
            <span class="macro-valor">${pctProt}%</span>
        </div>
        <div class="macro-fila">
            <span class="macro-nombre">Carbos</span>
            <div class="macro-barra-wrap">
                <div class="macro-barra-fill" style="width:${pctCarb}%;background:${COLORES.carb};"></div>
            </div>
            <span class="macro-valor">${pctCarb}%</span>
        </div>
        <div class="macro-fila">
            <span class="macro-nombre">Grasas</span>
            <div class="macro-barra-wrap">
                <div class="macro-barra-fill" style="width:${pctGrasa}%;background:${COLORES.grasa};"></div>
            </div>
            <span class="macro-valor">${pctGrasa}%</span>
        </div>
    `;

    // Grid de gramos
    document.getElementById('res-mac-grid').innerHTML = `
        <div class="resultado-item">
            <p class="resultado-item-valor" style="color:${COLORES.prot};">${protG}g</p>
            <p class="resultado-item-label">Proteína</p>
        </div>
        <div class="resultado-item">
            <p class="resultado-item-valor" style="color:${COLORES.carb};">${carbG}g</p>
            <p class="resultado-item-label">Carbohidratos</p>
        </div>
        <div class="resultado-item">
            <p class="resultado-item-valor" style="color:${COLORES.grasa};">${grasaG}g</p>
            <p class="resultado-item-label">Grasas</p>
        </div>
        <div class="resultado-item">
            <p class="resultado-item-valor">${calorias.toLocaleString('es-ES')}</p>
            <p class="resultado-item-label">Total kcal</p>
        </div>
    `;

    document.getElementById('res-mac-nota').textContent = nota;
    document.getElementById('res-macros').classList.add('visible');
}

// ── Auto calcular al cargar la página ──
// Los datos del usuario se leen desde los data-attributes del <body>,
// que PHP escribe en el servidor. Así el .js queda libre de PHP.
window.addEventListener('load', () => {
    const body       = document.body;
    const tienePeso  = body.dataset.peso   !== '';
    const tieneAltura = body.dataset.altura !== '';
    const tieneEdad  = body.dataset.edad   !== '';

    if (tienePeso && tieneAltura)           calcularIMC();
    if (tienePeso && tieneAltura && tieneEdad) calcularTDEE();
});