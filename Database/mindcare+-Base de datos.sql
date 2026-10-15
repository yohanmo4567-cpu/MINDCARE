-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 21-09-2026 a las 21:39:03
-- Versión del servidor: 10.4.22-MariaDB
-- Versión de PHP: 8.1.2

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `mindcare`
--
CREATE DATABASE IF NOT EXISTS `mindcare` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `mindcare`;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `actividad`
--

DROP TABLE IF EXISTS `actividad`;
CREATE TABLE `actividad` (
  `id_actividad` int(11) NOT NULL,
  `nombre_actividad` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `url_video` varchar(500) DEFAULT NULL,
  `duracion` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- RELACIONES PARA LA TABLA `actividad`:
--

--
-- Volcado de datos para la tabla `actividad`
--

INSERT INTO `actividad` (`id_actividad`, `nombre_actividad`, `descripcion`, `url_video`, `duracion`) VALUES
(2, 'Respiración consciente en 5 minutos', 'Observa el siguiente video y sigue las instrucciones paso a paso para realizar un ejercicio de respiración consciente. Al finalizar, dedica un minuto a reflexionar sobre cómo te sentiste antes y después del ejercicio. Esta actividad puede ayudarte a disminuir la tensión y promover un estado de calma. La respiración lenta y profunda puede ayudar a regular la respuesta del cuerpo al estrés.', 'https://youtu.be/0mXT7lc-la0?si=9p34KlO0bPKyiwzN', 5),
(3, 'Meditación guiada para reducir el estrés y la ansiedad', 'Busca un lugar tranquilo, ponte cómodo y reproduce el siguiente video. Durante 10 minutos, sigue las indicaciones de la meditación mientras observas los paisajes naturales. Al finalizar, escribe en una libreta o en tu página cómo te sentiste y menciona una emoción positiva que hayas experimentado. La práctica regular de la meditación puede ayudar a disminuir el estrés y la ansiedad, además de mejorar la concentración y el bienestar emocional.', 'https://www.youtube.com/watch?v=goqqLfrXzhI', 10);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `administrador`
--

DROP TABLE IF EXISTS `administrador`;
CREATE TABLE `administrador` (
  `id_admin` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `nivel_acceso` varchar(50) NOT NULL,
  `rol` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- RELACIONES PARA LA TABLA `administrador`:
--   `id_usuario`
--       `usuario` -> `id_usuario`
--

--
-- Volcado de datos para la tabla `administrador`
--

INSERT INTO `administrador` (`id_admin`, `id_usuario`, `nivel_acceso`, `rol`) VALUES
(1, 1, 'Super Administrador', 'Administrador');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `analisis_emocional`
--

DROP TABLE IF EXISTS `analisis_emocional`;
CREATE TABLE `analisis_emocional` (
  `id_analisis_emocional` int(11) NOT NULL,
  `id_admin` int(11) NOT NULL,
  `fecha_analisis` date NOT NULL,
  `resultado` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- RELACIONES PARA LA TABLA `analisis_emocional`:
--   `id_admin`
--       `administrador` -> `id_admin`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `analisis_habitos`
--

DROP TABLE IF EXISTS `analisis_habitos`;
CREATE TABLE `analisis_habitos` (
  `id_analisis_habitos` int(11) NOT NULL,
  `id_admin` int(11) NOT NULL,
  `fecha_analisis` date NOT NULL,
  `resultado` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- RELACIONES PARA LA TABLA `analisis_habitos`:
--   `id_admin`
--       `administrador` -> `id_admin`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `auditoria`
--

DROP TABLE IF EXISTS `auditoria`;
CREATE TABLE `auditoria` (
  `id_auditoria` int(11) NOT NULL,
  `id_admin` int(11) NOT NULL,
  `accion` varchar(255) NOT NULL,
  `fecha_accion` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- RELACIONES PARA LA TABLA `auditoria`:
--   `id_admin`
--       `administrador` -> `id_admin`
--

--
-- Volcado de datos para la tabla `auditoria`
--

INSERT INTO `auditoria` (`id_auditoria`, `id_admin`, `accion`, `fecha_accion`) VALUES
(1, 1, 'LOGIN: Inicio de sesión de administrador', '2026-07-14 14:48:02'),
(2, 1, 'LOGIN: Inicio de sesión de administrador', '2026-07-14 15:57:46'),
(3, 1, 'LOGIN: Inicio de sesión de administrador', '2026-07-14 16:12:56'),
(4, 1, 'LOGIN: Inicio de sesión de administrador', '2026-07-14 16:13:37'),
(5, 1, 'LOGIN: Inicio de sesión de administrador', '2026-07-16 14:40:48'),
(6, 1, 'LOGIN: Inicio de sesión de administrador', '2026-07-16 14:45:12'),
(7, 1, 'LOGIN: Inicio de sesión de administrador', '2026-07-21 14:16:04'),
(8, 1, 'LOGIN: Inicio de sesión de administrador', '2026-07-21 15:23:55'),
(9, 1, 'LOGIN: Inicio de sesión de administrador', '2026-07-21 15:25:34'),
(10, 1, 'LOGIN: Inicio de sesión de administrador', '2026-07-23 17:09:18'),
(11, 1, 'LOGIN: Inicio de sesión de administrador', '2026-07-27 14:48:55'),
(12, 1, 'LOGIN: Inicio de sesión de administrador', '2026-07-27 17:15:59'),
(13, 1, 'LOGIN: Inicio de sesión de administrador', '2026-07-28 14:01:21'),
(14, 1, 'LOGIN: Inicio de sesión de administrador', '2026-07-28 14:34:55'),
(15, 1, 'LOGIN: Inicio de sesión de administrador', '2026-07-28 14:41:55'),
(16, 1, 'LOGIN: Inicio de sesión de administrador', '2026-07-30 14:06:53'),
(17, 1, 'LOGIN: Inicio de sesión de administrador', '2026-08-03 15:13:36'),
(18, 1, 'LOGIN: Inicio de sesión de administrador', '2026-08-03 15:26:21'),
(19, 1, 'LOGIN: Inicio de sesión de administrador', '2026-08-11 14:43:35'),
(20, 1, 'LOGIN: Inicio de sesión de administrador', '2026-08-11 15:07:11'),
(21, 1, 'LOGIN: Inicio de sesión de administrador', '2026-08-13 16:39:38'),
(22, 1, 'LOGIN: Inicio de sesión de administrador', '2026-08-18 15:30:30'),
(23, 1, 'LOGIN: Inicio de sesión de administrador', '2026-08-27 14:41:06'),
(24, 1, 'LOGIN: Inicio de sesión de administrador', '2026-08-27 14:52:53'),
(25, 1, 'LOGIN: Inicio de sesión de administrador', '2026-09-07 13:21:35'),
(26, 1, 'LOGIN: Inicio de sesión de administrador', '2026-09-14 14:59:03'),
(27, 1, 'LOGIN: Inicio de sesión de administrador', '2026-09-15 16:48:10'),
(28, 1, 'LOGIN: Inicio de sesión de administrador', '2026-09-21 14:35:12');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categoria`
--

DROP TABLE IF EXISTS `categoria`;
CREATE TABLE `categoria` (
  `id_categoria` int(11) NOT NULL,
  `nombre_categoria` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- RELACIONES PARA LA TABLA `categoria`:
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `emocion`
--

DROP TABLE IF EXISTS `emocion`;
CREATE TABLE `emocion` (
  `id_emocion` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `fecha_registro` datetime NOT NULL,
  `observacion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- RELACIONES PARA LA TABLA `emocion`:
--   `id_usuario`
--       `usuario` -> `id_usuario`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `emocion_estado`
--

DROP TABLE IF EXISTS `emocion_estado`;
CREATE TABLE `emocion_estado` (
  `id_emocion_estado` int(11) NOT NULL,
  `id_emocion` int(11) NOT NULL,
  `id_estado` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- RELACIONES PARA LA TABLA `emocion_estado`:
--   `id_emocion`
--       `emocion` -> `id_emocion`
--   `id_estado`
--       `estado_emocional` -> `id_estado`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `encuesta_inicial`
--

DROP TABLE IF EXISTS `encuesta_inicial`;
CREATE TABLE `encuesta_inicial` (
  `id_encuesta` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `razon_uso` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '¿Por qué usas MindCare+?',
  `estado_emocional` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '¿Cómo te sientes actualmente?',
  `expectativa` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '¿Qué esperas de la plataforma?',
  `frecuencia_uso` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '¿Con qué frecuencia planeas usarla?',
  `area_mejora` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '¿En qué área quieres mejorar más?',
  `fecha_respuesta` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- RELACIONES PARA LA TABLA `encuesta_inicial`:
--   `id_usuario`
--       `usuario` -> `id_usuario`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estado_emocional`
--

DROP TABLE IF EXISTS `estado_emocional`;
CREATE TABLE `estado_emocional` (
  `id_estado` int(11) NOT NULL,
  `nombre_estado` varchar(50) NOT NULL,
  `descripcion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- RELACIONES PARA LA TABLA `estado_emocional`:
--

--
-- Volcado de datos para la tabla `estado_emocional`
--

INSERT INTO `estado_emocional` (`id_estado`, `nombre_estado`, `descripcion`) VALUES
(26, 'feliz', 'Se siente feliz y con una actitud positiva.'),
(27, 'alegre', 'Experimenta alegría y entusiasmo.'),
(28, 'contento', 'Se siente satisfecho con su estado actual.'),
(29, 'ansioso', 'Experimenta ansiedad o preocupación constante.'),
(30, 'ansiosa', 'Experimenta ansiedad o preocupación constante.'),
(31, 'nervioso', 'Se siente inquieto o con nerviosismo.'),
(32, 'triste', 'Se siente desanimado o con tristeza.'),
(33, 'deprimido', 'Presenta un estado de ánimo muy bajo.'),
(34, 'melancólico', 'Siente nostalgia o tristeza profunda.'),
(35, 'estresado', 'Se siente bajo presión o tensión.'),
(36, 'agobiado', 'Siente que las responsabilidades lo sobrepasan.'),
(37, 'cansado', 'Presenta fatiga física o mental.'),
(38, 'tranquilo', 'Se encuentra en calma.'),
(39, 'calmado', 'Mantiene un estado emocional sereno.'),
(40, 'relajado', 'Se siente libre de tensión.'),
(41, 'motivado', 'Tiene deseos de alcanzar sus objetivos.'),
(42, 'entusiasmado', 'Se siente muy emocionado por una actividad o meta.'),
(43, 'emocionado', 'Experimenta una emoción intensa y positiva.'),
(44, 'enojado', 'Siente ira o molestia.'),
(45, 'frustrado', 'Se siente decepcionado por no lograr algo.'),
(46, 'irritado', 'Presenta molestia o enfado leve.'),
(47, 'asustado', 'Siente miedo ante una situación.'),
(48, 'preocupado', 'Tiene inquietud por algún problema.'),
(49, 'inseguro', 'Duda de sí mismo o de sus decisiones.'),
(50, 'agradecido', 'Valora y aprecia lo que tiene o recibe.'),
(51, 'satisfecho', 'Se siente conforme con sus logros o situación.'),
(52, 'orgulloso', 'Se siente satisfecho por sus propios logros.');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `habito`
--

DROP TABLE IF EXISTS `habito`;
CREATE TABLE `habito` (
  `id_habito` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `nombre_habito` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `frecuencia` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- RELACIONES PARA LA TABLA `habito`:
--   `id_usuario`
--       `usuario` -> `id_usuario`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mascota`
--

DROP TABLE IF EXISTS `mascota`;
CREATE TABLE `mascota` (
  `id_mascota` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL DEFAULT 'Mindy',
  `nivel` int(11) NOT NULL DEFAULT 1,
  `experiencia` int(11) NOT NULL DEFAULT 0,
  `hambre` tinyint(4) NOT NULL DEFAULT 70,
  `felicidad` tinyint(4) NOT NULL DEFAULT 70,
  `tema_color` varchar(20) NOT NULL DEFAULT 'lavanda',
  `accesorio` varchar(40) DEFAULT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_ultima_visita` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- RELACIONES PARA LA TABLA `mascota`:
--   `id_usuario`
--       `usuario` -> `id_usuario`
--

--
-- Volcado de datos para la tabla `mascota`
--

INSERT INTO `mascota` (`id_mascota`, `id_usuario`, `nombre`, `nivel`, `experiencia`, `hambre`, `felicidad`, `tema_color`, `accesorio`, `fecha_creacion`, `fecha_ultima_visita`) VALUES
(1, 1, 'Mindy', 1, 251, 0, 0, 'lavanda', NULL, '2026-08-11 15:00:59', '2026-09-15');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mascota_evento`
--

DROP TABLE IF EXISTS `mascota_evento`;
CREATE TABLE `mascota_evento` (
  `id_evento` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `tipo` enum('habito','actividad','emocion') NOT NULL,
  `referencia` varchar(80) NOT NULL,
  `puntos` int(11) NOT NULL,
  `fecha_evento` date NOT NULL,
  `fecha_registro` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- RELACIONES PARA LA TABLA `mascota_evento`:
--   `id_usuario`
--       `usuario` -> `id_usuario`
--

--
-- Volcado de datos para la tabla `mascota_evento`
--

INSERT INTO `mascota_evento` (`id_evento`, `id_usuario`, `tipo`, `referencia`, `puntos`, `fecha_evento`, `fecha_registro`) VALUES
(1, 1, 'habito', '4_2026-07-28', 8, '2026-07-28', '2026-08-11 15:00:59'),
(2, 1, 'habito', '3_2026-07-28', 8, '2026-07-28', '2026-08-11 15:00:59'),
(3, 1, 'habito', '5_2026-07-30', 8, '2026-07-30', '2026-08-11 15:00:59'),
(4, 1, 'actividad', '2_2026-07-28', 12, '2026-07-28', '2026-08-11 15:00:59'),
(5, 1, 'actividad', '3_2026-07-28', 12, '2026-07-28', '2026-08-11 15:00:59'),
(6, 1, 'emocion', '3', 5, '2026-07-28', '2026-08-11 15:00:59'),
(7, 1, 'emocion', '4', 5, '2026-07-28', '2026-08-11 15:00:59'),
(8, 1, 'emocion', '5', 5, '2026-07-28', '2026-08-11 15:00:59'),
(9, 1, 'emocion', '6', 5, '2026-07-28', '2026-08-11 15:00:59'),
(10, 1, 'emocion', '7', 5, '2026-07-28', '2026-08-11 15:00:59'),
(11, 1, 'emocion', '8', 5, '2026-07-28', '2026-08-11 15:00:59'),
(12, 1, 'emocion', '9', 5, '2026-07-30', '2026-08-11 15:00:59'),
(15, 1, 'actividad', '2_2026-08-11', 12, '2026-08-11', '2026-08-11 15:17:04'),
(16, 1, 'actividad', '3_2026-08-11', 12, '2026-08-11', '2026-08-11 15:17:04'),
(17, 1, 'habito', '5_2026-08-11', 8, '2026-08-11', '2026-08-11 15:17:30'),
(18, 1, 'habito', '4_2026-08-11', 8, '2026-08-11', '2026-08-11 15:17:30'),
(19, 1, 'habito', '3_2026-08-11', 8, '2026-08-11', '2026-08-11 15:17:30'),
(20, 1, 'habito', '4_2026-08-13', 8, '2026-08-13', '2026-08-13 16:58:09'),
(21, 1, 'habito', '3_2026-08-13', 8, '2026-08-13', '2026-08-13 16:58:09'),
(22, 1, 'habito', '7_2026-08-13', 8, '2026-08-13', '2026-08-13 17:20:34'),
(23, 1, 'habito', '3_2026-08-18', 8, '2026-08-18', '2026-08-18 15:30:50'),
(24, 1, 'habito', '4_2026-08-18', 8, '2026-08-18', '2026-08-18 15:30:50'),
(25, 1, 'habito', '7_2026-08-18', 8, '2026-08-18', '2026-08-18 15:30:50'),
(26, 1, 'habito', '3_2026-08-27', 8, '2026-08-27', '2026-08-27 14:41:20'),
(27, 1, 'habito', '4_2026-08-27', 8, '2026-08-27', '2026-08-27 14:41:20'),
(28, 1, 'habito', '7_2026-08-27', 8, '2026-08-27', '2026-08-27 14:41:20'),
(29, 1, 'actividad', '3_2026-08-27', 12, '2026-08-27', '2026-08-27 15:08:38'),
(30, 1, 'actividad', '2_2026-08-27', 12, '2026-08-27', '2026-08-27 15:08:38'),
(31, 1, 'habito', '3_2026-09-07', 8, '2026-09-07', '2026-09-07 13:42:11'),
(32, 1, 'habito', '4_2026-09-07', 8, '2026-09-07', '2026-09-07 13:42:11'),
(33, 1, 'habito', '7_2026-09-07', 8, '2026-09-07', '2026-09-07 13:42:11');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `participacion_actividad`
--

DROP TABLE IF EXISTS `participacion_actividad`;
CREATE TABLE `participacion_actividad` (
  `id_participacion` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_actividad` int(11) NOT NULL,
  `fecha_realizacion` date NOT NULL,
  `progreso` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- RELACIONES PARA LA TABLA `participacion_actividad`:
--   `id_actividad`
--       `actividad` -> `id_actividad`
--   `id_usuario`
--       `usuario` -> `id_usuario`
--

--
-- Volcado de datos para la tabla `participacion_actividad`
--

INSERT INTO `participacion_actividad` (`id_participacion`, `id_usuario`, `id_actividad`, `fecha_realizacion`, `progreso`) VALUES
(1, 1, 2, '2026-07-28', 100),
(2, 1, 3, '2026-07-28', 100),
(3, 1, 2, '2026-08-11', 100),
(4, 1, 3, '2026-08-11', 100),
(5, 1, 3, '2026-08-11', 100),
(6, 1, 2, '2026-08-11', 100),
(7, 1, 3, '2026-08-27', 100),
(8, 1, 2, '2026-08-27', 100);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `recordatorio`
--

DROP TABLE IF EXISTS `recordatorio`;
CREATE TABLE `recordatorio` (
  `id_recordatorio` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `titulo` varchar(100) NOT NULL,
  `mensaje` text NOT NULL,
  `fecha_hora` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- RELACIONES PARA LA TABLA `recordatorio`:
--   `id_usuario`
--       `usuario` -> `id_usuario`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `recurso_categoria`
--

DROP TABLE IF EXISTS `recurso_categoria`;
CREATE TABLE `recurso_categoria` (
  `id_recurso_categoria` int(11) NOT NULL,
  `id_recurso` int(11) NOT NULL,
  `id_categoria` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- RELACIONES PARA LA TABLA `recurso_categoria`:
--   `id_categoria`
--       `categoria` -> `id_categoria`
--   `id_recurso`
--       `recurso_educativo` -> `id_recurso`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `recurso_educativo`
--

DROP TABLE IF EXISTS `recurso_educativo`;
CREATE TABLE `recurso_educativo` (
  `id_recurso` int(11) NOT NULL,
  `id_admin` int(11) NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `contenido` text NOT NULL,
  `fecha_publicacion` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- RELACIONES PARA LA TABLA `recurso_educativo`:
--   `id_admin`
--       `administrador` -> `id_admin`
--

--
-- Volcado de datos para la tabla `recurso_educativo`
--

INSERT INTO `recurso_educativo` (`id_recurso`, `id_admin`, `titulo`, `contenido`, `fecha_publicacion`) VALUES
(2, 1, 'Recurso educativo: Cómo combatir la depresión', 'La depresión es una condición de salud mental que puede afectar la forma en que una persona piensa, siente y actúa. Aunque cada caso es diferente, existen hábitos y estrategias que pueden ayudar a mejorar el bienestar emocional. Este recurso no sustituye la ayuda profesional, sino que complementa el proceso de recuperación.\r\n\r\n1. Habla con alguien de confianza\r\n\r\nCompartir lo que sientes con un amigo, familiar o profesional puede aliviar la carga emocional y ayudarte a sentirte acompañado.\r\n\r\n2. Mantén una rutina diaria\r\n\r\nEstablecer horarios para dormir, comer, estudiar o trabajar puede aportar estructura y estabilidad, incluso cuando la motivación es baja.\r\n\r\n3. Realiza actividad física\r\n\r\nCaminar, correr, practicar un deporte o hacer ejercicios durante 20 a 30 minutos al día puede favorecer la liberación de endorfinas y mejorar el estado de ánimo.\r\n\r\n4. Expresa tus emociones\r\n\r\nEscribir un diario, dibujar, tocar un instrumento o crear contenido artístico puede ser una forma saludable de expresar lo que sientes.\r\n\r\n5. Duerme lo suficiente\r\n\r\nDormir entre 7 y 9 horas por noche ayuda al cerebro a recuperarse y contribuye al equilibrio emocional.\r\n\r\n6. Reduce el aislamiento\r\n\r\nMantener contacto con otras personas, aunque sea mediante mensajes o llamadas, puede disminuir la sensación de soledad.\r\n\r\n7. Limita el consumo de alcohol y drogas\r\n\r\nEstas sustancias pueden empeorar los síntomas de la depresión y dificultar la recuperación.\r\n\r\n8. Busca ayuda profesional\r\n\r\nSi los síntomas persisten o interfieren con la vida diaria, es importante consultar a un psicólogo o psiquiatra. El tratamiento puede incluir terapia, medicación o una combinación de ambos.\r\n\r\nRecuerda\r\n\r\nPedir ayuda es un acto de fortaleza. La recuperación es posible y cada pequeño paso cuenta.\r\n\r\nFrase motivacional\r\n\r\n\"No tienes que superar todo hoy. Avanza un paso a la vez y celebra cada pequeño progreso.\"\r\n\r\nAviso importante\r\n\r\nSi tú o alguien que conoces tiene pensamientos de hacerse daño o siente que está en peligro inmediato, busca ayuda de un servicio de emergencias o de un profesional de salud mental de inmediato. La ayuda está disponible y no tienes que enfrentar la situación en soledad.', '2026-07-28'),
(3, 1, 'Recurso educativo: Cómo combatir la ansiedad', 'La ansiedad es una respuesta natural del cuerpo ante situaciones de estrés o incertidumbre. Sin embargo, cuando es muy intensa o frecuente, puede afectar la vida diaria. Existen estrategias que pueden ayudarte a manejarla de forma saludable.\r\n\r\n1. Practica la respiración profunda\r\n\r\nRespira lentamente por la nariz durante 4 segundos, mantén el aire durante 4 segundos y exhala por la boca durante 6 segundos. Repite este ejercicio durante unos minutos.\r\n\r\n2. Haz ejercicio regularmente\r\n\r\nCaminar, correr, bailar o practicar cualquier actividad física durante al menos 30 minutos al día puede ayudar a reducir los niveles de ansiedad.\r\n\r\n3. Mantén una rutina de sueño\r\n\r\nDormir entre 7 y 9 horas cada noche favorece el equilibrio emocional y mejora la capacidad para afrontar el estrés.\r\n\r\n4. Limita la cafeína y las bebidas energéticas\r\n\r\nEl exceso de cafeína puede aumentar los síntomas de ansiedad, como el nerviosismo y las palpitaciones.\r\n\r\n5. Practica técnicas de relajación\r\n\r\nLa meditación, el mindfulness o el yoga pueden ayudarte a concentrarte en el presente y disminuir los pensamientos preocupantes.\r\n\r\n6. Habla con alguien de confianza\r\n\r\nCompartir tus preocupaciones con un familiar, un amigo o un profesional puede ayudarte a sentirte comprendido y encontrar apoyo.\r\n\r\n7. Organiza tus tareas\r\n\r\nDividir las actividades en pequeños pasos y establecer prioridades puede hacer que las responsabilidades parezcan más manejables.\r\n\r\n8. Busca ayuda profesional si es necesario\r\n\r\nSi la ansiedad interfiere con tu vida diaria o persiste durante mucho tiempo, un psicólogo o psiquiatra puede ofrecer un tratamiento adecuado.\r\n\r\nConsejo rápido\r\n\r\nRecuerda: sentir ansiedad de vez en cuando es normal. Lo importante es aprender a manejarla y pedir ayuda cuando lo necesites.\r\n\r\nFrase motivacional\r\n\r\n\"No puedes controlar todo lo que sucede, pero sí puedes aprender a controlar cómo respondes a ello.\"\r\n\r\nAviso importante\r\n\r\nSi la ansiedad es muy intensa, provoca ataques de pánico frecuentes o se acompaña de pensamientos de hacerte daño, busca ayuda profesional o acude a un servicio de emergencias de inmediato.', '2026-07-28'),
(4, 1, 'Recurso educativo: 7 técnicas para controlar la ansiedad', 'La ansiedad puede hacer que te sientas preocupado, nervioso o con miedo incluso cuando no existe un peligro inmediato. Aprender a manejarla puede ayudarte a recuperar la calma y mejorar tu bienestar.\r\n\r\n? 1. Identifica qué la provoca\r\n\r\nPregúntate:\r\n\r\n¿Qué estaba haciendo antes de sentir ansiedad?\r\n¿Qué pensamientos pasaban por mi mente?\r\n¿Cómo reaccionó mi cuerpo?\r\n\r\nConocer los desencadenantes es el primer paso para aprender a manejarlos.\r\n\r\n?? 2. Concéntrate en tu respiración\r\n\r\nCuando notes que la ansiedad aumenta, inhala lentamente por la nariz y exhala por la boca. Repite este proceso durante 2 o 3 minutos para ayudar a reducir la tensión.\r\n\r\n? 3. Cuestiona los pensamientos negativos\r\n\r\nEn lugar de creer inmediatamente lo que piensas, pregúntate:\r\n\r\n¿Tengo pruebas de que esto va a pasar?\r\n¿Estoy imaginando el peor escenario?\r\n¿Qué le diría a un amigo si estuviera en mi situación?\r\n? 4. Muévete\r\n\r\nRealizar actividad física, aunque sea una caminata de 15 o 20 minutos, puede disminuir el estrés y ayudarte a despejar la mente.\r\n\r\n? 5. Haz una pausa de las redes sociales\r\n\r\nEl exceso de información y las comparaciones constantes pueden aumentar la ansiedad. Dedica un tiempo del día a desconectarte.\r\n\r\n? 6. Practica actividades que disfrutes\r\n\r\nEscuchar música, leer, pintar, cocinar o pasar tiempo con tus seres queridos puede ayudarte a cambiar el foco de atención y relajarte.\r\n\r\n? 7. Recuerda que no tienes que enfrentarlo solo\r\n\r\nHablar con alguien de confianza o buscar apoyo profesional puede marcar una gran diferencia si la ansiedad se vuelve difícil de manejar.\r\n\r\n? Consejo del día\r\n\r\nLa ansiedad no define quién eres. Es una emoción que puede aprenderse a manejar con tiempo, práctica y apoyo.\r\n\r\n? Recuerda\r\n\r\nSi la ansiedad es constante, afecta tus estudios, trabajo o relaciones, o sientes que ya no puedes controlarla, busca ayuda de un profesional de la salud mental. Pedir ayuda es un paso importante hacia el bienestar.', '2026-07-28');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reporte_estadisticas`
--

DROP TABLE IF EXISTS `reporte_estadisticas`;
CREATE TABLE `reporte_estadisticas` (
  `id_reporte` int(11) NOT NULL,
  `id_admin` int(11) NOT NULL,
  `fecha_generacion` date NOT NULL,
  `descripcion` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- RELACIONES PARA LA TABLA `reporte_estadisticas`:
--   `id_admin`
--       `administrador` -> `id_admin`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `seguimiento_habito`
--

DROP TABLE IF EXISTS `seguimiento_habito`;
CREATE TABLE `seguimiento_habito` (
  `id_seguimiento` int(11) NOT NULL,
  `id_habito` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `progreso` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- RELACIONES PARA LA TABLA `seguimiento_habito`:
--   `id_habito`
--       `habito` -> `id_habito`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `supervision_plataforma`
--

DROP TABLE IF EXISTS `supervision_plataforma`;
CREATE TABLE `supervision_plataforma` (
  `id_supervision` int(11) NOT NULL,
  `id_admin` int(11) NOT NULL,
  `fecha_supervision` date NOT NULL,
  `observaciones` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- RELACIONES PARA LA TABLA `supervision_plataforma`:
--   `id_admin`
--       `administrador` -> `id_admin`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

DROP TABLE IF EXISTS `usuario`;
CREATE TABLE `usuario` (
  `id_usuario` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `correo` varchar(150) NOT NULL,
  `contraseña` varchar(255) NOT NULL,
  `foto_perfil` varchar(255) DEFAULT NULL,
  `fecha_registro` date NOT NULL,
  `edad` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- RELACIONES PARA LA TABLA `usuario`:
--

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`id_usuario`, `nombre`, `apellido`, `correo`, `contraseña`, `foto_perfil`, `fecha_registro`, `edad`) VALUES
(1, 'xXadminXx', '.', 'derekzahidr@gmail.com', '$2y$12$6uuAfgwmoN3qPJtVvZu9vura/DHGJ0vYYc/iSSTB6H4tWhAyYZL9m', 'uploads/fotos/user_1_1784058917.jpg', '2026-07-14', 67);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `actividad`
--
ALTER TABLE `actividad`
  ADD PRIMARY KEY (`id_actividad`);

--
-- Indices de la tabla `administrador`
--
ALTER TABLE `administrador`
  ADD PRIMARY KEY (`id_admin`),
  ADD UNIQUE KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `analisis_emocional`
--
ALTER TABLE `analisis_emocional`
  ADD PRIMARY KEY (`id_analisis_emocional`),
  ADD KEY `fk_analisis_emocional_admin` (`id_admin`);

--
-- Indices de la tabla `analisis_habitos`
--
ALTER TABLE `analisis_habitos`
  ADD PRIMARY KEY (`id_analisis_habitos`),
  ADD KEY `fk_analisis_habitos_admin` (`id_admin`);

--
-- Indices de la tabla `auditoria`
--
ALTER TABLE `auditoria`
  ADD PRIMARY KEY (`id_auditoria`),
  ADD KEY `fk_auditoria_admin` (`id_admin`);

--
-- Indices de la tabla `categoria`
--
ALTER TABLE `categoria`
  ADD PRIMARY KEY (`id_categoria`),
  ADD UNIQUE KEY `nombre_categoria` (`nombre_categoria`);

--
-- Indices de la tabla `emocion`
--
ALTER TABLE `emocion`
  ADD PRIMARY KEY (`id_emocion`),
  ADD KEY `fk_emocion_usuario` (`id_usuario`);

--
-- Indices de la tabla `emocion_estado`
--
ALTER TABLE `emocion_estado`
  ADD PRIMARY KEY (`id_emocion_estado`),
  ADD KEY `fk_emocion_estado_emocion` (`id_emocion`),
  ADD KEY `fk_emocion_estado_estado` (`id_estado`);

--
-- Indices de la tabla `encuesta_inicial`
--
ALTER TABLE `encuesta_inicial`
  ADD PRIMARY KEY (`id_encuesta`),
  ADD UNIQUE KEY `uq_encuesta_usuario` (`id_usuario`);

--
-- Indices de la tabla `estado_emocional`
--
ALTER TABLE `estado_emocional`
  ADD PRIMARY KEY (`id_estado`),
  ADD UNIQUE KEY `nombre_estado` (`nombre_estado`);

--
-- Indices de la tabla `habito`
--
ALTER TABLE `habito`
  ADD PRIMARY KEY (`id_habito`),
  ADD KEY `fk_habito_usuario` (`id_usuario`);

--
-- Indices de la tabla `mascota`
--
ALTER TABLE `mascota`
  ADD PRIMARY KEY (`id_mascota`),
  ADD UNIQUE KEY `uq_mascota_usuario` (`id_usuario`);

--
-- Indices de la tabla `mascota_evento`
--
ALTER TABLE `mascota_evento`
  ADD PRIMARY KEY (`id_evento`),
  ADD UNIQUE KEY `uq_mascota_evento` (`id_usuario`,`tipo`,`referencia`);

--
-- Indices de la tabla `participacion_actividad`
--
ALTER TABLE `participacion_actividad`
  ADD PRIMARY KEY (`id_participacion`),
  ADD KEY `fk_participacion_usuario` (`id_usuario`),
  ADD KEY `fk_participacion_actividad` (`id_actividad`);

--
-- Indices de la tabla `recordatorio`
--
ALTER TABLE `recordatorio`
  ADD PRIMARY KEY (`id_recordatorio`),
  ADD KEY `fk_recordatorio_usuario` (`id_usuario`);

--
-- Indices de la tabla `recurso_categoria`
--
ALTER TABLE `recurso_categoria`
  ADD PRIMARY KEY (`id_recurso_categoria`),
  ADD KEY `fk_recurso_categoria_recurso` (`id_recurso`),
  ADD KEY `fk_recurso_categoria_categoria` (`id_categoria`);

--
-- Indices de la tabla `recurso_educativo`
--
ALTER TABLE `recurso_educativo`
  ADD PRIMARY KEY (`id_recurso`),
  ADD KEY `fk_recurso_admin` (`id_admin`);

--
-- Indices de la tabla `reporte_estadisticas`
--
ALTER TABLE `reporte_estadisticas`
  ADD PRIMARY KEY (`id_reporte`),
  ADD KEY `fk_reporte_admin` (`id_admin`);

--
-- Indices de la tabla `seguimiento_habito`
--
ALTER TABLE `seguimiento_habito`
  ADD PRIMARY KEY (`id_seguimiento`),
  ADD KEY `fk_seguimiento_habito` (`id_habito`);

--
-- Indices de la tabla `supervision_plataforma`
--
ALTER TABLE `supervision_plataforma`
  ADD PRIMARY KEY (`id_supervision`),
  ADD KEY `fk_supervision_admin` (`id_admin`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `correo` (`correo`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `actividad`
--
ALTER TABLE `actividad`
  MODIFY `id_actividad` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `administrador`
--
ALTER TABLE `administrador`
  MODIFY `id_admin` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `analisis_emocional`
--
ALTER TABLE `analisis_emocional`
  MODIFY `id_analisis_emocional` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `analisis_habitos`
--
ALTER TABLE `analisis_habitos`
  MODIFY `id_analisis_habitos` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `auditoria`
--
ALTER TABLE `auditoria`
  MODIFY `id_auditoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT de la tabla `categoria`
--
ALTER TABLE `categoria`
  MODIFY `id_categoria` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `emocion`
--
ALTER TABLE `emocion`
  MODIFY `id_emocion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `emocion_estado`
--
ALTER TABLE `emocion_estado`
  MODIFY `id_emocion_estado` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `encuesta_inicial`
--
ALTER TABLE `encuesta_inicial`
  MODIFY `id_encuesta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `estado_emocional`
--
ALTER TABLE `estado_emocional`
  MODIFY `id_estado` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT de la tabla `habito`
--
ALTER TABLE `habito`
  MODIFY `id_habito` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `mascota`
--
ALTER TABLE `mascota`
  MODIFY `id_mascota` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `mascota_evento`
--
ALTER TABLE `mascota_evento`
  MODIFY `id_evento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT de la tabla `participacion_actividad`
--
ALTER TABLE `participacion_actividad`
  MODIFY `id_participacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `recordatorio`
--
ALTER TABLE `recordatorio`
  MODIFY `id_recordatorio` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `recurso_categoria`
--
ALTER TABLE `recurso_categoria`
  MODIFY `id_recurso_categoria` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `recurso_educativo`
--
ALTER TABLE `recurso_educativo`
  MODIFY `id_recurso` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `reporte_estadisticas`
--
ALTER TABLE `reporte_estadisticas`
  MODIFY `id_reporte` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `seguimiento_habito`
--
ALTER TABLE `seguimiento_habito`
  MODIFY `id_seguimiento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT de la tabla `supervision_plataforma`
--
ALTER TABLE `supervision_plataforma`
  MODIFY `id_supervision` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `administrador`
--
ALTER TABLE `administrador`
  ADD CONSTRAINT `fk_admin_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`);

--
-- Filtros para la tabla `analisis_emocional`
--
ALTER TABLE `analisis_emocional`
  ADD CONSTRAINT `fk_analisis_emocional_admin` FOREIGN KEY (`id_admin`) REFERENCES `administrador` (`id_admin`);

--
-- Filtros para la tabla `analisis_habitos`
--
ALTER TABLE `analisis_habitos`
  ADD CONSTRAINT `fk_analisis_habitos_admin` FOREIGN KEY (`id_admin`) REFERENCES `administrador` (`id_admin`);

--
-- Filtros para la tabla `auditoria`
--
ALTER TABLE `auditoria`
  ADD CONSTRAINT `fk_auditoria_admin` FOREIGN KEY (`id_admin`) REFERENCES `administrador` (`id_admin`);

--
-- Filtros para la tabla `emocion`
--
ALTER TABLE `emocion`
  ADD CONSTRAINT `fk_emocion_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`);

--
-- Filtros para la tabla `emocion_estado`
--
ALTER TABLE `emocion_estado`
  ADD CONSTRAINT `fk_emocion_estado_emocion` FOREIGN KEY (`id_emocion`) REFERENCES `emocion` (`id_emocion`),
  ADD CONSTRAINT `fk_emocion_estado_estado` FOREIGN KEY (`id_estado`) REFERENCES `estado_emocional` (`id_estado`);

--
-- Filtros para la tabla `encuesta_inicial`
--
ALTER TABLE `encuesta_inicial`
  ADD CONSTRAINT `fk_encuesta_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `habito`
--
ALTER TABLE `habito`
  ADD CONSTRAINT `fk_habito_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`);

--
-- Filtros para la tabla `mascota`
--
ALTER TABLE `mascota`
  ADD CONSTRAINT `fk_mascota_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `mascota_evento`
--
ALTER TABLE `mascota_evento`
  ADD CONSTRAINT `fk_evento_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `participacion_actividad`
--
ALTER TABLE `participacion_actividad`
  ADD CONSTRAINT `fk_participacion_actividad` FOREIGN KEY (`id_actividad`) REFERENCES `actividad` (`id_actividad`),
  ADD CONSTRAINT `fk_participacion_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`);

--
-- Filtros para la tabla `recordatorio`
--
ALTER TABLE `recordatorio`
  ADD CONSTRAINT `fk_recordatorio_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`);

--
-- Filtros para la tabla `recurso_categoria`
--
ALTER TABLE `recurso_categoria`
  ADD CONSTRAINT `fk_recurso_categoria_categoria` FOREIGN KEY (`id_categoria`) REFERENCES `categoria` (`id_categoria`),
  ADD CONSTRAINT `fk_recurso_categoria_recurso` FOREIGN KEY (`id_recurso`) REFERENCES `recurso_educativo` (`id_recurso`);

--
-- Filtros para la tabla `recurso_educativo`
--
ALTER TABLE `recurso_educativo`
  ADD CONSTRAINT `fk_recurso_admin` FOREIGN KEY (`id_admin`) REFERENCES `administrador` (`id_admin`);

--
-- Filtros para la tabla `reporte_estadisticas`
--
ALTER TABLE `reporte_estadisticas`
  ADD CONSTRAINT `fk_reporte_admin` FOREIGN KEY (`id_admin`) REFERENCES `administrador` (`id_admin`);

--
-- Filtros para la tabla `seguimiento_habito`
--
ALTER TABLE `seguimiento_habito`
  ADD CONSTRAINT `fk_seguimiento_habito` FOREIGN KEY (`id_habito`) REFERENCES `habito` (`id_habito`);

--
-- Filtros para la tabla `supervision_plataforma`
--
ALTER TABLE `supervision_plataforma`
  ADD CONSTRAINT `fk_supervision_admin` FOREIGN KEY (`id_admin`) REFERENCES `administrador` (`id_admin`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
