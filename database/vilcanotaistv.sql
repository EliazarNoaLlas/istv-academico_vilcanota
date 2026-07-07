-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 06-07-2026 a las 22:08:17
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT = @@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS = @@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION = @@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `istv_academico_vilcanota`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `alertas_academicas`
--

CREATE TABLE `alertas_academicas`
(
    `id_alerta`      int(10) UNSIGNED                                                          NOT NULL,
    `id_estudiante`  int(10) UNSIGNED                                                                   DEFAULT NULL,
    `id_docente`     int(10) UNSIGNED                                                                   DEFAULT NULL,
    `id_curso`       int(10) UNSIGNED                                                                   DEFAULT NULL,
    `tipo`           enum ('RIESGO_ACADEMICO','INASISTENCIA','PORTAFOLIO','HORARIO','SISTEMA') NOT NULL,
    `severidad`      enum ('BAJA','MEDIA','ALTA','CRITICA')                                    NOT NULL DEFAULT 'MEDIA',
    `titulo`         varchar(150)                                                              NOT NULL,
    `detalle`        text                                                                               DEFAULT NULL,
    `estado`         enum ('ABIERTA','EN_SEGUIMIENTO','CERRADA')                               NOT NULL DEFAULT 'ABIERTA',
    `fecha_creacion` timestamp                                                                 NOT NULL DEFAULT current_timestamp(),
    `fecha_cierre`   datetime                                                                           DEFAULT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asistencia_detalle`
--

CREATE TABLE `asistencia_detalle`
(
    `id_asistencia`  int(10) UNSIGNED                                     NOT NULL,
    `id_sesion`      int(10) UNSIGNED                                     NOT NULL,
    `id_estudiante`  int(10) UNSIGNED                                     NOT NULL,
    `estado`         enum ('PRESENTE','TARDANZA','AUSENTE','JUSTIFICADO') NOT NULL DEFAULT 'PRESENTE',
    `observacion`    varchar(255)                                                  DEFAULT NULL,
    `fecha_registro` timestamp                                            NOT NULL DEFAULT current_timestamp()
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asistencia_sesiones`
--

CREATE TABLE `asistencia_sesiones`
(
    `id_sesion`    int(10) UNSIGNED                             NOT NULL,
    `id_curso`     int(10) UNSIGNED                             NOT NULL,
    `id_docente`   int(10) UNSIGNED                             NOT NULL,
    `id_horario`   int(10) UNSIGNED                                      DEFAULT NULL,
    `id_periodo`   int(10) UNSIGNED                             NOT NULL,
    `fecha_sesion` date                                         NOT NULL,
    `hora_inicio`  time                                                  DEFAULT NULL,
    `hora_fin`     time                                                  DEFAULT NULL,
    `tema`         varchar(180)                                          DEFAULT NULL,
    `estado`       enum ('PROGRAMADA','REALIZADA','SUSPENDIDA') NOT NULL DEFAULT 'PROGRAMADA'
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `auditoria_sistema`
--

CREATE TABLE `auditoria_sistema`
(
    `id_auditoria`   bigint(20) UNSIGNED NOT NULL,
    `id_usuario`     int(10) UNSIGNED             DEFAULT NULL,
    `accion`         varchar(80)         NOT NULL,
    `tabla_afectada` varchar(80)                  DEFAULT NULL,
    `registro_id`    varchar(80)                  DEFAULT NULL,
    `detalle`        text                         DEFAULT NULL,
    `ip`             varchar(45)                  DEFAULT NULL,
    `fecha_accion`   timestamp           NOT NULL DEFAULT current_timestamp()
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `auditoria_sistema`
--

INSERT INTO `auditoria_sistema` (`id_auditoria`, `id_usuario`, `accion`, `tabla_afectada`, `registro_id`, `detalle`,
                                 `ip`, `fecha_accion`)
VALUES (1, 1, 'USUARIO_CREADO', 'usuarios', '4', 'Cuenta creada con rol 4', NULL, '2026-07-06 14:58:23'),
       (2, 1, 'USUARIO_CREADO', 'usuarios', '5', 'Cuenta creada con rol 4', NULL, '2026-07-06 15:06:37'),
       (3, 1, 'USUARIO_CREADO', 'usuarios', '6', 'Cuenta creada con rol 4', NULL, '2026-07-06 15:09:52'),
       (4, 1, 'USUARIO_CREADO', 'usuarios', '7', 'Cuenta creada con rol 4', NULL, '2026-07-06 15:16:42'),
       (5, 1, 'USUARIO_CREADO', 'usuarios', '8', 'Cuenta creada con rol 4', NULL, '2026-07-06 15:18:51'),
       (6, 1, 'USUARIO_CREADO', 'usuarios', '9', 'Cuenta creada con rol 4', NULL, '2026-07-06 15:23:25'),
       (7, 1, 'USUARIO_CREADO', 'usuarios', '10', 'Cuenta creada con rol 3', NULL, '2026-07-06 15:26:16'),
       (8, 1, 'USUARIO_ACTUALIZADO', 'usuarios', '6', 'Datos de la cuenta actualizados', NULL, '2026-07-06 16:56:27'),
       (9, 1, 'USUARIO_ELIMINADO', 'usuarios', '11', 'Cuenta eliminada por Dirección', NULL, '2026-07-06 17:06:09'),
       (10, 1, 'USUARIO_ACTUALIZADO', 'usuarios', '2', 'Datos de la cuenta actualizados', NULL, '2026-07-06 17:09:21'),
       (11, 1, 'PASSWORD_RESTABLECIDA', 'usuarios', '2', 'Contraseña restablecida por Dirección', NULL,
        '2026-07-06 17:11:59'),
       (12, 1, 'SOLICITUD_PASSWORD_APROBADA', 'solicitudes_password', '1', 'Solicitud de maria aprobada', NULL,
        '2026-07-06 17:12:04'),
       (13, 2, 'PASSWORD_CAMBIADA_POR_USUARIO', 'usuarios', '2',
        'El usuario completó el cambio de contraseña obligatorio', NULL, '2026-07-06 17:13:36'),
       (14, 10, 'PASSWORD_CAMBIADA_POR_USUARIO', 'usuarios', '10',
        'El usuario completó el cambio de contraseña obligatorio', NULL, '2026-07-06 17:16:24'),
       (15, 8, 'PASSWORD_CAMBIADA_POR_USUARIO', 'usuarios', '8',
        'El usuario completó el cambio de contraseña obligatorio', NULL, '2026-07-06 17:43:37'),
       (16, 1, 'USUARIO_ELIMINADO', 'usuarios', '7', 'Cuenta eliminada por Dirección', NULL, '2026-07-06 18:41:32'),
       (17, 1, 'USUARIO_CREADO', 'usuarios', '14', 'Cuenta creada con rol 4', NULL, '2026-07-06 18:45:29'),
       (18, 1, 'USUARIO_CREADO', 'usuarios', '15', 'Cuenta creada con rol 4', NULL, '2026-07-06 19:02:11'),
       (19, 1, 'USUARIO_CREADO', 'usuarios', '16', 'Cuenta creada con rol 4', NULL, '2026-07-06 19:05:20'),
       (20, 1, 'USUARIO_ACTUALIZADO', 'usuarios', '10', 'Datos de la cuenta actualizados', NULL, '2026-07-06 19:36:49');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `aulas`
--

CREATE TABLE `aulas`
(
    `id_aula`   int(10) UNSIGNED                                    NOT NULL,
    `codigo`    varchar(30)                                         NOT NULL,
    `nombre`    varchar(100)                                        NOT NULL,
    `tipo`      enum ('AULA','LABORATORIO','TALLER','CAMPO','OTRO') NOT NULL DEFAULT 'AULA',
    `capacidad` int(11)                                             NOT NULL DEFAULT 30,
    `ubicacion` varchar(120)                                                 DEFAULT NULL,
    `estado`    enum ('DISPONIBLE','MANTENIMIENTO','INACTIVO')      NOT NULL DEFAULT 'DISPONIBLE'
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

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
-- Estructura de tabla para la tabla `cache`
--

CREATE TABLE `cache`
(
    `key`        varchar(255) NOT NULL,
    `value`      mediumtext   NOT NULL,
    `expiration` int(11)      NOT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`)
VALUES ('laravel-cache-5c785c036466adea360111aa28563bfd556b5fba', 'i:3;', 1783362597),
       ('laravel-cache-5c785c036466adea360111aa28563bfd556b5fba:timer', 'i:1783362597;', 1783362597);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cache_locks`
--

CREATE TABLE `cache_locks`
(
    `key`        varchar(255) NOT NULL,
    `owner`      varchar(255) NOT NULL,
    `expiration` int(11)      NOT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuracion_sistema`
--

CREATE TABLE `configuracion_sistema`
(
    `id_configuracion`    int(10) UNSIGNED NOT NULL,
    `clave`               varchar(80)      NOT NULL,
    `valor`               text             NOT NULL,
    `descripcion`         varchar(255)          DEFAULT NULL,
    `fecha_actualizacion` timestamp        NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `configuracion_sistema`
--

INSERT INTO `configuracion_sistema` (`id_configuracion`, `clave`, `valor`, `descripcion`, `fecha_actualizacion`)
VALUES (1, 'nota_minima_aprobatoria', '10.5', 'Nota minima para aprobar una unidad didactica', NULL),
       (2, 'porcentaje_riesgo_asistencia', '70', 'Umbral de asistencia para alerta academica', NULL),
       (3, 'ia_predictiva_modelo', 'reglas-academicas-v1', 'Modelo activo para deteccion preventiva', NULL),
       (4, 'horarios_protegidos', '1', 'La tabla horarios no debe modificarse desde scripts complementarios', NULL),
       (5, 'semestre_activo', '2026-I', 'Periodo academico activo por defecto', NULL),
       (6, 'institucion_nombre', 'Instituto Superior Tecnologico Vilcanota', 'Nombre institucional', NULL),
       (7, 'max_horas_docente_semana', '20', 'Limite recomendado de horas academicas por docente', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cursos`
--

CREATE TABLE `cursos`
(
    `id_curso`       int(10) UNSIGNED                       NOT NULL,
    `id_programa`    int(10) UNSIGNED                                DEFAULT NULL,
    `tipo_curso`     enum ('ESPECIFICO','TRANSVERSAL')      NOT NULL DEFAULT 'ESPECIFICO',
    `id_docente`     int(10) UNSIGNED                                DEFAULT NULL,
    `nombre_curso`   varchar(150)                           NOT NULL,
    `modulo`         varchar(100)                           NOT NULL,
    `semestre`       varchar(10)                            NOT NULL,
    `creditos`       int(11)                                NOT NULL,
    `horas_teoria`   int(11)                                NOT NULL,
    `horas_practica` int(11)                                NOT NULL,
    `horas_ud`       int(11)                                NOT NULL,
    `total_teoria`   int(11)                                NOT NULL,
    `total_practica` int(11)                                NOT NULL,
    `total_horas`    int(11)                                NOT NULL,
    `estado`         enum ('ACTIVO','INACTIVO','ARCHIVADO') NOT NULL DEFAULT 'ACTIVO',
    `deleted_at`     timestamp                              NULL     DEFAULT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `cursos`
--

INSERT INTO `cursos` (`id_curso`, `id_programa`, `tipo_curso`, `id_docente`, `nombre_curso`, `modulo`, `semestre`,
                      `creditos`, `horas_teoria`, `horas_practica`, `horas_ud`, `total_teoria`, `total_practica`,
                      `total_horas`, `estado`, `deleted_at`)
VALUES (3, 1, 'ESPECIFICO', NULL, 'Logica de programacion', 'Modulo I', 'I', 5, 1, 2, 3, 16, 64, 80, 'ACTIVO', NULL),
       (4, 1, 'ESPECIFICO', 1, 'Diseno de software', 'Modulo I', 'I', 6, 2, 2, 4, 32, 64, 96, 'ACTIVO', NULL),
       (5, 1, 'ESPECIFICO', NULL, 'Modelamiento de bases de datos', 'Modulo I', 'I', 7, 1, 3, 4, 16, 96, 112, 'ACTIVO',
        NULL),
       (6, 1, 'ESPECIFICO', 3, 'Tecnicas de programacion', 'Modulo I', 'I', 6, 2, 2, 4, 32, 64, 96, 'ACTIVO', NULL),
       (7, 1, 'ESPECIFICO', 9, 'Comunicacion oral', 'Modulo I', 'I', 3, 1, 1, 2, 16, 32, 48, 'ACTIVO', NULL),
       (8, 1, 'ESPECIFICO', 2, 'Aplicaciones en internet', 'Modulo I', 'I', 3, 1, 1, 2, 16, 32, 48, 'ACTIVO', NULL),
       (9, 1, 'ESPECIFICO', NULL, 'Algoritmos y estructuras de datos', 'Modulo I', 'II', 6, 2, 2, 6, 32, 64, 96,
        'ACTIVO', NULL),
       (10, 1, 'ESPECIFICO', NULL, 'Diseno web', 'Modulo I', 'II', 6, 2, 2, 6, 32, 64, 96, 'ACTIVO', NULL),
       (11, 1, 'ESPECIFICO', NULL, 'Gestion de base de datos', 'Modulo I', 'II', 7, 1, 3, 7, 16, 96, 112, 'ACTIVO',
        NULL),
       (12, 1, 'ESPECIFICO', NULL, 'Programacion orientada a objetos', 'Modulo I', 'II', 5, 1, 2, 5, 16, 64, 80,
        'ACTIVO', NULL),
       (13, 1, 'ESPECIFICO', NULL, 'Interpretacion y produccion de textos', 'Modulo I', 'II', 3, 1, 1, 3, 16, 32, 48,
        'ACTIVO', NULL),
       (14, 1, 'ESPECIFICO', NULL, 'Ofimatica', 'Modulo I', 'II', 3, 1, 1, 3, 16, 32, 48, 'ACTIVO', NULL),
       (15, 1, 'ESPECIFICO', 10, 'Administracion de sitios web', 'Modulo II', 'III', 8, 2, 3, 5, 32, 96, 128, 'ACTIVO',
        NULL),
       (16, 1, 'ESPECIFICO', 6, 'Seguridad informatica', 'Modulo II', 'III', 3, 1, 1, 2, 16, 32, 48, 'ACTIVO', NULL),
       (17, 1, 'ESPECIFICO', 2, 'Aplicaciones web', 'Modulo II', 'III', 9, 1, 4, 5, 16, 128, 144, 'ACTIVO', NULL),
       (18, 1, 'ESPECIFICO', 3, 'Aplicaciones moviles', 'Modulo II', 'III', 7, 1, 3, 4, 16, 96, 112, 'ACTIVO', NULL),
       (19, 1, 'ESPECIFICO', 6, 'Ingles para la comunicacion oral', 'Modulo II', 'III', 3, 1, 1, 2, 16, 32, 48,
        'ACTIVO', NULL),
       (20, 1, 'ESPECIFICO', NULL, 'Lenguaje de programacion concurrente', 'Modulo II', 'IV', 8, 2, 3, 8, 32, 96, 128,
        'ACTIVO', NULL),
       (21, 1, 'ESPECIFICO', NULL, 'Lenguaje de programacion web dinamico', 'Modulo II', 'IV', 8, 2, 3, 8, 32, 96, 128,
        'ACTIVO', NULL),
       (22, 1, 'ESPECIFICO', NULL, 'Modelamiento de software de entretenimiento', 'Modulo II', 'IV', 5, 1, 2, 5, 16, 64,
        80, 'ACTIVO', NULL),
       (23, 1, 'ESPECIFICO', NULL, 'Comprension y redaccion en ingles', 'Modulo II', 'IV', 2, 0, 1, 2, 0, 32, 32,
        'ACTIVO', NULL),
       (24, 1, 'ESPECIFICO', NULL, 'Base de datos no relacionales', 'Modulo II', 'IV', 7, 1, 3, 7, 16, 96, 112,
        'ACTIVO', NULL),
       (25, 1, 'ESPECIFICO', 6, 'Gestion de proyectos de TI', 'Modulo III', 'V', 5, 1, 2, 5, 16, 64, 80, 'ACTIVO',
        NULL),
       (26, 1, 'ESPECIFICO', 2, 'Pruebas y calidad del software', 'Modulo III', 'V', 7, 1, 3, 7, 16, 96, 112, 'ACTIVO',
        NULL),
       (27, 1, 'ESPECIFICO', 5, 'Inteligencia de negocios', 'Modulo III', 'V', 5, 1, 2, 5, 16, 64, 80, 'ACTIVO', NULL),
       (28, 1, 'ESPECIFICO', 3, 'Gestion de servicios de TI', 'Modulo III', 'V', 7, 1, 3, 7, 16, 96, 112, 'ACTIVO',
        NULL),
       (29, 1, 'ESPECIFICO', 8, 'Fundamentos de innovacion tecnologica', 'Modulo III', 'V', 3, 1, 1, 3, 16, 32, 48,
        'ACTIVO', NULL),
       (30, 1, 'ESPECIFICO', 6, 'Comportamiento etico', 'Modulo III', 'V', 3, 1, 1, 3, 16, 32, 48, 'ACTIVO', NULL),
       (31, 1, 'ESPECIFICO', NULL, 'Gestion de servidores', 'Modulo III', 'VI', 5, 1, 2, 3, 16, 64, 80, 'ACTIVO', NULL),
       (32, 1, 'ESPECIFICO', NULL, 'Gestion de redes informaticas', 'Modulo III', 'VI', 7, 1, 3, 4, 16, 96, 112,
        'ACTIVO', NULL),
       (33, 1, 'ESPECIFICO', NULL, 'Soporte de auditoria de TI', 'Modulo III', 'VI', 6, 2, 2, 4, 32, 64, 96, 'ACTIVO',
        NULL),
       (34, 1, 'ESPECIFICO', NULL, 'Auditoria de software', 'Modulo III', 'VI', 3, 1, 1, 2, 16, 32, 48, 'ACTIVO', NULL),
       (35, 1, 'ESPECIFICO', NULL, 'Inteligencia artificial', 'Modulo III', 'VI', 3, 1, 1, 2, 16, 32, 48, 'ACTIVO',
        NULL),
       (36, 1, 'ESPECIFICO', NULL, 'Solucion de problemas', 'Modulo III', 'VI', 3, 1, 1, 2, 16, 32, 48, 'ACTIVO', NULL),
       (37, 1, 'ESPECIFICO', NULL, 'Innovacion tecnologica', 'Modulo III', 'VI', 3, 1, 1, 2, 16, 32, 48, 'ACTIVO',
        NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `docentes`
--

CREATE TABLE `docentes`
(
    `id_docente`       int(10) UNSIGNED              NOT NULL,
    `id_usuario`       int(10) UNSIGNED                       DEFAULT NULL,
    `codigo_docente`   varchar(20)                   NOT NULL,
    `especialidad`     varchar(100)                           DEFAULT NULL,
    `tipo_docente`     enum ('ESPECIFICO','GENERAL') NOT NULL DEFAULT 'ESPECIFICO',
    `estado_academico` enum ('ACTIVO','INACTIVO')    NOT NULL DEFAULT 'ACTIVO',
    `fecha_registro`   timestamp                     NOT NULL DEFAULT current_timestamp(),
    `deleted_at`       timestamp                     NULL     DEFAULT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `docentes`
--

INSERT INTO `docentes` (`id_docente`, `id_usuario`, `codigo_docente`, `especialidad`, `tipo_docente`,
                        `estado_academico`, `fecha_registro`, `deleted_at`)
VALUES (1, 4, 'DOC001', 'Desarrollo de Software', 'ESPECIFICO', 'ACTIVO', '2026-07-06 14:58:23', NULL),
       (2, 5, 'DOC002', 'Desarrollo de Software', 'ESPECIFICO', 'ACTIVO', '2026-07-06 15:06:37', NULL),
       (3, 6, 'DOC003', 'Desarrollo de Software', 'ESPECIFICO', 'ACTIVO', '2026-07-06 15:09:52', NULL),
       (5, 8, 'DOC005', 'Desarrollo de Software', 'ESPECIFICO', 'ACTIVO', '2026-07-06 15:18:51', NULL),
       (6, 9, 'DOC006', 'Desarrollo de Software', 'ESPECIFICO', 'ACTIVO', '2026-07-06 15:23:25', NULL),
       (7, 10, 'DOC007', NULL, 'ESPECIFICO', 'ACTIVO', '2026-07-06 15:26:16', NULL),
       (8, 14, 'DOC008', 'Investigación', 'ESPECIFICO', 'ACTIVO', '2026-07-06 18:45:29', NULL),
       (9, 15, 'DOC009', 'Desarrollo de Software', 'ESPECIFICO', 'ACTIVO', '2026-07-06 19:02:11', NULL),
       (10, 16, 'DOC010', 'Desarrollo de Software', 'ESPECIFICO', 'ACTIVO', '2026-07-06 19:05:20', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `docente_disponibilidades`
--

CREATE TABLE `docente_disponibilidades`
(
    `id_disponibilidad` int(10) UNSIGNED                                       NOT NULL,
    `id_docente`        int(10) UNSIGNED                                       NOT NULL,
    `dia`               enum ('Lunes','Martes','Miércoles','Jueves','Viernes') NOT NULL,
    `hora_inicio`       time                                                   NOT NULL,
    `hora_fin`          time                                                   NOT NULL,
    `tipo`              enum ('DISPONIBLE','NO_DISPONIBLE','PREFERENCIA')      NOT NULL DEFAULT 'DISPONIBLE',
    `motivo`            varchar(180)                                                    DEFAULT NULL,
    `estado`            enum ('ACTIVO','INACTIVO')                             NOT NULL DEFAULT 'ACTIVO',
    `created_at`        timestamp                                              NULL     DEFAULT NULL,
    `updated_at`        timestamp                                              NULL     DEFAULT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `docente_programa`
--

CREATE TABLE `docente_programa`
(
    `id_docente_programa` int(10) UNSIGNED              NOT NULL,
    `id_docente`          int(10) UNSIGNED              NOT NULL,
    `id_programa`         int(10) UNSIGNED              NOT NULL,
    `tipo_asignacion`     enum ('ESPECIFICO','GENERAL') NOT NULL DEFAULT 'ESPECIFICO',
    `es_principal`        tinyint(1)                    NOT NULL DEFAULT 0,
    `estado`              enum ('ACTIVO','INACTIVO')    NOT NULL DEFAULT 'ACTIVO',
    `fecha_inicio`        date                                   DEFAULT NULL,
    `fecha_fin`           date                                   DEFAULT NULL,
    `observacion`         varchar(255)                           DEFAULT NULL,
    `created_at`          timestamp                     NULL     DEFAULT NULL,
    `updated_at`          timestamp                     NULL     DEFAULT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `docente_programa`
--

INSERT INTO `docente_programa` (`id_docente_programa`, `id_docente`, `id_programa`, `tipo_asignacion`, `es_principal`,
                                `estado`, `fecha_inicio`, `fecha_fin`, `observacion`, `created_at`, `updated_at`)
VALUES (1, 1, 1, 'ESPECIFICO', 1, 'ACTIVO', NULL, NULL, NULL, '2026-07-06 19:58:23', '2026-07-06 19:58:23'),
       (2, 2, 1, 'ESPECIFICO', 1, 'ACTIVO', NULL, NULL, NULL, '2026-07-06 20:06:37', '2026-07-06 20:06:37'),
       (3, 3, 1, 'ESPECIFICO', 1, 'ACTIVO', NULL, NULL, NULL, '2026-07-06 20:09:52', '2026-07-06 20:09:52'),
       (5, 5, 1, 'ESPECIFICO', 1, 'ACTIVO', NULL, NULL, NULL, '2026-07-06 20:18:51', '2026-07-06 20:18:51'),
       (6, 6, 1, 'ESPECIFICO', 1, 'ACTIVO', NULL, NULL, NULL, '2026-07-06 20:23:25', '2026-07-06 20:23:25'),
       (7, 8, 1, 'ESPECIFICO', 1, 'ACTIVO', NULL, NULL, NULL, '2026-07-06 23:45:29', '2026-07-06 23:45:29'),
       (8, 9, 1, 'ESPECIFICO', 1, 'ACTIVO', NULL, NULL, NULL, '2026-07-07 00:02:11', '2026-07-07 00:02:11'),
       (9, 10, 1, 'ESPECIFICO', 1, 'ACTIVO', NULL, NULL, NULL, '2026-07-07 00:05:20', '2026-07-07 00:05:20');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estudiantes`
--

CREATE TABLE `estudiantes`
(
    `id_estudiante`     int(10) UNSIGNED                                            NOT NULL,
    `codigo_estudiante` varchar(20)                                                 NOT NULL,
    `dni`               char(8)                                                              DEFAULT NULL,
    `nombres`           varchar(120)                                                NOT NULL,
    `apellido_paterno`  varchar(80)                                                          DEFAULT NULL,
    `apellido_materno`  varchar(80)                                                          DEFAULT NULL,
    `correo`            varchar(150)                                                         DEFAULT NULL,
    `telefono`          varchar(20)                                                          DEFAULT NULL,
    `id_programa`       int(10) UNSIGNED                                            NOT NULL,
    `ciclo`             enum ('I','II','III','IV','V','VI')                         NOT NULL DEFAULT 'I',
    `estado`            enum ('REGULAR','OBSERVADO','RIESGO','RETIRADO','EGRESADO') NOT NULL DEFAULT 'REGULAR',
    `fecha_registro`    timestamp                                                   NOT NULL DEFAULT current_timestamp(),
    `deleted_at`        timestamp                                                   NULL     DEFAULT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `estudiantes`
--

INSERT INTO `estudiantes` (`id_estudiante`, `codigo_estudiante`, `dni`, `nombres`, `apellido_paterno`,
                           `apellido_materno`, `correo`, `telefono`, `id_programa`, `ciclo`, `estado`, `fecha_registro`,
                           `deleted_at`)
VALUES (12, 'EST0002', '60738859', 'Anthony', 'Alvarez', 'Mamani', NULL, NULL, 1, 'III', '', '2026-07-06 19:46:31',
        NULL),
       (13, 'EST0003', '61120361', 'Johan', 'Aparicio', 'Fuentes', NULL, NULL, 1, 'III', '', '2026-07-06 19:46:31',
        NULL),
       (14, 'EST0004', '61243426', 'Moises Israel', 'Apaza', 'Ccahuana', NULL, NULL, 1, 'III', '',
        '2026-07-06 19:46:31', NULL),
       (15, 'EST0005', '60496825', 'Luz Danny', 'Arias', 'Huillca', NULL, NULL, 1, 'III', '', '2026-07-06 19:46:31',
        NULL),
       (16, 'EST0006', '75864388', 'Marco Antonio', 'Benavente', 'Mamani', NULL, NULL, 1, 'III', '',
        '2026-07-06 19:46:31', NULL),
       (17, 'EST0007', '61486129', 'Alexander', 'Cancapac', 'Cuchama', NULL, NULL, 1, 'III', '', '2026-07-06 19:46:31',
        NULL),
       (18, 'EST0008', '60308806', 'Franco Yobani', 'Ccahuana', 'Sasari', NULL, NULL, 1, 'III', '',
        '2026-07-06 19:46:31', NULL),
       (19, 'EST0009', '60021185', 'Nelida', 'Ccalsina', 'Huaman', NULL, NULL, 1, 'III', '', '2026-07-06 19:46:31',
        NULL),
       (20, 'EST0010', '77490284', 'Oscar Hemilton', 'Ccama', 'Llacsa', NULL, NULL, 1, 'III', '', '2026-07-06 19:46:31',
        NULL),
       (21, 'EST0011', '60477371', 'Jimena', 'Ccama', 'Torres', NULL, NULL, 1, 'III', '', '2026-07-06 19:46:31', NULL),
       (22, 'EST0012', '76838811', 'Samuel', 'Ccotohuanca', 'Huallpa', NULL, NULL, 1, 'III', '', '2026-07-06 19:46:31',
        NULL),
       (23, 'EST0013', '60277362', 'Gladyz', 'Chino', 'Meza', NULL, NULL, 1, 'III', '', '2026-07-06 19:46:31', NULL),
       (24, 'EST0014', '60244399', 'Yordi Alberth', 'Choque', 'Quispe', NULL, NULL, 1, 'III', '', '2026-07-06 19:46:31',
        NULL),
       (25, 'EST0015', '90138880', 'Jhon Brayan', 'Condori', 'Palomino', NULL, NULL, 1, 'III', '',
        '2026-07-06 19:46:31', NULL),
       (26, 'EST1001', NULL, 'Vaamnor Alejandro', 'Allccahuaman', 'Quispe', NULL, NULL, 1, 'V', '',
        '2026-07-06 19:51:19', NULL),
       (27, 'EST1002', NULL, 'Raphael Benjamin', 'Calachua', 'Huamani', NULL, NULL, 1, 'V', '', '2026-07-06 19:51:19',
        NULL),
       (28, 'EST1003', NULL, 'Yudtmary Yesenia', 'Chinchero', 'Quispe', NULL, NULL, 1, 'V', '', '2026-07-06 19:51:19',
        NULL),
       (29, 'EST1004', NULL, 'Jhon Jharet', 'Cjuno', 'Pineda', NULL, NULL, 1, 'V', '', '2026-07-06 19:51:19', NULL),
       (30, 'EST1005', NULL, 'Jose', 'Cuno', 'Huillca', NULL, NULL, 1, 'V', '', '2026-07-06 19:51:19', NULL),
       (31, 'EST1006', NULL, 'Nestor', 'Deza', 'Aroni', NULL, NULL, 1, 'V', '', '2026-07-06 19:51:19', NULL),
       (32, 'EST1007', NULL, 'Jose Luis', 'Gamarra', 'Chino', NULL, NULL, 1, 'V', '', '2026-07-06 19:51:19', NULL),
       (33, 'EST1008', NULL, 'Noemi Graciela', 'Huamani', 'Condori', NULL, NULL, 1, 'V', '', '2026-07-06 19:51:19',
        NULL),
       (34, 'EST1009', NULL, 'Javier', 'Huillca', 'Cahuana', NULL, NULL, 1, 'V', '', '2026-07-06 19:51:19', NULL),
       (35, 'EST1010', NULL, 'Erick', 'Lacuta', 'Huaman', NULL, NULL, 1, 'V', '', '2026-07-06 19:51:19', NULL),
       (36, 'EST1011', NULL, 'Julber', 'Mamani', 'Ccotohuanca', NULL, NULL, 1, 'V', '', '2026-07-06 19:51:19', NULL),
       (37, 'EST1012', NULL, 'Darwin', 'Mamani', 'Mamani', NULL, NULL, 1, 'V', '', '2026-07-06 19:51:19', NULL),
       (38, 'EST1013', NULL, 'Axel Fabrizzio', 'Peña', 'Quispe', NULL, NULL, 1, 'V', '', '2026-07-06 19:51:19', NULL),
       (39, 'EST1014', NULL, 'Clairraux Raul', 'Quispe', 'Huaman', NULL, NULL, 1, 'V', '', '2026-07-06 19:51:19', NULL),
       (40, 'EST1015', NULL, 'Emerson Frank', 'Quispe', 'Huayta', NULL, NULL, 1, 'V', '', '2026-07-06 19:51:19', NULL),
       (41, 'EST1016', NULL, 'Justo Anderson', 'Quispe', 'Romero', NULL, NULL, 1, 'V', '', '2026-07-06 19:51:19', NULL),
       (42, 'EST1017', NULL, 'Ivan Roger', 'Quispe', 'Sacaca', NULL, NULL, 1, 'V', '', '2026-07-06 19:51:19', NULL),
       (43, 'EST1018', NULL, 'Nely Vanesa', 'Roa', 'Cahuana', NULL, NULL, 1, 'V', '', '2026-07-06 19:51:19', NULL),
       (44, 'EST1019', NULL, 'Victor Ronaldo', 'Saire', 'Arrosquipa', NULL, NULL, 1, 'V', '', '2026-07-06 19:51:19',
        NULL),
       (45, 'EST1020', NULL, 'Jesus Armando', 'Salas', 'Puma', NULL, NULL, 1, 'V', '', '2026-07-06 19:51:19', NULL),
       (46, 'EST1021', NULL, 'Damaris', 'Solis', 'Cuno', NULL, NULL, 1, 'V', '', '2026-07-06 19:51:19', NULL),
       (47, 'EST1022', NULL, 'Bertha', 'Tinta', 'Mamani', NULL, NULL, 1, 'V', '', '2026-07-06 19:51:19', NULL),
       (48, 'EST2001', NULL, 'Marleni', 'Acsaraya', 'Leon', NULL, NULL, 1, 'I', '', '2026-07-06 19:52:57', NULL),
       (49, 'EST2002', NULL, 'Giancarlo Adrian', 'Aimituma', 'Mamani', NULL, NULL, 1, 'I', '', '2026-07-06 19:52:57',
        NULL),
       (50, 'EST2003', NULL, 'Jhenifer Pamela', 'Alvarado', 'Flores', NULL, NULL, 1, 'I', '', '2026-07-06 19:52:57',
        NULL),
       (51, 'EST2004', NULL, 'Cristofer Henry', 'Apaza', 'Hanco', NULL, NULL, 1, 'I', '', '2026-07-06 19:52:57', NULL),
       (52, 'EST2005', NULL, 'Gabriel Alejandro', 'Apaza', 'Pilco', NULL, NULL, 1, 'I', '', '2026-07-06 19:52:57',
        NULL),
       (53, 'EST2006', NULL, 'Karla Shiomara', 'Arapa', 'Mamani', NULL, NULL, 1, 'I', '', '2026-07-06 19:52:57', NULL),
       (54, 'EST2007', NULL, 'Yessica', 'Catunta', 'Huillca', NULL, NULL, 1, 'I', '', '2026-07-06 19:52:57', NULL),
       (55, 'EST2008', NULL, 'Jhon Antony', 'Ccansaya', 'Saraya', NULL, NULL, 1, 'I', '', '2026-07-06 19:52:57', NULL),
       (56, 'EST2009', NULL, 'Jose Alejandro', 'Champi', 'Mascco', NULL, NULL, 1, 'I', '', '2026-07-06 19:52:57', NULL),
       (57, 'EST2010', NULL, 'Xiomara Ximena', 'Chinchero', 'Cañihua', NULL, NULL, 1, 'I', '', '2026-07-06 19:52:57',
        NULL),
       (58, 'EST2011', NULL, 'Margoth', 'Choque', 'Castelo', NULL, NULL, 1, 'I', '', '2026-07-06 19:52:57', NULL),
       (59, 'EST2012', NULL, 'Larry Franco', 'Condori', 'Paucar', NULL, NULL, 1, 'I', '', '2026-07-06 19:52:57', NULL),
       (60, 'EST2013', NULL, 'Daylin Marely', 'Fernandez', 'Champi', NULL, NULL, 1, 'I', '', '2026-07-06 19:52:57',
        NULL),
       (61, 'EST2014', NULL, 'Kim Leonel', 'Hancco', 'Visa', NULL, NULL, 1, 'I', '', '2026-07-06 19:52:57', NULL),
       (62, 'EST2015', NULL, 'Franco Alex', 'Hanccoccallo', 'Huayhua', NULL, NULL, 1, 'I', '', '2026-07-06 19:52:57',
        NULL),
       (63, 'EST2016', NULL, 'Rody Jose', 'Huanca', 'Puma', NULL, NULL, 1, 'I', '', '2026-07-06 19:52:57', NULL),
       (64, 'EST2017', NULL, 'Yeishon Elvis', 'Huayta', 'Imata', NULL, NULL, 1, 'I', '', '2026-07-06 19:52:57', NULL),
       (65, 'EST2018', NULL, 'Jhon Mijail', 'Mamani', 'Charca', NULL, NULL, 1, 'I', '', '2026-07-06 19:52:57', NULL),
       (66, 'EST2019', NULL, 'Ronal Brayan', 'Mamani', 'Tacca', NULL, NULL, 1, 'I', '', '2026-07-06 19:52:57', NULL),
       (67, 'EST2020', NULL, 'Rosa Liz', 'Mejia', 'Titica', NULL, NULL, 1, 'I', '', '2026-07-06 19:52:57', NULL),
       (68, 'EST2021', NULL, 'Brigida', 'Mullisaca', 'Quispe', NULL, NULL, 1, 'I', '', '2026-07-06 19:52:57', NULL),
       (69, 'EST2022', NULL, 'Nicolae', 'Onofre', 'Chuquichampi', NULL, NULL, 1, 'I', '', '2026-07-06 19:52:57', NULL),
       (70, 'EST2023', NULL, 'Rosendo Aldair', 'Pillco', 'Mamani', NULL, NULL, 1, 'I', '', '2026-07-06 19:52:57', NULL),
       (71, 'EST2024', NULL, 'Edwin Edison', 'Quispe', 'Arce', NULL, NULL, 1, 'I', '', '2026-07-06 19:52:57', NULL),
       (72, 'EST2025', NULL, 'Nely Yanet', 'Quispe', 'Ayala', NULL, NULL, 1, 'I', '', '2026-07-06 19:52:57', NULL),
       (73, 'EST2026', NULL, 'Alexis Julinho', 'Quispe', 'Usca', NULL, NULL, 1, 'I', '', '2026-07-06 19:52:57', NULL),
       (74, 'EST2027', NULL, 'Leonidas Sandro', 'Quispe', 'Yucra', NULL, NULL, 1, 'I', '', '2026-07-06 19:52:57', NULL),
       (75, 'EST2028', NULL, 'Erick Enrique', 'Sanca', 'Guzman', NULL, NULL, 1, 'I', '', '2026-07-06 19:52:57', NULL),
       (76, 'EST2029', NULL, 'Francisco Leonel', 'Soto', 'Cjuno', NULL, NULL, 1, 'I', '', '2026-07-06 19:52:57', NULL),
       (77, 'EST2030', NULL, 'Patricia Alexsandra', 'Tapia', 'Mamani', NULL, NULL, 1, 'I', '', '2026-07-06 19:52:57',
        NULL),
       (78, 'EST2031', NULL, 'Raul', 'Ugarte', 'Nina', NULL, NULL, 1, 'I', '', '2026-07-06 19:52:57', NULL),
       (79, 'EST2032', NULL, 'Ciro Armando', 'Vargas', 'Cuti', NULL, NULL, 1, 'I', '', '2026-07-06 19:52:57', NULL),
       (80, 'EST2033', NULL, 'Erwin Daniel', 'Zarate', 'Choque', NULL, NULL, 1, 'I', '', '2026-07-06 19:52:57', NULL),
       (81, 'EST2034', NULL, 'Deysi Zamanta', 'Zavaleta', 'Aro', NULL, NULL, 1, 'I', '', '2026-07-06 19:52:57', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `failed_jobs`
--

CREATE TABLE `failed_jobs`
(
    `id`         bigint(20) UNSIGNED NOT NULL,
    `uuid`       varchar(255)        NOT NULL,
    `connection` text                NOT NULL,
    `queue`      text                NOT NULL,
    `payload`    longtext            NOT NULL,
    `exception`  longtext            NOT NULL,
    `failed_at`  timestamp           NOT NULL DEFAULT current_timestamp()
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `horarios`
--

CREATE TABLE `horarios`
(
    `id_horario`  int(10) UNSIGNED     NOT NULL,
    `id_curso`    int(10) UNSIGNED     NOT NULL,
    `id_docente`  int(10) UNSIGNED     NOT NULL,
    `id_aula`     int(10) UNSIGNED              DEFAULT NULL,
    `id_periodo`  int(10) UNSIGNED              DEFAULT NULL,
    `id_programa` int(10) UNSIGNED              DEFAULT NULL,
    `semestre`    varchar(10)                   DEFAULT NULL,
    `dia`         varchar(20)                   DEFAULT NULL,
    `hora_inicio` time                          DEFAULT NULL,
    `hora_fin`    time                          DEFAULT NULL,
    `aula`        varchar(80)                   DEFAULT NULL,
    `estado`      varchar(30)          NOT NULL DEFAULT 'Confirmado',
    `fuente`      enum ('MANUAL','IA') NOT NULL DEFAULT 'MANUAL',
    `observacion` text                          DEFAULT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `horarios_ia_generados`
--

CREATE TABLE `horarios_ia_generados`
(
    `id_generacion`    int(10) UNSIGNED                          NOT NULL,
    `id_usuario`       int(10) UNSIGNED                                   DEFAULT NULL,
    `id_periodo`       int(10) UNSIGNED                                   DEFAULT NULL,
    `programa`         varchar(150)                                       DEFAULT NULL,
    `modelo`           varchar(80)                                        DEFAULT NULL,
    `prompt_resumen`   text                                               DEFAULT NULL,
    `resultado_json`   longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`resultado_json`)),
    `metadata_json`    longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata_json`)),
    `errores_json`     longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`errores_json`)),
    `estado`           enum ('BORRADOR','APROBADO','DESCARTADO') NOT NULL DEFAULT 'BORRADOR',
    `fecha_generacion` timestamp                                 NOT NULL DEFAULT current_timestamp()
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `horarios_ia_generados`
--

INSERT INTO `horarios_ia_generados` (`id_generacion`, `id_usuario`, `id_periodo`, `programa`, `modelo`,
                                     `prompt_resumen`, `resultado_json`, `metadata_json`, `errores_json`, `estado`,
                                     `fecha_generacion`)
VALUES (1, 1, 1, 'Desarrollo de Sistemas de Informacion', 'fake',
        'Eres un asistente que genera horarios academicos para un instituto tecnologico peruano.\n\nDebes devolver EXCLUSIVAMENTE un JSON valido, sin texto adicional antes o despues, con esta forma exacta:\n{\n  \"estado\": \"GENERADO\",\n  \"detalles\": [\n    {\"id_curso\": 1, \"id_docente\": 2, \"id_aula\": 3, \"dia\": \"LUNES\", \"hora_inicio\": \"08:00\", \"hora_fin\": \"08:45\", \"tipo\": \"TEORIA\"}\n  ],\n  \"observaciones\": [],\n  \"conflictos\": []\n}\n\nReglas obligatorias (no negociables):\n- Usa UNICAMENTE los id_curso, id_docente e id_aula que aparecen en el contexto de datos. Esta prohibido inventar IDs.\n- Un docente no puede tener dos cursos el mismo dia y hora.\n- Un aula no puede ser usada por dos cursos el mismo dia y hora.\n- Ningun docente debe superar 20 bloques academicos semanales (sumando carga_actual_bloques + lo que le asignes).\n- No programes clases en el bloque de receso (11:00-11:15).\n- Si un curso tiene horas_practica > 0, prefiere un aula tipo LABORATORIO o TALLER cuando exista una disponible.\n- Un curso debe cubrir aproximadamente total_horas bloques academicos (1 bloque = 1 hora academica de 45 minutos).\n- Si un curso ya tiene id_docente asignado en el contexto, respeta ese docente; si es null, elige el docente compatible con menor carga_actual_bloques.\n- Distribuye los cursos entre los dias de la semana, evitando huecos innecesarios.\n\nContexto de datos (cursos, docentes, aulas, dias y bloques disponibles reales):\n### CONTEXTO_DATOS_JSON_INICIO\n{\"id_programa\":1,\"id_periodo\":1,\"semestre\":null,\"cursos\":[],\"docentes\":[],\"aulas\":[{\"id_aula\":1,\"codigo\":\"A201\",\"tipo\":\"AULA\",\"capacidad\":35},{\"id_aula\":2,\"codigo\":\"A202\",\"tipo\":\"AULA\",\"capacidad\":35},{\"id_aula\":3,\"codigo\":\"A203\",\"tipo\":\"AULA\",\"capacidad\":30},{\"id_aula\":4,\"codigo\":\"LAB-COMP\",\"tipo\":\"LABORATORIO\",\"capacidad\":28},{\"id_aula\":5,\"codigo\":\"LAB-REDES\",\"tipo\":\"LABORATORIO\",\"capacidad\":24}],\"dias\":[\"Lunes\",\"Martes\",\"Miércoles\",\"Jueves\",\"Viernes\",\"Sábado\"],\"bloques\":[{\"inicio\":\"08:00\",\"fin\":\"08:45\"},{\"inicio\":\"08:45\",\"fin\":\"09:30\"},{\"inicio\":\"09:30\",\"fin\":\"10:15\"},{\"inicio\":\"10:15\",\"fin\":\"11:00\"},{\"inicio\":\"11:15\",\"fin\":\"12:00\"},{\"inicio\":\"12:00\",\"fin\":\"12:45\"}],\"docente_max_bloques\":20}\n### CONTEXTO_DATOS_JSON_FIN\n\nResponde solo con el JSON pedido.',
        '{\"detalles\":[],\"observaciones\":[\"Propuesta generada por FakeHorarioProvider (sin llamada externa).\"]}',
        '{\"filtro\":{\"id_programa\":1,\"id_periodo\":1,\"semestre\":null},\"intentos_reparacion\":0,\"cambios_reparacion\":[]}',
        '{\"errores\":[\"La propuesta no contiene ningun bloque.\"],\"conflictos\":[]}', 'BORRADOR',
        '2026-07-06 14:51:34'),
       (2, 1, 1, 'Desarrollo de Sistemas de Informacion', 'fake',
        'Eres un asistente que genera horarios academicos para un instituto tecnologico peruano.\n\nDebes devolver EXCLUSIVAMENTE un JSON valido, sin texto adicional antes o despues, con esta forma exacta:\n{\n  \"estado\": \"GENERADO\",\n  \"detalles\": [\n    {\"id_curso\": 1, \"id_docente\": 2, \"id_aula\": 3, \"dia\": \"LUNES\", \"hora_inicio\": \"08:00\", \"hora_fin\": \"08:45\", \"tipo\": \"TEORIA\"}\n  ],\n  \"observaciones\": [],\n  \"conflictos\": []\n}\n\nReglas obligatorias (no negociables):\n- Usa UNICAMENTE los id_curso, id_docente e id_aula que aparecen en el contexto de datos. Esta prohibido inventar IDs.\n- Un docente no puede tener dos cursos el mismo dia y hora.\n- Un aula no puede ser usada por dos cursos el mismo dia y hora.\n- Ningun docente debe superar 20 bloques academicos semanales (sumando carga_actual_bloques + lo que le asignes).\n- No programes clases en el bloque de receso (11:00-11:15).\n- Si un curso tiene horas_practica > 0, prefiere un aula tipo LABORATORIO o TALLER cuando exista una disponible.\n- Un curso debe cubrir aproximadamente total_horas bloques academicos (1 bloque = 1 hora academica de 45 minutos).\n- Si un curso ya tiene id_docente asignado en el contexto, respeta ese docente; si es null, elige el docente compatible con menor carga_actual_bloques.\n- Distribuye los cursos entre los dias de la semana, evitando huecos innecesarios.\n\nContexto de datos (cursos, docentes, aulas, dias y bloques disponibles reales):\n### CONTEXTO_DATOS_JSON_INICIO\n{\"id_programa\":1,\"id_periodo\":1,\"semestre\":\"I\",\"cursos\":[],\"docentes\":[],\"aulas\":[{\"id_aula\":1,\"codigo\":\"A201\",\"tipo\":\"AULA\",\"capacidad\":35},{\"id_aula\":2,\"codigo\":\"A202\",\"tipo\":\"AULA\",\"capacidad\":35},{\"id_aula\":3,\"codigo\":\"A203\",\"tipo\":\"AULA\",\"capacidad\":30},{\"id_aula\":4,\"codigo\":\"LAB-COMP\",\"tipo\":\"LABORATORIO\",\"capacidad\":28},{\"id_aula\":5,\"codigo\":\"LAB-REDES\",\"tipo\":\"LABORATORIO\",\"capacidad\":24}],\"dias\":[\"Lunes\",\"Martes\",\"Miércoles\",\"Jueves\",\"Viernes\",\"Sábado\"],\"bloques\":[{\"inicio\":\"08:00\",\"fin\":\"08:45\"},{\"inicio\":\"08:45\",\"fin\":\"09:30\"},{\"inicio\":\"09:30\",\"fin\":\"10:15\"},{\"inicio\":\"10:15\",\"fin\":\"11:00\"},{\"inicio\":\"11:15\",\"fin\":\"12:00\"},{\"inicio\":\"12:00\",\"fin\":\"12:45\"}],\"docente_max_bloques\":20}\n### CONTEXTO_DATOS_JSON_FIN\n\nResponde solo con el JSON pedido.',
        '{\"detalles\":[],\"observaciones\":[\"Propuesta generada por FakeHorarioProvider (sin llamada externa).\"]}',
        '{\"filtro\":{\"id_programa\":1,\"id_periodo\":1,\"semestre\":\"I\"},\"intentos_reparacion\":0,\"cambios_reparacion\":[]}',
        '{\"errores\":[\"La propuesta no contiene ningun bloque.\"],\"conflictos\":[]}', 'DESCARTADO',
        '2026-07-06 14:53:36');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ia_predicciones`
--

CREATE TABLE `ia_predicciones`
(
    `id_prediccion`        int(10) UNSIGNED                       NOT NULL,
    `id_estudiante`        int(10) UNSIGNED                                   DEFAULT NULL,
    `id_curso`             int(10) UNSIGNED                                   DEFAULT NULL,
    `id_periodo`           int(10) UNSIGNED                                   DEFAULT NULL,
    `modelo`               varchar(80)                            NOT NULL    DEFAULT 'reglas-academicas-v1',
    `score_riesgo`         decimal(5, 2)                          NOT NULL    DEFAULT 0.00,
    `probabilidad_aprobar` decimal(5, 2)                                      DEFAULT NULL,
    `nivel`                enum ('BAJO','MEDIO','ALTO','CRITICO') NOT NULL    DEFAULT 'MEDIO',
    `factores_json`        longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`factores_json`)),
    `simulacion_json`      longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`simulacion_json`)),
    `recomendacion`        text                                               DEFAULT NULL,
    `fecha_prediccion`     timestamp                              NOT NULL    DEFAULT current_timestamp()
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `jobs`
--

CREATE TABLE `jobs`
(
    `id`           bigint(20) UNSIGNED NOT NULL,
    `queue`        varchar(255)        NOT NULL,
    `payload`      longtext            NOT NULL,
    `attempts`     tinyint(3) UNSIGNED NOT NULL,
    `reserved_at`  int(10) UNSIGNED DEFAULT NULL,
    `available_at` int(10) UNSIGNED    NOT NULL,
    `created_at`   int(10) UNSIGNED    NOT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `job_batches`
--

CREATE TABLE `job_batches`
(
    `id`             varchar(255) NOT NULL,
    `name`           varchar(255) NOT NULL,
    `total_jobs`     int(11)      NOT NULL,
    `pending_jobs`   int(11)      NOT NULL,
    `failed_jobs`    int(11)      NOT NULL,
    `failed_job_ids` longtext     NOT NULL,
    `options`        mediumtext DEFAULT NULL,
    `cancelled_at`   int(11)    DEFAULT NULL,
    `created_at`     int(11)      NOT NULL,
    `finished_at`    int(11)    DEFAULT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `login_otps`
--

CREATE TABLE `login_otps`
(
    `id`         bigint(20) UNSIGNED NOT NULL,
    `id_usuario` int(10) UNSIGNED    NOT NULL,
    `email`      varchar(150)        NOT NULL,
    `code_hash`  varchar(255)        NOT NULL,
    `expires_at` datetime            NOT NULL,
    `used_at`    datetime                     DEFAULT NULL,
    `attempts`   tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
    `ip_address` varchar(45)                  DEFAULT NULL,
    `user_agent` text                         DEFAULT NULL,
    `created_at` timestamp           NULL     DEFAULT NULL,
    `updated_at` timestamp           NULL     DEFAULT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `login_otps`
--

INSERT INTO `login_otps` (`id`, `id_usuario`, `email`, `code_hash`, `expires_at`, `used_at`, `attempts`, `ip_address`,
                          `user_agent`, `created_at`, `updated_at`)
VALUES (1, 1, 'director@istv.edu.pe', '$2y$12$QsKIboHOS0gz/0mp2Bo0Sult7tDMKgN2RHD.nn121fQj6E1pReYKG',
        '2026-07-05 22:54:17', '2026-07-05 22:46:57', 1, '127.0.0.1',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',
        '2026-07-06 03:44:17', '2026-07-06 03:46:57'),
       (2, 1, 'director@istv.edu.pe', '$2y$12$X79oBmltAloTY8mvQdxjkOGo8gmfflEV83ucm2.1FC.PyJGyxggru',
        '2026-07-05 22:56:58', '2026-07-05 22:51:03', 0, '127.0.0.1',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',
        '2026-07-06 03:46:58', '2026-07-06 03:51:03'),
       (3, 1, 'director@istv.edu.pe', '$2y$12$vADF/kzqxvdIaUaAzCJ20u.9lA0l/iC3NIMKECYgLbNpjPXbSUzT2',
        '2026-07-05 23:01:03', '2026-07-05 22:51:20', 0, '127.0.0.1',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',
        '2026-07-06 03:51:03', '2026-07-06 03:51:20'),
       (4, 2, 'damarissoliscuno631@gmail.com', '$2y$12$0kncZq4blHT6ZOa7p78gDO/ZwfvQtTUMgoU7H9RA1JOGWZ4j1c.QK',
        '2026-07-06 17:22:43', '2026-07-06 17:13:15', 0, '127.0.0.1',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',
        '2026-07-06 22:12:43', '2026-07-06 22:13:15'),
       (5, 10, 'gcielahuamani@gmail.com', '$2y$12$d0bt//cmJDgGGfaOOV6i8uFyJgzcNaTdpY3Py/m1ZqV6H/KkatsSW',
        '2026-07-06 17:25:29', '2026-07-06 17:15:58', 0, '127.0.0.1',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',
        '2026-07-06 22:15:29', '2026-07-06 22:15:58'),
       (6, 8, 'chincheroyesenia@gmail.com', '$2y$12$YDpxYCgG2IVm69Y4yoX6l.1NO7jwQP3i607m.9YSe2o76DrJ2EwPK',
        '2026-07-06 17:52:40', '2026-07-06 17:43:02', 0, '127.0.0.1',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0',
        '2026-07-06 22:42:40', '2026-07-06 22:43:02');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `matriculas`
--

CREATE TABLE `matriculas`
(
    `id_matricula`    int(10) UNSIGNED                                    NOT NULL,
    `id_estudiante`   int(10) UNSIGNED                                    NOT NULL,
    `id_periodo`      int(10) UNSIGNED                                    NOT NULL,
    `ciclo`           enum ('I','II','III','IV','V','VI')                 NOT NULL,
    `estado`          enum ('MATRICULADO','RESERVA','RETIRADO','CERRADO') NOT NULL DEFAULT 'MATRICULADO',
    `fecha_matricula` date                                                NOT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `matricula_cursos`
--

CREATE TABLE `matricula_cursos`
(
    `id_matricula_curso` int(10) UNSIGNED                                      NOT NULL,
    `id_matricula`       int(10) UNSIGNED                                      NOT NULL,
    `id_curso`           int(10) UNSIGNED                                      NOT NULL,
    `estado`             enum ('EN_CURSO','APROBADO','DESAPROBADO','RETIRADO') NOT NULL DEFAULT 'EN_CURSO'
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mensajes`
--

CREATE TABLE `mensajes`
(
    `id_mensaje`      int(10) UNSIGNED NOT NULL,
    `id_remitente`    int(10) UNSIGNED NOT NULL,
    `id_destinatario` int(10) UNSIGNED NOT NULL,
    `asunto`          varchar(180)     NOT NULL,
    `mensaje`         text             NOT NULL,
    `leido`           tinyint(1)       NOT NULL DEFAULT 0,
    `fecha_envio`     timestamp        NOT NULL DEFAULT current_timestamp()
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `migrations`
--

CREATE TABLE `migrations`
(
    `id`        int(10) UNSIGNED NOT NULL,
    `migration` varchar(255)     NOT NULL,
    `batch`     int(11)          NOT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`)
VALUES (1, '0001_01_01_000000_create_users_table', 1),
       (2, '0001_01_01_000001_create_cache_table', 1),
       (3, '0001_01_01_000002_create_jobs_table', 1),
       (4, '2026_07_03_100000_create_roles_table', 1),
       (5, '2026_07_03_100010_create_programas_estudio_table', 1),
       (6, '2026_07_03_100020_create_periodos_academicos_table', 1),
       (7, '2026_07_03_100030_create_aulas_table', 1),
       (8, '2026_07_03_100040_create_docentes_table', 1),
       (9, '2026_07_03_100050_create_silabo_estructuras_table', 1),
       (10, '2026_07_03_100060_create_configuracion_sistema_table', 1),
       (11, '2026_07_03_100070_create_usuarios_table', 1),
       (12, '2026_07_03_100080_create_estudiantes_table', 1),
       (13, '2026_07_03_100090_create_cursos_table', 1),
       (14, '2026_07_03_100100_create_silabo_estructura_criterios_table', 1),
       (15, '2026_07_03_100110_create_horarios_table', 1),
       (16, '2026_07_03_100120_create_matriculas_table', 1),
       (17, '2026_07_03_100130_create_portafolio_docente_table', 1),
       (18, '2026_07_03_100140_create_sesiones_aprendizaje_table', 1),
       (19, '2026_07_03_100150_create_mensajes_table', 1),
       (20, '2026_07_03_100160_create_notificaciones_table', 1),
       (21, '2026_07_03_100170_create_reportes_generados_table', 1),
       (22, '2026_07_03_100180_create_horarios_ia_generados_table', 1),
       (23, '2026_07_03_100190_create_auditoria_sistema_table', 1),
       (24, '2026_07_03_100200_create_matricula_cursos_table', 1),
       (25, '2026_07_03_100210_create_asistencia_sesiones_table', 1),
       (26, '2026_07_03_100220_create_portafolio_documentos_table', 1),
       (27, '2026_07_03_100230_create_alertas_academicas_table', 1),
       (28, '2026_07_03_100240_create_ia_predicciones_table', 1),
       (29, '2026_07_03_100250_create_notas_table', 1),
       (30, '2026_07_03_100260_create_asistencia_detalle_table', 1),
       (31, '2026_07_04_090000_add_id_programa_and_estado_to_cursos_table', 1),
       (32, '2026_07_04_120000_add_fuente_to_horarios_table', 1),
       (33, '2026_07_05_000000_move_docente_personal_data_to_usuarios_table', 1),
       (34, '2026_07_05_130000_create_login_otps_table', 1),
       (35, '2026_07_05_170551_add_otp_verified_fields_to_usuarios_table', 1),
       (36, '2026_07_06_000010_add_tipo_docente_to_docentes_table', 1),
       (37, '2026_07_06_000020_create_docente_programa_table', 1),
       (38, '2026_07_06_000030_add_tipo_curso_to_cursos_table', 1),
       (39, '2026_07_06_000040_add_columns_to_horarios_table', 1),
       (40, '2026_07_06_000050_create_docente_disponibilidades_table', 1),
       (41, '2026_07_07_000000_add_metadata_columns_to_horarios_ia_generados_table', 1),
       (42, '2026_07_07_000010_add_cambio_password_obligatorio_to_usuarios_table', 1),
       (43, '2026_07_07_000020_create_solicitudes_password_table', 1),
       (44, '2026_07_07_000030_add_id_programa_to_usuarios_table', 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notas`
--

CREATE TABLE `notas`
(
    `id_nota`             int(10) UNSIGNED                         NOT NULL,
    `id_matricula_curso`  int(10) UNSIGNED                         NOT NULL,
    `unidad`              varchar(20)                              NOT NULL DEFAULT 'I',
    `practica`            decimal(5, 2)                                     DEFAULT NULL,
    `teoria`              decimal(5, 2)                                     DEFAULT NULL,
    `examen`              decimal(5, 2)                                     DEFAULT NULL,
    `promedio`            decimal(5, 2) GENERATED ALWAYS AS (round(
        coalesce(`practica`, 0) * 0.20 + coalesce(`teoria`, 0) * 0.30 + coalesce(`examen`, 0) * 0.50, 2)) STORED,
    `estado`              enum ('ABIERTO','CERRADO','RECTIFICADO') NOT NULL DEFAULT 'ABIERTO',
    `fecha_registro`      timestamp                                NOT NULL DEFAULT current_timestamp(),
    `fecha_actualizacion` timestamp                                NULL     DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificaciones`
--

CREATE TABLE `notificaciones`
(
    `id_notificacion` int(10) UNSIGNED NOT NULL,
    `id_usuario`      int(10) UNSIGNED          DEFAULT NULL,
    `tipo`            varchar(40)      NOT NULL,
    `titulo`          varchar(150)     NOT NULL,
    `detalle`         varchar(255)              DEFAULT NULL,
    `url_destino`     varchar(255)              DEFAULT NULL,
    `leido`           tinyint(1)       NOT NULL DEFAULT 0,
    `fecha_creacion`  timestamp        NOT NULL DEFAULT current_timestamp()
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `notificaciones`
--

INSERT INTO `notificaciones` (`id_notificacion`, `id_usuario`, `tipo`, `titulo`, `detalle`, `url_destino`, `leido`,
                              `fecha_creacion`)
VALUES (1, 1, 'SOLICITUD_PASSWORD', 'Solicitud de restablecimiento de contraseña',
        'Maria Antonieta MENDOZA TECSI (maria) solicitó recuperar su contraseña.', '/director/usuarios', 0,
        '2026-07-06 17:11:23');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens`
(
    `email`      varchar(255) NOT NULL,
    `token`      varchar(255) NOT NULL,
    `created_at` timestamp    NULL DEFAULT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `periodos_academicos`
--

CREATE TABLE `periodos_academicos`
(
    `id_periodo`   int(10) UNSIGNED                        NOT NULL,
    `codigo`       varchar(20)                             NOT NULL,
    `nombre`       varchar(80)                             NOT NULL,
    `fecha_inicio` date                                             DEFAULT NULL,
    `fecha_fin`    date                                             DEFAULT NULL,
    `estado`       enum ('PLANIFICADO','ACTIVO','CERRADO') NOT NULL DEFAULT 'PLANIFICADO'
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

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
    `id_portafolio`       int(10) UNSIGNED                                         NOT NULL,
    `id_docente`          int(10) UNSIGNED                                         NOT NULL,
    `id_curso`            int(10) UNSIGNED                                         NOT NULL,
    `id_periodo`          int(10) UNSIGNED                                         NOT NULL,
    `silabo`              enum ('PENDIENTE','EN_REVISION','APROBADO','OBSERVADO')  NOT NULL DEFAULT 'PENDIENTE',
    `sesiones`            enum ('PENDIENTE','EN_REVISION','APROBADO','OBSERVADO')  NOT NULL DEFAULT 'PENDIENTE',
    `registro_asistencia` enum ('PENDIENTE','EN_REVISION','APROBADO','OBSERVADO')  NOT NULL DEFAULT 'PENDIENTE',
    `registro_notas`      enum ('PENDIENTE','EN_REVISION','APROBADO','OBSERVADO')  NOT NULL DEFAULT 'PENDIENTE',
    `actas`               enum ('PENDIENTE','EN_REVISION','APROBADO','OBSERVADO')  NOT NULL DEFAULT 'PENDIENTE',
    `estado`              enum ('INCOMPLETO','EN_REVISION','COMPLETO','OBSERVADO') NOT NULL DEFAULT 'INCOMPLETO',
    `observacion`         text                                                              DEFAULT NULL,
    `fecha_actualizacion` timestamp                                                NULL     DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `portafolio_documentos`
--

CREATE TABLE `portafolio_documentos`
(
    `id_documento`  int(10) UNSIGNED                                                                                        NOT NULL,
    `id_portafolio` int(10) UNSIGNED                                                                                        NOT NULL,
    `tipo`          enum ('SILABO','PLAN_SESION','EVALUACION','INSTRUMENTO','ASISTENCIA','NOTAS','EVIDENCIA','ACTA','OTRO') NOT NULL,
    `titulo`        varchar(180)                                                                                            NOT NULL,
    `archivo`       varchar(255)                                                                                                     DEFAULT NULL,
    `estado`        enum ('PENDIENTE','SUBIDO','APROBADO','OBSERVADO')                                                      NOT NULL DEFAULT 'PENDIENTE',
    `observacion`   text                                                                                                             DEFAULT NULL,
    `fecha_subida`  datetime                                                                                                         DEFAULT NULL,
    `deleted_at`    timestamp                                                                                               NULL     DEFAULT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `programas_estudio`
--

CREATE TABLE `programas_estudio`
(
    `id_programa`         int(10) UNSIGNED           NOT NULL,
    `codigo`              varchar(20)                NOT NULL,
    `nombre`              varchar(150)               NOT NULL,
    `familia_profesional` varchar(120)                        DEFAULT NULL,
    `duracion_ciclos`     tinyint(4)                 NOT NULL DEFAULT 6,
    `estado`              enum ('ACTIVO','INACTIVO') NOT NULL DEFAULT 'ACTIVO'
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

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
    `id_reporte`       int(10) UNSIGNED                                                                                       NOT NULL,
    `id_usuario`       int(10) UNSIGNED                                                                                                DEFAULT NULL,
    `tipo`             enum ('CURSOS','DOCENTES','ESTUDIANTES','HORARIOS','NOTAS','PORTAFOLIO','CONSOLIDADO','IA_PREDICTIVA') NOT NULL,
    `titulo`           varchar(180)                                                                                           NOT NULL,
    `formato`          enum ('PDF','EXCEL','CSV')                                                                             NOT NULL DEFAULT 'PDF',
    `filtros_json`     longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin                                                              DEFAULT NULL CHECK (json_valid(`filtros_json`)),
    `archivo`          varchar(255)                                                                                                    DEFAULT NULL,
    `fecha_generacion` timestamp                                                                                              NOT NULL DEFAULT current_timestamp()
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `reportes_generados`
--

INSERT INTO `reportes_generados` (`id_reporte`, `id_usuario`, `tipo`, `titulo`, `formato`, `filtros_json`, `archivo`,
                                  `fecha_generacion`)
VALUES (1, 1, 'ESTUDIANTES', 'Informe de Estudiantes', 'PDF', '[]',
        'reportes/informe-de-estudiantes-20260706-165317.pdf', '2026-07-06 16:53:23'),
       (2, 1, 'CURSOS', 'Consolidado de Cursos', 'PDF', '[]', 'reportes/consolidado-de-cursos-20260706-165345.pdf',
        '2026-07-06 16:53:46'),
       (3, 1, 'HORARIOS', 'Reporte de Horarios', 'PDF', '[]', 'reportes/reporte-de-horarios-20260706-165417.pdf',
        '2026-07-06 16:54:17');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles`
(
    `id_rol`         int(10) UNSIGNED           NOT NULL,
    `codigo`         varchar(30)                NOT NULL,
    `nombre`         varchar(80)                NOT NULL,
    `descripcion`    varchar(255)                        DEFAULT NULL,
    `estado`         enum ('ACTIVO','INACTIVO') NOT NULL DEFAULT 'ACTIVO',
    `fecha_creacion` timestamp                  NOT NULL DEFAULT current_timestamp()
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id_rol`, `codigo`, `nombre`, `descripcion`, `estado`, `fecha_creacion`)
VALUES (1, 'director', 'Director Academico', 'Acceso global a gestion academica', 'ACTIVO', '2026-07-05 22:42:24'),
       (2, 'jua', 'Jefe de Unidad Academica', 'Revision y aprobacion academica', 'ACTIVO', '2026-07-05 22:42:24'),
       (3, 'coordinador', 'Coordinador Academico', 'Gestion de cursos, docentes y seguimiento', 'ACTIVO',
        '2026-07-05 22:42:24'),
       (4, 'docente', 'Docente', 'Registro de notas, asistencia y portafolio', 'ACTIVO', '2026-07-05 22:42:24');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sesiones_aprendizaje`
--

CREATE TABLE `sesiones_aprendizaje`
(
    `id_sesion`     int(10) UNSIGNED                                        NOT NULL,
    `id_curso`      int(10) UNSIGNED                                        NOT NULL,
    `id_docente`    int(10) UNSIGNED                                        NOT NULL,
    `titulo`        varchar(255)                                            NOT NULL,
    `archivo`       varchar(255)                                            NOT NULL,
    `numero_sesion` int(11)                                                          DEFAULT NULL,
    `estado`        enum ('PENDIENTE','EN_REVISION','APROBADO','RECHAZADO') NOT NULL DEFAULT 'PENDIENTE',
    `fecha_subida`  datetime                                                NOT NULL DEFAULT current_timestamp()
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sessions`
--

CREATE TABLE `sessions`
(
    `id`            varchar(255) NOT NULL,
    `user_id`       bigint(20) UNSIGNED DEFAULT NULL,
    `ip_address`    varchar(45)         DEFAULT NULL,
    `user_agent`    text                DEFAULT NULL,
    `payload`       longtext     NOT NULL,
    `last_activity` int(11)      NOT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`)
VALUES ('1p3U8dKQzQAEZDVw7D2UfU8q0yX00Cjkl0Ag3kdl', 1, '127.0.0.1',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',
        'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiZkp2NDZLeUdjVHZhTUd0YnRxV0tCek03NFFUNjVURVBRcXhEMDJFRSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NzA6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hcGkvZGlyZWN0b3IvZXN0dWRpYW50ZXM/Y2ljbG89SUlJJmlkX3Byb2dyYW1hPTEiO3M6NToicm91dGUiO047fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7fQ==',
        1783367708),
       ('AozpMpdfYZ8lbVJFzwnE7M51Wliy9FKkl4u42dgg', 3, '127.0.0.1', 'curl/8.19.0',
        'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiMTRzMEpuOUg1a0ZSVU9VWGU5M1pGRlk0NEFhaWxYRnpNWlk4elJmQiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDM6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9jb29yZGluYWRvci9kYXNoYm9hcmQiO3M6NToicm91dGUiO3M6MjE6ImNvb3JkaW5hZG9yLmRhc2hib2FyZCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjM7fQ==',
        1783362365),
       ('CozQZWlgCC9NDQgmPGIc0xhdhR4QImBj1EMrmSVC', 8, '127.0.0.1',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0',
        'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiVjFHS3NLNXNPSEpFQmJ1c09ieVhsUndpV0ZQUmVtN2dwMTRobVZ0eiI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjQwOiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvYXBpL2RvY2VudGUvY3Vyc29zIjtzOjU6InJvdXRlIjtOO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aTo4O30=',
        1783359818),
       ('dDMCw8GQKQi9g0khm2p2AZShaVZtKMm2u4Nzvmpe', 1, '127.0.0.1', 'curl/8.19.0',
        'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiNm1wSGZZN0NDU3JSVDVhOXA3RmZ0MHdJRmZ2Rk4zT2tkVGk0RGt1TiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hcGkvZGlyZWN0b3IvY3Vyc29zIjtzOjU6InJvdXRlIjtOO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO30=',
        1783362406),
       ('EyIrvc1HJ4VKab4Dp9wcyvu3v57joc09T2bJghTo', 12, '127.0.0.1', 'curl/8.19.0',
        'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiZ0FsUVprQXF5NUdueXBDR1BaYjh3VTZ5SktvR3lhbm44TWxVSG8xNSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDM6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9jb29yZGluYWRvci9kYXNoYm9hcmQiO3M6NToicm91dGUiO3M6MjE6ImNvb3JkaW5hZG9yLmRhc2hib2FyZCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjEyO30=',
        1783362338),
       ('LbPX1ZEvqXOWH6mqc60D0dUk8M7YAqUpUtOxkHuV', 13, '127.0.0.1', 'curl/8.19.0',
        'YTo0OntzOjY6Il90b2tlbiI7czo0MDoidWpESWhQRDIxbFpUM1RIYm1FcFZzWU5xUjdJemFWcldKcDB0cW45RiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDQ6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hcGkvY29vcmRpbmFkb3IvY3Vyc29zIjtzOjU6InJvdXRlIjtOO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxMzt9',
        1783362382),
       ('S0PRoqlTCc3XnA1WhgMgvKjcCpjZ6rCB9M41PPlt', NULL, '127.0.0.1',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',
        'YToyOntzOjY6Il90b2tlbiI7czo0MDoienIyaDhTYkdEMWhURlZ0RDU3RHVnS2x1bmxmMzlWUGx3aDJ1ZGNxTSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',
        1783361195),
       ('TBEFlHnhAcoLqrYPbp6fgpcPNGVfDr6XnQK3se1T', 10, '127.0.0.1',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',
        'YTo0OntzOjY6Il90b2tlbiI7czo0MDoicHBqc2QyMm9kU1ZNcENpZW1ERWJCWjQ3UXBqakV3bTNWS2x1REh1MCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NTk6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hcGkvY29vcmRpbmFkb3IvZXN0dWRpYW50ZXM/Y2ljbG89SUlJIjtzOjU6InJvdXRlIjtOO31zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxMDt9',
        1783367907);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `silabo_estructuras`
--

CREATE TABLE `silabo_estructuras`
(
    `id_estructura`       int(10) UNSIGNED NOT NULL,
    `codigo`              varchar(50)      NOT NULL,
    `nombre`              varchar(180)     NOT NULL,
    `version`             varchar(30)      NOT NULL,
    `descripcion`         text                      DEFAULT NULL,
    `activo`              tinyint(1)       NOT NULL DEFAULT 1,
    `fecha_creacion`      timestamp        NOT NULL DEFAULT current_timestamp(),
    `fecha_actualizacion` timestamp        NULL     DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `silabo_estructura_criterios`
--

CREATE TABLE `silabo_estructura_criterios`
(
    `id_criterio`         int(10) UNSIGNED NOT NULL,
    `id_estructura`       int(10) UNSIGNED NOT NULL,
    `orden`               tinyint(4)       NOT NULL,
    `seccion`             varchar(120)     NOT NULL,
    `descripcion`         text             NOT NULL,
    `campos_json`         text             NOT NULL,
    `validaciones_json`   text             NOT NULL,
    `peso`                decimal(5, 2)    NOT NULL DEFAULT 0.00,
    `obligatorio`         tinyint(1)       NOT NULL DEFAULT 1,
    `fecha_creacion`      timestamp        NOT NULL DEFAULT current_timestamp(),
    `fecha_actualizacion` timestamp        NULL     DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `solicitudes_password`
--

CREATE TABLE `solicitudes_password`
(
    `id_solicitud`       int(10) UNSIGNED                          NOT NULL,
    `id_usuario`         int(10) UNSIGNED                          NOT NULL,
    `id_usuario_atiende` int(10) UNSIGNED                                   DEFAULT NULL,
    `motivo`             varchar(255)                                       DEFAULT NULL,
    `estado`             enum ('PENDIENTE','APROBADA','RECHAZADA') NOT NULL DEFAULT 'PENDIENTE',
    `motivo_rechazo`     varchar(255)                                       DEFAULT NULL,
    `ip_solicitud`       varchar(45)                                        DEFAULT NULL,
    `fecha_solicitud`    timestamp                                 NOT NULL DEFAULT current_timestamp(),
    `fecha_atencion`     datetime                                           DEFAULT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `solicitudes_password`
--

INSERT INTO `solicitudes_password` (`id_solicitud`, `id_usuario`, `id_usuario_atiende`, `motivo`, `estado`,
                                    `motivo_rechazo`, `ip_solicitud`, `fecha_solicitud`, `fecha_atencion`)
VALUES (1, 2, 1, 'me olvide', 'APROBADA', NULL, '127.0.0.1', '2026-07-06 17:11:23', '2026-07-06 17:12:04');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios`
(
    `id_usuario`                   int(10) UNSIGNED                       NOT NULL,
    `id_rol`                       int(10) UNSIGNED                       NOT NULL,
    `id_programa`                  int(10) UNSIGNED                                DEFAULT NULL,
    `usuario`                      varchar(80)                            NOT NULL,
    `correo`                       varchar(150)                           NOT NULL,
    `password_hash`                varchar(255)                           NOT NULL,
    `password_algoritmo`           varchar(40)                            NOT NULL DEFAULT 'sha256-demo',
    `cambio_password_obligatorio`  tinyint(1)                             NOT NULL DEFAULT 0,
    `nombres`                      varchar(120)                           NOT NULL,
    `apellidos`                    varchar(120)                                    DEFAULT NULL,
    `dni`                          char(8)                                         DEFAULT NULL,
    `telefono`                     varchar(20)                                     DEFAULT NULL,
    `estado`                       enum ('ACTIVO','INACTIVO','BLOQUEADO') NOT NULL DEFAULT 'ACTIVO',
    `ultimo_acceso`                datetime                                        DEFAULT NULL,
    `otp_verified_at`              timestamp                              NULL     DEFAULT NULL,
    `otp_last_verified_ip`         varchar(45)                                     DEFAULT NULL,
    `otp_last_verified_user_agent` text                                            DEFAULT NULL,
    `fecha_creacion`               timestamp                              NOT NULL DEFAULT current_timestamp(),
    `fecha_actualizacion`          timestamp                              NULL     DEFAULT NULL ON UPDATE current_timestamp(),
    `deleted_at`                   timestamp                              NULL     DEFAULT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `id_rol`, `id_programa`, `usuario`, `correo`, `password_hash`,
                        `password_algoritmo`, `cambio_password_obligatorio`, `nombres`, `apellidos`, `dni`, `telefono`,
                        `estado`, `ultimo_acceso`, `otp_verified_at`, `otp_last_verified_ip`,
                        `otp_last_verified_user_agent`, `fecha_creacion`, `fecha_actualizacion`, `deleted_at`)
VALUES (1, 1, NULL, 'director', 'director@istv.edu.pe', '$2y$12$35PDUH/xfCHkBXsAYZXDEOzuXBf2rSJpI01Wgyb6qQq3Mu3hZNZPq',
        'bcrypt', 0, 'Director', 'Academico', NULL, NULL, 'ACTIVO', '2026-07-06 18:13:58', '2026-07-06 03:51:20',
        '127.0.0.1',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',
        '2026-07-05 22:42:25', '2026-07-06 23:13:58', NULL),
       (2, 2, NULL, 'maria', 'damarissoliscuno631@gmail.com',
        '$2y$12$BDD9UHJsBIuASGeiBckMK.z.gyRJNKk.mwJi2KCgAkRSZblxcOezm', 'bcrypt', 0, 'Maria Antonieta', 'MENDOZA TECSI',
        '75937538', '957446363', 'ACTIVO', '2026-07-06 17:13:15', '2026-07-06 22:13:15', '127.0.0.1',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',
        '2026-07-05 22:42:25', '2026-07-06 22:13:36', NULL),
       (3, 3, 1, 'coordinador', 'coordinador@istv.edu.pe',
        '$2y$12$TqgwYz9PGukMKhNR8SvZ0ul7CIFRwJaBaY1EqsSbNl3pMUNdLix1.', 'bcrypt', 0, 'Coordinador', 'Academico', NULL,
        NULL, 'ACTIVO', '2026-07-06 18:22:51', '2026-07-06 23:21:45', NULL, NULL, '2026-07-05 22:42:25',
        '2026-07-06 23:22:51', NULL),
       (4, 4, NULL, 'hernan', 'hernaniestv@gmail.com', '$2y$12$CsJe.r3/bmNQb6.T3Oc0vODB/Z9LLJtsmpbvFIVxjcEgHP2C/0lGO',
        'bcrypt', 1, 'Hernan', 'Palomino  tunqui', NULL, '957446363', 'ACTIVO', NULL, NULL, NULL, NULL,
        '2026-07-06 19:58:23', '2026-07-06 19:58:23', NULL),
       (5, 4, NULL, 'diana', 'damarissoliscuno@gmail.com',
        '$2y$12$TRjEa6WBprw7hdMwddhMnuNGM45a0v6.iIGNnx0X.Co568ZcTXNsG', 'bcrypt', 1, 'Diana', 'huaylla tunti',
        '84773474', '947473667', 'ACTIVO', NULL, NULL, NULL, NULL, '2026-07-06 20:06:37', '2026-07-06 20:06:37', NULL),
       (6, 4, NULL, 'charles', 'chincheroquispeyesenia@gmail.com',
        '$2y$12$wfZC4FxtagKC7S2xCrJNFe1q/A8zV3/ExN1YlOJurCF9RWKVZb4nS', 'bcrypt', 1, 'Jhon charles', 'barrientos ferro',
        '57565654', '957757456', 'ACTIVO', NULL, NULL, NULL, NULL, '2026-07-06 20:09:52', '2026-07-06 21:56:27', NULL),
       (8, 4, NULL, 'pavel', 'chincheroyesenia@gmail.com',
        '$2y$12$vxQk1/xhUMYebg5XPJ7Zi.Upzl5csvnG.iEhbRmYqR0YK7tDS3iAu', 'bcrypt', 0, 'pavel lech', 'valer medina',
        '65765756', '94544365', 'ACTIVO', '2026-07-06 17:43:02', '2026-07-06 22:43:02', '127.0.0.1',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0',
        '2026-07-06 20:18:51', '2026-07-06 22:43:37', NULL),
       (9, 4, NULL, 'fredy', 'fredyiestv@gmail.com', '$2y$12$wG9SexiZDNE7Ro3aYwXciOq4jNB.mnKPlGxC7FlHO1KNm/GM8yftK',
        'bcrypt', 1, 'Fredy', 'quispe', '73535345', '94634646', 'ACTIVO', NULL, NULL, NULL, NULL, '2026-07-06 20:23:25',
        '2026-07-06 20:23:25', NULL),
       (10, 3, 1, 'rosaluz', 'gcielahuamani@gmail.com', '$2y$12$2suOaz.LV9OoLYNYjbyWJeGO2yDfkc6gP7WyLk3H0EG4U0BsFFFUi',
        'bcrypt', 0, 'Rosa luz', 'jara villanueva', '64326464', '946644343', 'ACTIVO', '2026-07-06 18:29:31',
        '2026-07-06 22:15:58', '127.0.0.1',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',
        '2026-07-06 20:26:16', '2026-07-06 23:29:31', NULL),
       (14, 4, NULL, 'fernando', 'fernandoev@gmail.com', '$2y$12$9dp1PbYij/QliY1IA7T5b.KxsMy.Lz3YhLxDZBeAZaBD0obCqvRhO',
        'bcrypt', 1, 'Fernando', 'Cornejo', '87653533', '928262524', 'ACTIVO', NULL, NULL, NULL, NULL,
        '2026-07-06 23:45:29', '2026-07-06 23:45:29', NULL),
       (15, 4, NULL, 'emiliano', 'emilianoiestv@gmail.com',
        '$2y$12$U4O86cVAfreUE6EfNQIJGOHeVCtyzr.YyJB7ciMpild43S5FE4e4u', 'bcrypt', 1, 'Emiliano', 'mendoza', '74673465',
        '946446645', 'ACTIVO', NULL, NULL, NULL, NULL, '2026-07-07 00:02:11', '2026-07-07 00:02:11', NULL),
       (16, 4, NULL, 'vladimir', 'vladimiriestv@gmail.com',
        '$2y$12$hNZ7tXkGH8.Djaj0O8KZ5uvhCpiXL4gzOJOpI4hQO1GUa6H22ntCO', 'bcrypt', 1, 'Vladimir', 'florez', '95544335',
        '955543322', 'ACTIVO', NULL, NULL, NULL, NULL, '2026-07-07 00:05:20', '2026-07-07 00:05:20', NULL);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `alertas_academicas`
--
ALTER TABLE `alertas_academicas`
    ADD PRIMARY KEY (`id_alerta`),
    ADD UNIQUE KEY `uk_alertas_contexto` (`id_estudiante`, `tipo`, `titulo`),
    ADD KEY `alertas_academicas_estado_index` (`estado`),
    ADD KEY `alertas_academicas_id_docente_foreign` (`id_docente`),
    ADD KEY `alertas_academicas_id_curso_foreign` (`id_curso`);

--
-- Indices de la tabla `asistencia_detalle`
--
ALTER TABLE `asistencia_detalle`
    ADD PRIMARY KEY (`id_asistencia`),
    ADD UNIQUE KEY `uk_asistencia_sesion_estudiante` (`id_sesion`, `id_estudiante`),
    ADD KEY `asistencia_detalle_id_estudiante_foreign` (`id_estudiante`);

--
-- Indices de la tabla `asistencia_sesiones`
--
ALTER TABLE `asistencia_sesiones`
    ADD PRIMARY KEY (`id_sesion`),
    ADD KEY `asistencia_sesiones_id_curso_foreign` (`id_curso`),
    ADD KEY `asistencia_sesiones_id_docente_foreign` (`id_docente`),
    ADD KEY `asistencia_sesiones_id_horario_foreign` (`id_horario`),
    ADD KEY `asistencia_sesiones_id_periodo_foreign` (`id_periodo`);

--
-- Indices de la tabla `auditoria_sistema`
--
ALTER TABLE `auditoria_sistema`
    ADD PRIMARY KEY (`id_auditoria`),
    ADD KEY `auditoria_sistema_id_usuario_foreign` (`id_usuario`);

--
-- Indices de la tabla `aulas`
--
ALTER TABLE `aulas`
    ADD PRIMARY KEY (`id_aula`),
    ADD UNIQUE KEY `aulas_codigo_unique` (`codigo`);

--
-- Indices de la tabla `cache`
--
ALTER TABLE `cache`
    ADD PRIMARY KEY (`key`),
    ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indices de la tabla `cache_locks`
--
ALTER TABLE `cache_locks`
    ADD PRIMARY KEY (`key`),
    ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indices de la tabla `configuracion_sistema`
--
ALTER TABLE `configuracion_sistema`
    ADD PRIMARY KEY (`id_configuracion`),
    ADD UNIQUE KEY `configuracion_sistema_clave_unique` (`clave`);

--
-- Indices de la tabla `cursos`
--
ALTER TABLE `cursos`
    ADD PRIMARY KEY (`id_curso`),
    ADD KEY `cursos_id_docente_foreign` (`id_docente`),
    ADD KEY `cursos_id_programa_index` (`id_programa`),
    ADD KEY `cursos_tipo_curso_index` (`tipo_curso`);

--
-- Indices de la tabla `docentes`
--
ALTER TABLE `docentes`
    ADD PRIMARY KEY (`id_docente`),
    ADD UNIQUE KEY `docentes_codigo_docente_unique` (`codigo_docente`),
    ADD UNIQUE KEY `docentes_id_usuario_unique` (`id_usuario`),
    ADD KEY `docentes_tipo_docente_index` (`tipo_docente`);

--
-- Indices de la tabla `docente_disponibilidades`
--
ALTER TABLE `docente_disponibilidades`
    ADD PRIMARY KEY (`id_disponibilidad`),
    ADD KEY `docente_disponibilidades_id_docente_dia_index` (`id_docente`, `dia`);

--
-- Indices de la tabla `docente_programa`
--
ALTER TABLE `docente_programa`
    ADD PRIMARY KEY (`id_docente_programa`),
    ADD UNIQUE KEY `uk_docente_programa` (`id_docente`, `id_programa`),
    ADD KEY `docente_programa_id_programa_estado_index` (`id_programa`, `estado`),
    ADD KEY `docente_programa_id_docente_estado_index` (`id_docente`, `estado`);

--
-- Indices de la tabla `estudiantes`
--
ALTER TABLE `estudiantes`
    ADD PRIMARY KEY (`id_estudiante`),
    ADD UNIQUE KEY `estudiantes_codigo_estudiante_unique` (`codigo_estudiante`),
    ADD UNIQUE KEY `estudiantes_dni_unique` (`dni`),
    ADD KEY `estudiantes_id_programa_foreign` (`id_programa`);

--
-- Indices de la tabla `failed_jobs`
--
ALTER TABLE `failed_jobs`
    ADD PRIMARY KEY (`id`),
    ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indices de la tabla `horarios`
--
ALTER TABLE `horarios`
    ADD PRIMARY KEY (`id_horario`),
    ADD KEY `horarios_id_curso_foreign` (`id_curso`),
    ADD KEY `horarios_id_periodo_foreign` (`id_periodo`),
    ADD KEY `idx_horarios_docente_slot` (`id_docente`, `dia`, `hora_inicio`, `hora_fin`),
    ADD KEY `idx_horarios_aula_slot` (`id_aula`, `dia`, `hora_inicio`, `hora_fin`),
    ADD KEY `idx_horarios_programa_semestre_dia` (`id_programa`, `semestre`, `dia`);

--
-- Indices de la tabla `horarios_ia_generados`
--
ALTER TABLE `horarios_ia_generados`
    ADD PRIMARY KEY (`id_generacion`),
    ADD KEY `horarios_ia_generados_id_usuario_foreign` (`id_usuario`),
    ADD KEY `horarios_ia_generados_id_periodo_foreign` (`id_periodo`);

--
-- Indices de la tabla `ia_predicciones`
--
ALTER TABLE `ia_predicciones`
    ADD PRIMARY KEY (`id_prediccion`),
    ADD KEY `ia_predicciones_nivel_index` (`nivel`),
    ADD KEY `ia_predicciones_id_estudiante_foreign` (`id_estudiante`),
    ADD KEY `ia_predicciones_id_curso_foreign` (`id_curso`),
    ADD KEY `ia_predicciones_id_periodo_foreign` (`id_periodo`);

--
-- Indices de la tabla `jobs`
--
ALTER TABLE `jobs`
    ADD PRIMARY KEY (`id`),
    ADD KEY `jobs_queue_index` (`queue`);

--
-- Indices de la tabla `job_batches`
--
ALTER TABLE `job_batches`
    ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `login_otps`
--
ALTER TABLE `login_otps`
    ADD PRIMARY KEY (`id`),
    ADD KEY `login_otps_id_usuario_index` (`id_usuario`),
    ADD KEY `login_otps_email_index` (`email`),
    ADD KEY `login_otps_expires_at_index` (`expires_at`),
    ADD KEY `login_otps_used_at_index` (`used_at`);

--
-- Indices de la tabla `matriculas`
--
ALTER TABLE `matriculas`
    ADD PRIMARY KEY (`id_matricula`),
    ADD UNIQUE KEY `uk_matriculas_estudiante_periodo` (`id_estudiante`, `id_periodo`),
    ADD KEY `matriculas_id_periodo_foreign` (`id_periodo`);

--
-- Indices de la tabla `matricula_cursos`
--
ALTER TABLE `matricula_cursos`
    ADD PRIMARY KEY (`id_matricula_curso`),
    ADD UNIQUE KEY `uk_matricula_curso` (`id_matricula`, `id_curso`),
    ADD KEY `matricula_cursos_id_curso_foreign` (`id_curso`);

--
-- Indices de la tabla `mensajes`
--
ALTER TABLE `mensajes`
    ADD PRIMARY KEY (`id_mensaje`),
    ADD KEY `mensajes_id_remitente_foreign` (`id_remitente`),
    ADD KEY `mensajes_id_destinatario_foreign` (`id_destinatario`);

--
-- Indices de la tabla `migrations`
--
ALTER TABLE `migrations`
    ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `notas`
--
ALTER TABLE `notas`
    ADD PRIMARY KEY (`id_nota`),
    ADD UNIQUE KEY `uk_notas_matricula_unidad` (`id_matricula_curso`, `unidad`);

--
-- Indices de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
    ADD PRIMARY KEY (`id_notificacion`),
    ADD KEY `notificaciones_tipo_index` (`tipo`),
    ADD KEY `notificaciones_id_usuario_foreign` (`id_usuario`);

--
-- Indices de la tabla `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
    ADD PRIMARY KEY (`email`);

--
-- Indices de la tabla `periodos_academicos`
--
ALTER TABLE `periodos_academicos`
    ADD PRIMARY KEY (`id_periodo`),
    ADD UNIQUE KEY `periodos_academicos_codigo_unique` (`codigo`);

--
-- Indices de la tabla `portafolio_docente`
--
ALTER TABLE `portafolio_docente`
    ADD PRIMARY KEY (`id_portafolio`),
    ADD UNIQUE KEY `uk_portafolio_docente_curso_periodo` (`id_docente`, `id_curso`, `id_periodo`),
    ADD KEY `portafolio_docente_id_curso_foreign` (`id_curso`),
    ADD KEY `portafolio_docente_id_periodo_foreign` (`id_periodo`);

--
-- Indices de la tabla `portafolio_documentos`
--
ALTER TABLE `portafolio_documentos`
    ADD PRIMARY KEY (`id_documento`),
    ADD UNIQUE KEY `uk_portafolio_documento_tipo` (`id_portafolio`, `tipo`, `titulo`);

--
-- Indices de la tabla `programas_estudio`
--
ALTER TABLE `programas_estudio`
    ADD PRIMARY KEY (`id_programa`),
    ADD UNIQUE KEY `programas_estudio_codigo_unique` (`codigo`);

--
-- Indices de la tabla `reportes_generados`
--
ALTER TABLE `reportes_generados`
    ADD PRIMARY KEY (`id_reporte`),
    ADD KEY `reportes_generados_id_usuario_foreign` (`id_usuario`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
    ADD PRIMARY KEY (`id_rol`),
    ADD UNIQUE KEY `roles_codigo_unique` (`codigo`);

--
-- Indices de la tabla `sesiones_aprendizaje`
--
ALTER TABLE `sesiones_aprendizaje`
    ADD PRIMARY KEY (`id_sesion`),
    ADD KEY `fk_sa_curso` (`id_curso`),
    ADD KEY `fk_sa_docente` (`id_docente`);

--
-- Indices de la tabla `sessions`
--
ALTER TABLE `sessions`
    ADD PRIMARY KEY (`id`),
    ADD KEY `sessions_user_id_index` (`user_id`),
    ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indices de la tabla `silabo_estructuras`
--
ALTER TABLE `silabo_estructuras`
    ADD PRIMARY KEY (`id_estructura`),
    ADD UNIQUE KEY `silabo_estructuras_codigo_unique` (`codigo`);

--
-- Indices de la tabla `silabo_estructura_criterios`
--
ALTER TABLE `silabo_estructura_criterios`
    ADD PRIMARY KEY (`id_criterio`),
    ADD UNIQUE KEY `uk_silabo_criterio_seccion` (`id_estructura`, `seccion`);

--
-- Indices de la tabla `solicitudes_password`
--
ALTER TABLE `solicitudes_password`
    ADD PRIMARY KEY (`id_solicitud`),
    ADD KEY `solicitudes_password_id_usuario_atiende_foreign` (`id_usuario_atiende`),
    ADD KEY `solicitudes_password_id_usuario_estado_index` (`id_usuario`, `estado`),
    ADD KEY `solicitudes_password_estado_index` (`estado`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
    ADD PRIMARY KEY (`id_usuario`),
    ADD UNIQUE KEY `usuarios_usuario_unique` (`usuario`),
    ADD UNIQUE KEY `usuarios_correo_unique` (`correo`),
    ADD KEY `usuarios_id_rol_foreign` (`id_rol`),
    ADD KEY `usuarios_id_programa_foreign` (`id_programa`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `alertas_academicas`
--
ALTER TABLE `alertas_academicas`
    MODIFY `id_alerta` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `asistencia_detalle`
--
ALTER TABLE `asistencia_detalle`
    MODIFY `id_asistencia` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `asistencia_sesiones`
--
ALTER TABLE `asistencia_sesiones`
    MODIFY `id_sesion` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `auditoria_sistema`
--
ALTER TABLE `auditoria_sistema`
    MODIFY `id_auditoria` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    AUTO_INCREMENT = 21;

--
-- AUTO_INCREMENT de la tabla `aulas`
--
ALTER TABLE `aulas`
    MODIFY `id_aula` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    AUTO_INCREMENT = 6;

--
-- AUTO_INCREMENT de la tabla `configuracion_sistema`
--
ALTER TABLE `configuracion_sistema`
    MODIFY `id_configuracion` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    AUTO_INCREMENT = 8;

--
-- AUTO_INCREMENT de la tabla `cursos`
--
ALTER TABLE `cursos`
    MODIFY `id_curso` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    AUTO_INCREMENT = 38;

--
-- AUTO_INCREMENT de la tabla `docentes`
--
ALTER TABLE `docentes`
    MODIFY `id_docente` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    AUTO_INCREMENT = 11;

--
-- AUTO_INCREMENT de la tabla `docente_disponibilidades`
--
ALTER TABLE `docente_disponibilidades`
    MODIFY `id_disponibilidad` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `docente_programa`
--
ALTER TABLE `docente_programa`
    MODIFY `id_docente_programa` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    AUTO_INCREMENT = 10;

--
-- AUTO_INCREMENT de la tabla `estudiantes`
--
ALTER TABLE `estudiantes`
    MODIFY `id_estudiante` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    AUTO_INCREMENT = 82;

--
-- AUTO_INCREMENT de la tabla `failed_jobs`
--
ALTER TABLE `failed_jobs`
    MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `horarios`
--
ALTER TABLE `horarios`
    MODIFY `id_horario` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `horarios_ia_generados`
--
ALTER TABLE `horarios_ia_generados`
    MODIFY `id_generacion` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    AUTO_INCREMENT = 3;

--
-- AUTO_INCREMENT de la tabla `ia_predicciones`
--
ALTER TABLE `ia_predicciones`
    MODIFY `id_prediccion` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `jobs`
--
ALTER TABLE `jobs`
    MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `login_otps`
--
ALTER TABLE `login_otps`
    MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    AUTO_INCREMENT = 7;

--
-- AUTO_INCREMENT de la tabla `matriculas`
--
ALTER TABLE `matriculas`
    MODIFY `id_matricula` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `matricula_cursos`
--
ALTER TABLE `matricula_cursos`
    MODIFY `id_matricula_curso` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `mensajes`
--
ALTER TABLE `mensajes`
    MODIFY `id_mensaje` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `migrations`
--
ALTER TABLE `migrations`
    MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    AUTO_INCREMENT = 45;

--
-- AUTO_INCREMENT de la tabla `notas`
--
ALTER TABLE `notas`
    MODIFY `id_nota` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
    MODIFY `id_notificacion` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    AUTO_INCREMENT = 2;

--
-- AUTO_INCREMENT de la tabla `periodos_academicos`
--
ALTER TABLE `periodos_academicos`
    MODIFY `id_periodo` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    AUTO_INCREMENT = 5;

--
-- AUTO_INCREMENT de la tabla `portafolio_docente`
--
ALTER TABLE `portafolio_docente`
    MODIFY `id_portafolio` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `portafolio_documentos`
--
ALTER TABLE `portafolio_documentos`
    MODIFY `id_documento` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `programas_estudio`
--
ALTER TABLE `programas_estudio`
    MODIFY `id_programa` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    AUTO_INCREMENT = 6;

--
-- AUTO_INCREMENT de la tabla `reportes_generados`
--
ALTER TABLE `reportes_generados`
    MODIFY `id_reporte` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    AUTO_INCREMENT = 4;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
    MODIFY `id_rol` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    AUTO_INCREMENT = 5;

--
-- AUTO_INCREMENT de la tabla `sesiones_aprendizaje`
--
ALTER TABLE `sesiones_aprendizaje`
    MODIFY `id_sesion` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `silabo_estructuras`
--
ALTER TABLE `silabo_estructuras`
    MODIFY `id_estructura` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `silabo_estructura_criterios`
--
ALTER TABLE `silabo_estructura_criterios`
    MODIFY `id_criterio` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `solicitudes_password`
--
ALTER TABLE `solicitudes_password`
    MODIFY `id_solicitud` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    AUTO_INCREMENT = 2;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
    MODIFY `id_usuario` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    AUTO_INCREMENT = 17;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `alertas_academicas`
--
ALTER TABLE `alertas_academicas`
    ADD CONSTRAINT `alertas_academicas_id_curso_foreign` FOREIGN KEY (`id_curso`) REFERENCES `cursos` (`id_curso`) ON DELETE SET NULL,
    ADD CONSTRAINT `alertas_academicas_id_docente_foreign` FOREIGN KEY (`id_docente`) REFERENCES `docentes` (`id_docente`) ON DELETE SET NULL,
    ADD CONSTRAINT `alertas_academicas_id_estudiante_foreign` FOREIGN KEY (`id_estudiante`) REFERENCES `estudiantes` (`id_estudiante`) ON DELETE SET NULL;

--
-- Filtros para la tabla `asistencia_detalle`
--
ALTER TABLE `asistencia_detalle`
    ADD CONSTRAINT `asistencia_detalle_id_estudiante_foreign` FOREIGN KEY (`id_estudiante`) REFERENCES `estudiantes` (`id_estudiante`) ON DELETE CASCADE,
    ADD CONSTRAINT `asistencia_detalle_id_sesion_foreign` FOREIGN KEY (`id_sesion`) REFERENCES `asistencia_sesiones` (`id_sesion`) ON DELETE CASCADE;

--
-- Filtros para la tabla `asistencia_sesiones`
--
ALTER TABLE `asistencia_sesiones`
    ADD CONSTRAINT `asistencia_sesiones_id_curso_foreign` FOREIGN KEY (`id_curso`) REFERENCES `cursos` (`id_curso`),
    ADD CONSTRAINT `asistencia_sesiones_id_docente_foreign` FOREIGN KEY (`id_docente`) REFERENCES `docentes` (`id_docente`),
    ADD CONSTRAINT `asistencia_sesiones_id_horario_foreign` FOREIGN KEY (`id_horario`) REFERENCES `horarios` (`id_horario`) ON DELETE SET NULL,
    ADD CONSTRAINT `asistencia_sesiones_id_periodo_foreign` FOREIGN KEY (`id_periodo`) REFERENCES `periodos_academicos` (`id_periodo`);

--
-- Filtros para la tabla `auditoria_sistema`
--
ALTER TABLE `auditoria_sistema`
    ADD CONSTRAINT `auditoria_sistema_id_usuario_foreign` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE SET NULL;

--
-- Filtros para la tabla `cursos`
--
ALTER TABLE `cursos`
    ADD CONSTRAINT `cursos_id_docente_foreign` FOREIGN KEY (`id_docente`) REFERENCES `docentes` (`id_docente`) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT `cursos_id_programa_foreign` FOREIGN KEY (`id_programa`) REFERENCES `programas_estudio` (`id_programa`) ON DELETE SET NULL;

--
-- Filtros para la tabla `docentes`
--
ALTER TABLE `docentes`
    ADD CONSTRAINT `docentes_id_usuario_foreign` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `docente_disponibilidades`
--
ALTER TABLE `docente_disponibilidades`
    ADD CONSTRAINT `docente_disponibilidades_id_docente_foreign` FOREIGN KEY (`id_docente`) REFERENCES `docentes` (`id_docente`) ON DELETE CASCADE;

--
-- Filtros para la tabla `docente_programa`
--
ALTER TABLE `docente_programa`
    ADD CONSTRAINT `docente_programa_id_docente_foreign` FOREIGN KEY (`id_docente`) REFERENCES `docentes` (`id_docente`) ON DELETE CASCADE,
    ADD CONSTRAINT `docente_programa_id_programa_foreign` FOREIGN KEY (`id_programa`) REFERENCES `programas_estudio` (`id_programa`) ON DELETE CASCADE;

--
-- Filtros para la tabla `estudiantes`
--
ALTER TABLE `estudiantes`
    ADD CONSTRAINT `estudiantes_id_programa_foreign` FOREIGN KEY (`id_programa`) REFERENCES `programas_estudio` (`id_programa`);

--
-- Filtros para la tabla `horarios`
--
ALTER TABLE `horarios`
    ADD CONSTRAINT `horarios_id_aula_foreign` FOREIGN KEY (`id_aula`) REFERENCES `aulas` (`id_aula`) ON DELETE SET NULL,
    ADD CONSTRAINT `horarios_id_curso_foreign` FOREIGN KEY (`id_curso`) REFERENCES `cursos` (`id_curso`),
    ADD CONSTRAINT `horarios_id_docente_foreign` FOREIGN KEY (`id_docente`) REFERENCES `docentes` (`id_docente`),
    ADD CONSTRAINT `horarios_id_periodo_foreign` FOREIGN KEY (`id_periodo`) REFERENCES `periodos_academicos` (`id_periodo`) ON DELETE SET NULL,
    ADD CONSTRAINT `horarios_id_programa_foreign` FOREIGN KEY (`id_programa`) REFERENCES `programas_estudio` (`id_programa`) ON DELETE SET NULL;

--
-- Filtros para la tabla `horarios_ia_generados`
--
ALTER TABLE `horarios_ia_generados`
    ADD CONSTRAINT `horarios_ia_generados_id_periodo_foreign` FOREIGN KEY (`id_periodo`) REFERENCES `periodos_academicos` (`id_periodo`) ON DELETE SET NULL,
    ADD CONSTRAINT `horarios_ia_generados_id_usuario_foreign` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE SET NULL;

--
-- Filtros para la tabla `ia_predicciones`
--
ALTER TABLE `ia_predicciones`
    ADD CONSTRAINT `ia_predicciones_id_curso_foreign` FOREIGN KEY (`id_curso`) REFERENCES `cursos` (`id_curso`) ON DELETE SET NULL,
    ADD CONSTRAINT `ia_predicciones_id_estudiante_foreign` FOREIGN KEY (`id_estudiante`) REFERENCES `estudiantes` (`id_estudiante`) ON DELETE SET NULL,
    ADD CONSTRAINT `ia_predicciones_id_periodo_foreign` FOREIGN KEY (`id_periodo`) REFERENCES `periodos_academicos` (`id_periodo`) ON DELETE SET NULL;

--
-- Filtros para la tabla `login_otps`
--
ALTER TABLE `login_otps`
    ADD CONSTRAINT `login_otps_id_usuario_foreign` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `matriculas`
--
ALTER TABLE `matriculas`
    ADD CONSTRAINT `matriculas_id_estudiante_foreign` FOREIGN KEY (`id_estudiante`) REFERENCES `estudiantes` (`id_estudiante`) ON DELETE CASCADE,
    ADD CONSTRAINT `matriculas_id_periodo_foreign` FOREIGN KEY (`id_periodo`) REFERENCES `periodos_academicos` (`id_periodo`);

--
-- Filtros para la tabla `matricula_cursos`
--
ALTER TABLE `matricula_cursos`
    ADD CONSTRAINT `matricula_cursos_id_curso_foreign` FOREIGN KEY (`id_curso`) REFERENCES `cursos` (`id_curso`),
    ADD CONSTRAINT `matricula_cursos_id_matricula_foreign` FOREIGN KEY (`id_matricula`) REFERENCES `matriculas` (`id_matricula`) ON DELETE CASCADE;

--
-- Filtros para la tabla `mensajes`
--
ALTER TABLE `mensajes`
    ADD CONSTRAINT `mensajes_id_destinatario_foreign` FOREIGN KEY (`id_destinatario`) REFERENCES `usuarios` (`id_usuario`),
    ADD CONSTRAINT `mensajes_id_remitente_foreign` FOREIGN KEY (`id_remitente`) REFERENCES `usuarios` (`id_usuario`);

--
-- Filtros para la tabla `notas`
--
ALTER TABLE `notas`
    ADD CONSTRAINT `notas_id_matricula_curso_foreign` FOREIGN KEY (`id_matricula_curso`) REFERENCES `matricula_cursos` (`id_matricula_curso`) ON DELETE CASCADE;

--
-- Filtros para la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
    ADD CONSTRAINT `notificaciones_id_usuario_foreign` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `portafolio_docente`
--
ALTER TABLE `portafolio_docente`
    ADD CONSTRAINT `portafolio_docente_id_curso_foreign` FOREIGN KEY (`id_curso`) REFERENCES `cursos` (`id_curso`),
    ADD CONSTRAINT `portafolio_docente_id_docente_foreign` FOREIGN KEY (`id_docente`) REFERENCES `docentes` (`id_docente`),
    ADD CONSTRAINT `portafolio_docente_id_periodo_foreign` FOREIGN KEY (`id_periodo`) REFERENCES `periodos_academicos` (`id_periodo`);

--
-- Filtros para la tabla `portafolio_documentos`
--
ALTER TABLE `portafolio_documentos`
    ADD CONSTRAINT `portafolio_documentos_id_portafolio_foreign` FOREIGN KEY (`id_portafolio`) REFERENCES `portafolio_docente` (`id_portafolio`) ON DELETE CASCADE;

--
-- Filtros para la tabla `reportes_generados`
--
ALTER TABLE `reportes_generados`
    ADD CONSTRAINT `reportes_generados_id_usuario_foreign` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE SET NULL;

--
-- Filtros para la tabla `sesiones_aprendizaje`
--
ALTER TABLE `sesiones_aprendizaje`
    ADD CONSTRAINT `fk_sa_curso` FOREIGN KEY (`id_curso`) REFERENCES `cursos` (`id_curso`) ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT `fk_sa_docente` FOREIGN KEY (`id_docente`) REFERENCES `docentes` (`id_docente`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `silabo_estructura_criterios`
--
ALTER TABLE `silabo_estructura_criterios`
    ADD CONSTRAINT `silabo_estructura_criterios_id_estructura_foreign` FOREIGN KEY (`id_estructura`) REFERENCES `silabo_estructuras` (`id_estructura`) ON DELETE CASCADE;

--
-- Filtros para la tabla `solicitudes_password`
--
ALTER TABLE `solicitudes_password`
    ADD CONSTRAINT `solicitudes_password_id_usuario_atiende_foreign` FOREIGN KEY (`id_usuario_atiende`) REFERENCES `usuarios` (`id_usuario`) ON DELETE SET NULL,
    ADD CONSTRAINT `solicitudes_password_id_usuario_foreign` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
    ADD CONSTRAINT `usuarios_id_programa_foreign` FOREIGN KEY (`id_programa`) REFERENCES `programas_estudio` (`id_programa`) ON DELETE SET NULL,
    ADD CONSTRAINT `usuarios_id_rol_foreign` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT = @OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS = @OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION = @OLD_COLLATION_CONNECTION */;
