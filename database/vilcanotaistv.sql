-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 03-07-2026 a las 02:56:55
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET
SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET
time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `vilcanotaistv`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `alertas_academicas`
--

CREATE TABLE `alertas_academicas`
(
    `id_alerta`      int(11) NOT NULL,
    `id_estudiante`  int(11) DEFAULT NULL,
    `id_docente`     int(11) DEFAULT NULL,
    `id_curso`       int(11) DEFAULT NULL,
    `tipo`           enum('RIESGO_ACADEMICO','INASISTENCIA','PORTAFOLIO','HORARIO','SISTEMA') NOT NULL,
    `severidad`      enum('BAJA','MEDIA','ALTA','CRITICA') NOT NULL DEFAULT 'MEDIA',
    `titulo`         varchar(150) NOT NULL,
    `detalle`        text                  DEFAULT NULL,
    `estado`         enum('ABIERTA','EN_SEGUIMIENTO','CERRADA') NOT NULL DEFAULT 'ABIERTA',
    `fecha_creacion` timestamp    NOT NULL DEFAULT current_timestamp(),
    `fecha_cierre`   datetime              DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asistencia_detalle`
--

CREATE TABLE `asistencia_detalle`
(
    `id_asistencia`  int(11) NOT NULL,
    `id_sesion`      int(11) NOT NULL,
    `id_estudiante`  int(11) NOT NULL,
    `estado`         enum('PRESENTE','TARDANZA','AUSENTE','JUSTIFICADO') NOT NULL DEFAULT 'PRESENTE',
    `observacion`    varchar(255)       DEFAULT NULL,
    `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asistencia_sesiones`
--

CREATE TABLE `asistencia_sesiones`
(
    `id_sesion`    int(11) NOT NULL,
    `id_curso`     int(11) NOT NULL,
    `id_docente`   int(11) DEFAULT NULL,
    `id_horario`   int(11) DEFAULT NULL,
    `id_periodo`   int(11) NOT NULL,
    `fecha_sesion` date NOT NULL,
    `hora_inicio`  time         DEFAULT NULL,
    `hora_fin`     time         DEFAULT NULL,
    `tema`         varchar(180) DEFAULT NULL,
    `estado`       enum('PROGRAMADA','REALIZADA','SUSPENDIDA') NOT NULL DEFAULT 'PROGRAMADA'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `auditoria_sistema`
--

CREATE TABLE `auditoria_sistema`
(
    `id_auditoria` bigint(20) NOT NULL,
    `id_usuario`   int(11) DEFAULT NULL,
    `modulo`       varchar(80) NOT NULL,
    `accion`       varchar(80) NOT NULL,
    `detalle`      text                 DEFAULT NULL,
    `ip_origen`    varchar(45)          DEFAULT NULL,
    `fecha_evento` timestamp   NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `aulas`
--

CREATE TABLE `aulas`
(
    `id_aula`   int(11) NOT NULL,
    `codigo`    varchar(30)  NOT NULL,
    `nombre`    varchar(100) NOT NULL,
    `tipo`      enum('AULA','LABORATORIO','TALLER','CAMPO','OTRO') NOT NULL DEFAULT 'AULA',
    `capacidad` int(11) NOT NULL DEFAULT 30,
    `ubicacion` varchar(120) DEFAULT NULL,
    `estado`    enum('DISPONIBLE','MANTENIMIENTO','INACTIVO') NOT NULL DEFAULT 'DISPONIBLE'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `aulas`
--

INSERT INTO `aulas` (`id_aula`, `codigo`, `nombre`, `tipo`, `capacidad`, `ubicacion`, `estado`)
VALUES (1, 'A201', 'Aula 201', 'AULA', 35, 'Pabellon A', 'DISPONIBLE'),
       (2, 'A202', 'Aula 202', 'AULA', 35, 'Pabellon A', 'DISPONIBLE'),
       (3, 'A203', 'Aula 203', 'AULA', 30, 'Pabellon A', 'DISPONIBLE'),
       (4, 'LAB-COMP', 'Laboratorio de Computo', 'LABORATORIO', 28, 'Pabellon B', 'DISPONIBLE'),
       (5, 'LAB-REDES', 'Laboratorio de Redes', 'LABORATORIO', 24, 'Pabellon B', 'DISPONIBLE');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuracion_sistema`
--

CREATE TABLE `configuracion_sistema`
(
    `id_configuracion`    int(11) NOT NULL,
    `clave`               varchar(80) NOT NULL,
    `valor`               text        NOT NULL,
    `descripcion`         varchar(255) DEFAULT NULL,
    `fecha_actualizacion` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `configuracion_sistema`
--

INSERT INTO `configuracion_sistema` (`id_configuracion`, `clave`, `valor`, `descripcion`, `fecha_actualizacion`)
VALUES (1, 'nota_minima_aprobatoria', '10.5', 'Nota minima para aprobar una unidad didactica', NULL),
       (2, 'porcentaje_riesgo_asistencia', '70', 'Umbral de asistencia para alerta academica', NULL),
       (3, 'ia_predictiva_modelo', 'reglas-academicas-v1', 'Modelo activo para deteccion preventiva', NULL),
       (4, 'horarios_protegidos', '1', 'La tabla horarios no debe modificarse desde scripts complementarios', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cursos`
--

CREATE TABLE `cursos`
(
    `id_curso`       int(11) NOT NULL,
    `id_docente`     int(11) DEFAULT NULL,
    `nombre_curso`   varchar(150) NOT NULL,
    `modulo`         varchar(100) NOT NULL,
    `semestre`       varchar(10)  NOT NULL,
    `creditos`       int(11) NOT NULL,
    `horas_teoria`   int(11) NOT NULL,
    `horas_practica` int(11) NOT NULL,
    `horas_ud`       int(11) NOT NULL,
    `total_teoria`   int(11) NOT NULL,
    `total_practica` int(11) NOT NULL,
    `total_horas`    int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `cursos`
--

INSERT INTO `cursos` (`id_curso`, `id_docente`, `nombre_curso`, `modulo`, `semestre`, `creditos`, `horas_teoria`,
                      `horas_practica`, `horas_ud`, `total_teoria`, `total_practica`, `total_horas`)
VALUES (1, 1, 'Lógica de programación', 'Módulo I', 'I', 5, 1, 2, 3, 16, 64, 80),
       (2, 7, 'Diseño de software', 'Módulo I', 'I', 6, 2, 2, 4, 32, 64, 96),
       (3, 5, 'Modelamiento de bases de datos', 'Módulo I', 'I', 7, 1, 3, 4, 16, 96, 112),
       (4, 6, 'Técnicas de programación', 'Módulo I', 'I', 6, 2, 2, 4, 32, 64, 96),
       (5, 2, 'Comunicación oral', 'Módulo I', 'I', 3, 1, 1, 2, 16, 32, 48),
       (6, 3, 'Aplicaciones en internet', 'Módulo I', 'I', 3, 1, 1, 2, 16, 32, 48),
       (7, 5, 'Algoritmos y estructuras de datos', 'Módulo I', 'II', 6, 2, 2, 6, 32, 64, 96),
       (8, 2, 'Diseño web', 'Módulo I', 'II', 6, 2, 2, 6, 32, 64, 96),
       (9, 3, 'Gestión de base de datos', 'Módulo I', 'II', 7, 1, 3, 7, 16, 96, 112),
       (10, 6, 'Programación orientada a objetos', 'Módulo I', 'II', 5, 1, 2, 5, 16, 64, 80),
       (11, 1, 'Interpretación y producción textos', 'Módulo I', 'II', 3, 1, 1, 3, 16, 32, 48),
       (12, 7, 'Ofimática', 'Módulo I', 'II', 3, 1, 1, 3, 16, 32, 48),
       (13, 3, 'Administración de sitios web', 'Módulo II', 'III', 8, 2, 3, 5, 32, 96, 128),
       (14, 5, 'Seguridad informática', 'Módulo II', 'III', 3, 1, 1, 2, 16, 32, 48),
       (15, 4, 'Aplicaciones web', 'Módulo II', 'III', 9, 1, 4, 5, 16, 128, 144),
       (16, 2, 'Aplicaciones móviles', 'Módulo II', 'III', 7, 1, 3, 4, 16, 96, 112),
       (17, 6, 'Inglés para la comunicación oral', 'Módulo II', 'III', 3, 1, 1, 2, 16, 32, 48),
       (18, 3, 'Lenguaje de programación concurrente', 'Módulo II', 'IV', 8, 2, 3, 8, 32, 96, 128),
       (19, 1, 'Lenguaje de programación web dinámico', 'Módulo II', 'IV', 8, 2, 3, 8, 32, 96, 128),
       (20, 4, 'Base de datos no relacionales', 'Módulo II', 'V', 7, 1, 3, 7, 16, 96, 112),
       (21, 4, 'Modelamiento de software de entretenimiento', 'Módulo II', 'IV', 5, 1, 2, 5, 16, 64, 80),
       (22, 5, 'Comprensión y redacción en inglés', 'Módulo II', 'IV', 2, 0, 1, 2, 0, 32, 32),
       (23, 2, 'Gestión de proyectos de TI', 'Módulo III', 'V', 5, 1, 2, 5, 16, 64, 80),
       (24, 1, 'Pruebas y calidad del software', 'Módulo III', 'V', 7, 1, 3, 7, 16, 96, 112),
       (25, 6, 'Inteligencia de negocios', 'Módulo III', 'V', 5, 1, 2, 5, 16, 64, 80),
       (26, 3, 'Gestión de servicios de TI', 'Módulo III', 'V', 7, 1, 3, 7, 16, 96, 112),
       (27, 7, 'Fundamentos de innovación tecnológica', 'Módulo III', 'V', 3, 1, 1, 3, 16, 32, 48),
       (28, 5, 'Comportamiento ético', 'Módulo III', 'V', 3, 1, 1, 3, 16, 32, 48),
       (29, NULL, 'Gestión de servidores', 'Módulo III', 'VI', 5, 1, 2, 3, 16, 64, 80),
       (30, NULL, 'Gestión de redes informáticas', 'Módulo III', 'VI', 7, 1, 3, 4, 16, 96, 112),
       (31, NULL, 'Soporte de auditoría de TI', 'Módulo III', 'VI', 6, 2, 2, 4, 32, 64, 96),
       (32, NULL, 'Auditoría de software', 'Módulo III', 'VI', 3, 1, 1, 2, 16, 32, 48),
       (33, NULL, 'Inteligencia artificial', 'Módulo III', 'VI', 3, 1, 1, 2, 16, 32, 48),
       (34, NULL, 'Solución de problemas', 'Módulo III', 'VI', 3, 1, 1, 2, 16, 32, 48),
       (35, NULL, 'Innovación tecnológica', 'Módulo III', 'VI', 3, 1, 1, 2, 16, 32, 48);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `docentes`
--

CREATE TABLE `docentes`
(
    `id_docente`       int(11) NOT NULL,
    `codigo_docente`   varchar(20)  NOT NULL,
    `dni`              char(8)      NOT NULL,
    `nombres`          varchar(100) NOT NULL,
    `apellido_paterno` varchar(50)           DEFAULT NULL,
    `apellido_materno` varchar(50)           DEFAULT NULL,
    `especialidad`     varchar(100)          DEFAULT NULL,
    `estado`           enum('ACTIVO','INACTIVO') DEFAULT 'ACTIVO',
    `fecha_registro`   timestamp    NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `docentes`
--

INSERT INTO `docentes` (`id_docente`, `codigo_docente`, `dni`, `nombres`, `apellido_paterno`, `apellido_materno`,
                        `especialidad`, `estado`, `fecha_registro`)
VALUES (1, 'DOC001', '00000001', 'Diana', 'Huaylla', '', 'Desarrollo de Sistemas', 'ACTIVO', '2026-06-10 19:02:48'),
       (2, 'DOC002', '00000002', 'Jhon', 'Barrientos', 'Ferro', 'Desarrollo de Sistemas', 'ACTIVO',
        '2026-06-10 19:02:48'),
       (3, 'DOC003', '00000003', 'Hernan', 'Palomino', '', 'Desarrollo de Sistemas', 'ACTIVO', '2026-06-10 19:02:48'),
       (4, 'DOC004', '00000004', 'Rosa Luz', 'Jara', '', 'Desarrollo de Sistemas', 'ACTIVO', '2026-06-10 19:02:48'),
       (5, 'DOC005', '00000005', 'Fredy', 'Quispe', '', 'Desarrollo de Sistemas', 'ACTIVO', '2026-06-10 19:02:48'),
       (6, 'DOC006', '00000006', 'Pavel', 'Lech', '', 'Desarrollo de Sistemas', 'ACTIVO', '2026-06-10 19:02:48'),
       (7, 'DOC007', '00000007', 'Fernando', 'Cornejo', '', 'Desarrollo de Sistemas', 'ACTIVO', '2026-06-10 19:02:48');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estudiantes`
--

CREATE TABLE `estudiantes`
(
    `id_estudiante`     int(11) NOT NULL,
    `codigo_estudiante` varchar(20)  NOT NULL,
    `dni`               char(8)               DEFAULT NULL,
    `nombres`           varchar(120) NOT NULL,
    `apellido_paterno`  varchar(80)           DEFAULT NULL,
    `apellido_materno`  varchar(80)           DEFAULT NULL,
    `correo`            varchar(150)          DEFAULT NULL,
    `telefono`          varchar(20)           DEFAULT NULL,
    `id_programa`       int(11) NOT NULL,
    `ciclo`             enum('I','II','III','IV','V','VI') NOT NULL DEFAULT 'I',
    `estado`            enum('REGULAR','OBSERVADO','RIESGO','RETIRADO','EGRESADO') NOT NULL DEFAULT 'REGULAR',
    `fecha_registro`    timestamp    NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `estudiantes`
--

INSERT INTO `estudiantes` (`id_estudiante`, `codigo_estudiante`, `dni`, `nombres`, `apellido_paterno`,
                           `apellido_materno`, `correo`, `telefono`, `id_programa`, `ciclo`, `estado`, `fecha_registro`)
VALUES (1, '2024-0158', '74000158', 'Maria', 'Condori', 'Apaza', 'maria.condori@istv.edu.pe', '900000158', 1, 'III',
        'REGULAR', '2026-06-22 01:19:15'),
       (2, '2024-0203', '74000203', 'Juan', 'Quispe', 'Huanca', 'juan.quispe@istv.edu.pe', '900000203', 1, 'II',
        'OBSERVADO', '2026-06-22 01:19:15'),
       (3, '2023-0091', '73000091', 'Pedro', 'Ccahuana', 'Lima', 'pedro.ccahuana@istv.edu.pe', '900000091', 1, 'V',
        'RIESGO', '2026-06-22 01:19:15'),
       (4, '2023-0142', '73000142', 'Ana', 'Ticona', 'Roque', 'ana.ticona@istv.edu.pe', '900000142', 1, 'V',
        'OBSERVADO', '2026-06-22 01:19:15'),
       (5, '2026-0031', '76000031', 'Rosa', 'Mamani', 'Ccoa', 'rosa.mamani@istv.edu.pe', '900000031', 2, 'I', 'REGULAR',
        '2026-06-22 01:19:15'),
       (6, '2026-0032', '76000032', 'Carlos', 'Turpo', 'Flores', 'carlos.turpo@istv.edu.pe', '900000032', 2, 'III',
        'RIESGO', '2026-06-22 01:19:15'),
       (7, '2023-0145', '73000145', 'Elena', 'Ticona', 'Roque', 'elena.ticona@istv.edu.pe', '900000145', 3, 'V',
        'REGULAR', '2026-06-22 01:19:15');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `horarios`
--

CREATE TABLE `horarios`
(
    `id_horario`  int(11) NOT NULL,
    `id_curso`    int(11) NOT NULL,
    `id_docente`  int(11) NOT NULL,
    `dia`         varchar(20) DEFAULT NULL,
    `hora_inicio` time        DEFAULT NULL,
    `hora_fin`    time        DEFAULT NULL,
    `aula`        varchar(80) DEFAULT NULL,
    `estado`      varchar(30) DEFAULT 'Confirmado'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `horarios`
--

INSERT INTO `horarios` (`id_horario`, `id_curso`, `id_docente`, `dia`, `hora_inicio`, `hora_fin`, `aula`, `estado`)
VALUES (1, 15, 4, 'Lunes', '08:00:00', '08:45:00', 'A202', 'Confirmado'),
       (2, 15, 4, 'Lunes', '08:45:00', '09:30:00', 'A203', 'Confirmado'),
       (3, 15, 4, 'Lunes', '09:30:00', '10:15:00', 'A202', 'Confirmado'),
       (4, 15, 4, 'Martes', '08:00:00', '08:45:00', 'A202', 'Confirmado'),
       (5, 15, 4, 'Martes', '08:45:00', '09:30:00', 'A203', 'Confirmado'),
       (6, 15, 4, 'Martes', '09:30:00', '10:15:00', 'A201', 'Confirmado'),
       (7, 15, 4, 'Miércoles', '08:00:00', '08:45:00', 'A201', 'Confirmado'),
       (8, 13, 3, 'Jueves', '09:30:00', '10:15:00', 'Invernadero', 'Confirmado'),
       (9, 13, 3, 'Miércoles', '12:00:00', '12:45:00', 'Campo Experimental', 'Confirmado'),
       (10, 13, 3, 'Jueves', '10:15:00', '11:00:00', 'A205', 'Confirmado'),
       (11, 13, 3, 'Jueves', '08:45:00', '09:30:00', 'A205', 'Confirmado'),
       (12, 13, 3, 'Miércoles', '11:15:00', '12:00:00', 'A205', 'Confirmado'),
       (13, 13, 3, 'Martes', '12:00:00', '12:45:00', 'A203', 'Confirmado'),
       (14, 13, 3, 'Lunes', '11:15:00', '12:00:00', 'Campo Experimental', 'Confirmado'),
       (15, 13, 3, 'Viernes', '10:15:00', '11:00:00', 'A201', 'Confirmado'),
       (16, 14, 5, 'Martes', '11:15:00', '12:00:00', 'Lab. Cómputo', 'Confirmado'),
       (17, 14, 5, 'Jueves', '11:15:00', '12:00:00', 'A201', 'Confirmado'),
       (18, 14, 5, 'Jueves', '12:00:00', '12:45:00', 'A202', 'Confirmado'),
       (19, 15, 4, 'Miércoles', '08:45:00', '09:30:00', 'A204', 'Confirmado'),
       (20, 15, 4, 'Miércoles', '09:30:00', '10:15:00', 'A203', 'Confirmado'),
       (21, 16, 2, 'Martes', '10:15:00', '11:00:00', 'Lab. Cómputo', 'Confirmado'),
       (22, 16, 2, 'Miércoles', '10:15:00', '11:00:00', 'A204', 'Confirmado'),
       (23, 16, 2, 'Lunes', '12:00:00', '12:45:00', 'Lab. Cómputo', 'Confirmado'),
       (24, 16, 2, 'Viernes', '08:00:00', '08:45:00', 'A203', 'Confirmado'),
       (25, 16, 2, 'Viernes', '08:45:00', '09:30:00', 'A204', 'Confirmado'),
       (26, 16, 2, 'Viernes', '09:30:00', '10:15:00', 'A205', 'Confirmado'),
       (27, 16, 2, 'Jueves', '08:00:00', '08:45:00', 'A205', 'Confirmado'),
       (28, 17, 6, 'Viernes', '12:00:00', '12:45:00', 'A203', 'Confirmado'),
       (29, 17, 6, 'Lunes', '10:15:00', '11:00:00', 'A204', 'Confirmado'),
       (30, 17, 6, 'Viernes', '11:15:00', '12:00:00', 'A205', 'Confirmado'),
       (31, 1, 1, 'Lunes', '08:00:00', '08:45:00', 'A201', 'Confirmado'),
       (32, 1, 1, 'Lunes', '08:45:00', '09:30:00', 'A201', 'Confirmado'),
       (33, 1, 1, 'Lunes', '09:30:00', '10:15:00', 'A201', 'Confirmado'),
       (34, 1, 1, 'Martes', '08:00:00', '08:45:00', 'A203', 'Confirmado'),
       (35, 1, 1, 'Jueves', '08:45:00', '09:30:00', 'Lab. Cómputo', 'Confirmado'),
       (36, 2, 7, 'Miércoles', '08:00:00', '08:45:00', 'A202', 'Confirmado'),
       (37, 2, 7, 'Miércoles', '08:45:00', '09:30:00', 'A202', 'Confirmado'),
       (38, 2, 7, 'Martes', '09:30:00', '10:15:00', 'Lab. Cómputo', 'Confirmado'),
       (39, 2, 7, 'Lunes', '12:00:00', '12:45:00', 'A203', 'Confirmado'),
       (40, 2, 7, 'Martes', '10:15:00', '11:00:00', 'A201', 'Confirmado'),
       (41, 2, 7, 'Miércoles', '09:30:00', '10:15:00', 'Invernadero', 'Confirmado'),
       (42, 3, 5, 'Martes', '08:45:00', '09:30:00', 'Campo Experimental', 'Confirmado'),
       (43, 3, 5, 'Jueves', '08:00:00', '08:45:00', 'A203', 'Confirmado'),
       (44, 3, 5, 'Viernes', '11:15:00', '12:00:00', 'Campo Experimental', 'Confirmado'),
       (45, 3, 5, 'Miércoles', '11:15:00', '12:00:00', 'Invernadero', 'Confirmado'),
       (46, 3, 5, 'Miércoles', '12:00:00', '12:45:00', 'Invernadero', 'Confirmado'),
       (47, 3, 5, 'Lunes', '11:15:00', '12:00:00', 'A202', 'Confirmado'),
       (48, 3, 5, 'Viernes', '12:00:00', '12:45:00', 'Lab. Redes', 'Confirmado'),
       (49, 4, 6, 'Martes', '11:15:00', '12:00:00', 'A204', 'Confirmado'),
       (50, 4, 6, 'Miércoles', '10:15:00', '11:00:00', 'A201', 'Confirmado'),
       (51, 4, 6, 'Martes', '12:00:00', '12:45:00', 'Lab. Cómputo', 'Confirmado'),
       (52, 4, 6, 'Jueves', '11:15:00', '12:00:00', 'A203', 'Confirmado'),
       (53, 4, 6, 'Jueves', '12:00:00', '12:45:00', 'Invernadero', 'Confirmado'),
       (54, 4, 6, 'Viernes', '10:15:00', '11:00:00', 'A205', 'Confirmado'),
       (55, 5, 2, 'Lunes', '10:15:00', '11:00:00', 'Campo Experimental', 'Confirmado'),
       (56, 5, 2, 'Jueves', '09:30:00', '10:15:00', 'A202', 'Confirmado'),
       (57, 5, 2, 'Jueves', '10:15:00', '11:00:00', 'A201', 'Confirmado'),
       (58, 6, 3, 'Viernes', '08:45:00', '09:30:00', 'A201', 'Confirmado'),
       (59, 6, 3, 'Viernes', '08:00:00', '08:45:00', 'A202', 'Confirmado'),
       (60, 6, 3, 'Viernes', '09:30:00', '10:15:00', 'A203', 'Confirmado');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `horarios_ia_generados`
--

CREATE TABLE `horarios_ia_generados`
(
    `id_generacion`    int(11) NOT NULL,
    `id_usuario`       int(11) DEFAULT NULL,
    `id_periodo`       int(11) DEFAULT NULL,
    `programa`         varchar(150)       DEFAULT NULL,
    `modelo`           varchar(80)        DEFAULT NULL,
    `prompt_resumen`   text               DEFAULT NULL,
    `resultado_json`   longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`resultado_json`)),
    `estado`           enum('BORRADOR','APROBADO','DESCARTADO') NOT NULL DEFAULT 'BORRADOR',
    `fecha_generacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ia_predicciones`
--

CREATE TABLE `ia_predicciones`
(
    `id_prediccion`        int(11) NOT NULL,
    `id_estudiante`        int(11) DEFAULT NULL,
    `id_curso`             int(11) DEFAULT NULL,
    `id_periodo`           int(11) DEFAULT NULL,
    `modelo`               varchar(80)   NOT NULL DEFAULT 'reglas-academicas-v1',
    `score_riesgo`         decimal(5, 2) NOT NULL DEFAULT 0.00,
    `probabilidad_aprobar` decimal(5, 2)          DEFAULT NULL,
    `nivel`                enum('BAJO','MEDIO','ALTO','CRITICO') NOT NULL DEFAULT 'MEDIO',
    `factores_json`        longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`factores_json`)),
    `simulacion_json`      longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`simulacion_json`)),
    `recomendacion`        text                   DEFAULT NULL,
    `fecha_prediccion`     timestamp     NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `matriculas`
--

CREATE TABLE `matriculas`
(
    `id_matricula`    int(11) NOT NULL,
    `id_estudiante`   int(11) NOT NULL,
    `id_periodo`      int(11) NOT NULL,
    `ciclo`           enum('I','II','III','IV','V','VI') NOT NULL,
    `estado`          enum('MATRICULADO','RESERVA','RETIRADO','CERRADO') NOT NULL DEFAULT 'MATRICULADO',
    `fecha_matricula` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `matriculas`
--

INSERT INTO `matriculas` (`id_matricula`, `id_estudiante`, `id_periodo`, `ciclo`, `estado`, `fecha_matricula`)
VALUES (1, 1, 1, 'III', 'MATRICULADO', '2026-03-10'),
       (2, 2, 1, 'II', 'MATRICULADO', '2026-03-10'),
       (3, 3, 1, 'V', 'MATRICULADO', '2026-03-10'),
       (4, 4, 1, 'V', 'MATRICULADO', '2026-03-10'),
       (5, 5, 1, 'I', 'MATRICULADO', '2026-03-10'),
       (6, 6, 1, 'III', 'MATRICULADO', '2026-03-10'),
       (7, 7, 1, 'V', 'MATRICULADO', '2026-03-10');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `matricula_cursos`
--

CREATE TABLE `matricula_cursos`
(
    `id_matricula_curso` int(11) NOT NULL,
    `id_matricula`       int(11) NOT NULL,
    `id_curso`           int(11) NOT NULL,
    `estado`             enum('EN_CURSO','APROBADO','DESAPROBADO','RETIRADO') NOT NULL DEFAULT 'EN_CURSO'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `matricula_cursos`
--

INSERT INTO `matricula_cursos` (`id_matricula_curso`, `id_matricula`, `id_curso`, `estado`)
VALUES (1, 5, 1, 'EN_CURSO'),
       (2, 5, 2, 'EN_CURSO'),
       (3, 5, 3, 'EN_CURSO'),
       (4, 5, 4, 'EN_CURSO'),
       (5, 5, 5, 'EN_CURSO'),
       (6, 5, 6, 'EN_CURSO'),
       (7, 2, 7, 'EN_CURSO'),
       (8, 2, 8, 'EN_CURSO'),
       (9, 2, 9, 'EN_CURSO'),
       (10, 2, 10, 'EN_CURSO'),
       (11, 2, 11, 'EN_CURSO'),
       (12, 2, 12, 'EN_CURSO'),
       (13, 1, 13, 'EN_CURSO'),
       (14, 6, 13, 'EN_CURSO'),
       (15, 1, 14, 'EN_CURSO'),
       (16, 6, 14, 'EN_CURSO'),
       (17, 1, 15, 'EN_CURSO'),
       (18, 6, 15, 'EN_CURSO'),
       (19, 1, 16, 'EN_CURSO'),
       (20, 6, 16, 'EN_CURSO'),
       (21, 1, 17, 'EN_CURSO'),
       (22, 6, 17, 'EN_CURSO'),
       (23, 3, 20, 'EN_CURSO'),
       (24, 4, 20, 'EN_CURSO'),
       (25, 7, 20, 'EN_CURSO'),
       (26, 3, 23, 'EN_CURSO'),
       (27, 4, 23, 'EN_CURSO'),
       (28, 7, 23, 'EN_CURSO'),
       (29, 3, 24, 'EN_CURSO'),
       (30, 4, 24, 'EN_CURSO'),
       (31, 7, 24, 'EN_CURSO'),
       (32, 3, 25, 'EN_CURSO'),
       (33, 4, 25, 'EN_CURSO'),
       (34, 7, 25, 'EN_CURSO'),
       (35, 3, 26, 'EN_CURSO'),
       (36, 4, 26, 'EN_CURSO'),
       (37, 7, 26, 'EN_CURSO'),
       (38, 3, 27, 'EN_CURSO'),
       (39, 4, 27, 'EN_CURSO'),
       (40, 7, 27, 'EN_CURSO'),
       (41, 3, 28, 'EN_CURSO'),
       (42, 4, 28, 'EN_CURSO'),
       (43, 7, 28, 'EN_CURSO');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mensajes`
--

CREATE TABLE `mensajes`
(
    `id_mensaje`      int(11) NOT NULL,
    `id_remitente`    int(11) NOT NULL,
    `id_destinatario` int(11) NOT NULL,
    `asunto`          varchar(180) NOT NULL,
    `mensaje`         text         NOT NULL,
    `leido`           tinyint(1) NOT NULL DEFAULT 0,
    `fecha_envio`     timestamp    NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notas`
--

CREATE TABLE `notas`
(
    `id_nota`             int(11) NOT NULL,
    `id_matricula_curso`  int(11) NOT NULL,
    `unidad`              varchar(20) NOT NULL DEFAULT 'I',
    `practica`            decimal(5, 2)        DEFAULT NULL,
    `teoria`              decimal(5, 2)        DEFAULT NULL,
    `examen`              decimal(5, 2)        DEFAULT NULL,
    `promedio`            decimal(5, 2) GENERATED ALWAYS AS (round(
        coalesce(`practica`, 0) * 0.20 + coalesce(`teoria`, 0) * 0.30 + coalesce(`examen`, 0) * 0.50, 2)) STORED,
    `estado`              enum('ABIERTO','CERRADO','RECTIFICADO') NOT NULL DEFAULT 'ABIERTO',
    `fecha_registro`      timestamp   NOT NULL DEFAULT current_timestamp(),
    `fecha_actualizacion` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `notas`
--

INSERT INTO `notas` (`id_nota`, `id_matricula_curso`, `unidad`, `practica`, `teoria`, `examen`, `estado`,
                     `fecha_registro`, `fecha_actualizacion`)
VALUES (1, 13, 'I', 13.00, 14.00, 15.00, 'ABIERTO', '2026-06-22 01:19:15', NULL),
       (2, 15, 'I', 13.00, 14.00, 15.00, 'ABIERTO', '2026-06-22 01:19:15', NULL),
       (3, 17, 'I', 13.00, 14.00, 15.00, 'ABIERTO', '2026-06-22 01:19:15', NULL),
       (4, 19, 'I', 13.00, 14.00, 15.00, 'ABIERTO', '2026-06-22 01:19:15', NULL),
       (5, 21, 'I', 13.00, 14.00, 15.00, 'ABIERTO', '2026-06-22 01:19:15', NULL),
       (6, 7, 'I', 13.00, 14.00, 15.00, 'ABIERTO', '2026-06-22 01:19:15', NULL),
       (7, 8, 'I', 13.00, 14.00, 15.00, 'ABIERTO', '2026-06-22 01:19:15', NULL),
       (8, 9, 'I', 13.00, 14.00, 15.00, 'ABIERTO', '2026-06-22 01:19:15', NULL),
       (9, 10, 'I', 13.00, 14.00, 15.00, 'ABIERTO', '2026-06-22 01:19:15', NULL),
       (10, 11, 'I', 13.00, 14.00, 15.00, 'ABIERTO', '2026-06-22 01:19:15', NULL),
       (11, 12, 'I', 13.00, 14.00, 15.00, 'ABIERTO', '2026-06-22 01:19:15', NULL),
       (12, 23, 'I', 8.00, 9.00, 8.00, 'ABIERTO', '2026-06-22 01:19:15', NULL),
       (13, 26, 'I', 8.00, 9.00, 8.00, 'ABIERTO', '2026-06-22 01:19:15', NULL),
       (14, 29, 'I', 8.00, 9.00, 8.00, 'ABIERTO', '2026-06-22 01:19:15', NULL),
       (15, 32, 'I', 8.00, 9.00, 8.00, 'ABIERTO', '2026-06-22 01:19:15', NULL),
       (16, 35, 'I', 8.00, 9.00, 8.00, 'ABIERTO', '2026-06-22 01:19:15', NULL),
       (17, 38, 'I', 8.00, 9.00, 8.00, 'ABIERTO', '2026-06-22 01:19:15', NULL),
       (18, 41, 'I', 8.00, 9.00, 8.00, 'ABIERTO', '2026-06-22 01:19:15', NULL),
       (19, 24, 'I', 13.00, 14.00, 15.00, 'ABIERTO', '2026-06-22 01:19:15', NULL),
       (20, 27, 'I', 13.00, 14.00, 15.00, 'ABIERTO', '2026-06-22 01:19:15', NULL),
       (21, 30, 'I', 13.00, 14.00, 15.00, 'ABIERTO', '2026-06-22 01:19:15', NULL),
       (22, 33, 'I', 13.00, 14.00, 15.00, 'ABIERTO', '2026-06-22 01:19:15', NULL),
       (23, 36, 'I', 13.00, 14.00, 15.00, 'ABIERTO', '2026-06-22 01:19:15', NULL),
       (24, 39, 'I', 13.00, 14.00, 15.00, 'ABIERTO', '2026-06-22 01:19:15', NULL),
       (25, 42, 'I', 13.00, 14.00, 15.00, 'ABIERTO', '2026-06-22 01:19:15', NULL),
       (26, 1, 'I', 13.00, 14.00, 15.00, 'ABIERTO', '2026-06-22 01:19:15', NULL),
       (27, 2, 'I', 13.00, 14.00, 15.00, 'ABIERTO', '2026-06-22 01:19:15', NULL),
       (28, 3, 'I', 13.00, 14.00, 15.00, 'ABIERTO', '2026-06-22 01:19:15', NULL),
       (29, 4, 'I', 13.00, 14.00, 15.00, 'ABIERTO', '2026-06-22 01:19:15', NULL),
       (30, 5, 'I', 13.00, 14.00, 15.00, 'ABIERTO', '2026-06-22 01:19:15', NULL),
       (31, 6, 'I', 13.00, 14.00, 15.00, 'ABIERTO', '2026-06-22 01:19:15', NULL),
       (32, 14, 'I', 8.00, 9.00, 8.00, 'ABIERTO', '2026-06-22 01:19:15', NULL),
       (33, 16, 'I', 8.00, 9.00, 8.00, 'ABIERTO', '2026-06-22 01:19:15', NULL),
       (34, 18, 'I', 8.00, 9.00, 8.00, 'ABIERTO', '2026-06-22 01:19:15', NULL),
       (35, 20, 'I', 8.00, 9.00, 8.00, 'ABIERTO', '2026-06-22 01:19:15', NULL),
       (36, 22, 'I', 8.00, 9.00, 8.00, 'ABIERTO', '2026-06-22 01:19:15', NULL),
       (37, 25, 'I', 13.00, 14.00, 15.00, 'ABIERTO', '2026-06-22 01:19:15', NULL),
       (38, 28, 'I', 13.00, 14.00, 15.00, 'ABIERTO', '2026-06-22 01:19:15', NULL),
       (39, 31, 'I', 13.00, 14.00, 15.00, 'ABIERTO', '2026-06-22 01:19:15', NULL),
       (40, 34, 'I', 13.00, 14.00, 15.00, 'ABIERTO', '2026-06-22 01:19:15', NULL),
       (41, 37, 'I', 13.00, 14.00, 15.00, 'ABIERTO', '2026-06-22 01:19:15', NULL),
       (42, 40, 'I', 13.00, 14.00, 15.00, 'ABIERTO', '2026-06-22 01:19:15', NULL),
       (43, 43, 'I', 13.00, 14.00, 15.00, 'ABIERTO', '2026-06-22 01:19:15', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificaciones`
--

CREATE TABLE `notificaciones`
(
    `id_notificacion` int(11) NOT NULL,
    `id_usuario`      int(11) DEFAULT NULL,
    `tipo`            varchar(40)  NOT NULL,
    `titulo`          varchar(150) NOT NULL,
    `detalle`         varchar(255)          DEFAULT NULL,
    `url_destino`     varchar(255)          DEFAULT NULL,
    `leido`           tinyint(1) NOT NULL DEFAULT 0,
    `fecha_creacion`  timestamp    NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `notificaciones`
--

INSERT INTO `notificaciones` (`id_notificacion`, `id_usuario`, `tipo`, `titulo`, `detalle`, `url_destino`, `leido`,
                              `fecha_creacion`)
VALUES (1, 1, 'SISTEMA', 'Bienvenido al sistema academico', 'Su cuenta se encuentra activa.', 'index.html', 0,
        '2026-06-22 01:46:11'),
       (2, 2, 'SISTEMA', 'Bienvenido al sistema academico', 'Su cuenta se encuentra activa.', 'index.html', 0,
        '2026-06-22 01:46:11'),
       (3, 3, 'SISTEMA', 'Bienvenido al sistema academico', 'Su cuenta se encuentra activa.', 'index.html', 0,
        '2026-06-22 01:46:11'),
       (4, 4, 'SISTEMA', 'Bienvenido al sistema academico', 'Su cuenta se encuentra activa.', 'index.html', 0,
        '2026-06-22 01:46:11'),
       (5, 5, 'SISTEMA', 'Bienvenido al sistema academico', 'Su cuenta se encuentra activa.', 'index.html', 0,
        '2026-06-22 01:46:11'),
       (6, 6, 'SISTEMA', 'Bienvenido al sistema academico', 'Su cuenta se encuentra activa.', 'index.html', 0,
        '2026-06-22 01:46:11'),
       (7, 7, 'SISTEMA', 'Bienvenido al sistema academico', 'Su cuenta se encuentra activa.', 'index.html', 0,
        '2026-06-22 01:46:11'),
       (8, 8, 'SISTEMA', 'Bienvenido al sistema academico', 'Su cuenta se encuentra activa.', 'index.html', 0,
        '2026-06-22 01:46:11'),
       (9, 9, 'SISTEMA', 'Bienvenido al sistema academico', 'Su cuenta se encuentra activa.', 'index.html', 0,
        '2026-06-22 01:46:11'),
       (10, 10, 'SISTEMA', 'Bienvenido al sistema academico', 'Su cuenta se encuentra activa.', 'index.html', 0,
        '2026-06-22 01:46:11'),
       (11, 9, 'PORTAFOLIO', 'Portafolio validado',
        'Su portafolio del curso Inteligencia de negocios fue validado por coordinacion academica.',
        'index.html#portafolio', 0, '2026-06-25 01:41:37'),
       (12, 9, 'PORTAFOLIO', 'Portafolio validado',
        'Su portafolio del curso Inteligencia de negocios fue validado por coordinacion academica.',
        'index.html#portafolio', 0, '2026-06-25 01:42:28'),
       (13, 9, 'PORTAFOLIO', 'Portafolio validado',
        'Su portafolio del curso Inteligencia de negocios fue validado por coordinacion academica.',
        'index.html#portafolio', 0, '2026-06-25 01:44:05'),
       (14, 9, 'PORTAFOLIO', 'Portafolio validado',
        'Su documento SILABO del curso Inteligencia de negocios fue validado por coordinación académica.',
        'index.html#portafolio', 0, '2026-06-25 01:57:03'),
       (15, 9, 'PORTAFOLIO', 'Portafolio validado',
        'Su documento SILABO del curso Inteligencia de negocios fue validado por coordinación académica.',
        'index.html#portafolio', 0, '2026-06-25 02:26:15'),
       (16, 9, 'PORTAFOLIO', 'Portafolio validado',
        'Su documento SILABO del curso Inteligencia de negocios fue validado por coordinación académica.',
        'index.html#portafolio', 0, '2026-06-25 17:59:26'),
       (17, 9, 'PORTAFOLIO', 'Silabo aprobado', 'Tu silabo de Inteligencia de negocios fue aprobado.',
        'index.html#portafolio', 0, '2026-07-02 20:14:39'),
       (18, 9, 'PORTAFOLIO', 'Silabo aprobado', 'Tu silabo de Inteligencia de negocios fue aprobado.',
        'index.html#portafolio', 0, '2026-07-02 20:22:33'),
       (19, 9, 'PORTAFOLIO', 'Silabo observado',
        'Tu silabo de Inteligencia de negocios tiene observaciones. Documento rechazado por observaciones de IA El silabo cumple con la estructura y contenido esperados, con algunas obs...',
        'index.html#portafolio', 0, '2026-07-02 20:43:40'),
       (20, 9, 'PORTAFOLIO', 'Silabo observado',
        'Tu silabo de Inteligencia de negocios tiene observaciones. Documento rechazado por observaciones de IA El documento no puede leerse y no es un silabo valido. Observaciones: - C...',
        'index.html#portafolio', 0, '2026-07-02 20:45:09'),
       (21, 9, 'PORTAFOLIO', 'Silabo observado',
        'Tu silabo de Inteligencia de negocios tiene observaciones. Documento rechazado por observaciones de IA El documento no puede leerse y no es un silabo. Observaciones: - Contenid...',
        'index.html#portafolio', 0, '2026-07-02 20:58:53'),
       (22, 9, 'PORTAFOLIO', 'Silabo aprobado', 'Tu silabo de Inteligencia de negocios fue aprobado.',
        'index.html#portafolio', 0, '2026-07-02 21:02:09'),
       (23, 9, 'PORTAFOLIO', 'Silabo aprobado', 'Tu silabo de Inteligencia de negocios fue aprobado.',
        'index.html#portafolio', 0, '2026-07-02 21:32:33'),
       (24, 9, 'PORTAFOLIO', 'Silabo observado',
        'Tu silabo de Inteligencia de negocios tiene observaciones. Documento rechazado por observaciones de IA El documento no cumple con los requisitos minimos para ser considerado un...',
        'index.html#portafolio', 0, '2026-07-02 22:28:07'),
       (25, 9, 'PORTAFOLIO', 'Silabo observado',
        'Tu silabo de Comunicación oral tiene observaciones. Documento rechazado por observaciones de IA El documento no puede leerse y no se encontraron evidencias claras de las...',
        'index.html#portafolio', 0, '2026-07-02 22:34:50'),
       (26, 9, 'PORTAFOLIO', 'PLAN_SESION observado',
        'Tu plan_sesion de Técnicas de programación tiene observaciones. Documento rechazado por observaciones de IA El archivo no cumple con los requisitos principales para un plan de sesió...',
        'index.html#portafolio', 0, '2026-07-03 00:53:54');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `periodos_academicos`
--

CREATE TABLE `periodos_academicos`
(
    `id_periodo`   int(11) NOT NULL,
    `codigo`       varchar(20) NOT NULL,
    `nombre`       varchar(80) NOT NULL,
    `fecha_inicio` date DEFAULT NULL,
    `fecha_fin`    date DEFAULT NULL,
    `estado`       enum('PLANIFICADO','ACTIVO','CERRADO') NOT NULL DEFAULT 'PLANIFICADO'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `periodos_academicos`
--

INSERT INTO `periodos_academicos` (`id_periodo`, `codigo`, `nombre`, `fecha_inicio`, `fecha_fin`, `estado`)
VALUES (1, '2026-I', 'Semestre Academico 2026-I', '2026-03-16', '2026-07-31', 'ACTIVO'),
       (2, '2026-II', 'Semestre Academico 2026-II', '2026-08-17', '2026-12-18', 'PLANIFICADO'),
       (3, '2026-V', 'Semestre 2026-V', NULL, NULL, 'PLANIFICADO'),
       (4, '2026-III', 'Semestre 2026-III', NULL, NULL, 'PLANIFICADO');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `portafolio_docente`
--

CREATE TABLE `portafolio_docente`
(
    `id_portafolio`       int(11) NOT NULL,
    `id_docente`          int(11) NOT NULL,
    `id_curso`            int(11) NOT NULL,
    `id_periodo`          int(11) NOT NULL,
    `silabo`              enum('PENDIENTE','EN_REVISION','APROBADO','OBSERVADO') NOT NULL DEFAULT 'PENDIENTE',
    `sesiones`            enum('PENDIENTE','EN_REVISION','APROBADO','OBSERVADO') NOT NULL DEFAULT 'PENDIENTE',
    `registro_asistencia` enum('PENDIENTE','EN_REVISION','APROBADO','OBSERVADO') NOT NULL DEFAULT 'PENDIENTE',
    `registro_notas`      enum('PENDIENTE','EN_REVISION','APROBADO','OBSERVADO') NOT NULL DEFAULT 'PENDIENTE',
    `actas`               enum('PENDIENTE','EN_REVISION','APROBADO','OBSERVADO') NOT NULL DEFAULT 'PENDIENTE',
    `estado`              enum('INCOMPLETO','EN_REVISION','COMPLETO','OBSERVADO') NOT NULL DEFAULT 'INCOMPLETO',
    `observacion`         text DEFAULT NULL,
    `fecha_actualizacion` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `portafolio_docente`
--

INSERT INTO `portafolio_docente` (`id_portafolio`, `id_docente`, `id_curso`, `id_periodo`, `silabo`, `sesiones`,
                                  `registro_asistencia`, `registro_notas`, `actas`, `estado`, `observacion`,
                                  `fecha_actualizacion`)
VALUES (1, 4, 15, 1, 'PENDIENTE', 'PENDIENTE', 'PENDIENTE', 'PENDIENTE', 'PENDIENTE', 'INCOMPLETO',
        'Registro inicial generado sin modificar horarios.', NULL),
       (2, 3, 13, 1, 'PENDIENTE', 'PENDIENTE', 'PENDIENTE', 'PENDIENTE', 'PENDIENTE', 'INCOMPLETO',
        'Registro inicial generado sin modificar horarios.', NULL),
       (3, 5, 14, 1, 'PENDIENTE', 'PENDIENTE', 'PENDIENTE', 'PENDIENTE', 'PENDIENTE', 'INCOMPLETO',
        'Registro inicial generado sin modificar horarios.', NULL),
       (4, 2, 16, 1, 'PENDIENTE', 'PENDIENTE', 'PENDIENTE', 'PENDIENTE', 'PENDIENTE', 'INCOMPLETO',
        'Registro inicial generado sin modificar horarios.', NULL),
       (5, 6, 17, 1, 'PENDIENTE', 'PENDIENTE', 'PENDIENTE', 'PENDIENTE', 'PENDIENTE', 'INCOMPLETO',
        'Registro inicial generado sin modificar horarios.', NULL),
       (6, 1, 1, 1, 'PENDIENTE', 'PENDIENTE', 'PENDIENTE', 'PENDIENTE', 'PENDIENTE', 'COMPLETO',
        'IA Groq (llama-3.3-70b-versatile): confianza 0.9%. {\n  \"estado\": \"Aprobado\",\n   \"cursoDetectado\": \"Inteligencia de Negocios\",\n   \"tipoDetectado\": \"Silabo\",\n   \"areaDetectada\": \"Desarrollo de Sistemas de Información\",\n   \"coincideCurso\": true,\n   \"coincideTipo\": true,\n   \"coincideArea\": true,\n   \"estructuraCorrecta\": true,\n   \"contenidoCorrecto\": true,\n   \"observaciones\": [],\n   \"faltantes\": [],\n   \"errores\": [],\n   \"resumen\": \"El contenido del archivo se relaciona con el curso de Inteligencia de Negocios, específicamente con el módulo de Administración de Sistemas de Información. El silabo detalla los objetivos, competencias y contenidos del curso, así como la metodología y evaluación.\",\n   \"textoExtraidoResumen\": \"El archivo contiene información sobre el curso de Inteligencia de Negocios, incluyendo el programa de estudios, módulo formativo, unidad didáctica, créditos académicos, horas totales y período lectivo.\",\n   \"confianza\": 0.9\n}',
        '2026-06-25 02:21:36'),
       (7, 7, 2, 1, 'PENDIENTE', 'PENDIENTE', 'PENDIENTE', 'PENDIENTE', 'PENDIENTE', 'INCOMPLETO',
        'Registro inicial generado sin modificar horarios.', NULL),
       (8, 5, 3, 1, 'PENDIENTE', 'PENDIENTE', 'PENDIENTE', 'PENDIENTE', 'PENDIENTE', 'INCOMPLETO',
        'Registro inicial generado sin modificar horarios.', NULL),
       (9, 6, 4, 1, 'EN_REVISION', 'OBSERVADO', 'PENDIENTE', 'PENDIENTE', 'PENDIENTE', 'OBSERVADO',
        'Documento rechazado por observaciones de IA\nEl archivo no cumple con los requisitos principales para un plan de sesión del curso \'Técnicas de programación\' en el semestre \'2026-I\'. Se necesitan revisiones significativas p...\nObservaciones:\n- El contenido del archivo no parece corresponder al curso \'Técnicas de programación\' o al semestre \'2026-I\'.\n- No se encontraron secciones claras que indiquen el propósito o logro de aprendizaje.\n- La secuencia didáctica de inicio, desarrollo y cierre no está definida.\n- No hay evidencia de actividades, recursos y tiempos específicos para la sesión.\n- No se encontró evidencia o evaluación de la sesión.\nFaltantes:\n- Datos de la sesión\n- Propósito o logro de aprendizaje\n- Secuencia didáctica\n- Actividades, recursos y tiempos\n- Evidencia o evaluación de la sesión',
        '2026-07-03 00:53:54'),
       (10, 2, 5, 1, 'PENDIENTE', 'PENDIENTE', 'PENDIENTE', 'PENDIENTE', 'PENDIENTE', 'INCOMPLETO',
        'Registro inicial generado sin modificar horarios.', NULL),
       (11, 3, 6, 1, 'PENDIENTE', 'PENDIENTE', 'PENDIENTE', 'PENDIENTE', 'PENDIENTE', 'INCOMPLETO',
        'Registro inicial generado sin modificar horarios.', NULL),
       (13, 6, 25, 1, 'PENDIENTE', 'PENDIENTE', 'PENDIENTE', 'PENDIENTE', 'PENDIENTE', 'INCOMPLETO', '',
        '2026-07-03 00:54:54'),
       (36, 6, 10, 2, 'PENDIENTE', 'PENDIENTE', 'PENDIENTE', 'PENDIENTE', 'PENDIENTE', 'INCOMPLETO', '',
        '2026-07-02 22:27:16'),
       (37, 6, 5, 4, 'OBSERVADO', 'PENDIENTE', 'PENDIENTE', 'PENDIENTE', 'PENDIENTE', 'OBSERVADO',
        'Documento rechazado por observaciones de IA\nEl documento no puede leerse y no se encontraron evidencias claras de las secciones obligatorias.\nObservaciones:\n- No se pudo leer el archivo en el servidor: Verificar que el archivo esté disponible y accesible en el servidor\n- No se encontraron evidencias textuales claras en la extracción: Revisar el contenido del documento y verificar que las secciones obligatorias estén presentes y sean claras\nFaltantes:\n- Datos generales\n- Sumilla\n- Competencias y capacidades\n- Programacion por semanas/unidades\n- Metodologia',
        '2026-07-02 22:34:50');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `portafolio_documentos`
--

CREATE TABLE `portafolio_documentos`
(
    `id_documento`  int(11) NOT NULL,
    `id_portafolio` int(11) NOT NULL,
    `tipo`          enum('SILABO','PLAN_SESION','EVALUACION','INSTRUMENTO','ASISTENCIA','NOTAS','EVIDENCIA','ACTA','OTRO') NOT NULL,
    `titulo`        varchar(180) NOT NULL,
    `archivo`       varchar(255) DEFAULT NULL,
    `estado`        enum('PENDIENTE','SUBIDO','APROBADO','OBSERVADO') NOT NULL DEFAULT 'PENDIENTE',
    `observacion`   text         DEFAULT NULL,
    `fecha_subida`  datetime     DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `portafolio_documentos`
--

INSERT INTO `portafolio_documentos` (`id_documento`, `id_portafolio`, `tipo`, `titulo`, `archivo`, `estado`,
                                     `observacion`, `fecha_subida`)
VALUES (53, 37, 'SILABO', 'Silabo - Inglés para la comunicación oral - 20260703 002633 - b66c',
        'uploads/portafolios/portafolio_1783031193_f8fd699a.docx', 'OBSERVADO',
        'Documento rechazado por observaciones de IA\nEl documento no puede leerse y no se encontraron evidencias claras de las secciones obligatorias.\nObservaciones:\n- No se pudo leer el archivo en el servidor: Verificar que el archivo esté disponible y accesible en el servidor\n- No se encontraron evidencias textuales claras en la extracción: Revisar el contenido del documento y verificar que las secciones obligatorias estén presentes y sean claras\nFaltantes:\n- Datos generales\n- Sumilla\n- Competencias y capacidades\n- Programacion por semanas/unidades\n- Metodologia',
        '2026-07-02 17:26:33'),
       (55, 9, 'PLAN_SESION', 'Sesiones de aprendizaje - Técnicas de programación - 20260703 012306 - ddf9',
        'uploads/portafolios/portafolio_1783034586_54d76a0c.pdf', 'OBSERVADO',
        'Documento rechazado por observaciones de IA\nEl archivo no cumple con los requisitos principales para un plan de sesión del curso \'Técnicas de programación\' en el semestre \'2026-I\'. Se necesitan revisiones significativas p...\nObservaciones:\n- El contenido del archivo no parece corresponder al curso \'Técnicas de programación\' o al semestre \'2026-I\'.\n- No se encontraron secciones claras que indiquen el propósito o logro de aprendizaje.\n- La secuencia didáctica de inicio, desarrollo y cierre no está definida.\n- No hay evidencia de actividades, recursos y tiempos específicos para la sesión.\n- No se encontró evidencia o evaluación de la sesión.\nFaltantes:\n- Datos de la sesión\n- Propósito o logro de aprendizaje\n- Secuencia didáctica\n- Actividades, recursos y tiempos\n- Evidencia o evaluación de la sesión',
        '2026-07-02 18:23:06');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `programas_estudio`
--

CREATE TABLE `programas_estudio`
(
    `id_programa`         int(11) NOT NULL,
    `codigo`              varchar(20)  NOT NULL,
    `nombre`              varchar(150) NOT NULL,
    `familia_profesional` varchar(120) DEFAULT NULL,
    `duracion_ciclos`     tinyint(4) NOT NULL DEFAULT 6,
    `estado`              enum('ACTIVO','INACTIVO') NOT NULL DEFAULT 'ACTIVO'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `programas_estudio`
--

INSERT INTO `programas_estudio` (`id_programa`, `codigo`, `nombre`, `familia_profesional`, `duracion_ciclos`, `estado`)
VALUES (1, 'DSI', 'Desarrollo de Sistemas de Informacion', 'Computacion e Informatica', 6, 'ACTIVO'),
       (2, 'AGRO', 'Produccion Agropecuaria', 'Actividades Agrarias', 6, 'ACTIVO'),
       (3, 'ENF', 'Enfermeria Tecnica', 'Salud', 6, 'ACTIVO'),
       (4, 'CON', 'Construccion Civil', 'Construccion', 6, 'ACTIVO'),
       (5, 'CTB', 'Contabilidad', 'Administracion y Comercio', 6, 'ACTIVO');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reportes_generados`
--

CREATE TABLE `reportes_generados`
(
    `id_reporte`       int(11) NOT NULL,
    `id_usuario`       int(11) DEFAULT NULL,
    `tipo`             enum('CURSOS','DOCENTES','ESTUDIANTES','HORARIOS','NOTAS','PORTAFOLIO','CONSOLIDADO','IA_PREDICTIVA') NOT NULL,
    `titulo`           varchar(180) NOT NULL,
    `formato`          enum('PDF','EXCEL','CSV') NOT NULL DEFAULT 'PDF',
    `filtros_json`     longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`filtros_json`)),
    `archivo`          varchar(255)          DEFAULT NULL,
    `fecha_generacion` timestamp    NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles`
(
    `id_rol`         int(11) NOT NULL,
    `codigo`         varchar(30) NOT NULL,
    `nombre`         varchar(80) NOT NULL,
    `descripcion`    varchar(255)         DEFAULT NULL,
    `estado`         enum('ACTIVO','INACTIVO') NOT NULL DEFAULT 'ACTIVO',
    `fecha_creacion` timestamp   NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id_rol`, `codigo`, `nombre`, `descripcion`, `estado`, `fecha_creacion`)
VALUES (1, 'director', 'Director Academico', 'Acceso global a gestion academica', 'ACTIVO', '2026-06-22 01:46:11'),
       (2, 'jua', 'Jefe de Unidad Academica', 'Revision y aprobacion academica', 'ACTIVO', '2026-06-22 01:46:11'),
       (3, 'coordinador', 'Coordinador Academico', 'Gestion de cursos, docentes y seguimiento', 'ACTIVO',
        '2026-06-22 01:46:11'),
       (4, 'docente', 'Docente', 'Registro de notas, asistencia y portafolio', 'ACTIVO', '2026-06-22 01:46:11');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sesiones_aprendizaje`
--

CREATE TABLE `sesiones_aprendizaje`
(
    `id_sesion`     int(11) NOT NULL,
    `id_curso`      int(11) NOT NULL,
    `id_docente`    int(11) NOT NULL,
    `titulo`        varchar(255) NOT NULL,
    `archivo`       varchar(255) NOT NULL,
    `numero_sesion` int(11) DEFAULT NULL,
    `estado`        enum('PENDIENTE','EN_REVISION','APROBADO','RECHAZADO') DEFAULT 'PENDIENTE',
    `fecha_subida`  datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `sesiones_aprendizaje`
--

INSERT INTO `sesiones_aprendizaje` (`id_sesion`, `id_curso`, `id_docente`, `titulo`, `archivo`, `numero_sesion`,
                                    `estado`, `fecha_subida`)
VALUES (2, 1, 1, 'Sesion 3 - Condicionales', 'uploads/sesiones/sesion_1783034088_c6e12fb2.pdf', 3, 'PENDIENTE',
        '2026-07-02 18:14:48'),
       (3, 1, 1, 'Sesion 1 - Introduccion', 'uploads/sesiones/sesion_1783034088_06352600.pdf', 1, 'PENDIENTE',
        '2026-07-02 18:14:48'),
       (4, 1, 1, 'Sesion 2 - Variables', 'uploads/sesiones/sesion_1783034088_8ee494ae.pdf', 2, 'PENDIENTE',
        '2026-07-02 18:14:48'),
       (5, 1, 1, 'Sesion sin numero', 'uploads/sesiones/sesion_1783034088_3c0af572.pdf', NULL, 'PENDIENTE',
        '2026-07-02 18:14:48'),
       (6, 1, 1, 'Sesion extra sin num', 'uploads/sesiones/sesion_1783034088_f534cd3e.pdf', NULL, 'PENDIENTE',
        '2026-07-02 18:14:48'),
       (7, 5, 2, 'Sesion 1 - Comunicacion oral', 'uploads/sesiones/sesion_1783034088_fa784d01.pdf', 1, 'PENDIENTE',
        '2026-07-02 18:14:48');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `silabo_estructuras`
--

CREATE TABLE `silabo_estructuras`
(
    `id_estructura`       int(11) NOT NULL,
    `codigo`              varchar(50)  NOT NULL,
    `nombre`              varchar(180) NOT NULL,
    `version`             varchar(30)  NOT NULL,
    `descripcion`         text                  DEFAULT NULL,
    `activo`              tinyint(1) NOT NULL DEFAULT 1,
    `fecha_creacion`      timestamp    NOT NULL DEFAULT current_timestamp(),
    `fecha_actualizacion` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `silabo_estructuras`
--

INSERT INTO `silabo_estructuras` (`id_estructura`, `codigo`, `nombre`, `version`, `descripcion`, `activo`,
                                  `fecha_creacion`, `fecha_actualizacion`)
VALUES (1, 'SILABO_ISTV_2026', 'Estructura institucional de silabo ISTV Vilcanota', '2026.1',
        'Rubrica oficial para validar silabos subidos al portafolio docente.', 1, '2026-07-02 20:00:58', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `silabo_estructura_criterios`
--

CREATE TABLE `silabo_estructura_criterios`
(
    `id_criterio`         int(11) NOT NULL,
    `id_estructura`       int(11) NOT NULL,
    `orden`               tinyint(4) NOT NULL,
    `seccion`             varchar(120)  NOT NULL,
    `descripcion`         text          NOT NULL,
    `campos_json`         text          NOT NULL,
    `validaciones_json`   text          NOT NULL,
    `peso`                decimal(5, 2) NOT NULL DEFAULT 0.00,
    `obligatorio`         tinyint(1) NOT NULL DEFAULT 1,
    `fecha_creacion`      timestamp     NOT NULL DEFAULT current_timestamp(),
    `fecha_actualizacion` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `silabo_estructura_criterios`
--

INSERT INTO `silabo_estructura_criterios` (`id_criterio`, `id_estructura`, `orden`, `seccion`, `descripcion`,
                                           `campos_json`, `validaciones_json`, `peso`, `obligatorio`, `fecha_creacion`,
                                           `fecha_actualizacion`)
VALUES (1, 1, 1, 'Datos Generales', 'Debe identificar el curso, docente, periodo y datos academicos basicos.',
        '[\"Programa de estudios\",\"Modulo formativo\",\"Unidad didactica\",\"Creditos\",\"Horas totales\",\"Horas semanales\",\"Periodo lectivo\",\"Periodo academico\",\"Fecha de inicio y termino\",\"Turno\",\"Docente\",\"Correo institucional\",\"Aula virtual\"]',
        '[\"No debe estar vacio\",\"Los datos deben corresponder al curso esperado\",\"El docente y curso deben poder identificarse\"]',
        15.00, 1, '2026-07-02 20:00:58', NULL),
       (2, 1, 2, 'Sumilla', 'Debe contener una descripcion del curso vinculada a la unidad didactica.',
        '[\"Descripcion del curso\",\"Relacion con la unidad didactica\"]',
        '[\"Debe corresponder al nombre de la unidad didactica\",\"No debe ser texto generico de otro curso\"]', 10.00,
        1, '2026-07-02 20:00:58', NULL),
       (3, 1, 3, 'Unidad de Competencia', 'Debe declarar la competencia especifica del modulo.',
        '[\"Competencia especifica del modulo\"]', '[\"La competencia debe existir y guardar relacion con el curso\"]',
        10.00, 1, '2026-07-02 20:00:58', NULL),
       (4, 1, 4, 'Capacidades e Indicadores', 'Debe listar capacidades e indicadores de logro.',
        '[\"Capacidades\",\"Indicadores de logro\"]',
        '[\"Debe existir al menos una capacidad\",\"Debe existir al menos un indicador\",\"Los indicadores deben relacionarse con las capacidades\"]',
        12.00, 1, '2026-07-02 20:00:58', NULL),
       (5, 1, 5, 'Competencias para la empleabilidad', 'Debe incluir competencias transversales.',
        '[\"Comunicacion\",\"Etica\",\"Emprendimiento\"]',
        '[\"Debe mencionar competencias transversales pertinentes\"]', 8.00, 1, '2026-07-02 20:00:58', NULL),
       (6, 1, 6, 'Programacion de sesiones', 'Cada sesion debe tener datos minimos de programacion y evaluacion.',
        '[\"Semana\",\"Numero de sesion\",\"Contenido\",\"Logro de aprendizaje\",\"Instrumento de evaluacion\"]',
        '[\"Las sesiones deben relacionarse con las capacidades\",\"No debe haber sesiones vacias\",\"Debe existir instrumento de evaluacion por sesion o bloque evaluativo\"]',
        18.00, 1, '2026-07-02 20:00:58', NULL),
       (7, 1, 7, 'Metodologia', 'Debe describir la metodologia de ensenanza.', '[\"Metodologia de ensenanza\"]',
        '[\"Debe describir como se desarrollara el curso\"]', 7.00, 1, '2026-07-02 20:00:58', NULL),
       (8, 1, 8, 'Ambientes y Recursos', 'Debe indicar ambientes y recursos utilizados.',
        '[\"Ambientes\",\"Recursos\",\"Medios y materiales\"]',
        '[\"Debe contener recursos concretos y relacionados al curso\"]', 6.00, 1, '2026-07-02 20:00:58', NULL),
       (9, 1, 9, 'Sistema de Evaluacion', 'Debe explicar como se evaluara el curso.',
        '[\"Sistema de evaluacion\",\"Criterios\",\"Instrumentos\",\"Condiciones de aprobacion\"]',
        '[\"Debe describir el sistema de evaluacion\",\"Debe ser coherente con las sesiones e indicadores\"]', 8.00, 1,
        '2026-07-02 20:00:58', NULL),
       (10, 1, 10, 'Bibliografia', 'Debe incluir bibliografia y referencias web relacionadas al curso.',
        '[\"Bibliografia\",\"Referencias web\"]',
        '[\"No debe estar vacia\",\"Debe relacionarse con el curso indicado\"]', 6.00, 1, '2026-07-02 20:00:58', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios`
(
    `id_usuario`          int(11) NOT NULL,
    `id_rol`              int(11) NOT NULL,
    `id_docente`          int(11) DEFAULT NULL,
    `usuario`             varchar(80)  NOT NULL,
    `correo`              varchar(150) NOT NULL,
    `password_hash`       varchar(255) NOT NULL,
    `password_algoritmo`  varchar(40)  NOT NULL DEFAULT 'sha256-demo',
    `nombres`             varchar(120) NOT NULL,
    `apellidos`           varchar(120)          DEFAULT NULL,
    `dni`                 char(8)               DEFAULT NULL,
    `telefono`            varchar(20)           DEFAULT NULL,
    `estado`              enum('ACTIVO','INACTIVO','BLOQUEADO') NOT NULL DEFAULT 'ACTIVO',
    `ultimo_acceso`       datetime              DEFAULT NULL,
    `fecha_creacion`      timestamp    NOT NULL DEFAULT current_timestamp(),
    `fecha_actualizacion` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `id_rol`, `id_docente`, `usuario`, `correo`, `password_hash`,
                        `password_algoritmo`, `nombres`, `apellidos`, `dni`, `telefono`, `estado`, `ultimo_acceso`,
                        `fecha_creacion`, `fecha_actualizacion`)
VALUES (1, 1, NULL, 'director', 'director@istv.edu.pe',
        '5994471abb01112afcc18159f6cc74b4f511b99806da59b3caf5a9c173cacfc5', 'sha256-demo', 'Director', 'Academico',
        '00000000', NULL, 'ACTIVO', NULL, '2026-06-22 01:46:11', NULL),
       (2, 2, NULL, 'jua', 'jua@istv.edu.pe', '5994471abb01112afcc18159f6cc74b4f511b99806da59b3caf5a9c173cacfc5',
        'sha256-demo', 'JUA', 'Academico', '00000001', NULL, 'ACTIVO', NULL, '2026-06-22 01:46:11', NULL),
       (3, 3, NULL, 'coordinador', 'coordinador@istv.edu.pe',
        '5994471abb01112afcc18159f6cc74b4f511b99806da59b3caf5a9c173cacfc5', 'sha256-demo', 'Coordinador', 'Academico',
        '00000002', NULL, 'ACTIVO', NULL, '2026-06-22 01:46:11', NULL),
       (4, 4, 1, 'd.huaylla', 'd.huaylla@istv.edu.pe',
        '85d49a0c207f8bba5fc713ba6a01d091e732bce604891ce8548f7a77e8d92a43', 'sha256-demo', 'Diana', 'Huaylla',
        '00000001', NULL, 'ACTIVO', NULL, '2026-06-22 01:46:11', NULL),
       (5, 4, 2, 'j.barrientos', 'j.barrientos@istv.edu.pe',
        '5c55b95deca65d2dbf3a9d9a0d45a106e918650ae8fe74c2a61d774434ac1c9c', 'sha256-demo', 'Jhon', 'Barrientos Ferro',
        '00000002', NULL, 'ACTIVO', NULL, '2026-06-22 01:46:11', NULL),
       (6, 4, 3, 'h.palomino', 'h.palomino@istv.edu.pe',
        '8f3e96dfb477b08d253c289857518b34290e9b72d7b56ffefa6ddb533afe5749', 'sha256-demo', 'Hernan', 'Palomino',
        '00000003', NULL, 'ACTIVO', NULL, '2026-06-22 01:46:11', NULL),
       (7, 4, 4, 'r.jara', 'r.jara@istv.edu.pe', '7f12147648a7c4566b8b48e86058dcc74aeee4bc3c7b3cc9b8934a08b86345bb',
        'sha256-demo', 'Rosa Luz', 'Jara', '00000004', NULL, 'ACTIVO', NULL, '2026-06-22 01:46:11', NULL),
       (8, 4, 5, 'f.quispe', 'f.quispe@istv.edu.pe', 'e95a5197ffb80c4edd24bf5c26a64b7f8694742ef6b22b1d99e1fd6717a9125f',
        'sha256-demo', 'Fredy', 'Quispe', '00000005', NULL, 'ACTIVO', NULL, '2026-06-22 01:46:11', NULL),
       (9, 4, 6, 'p.lech', 'p.lech@istv.edu.pe', 'f22427ebfec7380121317f4be1a9076718bc405532e526dc53de2ea803e9c0d5',
        'sha256-demo', 'Pavel', 'Lech', '00000006', NULL, 'ACTIVO', NULL, '2026-06-22 01:46:11', NULL),
       (10, 4, 7, 'f.cornejo', 'f.cornejo@istv.edu.pe',
        '416a134e76e08e3bc14fdcd0755796a71371c1426f33868621cb083257790e07', 'sha256-demo', 'Fernando', 'Cornejo',
        '00000007', NULL, 'ACTIVO', NULL, '2026-06-22 01:46:11', NULL);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `alertas_academicas`
--
ALTER TABLE `alertas_academicas`
    ADD PRIMARY KEY (`id_alerta`),
  ADD UNIQUE KEY `uk_alertas_contexto` (`id_estudiante`,`tipo`,`titulo`),
  ADD KEY `idx_alertas_estudiante` (`id_estudiante`),
  ADD KEY `idx_alertas_docente` (`id_docente`),
  ADD KEY `idx_alertas_curso` (`id_curso`),
  ADD KEY `idx_alertas_estado` (`estado`);

--
-- Indices de la tabla `asistencia_detalle`
--
ALTER TABLE `asistencia_detalle`
    ADD PRIMARY KEY (`id_asistencia`),
  ADD UNIQUE KEY `uk_asistencia_sesion_estudiante` (`id_sesion`,`id_estudiante`),
  ADD KEY `idx_asistencia_detalle_estudiante` (`id_estudiante`);

--
-- Indices de la tabla `asistencia_sesiones`
--
ALTER TABLE `asistencia_sesiones`
    ADD PRIMARY KEY (`id_sesion`),
  ADD KEY `idx_asistencia_sesiones_curso` (`id_curso`),
  ADD KEY `idx_asistencia_sesiones_docente` (`id_docente`),
  ADD KEY `idx_asistencia_sesiones_horario` (`id_horario`),
  ADD KEY `idx_asistencia_sesiones_periodo` (`id_periodo`);

--
-- Indices de la tabla `auditoria_sistema`
--
ALTER TABLE `auditoria_sistema`
    ADD PRIMARY KEY (`id_auditoria`),
  ADD KEY `idx_auditoria_usuario` (`id_usuario`),
  ADD KEY `idx_auditoria_modulo` (`modulo`),
  ADD KEY `idx_auditoria_fecha` (`fecha_evento`);

--
-- Indices de la tabla `aulas`
--
ALTER TABLE `aulas`
    ADD PRIMARY KEY (`id_aula`),
  ADD UNIQUE KEY `uk_aulas_codigo` (`codigo`);

--
-- Indices de la tabla `configuracion_sistema`
--
ALTER TABLE `configuracion_sistema`
    ADD PRIMARY KEY (`id_configuracion`),
  ADD UNIQUE KEY `uk_configuracion_clave` (`clave`);

--
-- Indices de la tabla `cursos`
--
ALTER TABLE `cursos`
    ADD PRIMARY KEY (`id_curso`),
  ADD KEY `idx_cursos_docente` (`id_docente`);

--
-- Indices de la tabla `docentes`
--
ALTER TABLE `docentes`
    ADD PRIMARY KEY (`id_docente`),
  ADD UNIQUE KEY `codigo_docente` (`codigo_docente`),
  ADD UNIQUE KEY `dni` (`dni`);

--
-- Indices de la tabla `estudiantes`
--
ALTER TABLE `estudiantes`
    ADD PRIMARY KEY (`id_estudiante`),
  ADD UNIQUE KEY `uk_estudiantes_codigo` (`codigo_estudiante`),
  ADD UNIQUE KEY `uk_estudiantes_dni` (`dni`),
  ADD KEY `idx_estudiantes_programa` (`id_programa`);

--
-- Indices de la tabla `horarios`
--
ALTER TABLE `horarios`
    ADD PRIMARY KEY (`id_horario`),
  ADD KEY `id_curso` (`id_curso`),
  ADD KEY `id_docente` (`id_docente`);

--
-- Indices de la tabla `horarios_ia_generados`
--
ALTER TABLE `horarios_ia_generados`
    ADD PRIMARY KEY (`id_generacion`),
  ADD KEY `idx_horarios_ia_usuario` (`id_usuario`),
  ADD KEY `idx_horarios_ia_periodo` (`id_periodo`);

--
-- Indices de la tabla `ia_predicciones`
--
ALTER TABLE `ia_predicciones`
    ADD PRIMARY KEY (`id_prediccion`),
  ADD KEY `idx_ia_pred_estudiante` (`id_estudiante`),
  ADD KEY `idx_ia_pred_curso` (`id_curso`),
  ADD KEY `idx_ia_pred_periodo` (`id_periodo`),
  ADD KEY `idx_ia_pred_nivel` (`nivel`);

--
-- Indices de la tabla `matriculas`
--
ALTER TABLE `matriculas`
    ADD PRIMARY KEY (`id_matricula`),
  ADD UNIQUE KEY `uk_matriculas_estudiante_periodo` (`id_estudiante`,`id_periodo`),
  ADD KEY `idx_matriculas_periodo` (`id_periodo`);

--
-- Indices de la tabla `matricula_cursos`
--
ALTER TABLE `matricula_cursos`
    ADD PRIMARY KEY (`id_matricula_curso`),
  ADD UNIQUE KEY `uk_matricula_curso` (`id_matricula`,`id_curso`),
  ADD KEY `idx_matricula_cursos_curso` (`id_curso`);

--
-- Indices de la tabla `mensajes`
--
ALTER TABLE `mensajes`
    ADD PRIMARY KEY (`id_mensaje`),
  ADD KEY `idx_mensajes_remitente` (`id_remitente`),
  ADD KEY `idx_mensajes_destinatario` (`id_destinatario`);

--
-- Indices de la tabla `notas`
--
ALTER TABLE `notas`
    ADD PRIMARY KEY (`id_nota`),
  ADD UNIQUE KEY `uk_notas_matricula_unidad` (`id_matricula_curso`,`unidad`);

--
-- Indices de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
    ADD PRIMARY KEY (`id_notificacion`),
  ADD KEY `idx_notificaciones_usuario` (`id_usuario`),
  ADD KEY `idx_notificaciones_tipo` (`tipo`);

--
-- Indices de la tabla `periodos_academicos`
--
ALTER TABLE `periodos_academicos`
    ADD PRIMARY KEY (`id_periodo`),
  ADD UNIQUE KEY `uk_periodos_codigo` (`codigo`);

--
-- Indices de la tabla `portafolio_docente`
--
ALTER TABLE `portafolio_docente`
    ADD PRIMARY KEY (`id_portafolio`),
  ADD UNIQUE KEY `uk_portafolio_docente_curso_periodo` (`id_docente`,`id_curso`,`id_periodo`),
  ADD KEY `idx_portafolio_docente` (`id_docente`),
  ADD KEY `idx_portafolio_curso` (`id_curso`),
  ADD KEY `idx_portafolio_periodo` (`id_periodo`);

--
-- Indices de la tabla `portafolio_documentos`
--
ALTER TABLE `portafolio_documentos`
    ADD PRIMARY KEY (`id_documento`),
  ADD UNIQUE KEY `uk_portafolio_documento_tipo` (`id_portafolio`,`tipo`,`titulo`),
  ADD KEY `idx_portafolio_documentos_portafolio` (`id_portafolio`);

--
-- Indices de la tabla `programas_estudio`
--
ALTER TABLE `programas_estudio`
    ADD PRIMARY KEY (`id_programa`),
  ADD UNIQUE KEY `uk_programas_codigo` (`codigo`);

--
-- Indices de la tabla `reportes_generados`
--
ALTER TABLE `reportes_generados`
    ADD PRIMARY KEY (`id_reporte`),
  ADD KEY `idx_reportes_usuario` (`id_usuario`),
  ADD KEY `idx_reportes_tipo` (`tipo`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
    ADD PRIMARY KEY (`id_rol`),
  ADD UNIQUE KEY `uk_roles_codigo` (`codigo`);

--
-- Indices de la tabla `sesiones_aprendizaje`
--
ALTER TABLE `sesiones_aprendizaje`
    ADD PRIMARY KEY (`id_sesion`),
  ADD KEY `idx_sa_curso` (`id_curso`),
  ADD KEY `idx_sa_docente` (`id_docente`);

--
-- Indices de la tabla `silabo_estructuras`
--
ALTER TABLE `silabo_estructuras`
    ADD PRIMARY KEY (`id_estructura`),
  ADD UNIQUE KEY `uk_silabo_estructuras_codigo` (`codigo`);

--
-- Indices de la tabla `silabo_estructura_criterios`
--
ALTER TABLE `silabo_estructura_criterios`
    ADD PRIMARY KEY (`id_criterio`),
  ADD UNIQUE KEY `uk_silabo_criterio_seccion` (`id_estructura`,`seccion`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
    ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `uk_usuarios_usuario` (`usuario`),
  ADD UNIQUE KEY `uk_usuarios_correo` (`correo`),
  ADD KEY `idx_usuarios_rol` (`id_rol`),
  ADD KEY `idx_usuarios_docente` (`id_docente`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `alertas_academicas`
--
ALTER TABLE `alertas_academicas`
    MODIFY `id_alerta` int (11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `asistencia_detalle`
--
ALTER TABLE `asistencia_detalle`
    MODIFY `id_asistencia` int (11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `asistencia_sesiones`
--
ALTER TABLE `asistencia_sesiones`
    MODIFY `id_sesion` int (11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `auditoria_sistema`
--
ALTER TABLE `auditoria_sistema`
    MODIFY `id_auditoria` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `aulas`
--
ALTER TABLE `aulas`
    MODIFY `id_aula` int (11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `configuracion_sistema`
--
ALTER TABLE `configuracion_sistema`
    MODIFY `id_configuracion` int (11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `cursos`
--
ALTER TABLE `cursos`
    MODIFY `id_curso` int (11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT de la tabla `docentes`
--
ALTER TABLE `docentes`
    MODIFY `id_docente` int (11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `estudiantes`
--
ALTER TABLE `estudiantes`
    MODIFY `id_estudiante` int (11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `horarios`
--
ALTER TABLE `horarios`
    MODIFY `id_horario` int (11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT de la tabla `horarios_ia_generados`
--
ALTER TABLE `horarios_ia_generados`
    MODIFY `id_generacion` int (11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `ia_predicciones`
--
ALTER TABLE `ia_predicciones`
    MODIFY `id_prediccion` int (11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `matriculas`
--
ALTER TABLE `matriculas`
    MODIFY `id_matricula` int (11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `matricula_cursos`
--
ALTER TABLE `matricula_cursos`
    MODIFY `id_matricula_curso` int (11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT de la tabla `mensajes`
--
ALTER TABLE `mensajes`
    MODIFY `id_mensaje` int (11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `notas`
--
ALTER TABLE `notas`
    MODIFY `id_nota` int (11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
    MODIFY `id_notificacion` int (11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT de la tabla `periodos_academicos`
--
ALTER TABLE `periodos_academicos`
    MODIFY `id_periodo` int (11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `portafolio_docente`
--
ALTER TABLE `portafolio_docente`
    MODIFY `id_portafolio` int (11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT de la tabla `portafolio_documentos`
--
ALTER TABLE `portafolio_documentos`
    MODIFY `id_documento` int (11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT de la tabla `programas_estudio`
--
ALTER TABLE `programas_estudio`
    MODIFY `id_programa` int (11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `reportes_generados`
--
ALTER TABLE `reportes_generados`
    MODIFY `id_reporte` int (11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
    MODIFY `id_rol` int (11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `sesiones_aprendizaje`
--
ALTER TABLE `sesiones_aprendizaje`
    MODIFY `id_sesion` int (11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `silabo_estructuras`
--
ALTER TABLE `silabo_estructuras`
    MODIFY `id_estructura` int (11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de la tabla `silabo_estructura_criterios`
--
ALTER TABLE `silabo_estructura_criterios`
    MODIFY `id_criterio` int (11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=211;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
    MODIFY `id_usuario` int (11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `cursos`
--
ALTER TABLE `cursos`
    ADD CONSTRAINT `fk_cursos_docentes` FOREIGN KEY (`id_docente`) REFERENCES `docentes` (`id_docente`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `horarios`
--
ALTER TABLE `horarios`
    ADD CONSTRAINT `horarios_ibfk_1` FOREIGN KEY (`id_curso`) REFERENCES `cursos` (`id_curso`),
  ADD CONSTRAINT `horarios_ibfk_2` FOREIGN KEY (`id_docente`) REFERENCES `docentes` (`id_docente`);

--
-- Filtros para la tabla `sesiones_aprendizaje`
--
ALTER TABLE `sesiones_aprendizaje`
    ADD CONSTRAINT `fk_sa_curso` FOREIGN KEY (`id_curso`) REFERENCES `cursos` (`id_curso`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_sa_docente` FOREIGN KEY (`id_docente`) REFERENCES `docentes` (`id_docente`) ON
DELETE
CASCADE ON
UPDATE CASCADE;

--
-- Filtros para la tabla `silabo_estructura_criterios`
--
ALTER TABLE `silabo_estructura_criterios`
    ADD CONSTRAINT `fk_silabo_criterios_estructura` FOREIGN KEY (`id_estructura`) REFERENCES `silabo_estructuras` (`id_estructura`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
