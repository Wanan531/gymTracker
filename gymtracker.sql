-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 13-05-2026 a las 12:32:59
-- Versión del servidor: 10.4.28-MariaDB
-- Versión de PHP: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `gymtracker`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ejercicio`
--

CREATE TABLE `ejercicio` (
  `id` int(11) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `grupo_muscular` varchar(100) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `gif_url` varchar(500) DEFAULT NULL,
  `external_id` varchar(50) DEFAULT NULL,
  `youtube_id` varchar(20) DEFAULT NULL,
  `es_predefinido` tinyint(1) DEFAULT 0,
  `usuario_id` int(11) DEFAULT NULL,
  `tipo` enum('reps','tiempo') NOT NULL DEFAULT 'reps'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ejercicio`
--

INSERT INTO `ejercicio` (`id`, `nombre`, `grupo_muscular`, `descripcion`, `gif_url`, `external_id`, `youtube_id`, `es_predefinido`, `usuario_id`, `tipo`) VALUES
(1, 'Press banca', 'Pecho', 'Ejercicio básico de empuje horizontal', 'assets/img/ejercicios/press-banca.gif', NULL, NULL, 1, NULL, 'reps'),
(2, 'Press inclinado', 'Pecho', 'Press con banco inclinado 45 grados', 'assets/img/ejercicios/press-inclinado.gif', NULL, NULL, 1, NULL, 'reps'),
(3, 'Aperturas con mancuernas', 'Pecho', 'Ejercicio de aislamiento para pecho', 'assets/img/ejercicios/aperturas-mancuernas.gif', NULL, NULL, 1, NULL, 'reps'),
(4, 'Fondos en paralelas', 'Pecho', 'Ejercicio con peso corporal para pecho y triceps', 'assets/img/ejercicios/fondos-paralelas.gif', NULL, NULL, 1, NULL, 'reps'),
(5, 'Dominadas', 'Espalda', 'Ejercicio básico de tirón vertical', 'assets/img/ejercicios/dominadas.gif', '0015', NULL, 1, NULL, 'reps'),
(6, 'Remo con barra', 'Espalda', 'Ejercicio básico de tirón horizontal', 'assets/img/ejercicios/remo-barra.gif', NULL, NULL, 1, NULL, 'reps'),
(7, 'Remo con mancuerna', 'Espalda', 'Remo unilateral con mancuerna', 'assets/img/ejercicios/remo-mancuerna.gif', NULL, NULL, 1, NULL, 'reps'),
(8, 'Jalón al pecho', 'Espalda', 'Tirón vertical en polea', 'assets/img/ejercicios/jalon-pecho.gif', NULL, NULL, 1, NULL, 'reps'),
(9, 'Press militar', 'Hombros', 'Press vertical con barra', 'assets/img/ejercicios/press-militar.gif', NULL, NULL, 1, NULL, 'reps'),
(10, 'Elevaciones laterales', 'Hombros', 'Aislamiento del deltoides lateral', 'assets/img/ejercicios/elevaciones-laterales.gif', NULL, NULL, 1, NULL, 'reps'),
(11, 'Press Arnold', 'Hombros', 'Press con rotación de mancuernas', 'assets/img/ejercicios/press-arnold.gif', NULL, NULL, 1, NULL, 'reps'),
(12, 'Sentadilla con barra', 'Pierna', 'De pie con las piernas separadas a la anchura de los hombros y una barra sobre el trapecio, ayudando a mantenerla con las manos a los lados de los hombros. Desciende flexionando las rodillas 90º, sin descansar abajo, subiendo de nuevo y estirando bien arriba.', 'assets/img/ejercicios/sentadilla-barra.gif', NULL, NULL, 1, NULL, 'reps'),
(13, 'Prensa de piernas', 'Pierna', 'Sentadilla en máquina', 'assets/img/ejercicios/prensa-piernas.gif', NULL, NULL, 1, NULL, 'reps'),
(14, 'Peso muerto', 'Pierna', 'Ejercicio básico de cadena posterior', 'assets/img/ejercicios/peso-muerto.gif', '0032', NULL, 1, NULL, 'reps'),
(15, 'Zancadas', 'Pierna', 'Ejercicio unilateral de tren inferior', 'assets/img/ejercicios/zancadas.gif', NULL, NULL, 1, NULL, 'reps'),
(16, 'Curl femoral', 'Pierna', 'Aislamiento de isquiotibiales', 'assets/img/ejercicios/curl-femoral.gif', NULL, NULL, 1, NULL, 'reps'),
(17, 'Extensiones de cuádriceps', 'Pierna', 'Aislamiento de cuádriceps', 'assets/img/ejercicios/extensiones-cuadriceps.gif', NULL, NULL, 1, NULL, 'reps'),
(18, 'Curl de bíceps', 'Bíceps', 'Ejercicio básico de bíceps', 'assets/img/ejercicios/curl-biceps.gif', NULL, NULL, 1, NULL, 'reps'),
(19, 'Curl martillo', 'Bíceps', 'Curl con agarre neutro', 'assets/img/ejercicios/curl-martillo.gif', NULL, NULL, 1, NULL, 'reps'),
(20, 'Curl en polea', 'Bíceps', 'Curl de bíceps en polea baja', 'assets/img/ejercicios/curl-polea.gif', NULL, NULL, 1, NULL, 'reps'),
(21, 'Extensión de tríceps en polea', 'Tríceps', 'Aislamiento de tríceps en polea', 'assets/img/ejercicios/extension-triceps-poleas.gif', NULL, NULL, 1, NULL, 'reps'),
(22, 'Press francés', 'Tríceps', 'Extensión de tríceps con barra Z', 'assets/img/ejercicios/press-frances.gif', NULL, NULL, 1, NULL, 'reps'),
(23, 'Fondos entre bancos', 'Tríceps', 'Ejercicio con peso corporal para tríceps', 'assets/img/ejercicios/fondos-entre-bancos.gif', NULL, NULL, 1, NULL, 'reps'),
(24, 'Plancha', 'Core', 'Ejercicio isométrico de core', 'assets/img/ejercicios/plancha.gif', '0464', NULL, 1, NULL, 'tiempo'),
(25, 'Crunch abdominal', 'Core', 'Ejercicio básico de abdominales', 'assets/img/ejercicios/crunch.gif', '0175', NULL, 1, NULL, 'reps'),
(26, 'Elevación de piernas', 'Core', 'Ejercicio de abdomen inferior', 'assets/img/ejercicios/elevacion-piernas.gif', NULL, NULL, 1, NULL, 'reps'),
(27, 'Extensión de tríceps con agarre', 'Tríceps', 'Consiste en empujar una barra o cuerda hacia abajo, manteniendo los codos fijos y pegados al cuerpo, extendiendo totalmente el codo al final para maximizar la contracción del músculo.', 'assets/img/ejercicios/extension-triceps.gif', NULL, NULL, 1, NULL, 'reps'),
(28, 'Extensión de tríceps con agarre inverso', 'Tríceps', 'ejercicio de aislamiento que enfoca el tríceps braquial, enfatizando la cabeza medial y lateral al mantener las palmas hacia arriba.', 'assets/img/ejercicios/extension-triceps-inverso.gif', NULL, NULL, 1, NULL, 'reps'),
(29, 'Sit up', 'Core', 'Ejercicio de core que se realiza al sentarse en el suelo y levantar el torso hacia las piernas, trabajando los músculos del abdomen.', 'assets/img/ejercicios/sit-up.gif', NULL, NULL, 1, NULL, 'reps'),
(30, 'Crunch en máquina', 'Core', 'Ejercicio de core que se realiza en una máquina específica para abdominales, donde se contraen los músculos del abdomen al flexionar el torso hacia adelante.', 'assets/img/ejercicios/crunch-maquina.gif', NULL, NULL, 1, NULL, 'reps'),
(31, 'Despliega con rueda', 'Core', 'Ejercicio de core que se realiza con una rueda de abdominales, donde se extiende el cuerpo hacia adelante y luego se regresa a la posición inicial, trabajando los músculos del abdomen y la parte baja de la espalda.', 'assets/img/ejercicios/despliega-rueda.gif', NULL, NULL, 1, NULL, 'reps'),
(32, 'Crunch bicicleta', 'Core', 'Ejercicio de core que se realiza acostado en el suelo, levantando los hombros y alternando el toque del codo con la rodilla opuesta, trabajando los músculos del abdomen y los oblicuos.', 'assets/img/ejercicios/crunch-bicicleta.gif', NULL, NULL, 1, NULL, 'reps'),
(33, 'Toques de talón alternos', 'Core', 'Ejercicio de core que se realiza acostado en el suelo, levantando los hombros y alternando el toque del talón con las manos, trabajando los músculos del abdomen y los oblicuos.', 'assets/img/ejercicios/toques-talon-alternos.gif', NULL, NULL, 1, NULL, 'reps'),
(34, 'Curl alterno con mancuernas en banco inclinado', 'Bíceps', 'Ejercicio de bíceps que se realiza en un banco inclinado, levantando mancuernas de forma alternativa, trabajando los músculos del bíceps.', 'assets/img/ejercicios/curl-alterno-banco-inclinado.gif', NULL, NULL, 1, NULL, 'reps'),
(35, 'Elevación frontal con mancuernas', 'Hombros', 'Ejercicio de hombros que se realiza levantando mancuernas hacia el frente, trabajando los músculos del deltoides anterior.', '/assets/img/ejercicios/elevacion-frontal-mancuernas.gif', NULL, NULL, 1, NULL, 'reps'),
(36, 'Curls bíceps con barra', 'Bíceps', 'Ejercicio de bíceps que se realiza levantando una barra con ambas manos, trabajando los músculos del bíceps.', 'assets/img/ejercicios/curls-biceps-barra.gif', NULL, NULL, 1, NULL, 'reps'),
(37, 'Curls bíceps con barra en banco inclinado', 'Bíceps', 'Ejercicio de bíceps que se realiza en un banco inclinado, levantando una barra con ambas manos, trabajando los músculos del bíceps.', 'assets/img/ejercicios/curls-biceps-barra-inclinado.gif', NULL, NULL, 1, NULL, 'reps'),
(38, 'Puente de glúteo', 'Core', 'Ejercicio de glúteos que se realiza acostado en el suelo, levantando las caderas hacia el techo, trabajando los músculos del glúteo.', 'assets/img/ejercicios/puente-gluteo.gif', NULL, NULL, 1, NULL, 'reps'),
(39, 'Sentadilla hack', 'Piernas', 'Ejercicio de piernas que se realiza con una barra en la parte posterior de los hombros, bajando el cuerpo como si se estuviera sentado en una silla, trabajando los músculos de las piernas y glúteos.', 'assets/img/ejercicios/sentadilla-hack.gif', NULL, NULL, 1, NULL, 'reps'),
(40, 'Hip thrust', 'Core', 'Ejercicio de glúteos que se realiza acostado en el suelo, levantando las caderas hacia el techo, trabajando los músculos del glúteo.', 'assets/img/ejercicios/hip-thrust.gif', NULL, NULL, 1, NULL, 'reps'),
(41, 'Elevación de talones sentado', 'Piernas', 'Ejercicio de piernas que se realiza sentado en el suelo, levantando los talones hacia el techo, trabajando los músculos de las pantorrillas.', 'assets/img/ejercicios/elevacion-talon-sentado.gif', NULL, NULL, 1, NULL, 'reps'),
(42, 'Shrug con barra', 'Hombros', 'Ejercicio de hombros que se realiza levantando los hombros hacia las orejas mientras se sostiene una barra con ambas manos, trabajando los músculos del trapecio.', 'assets/img/ejercicios/shrug-barra.gif', NULL, NULL, 1, NULL, 'reps'),
(43, 'Shrug con barra por detrás', 'Hombros', 'Ejercicio de hombros que se realiza levantando los hombros hacia las orejas mientras se sostiene una barra por detrás con ambas manos, trabajando los músculos del trapecio.', 'assets/img/ejercicios/shrug-barra-detras.gif', NULL, NULL, 1, NULL, 'reps'),
(44, 'Sentadilla sobre banco', 'Piernas', 'Ejercicio de piernas que se realiza con una barra en la parte posterior de los hombros, bajando el cuerpo hasta que los glúteos toquen un banco detrás, trabajando los músculos de las piernas y glúteos.', 'assets/img/ejercicios/sentadilla-sobre-banco.gif', NULL, NULL, 1, NULL, 'reps'),
(45, 'Set up', 'Core', 'Ejercicio de core que se realiza al sentarse en el suelo y levantar el torso hacia las piernas, trabajando los músculos del abdomen.', 'assets/img/ejercicios/set-up.gif', NULL, NULL, 1, NULL, 'reps'),
(46, 'Zancada caminando', 'Piernas', 'Ejercicio de piernas que se realiza dando pasos largos hacia adelante, bajando el cuerpo hasta que la rodilla trasera casi toque el suelo, trabajando los músculos de las piernas y glúteos.', 'assets/img/ejercicios/zancada-caminando.gif', NULL, NULL, 1, NULL, 'reps'),
(47, 'Pull-over con barra', 'Espalda', 'Ejercicio de espalda que se realiza acostado en un banco, levantando una barra desde detrás de la cabeza hacia el pecho, trabajando los músculos de la espalda y el pecho.', 'assets/img/ejercicios/pullover-barra.gif', NULL, NULL, 1, NULL, 'reps'),
(48, 'Pull-over con mancuerna', 'Espalda', 'Ejercicio de espalda que se realiza acostado en un banco, levantando una mancuerna desde detrás de la cabeza hacia el pecho, trabajando los músculos de la espalda y el pecho.', 'assets/img/ejercicios/pullover-mancuerna.gif', NULL, NULL, 1, NULL, 'reps'),
(49, 'Elevación de rodilla y cadera', 'Core', 'Ejercicio de core que se realiza acostado en el suelo, levantando las rodillas hacia el pecho y luego extendiendo las piernas, trabajando los músculos del abdomen y la parte baja de la espalda.', 'assets/img/ejercicios/elevacion-rodilla-cadera.gif', NULL, NULL, 1, NULL, 'reps'),
(50, 'Elevación Posterior con Mancuernas con Cabeza Apoyada en Banco', 'Hombros', 'Ejercicio de hombros que se realiza acostado boca abajo en un banco, levantando mancuernas hacia atrás, trabajando los músculos del deltoides posterior.', 'assets/img/ejercicios/elevacion-posterior-mancuernas-banco.gif', NULL, NULL, 1, NULL, 'reps'),
(51, 'Elevaciones Laterales en Polea Baja', 'Hombros', 'Ejercicio de hombros que se realiza levantando mancuernas hacia los lados, trabajando los músculos del deltoides lateral.', 'assets/img/ejercicios/elevaciones-laterales-polea-baja.gif', NULL, NULL, 1, NULL, 'reps'),
(52, 'Remo con Barra a una Mano', 'Espalda', 'Ejercicio de espalda que se realiza levantando una barra con una sola mano, trabajando los músculos de la espalda.', 'assets/img/ejercicios/remo-barra-una-mano.gif', NULL, NULL, 1, NULL, 'reps'),
(53, 'Butterfly con maquina', 'Pecho', 'Ejercicio de pecho que se realiza en una máquina específica para pecho, donde se juntan las manijas hacia el centro del cuerpo, trabajando los músculos del pecho.', 'assets/img/ejercicios/butterfly-maquina.gif', NULL, NULL, 1, NULL, 'reps'),
(54, 'Cruce de Poleas', 'Pecho', 'Ejercicio de pecho que se realiza con poleas, donde se cruzan las manijas hacia el centro del cuerpo, trabajando los músculos del pecho.', 'assets/img/ejercicios/cruce-poleas.gif', NULL, NULL, 1, NULL, 'reps'),
(55, 'Encogimiento en Polea Alta', 'Hombros', 'Ejercicio de hombros que se realiza levantando los hombros hacia las orejas mientras se sostiene una barra en una polea alta, trabajando los músculos del trapecio.', 'assets/img/ejercicios/encogimiento-polea-alta.gif', NULL, NULL, 1, NULL, 'reps'),
(56, 'Curl de Martillo en Polea', 'Bíceps', 'Ejercicio de bíceps que se realiza levantando una barra con agarre de martillo en una polea, trabajando los músculos del bíceps y el braquial.', 'assets/img/ejercicios/curl-martillo-polea.gif', NULL, NULL, 1, NULL, 'reps'),
(57, 'Extensión de tríceps en polea alta con banco inclinado', 'Tríceps', 'Ejercicio de tríceps que se realiza en una polea alta con un banco inclinado, donde se extienden los brazos hacia abajo, trabajando los músculos del tríceps.', 'assets/img/ejercicios/extension-triceps-polea-inclinado.gif', NULL, NULL, 1, NULL, 'reps'),
(58, 'Rotación interna de hombro en polea baja', 'Hombros', 'Ejercicio de hombros que se realiza con una polea baja, donde se rota el brazo hacia adentro, trabajando los músculos del manguito rotador.', 'assets/img/ejercicios/rotacion-interna-hombro-polea-baja.gif', NULL, NULL, 1, NULL, 'reps'),
(59, 'Iron Cross', 'Pecho', 'Ejercicio de pecho que se realiza con una barra en la parte superior del cuerpo, cruzando los brazos hacia el centro del pecho, trabajando los músculos del pecho.', 'assets/img/ejercicios/iron-cross.gif', NULL, NULL, 1, NULL, 'reps'),
(60, 'Extensión de tríceps acostado con polea', 'Tríceps', 'Ejercicio de tríceps que se realiza acostado en un banco, extendiendo los brazos hacia arriba mientras se sostiene una barra en una polea, trabajando los músculos del tríceps.', 'assets/img/ejercicios/extension-triceps-acostado-polea.gif', NULL, NULL, 1, NULL, 'reps'),
(61, 'Extensión de tríceps a una mano en polea', 'Tríceps', 'Ejercicio de tríceps que se realiza con una polea, donde se extiende un brazo hacia abajo mientras se sostiene la manija, trabajando los músculos del tríceps.', 'assets/img/ejercicios/extension-triceps-polea-una-mano.gif', NULL, NULL, 1, NULL, 'reps'),
(62, 'Curl predicador en polea', 'Bíceps', 'Ejercicio de bíceps que se realiza en una máquina de curl predicador con una polea, donde se levanta la barra hacia el pecho, trabajando los músculos del bíceps.', 'assets/img/ejercicios/curl-predicador-polea.gif', NULL, NULL, 1, NULL, 'reps'),
(63, 'Pájaros en polea de pie', 'Hombros', 'Ejercicio de hombros que se realiza con una polea, donde se levantan los brazos hacia los lados mientras se sostiene la manija, trabajando los músculos del deltoides posterior.', 'assets/img/ejercicios/pajaros-polea-de-pie.gif', NULL, NULL, 1, NULL, 'reps'),
(64, 'Crunch inverso con polea', 'Core', 'Ejercicio de core que se realiza acostado en el suelo, levantando las piernas hacia el pecho mientras se sostiene una barra en una polea, trabajando los músculos del abdomen y la parte baja de la espalda.', 'assets/img/ejercicios/crunch-inverso-polea.gif', NULL, NULL, 1, NULL, 'reps'),
(65, 'Extensión de tríceps sobre la cabeza con cuerda en polea', 'Tríceps', 'Ejercicio de tríceps que se realiza con una polea, donde se extienden los brazos hacia arriba mientras se sostiene una cuerda, trabajando los músculos del tríceps.', 'assets/img/ejercicios/extension-triceps-polea-cuerda.gif', NULL, NULL, 1, NULL, 'reps'),
(66, 'Remo para deltoides posterior con cuerda en polea', 'Hombros', 'Ejercicio de hombros que se realiza con una polea, donde se levanta la cuerda hacia el pecho mientras se mantiene el torso inclinado hacia adelante, trabajando los músculos del deltoides posterior.', 'assets/img/ejercicios/remo-deltoides-posterior-polea.gif', NULL, NULL, 1, NULL, 'reps'),
(67, 'Crunch abdominal sentado en polea', 'Core', 'Ejercicio de core que se realiza sentado en una máquina de crunch con polea, donde se contraen los músculos del abdomen al flexionar el torso hacia adelante.', 'assets/img/ejercicios/crunch-abdominal-sentado-polea.gif', NULL, NULL, 1, NULL, 'reps'),
(68, 'Elevaciones laterales sentado en polea', 'Hombros', 'Ejercicio de hombros que se realiza sentado en una máquina de elevaciones laterales con polea, donde se levantan los brazos hacia los lados mientras se sostiene la manija, trabajando los músculos del deltoides lateral.', 'assets/img/ejercicios/elevaciones-laterales-sentado-polea.gif', NULL, NULL, 1, NULL, 'reps'),
(69, 'Press de hombros con cable', 'Hombro', 'Press de hombros utilizando cables', '/assets/img/ejercicios/press-hombros-polea.gif', NULL, NULL, 1, NULL, 'reps'),
(70, 'Encogimientos de hombros con cable', 'Hombro', 'Encogimientos de hombros utilizando cables', '/assets/img/ejercicios/encogimientos-hombros-polea.gif', NULL, NULL, 1, NULL, 'reps'),
(71, 'Curl de muñeca con cable', 'Antebrazo', 'Curl de muñeca utilizando cables', '/assets/img/ejercicios/curl-muneca-polea.gif', NULL, NULL, 1, NULL, 'reps'),
(72, 'Encogimientos de hombros en máquina de gemelos', 'Hombro', 'Encogimientos de hombros utilizando la máquina de gemelos', '/assets/img/ejercicios/encogimientos-hombros-gemelos.gif', NULL, NULL, 1, NULL, 'reps'),
(73, 'Press de banca con agarre cerrado', 'Pecho', 'Press de banca con las manos más juntas para enfatizar los tríceps', '/assets/img/ejercicios/press-banca-agarre-cerrado.gif', NULL, NULL, 1, NULL, 'reps'),
(74, 'Press con barra EZ de agarre cerrado', 'Tríceps', 'Press con barra EZ con las manos más juntas para enfatizar los tríceps', '/assets/img/ejercicios/press-barra-ez-agarre-cerrado.gif', NULL, NULL, 1, NULL, 'reps'),
(75, 'Curl con barra EZ de agarre cerrado', 'Bíceps', 'Curl con barra EZ con las manos más juntas para enfatizar los bíceps', '/assets/img/ejercicios/curl-barra-ez-agarre-cerrado.gif', NULL, NULL, 1, NULL, 'reps'),
(76, 'Jalón al pecho con agarre cerrado', 'Espalda', 'Jalón al pecho con las manos más juntas para enfatizar la parte central de la espalda', '/assets/img/ejercicios/jalon-pecho-agarre-cerrado.gif', NULL, NULL, 1, NULL, 'reps'),
(77, 'Flexiones con agarre cerrado sobre una mancuerna', 'Pecho', 'Flexiones con las manos más juntas sobre una mancuerna para enfatizar los tríceps', '/assets/img/ejercicios/flexiones-agarre-cerrado-mancuerna.gif', NULL, NULL, 1, NULL, 'reps'),
(78, 'Curls de concentración con mancuerna', 'Bíceps', 'Curls de concentración utilizando una mancuerna para aislar los bíceps', '/assets/img/ejercicios/curls-concentracion-mancuerna.gif', NULL, NULL, 1, NULL, 'reps'),
(79, 'Abdominales cruzados', 'Abdominales', 'Abdominales realizados con un movimiento de torsión para trabajar los oblicuos', '/assets/img/ejercicios/abdominales-cruzados.gif', NULL, NULL, 1, NULL, 'reps'),
(80, 'rotadores del hombro', 'Hombro', 'Ejercicio para fortalecer los músculos rotadores del hombro', '/assets/img/ejercicios/rotadores-hombro.gif', NULL, NULL, 1, NULL, 'reps'),
(81, 'Dead Bug', 'Abdominales', 'Ejercicio de abdominales que se realiza acostado boca arriba, levantando las piernas y los brazos en el aire, y alternando el movimiento de las extremidades opuestas', '/assets/img/ejercicios/dead-bug.gif', NULL, NULL, 1, NULL, 'reps'),
(82, 'Rompecráneos', 'Tríceps', 'Ejercicio para trabajar los tríceps, también conocido como \"skull crushers\"', '/assets/img/ejercicios/rompecraneos.gif', NULL, NULL, 1, NULL, 'reps'),
(83, 'Abdominales en banco declinado', 'Abdominales', 'Ejercicio de abdominales realizado en un banco declinado para aumentar la dificultad', '/assets/img/ejercicios/abdominales-banco-declinado.gif', NULL, NULL, 1, NULL, 'reps'),
(84, 'Press de banca declinado con mancuernas', 'Pecho', 'Press de banca realizado en un banco declinado utilizando mancuernas para trabajar la parte inferior del pecho', '/assets/img/ejercicios/press-banca-declinado-mancuernas.gif', NULL, NULL, 1, NULL, 'reps'),
(85, 'Aperturas con mancuernas en banco declinado', 'Pecho', 'Aperturas con mancuernas realizadas en un banco declinado para trabajar la parte inferior del pecho', '/assets/img/ejercicios/aperturas-mancuernas-banco-declinado.gif', NULL, NULL, 1, NULL, 'reps'),
(86, 'Extensión de tríceps con mancuernas en banco declinado', 'Tríceps', 'Extensión de tríceps utilizando mancuernas en un banco declinado para trabajar los tríceps desde una posición diferente', '/assets/img/ejercicios/extension-triceps-mancuernas-banco-declinado.gif', NULL, NULL, 1, NULL, 'reps'),
(87, 'Extensión de tríceps con barra EZ en banco declinado', 'Tríceps', 'Extensión de tríceps utilizando una barra EZ en un banco declinado para trabajar los tríceps desde una posición diferente', '/assets/img/ejercicios/extension-triceps-barra-ez-banco-declinado.gif', NULL, NULL, 1, NULL, 'reps'),
(88, 'Crunch para oblicuos en banco declinado', 'Abdominales', 'Crunch para oblicuos realizado en un banco declinado para trabajar los músculos oblicuos', '/assets/img/ejercicios/crunch-oblicuos-banco-declinado.gif', NULL, NULL, 1, NULL, 'reps'),
(89, 'Flexiones declinadas', 'Pecho', 'Flexiones realizadas con los pies elevados en un banco para aumentar la dificultad y trabajar la parte superior del pecho', '/assets/img/ejercicios/flexiones-declinadas.gif', NULL, NULL, 1, NULL, 'reps'),
(90, 'Crunch inverso en banco declinado', 'Abdominales', 'Crunch inverso realizado en un banco declinado para trabajar los músculos abdominales inferiores', '/assets/img/ejercicios/crunch-inverso-banco-declinado.gif', NULL, NULL, 1, NULL, 'reps'),
(91, 'Press declinado en máquina Smith', 'Pecho', 'Press declinado realizado en una máquina Smith para trabajar la parte inferior del pecho', '/assets/img/ejercicios/press-declinado-maquina-smith.gif', NULL, NULL, 1, NULL, 'reps'),
(92, 'Máquina de fondos para tríceps', 'Tríceps', 'Ejercicio de fondos para tríceps utilizando una máquina específica para este movimiento', '/assets/img/ejercicios/maquina-fondos-triceps.gif', NULL, NULL, 1, NULL, 'reps'),
(93, 'Press de banca con mancuernas', 'Pecho', 'Press de banca realizado con mancuernas para trabajar el pecho de manera más equilibrada y activar más músculos estabilizadores', '/assets/img/ejercicios/press-banca-mancuernas.gif', NULL, NULL, 1, NULL, 'reps');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `logro`
--

