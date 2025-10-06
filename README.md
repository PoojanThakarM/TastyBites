# Tasty Bites Restaurant Portal

A web application for managing restaurant FAQs, reservations, orders, and user registration.

## Setup Instructions
1. **Requirements**: PHP 7.4+, MySQL, Apache (e.g., XAMPP).
2. **Database**:
   - Create a MySQL database named `tasty_bites`.
   - Import `database.sql` using phpMyAdmin or the MySQL command line (`mysql -u your_username -p tasty_bites < database.sql`).
3. **Configuration**:
   - Update `db.php` with your MySQL username and password.
4. **Run**:
   - Place the `tasty-bites-portal` folder in your web server’s root (e.g., `htdocs` in XAMPP).
   - Access via `http://localhost/tasty-bites-portal`.
5. **Test Accounts**:
   - Admin: username=`Poojan`, password=`963852741` (update `database.sql` with a real hash if needed).
   - Register new users via `register.php`.

## Features
- User registration with validation, sanitization, and password hashing.
- Admin management for FAQs, reservations, and orders.
- Public FAQ page with dynamic content.
- Role-based access control.

## Screenshots
- (Add descriptions of your planned screenshots here after capturing them in a later step.)