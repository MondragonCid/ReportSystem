-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 10, 2026 at 05:32 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `cit_reporting_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `damage_report`
--

CREATE TABLE `damage_report` (
  `ReportID` int(11) NOT NULL,
  `ReporterID` int(11) NOT NULL,
  `LocationID` int(11) NOT NULL,
  `StaffID` int(11) DEFAULT NULL,
  `Category` varchar(50) NOT NULL,
  `Description` text NOT NULL,
  `Status` enum('pending','in-progress','resolved','cancelled') DEFAULT 'pending',
  `DateReported` datetime DEFAULT current_timestamp(),
  `DateResolved` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `damage_report`
--

INSERT INTO `damage_report` (`ReportID`, `ReporterID`, `LocationID`, `StaffID`, `Category`, `Description`, `Status`, `DateReported`, `DateResolved`) VALUES
(1, 2, 1, NULL, 'Electrical', 'Air conditioning unit not cooling properly, makes loud noise', 'resolved', '2026-05-09 18:59:51', '2026-05-10 02:24:22'),
(2, 2, 2, NULL, 'Furniture', '5 chairs in classroom have broken armrests', 'in-progress', '2026-05-07 18:59:51', NULL),
(3, 2, 5, NULL, 'IT Equipment', 'Projector bulb needs replacement, display is dim', 'resolved', '2026-05-04 18:59:51', NULL),
(4, 2, 11, 3, 'Electrical', 'Some outlets in Lab 1 are not working', 'pending', '2026-05-08 18:59:51', NULL),
(5, 2, 7, NULL, '???? Plumbing (faucets, toilets, pipes)', 'guba, GUBA WHAT THE FUCLKKKK', 'pending', '2026-05-10 01:40:18', NULL),
(6, 3, 12, NULL, '???? Furniture (chairs, tables, cabinets)', 'asdasdasdsad', 'resolved', '2026-05-10 01:49:55', '2026-05-10 05:00:44'),
(7, 2, 7, 3, '???? Electrical (lights, outlets, fans, ACU)', 'Oh noooo, nasira yung electrical!', 'resolved', '2026-05-10 03:35:30', '2026-05-10 05:29:25'),
(9, 8, 8, 3, '???? Electrical (lights, outlets, fans, ACU)', 'my name is Oni, my friend Yelli was injured because of electrical lights.', 'resolved', '2026-05-10 04:15:16', '2026-05-10 05:23:06');

-- --------------------------------------------------------

--
-- Table structure for table `employee`
--

CREATE TABLE `employee` (
  `UserID` int(11) NOT NULL,
  `EmployeeID` varchar(50) NOT NULL,
  `Position` varchar(100) DEFAULT NULL,
  `Department` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employee`
--

INSERT INTO `employee` (`UserID`, `EmployeeID`, `Position`, `Department`) VALUES
(2, 'EMP001', 'Professor', 'Computer Science'),
(5, 'STU001', 'Student', 'Computer Science');

-- --------------------------------------------------------

--
-- Table structure for table `location`
--