CREATE TABLE `logro` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `condicion` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `logro`
--

INSERT INTO `logro` (`id`, `nombre`, `descripcion`, `condicion`) VALUES
(1, 'Primer entreno', 'Completa tu primera entrenamiento', 'sesiones_total >= 1'),
(2, 'Racha de 7 días', '7 días seguidos entrenando', 'racha >= 7'),
(3, 'Racha de 30 días', '30 días entrenando seguidos', 'racha >= 30'),
(4, '10 entrenamientos', 'Completa 10 entrenamientos', 'sesiones_total >= 10'),
(5, '50 entrenamientos', 'Completa 50 entrenamientos', 'sesiones_total >= 50'),
(6, '100 kg en banca', 'Levanta 100 kg en press banca', 'marca_banca >= 100'),
(7, 'Centurión', 'Completa 100 entrenamientos', 'sesiones_total >= 100'),
(8, 'Tonelada de hierro', 'Mueve 1.000 kg de volumen total', 'volumen_total >= 1000'),
(9, '10 toneladas', 'Mueve 10.000 kg de volumen total', 'volumen_total >= 10000'),
(10, '100 toneladas', 'Mueve 100.000 kg de volumen total', 'volumen_total >= 100000'),
(11, '1000 toneladas', 'Mueve 1.000.000 kg de volumen total', 'volumen_total >= 1000000'),
(12, 'Racha de 60 días', '60 días seguidos entrenando', 'racha >= 60'),
(13, 'Racha de 90 días', '90 días seguidos entrenando', 'racha >= 90'),
(14, 'Año sin parar', '365 días seguidos entrenando', 'racha >= 365'),
(15, '200 entrenamientos', 'Completa 200 entrenamientos', 'sesiones_total >= 200'),
(16, '365 entrenamientos', 'Un año de entrenos', 'sesiones_total >= 365'),
(17, '500 entrenamientos', 'Completa 500 entrenamientos', 'sesiones_total >= 500'),
(18, 'Sentadilla élite', 'Levanta 100 kg en sentadilla', 'marca_sentadilla >= 100'),
(19, 'Peso muerto élite', 'Levanta 140 kg en peso muerto', 'marca_peso_muerto >= 140'),
(20, 'Press banca 120 kg', 'Levanta 120 kg en press banca', 'marca_banca >= 120'),
(21, 'Explorador', 'Realiza 10 ejercicios distintos', 'ejercicios_distintos >= 10'),
(22, 'Atleta completo', 'Realiza 25 ejercicios distintos', 'ejercicios_distintos >= 25'),
(23, 'Enciclopedia viviente', 'Realiza 50 ejercicios distintos', 'ejercicios_distintos >= 50'),
(24, '10 horas entrenando', 'Entrena durante 10 horas en total', 'tiempo_total >= 600'),
(25, '100 horas entrenando', 'Entrena durante 100 horas en total', 'tiempo_total >= 6000'),
(26, '1000 horas entrenando', 'Entrena durante 1000 horas en total', 'tiempo_total >= 60000'),
(27, 'Sesión de más de 2 horas', 'Completa una sesión de entrenamiento de más de 2 horas', 'tiempo_sesion >= 120'),
(28, 'Semana de acero', 'Entrena al menos 3 veces en una semana', 'sesiones_semana >= 3'),
(29, 'Semana de hierro', 'Entrena al menos 5 veces en una semana', 'sesiones_semana >= 5'),
(30, 'Semana de titanio', 'Entrena al menos 7 veces en una semana', 'sesiones_semana >= 7'),
(31, 'Mejor marca personal', 'Supera tu mejor marca personal en cualquier ejercicio', 'marca_personal_superada = true'),
(32, 'Progreso constante', 'Mejora tu marca personal en 5 ejercicios diferentes', 'marcas_personales_mejoradas >= 5'),
(33, 'Progreso épico', 'Mejora tu marca personal en 10 ejercicios diferentes', 'marcas_personales_mejoradas >= 10'),
(34, 'lunes de hierro', '50 entrenamientos realizadas un lunes', 'sesiones_lunes >= 50'),
(35, 'Desafío de la variedad', 'Realiza al menos 20 ejercicios diferentes en un mes', 'ejercicios_distintos_mes >= 20'),
(36, 'Año nuevo, nuevo yo', 'Completa un entrenamiento el 1 de enero', 'sesion_enero_1 = true'),
(37, 'Imparable', 'Completa 1000 entrenamientos sin faltar ni uno', 'sesiones_total >= 1000 AND racha >= 1000'),
(38, 'comeback kid', 'Después de una racha de al menos 30 días, vuelve a entrenar y completa 30 días seguidos', 'racha >= 30 AND comeback_kid = true'),
(39, 'El número de la bestia', 'Completa 666 entrenamientos', 'sesiones_total >= 666');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `marca_personal`
--

