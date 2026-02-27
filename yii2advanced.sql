-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Хост: db:3306
-- Время создания: Фев 27 2026 г., 08:27
-- Версия сервера: 8.0.45
-- Версия PHP: 8.3.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `yii2advanced`
--

-- --------------------------------------------------------

--
-- Структура таблицы `migration`
--

CREATE TABLE `migration` (
  `version` varchar(180) NOT NULL,
  `apply_time` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `migration`
--

INSERT INTO `migration` (`version`, `apply_time`) VALUES
('m000000_000000_base', 1772174391),
('m130524_201442_init', 1772174393),
('m190124_110200_add_verification_token_column_to_user_table', 1772174394),
('m260122_201528_create_new_field', 1772174394),
('m260122_211339_create_request_table', 1772174395),
('m260201_125454_create_group_role', 1772174396);

-- --------------------------------------------------------

--
-- Структура таблицы `request`
--

CREATE TABLE `request` (
  `id` int NOT NULL COMMENT 'ID заявки',
  `user_id` int NOT NULL COMMENT 'ID пользователя',
  `request_date` date NOT NULL COMMENT 'Дата заявки',
  `request_time` time NOT NULL COMMENT 'Время заявки',
  `guests_count` int NOT NULL DEFAULT '1' COMMENT 'Количество участников',
  `contact_phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Контактный телефон',
  `contact_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Контактное лицо',
  `request_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'standard' COMMENT 'Тип заявки: standard, urgent, vip, group',
  `special_requests` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Особые пожелания',
  `status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending' COMMENT 'Статус: pending, confirmed, canceled, completed',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Дата создания',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Дата обновления'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `request`
--

INSERT INTO `request` (`id`, `user_id`, `request_date`, `request_time`, `guests_count`, `contact_phone`, `contact_name`, `request_type`, `special_requests`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, '2026-12-12', '11:11:00', 1, '7912345623', 'Тестовый тестов', 'standard', '12345', 'pending', '2026-02-27 07:11:43', '2026-02-27 07:11:43');

-- --------------------------------------------------------

--
-- Структура таблицы `user`
--

CREATE TABLE `user` (
  `id` int NOT NULL,
  `username` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `auth_key` varchar(32) COLLATE utf8mb3_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `password_reset_token` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `status` smallint NOT NULL DEFAULT '10',
  `created_at` int NOT NULL,
  `updated_at` int NOT NULL,
  `verification_token` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `phone` varchar(20) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `first_name` varchar(100) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `last_name` varchar(100) COLLATE utf8mb3_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Дамп данных таблицы `user`
--

INSERT INTO `user` (`id`, `username`, `auth_key`, `password_hash`, `password_reset_token`, `email`, `status`, `created_at`, `updated_at`, `verification_token`, `phone`, `first_name`, `last_name`) VALUES
(1, '1234', 'J-vfaWKeL_x8Zud9qvZZWUAxvSC5IOil', '$2y$13$.rQojLEXOg/AuK2AaDJbbOiYbjO90E4iaYB7CD7MjfunEL1sbsrzO', NULL, '12342@qwe.te', 10, 1772174401, 1772174401, NULL, '2134', '1234', '1234'),
(2, 'Admin26', '5bsZTrBTY9KWWujqcnIsZkYnLBa4rcCI', '$2y$13$wnNL1XeEPE62C5kgJt9UJumvarEIp2rA3a3mYO6WVptzTk44UHlLO', NULL, 'qwer@tre.wq', 10, 1772176444, 1772176444, NULL, '7900000000', 'Админ', 'Админов'),
(3, 'test1234', '-MvnKE5clhFenEbw2a8qDCw5cPf4HfrQ', '$2y$13$E1FyE69zFbQU9j7AH8ARpOsqNUI4geuif6C9V7.uQ.lybvQ3WKxgy', NULL, 'dqwer@yter.ru', 10, 1772179977, 1772179977, NULL, '791231254125', 'Dima', 'Sergeev');

-- --------------------------------------------------------

--
-- Структура таблицы `user_group`
--

CREATE TABLE `user_group` (
  `id` int NOT NULL COMMENT 'ID группы',
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Название группы',
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Описание группы',
  `is_default` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Группа по умолчанию для новых пользователей',
  `created_at` int NOT NULL COMMENT 'Дата создания',
  `updated_at` int NOT NULL COMMENT 'Дата обновления'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `user_group`
--

INSERT INTO `user_group` (`id`, `name`, `description`, `is_default`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'Администратор системы. Полный доступ ко всем функциям.', 0, 1772174396, 1772174396),
(2, 'user', 'Обычный пользователь. Базовые права доступа.', 1, 1772174396, 1772174396);

-- --------------------------------------------------------

--
-- Структура таблицы `user_group_assignment`
--

CREATE TABLE `user_group_assignment` (
  `user_id` int NOT NULL COMMENT 'ID пользователя',
  `group_id` int NOT NULL COMMENT 'ID группы',
  `created_at` int NOT NULL COMMENT 'Дата назначения'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `user_group_assignment`
--

INSERT INTO `user_group_assignment` (`user_id`, `group_id`, `created_at`) VALUES
(2, 1, 12341234);

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `migration`
--
ALTER TABLE `migration`
  ADD PRIMARY KEY (`version`);

--
-- Индексы таблицы `request`
--
ALTER TABLE `request`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk-request-user_id` (`user_id`);

--
-- Индексы таблицы `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `password_reset_token` (`password_reset_token`);

--
-- Индексы таблицы `user_group`
--
ALTER TABLE `user_group`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Индексы таблицы `user_group_assignment`
--
ALTER TABLE `user_group_assignment`
  ADD PRIMARY KEY (`user_id`,`group_id`),
  ADD KEY `fk-user_group_assignment-group_id` (`group_id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `request`
--
ALTER TABLE `request`
  MODIFY `id` int NOT NULL AUTO_INCREMENT COMMENT 'ID заявки', AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT для таблицы `user`
--
ALTER TABLE `user`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT для таблицы `user_group`
--
ALTER TABLE `user_group`
  MODIFY `id` int NOT NULL AUTO_INCREMENT COMMENT 'ID группы', AUTO_INCREMENT=3;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `request`
--
ALTER TABLE `request`
  ADD CONSTRAINT `fk-request-user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `user_group_assignment`
--
ALTER TABLE `user_group_assignment`
  ADD CONSTRAINT `fk-user_group_assignment-group_id` FOREIGN KEY (`group_id`) REFERENCES `user_group` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk-user_group_assignment-user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
