-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Dec 05, 2025 at 08:19 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `nit_student_attendance`
--

-- --------------------------------------------------------

--
-- Table structure for table `active_sessions`
--

CREATE TABLE `active_sessions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_role` varchar(20) NOT NULL,
  `session_id` varchar(255) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `last_activity` datetime NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `assignments`
--

CREATE TABLE `assignments` (
  `id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `due_date` date NOT NULL,
  `total_marks` int(11) NOT NULL DEFAULT 100,
  `file_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `assignment_submissions`
--

CREATE TABLE `assignment_submissions` (
  `id` int(11) NOT NULL,
  `assignment_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `submission_text` text DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `marks_obtained` int(11) DEFAULT NULL,
  `feedback` text DEFAULT NULL,
  `marked_at` timestamp NULL DEFAULT NULL,
  `status` enum('pending','submitted','marked') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `attendance_summary`
--

CREATE TABLE `attendance_summary` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `month` int(11) NOT NULL,
  `year` int(11) NOT NULL,
  `total_days` int(11) DEFAULT 0,
  `present_days` int(11) DEFAULT 0,
  `absent_days` int(11) DEFAULT 0,
  `late_days` int(11) DEFAULT 0,
  `attendance_percentage` decimal(5,2) DEFAULT 0.00,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chat_conversations`
--

CREATE TABLE `chat_conversations` (
  `id` int(11) NOT NULL,
  `user1_id` int(11) NOT NULL,
  `user1_type` enum('teacher','student') NOT NULL,
  `user2_id` int(11) NOT NULL,
  `user2_type` enum('teacher','student') NOT NULL,
  `last_message` text DEFAULT NULL,
  `last_message_time` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `unread_count_user1` int(11) DEFAULT 0,
  `unread_count_user2` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chat_messages`
--

CREATE TABLE `chat_messages` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `sender_type` enum('teacher','student') NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `receiver_type` enum('teacher','student') NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `classes`
--

CREATE TABLE `classes` (
  `id` int(11) NOT NULL,
  `class_name` varchar(100) NOT NULL,
  `department_id` int(11) NOT NULL,
  `year` int(11) NOT NULL,
  `section` varchar(10) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `semester` int(11) NOT NULL,
  `academic_year` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `classes`
--

INSERT INTO `classes` (`id`, `class_name`, `department_id`, `year`, `section`, `teacher_id`, `semester`, `academic_year`, `created_at`) VALUES
(41, 'Electrical Engineering - 1st Year (Mr. Prashant Dange)', 4, 1, 'Electrical', 23, 1, '2025-26', '2025-11-14 16:46:50'),
(49, 'Civil Engineering - 1st Year (Mr. Dhiraj Meghe)', 4, 1, 'Civil', 22, 1, '2025-26', '2025-11-15 04:29:38'),
(50, 'Civil Engineering - 1st Year (Dr. Mohammad Sabir)', 4, 1, 'Civil', 26, 1, '2025-26', '2025-11-15 04:29:54'),
(51, 'Civil Engineering - 1st Year (Mr. Ghufran Ahmad Khan)', 4, 1, 'Civil', 38, 1, '2025-26', '2025-11-15 04:30:26'),
(52, 'Civil Engineering - 1st Year (Dr. Amit Kharwade)', 4, 1, 'Civil', 36, 1, '2025-26', '2025-11-15 04:34:09'),
(53, 'Civil Engineering - 1st Year (Dr. Abdul Ghaffar)', 4, 1, 'Civil', 37, 1, '2025-26', '2025-11-15 04:34:36'),
(56, 'Electrical Engineering - 1st Year (Dr. Mohammad Sabir)', 4, 1, 'Electrical', 26, 1, '2025-26', '2025-11-15 04:36:11'),
(57, 'Electrical Engineering - 1st Year (Mrs Rachna Daga)', 4, 1, 'Electrical', 28, 1, '2025-26', '2025-11-15 04:36:31'),
(58, 'Electrical Engineering - 1st Year (Mr. Rohan Deshmukh)', 4, 1, 'Electrical', 39, 1, '2025-26', '2025-11-15 04:38:11'),
(59, 'Electrical Engineering - 1st Year (Mr. Harshal Ghatole)', 4, 1, 'Electrical', 34, 1, '2025-26', '2025-11-15 04:38:47'),
(60, 'Mechanical Engineering - 1st Year (Mr. Prashant Dange)', 4, 1, 'Mechanical', 23, 1, '2025-26', '2025-11-15 04:39:16'),
(61, 'Mechanical Engineering - 1st Year (Mr. Dhiraj Meghe)', 4, 1, 'Mechanical', 22, 1, '2025-26', '2025-11-15 04:39:36'),
(62, 'Mechanical Engineering - 1st Year (Dr. Mohammad Sabir)', 4, 1, 'Mechanical', 26, 1, '2025-26', '2025-11-15 04:39:56'),
(63, 'Mechanical Engineering - 1st Year (Mr. Samrat Kavishwar)', 4, 1, 'Mechanical', 35, 1, '2025-26', '2025-11-15 04:40:11'),
(65, 'Computer Science & Engineering - A - 1st Year (Mrs. Mona Dange)', 4, 1, 'CSE-A', 25, 1, '2025-26', '2025-11-15 04:41:24'),
(66, 'Computer Science & Engineering - A - 1st Year (Dr. (Mrs.) Sonika Kochhar)', 4, 1, 'CSE-A', 24, 1, '2025-26', '2025-11-15 04:41:49'),
(68, 'Computer Science & Engineering - A - 1st Year (Mrs Rachna Daga)', 4, 1, 'CSE-A', 28, 1, '2025-26', '2025-11-15 04:42:34'),
(69, 'Computer Science & Engineering - A - 1st Year (Mr. Ayaz Sheikh)', 4, 1, 'CSE-A', 27, 1, '2025-26', '2025-11-15 04:42:51'),
(70, 'Computer Science & Engineering - A - 1st Year (Ms. Pournima Bhuyar)', 4, 1, 'CSE-A', 29, 1, '2025-26', '2025-11-15 04:43:05'),
(71, 'Computer Science & Engineering - B - 1st Year (Mrs. Mona Dange)', 4, 1, 'CSE-B', 25, 1, '2025-26', '2025-11-15 04:43:35'),
(72, 'Computer Science & Engineering - B - 1st Year (Dr. (Mrs.) Sonika Kochhar)', 4, 1, 'CSE-B', 24, 1, '2025-26', '2025-11-15 04:43:51'),
(74, 'Computer Science & Engineering - B - 1st Year (Mr. Rahul Kadam)', 4, 1, 'CSE-B', 40, 1, '2025-26', '2025-11-15 04:46:04'),
(75, 'Computer Science & Engineering - B - 1st Year (Mr. Ayaz Sheikh)', 4, 1, 'CSE-B', 27, 1, '2025-26', '2025-11-15 04:46:21'),
(76, 'Computer Science & Engineering - B - 1st Year (Ms. Pournima Bhuyar)', 4, 1, 'CSE-B', 29, 1, '2025-26', '2025-11-15 04:46:38'),
(78, 'Mechanical Engineering - 1st Year (Ms. Pournima Bhuyar)', 4, 1, 'Mechanical', 29, 1, '2025-26', '2025-11-15 05:00:58'),
(79, 'IT - 1st Year (Dr. (Mrs.) Meghna Jumde ', 4, 1, 'IT', 51, 1, '2025-26', '2025-11-16 09:09:41'),
(80, 'IT - 1st Year (Ms. Vidya Raut)', 4, 1, 'IT', 52, 1, '2025-26', '2025-11-17 09:44:46'),
(81, 'IT - 1st Year (Ms. Pournima Bhuyar)', 4, 1, 'IT', 29, 1, '2025-26', '2025-11-17 12:50:56'),
(82, 'IT - 1st Year (Mr. Tushar Shelke)', 4, 1, 'IT', 55, 1, '2025-26', '2025-11-17 12:55:47'),
(83, 'IT - 1st Year (Ms. Divya Lande)', 4, 1, 'IT', 57, 1, '2025-26', '2025-11-17 12:56:08'),
(84, 'IT - 1st Year (Ms. Hitaishi Chauhan)', 4, 1, 'IT', 53, 1, '2025-26', '2025-11-17 12:57:29'),
(86, 'IT - 1st Year (Ms. Aayushi Sharma)', 4, 1, 'IT', 54, 1, '2025-26', '2025-11-17 13:00:12'),
(87, 'Civil Engineering - 1st Year (Ms. Vidya Raut)', 4, 1, 'Civil', 52, 1, '2025-26', '2025-11-17 16:56:56'),
(88, 'Mechanical Engineering - 1st Year (Ms. Aayushi Sharma)', 4, 1, 'Mechanical', 54, 1, '2025-26', '2025-11-17 16:59:11'),
(89, 'Electrical Engineering - 1st Year (Dr. jitendrabhaiswar)', 4, 1, 'Electrical', 59, 1, '2025-26', '2025-11-17 17:03:44'),
(90, 'Computer Science & Engineering - B - 1st Year (Ms. Hitaishi Chauhan)', 4, 1, 'CSE-B', 53, 1, '2025-26', '2025-11-17 17:05:13'),
(91, 'Computer Science & Engineering - A - 1st Year (Ms. Hitaishi Chauhan)', 4, 1, 'CSE-A', 53, 1, '2025-26', '2025-11-17 17:05:37');

-- --------------------------------------------------------

--
-- Table structure for table `class_subjects`
--

CREATE TABLE `class_subjects` (
  `id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `class_teachers`
--

CREATE TABLE `class_teachers` (
  `id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `subject` varchar(200) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` int(11) NOT NULL,
  `dept_name` varchar(100) NOT NULL,
  `dept_code` varchar(20) NOT NULL,
  `hod_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `dept_name`, `dept_code`, `hod_id`, `created_at`) VALUES
(4, '1st year', '1st Year -', 15, '2025-11-13 18:09:21');

-- --------------------------------------------------------

--
-- Table structure for table `exam_subjects`
--

CREATE TABLE `exam_subjects` (
  `id` int(11) NOT NULL,
  `timetable_id` int(11) NOT NULL,
  `subject_name` varchar(200) NOT NULL,
  `subject_code` varchar(50) NOT NULL,
  `exam_date` date NOT NULL,
  `exam_time` time NOT NULL,
  `duration` int(11) NOT NULL COMMENT 'Duration in minutes',
  `room_no` varchar(50) NOT NULL,
  `max_marks` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `exam_timetables`
--

CREATE TABLE `exam_timetables` (
  `id` int(11) NOT NULL,
  `department_id` int(11) NOT NULL,
  `exam_name` varchar(200) NOT NULL,
  `exam_type` varchar(100) NOT NULL,
  `academic_year` varchar(20) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `year` int(11) NOT NULL,
  `semester` int(11) NOT NULL,
  `section` varchar(100) NOT NULL,
  `instructions` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `exam_types`
--

CREATE TABLE `exam_types` (
  `id` int(11) NOT NULL,
  `exam_name` varchar(50) NOT NULL,
  `exam_code` varchar(20) NOT NULL,
  `max_marks` int(11) NOT NULL,
  `weightage` decimal(5,2) DEFAULT 100.00,
  `description` text DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `exam_types`
--

INSERT INTO `exam_types` (`id`, `exam_name`, `exam_code`, `max_marks`, `weightage`, `description`, `display_order`, `is_active`, `created_at`) VALUES
(1, 'MST 1', 'MST1', 20, 20.00, NULL, 1, 1, '2025-11-29 16:20:36'),
(2, 'MST 2', 'MST2', 20, 20.00, NULL, 2, 1, '2025-11-29 16:20:36'),
(3, 'Pre Board', 'PREBOARD', 30, 30.00, NULL, 3, 1, '2025-11-29 16:20:36'),
(4, 'Final Exam', 'FINAL', 100, 100.00, NULL, 4, 1, '2025-11-29 16:20:36');

-- --------------------------------------------------------

--
-- Table structure for table `facelock_users`
--

CREATE TABLE `facelock_users` (
  `id` int(11) NOT NULL,
  `student_id` varchar(50) NOT NULL,
  `student_name` varchar(255) NOT NULL,
  `face_photo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `facelock_users`
--

INSERT INTO `facelock_users` (`id`, `student_id`, `student_name`, `face_photo`, `created_at`, `last_login`) VALUES
(1, 'IT-03', 'ANSHIKA SANTOSH KUMAR NAGDEVE', 'student_1763384871_691b1e2726538.jpeg', '2025-12-03 03:45:07', NULL),
(2, 'IT-04', 'ANUJ BISEK NAKPURE', 'student_1763384952_691b1e784bf8e.jpeg', '2025-12-03 03:45:07', NULL),
(3, 'IT-05', 'ARYAN GAJANAN GHANMODE', 'student_1763385007_691b1eaf1c9dc.jpeg', '2025-12-03 03:45:07', NULL),
(4, 'IT-06', 'ARYAN SUNIL PATIL', 'student_1763385051_691b1edb8d40a.jpeg', '2025-12-03 03:45:07', NULL),
(5, 'IT-07', 'ARYAN VINOD MANGHATE', 'student_1763385098_691b1f0abf1c8.jpeg', '2025-12-03 03:45:07', NULL),
(6, 'IT-08', 'ATHARVA RAJENDRA SATIKOSARE', 'student_1763385142_691b1f3657db4.jpeg', '2025-12-03 03:45:07', NULL),
(7, 'IT-09', 'ATHARVA SANTOSH DEULKAR', 'student_1763385201_691b1f7129e7b.jpeg', '2025-12-03 03:45:07', NULL),
(8, 'IT-10', 'BHAKTI BALIRAM KURWADE', 'student_1763385249_691b1fa1ae71c.jpeg', '2025-12-03 03:45:07', NULL),
(9, 'IT-11', 'DEVYANI RAJENDRA PAL', 'student_1763385301_691b1fd5afc10.jpeg', '2025-12-03 03:45:07', NULL),
(10, 'IT-12', 'DHANASHREE DINESH MAHORKAR', 'student_1763385361_691b2011ae1a1.jpeg', '2025-12-03 03:45:07', NULL),
(11, 'IT-13', 'DHANASHREE SHIVAJI KAYANDE', 'student_1763385471_691b207fcb159.jpeg', '2025-12-03 03:45:07', NULL),
(12, 'IT-14', 'GAYATRI ARUN SHEWALKAR', 'student_1763385516_691b20acec94e.jpeg', '2025-12-03 03:45:07', NULL),
(13, 'IT-15', 'HIMANSHU RAJENDRA PATIL', 'student_149_1763823801.png', '2025-12-03 03:45:07', NULL),
(14, 'IT-16', 'KOMAL ATISH SHASTRAKAR', 'student_1763386809_691b25b95743c.jpeg', '2025-12-03 03:45:07', NULL),
(15, 'IT-17', 'KRUTIKA PRITAM BALPANDE', 'student_151_1763386899.jpeg', '2025-12-03 03:45:07', NULL),
(16, 'IT-18', 'KUNDAN GUNARAM GAIDHANE', 'student_1763386976_691b26601c457.jpeg', '2025-12-03 03:45:07', NULL),
(17, 'IT-19', 'MAMTA SUBHASH KOTHE', 'student_1763387034_691b269a722a0.jpeg', '2025-12-03 03:45:07', NULL),
(18, 'IT-20', 'MANASI VILAS SHEGAONKAR', 'student_1763387102_691b26dea258f.jpeg', '2025-12-03 03:45:07', NULL),
(19, 'IT-21', 'MANASWI PRAKASH BHAWALKAR', 'student_1763387143_691b27070d959.jpeg', '2025-12-03 03:45:07', NULL),
(20, 'IT-22', 'NALINI RANJEET BISWAS', 'student_1763387183_691b272fda7a5.jpeg', '2025-12-03 03:45:07', NULL),
(21, 'IT-23', 'NARGIS BRAMHANAND CHAUDHARI', 'student_1763387223_691b27570024d.jpeg', '2025-12-03 03:45:07', NULL),
(22, 'IT-24', 'NEHA DHANRAJ WAKDE', 'student_1763387264_691b2780abc29.jpeg', '2025-12-03 03:45:07', NULL),
(23, 'IT-25', 'NIKITA DADARAO PATIL', 'student_1763387304_691b27a805899.jpeg', '2025-12-03 03:45:07', NULL),
(24, 'IT-26', 'OM NARENDRA AUSARMOL', 'student_1763387360_691b27e0c0659.jpeg', '2025-12-03 03:45:07', NULL),
(25, 'IT-27', 'OM RAMKRISHNA KHADATKAR', 'student_1763387416_691b28189b382.jpeg', '2025-12-03 03:45:07', NULL),
(26, 'IT-28', 'PIYUSH JAYRAM PRASAD', 'student_1763387460_691b28448f1aa.jpeg', '2025-12-03 03:45:07', NULL),
(27, 'IT-29', 'PRANAY YOGRAJ PANORE', 'student_163_1763448683.png', '2025-12-03 03:45:07', NULL),
(28, 'IT-30', 'PRANJALI RAJESH BARGATH', 'student_1763387557_691b28a567b61.jpeg', '2025-12-03 03:45:07', NULL),
(29, 'IT-31', 'PRATHMESH PREMDAS NICHANT', 'student_1763387613_691b28ddc88ce.jpeg', '2025-12-03 03:45:07', NULL),
(30, 'IT-32', 'PREETI JIYALAL SHAHU', 'student_1763387691_691b292b90340.jpeg', '2025-12-03 03:45:07', NULL),
(31, 'IT-33', 'PURVA RAJESH REWATKAR', 'student_1763387748_691b296428b27.jpeg', '2025-12-03 03:45:07', NULL),
(32, 'IT-34', 'RITESH PANJAB DHULASE', 'student_1763387806_691b299e7f455.jpeg', '2025-12-03 03:45:07', NULL),
(33, 'IT-35', 'RIYA KISHOR YELEKAR', 'student_1763387874_691b29e2a451a.jpeg', '2025-12-03 03:45:07', NULL),
(34, 'IT-36', 'RIYA PRASHANT JAMGADE', 'student_1763387926_691b2a163d218.jpeg', '2025-12-03 03:45:07', NULL),
(35, 'IT-37', 'RIYA SANTOSH BHAGAT', 'student_1763387976_691b2a48bf6e3.jpeg', '2025-12-03 03:45:07', NULL),
(36, 'IT-38', 'RIYA SANTOSH MUSALE', 'student_1763388034_691b2a823f864.jpeg', '2025-12-03 03:45:07', NULL),
(37, 'IT-39', 'ROHAN PRAMOD KHADSE', 'student_1763388086_691b2ab6aed84.jpeg', '2025-12-03 03:45:07', NULL),
(38, 'IT-40', 'ROHIT RUPCHAND KHOBRAGADE', 'student_1763388134_691b2ae6d9f56.jpeg', '2025-12-03 03:45:07', NULL),
(39, 'IT-41', 'ROHIT SANDIP RATHOD', 'student_1763388185_691b2b19d297d.jpeg', '2025-12-03 03:45:07', NULL),
(40, 'IT-42', 'SAKSHI MORESHWAR MESHRAM', 'student_1763388235_691b2b4bd7da9.jpeg', '2025-12-03 03:45:07', NULL),
(41, 'IT-43', 'SALONI PURUSHOTTAM CHOPDE', 'student_1763388287_691b2b7fe3fa3.jpeg', '2025-12-03 03:45:07', NULL),
(42, 'IT-44', 'SAMARTH KISHOR BHOYAR', 'student_1763388350_691b2bbe25e3b.jpeg', '2025-12-03 03:45:07', NULL),
(43, 'IT-45', 'SAMIKSHA PRAKASH KANERE', 'student_1763388395_691b2beba7e17.jpeg', '2025-12-03 03:45:07', NULL),
(44, 'IT-46', 'SAROJ HARIDAS BAGDE', 'student_1763388456_691b2c2806b6e.jpeg', '2025-12-03 03:45:07', NULL),
(45, 'IT-47', 'SARTHAK VILAS MESHRAM', 'student_1763388505_691b2c59505a8.jpeg', '2025-12-03 03:45:07', NULL),
(46, 'IT-48', 'SHIVRAJ GANGADHAR DHAVALE', 'student_1763388548_691b2c8497b78.jpeg', '2025-12-03 03:45:07', NULL),
(47, 'IT-49', 'SHRAVANI RAMESHWAR AMBULKAR', 'student_1763388598_691b2cb62fd77.jpeg', '2025-12-03 03:45:07', NULL),
(48, 'IT-50', 'SHRUTI SANJAY WANDEKAR', 'student_1763388642_691b2ce29776e.jpeg', '2025-12-03 03:45:07', NULL),
(49, 'IT-51', 'SHRUTI SEWAK KOHAD', 'student_1763388691_691b2d1341579.jpeg', '2025-12-03 03:45:07', NULL),
(50, 'IT-52', 'SHUBHAM SANJAY PAWAR', 'student_1763388746_691b2d4abca7f.jpeg', '2025-12-03 03:45:07', NULL),
(51, 'IT-53', 'SIDDHANT RAJESH MAGARDE', 'student_1763388792_691b2d78733ce.jpeg', '2025-12-03 03:45:07', NULL),
(52, 'IT-54', 'SOHAM DINESH GULHANE', 'student_1763388831_691b2d9f9734c.jpeg', '2025-12-03 03:45:07', NULL),
(53, 'IT-55', 'SUJAL BHANUDAS WANODE', 'student_1763388882_691b2dd2881cf.jpeg', '2025-12-03 03:45:07', NULL),
(54, 'IT-56', 'SUJAL GAUTAM DABRASE', 'student_1763388935_691b2e078daeb.jpeg', '2025-12-03 03:45:07', NULL),
(55, 'IT-57', 'TANVI SUNIL GHATOL', 'student_1763388981_691b2e35a8cfc.jpeg', '2025-12-03 03:45:07', NULL),
(56, 'IT-59', 'UTTARA RAVINDRA BHOYAR', 'student_1763389078_691b2e961124c.jpeg', '2025-12-03 03:45:07', NULL),
(57, 'IT-60', 'UTTARANSHI PANKAJ CHOUDHARY', 'student_1763389131_691b2ecb6d309.jpeg', '2025-12-03 03:45:07', NULL),
(58, 'IT-61', 'VANSHIKA SANJAY NAGPURE', 'student_1763389181_691b2efd9d812.jpeg', '2025-12-03 03:45:07', NULL),
(59, 'IT-62', 'VEDANT VIJAYRAO GHARDE', 'student_1763389228_691b2f2c2b7a8.jpeg', '2025-12-03 03:45:07', NULL),
(60, 'CE-01', 'ADITYA CHANDRASHEKHAR MESHRAM', 'student_34_1763719715.png', '2025-12-03 03:45:07', NULL),
(61, 'CE-02', 'ANIKET RAJENDRA BANSOD', 'student_35_1763719744.png', '2025-12-03 03:45:07', NULL),
(62, 'CE-03', 'ANKUSH RAJENDRA ADAY', 'student_36_1763719769.png', '2025-12-03 03:45:07', NULL),
(63, 'CE-04', 'ANSH RAHUL GAJBHIYE', 'student_37_1763719804.png', '2025-12-03 03:45:07', NULL),
(64, 'CE-05', 'ANUSHKA DHANRAJ RAKSHASKAR', 'student_38_1763719828.png', '2025-12-03 03:45:07', NULL),
(65, 'ME-01', 'ABDUL ZISHAN ABDUL JAVED SHEIKH', 'student_39_1763720270.png', '2025-12-03 03:45:07', NULL),
(66, 'ME-02', 'ADESH PURUSHOTTAM GAURAV', 'student_40_1763399270.jpeg', '2025-12-03 03:45:07', NULL),
(67, 'ME-03', 'ADITYA RAJENDRA WADBUDHE', 'student_41_1763720428.png', '2025-12-03 03:45:07', NULL),
(68, 'ME-04', 'AKASH RAJKUMAR BINZADE', 'student_42_1763720487.png', '2025-12-03 03:45:07', NULL),
(69, 'ME-05', 'AMAN DINESH SHINGARE', 'student_43_1763720546.png', '2025-12-03 03:45:07', NULL),
(70, 'ME-06', 'ANISHA SACHIN BHAGAT', 'student_1763706639_6920070f9fd52.png', '2025-12-03 03:45:07', NULL),
(71, 'ME-07', 'ANUSHKA GAJANAN ANTURKAR', 'student_1763706684_6920073ca7097.png', '2025-12-03 03:45:07', NULL),
(72, 'ME-08', 'ARNAV VIJAY DANGE', 'student_1763706733_6920076dac770.png', '2025-12-03 03:45:07', NULL),
(73, 'ME-09', 'ARYAN BABANAND THOOL', 'student_1763706791_692007a701b9d.png', '2025-12-03 03:45:07', NULL),
(74, 'ME-10', 'ATHARVA RAJESH MALEWAR', 'student_1763706831_692007cfb5d56.png', '2025-12-03 03:45:07', NULL),
(75, 'ME-11', 'BHUPESH NITIN NAVGHARE', 'student_1763706870_692007f6f2180.png', '2025-12-03 03:45:07', NULL),
(76, 'ME-12', 'BHUSHAN UMESH THAWARE', 'student_1763706967_69200857269c8.png', '2025-12-03 03:45:07', NULL),
(77, 'ME-13', 'CHAITANYA RAJESH MANOHARKAR', 'student_1763707006_6920087e393ab.png', '2025-12-03 03:45:07', NULL),
(78, 'ME-14', 'CHETANA MADAN PANDE', 'student_1763707062_692008b677300.png', '2025-12-03 03:45:07', NULL),
(79, 'ME-15', 'DEEPAK RAJKUMAR DOSHIYA', 'student_1763707119_692008ef5ae32.png', '2025-12-03 03:45:07', NULL),
(80, 'ME-16', 'DEVESH NAVIN GIRI', 'student_1763707199_6920093fa30f5.png', '2025-12-03 03:45:07', NULL),
(81, 'ME-18', 'DEVYANI DIGAMBAR KOLHE', 'student_1763707475_69200a53a4bd0.png', '2025-12-03 03:45:07', NULL),
(82, 'ME-19', 'DHANANJAY GAJANAN THAKRE', 'student_1763707522_69200a826bdc9.png', '2025-12-03 03:45:07', NULL),
(83, 'ME-20', 'DIKSHA MORESHWAR KAWALE', 'student_1763707585_69200ac1869c6.png', '2025-12-03 03:45:07', NULL),
(84, 'ME-21', 'DIVYANSH PURUSHOTTAM ALAM', 'student_1763707670_69200b16c9a1a.png', '2025-12-03 03:45:07', NULL),
(85, 'ME-22', 'GAURAV RAKESH ATRAHE', 'student_1763707713_69200b413bb2c.png', '2025-12-03 03:45:07', NULL),
(86, 'ME-23', 'HARSH VIJAY JAMOTKAR', 'student_1763707767_69200b77792f7.png', '2025-12-03 03:45:07', NULL),
(87, 'ME-24', 'KAIF MEHMUD SHEIKH', 'student_1763706321_69200bad02c69.png', '2025-12-03 03:45:07', NULL),
(88, 'ME-25', 'KARTIK NARAYAN POINKAR', 'student_1763706368_69200bdcf2fdf.png', '2025-12-03 03:45:07', NULL),
(89, 'ME-26', 'KASHISH DIVAKAR OKTE', 'student_1763707915_69200c0b54263.png', '2025-12-03 03:45:07', NULL),
(90, 'ME-27', 'KUNAL RAMSWARTH PRASAD', 'student_1763707959_69200c3775ba2.png', '2025-12-03 03:45:07', NULL),
(91, 'ME-28', 'MALLIKA BHOJRAJ THAKUR', 'student_1763708016_69200c7002a94.png', '2025-12-03 03:45:07', NULL),
(92, 'ME-29', 'MANSI KAILAS KSHIRSAGAR', 'student_1763708058_69200c9a4c3d3.png', '2025-12-03 03:45:07', NULL),
(93, 'ME-30', 'MAYUR PALIK BISEN', 'student_1763708130_69200ce2b633e.png', '2025-12-03 03:45:07', NULL),
(94, 'ME-31', 'NIDHI BHUPESH PETHE', 'student_1763708191_69200d1fddf99.png', '2025-12-03 03:45:07', NULL),
(95, 'ME-32', 'NIKITA NAMDEV RATHOD', 'student_1763708233_69200d493a0f7.png', '2025-12-03 03:45:07', NULL),
(96, 'ME-33', 'OM KUNDAN DESHMUKH', 'student_1763708277_69200d75b4f24.png', '2025-12-03 03:45:07', NULL),
(97, 'ME-34', 'PAYAL GIRIDHAR MOHOD', 'student_1763708329_69200da906542.png', '2025-12-03 03:45:07', NULL),
(98, 'ME-35', 'PAYAL JAGDISH TATTE', 'student_1763708377_69200dd90aff1.png', '2025-12-03 03:45:07', NULL),
(99, 'ME-36', 'PIYUSH MUKESH ATHAWALE', 'student_1763708422_69200e06799c9.png', '2025-12-03 03:45:07', NULL),
(100, 'ME-37', 'PIYUSH NANDKISHOR DHANDE', 'student_1763708477_69200e3d083d2.png', '2025-12-03 03:45:07', NULL),
(101, 'ME-38', 'PRAJWAL PRABHAKAR JAMBHULE', 'student_1763708516_69200e64eeddb.png', '2025-12-03 03:45:07', NULL),
(102, 'ME-39', 'PRATHMESH GAJANAN DAGWAR', 'student_1763708561_69200e913faa9.png', '2025-12-03 03:45:07', NULL),
(103, 'ME-40', 'PRITESH PRAMOD DIGAL', 'student_1763708601_69200eb92c28b.png', '2025-12-03 03:45:07', NULL),
(104, 'ME-17', 'DEVYANI CHHAGAN GOMKAR', 'student_1763708770_69200f6244758.png', '2025-12-03 03:45:07', NULL),
(105, 'ME-41', 'RAGHAVENDRA VIJAY WAGHE', 'student_1763708827_69200f9be36ec.png', '2025-12-03 03:45:07', NULL),
(106, 'ME-42', 'RAM TULSHIDAS PATIL', 'student_1763708865_69200fc16daa0.png', '2025-12-03 03:45:07', NULL),
(107, 'ME-43', 'RITIK RAVINDRA LADHAIKAR', 'student_1763708906_69200fea00680.png', '2025-12-03 03:45:07', NULL),
(108, 'ME-44', 'ROHAN SHRIKRUSHNA SHERKI', 'student_1763708960_6920102024094.png', '2025-12-03 03:45:07', NULL),
(109, 'ME-45', 'ROHIT DEEPAK RAMAVAT', 'student_1763709007_6920104f5c17c.png', '2025-12-03 03:45:07', NULL),
(110, 'ME-46', 'SAHIL DHANRAJ NINAWE', 'student_1763709050_6920107add5b9.png', '2025-12-03 03:45:07', NULL),
(111, 'ME-47', 'SAYALI DAMODHAR HEDAOO', 'student_1763709094_692010a6bbbe6.png', '2025-12-03 03:45:07', NULL),
(112, 'ME-48', 'SHAURYA RAJESH RAMPURKAR', 'student_1763709137_692010d1cfcef.png', '2025-12-03 03:45:07', NULL),
(113, 'ME-49', 'SHIVAM RAVI DAMODHARE', 'student_1763709163_692010fae9ca9.png', '2025-12-03 03:45:07', NULL),
(114, 'ME-50', 'SHRAVNI SUNILRAO DIDSHE', 'student_1763709227_6920112b3d91b.png', '2025-12-03 03:45:07', NULL),
(115, 'ME-51', 'SIMON RAJESH BINZADE', 'student_1763709274_6920115a769cf.png', '2025-12-03 03:45:07', NULL),
(116, 'ME-52', 'SNEHA SUDHAKAR BAWANE', 'student_1763709315_69201183bcc2b.png', '2025-12-03 03:45:07', NULL),
(117, 'ME-53', 'SUMIT NANDU BASHINE', 'student_1763709361_692011b15b5fb.png', '2025-12-03 03:45:07', NULL),
(118, 'ME-54', 'TANISHQ DHANANJAY SHENDE', 'student_1763709401_692011d982f63.png', '2025-12-03 03:45:07', NULL),
(119, 'ME-55', 'TANMAY DNYANESHWAR MOTGHARE', 'student_1763709438_692011fe023c5.png', '2025-12-03 03:45:07', NULL),
(120, 'ME-56', 'TEJAS SANTOSH GAJBHAR', 'student_1763709482_6920122a0f757.png', '2025-12-03 03:45:07', NULL),
(121, 'ME-57', 'VANSH VIJAY NAGOSE', 'student_1763709520_692012507b703.png', '2025-12-03 03:45:07', NULL),
(122, 'ME-58', 'VANSH VIPINKUMAR NIMR', 'student_1763709559_6920127726d0b.png', '2025-12-03 03:45:07', NULL),
(123, 'ME-59', 'VEDANT NITIN LADSE', 'student_1763709624_692012b8373ed.png', '2025-12-03 03:45:07', NULL),
(124, 'ME-60', 'VEDANT RAVINDRA BIGHANE', 'student_1763709672_692012e8eec29.png', '2025-12-03 03:45:07', NULL),
(125, 'ME-61', 'VEDANTI DEEPAK RANGARI', 'student_1763709718_6920131677749.png', '2025-12-03 03:45:07', NULL),
(126, 'ME-62', 'VINAY DHARMARAJ SAWWALAKHE', 'student_1763709762_692013420e86f.png', '2025-12-03 03:45:07', NULL),
(127, 'ME-63', 'YASH DEVANAND BINZADE', 'student_1763709809_6920137163234.png', '2025-12-03 03:45:07', NULL),
(128, 'ME-64', 'YASH DILIP NARNAWARE', 'student_1763709849_69201399de301.png', '2025-12-03 03:45:07', NULL),
(129, 'CE-06', 'ARIYA HARIDAS SOMKUWAR', 'student_1763689821_691fc55de313e.png', '2025-12-03 03:45:07', NULL),
(130, 'CE-07', 'ARYAN PRAKASH RAMTEKE', 'student_197_1763689942.png', '2025-12-03 03:45:07', NULL),
(131, 'CE-08', 'ARYAN PUSARAM WANJARI', 'student_1763699679_691febdf23870.png', '2025-12-03 03:45:07', NULL),
(132, 'CE-09', 'CHAITALI GOURISHANKAR DURBUDE', 'student_1763699941_691fece50f000.png', '2025-12-03 03:45:07', NULL),
(133, 'CE-10', 'CHAITANYA SUBHASH BHAJBHUJE', 'student_1763699978_691fed0ad1b57.png', '2025-12-03 03:45:07', NULL),
(134, 'CE-11', 'DEEP RAVINDRA WAKADE', 'student_209_1763719959.png', '2025-12-03 03:45:07', NULL),
(135, 'CE-12', 'DHANASHRI VISHVESHWAR SOMKUWAR', 'student_1763700062_691fed5e002d1.png', '2025-12-03 03:45:07', NULL),
(136, 'CE-13', 'DISHANT RAMESH PATIL', 'student_1763700104_691fed88b2d82.png', '2025-12-03 03:45:07', NULL),
(137, 'CE-14', 'DIYA RAKESH POREDDIWAR', 'student_1763700147_691fedb357e4c.png', '2025-12-03 03:45:07', NULL),
(138, 'CE-15', 'DIYA TEJKUMAR PUNVATKAR', 'student_1763700184_691fedd8ef488.png', '2025-12-03 03:45:07', NULL),
(139, 'CE-16', 'HITESH PRASHANT WAGHE', 'student_1763700239_691fee0f7db29.png', '2025-12-03 03:45:07', NULL),
(140, 'CE-17', 'KANAK AMOL GAIDHANI', 'student_1763700289_691fee41f2c0d.png', '2025-12-03 03:45:07', NULL),
(141, 'CE-18', 'KRUTIKA PRAVIN KELWADKAR', 'student_1763700350_691fee7e630b0.png', '2025-12-03 03:45:07', NULL),
(142, 'CE-19', 'MADHAVI RAVINDRA BAWANTHADE', 'student_1763700388_691feea5398da.png', '2025-12-03 03:45:07', NULL),
(143, 'CE-20', 'MAHI SOMESHWAR ILAMKAR', 'student_1763700425_691feec90ff39.png', '2025-12-03 03:45:07', NULL),
(144, 'CE-21', 'MANASVI AMAR DHANREL', 'student_1763700464_691feef058707.png', '2025-12-03 03:45:07', NULL),
(145, 'CE-22', 'MAYANK KAILAS KHORGADE', 'student_1763700503_691fef17217de.png', '2025-12-03 03:45:07', NULL),
(146, 'CE-23', 'MEET LAXMINARAYAN MANE', 'student_1763700547_691fef43b5cbf.png', '2025-12-03 03:45:07', NULL),
(147, 'CE-24', 'MOHD TAHA MOHD NADEEMUDDIN KHATIB', 'student_1763700588_691fef6c168b4.png', '2025-12-03 03:45:07', NULL),
(148, 'CE-25', 'MUSKAN PRAVIN MESHRAM', 'student_1763700641_691fefa16818e.png', '2025-12-03 03:45:07', NULL),
(149, 'CE-27', 'NIKHIL PRAMOD BRAMHANE', 'student_1763700692_691fefd46bf2e.png', '2025-12-03 03:45:07', NULL),
(150, 'CE-28', 'NISHA DEVENDRA DHARMIK', 'student_1763700742_691ff00634a35.png', '2025-12-03 03:45:07', NULL),
(151, 'CE-29', 'PAYAL ANIL LENDE', 'student_1763700784_691ff030436c8.png', '2025-12-03 03:45:07', NULL),
(152, 'CE-30', 'PRACHI RAJESH PATIL', 'student_1763700890_691ff09ae67ac.png', '2025-12-03 03:45:07', NULL),
(153, 'CE-31', 'PRAJWAL ISHWAR BORKAR', 'student_1763700930_691ff0c267736.png', '2025-12-03 03:45:07', NULL),
(154, 'CE-32', 'RADHESHAM RAJENDRA RATHOD', 'student_1763700985_691ff0f9539fc.png', '2025-12-03 03:45:07', NULL),
(155, 'CE-33', 'RAGHAV MOTIDAS NAGPURE', 'student_1763701038_691ff12e88b2c.png', '2025-12-03 03:45:07', NULL),
(156, 'CE-34', 'RESHMI PRAMOD NIKHADE', 'student_1763701085_691ff15d5f9f6.png', '2025-12-03 03:45:07', NULL),
(157, 'CE-35', 'RUDRAKSH ROSHAN NANETKAR', 'student_1763701135_691ff18f7bd4a.png', '2025-12-03 03:45:07', NULL),
(158, 'CE-36', 'SAHIL BALU PAWAR', 'student_1763701173_691ff1b5edbf2.png', '2025-12-03 03:45:07', NULL),
(159, 'CE-37', 'SAKSHI SUNIL LAMBKANE', 'student_1763701238_691ff1f6b7131.png', '2025-12-03 03:45:07', NULL),
(160, 'CE-38', 'SALONI SUDESH GAJBHIYE', 'student_1763701283_691ff2231eb6e.png', '2025-12-03 03:45:07', NULL),
(161, 'CE-39', 'SANKET DARASING CHOUDHARI', 'student_1763701325_691ff24d1e7ab.png', '2025-12-03 03:45:07', NULL),
(162, 'CE-40', 'SHITAL RAMPAT WARTHI', 'student_1763701363_691ff2737fe51.png', '2025-12-03 03:45:07', NULL),
(163, 'CE-41', 'SHREYA BANDUJI SONTAKKE', 'student_1763701406_691ff29ea9dcd.png', '2025-12-03 03:45:07', NULL),
(164, 'CE-42', 'SHUBHAM VILAS CHAVHAN', 'student_1763701454_691ff2ce16395.png', '2025-12-03 03:45:07', NULL),
(165, 'CE-43', 'SUBODH UMESH KHANDEKAR', 'student_1763701534_691ff2f6098b3.png', '2025-12-03 03:45:07', NULL),
(166, 'CE-44', 'SUDHANSHU NISHILESH WANDRE', 'student_1763701537_691ff3215908c.png', '2025-12-03 03:45:07', NULL),
(167, 'CE-45', 'SUMIT JAGDISH KAWALE', 'student_1763701571_691ff343466c0.png', '2025-12-03 03:45:07', NULL),
(168, 'CE-46', 'SUNABH NAVNEET BORKAR', 'student_1763701605_691ff365a53dc.png', '2025-12-03 03:45:07', NULL),
(169, 'CE-47', 'SUSHANT YOGENDRA TAMBE', 'student_1763701644_691ff38ceaa0a.png', '2025-12-03 03:45:07', NULL),
(170, 'CE-48', 'SWAMINI PURUSHOTTAM RAUT', 'student_1763701685_691ff3b5c132f.png', '2025-12-03 03:45:07', NULL),
(171, 'CE-50', 'TANMAY SURESH BHANARE', 'student_1763701772_691ff40c43258.png', '2025-12-03 03:45:07', NULL),
(172, 'CE-51', 'TANUJA PURUSHOTTAM LOLE', 'student_1763701858_691ff462c93ce.png', '2025-12-03 03:45:07', NULL),
(173, 'CE-52', 'TEJAS SUNIL SONWANE', 'student_1763701903_691ff48f52afc.png', '2025-12-03 03:45:07', NULL),
(174, 'CE-53', 'TRUPTI MURLIDHAR DESHBHRATAR', 'student_1763701953_691ff4c17c751.png', '2025-12-03 03:45:07', NULL),
(175, 'CE-54', 'TRUSHNA DILIP SOMANKAR', 'student_1763702010_691ff4fa7b853.png', '2025-12-03 03:45:07', NULL),
(176, 'CE-55', 'TUSHAR NANDULAL THAKUR', 'student_1763702072_691ff538e67c3.png', '2025-12-03 03:45:07', NULL),
(177, 'CE-56', 'UBAID JAVED SAYYED', 'student_1763702170_691ff59a55553.png', '2025-12-03 03:45:07', NULL),
(178, 'CE-57', 'UNNATI NITIN KASAR', 'student_1763702225_691ff5d125d24.png', '2025-12-03 03:45:07', NULL),
(179, 'CE-58', 'VAISHNAVI EKNATH BARSAGADE', 'student_1763702346_691ff64a51397.png', '2025-12-03 03:45:07', NULL),
(180, 'CE-59', 'VEDANT PRASHANT CHAUDHARI', 'student_1763702400_691ff68059d5b.png', '2025-12-03 03:45:07', NULL),
(181, 'CE-60', 'VEDANTI RAJU PUNATKAR', 'student_1763702447_691ff6af53124.png', '2025-12-03 03:45:07', NULL),
(182, 'CE-61', 'VINAY RAJESH UIKEY', 'student_1763702519_691ff6f7ab04d.png', '2025-12-03 03:45:07', NULL),
(183, 'CE-62', 'VISHAKHA NARAYAN BHOYAR', 'student_1763702564_691ff72537871.png', '2025-12-03 03:45:07', NULL),
(184, 'CE-63', 'YASHIKA SUDHIR KALNAKE', 'student_1763702621_691ff75d38af6.png', '2025-12-03 03:45:07', NULL),
(185, 'CE-64', 'YUGAL DINKAR GAIDHANE', 'student_1763702669_691ff78d76487.png', '2025-12-03 03:45:07', NULL),
(186, 'CE-26', 'NACHIKET MUKESH PARAYE', 'student_1763702748_691ff7dcb3df.png', '2025-12-03 03:45:07', NULL),
(187, 'EE-01', 'AASTHA WASUDEO WANKHADE', 'student_44_1763720002.png', '2025-12-03 03:45:07', NULL),
(188, 'EE-02', 'AISHWARYA SANJAY THAKUR', 'student_45_1763720025.png', '2025-12-03 03:45:07', NULL),
(189, 'EE-03', 'AMBAR GAGAN KURANKAR', 'student_46_1763720041.png', '2025-12-03 03:45:07', NULL),
(190, 'EE-04', 'ANJALI BANDU YEWALE', 'student_50_1763720069.png', '2025-12-03 03:45:07', NULL),
(191, 'EE-05', 'ANKUSH BHUWANLAL TURKAR', 'student_51_1763720095.png', '2025-12-03 03:45:07', NULL),
(192, 'EE-06', 'ARYAN HEMRAJ NANDANWAR', 'student_52_1763720247.png', '2025-12-03 03:45:07', NULL),
(193, 'EE-07', 'ARYAN VASANTA SAKHARE', 'student_53_1763720162.png', '2025-12-03 03:45:07', NULL),
(194, 'EE-08', 'AYUSHI UMESH WATH', 'student_1763702892_691ff86cd6820.png', '2025-12-03 03:45:07', NULL),
(195, 'EE-09', 'BHUMIKA RAVINDRA PATRIVAR', 'student_1763702949_691ff8a55078f.png', '2025-12-03 03:45:07', NULL),
(196, 'EE-11', 'CHETAN SANJAY BASKAWARE', 'student_1763703040_691ff900edcbd.png', '2025-12-03 03:45:07', NULL),
(197, 'EE-13', 'EKTA BANDU MORE', 'student_1763703130_691ff95aedff7.png', '2025-12-03 03:45:07', NULL),
(198, 'EE-14', 'GAJANAN PIRAJI CHUNUPWAD', 'student_1763703171_691ff9835e77b.png', '2025-12-03 03:45:07', NULL),
(199, 'EE-15', 'GAURI HEMRAJ GADHAVE', 'student_1763703212_691ff9ac617ab.png', '2025-12-03 03:45:07', NULL),
(200, 'EE-16', 'GULSHAN VINAYAKRAO CHAUDHARI', 'student_1763703269_691ff9e56763c.png', '2025-12-03 03:45:07', NULL),
(201, 'EE-17', 'GUNJAN HEMRAJ MANMODE', 'student_1763703309_691ffa0d7676f.png', '2025-12-03 03:45:07', NULL),
(202, 'EE-18', 'HEMANT BANDUJI BHOYAR', 'student_1763703360_691ffa4047206.png', '2025-12-03 03:45:07', NULL),
(203, 'EE-19', 'HIMANI SUDAM TAKIT', 'student_1763703405_691ffa6dc4a33.png', '2025-12-03 03:45:07', NULL),
(204, 'EE-20', 'KHUSHAL NARENDRA NANDANWAR', 'student_1763703504_691ffad06a7aa.png', '2025-12-03 03:45:07', NULL),
(205, 'EE-10', 'CHAITANYA AVINASH JAGTAP', 'student_1763703897_691ffc5953dcb.png', '2025-12-03 03:45:07', NULL),
(206, 'EE-12', 'DEVYANI VIJAY WAGHMARE', 'student_1763703983_691ffcafd5c45.png', '2025-12-03 03:45:07', NULL),
(207, 'EE-21', 'KHUSHI SANJAY SAWARKAR', 'student_1763704049_691ffcf19b513.png', '2025-12-03 03:45:07', NULL),
(208, 'EE-22', 'KHUSHI SATISH NANWATKAR', 'student_1763704088_691ffd182f19e.png', '2025-12-03 03:45:07', NULL),
(209, 'EE-23', 'KHUSHIYA TUKARAM BAGDE', 'student_1763705936_691ffd487f582.png', '2025-12-03 03:45:07', NULL),
(210, 'EE-24', 'KIRTI SUBHASH KAMLE', 'student_1763705970_691ffd6ad1999.png', '2025-12-03 03:45:07', NULL),
(211, 'EE-25', 'LAWANYA NARESH RAMTEKE', 'student_1763704216_691ffd983d820.png', '2025-12-03 03:45:07', NULL),
(212, 'EE-26', 'MANISH MANOJ PANDIT', 'student_1763704255_691ffdbf14604.png', '2025-12-03 03:45:07', NULL),
(213, 'EE-27', 'MANTHAN SHEKHAR TELRANDHE', 'student_1763704292_691ffde450500.png', '2025-12-03 03:45:07', NULL),
(214, 'EE-28', 'NIRALI ULHAS BORKAR', 'student_1763704339_691ffe1369493.png', '2025-12-03 03:45:07', NULL),
(215, 'EE-29', 'PRACHI ABHAY BHOYAR', 'student_1763704375_691ffe3791ab4.png', '2025-12-03 03:45:07', NULL),
(216, 'EE-30', 'PRADNYA NIRAJ RAUT', 'student_1763704420_691ffe646eaba.png', '2025-12-03 03:45:07', NULL),
(217, 'EE-31', 'PRAGATI RADHESHYAMJI DHARPURE', 'student_1763704461_691ffe8d39b80.png', '2025-12-03 03:45:07', NULL),
(218, 'EE-32', 'PRAJWAL LAXMAN KUTTARMARE', 'student_1763704507_691ffebb3da57.png', '2025-12-03 03:45:07', NULL),
(219, 'EE-33', 'PRASHIKA PRAKASH GATHE', 'student_1763704554_691ffeea75f07.png', '2025-12-03 03:45:07', NULL),
(220, 'EE-34', 'PRATIK LALIT KADREL', 'student_1763704602_691fff1ac80e9.png', '2025-12-03 03:45:07', NULL),
(221, 'EE-35', 'PREM DHARMESHWAR KIRDE', 'student_1763704639_691fff3f04a3d.png', '2025-12-03 03:45:07', NULL),
(222, 'EE-36', 'PRERNA SANJAY BAVISKAR', 'student_1763704690_691fff72dd161.png', '2025-12-03 03:45:07', NULL),
(223, 'EE-37', 'PURVA GAJANAN GORDE', 'student_1763704754_691fffb23971e.png', '2025-12-03 03:45:07', NULL),
(224, 'EE-38', 'PURVA JITENDRA DEVGHARE', 'student_1763704800_691fffe05de59.png', '2025-12-03 03:45:07', NULL),
(225, 'EE-39', 'RIYA SAHENDRA SURYAWANSHI', 'student_1763704843_6920000b8a342.png', '2025-12-03 03:45:07', NULL),
(226, 'EE-40', 'ROHIT VIKAS BHUTE', 'student_1763704913_69200051502f0.png', '2025-12-03 03:45:07', NULL),
(227, 'EE-59', 'RUTUJA SHILWANT PATIL', 'student_1763705590_69200242a19aa.png', '2025-12-03 03:45:07', NULL),
(228, 'EE-42', 'SAMIKSHA GAJANAN BHATE', 'student_1763705475_69200283cde95.png', '2025-12-03 03:45:07', NULL),
(229, 'EE-43', 'SAMYAK ANIL LOHAKARE', 'student_1763705526_692002b6994ab.png', '2025-12-03 03:45:07', NULL),
(230, 'EE-44', 'SANIKA LAXMAN KANTODE', 'student_1763705576_692002e85319d.png', '2025-12-03 03:45:07', NULL),
(231, 'EE-45', 'SANIYA RAMRAO JAMBHULKAR', 'student_1763705611_6920030bc2593.png', '2025-12-03 03:45:07', NULL),
(232, 'EE-46', 'SANSKRUTI NITIN PAWAR', 'student_1763705658_6920033a389ac.png', '2025-12-03 03:45:07', NULL),
(233, 'EE-47', 'SATYAM MANGALCHANDI GHOSH', 'student_1763705757_6920039d7dae5.png', '2025-12-03 03:45:07', NULL),
(234, 'EE-48', 'SHILPKAR SURESH RAMTEKE', 'student_1763705848_692003f8aa93b.png', '2025-12-03 03:45:07', NULL),
(235, 'EE-49', 'SHIVANI RAVI INGALE', 'student_1763705901_6920042dd404e.png', '2025-12-03 03:45:07', NULL),
(236, 'EE-50', 'SHRUTI MAHESH MESHRAM', 'student_1763705943_69200457e3bf9.png', '2025-12-03 03:45:07', NULL),
(237, 'EE-51', 'SHRUTI MUKINDA MANERAO', 'student_1763705984_692004807968d.png', '2025-12-03 03:45:07', NULL),
(238, 'EE-52', 'SHUBHAM GAJANAN KHORGADE', 'student_1763706028_692004ac286ef.png', '2025-12-03 03:45:07', NULL),
(239, 'EE-53', 'SOHAM VINOD DHOTE', 'student_1763706066_692004d2211d6.png', '2025-12-03 03:45:07', NULL),
(240, 'EE-54', 'SUHANI JAYCHAND PATLE', 'student_1763706252_6920058caab07.png', '2025-12-03 03:45:07', NULL),
(241, 'EE-55', 'SUHANI VIJAY CHAUDHARI', 'student_1763706296_692005b881620.png', '2025-12-03 03:45:07', NULL),
(242, 'EE-56', 'SWATI KESHAVRAO BHOPE', 'student_1763706347_692005eb87a93.png', '2025-12-03 03:45:07', NULL),
(243, 'EE-57', 'TANISHA WASUDEO BADODE', 'student_1763706379_6920060b5e491.png', '2025-12-03 03:45:07', NULL),
(244, 'EE-58', 'TRUPTI PURUSHOTTAM MORE', 'student_1763706595_6920062faccf5.png', '2025-12-03 03:45:07', NULL),
(245, 'EE-60', 'YASH RAVINDRA DAFAR', 'student_1763706492_6920067c2fd21.png', '2025-12-03 03:45:07', NULL),
(246, 'EE-61', 'YOGITA CHANDRASHEKHAR THAKUR', 'student_1763706530_692006a210f1f.png', '2025-12-03 03:45:07', NULL),
(247, 'BCSE-01', 'ANKITA ANIL CHANDANKHEDE', 'student_59_1763719374.png', '2025-12-03 03:45:07', NULL),
(248, 'BCSE-02', 'ANUSHKA SANJAYRAO RAUT', 'student_60_1763719399.png', '2025-12-03 03:45:07', NULL),
(249, 'BCSE-03', 'ARYA GUNWANT LEDANGE', 'student_61_1763719452.png', '2025-12-03 03:45:07', NULL),
(250, 'BCSE-04', 'ARYA JOGENDRA BINZADE', 'student_62_1763719474.png', '2025-12-03 03:45:07', NULL),
(251, 'BCSE-05', 'ARYAN HANSRAJ PATIL', 'student_63_1763719643.png', '2025-12-03 03:45:07', NULL),
(252, 'BCSE-06', 'ARYAN NANDKISHOR PAL', 'student_1763714879_6920273fd4c06.png', '2025-12-03 03:45:07', NULL),
(253, 'BCSE-07', 'ARYAN VIJAY KITUKALE', 'student_1763714921_692027692f1df.png', '2025-12-03 03:45:07', NULL),
(254, 'BCSE-08', 'BRIJESH BABALU MADHEKAR', 'student_1763714961_69202791e4ec5.png', '2025-12-03 03:45:07', NULL),
(255, 'BCSE-09', 'CHAITANYA WASUDEO RAUT', 'student_1763715002_692027baaf127.png', '2025-12-03 03:45:07', NULL),
(256, 'BCSE-10', 'DHRUVESH RAMKRRUSHNA PARATE', 'student_1763715039_692027dfd48f8.png', '2025-12-03 03:45:07', NULL),
(257, 'BCSE-11', 'DINESH DILIP KALE', 'student_1763715077_692028057f500.png', '2025-12-03 03:45:07', NULL),
(258, 'BCSE-12', 'HARSHITA RAVINDRA MANUSMARE', 'student_1763715168_6920286020b81.png', '2025-12-03 03:45:07', NULL),
(259, 'BCSE-13', 'JANHVI PRAKASH BAMBAL', 'student_1763715211_6920288b526cd.png', '2025-12-03 03:45:07', NULL),
(260, 'BCSE-14', 'JAYENDRA JAYPAL FUNDE', 'student_1763715270_692028c64aa35.png', '2025-12-03 03:45:07', NULL),
(261, 'BCSE-15', 'KALASH VINOD KOLHATKAR', 'student_1763715308_692028ec54286.png', '2025-12-03 03:45:07', NULL),
(262, 'BCSE-16', 'KARTIK VASUDEO CHOUDHARY', 'student_1763715353_6920291972fa1.png', '2025-12-03 03:45:07', NULL),
(263, 'BCSE-17', 'LOBHANSHA KISHOR DAHAKE', 'student_1763715412_692029546efe9.png', '2025-12-03 03:45:07', NULL),
(264, 'BCSE-20', 'MEGHA UMESH DHARMIK', 'student_1763715856_69202b10d044e.png', '2025-12-03 03:45:07', NULL),
(265, 'BCSE-21', 'MEHUL GAUTAM MESHRAM', 'student_1763715929_69202b59cc2e5.png', '2025-12-03 03:45:07', NULL),
(266, 'BCSE-22', 'MILIND BHUNESHWAR GIRI', 'student_1763715984_69202b74c7955.png', '2025-12-03 03:45:07', NULL),
(267, 'BCSE-23', 'MOHAMMAD FARHAN SHAKIL AHMED', 'student_1763716044_69202bcc25294.png', '2025-12-03 03:45:07', NULL),
(268, 'BCSE-24', 'MRUNALI VITHOBA MARASKOLHE', 'student_1763716074_69202bfa8ff12.png', '2025-12-03 03:45:07', NULL),
(269, 'BCSE-25', 'NAMRATA ANIL NAVALE', 'student_1763716129_69202c21e2cbd.png', '2025-12-03 03:45:07', NULL),
(270, 'BCSE-26', 'NANDINI GAURISHANKAR BANKAR', 'student_1763716170_69202c4a7a921.png', '2025-12-03 03:45:07', NULL),
(271, 'BCSE-27', 'NEHA GANESH JIBHAKATE', 'student_1763716218_69202c7a666e0.png', '2025-12-03 03:45:07', NULL),
(272, 'BCSE-28', 'NIDHI ANIL RAUT', 'student_1763716258_69202ca2213a7.png', '2025-12-03 03:45:07', NULL),
(273, 'BCSE-29', 'NIDHI ASHOK BASANWAR', 'student_1763716293_69202cc507e66.png', '2025-12-03 03:45:07', NULL),
(274, 'BCSE-30', 'NIHARIKA SHANTILAL PACHDHARE', 'student_1763716331_69202ceb9e122.png', '2025-12-03 03:45:07', NULL),
(275, 'BCSE-31', 'NIKHIL SURESH NINAWE', 'student_1763716393_69202d29d3fe8.png', '2025-12-03 03:45:07', NULL),
(276, 'BCSE-32', 'NIKHILESH PRAKASH DIKONDWAR', 'student_1763716432_69202d50cd57c.png', '2025-12-03 03:45:07', NULL),
(277, 'BCSE-33', 'NISARG ATUL SATHAWANE', 'student_1763716470_69202d76a68c4.png', '2025-12-03 03:45:07', NULL),
(278, 'BCSE-34', 'PRACHI VIJAY GADGE', 'student_1763716526_69202daeab977.png', '2025-12-03 03:45:07', NULL),
(279, 'BCSE-35', 'PRANALI SUBHASH MOON', 'student_1763716568_69202dd852541.png', '2025-12-03 03:45:07', NULL),
(280, 'BCSE-36', 'PRANJAL KRUSHNA FULE', 'student_1763716632_69202e18b624c.png', '2025-12-03 03:45:07', NULL),
(281, 'BCSE-37', 'PRIYA BABANRAO NAGLE', 'student_1763716683_69202e4b14780.png', '2025-12-03 03:45:07', NULL),
(282, 'BCSE-38', 'PUNAM KRUSHNAKUMAR BISEN', 'student_1763716722_69202e72586ec.png', '2025-12-03 03:45:07', NULL),
(283, 'BCSE-39', 'RAXIT PRAVIN KADU', 'student_1763716774_69202ea6377fa.png', '2025-12-03 03:45:07', NULL),
(284, 'BCSE-40', 'RISHABH JAYPAL PATIL', 'student_1763716818_69202ed21b7d3.png', '2025-12-03 03:45:07', NULL),
(285, 'BCSE-41', 'RIYA NILESH BANKAR', 'student_1763716869_69202f051836b.png', '2025-12-03 03:45:07', NULL),
(286, 'BCSE-42', 'RIYA SANJAY KOLURWAR', 'student_1763716912_69202f30bb264.png', '2025-12-03 03:45:07', NULL),
(287, 'BCSE-43', 'ROUNAK VINAY SINGH', 'student_1763716977_69202f7127069.png', '2025-12-03 03:45:07', NULL),
(288, 'BCSE-44', 'RUCHIKA PRAKASH SONKUSARE', 'student_1763717030_69202fa66d785.png', '2025-12-03 03:45:07', NULL),
(289, 'BCSE-45', 'SAHIL GUNVANT CHAVHAN', 'student_1763717081_69202fd9899c6.png', '2025-12-03 03:45:07', NULL),
(290, 'BCSE-46', 'SAMIKSHA MAHESH FUNDE', 'student_1763717150_6920301e17896.png', '2025-12-03 03:45:07', NULL),
(291, 'BCSE-47', 'SAMRAT SARNATH MOHOD', 'student_1763717204_69203054a32f9.png', '2025-12-03 03:45:07', NULL),
(292, 'BCSE-48', 'SANCHITI SANJAY NIRWAN', 'student_1763717283_692030a3ef25f.png', '2025-12-03 03:45:07', NULL),
(293, 'BCSE-49', 'SANI GAUTAM DESHBHRATAR', 'student_1763717343_692030df99bb8.png', '2025-12-03 03:45:07', NULL),
(294, 'BCSE-50', 'SATYAM HANUMAN PRASAD SHUKLA', 'student_1763717394_6920311207238.png', '2025-12-03 03:45:07', NULL),
(295, 'BCSE-51', 'SHREYASH GAUTAM SORDE', 'student_1763717438_6920313e78611.png', '2025-12-03 03:45:07', NULL),
(296, 'BCSE-52', 'SHRUTI SHISHUPAL WALKE', 'student_1763717484_6920316ce1b36.png', '2025-12-03 03:45:07', NULL),
(297, 'BCSE-53', 'SHUBHANGI ASARAM ANDHALE', 'student_1763717533_6920319db4dca.png', '2025-12-03 03:45:07', NULL),
(298, 'BCSE-54', 'SHUBHANGI SANJAY JAYBHAYE', 'student_1763717575_692031c7e9ba4.png', '2025-12-03 03:45:07', NULL),
(299, 'BCSE-55', 'SIDDHARTH SHRIKANT UKEY', 'student_1763717630_692031fe804e6.png', '2025-12-03 03:45:07', NULL),
(300, 'BCSE-56', 'SMITA ANIL KAYANDE', 'student_1763717671_69203227c0caf.png', '2025-12-03 03:45:07', NULL),
(301, 'BCSE-57', 'SUDHANSHU NANDKISHOR BHASAKHETRE', 'student_1763717723_6920325bb55d4.png', '2025-12-03 03:45:07', NULL),
(302, 'BCSE-58', 'TANISH NITIN SONDOWLE', 'student_1763717769_692032874e61b.png', '2025-12-03 03:45:07', NULL),
(303, 'BCSE-59', 'TANUSHREE GANESH PANPATTE', 'student_1763717817_692032b988919.png', '2025-12-03 03:45:07', NULL),
(304, 'BCSE-60', 'TRISHA ANANDRAO THAMKE', 'student_1763717867_692032ebde3cd.png', '2025-12-03 03:45:07', NULL),
(305, 'BCSE-61', 'VAISHNAVI UMESH MUDIRAJ', 'student_1763717749_69203315d58eb.png', '2025-12-03 03:45:07', NULL),
(306, 'BCSE-62', 'VEDANT RAMESH BAGDE', 'student_1763717952_69203340e684f.png', '2025-12-03 03:45:07', NULL),
(307, 'BCSE-63', 'VIDHI BHUVANESHWAR BASEWAR', 'student_1763718006_6920337628240.png', '2025-12-03 03:45:07', NULL),
(308, 'BCSE-64', 'VIDISHA SHITAL DODKE', 'student_1763718049_692033a19e24b.png', '2025-12-03 03:45:07', NULL),
(309, 'BCSE-65', 'YAMINI ANIL WASADE', 'student_1763718093_692033cdd1d48.png', '2025-12-03 03:45:07', NULL),
(310, 'BCSE-18', 'MANMEET KAUR NARENDRA POTHIWAL', 'student_1763718248_6920346809416.png', '2025-12-03 03:45:07', NULL),
(311, 'BCSE-19', 'MANTHAN RAVINDRA ARGHODE', 'student_1763718600_692035c80b2e1.png', '2025-12-03 03:45:07', NULL),
(312, 'BCSE-66', 'YASH KAILASH NANNAWARE', 'student_1763718141_692033fd020e1.png', '2025-12-03 03:45:07', NULL),
(313, 'ACSE-01', 'ABIR GANESH GUJAR', 'student_1763710518_692016364b239.png', '2025-12-03 03:45:07', NULL),
(314, 'ACSE-02', 'ADIBA AFROZ HAMID SAYYED', 'student_1763710934_692017d6d9bb0.png', '2025-12-03 03:45:07', NULL),
(315, 'ACSE-03', 'ADITYA NIWRUTTI PATIL', 'student_1763711016_69201828919be.png', '2025-12-03 03:45:07', NULL),
(316, 'ACSE-04', 'ALIZA PRAVEEN NAIM KHAN', 'student_1763711086_6920186e6b927.png', '2025-12-03 03:45:07', NULL),
(317, 'ACSE-05', 'ANIKET DINKAR MUSALE', 'student_1763711146_692018aa99de4.png', '2025-12-03 03:45:07', NULL),
(318, 'ACSE-06', 'ANJALI SAMEER KIRNAKE', 'student_1763711242_6920190a60fa6.png', '2025-12-03 03:45:07', NULL),
(319, 'ACSE-07', 'ARYAN GANESH BHUTE', 'student_1763711302_69201946eedc1.png', '2025-12-03 03:45:07', NULL),
(320, 'ACSE-08', 'ASHWINI JITESH GAYAKWAD', 'student_1763711393_692019a1ca95f.png', '2025-12-03 03:45:07', NULL),
(321, 'ACSE-09', 'AYUSH NARESH SOMKUWAR', 'student_1763711453_692019ddf1ced.png', '2025-12-03 03:45:07', NULL),
(322, 'ACSE-10', 'AYUSH SUDHAKAR CHATULE', 'student_1763711516_69201a1c5a4ea.png', '2025-12-03 03:45:07', NULL),
(323, 'ACSE-11', 'BHARGAV SANTOSH DESHPANDE', 'student_1763711571_69201a53bdb11.png', '2025-12-03 03:45:07', NULL),
(324, 'ACSE-12', 'BINTIKUMAR KANHAIYA SINGH', 'student_1763711625_69201a8917c4c.png', '2025-12-03 03:45:07', NULL),
(325, 'ACSE-13', 'CHAITANYA ALANKAR BHAISARE', 'student_1763711677_69201abd678ba.png', '2025-12-03 03:45:07', NULL),
(326, 'ACSE-14', 'DEETI KALYANI SHRINIWAS', 'student_1763711750_69201b06b2568.png', '2025-12-03 03:45:07', NULL),
(327, 'ACSE-16', 'GURPREET KAUR SWARN SINGH MINHAS', 'student_1763711870_69201b7e53a1d.png', '2025-12-03 03:45:07', NULL),
(328, 'ACSE-15', 'DIVYA RAMDASJI KALBANDE', 'student_1763711975_69201be70185b.png', '2025-12-03 03:45:07', NULL),
(329, 'ACSE-17', 'HARSH ANIL CHAVHAN', 'student_1763712032_69201c20c215a.png', '2025-12-03 03:45:07', NULL),
(330, 'ACSE-18', 'JANHAVI VINOD SAWANT', 'student_1763712101_69201c65c7f7b.png', '2025-12-03 03:45:07', NULL),
(331, 'ACSE-19', 'JANKI JAYANT PATHAK', 'student_1763712157_69201c9d38e41.png', '2025-12-03 03:45:07', NULL),
(332, 'ACSE-20', 'JATAN GAJANAN JAMBHULKAR', 'student_1763712212_69201cd4dfdfc.png', '2025-12-03 03:45:07', NULL),
(333, 'ACSE-21', 'KOMAL RAVINDRA SARODE', 'student_1763712286_69201d1edc723.png', '2025-12-03 03:45:07', NULL),
(334, 'ACSE-22', 'KUNAL YOGIRAJ DESHMUKH', 'student_1763712343_69201d57103b8.png', '2025-12-03 03:45:07', NULL),
(335, 'ACSE-23', 'MADHURA SURESH GAJBHIYE', 'student_1763712397_69201d8d46230.png', '2025-12-03 03:45:07', NULL),
(336, 'ACSE-24', 'MAHEK VASUDEO DEKATE', 'student_1763712444_69201dbc82d17.png', '2025-12-03 03:45:07', NULL),
(337, 'ACSE-25', 'MAHESH MADAN WAGH', 'student_1763712508_69201dfc04af1.png', '2025-12-03 03:45:07', NULL),
(338, 'ACSE-26', 'MAISA VINOD KALMEGH', 'student_1763712568_69201e385fb63.png', '2025-12-03 03:45:07', NULL),
(339, 'ACSE-27', 'MANASHRI VIJAY PUND', 'student_1763712611_69201e63e4cc4.png', '2025-12-03 03:45:07', NULL),
(340, 'ACSE-28', 'MANDAR SANTOSH BANGALE', 'student_1763712658_69201e92828c0.png', '2025-12-03 03:45:07', NULL),
(341, 'ACSE-29', 'MANTASHA NOOR MOBEEN KAMAL', 'student_1763712881_69201f7179250.png', '2025-12-03 03:45:07', NULL),
(342, 'ACSE-30', 'MAYANK SHAM YADAV', 'student_1763712934_69201fa658c56.png', '2025-12-03 03:45:07', NULL),
(343, 'ACSE-31', 'NIKITA PRABHAKAR RAUT', 'student_1763713017_69201ff9b761d.png', '2025-12-03 03:45:07', NULL),
(344, 'ACSE-32', 'NILIMA NIRANJANDAS CHAURE', 'student_1763713062_6920202675289.png', '2025-12-03 03:45:07', NULL),
(345, 'ACSE-33', 'NISHIKA SANDIP JICHKAR', 'student_1763713110_692020567869e.png', '2025-12-03 03:45:07', NULL),
(346, 'ACSE-34', 'OM VISHNU PATIL', 'student_1763713156_6920208457c67.png', '2025-12-03 03:45:07', NULL),
(347, 'ACSE-35', 'PARI VIJAY WANDHARE', 'student_1763713197_692020ade27fa.png', '2025-12-03 03:45:07', NULL),
(348, 'ACSE-36', 'PARTH AVINASH THAKRE', 'student_1763713241_692020d9dcfd9.png', '2025-12-03 03:45:07', NULL),
(349, 'ACSE-37', 'PAYAL GAJANAN SALODE', 'student_1763713306_6920211a4365f.png', '2025-12-03 03:45:07', NULL),
(350, 'ACSE-38', 'PRAJAKTA SUNIL BOPULKAR', 'student_1763713353_69202149daec7.png', '2025-12-03 03:45:07', NULL),
(351, 'ACSE-39', 'PRATHAMESH VINOD DEHANKAR', 'student_1763713404_6920217c0129d.png', '2025-12-03 03:45:07', NULL),
(352, 'ACSE-40', 'PREM CHOPRAM ZINGARE', 'student_1763713439_6920219f2cdbe.png', '2025-12-03 03:45:07', NULL),
(353, 'ACSE-41', 'PRIME LILADHAR SAMRIT', 'student_1763713474_692021c2995f9.png', '2025-12-03 03:45:07', NULL),
(354, 'ACSE-42', 'PRIYANKA BANDU NINAVE', 'student_1763713525_692021f56ca75.png', '2025-12-03 03:45:07', NULL),
(355, 'ACSE-43', 'RAJVEER CHANDRABHAN GUPTA', 'student_1763713571_69202223de69f.png', '2025-12-03 03:45:07', NULL),
(356, 'ACSE-44', 'RUDRA RAJESH NINAWE', 'student_1763713616_692022506c936.png', '2025-12-03 03:45:07', NULL),
(357, 'ACSE-45', 'SAIRAM SHIVAJI PALLEKONDWAD', 'student_1763713655_69202277ca5c7.png', '2025-12-03 03:45:07', NULL),
(358, 'ACSE-46', 'SAKSHI DILIP KOLHE', 'student_1763713697_692022a1ca6d7.png', '2025-12-03 03:45:07', NULL),
(359, 'ACSE-47', 'SAMYAK MUNNESHWAR NAGRARE', 'student_1763713763_692022e3adba0.png', '2025-12-03 03:45:07', NULL),
(360, 'ACSE-48', 'SANSKRUTI SANJAY RATHOD', 'student_1763713818_6920231a404a3.png', '2025-12-03 03:45:07', NULL),
(361, 'ACSE-49', 'SAYALI NANDKISHOR JUGSENIYA', 'student_1763713855_6920233fba2fc.png', '2025-12-03 03:45:07', NULL),
(362, 'ACSE-50', 'SEJAL ROSHAN RAMTEKE', 'student_1763713891_69202363c1a95.png', '2025-12-03 03:45:07', NULL),
(363, 'ACSE-51', 'SHIVAM MANOJSINGH CHAVHAN', 'student_1763713935_6920238f0ef8c.png', '2025-12-03 03:45:07', NULL),
(364, 'ACSE-52', 'SHRADDHA HEMRAJ DONGARE', 'student_1763713981_692023bd27ac9.png', '2025-12-03 03:45:07', NULL),
(365, 'ACSE-53', 'SONALI ARVIND RAMTEKE', 'student_1763714024_692023e87f488.png', '2025-12-03 03:45:07', NULL),
(366, 'ACSE-54', 'TANISHKA SUNIL BORKAR', 'student_1763714082_69202422f1b95.png', '2025-12-03 03:45:07', NULL),
(367, 'ACSE-55', 'TANMAY SHARAD KAUTKAR', 'student_1763714131_6920245322c21.png', '2025-12-03 03:45:07', NULL),
(368, 'ACSE-56', 'TANUJA DNYANESHWAR NARINGE', 'student_1763714185_6920248957fcb.png', '2025-12-03 03:45:07', NULL),
(369, 'ACSE-57', 'TASMIYA NAUSHAD PATHAN', 'student_1763714226_692024b26c171.png', '2025-12-03 03:45:07', NULL),
(370, 'ACSE-58', 'TEJAS DEEPAK KACHHAWAH', 'student_1763714297_692024f9f26a2.png', '2025-12-03 03:45:07', NULL),
(371, 'ACSE-59', 'TEJAS SANTOSH SAHARE', 'student_1763714337_69202521f2445.png', '2025-12-03 03:45:07', NULL),
(372, 'ACSE-60', 'TULSI SANTOSH MESHRAM', 'student_1763714397_6920255d90ecc.png', '2025-12-03 03:45:07', NULL),
(373, 'ACSE-61', 'VEDANSHREE GAJANAN SAWAI', 'student_1763714442_6920258a67717.png', '2025-12-03 03:45:07', NULL),
(374, 'ACSE-62', 'VEDANT SUNIL PAWAR', 'student_1763714504_692025c85b8f9.png', '2025-12-03 03:45:07', NULL),
(375, 'ACSE-63', 'VIDHI PRAVIN TUPKAR', 'student_1763714551_692025f7510fc.png', '2025-12-03 03:45:07', NULL),
(376, 'ACSE-64', 'VISHESH SANTOSH BANGRE', 'student_1763714625_692026417cff1.png', '2025-12-03 03:45:07', NULL),
(377, 'ACSE-65', 'VISHWARI RAVINDRA CHINCHMALATPURE', 'student_1763714680_69202678553a3.png', '2025-12-03 03:45:07', NULL),
(378, 'ACSE-66', 'YASH KUNDLIK NANDESHWAR', 'student_1763714724_692026a496fae.png', '2025-12-03 03:45:07', NULL),
(379, 'CE-49', 'ANSHIKA SANTOSH KUMAR NAGDEVE', 'student_1763384871_691b1e2726538.jpeg', '2025-12-03 03:45:07', NULL),
(514, 'IT-01', 'AAVANYA VILAS KHANDAL', '', '2025-12-03 03:46:59', NULL),
(515, 'IT-02', 'ADITYA ANIL GOUR', '', '2025-12-03 03:46:59', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `faculty_load`
--

CREATE TABLE `faculty_load` (
  `id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `department_id` int(11) NOT NULL,
  `academic_year` varchar(20) NOT NULL,
  `semester` varchar(10) NOT NULL,
  `theory` int(11) NOT NULL DEFAULT 0,
  `practical` int(11) NOT NULL DEFAULT 0,
  `other_load` int(11) NOT NULL DEFAULT 0,
  `total` int(11) NOT NULL DEFAULT 0,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `faculty_load`
--

INSERT INTO `faculty_load` (`id`, `teacher_id`, `department_id`, `academic_year`, `semester`, `theory`, `practical`, `other_load`, `total`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 59, 4, '2025-2026', 'I', 5, 4, 0, 9, 15, '2025-12-03 06:20:09', '2025-12-03 06:20:09'),
(2, 22, 4, '2025-2026', 'I', 10, 0, 0, 10, 15, '2025-12-03 06:28:38', '2025-12-03 06:28:38'),
(3, 24, 4, '2025-2026', 'I', 10, 0, 0, 10, 15, '2025-12-03 06:29:11', '2025-12-03 06:29:11'),
(4, 51, 4, '2025-2026', 'I', 5, 4, 0, 9, 15, '2025-12-03 06:29:25', '2025-12-03 06:29:25'),
(5, 25, 4, '2025-2026', 'I', 12, 0, 0, 12, 15, '2025-12-03 06:30:00', '2025-12-03 06:30:00'),
(6, 23, 4, '2025-2026', 'I', 12, 0, 0, 12, 15, '2025-12-03 06:30:08', '2025-12-03 06:30:08'),
(7, 28, 4, '2025-2026', 'I', 8, 4, 0, 12, 15, '2025-12-03 06:30:23', '2025-12-03 06:30:23'),
(8, 53, 4, '2025-2026', 'I', 6, 12, 0, 18, 15, '2025-12-03 06:30:38', '2025-12-03 06:30:38'),
(9, 61, 4, '2025-2026', 'I', 0, 8, 0, 8, 15, '2025-12-03 06:30:55', '2025-12-03 06:30:55'),
(10, 62, 4, '2025-2026', 'I', 0, 8, 4, 12, 15, '2025-12-03 06:31:11', '2025-12-03 06:31:11'),
(11, 52, 4, '2025-2026', 'I', 12, 0, 0, 12, 15, '2025-12-03 06:31:19', '2025-12-03 06:31:19'),
(12, 29, 4, '2025-2026', 'I', 0, 20, 0, 20, 15, '2025-12-03 06:31:31', '2025-12-03 06:31:31'),
(13, 63, 4, '2025-2026', 'I', 10, 8, 0, 18, 15, '2025-12-03 06:31:45', '2025-12-03 06:31:45'),
(14, 35, 4, '2025-2026', 'I', 9, 0, 0, 9, 15, '2025-12-03 06:31:52', '2025-12-03 06:31:52'),
(15, 38, 4, '2025-2026', 'I', 5, 4, 0, 9, 15, '2025-12-03 06:32:13', '2025-12-03 06:32:13'),
(16, 57, 4, '2025-2026', 'I', 0, 16, 0, 16, 15, '2025-12-03 06:32:30', '2025-12-03 06:32:30'),
(17, 64, 4, '2025-2026', 'I', 0, 16, 0, 16, 15, '2025-12-03 06:32:50', '2025-12-03 06:32:50'),
(19, 34, 4, '2025-2026', 'I', 0, 4, 0, 4, 15, '2025-12-03 06:33:27', '2025-12-03 06:33:27'),
(20, 27, 4, '2025-2026', 'I', 10, 0, 0, 10, 15, '2025-12-03 06:35:01', '2025-12-03 06:35:01'),
(21, 39, 4, '2025-2026', 'I', 5, 4, 0, 9, 15, '2025-12-03 06:35:15', '2025-12-03 06:35:15'),
(22, 55, 4, '2025-2026', 'I', 5, 4, 0, 9, 15, '2025-12-03 06:35:25', '2025-12-03 06:35:25'),
(23, 40, 4, '2025-2026', 'I', 5, 0, 0, 5, 15, '2025-12-03 06:35:53', '2025-12-03 06:35:53'),
(24, 36, 4, '2025-2026', 'I', 4, 4, 0, 8, 15, '2025-12-03 06:36:00', '2025-12-03 06:36:00'),
(25, 37, 4, '2025-2026', 'I', 0, 4, 0, 4, 15, '2025-12-03 06:36:10', '2025-12-03 06:36:10'),
(26, 26, 4, '2025-2026', 'I', 6, 12, 0, 18, 15, '2025-12-03 06:36:19', '2025-12-03 06:36:19');

-- --------------------------------------------------------

--
-- Table structure for table `file_notifications`
--

CREATE TABLE `file_notifications` (
  `id` int(11) NOT NULL,
  `file_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hod_messages`
--

CREATE TABLE `hod_messages` (
  `id` int(11) NOT NULL,
  `hod_id` int(11) NOT NULL,
  `sent_by` int(11) NOT NULL,
  `message` text NOT NULL,
  `teacher_ids` text DEFAULT NULL COMMENT 'JSON array of teacher IDs with light load',
  `academic_year` varchar(20) NOT NULL,
  `semester` varchar(10) NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `leave_applications`
--

CREATE TABLE `leave_applications` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `leave_type` enum('sick','emergency','personal','family','other') NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `reason` text NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `teacher_remarks` text DEFAULT NULL,
  `attachment` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `login_attempts`
--

CREATE TABLE `login_attempts` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `attempt_time` datetime NOT NULL,
  `success` tinyint(1) NOT NULL DEFAULT 0,
  `user_agent` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `message_attachments`
--

CREATE TABLE `message_attachments` (
  `id` int(11) NOT NULL,
  `message_id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_size` int(11) NOT NULL,
  `file_type` varchar(100) NOT NULL,
  `uploaded_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `message_replies`
--

CREATE TABLE `message_replies` (
  `id` int(11) NOT NULL,
  `message_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `sender_type` enum('hod','teacher','student','parent') NOT NULL,
  `reply_text` text NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nit_importnoticess`
--

CREATE TABLE `nit_importnoticess` (
  `id` int(11) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `exam_date` date NOT NULL,
  `section` varchar(255) NOT NULL,
  `day` varchar(50) NOT NULL,
  `time` varchar(100) NOT NULL,
  `marks` varchar(50) NOT NULL,
  `department_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `nit_importnoticess`
--

INSERT INTO `nit_importnoticess` (`id`, `subject`, `exam_date`, `section`, `day`, `time`, `marks`, `department_id`, `created_at`, `updated_at`) VALUES
(1, 'Communication Skills ', '2025-12-04', 'CSEA,CSEB & IT', 'THURSDAY', '10:00  -  11:30', '30', 4, '2025-12-02 11:11:28', '2025-12-02 11:13:20'),
(2, 'Essential Physics', '2025-12-04', 'CE,EE,ME', 'THURSDAY', '10:00 - 11:30', '30', 4, '2025-12-02 11:13:45', '2025-12-02 11:13:45'),
(3, 'Essential of Chemistry', '2025-12-05', 'CSEA, CSEB & IT', 'FRIDAY', '10:00 - 11:30 ', '30', 4, '2025-12-02 11:15:09', '2025-12-02 11:15:44'),
(4, 'Communication Skills', '2025-12-05', 'CE, EE,ME', 'FRIDAY', '10:00 - 11:30', '30', 4, '2025-12-02 11:16:39', '2025-12-02 11:16:39'),
(5, 'A- Mathematics-I', '2025-12-06', 'CSEA,CSEB & IT', 'SATURDAY', '10:00 - 11:30 ', '30', 4, '2025-12-02 11:18:17', '2025-12-02 11:18:17'),
(6, 'Engineering Graphic', '2025-12-06', 'CE,EE,ME', 'SATURDAY', '10:00 - 11:30', '30', 4, '2025-12-02 11:19:49', '2025-12-02 11:19:49'),
(7, 'Problem solving using \"c\"', '2025-12-08', 'CSEA,CSEB & IT', 'MONDAY', '10:00 - 11:30', '30', 4, '2025-12-02 11:21:09', '2025-12-02 11:21:09'),
(8, 'FOV,BEE,C-Programming', '2025-12-08', 'CE,ME,EE', 'MONDAY', '10:00 - 11:30', '30', 4, '2025-12-02 11:22:10', '2025-12-02 11:22:10'),
(9, 'Basic Electrical and Electronies Engineering', '2025-12-09', 'CSEA,CSEB & IT', 'TUESDAY', '10:00 - 11:30', '30', 4, '2025-12-02 11:24:22', '2025-12-02 11:24:22'),
(10, 'A- Mathematics-I', '2025-12-09', 'CE,EE,ME', 'TUESDAY', '10:00 - 11:30', '30', 4, '2025-12-02 11:25:09', '2025-12-02 11:25:09');

-- --------------------------------------------------------

--
-- Table structure for table `notices`
--

CREATE TABLE `notices` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `notice_type` enum('info','warning','success','danger') DEFAULT 'info',
  `priority` enum('low','medium','high') DEFAULT 'medium',
  `target_audience` enum('all','students','teachers','hods','parents') DEFAULT 'all',
  `is_active` tinyint(1) DEFAULT 1,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notices`
--

INSERT INTO `notices` (`id`, `title`, `message`, `notice_type`, `priority`, `target_audience`, `is_active`, `start_date`, `end_date`, `created_by`, `created_at`, `updated_at`) VALUES
(5, 'Alert message', 'This is to inform you that tomorrow has been granted as a holiday  due to the general Election of Nagar Panchayat and Nagar Parishad.', 'info', 'high', 'all', 1, '2025-12-01', '2025-12-02', 1, '2025-12-01 16:56:27', '2025-12-01 16:56:27'),
(9, 'Notice', 'All the students kindly note this is time to give best in the MST2 as the best score out of 2 MST is considered for the internal marks.Do hard work to improve your result.Your attendance in daily classes and class test is very important for the preparation of exams.', 'danger', 'high', 'all', 1, '2025-12-02', '2025-12-09', 15, '2025-12-02 14:09:48', '2025-12-02 14:09:48');

-- --------------------------------------------------------

--
-- Table structure for table `paper_marks`
--

CREATE TABLE `paper_marks` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `exam_type_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `marks_obtained` decimal(5,2) NOT NULL,
  `max_marks` int(11) NOT NULL,
  `percentage` decimal(5,2) DEFAULT 0.00,
  `grade` varchar(5) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `year` int(11) NOT NULL,
  `semester` int(11) NOT NULL,
  `academic_year` varchar(20) NOT NULL,
  `exam_date` date DEFAULT NULL,
  `is_published` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Triggers `paper_marks`
--
DELIMITER $$
CREATE TRIGGER `calculate_grade_before_insert` BEFORE INSERT ON `paper_marks` FOR EACH ROW BEGIN
    DECLARE calculated_percentage DECIMAL(5,2);
    
    SET calculated_percentage = (NEW.marks_obtained / NEW.max_marks) * 100;
    SET NEW.percentage = calculated_percentage;
    
    IF calculated_percentage >= 90 THEN SET NEW.grade = 'O';
    ELSEIF calculated_percentage >= 80 THEN SET NEW.grade = 'A+';
    ELSEIF calculated_percentage >= 70 THEN SET NEW.grade = 'A';
    ELSEIF calculated_percentage >= 60 THEN SET NEW.grade = 'B+';
    ELSEIF calculated_percentage >= 50 THEN SET NEW.grade = 'B';
    ELSEIF calculated_percentage >= 40 THEN SET NEW.grade = 'C';
    ELSE SET NEW.grade = 'F';
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `calculate_grade_before_update` BEFORE UPDATE ON `paper_marks` FOR EACH ROW BEGIN
    DECLARE calculated_percentage DECIMAL(5,2);
    
    SET calculated_percentage = (NEW.marks_obtained / NEW.max_marks) * 100;
    SET NEW.percentage = calculated_percentage;
    
    IF calculated_percentage >= 90 THEN SET NEW.grade = 'O';
    ELSEIF calculated_percentage >= 80 THEN SET NEW.grade = 'A+';
    ELSEIF calculated_percentage >= 70 THEN SET NEW.grade = 'A';
    ELSEIF calculated_percentage >= 60 THEN SET NEW.grade = 'B+';
    ELSEIF calculated_percentage >= 50 THEN SET NEW.grade = 'B';
    ELSEIF calculated_percentage >= 40 THEN SET NEW.grade = 'C';
    ELSE SET NEW.grade = 'F';
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `parents`
--

CREATE TABLE `parents` (
  `id` int(11) NOT NULL,
  `parent_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `student_id` int(11) NOT NULL,
  `relationship` enum('father','mother','guardian') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_login` datetime DEFAULT NULL,
  `failed_login_attempts` int(11) DEFAULT 0,
  `account_locked_until` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `parents`
--

INSERT INTO `parents` (`id`, `parent_name`, `email`, `phone`, `photo`, `password`, `student_id`, `relationship`, `created_at`, `last_login`, `failed_login_attempts`, `account_locked_until`) VALUES
(13, 'Mr. Rajendra Patil', 'rajendrapatil@gmail.com', '9545966656', 'parent_1763386691_691b2543570b2.jpeg', '$2y$10$70jxX4SoxjllzA2CG8TKAe5atjGEkAXVV4N8yZllH2B9EUcF6xF7S', 149, 'father', '2025-11-17 13:38:11', '2025-12-04 21:56:40', 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `security_logs`
--

CREATE TABLE `security_logs` (
  `id` int(11) NOT NULL,
  `event_type` varchar(50) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `details` text DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `security_logs`
--

INSERT INTO `security_logs` (`id`, `event_type`, `ip_address`, `user_agent`, `details`, `user_id`, `created_at`) VALUES
(1, 'SYSTEM_INIT', '127.0.0.1', 'System', 'Security system initialized', NULL, '2025-12-04 17:34:01');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `roll_number` varchar(50) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `department_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `year` int(11) NOT NULL,
  `semester` int(11) NOT NULL,
  `admission_year` varchar(10) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_login` datetime DEFAULT NULL,
  `failed_login_attempts` int(11) DEFAULT 0,
  `account_locked_until` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `user_id`, `roll_number`, `full_name`, `email`, `phone`, `photo`, `password`, `department_id`, `class_id`, `year`, `semester`, `admission_year`, `is_active`, `created_at`, `last_login`, `failed_login_attempts`, `account_locked_until`) VALUES
(135, NULL, 'IT-01', 'AAVANYA VILAS KHANDAL', 'aavanyakhandal_7254it25@nit.edu.in', '9309050268', '', '$2y$10$dkE2CiqDrIgZ8XmNUflph.Jbvka55b3M4BPnk.jNM1fOMtU3x3dia', 4, 79, 1, 1, '2025', 1, '2025-11-17 07:34:29', NULL, 0, NULL),
(136, NULL, 'IT-02', 'ADITYA ANIL GOUR', 'adityagour_7139it25@nit.edu.in', '7517629740', '', '$2y$10$.8u5vCvxhSiz7qA7SdJNouMmVkTpQnh2NbmebUNEGqzGWTEjWAe7S', 4, 79, 1, 1, '2025', 1, '2025-11-17 07:36:21', NULL, 0, NULL),
(137, NULL, 'IT-03', 'ANSHIKA SANTOSH KUMAR NAGDEVE', '[anshikanagdeve_7072it25@nit.edu.in](mailto:anshikanagdeve_7072it25@nit.edu.in)', '9623957788', 'student_1763384871_691b1e2726538.jpeg', '$2y$10$ZFAF0KmRc7zn9kgM3eZRX..BvKMrdMpEA4SuoMB03GY3.Kxn11Yem', 4, 79, 1, 1, '2025', 1, '2025-11-17 07:37:51', NULL, 0, NULL),
(138, NULL, 'IT-04', 'ANUJ BISEK NAKPURE', '[anujnakpure_7295it25@nit.edu.in](mailto:anujnakpure_7295it25@nit.edu.in)', '9209464838', 'student_1763384952_691b1e784bf8e.jpeg', '$2y$10$xqY50yU3GOBte0FMEvoBCexC0/.jfHZEwF2t8oPZTZWHwDEeWyKmq', 4, 79, 1, 1, '2025', 1, '2025-11-17 07:39:12', NULL, 0, NULL),
(139, NULL, 'IT-05', 'ARYAN GAJANAN GHANMODE', '[aryanghanmode_6945it25@nit.edu.in](mailto:aryanghanmode_6945it25@nit.edu.in)', '8983216759', 'student_1763385007_691b1eaf1c9dc.jpeg', '$2y$10$f6foBcEUsevJ7YU3EXbmoudAxMnCoUn1dfVmyUEu.JLfNr7KKV.rC', 4, 79, 1, 1, '2025', 1, '2025-11-17 07:40:07', NULL, 0, NULL),
(140, NULL, 'IT-06', 'ARYAN SUNIL PATIL', '[aryanpatil_7307it25@nit.edu.in](mailto:aryanpatil_7307it25@nit.edu.in)', '9322954125', 'student_1763385051_691b1edb8d40a.jpeg', '$2y$10$srQloiojStAK61YfLFsLnum0kwlQnZ0aQToyK5s0ZII4q70VQZpXS', 4, 79, 1, 1, '2025', 1, '2025-11-17 07:40:51', NULL, 0, NULL),
(141, NULL, 'IT-07', 'ARYAN VINOD MANGHATE', '[aryanmanghate_7081it25@nit.edu.in](mailto:aryanmanghate_7081it25@nit.edu.in)', '8329871648', 'student_1763385098_691b1f0abf1c8.jpeg', '$2y$10$rAATM3rKnEIDrO0nvij17u8pRTVMlA3YnMdc4JAeyv0O6cxY54ahC', 4, 79, 1, 1, '2025', 1, '2025-11-17 07:41:38', NULL, 0, NULL),
(142, NULL, 'IT-08', 'ATHARVA RAJENDRA SATIKOSARE', '[atharvasatikosare_7114it25@nit.edu.in](mailto:atharvasatikosare_7114it25@nit.edu.in)', '9860052615', 'student_1763385142_691b1f3657db4.jpeg', '$2y$10$E7KIYNCIiTA5tF/2M0QE2uLlyOXygtz/TDgNH3D/8WrnN0YbPXkyK', 4, 79, 1, 1, '2025', 1, '2025-11-17 07:42:22', NULL, 0, NULL),
(143, NULL, 'IT-09', 'ATHARVA SANTOSH DEULKAR', '[atharvadeulkar_7187it25@nit.edu.in](mailto:atharvadeulkar_7187it25@nit.edu.in)', '8956960820', 'student_1763385201_691b1f7129e7b.jpeg', '$2y$10$N5OYTGMALfObTdQNBOIlYeH.G6FkvEH/5CsNqCRMGkpO3lkypEB.u', 4, 79, 1, 1, '2025', 1, '2025-11-17 07:43:21', NULL, 0, NULL),
(144, NULL, 'IT-10', 'BHAKTI BALIRAM KURWADE', '[bhaktikurwade_7210it25@nit.edu.in](mailto:bhaktikurwade_7210it25@nit.edu.in)', '7218427723', 'student_1763385249_691b1fa1ae71c.jpeg', '$2y$10$XH91g.XtwQJwE2q3C8YFJO9GtTsagcaIOupOWJh7z9zJA9ggT/Q3u', 4, 79, 1, 1, '2025', 1, '2025-11-17 07:44:09', NULL, 0, NULL),
(145, NULL, 'IT-11', 'DEVYANI RAJENDRA PAL', '[devyanipal_7263it25@nit.edu.in](mailto:devyanipal_7263it25@nit.edu.in)', '9209540013', 'student_1763385301_691b1fd5afc10.jpeg', '$2y$10$1TUICo9X3sT0R7zKV6ZGP.YNq7yMHzUsIH5QGifVYCzd1TlfbfGXS', 4, 79, 1, 1, '2025', 1, '2025-11-17 07:45:01', NULL, 0, NULL),
(146, NULL, 'IT-12', 'DHANASHREE DINESH MAHORKAR', '[dhanashreemahorkar_7180it25@nit.edu.in](mailto:dhanashreemahorkar_7180it25@nit.edu.in)', '8237397356', 'student_1763385361_691b2011ae1a1.jpeg', '$2y$10$hjtkJ76wKhrbgsB8etlit.Vwtq/BmBMdGOj5q8oByDBvdbZGDZF.G', 4, 79, 1, 1, '2025', 1, '2025-11-17 07:46:01', NULL, 0, NULL),
(147, NULL, 'IT-13', 'DHANASHREE SHIVAJI KAYANDE', '[dhanashreekayande_7189it25@nit.edu.in](mailto:dhanashreekayande_7189it25@nit.edu.in)', '9834835808', 'student_1763385471_691b207fcb159.jpeg', '$2y$10$rf5bXqFF8zvjoRj69I2uROjGeefmbOlGP7cE29Bmbk2ZNtzz7P9iu', 4, 79, 1, 1, '2025', 1, '2025-11-17 07:47:51', NULL, 0, NULL),
(148, NULL, 'IT-14', 'GAYATRI ARUN SHEWALKAR', '[gayatrishewalkar_7145it25@nit.edu.in](mailto:gayatrishewalkar_7145it25@nit.edu.in)', '8446456270', 'student_1763385516_691b20acec94e.jpeg', '$2y$10$3vv8toFxEuZQJp3d.wa4q.Y77OcdIZtn/K1Zswtz1XIA07Emhnygi', 4, 79, 1, 1, '2025', 1, '2025-11-17 07:48:36', NULL, 0, NULL),
(149, NULL, 'IT-15', 'HIMANSHU RAJENDRA PATIL', '[himanshupatil_7094it25@nit.edu.in](mailto:himanshupatil_7094it25@nit.edu.in)', '8788209773', 'student_149_1764777948.png', '$2y$10$iRceNQNH6/P0enHQiplJgucmc16lGXe25WaQJgNFMVFNqzsj1yA8m', 4, 79, 1, 1, '2025', 1, '2025-11-17 07:50:30', '2025-12-04 21:48:06', 0, NULL),
(150, NULL, 'IT-16', 'KOMAL ATISH SHASTRAKAR', '[komalshastrakar_7275it25@nit.edu.in](mailto:komalshastrakar_7275it25@nit.edu.in)', '9637002755', 'student_1763386809_691b25b95743c.jpeg', '$2y$10$lewB/5edzUuI8h0vxZYbDe7fgv11XLaMTWUkV7iypJMI.sJrfNwA6', 4, 79, 1, 1, '2025', 1, '2025-11-17 08:10:09', NULL, 0, NULL),
(151, NULL, 'IT-17', 'KRUTIKA PRITAM BALPANDE', '[krutikabalpande_6998it25@nit.edu.in](mailto:krutikabalpande_6998it25@nit.edu.in)', '9881869681', 'student_151_1763386899.jpeg', '$2y$10$DwgYu0p3ie5uKPwf36yjquduwJfuARsApTy6JlDYBfymyhyVB5IOO', 4, 79, 1, 1, '2025', 1, '2025-11-17 08:11:01', NULL, 0, NULL),
(152, NULL, 'IT-18', 'KUNDAN GUNARAM GAIDHANE', '[kundangaidhane_7197it25@nit.edu.in](mailto:kundangaidhane_7197it25@nit.edu.in)', '9764851134', 'student_1763386976_691b26601c457.jpeg', '$2y$10$D2TUdCkkILsywoI9d9PnPegwTbN9PRv/PRqEE60/BqjfWtVj.ADWm', 4, 79, 1, 1, '2025', 1, '2025-11-17 08:12:56', NULL, 0, NULL),
(153, NULL, 'IT-19', 'MAMTA SUBHASH KOTHE', '[mamtakothe_7250it25@nit.edu.in](mailto:mamtakothe_7250it25@nit.edu.in)', '8888442596', 'student_1763387034_691b269a722a0.jpeg', '$2y$10$LoYRC8i2hCjROonDA3Hn1.0o4Ec4KS5PLRCYE4eGJEZIkJigY0sHG', 4, 79, 1, 1, '2025', 1, '2025-11-17 08:13:54', NULL, 0, NULL),
(154, NULL, 'IT-20', 'MANASI VILAS SHEGAONKAR', '[manasishegaonkar_7151it25@nit.edu.in](mailto:manasishegaonkar_7151it25@nit.edu.in)', '7385216816', 'student_1763387102_691b26dea258f.jpeg', '$2y$10$ZM78Bv7acBz9R9EqAqe0TuIeB0g86gYydBCtl49rMj4gX37au6x36', 4, 79, 1, 1, '2025', 1, '2025-11-17 08:15:02', NULL, 0, NULL),
(155, NULL, 'IT-21', 'MANASWI PRAKASH BHAWALKAR', '[manaswibhawalkar_7214it25@nit.edu.in](mailto:manaswibhawalkar_7214it25@nit.edu.in)', '8830546235', 'student_1763387143_691b27070d959.jpeg', '$2y$10$8.fULD9sYlRVsanj.sIsiOOC29t63pvY19jMFOpx/lAa2zbuozH0i', 4, 79, 1, 1, '2025', 1, '2025-11-17 08:15:43', NULL, 0, NULL),
(156, NULL, 'IT-22', 'NALINI RANJEET BISWAS', '[nalinibiswas_7111it25@nit.edu.in](mailto:nalinibiswas_7111it25@nit.edu.in)', '7517903753', 'student_1763387183_691b272fda7a5.jpeg', '$2y$10$AfuBeswNzKPUGaaxwCQ5/OW20OAAFTBLrB9fg0V4XeBoBwPrryiRi', 4, 79, 1, 1, '2025', 1, '2025-11-17 08:16:23', NULL, 0, NULL),
(157, NULL, 'IT-23', 'NARGIS BRAMHANAND CHAUDHARI', '[nargischaudhari_7108it25@nit.edu.in](mailto:nargischaudhari_7108it25@nit.edu.in)', '9356848498', 'student_1763387223_691b27570024d.jpeg', '$2y$10$wu8Hq1zrBXGN/Z6C7r8OP.DtHIgMZOtefNKPVYiLq5U6bkNzoAKM.', 4, 79, 1, 1, '2025', 1, '2025-11-17 08:17:03', NULL, 0, NULL),
(158, NULL, 'IT-24', 'NEHA DHANRAJ WAKDE', '[nehawakde_7062it25@nit.edu.in](mailto:nehawakde_7062it25@nit.edu.in)', '9552179076', 'student_1763387264_691b2780abc29.jpeg', '$2y$10$Igb.6Odr6iMgSCF77/E.F.QeZBw4sFwWMvPkF1wehrre7JTNMiy4q', 4, 79, 1, 1, '2025', 1, '2025-11-17 08:17:44', NULL, 0, NULL),
(159, NULL, 'IT-25', 'NIKITA DADARAO PATIL', '[nikitapatil_7084it25@nit.edu.in](mailto:nikitapatil_7084it25@nit.edu.in)', '8600112994', 'student_1763387304_691b27a805899.jpeg', '$2y$10$xwMNyZkTVSrAiE2EdvnNaeHm9iD32Ej7QNeFFeBRTB4kmYN5FewuO', 4, 79, 1, 1, '2025', 1, '2025-11-17 08:18:24', NULL, 0, NULL),
(160, NULL, 'IT-26', 'OM NARENDRA AUSARMOL', '[omausarmol_7178it25@nit.edu.in](mailto:omausarmol_7178it25@nit.edu.in)', '8421214208', 'student_1763387360_691b27e0c0659.jpeg', '$2y$10$GEBnbAYWThQj7D.RjJHnsORINLl6jXlQmho3zVsa2On4Fj7ap5mUC', 4, 79, 1, 1, '2025', 1, '2025-11-17 08:19:20', NULL, 0, NULL),
(161, NULL, 'IT-27', 'OM RAMKRISHNA KHADATKAR', '[omkhadatkar_7274it25@nit.edu.in](mailto:omkhadatkar_7274it25@nit.edu.in)', '9028510918', 'student_1763387416_691b28189b382.jpeg', '$2y$10$S8RJY7Ch17Ol8XPF6IBeEuEt.nJ1U.Yjgo/EQ.vJ/50EfU7rz9gKi', 4, 79, 1, 1, '2025', 1, '2025-11-17 08:20:16', NULL, 0, NULL),
(162, NULL, 'IT-28', 'PIYUSH JAYRAM PRASAD', '[piyushprasad_7224it25@nit.edu.in](mailto:piyushprasad_7224it25@nit.edu.in)', '7870276275', 'student_1763387460_691b28448f1aa.jpeg', '$2y$10$XNLCawLBofZ8H2kYwf3M0OVNmzFXRuHgCFMeDNNnUDkCQVT6Mmevm', 4, 79, 1, 1, '2025', 1, '2025-11-17 08:21:00', NULL, 0, NULL),
(163, NULL, 'IT-29', 'PRANAY YOGRAJ PANORE', '[pranaypanore_6981it25@nit.edu.in](mailto:pranaypanore_6981it25@nit.edu.in)', '9699151494', 'student_163_1763448683.png', '$2y$10$iUSIqHJMYL/GXcUiO4AoRO4.dbG3tEGEuiYvteiYVqrqb6RQWXryu', 4, 79, 1, 1, '2025', 1, '2025-11-17 08:21:43', NULL, 0, NULL),
(164, NULL, 'IT-30', 'PRANJALI RAJESH BARGATH', '[pranjalibargath_7238it25@nit.edu.in](mailto:pranjalibargath_7238it25@nit.edu.in)', '9699128614', 'student_1763387557_691b28a567b61.jpeg', '$2y$10$nIEOL2wpjtzXSFBntUgNn.D1yP0EwokHxC7s7i9gURV7k8OHtqjRq', 4, 79, 1, 1, '2025', 1, '2025-11-17 08:22:37', NULL, 0, NULL),
(165, NULL, 'IT-31', 'PRATHMESH PREMDAS NICHANT', 'prathmeshnichant_7291it25@nit.edu.in', '9689070135', 'student_1763387613_691b28ddc88ce.jpeg', '$2y$10$2Cc.7ghNoeXlg9LvxTYf.en5n.CNwn6IsaBNt.8V.TWe.E2kmrqsK', 4, 79, 1, 1, '2025', 1, '2025-11-17 08:23:33', NULL, 0, NULL),
(166, NULL, 'IT-32', 'PREETI JIYALAL SHAHU', 'preetishahu_7052it25@nit.edu.in', '9322278183', 'student_1763387691_691b292b90340.jpeg', '$2y$10$0G0igYIKAtk7QvR/SCoUYe307b8E24dfsO.KuPFUyl1EJAMDpiRA2', 4, 79, 1, 1, '2025', 1, '2025-11-17 08:24:51', NULL, 0, NULL),
(167, NULL, 'IT-33', 'PURVA RAJESH REWATKAR', 'purvarewatkar_7184it25@nit.edu.in', '9699627399', 'student_1763387748_691b296428b27.jpeg', '$2y$10$G2o9rY9qrGzaJzG9V17r4OdGrlblUqCo1t8xaVsSneXg.Ql.JwlFe', 4, 79, 1, 1, '2025', 1, '2025-11-17 08:25:48', NULL, 0, NULL),
(168, NULL, 'IT-34', 'RITESH PANJAB DHULASE', 'riteshdhulase_7095it25@nit.edu.in', '9356608789', 'student_1763387806_691b299e7f455.jpeg', '$2y$10$VAfBH9AAiASyoIQm5y3wme/CEb6n/QoKXW9.ntlt2Y9enZ22I3MWi', 4, 79, 1, 1, '2025', 1, '2025-11-17 08:26:46', NULL, 0, NULL),
(169, NULL, 'IT-35', 'RIYA KISHOR YELEKAR', 'riyayelekar_7153it25@nit.edu.in', '8928068265', 'student_1763387874_691b29e2a451a.jpeg', '$2y$10$JwtfsuyjcQWSH6nW9C5AjeG1.T2emQ4FnIEnt7aHsF6vzl5wJLeAe', 4, 79, 1, 1, '2025', 1, '2025-11-17 08:27:54', NULL, 0, NULL),
(170, NULL, 'IT-36', 'RIYA PRASHANT JAMGADE', 'riyajamgade_7271it25@nit.edu.in', '9022093269', 'student_1763387926_691b2a163d218.jpeg', '$2y$10$0F/u588dcCl71OyMOE38BOhLTmKStoM7xr7XjLF1MxyE.BWCauLD6', 4, 79, 1, 1, '2025', 1, '2025-11-17 08:28:46', NULL, 0, NULL),
(171, NULL, 'IT-37', 'RIYA SANTOSH BHAGAT', 'riyabhagat_7130it25@nit.edu.in', '8390008309', 'student_1763387976_691b2a48bf6e3.jpeg', '$2y$10$G52n5MZrXkpteYfyxjVBkezsYNSfRaguifL4eD8XiX7mtj0Znwdwm', 4, 79, 1, 1, '2025', 1, '2025-11-17 08:29:36', NULL, 0, NULL),
(172, NULL, 'IT-38', 'RIYA SANTOSH MUSALE', 'riyamusale_7194it25@nit.edu.in', '9307275418', 'student_1763388034_691b2a823f864.jpeg', '$2y$10$3jtSpMgRPiHUn.6WVRRbleX9wZTLSUGwbfoxmanIff/tRgnB5XOR.', 4, 79, 1, 1, '2025', 1, '2025-11-17 08:30:34', NULL, 0, NULL),
(173, NULL, 'IT-39', 'ROHAN PRAMOD KHADSE', 'rohankhadse_7107it25@nit.edu.in', '9284023176', 'student_1763388086_691b2ab6aed84.jpeg', '$2y$10$57CyN28UvEiMRLUoJCPyRePI73Q25zNHrae6vMCq2lh9mMZ7PXH8e', 4, 79, 1, 1, '2025', 1, '2025-11-17 08:31:26', NULL, 0, NULL),
(174, NULL, 'IT-40', 'ROHIT RUPCHAND KHOBRAGADE', 'rohitkhobragade_7313it25@nit.edu.in', '9021550328', 'student_1763388134_691b2ae6d9f56.jpeg', '$2y$10$jA3Oqp8nbl/t5UFGr9PJaukgIyayJuLH4/Pv6imM4lqcB.AuhBlc2', 4, 79, 1, 1, '2025', 1, '2025-11-17 08:32:14', NULL, 0, NULL),
(175, NULL, 'IT-41', 'ROHIT SANDIP RATHOD', 'rohitrathod_6996it25@nit.edu.in', '7410761022', 'student_1763388185_691b2b19d297d.jpeg', '$2y$10$G3Ib1avO86y7sQrsu9pMeON3ESIMVAtTQV/h9ZGER2Nb1.EFV5wJS', 4, 79, 1, 1, '2025', 1, '2025-11-17 08:33:05', NULL, 0, NULL),
(176, NULL, 'IT-42', 'SAKSHI MORESHWAR MESHRAM', 'sakshimeshram_7192it25@nit.edu.in', '9322403889', 'student_1763388235_691b2b4bd7da9.jpeg', '$2y$10$uF4qubwL53kNj3NSh4LUG.RSEkY0LdtW3yu2YzRg0BCoTxEPGK6Pi', 4, 79, 1, 1, '2025', 1, '2025-11-17 08:33:55', NULL, 0, NULL),
(177, NULL, 'IT-43', 'SALONI PURUSHOTTAM CHOPDE', 'salonichopde_7248it25@nit.edu.in', '8459599077', 'student_1763388287_691b2b7fe3fa3.jpeg', '$2y$10$EOpqqQyK7hQC0SP9LyhSkuPlyk4OZCMw6uUm1c8Ms/zi.wZpAcrty', 4, 79, 1, 1, '2025', 1, '2025-11-17 08:34:47', NULL, 0, NULL),
(178, NULL, 'IT-44', 'SAMARTH KISHOR BHOYAR', 'samarthbhoyar_7099it25@nit.edu.in', '9322348535', 'student_1763388350_691b2bbe25e3b.jpeg', '$2y$10$awkbgkCglr/s9dKNk0paXe8gj2tkhUa7ulHR9VcSS9dQXqxodjoEW', 4, 79, 1, 1, '2025', 1, '2025-11-17 08:35:50', NULL, 0, NULL),
(179, NULL, 'IT-45', 'SAMIKSHA PRAKASH KANERE', 'samikshakanere_7278it25@nit.edu.in', '9764573116', 'student_1763388395_691b2beba7e17.jpeg', '$2y$10$Iu8nf.FJC8XLtWi8icU/u.TDUJMjJBi99aJ6YDS4M/IK72YMkt/Ru', 4, 79, 1, 1, '2025', 1, '2025-11-17 08:36:35', NULL, 0, NULL),
(180, NULL, 'IT-46', 'SAROJ HARIDAS BAGDE', 'sarojbagde_7306it25@nit.edu.in', '8983816886', 'student_1763388456_691b2c2806b6e.jpeg', '$2y$10$a8PyNcNbhUrqqU4gPFgWb.qaUWvG9qgxMbNfzhgnw60PKD22.Vp8q', 4, 79, 1, 1, '2025', 1, '2025-11-17 08:37:36', NULL, 0, NULL),
(181, NULL, 'IT-47', 'SARTHAK VILAS MESHRAM', 'sarthakmeshram_7102it25@nit.edu.in', '9766302812', 'student_1763388505_691b2c59505a8.jpeg', '$2y$10$LupvlwPUDZJQ/z8Iqo49WeCK5HEhCsUd/slCuqfK/QO.IjoUBA0vq', 4, 79, 1, 1, '2025', 1, '2025-11-17 08:38:25', NULL, 0, NULL),
(182, NULL, 'IT-48', 'SHIVRAJ GANGADHAR DHAVALE', 'shivrajdhavale_7201it25@nit.edu.in', '8805577509', 'student_1763388548_691b2c8497b78.jpeg', '$2y$10$sQ4asvrlndQd0nfO4Ncf8.p9VapXjh.jC2VUHjqIYrrZr0VCa9MaW', 4, 79, 1, 1, '2025', 1, '2025-11-17 08:39:08', NULL, 0, NULL),
(183, NULL, 'IT-49', 'SHRAVANI RAMESHWAR AMBULKAR', 'shravaniambulkar_7196it25@nit.edu.in', '9284517546', 'student_1763388598_691b2cb62fd77.jpeg', '$2y$10$2vjcqoyM.1O/uIvAG7DYGesN85RXJJrhksGNoBLKe2j7DQMpzhuR6', 4, 79, 1, 1, '2025', 1, '2025-11-17 08:39:58', NULL, 0, NULL),
(184, NULL, 'IT-50', 'SHRUTI SANJAY WANDEKAR', 'shrutiwandekar_7205it25@nit.edu.in', '9673908512', 'student_1763388642_691b2ce29776e.jpeg', '$2y$10$kLJZfe21nWAdmQIn5QBvwebiD4oWuHxBPalA6NxT/DCnQqgMRhuGq', 4, 79, 1, 1, '2025', 1, '2025-11-17 08:40:42', NULL, 0, NULL),
(185, NULL, 'IT-51', 'SHRUTI SEWAK KOHAD', 'shrutikohad_7303it25@nit.edu.in', '9075288540', 'student_1763388691_691b2d1341579.jpeg', '$2y$10$qZ.0oMEGSxgZhdiFt9jVCeloYRggZe/cE5TWDSchs6ndjFGfdjhZa', 4, 79, 1, 1, '2025', 1, '2025-11-17 08:41:31', NULL, 0, NULL),
(186, NULL, 'IT-52', 'SHUBHAM SANJAY PAWAR', 'shubhampawar_7142it25@nit.edu.in', '9373964092', 'student_1763388746_691b2d4abca7f.jpeg', '$2y$10$CmcUjWZYzioQHo2E5X./m.Q6AmrTBAzgs3symBhFel.or4ICZ47xu', 4, 79, 1, 1, '2025', 1, '2025-11-17 08:42:26', NULL, 0, NULL),
(187, NULL, 'IT-53', 'SIDDHANT RAJESH MAGARDE', 'siddhantmagarde_7159it25@nit.edu.in', '9322165638', 'student_1763388792_691b2d78733ce.jpeg', '$2y$10$J/EzAWJH4U8Z2iCFshj1KucM9RktPGq3mK4Oo1Fz0gkT19rSN.DYu', 4, 79, 1, 1, '2025', 1, '2025-11-17 08:43:12', NULL, 0, NULL),
(188, NULL, 'IT-54', 'SOHAM DINESH GULHANE', 'sohamgulhane_7193it25@nit.edu.in', '9359947568', 'student_1763388831_691b2d9f9734c.jpeg', '$2y$10$yUoz/29ENe0mMVKUV2Bx/ey3PTd0YcjDL1YQyq4z3aaqIhxJdowtO', 4, 79, 1, 1, '2025', 1, '2025-11-17 08:43:51', NULL, 0, NULL),
(189, NULL, 'IT-55', 'SUJAL BHANUDAS WANODE', 'sujalwanode_7302it25@nit.edu.in', '9359045425', 'student_1763388882_691b2dd2881cf.jpeg', '$2y$10$KT0VjmvgzfUd2UM4ToZL..nHXAXJ0SSie8CNu8u4I3TM67OwyvDAy', 4, 79, 1, 1, '2025', 1, '2025-11-17 08:44:42', NULL, 0, NULL),
(190, NULL, 'IT-56', 'SUJAL GAUTAM DABRASE', 'sujaldabrase_6932it25@nit.edu.in', '9767738051', 'student_1763388935_691b2e078daeb.jpeg', '$2y$10$Kd7BJO5U7OKqSmaSv5cA5OtlBqw9pdBScKrZI0MFSLjS4E8q8NYqC', 4, 79, 1, 1, '2025', 1, '2025-11-17 08:45:35', NULL, 0, NULL),
(191, NULL, 'IT-57', 'TANVI SUNIL GHATOL', 'tanvighatol_7133it25@nit.edu.in', '9850794193', 'student_1763388981_691b2e35a8cfc.jpeg', '$2y$10$c6bvI8nBghabX8yAINrp7OstgISbuYeymi98NWX4Ts2cCv74mTg3e', 4, 79, 1, 1, '2025', 1, '2025-11-17 08:46:21', NULL, 0, NULL),
(192, NULL, 'IT-59', 'UTTARA RAVINDRA BHOYAR', 'uttarabhoyar_7249it25@nit.edu.in', '9370338423', 'student_1763389078_691b2e961124c.jpeg', '$2y$10$tu5M62uK1aZPjB7yA5/h2.aTLov/eUV4LqYcUbVDLJ0tjeWwPr5d.', 4, 79, 1, 1, '2025', 1, '2025-11-17 08:47:58', NULL, 0, NULL),
(193, NULL, 'IT-60', 'UTTARANSHI PANKAJ CHOUDHARY', 'uttaranshichoudhary_7085it25@nit.edu.in', '7276070340', 'student_1763389131_691b2ecb6d309.jpeg', '$2y$10$AeWvsQoqLdaMmz2kCZUZ/OBT1DdjbIfvZUC9Ds4OZQUxG45G/1QIG', 4, 79, 1, 1, '2025', 1, '2025-11-17 08:48:51', NULL, 0, NULL),
(194, NULL, 'IT-61', 'VANSHIKA SANJAY NAGPURE', 'vanshikanagpure_7243it25@nit.edu.in', '7620103872', 'student_1763389181_691b2efd9d812.jpeg', '$2y$10$sLuHFAo8Kj6ZtP5VPttFJuPLEoyCankjq6Uycavz2RoCMGCE.zSiO', 4, 79, 1, 1, '2025', 1, '2025-11-17 08:49:41', NULL, 0, NULL),
(195, NULL, 'IT-62', 'VEDANT VIJAYRAO GHARDE', 'vedantgharde_7143it25@nit.edu.in', '7276039131', 'student_1763389228_691b2f2c2b7a8.jpeg', '$2y$10$RPirpA6Ajk5oIgbKfuSf2eK8.Vv1wILm2QvobQnJe24nTVsN4Av2G', 4, 79, 1, 1, '2025', 1, '2025-11-17 08:50:28', NULL, 0, NULL),
(34, NULL, 'CE-01', 'ADITYA CHANDRASHEKHAR MESHRAM', 'adityameshram_7229ce25@nit.edu.in', '8408903740', 'student_34_1763719715.png', '$2y$10$kEBPK1NMIF2ZZdYpgU71xuGZMioS9A356e5D851BZXxP1tYez6vBm', 4, 53, 1, 1, '2025', 1, '2025-11-15 01:09:24', NULL, 0, NULL),
(35, NULL, 'CE-02', 'ANIKET RAJENDRA BANSOD', 'aniketbansod_7241ce25@nit.edu.in', '7709878368', 'student_35_1763719744.png', '$2y$10$Hv7fs6N4bWeDckyQJc1amOyyGW7k6wy4ojiOh6n.wzXwzIIB.GNhO', 4, 53, 1, 1, '2025', 1, '2025-11-15 01:10:45', NULL, 0, NULL),
(36, NULL, 'CE-03', 'ANKUSH RAJENDRA ADAY', 'ankushaday_6969ce25@nit.edu.in', '9699841693', 'student_36_1763719769.png', '$2y$10$b/Qv6DZZbnESf2qPnArhz.d3DZpT/x.tb1m9oJktfUD0xgBx1e7oO', 4, 53, 1, 1, '2025', 1, '2025-11-15 01:11:25', NULL, 0, NULL),
(37, NULL, 'CE-04', 'ANSH RAHUL GAJBHIYE', 'anshgajbhiye_7209ce25@nit.edu.in', '7773994195', 'student_37_1763719804.png', '$2y$10$ATmsKToMYC7UAgPINEKXtuMtFcZh39PGZKksnosluvkNDijBdBGMO', 4, 53, 1, 1, '2025', 1, '2025-11-15 01:12:27', NULL, 0, NULL),
(38, NULL, 'CE-05', 'ANUSHKA DHANRAJ RAKSHASKAR', 'anushkarakshaskar_7144ce25@nit.edu.in', '8975703331', 'student_38_1763719828.png', '$2y$10$KRxM4G06bNX/56BV6ACZwO0B4UFz3xbmraDLN.8YRAqSNo0CsTWzq', 4, 53, 1, 1, '2025', 1, '2025-11-15 01:13:16', NULL, 0, NULL),
(39, NULL, 'ME-01', 'ABDUL ZISHAN ABDUL JAVED SHEIKH', 'abdulzishan_6967me25@nit.edu.in', '8767644897', 'student_39_1763720270.png', '$2y$10$leLV0K/KiaiHhAqsHV/Hvu7BxmGFoPmvUCPQsDjTsrw3.f/C9ASmW', 4, 63, 1, 1, '2025', 1, '2025-11-15 01:16:36', NULL, 0, NULL),
(40, NULL, 'ME-02', 'ADESH PURUSHOTTAM GAURAV', 'adeshgurav_6989me25@nit.edu.in', '9284298135', 'student_40_1763399270.jpeg', '$2y$10$tcgL2nj52d4NYblmOFEE/OBqa4ZJ7FhkjAnJqyQUxKGPrD2N6JrFe', 4, 63, 1, 1, '2025', 1, '2025-11-15 01:18:44', NULL, 0, NULL),
(41, NULL, 'ME-03', 'ADITYA RAJENDRA WADBUDHE', 'adityawadbudhe_6962me25@nit.edu.in', '9226853072', 'student_41_1763720428.png', '$2y$10$tcgL2nj52d4NYblmOFEE/OBqa4ZJ7FhkjAnJqyQUxKGPrD2N6JrFe', 4, 63, 1, 1, '2025', 1, '2025-11-15 01:19:45', NULL, 0, NULL),
(42, NULL, 'ME-04', 'AKASH RAJKUMAR BINZADE', 'akashbinzade_7035me25@nit.edu.in', '7030642853', 'student_42_1763720487.png', '$2y$10$tcgL2nj52d4NYblmOFEE/OBqa4ZJ7FhkjAnJqyQUxKGPrD2N6JrFe', 4, 63, 1, 1, '2025', 1, '2025-11-15 01:20:39', NULL, 0, NULL),
(43, NULL, 'ME-05', 'AMAN DINESH SHINGARE', 'amanshingare_7019me25@nit.edu.in', '9356375511', 'student_43_1763720546.png', '$2y$10$tcgL2nj52d4NYblmOFEE/OBqa4ZJ7FhkjAnJqyQUxKGPrD2N6JrFe', 4, 63, 1, 1, '2025', 1, '2025-11-15 01:21:31', NULL, 0, NULL),
(322, NULL, 'ME-06', 'ANISHA SACHIN BHAGAT', 'anishabhagat_6988me25@nit.edu.in', '7218170799', 'student_1763706639_6920070f9fd52.png', '$2y$10$RzdUEah30APc5gT5uPCeIONd..klG81jg8Q7OjtZ81s.sCmxCy31G', 4, 63, 1, 1, '2025', 1, '2025-11-21 01:00:39', NULL, 0, NULL),
(323, NULL, 'ME-07', 'ANUSHKA GAJANAN ANTURKAR', 'anushkaanturkar_7066me25@nit.edu.in', '9119502249', 'student_1763706684_6920073ca7097.png', '$2y$10$.rPjVLrX/ygBb8eDd2zT8.Xo0p9AsLpHodruTnN/uelS45lcar6ka', 4, 63, 1, 1, '2025', 1, '2025-11-21 01:01:24', NULL, 0, NULL),
(324, NULL, 'ME-08', 'ARNAV VIJAY DANGE', 'arnavdange_7060me25@nit.edu.in', '9699825193', 'student_1763706733_6920076dac770.png', '$2y$10$6FCOYT.hzwidy1QP9tqvUelDMMzGmNShOh2uOGXPTpogHAju71KlK', 4, 63, 1, 1, '2025', 1, '2025-11-21 01:02:13', NULL, 0, NULL),
(325, NULL, 'ME-09', 'ARYAN BABANAND THOOL', 'aryanthool_7098me25@nit.edu.in', '8262912684', 'student_1763706791_692007a701b9d.png', '$2y$10$euS9AkLOG0KGsbqfZXM3G.SP5nPylhyUj546izSlMhXFtZx.CYHqe', 4, 63, 1, 1, '2025', 1, '2025-11-21 01:03:11', NULL, 0, NULL),
(326, NULL, 'ME-10', 'ATHARVA RAJESH MALEWAR', 'atharvamalewar_6979me25@nit.edu.in', '9404905866', 'student_1763706831_692007cfb5d56.png', '$2y$10$hTVZ4kMtDtiwyhAV8Ay5KOmtiHLGsuDJP5ul4xXydzreaFcXa86aW', 4, 63, 1, 1, '2025', 1, '2025-11-21 01:03:51', NULL, 0, NULL),
(327, NULL, 'ME-11', 'BHUPESH NITIN NAVGHARE', 'bhupeshnavghare_7283me25@nit.edu.in', '8623800153', 'student_1763706870_692007f6f2180.png', '$2y$10$A/bS5TP7YuEbLKt6EfvIyO.kMMzBF2hGW.epDHsxOLNGFZb5elLW2', 4, 63, 1, 1, '2025', 1, '2025-11-21 01:04:30', NULL, 0, NULL),
(328, NULL, 'ME-12', 'BHUSHAN UMESH THAWARE', 'bhushanthaware_7134me25@nit.edu.in', '8087183989', 'student_1763706967_69200857269c8.png', '$2y$10$J2Rq2WppQVue37q4FNGE9OiKWFxDZWVH0fV/zFycR9daPujvOgygW', 4, 63, 1, 1, '2025', 1, '2025-11-21 01:06:07', NULL, 0, NULL),
(329, NULL, 'ME-13', 'CHAITANYA RAJESH MANOHARKAR', 'chaitanyamanoharkar_7050me25@nit.edu.in', '9503993703', 'student_1763707006_6920087e393ab.png', '$2y$10$ZsqdsQh6cSenBDY6Cv5/ZulnhkFarSC8TPWHU.LiGCsjjWox8DFzS', 4, 63, 1, 1, '2025', 1, '2025-11-21 01:06:46', NULL, 0, NULL),
(330, NULL, 'ME-14', 'CHETANA MADAN PANDE', 'chetanapande_7239me25@nit.edu.in', '9370771305', 'student_1763707062_692008b677300.png', '$2y$10$LXub7pQbu5mxFPY4BLF1y.74YyvicNImX04eyb35nQwO1yPnxJLDy', 4, 63, 1, 1, '2025', 1, '2025-11-21 01:07:42', NULL, 0, NULL),
(331, NULL, 'ME-15', 'DEEPAK RAJKUMAR DOSHIYA', 'deepakdoshiya_6986me25@nit.edu.in', '9730987038', 'student_1763707119_692008ef5ae32.png', '$2y$10$DvDUXzRa5PFhkJ7a/LNECO.yhH9.rvwW5mk0qUQQaPR2asO3uCv3K', 4, 63, 1, 1, '2025', 1, '2025-11-21 01:08:39', NULL, 0, NULL),
(332, NULL, 'ME-16', 'DEVESH NAVIN GIRI', 'deveshgiri_7222me25@nit.edu.in', '9067643290', 'student_1763707199_6920093fa30f5.png', '$2y$10$Tjp9OksYkExFai6Vl5Ptr.MSOCn9kRfadn/hieMotaCspU5cL1yXy', 4, 63, 1, 1, '2025', 1, '2025-11-21 01:09:59', NULL, 0, NULL),
(335, NULL, 'ME-18', 'DEVYANI DIGAMBAR KOLHE', 'devyanikolhe_7234me25@nit.edu.in', '9322103153', 'student_1763707475_69200a53a4bd0.png', '$2y$10$NYjZm70fLMPJuPCRg8CVsOVge80x8bzswBwCp5Sx2F84pBw725XQy', 4, 63, 1, 1, '2025', 1, '2025-11-21 01:14:35', NULL, 0, NULL),
(336, NULL, 'ME-19', 'DHANANJAY GAJANAN THAKRE', 'dhananjaythakre_7010me25@nit.edu.in', '8999269460', 'student_1763707522_69200a826bdc9.png', '$2y$10$ttl1fwMbucX0oP9jXU3oduZZz5x1wyua/2CNyfKT/DUgIXZ380LlS', 4, 63, 1, 1, '2025', 1, '2025-11-21 01:15:22', NULL, 0, NULL),
(337, NULL, 'ME-20', 'DIKSHA MORESHWAR KAWALE', 'dikshakawale_7003me25@nit.edu.in', '9699062284', 'student_1763707585_69200ac1869c6.png', '$2y$10$M1..Ca9O/Aem9epTLj4F1uxMkxx.dWu/eajxgrDXU0gwvw0dsB00C', 4, 63, 1, 1, '2025', 1, '2025-11-21 01:16:25', NULL, 0, NULL),
(338, NULL, 'ME-21', 'DIVYANSH PURUSHOTTAM ALAM', 'divyanshalam_7044me25@nit.edu.in', '9270589356', 'student_1763707670_69200b16c9a1a.png', '$2y$10$PsOZas6OgCL80J/WFoV7eO87B8e3zqfY69iLARf7QXSh2AgMk2Zpa', 4, 63, 1, 1, '2025', 1, '2025-11-21 01:17:50', NULL, 0, NULL),
(339, NULL, 'ME-22', 'GAURAV RAKESH ATRAHE', 'gauravatrahe_7206me25@nit.edu.in', '9370175920', 'student_1763707713_69200b413bb2c.png', '$2y$10$9xfyfKjtNQV5T3VKwlABJe/ZPJMZ6MsLQrpbeB45OMmhBHj/f.tg.', 4, 63, 1, 1, '2025', 1, '2025-11-21 01:18:33', NULL, 0, NULL),
(340, NULL, 'ME-23', 'HARSH VIJAY JAMOTKAR', 'harshjamotkar_7290me25@nit.edu.in', '7218898873', 'student_1763707767_69200b77792f7.png', '$2y$10$V2Ollet.3XgGXUtnNSCMYe6Zy8Js2TNCWYLDkvkpjiKvKEIzcXJ8i', 4, 63, 1, 1, '2025', 1, '2025-11-21 01:19:27', NULL, 0, NULL),
(341, NULL, 'ME-24', 'KAIF MEHMUD SHEIKH', 'kaifsheikh_7012me25@nit.edu.in', '9156233742', 'student_1763706321_69200bad02c69.png', '$2y$10$4QV/IaLKIcNOv0YNoM.mQeU8bNTCYuU1J069fKBgQh8GRksoVGqAC', 4, 63, 1, 1, '2025', 1, '2025-11-21 01:20:21', NULL, 0, NULL),
(342, NULL, 'ME-25', 'KARTIK NARAYAN POINKAR', 'kartikpoinkar_7129me25@nit.edu.in', '9307522884', 'student_1763706368_69200bdcf2fdf.png', '$2y$10$DbUOcK.CjcOqJnrwCMi.1uhDekgFImQPe/z9yEgxGfPV4RbN5Qs7e', 4, 63, 1, 1, '2025', 1, '2025-11-21 01:21:08', NULL, 0, NULL),
(343, NULL, 'ME-26', 'KASHISH DIVAKAR OKTE', 'kashishokte_7015me25@nit.edu.in', '9906373812', 'student_1763707915_69200c0b54263.png', '$2y$10$zFlWaWkVfKDVbAfFkJDz2.HDq1WSzsZ2QXwhTIdfm0VMgxq5NIvSO', 4, 63, 1, 1, '2025', 1, '2025-11-21 01:21:55', NULL, 0, NULL),
(344, NULL, 'ME-27', 'KUNAL RAMSWARTH PRASAD', 'kunalprasad_7064me25@nit.edu.in', '8638401630', 'student_1763707959_69200c3775ba2.png', '$2y$10$AIGKRkJVqb4l8zJuj0hezev9oVN4D5o6.t3da4qx7o1SUTSKC3Nka', 4, 63, 1, 1, '2025', 1, '2025-11-21 01:22:39', NULL, 0, NULL),
(345, NULL, 'ME-28', 'MALLIKA BHOJRAJ THAKUR', 'mallikathakur_6993me25@nit.edu.in', '9579629958', 'student_1763708016_69200c7002a94.png', '$2y$10$xC.5YJOWBYLcGwxHz06Q.O8cU6HQKn/8VYH6yRgkgxSRO6w4UdRO6', 4, 63, 1, 1, '2025', 1, '2025-11-21 01:23:36', NULL, 0, NULL),
(346, NULL, 'ME-29', 'MANSI KAILAS KSHIRSAGAR', 'mansikshirsagar_7089me25@nit.edu.in', '8600902684', 'student_1763708058_69200c9a4c3d3.png', '$2y$10$Q92YNU36IjCr1.vpx.pNPOQT92vnR3SX4exrpm36ftX74PJkAW9Wu', 4, 63, 1, 1, '2025', 1, '2025-11-21 01:24:18', NULL, 0, NULL),
(347, NULL, 'ME-30', 'MAYUR PALIK BISEN', 'mayurbisen_7281me25@nit.edu.in', '9307397039', 'student_1763708130_69200ce2b633e.png', '$2y$10$kJF3dFz2KMyLzcnc9H3GLewFLoln2fhPgGBqnHqcE0bQ3593iibWG', 4, 63, 1, 1, '2025', 1, '2025-11-21 01:25:30', NULL, 0, NULL),
(348, NULL, 'ME-31', 'NIDHI BHUPESH PETHE', 'nidhipethe_7233me25@nit.edu.in', '7083965584', 'student_1763708191_69200d1fddf99.png', '$2y$10$UuE0ClpYuFgeIeFNh.8/ceds1Fjt2IpQZMhiS93FYArUGte5Bf8nC', 4, 63, 1, 1, '2025', 1, '2025-11-21 01:26:31', NULL, 0, NULL),
(349, NULL, 'ME-32', 'NIKITA NAMDEV RATHOD', 'nikitarathod_7031me25@nit.edu.in', '8793894187', 'student_1763708233_69200d493a0f7.png', '$2y$10$uJGa6WxyIQvi9MbzIc.XDufyiMF8Bh5igsG3uWRf.ouBC5PoQ4c8i', 4, 63, 1, 1, '2025', 1, '2025-11-21 01:27:13', NULL, 0, NULL),
(350, NULL, 'ME-33', 'OM KUNDAN DESHMUKH', 'omdeshmukh_7096me25@nit.edu.in', '9579581022', 'student_1763708277_69200d75b4f24.png', '$2y$10$JUOGGov2SuhD2wV05fzxJuOWZqfyotTc3bLoNb253Uu.xgxSsCPvO', 4, 63, 1, 1, '2025', 1, '2025-11-21 01:27:57', NULL, 0, NULL),
(351, NULL, 'ME-34', 'PAYAL GIRIDHAR MOHOD', 'payalmohod_7047me25@nit.edu.in', '9579776133', 'student_1763708329_69200da906542.png', '$2y$10$p1drybF2.EC2wQssmMa..u8.IqkNJxSFIaa0ECefZVJzS584O7PHq', 4, 63, 1, 1, '2025', 1, '2025-11-21 01:28:49', NULL, 0, NULL),
(352, NULL, 'ME-35', 'PAYAL JAGDISH TATTE', 'payaltatte_7228me25@nit.edu.in', '8638604026', 'student_1763708377_69200dd90aff1.png', '$2y$10$rjh8ZLuAMWafObcM7zRl3u98Z2hDeFg7RcMbxA0N331iAPz3eQiUq', 4, 63, 1, 1, '2025', 1, '2025-11-21 01:29:37', NULL, 0, NULL),
(353, NULL, 'ME-36', 'PIYUSH MUKESH ATHAWALE', 'piyushathawale_7285me25@nit.edu.in', '9371655365', 'student_1763708422_69200e06799c9.png', '$2y$10$pBRXqVsSQYIpPbQaB6Seo.PyjjRtUJsRQKKypaNbzYli/L/43fYjO', 4, 63, 1, 1, '2025', 1, '2025-11-21 01:30:22', NULL, 0, NULL),
(354, NULL, 'ME-37', 'PIYUSH NANDKISHOR DHANDE', 'piyushdhande_7048me25@nit.edu.in', '9226305122', 'student_1763708477_69200e3d083d2.png', '$2y$10$XKCC1dLtpTCZk1t7MbajeekUDAYx6uA5bVPGRerFg9DKWl36Vr0ou', 4, 63, 1, 1, '2025', 1, '2025-11-21 01:31:17', NULL, 0, NULL),
(355, NULL, 'ME-38', 'PRAJWAL PRABHAKAR JAMBHULE', 'prajwaljambhule_7124me25@nit.edu.in', '8767155420', 'student_1763708516_69200e64eeddb.png', '$2y$10$ZRJyZKVtEQRB2PsDfjw4lO86k/FI9IAB1qsm/9welV63ctSj/P/S6', 4, 63, 1, 1, '2025', 1, '2025-11-21 01:31:56', NULL, 0, NULL),
(356, NULL, 'ME-39', 'PRATHMESH GAJANAN DAGWAR', 'prathmeshdagwar_7128me25@nit.edu.in', '6321852659', 'student_1763708561_69200e913faa9.png', '$2y$10$zl6Z.wIbJdoetHkWOsR4iu0vxLPHyPzmSF9KgQnSD5FKsfzUnsVwC', 4, 63, 1, 1, '2025', 1, '2025-11-21 01:32:41', NULL, 0, NULL),
(357, NULL, 'ME-40', 'PRITESH PRAMOD DIGAL', 'priteshdigal_7005me25@nit.edu.in', '9309397375', 'student_1763708601_69200eb92c28b.png', '$2y$10$7i34oLLkwE6mtsJQ02bojem6n4orsA7dps7C0sadZ7cIyQvHuSwCm', 4, 63, 1, 1, '2025', 1, '2025-11-21 01:33:21', NULL, 0, NULL),
(358, NULL, 'ME-17', 'DEVYANI CHHAGAN GOMKAR', 'devyanigomkar_7097me25@nit.edu.in', '7666429873', 'student_1763708770_69200f6244758.png', '$2y$10$IH6AM352YD8loh/iwWaBae/DMH02x91t.X9UtLmbx3s7.fkmMLtJW', 4, 63, 1, 1, '2025', 1, '2025-11-21 01:36:10', NULL, 0, NULL),
(359, NULL, 'ME-41', 'RAGHAVENDRA VIJAY WAGHE', 'raghavendrawaghe_7001me25@nit.edu.in', '7796106631', 'student_1763708827_69200f9be36ec.png', '$2y$10$yXW.K6RyaAVjVyOce9pxJOdJ8p8n2eSX9uViEIfdr8tGD66LHVd0K', 4, 63, 1, 1, '2025', 1, '2025-11-21 01:37:07', NULL, 0, NULL),
(360, NULL, 'ME-42', 'RAM TULSHIDAS PATIL', 'rampatil_7104me25@nit.edu.in', '9607481639', 'student_1763708865_69200fc16daa0.png', '$2y$10$lnY9T1kJ9t4ivMETl4j.MOdb7pcjXRDe6iEAcO.AvTHdwTGH2nCA2', 4, 63, 1, 1, '2025', 1, '2025-11-21 01:37:45', NULL, 0, NULL),
(361, NULL, 'ME-43', 'RITIK RAVINDRA LADHAIKAR', 'ritikladhaikar_6991me25@nit.edu.in', '7743915898', 'student_1763708906_69200fea00680.png', '$2y$10$tCGBH3qAy8wMPi8qoheQKeVem2qoqaz7qwDDBAQuCVg3E6MW9lTDK', 4, 63, 1, 1, '2025', 1, '2025-11-21 01:38:26', NULL, 0, NULL),
(362, NULL, 'ME-44', 'ROHAN SHRIKRUSHNA SHERKI', 'rohansherki_7021me25@nit.edu.in', '7666720775', 'student_1763708960_6920102024094.png', '$2y$10$jobL5wLg37PIzxOkt9TEtur8HOFNd7Q1sjqXzeb2kWUcfjTap.aNi', 4, 63, 1, 1, '2025', 1, '2025-11-21 01:39:20', NULL, 0, NULL),
(363, NULL, 'ME-45', 'ROHIT DEEPAK RAMAVAT', 'rohitramavat_7116me25@nit.edu.in', '9322124539', 'student_1763709007_6920104f5c17c.png', '$2y$10$aWxRzcSYg5izlNj7hCOD5.mP1kNiqYoK.Sv3FJfax67Zh07w2wyOm', 4, 63, 1, 1, '2025', 1, '2025-11-21 01:40:07', NULL, 0, NULL),
(364, NULL, 'ME-46', 'SAHIL DHANRAJ NINAWE', 'sahilninawe_7162me25@nit.edu.in', '9730243241', 'student_1763709050_6920107add5b9.png', '$2y$10$TRhV5R011GGNVdsuGbGqzejjUkzEJJnNfVk5BZ/HN0/1HEtqCSa.W', 4, 63, 1, 1, '2025', 1, '2025-11-21 01:40:50', NULL, 0, NULL),
(365, NULL, 'ME-47', 'SAYALI DAMODHAR HEDAOO', 'sayalihedaoo_7269me25@nit.edu.in', '8087229477', 'student_1763709094_692010a6bbbe6.png', '$2y$10$6gg1dRMx2dXTyRMUInLBCOxHawb.oaP/I33kNpOuhkLe51qg7UvH6', 4, 63, 1, 1, '2025', 1, '2025-11-21 01:41:34', NULL, 0, NULL),
(366, NULL, 'ME-48', 'SHAURYA RAJESH RAMPURKAR', 'shauryarampurkar_7026me25@nit.edu.in', '9272129198', 'student_1763709137_692010d1cfcef.png', '$2y$10$2ULs75AnCTP7y7e58mkld.6gRWQxhwBbU83pY5z3JsNRoeCCW9xuy', 4, 63, 1, 1, '2025', 1, '2025-11-21 01:42:17', NULL, 0, NULL),
(367, NULL, 'ME-49', 'SHIVAM RAVI DAMODHARE', 'shivamdamodhare_7065me25@nit.edu.in', '9226393572', 'student_1763709163_692010fae9ca9.png', '$2y$10$BuBbSmdL5xp80tanw7JcKepp3YHR/.2OyEw7Gyd0ktNyTErHEBjFq', 4, 63, 1, 1, '2025', 1, '2025-11-21 01:42:58', NULL, 0, NULL),
(368, NULL, 'ME-50', 'SHRAVNI SUNILRAO DIDSHE', 'shravnididshe_7268me25@nit.edu.in', '9309728764', 'student_1763709227_6920112b3d91b.png', '$2y$10$4izSw/WsDALr.E6jYxhcLuHApWJt3X16cUq4ywuSr43q42A/swO4G', 4, 63, 1, 1, '2025', 1, '2025-11-21 01:43:47', NULL, 0, NULL),
(369, NULL, 'ME-51', 'SIMON RAJESH BINZADE', 'simonbinzade_6963me25@nit.edu.in', '9226076486', 'student_1763709274_6920115a769cf.png', '$2y$10$MalLqQ.07ddCGmodiEkmJOScj1VF0cs6A/zGt8VLDxvUE.pW380ge', 4, 63, 1, 1, '2025', 1, '2025-11-21 01:44:34', NULL, 0, NULL),
(370, NULL, 'ME-52', 'SNEHA SUDHAKAR BAWANE', 'snehabawane_7115me25@nit.edu.in', '9588457235', 'student_1763709315_69201183bcc2b.png', '$2y$10$Bru4aarl.rh2PDMCuKg7GeC6VwoXHDGv8r5XpCQpOQN868xQ3tuaa', 4, 63, 1, 1, '2025', 1, '2025-11-21 01:45:15', NULL, 0, NULL),
(371, NULL, 'ME-53', 'SUMIT NANDU BASHINE', 'sumitbashine_7070me25@nit.edu.in', '9604748776', 'student_1763709361_692011b15b5fb.png', '$2y$10$OQOtFGxUj1lq5KziPk382Of3C0JxvEg3p1SSa.vdual6Gqpdw5d6W', 4, 63, 1, 1, '2025', 1, '2025-11-21 01:46:01', NULL, 0, NULL),
(372, NULL, 'ME-54', 'TANISHQ DHANANJAY SHENDE', 'tanishqshende_7135me25@nit.edu.in', '8459304196', 'student_1763709401_692011d982f63.png', '$2y$10$Z.GCP1OBGVp6fSA9hz3YUeGQ6iegozAbokVDpT1M1gR/TJGXpLfWy', 4, 63, 1, 1, '2025', 1, '2025-11-21 01:46:41', NULL, 0, NULL),
(373, NULL, 'ME-55', 'TANMAY DNYANESHWAR MOTGHARE', 'tanmaymotghare_7014me25@nit.edu.in', '8080067214', 'student_1763709438_692011fe023c5.png', '$2y$10$3IqHgAnf9Ae8tkKwmAduEOICLqPXjakHB2B686zFu0gWhm09WwZx.', 4, 63, 1, 1, '2025', 1, '2025-11-21 01:47:18', NULL, 0, NULL),
(374, NULL, 'ME-56', 'TEJAS SANTOSH GAJBHAR', 'tejasgajbhar_7018me25@nit.edu.in', '8208381504', 'student_1763709482_6920122a0f757.png', '$2y$10$BUitrrWCVeGMuySvXFNV0.Qz4nWKhLxexUtDIYD/MgcXOgrQW4Th6', 4, 63, 1, 1, '2025', 1, '2025-11-21 01:48:02', NULL, 0, NULL),
(375, NULL, 'ME-57', 'VANSH VIJAY NAGOSE', 'vanshnagose_7025me25@nit.edu.in', '9822656310', 'student_1763709520_692012507b703.png', '$2y$10$tfQwPH3hcsMlxJMLGfdgA.l/p2Lhg1iyzAFQ1uxefjgJCsyR19h1C', 4, 63, 1, 1, '2025', 1, '2025-11-21 01:48:40', NULL, 0, NULL),
(376, NULL, 'ME-58', 'VANSH VIPINKUMAR NIMR', 'vanshnimr_6977me25@nit.edu.in', '9636397725', 'student_1763709559_6920127726d0b.png', '$2y$10$ppzjhVJXUZxI2z4a63JrLewDe9roI68zu/NYpt12KdKB2FrYFqeeS', 4, 63, 1, 1, '2025', 1, '2025-11-21 01:49:19', NULL, 0, NULL),
(377, NULL, 'ME-59', 'VEDANT NITIN LADSE', 'vedantladse_7282me25@nit.edu.in', '7741881420', 'student_1763709624_692012b8373ed.png', '$2y$10$nvD4CAYu84sVkpUUAqUb8OQCQ4HCdJKWQgolWT4hDUy3Kp0Zb26FG', 4, 63, 1, 1, '2025', 1, '2025-11-21 01:50:24', NULL, 0, NULL),
(378, NULL, 'ME-60', 'VEDANT RAVINDRA BIGHANE', 'vedantbighane_7073me25@nit.edu.in', '8483995495', 'student_1763709672_692012e8eec29.png', '$2y$10$8uykysJZFFH4Ji1UY.wCQuW.G6G.i6Lf7qklprGxBcYans13AwDk6', 4, 63, 1, 1, '2025', 1, '2025-11-21 01:51:12', NULL, 0, NULL),
(379, NULL, 'ME-61', 'VEDANTI DEEPAK RANGARI', 'vedantirangari_7059me25@nit.edu.in', '7276312104', 'student_1763709718_6920131677749.png', '$2y$10$i4coFykbN0DmNVA1o6rBjul.r8RYlENPBjShJMp35C/ZIlje7mo0K', 4, 63, 1, 1, '2025', 1, '2025-11-21 01:51:58', NULL, 0, NULL),
(380, NULL, 'ME-62', 'VINAY DHARMARAJ SAWWALAKHE', 'vinaysawwalakhe_7011me25@nit.edu.in', '9284182670', 'student_1763709762_692013420e86f.png', '$2y$10$NGaTuxVDfrP9VA9AIHNKnOludpGfHZ21xWuP5eV91rKh/hrJLtgIW', 4, 63, 1, 1, '2025', 1, '2025-11-21 01:52:42', NULL, 0, NULL),
(381, NULL, 'ME-63', 'YASH DEVANAND BINZADE', 'yashbinzade_6960me25@nit.edu.in', '8007573521', 'student_1763709809_6920137163234.png', '$2y$10$TxnJXklZvTnBkpYzRppoBOe2y23k6W4WPm4I56D7hMcUEd1v7xI9m', 4, 63, 1, 1, '2025', 1, '2025-11-21 01:53:29', NULL, 0, NULL),
(382, NULL, 'ME-64', 'YASH DILIP NARNAWARE', 'yashnarnaware_7058me25@nit.edu.in', '8432632107', 'student_1763709849_69201399de301.png', '$2y$10$u6REyNFaskvoRn1R/0D3q.kCZpRsY9bLKQKFAQ6xSmRNSA5AOoL0a', 4, 63, 1, 1, '2025', 1, '2025-11-21 01:54:09', NULL, 0, NULL),
(196, NULL, 'CE-06', 'ARIYA HARIDAS SOMKUWAR', 'ariyasomkuwar_6966ce25@nit.edu.in', '8830864633', 'student_1763689821_691fc55de313e.png', '$2y$10$S1hWs1tJ0kKJmqI1iHNn9.8FPhFxcSfiDZr3ZM/nRWPtY7T2IklJi', 4, 53, 1, 1, '2025', 1, '2025-11-20 20:20:21', NULL, 0, NULL),
(197, NULL, 'CE-07', 'ARYAN PRAKASH RAMTEKE', 'aryanramteke_7013ce25@nit.edu.in', '7397808140', 'student_197_1763689942.png', '$2y$10$YcsMPcuPd58e1g2/aOBYfewbJMeHas7z2UEqZfP/VYu5YTXfkMhmy', 4, 53, 1, 1, '2025', 1, '2025-11-20 20:21:28', NULL, 0, NULL),
(203, NULL, 'CE-08', 'ARYAN PUSARAM WANJARI', 'aryanwanjari_6987ce25@nit.edu.in', '9579309429', 'student_1763699679_691febdf23870.png', '$2y$10$CGegOlGbvNigKYnEf.Xhh./LXynSULjE448A.x8.6bnflSgqsQeN6', 4, 53, 1, 1, '2025', 1, '2025-11-20 23:04:39', NULL, 0, NULL),
(207, NULL, 'CE-09', 'CHAITALI GOURISHANKAR DURBUDE', 'chaitalidurbude_7057ce25@nit.edu.in', '8153989912', 'student_1763699941_691fece50f000.png', '$2y$10$Pw.K1HPCFnOWrw9dkaM9HOKuOYGEFWxruovmwdhqldTSYQf7Oc9JW', 4, 53, 1, 1, '2025', 1, '2025-11-20 23:09:01', NULL, 0, NULL),
(208, NULL, 'CE-10', 'CHAITANYA SUBHASH BHAJBHUJE', 'chaitanyabhajbhuje_7288ce25@nit.edu.in', '7767854471', 'student_1763699978_691fed0ad1b57.png', '$2y$10$PkHTePvcrCzdhKP4RF04COV6lcMCSZ11nID48tkqUi9Jzj58hRdMa', 4, 53, 1, 1, '2025', 1, '2025-11-20 23:09:38', NULL, 0, NULL),
(209, NULL, 'CE-11', 'DEEP RAVINDRA WAKADE', 'deepwakade_7147ce25@nit.edu.in', '9975289996', 'student_209_1763719959.png', '$2y$10$1hoNX94JzO.tdSQFg4cKKO2cqpk6WDgAhupezpHoN8OLiFlrrt6ge', 4, 53, 1, 1, '2025', 1, '2025-11-20 23:10:21', NULL, 0, NULL),
(210, NULL, 'CE-12', 'DHANASHRI VISHVESHWAR SOMKUWAR', 'dhanashrisomkuwar_6974ce25@nit.edu.in', '9673927505', 'student_1763700062_691fed5e002d1.png', '$2y$10$tCkO7EUFlHSf6dn7m4hgWOyOIIGPXgTNY4sLqGGXq208lJZ7hU/8S', 4, 53, 1, 1, '2025', 1, '2025-11-20 23:11:02', NULL, 0, NULL),
(211, NULL, 'CE-13', 'DISHANT RAMESH PATIL', 'dishantpatil_7083ce25@nit.edu.in', '9890285063', 'student_1763700104_691fed88b2d82.png', '$2y$10$klTqGOV5dFgjxuuUSUjZ/e/sJAWvg1aWat65Yfz3KRX5KVYYvmzsG', 4, 53, 1, 1, '2025', 1, '2025-11-20 23:11:44', NULL, 0, NULL),
(212, NULL, 'CE-14', 'DIYA RAKESH POREDDIWAR', 'diyaporeddiwar_7216ce25@nit.edu.in', '8010554690', 'student_1763700147_691fedb357e4c.png', '$2y$10$nHmuizmKV4Hnao0v51lOJer4T3mE/lvQLrhCL0eOZ1P04qMgeNuBq', 4, 53, 1, 1, '2025', 1, '2025-11-20 23:12:27', NULL, 0, NULL),
(213, NULL, 'CE-15', 'DIYA TEJKUMAR PUNVATKAR', 'diyapunvatkar_7272ce25@nit.edu.in', '8432402050', 'student_1763700184_691fedd8ef488.png', '$2y$10$xV.kBKqTpRvqfyP7Q2NLkecBvA1omDV66nW8jbgbP1xGoceRQkE4K', 4, 53, 1, 1, '2025', 1, '2025-11-20 23:13:04', NULL, 0, NULL),
(214, NULL, 'CE-16', 'HITESH PRASHANT WAGHE', 'hiteshwaghe_6997ce25@nit.edu.in', '8767734289', 'student_1763700239_691fee0f7db29.png', '$2y$10$HL30fxLmWxzlBYRP4fXwhO/Ksu1sDO16ZkMyqkb9IbWOtMumkQPfi', 4, 53, 1, 1, '2025', 1, '2025-11-20 23:13:59', NULL, 0, NULL),
(215, NULL, 'CE-17', 'KANAK AMOL GAIDHANI', 'kanakgaidhani_7008ce25@nit.edu.in', '9834528465', 'student_1763700289_691fee41f2c0d.png', '$2y$10$IDzql/AyRBuyB6ytSUdgEOOPobnfCkCna0y/BL4ChKTpVz0VJqrT.', 4, 53, 1, 1, '2025', 1, '2025-11-20 23:14:53', NULL, 0, NULL),
(216, NULL, 'CE-18', 'KRUTIKA PRAVIN KELWADKAR', 'krutikakelwadkar_7220ce25@nit.edu.in', '9356766727', 'student_1763700350_691fee7e630b0.png', '$2y$10$VpSldaQ1jer.2UF0WIckzeS53lyFDdyOXrzYRRxAD9NKDhGLrCLB.', 4, 53, 1, 1, '2025', 1, '2025-11-20 23:15:50', NULL, 0, NULL),
(217, NULL, 'CE-19', 'MADHAVI RAVINDRA BAWANTHADE', 'madhavibawanthade_7257ce25@nit.edu.in', '9922637581', 'student_1763700388_691feea5398da.png', '$2y$10$Du6ilFq1PFQh8Pd/Hm9CLe.KLAi0G8AOY3ZvfoirfjxgOseRtrDyC', 4, 53, 1, 1, '2025', 1, '2025-11-20 23:16:28', NULL, 0, NULL),
(218, NULL, 'CE-20', 'MAHI SOMESHWAR ILAMKAR', 'mahiilamkar_6985ce25@nit.edu.in', '9850802290', 'student_1763700425_691feec90ff39.png', '$2y$10$VxgUnwKIQpvLP7yqu0LJJOHuuM9stg9egxA.Y4lSr2H.fhoq0kr7.', 4, 53, 1, 1, '2025', 1, '2025-11-20 23:17:05', NULL, 0, NULL),
(219, NULL, 'CE-21', 'MANASVI AMAR DHANREL', 'manasvidhanrel_7061ce25@nit.edu.in', '9766162139', 'student_1763700464_691feef058707.png', '$2y$10$urp4BrTA7cp5.BFrV4qnc.YnMyjnpQlKw7Nfd9iadsnL0atwvfytO', 4, 53, 1, 1, '2025', 1, '2025-11-20 23:17:44', NULL, 0, NULL),
(220, NULL, 'CE-22', 'MAYANK KAILAS KHORGADE', 'mayankkhorgade_7053ce25@nit.edu.in', '9766643002', 'student_1763700503_691fef17217de.png', '$2y$10$1FgDnLE1NiTwbkxf.Nwac.i0V1cKGIHMyWtlWIk.XrfBnsTCtncCS', 4, 53, 1, 1, '2025', 1, '2025-11-20 23:18:23', NULL, 0, NULL),
(221, NULL, 'CE-23', 'MEET LAXMINARAYAN MANE', 'meetmane_7204ce25@nit.edu.in', '8010844253', 'student_1763700547_691fef43b5cbf.png', '$2y$10$hDZlqtjlHSkIFmjO9vgRFeCFXoSWc0igBcIjhPAT.GpXdwgTC5rIe', 4, 53, 1, 1, '2025', 1, '2025-11-20 23:23:07', NULL, 0, NULL),
(222, NULL, 'CE-24', 'MOHD TAHA MOHD NADEEMUDDIN KHATIB', 'mohdtaha_7006ce25@nit.edu.in', '8275936533', 'student_1763700588_691fef6c168b4.png', '$2y$10$7ViIUjpDwSA9f63BEWtbDeU46nrlYprgZiHMIfFepRQ6t/2LkmQk.', 4, 53, 1, 1, '2025', 1, '2025-11-20 23:23:48', NULL, 0, NULL),
(223, NULL, 'CE-25', 'MUSKAN PRAVIN MESHRAM', 'muskanmeshram_7043ce25@nit.edu.in', '9764885585', 'student_1763700641_691fefa16818e.png', '$2y$10$jzKmAEZgDVjy2PMLKK3AvOMT7DjWAjFxdd51oDbdDjyAo/yq3Rv4C', 4, 53, 1, 1, '2025', 1, '2025-11-20 23:20:41', NULL, 0, NULL),
(224, NULL, 'CE-27', 'NIKHIL PRAMOD BRAMHANE', 'nikhilbramhane_7024ce25@nit.edu.in', '8010026623', 'student_1763700692_691fefd46bf2e.png', '$2y$10$UopqiERr2qitZrlSundQAuUnaS1rucKIkXX4oDT1EZ03HC0aNmCjK', 4, 53, 1, 1, '2025', 1, '2025-11-20 23:21:32', NULL, 0, NULL),
(225, NULL, 'CE-28', 'NISHA DEVENDRA DHARMIK', 'nishadharmik_6959ce25@nit.edu.in', '9373197042', 'student_1763700742_691ff00634a35.png', '$2y$10$z6M0tcPvMucMhYpjgHDnqe0qfSrlxwZ.zUSwaiO5T3ylQ0S5YkfgC', 4, 53, 1, 1, '2025', 1, '2025-11-20 23:22:22', NULL, 0, NULL),
(226, NULL, 'CE-29', 'PAYAL ANIL LENDE', 'payallende_7309ce25@nit.edu.in', '8007685773', 'student_1763700784_691ff030436c8.png', '$2y$10$9YmGBC9iwhcLi3tG85NSuuZkRIKlCEA.Yt6DgOo0FTJFDn26RfoEi', 4, 53, 1, 1, '2025', 1, '2025-11-20 23:23:04', NULL, 0, NULL),
(227, NULL, 'CE-30', 'PRACHI RAJESH PATIL', 'prachipatil_7105ce25@nit.edu.in', '9325388673', 'student_1763700890_691ff09ae67ac.png', '$2y$10$PFYj5.K.BSePuZR.whf8LOnA1Dsu70EtL4mPmkwdnEkfKcwGUvxua', 4, 53, 1, 1, '2025', 1, '2025-11-20 23:24:50', NULL, 0, NULL),
(228, NULL, 'CE-31', 'PRAJWAL ISHWAR BORKAR', 'prajwalborkar_7121ce25@nit.edu.in', '7057394146', 'student_1763700930_691ff0c267736.png', '$2y$10$Jc1MCUdsx6AgTUzAOgMbeOT9BsvQ61ZGS3bIvWKEXH6QWyUSgCz0G', 4, 53, 1, 1, '2025', 1, '2025-11-20 23:25:30', NULL, 0, NULL),
(229, NULL, 'CE-32', 'RADHESHAM RAJENDRA RATHOD', 'radheshamrathod_6983ce25@nit.edu.in', '8329095135', 'student_1763700985_691ff0f9539fc.png', '$2y$10$u2MY4XhXzLH3mBLSjfg1yO.Jdu7XZWDAEMn3XMSfi0FZXJJfhwvje', 4, 53, 1, 1, '2025', 1, '2025-11-20 23:26:25', NULL, 0, NULL),
(230, NULL, 'CE-33', 'RAGHAV MOTIDAS NAGPURE', 'raghavnagpure_7109ce25@nit.edu.in', '9403005389', 'student_1763701038_691ff12e88b2c.png', '$2y$10$nspYN8x9SqI80sxwr70cGO4pmUYZTv8OIZjAkfsGtUFnagXOb81L6', 4, 53, 1, 1, '2025', 1, '2025-11-20 23:27:18', NULL, 0, NULL),
(231, NULL, 'CE-34', 'RESHMI PRAMOD NIKHADE', 'reshminikhade_6940ce25@nit.edu.in', '9172101958', 'student_1763701085_691ff15d5f9f6.png', '$2y$10$q3.cKkcIJbqwOg.ALfkPe.UVHjbwy7e9hlMNUSSYFJEwSQOmf7SIS', 4, 53, 1, 1, '2025', 1, '2025-11-20 23:28:05', NULL, 0, NULL),
(232, NULL, 'CE-35', 'RUDRAKSH ROSHAN NANETKAR', 'rudrakshnanetkar_6935ce25@nit.edu.in', '9503333085', 'student_1763701135_691ff18f7bd4a.png', '$2y$10$DJRXqSs2sdJdjHuw2BrHEeCiztRBqH0wy4y93IIwrf0xfgri2Op4C', 4, 53, 1, 1, '2025', 1, '2025-11-20 23:28:55', NULL, 0, NULL),
(233, NULL, 'CE-36', 'SAHIL BALU PAWAR', 'sahilpawar_7113ce25@nit.edu.in', '7538747134', 'student_1763701173_691ff1b5edbf2.png', '$2y$10$SseS2WW81Pt6FRWD1PQTTOoyRPqOk83UgYbDplHuET1aJCEFYF17i', 4, 53, 1, 1, '2025', 1, '2025-11-20 23:29:33', NULL, 0, NULL),
(234, NULL, 'CE-37', 'SAKSHI SUNIL LAMBKANE', 'sakshilambkane_7086ce25@nit.edu.in', '9921381419', 'student_1763701238_691ff1f6b7131.png', '$2y$10$Md7VJhJqUCvWkLw/SsthEu.lt8KhkKJy5g5ix5Zl5OQTxDpwJ4Nea', 4, 53, 1, 1, '2025', 1, '2025-11-20 23:30:38', NULL, 0, NULL),
(235, NULL, 'CE-38', 'SALONI SUDESH GAJBHIYE', 'salonigajbhiye_7004ce25@nit.edu.in', '7666532561', 'student_1763701283_691ff2231eb6e.png', '$2y$10$q/TX3DWjiEpG/h3kFV5UMuETWx2F9UUX4jBv2OG5T3ylQ0S5YkfgC', 4, 53, 1, 1, '2025', 1, '2025-11-20 23:31:23', NULL, 0, NULL),
(236, NULL, 'CE-39', 'SANKET DARASING CHOUDHARI', 'sanketchoudhari_7298ce25@nit.edu.in', '8626009469', 'student_1763701325_691ff24d1e7ab.png', '$2y$10$eu7UzOXfl.FMunhTf.E.3.dsKcjvgHVUlWVnEaOvn2S2OcRIlK8k2', 4, 53, 1, 1, '2025', 1, '2025-11-20 23:32:05', NULL, 0, NULL),
(237, NULL, 'CE-40', 'SHITAL RAMPAT WARTHI', 'shitalwarthi_7023ce25@nit.edu.in', '9209188194', 'student_1763701363_691ff2737fe51.png', '$2y$10$7cwjYELwzS.yqCFwcStF/eAKcqYFdu9zci2Usw89luqnakBzLp/va', 4, 53, 1, 1, '2025', 1, '2025-11-20 23:32:43', NULL, 0, NULL),
(238, NULL, 'CE-41', 'SHREYA BANDUJI SONTAKKE', 'shreyasontakke_7175ce25@nit.edu.in', '9518305816', 'student_1763701406_691ff29ea9dcd.png', '$2y$10$ro4x6hXH/es9DDr8duqu2e4uryUxoMom.cZoBDhuG3WCNyoW1bGCO', 4, 53, 1, 1, '2025', 1, '2025-11-20 23:33:26', NULL, 0, NULL),
(239, NULL, 'CE-42', 'SHUBHAM VILAS CHAVHAN', 'shubhamchavhan_7311ce25@nit.edu.in', '9822454682', 'student_1763701454_691ff2ce16395.png', '$2y$10$H/fDsLQV/EXOeN1lzmUKX.kj2lt53eXwe/avs7UEV6.yzbnR3MjIy', 4, 53, 1, 1, '2025', 1, '2025-11-20 23:34:14', NULL, 0, NULL),
(240, NULL, 'CE-43', 'SUBODH UMESH KHANDEKAR', 'subodhkhandekar_7063ce25@nit.edu.in', '9356291762', 'student_1763701534_691ff2f6098b3.png', '$2y$10$ckD80ec09/GawWMOdGIekeDwMYx1.OlHP2UTF95ME/yz3zZ2KTIXa', 4, 53, 1, 1, '2025', 1, '2025-11-20 23:34:54', NULL, 0, NULL),
(241, NULL, 'CE-44', 'SUDHANSHU NISHILESH WANDRE', 'sudhanshuwandre_7173ce25@nit.edu.in', '9527007795', 'student_1763701537_691ff3215908c.png', '$2y$10$M939Gdh0MpNDggbcghI97eixO7V4R3KlDZPN5aEDSkU4XvPzgttyi', 4, 53, 1, 1, '2025', 1, '2025-11-20 23:35:37', NULL, 0, NULL),
(242, NULL, 'CE-45', 'SUMIT JAGDISH KAWALE', 'sumitkawale_7120ce25@nit.edu.in', '9322746313', 'student_1763701571_691ff343466c0.png', '$2y$10$TtjeMbeFmQAu4LrIaNWEGeR9V9PiX67BvzrhjY/Tq/QrY1t04vHoy', 4, 53, 1, 1, '2025', 1, '2025-11-20 23:36:11', NULL, 0, NULL),
(243, NULL, 'CE-46', 'SUNABH NAVNEET BORKAR', 'sunabhborkar_6973ce25@nit.edu.in', '8668696295', 'student_1763701605_691ff365a53dc.png', '$2y$10$q2flAMHtUDMxOmsiUuSeNuA0ZmOMcagsIezBVdbPh9o7JDoTEJhr2', 4, 53, 1, 1, '2025', 1, '2025-11-20 23:36:45', NULL, 0, NULL),
(244, NULL, 'CE-47', 'SUSHANT YOGENDRA TAMBE', 'sushanttambe_7218ce25@nit.edu.in', '9373275533', 'student_1763701644_691ff38ceaa0a.png', '$2y$10$9PC9GRpR8xSgliRb.8pkI.4yjhfTmdr379k3EBvRqv4Pd9VKNwAUa', 4, 53, 1, 1, '2025', 1, '2025-11-20 23:37:24', NULL, 0, NULL),
(245, NULL, 'CE-48', 'SWAMINI PURUSHOTTAM RAUT', 'swaminiraut_7040ce25@nit.edu.in', '8208794239', 'student_1763701685_691ff3b5c132f.png', '$2y$10$5qdZZR/92lxAIlKMfen8Uur8DqCaFV3SJ.5Q7w0fcx73ESwRE11JC', 4, 53, 1, 1, '2025', 1, '2025-11-20 23:38:05', NULL, 0, NULL),
(247, NULL, 'CE-50', 'TANMAY SURESH BHANARE', 'tanmaybhanare_7132ce25@nit.edu.in', '9930026331', 'student_1763701772_691ff40c43258.png', '$2y$10$iC6XpusOKdl7uBvwPDHn2eWfoMYrY9SHL03Q5X4KxbsmPWQ8Ujepi', 4, 53, 1, 1, '2025', 1, '2025-11-20 23:39:32', NULL, 0, NULL),
(248, NULL, 'CE-51', 'TANUJA PURUSHOTTAM LOLE', 'tanujalole_7041ce25@nit.edu.in', '9579625729', 'student_1763701858_691ff462c93ce.png', '$2y$10$oNb3nbMXEXhfNzH8ya2sFOE1ILTq.73NXoyehtp5o4El4U0JUYxye', 4, 53, 1, 1, '2025', 1, '2025-11-20 23:40:58', NULL, 0, NULL),
(253, NULL, 'CE-52', 'TEJAS SUNIL SONWANE', 'tejassonwane_7075ce25@nit.edu.in', '7559258315', 'student_1763701903_691ff48f52afc.png', '$2y$10$V97OPMsiLdm2pdM8e.94heRUBOFq7NOYulf2QQvIrtWgc/PdRU0tW', 4, 53, 1, 1, '2025', 1, '2025-11-20 23:41:43', NULL, 0, NULL),
(250, NULL, 'CE-53', 'TRUPTI MURLIDHAR DESHBHRATAR', 'truptideshbhratar_7261ce25@nit.edu.in', '8888623074', 'student_1763701953_691ff4c17c751.png', '$2y$10$Er43WfFaAwrSNb26oGJDxu9Ib.8JP9QTkcPDYY4uPlu/a86H2cxUK', 4, 53, 1, 1, '2025', 1, '2025-11-20 23:42:33', NULL, 0, NULL),
(251, NULL, 'CE-54', 'TRUSHNA DILIP SOMANKAR', 'trushnasomankar_7165ce25@nit.edu.in', '9699961308', 'student_1763702010_691ff4fa7b853.png', '$2y$10$gHjYZmwxJlCpCM/XiynLcOb8/VqB6N6tmWQmpMwKT.eT7Y.7B9wzO', 4, 53, 1, 1, '2025', 1, '2025-11-20 23:43:30', NULL, 0, NULL),
(252, NULL, 'CE-55', 'TUSHAR NANDULAL THAKUR', 'tusharthakur_6931ce25@nit.edu.in', '8999917723', 'student_1763702072_691ff538e67c3.png', '$2y$10$NBZQnYSEVlO48KRr57tozOnVbjKXSFPn0KypZfSTTvY6F.4C9kUq.', 4, 53, 1, 1, '2025', 1, '2025-11-20 23:44:32', NULL, 0, NULL),
(253, NULL, 'CE-56', 'UBAID JAVED SAYYED', 'ubaidsayyed_6972ce25@nit.edu.in', '7058881277', 'student_1763702170_691ff59a55553.png', '$2y$10$Ls5lfA9dJDbd0DX7Yy2IWe32WDp7XEMP8WSwJUXdbi5UmV/Sg2FNq', 4, 53, 1, 1, '2025', 1, '2025-11-20 23:46:10', NULL, 0, NULL),
(254, NULL, 'CE-57', 'UNNATI NITIN KASAR', 'unnatikasar_7017ce25@nit.edu.in', '9322809535', 'student_1763702225_691ff5d125d24.png', '$2y$10$Y1hzcnjK8U6btU6YMUv.8.IAbPc7CgNDU9jO12m2R5RW58/LN/RR6', 4, 53, 1, 1, '2025', 1, '2025-11-20 23:47:05', NULL, 0, NULL),
(256, NULL, 'CE-58', 'VAISHNAVI EKNATH BARSAGADE', 'vaishnavibarsagade_7030ce25@nit.edu.in', '9022082577', 'student_1763702346_691ff64a51397.png', '$2y$10$ri7HI3OCLhUKNa4vYYQWZekfeztvGa20qV25wMzmzy/ZEh5BXmolK', 4, 53, 1, 1, '2025', 1, '2025-11-20 23:49:06', NULL, 0, NULL),
(257, NULL, 'CE-59', 'VEDANT PRASHANT CHAUDHARI', 'vedantchaudhari_7203ce25@nit.edu.in', '9359727851', 'student_1763702400_691ff68059d5b.png', '$2y$10$YF1844wLxamsBgEU2FT7JeCI4koSKboYJ8ZiqEuwsA5oGs2ECtH86', 4, 53, 1, 1, '2025', 1, '2025-11-20 23:50:00', NULL, 0, NULL),
(258, NULL, 'CE-60', 'VEDANTI RAJU PUNATKAR', 'vedantipunatkar_6980ce25@nit.edu.in', '8607724648', 'student_1763702447_691ff6af53124.png', '$2y$10$Ot8kFo0fJLkP6tOBjwo0leo8ZlKm5w3M57LE8UmExlFrh9lSPA4Vi', 4, 53, 1, 1, '2025', 1, '2025-11-20 23:50:47', NULL, 0, NULL),
(259, NULL, 'CE-61', 'VINAY RAJESH UIKEY', 'vinayuikey_7033ce25@nit.edu.in', '7822985901', 'student_1763702519_691ff6f7ab04d.png', '$2y$10$qGy4AkjFEKeex/FjMk857eRPv1hiCcA5LXMEkRHA0kV.rYm8.8S7G', 4, 53, 1, 1, '2025', 1, '2025-11-20 23:51:59', NULL, 0, NULL),
(260, NULL, 'CE-62', 'VISHAKHA NARAYAN BHOYAR', 'vishakhabhoyar_7300ce25@nit.edu.in', '9511732059', 'student_1763702564_691ff72537871.png', '$2y$10$48RQjntfUZ.NHYJMPQeGCOJjAP5oVyLa8yI2EVpU45DyvjeWUmWjG', 4, 53, 1, 1, '2025', 1, '2025-11-20 23:52:44', NULL, 0, NULL);
INSERT INTO `students` (`id`, `user_id`, `roll_number`, `full_name`, `email`, `phone`, `photo`, `password`, `department_id`, `class_id`, `year`, `semester`, `admission_year`, `is_active`, `created_at`, `last_login`, `failed_login_attempts`, `account_locked_until`) VALUES
(261, NULL, 'CE-63', 'YASHIKA SUDHIR KALNAKE', 'yashikakalnake_7183ce25@nit.edu.in', '9356845334', 'student_1763702621_691ff75d38af6.png', '$2y$10$K2bdWIspHqB1uIGoumKKUuI.fm26HJFgD0BDXJMJMneG/H7RhPCxy', 4, 53, 1, 1, '2025', 1, '2025-11-20 23:53:41', NULL, 0, NULL),
(262, NULL, 'CE-64', 'YUGAL DINKAR GAIDHANE', 'yugalgaidhane_7299ce25@nit.edu.in', '9561972541', 'student_1763702669_691ff78d76487.png', '$2y$10$57prWKjpYJ3nqQ8VQ.3A.e31Z5FBBB9bf6AXpTBSAxGt/uQjA0066', 4, 53, 1, 1, '2025', 1, '2025-11-20 23:54:29', NULL, 0, NULL),
(263, NULL, 'CE-26', 'NACHIKET MUKESH PARAYE', 'nachiketparaye_7240ce25@nit.edu.in', '9209160386', 'student_1763702748_691ff7dcb3df.png', '$2y$10$sXF3aB282tS77R42dVNzfu90f4bkUEWt9bGn0wsPVq0z3mQREF8h.', 4, 53, 1, 1, '2025', 1, '2025-11-20 23:55:48', NULL, 0, NULL),
(44, NULL, 'EE-01', 'AASTHA WASUDEO WANKHADE', 'aasthawankhade_7137ee25@nit.edu.in', '7666683239', 'student_44_1763720002.png', '$2y$10$ulz9EJkmvdWFqS4cVe7D6Oq0D//OFMzVxaQ8aWZTucpbqxYwp3Jvq', 4, 59, 1, 1, '2025', 1, '2025-11-15 01:24:07', NULL, 0, NULL),
(45, NULL, 'EE-02', 'AISHWARYA SANJAY THAKUR', 'aishwaryathakur_6939ee25@nit.edu.in', '9699755971', 'student_45_1763720025.png', '$2y$10$XvHKi/d8CuzfFzL34x9HbOCc/GjZyxg200GGkvtDoy/6KzoRxjUra', 4, 59, 1, 1, '2025', 1, '2025-11-15 01:25:15', NULL, 0, NULL),
(46, NULL, 'EE-03', 'AMBAR GAGAN KURANKAR', 'ambarkurankar_6946ee25@nit.edu.in', '9404679905', 'student_46_1763720041.png', '$2y$10$HVgvSgD9C5nfRhAy5xbE4e9RzJ3VRbPXUi49ynUkPPP8WR1Ybz3Qy', 4, 59, 1, 1, '2025', 1, '2025-11-15 01:26:27', NULL, 0, NULL),
(50, NULL, 'EE-04', 'ANJALI BANDU YEWALE', 'anjaliyewale_7054ee25@nit.edu.in', '9850560617', 'student_50_1763720069.png', '$2y$10$W1OWqk/h1v2G8V0x2xTgZ.C.pX9cnO2.XDIkQEteSwQJLkci53EXm', 4, 59, 1, 1, '2025', 1, '2025-11-15 01:31:14', NULL, 0, NULL),
(51, NULL, 'EE-05', 'ANKUSH BHUWANLAL TURKAR', 'ankushturkar_7276ee25@nit.edu.in', '8623037063', 'student_51_1763720095.png', '$2y$10$L8yNVNSBWt.ZkmNkKT8zOubyNnfMvIsBY7DvBnhDARDEncKlhwnxq', 4, 59, 1, 1, '2025', 1, '2025-11-15 01:33:02', NULL, 0, NULL),
(52, NULL, 'EE-06', 'ARYAN HEMRAJ NANDANWAR', 'aryannandanwar_6948ee25@nit.edu.in', '9579970030', 'student_52_1763720247.png', '$2y$10$tcgL2nj52d4NYblmOFEE/OBqa4ZJ7FhkjAnJqyQUxKGPrD2N6JrFe', 4, 59, 1, 1, '2025', 1, '2025-11-15 01:34:31', NULL, 0, NULL),
(53, NULL, 'EE-07', 'ARYAN VASANTA SAKHARE', 'aryansakhare_7168ee25@nit.edu.in', '9021201815', 'student_53_1763720162.png', '$2y$10$ce5b/LLJ1BniXoWw0PlUyexn7nF3BL4ZVHn/9Pit6glbplj7Q5DPK', 4, 59, 1, 1, '2025', 1, '2025-11-15 01:35:41', NULL, 0, NULL),
(264, NULL, 'EE-08', 'AYUSHI UMESH WATH', 'ayushiwath_7244ee25@nit.edu.in', '9011022559', 'student_1763702892_691ff86cd6820.png', '$2y$10$UlPsMuGlUMDo7SSDv69gV.vCB2luZH.EX01u4wQyZooQ128WtG.Ye', 4, 59, 1, 1, '2025', 1, '2025-11-20 23:58:12', NULL, 0, NULL),
(265, NULL, 'EE-09', 'BHUMIKA RAVINDRA PATRIVAR', 'bhumikapatrivar_7199ee25@nit.edu.in', '7588151236', 'student_1763702949_691ff8a55078f.png', '$2y$10$iHZS54VXjn7BHj18jq5XV.owVCjTP4.4tqXgkuIDm1Zj/BI6NpFFe', 4, 59, 1, 1, '2025', 1, '2025-11-20 23:59:09', NULL, 0, NULL),
(267, NULL, 'EE-11', 'CHETAN SANJAY BASKAWARE', 'chetanbaskaware_7122ee25@nit.edu.in', '8329276305', 'student_1763703040_691ff900edcbd.png', '$2y$10$dFJQv4./tmiInEczPSPny.pCrStIuBZPPGisjAkL4dStAz6NTvblG', 4, 59, 1, 1, '2025', 1, '2025-11-21 00:00:40', NULL, 0, NULL),
(269, NULL, 'EE-13', 'EKTA BANDU MORE', 'ektamore_7093ee25@nit.edu.in', '9325707165', 'student_1763703130_691ff95aedff7.png', '$2y$10$6F6MtrXIQtV53Juvkb4SP.vcZ3bE.ofKVkwlZnIW3F3/9J.hpFiHe', 4, 59, 1, 1, '2025', 1, '2025-11-21 00:02:10', NULL, 0, NULL),
(270, NULL, 'EE-14', 'GAJANAN PIRAJI CHUNUPWAD', 'gajananchunupwad_7087ee25@nit.edu.in', '8010721848', 'student_1763703171_691ff9835e77b.png', '$2y$10$gII8yjzTE83gUVaSU13OS.UayJ5wf2ZliJoD9hbEtZ8zu6SWBByyS', 4, 59, 1, 1, '2025', 1, '2025-11-21 00:02:51', NULL, 0, NULL),
(271, NULL, 'EE-15', 'GAURI HEMRAJ GADHAVE', 'gaurigadhave_7009ee25@nit.edu.in', '9309985552', 'student_1763703212_691ff9ac617ab.png', '$2y$10$3ogDPMfwDVyGhdbaj4ISM.qmqZaNZby1fr5pLBKNYzyfVT0/SIIQq', 4, 59, 1, 1, '2025', 1, '2025-11-21 00:03:32', NULL, 0, NULL),
(272, NULL, 'EE-16', 'GULSHAN VINAYAKRAO CHAUDHARI', 'gulshanchaudhari_7027ee25@nit.edu.in', '8767532840', 'student_1763703269_691ff9e56763c.png', '$2y$10$Do0iIFgJtG8P2zMsSigWfuevq6Gx/vYf75OYHmacCkwklqHBrjoVi', 4, 59, 1, 1, '2025', 1, '2025-11-21 00:04:29', NULL, 0, NULL),
(273, NULL, 'EE-17', 'GUNJAN HEMRAJ MANMODE', 'gunjanmanmode_7080ee25@nit.edu.in', '9322595982', 'student_1763703309_691ffa0d7676f.png', '$2y$10$9YwxnpRt38LRDsFAHm7Lu.UxErFODIzS2WLOntm6mKn9NTWLA52Wy', 4, 59, 1, 1, '2025', 1, '2025-11-21 00:05:09', NULL, 0, NULL),
(274, NULL, 'EE-18', 'HEMANT BANDUJI BHOYAR', 'hemantbhoyar_6949ee25@nit.edu.in', '8010205693', 'student_1763703360_691ffa4047206.png', '$2y$10$3vHKzX8nn5935RMh5t5kTO09CH40gdosh9vVej3gWOxXC0DnZSAEC', 4, 59, 1, 1, '2025', 1, '2025-11-21 00:06:00', NULL, 0, NULL),
(275, NULL, 'EE-19', 'HIMANI SUDAM TAKIT', 'himanitakit_6990ee25@nit.edu.in', '7666429873', 'student_1763703405_691ffa6dc4a33.png', '$2y$10$e/7gwC.Gprg6I4pnf/vENuzb8wIKEf8cfnQuV.cXH3olQ/Qs8XCsy', 4, 59, 1, 1, '2025', 1, '2025-11-21 00:06:45', NULL, 0, NULL),
(276, NULL, 'EE-20', 'KHUSHAL NARENDRA NANDANWAR', 'khushalnandanwar_7237ee25@nit.edu.in', '9921384594', 'student_1763703504_691ffad06a7aa.png', '$2y$10$nxYVxb2K5wsZ7blwIM0QEOZnCJ/pwvn3MxLXz0DvaMjWiu//5DO2a', 4, 59, 1, 1, '2025', 1, '2025-11-21 00:08:24', NULL, 0, NULL),
(277, NULL, 'EE-10', 'CHAITANYA AVINASH JAGTAP', 'chaitanyajagtap_7103ee25@nit.edu.in', '9011602152', 'student_1763703897_691ffc5953dcb.png', '$2y$10$Wm5mTO2JlPZomZV9ISSFouCsZ1vkGY2/dKlmVU.yymjqGpCczrd7u', 4, 59, 1, 1, '2025', 1, '2025-11-21 00:14:57', NULL, 0, NULL),
(278, NULL, 'EE-12', 'DEVYANI VIJAY WAGHMARE', 'devyaniwaghmare_7227ee25@nit.edu.in', '7498873429', 'student_1763703983_691ffcafd5c45.png', '$2y$10$bW4W1CXXwAb8n1EtYEq9O.icHbrVGX/v8ijlh/gdZC2wEfiXsqPWe', 4, 59, 1, 1, '2025', 1, '2025-11-21 00:16:23', NULL, 0, NULL),
(279, NULL, 'EE-21', 'KHUSHI SANJAY SAWARKAR', 'khushisawarkar_6937ee25@nit.edu.in', '8805369178', 'student_1763704049_691ffcf19b513.png', '$2y$10$aoe0s520JXjBRglFwS28Bu.mvI6iB3FZ30EK0VAhFFSKstoxqBW1C', 4, 59, 1, 1, '2025', 1, '2025-11-21 00:17:29', NULL, 0, NULL),
(280, NULL, 'EE-22', 'KHUSHI SATISH NANWATKAR', 'khushinanwatkar_7212ee25@nit.edu.in', '7757073465', 'student_1763704088_691ffd182f19e.png', '$2y$10$35TY/KfxV.vnpg5G9y8DIePggbPzWr86js9n45SU1VdU79CVny5n.', 4, 59, 1, 1, '2025', 1, '2025-11-21 00:18:08', NULL, 0, NULL),
(281, NULL, 'EE-23', 'KHUSHIYA TUKARAM BAGDE', 'khushiyabagde_7304ee25@nit.edu.in', '8999317878', 'student_1763705936_691ffd487f582.png', '$2y$10$usVFFYAbEx3o5xz2dH0gZ.0MF1kYXCLIeuixLZQsVxRI.X9Su/gs.', 4, 59, 1, 1, '2025', 1, '2025-11-21 00:18:56', NULL, 0, NULL),
(282, NULL, 'EE-24', 'KIRTI SUBHASH KAMLE', 'kirtikamle_7289ee25@nit.edu.in', '7498879759', 'student_1763705970_691ffd6ad1999.png', '$2y$10$pTMMTvnBbkNUVx8cdoBtRuvHr8L0w8HxPrq1Xq/yvO6tBjlTRQz0W', 4, 59, 1, 1, '2025', 1, '2025-11-21 00:19:30', NULL, 0, NULL),
(283, NULL, 'EE-25', 'LAWANYA NARESH RAMTEKE', 'lawanyaramteke_7258ee25@nit.edu.in', '7249455475', 'student_1763704216_691ffd983d820.png', '$2y$10$btlhVQtDdf2tVcXrOBapD.Ral3aQIJPuo8zkbhPsu06dn2JBca4Se', 4, 59, 1, 1, '2025', 1, '2025-11-21 00:20:16', NULL, 0, NULL),
(284, NULL, 'EE-26', 'MANISH MANOJ PANDIT', 'manishpandit_6970ee25@nit.edu.in', '9699174787', 'student_1763704255_691ffdbf14604.png', '$2y$10$Wx/x/Lc/cWjs5zZq8LNBE.hIfLNCD614P6RBJqHk6UvQFyyH8jGu6', 4, 59, 1, 1, '2025', 1, '2025-11-21 00:20:55', NULL, 0, NULL),
(285, NULL, 'EE-27', 'MANTHAN SHEKHAR TELRANDHE', 'manthantelrandhe_7287ee25@nit.edu.in', '7666672491', 'student_1763704292_691ffde450500.png', '$2y$10$1ItL7HpjC2lcshr1KhjFWeTr8UJHNuFXhSRfFNt4sTAYzW8g9dPyO', 4, 59, 1, 1, '2025', 1, '2025-11-21 00:21:32', NULL, 0, NULL),
(286, NULL, 'EE-28', 'NIRALI ULHAS BORKAR', 'niraliborkar_7126ee25@nit.edu.in', '7498255674', 'student_1763704339_691ffe1369493.png', '$2y$10$e4ChZKck16zQbL/u04Pp2eLzz2f4AWDnFxjG09JKowgbyXpB5Zfri', 4, 59, 1, 1, '2025', 1, '2025-11-21 00:22:19', NULL, 0, NULL),
(287, NULL, 'EE-29', 'PRACHI ABHAY BHOYAR', 'prachibhoyar_7160ee25@nit.edu.in', '8010998697', 'student_1763704375_691ffe3791ab4.png', '$2y$10$40E2SZpGD4unec/GuirpJeBeuo/vCtVyModYyBTJM.SLRQ9wYj.MS', 4, 59, 1, 1, '2025', 1, '2025-11-21 00:22:55', NULL, 0, NULL),
(288, NULL, 'EE-30', 'PRADNYA NIRAJ RAUT', 'pradnyaraut_7131ee25@nit.edu.in', '7447704249', 'student_1763704420_691ffe646eaba.png', '$2y$10$HYhEG7nkUU4LKob2Cd1tYuprfMu59WfxBe4LLNsCgIawhN0pSwzES', 4, 59, 1, 2, '2025', 1, '2025-11-21 00:23:40', NULL, 0, NULL),
(289, NULL, 'EE-31', 'PRAGATI RADHESHYAMJI DHARPURE', 'pragatidharpure_7055ee25@nit.edu.in', '9370879204', 'student_1763704461_691ffe8d39b80.png', '$2y$10$rNJ6gflokfybBG3DfrwSIOt0FXp/R/ZcIE0UwDeqe7aF2TWnmKnRq', 4, 59, 1, 1, '2025', 1, '2025-11-21 00:24:21', NULL, 0, NULL),
(290, NULL, 'EE-32', 'PRAJWAL LAXMAN KUTTARMARE', 'prajwalkuttarmare_7039ee25@nit.edu.in', '7776894590', 'student_1763704507_691ffebb3da57.png', '$2y$10$HJORR0N.xz/UWCNkVNsdqOz.Un555Q7pvVM2LMEk.EHnlzjOyK4cu', 4, 59, 1, 1, '2025', 1, '2025-11-21 00:25:07', NULL, 0, NULL),
(291, NULL, 'EE-33', 'PRASHIKA PRAKASH GATHE', 'prashikagathe_7231ee25@nit.edu.in', '8265065351', 'student_1763704554_691ffeea75f07.png', '$2y$10$KBLCTvsF4s6Ed4LlrEXoveDLcTKC9HN.aNqQEoP11zVNePsSg2Ixe', 4, 59, 1, 1, '2025', 1, '2025-11-21 00:25:54', NULL, 0, NULL),
(292, NULL, 'EE-34', 'PRATIK LALIT KADREL', 'pratikkadrel_6975ee25@nit.edu.in', '7721856928', 'student_1763704602_691fff1ac80e9.png', '$2y$10$TSn/8lfVetbCoC7gtEublOdfQh3uO/Y/Z1Njs5isC2pXHz/RtXcqS', 4, 59, 1, 1, '2025', 1, '2025-11-21 00:26:42', NULL, 0, NULL),
(293, NULL, 'EE-35', 'PREM DHARMESHWAR KIRDE', 'premkirde_7251ee25@nit.edu.in', '8999709074', 'student_1763704639_691fff3f04a3d.png', '$2y$10$MkXTcQFT3u6BMcP6RKlGwOezxePcdbb8gIFTMK3kG9/sjzzs6U6wm', 4, 59, 1, 1, '2025', 1, '2025-11-21 00:27:19', NULL, 0, NULL),
(294, NULL, 'EE-36', 'PRERNA SANJAY BAVISKAR', 'prernabaviskar_7159ee25@nit.edu.in', '7020671947', 'student_1763704690_691fff72dd161.png', '$2y$10$gHAdI.hrI/fKiTcJU04lhe5VVWGrmbZ.k0xVKjfdjpBB7iF2Ia14a', 4, 59, 1, 1, '2025', 1, '2025-11-21 00:28:10', NULL, 0, NULL),
(295, NULL, 'EE-37', 'PURVA GAJANAN GORDE', 'purvagorde_7171ee25@nit.edu.in', '9422515498', 'student_1763704754_691fffb23971e.png', '$2y$10$boRfXZe7/8iWINhHoMsDpuXfRvx9nWzYZ4NqozVuLlnPvl/r3esHK', 4, 59, 1, 1, '2025', 1, '2025-11-21 00:29:14', NULL, 0, NULL),
(296, NULL, 'EE-38', 'PURVA JITENDRA DEVGHARE', 'purvadevghare_7079ee25@nit.edu.in', '9545753173', 'student_1763704800_691fffe05de59.png', '$2y$10$8cknwK.Tpqos.iDYCGdaeuA7DxKuKd2DVK307tZhFi.YBfKLJs5C2', 4, 59, 1, 1, '2025', 1, '2025-11-21 00:30:00', NULL, 0, NULL),
(297, NULL, 'EE-39', 'RIYA SAHENDRA SURYAWANSHI', 'riyasuryawanshi_7260ee25@nit.edu.in', '8554897885', 'student_1763704843_6920000b8a342.png', '$2y$10$QvocNksBgGJDi0qCjv1GEupga3xoRSR.v303gYUe9SsDT.SqV9H2y', 4, 59, 1, 1, '2025', 1, '2025-11-21 00:30:43', NULL, 0, NULL),
(298, NULL, 'EE-40', 'ROHIT VIKAS BHUTE', 'rohitbhute_6976ee25@nit.edu.in', '8767696452', 'student_1763704913_69200051502f0.png', '$2y$10$6Bjs3F9hgQV0EVXPmhpz2.XxSrXgsjMRgJJPmua5ynJ/7cKWEkv.K', 4, 56, 1, 1, '2025', 1, '2025-11-21 00:31:53', NULL, 0, NULL),
(299, NULL, 'EE-59', 'RUTUJA SHILWANT PATIL', 'rutujapatil_7167ee25@nit.edu.in', '8208259570', 'student_1763705590_69200242a19aa.png', '$2y$10$J3bYNd0HZTyMprb.KKLKjeR3L3HanD437uUVbcwq3FWb3A5kwtkg6', 4, 59, 1, 1, '2025', 1, '2025-11-21 00:40:10', NULL, 0, NULL),
(300, NULL, 'EE-42', 'SAMIKSHA GAJANAN BHATE', 'samikshabhate_6959ee25@nit.edu.in', '9373777584', 'student_1763705475_69200283cde95.png', '$2y$10$EIg8PnE83pIxUVT92rWjI.oShGOvG1qaQ4m4LKjhVmXxZ9txNqGnC', 4, 59, 1, 1, '2025', 1, '2025-11-21 00:41:15', NULL, 0, NULL),
(301, NULL, 'EE-43', 'SAMYAK ANIL LOHAKARE', 'samyaklohakare_7221ee25@nit.edu.in', '7558580696', 'student_1763705526_692002b6994ab.png', '$2y$10$9PQvt7pAHJgbIN3u8ko49.o7fqLTSIdHD1J5.IQ4omtS0ebh5gPVy', 4, 59, 1, 1, '2025', 1, '2025-11-21 00:42:06', NULL, 0, NULL),
(302, NULL, 'EE-44', 'SANIKA LAXMAN KANTODE', 'sanikakantode_7038ee25@nit.edu.in', '9421927342', 'student_1763705576_692002e85319d.png', '$2y$10$vVjjhow.Q18Y32RXecWatO0WorzitCY8fhaPgJn5yqpx8MhBZFzty', 4, 59, 1, 1, '2025', 1, '2025-11-21 00:42:56', NULL, 0, NULL),
(303, NULL, 'EE-45', 'SANIYA RAMRAO JAMBHULKAR', 'saniyajambhulkar_7277ee25@nit.edu.in', '7875951626', 'student_1763705611_6920030bc2593.png', '$2y$10$IINwLeWgCy/lm/O7iiQiPuotzsA9DmhLtPyDSLO8xp3fDhdcaMEle', 4, 59, 1, 1, '2025', 1, '2025-11-21 00:43:31', NULL, 0, NULL),
(304, NULL, 'EE-46', 'SANSKRUTI NITIN PAWAR', 'sanskrutipawar_7042ee25@nit.edu.in', '7666340208', 'student_1763705658_6920033a389ac.png', '$2y$10$ayLej3qlp1cpRTg999NmT.emGCfFNyBw4sbo51ICz5shDiEGKZl9W', 4, 59, 1, 1, '2025', 1, '2025-11-21 00:44:18', NULL, 0, NULL),
(305, NULL, 'EE-47', 'SATYAM MANGALCHANDI GHOSH', 'satyamghosh_7246ee25@nit.edu.in', '8793163140', 'student_1763705757_6920039d7dae5.png', '$2y$10$nfnTGmFd3KovXyid8BKvyO5V3t4qItTAWvDm4lhGfj4W2z5FxXlJW', 4, 59, 1, 1, '2025', 1, '2025-11-21 00:45:57', NULL, 0, NULL),
(306, NULL, 'EE-48', 'SHILPKAR SURESH RAMTEKE', 'shilpkarramteke_7280ee25@nit.edu.in', '8767009286', 'student_1763705848_692003f8aa93b.png', '$2y$10$MCW.vC3U2gB4LBKwDaCAOOWtGWSarpP/L3ZzptATAovrIDX5WXKna', 4, 59, 1, 1, '2025', 1, '2025-11-21 00:47:28', NULL, 0, NULL),
(307, NULL, 'EE-49', 'SHIVANI RAVI INGALE', 'shivaniingale_7182ee25@nit.edu.in', '7020755162', 'student_1763705901_6920042dd404e.png', '$2y$10$DzTWRJZPAdDifMIE0AbKaOeF6Mo/ZYudKJYn2Y81xvsVqF0Vws.9W', 4, 59, 1, 1, '2025', 1, '2025-11-21 00:48:21', NULL, 0, NULL),
(308, NULL, 'EE-50', 'SHRUTI MAHESH MESHRAM', 'shrutimeshram_7256ee25@nit.edu.in', '8698157281', 'student_1763705943_69200457e3bf9.png', '$2y$10$oFBnnypsM7wGjLoufmFEjujdqsqxOdAeEsoUcNiwGOfquQ8PQiz12', 4, 59, 1, 1, '2025', 1, '2025-11-21 00:49:03', NULL, 0, NULL),
(309, NULL, 'EE-51', 'SHRUTI MUKINDA MANERAO', 'shrutimanerao_7101ee25@nit.edu.in', '9209421053', 'student_1763705984_692004807968d.png', '$2y$10$zonI3fXvJmDf0B6tGPO2IOKYF2nM7ZQkcU/MJNVIxhzpRo2N3MaBu', 4, 59, 1, 1, '2025', 1, '2025-11-21 00:49:44', NULL, 0, NULL),
(310, NULL, 'EE-52', 'SHUBHAM GAJANAN KHORGADE', 'shubhamkhorgade_7208ee25@nit.edu.in', '8999976180', 'student_1763706028_692004ac286ef.png', '$2y$10$r//SAWs6Hpe6dyd9TNURTuP3z2XliBeljZfIT6usDR2iX/JSov.8G', 4, 59, 1, 1, '2025', 1, '2025-11-21 00:50:28', NULL, 0, NULL),
(311, NULL, 'EE-53', 'SOHAM VINOD DHOTE', 'sohamdhote_7286ee25@nit.edu.in', '8390366168', 'student_1763706066_692004d2211d6.png', '$2y$10$GvUIMwhn8cHCcD.c21ZKXu0TwtYkB/ZA1ncBsZYUXjc4ljVpuXJ9q', 4, 59, 1, 1, '2025', 1, '2025-11-21 00:51:06', NULL, 0, NULL),
(314, NULL, 'EE-54', 'SUHANI JAYCHAND PATLE', 'suhanipatle_7117ee25@nit.edu.in', '8459761379', 'student_1763706252_6920058caab07.png', '$2y$10$S1sFCVSBGPpugl1nzmOcnOqiLXU.x2E7Ssk.tTRJphtPMR468fjL6', 4, 59, 1, 1, '2025', 1, '2025-11-21 00:54:12', NULL, 0, NULL),
(315, NULL, 'EE-55', 'SUHANI VIJAY CHAUDHARI', 'suhanichaudhari_7119ee25@nit.edu.in', '9890506163', 'student_1763706296_692005b881620.png', '$2y$10$yJZycFmNsZyoYVggPh4XOe3Q7/QKeSW.2VO9P8QGLzMS.raFjEXPK', 4, 59, 1, 1, '2025', 1, '2025-11-21 00:54:56', NULL, 0, NULL),
(316, NULL, 'EE-56', 'SWATI KESHAVRAO BHOPE', 'swatibhope_7230ee25@nit.edu.in', '8263808582', 'student_1763706347_692005eb87a93.png', '$2y$10$.mL94z98UyntBX.BrxG0tuTMOU6LrNJv2iY6VX3fiu.cbCq.Piqmu', 4, 59, 1, 1, '2025', 1, '2025-11-21 00:55:47', NULL, 0, NULL),
(317, NULL, 'EE-57', 'TANISHA WASUDEO BADODE', 'tanishabadode_7091ee25@nit.edu.in', '7498002763', 'student_1763706379_6920060b5e491.png', '$2y$10$IJVoBKVr7UA7jgrPnzS8OujIzN4NGI97tRGcbyhFGsbklA6iLQLc6', 4, 59, 1, 1, '2025', 1, '2025-11-21 00:56:19', NULL, 0, NULL),
(318, NULL, 'EE-58', 'TRUPTI PURUSHOTTAM MORE', 'truptimore_7301ee25@nit.edu.in', '9637807519', 'student_1763706595_6920062faccf5.png', '$2y$10$m7BLAxDhlOt.khFt27xFK.H4mt2Y2LFr1ZNfK07HPvO/Rd1mHTu8q', 4, 59, 1, 1, '2025', 1, '2025-11-21 00:56:55', NULL, 0, NULL),
(319, NULL, 'EE-59', 'VIPUL VIJAY DHANDE', 'vipuldhande_7082ee25@nit.edu.in', '8767966842', 'student_1763706458_6920065a127d3.png', '$2y$10$7z29.l.GMNR8/BnB0kVLguBKSVhPQIUEGbnZuaBUiyi0k5vAKdxca', 4, 59, 1, 1, '2025', 1, '2025-11-21 00:57:38', NULL, 0, NULL),
(320, NULL, 'EE-60', 'YASH RAVINDRA DAFAR', 'yashdafar_7007ee25@nit.edu.in', '9552402792', 'student_1763706492_6920067c2fd21.png', '$2y$10$K7w0JC9Ua0cpvXgCRO6jBu83602IIyi8RI.HrLQmXBv4SaRRe9/BG', 4, 59, 1, 1, '2025', 1, '2025-11-21 00:58:12', NULL, 0, NULL),
(321, NULL, 'EE-61', 'YOGITA CHANDRASHEKHAR THAKUR', 'yogitathakur_7297ee25@nit.edu.in', '7558464380', 'student_1763706530_692006a210f1f.png', '$2y$10$DWx7g3OObqlrhEQhEc3iiONwz3jQVWTPTgWR7srtPjgU7beclMUsm', 4, 59, 1, 1, '2025', 1, '2025-11-21 00:58:50', NULL, 0, NULL),
(59, NULL, 'BCSE-01', 'ANKITA ANIL CHANDANKHEDE', 'ankitachandankhede_7157cse25@nit.edu.in', '9960989041', 'student_59_1763719374.png', '$2y$10$/S6GT/.4CmWruxHwryOreuzWp2msqDjYg1MTcTKpHkbCVEDrEB6NG', 4, 74, 1, 1, '2025', 1, '2025-11-15 01:51:08', NULL, 0, NULL),
(60, NULL, 'BCSE-02', 'ANUSHKA SANJAYRAO RAUT', 'anushkaraut_7088cse25@nit.edu.in', '8208962296', 'student_60_1763719399.png', '$2y$10$DA9DpwVUq0Q52ppKxv5sK.qqLyTHfEVcU.bFDbE8ArkeTQkrU2Ed.', 4, 74, 1, 1, '2025', 1, '2025-11-15 01:52:09', NULL, 0, NULL),
(61, NULL, 'BCSE-03', 'ARYA GUNWANT LEDANGE', 'aryaledange_7188cse25@nit.edu.in', '9284356283', 'student_61_1763719452.png', '$2y$10$3bDsI7Ork2HDhMhye4FZCO9ZhGYu2Ew/1X/Tf2Io4QTTOQ/B6vbl2', 4, 74, 1, 1, '2025', 1, '2025-11-15 01:52:59', NULL, 0, NULL),
(62, NULL, 'BCSE-04', 'ARYA JOGENDRA BINZADE', 'aryabinzade_7028cse25@nit.edu.in', '8378912413', 'student_62_1763719474.png', '$2y$10$Hra6Tm7GSHXDkCpNR6RbRuEIX7k/4thGRiB.VQs3Gs5JYG2gtFdK.', 4, 74, 1, 1, '2025', 1, '2025-11-15 01:53:43', NULL, 0, NULL),
(63, NULL, 'BCSE-05', 'ARYAN HANSRAJ PATIL', 'aryanpatil_7198cse25@nit.edu.in', '8329139697', 'student_63_1763719643.png', '$2y$10$TK0KK6MZptebURyjdP7CGeycBkpY3SLoQjmHY/hrrnVsPonvGITtW', 4, 74, 1, 1, '2025', 1, '2025-11-15 01:54:32', NULL, 0, NULL),
(451, NULL, 'BCSE-06', 'ARYAN NANDKISHOR PAL', 'aryanpal_7152cse25@nit.edu.in', '9834659244', 'student_1763714879_6920273fd4c06.png', '$2y$10$IY1lWh1V1le4QR2PXkd61e4p.qfoKEnT8aLrbiTRXrSkpQOJYDxZW', 4, 74, 1, 1, '2025', 1, '2025-11-21 03:17:59', NULL, 0, NULL),
(452, NULL, 'BCSE-07', 'ARYAN VIJAY KITUKALE', 'aryankitukale_7235cse25@nit.edu.in', '8411914251', 'student_1763714921_692027692f1df.png', '$2y$10$GkKpmx39UVTQtKEHSnoYy.672yZ4TS0BQYfBtaOcZzDSxC3Si.qLa', 4, 74, 1, 1, '2025', 1, '2025-11-21 03:18:41', NULL, 0, NULL),
(453, NULL, 'BCSE-08', 'BRIJESH BABALU MADHEKAR', 'brijeshmadhekar_6958cse25@nit.edu.in', '7620001752', 'student_1763714961_69202791e4ec5.png', '$2y$10$RMa9o7Jb4xA29WStgx3Qie4BebGogsBbmOkqWEYKgYOQav.2GMR1y', 4, 74, 1, 1, '2025', 1, '2025-11-21 03:19:21', NULL, 0, NULL),
(454, NULL, 'BCSE-09', 'CHAITANYA WASUDEO RAUT', 'chaitanyaraut_6943cse25@nit.edu.in', '8600193146', 'student_1763715002_692027baaf127.png', '$2y$10$gc96a50H0p5Md7qU7fNTveJ6WtkA1imvitw8kW19JK4uCEq9AVlRO', 4, 74, 1, 1, '2025', 1, '2025-11-21 03:20:02', NULL, 0, NULL),
(455, NULL, 'BCSE-10', 'DHRUVESH RAMKRRUSHNA PARATE', 'dhruveshparate_7305cse25@nit.edu.in', '9130508625', 'student_1763715039_692027dfd48f8.png', '$2y$10$SjP33vYkpYhsRMQUxXmvhOm9HnQQgBqtfArbMv3sTHWGq5N827IuK', 4, 74, 1, 1, '2025', 1, '2025-11-21 03:20:39', NULL, 0, NULL),
(456, NULL, 'BCSE-11', 'DINESH DILIP KALE', 'dineshkale_7077cse25@nit.edu.in', '8975669285', 'student_1763715077_692028057f500.png', '$2y$10$vibihUKVUYVey3RAqpLnnOM4hWMoDKzJpErADMZgdt62FkCFxKjKC', 4, 74, 1, 1, '2025', 1, '2025-11-21 03:21:17', NULL, 0, NULL),
(457, NULL, 'BCSE-12', 'HARSHITA RAVINDRA MANUSMARE', 'harshitamanusmare_7034cse25@nit.edu.in', '9921420921', 'student_1763715168_6920286020b81.png', '$2y$10$oot5lw2v5fhYBYlXc7QYtuXHsZr8tLxh.uxxdTZhcVVgDP4Y4Rnw.', 4, 74, 1, 1, '2025', 1, '2025-11-21 03:22:48', NULL, 0, NULL),
(458, NULL, 'BCSE-13', 'JANHVI PRAKASH BAMBAL', 'janhvibambal_7150cse25@nit.edu.in', '9322401157', 'student_1763715211_6920288b526cd.png', '$2y$10$3zIJPZ/VzEMy.bb8oy8D5uKRx8sD9RrUCLArXZBDTwg5llc/bcSb.', 4, 74, 1, 1, '2025', 1, '2025-11-21 03:23:31', NULL, 0, NULL),
(459, NULL, 'BCSE-14', 'JAYENDRA JAYPAL FUNDE', 'jayendrafunde_7164cse25@nit.edu.in', '7030533246', 'student_1763715270_692028c64aa35.png', '$2y$10$NeSqlR.jboGCXWAGQdJmnudLgpZfSZYBg1GIJBnL2G34DjKPjG0Ty', 4, 74, 1, 1, '2025', 1, '2025-11-21 03:24:30', NULL, 0, NULL),
(460, NULL, 'BCSE-15', 'KALASH VINOD KOLHATKAR', 'kalashkolhatkar_7202cse25@nit.edu.in', '8767813403', 'student_1763715308_692028ec54286.png', '$2y$10$p0v4fgojYga7msN.DXg4.ejc5re.f65WgDbt7d7SIoQpMtelS2RDC', 4, 74, 1, 1, '2025', 1, '2025-11-21 03:25:08', NULL, 0, NULL),
(461, NULL, 'BCSE-16', 'KARTIK VASUDEO CHOUDHARY', 'kartikchoudhary_7020cse25@nit.edu.in', '8999328701', 'student_1763715353_6920291972fa1.png', '$2y$10$ycDU3YEMi7LD2JYOzwUfp.BeCj4L6ysJbV4xg4jrUv1zQsN1OSPom', 4, 74, 1, 1, '2025', 1, '2025-11-21 03:25:53', NULL, 0, NULL),
(462, NULL, 'BCSE-17', 'LOBHANSHA KISHOR DAHAKE', 'lobhanshadahake_6942cse25@nit.edu.in', '9730721764', 'student_1763715412_692029546efe9.png', '$2y$10$dbaXJeVjOzutAVjjECfK7u.Wl6sY.WsseWYAdLCq3nln5WUXNLg.i', 4, 74, 1, 1, '2025', 1, '2025-11-21 03:26:52', NULL, 0, NULL),
(467, NULL, 'BCSE-20', 'MEGHA UMESH DHARMIK', 'meghadharmik_7265cse25@nit.edu.in', '9158327881', 'student_1763715856_69202b10d044e.png', '$2y$10$PV99PZHjkPuAfUtLscOgWOIdndCdOqrgCPV6jQAmr/qwwxkcgopC2', 4, 74, 1, 1, '2025', 1, '2025-11-21 03:34:16', NULL, 0, NULL),
(468, NULL, 'BCSE-21', 'MEHUL GAUTAM MESHRAM', 'mehulmeshram_7071cse25@nit.edu.in', '7066948241', 'student_1763715929_69202b59cc2e5.png', '$2y$10$ljCdLmFLSaONJW9MVEAb3evb0J4Iej8T4.lIXuzDb8RysUiJRYREO', 4, 74, 1, 1, '2025', 1, '2025-11-21 03:35:29', NULL, 0, NULL),
(469, NULL, 'BCSE-22', 'MILIND BHUNESHWAR GIRI', 'milindgiri_6999cse25@nit.edu.in', '9699736085', 'student_1763715984_69202b74c7955.png', '$2y$10$ed6mdXmNPwnLfJfSLy9AbuE8um0/XR225et4EWKQM8mZW0Cog5VMG', 4, 74, 1, 1, '2025', 1, '2025-11-21 03:36:24', NULL, 0, NULL),
(470, NULL, 'BCSE-23', 'MOHAMMAD FARHAN SHAKIL AHMED', 'mohammadfarhan_7049cse25@nit.edu.in', '8657860835', 'student_1763716044_69202bcc25294.png', '$2y$10$rymJyT12g3BeiwJy6ZbMAeD06HEQCkjwRt1uX.NJpq/OGEDPn7QsG', 4, 74, 1, 1, '2025', 1, '2025-11-21 03:37:24', NULL, 0, NULL),
(471, NULL, 'BCSE-24', 'MRUNALI VITHOBA MARASKOLHE', 'mrunalimaraskolhe_7161cse25@nit.edu.in', '8421948262', 'student_1763716074_69202bfa8ff12.png', '$2y$10$SGpuAIMeJaE2VvxF5.Z/ceK6dQr6Wqa/a8MR57R1ZaWfsFau2dEQC', 4, 74, 1, 1, '2025', 1, '2025-11-21 03:38:10', NULL, 0, NULL),
(472, NULL, 'BCSE-25', 'NAMRATA ANIL NAVALE', 'namratanavale_7225cse25@nit.edu.in', '7058963610', 'student_1763716129_69202c21e2cbd.png', '$2y$10$7HwpORz7N4.OWmvYKx/Pbu36624RkEGHDarVOiD1gJAT4jtrF4xUe', 4, 74, 1, 1, '2025', 1, '2025-11-21 03:38:49', NULL, 0, NULL),
(473, NULL, 'BCSE-26', 'NANDINI GAURISHANKAR BANKAR', 'nandinibankar_7211cse25@nit.edu.in', '9156912062', 'student_1763716170_69202c4a7a921.png', '$2y$10$JO34XjAN.RQub7Fu2tmyGeJyP9IKojyPFbLaicbZU/.nSLPDlQuVe', 4, 74, 1, 1, '2025', 1, '2025-11-21 03:39:30', NULL, 0, NULL),
(474, NULL, 'BCSE-27', 'NEHA GANESH JIBHAKATE', 'nehajibhakate_7191cse25@nit.edu.in', '7798285192', 'student_1763716218_69202c7a666e0.png', '$2y$10$DJ/RVPZETmTKuk2s5mokSOWy7LTCEeIHHX1hcFevvhyRztgIuwaTa', 4, 74, 1, 1, '2025', 1, '2025-11-21 03:40:18', NULL, 0, NULL),
(475, NULL, 'BCSE-28', 'NIDHI ANIL RAUT', 'nidhiraut_7293cse25@nit.edu.in', '9579206328', 'student_1763716258_69202ca2213a7.png', '$2y$10$9OJMpr5AsW3HD7GOa.iQr.7QWsESvnEgRDi/ljycr8zK7YS9adwji', 4, 74, 1, 1, '2025', 1, '2025-11-21 03:40:58', NULL, 0, NULL),
(476, NULL, 'BCSE-29', 'NIDHI ASHOK BASANWAR', 'nidhibasanwar_7215cse25@nit.edu.in', '8208264560', 'student_1763716293_69202cc507e66.png', '$2y$10$A79SjMqiQszkCH0.ut/ZguhWjc2xm.KCBJfXxWrBB/zhEgBLobcGa', 4, 74, 1, 1, '2025', 1, '2025-11-21 03:41:33', NULL, 0, NULL),
(477, NULL, 'BCSE-30', 'NIHARIKA SHANTILAL PACHDHARE', 'niharikapachdhare_7051cse25@nit.edu.in', '7517662821', 'student_1763716331_69202ceb9e122.png', '$2y$10$kkNG8baoDpapmCUr5fLEjuy/QUmm20it1viQTxcSBC2oKn2UDSHXu', 4, 74, 1, 1, '2025', 1, '2025-11-21 03:42:11', NULL, 0, NULL),
(478, NULL, 'BCSE-31', 'NIKHIL SURESH NINAWE', 'nikhilninawe_7156cse25@nit.edu.in', '8503801634', 'student_1763716393_69202d29d3fe8.png', '$2y$10$Z/FxCby.7c37G8AUEWET3uPxYibzCzdBCq1VOxMvlQ25JLauPcBoO', 4, 74, 1, 1, '2025', 1, '2025-11-21 03:43:13', NULL, 0, NULL),
(479, NULL, 'BCSE-32', 'NIKHILESH PRAKASH DIKONDWAR', 'nikhileshdikondwar_6971cse25@nit.edu.in', '7496343589', 'student_1763716432_69202d50cd57c.png', '$2y$10$.0rasAeX.CquDes48RMPeOVp8Ztuq7CelBLB/93fKixmR5jkyy3wi', 4, 74, 1, 1, '2025', 1, '2025-11-21 03:43:52', NULL, 0, NULL),
(480, NULL, 'BCSE-33', 'NISARG ATUL SATHAWANE', 'nisargsathawane_7264cse25@nit.edu.in', '8484974082', 'student_1763716470_69202d76a68c4.png', '$2y$10$KJ2w1RBEmHOmrVs.rdf9W.H7E.cGPYaK7mej7oBCGfoLSZ9Ty3vMq', 4, 74, 1, 1, '2025', 1, '2025-11-21 03:44:30', NULL, 0, NULL),
(481, NULL, 'BCSE-34', 'PRACHI VIJAY GADGE', 'prachigadge_7174cse25@nit.edu.in', '7020572143', 'student_1763716526_69202daeab977.png', '$2y$10$X7Rr8TTUuYsb7AHn1qH3xuzkU7PX3KShebh4kVggq5KpcYxzQ0l/6', 4, 74, 1, 1, '2025', 1, '2025-11-21 03:45:26', NULL, 0, NULL),
(482, NULL, 'BCSE-35', 'PRANALI SUBHASH MOON', 'pranalimoon_7076cse25@nit.edu.in', '8830240886', 'student_1763716568_69202dd852541.png', '$2y$10$g7CDPGI/fdms.x.cHVDBIumaJ4b4Dzdcq0ToJkpH87kaWUhvZWCUq', 4, 74, 1, 1, '2025', 1, '2025-11-21 03:46:08', NULL, 0, NULL),
(483, NULL, 'BCSE-36', 'PRANJAL KRUSHNA FULE', 'pranjalfule_6936cse25@nit.edu.in', '9243579304', 'student_1763716632_69202e18b624c.png', '$2y$10$lGKHW31MkNhXTVZbXlds3.s8.gXool4kpF4qF/I.0Jl3ssEgGofxK', 4, 74, 1, 1, '2025', 1, '2025-11-21 03:47:12', NULL, 0, NULL),
(484, NULL, 'BCSE-37', 'PRIYA BABANRAO NAGLE', 'priyanagle_6992cse25@nit.edu.in', '7498358199', 'student_1763716683_69202e4b14780.png', '$2y$10$kxrFxjoo1w213hrdHtF./O.21ru.n1p0dfrOLd24wFPoYnEGdGkb2', 4, 74, 1, 1, '2025', 1, '2025-11-21 03:48:03', NULL, 0, NULL),
(485, NULL, 'BCSE-38', 'PUNAM KRUSHNAKUMAR BISEN', 'punambisen_7138cse25@nit.edu.in', '9834997178', 'student_1763716722_69202e72586ec.png', '$2y$10$c6j20b3G5sdgRE/Rn685f.bnwUsExA6N/RjfF6/DCUKTeSg64XMx.', 4, 74, 1, 1, '2025', 1, '2025-11-21 03:48:42', NULL, 0, NULL),
(486, NULL, 'BCSE-39', 'RAXIT PRAVIN KADU', 'raxitkadu_6954cse25@nit.edu.in', '7449350608', 'student_1763716774_69202ea6377fa.png', '$2y$10$QBh5Cj691F2lhbxu0wysdOwhfeUzsGqedwqSZluQHT48rNPcx9AN.', 4, 74, 1, 1, '2025', 1, '2025-11-21 03:49:34', NULL, 0, NULL),
(487, NULL, 'BCSE-40', 'RISHABH JAYPAL PATIL', 'rishabhpatil_7136cse25@nit.edu.in', '9322789943', 'student_1763716818_69202ed21b7d3.png', '$2y$10$0WKP7NmwaaIrb2B4rNNM2uzVpA6.SPsYy3FkjFktZ.anAaK2jHESW', 4, 74, 1, 1, '2025', 1, '2025-11-21 03:50:18', NULL, 0, NULL),
(488, NULL, 'BCSE-41', 'RIYA NILESH BANKAR', 'riyabankar_7118cse25@nit.edu.in', '8999881356', 'student_1763716869_69202f051836b.png', '$2y$10$6qo3u3OQc3ZCtSZTD.6f4OwFeni54AbfzK9.Nq7O1t4XfpMwAe5vi', 4, 74, 1, 1, '2025', 1, '2025-11-21 03:51:09', NULL, 0, NULL),
(489, NULL, 'BCSE-42', 'RIYA SANJAY KOLURWAR', 'riyakolurwar_7247cse25@nit.edu.in', '9665252699', 'student_1763716912_69202f30bb264.png', '$2y$10$fX4.MT7m9aedCYp7PHe5t.ermcu28CmmKaaA1glMDbhFuvljYaBeO', 4, 74, 1, 1, '2025', 1, '2025-11-21 03:51:52', NULL, 0, NULL),
(474, NULL, 'BCSE-43', 'ROUNAK VINAY SINGH', 'rounaksingh_6965cse25@nit.edu.in', '9209393056', 'student_1763716977_69202f7127069.png', '$2y$10$pKsd3Vd7HCixQ57Cc/nbz.ud339r7SYCkfACzUqswk8ZNBQUEkZMO', 4, 74, 1, 1, '2025', 1, '2025-11-21 03:52:57', NULL, 0, NULL),
(491, NULL, 'BCSE-44', 'RUCHIKA PRAKASH SONKUSARE', 'ruchikasonkusare_7177cse25@nit.edu.in', '9270488827', 'student_1763717030_69202fa66d785.png', '$2y$10$kltCR9rZs0DiLfI62lAHqeWNaaU/3jSow0xTVvZ2u4fiqJJWv0umS', 4, 74, 1, 1, '2025', 1, '2025-11-21 03:53:50', NULL, 0, NULL),
(492, NULL, 'BCSE-45', 'SAHIL GUNVANT CHAVHAN', 'sahilchavhan_6947cse25@nit.edu.in', '8263802270', 'student_1763717081_69202fd9899c6.png', '$2y$10$jSHnJVeZ9PyEccDmstQwJe7I6Xao3GpBDXjPU71Uh9O1Pntq0D0uW', 4, 74, 1, 1, '2025', 1, '2025-11-21 03:54:41', NULL, 0, NULL),
(493, NULL, 'BCSE-46', 'SAMIKSHA MAHESH FUNDE', 'samikshafunde_7125cse25@nit.edu.in', '7875273266', 'student_1763717150_6920301e17896.png', '$2y$10$PPvkeWlikUpKeCTVGARnk.L9KP1yOfSW56EZB7IDo2i8gANNPXdT.', 4, 74, 1, 1, '2025', 1, '2025-11-21 03:55:50', NULL, 0, NULL),
(494, NULL, 'BCSE-47', 'SAMRAT SARNATH MOHOD', 'samratmohod_7106cse25@nit.edu.in', '9923588348', 'student_1763717204_69203054a32f9.png', '$2y$10$BVHlIaOxOS.LIWVwRXuoreWhmYKBRBGEIwl9eqLzssjSjBaOmAzKG', 4, 74, 1, 1, '2025', 1, '2025-11-21 03:56:44', NULL, 0, NULL),
(495, NULL, 'BCSE-48', 'SANCHITI SANJAY NIRWAN', 'sanchitinirwan_7100cse25@nit.edu.in', '9730123552', 'student_1763717283_692030a3ef25f.png', '$2y$10$xKcjCsLukgDb1hIEDpa8muwUHSbqe6lX8dgx3FpkkzoYItLkTxqjK', 4, 74, 1, 1, '2025', 1, '2025-11-21 03:58:03', NULL, 0, NULL),
(496, NULL, 'BCSE-49', 'SANI GAUTAM DESHBHRATAR', 'sanideshbhratar_7032cse25@nit.edu.in', '9637235141', 'student_1763717343_692030df99bb8.png', '$2y$10$uV4eRD7leqJXitReq9atveRohSLLCRv8y52XBuT0goKou/cW.dZ6y', 4, 74, 1, 1, '2025', 1, '2025-11-21 03:59:03', NULL, 0, NULL),
(497, NULL, 'BCSE-50', 'SATYAM HANUMAN PRASAD SHUKLA', 'satyamshukla_7067cse25@nit.edu.in', '9561431595', 'student_1763717394_6920311207238.png', '$2y$10$bTz1Vd42uSwjlWP.aMWnJOjT9GQrqHCf6O0DBJ6ti1JxxUkXiW53q', 4, 74, 1, 1, '2025', 1, '2025-11-21 03:59:54', NULL, 0, NULL),
(498, NULL, 'BCSE-51', 'SHREYASH GAUTAM SORDE', 'shreyashsorde_7045cse25@nit.edu.in', '9356514243', 'student_1763717438_6920313e78611.png', '$2y$10$TYzmGzYPoQ733.OvPrkLousTLAONty1OYyQ25m..53Qo/LKJonLiK', 4, 74, 1, 1, '2025', 1, '2025-11-21 04:00:38', NULL, 0, NULL),
(499, NULL, 'BCSE-52', 'SHRUTI SHISHUPAL WALKE', 'shrutiwalke_7255cse25@nit.edu.in', '9209579343', 'student_1763717484_6920316ce1b36.png', '$2y$10$KwyJ7/VtWc1Hu4/ucxYX/uso1yl4rPB2uhTUo1kdfakGm4x825bLO', 4, 74, 1, 1, '2025', 1, '2025-11-21 04:01:24', NULL, 0, NULL),
(500, NULL, 'BCSE-53', 'SHUBHANGI ASARAM ANDHALE', 'shubhangiandhale_6964cse25@nit.edu.in', '9307432108', 'student_1763717533_6920319db4dca.png', '$2y$10$NvyO.fj57il85DpdHqn5hOxM5CqfZMmHMFbKxq0I8CI5cl2f6K8rO', 4, 74, 1, 1, '2025', 1, '2025-11-21 04:02:13', NULL, 0, NULL),
(501, NULL, 'BCSE-54', 'SHUBHANGI SANJAY JAYBHAYE', 'shubhangijaybhaye_6956cse25@nit.edu.in', '9518914035', 'student_1763717575_692031c7e9ba4.png', '$2y$10$yfsWbfODrmgIFwms65RqyucSBsrVq29WAAnyyzny0.yAm..iCTN4e', 4, 74, 1, 1, '2025', 1, '2025-11-21 04:02:55', NULL, 0, NULL),
(502, NULL, 'BCSE-55', 'SIDDHARTH SHRIKANT UKEY', 'siddharthukey_7284cse25@nit.edu.in', '8975714701', 'student_1763717630_692031fe804e6.png', '$2y$10$.1Dv2zNw0Rm2pDKGfpYv5eL57Uwz20LNHo57lTQBtOR0UwJt6dYo2', 4, 74, 1, 1, '2025', 1, '2025-11-21 04:03:50', NULL, 0, NULL),
(503, NULL, 'BCSE-56', 'SMITA ANIL KAYANDE', 'smitakayande_6952cse25@nit.edu.in', '9272003573', 'student_1763717671_69203227c0caf.png', '$2y$10$pM8uC7NvmpDod7QdMbcxJeYJZQWYB60GbyaCt8R/KPL49msizpmFO', 4, 74, 1, 1, '2025', 1, '2025-11-21 04:04:31', NULL, 0, NULL),
(504, NULL, 'BCSE-57', 'SUDHANSHU NANDKISHOR BHASAKHETRE', 'sudhanshubhasakhetre_7186cse25@nit.edu.in', '9325262638', 'student_1763717723_6920325bb55d4.png', '$2y$10$OMujVr7kpQ0a/WHkKPHB.eJx9qHrexyeDQxCP68mm3Hd8lcF9eee.', 4, 74, 1, 1, '2025', 1, '2025-11-21 04:05:23', NULL, 0, NULL),
(505, NULL, 'BCSE-58', 'TANISH NITIN SONDOWLE', 'tanishsondowle_7148cse25@nit.edu.in', '9372182858', 'student_1763717769_692032874e61b.png', '$2y$10$zhXH16nM5arEpiWEz1TpEejxj1XjQQoinJRQLNFZiLvwfkCLr4iby', 4, 74, 1, 1, '2025', 1, '2025-11-21 04:06:09', NULL, 0, NULL),
(506, NULL, 'BCSE-59', 'TANUSHREE GANESH PANPATTE', 'tanushreepanpatte_7253cse25@nit.edu.in', '7499886745', 'student_1763717817_692032b988919.png', '$2y$10$b42Vw/LHyqCBZtgieW6/t.WaptoH25.q8HUfvseoTCZkvvl1myW0W', 4, 74, 1, 1, '2025', 1, '2025-11-21 04:06:57', NULL, 0, NULL),
(507, NULL, 'BCSE-60', 'TRISHA ANANDRAO THAMKE', 'trishathamke_7310cse25@nit.edu.in', '8698862591', 'student_1763717867_692032ebde3cd.png', '$2y$10$yrf.TrtAVadjP4HtLU2y..h2eTCpoiQeSVe79ZwInrWymLQkSxOWK', 4, 74, 1, 1, '2025', 1, '2025-11-21 04:07:47', NULL, 0, NULL),
(508, NULL, 'BCSE-61', 'VAISHNAVI UMESH MUDIRAJ', 'vaishnavimudiraj_7170cse25@nit.edu.in', '9270295043', 'student_1763717749_69203315d58eb.png', '$2y$10$CDm5nQ34gPTDl/f5uosu7O.j53TF96.1Pn/LIxhXz59nlOVYcBl.q', 4, 74, 1, 1, '2025', 1, '2025-11-21 04:08:29', NULL, 0, NULL),
(509, NULL, 'BCSE-62', 'VEDANT RAMESH BAGDE', 'vedantbagde_7242cse25@nit.edu.in', '7350128814', 'student_1763717952_69203340e684f.png', '$2y$10$VO.NCl170lSqUMTGfgIE3Og68aAFaUeK6NwNcINfnKLi85wTvNSP.', 4, 74, 1, 1, '2025', 1, '2025-11-21 04:09:12', NULL, 0, NULL),
(510, NULL, 'BCSE-63', 'VIDHI BHUVANESHWAR BASEWAR', 'vidhibasewar_7181cse25@nit.edu.in', '9923476461', 'student_1763718006_6920337628240.png', '$2y$10$7flVL8zTKCmWqTgbijTobeY6B/WLEWeV.CBKIHQ6RL3fjD6W8VDCG', 4, 74, 1, 1, '2025', 1, '2025-11-21 04:10:06', NULL, 0, NULL),
(511, NULL, 'BCSE-64', 'VIDISHA SHITAL DODKE', 'vidishadodke_7267cse25@nit.edu.in', '9552662344', 'student_1763718049_692033a19e24b.png', '$2y$10$uoBJwX95Za6F5IqHLJNg7uP6pH8NuQNNCfkjbsK.nRRq1bzt2YlZi', 4, 74, 1, 1, '2025', 1, '2025-11-21 04:10:49', NULL, 0, NULL),
(512, NULL, 'BCSE-65', 'YAMINI ANIL WASADE', 'yaminiwasade_6982cse25@nit.edu.in', '9604121249', 'student_1763718093_692033cdd1d48.png', '$2y$10$R2jc.6pEJjmMJiFa7FWbBu8bUQGVLwmShtZU5rQkVHxj3u4oxOqmu', 4, 74, 1, 1, '2025', 1, '2025-11-21 04:11:33', NULL, 0, NULL),
(514, NULL, 'BCSE-18', 'MANMEET KAUR NARENDRA POTHIWAL', 'manmeetkaur_7000cse25@nit.edu.in', '7498462870', 'student_1763718248_6920346809416.png', '$2y$10$/8zBLfzEFWaoW8msgHHx6.OXGWXHWvCqLA0q9bOhqlB9L2TNNHPWe', 4, 74, 1, 1, '2025', 1, '2025-11-21 04:14:08', NULL, 0, NULL),
(518, NULL, 'BCSE-19', 'MANTHAN RAVINDRA ARGHODE', 'mathan@gmail.com', '9545966622', 'student_1763718600_692035c80b2e1.png', '$2y$10$T7kjM4tVYdE3lMN6hkrcbuqJvGGL0JBR1DExznACc0fIyLpsYrjS6', 4, 74, 1, 1, '2025', 1, '2025-11-21 04:20:00', NULL, 0, NULL),
(513, NULL, 'BCSE-66', 'YASH KAILASH NANNAWARE', 'yashnandeshwar_7200cse25@nit.edu.in', '7447325744', 'student_1763718141_692033fd020e1.png', '$2y$10$Of0VwZv7RIfLKy30WKxpI.p9DUVRYkH.kfFlzXoK3FN/ZG5cRDdrq', 4, 74, 1, 1, '2025', 1, '2025-11-21 04:12:21', NULL, 0, NULL),
(383, NULL, 'ACSE-01', 'ABIR GANESH GUJAR', 'abirgujar_7245cse25@nit.edu.in', '9309539934', 'student_1763710518_692016364b239.png', '$2y$10$tcgL2nj52d4NYblmOFEE/OBqa4ZJ7FhkjAnJqyQUxKGPrD2N6JrFe', 4, 68, 1, 1, '2025', 1, '2025-11-21 02:05:18', NULL, 0, NULL),
(384, NULL, 'ACSE-02', 'ADIBA AFROZ HAMID SAYYED', 'adibasayyed_7219cse25@nit.edu.in', '9595912738', 'student_1763710934_692017d6d9bb0.png', '$2y$10$TK0KK6MZptebURyjdP7CGeycBkpY3SLoQjmHY/hrrnVsPonvGITtW', 4, 68, 1, 1, '2025', 1, '2025-11-21 02:12:14', NULL, 0, NULL),
(385, NULL, 'ACSE-03', 'ADITYA NIWRUTTI PATIL', 'adityapatil_7154cse25@nit.edu.in', '9322357760', 'student_1763711016_69201828919be.png', '$2y$10$ID9O3m7ALZXKFhmpWohJaeIDZnML2WUxjiIkwtXpcWaSP8vo/UVFW', 4, 68, 1, 1, '2025', 1, '2025-11-21 02:13:36', NULL, 0, NULL),
(386, NULL, 'ACSE-04', 'ALIZA PRAVEEN NAIM KHAN', 'alizakhan_7217cse25@nit.edu.in', '9322057131', 'student_1763711086_6920186e6b927.png', '$2y$10$iJXRuGVAtqF29ynkOxquQOeabFEpK3ehx4b1hy0kjte7fOqOISh9W', 4, 68, 1, 1, '2025', 1, '2025-11-21 02:14:46', NULL, 0, NULL),
(387, NULL, 'ACSE-05', 'ANIKET DINKAR MUSALE', 'aniketmusale_7146cse25@nit.edu.in', '9921317433', 'student_1763711146_692018aa99de4.png', '$2y$10$YfrdTPqkS/IR5aPt0W2FQeG.1U8B9iIsDHfq7LcdMNwTLFcNGLULO', 4, 68, 1, 1, '2025', 1, '2025-11-21 02:15:46', NULL, 0, NULL),
(388, NULL, 'ACSE-06', 'ANJALI SAMEER KIRNAKE', 'anjalikirnake_7158cse25@nit.edu.in', '7219457097', 'student_1763711242_6920190a60fa6.png', '$2y$10$GJwwZ0bpMCV/Xj20R8n1GOPpwH6gOoSLPbclO10rbaw9oBN0u4lyW', 4, 68, 1, 1, '2025', 1, '2025-11-21 02:17:22', NULL, 0, NULL),
(389, NULL, 'ACSE-07', 'ARYAN GANESH BHUTE', 'aryanbhute_7262cse25@nit.edu.in', '9272026092', 'student_1763711302_69201946eedc1.png', '$2y$10$saGT8KVplhbxuc7.j/1IuuSxDMN2d3DPFg.Ct5XS2xp3IpiGDtMTy', 4, 68, 1, 1, '2025', 1, '2025-11-21 02:18:22', NULL, 0, NULL),
(390, NULL, 'ACSE-08', 'ASHWINI JITESH GAYAKWAD', 'ashwinigayakwad_7068cse25@nit.edu.in', '7758077186', 'student_1763711393_692019a1ca95f.png', '$2y$10$NfJks.5VIoRf9vhnP4rfVeiej1.A11YJT25qB/D13RD/fut7fyHyu', 4, 68, 1, 1, '2025', 1, '2025-11-21 02:19:53', NULL, 0, NULL),
(391, NULL, 'ACSE-09', 'AYUSH NARESH SOMKUWAR', 'ayushsomkuwar_7112cse25@nit.edu.in', '9975072667', 'student_1763711453_692019ddf1ced.png', '$2y$10$ULKVAkj0SZxGk2T6aoz2BOXmnDD7ZelCLRe1lMqmIC4lzcrko117O', 4, 68, 1, 1, '2025', 1, '2025-11-21 02:20:53', NULL, 0, NULL),
(392, NULL, 'ACSE-10', 'AYUSH SUDHAKAR CHATULE', 'ayushchatule_7149cse25@nit.edu.in', '7249589689', 'student_1763711516_69201a1c5a4ea.png', '$2y$10$AVVPK5yFy5P7WmiBH8gHqeNslAoJZcT5tkbxr/XdQhAlHpVSdEkGi', 4, 68, 1, 1, '2025', 1, '2025-11-21 02:21:56', NULL, 0, NULL),
(393, NULL, 'ACSE-11', 'BHARGAV SANTOSH DESHPANDE', 'bhargavdeshpande_7022cse25@nit.edu.in', '9420902238', 'student_1763711571_69201a53bdb11.png', '$2y$10$cyBT9xIGewdFRMqG4CJCqOKk.3VgHxD0eAEHIk0jG1V27ijFhJ6/S', 4, 68, 1, 1, '2025', 1, '2025-11-21 02:22:51', NULL, 0, NULL),
(394, NULL, 'ACSE-12', 'BINTIKUMAR KANHAIYA SINGH', 'binitkumarsingh_6933cse25@nit.edu.in', '9511651722', 'student_1763711625_69201a8917c4c.png', '$2y$10$nVsiOuQ4DeRs0VODaptkWuciFmubrnI.Rr7layriqqfs7DTHHg8Ba', 4, 68, 1, 1, '2025', 1, '2025-11-21 02:23:45', NULL, 0, NULL),
(395, NULL, 'ACSE-13', 'CHAITANYA ALANKAR BHAISARE', 'chaitanyabhaisare_6938cse25@nit.edu.in', '8275013559', 'student_1763711677_69201abd678ba.png', '$2y$10$bYfE66jSorUMU8J8CdFXM.dERC2Dy3KYwl3hTMSWlfkjdHTFcJeSu', 4, 68, 1, 1, '2025', 1, '2025-11-21 02:24:37', NULL, 0, NULL),
(396, NULL, 'ACSE-14', 'DEETI KALYANI SHRINIWAS', 'deetishriniwas_7127cse25@nit.edu.in', '7498449397', 'student_1763711750_69201b06b2568.png', '$2y$10$2zewGAfjbWTp193urbCj9eJJsEhKPIN2PDgmkN7T1k6jFoVmePFTy', 4, 68, 1, 1, '2025', 1, '2025-11-21 02:25:50', NULL, 0, NULL),
(398, NULL, 'ACSE-16', 'GURPREET KAUR SWARN SINGH MINHAS', 'gurpreetkaur_7140cse25@nit.edu.in', '7391828688', 'student_1763711870_69201b7e53a1d.png', '$2y$10$44ZBVM0ee9DldNz31l1fxePCnxzx7Ct1PLyESSYK5gqVyXwembBKm', 4, 68, 1, 1, '2025', 1, '2025-11-21 02:27:50', NULL, 0, NULL),
(399, NULL, 'ACSE-15', 'DIVYA RAMDASJI KALBANDE', 'divyakalbande_7179cse25@nit.edu.in', '9322029651', 'student_1763711975_69201be70185b.png', '$2y$10$HjTKo/ZwFrDq5eeFWsNBcuuMnkg8l5hD4gXND6idIlyFz7We6qpZi', 4, 68, 1, 1, '2025', 1, '2025-11-21 02:29:35', NULL, 0, NULL),
(400, NULL, 'ACSE-17', 'HARSH ANIL CHAVHAN', 'harshchavhan_7046cse25@nit.edu.in', '9579255257', 'student_1763712032_69201c20c215a.png', '$2y$10$4mN3m9I8wpXBDIIvt5yCc.5pe6p.eXIfOQmBD6/eWI9zwxTr0oxYq', 4, 68, 1, 1, '2025', 1, '2025-11-21 02:30:32', NULL, 0, NULL),
(401, NULL, 'ACSE-18', 'JANHAVI VINOD SAWANT', 'janhavisawant_6995cse25@nit.edu.in', '9370695870', 'student_1763712101_69201c65c7f7b.png', '$2y$10$Cj5oldNxrbEPUMgogtExNeZPmzd.gpjXA1dbKU4stPLGWOtsB2eb6', 4, 68, 1, 1, '2025', 1, '2025-11-21 02:31:41', NULL, 0, NULL),
(402, NULL, 'ACSE-19', 'JANKI JAYANT PATHAK', 'jankipathak_7036cse25@nit.edu.in', '7249238153', 'student_1763712157_69201c9d38e41.png', '$2y$10$TeaRqISzDLwxezXIi5jTiOhRLYUuaNXiudq0zSsJ8vfss9p9HTI0G', 4, 68, 1, 1, '2025', 1, '2025-11-21 02:32:37', NULL, 0, NULL),
(403, NULL, 'ACSE-20', 'JATAN GAJANAN JAMBHULKAR', 'jatanjambhulkar_6955cse25@nit.edu.in', '7775011793', 'student_1763712212_69201cd4dfdfc.png', '$2y$10$cOnJWdPJarZEhDs17vnxL.McbdLej1zd4h63Dn39b1p06dir3JXa2', 4, 68, 1, 1, '2025', 1, '2025-11-21 02:33:32', NULL, 0, NULL),
(404, NULL, 'ACSE-21', 'KOMAL RAVINDRA SARODE', 'komalsarode_7213cse25@nit.edu.in', '9152761350', 'student_1763712286_69201d1edc723.png', '$2y$10$qIBQS/Yikiz1inMlh9Zm8.fnPnNZahDhUSFkDqkdA5RYKNDqE.ENW', 4, 68, 1, 1, '2025', 1, '2025-11-21 02:34:46', NULL, 0, NULL),
(405, NULL, 'ACSE-22', 'KUNAL YOGIRAJ DESHMUKH', 'kunaldeshmukh_7092cse25@nit.edu.in', '7262050011', 'student_1763712343_69201d57103b8.png', '$2y$10$ohbMWVcfSYsESe/G8THh6upJd8xplY24PZWR6zeOpIJe8Jx9oY4ra', 4, 68, 1, 1, '2025', 1, '2025-11-21 02:35:43', NULL, 0, NULL),
(406, NULL, 'ACSE-23', 'MADHURA SURESH GAJBHIYE', 'madhuragajbhiye_7223cse25@nit.edu.in', '9657874676', 'student_1763712397_69201d8d46230.png', '$2y$10$VDc.8SBFsFb2XgaaRc6ev.iyUc9D03PHdYLPMQxrngX5hV/fCJeUW', 4, 68, 1, 1, '2025', 1, '2025-11-21 02:36:37', NULL, 0, NULL),
(407, NULL, 'ACSE-24', 'MAHEK VASUDEO DEKATE', 'mahekdekate_7176cse25@nit.edu.in', '9226180160', 'student_1763712444_69201dbc82d17.png', '$2y$10$yM1anTffQOWJtzNHG6f9IO7w.70YH/eSYZiQR22M9tjKH6.YOqYRe', 4, 68, 1, 1, '2025', 1, '2025-11-21 02:37:24', NULL, 0, NULL),
(408, NULL, 'ACSE-25', 'MAHESH MADAN WAGH', 'maheshwagh_6951cse25@nit.edu.in', '9503919506', 'student_1763712508_69201dfc04af1.png', '$2y$10$MSS9wvk9L.8iBOf9H8dqz.3Pnv84eaN9dZtp9YZcK6iI/mc2YZWw.', 4, 68, 1, 1, '2025', 1, '2025-11-21 02:38:28', NULL, 0, NULL),
(409, NULL, 'ACSE-26', 'MAISA VINOD KALMEGH', 'maisakalmegh_7169cse25@nit.edu.in', '7276129425', 'student_1763712568_69201e385fb63.png', '$2y$10$YpRI4eRywOpl3/dle5ewQeP4L6XIyA4dchXUlKbjxKqJkOa.y6Nwq', 4, 68, 1, 1, '2025', 1, '2025-11-21 02:39:28', NULL, 0, NULL),
(410, NULL, 'ACSE-27', 'MANASHRI VIJAY PUND', 'manashripund_7172cse25@nit.edu.in', '9579516987', 'student_1763712611_69201e63e4cc4.png', '$2y$10$qXkQm7DstEQvqkosIDqAG.d1F8TPzuOjhEq.O6/Eb0mdLqzFOEVF.', 4, 68, 1, 1, '2025', 1, '2025-11-21 02:40:11', NULL, 0, NULL),
(411, NULL, 'ACSE-28', 'MANDAR SANTOSH BANGALE', 'mandarbangale_6968cse25@nit.edu.in', '8390817925', 'student_1763712658_69201e92828c0.png', '$2y$10$eYXB9.114fqKa.B9CiWwiOui6DFhG0hujzwUfbNSMitvAJ1/cw3/.', 4, 68, 1, 1, '2025', 1, '2025-11-21 02:40:58', NULL, 0, NULL),
(412, NULL, 'ACSE-29', 'MANTASHA NOOR MOBEEN KAMAL', 'manthanarghode_7252cse25@nit.edu.in', '8668610316', 'student_1763712881_69201f7179250.png', '$2y$10$WY.yP4c0Op5wp/KHhadSE.2vUVbOK98MizCD5BuizowYLSumH8iDe', 4, 68, 1, 1, '2025', 1, '2025-11-21 02:44:41', NULL, 0, NULL),
(413, NULL, 'ACSE-30', 'MAYANK SHAM YADAV', 'mayankyadav_6961cse25@nit.edu.in', '9860401784', 'student_1763712934_69201fa658c56.png', '$2y$10$ngpYJPFaPgG.hvm5YQaxnujDpn1XzCA9kyg07/EMzkcULGyZ01DN6', 4, 68, 1, 1, '2025', 1, '2025-11-21 02:45:34', NULL, 0, NULL),
(414, NULL, 'ACSE-31', 'NIKITA PRABHAKAR RAUT', 'nikitaraut_7259cse25@nit.edu.in', '8429830427', 'student_1763713017_69201ff9b761d.png', '$2y$10$nKtUZDjYPD1jYkBLZmljTu3DoF.gaBC/jh1dLAaBsoarylwjqkXQC', 4, 68, 1, 1, '2025', 1, '2025-11-21 02:46:57', NULL, 0, NULL),
(415, NULL, 'ACSE-32', 'NILIMA NIRANJANDAS CHAURE', 'nilimachaure_7029cse25@nit.edu.in', '9545301600', 'student_1763713062_6920202675289.png', '$2y$10$rsJNDkYe2x2fb8JKksqX7.fivP0OizVYaXGaBmKUmDysimesTN64W', 4, 68, 1, 1, '2025', 1, '2025-11-21 02:47:42', NULL, 0, NULL),
(416, NULL, 'ACSE-33', 'NISHIKA SANDIP JICHKAR', 'nishikajichkar_7090cse25@nit.edu.in', '9970377128', 'student_1763713110_692020567869e.png', '$2y$10$/KwGFsy6YV5U9rfLjYMGDOuOiMu0TT0PRfL3XNUfg3/hkz1bdSLU6', 4, 68, 1, 1, '2025', 1, '2025-11-21 02:48:30', NULL, 0, NULL),
(417, NULL, 'ACSE-34', 'OM VISHNU PATIL', 'ompatil_7069cse25@nit.edu.in', '7020796534', 'student_1763713156_6920208457c67.png', '$2y$10$NUkmPAlaLIuzmdkmBgPtv.6aB9SII1q4UcncefxCVjx5wFQiklUcm', 4, 68, 1, 1, '2025', 1, '2025-11-21 02:49:16', NULL, 0, NULL),
(418, NULL, 'ACSE-35', 'PARI VIJAY WANDHARE', 'pariwandhare_7190cse25@nit.edu.in', '9511794547', 'student_1763713197_692020ade27fa.png', '$2y$10$aYuZGClH.4O.0qY7n2CoHejRKEr5JQqq0DZrH.tcC.rRdSGtlxaBa', 4, 68, 1, 1, '2025', 1, '2025-11-21 02:49:57', NULL, 0, NULL),
(419, NULL, 'ACSE-36', 'PARTH AVINASH THAKRE', 'parththakre_7002cse25@nit.edu.in', '8237890176', 'student_1763713241_692020d9dcfd9.png', '$2y$10$s5r1H2qAphx42bkpoqYCaOtIVwnFjBS0d7SelaLuIzhbU/9.U9RAa', 4, 68, 1, 1, '2025', 1, '2025-11-21 02:50:41', NULL, 0, NULL),
(420, NULL, 'ACSE-37', 'PAYAL GAJANAN SALODE', 'payalsalode_7292cse25@nit.edu.in', '7709576195', 'student_1763713306_6920211a4365f.png', '$2y$10$SBdF9bFX7EiV58r1z2ifPeCv8QgkU5lj7Nf4M4gNECzX.HxPA5be2', 4, 68, 1, 1, '2025', 1, '2025-11-21 02:51:46', NULL, 0, NULL),
(421, NULL, 'ACSE-38', 'PRAJAKTA SUNIL BOPULKAR', 'prajaktabopulkar_7078cse25@nit.edu.in', '7448075839', 'student_1763713353_69202149daec7.png', '$2y$10$aIet1nbZYtWK3XV38NhqkeUuCZG.Fm84EvJBIaJjbeuzmGkrkwBIu', 4, 68, 1, 1, '2025', 1, '2025-11-21 02:52:33', NULL, 0, NULL),
(422, NULL, 'ACSE-39', 'PRATHAMESH VINOD DEHANKAR', 'prathameshdehankar_7312cse25@nit.edu.in', '9322549076', 'student_1763713404_6920217c0129d.png', '$2y$10$H./YyhIYk4XxdVJ8Vn1Cje5BhVTMpXUtAT7Jv0PC50I6IR.tlmwHy', 4, 68, 1, 1, '2025', 1, '2025-11-21 02:53:24', NULL, 0, NULL),
(423, NULL, 'ACSE-40', 'PREM CHOPRAM ZINGARE', 'premzingare_7163cse25@nit.edu.in', '8806457120', 'student_1763713439_6920219f2cdbe.png', '$2y$10$bTxRzIwIQ5wToOYFHZQHSOEqdeog0dv6cxrMlK0rgvKyDNEux3GhW', 4, 68, 1, 1, '2025', 1, '2025-11-21 02:53:59', NULL, 0, NULL),
(424, NULL, 'ACSE-41', 'PRIME LILADHAR SAMRIT', 'primesamrit_7195cse25@nit.edu.in', '9359142015', 'student_1763713474_692021c2995f9.png', '$2y$10$Tau.n7aJLJYYI.vNF4i/EOALHpf0Gxdlqmr5WuiQH6caI0YWSyCai', 4, 68, 1, 1, '2025', 1, '2025-11-21 02:54:34', NULL, 0, NULL),
(425, NULL, 'ACSE-42', 'PRIYANKA BANDU NINAVE', 'priyankaninave_7123cse25@nit.edu.in', '9960854125', 'student_1763713525_692021f56ca75.png', '$2y$10$RVIHUYOh7lP4mq4gL.n0sOYpZaWadiL8GWsl25zxWgRiGzCgrnYju', 4, 68, 1, 1, '2025', 1, '2025-11-21 02:55:25', NULL, 0, NULL),
(426, NULL, 'ACSE-43', 'RAJVEER CHANDRABHAN GUPTA', 'rajveergupta_7236cse25@nit.edu.in', '8806571042', 'student_1763713571_69202223de69f.png', '$2y$10$NbHZXSmRMJmEk.7oJHtwfuYrOk6I82O2D9vHzgEJGuTcXlRywJpyS', 4, 68, 1, 1, '2025', 1, '2025-11-21 02:56:11', NULL, 0, NULL),
(427, NULL, 'ACSE-44', 'RUDRA RAJESH NINAWE', 'rudraninawe_7270cse25@nit.edu.in', '8010510174', 'student_1763713616_692022506c936.png', '$2y$10$j0JSYyxMXp6KTpnc6jlife3fVNI13rwtZDQgXWMxi4RLFVFY3m7Xi', 4, 68, 1, 1, '2025', 1, '2025-11-21 02:56:56', NULL, 0, NULL),
(428, NULL, 'ACSE-45', 'SAIRAM SHIVAJI PALLEKONDWAD', 'sairampallekondwad_7037cse25@nit.edu.in', '9021635659', 'student_1763713655_69202277ca5c7.png', '$2y$10$Wfu0OhNgkfayao9WEM9WHuDUceBOF0NLcQGWmpP5LqCX4Ac9U/oLW', 4, 68, 1, 1, '2025', 1, '2025-11-21 02:57:35', NULL, 0, NULL),
(429, NULL, 'ACSE-46', 'SAKSHI DILIP KOLHE', 'sakshikolhe_7110cse25@nit.edu.in', '9890126481', 'student_1763713697_692022a1ca6d7.png', '$2y$10$yPjeKIRv7RhRQTv7Rn9Fae1ABTfFIewQ3QcOvN2OUjmAFdut7A.um', 4, 68, 1, 1, '2025', 1, '2025-11-21 02:58:17', NULL, 0, NULL),
(430, NULL, 'ACSE-47', 'SAMYAK MUNNESHWAR NAGRARE', 'samyaknagrare_7279cse25@nit.edu.in', '9561867658', 'student_1763713763_692022e3adba0.png', '$2y$10$Kxc57q4d268J/tngSBbvRuuFr1trIK.BvEHPtI7vp6UiJ9PJwFBzu', 4, 68, 1, 1, '2025', 1, '2025-11-21 02:59:23', NULL, 0, NULL),
(431, NULL, 'ACSE-48', 'SANSKRUTI SANJAY RATHOD', 'sanskrutirathod_6957cse25@nit.edu.in', '9699407741', 'student_1763713818_6920231a404a3.png', '$2y$10$pMK8FENbnR510cl3JuJ5EeONsgIT57IEvg/ZAHrfne0SvjZX0PlQa', 4, 68, 1, 1, '2025', 1, '2025-11-21 03:00:18', NULL, 0, NULL),
(432, NULL, 'ACSE-49', 'SAYALI NANDKISHOR JUGSENIYA', 'sayalijugseniya_7207cse25@nit.edu.in', '7264062612', 'student_1763713855_6920233fba2fc.png', '$2y$10$x6Gp4.hU/QH/JvNh08zpJ.HHag2Dmq1og1cQrc7Rq99wTBrq3bYLG', 4, 68, 1, 1, '2025', 1, '2025-11-21 03:00:55', NULL, 0, NULL),
(433, NULL, 'ACSE-50', 'SEJAL ROSHAN RAMTEKE', 'sejalramteke_7155cse25@nit.edu.in', '7822018764', 'student_1763713891_69202363c1a95.png', '$2y$10$qyakSaQoksjrP2M2lwLzXe7qhvG3nMLwO4AN0.v7m482XZ7/AbuJC', 4, 68, 1, 1, '2025', 1, '2025-11-21 03:01:31', NULL, 0, NULL),
(434, NULL, 'ACSE-51', 'SHIVAM MANOJSINGH CHAVHAN', 'shivamchavhan_6994cse25@nit.edu.in', '8817183868', 'student_1763713935_6920238f0ef8c.png', '$2y$10$mqoAMJWE6Byo4.EWtXTx7OclTws9QWuWH7C20qnbJeHkK37ek9Jui', 4, 68, 1, 1, '2025', 1, '2025-11-21 03:02:15', NULL, 0, NULL),
(435, NULL, 'ACSE-52', 'SHRADDHA HEMRAJ DONGARE', 'shraddhadongare_7308cse25@nit.edu.in', '9112604357', 'student_1763713981_692023bd27ac9.png', '$2y$10$kODqsh6rD30vYEVgJp3qp.kcFAN.3JB12mnxvD5x2BD53bGy3T8Su', 4, 68, 1, 1, '2025', 1, '2025-11-21 03:03:01', NULL, 0, NULL),
(436, NULL, 'ACSE-53', 'SONALI ARVIND RAMTEKE', 'sonaliramteke_7016cse25@nit.edu.in', '9579556254', 'student_1763714024_692023e87f488.png', '$2y$10$FQaio43WUW4yBiCekhGel.OUYOGy8tOHbB94KxTy.iymrj6cCmYLW', 4, 68, 1, 1, '2025', 1, '2025-11-21 03:03:44', NULL, 0, NULL),
(437, NULL, 'ACSE-54', 'TANISHKA SUNIL BORKAR', 'tanishkaborkar_7226cse25@nit.edu.in', '8275640574', 'student_1763714082_69202422f1b95.png', '$2y$10$hMUvVxkBTklqWED8miDSyeKT8diCw9EjP6cgM7aoxAVChVLECp0xe', 4, 68, 1, 1, '2025', 1, '2025-11-21 03:04:42', NULL, 0, NULL),
(438, NULL, 'ACSE-55', 'TANMAY SHARAD KAUTKAR', 'tanmaykautkar_7294cse25@nit.edu.in', '7498834855', 'student_1763714131_6920245322c21.png', '$2y$10$7zi8MPDTIxu5xvMS7shxw.vDXogtWPzl66Zhdo4lPxPEvw2T30tfq', 4, 68, 1, 1, '2025', 1, '2025-11-21 03:05:31', NULL, 0, NULL),
(439, NULL, 'ACSE-56', 'TANUJA DNYANESHWAR NARINGE', 'tanujanaringe_6950cse25@nit.edu.in', '9823532604', 'student_1763714185_6920248957fcb.png', '$2y$10$IRNtJUIb5WcUq5tS4Q6/4esB8t93Y3dNnA5nDM.uIvI09qMhNT0Gq', 4, 68, 1, 1, '2025', 1, '2025-11-21 03:06:25', NULL, 0, NULL),
(440, NULL, 'ACSE-57', 'TASMIYA NAUSHAD PATHAN', 'tasmiyapathan_6953cse25@nit.edu.in', '7038725948', 'student_1763714226_692024b26c171.png', '$2y$10$w45x8NsreW9vjUE/1RPHHuMVtOzln3OgQu0o.MdcQOK4cDB1SjpfC', 4, 68, 1, 1, '2025', 1, '2025-11-21 03:07:06', NULL, 0, NULL),
(441, NULL, 'ACSE-58', 'TEJAS DEEPAK KACHHAWAH', 'tejaskachhawah_7074cse25@nit.edu.in', '9699362131', 'student_1763714297_692024f9f26a2.png', '$2y$10$9kS6/RElMOBMiqKmRSY6h.BUs4uDdHhVq.1mg0FUM6sXGJlrePW1K', 4, 68, 1, 1, '2025', 1, '2025-11-21 03:08:17', NULL, 0, NULL);
INSERT INTO `students` (`id`, `user_id`, `roll_number`, `full_name`, `email`, `phone`, `photo`, `password`, `department_id`, `class_id`, `year`, `semester`, `admission_year`, `is_active`, `created_at`, `last_login`, `failed_login_attempts`, `account_locked_until`) VALUES
(442, NULL, 'ACSE-59', 'TEJAS SANTOSH SAHARE', 'tejassahare_6944cse25@nit.edu.in', '7028037890', 'student_1763714337_69202521f2445.png', '$2y$10$VPz0JLLVgIntK9tSPpBsYOsKBoHu5qfSxNLAiU7SXOBu.feITqA6W', 4, 66, 1, 1, '2025', 1, '2025-11-21 03:08:57', NULL, 0, NULL),
(443, NULL, 'ACSE-60', 'TULSI SANTOSH MESHRAM', 'tulsimeshram_6984cse25@nit.edu.in', '7264965211', 'student_1763714397_6920255d90ecc.png', '$2y$10$j58SjJLos.YRvawt6d/DU.ig6ymfubAGx7.8AKNC/U475hhTsH3Hq', 4, 68, 1, 1, '2025', 1, '2025-11-21 03:09:57', NULL, 0, NULL),
(444, NULL, 'ACSE-61', 'VEDANSHREE GAJANAN SAWAI', 'vedanshreesawai_6934cse25@nit.edu.in', '8956420803', 'student_1763714442_6920258a67717.png', '$2y$10$sCfgqbJjFnWxA0uxiMSgV.1H2f5goAlKltwO1d11e3d8MpBQVHb3W', 4, 68, 1, 1, '2025', 1, '2025-11-21 03:10:42', NULL, 0, NULL),
(445, NULL, 'ACSE-62', 'VEDANT SUNIL PAWAR', 'vedantpawar_7056cse25@nit.edu.in', '8459394586', 'student_1763714504_692025c85b8f9.png', '$2y$10$SitgMey/DKb/S5V3Ly0i2.eBm4dGA1B3qFllnNzzfITAwlqkopZvG', 4, 68, 1, 1, '2025', 1, '2025-11-21 03:11:44', NULL, 0, NULL),
(446, NULL, 'ACSE-63', 'VIDHI PRAVIN TUPKAR', 'vidhitupkar_7266cse25@nit.edu.in', '9403349477', 'student_1763714551_692025f7510fc.png', '$2y$10$9mgeSvCbNhXrbEeKmvtVXeFIcSJXOKnjijoc7dBYYxqYsvFk9K02y', 4, 68, 1, 1, '2025', 1, '2025-11-21 03:12:31', NULL, 0, NULL),
(447, NULL, 'ACSE-64', 'VISHESH SANTOSH BANGRE', 'visheshbangre_7166cse25@nit.edu.in', '9322090576', 'student_1763714625_692026417cff1.png', '$2y$10$WcIZiPgDK35PmIYt3yvbLendELao4Mh3PaYZlmCazyomxGpm6dugW', 4, 68, 1, 1, '2025', 1, '2025-11-21 03:13:45', NULL, 0, NULL),
(448, NULL, 'ACSE-65', 'VISHWARI RAVINDRA CHINCHMALATPURE', 'vishwarichinchmalatpure_7296cse25@nit.edu.in', '7875336342', 'student_1763714680_69202678553a3.png', '$2y$10$xPVfwvr6s/iMsa8VVT5SPOUJ6KDjW5aOzAvxIwqQLwb08DGRkFQQS', 4, 68, 1, 1, '2025', 1, '2025-11-21 03:14:40', NULL, 0, NULL),
(449, NULL, 'ACSE-66', 'YASH KUNDLIK NANDESHWAR', 'yashnannaware_7273cse25@nit.edu.in', '9579849311', 'student_1763714724_692026a496fae.png', '$2y$10$f/8Q8dct54rU.0axBbNeGeRZ9oj2chwX2G1NrTa8mzZH2ke5i.6IC', 4, 68, 1, 1, '2025', 1, '2025-11-21 03:15:24', NULL, 0, NULL),
(13733, NULL, 'CE-49', 'ANSHIKA SANTOSH KUMAR NAGDEVE', 'anshikanagdeve_7072it25@nit.edu.in', '9623957788', 'student_1763384871_691b1e2726538.jpeg', '$2y$10$ZFAF0KmRc7zn9kgM3eZRX..BvKMrdMpEA4SuoMB03GY3.Kxn11Yem', 4, 53, 1, 1, '2025', 1, '2025-11-17 07:37:51', NULL, 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `student_attendance`
--

CREATE TABLE `student_attendance` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `attendance_date` date NOT NULL,
  `status` enum('present','absent','late') NOT NULL,
  `remarks` text DEFAULT NULL,
  `marked_by` int(11) NOT NULL,
  `marked_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_file_access`
--

CREATE TABLE `student_file_access` (
  `id` int(11) NOT NULL,
  `file_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `access_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `download_count` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_notifications`
--

CREATE TABLE `student_notifications` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `notification_date` date NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_resumes`
--

CREATE TABLE `student_resumes` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `objective` text DEFAULT NULL,
  `skills` text DEFAULT NULL,
  `education` text DEFAULT NULL,
  `experience` text DEFAULT NULL,
  `projects` text DEFAULT NULL,
  `certifications` text DEFAULT NULL,
  `languages` varchar(255) DEFAULT NULL,
  `hobbies` varchar(255) DEFAULT NULL,
  `theme` enum('professional','modern','creative','minimal') DEFAULT 'professional',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_resumes`
--

INSERT INTO `student_resumes` (`id`, `student_id`, `objective`, `skills`, `education`, `experience`, `projects`, `certifications`, `languages`, `hobbies`, `theme`, `created_at`, `updated_at`) VALUES
(1, 149, 'To secure a responsible and growth-oriented position where I can utilize my skills in Web Development and backend technologies to contribute effectively to organizational goals, while gaining valuable experience and insights that will help me establish my own software company and manage large-scale, impactful software projects in the future.', 'Frontend Development: HTML CSS JavaScript , Backend & Databases: PHP  Node.js SQL,  Programming Languages: C  Python,  UI/UX & Design Tools: Figma, Android UI Design, iOS UI Design', 'Bachelor of Technology (B.Tech) — [NIT]\r\nYear: 2025\r\nHigher Secondary Education (12th) — [Concept School]\r\nYear: 2024-25\r\nSecondary School (10th) — [TIPS Katol]\r\nYear: 2023-24', '2 year experience\r\nWorked on web development and programming tasks, contributing to real-time project modules.', '🚀 Projects\r\n1. Web Development Project\r\nTechnologies: HTML, CSS, JavaScript, PHP\r\nDescription: Developed a fully responsive and dynamic website featuring user authentication, interactive forms, and real-time content updates. Implemented secure login, optimized UI/UX, and improved overall performance.\r\n2. Website Design Project\r\nTechnologies: HTML, CSS, UI/UX Design\r\nDescription: Designed a modern and visually appealing website layout with focus on user experience, color harmony, responsive structure, and intuitive navigation.\r\n3. Software Design Project\r\nTechnologies: Java / Python / Any tool you used\r\nDescription: Designed a software solution from planning to prototype stage, including system flow, user requirements, interface design, and basic functionality planning.', 'I am beyond excited to share that I have successfully completed HTML, CSS, JavaScript, QA, and SQL, and have officially earned my certification! This journey has been a truly enriching experience, allowing me to develop new skills, overcome challenges, and grow both technically and professionally.\r\nReceiving this certificate is a moment of immense joy and pride—it reflects my dedication, perseverance, and passion for learning! \r\nA heartfelt thank you to Enjoy Programming for their incredible support, insightful guidance, and motivation throughout this learning journey. Their expertise has been instrumental in my growth, and I am truly grateful.\r\nThis achievement is just the beginning! I am eager to put my knowledge into action, take on new challenges, and continue evolving in the dynamic world of web development! \r\nhashtag#MilestoneAchieved hashtag#CertifiedDeveloper hashtag#Grateful hashtag#KeepLearning hashtag#ExcitedForTheFuture', 'English, Hindi, Marathi', 'Coding and exploring new technologies  Designing software and creating user-friendly interfaces  Web design and creative layout work  Problem-solving and logical thinking activities  Learning new programming skills and tools', 'professional', '2025-12-02 07:33:28', '2025-12-03 04:40:37'),
(3, 163, 'To secure a responsible and growth-oriented position where I can utilize my skills in Web Development, to contribute effectively to organizational goals while continuously improving my professional knowledge.', 'HTML,CSS,JS ,C', 'Bachelor of Technology (B.Tech) — [NIT]\r\nYear:2025\r\nHigher Secondary Education (12th) — [Concept School]\r\nYear:2023-25\r\nSecondary School (10th) — [AIPS]\r\nYear: 2023', '1 Year Experience\r\nWorked on web development and programming tasks, contributing to real-time project modules.', 'Web Development Project\r\nTechnologies: HTML, CSS, JavaScript\r\nDescription: Developed a responsive website', 'Web Development Certification', 'English, Hindi, Marathi', 'Reading, Coding, Sports', 'creative', '2025-12-02 12:20:56', '2025-12-02 12:21:21');

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `id` int(11) NOT NULL,
  `subject_name` varchar(100) NOT NULL,
  `subject_code` varchar(20) NOT NULL,
  `department_id` int(11) DEFAULT NULL,
  `year` int(11) NOT NULL DEFAULT 1,
  `semester` int(11) NOT NULL,
  `credits` int(11) DEFAULT 3,
  `total_marks` int(11) DEFAULT 100,
  `passing_marks` int(11) DEFAULT 40,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`id`, `subject_name`, `subject_code`, `department_id`, `year`, `semester`, `credits`, `total_marks`, `passing_marks`, `is_active`, `created_at`) VALUES
(1, 'Engineering Mathematics - I', 'MA101', NULL, 1, 1, 4, 100, 40, 1, '2025-11-29 16:28:18'),
(2, 'Engineering Physics', 'PH101', NULL, 1, 1, 3, 100, 40, 1, '2025-11-29 16:28:18'),
(3, 'Engineering Chemistry', 'CH101', NULL, 1, 1, 3, 100, 40, 1, '2025-11-29 16:28:18'),
(4, 'Engineering Mechanics', 'ME101', NULL, 1, 1, 3, 100, 40, 1, '2025-11-29 16:28:18'),
(5, 'Basic Electrical Engineering', 'EE101', NULL, 1, 1, 3, 100, 40, 1, '2025-11-29 16:28:18'),
(6, 'Programming in C', 'CS101', NULL, 1, 1, 3, 100, 40, 1, '2025-11-29 16:28:18'),
(7, 'Engineering Mathematics - II', 'MA102', NULL, 1, 2, 4, 100, 40, 1, '2025-11-29 16:28:18'),
(8, 'Engineering Physics Lab', 'PH102', NULL, 1, 2, 2, 100, 40, 1, '2025-11-29 16:28:18'),
(9, 'Engineering Chemistry Lab', 'CH102', NULL, 1, 2, 2, 100, 40, 1, '2025-11-29 16:28:18'),
(10, 'Engineering Drawing', 'ME102', NULL, 1, 2, 3, 100, 40, 1, '2025-11-29 16:28:18'),
(11, 'Electronic Devices & Circuits', 'EE102', NULL, 1, 2, 3, 100, 40, 1, '2025-11-29 16:28:18'),
(12, 'Data Structures', 'CS102', NULL, 1, 2, 3, 100, 40, 1, '2025-11-29 16:28:18'),
(13, 'Engineering Mathematics - I', 'MA101', NULL, 1, 1, 4, 100, 40, 1, '2025-11-30 04:07:22'),
(14, 'Engineering Physics', 'PH101', NULL, 1, 1, 3, 100, 40, 1, '2025-11-30 04:07:22'),
(15, 'Engineering Chemistry', 'CH101', NULL, 1, 1, 3, 100, 40, 1, '2025-11-30 04:07:22'),
(16, 'Engineering Mechanics', 'ME101', NULL, 1, 1, 3, 100, 40, 1, '2025-11-30 04:07:22'),
(17, 'Basic Electrical Engineering', 'EE101', NULL, 1, 1, 3, 100, 40, 1, '2025-11-30 04:07:22'),
(18, 'Programming in C', 'CS101', NULL, 1, 1, 3, 100, 40, 1, '2025-11-30 04:07:22'),
(19, 'Engineering Mathematics - II', 'MA102', NULL, 1, 2, 4, 100, 40, 1, '2025-11-30 04:07:22'),
(20, 'Engineering Physics Lab', 'PH102', NULL, 1, 2, 2, 100, 40, 1, '2025-11-30 04:07:22'),
(21, 'Engineering Chemistry Lab', 'CH102', NULL, 1, 2, 2, 100, 40, 1, '2025-11-30 04:07:22'),
(22, 'Engineering Drawing', 'ME102', NULL, 1, 2, 3, 100, 40, 1, '2025-11-30 04:07:22'),
(23, 'Electronic Devices & Circuits', 'EE102', NULL, 1, 2, 3, 100, 40, 1, '2025-11-30 04:07:22'),
(24, 'Data Structures', 'CS102', NULL, 1, 2, 3, 100, 40, 1, '2025-11-30 04:07:22');

-- --------------------------------------------------------

--
-- Table structure for table `subject_teachers`
--

CREATE TABLE `subject_teachers` (
  `id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `section` varchar(50) NOT NULL,
  `year` int(11) NOT NULL,
  `semester` int(11) NOT NULL,
  `academic_year` varchar(20) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subject_teachers`
--

INSERT INTO `subject_teachers` (`id`, `subject_id`, `teacher_id`, `section`, `year`, `semester`, `academic_year`, `is_active`, `created_at`) VALUES
(6, 3, 51, 'IT', 1, 1, '2025-2026', 1, '2025-11-30 04:20:30'),
(7, 17, 55, 'IT', 1, 1, '2025-2026', 1, '2025-11-30 04:32:30'),
(8, 18, 54, 'Civil', 1, 1, '2025-2026', 1, '2025-11-30 04:33:03');

-- --------------------------------------------------------

--
-- Table structure for table `system_health`
--

CREATE TABLE `system_health` (
  `id` int(11) NOT NULL,
  `status` varchar(50) DEFAULT NULL,
  `memory_usage` float DEFAULT NULL,
  `database_size` bigint(20) DEFAULT NULL,
  `active_users` int(11) DEFAULT NULL,
  `timestamp` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `teacher_assignments`
--

CREATE TABLE `teacher_assignments` (
  `id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `subject_id` int(11) DEFAULT NULL,
  `academic_year` varchar(10) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `teacher_files`
--

CREATE TABLE `teacher_files` (
  `id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `class_id` int(11) DEFAULT NULL,
  `subject` varchar(100) NOT NULL,
  `file_type` varchar(50) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `description` longtext DEFAULT NULL,
  `upload_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `downloads` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `role` enum('admin','hod','teacher','student','parent','superadmin') NOT NULL,
  `department_id` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_login` datetime DEFAULT NULL,
  `failed_login_attempts` int(11) DEFAULT 0,
  `account_locked_until` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `full_name`, `email`, `phone`, `photo`, `role`, `department_id`, `is_active`, `created_at`, `last_login`, `failed_login_attempts`, `account_locked_until`) VALUES
(1, 'admin', '$2b$12$8l9C/DEJ2SD.dCvdj1hwL.OHYx0I15L39JEJxwUkKXklU2r.hKVHG', ' Admin', 'admin@nitcollege.edu', NULL, NULL, 'admin', NULL, 1, '2025-11-15 17:17:27', '2025-12-06 00:48:31', 0, NULL),
(15, 'FY_HOD', '$2b$12$oRzxMDZGfViVcvo62E1u3uIPsHAuYj7EuiHOc5/s/ZhRHNMjxllw2', 'Dr. Jitendra Bhaiswar', 'jbbhaiswar@nit.edu.in', '8007673735', 'hod_15_1763297436.jpg', 'hod', 4, 1, '2025-11-14 14:37:59', '2025-12-05 16:48:31', 0, NULL),
(22, 'Dmeghe', '$2b$12$dtNCGYuTEk9iHHkkWxhO4ObG9k4MOj5ZZfBnwCvmvuwj0RbHpihpq', 'Mr. Dhiraj Meghe', 'dpmeghe@nit.edu.in', '9923329483', NULL, 'teacher', 4, 1, '2025-11-14 15:41:41', '2025-12-05 17:11:55', 0, NULL),
(23, 'Pdange', '$2b$12$vJt9FgE.91eklBXN9EJkb.O23q2m3GO.CxrguO3NDCuyG2.lK43LO', 'Mr. Prashant Dange', 'pddange@nit.edu.in', '9881244183', NULL, 'teacher', 4, 1, '2025-11-14 15:43:09', '2025-12-05 17:19:45', 0, NULL),
(24, 'Skochhar', '$2b$12$UglyBCKmCA0eUUdwy7/KzuWpdnrNxxQrnkLhiyw5C6u.CgqqAyEVa', 'Dr. (Mrs.) Sonika Kochhar', 'srkochhar@nit.edu.in', '9011856565', NULL, 'teacher', 4, 1, '2025-11-14 15:44:05', '2025-12-05 17:42:24', 0, NULL),
(25, 'Mdange', '$2y$10$NLsSRzVRIi60bRPzzVP1teBiCSg3dfKrar6Jg1e6Sy7OEC0GclvVS', 'Mrs. Mona Dange', 'mpdange@nit.edu.in', '9850064955', NULL, 'teacher', 4, 1, '2025-11-14 15:44:51', '2025-12-05 22:56:39', 0, NULL),
(26, 'Msabir', '$2y$10$AR3DcVxrcEdmwcb1m5GTQ.YuvQ.iLMxtsloLFdRN9FMPl537b0DqC', 'Dr. Mohammad Sabir', 'mmsabir@nit.edu.in', '9850671525', NULL, 'teacher', 4, 1, '2025-11-14 15:46:29', NULL, 0, NULL),
(27, 'Asheikh', '$2y$10$wEo8cGEnb452f8u7c6HwF.30MqqmUs7viSUxOqAy0p.L5ndyvDI/W', 'Mr. Ayaz Sheikh', 'sheikhayaz@nit.edu.in', '9834804020', NULL, 'teacher', 4, 1, '2025-11-14 15:48:28', NULL, 0, NULL),
(28, 'Rdaga', '$2y$10$r9770j12.lAIrGIveybCA.tsDTKKuLQegHtX1E9PsWw4c3gdfa876', 'Mrs Rachna Daga', 'dagarachna@nit.edu.in', '8766775204', NULL, 'teacher', 4, 1, '2025-11-15 04:10:48', NULL, 0, NULL),
(29, 'pbhuyar', '$2y$10$jSkCd9856MBSHimJG1KkouTMEMIHSN9wJljXwDkgh66iEEYI9hYUm', 'Ms. Pournima Bhuyar', 'bhuyarpournima@nit.edu.in', '8668573942', 'uploads/photos/user_29_1763383774.jpeg', 'teacher', 4, 1, '2025-11-15 04:12:17', '2025-12-05 23:40:24', 0, NULL),
(34, 'Hghatole', '$2y$10$WOtfEk1iv2bI//JBw9ky7.x3D/RhDJhhGYUEPHj2dz6/NMl5K5cCO', 'Mr. Harshal Ghatole', 'ghatoleharshal@nit.edu.in', '8390601774', NULL, 'teacher', 4, 1, '2025-11-15 04:20:16', NULL, 0, NULL),
(35, 'Skavishwar', '$2y$10$NdWRli.O/xNMx7nB/bM/dewQ27u19S5X6DkIHVH2GGV2jbSZjhnAq', 'Mr. Samrat Kavishwar', 'smkavishwar@nit.edu.in', '9834095486', NULL, 'teacher', 4, 1, '2025-11-15 04:21:37', NULL, 0, NULL),
(36, 'Akharwade', '$2y$10$Z3LPsDRkVwcCJCoNLcup8.t6neEKApYBpBYhFSGMw6pZUEHFRMaYG', 'Dr. Amit Kharwade', 'amkharwade@nit.edu.in', '7972641522', NULL, 'teacher', 4, 1, '2025-11-15 04:22:44', NULL, 0, NULL),
(37, 'Aghaffar', '$2y$10$w/ehqv8V20phl18GSRW.XuJA33RISoDcB9XqgHPuaR/RRAJP0MrFq', 'Dr. Abdul Ghaffar', 'abdulghaffar@nit.edu.in', '9881047800', NULL, 'teacher', 4, 1, '2025-11-15 04:23:43', NULL, 0, NULL),
(38, 'GAkhan', '$2y$10$vFDsFzgCsordLwC83BLiG.tbWjEY.DLOTzLd1vcO3A0ciWKWfffca', 'Mr. Ghufran Ahmad Khan', 'khangurfan@nit.edu.in', '8999941317', NULL, 'teacher', 4, 1, '2025-11-15 04:26:03', NULL, 0, NULL),
(39, 'Rdeshmukh', '$2y$10$gM1JBIoZd4e2BK3C4.El2enPtnVg9DbNg1FDaNd1S9/wcSJG.mVTO', 'Mr. Rohan Deshmukh', 'deshmukhrohan@nit.edu.in', '9370594377', NULL, 'teacher', 4, 1, '2025-11-15 04:37:47', NULL, 0, NULL),
(40, 'Rkadam', '$2y$10$TgG/L1YCc2bP7B8r67yuAunIBxM11TAzwV72GKdn9CFtbAzSV/LtO', 'Mr. Rahul Kadam', 'rrkadam@nit.edu.in', '8806309018', NULL, 'teacher', 4, 1, '2025-11-15 04:45:36', NULL, 0, NULL),
(51, 'Mjumde', '$2y$10$JR2IE4kVU2rd4cItBJw2Geh9d0gofwEG8FYzdbiVdMjIxAnJ4Jrqy', 'Dr. (Mrs.) Meghna Jumde', 'mhjumde@nit.edu.in', '9511664867', 'uploads/photos/teacher_51_691b2fc68de3f.jpeg', 'teacher', 4, 1, '2025-11-16 09:09:17', '2025-12-05 23:51:39', 0, NULL),
(52, 'Vraut', '$2y$10$rjLRpM3gEjjzWzpgp5cfkutbz9gqpsfKrWXeuFiD2ZNJ3NmznSsCS', 'Ms. Vidya Raut', 'rautvidya@nit.edu.in', '9890701053', 'uploads/teachers/teacher_1763372594_691aee324fdb4.jpeg', 'teacher', 4, 1, '2025-11-17 09:43:14', NULL, 0, NULL),
(53, 'Hchauhan', '$2y$10$m0FDlft.4ffCHz2M0Zmb7eeteYAnI0eSud/dPsZcB8qJwRZdOTRtm', 'Ms. Hitaishi Chauhan', 'chauhanhitaishi@nit.edu.in', '7821949253', 'uploads/teachers/teacher_1763373074_691af0128ffb3.jpeg', 'teacher', 4, 1, '2025-11-17 09:51:14', NULL, 0, NULL),
(54, 'Asharma', '$2y$10$6S0FLIxxCTCOPjdc0tW03uSAsnr/x1nxy.0/TtOZ2UznORh90mwya', 'Ms. Aayushi Sharma', 'sharmaaayushi@nit.edu.in', '9589344599', 'uploads/teachers/teacher_1763373261_691af0cdc222e.png', 'teacher', 4, 1, '2025-11-17 09:54:21', NULL, 0, NULL),
(55, 'Tshelke', '$2y$10$MoQJUav6xUej4y6Bz8jm7ODj5/UHn2Hnxz11rvAP9Sqcwt1sMM6Ru', 'Mr. Tushar Shelke', 'tvshelke@nit.edu.in', '9970935793', 'uploads/teachers/teacher_1763378190_691b040ea1b8a.jpeg', 'teacher', 4, 1, '2025-11-17 11:16:30', NULL, 0, NULL),
(57, 'Dlande', '$2y$10$ujBuyrMug2Hlsa7oJUN.BeLeCXEE1yV3My53unHiF08idMhaS8nDC', 'Ms. Divya Lande', 'landedivya@nit.edu.in', '8806569892', 'uploads/teachers/teacher_1763384069_691b1b05227b4.jpeg', 'teacher', 4, 1, '2025-11-17 12:54:29', NULL, 0, NULL),
(59, 'Jbhaiswar', '$2y$10$7YVx5GnA6jT7k3JG.ZbQXO/eioS6aCRCabBBVUYlffCwqHmB1iWD2', 'Dr. Jitendra Bhaiswar', 'jbhaiswar@nit.edu.in', '8007673734', 'uploads/teachers/teacher_1763398976_691b55405d25a.jpeg', 'teacher', 4, 1, '2025-11-17 17:02:56', NULL, 0, NULL),
(61, 'Sbhujade', '$2y$10$QORkRMomjxtbJ8fZ6.yTmu2iACuHfnUXKd4gs9pxd0GPVtsFIf8N2', 'Mrs. Shweta Bhujade', 'shewta@gmail.com', '5695423223', NULL, 'teacher', 4, 1, '2025-11-29 05:12:29', NULL, 0, NULL),
(62, 'Asheikh', '$2y$10$3BzAYCPxFAUKznXVduWj6ebyxw4uNZ06obMHWb4IwlThVolU.kRwW', 'Mrs.Afreen Sheikh', 'afreensheikh@gmail.com', '3435443436', NULL, 'teacher', 4, 1, '2025-11-29 05:13:05', NULL, 0, NULL),
(63, 'Dbadwaik', '$2y$10$FNpVRHwlT6Tv3bcqYWzbD.rGi3Kd3QbgEs9utSxFIK2.7YBlml9pe', 'Ms. Divya Badwaik', 'divyabadwaik@gmail.com', '355345353', NULL, 'teacher', 4, 1, '2025-11-29 05:14:05', NULL, 0, NULL),
(64, 'Ssomkuwar', '$2y$10$pCqBH8vptMLdjFZrAqeIeeenHEk2gELtMq8.eVX1K1QKTCBFoA2Ke', 'Mrs Suharshana Somkuwar', 'suharshana@gmail.com', '466644664', NULL, 'teacher', 4, 1, '2025-11-29 05:15:10', '2025-12-05 23:49:30', 0, NULL);

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_exam_timetables_full`
-- (See below for the actual view)
--
CREATE TABLE `view_exam_timetables_full` (
`id` int(11)
,`department_id` int(11)
,`exam_name` varchar(200)
,`exam_type` varchar(100)
,`academic_year` varchar(20)
,`start_date` date
,`end_date` date
,`year` int(11)
,`semester` int(11)
,`section` varchar(100)
,`instructions` text
,`created_by` int(11)
,`created_at` timestamp
,`updated_at` timestamp
,`dept_name` varchar(100)
,`created_by_name` varchar(100)
,`subject_count` bigint(21)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_upcoming_exams`
-- (See below for the actual view)
--
CREATE TABLE `view_upcoming_exams` (
`id` int(11)
,`department_id` int(11)
,`exam_name` varchar(200)
,`exam_type` varchar(100)
,`academic_year` varchar(20)
,`start_date` date
,`end_date` date
,`year` int(11)
,`semester` int(11)
,`section` varchar(100)
,`instructions` text
,`created_by` int(11)
,`created_at` timestamp
,`updated_at` timestamp
,`dept_name` varchar(100)
,`subject_count` bigint(21)
,`days_until_exam` int(7)
);

-- --------------------------------------------------------

--
-- Table structure for table `v_class_student_count`
--

CREATE TABLE `v_class_student_count` (
  `class_id` int(11) DEFAULT NULL,
  `class_name` varchar(100) DEFAULT NULL,
  `dept_name` varchar(100) DEFAULT NULL,
  `year` int(11) DEFAULT NULL,
  `section` varchar(10) DEFAULT NULL,
  `semester` int(11) DEFAULT NULL,
  `academic_year` varchar(20) DEFAULT NULL,
  `teacher_name` varchar(100) DEFAULT NULL,
  `student_count` bigint(21) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `v_student_details`
--

CREATE TABLE `v_student_details` (
  `id` int(11) DEFAULT NULL,
  `roll_number` varchar(50) DEFAULT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `dept_name` varchar(100) DEFAULT NULL,
  `class_name` varchar(100) DEFAULT NULL,
  `year` int(11) DEFAULT NULL,
  `semester` int(11) DEFAULT NULL,
  `admission_year` varchar(10) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_student_marks`
-- (See below for the actual view)
--
CREATE TABLE `v_student_marks` (
`id` int(11)
,`student_id` int(11)
,`roll_number` varchar(50)
,`student_name` varchar(100)
,`subject_code` varchar(20)
,`subject_name` varchar(100)
,`exam_name` varchar(50)
,`marks_obtained` decimal(5,2)
,`max_marks` int(11)
,`percentage` decimal(5,2)
,`grade` varchar(5)
,`teacher_name` varchar(100)
,`year` int(11)
,`semester` int(11)
,`academic_year` varchar(20)
,`is_published` tinyint(1)
,`exam_date` date
,`remarks` text
,`section` varchar(10)
,`dept_name` varchar(100)
);

-- --------------------------------------------------------

--
-- Table structure for table `v_today_attendance`
--

CREATE TABLE `v_today_attendance` (
  `id` int(11) DEFAULT NULL,
  `roll_number` varchar(50) DEFAULT NULL,
  `student_name` varchar(100) DEFAULT NULL,
  `class_name` varchar(100) DEFAULT NULL,
  `dept_name` varchar(100) DEFAULT NULL,
  `status` enum('present','absent','late') DEFAULT NULL,
  `marked_by` varchar(100) DEFAULT NULL,
  `marked_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure for view `view_exam_timetables_full`
--
DROP TABLE IF EXISTS `view_exam_timetables_full`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_exam_timetables_full`  AS SELECT `et`.`id` AS `id`, `et`.`department_id` AS `department_id`, `et`.`exam_name` AS `exam_name`, `et`.`exam_type` AS `exam_type`, `et`.`academic_year` AS `academic_year`, `et`.`start_date` AS `start_date`, `et`.`end_date` AS `end_date`, `et`.`year` AS `year`, `et`.`semester` AS `semester`, `et`.`section` AS `section`, `et`.`instructions` AS `instructions`, `et`.`created_by` AS `created_by`, `et`.`created_at` AS `created_at`, `et`.`updated_at` AS `updated_at`, `d`.`dept_name` AS `dept_name`, `u`.`full_name` AS `created_by_name`, count(`es`.`id`) AS `subject_count` FROM (((`exam_timetables` `et` left join `departments` `d` on(`et`.`department_id` = `d`.`id`)) left join `users` `u` on(`et`.`created_by` = `u`.`id`)) left join `exam_subjects` `es` on(`et`.`id` = `es`.`timetable_id`)) GROUP BY `et`.`id` ;

-- --------------------------------------------------------

--
-- Structure for view `view_upcoming_exams`
--
DROP TABLE IF EXISTS `view_upcoming_exams`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_upcoming_exams`  AS SELECT `et`.`id` AS `id`, `et`.`department_id` AS `department_id`, `et`.`exam_name` AS `exam_name`, `et`.`exam_type` AS `exam_type`, `et`.`academic_year` AS `academic_year`, `et`.`start_date` AS `start_date`, `et`.`end_date` AS `end_date`, `et`.`year` AS `year`, `et`.`semester` AS `semester`, `et`.`section` AS `section`, `et`.`instructions` AS `instructions`, `et`.`created_by` AS `created_by`, `et`.`created_at` AS `created_at`, `et`.`updated_at` AS `updated_at`, `d`.`dept_name` AS `dept_name`, count(`es`.`id`) AS `subject_count`, to_days(`et`.`start_date`) - to_days(curdate()) AS `days_until_exam` FROM ((`exam_timetables` `et` left join `departments` `d` on(`et`.`department_id` = `d`.`id`)) left join `exam_subjects` `es` on(`et`.`id` = `es`.`timetable_id`)) WHERE `et`.`start_date` >= curdate() GROUP BY `et`.`id` ORDER BY `et`.`start_date` ASC ;

-- --------------------------------------------------------

--
-- Structure for view `v_student_marks`
--
DROP TABLE IF EXISTS `v_student_marks`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_student_marks`  AS SELECT `pm`.`id` AS `id`, `pm`.`student_id` AS `student_id`, `s`.`roll_number` AS `roll_number`, `s`.`full_name` AS `student_name`, `sub`.`subject_code` AS `subject_code`, `sub`.`subject_name` AS `subject_name`, `et`.`exam_name` AS `exam_name`, `pm`.`marks_obtained` AS `marks_obtained`, `pm`.`max_marks` AS `max_marks`, `pm`.`percentage` AS `percentage`, `pm`.`grade` AS `grade`, `u`.`full_name` AS `teacher_name`, `pm`.`year` AS `year`, `pm`.`semester` AS `semester`, `pm`.`academic_year` AS `academic_year`, `pm`.`is_published` AS `is_published`, `pm`.`exam_date` AS `exam_date`, `pm`.`remarks` AS `remarks`, `c`.`section` AS `section`, `d`.`dept_name` AS `dept_name` FROM ((((((`paper_marks` `pm` join `students` `s` on(`pm`.`student_id` = `s`.`id`)) join `subjects` `sub` on(`pm`.`subject_id` = `sub`.`id`)) join `exam_types` `et` on(`pm`.`exam_type_id` = `et`.`id`)) join `users` `u` on(`pm`.`teacher_id` = `u`.`id`)) left join `classes` `c` on(`s`.`class_id` = `c`.`id`)) left join `departments` `d` on(`s`.`department_id` = `d`.`id`)) ORDER BY `pm`.`academic_year` DESC, `pm`.`semester` ASC, `s`.`roll_number` ASC ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `active_sessions`
--
ALTER TABLE `active_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_session_id` (`session_id`),
  ADD KEY `idx_user` (`user_id`,`user_role`),
  ADD KEY `idx_last_activity` (`last_activity`);

--
-- Indexes for table `assignments`
--
ALTER TABLE `assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_teacher` (`teacher_id`),
  ADD KEY `idx_class` (`class_id`),
  ADD KEY `idx_due_date` (`due_date`);

--
-- Indexes for table `assignment_submissions`
--
ALTER TABLE `assignment_submissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_submission` (`assignment_id`,`student_id`),
  ADD KEY `idx_assignment` (`assignment_id`),
  ADD KEY `idx_student` (`student_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_idx` (`sender_id`,`sender_type`),
  ADD KEY `receiver_idx` (`receiver_id`,`receiver_type`),
  ADD KEY `created_at_idx` (`created_at`),
  ADD KEY `is_read_idx` (`is_read`);

--
-- Indexes for table `exam_subjects`
--
ALTER TABLE `exam_subjects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `timetable_id` (`timetable_id`),
  ADD KEY `exam_date` (`exam_date`),
  ADD KEY `idx_timetable_date` (`timetable_id`,`exam_date`);

--
-- Indexes for table `exam_timetables`
--
ALTER TABLE `exam_timetables`
  ADD PRIMARY KEY (`id`),
  ADD KEY `department_id` (`department_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `academic_year` (`academic_year`),
  ADD KEY `year_semester` (`year`,`semester`),
  ADD KEY `section` (`section`),
  ADD KEY `idx_dept_year_sem` (`department_id`,`year`,`semester`),
  ADD KEY `idx_date_range` (`start_date`,`end_date`);

--
-- Indexes for table `exam_types`
--
ALTER TABLE `exam_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `exam_code` (`exam_code`);

--
-- Indexes for table `facelock_users`
--
ALTER TABLE `facelock_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `student_id` (`student_id`);

--
-- Indexes for table `faculty_load`
--
ALTER TABLE `faculty_load`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_load` (`teacher_id`,`academic_year`,`semester`),
  ADD UNIQUE KEY `unique_teacher_semester` (`teacher_id`,`academic_year`,`semester`),
  ADD KEY `teacher_id` (`teacher_id`),
  ADD KEY `department_id` (`department_id`),
  ADD KEY `academic_year` (`academic_year`),
  ADD KEY `semester` (`semester`),
  ADD KEY `idx_teacher` (`teacher_id`),
  ADD KEY `idx_department` (`department_id`),
  ADD KEY `idx_academic_year` (`academic_year`),
  ADD KEY `idx_semester` (`semester`);

--
-- Indexes for table `file_notifications`
--
ALTER TABLE `file_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_student_id` (`student_id`),
  ADD KEY `idx_file_id` (`file_id`),
  ADD KEY `idx_is_read` (`is_read`);

--
-- Indexes for table `hod_messages`
--
ALTER TABLE `hod_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hod_id` (`hod_id`),
  ADD KEY `sent_by` (`sent_by`),
  ADD KEY `is_read` (`is_read`),
  ADD KEY `created_at` (`created_at`),
  ADD KEY `idx_hod` (`hod_id`),
  ADD KEY `idx_sender` (`sent_by`),
  ADD KEY `idx_read_status` (`is_read`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `leave_applications`
--
ALTER TABLE `leave_applications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_student` (`student_id`),
  ADD KEY `idx_teacher` (`teacher_id`),
  ADD KEY `idx_class` (`class_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_username` (`username`),
  ADD KEY `idx_ip_address` (`ip_address`),
  ADD KEY `idx_attempt_time` (`attempt_time`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`);

--
-- Indexes for table `message_attachments`
--
ALTER TABLE `message_attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_message_id` (`message_id`);

--
-- Indexes for table `message_replies`
--
ALTER TABLE `message_replies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_message_id` (`message_id`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `nit_importnoticess`
--
ALTER TABLE `nit_importnoticess`
  ADD PRIMARY KEY (`id`),
  ADD KEY `department_id` (`department_id`),
  ADD KEY `exam_date` (`exam_date`),
  ADD KEY `idx_dept_date` (`department_id`,`exam_date`);

--
-- Indexes for table `notices`
--
ALTER TABLE `notices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_active` (`is_active`),
  ADD KEY `idx_dates` (`start_date`,`end_date`),
  ADD KEY `idx_audience` (`target_audience`);

--
-- Indexes for table `paper_marks`
--
ALTER TABLE `paper_marks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_student_exam` (`student_id`,`subject_id`,`exam_type_id`,`academic_year`),
  ADD KEY `idx_student_id` (`student_id`),
  ADD KEY `idx_subject_id` (`subject_id`),
  ADD KEY `idx_exam_type_id` (`exam_type_id`),
  ADD KEY `idx_teacher_id` (`teacher_id`),
  ADD KEY `idx_academic_year` (`academic_year`);

--
-- Indexes for table `security_logs`
--
ALTER TABLE `security_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_event_type` (`event_type`),
  ADD KEY `idx_ip_address` (`ip_address`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `student_attendance`
--
ALTER TABLE `student_attendance`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_attendance` (`student_id`,`class_id`,`attendance_date`),
  ADD KEY `idx_date` (`attendance_date`),
  ADD KEY `idx_student` (`student_id`),
  ADD KEY `idx_class` (`class_id`);

--
-- Indexes for table `student_file_access`
--
ALTER TABLE `student_file_access`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_file_student` (`file_id`,`student_id`),
  ADD KEY `idx_file_id` (`file_id`),
  ADD KEY `idx_student_id` (`student_id`),
  ADD KEY `idx_access_date` (`access_date`);

--
-- Indexes for table `student_notifications`
--
ALTER TABLE `student_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_student_read` (`student_id`,`is_read`),
  ADD KEY `idx_date` (`notification_date`),
  ADD KEY `idx_student` (`student_id`),
  ADD KEY `idx_teacher` (`teacher_id`),
  ADD KEY `idx_class` (`class_id`);

--
-- Indexes for table `student_resumes`
--
ALTER TABLE `student_resumes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_student` (`student_id`),
  ADD KEY `idx_student_id` (`student_id`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `subject_teachers`
--
ALTER TABLE `subject_teachers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_subject_teacher` (`subject_id`,`teacher_id`,`section`,`year`,`semester`,`academic_year`),
  ADD KEY `idx_subject_id` (`subject_id`),
  ADD KEY `idx_teacher_id` (`teacher_id`);

--
-- Indexes for table `teacher_files`
--
ALTER TABLE `teacher_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_teacher_id` (`teacher_id`),
  ADD KEY `idx_class_id` (`class_id`),
  ADD KEY `idx_upload_date` (`upload_date`),
  ADD KEY `idx_file_type` (`file_type`),
  ADD KEY `idx_is_active` (`is_active`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `active_sessions`
--
ALTER TABLE `active_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `assignments`
--
ALTER TABLE `assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `assignment_submissions`
--
ALTER TABLE `assignment_submissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `chat_messages`
--
ALTER TABLE `chat_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `exam_subjects`
--
ALTER TABLE `exam_subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `exam_timetables`
--
ALTER TABLE `exam_timetables`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `exam_types`
--
ALTER TABLE `exam_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `facelock_users`
--
ALTER TABLE `facelock_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=517;

--
-- AUTO_INCREMENT for table `faculty_load`
--
ALTER TABLE `faculty_load`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `file_notifications`
--
ALTER TABLE `file_notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hod_messages`
--
ALTER TABLE `hod_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `leave_applications`
--
ALTER TABLE `leave_applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `message_attachments`
--
ALTER TABLE `message_attachments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `message_replies`
--
ALTER TABLE `message_replies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `nit_importnoticess`
--
ALTER TABLE `nit_importnoticess`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `notices`
--
ALTER TABLE `notices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `paper_marks`
--
ALTER TABLE `paper_marks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=654;

--
-- AUTO_INCREMENT for table `security_logs`
--
ALTER TABLE `security_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `student_attendance`
--
ALTER TABLE `student_attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21323;

--
-- AUTO_INCREMENT for table `student_file_access`
--
ALTER TABLE `student_file_access`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_notifications`
--
ALTER TABLE `student_notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `student_resumes`
--
ALTER TABLE `student_resumes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `subject_teachers`
--
ALTER TABLE `subject_teachers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `teacher_files`
--
ALTER TABLE `teacher_files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `assignments`
--
ALTER TABLE `assignments`
  ADD CONSTRAINT `fk_assignments_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `exam_subjects`
--
ALTER TABLE `exam_subjects`
  ADD CONSTRAINT `exam_subjects_ibfk_1` FOREIGN KEY (`timetable_id`) REFERENCES `exam_timetables` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `message_attachments`
--
ALTER TABLE `message_attachments`
  ADD CONSTRAINT `message_attachments_ibfk_1` FOREIGN KEY (`message_id`) REFERENCES `hod_messages` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `message_replies`
--
ALTER TABLE `message_replies`
  ADD CONSTRAINT `message_replies_ibfk_1` FOREIGN KEY (`message_id`) REFERENCES `hod_messages` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
