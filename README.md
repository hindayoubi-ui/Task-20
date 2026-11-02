 Small Business Management System

 About the Project

This is my Full-Stack Final Project. I built this web application to help a small business organize its daily work in one place.

The system allows users to log in according to their role and helps the Admin manage customers, products, orders, and employees.

I used PHP and MySQL for the backend and HTML, CSS, Bootstrap, and JavaScript for the frontend.

 Main Features

 1. Login and User Roles
- Users can log in using their email and password.
- The system has two roles: Admin and Employee.
- The selected role must match the role saved in the database.
- Passwords are protected using password hashing.
- PHP sessions are used to keep users logged in.
- Disabled employee accounts cannot log in.
- Users can log out securely.

 2. Dashboard

The dashboard displays:
- Total Customers
- Total Products
- Total Orders
- Total Employees
- Total Sales from completed orders

 3. Customer Management

The Admin can add, view, search, edit, and delete customers.

Customer information includes:
- Name
- Phone
- Email
- Address

 4. Product Management

The Admin can add, view, edit, and delete products.

The system also supports:
- Product image uploads
- Searching products
- Filtering products by category

 5. Order Management

The Admin can create orders by selecting a customer and adding multiple products with their quantities.

The system calculates the total automatically and saves the order details.

The Admin can also view orders and update their status.

Available statuses:
- Pending
- Processing
- Completed
- Cancelled

 6. Employee Management

The Admin can add, view, search, edit, and disable employee accounts.

Employee information includes:
- Name
- Email
- Phone
- Position

The Admin can also create additional Admin accounts from the protected Add New Admin page.

 Technologies Used

- HTML
- CSS
- Bootstrap
- Bootstrap Icons
- JavaScript
- PHP
- MySQL
- XAMPP
- Git and GitHub



 How to Run the Project

1. Install and open XAMPP.
2. Start Apache and MySQL.
3. Copy the project folder into the `htdocs` folder.
4. Open phpMyAdmin and create a database named `task 20`.
5. Import the SQL database file.
6. Open `conn.php` and check the database connection details.
7. Open the project using localhost in your browser.
8. Go to `login.php` to start using the system.

 Database and Security

The project uses MySQL to store users, customers, products, orders, and order items.

Prepared statements are used for database queries that receive user input.

Passwords are hashed before being saved. PHP sessions and role checks are also used to protect the system pages.

Product images are uploaded to the `uploads/products/` folder.

 Notes

- An Employee account can be registered from the registration page.
- Admin pages are restricted to users with the Admin role.
- A new Admin account can only be created by an already logged-in Admin.
- The database name used by this project is `task 20`.




