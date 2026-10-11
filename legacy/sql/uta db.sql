-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 02-07-2025 a las 04:07:27
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
-- Base de datos: `uta`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias_evento`
--

CREATE TABLE `categorias_evento` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categorias_evento`
--

INSERT INTO `categorias_evento` (`id`, `nombre`) VALUES
(19, 'Comunicación'),
(5, 'Industrial'),
(2, 'Publico'),
(4, 'Robotica'),
(1, 'Software'),
(3, 'TI');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estudiantes`
--

CREATE TABLE `estudiantes` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `cedula` varchar(20) NOT NULL,
  `telefono` varchar(15) NOT NULL,
  `correo` varchar(100) NOT NULL,
  `password` text NOT NULL,
  `genero` varchar(20) NOT NULL,
  `fecha_nacimiento` date NOT NULL,
  `tipo` enum('institucional','publico') NOT NULL DEFAULT 'publico',
  `carrera` varchar(100) DEFAULT NULL,
  `cedula_path` text DEFAULT NULL,
  `papeleta_path` text DEFAULT NULL,
  `matricula_path` text DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `rol` varchar(20) NOT NULL DEFAULT 'estudiante',
  `token` varchar(64) DEFAULT NULL,
  `codigo_verificacion` varchar(10) DEFAULT NULL,
  `verificado` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `estudiantes`
--

INSERT INTO `estudiantes` (`id`, `nombre`, `apellido`, `cedula`, `telefono`, `correo`, `password`, `genero`, `fecha_nacimiento`, `tipo`, `carrera`, `cedula_path`, `papeleta_path`, `matricula_path`, `fecha_registro`, `rol`, `token`, `codigo_verificacion`, `verificado`) VALUES
(2, 'Chris', 'Changoluisa', '1850410638', '', 'chris@uta.edu.ec', '$2y$10$ZD1mgpoDXD4FbTwHz39xtuU7p4KeY2C80V/QmlQKHiFLut7kDB9BK', 'Hombre', '2006-04-11', 'institucional', 'Software', 'uploads/documentos/chris_uta.edu.ec/cedula_20250605_133835.jpg', NULL, NULL, '2025-06-08 16:26:05', 'administrador', NULL, NULL, 1),
(17, 'Johan', 'Rodríguez', '1850410612', '0961957956', '907johan@uta.edu.ec', '$2y$10$i32YhVcf9QUigwCUARWdw./svOTApTqYxVMoTho5vcRRKvjOgSYqC', 'Hombre', '2000-01-01', 'institucional', '1', NULL, NULL, NULL, '2025-06-30 01:33:35', 'estudiante', NULL, NULL, 0),
(18, 'Cristian', 'Amaya', '1802570059', '0985576390', '907johan@gmail.com', '$2y$10$Ts35TuHGRLJsqsEHHvgdQuUyHifuLb.CObPM047NnIRDPudtEbsbW', 'Hombre', '2004-09-16', 'publico', '0', NULL, NULL, NULL, '2025-06-30 01:44:57', 'estudiante', NULL, NULL, 0),
(24, 'José', 'Manzano', '1805093224', '0995842174', 'josemanzano134@gmail.com', '$2y$10$YyEGTz7y6b/NNJVYARoJbOsSMId9vVmEH9lsEu5mQhkuBxA9TsxnO', 'Hombre', '2004-10-15', 'publico', '0', NULL, NULL, NULL, '2025-06-30 02:21:59', 'estudiante', NULL, NULL, 1),
(25, 'Pablo', 'Vayas', '1850257732', '0983554459', 'pablisvatru12@gmail.com', '$2y$10$W1Qa3H1CUdzRk6wNSaXI2e.Avy.3nYlEHlqQreEiPSOb.QY553xlK', 'Hombre', '2004-04-07', 'publico', '0', NULL, NULL, NULL, '2025-06-30 02:29:19', 'estudiante', NULL, NULL, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `eventos`
--

CREATE TABLE `eventos` (
  `id` int(11) NOT NULL,
  `nombre_evento` varchar(255) NOT NULL,
  `tipo_evento_id` int(11) NOT NULL,
  `categoria_id` int(11) NOT NULL,
  `ponentes` text NOT NULL,
  `descripcion` text DEFAULT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `fecha_inicio_inscripciones` date NOT NULL,
  `fecha_fin_inscripciones` date NOT NULL,
  `horas` int(11) NOT NULL,
  `cupos` int(11) NOT NULL,
  `ruta_imagen` varchar(255) NOT NULL,
  `estado` enum('abierto','cerrado','en_ejecucion','cerrado_inscripciones') NOT NULL DEFAULT 'abierto'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `eventos`
--

INSERT INTO `eventos` (`id`, `nombre_evento`, `tipo_evento_id`, `categoria_id`, `ponentes`, `descripcion`, `fecha_inicio`, `fecha_fin`, `fecha_inicio_inscripciones`, `fecha_fin_inscripciones`, `horas`, `cupos`, `ruta_imagen`, `estado`) VALUES
(10, 'Python', 3, 4, 'Johan Rodriguez', 'En este evento se busca fomentar el aprendizaje', '2025-02-25', '2025-02-28', '2025-01-20', '2025-01-20', 40, 30, '../uploads/evento_6862b56b40267.jpg', 'abierto'),
(11, 'Inteligencia artifical', 3, 1, 'Johan Rodriguez', 'Crear ChatGPT 2', '2025-07-01', '2025-07-01', '2025-06-20', '2025-06-30', 40, 20, '../uploads/evento_6862cc90766bb.jpg', 'abierto');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `info_fisei`
--

CREATE TABLE `info_fisei` (
  `id` int(11) NOT NULL,
  `tipo` varchar(50) DEFAULT NULL,
  `contenido` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `info_fisei`
--

INSERT INTO `info_fisei` (`id`, `tipo`, `contenido`) VALUES
(1, 'mision', 'Nuestra misión es formar profesionales de excelencia en ingeniería.'),
(2, 'vision', 'Ser una facultad líder en innovación, tecnología y ciencia.'),
(3, 'evento', 'Conferencia de innovación 2025 el 12 de julio'),
(4, 'imagen', 'img/evento1.jpg'),
(5, 'mision', 'La FISEI-UTA aspira a ser reconocida, tanto a nivel nacional como internacional, por su excelencia en la formación de profesionales en ingeniería con un enfoque integral y comprometidos con el desarrollo sostenible. Su visión es consolidarse como una facultad líder en el ámbito de la ingeniería, destacando por la calidad académica y la pertinencia de sus programas de estudio. Pretende convertirse en un referente en investigación, innovación y desarrollo tecnológico, promoviendo el emprendimiento y actuando como un motor de cambio social y tecnológico en la región y el país. Su propósito es contribuir significativamente al progreso nacional mediante la formación de profesionales capaces de afrontar los desafíos contemporáneos con responsabilidad y compromiso.'),
(6, 'vision', 'La misión de la Facultad de Ingeniería en Sistemas, Electrónica e Industrial (FISEI) de la Universidad Técnica de Ambato (UTA) es formar profesionales líderes y competentes, con una sólida visión humanista y pensamiento crítico. A través de la docencia, la investigación y la vinculación con la sociedad, busca preparar profesionales a la vanguardia del desarrollo nacional, capaces de conducir y liderar procesos transformadores. FISEI imparte conocimientos científico-técnicos con un enfoque social y humanístico, fomentando la innovación, la transferencia de tecnología y la participación activa en la solución de problemas sociales. Además, inculca valores de ética, responsabilidad social y compromiso con el medio ambiente, respondiendo así a las necesidades del país con una formación integral.'),
(7, 'evento', 'Taller de Innovación 2025'),
(8, 'autoridad', '{\"nombre\":\"Ing. Franklin Mayorga, Mg.\",\"cargo\":\"DECANO\",\"img\":\"uploads\\/landing\\/6859b398b324e_autoridad1.png\"}'),
(14, 'nosotros', '{\"titulo\":\"El inicio de una nueva forma de aprendizaje.\",\"descripcion\":\"El 20 de octubre de 2002 se crea el Centro de Transferencia y Desarrollo de Tecnologías mediante resolución 1452-2002-CU-P en la áreas de Ingenierías en Sistemas, Electrónica e Industrial de la Universidad Técnica de Ambato, para proveer servicios a la comunidad mediante la realización de trabajos y proyectos específicos, asesorías, estudios, investigaciones, cursos de entrenamiento, seminarios y otras actividades de servicios a los sectores sociales y productivos en las áreas de Ingeniería en Sistemas Computacionales e Informáticos, Ingeniería Electrónica y Comunicaciones e Ingeniería Industrial en Procesos de Automatización.\",\"img\":\"uploads\\/landing\\/6859ba44e3b68_slider3.jpg\"}'),
(26, 'carrusel', '{\"titulo\":\"CURSOS VIRTUALES Y PRESENCIALES\",\"descripcion\":\"Disponibilidad para todas las personas que deseen expandir sus conocimientos.\",\"img\":\"uploads\\/landing\\/6859b5e0057b0_slider3.jpg\"}'),
(27, 'carrusel', '{\"titulo\":\"CONOCE TU NUEVO CAMPUS :V\",\"descripcion\":\"Compromiso con el desarrollo tecnológico.\",\"img\":\"uploads\\/landing\\/6859b384792df_slide1.jpeg\"}'),
(28, 'resena', '{\"autor\":\"Chris Changoluisa\",\"rol\":\"Estudiante\",\"texto\":\"Una de las cosas destacables, es sin duda la calidad de los\\r\\ncursos. Son instruidos por profesionales altamente\\r\\npreparados.\",\"img\":\"uploads\\/landing\\/6859b3b0ae15e_chirs.jpg\"}'),
(29, 'resena', '{\"autor\":\"Alan Cuenquita\",\"rol\":\"Master de la Programación\",\"texto\":\"La plataforma en donde se puede acceder a los cursos es\\r\\nmuy intuitiva y agradable para el usuario, sin duda, incita a\\r\\nir por más cursos.\",\"img\":\"uploads\\/landing\\/6859b3b721113_20240424_155222.jpg\"}'),
(30, 'autoridad', '{\"nombre\":\"Chris Changoluisa\",\"cargo\":\"Novio\",\"img\":\"uploads\\/landing\\/6859b3a85219c_Imagen de WhatsApp 2025-06-14 a las 23.38.33_1783dc8a.jpg\"}'),
(32, 'autoridad', '{\"nombre\":\"Ing. Daniel Jerez, Mg.\",\"cargo\":\"CTT\",\"img\":\"uploads\\/landing\\/6859bafb8db00_daniel_jerez.jpeg\"}'),
(33, 'resena', '{\"autor\":\"Mr Graso\",\"rol\":\"Momero\",\"texto\":\"Una de las cosas destacables, es sin duda la calidad de los cursos. Son instruidos por profesionales altamente preparados.\",\"img\":\"uploads\\/landing\\/6859bb52c8fff_images.jpg\"}');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inscripciones`
--

