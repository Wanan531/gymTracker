<?php if (!isset($_SESSION)) session_start(); ?>

<!-- Aplicar tema antes de renderizar para evitar flash -->
<script>
    (function() {
        const tema = localStorage.getItem('tema');
        if (tema === 'light') document.documentElement.classList.add('light-pre');
    })();
</script>
<style>
    /* Evita el flash blanco al cargar en light mode */
    html.light-pre body { background: #f0f4ee; }

    .hamburger {
        display: none;
        background: none;
        border: none;
        font-size: 1.8rem;
        cursor: pointer;
        color: inherit;
    }

    @media (max-width: 768px) {
        .hamburger {
            display: block;
        }

        .navbar-links {
            display: none;
            flex-direction: column;
            position: absolute;
            top: 60px;
            right: 0;
            width: 200px;
            background: var(--color-fondo, #fff);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            border-radius: 8px;
            padding: 1rem;
            gap: 0.5rem;
            z-index: 999;
        }

        .navbar-links.open {
            display: flex;
        }

        .navbar {
            position: relative;
        }
    }
</style>

<header class="navbar">
    <a href="dashboard.php" class="navbar-brand">gYmtraCker</a>
    <button class="hamburger" id="hamburger" onclick="toggleMenu()">☰</button>
    <nav class="navbar-links" id="navbar-links">
        <a href="rutinas.php">Rutinas</a>
        <a href="progreso.php">Progreso</a>
        <a href="logros.php">Logros</a>
        <a href="historial.php">Historial</a>
        <a href="calculadora.php">Calculadora</a>
        <a href="perfil.php">Perfil</a>
        <button class="btn-theme" onclick="toggleTema()" id="btn-tema">◑ Oscuro</button>
    </nav>
</header>

<script>
    // Aplicar tema guardado al cargar
    (function() {
        const tema = localStorage.getItem('tema') || 'dark';
        if (tema === 'light') {
            document.body.classList.add('light');
            document.getElementById('btn-tema').innerHTML = '<span id="tema-label">Claro</span>';
        }
        document.documentElement.classList.remove('light-pre');
    })();

    function toggleTema() {
        const esLight = document.body.classList.toggle('light');
        const btn     = document.getElementById('btn-tema');
       if (esLight) {
            localStorage.setItem('tema', 'light');
            btn.textContent = '◑ Claro';
        } else {
            localStorage.setItem('tema', 'dark');
            btn.textContent = '◑ Oscuro';
        }
    }

    function toggleMenu() {
        const nav = document.getElementById('navbar-links');
        nav.classList.toggle('open');
    }

    // Cierra el menú al hacer clic fuera
    document.addEventListener('click', function(e) {
        const nav = document.getElementById('navbar-links');
        const hamburger = document.getElementById('hamburger');
        if (!nav.contains(e.target) && !hamburger.contains(e.target)) {
            nav.classList.remove('open');
        }
    });
</script>