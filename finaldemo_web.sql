-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jun 12, 2026 at 02:53 PM
-- Server version: 8.0.30
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `finaldemo_web`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` int NOT NULL,
  `class_id` int NOT NULL,
  `student_id` int NOT NULL,
  `attendance_date` date NOT NULL,
  `attendance_status` enum('PRESENT','ABSENT','LATE','EXCUSED') DEFAULT 'PRESENT',
  `tinh_luong` tinyint(1) DEFAULT '0',
  `note` varchar(300) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`id`, `class_id`, `student_id`, `attendance_date`, `attendance_status`, `tinh_luong`, `note`) VALUES
(1, 9, 1, '2026-04-16', 'PRESENT', 1, NULL),
(2, 9, 2, '2026-04-16', 'PRESENT', 1, NULL),
(3, 9, 16, '2026-04-16', 'PRESENT', 1, NULL),
(4, 9, 17, '2026-04-16', 'PRESENT', 1, NULL),
(5, 9, 1, '2026-04-20', 'PRESENT', 1, NULL),
(6, 9, 2, '2026-04-20', 'PRESENT', 1, NULL),
(7, 9, 16, '2026-04-20', 'PRESENT', 1, NULL),
(8, 9, 17, '2026-04-20', 'PRESENT', 1, NULL),
(9, 9, 1, '2026-04-23', 'PRESENT', 1, NULL),
(10, 9, 2, '2026-04-23', 'LATE', 1, NULL),
(11, 9, 16, '2026-04-23', 'PRESENT', 1, NULL),
(12, 9, 17, '2026-04-23', 'EXCUSED', 0, NULL),
(13, 9, 1, '2026-04-27', 'PRESENT', 1, NULL),
(14, 9, 2, '2026-04-27', 'PRESENT', 1, NULL),
(15, 9, 16, '2026-04-27', 'EXCUSED', 0, NULL),
(16, 9, 17, '2026-04-27', 'PRESENT', 1, NULL),
(17, 9, 1, '2026-04-30', 'ABSENT', 0, NULL),
(18, 9, 2, '2026-04-30', 'PRESENT', 1, NULL),
(19, 9, 16, '2026-04-30', 'PRESENT', 1, NULL),
(20, 9, 17, '2026-04-30', 'PRESENT', 1, NULL),
(21, 9, 1, '2026-05-04', 'PRESENT', 1, NULL),
(22, 9, 2, '2026-05-04', 'PRESENT', 1, NULL),
(23, 9, 16, '2026-05-04', 'PRESENT', 1, NULL),
(24, 9, 17, '2026-05-04', 'PRESENT', 1, NULL),
(25, 9, 1, '2026-05-07', 'PRESENT', 1, NULL),
(26, 9, 2, '2026-05-07', 'PRESENT', 1, NULL),
(27, 9, 16, '2026-05-07', 'PRESENT', 1, NULL),
(28, 9, 17, '2026-05-07', 'PRESENT', 1, NULL),
(29, 9, 1, '2026-05-11', 'PRESENT', 1, NULL),
(30, 9, 2, '2026-05-11', 'LATE', 1, NULL),
(31, 9, 16, '2026-05-11', 'PRESENT', 1, NULL),
(32, 9, 17, '2026-05-11', 'PRESENT', 1, NULL),
(33, 9, 1, '2026-05-14', 'PRESENT', 1, NULL),
(34, 9, 2, '2026-05-14', 'EXCUSED', 0, NULL),
(35, 9, 16, '2026-05-14', 'PRESENT', 1, NULL),
(36, 9, 17, '2026-05-14', 'PRESENT', 1, NULL),
(37, 9, 1, '2026-05-18', 'PRESENT', 1, NULL),
(38, 9, 2, '2026-05-18', 'ABSENT', 0, NULL),
(39, 9, 16, '2026-05-18', 'PRESENT', 1, NULL),
(40, 9, 17, '2026-05-18', 'PRESENT', 1, NULL),
(41, 9, 1, '2026-05-21', 'PRESENT', 1, NULL),
(42, 9, 2, '2026-05-21', 'PRESENT', 1, NULL),
(43, 9, 16, '2026-05-21', 'PRESENT', 1, NULL),
(44, 9, 17, '2026-05-21', 'LATE', 1, NULL),
(45, 9, 1, '2026-05-25', 'PRESENT', 1, NULL),
(46, 9, 2, '2026-05-25', 'LATE', 1, NULL),
(47, 9, 16, '2026-05-25', 'PRESENT', 1, NULL),
(48, 9, 17, '2026-05-25', 'PRESENT', 1, NULL),
(49, 9, 1, '2026-05-28', 'PRESENT', 1, NULL),
(50, 9, 2, '2026-05-28', 'PRESENT', 1, NULL),
(51, 9, 16, '2026-05-28', 'LATE', 1, NULL),
(52, 9, 17, '2026-05-28', 'PRESENT', 1, NULL),
(53, 9, 1, '2026-06-01', 'PRESENT', 1, NULL),
(54, 9, 2, '2026-06-01', 'PRESENT', 1, NULL),
(55, 9, 16, '2026-06-01', 'PRESENT', 1, NULL),
(56, 9, 17, '2026-06-01', 'PRESENT', 1, NULL),
(57, 9, 1, '2026-06-04', 'LATE', 1, NULL),
(58, 9, 2, '2026-06-04', 'PRESENT', 1, NULL),
(59, 9, 16, '2026-06-04', 'EXCUSED', 0, NULL),
(60, 9, 17, '2026-06-04', 'PRESENT', 1, NULL),
(61, 9, 1, '2026-06-08', 'PRESENT', 1, NULL),
(62, 9, 2, '2026-06-08', 'PRESENT', 1, NULL),
(63, 9, 16, '2026-06-08', 'PRESENT', 1, NULL),
(64, 9, 17, '2026-06-08', 'PRESENT', 1, NULL),
(65, 10, 3, '2026-04-16', 'PRESENT', 1, NULL),
(66, 10, 16, '2026-04-16', 'PRESENT', 1, NULL),
(67, 10, 3, '2026-04-20', 'PRESENT', 1, NULL),
(68, 10, 16, '2026-04-20', 'PRESENT', 1, NULL),
(69, 10, 3, '2026-04-23', 'PRESENT', 1, NULL),
(70, 10, 16, '2026-04-23', 'PRESENT', 1, NULL),
(71, 10, 3, '2026-04-27', 'PRESENT', 1, NULL),
(72, 10, 16, '2026-04-27', 'PRESENT', 1, NULL),
(73, 10, 3, '2026-04-30', 'PRESENT', 1, NULL),
(74, 10, 16, '2026-04-30', 'PRESENT', 1, NULL),
(75, 10, 3, '2026-05-04', 'PRESENT', 1, NULL),
(76, 10, 16, '2026-05-04', 'PRESENT', 1, NULL),
(77, 10, 3, '2026-05-07', 'LATE', 1, NULL),
(78, 10, 16, '2026-05-07', 'PRESENT', 1, NULL),
(79, 10, 3, '2026-05-11', 'LATE', 1, NULL),
(80, 10, 16, '2026-05-11', 'LATE', 1, NULL),
(81, 10, 3, '2026-05-14', 'ABSENT', 0, NULL),
(82, 10, 16, '2026-05-14', 'PRESENT', 1, NULL),
(83, 10, 3, '2026-05-18', 'PRESENT', 1, NULL),
(84, 10, 16, '2026-05-18', 'PRESENT', 1, NULL),
(85, 10, 3, '2026-05-21', 'PRESENT', 1, NULL),
(86, 10, 16, '2026-05-21', 'PRESENT', 1, NULL),
(87, 10, 3, '2026-05-25', 'PRESENT', 1, NULL),
(88, 10, 16, '2026-05-25', 'LATE', 1, NULL),
(89, 10, 3, '2026-05-28', 'LATE', 1, NULL),
(90, 10, 16, '2026-05-28', 'PRESENT', 1, NULL),
(91, 10, 3, '2026-06-01', 'LATE', 1, NULL),
(92, 10, 16, '2026-06-01', 'PRESENT', 1, NULL),
(93, 10, 3, '2026-06-04', 'PRESENT', 1, NULL),
(94, 10, 16, '2026-06-04', 'PRESENT', 1, NULL),
(95, 10, 3, '2026-06-08', 'PRESENT', 1, NULL),
(96, 10, 16, '2026-06-08', 'PRESENT', 1, NULL),
(97, 11, 1, '2026-05-12', 'PRESENT', 1, NULL),
(98, 11, 3, '2026-05-12', 'PRESENT', 1, NULL),
(99, 11, 17, '2026-05-12', 'PRESENT', 1, NULL),
(100, 11, 1, '2026-05-15', 'PRESENT', 1, NULL),
(101, 11, 3, '2026-05-15', 'PRESENT', 1, NULL),
(102, 11, 17, '2026-05-15', 'PRESENT', 1, NULL),
(103, 11, 1, '2026-05-19', 'ABSENT', 0, NULL),
(104, 11, 3, '2026-05-19', 'PRESENT', 1, NULL),
(105, 11, 17, '2026-05-19', 'PRESENT', 1, NULL),
(106, 11, 1, '2026-05-22', 'PRESENT', 1, NULL),
(107, 11, 3, '2026-05-22', 'PRESENT', 1, NULL),
(108, 11, 17, '2026-05-22', 'LATE', 1, NULL),
(109, 11, 1, '2026-05-26', 'PRESENT', 1, NULL),
(110, 11, 3, '2026-05-26', 'PRESENT', 1, NULL),
(111, 11, 17, '2026-05-26', 'LATE', 1, NULL),
(112, 11, 1, '2026-05-29', 'PRESENT', 1, NULL),
(113, 11, 3, '2026-05-29', 'PRESENT', 1, NULL),
(114, 11, 17, '2026-05-29', 'LATE', 1, NULL),
(115, 11, 1, '2026-06-02', 'PRESENT', 1, NULL),
(116, 11, 3, '2026-06-02', 'ABSENT', 0, NULL),
(117, 11, 17, '2026-06-02', 'PRESENT', 1, NULL),
(118, 11, 1, '2026-06-05', 'LATE', 1, NULL),
(119, 11, 3, '2026-06-05', 'PRESENT', 1, NULL),
(120, 11, 17, '2026-06-05', 'PRESENT', 1, NULL),
(121, 11, 1, '2026-06-09', 'PRESENT', 1, NULL),
(122, 11, 3, '2026-06-09', 'PRESENT', 1, NULL),
(123, 11, 17, '2026-06-09', 'PRESENT', 1, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `action` varchar(60) DEFAULT NULL,
  `entity_name` varchar(60) DEFAULT NULL,
  `entity_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_name`, `entity_id`, `created_at`) VALUES
(1, 1, 'CONFIRM_PAYMENT', 'enrollments', 3, '2026-06-10 07:18:44'),
(2, 1, 'CONFIRM_PAYMENT', 'enrollments', 2, '2026-06-10 07:18:55'),
(3, 1, 'CREATE_CLASS_PLAN', 'class_plans', 1, '2026-06-10 10:21:02'),
(4, 1, 'CREATE_CLASS', 'classes', 8, '2026-06-10 10:23:09'),
(5, 1, 'CREATE_SCHEDULE', 'schedules', 5, '2026-06-10 10:28:16'),
(6, 1, 'CREATE_CLASS', 'classes', 23, '2026-06-10 17:40:27'),
(7, 1, 'CREATE_ENROLLMENT', 'enrollments', 0, '2026-06-10 17:43:03'),
(8, 1, 'CREATE_STUDENT', 'students', 0, '2026-06-10 17:46:50'),
(9, 1, 'CREATE_ENROLLMENT', 'enrollments', 0, '2026-06-10 17:47:07'),
(10, 1, 'DELETE_ENROLLMENT', 'enrollments', 23, '2026-06-10 17:47:17'),
(11, 1, 'CREATE_SCHEDULE', 'schedules', 143, '2026-06-10 17:47:57'),
(12, 1, 'CREATE_TEACHER', 'teachers', 0, '2026-06-10 17:58:25'),
(13, 1, 'CREATE_ASSIGNMENT', 'teacher_assignments', 19, '2026-06-10 18:01:46'),
(14, 1, 'UPDATE_ASSIGNMENT', 'teacher_assignments', 19, '2026-06-10 18:05:59'),
(15, 1, 'UPDATE_SCHEDULE', 'schedules', 143, '2026-06-10 18:17:22'),
(16, 1, 'CONFIRM_PAYMENT', 'enrollments', 18, '2026-06-10 18:25:08'),
(17, 1, 'UPDATE_COURSE', 'courses', 4, '2026-06-10 18:26:43'),
(18, 1, 'UPDATE_CLASS', 'classes', 23, '2026-06-10 18:26:59'),
(19, 1, 'CREATE_TEACHER', 'teachers', 0, '2026-06-12 14:29:52'),
(20, 1, 'CONFIRM_PAYMENT', 'enrollments', 19, '2026-06-12 14:30:12'),
(21, 1, 'CREATE_COURSE', 'courses', 22, '2026-06-12 14:31:55'),
(22, 1, 'CREATE_CLASS', 'classes', 24, '2026-06-12 14:32:40'),
(23, 1, 'CREATE_CLASS_PLAN', 'class_plans', 7, '2026-06-12 14:33:45'),
(24, 1, 'CREATE_CLASS', 'classes', 25, '2026-06-12 14:34:23'),
(25, 1, 'CREATE_ENROLLMENT', 'enrollments', 0, '2026-06-12 14:35:10'),
(26, 1, 'CONFIRM_PAYMENT', 'enrollments', 29, '2026-06-12 14:36:46'),
(27, 1, 'CREATE_SCHEDULE', 'schedules', 158, '2026-06-12 14:37:34'),
(28, 1, 'DELETE_SCHEDULE', 'schedules', 158, '2026-06-12 14:39:37'),
(29, 49, 'CREATE_LEAVE', 'leave_requests', 0, '2026-06-12 14:40:33'),
(30, 49, 'UPLOAD_SUBMISSION', 'submissions', 12, '2026-06-12 14:41:20'),
(31, 50, 'GRADE', 'submissions', 12, '2026-06-12 14:51:14');

-- --------------------------------------------------------

--
-- Table structure for table `classes`
--

CREATE TABLE `classes` (
  `id` int NOT NULL,
  `class_code` varchar(40) NOT NULL,
  `course_id` int NOT NULL,
  `teacher_id` int DEFAULT NULL,
  `classroom_id` int DEFAULT NULL,
  `semester_id` int DEFAULT NULL,
  `max_students` int NOT NULL DEFAULT '30',
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('UPCOMING','ONGOING','COMPLETED','CANCELLED') DEFAULT 'UPCOMING',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `classes`
--

INSERT INTO `classes` (`id`, `class_code`, `course_id`, `teacher_id`, `classroom_id`, `semester_id`, `max_students`, `start_date`, `end_date`, `status`, `created_at`) VALUES
(1, 'PY501-A', 4, 1, 1, 1, 25, '2026-07-01', '2026-09-09', 'UPCOMING', '2026-06-10 04:34:11'),
(2, 'JS101-A', 1, 2, 2, 1, 25, '2026-07-01', '2026-09-09', 'UPCOMING', '2026-06-10 05:24:32'),
(8, 'AAA123123', 3, NULL, 1, 7, 20, '2026-06-10', '2026-08-05', 'UPCOMING', '2026-06-10 10:23:09'),
(9, 'JS101-B', 1, 1, 1, 1, 25, '2026-04-14', '2026-06-19', 'ONGOING', '2026-06-10 17:25:29'),
(10, 'PHP201-A', 2, 2, 2, 1, 20, '2026-04-14', '2026-06-19', 'ONGOING', '2026-06-10 17:25:29'),
(11, 'DB301-A', 3, 1, 3, 1, 15, '2026-05-12', '2026-06-19', 'ONGOING', '2026-06-10 17:25:29'),
(13, 'PY501-B', 4, 11, 13, 2, 30, '2026-07-06', '2026-09-13', 'UPCOMING', '2026-06-10 17:25:29'),
(15, 'PHP201-B', 2, 2, 3, 2, 20, '2026-07-07', '2026-09-14', 'UPCOMING', '2026-06-10 17:25:29'),
(23, 'LTJAV08', 1, NULL, 2, 7, 25, '2026-08-01', '2026-10-10', 'UPCOMING', '2026-06-10 17:40:27'),
(24, 'LPHPNC01', 22, NULL, 13, 12, 25, '2026-06-16', '2026-08-25', 'UPCOMING', '2026-06-12 14:32:40'),
(25, 'PHP2021', 22, NULL, 3, 12, 20, '2026-06-16', '2026-08-25', 'UPCOMING', '2026-06-12 14:34:23');

-- --------------------------------------------------------

--
-- Table structure for table `classrooms`
--

CREATE TABLE `classrooms` (
  `id` int NOT NULL,
  `room_name` varchar(50) NOT NULL,
  `capacity` int NOT NULL,
  `location` varchar(200) DEFAULT NULL,
  `status` enum('ACTIVE','INACTIVE') DEFAULT 'ACTIVE'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `classrooms`
--

INSERT INTO `classrooms` (`id`, `room_name`, `capacity`, `location`, `status`) VALUES
(1, 'P101', 30, 'Tầng 1', 'ACTIVE'),
(2, 'P201', 25, 'Tầng 2', 'ACTIVE'),
(3, 'P301', 20, 'Tầng 3', 'ACTIVE'),
(13, 'P401', 35, 'Tầng 4 – Lab máy tính', 'ACTIVE');

-- --------------------------------------------------------

--
-- Table structure for table `class_plans`
--

CREATE TABLE `class_plans` (
  `id` int NOT NULL,
  `course_id` int NOT NULL,
  `semester_id` int NOT NULL,
  `planned_class_count` int DEFAULT '1',
  `target_student_count` int DEFAULT '20',
  `status` enum('DRAFT','APPROVED','CANCELLED') DEFAULT 'DRAFT',
  `created_by` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `class_plans`
--

INSERT INTO `class_plans` (`id`, `course_id`, `semester_id`, `planned_class_count`, `target_student_count`, `status`, `created_by`) VALUES
(1, 3, 7, 2, 20, 'APPROVED', 1),
(2, 1, 1, 2, 25, 'APPROVED', 1),
(3, 2, 1, 1, 20, 'APPROVED', 1),
(4, 3, 1, 1, 15, 'APPROVED', 1),
(5, 4, 2, 2, 25, 'APPROVED', 1),
(6, 1, 2, 2, 25, 'DRAFT', 1),
(7, 22, 12, 2, 20, 'APPROVED', 1);

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` int NOT NULL,
  `course_code` varchar(30) NOT NULL,
  `course_name` varchar(200) NOT NULL,
  `description` text,
  `duration_weeks` int NOT NULL DEFAULT '10',
  `total_sessions` int NOT NULL DEFAULT '20',
  `day_primary` tinyint NOT NULL DEFAULT '1',
  `day_secondary` tinyint NOT NULL DEFAULT '4',
  `default_start_time` time DEFAULT '18:00:00',
  `default_end_time` time DEFAULT '20:00:00',
  `tuition_fee` decimal(12,2) NOT NULL,
  `status` enum('ACTIVE','INACTIVE') DEFAULT 'ACTIVE',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `course_code`, `course_name`, `description`, `duration_weeks`, `total_sessions`, `day_primary`, `day_secondary`, `default_start_time`, `default_end_time`, `tuition_fee`, `status`, `created_at`) VALUES
