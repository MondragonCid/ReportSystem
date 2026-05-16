updated btw fr fr

# CIT University Damage Reporting System

A web-based damage reporting system for Cebu Institute of Technology - University.

## Tech Stack
- PHP (Procedural) - Backend
- MySQL - Database
- HTML/CSS/JS - Frontend
- XAMPP - Local Development

---

## Getting Started

### Prerequisites
- XAMPP installed
- PHP 7.4 or higher
- MySQL

### Installation

1. Start XAMPP (Apache + MySQL)
2. Clone/copy project to: `C:\xampp\htdocs\ReportSystem`
3. Import database from `sql/ReportSystem.sql` using phpMyAdmin
4. Access: `http://localhost/ReportSystem`

### Test Credentials

| Role | Username | Password |
|------|----------|----------|
| Admin | admin.cit | admin123 |
| Employee | john.smith | admin123 |
| Staff | mike.staff | admin123 |

---

## Project Structure

cit_reporting_system/
│
├── config/ # Database configuration
├── includes/ # Reusable components (header, footer, navbar)
├── admin/ # Admin CRUD (backend)
├── locations/ # Location CRUD (backend)
├── assets/ # FRONTEND FILES
│ ├── css/style.css # All styles
│ └── js/main.js # All JavaScript
├── sql/ # Database dump
├── login.php # Authentication page
├── dashboard.php # User dashboard
├── report_damage.php # Report submission form
├── my_reports.php # User reports list
├── index.php # Landing page
└── logout.php # Logout handler


---

## Development Responsibilities

### Backend Developer
- Database schema and queries
- Admin CRUD operations
- Location CRUD operations
- Authentication logic
- Form processing
- Email validation (`firstname.lastname@cit.edu`)

### Frontend Developer
- All visual design and styling
- Responsive layouts
- Form enhancements (character counters, validation)
- Navigation design
- Dashboard UI components
- Mobile responsiveness

---

## Frontend Files to Modify

| File | Responsibility |
|------|----------------|
| `assets/css/style.css` | All CSS styles |
| `assets/js/main.js` | All JavaScript functionality |
| `login.php` | Login page layout |
| `dashboard.php` | User dashboard layout |
| `report_damage.php` | Report form layout |
| `my_reports.php` | Reports table layout |
| `index.php` | Homepage layout |
| `includes/header.php` | Document head, meta tags |
| `includes/navbar.php` | Navigation menu styling |
| `includes/footer.php` | Footer layout |

---

## Backend Files (Do Not Modify)

- `config/database.php`
- `admin/*.php`
- `locations/*.php`
- `includes/validation.php`
- `logout.php`

---

## API / Page Endpoints

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/login.php` | GET/POST | Authentication |
| `/dashboard.php` | GET | User dashboard |
| `/report_damage.php` | GET/POST | Submit report |
| `/my_reports.php` | GET | View user reports |
| `/admin/index.php` | GET | List admins |
| `/admin/create.php` | GET/POST | Create admin |
| `/admin/edit.php` | GET/POST | Edit admin |
| `/admin/delete.php` | GET/POST | Delete admin |
| `/locations/index.php` | GET | List locations |
| `/locations/create.php` | GET/POST | Create location |
| `/locations/edit.php` | GET/POST | Edit location |
| `/locations/delete.php` | GET/POST | Delete location |

---

## Session Variables Available

```php
$_SESSION['user_id']     // User ID
$_SESSION['username']    // Username
$_SESSION['user_type']   // 'admin', 'employee', or 'staff'
$_SESSION['fullname']    // First + Last name
$_SESSION['email']       // User email

Database Schema (Quick Reference)
user table
UserID (PK), Username, Password, Email, FirstName, LastName, UserType

system_administrator table
UserID (FK to user), AdminID, Department

location table
LocationID (PK), BuildingName, ClassRoomNum

damage_report table
ReportID (PK), ReporterID (FK to user), LocationID (FK to location), Category, Description, Status, DateReported

Common Frontend Tasks
Add Character Counter to Textarea
// In main.js
const textarea = document.querySelector('textarea[name="description"]');
const counter = document.createElement('div');
textarea.addEventListener('input', () => {
    counter.textContent = `${textarea.value.length}/500`;
});

Style Status Badges
.status-pending { background: #f39c12; }
.status-progress { background: #3498db; }
.status-resolved { background: #27ae60; }

Confirm Before Delete
// Add to delete buttons
onclick="return confirm('Delete this item?')"

Testing
Start XAMPP (Apache + MySQL)

Access: http://localhost/ReportSystem

Login with test credentials

Test all pages and forms

Version
1.0.0

License
CIT University - Educational Project

TEST FILES :

create_password.php
fix_password.php
register_admin.php
test_login.php
test_password.php
staff_dashboard.php
admin/reports.php

WHEN ADDING NEW STAFF

QUERY:
SELECT UserID, FirstName, LastName FROM user WHERE UserType = 'staff';

INSERT INTO maintainance_staff (UserID, StaffID, Specialization, ContactNumber)
VALUES (11, 'STF002', 'Plumbing', '09987654321');

11 = USERID "CHANGE TO DEDICATED USERID"

ADD CLICKABLE BUTTON FOR SPECIFIC LOCATION IN ADMIN 
REFACTOR STUDENTS DASHBOARD SO THEY CAN SEE ALL THE LISTED REPORTS ON THE DATABASE

