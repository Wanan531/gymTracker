<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GymTracker — Legal Modal</title>
    <style>
        .btn-legal {
            background: none;
            border: none;
            cursor: pointer;
            font-family: var(--font-mono);
            font-size: 11px;
            color: var(--text3);
            text-decoration: underline;
            text-underline-offset: 3px;
            padding: 0;
            transition: color 0.2s;
        }
        .btn-legal:hover { color: var(--text2); }
    </style>
</head>
<body>

<footer class="footer" style="text-align:center; padding: 24px 16px 8px; border-top: 0.5px solid var(--border);">
    <p>GymTracker &copy; <?= date('Y') ?></p>
    <br>
    <div style="display:flex; justify-content:center; gap:20px; flex-wrap:wrap;">
        <button onclick="abrirModal('terminos')"   class="btn-legal">Términos y condiciones</button>
        <button onclick="abrirModal('privacidad')" class="btn-legal">Política de privacidad</button>
        <button onclick="abrirModal('cookies')"    class="btn-legal">Política de cookies</button>
    </div>
</footer>

<!-- Modal legal -->
<div id="modal-legal" onclick="cerrarModal(event)" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); backdrop-filter:blur(6px); z-index:300; align-items:center; justify-content:center; padding:16px;">
    <div style="background:var(--surface2); border:0.5px solid var(--border2); border-radius:18px; width:100%; max-width:480px; max-height:80vh; display:flex; flex-direction:column; overflow:hidden;">
        <div style="display:flex; justify-content:space-between; align-items:center; padding:18px 20px; border-bottom:0.5px solid var(--border);">
            <h3 id="modal-titulo" style="font-size:15px; font-weight:600; color:var(--text); margin:0;"></h3>
            <button onclick="cerrarModal(null,true)" style="background:none;border:none;cursor:pointer;color:var(--text3);font-size:20px;line-height:1;">✕</button>
        </div>
        <div id="modal-cuerpo" style="overflow-y:auto; padding:20px; font-size:13px; line-height:1.7; color:var(--text2);"></div>
    </div>
</div>

