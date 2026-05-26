# E-Commerce Website

A PHP-based e-commerce web application with customer storefront and admin panel. Built with vanilla PHP, MySQL, and plain CSS.

## Features

**Customer Side**
- User registration and login with password hashing (bcrypt)
- Product browsing with search, category filter, and sorting
- Product detail pages with stock info
- Shopping cart (database for logged-in users, session for guests)
- Checkout with shipping address and payment method selection
- Order history with status tracking

**Admin Panel**
- Dashboard with revenue, order, product, and customer statistics
- Product CRUD (add, edit, delete) with image upload
- Category management (add, edit, delete/deactivate)
- Order management with status updates and filtering
- Protected routes (admin-only access)

## Tech Stack

- **Backend:** PHP 8.x (no framework)
- **Database:** MySQL / MariaDB
- **Frontend:** HTML, CSS, JavaScript (vanilla)
- **Architecture:** MVC-style (controllers + view templates + shared partials)
- **Icons:** FontAwesome 6 (CDN)

## Setup

### Requirements

- PHP 8.0+
- MySQL 5.7+ or MariaDB
- Apache (XAMPP/WAMP) or PHP built-in server

### Installation

1. Clone the repository:
```bash
git clone https://github.com/acesuus/E-Commerce-Website.git
```

2. Import the database:
   - Open phpMyAdmin or MySQL Workbench
   - Run `database.sql` to create the database and tables with sample data

3. Configure the database connection in `config/database.php`:
```php
$host = '127.0.0.1';
$dbname = 'ecommerce_website';
$username = 'root';
$password = '';  // your MySQL password
```

4. Start the server:

**Option A: XAMPP**
- Copy the project folder to `C:\xampp\htdocs\`
- Start Apache and MySQL
- Visit `http://localhost/E-Commerce-Website/`

**Option B: PHP built-in server**
```bash
cd E-Commerce-Website
php -S localhost:8000
```
Then visit `http://localhost:8000/`

## Test Accounts

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@ecommerce.com | admin123 |
| Customer | customer@example.com | customer123 |

## Project Structure

```
E-Commerce-Website/
├── admin/                  # Admin controllers
│   ├── index.php           # Dashboard
│   ├── products.php        # Product list
│   ├── add_product.php     # Add product
│   ├── edit_product.php    # Edit product
│   ├── delete_product.php  # Delete product
│   ├── categories.php      # Category management
│   ├── orders.php          # Order management
│   ├── login.php           # Admin login
│   └── logout.php          # Admin logout
├── user/                   # Customer controllers
│   ├── login.php
│   ├── register.php
│   ├── orders.php
│   └── logout.php
├── views/                  # HTML templates
│   ├── admin/              # Admin views
│   ├── user/               # Customer auth views
│   ├── partials/           # Shared components (header, nav, footer)
│   ├── home.view.php
│   ├── products.view.php
│   ├── product_detail.view.php
│   ├── cart.view.php
│   ├── checkout.view.php
│   └── order_confirmation.view.php
├── config/
│   └── database.php        # DB connection + url() helper
├── includes/
│   ├── session.php         # Auth helpers, CSRF, flash messages
│   ├── header.php
│   └── footer.php
├── css/
│   └── style.css           # All styles
├── uploads/
│   └── products/           # Product images
├── index.php               # Homepage
├── products.php            # Product listing
├── product_detail.php      # Single product
├── cart.php                # Shopping cart
├── checkout.php            # Checkout
├── order_confirmation.php  # Order success
├── database.sql            # Database schema + sample data
└── README.md
```

## Database Schema

- **users** - Customers and admin accounts
- **categories** - Product categories (Electronics, Clothing, Books, Home & Living)
- **products** - Product catalog with images, pricing, stock
- **orders** - Customer orders with status tracking
- **order_items** - Individual items within each order
- **cart** - Persistent shopping cart for logged-in users

## Color Scheme

- Primary: `#2563eb` (blue)
- Success: `#16a34a` (green)
- Warning: `#d97706` (amber)
- Danger: `#dc2626` (red)
- Background: `#f5f7fa` (light gray)
- Text: `#333` / `#2d3748` (dark slate)
- Admin header: `#1e293b` (dark navy)

## License

This project is for educational purposes (Final Project submission).
