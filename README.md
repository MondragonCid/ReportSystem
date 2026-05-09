# CIT University Damage Reporting System

## Project Overview
A web-based damage reporting system for Cebu Institute of Technology - University.

## Features
- ✅ Admin CRUD Operations (Create, Read, Update, Delete)
- ✅ User Authentication (Login/Logout)
- ✅ Submit Damage Reports
- ✅ View My Reports
- ✅ Auto-generate CIT email format (firstname.lastname@cit.edu)

## Tech Stack
- PHP (Procedural)
- MySQL
- HTML/CSS
- XAMPP

## Installation

### 1. Start XAMPP
- Start Apache and MySQL services

### 2. Create Database
- Open phpMyAdmin: http://localhost/phpmyadmin
- Import `sql/cit_reporting_system.sql`

### 3. Configure Project
- Copy folder to: `C:\xampp\htdocs\cit_reporting_system`

### 4. Run Project
- Open browser: `http://localhost/cit_reporting_system`

## Test Accounts

| User Type | Username | Password | Email |
|-----------|----------|----------|-------|
| Admin | admin.cit | admin123 | admin@cit.edu |
| Employee | john.smith | admin123 | john.smith@cit.edu |
| Staff | mike.staff | admin123 | mike.johnson@cit.edu |

## Project Structure

cit_reporting_system/
├── config/ # Database configuration
├── includes/ # Header, footer, navbar, validation
├── admin/ # Admin CRUD operations
├── assets/ # CSS, JS files
├── sql/ # Database SQL file
├── login.php # Login page
├── dashboard.php # User dashboard
├── report_damage.php # Submit report page
├── my_reports.php # View user's reports
└── index.php # Home page


## CRUD Operations (Admin)

| Operation | URL | Description |
|-----------|-----|-------------|
| CREATE | `/admin/create.php` | Add new admin |
| READ | `/admin/index.php` | List all admins |
| UPDATE | `/admin/edit.php?id=X` | Edit admin |
| DELETE | `/admin/delete.php?id=X` | Delete admin |

## Email Format
- All emails follow: `firstname.lastname@cit.edu`
- Auto-generated from first and last name

## Developer
CIT University - BSIT Program

## License
For educational purposes only.

cit_reporting_system/
│
├── config/
│   └── database.php
│
├── includes/
│   ├── header.php
│   ├── footer.php
│   ├── navbar.php
│   └── validation.php
│
├── admin/
│   ├── index.php          (READ - List all admins)
│   ├── create.php         (CREATE - Add new admin)
│   ├── edit.php           (UPDATE - Edit admin)
│   └── delete.php         (DELETE - Delete admin)
│
├── assets/
│   ├── css/
│   │   └── style.css
│   └── js/
│       └── main.js
│
├── sql/
│   └── cit_reporting_system.sql
│
├── login.php
├── logout.php
├── dashboard.php
├── report_damage.php
├── my_reports.php
├── index.php
└── README.md