<script>
const contenidosLegales = {
    terminos: {
        titulo: 'Términos y condiciones',
        cuerpo: `<strong>Términos y condiciones de uso — GymTracker</strong>

Última actualización: [FECHA]

<strong>1. Aceptación de los términos</strong>
Al acceder y utilizar GymTracker ("la Plataforma"), aceptas quedar vinculado por estos Términos y Condiciones. Si no estás de acuerdo con alguno de ellos, te rogamos que no utilices el servicio.

<strong>2. Descripción del servicio</strong>
GymTracker es una aplicación web que permite a los usuarios registrar, organizar y hacer seguimiento de sus entrenamientos y progreso físico.

<strong>3. Registro de cuenta</strong>
Para acceder a las funcionalidades completas de la Plataforma, es necesario crear una cuenta con una dirección de correo electrónico válida y una contraseña. Eres responsable de mantener la confidencialidad de tus credenciales y de todas las actividades realizadas desde tu cuenta.

<strong>4. Uso aceptable</strong>
Te comprometes a utilizar GymTracker únicamente para fines lícitos y de acuerdo con estos términos. Queda prohibido: (a) utilizar la Plataforma de forma que pueda dañar, deshabilitar o sobrecargar el servicio; (b) intentar acceder sin autorización a sistemas o redes relacionados; (c) transmitir contenido ofensivo, ilegal o no autorizado.

<strong>5. Propiedad intelectual</strong>
Todo el contenido, diseño, código y materiales de GymTracker son propiedad de sus titulares y están protegidos por la legislación vigente en materia de propiedad intelectual. No se permite su reproducción sin autorización expresa.

<strong>6. Limitación de responsabilidad</strong>
GymTracker proporciona el servicio "tal como está". No garantizamos que el servicio esté disponible de forma ininterrumpida ni libre de errores. En ningún caso seremos responsables de daños indirectos, incidentales o consecuentes derivados del uso del servicio.

<strong>7. Modificaciones</strong>
Nos reservamos el derecho de modificar estos términos en cualquier momento. Notificaremos los cambios relevantes mediante aviso en la Plataforma o por correo electrónico.

<strong>8. Legislación aplicable</strong>
Estos términos se rigen por la legislación española. Para cualquier controversia, las partes se someten a los juzgados y tribunales competentes.

<strong>9. Contacto</strong>
Para cualquier consulta sobre estos términos puedes contactarnos en: [EMAIL DE CONTACTO]`
    },

    privacidad: {
        titulo: 'Política de privacidad',
        cuerpo: `<strong>Política de privacidad — GymTracker</strong>

Última actualización: [FECHA]

<strong>1. Responsable del tratamiento</strong>
[NOMBRE O RAZÓN SOCIAL] con domicilio en [DIRECCIÓN] y correo electrónico [EMAIL] es el responsable del tratamiento de tus datos personales.

<strong>2. Datos que recopilamos</strong>
Al registrarte y utilizar GymTracker recopilamos los siguientes datos:
— Dirección de correo electrónico y contraseña (cifrada) para la creación de cuenta.
— Datos de entrenamiento que introduces voluntariamente (ejercicios, series, repeticiones, peso, fechas).
— Datos técnicos de uso: dirección IP, tipo de navegador, páginas visitadas y duración de sesión.

<strong>3. Finalidad y base legal</strong>
Tratamos tus datos para: (a) prestar el servicio de seguimiento de entrenamientos — base legal: ejecución de contrato; (b) mejorar la Plataforma mediante análisis de uso — base legal: interés legítimo; (c) enviarte comunicaciones sobre el servicio — base legal: consentimiento.

<strong>4. Conservación de los datos</strong>
Conservamos tus datos mientras tu cuenta esté activa. Tras la eliminación de la cuenta, los datos se eliminan en un plazo máximo de 30 días, salvo obligación legal de conservación.

<strong>5. Destinatarios</strong>
Tus datos no se venden ni ceden a terceros. Pueden ser accesibles por proveedores de servicios técnicos (alojamiento, análisis) que actúan como encargados del tratamiento bajo las garantías adecuadas.

<strong>6. Tus derechos</strong>
Tienes derecho a acceder, rectificar, suprimir, oponerte y solicitar la portabilidad de tus datos. Puedes ejercerlos escribiendo a [EMAIL]. Si consideras que el tratamiento no es conforme, puedes reclamar ante la Agencia Española de Protección de Datos (aepd.es).

<strong>7. Seguridad</strong>
Aplicamos medidas técnicas y organizativas para proteger tus datos frente a accesos no autorizados, pérdida o destrucción, incluyendo cifrado de contraseñas y conexiones HTTPS.

<strong>8. Cambios en esta política</strong>
Cualquier modificación relevante será comunicada con antelación razonable a través de la Plataforma o por correo electrónico.`
    },

    cookies: {
        titulo: 'Política de cookies',
        cuerpo: `<strong>Política de cookies — GymTracker</strong>

Última actualización: [FECHA]

<strong>¿Qué son las cookies?</strong>
Las cookies son pequeños archivos de texto que se almacenan en tu dispositivo cuando visitas un sitio web. Permiten recordar información sobre tu visita para mejorar la experiencia de uso.

<strong>Cookies que utilizamos</strong>

<u>Cookies técnicas (necesarias)</u>
Son imprescindibles para el funcionamiento de la Plataforma. Incluyen la cookie de sesión que mantiene tu inicio de sesión activo. No requieren consentimiento.

<u>Cookies analíticas (opcionales)</u>
Utilizamos herramientas de análisis para entender cómo se usa la Plataforma (páginas visitadas, tiempo de sesión, errores). Esta información es agregada y anónima.

<u>Cookies de preferencias (opcionales)</u>
Almacenan ajustes como el idioma o el tema visual elegido para personalizar tu experiencia.

<strong>Gestión de cookies</strong>
Puedes aceptar o rechazar las cookies opcionales a través del panel de ajustes de la Plataforma. También puedes configurar tu navegador para bloquear o eliminar cookies, aunque esto puede afectar al funcionamiento de algunas funcionalidades.

<strong>Cookies de terceros</strong>
Si utilizamos servicios externos de análisis (como Google Analytics u otros), estos pueden establecer sus propias cookies sujetas a sus respectivas políticas de privacidad.

<strong>Más información</strong>
Para cualquier consulta sobre el uso de cookies puedes contactarnos en: [EMAIL DE CONTACTO]`
    }
};

function abrirModal(tipo) {
    const d = contenidosLegales[tipo];
    document.getElementById('modal-titulo').textContent = d.titulo;
    document.getElementById('modal-cuerpo').innerHTML   = d.cuerpo;
    document.getElementById('modal-legal').style.display = 'flex';
}

function cerrarModal(e, forzar) {
    if (forzar || e.target === document.getElementById('modal-legal'))
        document.getElementById('modal-legal').style.display = 'none';
}

document.addEventListener('keydown', e => { if (e.key === 'Escape') cerrarModal(null, true); });
</script>

</body>
</html>