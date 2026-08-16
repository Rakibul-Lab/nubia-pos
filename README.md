# Nubia Inventory

A production-ready, enterprise-grade **Inventory Management & POS** web application built with **plain PHP 8 (MVC, no framework)**, **MySQL 8**, and a premium SaaS-style Bootstrap 5.3 interface. Designed for retail shops, wholesalers, pharmacies, electronics & fashion stores, restaurants, supermarkets, hardware stores, and virtually any product-based business.

---

## Highlights

- **Clean MVC architecture** — no framework, PSR-style OOP, PDO prepared statements only.
- **Premium UI** — glassmorphism, soft shadows, rounded cards, gradients, dark/light mode, fully responsive (desktop → POS touch screen) with a mobile bottom-nav.
- **Role-based access control** — Super Admin, Admin, Manager, Cashier, Salesman, Store Keeper, Accountant, Employee, each with granular permissions.
- **Secure by default** — CSRF tokens, XSS escaping, SQL-injection-safe queries, password hashing, hardened sessions, activity/audit logs.
- **Complete business suite** — dashboard KPIs & charts, products, multi-warehouse stock, purchases, sales, POS, quotations, customers/suppliers with ledgers, expenses, accounting, reports (CSV/Excel/PDF), and settings.
- **Special feature — Direct Buy & Sell** — fulfil an out-of-stock customer request by buying from a supplier and selling on the spot, with optional inventory addition, automatic profit calculation, and linked purchase/sale records.

---

## Technology Stack

| Layer      | Technology |
|------------|------------|
| Backend    | PHP 8.2+ (8.3 ready), MySQL 8+, PDO, MVC, OOP, Composer |
| Frontend   | HTML5, CSS3, Bootstrap 5.3, JavaScript ES6, AJAX, jQuery |
| UI Libs    | Chart.js, DataTables, SweetAlert2, Toastr, Font Awesome, Material Symbols |
| PHP Libs   | PHPMailer, DomPDF, PhpSpreadsheet, Endroid QR Code, Picqer Barcode Generator |

---

## Requirements

- PHP **8.2** or higher with extensions: `pdo_mysql`, `mbstring`, `openssl`, `gd`, `zip`, `intl`
- MySQL **8.0** or higher (or MariaDB 10.4+)
- Composer 2.x
- A web server (Apache/Nginx) or PHP's built-in server for local development

---

## Installation

### 1. Clone / copy the project

```bash
cd nubia_inventory
```

### 2. Install PHP dependencies

```bash
composer install
```

### 3. Configure environment

Copy the example env file and edit the values:

```bash
cp .env.example .env
```

Set your database credentials and app URL in `.env`:

```dotenv
APP_URL=http://localhost:8000
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=nubia_inventory
DB_USER=root
DB_PASS=
```

> **Tip:** set `APP_URL` to the exact host and port you serve from (e.g. `http://127.0.0.1:8899`) so generated links and redirects point to the right place.

### 4. Create & seed the database

**Option A — one-command installer (recommended, no MySQL CLI needed):**

```bash
php database/install.php
```

This creates the database (if missing) and runs `database/schema.sql` + `database/seed.sql`.

**Option B — manual with the MySQL client:**

```bash
mysql -u root -p -e "CREATE DATABASE nubia_inventory CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p nubia_inventory < database/schema.sql
mysql -u root -p nubia_inventory < database/seed.sql
```

### 5. Run the application

**Local (PHP built-in server):**

```bash
php -S 127.0.0.1:8000 -t public public/index.php
```

Then open <http://127.0.0.1:8000>.

**Production (Apache):** point the virtual host document root at the `public/` directory. The included `.htaccess` files handle rewrites.

---

## Default Login

| Field    | Value              |
|----------|--------------------|
| Email    | `admin@nubia.test` |
| Password | `admin123`         |

> Change this password immediately after your first login (Profile → Change Password).

---

## Project Structure

```
nubia_inventory/
├── app/
│   ├── Controllers/     # Request handlers (one per module)
│   ├── Core/            # Framework: Router, Database, Auth, View, Request, etc.
│   ├── Helpers/         # Global helper functions
│   ├── Models/          # Active-record-lite data models
│   ├── Services/        # Business logic (Stock, Payment, Sale, Mailer)
│   └── Views/           # PHP templates, layouts, partials, components
├── config/
│   ├── config.php       # Configuration loader
│   └── routes.php       # Route definitions
├── database/
│   ├── schema.sql       # Normalized MySQL schema
│   ├── seed.sql         # Initial roles, permissions, demo data
│   └── install.php      # One-command DB installer
├── public/
│   ├── index.php        # Front controller (entry point)
│   ├── .htaccess        # Rewrite rules
│   └── assets/          # CSS, JS, images
├── storage/             # Logs, cache, uploads (writable)
├── vendor/              # Composer dependencies
├── composer.json
├── .env.example
└── README.md
```

---

## Modules

- **Dashboard** — today's sales/purchase/profit/expenses, monthly & yearly revenue/profit, stock value, top products, recent sales, low/out-of-stock alerts, best customers/suppliers, and Chart.js visualisations.
- **Products** — categories, sub-categories, brands, units, warehouses, variants, barcode & QR generation, serial/IMEI, expiry, opening stock, low-stock alerts.
- **Stock** — live levels, adjustments, warehouse-to-warehouse transfers, full movement history.
- **Purchases** — orders, direct/supplier purchases, returns, dues, payments, printable invoices.
- **Sales & POS** — retail/wholesale sales, touch-friendly POS, discounts, tax/VAT, shipping, multiple payment methods, partial/full payment, thermal & A4 & PDF invoices.
- **Quotations** — customer estimates with validity dates.
- **Direct Buy & Sell** — the flagship feature for fulfilling out-of-stock requests on the fly.
- **Customers & Suppliers** — profiles, ledgers, dues, payment history.
- **Expenses** — categorised daily/monthly/yearly expense tracking.
- **Accounting** — cash book, bank book, profit & loss, accounts payable/receivable.
- **Reports** — sales, purchases, profit, expenses, stock, customers, suppliers, with CSV/Excel/PDF export and print.
- **Users, Roles & Permissions** — full RBAC administration.
- **Activity Logs** — audit trail of user actions.
- **Settings** — business info, logos, currency, timezone, tax/VAT, invoice prefix, theme, language.

---

## Security

- All database access uses **PDO prepared statements**.
- **CSRF tokens** protect every state-changing request.
- Output is **HTML-escaped** to prevent XSS.
- Passwords are hashed with `password_hash()` (bcrypt).
- Sessions use secure cookie parameters and ID regeneration.
- Sensitive actions are recorded in **activity logs**.

---

## Development

Run the coding-standards check (PHP_CodeSniffer):

```bash
composer cs
```

Start the dev server via Composer script:

```bash
composer serve
```

---

## License

Proprietary — © Nubia Inventory. All rights reserved.
