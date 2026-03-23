# PC STORE 🖥️

A full-stack multi-vendor e-commerce platform for PC hardware and accessories, built with PHP, MySQL, and vanilla JavaScript.

![PHP](https://img.shields.io/badge/PHP-8.4-777BB4?logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.4-4479A1?logo=mysql&logoColor=white)
![License](https://img.shields.io/badge/license-MIT-green)

---

## Features

### Customer
- Register / Login / Forgot Password
- Browse products by category, search, price range, sort
- Product details with ratings and stock status
- Add to cart, update quantity, remove items
- Checkout with shipping address + Razorpay payment
- Order history with PDF invoice download
- Submit product reviews (purchase-verified)
- Support ticket system

### Vendor
- Register and manage your own product listings
- Add / Edit / Delete products (with image upload)
- Products go through admin approval before going live
- Sales reports and analytics dashboard
- Low stock alerts
- Support ticket system

### Admin
- Approve / Reject vendor product submissions
- Manage all products, categories, users
- Order management with status updates
- Sales reports with Chart.js visualizations
- Support ticket replies
- Delete users (with full cascade cleanup)

---

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | PHP 8.4 |
| Database | MySQL 8.4 (PDO) |
| Frontend | HTML5, CSS3, Vanilla JS |
| PDF | TCPDF |
| Payment | Razorpay |
| Charts | Chart.js |
| Icons | Boxicons |

---

## Project Structure

```
/
├── admin/          # Admin dashboard pages
├── vendor/         # Vendor dashboard pages
├── css/            # All stylesheets
├── js/             # JavaScript files
├── icon/           # SVG icons
├── image/          # Carousel images
├── bg/             # Background images
├── uploads/        # Product images (auto-created)
├── tcpdf/          # PDF generation library
├── config.php      # DB connection (uses .env)
├── env.php         # .env loader (no Composer needed)
├── functions.php   # All shared PHP functions
├── database.sql    # Full DB schema + seed data
└── .env            # Secret keys (never committed)
```

---

## Setup

### Requirements
- WAMP / XAMPP / LAMP
- PHP 8.0+
- MySQL 8.0+

### Steps

**1. Clone the repo**
```bash
git clone https://github.com/yourusername/pc-store.git
cd pc-store
```

**2. Import the database**

Open phpMyAdmin, create a database called `my_website`, then import:
```
database.sql
```

**3. Configure environment**

Copy the example and fill in your values:
```bash
cp .env.example .env
```

```env
DB_HOST=localhost
DB_NAME=my_website
DB_USER=root
DB_PASS=

RAZORPAY_KEY_ID=your_key_id_here
RAZORPAY_KEY_SECRET=your_key_secret_here
```

**4. Create uploads folder** (if not already present)
```bash
mkdir uploads
```

**5. Serve the project**

Place the folder in your WAMP/XAMPP `www` or `htdocs` directory and visit:
```
http://localhost/pc-store/
```

---

## Default Accounts

| Role | Email | Password |
|---|---|---|
| Admin | admin@pcstore.com | Admin@123 |
| Vendor | vendor@pcstore.com | Vendor@123 |

> You can register new customer accounts from the login page.

---

## Payment (Razorpay)

This project uses [Razorpay](https://razorpay.com) in test mode. To test payments:

1. Get your test API keys from the [Razorpay Dashboard](https://dashboard.razorpay.com)
2. Add them to your `.env` file
3. Use Razorpay's [test card numbers](https://razorpay.com/docs/payments/payments/test-card-details/) at checkout

---

## Screenshots

| Home | Products | Admin Dashboard |
|---|---|---|
| ![home](screenshots/Screenshot%202026-03-23%20092922.png) | ![products](screenshots/Screenshot%202026-03-23%20093711.png) | ![admin](screenshots/Screenshot%202026-03-23%20105433.png) |

---

## Security Notes

- All secrets stored in `.env` — never committed to git
- Passwords hashed with `password_hash()` (bcrypt)
- All DB queries use PDO prepared statements
- All user output escaped with `htmlspecialchars()`
- Reviews restricted to verified purchasers only

---

## License

MIT — free to use, modify, and distribute.
