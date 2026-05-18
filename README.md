# ♻️ Re-fill — Reusable Cup Loyalty App

A secure, web-based loyalty programme that rewards café customers for bringing reusable cups to a network of independent cafés in Sheffield.

Built with PHP 8.2, MySQL, and vanilla JavaScript. No frameworks — just clean, readable code.

## 🌐 Live Demo: https://refill.xo.je/

<img width="1909" height="993" alt="image" src="https://github.com/user-attachments/assets/e3e9c315-adb0-468b-9789-caf5571f7403" />


<img width="1903" height="979" alt="image" src="https://github.com/user-attachments/assets/a2a771e8-e2e4-412c-8c64-8518102781a9" />

---

Use the demo accounts below to explore all three user roles without registering.

**Customer**

| Role | Email | Password |
|------|-------|----------|
| Customer | `demo@refill.app` | `Test@1234` |

**The Daily Grind**

| Role | Email | Password |
|------|-------|----------|
| Staff (Owner) | `owner@dailygrind.co.uk` | `Staff@1234` |
| Staff (Barista) | `barista@dailygrind.co.uk` | `Staff@1234` |

**Brew & Bloom**

| Role | Email | Password |
|------|-------|----------|
| Staff (Owner) | `owner@brewandbloom.co.uk` | `Staff@1234` |
| Staff (Barista) | `barista@brewandbloom.co.uk` | `Staff@1234` |

**Common Ground**

| Role | Email | Password |
|------|-------|----------|
| Staff (Owner) | `owner@commonground.cafe` | `Staff@1234` |
| Staff (Barista) | `barista@commonground.cafe` | `Staff@1234` |

---

## 🚀 Running Locally

### Prerequisites

- [XAMPP](https://www.apachefriends.org/) (Apache 2.4 + PHP 8.2 + MySQL)
- Git

### 1. Clone the repository

```bash
git clone https://github.com/moralisedd/refill.git
```

Place the cloned folder inside your XAMPP `htdocs` directory and rename it to `refill`:

```
C:\xampp\htdocs\refill\
```

### 2. Start XAMPP

Open the XAMPP Control Panel and start both **Apache** and **MySQL**.

### 3. Create the database

1. Open your browser and go to `http://localhost/phpmyadmin`
2. Click **New** in the left sidebar and create a database named `refill`
3. Select the `refill` database, click the **Import** tab
4. Choose `refill_schema.sql` from the project root and click **Go**

This will create all 7 tables, 3 views, and load the demo seed data.

### 4. Configure the database connection

Open `includes/db.php` and update the credentials if needed:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'refill');
define('DB_USER', 'root');   // your MySQL username
define('DB_PASS', '');       // your MySQL password (blank by default in XAMPP)
```

### 5. Open the app

Go to `http://localhost/refill/` in your browser.

Log in with any of the demo accounts above, or register a new customer account.

---

## 📁 Project Structure

```
refill/
├── includes/
│   ├── config.php          # BASE_URL, APP_ENV, error settings
│   ├── db.php              # PDO singleton database connection
│   ├── auth.php            # Login, session management, access guards
│   └── qr.php              # QR token generation & validation (CPM model)
├── customer/
│   ├── register.php        # Account registration
│   ├── login.php           # Customer login
│   ├── dashboard.php       # Points balance + recent activity
│   ├── qr.php              # Dynamic QR code (60-second expiry)
│   ├── rewards.php         # Rewards catalogue
│   └── history.php         # Full transaction history
├── staff/
│   ├── login.php           # Staff login
│   ├── dashboard.php       # Today's scan stats + recent transactions
│   └── scan.php            # QR scanner (camera + manual entry)
├── api/
│   ├── validate.php        # POST: validate QR token, award point
│   └── generate_token.php  # POST: AJAX token refresh
├── admin/
│   └── index.php           # Owner-only analytics panel
├── assets/
│   ├── css/style.css       # WCAG 2.1 AA stylesheet
│   ├── js/app.js           # QR countdown timer, accessibility helpers
│   └── js/qrcode.min.js    # Bundled QR library (no CDN dependency)
├── refill_schema.sql       # Full database schema + seed data
├── .htaccess               # Apache 2.4 config (URL rewriting, security headers)
└── index.php               # Landing page / route dispatcher
```

---

## 🛠️ Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | PHP 8.2 (procedural, PDO prepared statements) |
| Database | MySQL / MariaDB (InnoDB, utf8mb4) |
| Frontend | HTML5, CSS3, JavaScript (vanilla) |
| Server | Apache 2.4 |
| QR Scanning | [html5-qrcode](https://github.com/mebjas/html5-qrcode) |
| Version Control | Git / GitHub |

---

## 🔒 Security Features

- **Dynamic QR tokens** — 60-second expiry + one-time nonce (Alipay CPM model)
- **bcrypt password hashing** at cost factor 12
- **PDO prepared statements** throughout — no raw SQL
- **Session hardening** — `httponly`, `strict_mode`, ID regeneration on login
- **Row-level locking** (`FOR UPDATE`) prevents race conditions on QR validation
- **WCAG 2.1 AA** accessibility compliance

---

## 🧪 Test Flows

**Customer flow:** Register → Log in → My QR → show QR to staff

**Staff scan flow:** Staff login → Scan QR Code → point camera at customer's QR (or paste token manually) → confirm point awarded

**Reward redemption:** Customer reaches required points → staff redeems via admin panel

---

## 📄 Licence

Built as a university assessment project. Not licensed for commercial use.
