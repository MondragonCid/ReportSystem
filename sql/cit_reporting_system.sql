-- ============================================
-- CIT UNIVERSITY DAMAGE REPORTING SYSTEM
-- Complete Database Schema
-- ============================================

-- Drop database if exists (for fresh install)
DROP DATABASE IF EXISTS cit_reporting_system;

-- Create database
CREATE DATABASE cit_reporting_system;
USE cit_reporting_system;

-- ============================================
-- 1. USER TABLE (Base Table for all users)
-- ============================================
CREATE TABLE `user` (
    UserID INT PRIMARY KEY AUTO_INCREMENT,
    StudentID VARCHAR(50) DEFAULT NULL,
    Username VARCHAR(50) NOT NULL UNIQUE,
    Password VARCHAR(255) NOT NULL,
    Email VARCHAR(100) NOT NULL UNIQUE,
    FirstName VARCHAR(50) NOT NULL,
    LastName VARCHAR(50) NOT NULL,
    UserType ENUM('admin', 'employee', 'staff') NOT NULL,
    IsActive BOOLEAN DEFAULT TRUE,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- 2. SYSTEM_ADMINISTRATOR TABLE
-- ============================================
CREATE TABLE `system_administrator` (
    UserID INT PRIMARY KEY,
    AdminID VARCHAR(50) NOT NULL UNIQUE,
    Department VARCHAR(100),
    FOREIGN KEY (UserID) REFERENCES user(UserID) ON DELETE CASCADE
);

-- ============================================
-- 3. EMPLOYEE TABLE
-- ============================================
CREATE TABLE `employee` (
    UserID INT PRIMARY KEY,
    EmployeeID VARCHAR(50) NOT NULL UNIQUE,
    Position VARCHAR(100),
    Department VARCHAR(100),
    FOREIGN KEY (UserID) REFERENCES user(UserID) ON DELETE CASCADE
);

-- ============================================
-- 4. MAINTAINANCE_STAFF TABLE
-- ============================================
CREATE TABLE `maintainance_staff` (
    UserID INT PRIMARY KEY,
    StaffID VARCHAR(50) NOT NULL UNIQUE,
    Specialization VARCHAR(100),
    ContactNumber VARCHAR(20),
    FOREIGN KEY (UserID) REFERENCES user(UserID) ON DELETE CASCADE
);

-- ============================================
-- 5. LOCATION TABLE
-- ============================================
CREATE TABLE `location` (
    LocationID INT PRIMARY KEY AUTO_INCREMENT,
    BuildingName VARCHAR(100) NOT NULL,
    ClassRoomNum VARCHAR(50) NOT NULL,
    UNIQUE KEY unique_location (BuildingName, ClassRoomNum)
);

-- ============================================
-- 6. DAMAGE_REPORT TABLE
-- ============================================
CREATE TABLE `damage_report` (
    ReportID INT PRIMARY KEY AUTO_INCREMENT,
    ReporterID INT NOT NULL,
    LocationID INT NOT NULL,
    StaffID INT DEFAULT NULL,
    Category VARCHAR(50) NOT NULL,
    Description TEXT NOT NULL,
    Status ENUM('pending', 'in-progress', 'resolved', 'cancelled') DEFAULT 'pending',
    DateReported DATETIME DEFAULT CURRENT_TIMESTAMP,
    DateResolved DATETIME DEFAULT NULL,
    FOREIGN KEY (ReporterID) REFERENCES user(UserID),
    FOREIGN KEY (LocationID) REFERENCES location(LocationID),
    FOREIGN KEY (StaffID) REFERENCES maintainance_staff(UserID),
    INDEX idx_status (Status),
    INDEX idx_date (DateReported)
);

-- ============================================
-- SAMPLE DATA
-- ============================================

-- Insert sample locations
INSERT INTO `location` (BuildingName, ClassRoomNum) VALUES
('Main Building', '101'),
('Main Building', '102'),
('Main Building', '201'),
('Main Building', '202'),
('Library', '1F Reading Room'),
('Library', '2F Study Area'),
('CIT Gym', 'Court A'),
('CIT Gym', 'Court B'),
('Engineering Building', '301'),
('Engineering Building', '302'),
('CS/IT Building', 'Lab 1'),
('CS/IT Building', 'Lab 2');

-- Insert sample admin (password: admin123)
INSERT INTO `user` (Username, Password, Email, FirstName, LastName, UserType, IsActive) VALUES
('admin.cit', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@cit.edu', 'System', 'Administrator', 'admin', 1);

INSERT INTO `system_administrator` (UserID, AdminID, Department) VALUES
(1, 'ADM001', 'IT Department');

-- Insert sample employee
INSERT INTO `user` (StudentID, Username, Password, Email, FirstName, LastName, UserType, IsActive) VALUES
('2020-10001', 'john.smith', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'john.smith@cit.edu', 'John', 'Smith', 'employee', 1);

INSERT INTO `employee` (UserID, EmployeeID, Position, Department) VALUES
(2, 'EMP001', 'Professor', 'Computer Science');

-- Insert sample maintenance staff
INSERT INTO `user` (Username, Password, Email, FirstName, LastName, UserType, IsActive) VALUES
('mike.staff', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mike.johnson@cit.edu', 'Mike', 'Johnson', 'staff', 1);

INSERT INTO `maintainance_staff` (UserID, StaffID, Specialization, ContactNumber) VALUES
(3, 'STF001', 'Electrical', '09123456789');

-- Insert sample damage reports
INSERT INTO `damage_report` (ReporterID, LocationID, Category, Description, Status, DateReported) VALUES
(2, 1, 'Electrical', 'Air conditioning unit not cooling properly, makes loud noise', 'pending', NOW()),
(2, 2, 'Furniture', '5 chairs in classroom have broken armrests', 'in-progress', NOW() - INTERVAL 2 DAY),
(2, 5, 'IT Equipment', 'Projector bulb needs replacement, display is dim', 'resolved', NOW() - INTERVAL 5 DAY),
(2, 11, 'Electrical', 'Some outlets in Lab 1 are not working', 'pending', NOW() - INTERVAL 1 DAY);

-- Display all tables
SHOW TABLES;

-- Verify data
SELECT 'Database setup complete!' as Status;
SELECT COUNT(*) as TotalUsers FROM user;
SELECT COUNT(*) as TotalLocations FROM location;
SELECT COUNT(*) as TotalReports FROM damage_report;