-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 24-11-2025 a las 21:33:09
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `preguntados`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `dificultad`
--

CREATE TABLE `dificultad` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `dificultad`
--

INSERT INTO `dificultad` (`id`, `nombre`) VALUES
(1, 'Inicial'),
(2, 'Intermedio'),
(3, 'Avanzado');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `direccion`
--

CREATE TABLE `direccion` (
  `id` int(11) NOT NULL,
  `calle` varchar(50) NOT NULL,
  `numero` int(11) NOT NULL,
  `pais` varchar(50) NOT NULL,
  `ciudad` varchar(50) NOT NULL,
  `cp` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estado_pregunta`
--

CREATE TABLE `estado_pregunta` (
  `id` int(11) NOT NULL,
  `nombre` enum('ACTIVA','INACTIVA','SUGERIDA','REPORTADA') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `estado_pregunta`
--

INSERT INTO `estado_pregunta` (`id`, `nombre`) VALUES
(1, 'ACTIVA'),
(2, 'INACTIVA'),
(3, 'SUGERIDA'),
(4, 'REPORTADA');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estado_reporte`
--

CREATE TABLE `estado_reporte` (
  `id_estado_reporte` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `estado_reporte`
--

INSERT INTO `estado_reporte` (`id_estado_reporte`, `nombre`, `descripcion`) VALUES
(1, 'pendiente', 'Reporte aún no revisado'),
(2, 'aceptado', 'Reporte válido, se tomaron acciones'),
(3, 'rechazado', 'Reporte inválido o sin acción');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estado_solicitud`
--

CREATE TABLE `estado_solicitud` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `estado_solicitud`
--

INSERT INTO `estado_solicitud` (`id`, `nombre`) VALUES
(2, 'aceptada'),
(1, 'pendiente'),
(3, 'rechazada');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `genero`
--

CREATE TABLE `genero` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `genero`
--

INSERT INTO `genero` (`id`, `nombre`) VALUES
(5, 'arte'),
(2, 'ciencia'),
(4, 'deportes'),
(6, 'entretenimiento'),
(3, 'geografia'),
(1, 'historia');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historial_partida`
--

CREATE TABLE `historial_partida` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `partida_id` int(11) NOT NULL,
  `pregunta_id` int(11) NOT NULL,
  `respondida_correctamente` tinyint(1) NOT NULL,
  `fecha_respuesta` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `historial_partida`
--

INSERT INTO `historial_partida` (`id`, `usuario_id`, `partida_id`, `pregunta_id`, `respondida_correctamente`, `fecha_respuesta`) VALUES
(12, 4, 4, 76, 1, '2025-11-09 23:56:20'),
(13, 4, 4, 68, 1, '2025-11-09 23:56:25'),
(14, 4, 4, 71, 0, '2025-11-09 23:56:29'),
(15, 4, 5, 85, 0, '2025-11-10 00:03:33'),
(16, 4, 15, 53, 0, '2025-11-17 20:21:12'),
(17, 4, 16, 64, 0, '2025-11-17 20:22:11');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `nivel`
--

CREATE TABLE `nivel` (
  `id` int(11) NOT NULL,
  `nivel` int(11) NOT NULL,
  `experiencia_necesaria` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `nivel`
--

INSERT INTO `nivel` (`id`, `nivel`, `experiencia_necesaria`) VALUES
(1, 1, 0),
(2, 2, 100),
(3, 3, 250),
(4, 4, 450),
(5, 5, 700),
(6, 6, 1000),
(7, 7, 1350),
(8, 8, 1750),
(9, 9, 2200),
(10, 10, 2700),
(11, 11, 3250),
(12, 12, 3850),
(13, 13, 4500),
(14, 14, 5200),
(15, 15, 5950),
(16, 16, 6750),
(17, 17, 7600),
(18, 18, 8500),
(19, 19, 9450),
(20, 20, 10450);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `partida`
--

CREATE TABLE `partida` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `genero_actual_id` int(11) NOT NULL,
  `dificultad_id` int(11) NOT NULL,
  `estado` varchar(50) DEFAULT 'EN_CURSO',
  `created_at` datetime DEFAULT current_timestamp(),
  `ended_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `partida`
--

INSERT INTO `partida` (`id`, `usuario_id`, `genero_actual_id`, `dificultad_id`, `estado`, `created_at`, `ended_at`) VALUES
(4, 4, 4, 1, 'PERDIDA', '2025-11-09 23:50:04', '2025-11-09 23:56:29'),
(5, 4, 5, 1, 'PERDIDA', '2025-11-10 00:03:28', '2025-11-10 00:03:33'),
(6, 4, 1, 1, 'EN_CURSO', '2025-11-10 00:07:35', NULL),
(7, 4, 1, 1, 'EN_CURSO', '2025-11-10 00:14:46', NULL),
(8, 4, 1, 1, 'EN_CURSO', '2025-11-10 00:14:59', NULL),
(9, 4, 1, 1, 'EN_CURSO', '2025-11-10 00:15:14', NULL),
(10, 4, 2, 1, 'EN_CURSO', '2025-11-15 16:45:42', NULL),
(11, 4, 5, 1, 'EN_CURSO', '2025-11-16 14:04:45', NULL),
(12, 4, 1, 1, 'EN_CURSO', '2025-11-16 14:58:30', NULL),
(13, 5, 5, 1, 'EN_CURSO', '2025-11-17 18:40:00', NULL),
(14, 4, 6, 1, 'EN_CURSO', '2025-11-17 20:02:21', NULL),
(15, 4, 2, 1, 'PERDIDA', '2025-11-17 20:20:56', '2025-11-17 20:21:12'),
(16, 4, 3, 1, 'PERDIDA', '2025-11-17 20:21:23', '2025-11-17 20:22:11'),
(17, 4, 6, 1, 'EN_CURSO', '2025-11-18 16:05:25', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pregunta`
--

CREATE TABLE `pregunta` (
  `id` int(11) NOT NULL,
  `genero_id` int(11) NOT NULL,
  `dificultad_id` int(11) NOT NULL,
  `texto` text NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `estado_id` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pregunta`
--

INSERT INTO `pregunta` (`id`, `genero_id`, `dificultad_id`, `texto`, `usuario_id`, `estado_id`) VALUES
(43, 1, 1, '¿Quién fue el libertador de Argentina, Chile y Perú?', 2, 1),
(44, 1, 1, '¿En qué año se firmó la independencia de Argentina?', 2, 1),
(45, 1, 1, '¿Qué muralla famosa se construyó para proteger a China?', 2, 1),
(46, 1, 1, '¿Qué imperio construyó las pirámides de Egipto?', 2, 1),
(47, 1, 1, '¿Quién descubrió la penicilina?', 2, 1),
(48, 2, 1, '¿Qué planeta está más cerca del Sol?', 2, 1),
(49, 2, 1, '¿Cuál es el gas más abundante en la atmósfera terrestre?', 2, 1),
(50, 2, 1, '¿Qué científico propuso la teoría de la relatividad?', 2, 1),
(51, 2, 1, '¿Qué elemento químico tiene el símbolo O?', 2, 1),
(52, 2, 1, '¿Cuántos huesos tiene el cuerpo humano adulto?', 2, 1),
(53, 2, 1, '¿Cuál es el órgano más grande del cuerpo humano?', 2, 1),
(54, 2, 1, '¿Qué instrumento mide la presión atmosférica?', 2, 1),
(55, 2, 1, '¿Qué fuerza nos mantiene en la Tierra?', 2, 1),
(56, 2, 1, '¿Qué células transportan oxígeno en la sangre?', 2, 1),
(57, 2, 1, '¿Qué planeta es conocido como el planeta rojo?', 2, 1),
(58, 3, 1, '¿Cuál es el río más largo del mundo?', 2, 1),
(59, 3, 1, '¿En qué continente se encuentra Egipto?', 2, 1),
(60, 3, 1, '¿Cuál es el océano más grande del planeta?', 2, 1),
(61, 3, 1, '¿Cuál es la capital de Francia?', 2, 1),
(62, 3, 1, '¿Qué país tiene forma de bota?', 2, 1),
(63, 3, 1, '¿En qué continente está la Argentina?', 2, 1),
(64, 3, 1, '¿Cuál es el desierto más grande del mundo?', 2, 1),
(66, 3, 1, '¿Qué país limita con más países en Sudamérica?', 2, 1),
(67, 3, 1, '¿Cuál es el país más grande del mundo?', 2, 1),
(68, 4, 1, '¿Cuántos jugadores tiene un equipo de fútbol en el campo?', 2, 1),
(69, 4, 1, '¿En qué deporte se utiliza una raqueta y una pelota pequeña?', 2, 1),
(70, 4, 1, '¿Cuántos anillos tiene el símbolo olímpico?', 2, 1),
(71, 4, 1, '¿Qué país ganó el Mundial de fútbol en 2014?', 2, 1),
(72, 4, 1, '¿Quién es considerado el mejor basquetbolista de todos los tiempos?', 2, 1),
(73, 4, 1, '¿En qué país se originaron los Juegos Olímpicos?', 2, 1),
(74, 4, 1, '¿Qué deportista ganó más títulos de Fórmula 1?', 2, 1),
(75, 4, 1, '¿Cuántos sets necesita ganar un jugador para ganar un partido de tenis masculino de Grand Slam?', 2, 1),
(76, 4, 1, '¿Cuál es el deporte más popular del mundo?', 2, 1),
(77, 4, 1, '¿Cuántos minutos dura un partido de fútbol profesional?', 2, 1),
(85, 5, 1, '¿Qué escultor es famoso por \"El Pensador\"?', 2, 1),
(87, 5, 1, '¿Qué técnica utiliza pequeños puntos de color para formar una imagen?', 2, 1),
(89, 6, 1, '¿Qué saga de películas tiene como personaje principal a Darth Vader?', 2, 1),
(90, 6, 1, '¿Qué superhéroe es conocido como \"El Caballero de la Noche\"?', 2, 1),
(91, 6, 1, '¿Qué estudio creó las películas de Toy Story?', 2, 1),
(92, 6, 1, '¿Quién es el creador de la serie Los Simpson?', 2, 1),
(93, 6, 1, '¿Cuál es el nombre del villano principal en la saga de Los Vengadores?', 2, 1),
(94, 6, 1, '¿Qué cantante es conocido como \"El Rey del Pop\"?', 2, 1),
(95, 6, 1, '¿Cuál de estas películas ganó el Oscar a Mejor Película en 1997?', 2, 1),
(97, 6, 1, '¿Qué película animada tiene a un león llamado Simba?', 2, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reporte`
--

CREATE TABLE `reporte` (
  `id_reporte` int(11) NOT NULL,
  `id_pregunta` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_estado_reporte` int(11) NOT NULL DEFAULT 1,
  `motivo` varchar(50) NOT NULL,
  `comentario` text DEFAULT NULL,
  `fecha_reporte` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `reporte`
--

INSERT INTO `reporte` (`id_reporte`, `id_pregunta`, `id_usuario`, `id_estado_reporte`, `motivo`, `comentario`, `fecha_reporte`) VALUES
(6, 91, 4, 1, 'Inexactitud', 'Las respuestas son incorrectas', '2025-11-18 16:36:08');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `respuesta`
--

CREATE TABLE `respuesta` (
  `id` int(11) NOT NULL,
  `pregunta_id` int(11) NOT NULL,
  `texto` varchar(255) NOT NULL,
  `es_correcta` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `respuesta`
--

INSERT INTO `respuesta` (`id`, `pregunta_id`, `texto`, `es_correcta`) VALUES
(21, 43, 'José de San Martín', 1),
(22, 43, 'Simón Bolívar', 0),
(23, 43, 'Belgrano', 0),
(24, 43, 'Artigas', 0),
(25, 44, '1816', 1),
(26, 44, '1810', 0),
(27, 44, '1820', 0),
(28, 44, '1806', 0),
(29, 45, 'La Gran Muralla China', 1),
(30, 45, 'El Muro de Berlín', 0),
(31, 45, 'La Muralla Roja', 0),
(32, 45, 'La Muralla del Sol', 0),
(33, 46, 'Egipcio', 1),
(34, 46, 'Romano', 0),
(35, 46, 'Griego', 0),
(36, 46, 'Persa', 0),
(37, 47, 'Alexander Fleming', 1),
(38, 47, 'Isaac Newton', 0),
(39, 47, 'Marie Curie', 0),
(40, 47, 'Albert Einstein', 0),
(41, 48, 'Mercurio', 1),
(42, 48, 'Venus', 0),
(43, 48, 'Tierra', 0),
(44, 48, 'Marte', 0),
(45, 49, 'Nitrógeno', 1),
(46, 49, 'Oxígeno', 0),
(47, 49, 'Dióxido de carbono', 0),
(48, 49, 'Hidrógeno', 0),
(49, 50, 'Albert Einstein', 1),
(50, 50, 'Isaac Newton', 0),
(51, 50, 'Galileo Galilei', 0),
(52, 50, 'Charles Darwin', 0),
(53, 51, 'Oxígeno', 1),
(54, 51, 'Oro', 0),
(55, 51, 'Osmio', 0),
(56, 51, 'Oxalato', 0),
(57, 52, '206', 1),
(58, 52, '208', 0),
(59, 52, '200', 0),
(60, 52, '250', 0),
(61, 53, 'La piel', 1),
(62, 53, 'El hígado', 0),
(63, 53, 'El cerebro', 0),
(64, 53, 'El corazón', 0),
(65, 54, 'Barómetro', 1),
(66, 54, 'Termómetro', 0),
(67, 54, 'Anemómetro', 0),
(68, 54, 'Higrómetro', 0),
(69, 55, 'Gravedad', 1),
(70, 55, 'Magnetismo', 0),
(71, 55, 'Fricción', 0),
(72, 55, 'Inercia', 0),
(73, 56, 'Glóbulos rojos', 1),
(74, 56, 'Glóbulos blancos', 0),
(75, 56, 'Plaquetas', 0),
(76, 56, 'Plasma', 0),
(77, 57, 'Marte', 1),
(78, 57, 'Venus', 0),
(79, 57, 'Saturno', 0),
(80, 57, 'Júpiter', 0),
(81, 58, 'Nilo', 1),
(82, 58, 'Amazonas', 0),
(83, 58, 'Yangtsé', 0),
(84, 58, 'Misisipi', 0),
(85, 59, 'África', 1),
(86, 59, 'Asia', 0),
(87, 59, 'Europa', 0),
(88, 59, 'Oceanía', 0),
(89, 60, 'Océano Pacífico', 1),
(90, 60, 'Océano Atlántico', 0),
(91, 60, 'Océano Índico', 0),
(92, 60, 'Océano Ártico', 0),
(93, 61, 'París', 1),
(94, 61, 'Londres', 0),
(95, 61, 'Madrid', 0),
(96, 61, 'Roma', 0),
(97, 62, 'Italia', 1),
(98, 62, 'España', 0),
(99, 62, 'Grecia', 0),
(100, 62, 'Turquía', 0),
(101, 63, 'América del Sur', 1),
(102, 63, 'África', 0),
(103, 63, 'Asia', 0),
(104, 63, 'Europa', 0),
(105, 64, 'Sahara', 1),
(106, 64, 'Gobi', 0),
(107, 64, 'Atacama', 0),
(108, 64, 'Kalahari', 0),
(113, 66, 'Brasil', 1),
(114, 66, 'Argentina', 0),
(115, 66, 'Perú', 0),
(116, 66, 'Colombia', 0),
(117, 67, 'Rusia', 1),
(118, 67, 'Canadá', 0),
(119, 67, 'China', 0),
(120, 67, 'Estados Unidos', 0),
(121, 68, '11', 1),
(122, 68, '10', 0),
(123, 68, '9', 0),
(124, 68, '12', 0),
(125, 69, 'Tenis', 1),
(126, 69, 'Golf', 0),
(127, 69, 'Béisbol', 0),
(128, 69, 'Críquet', 0),
(129, 70, '5', 1),
(130, 70, '4', 0),
(131, 70, '6', 0),
(132, 70, '7', 0),
(133, 71, 'Alemania', 1),
(134, 71, 'Brasil', 0),
(135, 71, 'Argentina', 0),
(136, 71, 'Francia', 0),
(137, 72, 'Michael Jordan', 1),
(138, 72, 'LeBron James', 0),
(139, 72, 'Kobe Bryant', 0),
(140, 72, 'Shaquille O’Neal', 0),
(141, 73, 'Grecia', 1),
(142, 73, 'Italia', 0),
(143, 73, 'Egipto', 0),
(144, 73, 'China', 0),
(145, 74, 'Lewis Hamilton', 1),
(146, 74, 'Michael Schumacher', 0),
(147, 74, 'Ayrton Senna', 0),
(148, 74, 'Fernando Alonso', 0),
(149, 75, '3', 1),
(150, 75, '2', 0),
(151, 75, '4', 0),
(152, 75, '5', 0),
(153, 76, 'Fútbol', 1),
(154, 76, 'Baloncesto', 0),
(155, 76, 'Críquet', 0),
(156, 76, 'Voleibol', 0),
(157, 77, '90', 1),
(158, 77, '80', 0),
(159, 77, '60', 0),
(160, 77, '100', 0),
(189, 85, 'Auguste Rodin', 1),
(190, 85, 'Miguel Ángel', 0),
(191, 85, 'Bernini', 0),
(192, 85, 'Donatello', 0),
(197, 87, 'Puntillismo', 1),
(198, 87, 'Acuarela', 0),
(199, 87, 'Expresionismo', 0),
(200, 87, 'Surrealismo', 0),
(205, 89, 'Star Wars', 1),
(206, 89, 'Star Trek', 0),
(207, 89, 'Guardians of the Galaxy', 0),
(208, 89, 'Avengers', 0),
(209, 90, 'Batman', 1),
(210, 90, 'Superman', 0),
(211, 90, 'Iron Man', 0),
(212, 90, 'Spider-Man', 0),
(213, 91, 'Pixar', 1),
(214, 91, 'DreamWorks', 0),
(215, 91, 'Disney', 0),
(216, 91, 'Illumination', 0),
(217, 92, 'Matt Groening', 1),
(218, 92, 'Seth MacFarlane', 0),
(219, 92, 'Dan Harmon', 0),
(220, 92, 'Trey Parker', 0),
(221, 93, 'Thanos', 1),
(222, 93, 'Loki', 0),
(223, 93, 'Ultron', 0),
(224, 93, 'Magneto', 0),
(225, 94, 'Michael Jackson', 1),
(226, 94, 'Elvis Presley', 0),
(227, 94, 'Prince', 0),
(228, 94, 'Freddie Mercury', 0),
(229, 95, 'Titanic', 1),
(230, 95, 'Matrix', 0),
(231, 95, 'Gladiador', 0),
(232, 95, 'Forrest Gump', 0),
(237, 97, 'El Rey León', 1),
(238, 97, 'Madagascar', 0),
(239, 97, 'Zootopia', 0),
(240, 97, 'Buscando a Nemo', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rol`
--

CREATE TABLE `rol` (
  `id` int(11) NOT NULL,
  `tipo` varchar(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `rol`
--

INSERT INTO `rol` (`id`, `tipo`) VALUES
(1, 'Jugador'),
(2, 'Administrador'),
(3, 'Editor');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sexo`
--

CREATE TABLE `sexo` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `sexo`
--

INSERT INTO `sexo` (`id`, `nombre`) VALUES
(1, 'Masculino'),
(2, 'Femenino'),
(3, 'Prefiero no cargarlo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `solicitud_partida`
--

CREATE TABLE `solicitud_partida` (
  `id` int(11) NOT NULL,
  `usuario_remitente_id` int(11) NOT NULL,
  `usuario_destinatario_id` int(11) NOT NULL,
  `estado_solicitud_id` int(11) NOT NULL DEFAULT 1,
  `fecha_envio` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `apellido` varchar(100) DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `email` varchar(150) NOT NULL,
  `contrasena` varchar(255) NOT NULL,
  `nombre_usuario` varchar(50) NOT NULL,
  `foto_perfil` varchar(255) DEFAULT NULL,
  `experiencia` int(11) DEFAULT 0,
  `token_verificacion` varchar(255) DEFAULT NULL,
  `verificado` tinyint(1) DEFAULT 0,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `token_expiracion` timestamp NOT NULL DEFAULT (current_timestamp() + interval 24 hour),
  `sexo_id` int(11) DEFAULT NULL,
  `rol_id` int(11) DEFAULT NULL,
  `nivel_id` int(11) DEFAULT NULL,
  `puntaje_total` int(11) DEFAULT 0,
  `nivel_actual` enum('Inicial','Intermedio','Avanzado') DEFAULT 'Inicial',
  `puntos` int(11) DEFAULT 0,
  `pais` varchar(100) NOT NULL,
  `ciudad` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`id`, `nombre`, `apellido`, `fecha_nacimiento`, `email`, `contrasena`, `nombre_usuario`, `foto_perfil`, `experiencia`, `token_verificacion`, `verificado`, `fecha_creacion`, `token_expiracion`, `sexo_id`, `rol_id`, `nivel_id`, `puntaje_total`, `nivel_actual`, `puntos`, `pais`, `ciudad`) VALUES
(1, NULL, NULL, NULL, 'admin@admin.com', '$2y$10$.Jp9HQqwlc1CFPw7xtGQ6ef7RTKdOmqs6ta9yrDLp1AnIxYqMDR/q', 'admin', '/uploads/default/default.png', 0, NULL, 1, '2025-11-05 17:15:12', '2025-11-06 17:15:12', NULL, 2, NULL, 0, 'Inicial', 0, '', ''),
(2, NULL, NULL, NULL, 'editor@editor.com', '$2y$10$KQ6nvlJ4.mmbNt0CBa1LWubhG5r9O0C6cUfmTu4RwmJe9rbtTZ7MW', 'editor', '/uploads/default/default.png', 0, NULL, 1, '2025-11-05 17:15:12', '2025-11-06 17:15:12', NULL, 3, NULL, 0, 'Inicial', 0, '', ''),
(4, 'Ezequiel Nicolas ', 'Luci', '2003-04-02', 'lucieze02@icloud.com', '$2y$10$5NzyTE1w0x59KuVx2tf1geeTApOAJepPveEivPgVdmbPvKg67xD5.', 'y2klcy', '/uploads/profiles/ef5c84dcabd97fba00b2ffee85bec220.jpg', 0, NULL, 1, '2025-11-10 02:47:32', '2025-11-11 02:47:32', 1, 1, 1, 0, 'Inicial', 2, 'Argentina', 'Virrey Del Pino'),
(5, 'Natalia', 'Felicetti', '2001-03-09', 'natalia.dgdigital@gmail.com', '$2y$10$qouqVtQYd3YYjofOTYRJv.gtLNBlFMyyfaAeNewWZhDTjbBzxhoW2', 'natbelen', '/uploads/default/default.png', 0, NULL, 1, '2025-11-17 21:38:52', '2025-11-17 21:39:27', 2, 1, 1, 0, 'Inicial', 0, 'Argentina', 'Buenos Aires');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `dificultad`
--
ALTER TABLE `dificultad`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `direccion`
--
ALTER TABLE `direccion`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_direccion_usuario` (`usuario_id`);

--
-- Indices de la tabla `estado_pregunta`
--
ALTER TABLE `estado_pregunta`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `estado_reporte`
--
ALTER TABLE `estado_reporte`
  ADD PRIMARY KEY (`id_estado_reporte`);

--
-- Indices de la tabla `estado_solicitud`
--
ALTER TABLE `estado_solicitud`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `genero`
--
ALTER TABLE `genero`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `historial_partida`
--
ALTER TABLE `historial_partida`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_historial_usuario` (`usuario_id`),
  ADD KEY `fk_historial_partida` (`partida_id`),
  ADD KEY `fk_historial_pregunta` (`pregunta_id`);

--
-- Indices de la tabla `nivel`
--
ALTER TABLE `nivel`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `partida`
--
ALTER TABLE `partida`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `genero_actual_id` (`genero_actual_id`),
  ADD KEY `dificultad_id` (`dificultad_id`);

--
-- Indices de la tabla `pregunta`
--
ALTER TABLE `pregunta`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_pregunta_genero` (`genero_id`),
  ADD KEY `fk_pregunta_dificultad` (`dificultad_id`),
  ADD KEY `fk_usuario_pregunta` (`usuario_id`),
  ADD KEY `estado_id` (`estado_id`);

--
-- Indices de la tabla `reporte`
--
ALTER TABLE `reporte`
  ADD PRIMARY KEY (`id_reporte`),
  ADD KEY `fk_reporte_pregunta` (`id_pregunta`),
  ADD KEY `fk_reporte_usuario` (`id_usuario`),
  ADD KEY `fk_reporte_estado` (`id_estado_reporte`);

--
-- Indices de la tabla `respuesta`
--
ALTER TABLE `respuesta`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_respuesta_pregunta` (`pregunta_id`);

--
-- Indices de la tabla `rol`
--
ALTER TABLE `rol`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `sexo`
--
ALTER TABLE `sexo`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `solicitud_partida`
--
ALTER TABLE `solicitud_partida`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_remitente` (`usuario_remitente_id`),
  ADD KEY `idx_destinatario` (`usuario_destinatario_id`),
  ADD KEY `idx_estado` (`estado_solicitud_id`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `nombre_usuario` (`nombre_usuario`),
  ADD KEY `idx_usuario_email` (`email`),
  ADD KEY `idx_usuario_nombre_usuario` (`nombre_usuario`),
  ADD KEY `idx_usuario_sexo_fk` (`sexo_id`),
  ADD KEY `idx_usuario_rol_fk` (`rol_id`),
  ADD KEY `idx_usuario_nivel_fk` (`nivel_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `dificultad`
--
ALTER TABLE `dificultad`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `direccion`
--
ALTER TABLE `direccion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `estado_pregunta`
--
ALTER TABLE `estado_pregunta`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `estado_reporte`
--
ALTER TABLE `estado_reporte`
  MODIFY `id_estado_reporte` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `estado_solicitud`
--
ALTER TABLE `estado_solicitud`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `genero`
--
ALTER TABLE `genero`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `historial_partida`
--
ALTER TABLE `historial_partida`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `nivel`
--
ALTER TABLE `nivel`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de la tabla `partida`
--
ALTER TABLE `partida`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `pregunta`
--
ALTER TABLE `pregunta`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=113;

--
-- AUTO_INCREMENT de la tabla `reporte`
--
ALTER TABLE `reporte`
  MODIFY `id_reporte` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `respuesta`
--
ALTER TABLE `respuesta`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=295;

--
-- AUTO_INCREMENT de la tabla `rol`
--
ALTER TABLE `rol`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `sexo`
--
ALTER TABLE `sexo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `solicitud_partida`
--
ALTER TABLE `solicitud_partida`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `direccion`
--
ALTER TABLE `direccion`
  ADD CONSTRAINT `fk_direccion_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `historial_partida`
--
ALTER TABLE `historial_partida`
  ADD CONSTRAINT `fk_historial_partida` FOREIGN KEY (`partida_id`) REFERENCES `partida` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_historial_pregunta` FOREIGN KEY (`pregunta_id`) REFERENCES `pregunta` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_historial_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `partida`
--
ALTER TABLE `partida`
  ADD CONSTRAINT `partida_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`id`),
  ADD CONSTRAINT `partida_ibfk_2` FOREIGN KEY (`genero_actual_id`) REFERENCES `genero` (`id`),
  ADD CONSTRAINT `partida_ibfk_3` FOREIGN KEY (`dificultad_id`) REFERENCES `dificultad` (`id`);

--
-- Filtros para la tabla `pregunta`
--
ALTER TABLE `pregunta`
  ADD CONSTRAINT `fk_pregunta_dificultad` FOREIGN KEY (`dificultad_id`) REFERENCES `dificultad` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pregunta_genero` FOREIGN KEY (`genero_id`) REFERENCES `genero` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_usuario_pregunta` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`id`),
  ADD CONSTRAINT `pregunta_ibfk_1` FOREIGN KEY (`estado_id`) REFERENCES `estado_pregunta` (`id`);

--
-- Filtros para la tabla `reporte`
--
ALTER TABLE `reporte`
  ADD CONSTRAINT `fk_reporte_estado` FOREIGN KEY (`id_estado_reporte`) REFERENCES `estado_reporte` (`id_estado_reporte`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_reporte_pregunta` FOREIGN KEY (`id_pregunta`) REFERENCES `pregunta` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_reporte_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `respuesta`
--
ALTER TABLE `respuesta`
  ADD CONSTRAINT `fk_respuesta_pregunta` FOREIGN KEY (`pregunta_id`) REFERENCES `pregunta` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `solicitud_partida`
--
ALTER TABLE `solicitud_partida`
  ADD CONSTRAINT `solicitud_partida_ibfk_1` FOREIGN KEY (`usuario_remitente_id`) REFERENCES `usuario` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `solicitud_partida_ibfk_2` FOREIGN KEY (`usuario_destinatario_id`) REFERENCES `usuario` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `solicitud_partida_ibfk_3` FOREIGN KEY (`estado_solicitud_id`) REFERENCES `estado_solicitud` (`id`);

--
-- Filtros para la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD CONSTRAINT `fk_usuario_nivel` FOREIGN KEY (`nivel_id`) REFERENCES `nivel` (`id`),
  ADD CONSTRAINT `fk_usuario_rol` FOREIGN KEY (`rol_id`) REFERENCES `rol` (`id`),
  ADD CONSTRAINT `fk_usuario_sexo` FOREIGN KEY (`sexo_id`) REFERENCES `sexo` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
