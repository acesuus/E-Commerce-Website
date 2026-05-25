# E-Commerce Website - Template & Backend Variable Reference

This document maps every page's HTML template to its backend PHP variables and corresponding database table columns.

---

## Database Tables & Columns

### `users` table
| Column | Type | Notes |
|--------|------|-------|
| user_id | INT (PK, AUTO_INCREMENT) | |
| full_name | VARCHAR(100) | NOT NULL |
| email | VARCHAR(100) | UNIQUE, NOT NULL |
| password_hash | VARCHAR(255) | bcrypt hashed |
| phone | VARCHAR(20) | nullable |
| address | TEXT | nullable |
| user_type | ENUM('customer','admin') | default 'customer' |
| created_at | TIMESTAMP | auto |
| updated_at | TIMESTAMP | auto on update |

### `categories` table
| Column | Type | Notes |
|--------|------|-------|
| category_id | INT (PK) | |
| category_name | VARCHAR(50) | UNIQUE, NOT NULL |
| description | TEXT | |
| status | ENUM('active','inactive') | default 'active' |
| created_at | TIMESTAMP | |

### `products` table
| Column | Type | Notes |
|--------|------|-------|
| product_id | INT (PK) | |
| product_name | VARCHAR(200) | NOT NULL |
| description | TEXT | |
| price | DECIMAL(10,2) | >= 0 |
| stock_quantity | INT | >= 0 |
| product_image | VARCHAR(255) | file path |
| category_id | INT (FK) | references categories |
| status | ENUM('available','out_of_stock','discontinued') | |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

### `orders` table
| Column | Type | Notes |
|--------|------|-------|
| order_id | INT (PK) | |
| user_id | INT (FK) | references users |
| order_date | TIMESTAMP | auto |
| total_amount | DECIMAL(10,2) | >= 0 |
| order_status | ENUM('pending','processing','completed','cancelled') | |
| shipping_address | TEXT | NOT NULL |
| payment_method | VARCHAR(50) | default 'cash_on_delivery' |
| notes | TEXT | nullable |

### `order_items` table
| Column | Type | Notes |
|--------|------|-------|
| order_item_id | INT (PK) | |
| order_id | INT (FK) | references orders |
| product_id | INT (FK) | references products |
| quantity | INT | > 0 |
| unit_price | DECIMAL(10,2) | >= 0 |
| subtotal | DECIMAL(10,2) | GENERATED (quantity * unit_price) |

### `cart` table
| Column | Type | Notes |
|--------|------|-------|
| cart_id | INT (PK) | |
| user_id | INT (FK) | references users |
| product_id | INT (FK) | references products |
| quantity | INT | > 0 |
| added_at | TIMESTAMP | |
| UNIQUE(user_id, product_id) | | prevents duplicates |

---

## Session Variables (Available Globally After Login)

```php
$_SESSION['user_id']       // INT - from users.user_id
$_SESSION['full_name']     // STRING - from users.full_name
$_SESSION['email']         // STRING - from users.email
$_SESSION['user_type']     // STRING - 'customer' or 'admin'
$_SESSION['logged_in_at']  // INT - Unix timestamp of login
$_SESSION['csrf_token']    // STRING - CSRF protection token
$_SESSION['flash']         // ARRAY ['type'=>'success|error|warning', 'message'=>'...']
$_SESSION['cart']          // ARRAY [product_id => quantity] (guest users only)
```

---

## Page-by-Page Template Variable Reference

---

### 1. `user/login.php` - Customer Login

**Backend Variables Available in Template:**
```php
$csrf_token   // STRING - CSRF token for form
$old_email    // STRING - previously entered email (for repopulation)
$errors       // ARRAY - validation errors ['email'=>'...', 'password'=>'...'] or [0=>'generic error']
$flash        // ARRAY|null - flash message ['type'=>'success', 'message'=>'Registration successful!']
```

**Database Query (on POST):**
```sql
SELECT * FROM users WHERE email = ? AND user_type = 'customer'
-- Returns: user_id, full_name, email, password_hash, phone, address, user_type, created_at, updated_at
```

