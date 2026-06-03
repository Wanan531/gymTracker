let rutinaSeleccionada = null;

function abrirModalCrear()   { document.getElementById('modal-crear').classList.add('visible'); }
function cerrarModalCrear()  { document.getElementById('modal-crear').classList.remove('visible'); }
function abrirBiblioteca(id) { rutinaSeleccionada = id; document.getElementById('modal-biblioteca').classList.add('visible'); }
function cerrarBiblioteca()  { document.getElementById('modal-biblioteca').classList.remove('visible'); }
function abrirModalEjercicio()  { document.getElementById('modal-ejercicio-propio').classList.add('visible'); }
function cerrarModalEjercicio() {
    document.getElementById('modal-ejercicio-propio').classList.remove('visible');
    ['ej-nombre','ej-descripcion'].forEach(id => document.getElementById(id).value = '');
    document.getElementById('ej-musculo').value = '';
    document.getElementById('ej-tipo').value = 'reps';
    document.getElementById('ej-error').style.display = 'none';
    document.getElementById('ej-gif-file').value = '';
    document.getElementById('ej-gif-preview').style.display = 'none';
    document.getElementById('ej-gif-label').textContent = 'Seleccionar archivo...';
}

let filtroMusculo = 'todos';
function filtrarMusculo(musculo, btn) {
    filtroMusculo = musculo;
    document.querySelectorAll('.filtro-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    filtrarEjercicios();
}
function filtrarEjercicios() {
    const q = document.getElementById('buscador-ejercicio').value.toLowerCase();
    document.querySelectorAll('.biblioteca-item').forEach(item => {
        const nombreOk  = item.dataset.nombre.includes(q);
        const musculoOk = filtroMusculo === 'todos' || item.dataset.musculo === filtroMusculo;
        item.style.display = (nombreOk && musculoOk) ? 'flex' : 'none';
    });
}

function anadirEjercicio(ejercicioId, nombre) {
    const series = prompt(`¿Cuántas series para ${nombre}?`, '3');
    const reps   = prompt('¿Cuántas repeticiones / segundos?', '10');
    const peso   = prompt('¿Peso inicial (kg)?', '0');
    if (series === null) return;
    fetch('../api/anadir_ejercicio.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ rutina_id: rutinaSeleccionada, ejercicio_id: ejercicioId, series: parseInt(series), repeticiones: parseInt(reps), peso_kg: parseFloat(peso) })
    }).then(r => r.json()).then(data => { if (data.ok) { cerrarBiblioteca(); location.reload(); } });
}

function eliminarEjercicio(id) {
    if (!confirm('¿Eliminar este ejercicio de la rutina?')) return;
    fetch('../api/eliminar_ejercicio.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id })
    }).then(r => r.json()).then(data => { if (data.ok) location.reload(); });
}

function subirModal() {
    const modal = document.querySelector('#modal-biblioteca .modal');
    if (modal) modal.scrollTo({ top: 0, behavior: 'smooth' });
}

// Null guard: the modal may not exist if PHP rendered no exercises
const modalBibliotecaInner = document.querySelector('#modal-biblioteca .modal');
if (modalBibliotecaInner) {
    modalBibliotecaInner.addEventListener('scroll', function () {
        const btn = document.getElementById('btn-arriba-modal');
        if (btn) btn.style.display = this.scrollTop > 150 ? 'inline-block' : 'none';
    });
}

function ampliarImagen(url) {
    if (!url) return;
    document.getElementById('img-ampliada').src = url;
    document.getElementById('modal-imagen').classList.add('visible');
    document.body.style.overflow = 'hidden';
}
function cerrarImagen() {
    document.getElementById('modal-imagen').classList.remove('visible');
    document.body.style.overflow = 'auto';
}

async function generarRutinaIA() {
    const btn       = document.getElementById('btn-ia');
    const resultado = document.getElementById('ia-resultado');
    const payload   = {
        objetivo: document.getElementById('ia-objetivo').value,
        nivel:    document.getElementById('ia-nivel').value,
        dias:     document.getElementById('ia-dias').value,
        equipo:   document.getElementById('ia-equipo').value,
        notas:    document.getElementById('ia-notas').value,
        lesiones: document.getElementById('ia-lesiones').value
    };
    btn.disabled    = true;
    btn.textContent = 'Generando...';
    resultado.innerHTML = '<p style="color:var(--text3);font-size:13px;margin-top:12px;">La IA está diseñando tu rutina...</p>';
    try {
        const response = await fetch('../api/generar_rutina_ia.php', {
            method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload)
        });
        const rutina = await response.json();
        if (rutina.error) { resultado.innerHTML = `<p style="color:var(--orange);">Error: ${rutina.error}</p>`; return; }
        mostrarRutinaIA(rutina);
    } catch (e) {
        resultado.innerHTML = '<p style="color:var(--orange);">Error de conexión. Inténtalo de nuevo.</p>';
    } finally {
        btn.disabled    = false;
        btn.textContent = 'Generar rutina con IA';
    }
}