(1, 'JS101', 'Lập trình JavaScript', 'Khóa học JS từ cơ bản đến nâng cao', 10, 20, 1, 4, '18:00:00', '20:00:00', '3500000.00', 'ACTIVE', '2026-06-10 04:34:11'),
(2, 'PHP201', 'Lập trình PHP & MySQL', 'Backend development với PHP', 10, 20, 1, 4, '18:00:00', '20:00:00', '3000000.00', 'ACTIVE', '2026-06-10 04:34:11'),
(3, 'DB301', 'Cơ sở dữ liệu nâng cao', 'SQL, tối ưu truy vấn, thiết kế DB', 8, 16, 2, 5, '18:00:00', '20:00:00', '2500000.00', 'ACTIVE', '2026-06-10 04:34:11'),
(4, 'PY501', 'Lập trình Python', 'Python từ cơ bản đến nâng cao', 10, 20, 1, 4, '18:00:00', '20:00:00', '3200001.00', 'ACTIVE', '2026-06-10 04:34:11'),
(22, 'PHP202', 'Lập trình PHP nâng cao tháng 6', 'Lập trình PHP nâng cao tháng 6', 10, 20, 2, 6, '18:00:00', '20:00:00', '5500001.00', 'ACTIVE', '2026-06-12 14:31:55');

-- --------------------------------------------------------

--
-- Table structure for table `enrollments`
--

