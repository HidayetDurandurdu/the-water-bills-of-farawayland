# The Water Bills of Farawayland

A PHP-based water bill management system where users can register, log in, report meter readings, view bills, and pay unpaid bills. Administrators can manage users, issue bills, search users, update user details, and delete bills.

## Features

* User registration and login system
* Admin user account
* Consumer dashboard
* Meter reading submission
* Previous meter report history
* Bill and debt display
* Pay unpaid bills
* Admin panel for managing users
* Search users by water meter ID or address
* Edit user address and water meter ID
* View user reports and bills
* Delete user bills
* JSON-based data storage

## Project Structure

* `index.php` – homepage
* `register.php` – user registration
* `login.php` – user login
* `logout.php` – user logout
* `dashboard.php` – consumer dashboard
* `pay_bill.php` – bill payment logic
* `admin/` – admin pages and user management
* `admin/index.php` – admin dashboard
* `admin/user.php` – user details and bill management
* `includes/` – authentication and storage helpers
* `includes/auth.php` – login and session helper functions
* `includes/storage.php` – JSON data storage functions
* `assets/style.css` – project styling
* `data/` – JSON data files and session storage
* `data/data.json` – stored users, reports, and bills
* `data/sessions/` – session files

## How to Run

1. Download or clone this repository.
2. Place the project folder inside a PHP server environment such as XAMPP, WAMP, MAMP, or Laragon.
3. Start Apache.
4. Open the project in your browser.
5. Register a new user or log in with the admin account.
6. Use the dashboard to submit meter readings and manage bills.

## Notes

* This project uses PHP and JSON files instead of a database.
* The application was created for a PHP assignment.
* No external framework is required.
* The project runs locally using a PHP server.
