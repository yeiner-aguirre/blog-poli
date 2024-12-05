-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 13-11-2024 a las 01:21:23
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
-- Base de datos: `blog`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `authors`
--

CREATE TABLE `authors` (
  `id` int(11) NOT NULL,
  `name` varchar(20) DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `password` varchar(50) DEFAULT NULL,
  `is_locked` tinyint(1) DEFAULT 0,
  `failed_attempts` int(11) DEFAULT 0,
  `last_failed_attempt` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `authors`
--

INSERT INTO `authors` (`id`, `name`, `email`, `password`, `is_locked`, `failed_attempts`, `last_failed_attempt`) VALUES
(1, 'iamsebas376', 'londonosebas201.sloa@gmail.com', 'sebaslondono201', 0, 0, NULL),
(11, 'admin', 'admin@gmail.com', '12345', 0, 0, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categories`
--

INSERT INTO `categories` (`id`, `name`) VALUES
(1, 'Tecnología'),
(2, 'Comedia'),
(3, 'Educación'),
(4, 'Ciencia Ficción'),
(5, 'Aventura'),
(6, 'Desarrollo Personal'),
(7, 'Historia'),
(8, 'Naturaleza'),
(9, 'Música'),
(10, 'Fotografía'),
(11, 'Arte'),
(12, 'Literatura'),
(13, 'Gaming');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `comments`
--

CREATE TABLE `comments` (
  `id` int(11) NOT NULL,
  `post_id` int(11) DEFAULT NULL,
  `author_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `comment` varchar(1000) DEFAULT NULL,
  `date` date DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `comments`
--

INSERT INTO `comments` (`id`, `post_id`, `author_id`, `user_id`, `comment`, `date`) VALUES
(11, 20, 1, 1, 'bueno', '2024-11-05');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `likes`
--

CREATE TABLE `likes` (
  `id` int(11) NOT NULL,
  `author_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `post_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `likes`
--

INSERT INTO `likes` (`id`, `author_id`, `user_id`, `post_id`) VALUES
(64, 1, 1, 20);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `posts`
--

CREATE TABLE `posts` (
  `id` int(11) NOT NULL,
  `author_id` int(11) DEFAULT NULL,
  `title` varchar(100) DEFAULT NULL,
  `content` varchar(10000) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `image` varchar(100) DEFAULT NULL,
  `date` datetime DEFAULT current_timestamp(),
  `status_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `posts`
--

INSERT INTO `posts` (`id`, `author_id`, `title`, `content`, `category_id`, `image`, `date`, `status_id`) VALUES
(15, 1, 'Las últimas tendencias de la inteligencia artificial', 'La inteligencia artificial está revolucionando la forma en que interactuamos con la tecnología. Desde asistentes virtuales hasta sistemas de recomendación, exploremos cómo la IA está cambiando nuestras vidas.', 1, 'uploads/1730347184_luca-bravo-XJXWbfSo2f0-unsplash.jpg', '2024-10-30 22:57:33', 1),
(16, 1, 'Los mejores chistes de la semana', 'Aquí tienes una recopilación de los chistes más divertidos que te harán reír a carcajadas. ¡No te los pierdas!', 2, 'uploads/1730347247_clem-onojeghuo-phIDtKzQN8k-unsplash.jpg', '2024-10-30 22:57:33', 1),
(17, 1, 'Cómo mejorar tus habilidades de estudio', 'Estudiar de manera efectiva es clave para el éxito académico. Aquí hay algunos consejos para mejorar tus habilidades de estudio y maximizar tu aprendizaje.', 3, 'uploads/1730347273_kimberly-farmer-lUaaKCUANVI-unsplash.jpg', '2024-10-30 22:57:33', 1),
(18, 1, 'Las mejores películas de ciencia ficción de la última década', 'Desde \\\"Inception\\\" hasta \\\\\\\"Blade Runner 2049\\\\\\\", exploramos las películas de ciencia ficción que han dejado una huella en la industria del cine.', 4, 'uploads/1730347312_zoltan-tasi-6vEqcR8Icbs-unsplash.jpg', '2024-10-30 22:57:33', 1),
(19, 1, 'Mis aventuras en el sendero de los Apalaches', 'Un relato sobre mi experiencia caminando por el sendero de los Apalaches, enfrentando desafíos y disfrutando de la belleza de la naturaleza.', 5, 'uploads/1730347339_cem-sagisman-bLS61-FdP8E-unsplash.jpg', '2024-10-30 22:57:33', 1),
(20, 1, '5 hábitos para un crecimiento personal efectivo', 'El desarrollo personal es un viaje continuo. Aquí hay cinco hábitos que puedes adoptar para mejorar tu vida y alcanzar tus metas.', 6, 'uploads/1730347369_austin-distel-VwsuhJ9uee4-unsplash.jpg', '2024-10-30 22:57:33', 1),
(21, 1, 'Los eventos que cambiaron el curso de la historia', 'Desde la Revolución Francesa hasta la caída del Muro de Berlín, exploramos los eventos más significativos que han dado forma a nuestro mundo.', 7, 'uploads/1730347399_andrew-neel-1-29wyvvLJA-unsplash.jpg', '2024-10-30 22:57:33', 1),
(22, 1, 'La importancia de la conservación del medio ambiente', 'La conservación del medio ambiente es crucial para el futuro de nuestro planeta. Aquí discutimos algunas estrategias para proteger nuestro entorno.', 8, 'uploads/1730347431_v2osk-1Z2niiBPg5A-unsplash.jpg', '2024-10-30 22:57:33', 1),
(23, 1, 'Los álbumes más influyentes de la década de 2020', 'Exploramos los álbumes que han definido la música en la última década y su impacto en la cultura popular.', 9, 'uploads/1730347458_gabriel-gurrola-2UuhMZEChdc-unsplash.jpg', '2024-10-30 22:57:33', 1),
(24, 1, 'Consejos para capturar la belleza de la naturaleza', 'La fotografía de naturaleza puede ser desafiante pero gratificante. Aquí hay algunos consejos para capturar imágenes impresionantes.', 10, 'uploads/1730347484_samsung-memory-xiX2PkgsPn4-unsplash.jpg', '2024-10-30 22:57:33', 1),
(25, 1, 'Los movimientos artísticos más importantes del siglo XX', 'Desde el cubismo hasta el surrealismo, exploramos los movimientos artísticos que han influido en la creación de arte moderno.', 11, 'uploads/1730347512_khara-woods-KR84RpMCb0w-unsplash.jpg', '2024-10-30 22:57:33', 1),
(26, 1, 'Los libros que debes leer antes de morir', 'Una lista de libros clásicos y contemporáneos que todo amante de la literatura debería leer al menos una vez en su vida.', 12, 'uploads/1730347547_clem-onojeghuo-x7CDil50KKY-unsplash.jpg', '2024-10-30 22:57:33', 1),
(27, 1, 'Los videojuegos más esperados de 2023', 'Un vistazo a los videojuegos que están generando más expectativa este año y lo que podemos esperar de ellos.', 13, 'uploads/1730347572_carl-raw-m3hn2Kn5Bns-unsplash.jpg', '2024-10-30 22:57:33', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `statuses`
--

CREATE TABLE `statuses` (
  `id` int(11) NOT NULL,
  `name` varchar(15) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `statuses`
--

INSERT INTO `statuses` (`id`, `name`) VALUES
(1, 'Activado'),
(2, 'Desactivado'),
(3, 'Borrador');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `authors`
--
ALTER TABLE `authors`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `post_id` (`post_id`);

--
-- Indices de la tabla `likes`
--
ALTER TABLE `likes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `author_id` (`author_id`),
  ADD KEY `post_id` (`post_id`);

--
-- Indices de la tabla `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `author_id` (`author_id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `status_id` (`status_id`);

--
-- Indices de la tabla `statuses`
--
ALTER TABLE `statuses`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `authors`
--
ALTER TABLE `authors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `likes`
--
ALTER TABLE `likes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- AUTO_INCREMENT de la tabla `posts`
--
ALTER TABLE `posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT de la tabla `statuses`
--
ALTER TABLE `statuses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`);

--
-- Filtros para la tabla `likes`
--
ALTER TABLE `likes`
  ADD CONSTRAINT `likes_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `authors` (`id`),
  ADD CONSTRAINT `likes_ibfk_2` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`);

--
-- Filtros para la tabla `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `authors` (`id`),
  ADD CONSTRAINT `posts_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
  ADD CONSTRAINT `posts_ibfk_3` FOREIGN KEY (`status_id`) REFERENCES `statuses` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
