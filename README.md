<div align="center">

<img src="logo.png" alt="PC Store Logo" width="80" />

# PC STORE

**A multi-vendor e-commerce platform for PC hardware and accessories**

[![PHP](https://img.shields.io/badge/PHP-8.4-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.4-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://mysql.com)
[![License](https://img.shields.io/badge/License-MIT-1fbb1f?style=for-the-badge)](LICENSE)

[Live Demo](#) · [Report Bug](https://github.com/shanto-joseph/pc-store/issues) · [Request Feature](https://github.com/shanto-joseph/pc-store/issues)

</div>

---

## Overview

PC STORE is a full-stack multi-vendor e-commerce web app where customers can shop for PC components, vendors can list and manage their products, and admins oversee the entire platform. Built with plain PHP, MySQL, and vanilla JS — no frameworks, no Composer required.

---

## Screenshots

| Home | Products | Admin Dashboard |
|:---:|:---:|:---:|
| ![Home](screenshots/Screenshot%202026-03-23%20092922.png) | ![Products](screenshots/Screenshot%202026-03-23%20093711.png) | ![Admin](screenshots/Screenshot%202026-03-23%20105433.png) |

---

## Features

<details>
<summary><strong>🛒 Customer</strong></summary>

- Register, login, forgot password with email reset
- Browse by category, search, filter by price, sort results
- Product detail pages with ratings and live stock status
- Cart management — add, update quantity, remove
- Checkout with shipping address + Razorpay payment gateway
- Order history with downloadable PDF invoices
- Purchase-verified product reviews
- Support ticket system

</details>

<details>
<summary><strong>🏪 Vendor</strong></summary>

- Dedicated vendor registration and dashboard
- Add, edit, delete product listings with image uploads
- Products require admin approval before going live
- Sales analytics and revenue reports
- Low stock alerts
- Support ticket system

</details>

<details>
<summary><strong>🔧 Admin</strong></summary>

- Approve or reject vendor product submissions
- Full product, category, and user management
- Order management with status updates
- Platform-wide sales reports with Chart.js charts
- Support ticket replies
- User deletion with full cascade cleanup

</details>

---

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | PHP 8.4 |
| Database | MySQL 8.4 via PDO |
| Frontend | HTML5, CSS3, Vanilla JavaScript |
| PDF Generation | TCPDF |
| Payments | Razorpay |
| Charts | Chart.js |
| Icons | Boxicons + Custom SVGs |

---

## Project Structure

```
pc-store/
├── admin/          # Admin dashboard pages
├── vendor/         # Vendor dashboard pages
├── css/            # Stylesheets
├── js/             # JavaScript files
├── icon/           # SVG icons
├── image/          # Homepage carousel images
├── uploads/        # Uploaded product images
├── tcpdf/          # PDF library (bundled)
├── config.php      # DB connection (reads .env)
├── env.php         # Lightweight .env loader
├── functions.php   # Shared helper functions
├── database.sql    # Full schema + seed data
└── .env            # Secrets — never committed
```

---

## Getting Started

### Requirements

- WAMP / XAMPP / LAMP
- PHP 8.0+
- MySQL 8.0+

### Installation

**1. Clone the repository**
```bash
git clone https://github.com/shanto-joseph/pc-store.git
cd pc-store
```

**2. Import the database**

In phpMyAdmin, create a database named `my_website` and import `database.sql`.

**3. Set up environment variables**
```bash
cp .env.example .env
```

Edit `.env` with your values:
```env
DB_HOST=localhost
DB_NAME=my_website
DB_USER=root
DB_PASS=

RAZORPAY_KEY_ID=your_key_id_here
RAZORPAY_KEY_SECRET=your_key_secret_here
```

**4. Place in web root**

Move the folder to your WAMP `www` or XAMPP `htdocs` directory, then visit:
```
http://localhost/pc-store/
```

---

## Default Accounts

| Role | Email | Password |
|---|---|---|
| Admin | admin@pcstore.com | Admin@123 |
| Vendor | vendor@pcstore.com | Vendor@123 |

> Register new customer accounts directly from the login page.

---

## Razorpay Test Payments

1. Grab your test API keys from the [Razorpay Dashboard](https://dashboard.razorpay.com)
2. Add them to `.env`
3. Use [Razorpay test cards](https://razorpay.com/docs/payments/payments/test-card-details/) at checkout

---

## Security

- Secrets in `.env` — excluded from git via `.gitignore`
- Passwords hashed with `password_hash()` (bcrypt)
- All queries use PDO prepared statements
- All output escaped with `htmlspecialchars()`
- Reviews restricted to verified purchasers

---

## License

[MIT](LICENSE) — free to use, modify, and distribute.

---

<div align="center">
Made with ☕ by <a href="https://github.com/shanto-joseph">shanto-joseph</a>
</div>
