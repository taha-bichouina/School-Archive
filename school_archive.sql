-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : ven. 14 mars 2025 à 12:33
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `school_archive`
--

-- --------------------------------------------------------

--
-- Structure de la table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `activity` varchar(255) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `activity`, `ip_address`, `created_at`) VALUES
(1, 3, 'Login', '::1', '2025-03-12 11:45:47'),
(2, 3, 'Login', '::1', '2025-03-12 11:46:11'),
(3, 3, 'Login', '::1', '2025-03-13 13:23:59'),
(4, 3, 'Login', '::1', '2025-03-13 13:33:50'),
(5, 3, 'Login', '::1', '2025-03-13 14:05:23'),
(6, 3, 'Login', '192.168.11.112', '2025-03-13 14:10:13'),
(7, 3, 'Login', '192.168.11.112', '2025-03-13 14:12:47'),
(8, 3, 'Login', '192.168.11.111', '2025-03-13 14:14:14'),
(9, 3, 'Login', '::1', '2025-03-13 14:15:45'),
(10, 3, 'Login', '::1', '2025-03-14 09:43:55'),
(11, 3, 'Login', '::1', '2025-03-14 10:49:36'),
(12, 3, 'Login', '::1', '2025-03-14 10:54:36'),
(13, 3, 'Login', '::1', '2025-03-14 11:03:09');

-- --------------------------------------------------------

--
-- Structure de la table `classes`
--

CREATE TABLE `classes` (
  `id` int(11) NOT NULL,
  `class_id` varchar(50) NOT NULL,
  `class_name` varchar(255) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `student_id` varchar(50) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `user_id_or_cin` varchar(50) NOT NULL,
  `class_id` varchar(50) NOT NULL,
  `season` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_of_birth` date NOT NULL,
  `date_of_start` date NOT NULL,
  `date_of_end` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `students`
--

INSERT INTO `students` (`id`, `student_id`, `first_name`, `last_name`, `user_id_or_cin`, `class_id`, `season`, `created_at`, `date_of_birth`, `date_of_start`, `date_of_end`) VALUES
(9, '2', 'Taha', 'Taha', 'jghhsjgdf', '1BAC', '2017-2018', '2025-03-13 14:25:35', '2000-02-02', '2000-02-05', '2000-02-05'),
(10, '4', 'taha', 'bichouina', 'bb25950', 'TC', '2023-2024', '2025-03-13 14:29:15', '2024-06-12', '2025-12-12', '2500-12-12'),
(11, '3', 'Taha', 'Bichouina', 'R155063529', 'TC', '2021-2022', '2025-03-14 09:45:16', '2000-12-21', '2004-02-12', '2009-12-13'),
(12, '5', 'Taha', 'Bichouina', 'R155063529', '1BAC', '2017-2018', '2025-03-14 11:08:18', '1999-12-21', '1012-12-21', '2019-12-12');

-- --------------------------------------------------------

--
-- Structure de la table `teachers`
--

CREATE TABLE `teachers` (
  `id` int(11) NOT NULL,
  `teacher_id` varchar(50) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','teacher','staff') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `created_at`, `first_name`, `last_name`, `status`) VALUES
(3, 'admin', '$2y$10$DYAtmrpObE.QLjdV3Tv6Su/idDV0NCTEDWdGrwJ2ZBkwrEWzfAeq6', 'admin', '2025-03-12 11:31:27', 'Nacer', 'Eddine', 'Active'),
(4, 'taha', '$2y$10$W03NDugAif/wirsNqA4uWemTAacibHzyUyUHBwErdw13dT7xWlhUm', '', '2025-03-14 10:50:48', 'Taha', 'Bichouina', ''),
(5, 'med', '$2y$10$VQ1dMk3onkAxuISl4Z2vgOB0Nc0ytPIGFXzk9xMZPbZXCXJX.GGfy', '', '2025-03-14 10:54:00', 'med', 'med', '');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `classes`
--
ALTER TABLE `classes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `class_id` (`class_id`),
  ADD KEY `teacher_id` (`teacher_id`);

--
-- Index pour la table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `student_id` (`student_id`);

--
-- Index pour la table `teachers`
--
ALTER TABLE `teachers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `teacher_id` (`teacher_id`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT pour la table `classes`
--
ALTER TABLE `classes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT pour la table `teachers`
--
ALTER TABLE `teachers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `classes`
--
ALTER TABLE `classes`
  ADD CONSTRAINT `classes_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