**Template displays:**
- `$flash['message']` - Success/error flash
- `$errors[0]` - General login error
- `$errors['email']` - Email field error
- `$errors['password']` - Password field error
- `$old_email` - Repopulates email input on failed attempt

---

### 2. `user/register.php` - Customer Registration

**Backend Variables Available in Template:**
```php
$csrf_token   // STRING - CSRF token
$old          // ARRAY - previous form values for repopulation:
              //   $old['full_name']  - from users.full_name (VARCHAR 100)
              //   $old['email']      - from users.email (VARCHAR 100)
              //   $old['phone']      - from users.phone (VARCHAR 20)
              //   $old['address']    - from users.address (TEXT)
$errors       // ARRAY - field-specific errors:
              //   $errors['full_name'], $errors['email'], $errors['phone'],
              //   $errors['address'], $errors['password'], $errors['confirm_password']
              //   $errors[0] - generic error
```

**Form fields map to `users` table:**
| Form Field | DB Column | Validation |
|------------|-----------|------------|
| full_name | users.full_name | 3-100 chars, letters/spaces/dots/hyphens |
| email | users.email | valid email, unique, max 100 |
| phone | users.phone | optional, 7-20 digits/symbols |
| address | users.address | optional, max 500 |
| password | users.password_hash | 8-72 chars, 1 upper, 1 lower, 1 number |
| confirm_password | (verification only) | must match password |

---

### 3. `index.php` - Homepage

**Backend Variables Available in Template:**
```php
$featured_products  // ARRAY of products (max 8, newest available)
  // Each item: product_id, product_name, description, price, stock_quantity,
  //            product_image, category_id, status, created_at, updated_at, category_name

$categories         // ARRAY of categories with product counts
  // Each item: category_id, category_name, description, status, created_at, product_count
```

**Session-based display:**
- `isLoggedIn()` → shows "Hi, {full_name}" + My Orders + Logout
- `!isLoggedIn()` → shows Sign In + Register buttons

---

### 4. `products.php` - Product Listing with Search/Filter

**Backend Variables Available in Template:**
```php
$products           // ARRAY - filtered products list
  // Each: product_id, product_name, description, price, stock_quantity,
  //        product_image, category_id, status, created_at, updated_at, category_name

$categories         // ARRAY - all active categories for filter dropdown
  // Each: category_id, category_name, description, status, created_at

$search             // STRING - current search term (from $_GET['search'])
$category_id        // INT - selected category filter (from $_GET['category'])
$sort               // STRING - sort option: 'newest'|'price_low'|'price_high'|'name_az'|'name_za'
$product_count      // INT - total products matching filters
$active_category_name // STRING - name of selected category (or empty)
```

---

### 5. `product_detail.php` - Single Product Page

**Backend Variables Available in Template:**
```php
$product            // ARRAY - single product record
  // product_id, product_name, description, price, stock_quantity,
  // product_image, category_id, status, created_at, updated_at, category_name

$related_products   // ARRAY - up to 4 same-category products
  // Same structure as $product

$stock_status       // STRING - 'In Stock' | 'Out of Stock' | 'Only X left!'
$stock_class        // STRING - '' | 'out' | 'low' (CSS class)
```

---

### 6. `cart.php` - Shopping Cart

**Backend Variables Available in Template:**
```php
$cart_items   // ARRAY - cart items with full product info
  // Logged-in users (from DB): cart_id, quantity, added_at,
  //   product_id, product_name, price, stock_quantity, product_image, status, category_name
  // Guest users (from session): product_id, product_name, price,
  //   stock_quantity, product_image, status, category_name, quantity

$cart_total   // FLOAT - sum of (price * quantity) for all items
$cart_count   // INT - sum of all quantities
$flash        // ARRAY|null - flash message from cart actions
```