function mostrarRutinaIA(rutina) {
    const resultado = document.getElementById('ia-resultado');
    let html = `<div style="margin:12px 0;padding:10px 14px;background:rgba(114,196,66,0.1);border:0.5px solid rgba(114,196,66,0.2);border-radius:var(--radius-sm);font-size:13px;color:var(--green);">Rutina <strong>${rutina.nombre}</strong> guardada. <span style="color:var(--text3);">Recargando...</span></div>`;
    rutina.dias.forEach(dia => {
        html += `<div class="dia-rutina-ia"><h5>${dia.nombre}</h5><ul>`;
        dia.ejercicios.forEach(ej => {
            html += `<li><strong>${ej.nombre}</strong> — ${ej.series} series × ${ej.repeticiones}, descanso ${ej.descanso}${ej.nota ? `<br><small style="color:var(--text3);">${ej.nota}</small>` : ''}</li>`;
        });
        html += `</ul></div>`;
    });
    resultado.innerHTML = html;
    setTimeout(() => window.location.reload(), 2000);
}

async function guardarEjercicioPropio() {
    const nombre  = document.getElementById('ej-nombre').value.trim();
    const musculo = document.getElementById('ej-musculo').value;
    const tipo    = document.getElementById('ej-tipo').value;
    const errDiv  = document.getElementById('ej-error');
    if (!nombre || !musculo) {
        errDiv.textContent = 'El nombre y el grupo muscular son obligatorios.';
        errDiv.style.display = 'block';
        return;
    }
    const fileInput = document.getElementById('ej-gif-file');
    const file      = fileInput.files[0];
    let gif_base64  = null;
    if (file) {
        gif_base64 = await new Promise(resolve => {
            const reader = new FileReader();
            reader.onload = e => resolve(e.target.result);
            reader.readAsDataURL(file);
        });
    }
    const res = await fetch('../api/crear_ejercicio.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            nombre,
            grupo_muscular: musculo,
            tipo,
            descripcion: document.getElementById('ej-descripcion').value.trim(),
            gif_base64
        })
    });
    const data = await res.json();
    if (data.ok) { cerrarModalEjercicio(); location.reload(); }
    else { errDiv.textContent = data.error || 'Error al guardar.'; errDiv.style.display = 'block'; }
}

function previsualizarGif(input) {
    const file = input.files[0];
    if (!file) return;
    document.getElementById('ej-gif-label').textContent = file.name;
    const reader = new FileReader();
    reader.onload = e => {
        const prev = document.getElementById('ej-gif-preview');
        prev.src   = e.target.result;
        prev.style.display = 'block';
    };
    reader.readAsDataURL(file);
}

function toggleDia(header) {
    const contenido = header.nextElementSibling;
    const flecha    = header.querySelector('.dia-flecha');
    const abierto   = contenido.style.maxHeight !== '0px' && contenido.style.maxHeight !== '';
    if (abierto) {
        contenido.style.maxHeight = '0';
        flecha.classList.remove('abierto');
    } else {
        contenido.style.maxHeight = contenido.scrollHeight + 'px';
        flecha.classList.add('abierto');
    }
}

// Abrir el primer día de cada rutina por defecto
document.querySelectorAll('.rutina-card').forEach(card => {
    const primerHeader = card.querySelector('.dia-header');
    if (primerHeader) {
        const contenido = primerHeader.nextElementSibling;
        const flecha    = primerHeader.querySelector('.dia-flecha');
        contenido.style.maxHeight = contenido.scrollHeight + 'px';
        flecha.classList.add('abierto');
    }
});

function abrirModalEditar(id, nombre, series, reps, peso) {
    document.getElementById('editar-ejercicio-id').value = id;
    document.getElementById('editar-nombre-ejercicio').textContent = nombre;
    document.getElementById('editar-series').value = series;
    document.getElementById('editar-reps').value = reps;
    document.getElementById('editar-peso').value = peso;
    document.getElementById('modal-editar-ejercicio').classList.add('visible');
}
function cerrarModalEditar() {
    document.getElementById('modal-editar-ejercicio').classList.remove('visible');
}
async function guardarEdicionEjercicio() {
    const id     = document.getElementById('editar-ejercicio-id').value;
    const series = document.getElementById('editar-series').value;
    const reps   = document.getElementById('editar-reps').value;
    const peso   = document.getElementById('editar-peso').value;
    const res = await fetch('../api/editar_ejercicio.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id, series, repeticiones: reps, peso_kg: peso })
    });
    const data = await res.json();
    if (data.ok) { cerrarModalEditar(); location.reload(); }
}