# PWeb Booking System

A booking management system for a hair salon, built as the final project for the **Web Programming** course at the University of Pisa — grade **30/30**.

Clients can register, log in, and book appointments online; salon staff manage bookings, clients, and no-shows through a dedicated admin dashboard.

## Features

**Client side**
- Registration and login with server-side validation
- Appointment booking with service selection
- Personal area to view/manage upcoming appointments and edit profile

**Admin side**
- Dashboard with all upcoming appointments
- Client management
- Blacklist management for repeated no-shows ("ghost" bookings)

**Security**
- Prepared statements (mysqli) against SQL injection
- CSRF token verification on all forms
- Session timeout after 30 minutes of inactivity
- "Ghost session" check — invalidates sessions tied to deleted users
- Server-side input validation (e.g. email format)

## Tech stack

- **Backend:** PHP (mysqli, procedural)
- **Database:** MySQL
- **Frontend:** HTML, CSS, vanilla JavaScript

## Project structure

```
├── index.php              # Homepage
├── css/                    # Stylesheets
├── js/                     # Client-side scripts
├── php/
│   ├── admin/               # Admin dashboard, client & blacklist management
│   ├── cliente/              # Client area, booking, profile
│   └── script/                # Shared logic: auth, session, DB connection
├── manuale.html             # User manual
└── *.sql                     # Database schema
```

## Setup

1. Import the `.sql` file into a local MySQL database.
2. Update the database credentials in `php/script/connessione.php` if needed (defaults to local XAMPP/WAMP-style setup: `root`, no password).
3. Serve the project with a local PHP server (e.g. XAMPP, WAMP, or `php -S localhost:8000`).
4. Open `index.php` in the browser.

## Note

This is the original university project. A separate, evolved version of this system is currently running in production for a real hair salon.

---
Student: Xhorxhi Mene — University of Pisa, Computer Engineering