CREATE TABLE `location` (
  `LocationID` int(11) NOT NULL,
  `BuildingName` varchar(100) NOT NULL,
  `ClassRoomNum` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `location`
--

INSERT INTO `location` (`LocationID`, `BuildingName`, `ClassRoomNum`) VALUES
(7, 'CIT Gym', 'Court A'),
(8, 'CIT Gym', 'Court B'),
(11, 'CS/IT Building', 'Lab 1'),
(12, 'CS/IT Building', 'Lab 2'),
(9, 'Engineering Building', '301'),
(10, 'Engineering Building', '302'),
(5, 'Library', '1F Reading Room'),
(6, 'Library', '2F Study Area'),
(1, 'Main Building', '101'),
(2, 'Main Building', '102'),
(3, 'Main Building', '201'),
(4, 'Main Building', '202'),
(13, 'Wow Wow', '123123');

-- --------------------------------------------------------

--
-- Table structure for table `maintainance_staff`
--

CREATE TABLE `maintainance_staff` (
  `UserID` int(11) NOT NULL,
  `StaffID` varchar(50) NOT NULL,
  `Specialization` varchar(100) DEFAULT NULL,
  `ContactNumber` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `maintainance_staff`
--

INSERT INTO `maintainance_staff` (`UserID`, `StaffID`, `Specialization`, `ContactNumber`) VALUES
(3, 'STF001', 'Electrical', '09123456789');

-- --------------------------------------------------------

--
-- Table structure for table `system_administrator`
--

CREATE TABLE `system_administrator` (
  `UserID` int(11) NOT NULL,
  `AdminID` varchar(50) NOT NULL,
  `Department` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_administrator`
--

INSERT INTO `system_administrator` (`UserID`, `AdminID`, `Department`) VALUES
(7, 'ADM001', 'IT Department'),
(8, '123', '123');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `UserID` int(11) NOT NULL,
  `StudentID` varchar(50) DEFAULT NULL,
  `Username` varchar(50) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `FirstName` varchar(50) NOT NULL,
  `LastName` varchar(50) NOT NULL,
  `UserType` enum('admin','employee','staff') NOT NULL,
  `IsActive` tinyint(1) DEFAULT 1,
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`UserID`, `StudentID`, `Username`, `Password`, `Email`, `FirstName`, `LastName`, `UserType`, `IsActive`, `CreatedAt`) VALUES
(2, '2020-10001', 'john.smith', '$2y$10$2wB.KrFEl2UfLQcIHoqcX.L48Br30NE/hIrV331QV9SgRxuPWbF3S', 'john.smith@cit.edu', 'John', 'Smith', 'employee', 1, '2026-05-09 10:59:51'),
(3, NULL, 'mike.staff', '$2y$10$2wB.KrFEl2UfLQcIHoqcX.L48Br30NE/hIrV331QV9SgRxuPWbF3S', 'mike.johnson@cit.edu', 'Mike', 'Johnson', 'staff', 1, '2026-05-09 10:59:51'),
(5, '2024-10001', 'juana.delacruz', '$2y$10$2wB.KrFEl2UfLQcIHoqcX.L48Br30NE/hIrV331QV9SgRxuPWbF3S', 'juana.delacruz@cit.edu', 'Juana', 'Dela Cruz', 'employee', 1, '2026-05-09 17:35:00'),
(7, NULL, 'admin', '$2y$10$3c5oifp.jQ8ApsDLN.N9TO6WuEgjMs9ZfTQ0Mz65mUi37e5Buh2K6', 'system.administrator@cit.edu', 'System', 'Administrator', 'admin', 1, '2026-05-09 17:52:15'),
(8, NULL, 'onissu', '$2y$10$1l.c6FzIGCzk5bchqJLLtugzSmq4yHCPcQNKsZ3C673YVhyASHtd6', 'oni.roblox@cit.edu', 'oni', 'roblox', 'admin', 1, '2026-05-09 19:38:54');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `damage_report`
--
ALTER TABLE `damage_report`
  ADD PRIMARY KEY (`ReportID`),
  ADD KEY `ReporterID` (`ReporterID`),
  ADD KEY `LocationID` (`LocationID`),
  ADD KEY `StaffID` (`StaffID`),
  ADD KEY `idx_status` (`Status`),
  ADD KEY `idx_date` (`DateReported`);

--
-- Indexes for table `employee`
--
ALTER TABLE `employee`
  ADD PRIMARY KEY (`UserID`),
  ADD UNIQUE KEY `EmployeeID` (`EmployeeID`);

--
-- Indexes for table `location`
--
ALTER TABLE `location`
  ADD PRIMARY KEY (`LocationID`),
  ADD UNIQUE KEY `unique_location` (`BuildingName`,`ClassRoomNum`);

--
-- Indexes for table `maintainance_staff`
--
ALTER TABLE `maintainance_staff`
  ADD PRIMARY KEY (`UserID`),
  ADD UNIQUE KEY `StaffID` (`StaffID`);

--
-- Indexes for table `system_administrator`
--
ALTER TABLE `system_administrator`
  ADD PRIMARY KEY (`UserID`),
  ADD UNIQUE KEY `AdminID` (`AdminID`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`UserID`),
  ADD UNIQUE KEY `Username` (`Username`),
  ADD UNIQUE KEY `Email` (`Email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `damage_report`
--
ALTER TABLE `damage_report`
  MODIFY `ReportID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `location`
--
ALTER TABLE `location`
  MODIFY `LocationID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `UserID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `damage_report`
--
ALTER TABLE `damage_report`
  ADD CONSTRAINT `damage_report_ibfk_1` FOREIGN KEY (`ReporterID`) REFERENCES `user` (`UserID`),
  ADD CONSTRAINT `damage_report_ibfk_2` FOREIGN KEY (`LocationID`) REFERENCES `location` (`LocationID`),
  ADD CONSTRAINT `damage_report_ibfk_3` FOREIGN KEY (`StaffID`) REFERENCES `maintainance_staff` (`UserID`);

--
-- Constraints for table `employee`
--
ALTER TABLE `employee`
  ADD CONSTRAINT `employee_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `user` (`UserID`) ON DELETE CASCADE;

--
-- Constraints for table `maintainance_staff`
--
ALTER TABLE `maintainance_staff`
  ADD CONSTRAINT `maintainance_staff_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `user` (`UserID`) ON DELETE CASCADE;

--
-- Constraints for table `system_administrator`
--
ALTER TABLE `system_administrator`
  ADD CONSTRAINT `system_administrator_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `user` (`UserID`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
