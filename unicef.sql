-- phpMyAdmin SQL Dump
-- version 4.8.5
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Apr 03, 2021 at 11:57 PM
-- Server version: 5.7.24
-- PHP Version: 7.3.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "-00:00";
SET FOREIGN_KEY_CHECKS=0;

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `unicef`
--

--
-- Dumping data for table `grupos`
--

INSERT INTO `grupos` (`id`, `name`, `descrip`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'admin', 'aministradores', '1', '2021-03-09 21:14:21', '2021-03-09 21:14:21', NULL),
(2, 'Monitores', 'Permsos para monitores', '1', '2021-04-02 00:52:11', '2021-04-02 00:52:11', NULL);

--
-- Dumping data for table `grupos_permisos`
--

INSERT INTO `grupos_permisos` (`valor`, `deleted_at`, `permisos_id`, `grupos_id`) VALUES
(15, NULL, 4, 1),
(15, NULL, 5, 1),
(15, NULL, 6, 1),
(15, NULL, 1, 1),
(15, NULL, 2, 1),
(15, NULL, 9, 1),
(15, NULL, 7, 1),
(1, NULL, 3, 2),
(1, NULL, 10, 2),
(15, NULL, 8, 2),
(1, NULL, 4, 2),
(15, NULL, 5, 2),
(1, NULL, 6, 2),
(15, NULL, 1, 2),
(15, NULL, 2, 2),
(15, NULL, 9, 2),
(1, NULL, 7, 2);

--
-- Dumping data for table `permisos`
--

INSERT INTO `permisos` (`id`, `slug`, `name`, `descrip`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'ruteos', 'ruteos', 'asasas', '1', '2021-03-09 21:13:54', '2021-04-02 00:45:41', NULL),
(2, 'ruteosMonitor', 'ruteos monitor', NULL, '1', '2021-04-02 00:36:10', '2021-04-02 00:36:10', NULL),
(3, 'Beneficiarios', 'Beneficiarios', 'Beneficiarios', '1', '2021-04-02 00:44:58', '2021-04-02 00:44:58', NULL),
(4, 'Preguntas', 'Preguntas', 'Preguntas', '1', '2021-04-02 00:45:20', '2021-04-02 00:45:20', NULL),
(5, 'Respuestas', 'Respuestas', 'Respuestas', '1', '2021-04-02 00:45:54', '2021-04-02 00:45:54', NULL),
(6, 'Rutas', 'Rutas', 'Rutas', '1', '2021-04-02 00:46:37', '2021-04-02 00:46:37', NULL),
(7, 'Usuarios', 'Usuarios', 'Usuarios', '1', '2021-04-02 00:47:00', '2021-04-02 00:47:00', NULL),
(8, 'Evaluaciones', 'Evaluaciones', 'Evaluaciones', '1', '2021-04-02 00:48:45', '2021-04-02 00:48:45', NULL),
(9, 'Servicios', 'Servicios', 'Servicios', '1', '2021-04-02 00:49:03', '2021-04-02 00:49:03', NULL),
(10, 'categ', 'Categorias', 'Categorias', '1', '2021-04-02 21:05:46', '2021-04-02 21:05:46', NULL);

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `descrip`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'admin', 'administrador', '1', '2021-03-09 21:13:03', '2021-03-09 21:13:03', NULL),
(2, 'monitor', 'Monitor de Rutas', '1', '2021-03-09 21:13:22', '2021-03-09 21:13:22', NULL),
(3, 'superAdmin', 'super Admininistrador', '1', NULL, NULL, NULL);

--
-- Dumping data for table `usuarios`
--

INSERT INTO `usuarios` (`id`, `name`, `email`, `pass`, `rolActivo`, `remember_token`, `status`, `created_at`, `updated_at`, `deleted_at`, `roles_id`) VALUES
(1, 'mario', '2@2.com', 'f7c3bc1d808e04732adf679965ccc34ca7ae3441', 0, NULL, '1', '2021-03-09 21:15:04', '2021-04-01 19:16:03', NULL, 2),
(2, 'Tania andrea copa mendoza', '1@1.com', 'f7c3bc1d808e04732adf679965ccc34ca7ae3441', 0, NULL, '1', '2021-03-09 21:16:31', '2021-03-09 21:16:31', NULL, 2),
(3, 'Makroz', 'mk@mk.com', 'f7c3bc1d808e04732adf679965ccc34ca7ae3441', 0, NULL, '1', '2021-04-01 19:15:48', '2021-04-01 19:15:48', NULL, 3);

--
-- Dumping data for table `usuarios_grupos`
--

INSERT INTO `usuarios_grupos` (`deleted_at`, `usuarios_id`, `grupos_id`) VALUES
(NULL, 1, 2),
(NULL, 3, 1),
(NULL, 2, 2);

--
-- Dumping data for table `usuarios_permisos`
--

INSERT INTO `usuarios_permisos` (`valor`, `deleted_at`, `usuarios_id`, `permisos_id`) VALUES
(15, NULL, 1, 1),
(15, NULL, 1, 2);
SET FOREIGN_KEY_CHECKS=1;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