CREATE TABLE `enrollments` (
  `id` int NOT NULL,
  `student_id` int NOT NULL,
  `class_id` int NOT NULL,
  `enrollment_date` date NOT NULL,
  `payment_status` enum('UNPAID','PAID','REFUNDED') DEFAULT 'UNPAID',
  `status` enum('ACTIVE','DROPPED','COMPLETED') DEFAULT 'ACTIVE'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `enrollments`
--

INSERT INTO `enrollments` (`id`, `student_id`, `class_id`, `enrollment_date`, `payment_status`, `status`) VALUES
(1, 2, 1, '2026-06-10', 'PAID', 'ACTIVE'),
(2, 3, 2, '2026-06-10', 'PAID', 'ACTIVE'),
(3, 1, 9, '2026-04-10', 'PAID', 'ACTIVE'),
(4, 2, 9, '2026-04-10', 'PAID', 'ACTIVE'),
(5, 16, 9, '2026-04-11', 'PAID', 'ACTIVE'),
(6, 17, 9, '2026-04-12', 'PAID', 'ACTIVE'),
(7, 20, 9, '2026-04-13', 'UNPAID', 'ACTIVE'),
(8, 3, 10, '2026-04-10', 'PAID', 'ACTIVE'),
(9, 16, 10, '2026-04-11', 'PAID', 'ACTIVE'),
(10, 18, 10, '2026-04-12', 'UNPAID', 'ACTIVE'),
(11, 19, 10, '2026-04-12', 'UNPAID', 'ACTIVE'),
(12, 1, 11, '2026-05-08', 'PAID', 'ACTIVE'),
(13, 3, 11, '2026-05-09', 'PAID', 'ACTIVE'),
(14, 17, 11, '2026-05-08', 'PAID', 'ACTIVE'),
(15, 1, 1, '2026-06-01', 'UNPAID', 'ACTIVE'),
(16, 16, 1, '2026-06-02', 'PAID', 'ACTIVE'),
(17, 17, 1, '2026-06-02', 'PAID', 'ACTIVE'),
(18, 18, 1, '2026-06-03', 'PAID', 'ACTIVE'),
(19, 19, 2, '2026-06-02', 'PAID', 'ACTIVE'),
(20, 20, 2, '2026-06-02', 'PAID', 'ACTIVE'),
(21, 2, 13, '2026-06-04', 'PAID', 'ACTIVE'),
(22, 3, 13, '2026-06-04', 'PAID', 'ACTIVE'),
(24, 29, 23, '2026-06-11', 'PAID', 'ACTIVE'),
(25, 29, 24, '2026-06-12', 'PAID', 'ACTIVE');

-- --------------------------------------------------------

--
-- Table structure for table `grades`
--

CREATE TABLE `grades` (
  `id` int NOT NULL,
  `submission_id` int NOT NULL,
  `teacher_id` int NOT NULL,
  `score` decimal(5,2) DEFAULT NULL,
  `comment` text,
  `graded_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `grades`
--

INSERT INTO `grades` (`id`, `submission_id`, `teacher_id`, `score`, `comment`, `graded_at`) VALUES
(1, 1, 1, '78.20', 'Xuất sắc, đề nghị làm thêm bài nâng cao.', '2026-06-10 17:25:38'),
(2, 2, 1, '75.50', 'Xuất sắc, đề nghị làm thêm bài nâng cao.', '2026-06-10 17:25:38'),
(3, 4, 1, '74.20', 'Xuất sắc, đề nghị làm thêm bài nâng cao.', '2026-06-10 17:25:38'),
(4, 5, 1, '64.10', 'Xuất sắc, đề nghị làm thêm bài nâng cao.', '2026-06-10 17:25:38'),
(5, 6, 1, '95.60', 'Xuất sắc, đề nghị làm thêm bài nâng cao.', '2026-06-10 17:25:38'),
(6, 7, 1, '96.30', 'Có tiến bộ, tiếp tục cố gắng.', '2026-06-10 17:25:38'),
(7, 8, 1, '69.20', 'Có tiến bộ, tiếp tục cố gắng.', '2026-06-10 17:25:38'),
(8, 9, 1, '82.90', 'Có tiến bộ, tiếp tục cố gắng.', '2026-06-10 17:25:38'),
(9, 10, 1, '60.40', 'Xuất sắc, đề nghị làm thêm bài nâng cao.', '2026-06-10 17:25:38'),
(10, 12, 15, '5.00', 'bài làm bình thường', '2026-06-12 14:51:14');

-- --------------------------------------------------------

--
-- Table structure for table `leave_requests`
--

CREATE TABLE `leave_requests` (
  `id` int NOT NULL,
  `requester_type` enum('TEACHER','STUDENT') NOT NULL,
  `requester_id` int NOT NULL,
  `request_type` enum('LEAVE','MAKEUP') DEFAULT 'LEAVE',
  `class_id` int DEFAULT NULL,
  `request_date` date NOT NULL,
  `reason` text,
  `status` enum('PENDING','APPROVED','REJECTED') DEFAULT 'PENDING',
  `reviewed_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `leave_requests`
--

INSERT INTO `leave_requests` (`id`, `requester_type`, `requester_id`, `request_type`, `class_id`, `request_date`, `reason`, `status`, `reviewed_by`, `created_at`) VALUES
(1, 'TEACHER', 1, 'MAKEUP', 1, '2026-06-17', 'Demo: xin học bù buổi vắng do công tác', 'PENDING', NULL, '2026-06-10 05:26:28'),
(2, 'TEACHER', 2, 'MAKEUP', 10, '2026-06-13', 'Bận họp chuyên môn, xin nghỉ và dạy bù sau.', 'PENDING', NULL, '2026-06-10 17:25:38'),
(3, 'TEACHER', 1, 'MAKEUP', 9, '2026-06-18', 'Công tác ngoài tỉnh, xin dạy bù ngày khác.', 'APPROVED', NULL, '2026-06-10 17:25:38'),
(4, 'STUDENT', 1, 'LEAVE', 9, '2026-06-12', 'Bị ốm, không thể đến lớp.', 'PENDING', NULL, '2026-06-10 17:25:38'),
(5, 'STUDENT', 2, 'LEAVE', 9, '2026-06-07', 'Gia đình có việc bận đột xuất.', 'APPROVED', NULL, '2026-06-10 17:25:38'),
(6, 'STUDENT', 16, 'LEAVE', 10, '2026-06-13', 'Xin phép nghỉ học do bận thi môn khác.', 'PENDING', NULL, '2026-06-10 17:25:38'),
(7, 'STUDENT', 29, 'LEAVE', 24, '2026-06-15', 'Bận không đi học được', 'PENDING', NULL, '2026-06-12 14:40:33');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int NOT NULL,
  `title` varchar(200) NOT NULL,
  `content` text,
  `receiver_id` int NOT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `title`, `content`, `receiver_id`, `is_read`, `created_at`) VALUES
(1, 'Thanh toán thành công', 'Bạn đã được xếp vào lớp JS101-A. Lịch học đã được tạo.', 6, 0, '2026-06-10 07:18:42'),
(2, 'Phân công dạy mới', 'Bạn được phân công dạy học viên tại lớp JS101-A.', 2, 0, '2026-06-10 07:18:44'),
(3, 'Thanh toán thành công', 'Bạn đã được xếp vào lớp PY501-A. Lịch học đã được tạo.', 5, 0, '2026-06-10 07:18:54'),
(4, 'Phân công dạy mới', 'Bạn được phân công dạy học viên tại lớp PY501-A.', 3, 0, '2026-06-10 07:18:54'),
(5, 'Lịch Học thường mới', 'Admin đã thêm lịch: Lớp PY501-A, Thứ ba, 18:00-20:00 (Học thường) — HV: Nguyễn Thị Bình.', 5, 0, '2026-06-10 10:28:14'),
(6, 'Lịch Học thường mới', 'Admin đã thêm lịch: Lớp PY501-A, Thứ ba, 18:00-20:00 (Học thường) — HV: Nguyễn Thị Bình.', 3, 0, '2026-06-10 10:28:15'),
(7, 'Lịch Học thường mới', 'Admin đã thêm lịch: Lớp PY501-A, Thứ ba, 18:00-20:00 (Học thường) — HV: Nguyễn Thị Bình.', 2, 0, '2026-06-10 10:28:15'),
(8, 'Yêu cầu học bù mới', 'GV Lê Thị Hoa gửi yêu cầu học bù ngày 17/06/2026 (PHP201-A). Vui lòng xem xét duyệt.', 1, 0, '2026-06-10 17:25:39'),
(9, '3 học viên chưa đóng học phí', 'PHP201-A: HV006 (Linh), HV007 (Hùng) và JS101-B: HV008 (Thu) chưa đóng học phí. Cần xử lý.', 1, 0, '2026-06-10 17:25:39'),
(10, 'Bảng lương tháng 06/2026 chờ duyệt', 'Có 3 bảng lương tháng 06/2026 (GV001, GV002, GV003) đang ở trạng thái PENDING.', 1, 0, '2026-06-10 17:25:39'),
(11, 'Lớp PY501-A sắp khai giảng', 'Lớp PY501-A dự kiến khai giảng 06/07/2026. Hiện có 5 học viên đăng ký (2 đã đóng tiền).', 1, 1, '2026-06-10 17:25:39'),
(12, 'Lịch dạy hôm nay', 'Bạn có buổi dạy JS101-B hôm nay (10/06/2026) lúc 18:00–20:00 tại P101.', 2, 0, '2026-06-10 17:25:39'),
(13, 'Bảng lương 05/2026 đã thanh toán', 'Bảng lương tháng 05/2026 đã được xử lý. Số tiền: 6,000,000 VND. Vui lòng kiểm tra.', 2, 1, '2026-06-10 17:25:39'),
(14, 'Yêu cầu học bù được duyệt', 'Yêu cầu học bù ngày 2026-06-18 của bạn (JS101-B) đã được Admin phê duyệt.', 2, 1, '2026-06-10 17:25:39'),
(15, 'Nhắc nhở: điểm danh lớp DB301-A', 'Lớp DB301-A còn 3 buổi học. Vui lòng điểm danh đúng hạn sau mỗi buổi.', 2, 0, '2026-06-10 17:25:39'),
(16, 'Lịch dạy hôm nay', 'Bạn có buổi dạy PHP201-A hôm nay (10/06/2026) lúc 18:00–20:00 tại P201.', 3, 0, '2026-06-10 17:25:39'),
(17, 'Học viên chưa đóng học phí', 'Lớp PHP201-A có 2 học viên (HV006 Linh, HV007 Hùng) chưa đóng học phí.', 3, 0, '2026-06-10 17:25:39'),
(18, 'Bảng lương 05/2026 đã thanh toán', 'Bảng lương tháng 05/2026: 5,400,000 VND đã được thanh toán.', 3, 1, '2026-06-10 17:25:39'),
(19, 'Chào mừng gia nhập EduCenter!', 'Tài khoản của bạn đã được kích hoạt. Lớp PY501-B sẽ khai giảng 06/07/2026.', 28, 1, '2026-06-10 17:25:39'),
(20, 'Bảng lương 05/2026', 'Bảng lương tháng 05/2026 đã được thanh toán. Số tiền: 1,800,000 VND.', 28, 1, '2026-06-10 17:25:39'),
(21, 'Lịch học hôm nay', 'JS101-B: buổi học hôm nay (10/06/2026) lúc 18:00–20:00 tại P101. Đừng quên bài tập!', 4, 0, '2026-06-10 17:25:39'),
(22, 'Xác nhận đăng ký PY501-A', 'Bạn đã đăng ký lớp PY501-A (khai giảng 06/07). Học phí 3,200,000 VND chưa đóng.', 4, 0, '2026-06-10 17:25:39'),
(23, 'Đã thanh toán – JS101-B', 'Học phí lớp JS101-B (3,500,000 VND) đã được xác nhận. Cảm ơn bạn!', 4, 1, '2026-06-10 17:25:39'),
(24, 'Lịch học hôm nay', 'JS101-B: buổi học hôm nay (10/06/2026) lúc 18:00–20:00 tại P101.', 5, 0, '2026-06-10 17:25:39'),
(25, 'Nhắc nhở học phí PY501-A', 'Học phí lớp PY501-A (3,200,000 VND) chưa thanh toán. Hạn đóng: 30/06/2026.', 5, 0, '2026-06-10 17:25:39'),
(26, 'Nhắc nhở học phí JS101-A', 'Học phí lớp JS101-A (3,500,000 VND) chưa thanh toán. Hạn đóng: 30/06/2026.', 6, 0, '2026-06-10 17:25:39'),
(27, 'Kết quả học tập PHP201-A', 'Điểm trung bình của bạn: 9.2 – Loại GIỎI! Xuất sắc!', 6, 1, '2026-06-10 17:25:39'),
(28, 'Nhắc nhở học phí PHP201-A', 'Học phí lớp PHP201-A (3,000,000 VND) chưa thanh toán. Hạn đóng: 30/06/2026.', 34, 0, '2026-06-10 17:25:40'),
(29, 'Nhắc nhở học phí PHP201-A', 'Học phí lớp PHP201-A (3,000,000 VND) chưa thanh toán. Hạn đóng: 30/06/2026.', 35, 0, '2026-06-10 17:25:40'),
(30, 'Yêu cầu học bù mới', 'GV Lê Thị Hoa gửi yêu cầu học bù ngày 17/06/2026 (PHP201-A). Vui lòng xem xét duyệt.', 1, 0, '2026-06-10 17:27:36'),
(31, '3 học viên chưa đóng học phí', 'PHP201-A: HV006 (Linh), HV007 (Hùng) và JS101-B: HV008 (Thu) chưa đóng học phí. Cần xử lý.', 1, 0, '2026-06-10 17:27:36'),
(32, 'Bảng lương tháng 06/2026 chờ duyệt', 'Có 3 bảng lương tháng 06/2026 (GV001, GV002, GV003) đang ở trạng thái PENDING.', 1, 0, '2026-06-10 17:27:36'),
(33, 'Lớp PY501-A sắp khai giảng', 'Lớp PY501-A dự kiến khai giảng 06/07/2026. Hiện có 5 học viên đăng ký (2 đã đóng tiền).', 1, 1, '2026-06-10 17:27:36'),
(34, 'Lịch dạy hôm nay', 'Bạn có buổi dạy JS101-B hôm nay (10/06/2026) lúc 18:00–20:00 tại P101.', 2, 0, '2026-06-10 17:27:36'),
(35, 'Bảng lương 05/2026 đã thanh toán', 'Bảng lương tháng 05/2026 đã được xử lý. Số tiền: 6,000,000 VND. Vui lòng kiểm tra.', 2, 1, '2026-06-10 17:27:36'),
(36, 'Yêu cầu học bù được duyệt', 'Yêu cầu học bù ngày 2026-06-18 của bạn (JS101-B) đã được Admin phê duyệt.', 2, 1, '2026-06-10 17:27:36'),
(37, 'Nhắc nhở: điểm danh lớp DB301-A', 'Lớp DB301-A còn 3 buổi học. Vui lòng điểm danh đúng hạn sau mỗi buổi.', 2, 0, '2026-06-10 17:27:36'),
(38, 'Lịch dạy hôm nay', 'Bạn có buổi dạy PHP201-A hôm nay (10/06/2026) lúc 18:00–20:00 tại P201.', 3, 0, '2026-06-10 17:27:36'),
(39, 'Học viên chưa đóng học phí', 'Lớp PHP201-A có 2 học viên (HV006 Linh, HV007 Hùng) chưa đóng học phí.', 3, 0, '2026-06-10 17:27:36'),
(40, 'Bảng lương 05/2026 đã thanh toán', 'Bảng lương tháng 05/2026: 5,400,000 VND đã được thanh toán.', 3, 1, '2026-06-10 17:27:36'),
(41, 'Chào mừng gia nhập EduCenter!', 'Tài khoản của bạn đã được kích hoạt. Lớp PY501-B sẽ khai giảng 06/07/2026.', 28, 1, '2026-06-10 17:27:36'),
(42, 'Bảng lương 05/2026', 'Bảng lương tháng 05/2026 đã được thanh toán. Số tiền: 1,800,000 VND.', 28, 1, '2026-06-10 17:27:36'),
(43, 'Lịch học hôm nay', 'JS101-B: buổi học hôm nay (10/06/2026) lúc 18:00–20:00 tại P101. Đừng quên bài tập!', 4, 0, '2026-06-10 17:27:36'),
(44, 'Xác nhận đăng ký PY501-A', 'Bạn đã đăng ký lớp PY501-A (khai giảng 06/07). Học phí 3,200,000 VND chưa đóng.', 4, 0, '2026-06-10 17:27:36'),
(45, 'Đã thanh toán – JS101-B', 'Học phí lớp JS101-B (3,500,000 VND) đã được xác nhận. Cảm ơn bạn!', 4, 1, '2026-06-10 17:27:36'),
(46, 'Lịch học hôm nay', 'JS101-B: buổi học hôm nay (10/06/2026) lúc 18:00–20:00 tại P101.', 5, 0, '2026-06-10 17:27:36'),
(47, 'Nhắc nhở học phí PY501-A', 'Học phí lớp PY501-A (3,200,000 VND) chưa thanh toán. Hạn đóng: 30/06/2026.', 5, 0, '2026-06-10 17:27:36'),
(48, 'Nhắc nhở học phí JS101-A', 'Học phí lớp JS101-A (3,500,000 VND) chưa thanh toán. Hạn đóng: 30/06/2026.', 6, 0, '2026-06-10 17:27:36'),
(49, 'Kết quả học tập PHP201-A', 'Điểm trung bình của bạn: 9.2 – Loại GIỎI! Xuất sắc!', 6, 1, '2026-06-10 17:27:36'),
(50, 'Nhắc nhở học phí PHP201-A', 'Học phí lớp PHP201-A (3,000,000 VND) chưa thanh toán. Hạn đóng: 30/06/2026.', 34, 0, '2026-06-10 17:27:36'),
(51, 'Nhắc nhở học phí PHP201-A', 'Học phí lớp PHP201-A (3,000,000 VND) chưa thanh toán. Hạn đóng: 30/06/2026.', 35, 0, '2026-06-10 17:27:36'),
(52, 'Lịch Học thường mới', 'Admin đã thêm lịch: Lớp LTJAV08, Thứ bảy, 08:00-10:00 (Học thường) — HV: Nguyễn Văn Dược.', 49, 1, '2026-06-10 17:47:55'),
(53, 'Phân công mới', 'Bạn được phân công dạy thêm.', 50, 0, '2026-06-10 18:01:46'),
(54, 'Lịch Học thường đã cập nhật', 'Admin đã cập nhật lịch: Lớp LTJAV08, Thứ bảy, 08:00-10:00 (Học thường) — HV: Nguyễn Văn Dược.', 49, 0, '2026-06-10 18:17:21'),
(55, 'Lịch Học thường đã cập nhật', 'Admin đã cập nhật lịch: Lớp LTJAV08, Thứ bảy, 08:00-10:00 (Học thường) — HV: Nguyễn Văn Dược.', 50, 0, '2026-06-10 18:17:22'),
(56, 'Thanh toán thành công', 'Bạn đã được xếp vào lớp PY501-A. Lịch học đã được tạo.', 34, 0, '2026-06-10 18:25:08'),
(57, 'Phân công dạy mới', 'Bạn được phân công dạy học viên tại lớp PY501-A.', 28, 0, '2026-06-10 18:25:08'),
(58, 'Thanh toán thành công', 'Bạn đã được xếp vào lớp JS101-A. Lịch học đã được tạo.', 35, 0, '2026-06-12 14:30:10'),
(59, 'Phân công dạy mới', 'Bạn được phân công dạy học viên tại lớp JS101-A.', 51, 0, '2026-06-12 14:30:11'),
(60, 'Thanh toán thành công', 'Bạn đã được xếp vào lớp LPHPNC01. Lịch học đã được tạo.', 49, 0, '2026-06-12 14:36:45'),
(61, 'Phân công dạy mới', 'Bạn được phân công dạy học viên tại lớp LPHPNC01.', 2, 0, '2026-06-12 14:36:46'),
(62, 'Lịch Thi mới', 'Admin đã thêm lịch: Lớp LPHPNC01, ngày 16/06/2026, 18:00-20:00 (Thi) — HV: Nguyễn Văn Dược.', 49, 0, '2026-06-12 14:37:34'),
(63, 'Lịch Thi mới', 'Admin đã thêm lịch: Lớp LPHPNC01, ngày 16/06/2026, 18:00-20:00 (Thi) — HV: Nguyễn Văn Dược.', 2, 0, '2026-06-12 14:37:34'),
(64, 'Lịch Thi đã hủy', 'Admin đã hủy lịch: Lớp LPHPNC01, ngày 16/06/2026, 18:00-20:00 (Thi) — HV: Nguyễn Văn Dược.', 49, 0, '2026-06-12 14:39:37'),
(65, 'Lịch Thi đã hủy', 'Admin đã hủy lịch: Lớp LPHPNC01, ngày 16/06/2026, 18:00-20:00 (Thi) — HV: Nguyễn Văn Dược.', 2, 0, '2026-06-12 14:39:37'),
(66, 'Yêu cầu LEAVE mới', 'Có yêu cầu LEAVE cần duyệt (ngày 2026-06-15).', 1, 0, '2026-06-12 14:40:33'),
(67, 'Bài nộp mới', 'Học viên đã nộp bài (MIDTERM).', 50, 0, '2026-06-12 14:41:20'),
(68, 'Bài đã được chấm', 'Điểm: 5/10. bài làm bình thường', 49, 0, '2026-06-12 14:51:14');

-- --------------------------------------------------------

--
-- Table structure for table `payrolls`
--

CREATE TABLE `payrolls` (
  `id` int NOT NULL,
  `teacher_id` int NOT NULL,
  `month` varchar(7) NOT NULL,
  `teaching_hours` decimal(6,1) DEFAULT '0.0',
  `salary_amount` decimal(12,2) DEFAULT '0.00',
  `payment_status` enum('PENDING','PAID') DEFAULT 'PENDING'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payrolls`
--

INSERT INTO `payrolls` (`id`, `teacher_id`, `month`, `teaching_hours`, `salary_amount`, `payment_status`) VALUES
(1, 1, '2026-04', '32.0', '4800000.00', 'PAID'),
(2, 1, '2026-05', '40.0', '6000000.00', 'PAID'),
(3, 1, '2026-06', '20.0', '3000000.00', 'PENDING'),
(4, 2, '2026-04', '28.0', '4200000.00', 'PAID'),
(5, 2, '2026-05', '36.0', '5400000.00', 'PAID'),
(6, 2, '2026-06', '16.0', '2400000.00', 'PENDING'),
(7, 11, '2026-05', '12.0', '1800000.00', 'PAID'),
(8, 11, '2026-06', '8.0', '1200000.00', 'PENDING');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int NOT NULL,
  `role_name` varchar(60) NOT NULL,
  `description` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `role_name`, `description`) VALUES
(1, 'ADMIN', 'Quản trị viên'),
(2, 'TEACHER', 'Giáo viên'),
(3, 'STUDENT', 'Học viên');

-- --------------------------------------------------------

--
-- Table structure for table `schedules`
--

CREATE TABLE `schedules` (
  `id` int NOT NULL,
  `class_id` int NOT NULL,
  `student_id` int DEFAULT NULL,
  `day_of_week` tinyint NOT NULL,
  `specific_date` date DEFAULT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `schedule_type` enum('REGULAR','EXAM','MAKEUP','EXTRA') DEFAULT 'REGULAR',
  `exam_label` varchar(100) DEFAULT NULL,
  `exam_supervisor` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `schedules`
--

INSERT INTO `schedules` (`id`, `class_id`, `student_id`, `day_of_week`, `specific_date`, `start_time`, `end_time`, `schedule_type`, `exam_label`, `exam_supervisor`) VALUES
(1, 2, 3, 1, NULL, '18:00:00', '20:00:00', 'REGULAR', NULL, NULL),
(2, 2, 3, 4, NULL, '18:00:00', '20:00:00', 'REGULAR', NULL, NULL),
(3, 1, 2, 1, NULL, '18:00:00', '20:00:00', 'REGULAR', NULL, NULL),
(4, 1, 2, 4, NULL, '18:00:00', '20:00:00', 'REGULAR', NULL, NULL),
(5, 1, 2, 2, NULL, '18:00:00', '20:00:00', 'REGULAR', NULL, NULL),
(6, 9, NULL, 4, '2026-04-16', '18:00:00', '20:00:00', 'REGULAR', NULL, NULL),
(7, 9, NULL, 1, '2026-04-20', '18:00:00', '20:00:00', 'REGULAR', NULL, NULL),
(8, 9, NULL, 4, '2026-04-23', '18:00:00', '20:00:00', 'REGULAR', NULL, NULL),
(9, 9, NULL, 1, '2026-04-27', '18:00:00', '20:00:00', 'REGULAR', NULL, NULL),
(10, 9, NULL, 4, '2026-04-30', '18:00:00', '20:00:00', 'REGULAR', NULL, NULL),
(11, 9, NULL, 1, '2026-05-04', '18:00:00', '20:00:00', 'REGULAR', NULL, NULL),
(12, 9, NULL, 4, '2026-05-07', '18:00:00', '20:00:00', 'REGULAR', NULL, NULL),
(13, 9, NULL, 1, '2026-05-11', '18:00:00', '20:00:00', 'REGULAR', NULL, NULL),
(14, 9, NULL, 4, '2026-05-14', '18:00:00', '20:00:00', 'REGULAR', NULL, NULL),
(15, 9, NULL, 1, '2026-05-18', '18:00:00', '20:00:00', 'REGULAR', NULL, NULL),
(16, 9, NULL, 4, '2026-05-21', '18:00:00', '20:00:00', 'REGULAR', NULL, NULL),
(17, 9, NULL, 1, '2026-05-25', '18:00:00', '20:00:00', 'REGULAR', NULL, NULL),
(18, 9, NULL, 4, '2026-05-28', '18:00:00', '20:00:00', 'REGULAR', NULL, NULL),
(19, 9, NULL, 1, '2026-06-01', '18:00:00', '20:00:00', 'REGULAR', NULL, NULL),
(20, 9, NULL, 4, '2026-06-04', '18:00:00', '20:00:00', 'REGULAR', NULL, NULL),
(21, 9, NULL, 1, '2026-06-08', '18:00:00', '20:00:00', 'REGULAR', NULL, NULL),
(22, 9, NULL, 4, '2026-06-11', '18:00:00', '20:00:00', 'REGULAR', NULL, NULL),
(23, 9, NULL, 1, '2026-06-15', '18:00:00', '20:00:00', 'REGULAR', NULL, NULL),
(24, 9, NULL, 4, '2026-06-18', '18:00:00', '20:00:00', 'REGULAR', NULL, NULL),
(25, 9, NULL, 2, '2026-06-16', '18:00:00', '20:00:00', 'EXAM', 'Kiểm tra cuối kỳ', 'Nguyễn Quản Trị'),
(26, 10, NULL, 4, '2026-04-16', '18:00:00', '20:00:00', 'REGULAR', NULL, NULL),
(27, 10, NULL, 1, '2026-04-20', '18:00:00', '20:00:00', 'REGULAR', NULL, NULL),
(28, 10, NULL, 4, '2026-04-23', '18:00:00', '20:00:00', 'REGULAR', NULL, NULL),
(29, 10, NULL, 1, '2026-04-27', '18:00:00', '20:00:00', 'REGULAR', NULL, NULL),
(30, 10, NULL, 4, '2026-04-30', '18:00:00', '20:00:00', 'REGULAR', NULL, NULL),
(31, 10, NULL, 1, '2026-05-04', '18:00:00', '20:00:00', 'REGULAR', NULL, NULL),
(32, 10, NULL, 4, '2026-05-07', '18:00:00', '20:00:00', 'REGULAR', NULL, NULL),
(33, 10, NULL, 1, '2026-05-11', '18:00:00', '20:00:00', 'REGULAR', NULL, NULL),
(34, 10, NULL, 4, '2026-05-14', '18:00:00', '20:00:00', 'REGULAR', NULL, NULL),
(35, 10, NULL, 1, '2026-05-18', '18:00:00', '20:00:00', 'REGULAR', NULL, NULL),
(36, 10, NULL, 4, '2026-05-21', '18:00:00', '20:00:00', 'REGULAR', NULL, NULL),
(37, 10, NULL, 1, '2026-05-25', '18:00:00', '20:00:00', 'REGULAR', NULL, NULL),
(38, 10, NULL, 4, '2026-05-28', '18:00:00', '20:00:00', 'REGULAR', NULL, NULL),
(39, 10, NULL, 1, '2026-06-01', '18:00:00', '20:00:00', 'REGULAR', NULL, NULL),
(40, 10, NULL, 4, '2026-06-04', '18:00:00', '20:00:00', 'REGULAR', NULL, NULL),
(41, 10, NULL, 1, '2026-06-08', '18:00:00', '20:00:00', 'REGULAR', NULL, NULL),
(42, 10, NULL, 4, '2026-06-11', '18:00:00', '20:00:00', 'REGULAR', NULL, NULL),
(43, 10, NULL, 1, '2026-06-15', '18:00:00', '20:00:00', 'REGULAR', NULL, NULL),
(44, 10, NULL, 4, '2026-06-18', '18:00:00', '20:00:00', 'REGULAR', NULL, NULL),
(45, 10, NULL, 2, '2026-06-16', '18:00:00', '20:00:00', 'EXAM', 'Kiểm tra cuối kỳ', 'Nguyễn Quản Trị'),
(46, 11, NULL, 2, '2026-05-12', '08:00:00', '10:00:00', 'REGULAR', NULL, NULL),
(47, 11, NULL, 5, '2026-05-15', '08:00:00', '10:00:00', 'REGULAR', NULL, NULL),
(48, 11, NULL, 2, '2026-05-19', '08:00:00', '10:00:00', 'REGULAR', NULL, NULL),
(49, 11, NULL, 5, '2026-05-22', '08:00:00', '10:00:00', 'REGULAR', NULL, NULL),
(50, 11, NULL, 2, '2026-05-26', '08:00:00', '10:00:00', 'REGULAR', NULL, NULL),
(51, 11, NULL, 5, '2026-05-29', '08:00:00', '10:00:00', 'REGULAR', NULL, NULL),
(52, 11, NULL, 2, '2026-06-02', '08:00:00', '10:00:00', 'REGULAR', NULL, NULL),
(53, 11, NULL, 5, '2026-06-05', '08:00:00', '10:00:00', 'REGULAR', NULL, NULL),
(54, 11, NULL, 2, '2026-06-09', '08:00:00', '10:00:00', 'REGULAR', NULL, NULL),
(55, 11, NULL, 5, '2026-06-12', '08:00:00', '10:00:00', 'REGULAR', NULL, NULL),
(56, 11, NULL, 2, '2026-06-16', '08:00:00', '10:00:00', 'REGULAR', NULL, NULL),
(57, 11, NULL, 5, '2026-06-19', '08:00:00', '10:00:00', 'REGULAR', NULL, NULL),
(58, 11, NULL, 1, '2026-06-15', '08:00:00', '10:00:00', 'EXAM', 'Kiểm tra cuối kỳ', 'Nguyễn Quản Trị'),
(59, 1, NULL, 1, '2026-07-06', '18:00:00', '20:00:00', 'REGULAR', NULL, NULL),
(60, 1, NULL, 4, '2026-07-09', '18:00:00', '20:00:00', 'REGULAR', NULL, NULL),
(61, 1, NULL, 1, '2026-07-13', '18:00:00', '20:00:00', 'REGULAR', NULL, NULL),
(62, 1, NULL, 4, '2026-07-16', '18:00:00', '20:00:00', 'REGULAR', NULL, NULL),
(63, 1, NULL, 1, '2026-07-20', '18:00:00', '20:00:00', 'REGULAR', NULL, NULL),
(64, 1, NULL, 4, '2026-07-23', '18:00:00', '20:00:00', 'REGULAR', NULL, NULL),
(65, 1, NULL, 1, '2026-07-27', '18:00:00', '20:00:00', 'REGULAR', NULL, NULL),
(66, 1, NULL, 4, '2026-07-30', '18:00:00', '20:00:00', 'REGULAR', NULL, NULL),
(67, 1, NULL, 1, '2026-08-03', '18:00:00', '20:00:00', 'REGULAR', NULL, NULL),
(68, 1, NULL, 4, '2026-08-06', '18:00:00', '20:00:00', 'REGULAR', NULL, NULL),
(69, 1, NULL, 1, '2026-08-10', '18:00:00', '20:00:00', 'REGULAR', NULL, NULL),
(70, 1, NULL, 4, '2026-08-13', '18:00:00', '20:00:00', 'REGULAR', NULL, NULL),
(71, 1, NULL, 1, '2026-08-17', '18:00:00', '20:00:00', 'REGULAR', NULL, NULL),
(72, 1, NULL, 4, '2026-08-20', '18:00:00', '20:00:00', 'REGULAR', NULL, NULL),
(73, 1, NULL, 1, '2026-08-24', '18:00:00', '20:00:00', 'REGULAR', NULL, NULL),
(74, 1, NULL, 4, '2026-08-27', '18:00:00', '20:00:00', 'REGULAR', NULL, NULL),
(75, 1, NULL, 1, '2026-08-31', '18:00:00', '20:00:00', 'REGULAR', NULL, NULL),
(76, 1, NULL, 4, '2026-09-03', '18:00:00', '20:00:00', 'REGULAR', NULL, NULL),
(77, 1, NULL, 1, '2026-09-07', '18:00:00', '20:00:00', 'REGULAR', NULL, NULL),
(78, 1, NULL, 4, '2026-09-10', '18:00:00', '20:00:00', 'REGULAR', NULL, NULL),
(79, 1, NULL, 4, '2026-09-10', '18:00:00', '20:00:00', 'EXAM', 'Kiểm tra cuối kỳ', 'Nguyễn Quản Trị'),
(80, 13, NULL, 1, '2026-07-06', '08:00:00', '10:00:00', 'REGULAR', NULL, NULL),
(81, 13, NULL, 4, '2026-07-09', '08:00:00', '10:00:00', 'REGULAR', NULL, NULL),
(82, 13, NULL, 1, '2026-07-13', '08:00:00', '10:00:00', 'REGULAR', NULL, NULL),
(83, 13, NULL, 4, '2026-07-16', '08:00:00', '10:00:00', 'REGULAR', NULL, NULL),
(84, 13, NULL, 1, '2026-07-20', '08:00:00', '10:00:00', 'REGULAR', NULL, NULL),
(85, 13, NULL, 4, '2026-07-23', '08:00:00', '10:00:00', 'REGULAR', NULL, NULL),
(86, 13, NULL, 1, '2026-07-27', '08:00:00', '10:00:00', 'REGULAR', NULL, NULL),
(87, 13, NULL, 4, '2026-07-30', '08:00:00', '10:00:00', 'REGULAR', NULL, NULL),
(88, 13, NULL, 1, '2026-08-03', '08:00:00', '10:00:00', 'REGULAR', NULL, NULL),
(89, 13, NULL, 4, '2026-08-06', '08:00:00', '10:00:00', 'REGULAR', NULL, NULL),
(90, 13, NULL, 1, '2026-08-10', '08:00:00', '10:00:00', 'REGULAR', NULL, NULL),
(91, 13, NULL, 4, '2026-08-13', '08:00:00', '10:00:00', 'REGULAR', NULL, NULL),
(92, 13, NULL, 1, '2026-08-17', '08:00:00', '10:00:00', 'REGULAR', NULL, NULL),
(93, 13, NULL, 4, '2026-08-20', '08:00:00', '10:00:00', 'REGULAR', NULL, NULL),
(94, 13, NULL, 1, '2026-08-24', '08:00:00', '10:00:00', 'REGULAR', NULL, NULL),
(95, 13, NULL, 4, '2026-08-27', '08:00:00', '10:00:00', 'REGULAR', NULL, NULL),
(96, 13, NULL, 1, '2026-08-31', '08:00:00', '10:00:00', 'REGULAR', NULL, NULL),
(97, 13, NULL, 4, '2026-09-03', '08:00:00', '10:00:00', 'REGULAR', NULL, NULL),
(98, 13, NULL, 1, '2026-09-07', '08:00:00', '10:00:00', 'REGULAR', NULL, NULL),
(99, 13, NULL, 4, '2026-09-10', '08:00:00', '10:00:00', 'REGULAR', NULL, NULL),
(100, 13, NULL, 4, '2026-09-10', '08:00:00', '10:00:00', 'EXAM', 'Kiểm tra cuối kỳ', 'Nguyễn Quản Trị'),
(101, 2, NULL, 2, '2026-07-07', '18:00:00', '20:00:00', 'REGULAR', NULL, NULL),
(102, 2, NULL, 5, '2026-07-10', '18:00:00', '20:00:00', 'REGULAR', NULL, NULL),
(103, 2, NULL, 2, '2026-07-14', '18:00:00', '20:00:00', 'REGULAR', NULL, NULL),
(104, 2, NULL, 5, '2026-07-17', '18:00:00', '20:00:00', 'REGULAR', NULL, NULL),
(105, 2, NULL, 2, '2026-07-21', '18:00:00', '20:00:00', 'REGULAR', NULL, NULL),
(106, 2, NULL, 5, '2026-07-24', '18:00:00', '20:00:00', 'REGULAR', NULL, NULL),
(107, 2, NULL, 2, '2026-07-28', '18:00:00', '20:00:00', 'REGULAR', NULL, NULL),
(108, 2, NULL, 5, '2026-07-31', '18:00:00', '20:00:00', 'REGULAR', NULL, NULL),
(109, 2, NULL, 2, '2026-08-04', '18:00:00', '20:00:00', 'REGULAR', NULL, NULL),
(110, 2, NULL, 5, '2026-08-07', '18:00:00', '20:00:00', 'REGULAR', NULL, NULL),
(111, 2, NULL, 2, '2026-08-11', '18:00:00', '20:00:00', 'REGULAR', NULL, NULL),
(112, 2, NULL, 5, '2026-08-14', '18:00:00', '20:00:00', 'REGULAR', NULL, NULL),
(113, 2, NULL, 2, '2026-08-18', '18:00:00', '20:00:00', 'REGULAR', NULL, NULL),
(114, 2, NULL, 5, '2026-08-21', '18:00:00', '20:00:00', 'REGULAR', NULL, NULL),
(115, 2, NULL, 2, '2026-08-25', '18:00:00', '20:00:00', 'REGULAR', NULL, NULL),
(116, 2, NULL, 5, '2026-08-28', '18:00:00', '20:00:00', 'REGULAR', NULL, NULL),
(117, 2, NULL, 2, '2026-09-01', '18:00:00', '20:00:00', 'REGULAR', NULL, NULL),
(118, 2, NULL, 5, '2026-09-04', '18:00:00', '20:00:00', 'REGULAR', NULL, NULL),
(119, 2, NULL, 2, '2026-09-08', '18:00:00', '20:00:00', 'REGULAR', NULL, NULL),
(120, 2, NULL, 5, '2026-09-11', '18:00:00', '20:00:00', 'REGULAR', NULL, NULL),
(121, 2, NULL, 5, '2026-09-11', '18:00:00', '20:00:00', 'EXAM', 'Kiểm tra cuối kỳ', 'Nguyễn Quản Trị'),
(122, 15, NULL, 2, '2026-07-07', '08:00:00', '10:00:00', 'REGULAR', NULL, NULL),
(123, 15, NULL, 5, '2026-07-10', '08:00:00', '10:00:00', 'REGULAR', NULL, NULL),
(124, 15, NULL, 2, '2026-07-14', '08:00:00', '10:00:00', 'REGULAR', NULL, NULL),
(125, 15, NULL, 5, '2026-07-17', '08:00:00', '10:00:00', 'REGULAR', NULL, NULL),
(126, 15, NULL, 2, '2026-07-21', '08:00:00', '10:00:00', 'REGULAR', NULL, NULL),
(127, 15, NULL, 5, '2026-07-24', '08:00:00', '10:00:00', 'REGULAR', NULL, NULL),
(128, 15, NULL, 2, '2026-07-28', '08:00:00', '10:00:00', 'REGULAR', NULL, NULL),
(129, 15, NULL, 5, '2026-07-31', '08:00:00', '10:00:00', 'REGULAR', NULL, NULL),
(130, 15, NULL, 2, '2026-08-04', '08:00:00', '10:00:00', 'REGULAR', NULL, NULL),
(131, 15, NULL, 5, '2026-08-07', '08:00:00', '10:00:00', 'REGULAR', NULL, NULL),
(132, 15, NULL, 2, '2026-08-11', '08:00:00', '10:00:00', 'REGULAR', NULL, NULL),
(133, 15, NULL, 5, '2026-08-14', '08:00:00', '10:00:00', 'REGULAR', NULL, NULL),
(134, 15, NULL, 2, '2026-08-18', '08:00:00', '10:00:00', 'REGULAR', NULL, NULL),
(135, 15, NULL, 5, '2026-08-21', '08:00:00', '10:00:00', 'REGULAR', NULL, NULL),
(136, 15, NULL, 2, '2026-08-25', '08:00:00', '10:00:00', 'REGULAR', NULL, NULL),
(137, 15, NULL, 5, '2026-08-28', '08:00:00', '10:00:00', 'REGULAR', NULL, NULL),
(138, 15, NULL, 2, '2026-09-01', '08:00:00', '10:00:00', 'REGULAR', NULL, NULL),
(139, 15, NULL, 5, '2026-09-04', '08:00:00', '10:00:00', 'REGULAR', NULL, NULL),
(140, 15, NULL, 2, '2026-09-08', '08:00:00', '10:00:00', 'REGULAR', NULL, NULL),
(141, 15, NULL, 5, '2026-09-11', '08:00:00', '10:00:00', 'REGULAR', NULL, NULL),
(142, 15, NULL, 5, '2026-09-11', '08:00:00', '10:00:00', 'EXAM', 'Kiểm tra cuối kỳ', 'Nguyễn Quản Trị'),
(143, 23, 29, 6, NULL, '08:00:00', '10:00:00', 'REGULAR', NULL, NULL),
(150, 1, 18, 1, NULL, '18:00:00', '20:00:00', 'REGULAR', NULL, NULL),
(151, 1, 18, 4, NULL, '18:00:00', '20:00:00', 'REGULAR', NULL, NULL),
(154, 2, 19, 1, NULL, '18:00:00', '20:00:00', 'REGULAR', NULL, NULL),
(155, 2, 19, 4, NULL, '18:00:00', '20:00:00', 'REGULAR', NULL, NULL),
(156, 24, 29, 2, NULL, '18:00:00', '20:00:00', 'REGULAR', NULL, NULL),
(157, 24, 29, 6, NULL, '18:00:00', '20:00:00', 'REGULAR', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `semesters`
--

CREATE TABLE `semesters` (
  `id` int NOT NULL,
  `semester_name` varchar(100) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('UPCOMING','ONGOING','COMPLETED') DEFAULT 'UPCOMING'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `semesters`
--

INSERT INTO `semesters` (`id`, `semester_name`, `start_date`, `end_date`, `status`) VALUES
(1, 'Học kỳ 1 - 2026', '2026-01-01', '2026-06-30', 'ONGOING'),
(2, 'Học kỳ 2 - 2026', '2026-07-01', '2026-12-31', 'UPCOMING'),
(3, 'Học kỳ 1 - 2026', '2026-01-01', '2026-06-30', 'ONGOING'),
(4, 'Học kỳ 2 - 2026', '2026-07-01', '2026-12-31', 'UPCOMING'),
(5, 'Học kỳ 1 - 2026', '2026-01-01', '2026-06-30', 'ONGOING'),
(6, 'Học kỳ 2 - 2026', '2026-07-01', '2026-12-31', 'UPCOMING'),
(7, 'Học kỳ 1 - 2026', '2026-01-01', '2026-06-30', 'ONGOING'),
(8, 'Học kỳ 2 - 2026', '2026-07-01', '2026-12-31', 'UPCOMING'),
(9, 'Học kỳ 1 – 2026', '2026-01-01', '2026-06-30', 'ONGOING'),
(10, 'Học kỳ 2 – 2026', '2026-07-01', '2026-12-31', 'UPCOMING'),
(11, 'Học kỳ 1 – 2026', '2026-01-01', '2026-06-30', 'ONGOING'),
(12, 'Học kỳ 2 – 2026', '2026-07-01', '2026-12-31', 'UPCOMING');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `student_code` varchar(30) NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `parent_phone` varchar(20) DEFAULT NULL,
  `status` enum('ACTIVE','INACTIVE','GRADUATED') DEFAULT 'ACTIVE'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `user_id`, `student_code`, `date_of_birth`, `parent_phone`, `status`) VALUES
(1, 4, 'HV001', '2002-05-10', NULL, 'ACTIVE'),
(2, 5, 'HV002', '2001-11-20', NULL, 'ACTIVE'),
(3, 6, 'HV003', '2000-07-03', NULL, 'ACTIVE'),
(16, 32, 'HV004', '2003-02-14', '0904000004', 'ACTIVE'),
(17, 33, 'HV005', '2002-08-22', '0904000005', 'ACTIVE'),
(18, 34, 'HV006', '2001-12-05', '0904000006', 'ACTIVE'),
(19, 35, 'HV007', '2003-04-17', '0904000007', 'ACTIVE'),
(20, 36, 'HV008', '2002-09-30', '0904000008', 'ACTIVE'),
(29, 49, 'HV009', '2002-06-06', '0928817228', 'ACTIVE');

-- --------------------------------------------------------

--
-- Table structure for table `student_evaluations`
--

CREATE TABLE `student_evaluations` (
  `id` int NOT NULL,
  `student_id` int NOT NULL,
  `class_id` int NOT NULL,
  `teacher_id` int DEFAULT NULL,
  `avg_score` decimal(5,2) DEFAULT '0.00',
  `level` enum('GIOI','KHA','TRUNG_BINH','KEM') DEFAULT 'TRUNG_BINH',
  `retake_needed` tinyint(1) DEFAULT '0',
  `teacher_comment` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `student_evaluations`
--

INSERT INTO `student_evaluations` (`id`, `student_id`, `class_id`, `teacher_id`, `avg_score`, `level`, `retake_needed`, `teacher_comment`, `created_at`, `updated_at`) VALUES
(1, 1, 9, 1, '8.50', 'GIOI', 0, 'Học viên chăm chỉ, nắm vững kiến thức. Đề nghị tham gia nhóm nâng cao.', '2026-06-10 17:25:39', '2026-06-10 17:25:39'),
(2, 2, 9, 1, '7.20', 'KHA', 0, 'Tiến bộ tốt, cần luyện thêm bài tập thực hành.', '2026-06-10 17:25:39', '2026-06-10 17:25:39'),
(3, 16, 9, 1, '5.40', 'TRUNG_BINH', 1, 'Cần cố gắng hơn, dự kiến phải thi lại.', '2026-06-10 17:25:39', '2026-06-10 17:25:39'),
(4, 17, 9, 1, '7.80', 'KHA', 0, 'Hoàn thành tốt, cần thêm kinh nghiệm thực tế.', '2026-06-10 17:25:39', '2026-06-10 17:25:39'),
(5, 3, 10, 2, '9.20', 'GIOI', 0, 'Xuất sắc, lập trình backend rất chắc. Nên học thêm framework Laravel.', '2026-06-10 17:25:39', '2026-06-10 17:25:39'),
(6, 16, 10, 2, '6.80', 'KHA', 0, 'Hoàn thành tốt các bài tập cơ bản.', '2026-06-10 17:25:39', '2026-06-10 17:25:39'),
(7, 1, 11, 1, '8.00', 'GIOI', 0, 'Nắm vững SQL, tối ưu query tốt.', '2026-06-10 17:25:39', '2026-06-10 17:25:39'),
(8, 3, 11, 1, '7.50', 'KHA', 0, 'Tốt ở lý thuyết, cần thực hành thêm.', '2026-06-10 17:25:39', '2026-06-10 17:25:39'),
(9, 17, 11, 1, '6.20', 'KHA', 0, 'Đạt yêu cầu, tiếp tục ôn tập.', '2026-06-10 17:25:39', '2026-06-10 17:25:39'),
(10, 29, 23, 15, '5.00', 'TRUNG_BINH', 0, NULL, '2026-06-12 14:51:14', '2026-06-12 14:51:14');

-- --------------------------------------------------------

--
-- Table structure for table `submissions`
--

CREATE TABLE `submissions` (
  `id` int NOT NULL,
  `student_id` int NOT NULL,
  `class_id` int NOT NULL,
  `type` enum('ASSIGNMENT','MIDTERM','FINAL') DEFAULT 'ASSIGNMENT',
  `file_path` varchar(500) DEFAULT NULL,
  `submitted_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('PENDING','GRADED') DEFAULT 'PENDING'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `submissions`
--

INSERT INTO `submissions` (`id`, `student_id`, `class_id`, `type`, `file_path`, `submitted_at`, `status`) VALUES
(1, 1, 9, 'ASSIGNMENT', NULL, '2026-06-10 17:25:38', 'GRADED'),
(2, 2, 9, 'ASSIGNMENT', NULL, '2026-06-10 17:25:38', 'GRADED'),
(3, 16, 9, 'ASSIGNMENT', NULL, '2026-06-10 17:25:38', 'PENDING'),
(4, 17, 9, 'ASSIGNMENT', NULL, '2026-06-10 17:25:38', 'GRADED'),
(5, 1, 9, 'MIDTERM', NULL, '2026-06-10 17:25:38', 'GRADED'),
(6, 2, 9, 'MIDTERM', NULL, '2026-06-10 17:25:38', 'GRADED'),
(7, 3, 10, 'ASSIGNMENT', NULL, '2026-06-10 17:25:38', 'GRADED'),
(8, 16, 10, 'ASSIGNMENT', NULL, '2026-06-10 17:25:38', 'GRADED'),
(9, 3, 10, 'MIDTERM', NULL, '2026-06-10 17:25:38', 'GRADED'),
(10, 1, 11, 'ASSIGNMENT', NULL, '2026-06-10 17:25:38', 'GRADED'),
(11, 17, 11, 'ASSIGNMENT', NULL, '2026-06-10 17:25:39', 'PENDING'),
(12, 29, 23, 'MIDTERM', 'uploads/submissions/pdf_6a2c1a8fefd540.35061256.pdf', '2026-06-12 14:41:20', 'GRADED');

-- --------------------------------------------------------

--
-- Table structure for table `surveys`
--

CREATE TABLE `surveys` (
  `id` int NOT NULL,
  `class_id` int NOT NULL,
  `title` varchar(200) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `surveys`
--

INSERT INTO `surveys` (`id`, `class_id`, `title`, `created_at`) VALUES
(1, 9, 'Khảo sát chất lượng giảng dạy – JS101-B tháng 6/2026', '2026-06-10 17:25:39'),
(2, 10, 'Khảo sát cuối kỳ – PHP201-A', '2026-06-10 17:25:39');

-- --------------------------------------------------------

--
-- Table structure for table `survey_responses`
--

CREATE TABLE `survey_responses` (
  `id` int NOT NULL,
  `survey_id` int NOT NULL,
  `student_id` int NOT NULL,
  `teacher_id` int NOT NULL,
  `rating` tinyint NOT NULL,
  `comment` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `survey_responses`
--

INSERT INTO `survey_responses` (`id`, `survey_id`, `student_id`, `teacher_id`, `rating`, `comment`, `created_at`) VALUES
(1, 1, 1, 1, 5, 'Giáo viên nhiệt tình, dễ hiểu. Rất hài lòng!', '2026-06-10 17:25:39'),
(2, 1, 2, 1, 4, 'Bài giảng hay, mong có thêm ví dụ thực tế.', '2026-06-10 17:25:39'),
(3, 1, 16, 1, 3, 'Tốc độ giảng hơi nhanh, khó theo kịp.', '2026-06-10 17:25:39'),
(4, 1, 17, 1, 5, 'Rất tốt! Hiểu rõ JavaScript hơn nhiều.', '2026-06-10 17:25:39'),
(5, 2, 3, 2, 5, 'Cô Hoa dạy rất kỹ, code ví dụ thực tế.', '2026-06-10 17:25:39'),
(6, 2, 16, 2, 4, 'Nội dung tốt, nên có thêm bài tập nhóm.', '2026-06-10 17:25:39');

-- --------------------------------------------------------

--
-- Table structure for table `teachers`
--

CREATE TABLE `teachers` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `teacher_code` varchar(30) NOT NULL,
  `specialization` varchar(200) NOT NULL,
  `hire_date` date DEFAULT NULL,
  `teacher_type` enum('FULL_TIME','VISITING') DEFAULT 'FULL_TIME',
  `standard_hours` decimal(5,1) DEFAULT '40.0',
  `status` enum('ACTIVE','INACTIVE') DEFAULT 'ACTIVE'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `teachers`
--

INSERT INTO `teachers` (`id`, `user_id`, `teacher_code`, `specialization`, `hire_date`, `teacher_type`, `standard_hours`, `status`) VALUES
(1, 2, 'GV001', 'Lập trình Web & JavaScript', '2023-01-15', 'FULL_TIME', '40.0', 'ACTIVE'),
(2, 3, 'GV002', 'Cơ sở dữ liệu & PHP Backend', '2023-03-01', 'FULL_TIME', '40.0', 'ACTIVE'),
(11, 28, 'GV003', 'Lập trình Python & Data Science', '2024-01-10', 'VISITING', '20.0', 'ACTIVE'),
(15, 50, 'GV0089', 'Lập trình', '2026-01-11', 'FULL_TIME', '40.0', 'ACTIVE'),
(16, 51, 'GV0085', 'Lập trình AI', '2026-06-10', 'FULL_TIME', '40.0', 'ACTIVE');

-- --------------------------------------------------------

--
-- Table structure for table `teacher_assignments`
--

CREATE TABLE `teacher_assignments` (
  `id` int NOT NULL,
  `teacher_id` int NOT NULL,
  `class_id` int NOT NULL,
  `student_id` int DEFAULT NULL,
  `day_of_week` tinyint DEFAULT NULL,
  `scenario_name` varchar(60) DEFAULT 'FINAL',
  `assigned_by` int DEFAULT NULL,
  `assigned_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `assignment_status` enum('PENDING','CONFIRMED','CANCELLED') DEFAULT 'CONFIRMED'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `teacher_assignments`
--

INSERT INTO `teacher_assignments` (`id`, `teacher_id`, `class_id`, `student_id`, `day_of_week`, `scenario_name`, `assigned_by`, `assigned_at`, `assignment_status`) VALUES
(1, 1, 2, 3, 1, 'FINAL', 1, '2026-06-10 07:18:42', 'CONFIRMED'),
(2, 1, 2, 3, 4, 'FINAL', 1, '2026-06-10 07:18:42', 'CONFIRMED'),
(3, 2, 1, 2, 1, 'FINAL', 1, '2026-06-10 07:18:54', 'CONFIRMED'),
(4, 2, 1, 2, 4, 'FINAL', 1, '2026-06-10 07:18:54', 'CONFIRMED'),
(5, 1, 9, NULL, 1, 'FINAL', NULL, '2026-06-10 17:25:29', 'CONFIRMED'),
(6, 1, 9, NULL, 4, 'FINAL', NULL, '2026-06-10 17:25:29', 'CONFIRMED'),
(7, 2, 10, NULL, 1, 'FINAL', NULL, '2026-06-10 17:25:29', 'CONFIRMED'),
(8, 2, 10, NULL, 4, 'FINAL', NULL, '2026-06-10 17:25:29', 'CONFIRMED'),
(9, 1, 11, NULL, 2, 'FINAL', NULL, '2026-06-10 17:25:29', 'CONFIRMED'),
(10, 1, 11, NULL, 5, 'FINAL', NULL, '2026-06-10 17:25:29', 'CONFIRMED'),
(11, 1, 1, NULL, 1, 'FINAL', NULL, '2026-06-10 17:25:29', 'CONFIRMED'),
(12, 1, 1, NULL, 4, 'FINAL', NULL, '2026-06-10 17:25:29', 'CONFIRMED'),
(13, 11, 13, NULL, 1, 'FINAL', NULL, '2026-06-10 17:25:29', 'CONFIRMED'),
(14, 11, 13, NULL, 4, 'FINAL', NULL, '2026-06-10 17:25:29', 'CONFIRMED'),
(15, 2, 2, NULL, 2, 'FINAL', NULL, '2026-06-10 17:25:29', 'CONFIRMED'),
(16, 2, 2, NULL, 5, 'FINAL', NULL, '2026-06-10 17:25:29', 'CONFIRMED'),
(17, 2, 15, NULL, 2, 'FINAL', NULL, '2026-06-10 17:25:29', 'CONFIRMED'),
(18, 2, 15, NULL, 5, 'FINAL', NULL, '2026-06-10 17:25:29', 'CONFIRMED'),
(19, 15, 23, 29, NULL, 'FINAL', 1, '2026-06-10 18:01:45', 'CONFIRMED'),
(20, 11, 1, 18, 1, 'FINAL', 1, '2026-06-10 18:25:08', 'CONFIRMED'),
(21, 11, 1, 18, 4, 'FINAL', 1, '2026-06-10 18:25:08', 'CONFIRMED'),
(22, 16, 2, 19, 1, 'FINAL', 1, '2026-06-12 14:30:10', 'CONFIRMED'),
(23, 16, 2, 19, 4, 'FINAL', 1, '2026-06-12 14:30:10', 'CONFIRMED'),
(24, 1, 24, 29, 2, 'FINAL', 1, '2026-06-12 14:36:45', 'CONFIRMED'),
(25, 1, 24, 29, 6, 'FINAL', 1, '2026-06-12 14:36:45', 'CONFIRMED');

-- --------------------------------------------------------

--
-- Table structure for table `tuition_payments`
--

CREATE TABLE `tuition_payments` (
  `id` int NOT NULL,
  `student_id` int NOT NULL,
  `class_id` int DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL,
  `payment_date` date DEFAULT NULL,
  `payment_method` varchar(40) DEFAULT NULL,
  `payment_status` enum('UNPAID','COMPLETED','REFUNDED') DEFAULT 'UNPAID',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tuition_payments`
--

INSERT INTO `tuition_payments` (`id`, `student_id`, `class_id`, `amount`, `payment_date`, `payment_method`, `payment_status`, `created_at`) VALUES
(1, 2, 1, '3200000.00', '2026-06-10', 'CASH', 'COMPLETED', '2026-06-10 05:24:32'),
(2, 3, 2, '3500000.00', '2026-06-10', 'CASH', 'COMPLETED', '2026-06-10 05:24:32'),
(3, 2, 1, '3200000.00', '2026-06-10', NULL, 'UNPAID', '2026-06-10 07:57:50'),
(4, 3, 2, '3500000.00', '2026-06-10', NULL, 'UNPAID', '2026-06-10 07:57:50'),
(5, 1, 9, '3500000.00', '2026-04-10', 'CASH', 'COMPLETED', '2026-06-10 17:25:33'),
(6, 2, 9, '3500000.00', '2026-04-11', 'BANK_TRANSFER', 'COMPLETED', '2026-06-10 17:25:33'),
(7, 16, 9, '3500000.00', '2026-04-12', 'CASH', 'COMPLETED', '2026-06-10 17:25:34'),
(8, 17, 9, '3500000.00', '2026-04-13', 'MOMO', 'COMPLETED', '2026-06-10 17:25:34'),
(9, 3, 10, '3000000.00', '2026-04-10', 'BANK_TRANSFER', 'COMPLETED', '2026-06-10 17:25:34'),
(10, 16, 10, '3000000.00', '2026-04-12', 'CASH', 'COMPLETED', '2026-06-10 17:25:34'),
(11, 1, 11, '2500000.00', '2026-05-08', 'CASH', 'COMPLETED', '2026-06-10 17:25:34'),
(12, 3, 11, '2500000.00', '2026-05-09', 'MOMO', 'COMPLETED', '2026-06-10 17:25:34'),
(13, 17, 11, '2500000.00', '2026-05-08', 'BANK_TRANSFER', 'COMPLETED', '2026-06-10 17:25:34'),
(14, 16, 1, '3200000.00', '2026-06-02', 'MOMO', 'COMPLETED', '2026-06-10 17:25:34'),
(15, 17, 1, '3200000.00', '2026-06-02', 'BANK_TRANSFER', 'COMPLETED', '2026-06-10 17:25:34'),
(16, 20, 2, '3500000.00', '2026-06-03', 'CASH', 'COMPLETED', '2026-06-10 17:25:34'),
(17, 2, 13, '3200000.00', '2026-06-04', 'CASH', 'COMPLETED', '2026-06-10 17:25:34'),
(18, 3, 13, '3200000.00', '2026-06-04', 'MOMO', 'COMPLETED', '2026-06-10 17:25:34'),
(19, 18, 10, '3000000.00', '2026-06-11', NULL, 'UNPAID', '2026-06-10 17:25:34'),
(20, 19, 10, '3000000.00', '2026-06-11', NULL, 'UNPAID', '2026-06-10 17:25:34'),
(21, 20, 9, '3500000.00', '2026-06-11', NULL, 'UNPAID', '2026-06-10 17:25:34'),
(22, 1, 1, '3200000.00', '2026-06-11', NULL, 'UNPAID', '2026-06-10 17:25:34'),
(23, 18, 1, '3200000.00', '2026-06-11', 'CASH', 'COMPLETED', '2026-06-10 17:25:34'),
(24, 19, 2, '3500000.00', '2026-06-12', 'BANK_TRANSFER', 'COMPLETED', '2026-06-10 17:25:34'),
(26, 29, 23, '3500000.00', '2026-06-11', NULL, 'COMPLETED', '2026-06-10 17:47:07'),
(27, 29, 24, '5500001.00', '2026-06-12', 'CASH', 'COMPLETED', '2026-06-12 14:35:10');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `status` enum('ACTIVE','INACTIVE') DEFAULT 'ACTIVE',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `password_hash`, `phone`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Nguyễn Quản Trị', 'admin@edu.vn', '$2y$10$d5ztpk8oNAHR82czg3k3G.tFIKiNYH6QfCXOYvtQZol1UMOJCH862', '0901000001', 'ACTIVE', '2026-06-10 04:34:11', '2026-06-10 04:34:11'),
(2, 'Trần Minh Tuấn', 'tuan.gv@edu.vn', '$2y$10$NmJlSKyyc7.PA58VFcA8qOLijXfh55dz5LPnDKogw.N.L8GawHjDC', '0902000001', 'ACTIVE', '2026-06-10 04:34:11', '2026-06-10 04:34:11'),
(3, 'Lê Thị Hoa', 'hoa.gv@edu.vn', '$2y$10$jfjW80UIlppfn14Z/F3lwe4LrLjpl9VN6VciKAVJcxKyi9ESd8fRa', '0902000002', 'ACTIVE', '2026-06-10 04:34:11', '2026-06-10 04:34:11'),
(4, 'Phạm Văn An', 'an.hv@edu.vn', '$2y$10$bASFW2OAd/BNxaUr29tQFuJSfXffR7ysnFHTyXnRiAEQ0glfmJNjy', '0903000001', 'ACTIVE', '2026-06-10 04:34:11', '2026-06-10 04:34:11'),
(5, 'Nguyễn Thị Bình', 'binh.hv@edu.vn', '$2y$10$f81E0t4uvdIlSzIwaRUWsudMgYaNcmRhrubkFUIT.QEKa/xQARPD2', '0903000002', 'ACTIVE', '2026-06-10 04:34:11', '2026-06-10 04:34:11'),
(6, 'Hoàng Minh Cường', 'cuong.hv@edu.vn', '$2y$10$NzLRstdQCmtyvOtsYwukfuUmswKKMmM.1d1V7/LyIcsdz4DmKo7de', '0903000003', 'ACTIVE', '2026-06-10 04:34:11', '2026-06-10 04:34:11'),
(28, 'Nguyễn Minh Đức', 'duc.gv@edu.vn', '$2y$10$GAunDbeHOJ7TE.nhNr1awOp27L5tbyw6VAWQcQvHr8.1Q4MOq7iui', '0902000003', 'ACTIVE', '2026-06-10 17:25:27', '2026-06-10 17:25:27'),
(32, 'Trần Thị Mai', 'mai.hv@edu.vn', '$2y$10$b0aR0a3M3JSqxx9DVQQBUOCIuX0v5zwdqcOVdEwKTNLkzuwAu8hrW', '0903000004', 'ACTIVE', '2026-06-10 17:25:27', '2026-06-10 17:25:27'),
(33, 'Lê Văn Khoa', 'khoa.hv@edu.vn', '$2y$10$ieXLImfkbdZGEJsOW47CO.MKg4z8fJZbzVTUz21tqy.LIfAYoOi8.', '0903000005', 'ACTIVE', '2026-06-10 17:25:28', '2026-06-10 17:25:28'),
(34, 'Phạm Thị Linh', 'linh.hv@edu.vn', '$2y$10$YT47eGE5l5GDmLWFBmB4auEPIgva3et4RlopzaKT78VG4Y/Cfktde', '0903000006', 'ACTIVE', '2026-06-10 17:25:28', '2026-06-10 17:25:28'),
(35, 'Võ Văn Hùng', 'hung.hv@edu.vn', '$2y$10$R8UTkz38Dh1evl2eT7Xo.urNEeAgFkNyP9HpgNt3eq/sP6nn.5vny', '0903000007', 'ACTIVE', '2026-06-10 17:25:28', '2026-06-10 17:25:28'),
(36, 'Đặng Thị Thu', 'thu.hv@edu.vn', '$2y$10$KZzO0eBp1Eh.SZ86ABuBaul7lVNYsFPOKopJ0PV1oRHXhWQbpPFba', '0903000008', 'ACTIVE', '2026-06-10 17:25:28', '2026-06-10 17:25:28'),
(49, 'Nguyễn Văn Dược', 'duocnv@edu.vn', '$2y$10$o.inDt9t0i0Nww/TIJuO/eZjREwua8jFBiRGn.CYQHJ00/h8GBhR.', '0359020898', 'ACTIVE', '2026-06-10 17:46:50', '2026-06-10 17:46:50'),
(50, 'Trần Trung Đức', 'trantrungduc@edu.vn', '$2y$10$Cxcd7yXYh5GLUKFB2kCiyeEgdJkDseuC2hAi9baXVBNIkTgT9zz/O', '0359020885', 'ACTIVE', '2026-06-10 17:58:24', '2026-06-10 17:58:24'),
(51, 'Nguyễn Văn B', 'nguyenvanb@edu.vn', '$2y$10$5OxheSFVP3rT/fR9YCUiHuc5gYLAQUDXkBMckdOQC8JEsU81SqoRW', '0359020888', 'ACTIVE', '2026-06-12 14:29:52', '2026-06-12 14:29:52');

-- --------------------------------------------------------

--
-- Table structure for table `user_roles`
--

CREATE TABLE `user_roles` (
  `user_id` int NOT NULL,
  `role_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_roles`
--

INSERT INTO `user_roles` (`user_id`, `role_id`) VALUES
(1, 1),
(2, 2),
(3, 2),
(28, 2),
(50, 2),
(51, 2),
(4, 3),
(5, 3),
(6, 3),
(32, 3),
(33, 3),
(34, 3),
(35, 3),
(36, 3),
(49, 3);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `class_id` (`class_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `classes`
--
ALTER TABLE `classes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `class_code` (`class_code`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `teacher_id` (`teacher_id`),
  ADD KEY `classroom_id` (`classroom_id`),
  ADD KEY `semester_id` (`semester_id`);

--
-- Indexes for table `classrooms`
--
ALTER TABLE `classrooms`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `room_name` (`room_name`);

--
-- Indexes for table `class_plans`
--
ALTER TABLE `class_plans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `semester_id` (`semester_id`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `course_code` (`course_code`);

--
-- Indexes for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_enroll` (`student_id`,`class_id`),
  ADD KEY `class_id` (`class_id`);

--
-- Indexes for table `grades`
--
ALTER TABLE `grades`
  ADD PRIMARY KEY (`id`),
  ADD KEY `submission_id` (`submission_id`),
  ADD KEY `teacher_id` (`teacher_id`);

--
-- Indexes for table `leave_requests`
--
ALTER TABLE `leave_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `class_id` (`class_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `receiver_id` (`receiver_id`);

--
-- Indexes for table `payrolls`
--
ALTER TABLE `payrolls`
  ADD PRIMARY KEY (`id`),
  ADD KEY `teacher_id` (`teacher_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `role_name` (`role_name`);

--
-- Indexes for table `schedules`
--
ALTER TABLE `schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `class_id` (`class_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `semesters`
--
ALTER TABLE `semesters`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `student_code` (`student_code`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `student_evaluations`
--
ALTER TABLE `student_evaluations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_stu_class` (`student_id`,`class_id`),
  ADD KEY `class_id` (`class_id`),
  ADD KEY `teacher_id` (`teacher_id`);

--
-- Indexes for table `submissions`
--
ALTER TABLE `submissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `class_id` (`class_id`);

--
-- Indexes for table `surveys`
--
ALTER TABLE `surveys`
  ADD PRIMARY KEY (`id`),
  ADD KEY `class_id` (`class_id`);

--
-- Indexes for table `survey_responses`
--
ALTER TABLE `survey_responses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `survey_id` (`survey_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `teacher_id` (`teacher_id`);

--
-- Indexes for table `teachers`
--
ALTER TABLE `teachers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `teacher_code` (`teacher_code`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `teacher_assignments`
--
ALTER TABLE `teacher_assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `teacher_id` (`teacher_id`),
  ADD KEY `class_id` (`class_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `tuition_payments`
--
ALTER TABLE `tuition_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `class_id` (`class_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD PRIMARY KEY (`user_id`,`role_id`),
  ADD KEY `role_id` (`role_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=124;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `classes`
--
ALTER TABLE `classes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `classrooms`
--
ALTER TABLE `classrooms`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `class_plans`
--
ALTER TABLE `class_plans`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `enrollments`
--
ALTER TABLE `enrollments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `grades`
--
ALTER TABLE `grades`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `leave_requests`
--
ALTER TABLE `leave_requests`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=69;

--
-- AUTO_INCREMENT for table `payrolls`
--
ALTER TABLE `payrolls`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `schedules`
--
ALTER TABLE `schedules`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=159;

--
-- AUTO_INCREMENT for table `semesters`
--
ALTER TABLE `semesters`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `student_evaluations`
--
ALTER TABLE `student_evaluations`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `submissions`
--
ALTER TABLE `submissions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `surveys`
--
ALTER TABLE `surveys`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `survey_responses`
--
ALTER TABLE `survey_responses`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `teachers`
--
ALTER TABLE `teachers`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `teacher_assignments`
--
ALTER TABLE `teacher_assignments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `tuition_payments`
--
ALTER TABLE `tuition_payments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`),
  ADD CONSTRAINT `attendance_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`);

--
-- Constraints for table `classes`
--
ALTER TABLE `classes`
  ADD CONSTRAINT `classes_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`),
  ADD CONSTRAINT `classes_ibfk_2` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`),
  ADD CONSTRAINT `classes_ibfk_3` FOREIGN KEY (`classroom_id`) REFERENCES `classrooms` (`id`),
  ADD CONSTRAINT `classes_ibfk_4` FOREIGN KEY (`semester_id`) REFERENCES `semesters` (`id`);

--
-- Constraints for table `class_plans`
--
ALTER TABLE `class_plans`
  ADD CONSTRAINT `class_plans_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`),
  ADD CONSTRAINT `class_plans_ibfk_2` FOREIGN KEY (`semester_id`) REFERENCES `semesters` (`id`);

--
-- Constraints for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD CONSTRAINT `enrollments_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`),
  ADD CONSTRAINT `enrollments_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`);

--
-- Constraints for table `grades`
--
ALTER TABLE `grades`
  ADD CONSTRAINT `grades_ibfk_1` FOREIGN KEY (`submission_id`) REFERENCES `submissions` (`id`),
  ADD CONSTRAINT `grades_ibfk_2` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`);

--
-- Constraints for table `leave_requests`
--
ALTER TABLE `leave_requests`
  ADD CONSTRAINT `leave_requests_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`);

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `payrolls`
--
ALTER TABLE `payrolls`
  ADD CONSTRAINT `payrolls_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`);

--
-- Constraints for table `schedules`
--
ALTER TABLE `schedules`
  ADD CONSTRAINT `schedules_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `schedules_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `students_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `student_evaluations`
--
ALTER TABLE `student_evaluations`
  ADD CONSTRAINT `student_evaluations_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`),
  ADD CONSTRAINT `student_evaluations_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`),
  ADD CONSTRAINT `student_evaluations_ibfk_3` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`);

--
-- Constraints for table `submissions`
--
ALTER TABLE `submissions`
  ADD CONSTRAINT `submissions_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`),
  ADD CONSTRAINT `submissions_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`);

--
-- Constraints for table `surveys`
--
ALTER TABLE `surveys`
  ADD CONSTRAINT `surveys_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`);

--
-- Constraints for table `survey_responses`
--
ALTER TABLE `survey_responses`
  ADD CONSTRAINT `survey_responses_ibfk_1` FOREIGN KEY (`survey_id`) REFERENCES `surveys` (`id`),
  ADD CONSTRAINT `survey_responses_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`),
  ADD CONSTRAINT `survey_responses_ibfk_3` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`);

--
-- Constraints for table `teachers`
--
ALTER TABLE `teachers`
  ADD CONSTRAINT `teachers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `teacher_assignments`
--
ALTER TABLE `teacher_assignments`
  ADD CONSTRAINT `teacher_assignments_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`),
  ADD CONSTRAINT `teacher_assignments_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`),
  ADD CONSTRAINT `teacher_assignments_ibfk_3` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`);

--
-- Constraints for table `tuition_payments`
--
ALTER TABLE `tuition_payments`
  ADD CONSTRAINT `tuition_payments_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`),
  ADD CONSTRAINT `tuition_payments_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`);

--
-- Constraints for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD CONSTRAINT `user_roles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_roles_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
