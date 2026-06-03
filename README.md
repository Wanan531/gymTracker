# 🏋️ GymTracker

Aplicación web de seguimiento de entrenamientos deportivos desarrollada como Proyecto de Fin de Ciclo (DAW).

![PHP](https://img.shields.io/badge/PHP-7.4+-777BB4?style=flat&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-5.7+-4479A1?style=flat&logo=mysql&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-ES6+-F7DF1E?style=flat&logo=javascript&logoColor=black)
![Groq](https://img.shields.io/badge/IA-Groq-FF6B35?style=flat)

---

## 📋 Descripción

GymTracker permite gestionar rutinas de entrenamiento personalizadas, registrar sesiones en tiempo real y consultar el progreso a lo largo del tiempo. Incluye generación automática de rutinas mediante inteligencia artificial a través de la API de Groq.

---

## ✨ Funcionalidades

- 🔐 Registro e inicio de sesión con contraseñas hasheadas (bcrypt)
- 💪 Gestión completa de ejercicios (CRUD) con GIFs y grupos musculares
- 📅 Creación de rutinas personalizadas organizadas por días de la semana
- ⏱️ Pantalla de entrenamiento en tiempo real con cronómetro, timers de descanso y seguimiento de series
- 🤖 Generación automática de rutinas con IA (Groq)
- 📊 Historial de sesiones y estadísticas de progreso
- 🏆 Sistema de logros
- 🧮 Calculadora de calorías y macronutrientes
- 👤 Perfil de usuario personalizable

---

## 🗂️ Estructura del proyecto

```
gymtracker/
├── api/                   — Scripts PHP de la API interna (CRUD + IA)
├── assets/
│   ├── css/               — Hojas de estilo
│   ├── js/                — Scripts JavaScript
│   ├── img/ejercicios/    — GIFs e imágenes de ejercicios
│   └── uploads/           — Imágenes subidas por usuarios (no incluidas en el repo)
├── config/
│   ├── db.php             — Configuración de BD (crear desde db_example.php)
│   └── db_example.php     — Plantilla de configuración
├── includes/              — Componentes reutilizables (header, footer, auth)
├── pages/                 — Páginas de la aplicación
├── .env                   — Variables de entorno (no incluido en el repo)
└── index.php              — Punto de entrada
```

---

## 🚀 Instalación

### Requisitos previos

- PHP 7.4 o superior
- MySQL 5.7 o superior
- Servidor web Apache o Nginx
- Cuenta en [Groq](https://console.groq.com) para la funcionalidad de IA

### Pasos

**1. Clonar el repositorio**

```bash
git clone https://github.com/Wanan531/gymtracker.git
cd gymtracker
```

**2. Crear la base de datos**

```bash
mysql -u root -p < sql/gymtracker.sql
```

O importar el archivo desde phpMyAdmin.

**3. Configurar la conexión a la base de datos**

```bash
cp config/db_example.php config/db.php
```

Editar `config/db.php` con tus credenciales:

```php
$host     = 'localhost';
$dbname   = 'gymtracker';
$user     = 'tu_usuario';
$password = 'tu_contraseña';
```

**4. Configurar la API de Groq**

Crear el archivo `.env` en la raíz del proyecto:

```
GROQ_API_KEY=tu_api_key_de_groq
```

**5. Crear las carpetas de uploads**

```bash
mkdir -p assets/uploads/avatars assets/uploads/ejercicios
```

**6. Acceder a la aplicación**

```
http://localhost/gymtracker
```

---

## 🔒 Seguridad

- Contraseñas almacenadas con `password_hash()` (bcrypt)
- Consultas SQL con PDO y sentencias preparadas (prevención de SQL Injection)
- Control de sesiones con validación server-side
- Archivos sensibles excluidos del repositorio mediante `.gitignore`

---

## 🛠️ Tecnologías utilizadas

| Capa | Tecnología |
|---|---|
| Backend | PHP 7.4+ |
| Base de datos | MySQL 5.7+ |
| Frontend | HTML5, CSS3, JavaScript ES6+ |
| Tipografía | Syne + DM Mono (Google Fonts) |
| IA | Groq API |
| Servidor (dev) | WSL2 |

---

## 👤 Autor

**Wanan531** — Proyecto de Fin de Ciclo · Desarrollo de Aplicaciones Web (DAW)

🔗 [github.com/Wanan531](https://github.com/Wanan531)

---

## 📄 Licencia

Este proyecto se ha desarrollado con fines educativos.
