# Tasty Bites Restaurant Portal

A web application for managing restaurant orders, FAQs, reservations, and user registration.

## Setup Instructions
1. **Requirements**: PHP 7.4+, MySQL, Apache (e.g., XAMPP).
2. **Database**:
   - Create a MySQL database named `tasty_bites`.
   - Import `database.sql` using phpMyAdmin or the MySQL command line (`mysql -u your_username -p tasty_bites < database.sql`).
3. **Configuration**:
   - Update `db.php` with your MySQL username and password.
4. **Run**:
   - Place the `tasty-bites-portal` folder in your web server’s root (e.g., `htdocs` in XAMPP).
   - Access via `http://localhost/TastyBites`.
5. **Test Accounts**:
   - Admin: username=`admin`, password=`Admin123` (update `database.sql` with a real hash if needed).
   - Register new users via `register.php`.

## Features
- User registration with validation, sanitization, and password hashing.
- Admin management for FAQs, reservations, and orders.
- Public FAQ page with dynamic content.
- Role-based access control.
- Order placement and mock payment processing with status tracking.

## Payment Feature
- The portal includes a mock payment system for orders, simulating a secure checkout process.
- Users can create orders via `orders.php`, proceed to `payment.php` to simulate payment with a 16-digit card number, and receive confirmation on `order_confirmation.php`.
- Admins can manage payment statuses (e.g., "Pending", "Completed", "Failed") in `admin_orders.php`.

## Testing
- **Registration**: Test `register.php` with valid and invalid data (e.g., short password, duplicate username).
- **Login**: Use admin credentials to access `admin_dashboard.php` or similar.
- **FAQs**: Verify `faq.php` displays sample FAQs.
- **Admin Access**: Check `admin_faq.php`, `admin_reservations.php`, `admin_orders.php` as admin.
- **Order Placement**: 
  - Visit `orders.php`, log in, and create an order with valid items from the menu (e.g., "Pizza x2" for ₹400).
  - Submit the form and click "Pay Now" to go to `payment.php`.
  - Enter a 16-digit number (e.g., 4111111111111111) to simulate payment and confirm redirection to `order_confirmation.php`.
- **Payment Status**: Log in as admin, visit `admin_orders.php`, and update the payment status to "Completed" to verify database updates.
- **Navigation**: Ensure all `navbar.php` links work.

## Screenshots
- Registration Form: `register.php` default state.
- Validation Errors: Submit with invalid data (e.g., "John123!" for name).
- Successful Registration: After valid submission, show success message or `profile.php`.
- Order Creation: `orders.php` with sample items.
- Payment Page: `payment.php` with order details.
- Order Confirmation: `order_confirmation.php` after payment.
- Admin Orders: `admin_orders.php` showing payment status.

## Notes
- The payment system is a mock implementation for demonstration. For real transactions, integrate a payment gateway like Razorpay or Stripe.
- Ensure `menu_items` table has data for order items to work.