**Cart item data sources:**
| Variable | DB Source |
|----------|-----------|
| product_id | products.product_id |
| product_name | products.product_name |
| price | products.price |
| stock_quantity | products.stock_quantity |
| product_image | products.product_image |
| status | products.status |
| category_name | categories.category_name |
| quantity | cart.quantity |
| added_at | cart.added_at |

---

### 7. `checkout.php` - Checkout Page

**Backend Variables Available in Template:**
```php
$user               // ARRAY - full user record from users table
  // user_id, full_name, email, phone, address, user_type, created_at, updated_at

$cart_items         // ARRAY - same as cart.php structure
$cart_total         // FLOAT - order total
$cart_count         // INT - total items
$unavailable_items  // ARRAY of product names that are out of stock
$errors             // ARRAY - validation errors
$csrf_token         // STRING - CSRF token
```

**User fields displayed (pre-filled, disabled):**
| Display | Source |
|---------|--------|
| Full Name | $user['full_name'] → users.full_name |
| Email | $user['email'] → users.email |
| Phone | $user['phone'] → users.phone |
| Address (editable) | $user['address'] → users.address (default value) |

**Form submission creates:**
| orders column | Source |
|---------------|--------|
| user_id | $_SESSION['user_id'] |
| total_amount | $cart_total |
| order_status | 'pending' (hardcoded) |
| shipping_address | $_POST['shipping_address'] |
| payment_method | $_POST['payment_method'] |
| notes | $_POST['notes'] |

---

### 8. `order_confirmation.php` - Order Success

**Backend Variables Available in Template:**
```php
$order              // ARRAY - the completed order
  // order_id, user_id, order_date, total_amount, order_status,
  // shipping_address, payment_method, notes

$order_items        // ARRAY - items in this order
  // order_item_id, order_id, product_id, quantity, unit_price, subtotal,
  // product_name, product_image, category_name

$payment_display    // STRING - human-readable payment method name
$status_class       // STRING - CSS class for status badge
```

---

### 9. `user/orders.php` - Order History

**Backend Variables Available in Template:**
```php
// ORDER LIST VIEW:
$orders             // ARRAY - all user orders with aggregates
  // order_id, user_id, order_date, total_amount, order_status,
  // shipping_address, payment_method, notes, item_count, total_items

// ORDER DETAIL VIEW (when ?view=ID):
$order_detail       // ARRAY|null - single order record (same columns as $orders)
$order_items        // ARRAY - items for viewed order
  // order_item_id, order_id, product_id, quantity, unit_price, subtotal,
  // product_name, product_image, product_status, category_name

// Helpers:
$payment_labels     // ARRAY - ['cash_on_delivery'=>'Cash on Delivery', ...]
$status_config      // ARRAY - ['pending'=>['class'=>'...','icon'=>'...','label'=>'...']]
```

---

### 10. `admin/login.php` - Admin Login

**Backend Variables Available in Template:**
```php
$csrf_token   // STRING
$old_email    // STRING
$errors       // ARRAY - same structure as user login
$flash        // ARRAY|null
```

**Database Query:**
```sql
SELECT * FROM users WHERE email = ? AND user_type = 'admin'
```

---

### 11. `admin/index.php` - Admin Dashboard

**Backend Variables Available in Template:**
```php
$pageTitle    // STRING - 'Admin Dashboard'
// Session: $_SESSION['full_name'], $_SESSION['email']
```

**Access Control:** `requireAdmin()` — redirects non-admin users

---

## Test Credentials

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@ecommerce.com | admin123 |
| Customer | customer@example.com | customer123 |

---

## Sample Data in Database

### Categories (4):
Electronics, Clothing, Books, Home & Living

### Products (9):
iPhone 15 Pro ($999.99), Samsung Galaxy S24 ($899.99), MacBook Pro 14" ($1999.99), Cotton T-Shirt ($19.99), Jeans Pants ($49.99), Python Programming ($39.99), Web Development Guide ($45.99), Desk Lamp ($29.99), Coffee Mug ($9.99)

### Sample Order:
Order #1 by John Doe (user_id=2), total $1069.98, status: completed, items: iPhone 15 Pro x1 + Cotton T-Shirt x3