CREATE TABLE `marca_personal` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `ejercicio_id` int(11) NOT NULL,
  `peso_kg` float DEFAULT NULL,
  `repeticiones` int(11) DEFAULT NULL,
  `fecha` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `marca_personal`
--

INSERT INTO `marca_personal` (`id`, `usuario_id`, `ejercicio_id`, `peso_kg`, `repeticiones`, `fecha`) VALUES
(7, 1, 5, 0, 10, '2026-04-17'),
(8, 1, 6, 40, 10, '2026-04-17'),
(9, 1, 6, 50, 10, '2026-04-17'),
(10, 1, 6, 60, 10, '2026-04-17'),
(11, 1, 8, 50, 10, '2026-04-17'),
(12, 1, 8, 60, 10, '2026-04-17'),
(13, 1, 8, 75, 10, '2026-04-17'),
(14, 1, 1, 0, 10, '2026-04-24'),
(15, 1, 2, 0, 10, '2026-04-24'),
(16, 1, 27, 0, 12, '2026-04-24'),
(17, 1, 21, 0, 12, '2026-04-24'),
(18, 1, 9, 15, 8, '2026-05-01'),
(19, 1, 9, 20, 8, '2026-05-01'),
(20, 1, 9, 25, 8, '2026-05-01'),
(21, 1, 10, 10, 10, '2026-05-01'),
(22, 1, 10, 12, 10, '2026-05-01'),
(23, 1, 10, 15, 10, '2026-05-01'),
(24, 1, 25, 0, 12, '2026-05-01'),
(25, 1, 24, 0, 60, '2026-05-01'),
(26, 1, 89, 0, 8, '2026-05-01'),
(27, 1, 86, 0, 10, '2026-05-01'),
(28, 1, 56, 12, 10, '2026-05-01'),
(29, 1, 15, 12, 10, '2026-05-01'),
(30, 1, 79, 0, 10, '2026-05-12'),
(31, 1, 90, 0, 10, '2026-05-12'),
(32, 1, 8, 80, 8, '2026-05-12'),
(33, 1, 6, 70, 10, '2026-05-12'),
(34, 1, 18, 12, 10, '2026-05-12'),
(35, 1, 18, 14, 10, '2026-05-12'),
(36, 1, 18, 16, 10, '2026-05-12'),
(37, 1, 19, 14, 10, '2026-05-12'),
(38, 1, 19, 16, 10, '2026-05-12'),
(39, 1, 19, 18, 10, '2026-05-12'),
(40, 1, 8, 105, 8, '2026-05-12'),
(41, 1, 6, 100, 10, '2026-05-12'),
(42, 1, 6, 105, 10, '2026-05-12'),
(43, 1, 6, 110, 10, '2026-05-12'),
(44, 1, 75, 12, 10, '2026-05-12'),
(45, 1, 75, 18, 10, '2026-05-12'),
(46, 1, 75, 22, 10, '2026-05-12'),
(47, 1, 36, 20, 10, '2026-05-12'),
(48, 1, 36, 25, 10, '2026-05-12'),
(49, 1, 36, 30, 10, '2026-05-12');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `progreso_corporal`
--

