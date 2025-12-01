-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 01-12-2025 a las 04:48:56
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
  `token_verificacion` varchar(255) DEFAULT NULL,
  `verificado` tinyint(1) DEFAULT 0,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `token_expiracion` timestamp NOT NULL DEFAULT (current_timestamp() + interval 24 hour),
  `sexo_id` int(11) DEFAULT NULL,
  `rol_id` int(11) DEFAULT NULL,
  `nivel_id` int(11) DEFAULT NULL,
  `puntos` int(11) DEFAULT 0,
  `pais` varchar(100) NOT NULL,
  `ciudad` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`id`, `nombre`, `apellido`, `fecha_nacimiento`, `email`, `contrasena`, `nombre_usuario`, `foto_perfil`, `token_verificacion`, `verificado`, `fecha_creacion`, `token_expiracion`, `sexo_id`, `rol_id`, `nivel_id`, `puntos`, `pais`, `ciudad`) VALUES
(1, NULL, NULL, NULL, 'admin@admin.com', '$2y$10$.Jp9HQqwlc1CFPw7xtGQ6ef7RTKdOmqs6ta9yrDLp1AnIxYqMDR/q', 'admin', '/uploads/default/default.png', NULL, 1, '2025-11-05 17:15:12', '2025-11-06 17:15:12', NULL, 2, NULL, 0, '', ''),
(2, NULL, NULL, NULL, 'editor@editor.com', '$2y$10$KQ6nvlJ4.mmbNt0CBa1LWubhG5r9O0C6cUfmTu4RwmJe9rbtTZ7MW', 'editor', '/uploads/default/default.png', NULL, 1, '2025-11-05 17:15:12', '2025-11-06 17:15:12', NULL, 3, NULL, 0, '', ''),
(4, 'Ezequiel Nicolas', 'Luci', '2003-04-02', 'lucieze02@icloud.com', '$2y$10$5NzyTE1w0x59KuVx2tf1geeTApOAJepPveEivPgVdmbPvKg67xD5.', 'y2klcy', '/uploads/default/default.png', NULL, 1, '2025-11-10 02:47:32', '2025-11-11 02:47:32', 1, 1, 1, 138, 'Argentina', 'Virrey Del Pino'),
(5, 'Natalia', 'Felicetti', '2001-03-09', 'natalia.dgdigital@gmail.com', '$2y$10$qouqVtQYd3YYjofOTYRJv.gtLNBlFMyyfaAeNewWZhDTjbBzxhoW2', 'natbelen', '/uploads/default/default.png', NULL, 1, '2025-11-17 21:38:52', '2025-11-17 21:39:27', 2, 1, 1, 0, 'Argentina', 'Buenos Aires'),
(7, 'ezequiel luci', 'nicolas', '2003-04-02', 'e@gmail.com', '$2y$10$aCBhOKVqPxeMWNGwxykdlu7P09nkwUGigsQiMIqnOBfAozdKALdlC', '20nudes', '/uploads/profiles/a2d00774a3b59b559d01170bff0e44f2.jpeg', NULL, 1, '2025-11-25 20:29:07', '2025-11-26 20:29:07', 1, 1, 1, 0, 'Argentina', 'Buenos Aires');

--
-- Índices para tablas volcadas
--

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
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Restricciones para tablas volcadas
--

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