CREATE TABLE `inscripciones` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `evento_id` int(11) NOT NULL,
  `estado` enum('En espera de orden de pago','Esperando aprobación del admin','Pagado','Error') DEFAULT 'En espera de orden de pago',
  `nota` decimal(4,2) DEFAULT NULL,
  `asistencia` decimal(5,2) DEFAULT NULL,
  `comprobante_pago` text DEFAULT NULL,
  `legalizado` tinyint(1) DEFAULT 0,
  `pago_confirmado` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `requisitos_evento`
--

CREATE TABLE `requisitos_evento` (
  `id` int(11) NOT NULL,
  `evento_id` int(11) NOT NULL,
  `descripcion` text NOT NULL,
  `tipo` enum('archivo','texto') NOT NULL DEFAULT 'archivo',
  `campo_estudiante` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `requisitos_evento`
--

INSERT INTO `requisitos_evento` (`id`, `evento_id`, `descripcion`, `tipo`, `campo_estudiante`) VALUES
(1, 10, 'Ruta_cedula', '', NULL),
(2, 10, 'Carta de motivación', 'texto', NULL),
(3, 11, 'Ruta_cedula', '', 'ruta_cedula'),
(4, 11, 'Ruta_papeleta', '', 'ruta_papeleta'),
(5, 11, 'Carta de motivación', 'texto', NULL),
(6, 11, 'Carta para la mamá', 'texto', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `requisitos_inscripcion`
--

CREATE TABLE `requisitos_inscripcion` (
  `id` int(11) NOT NULL,
  `inscripcion_id` int(11) NOT NULL,
  `requisito_id` int(11) NOT NULL,
  `archivo` text NOT NULL,
  `texto_respuesta` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `resoluciones`
--

CREATE TABLE `resoluciones` (
  `id` int(11) NOT NULL,
  `id_solicitud` int(11) NOT NULL,
  `prioridad` varchar(50) DEFAULT NULL,
  `comentario` text DEFAULT NULL,
  `estado` varchar(50) DEFAULT NULL,
  `fecha_resolucion` date DEFAULT curdate()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `resoluciones`
--

INSERT INTO `resoluciones` (`id`, `id_solicitud`, `prioridad`, `comentario`, `estado`, `fecha_resolucion`) VALUES
(1, 1, 'Alta', 'Pq si', 'En revisión', '2025-06-09'),
(2, 1, 'Alta', 'Pq si', 'En revisión', '2025-06-09'),
(3, 1, 'Alta', 'Pq si', 'En revisión', '2025-06-09'),
(4, 1, 'Alta', 'asfasdjg', 'En revisión', '2025-06-09'),
(5, 1, 'Alta', 'asfasdjg', 'En revisión', '2025-06-09'),
(6, 1, 'Alta', 'asfasdjg', 'En revisión', '2025-06-09'),
(7, 2, 'Alta', 'Ok', 'Terminado', '2025-06-11'),
(8, 2, 'Alta', 'Ok', 'En revisión', '2025-06-11'),
(9, 2, 'Alta', 'Ok', 'En revisión', '2025-06-11'),
(10, 2, 'Alta', 'Ok', 'En revisión', '2025-06-11'),
(11, 2, 'Alta', 'Ok', 'En revisión', '2025-06-11');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `solicitudes`
--

CREATE TABLE `solicitudes` (
  `id` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `fecha` date NOT NULL,
  `tipo` varchar(100) NOT NULL,
  `descripcion` text NOT NULL,
  `justificacion` text DEFAULT NULL,
  `contexto` text DEFAULT NULL,
  `captura` varchar(500) DEFAULT NULL,
  `uid` varchar(50) NOT NULL,
  `uname` varchar(255) NOT NULL,
  `uemail` varchar(255) NOT NULL,
  `urol` varchar(100) NOT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `solicitudes`
--

INSERT INTO `solicitudes` (`id`, `titulo`, `fecha`, `tipo`, `descripcion`, `justificacion`, `contexto`, `captura`, `uid`, `uname`, `uemail`, `urol`, `creado_en`) VALUES
(1, 'No se muestra bien las imagenes', '2025-06-09', 'Corrección de Error', 'Debe estar bien', 'Porque si', 'Probando', '', '2', 'Chris Changoluisa', 'chris@uta.edu.ec', 'admimistrador', '2025-06-09 19:15:26'),
(2, 'El rol de administrador no está bien definido', '2025-06-12', 'Corrección de Error', 'El rol con el que se está guardando el administrador es \'admimistrador\' y no \'administrador\'', 'Es sumamente fundamental para el desarrollo del la app', 'Estaba estaba creando un admin', '', '2', 'Chris Changoluisa', 'chris@uta.edu.ec', 'admimistrador', '2025-06-11 22:31:03');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipos_evento`
--

CREATE TABLE `tipos_evento` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tipos_evento`
--

INSERT INTO `tipos_evento` (`id`, `nombre`) VALUES
(3, 'Congreso'),
(1, 'Curso'),
(6, 'Evento FISEI'),
(2, 'Webinar');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `categorias_evento`
--
ALTER TABLE `categorias_evento`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `estudiantes`
--
ALTER TABLE `estudiantes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cedula` (`cedula`),
  ADD UNIQUE KEY `correo` (`correo`);

--
-- Indices de la tabla `eventos`
--
ALTER TABLE `eventos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tipo_evento_id` (`tipo_evento_id`),
  ADD KEY `categoria_id` (`categoria_id`);

--
-- Indices de la tabla `info_fisei`
--
ALTER TABLE `info_fisei`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `inscripciones`
--
ALTER TABLE `inscripciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `evento_id` (`evento_id`);

--
-- Indices de la tabla `requisitos_evento`
--
ALTER TABLE `requisitos_evento`
  ADD PRIMARY KEY (`id`),
  ADD KEY `evento_id` (`evento_id`);

--
-- Indices de la tabla `requisitos_inscripcion`
--
ALTER TABLE `requisitos_inscripcion`
  ADD PRIMARY KEY (`id`),
  ADD KEY `inscripcion_id` (`inscripcion_id`),
  ADD KEY `requisito_id` (`requisito_id`);

--
-- Indices de la tabla `resoluciones`
--
ALTER TABLE `resoluciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_solicitud` (`id_solicitud`);

--
-- Indices de la tabla `solicitudes`
--
ALTER TABLE `solicitudes`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `tipos_evento`
--
ALTER TABLE `tipos_evento`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `categorias_evento`
--
ALTER TABLE `categorias_evento`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT de la tabla `estudiantes`
--
ALTER TABLE `estudiantes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT de la tabla `eventos`
--
ALTER TABLE `eventos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `info_fisei`
--
ALTER TABLE `info_fisei`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT de la tabla `inscripciones`
--
ALTER TABLE `inscripciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `requisitos_evento`
--
ALTER TABLE `requisitos_evento`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `requisitos_inscripcion`
--
ALTER TABLE `requisitos_inscripcion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `resoluciones`
--
ALTER TABLE `resoluciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `solicitudes`
--
ALTER TABLE `solicitudes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `tipos_evento`
--
ALTER TABLE `tipos_evento`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `eventos`
--
ALTER TABLE `eventos`
  ADD CONSTRAINT `eventos_ibfk_1` FOREIGN KEY (`tipo_evento_id`) REFERENCES `tipos_evento` (`id`),
  ADD CONSTRAINT `eventos_ibfk_2` FOREIGN KEY (`categoria_id`) REFERENCES `categorias_evento` (`id`);

--
-- Filtros para la tabla `inscripciones`
--
ALTER TABLE `inscripciones`
  ADD CONSTRAINT `inscripciones_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `estudiantes` (`id`),
  ADD CONSTRAINT `inscripciones_ibfk_2` FOREIGN KEY (`evento_id`) REFERENCES `eventos` (`id`);

--
-- Filtros para la tabla `requisitos_evento`
--
ALTER TABLE `requisitos_evento`
  ADD CONSTRAINT `requisitos_evento_ibfk_1` FOREIGN KEY (`evento_id`) REFERENCES `eventos` (`id`);

--
-- Filtros para la tabla `requisitos_inscripcion`
--
ALTER TABLE `requisitos_inscripcion`
  ADD CONSTRAINT `requisitos_inscripcion_ibfk_1` FOREIGN KEY (`inscripcion_id`) REFERENCES `inscripciones` (`id`),
  ADD CONSTRAINT `requisitos_inscripcion_ibfk_2` FOREIGN KEY (`requisito_id`) REFERENCES `requisitos_evento` (`id`);

--
-- Filtros para la tabla `resoluciones`
--
ALTER TABLE `resoluciones`
  ADD CONSTRAINT `resoluciones_ibfk_1` FOREIGN KEY (`id_solicitud`) REFERENCES `solicitudes` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