CREATE TABLE `progreso_corporal` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `peso_kg` float DEFAULT NULL,
  `pecho_cm` float DEFAULT NULL,
  `cintura_cm` float DEFAULT NULL,
  `brazo_cm` float DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `progreso_corporal`
--

INSERT INTO `progreso_corporal` (`id`, `usuario_id`, `fecha`, `peso_kg`, `pecho_cm`, `cintura_cm`, `brazo_cm`) VALUES
(1, 1, '2026-04-13', 83, 38, 70, 36),
(2, 1, '2026-04-13', 84, 39, 72, 38),
(3, 1, '2026-04-13', 85, 52, 63, 45),
(4, 1, '2026-04-16', 82, 53, 43, 45),
(5, 1, '2026-04-16', 81.5, NULL, NULL, NULL),
(6, 1, '2026-04-16', 81.5, 35, 34, 53),
(7, 1, '2026-04-16', 80, 43, 34, 45),
(8, 1, '2026-04-16', 78, 234, 324, 423),
(9, 1, '2026-04-16', 78, NULL, NULL, NULL),
(10, 1, '2026-05-11', 83, NULL, NULL, NULL),
(11, 1, '2026-05-12', 83, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rutina`
--

CREATE TABLE `rutina` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `activa` tinyint(1) DEFAULT 0,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `rutina`
--

INSERT INTO `rutina` (`id`, `usuario_id`, `nombre`, `descripcion`, `activa`, `creado_en`) VALUES
(45, 1, 'Rutina de Hipertrofia para Principiantes', 'Rutina de 3 días para principiantes con el objetivo de aumentar la masa muscular', 1, '2026-05-12 13:32:11');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rutina_dia`
--

CREATE TABLE `rutina_dia` (
  `id` int(11) NOT NULL,
  `rutina_id` int(11) NOT NULL,
  `dia_semana` enum('lunes','martes','miercoles','jueves','viernes','sabado','domingo') DEFAULT NULL,
  `dia_nombre` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `rutina_dia`
--

INSERT INTO `rutina_dia` (`id`, `rutina_id`, `dia_semana`, `dia_nombre`) VALUES
(106, 45, 'lunes', 'Dia 1: Pecho y Tríceps'),
(107, 45, 'martes', 'Dia 2: Espalda y Bíceps'),
(108, 45, 'miercoles', 'Dia 3: Piernas y Hombros');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rutina_ejercicio`
--

CREATE TABLE `rutina_ejercicio` (
  `id` int(11) NOT NULL,
  `rutina_id` int(11) NOT NULL,
  `ejercicio_id` int(11) NOT NULL,
  `series` int(11) DEFAULT 3,
  `repeticiones` int(11) DEFAULT 10,
  `peso_kg` float DEFAULT 0,
  `orden` int(11) DEFAULT 0,
  `dia_nombre` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `rutina_ejercicio`
--

INSERT INTO `rutina_ejercicio` (`id`, `rutina_id`, `ejercicio_id`, `series`, `repeticiones`, `peso_kg`, `orden`, `dia_nombre`) VALUES
(389, 45, 1, 3, 8, 0, 1, 'Dia 1: Pecho y Tríceps'),
(390, 45, 3, 3, 10, 0, 2, 'Dia 1: Pecho y Tríceps'),
(391, 45, 86, 3, 10, 0, 3, 'Dia 1: Pecho y Tríceps'),
(392, 45, 23, 3, 10, 0, 4, 'Dia 1: Pecho y Tríceps'),
(393, 45, 24, 1, 60, 0, 5, 'Dia 1: Pecho y Tríceps'),
(394, 45, 8, 3, 8, 0, 6, 'Dia 2: Espalda y Bíceps'),
(395, 45, 6, 3, 10, 0, 7, 'Dia 2: Espalda y Bíceps'),
(396, 45, 75, 3, 10, 0, 8, 'Dia 2: Espalda y Bíceps'),
(397, 45, 36, 3, 10, 0, 9, 'Dia 2: Espalda y Bíceps'),
(398, 45, 24, 1, 60, 0, 10, 'Dia 2: Espalda y Bíceps'),
(399, 45, 12, 3, 8, 0, 11, 'Dia 3: Piernas y Hombros'),
(400, 45, 13, 3, 10, 0, 12, 'Dia 3: Piernas y Hombros'),
(401, 45, 10, 3, 10, 0, 13, 'Dia 3: Piernas y Hombros'),
(402, 45, 69, 3, 10, 0, 14, 'Dia 3: Piernas y Hombros'),
(403, 45, 24, 1, 60, 0, 15, 'Dia 3: Piernas y Hombros');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sesion`
--

CREATE TABLE `sesion` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `rutina_id` int(11) DEFAULT NULL,
  `fecha` date NOT NULL,
  `duracion_min` int(11) DEFAULT NULL,
  `notas` text DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `sesion`
--

INSERT INTO `sesion` (`id`, `usuario_id`, `rutina_id`, `fecha`, `duracion_min`, `notas`, `creado_en`) VALUES
(8, 1, NULL, '2026-04-15', 0, NULL, '2026-04-15 18:58:17'),
(9, 1, NULL, '2026-04-15', 0, NULL, '2026-04-15 18:59:06'),
(10, 1, NULL, '2026-04-15', NULL, NULL, '2026-04-15 21:10:12'),
(11, 1, NULL, '2026-04-16', 0, NULL, '2026-04-16 21:04:54'),
(12, 1, NULL, '2026-04-17', 3, NULL, '2026-04-17 11:22:05'),
(13, 1, NULL, '2026-04-24', 0, NULL, '2026-04-24 16:50:41'),
(14, 1, NULL, '2026-05-01', 3, NULL, '2026-05-01 21:05:15'),
(15, 1, NULL, '2026-05-01', 1, NULL, '2026-05-01 21:48:17'),
(16, 1, NULL, '2026-05-12', 0, NULL, '2026-05-12 09:42:44'),
(17, 1, NULL, '2026-05-12', 3, NULL, '2026-05-12 10:34:17'),
(18, 1, NULL, '2026-05-12', 7, NULL, '2026-05-12 12:12:44'),
(19, 1, 45, '2026-05-12', 2, NULL, '2026-05-12 13:34:38');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sesion_serie`
--

CREATE TABLE `sesion_serie` (
  `id` int(11) NOT NULL,
  `sesion_id` int(11) NOT NULL,
  `ejercicio_id` int(11) NOT NULL,
  `num_serie` int(11) NOT NULL,
  `repeticiones` int(11) DEFAULT NULL,
  `peso_kg` float DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `sesion_serie`
--

INSERT INTO `sesion_serie` (`id`, `sesion_id`, `ejercicio_id`, `num_serie`, `repeticiones`, `peso_kg`) VALUES
(34, 12, 5, 1, 10, 0),
(35, 12, 5, 2, 10, 0),
(36, 12, 5, 3, 10, 0),
(37, 12, 6, 1, 10, 40),
(38, 12, 6, 2, 10, 50),
(39, 12, 6, 3, 10, 60),
(40, 12, 8, 1, 10, 50),
(41, 12, 8, 2, 10, 60),
(42, 12, 8, 3, 10, 75),
(43, 13, 1, 1, 10, 0),
(44, 13, 1, 2, 10, 0),
(45, 13, 1, 3, 10, 0),
(46, 13, 2, 3, 10, 0),
(47, 13, 2, 2, 10, 0),
(48, 13, 2, 1, 10, 0),
(49, 13, 27, 2, 12, 0),
(50, 13, 27, 1, 12, 0),
(51, 13, 27, 3, 12, 0),
(52, 13, 21, 1, 12, 0),
(53, 13, 21, 2, 12, 0),
(54, 13, 21, 3, 12, 0),
(55, 14, 9, 1, 8, 15),
(56, 14, 9, 2, 8, 20),
(57, 14, 9, 3, 8, 25),
(58, 14, 10, 1, 10, 10),
(59, 14, 10, 2, 10, 12),
(60, 14, 10, 3, 10, 15),
(61, 14, 25, 1, 12, 0),
(62, 14, 25, 2, 12, 0),
(63, 14, 25, 3, 12, 0),
(64, 14, 24, 1, 60, 0),
(65, 14, 24, 2, 60, 0),
(66, 14, 24, 3, 60, 0),
(67, 15, 89, 1, 8, 0),
(68, 15, 89, 2, 8, 0),
(69, 15, 89, 3, 8, 0),
(70, 15, 86, 1, 10, 0),
(71, 15, 86, 2, 10, 0),
(72, 15, 86, 3, 10, 0),
(73, 15, 56, 1, 10, 12),
(74, 15, 56, 2, 10, 0),
(75, 15, 56, 3, 10, 0),
(76, 15, 15, 1, 10, 12),
(77, 15, 15, 2, 10, 12),
(78, 15, 15, 3, 10, 12),
(79, 15, 24, 1, 30, 0),
(80, 15, 24, 2, 30, 0),
(81, 15, 24, 3, 30, 0),
(82, 17, 24, 1, 90, 0),
(83, 17, 79, 1, 10, 0),
(84, 17, 79, 2, 10, 0),
(85, 17, 79, 3, 10, 0),
(86, 17, 90, 1, 10, 0),
(87, 17, 90, 2, 10, 0),
(88, 17, 90, 3, 10, 0),
(89, 18, 8, 1, 12, 50),
(90, 18, 8, 2, 12, 65),
(91, 18, 8, 3, 8, 80),
(92, 18, 6, 1, 10, 50),
(93, 18, 6, 2, 10, 60),
(94, 18, 6, 3, 10, 70),
(95, 18, 18, 1, 10, 12),
(96, 18, 18, 2, 10, 14),
(97, 18, 18, 3, 10, 16),
(98, 18, 19, 1, 10, 14),
(99, 18, 19, 2, 10, 16),
(100, 18, 19, 3, 10, 18),
(101, 19, 8, 3, 8, 105),
(102, 19, 8, 1, 8, 90),
(103, 19, 8, 2, 8, 100),
(104, 19, 6, 1, 10, 100),
(105, 19, 6, 2, 10, 105),
(106, 19, 6, 3, 10, 110),
(107, 19, 75, 1, 10, 12),
(108, 19, 75, 2, 10, 18),
(109, 19, 75, 3, 10, 22),
(110, 19, 36, 1, 10, 20),
(111, 19, 36, 2, 10, 25),
(112, 19, 36, 3, 10, 30);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `peso_kg` float DEFAULT NULL,
  `altura_cm` float DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `objetivo` enum('hipertrofia','fuerza','definicion','resistencia') DEFAULT 'hipertrofia',
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `foto_perfil` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`id`, `nombre`, `email`, `password_hash`, `peso_kg`, `altura_cm`, `fecha_nacimiento`, `objetivo`, `creado_en`, `foto_perfil`) VALUES
(1, 'Mateo', 'yucheng692@gmail.com', '$2y$10$5A3FBefqkySPLtkxARPhaeYF8jAw1J7RcS/whJHyYudFq20xFeLn6', 83, 180, '2003-08-06', 'definicion', '2026-04-13 07:24:18', 'assets/uploads/avatars/avatar_1_1778577326.png');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario_logro`
--

CREATE TABLE `usuario_logro` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `logro_id` int(11) NOT NULL,
  `conseguido_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuario_logro`
--

INSERT INTO `usuario_logro` (`id`, `usuario_id`, `logro_id`, `conseguido_en`) VALUES
(2, 1, 1, '2026-04-13 21:37:01'),
(3, 1, 4, '2026-05-12 10:34:17');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `ejercicio`
--
ALTER TABLE `ejercicio`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `logro`
--
ALTER TABLE `logro`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `marca_personal`
--
ALTER TABLE `marca_personal`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `ejercicio_id` (`ejercicio_id`);

--
-- Indices de la tabla `progreso_corporal`
--
ALTER TABLE `progreso_corporal`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `rutina`
--
ALTER TABLE `rutina`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `rutina_dia`
--
ALTER TABLE `rutina_dia`
  ADD PRIMARY KEY (`id`),
  ADD KEY `rutina_id` (`rutina_id`);

--
-- Indices de la tabla `rutina_ejercicio`
--
ALTER TABLE `rutina_ejercicio`
  ADD PRIMARY KEY (`id`),
  ADD KEY `rutina_id` (`rutina_id`),
  ADD KEY `ejercicio_id` (`ejercicio_id`);

--
-- Indices de la tabla `sesion`
--
ALTER TABLE `sesion`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `rutina_id` (`rutina_id`);

--
-- Indices de la tabla `sesion_serie`
--
ALTER TABLE `sesion_serie`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sesion_id` (`sesion_id`),
  ADD KEY `ejercicio_id` (`ejercicio_id`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indices de la tabla `usuario_logro`
--
ALTER TABLE `usuario_logro`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `logro_id` (`logro_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `ejercicio`
--
ALTER TABLE `ejercicio`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=119;

--
-- AUTO_INCREMENT de la tabla `logro`
--
ALTER TABLE `logro`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT de la tabla `marca_personal`
--
ALTER TABLE `marca_personal`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT de la tabla `progreso_corporal`
--
ALTER TABLE `progreso_corporal`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `rutina`
--
ALTER TABLE `rutina`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT de la tabla `rutina_dia`
--
ALTER TABLE `rutina_dia`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=109;

--
-- AUTO_INCREMENT de la tabla `rutina_ejercicio`
--
ALTER TABLE `rutina_ejercicio`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=404;

--
-- AUTO_INCREMENT de la tabla `sesion`
--
ALTER TABLE `sesion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de la tabla `sesion_serie`
--
ALTER TABLE `sesion_serie`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=113;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `usuario_logro`
--
ALTER TABLE `usuario_logro`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `ejercicio`
--
ALTER TABLE `ejercicio`
  ADD CONSTRAINT `ejercicio_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `marca_personal`
--
ALTER TABLE `marca_personal`
  ADD CONSTRAINT `marca_personal_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `marca_personal_ibfk_2` FOREIGN KEY (`ejercicio_id`) REFERENCES `ejercicio` (`id`);

--
-- Filtros para la tabla `progreso_corporal`
--
ALTER TABLE `progreso_corporal`
  ADD CONSTRAINT `progreso_corporal_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `rutina`
--
ALTER TABLE `rutina`
  ADD CONSTRAINT `rutina_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `rutina_dia`
--
ALTER TABLE `rutina_dia`
  ADD CONSTRAINT `rutina_dia_ibfk_1` FOREIGN KEY (`rutina_id`) REFERENCES `rutina` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `rutina_ejercicio`
--
ALTER TABLE `rutina_ejercicio`
  ADD CONSTRAINT `rutina_ejercicio_ibfk_1` FOREIGN KEY (`rutina_id`) REFERENCES `rutina` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `rutina_ejercicio_ibfk_2` FOREIGN KEY (`ejercicio_id`) REFERENCES `ejercicio` (`id`);

--
-- Filtros para la tabla `sesion`
--
ALTER TABLE `sesion`
  ADD CONSTRAINT `sesion_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sesion_ibfk_2` FOREIGN KEY (`rutina_id`) REFERENCES `rutina` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `sesion_serie`
--
ALTER TABLE `sesion_serie`
  ADD CONSTRAINT `sesion_serie_ibfk_1` FOREIGN KEY (`sesion_id`) REFERENCES `sesion` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sesion_serie_ibfk_2` FOREIGN KEY (`ejercicio_id`) REFERENCES `ejercicio` (`id`);

--
-- Filtros para la tabla `usuario_logro`
--
ALTER TABLE `usuario_logro`
  ADD CONSTRAINT `usuario_logro_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `usuario_logro_ibfk_2` FOREIGN KEY (`logro_id`) REFERENCES `logro` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
