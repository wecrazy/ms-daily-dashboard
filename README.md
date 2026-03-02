<p align="center">
  <img src="https://img.shields.io/badge/PHP-8.2+-777BB4?logo=php&logoColor=white" alt="PHP 8.2+">
  <img src="https://img.shields.io/badge/Composer-PSR--4-885630?logo=composer&logoColor=white" alt="Composer">
  <img src="https://img.shields.io/badge/AdminLTE-3-007bff?logo=bootstrap&logoColor=white" alt="AdminLTE 3">
  <img src="https://img.shields.io/badge/Highcharts-Charts-6C63FF" alt="Highcharts">
  <img src="https://img.shields.io/badge/Android-WebView_APK-3DDC84?logo=android&logoColor=white" alt="Android">
  <img src="https://img.shields.io/badge/License-MIT-green" alt="MIT License">
</p>

# 📊 MS Daily Dashboard

**Manage Service Daily Dashboard** — A real-time monitoring dashboard that visualizes Odoo helpdesk task data across multiple partner companies with auto-rotating slideshows, interactive charts, and SLA deadline tracking.

<p align="center">
  <a href="https://youtube.com/shorts/9k9UMesK8GI">
    <img src="https://img.shields.io/badge/▶_Demo_Video-YouTube-FF0000?style=for-the-badge&logo=youtube&logoColor=white" alt="Demo Video">
  </a>
</p>

---

## ✨ Features

- **Multi-partner dashboard** — Single template serves ARTAJASA, CIMB NIAGA, DANA, MANDIRI, MTI, NDP, OVO
- **Auto-rotating slideshow** — Cycles through 6 chart views per partner, then redirects to the next
- **Interactive Highcharts** — Stacked columns, line overlays, and pie charts
- **Real-time data** — Today's task assignments updated via cron exports
- **SLA deadline tracking** — Pie charts grouped by days and hours remaining
- **Technician summary** — Top 10 most/least visited and task completion rankings
- **Secure authentication** — SHA-256 hashed passwords with cookie-based sessions
- **Android companion app** — Fullscreen WebView APK with token-based auto-login (no URL bar)

---

## 🏗️ Project Structure

```
ms-daily-dashboard/
├── bin/                              # CLI export scripts (cron jobs)
│   ├── export-tasks.php              #   Scheduled task export
│   ├── export-tasks-now.php          #   Real-time task export
│   ├── export-stages.php             #   Stage data export
│   └── export-sla.php               #   SLA deadline export
│
├── database/                         # Database schema
│   └── login.sql                     #   Session/auth tables
│
├── public/                           # Web root (Apache document root)
│   ├── index.php                     #   Front controller & router
│   ├── .htaccess                     #   URL rewriting
│   └── assets/
│       ├── css/login.css             #   Neumorphic login styles
│       └── js/
│           ├── login.js              #   Login form handler
│           └── dashboard.js          #   Slideshow controller
│
├── src/                              # Application source (PSR-4)
│   ├── Auth/
│   │   └── SessionAuth.php           #   Cookie-based authentication
│   ├── Config/
│   │   ├── Config.php                #   .env loader (singleton)
│   │   ├── Database.php              #   PDO connection factory
│   │   └── Partners.php              #   Partner list & rotation logic
│   ├── Http/
│   │   └── OdooClient.php            #   Odoo JSON-RPC API client
│   └── Service/
│       ├── OdooExporter.php          #   Export tasks/stages/SLA to JSON
│       ├── SlaDataService.php        #   SLA deadline data processing
│       ├── StageDataService.php      #   Stage & ticket type aggregation
│       ├── TaskDataService.php       #   Weekly/daily task chart data
│       └── TechnicianDataService.php #   Technician ranking data
│
├── storage/                          # Runtime data (gitignored)
│   └── log/                          #   JSON data files from exports
│
├── templates/                        # View templates
│   ├── auth/login.php                #   Login page
│   ├── layout/
│   │   ├── head.php                  #   CDN links (CSS/JS)
│   │   ├── scripts.php               #   Bootstrap + AdminLTE JS
│   │   └── footer.php                #   Page footer
│   ├── dashboard/
│   │   ├── partner.php               #   Main dashboard (all partners)
│   │   └── partials/
│   │       ├── content_header.php    #   Header with EXIT button
│   │       ├── loading.php           #   Bootstrap spinner
│   │       ├── last_week_graphic.php #   Last week stacked column
│   │       ├── running_week_graphic.php        # Current week chart
│   │       ├── running_week_graphic_realtime.php # Today's data
│   │       ├── pie_chart_stage.php             # Stage pie chart
│   │       ├── pie_chart_stage_ticket_type.php # Stage × ticket type
│   │       └── pie_chart_sla_deadline.php      # SLA deadline pies
│   └── technician/
│       └── summary.php               #   Technician ranking charts
│
├── android/                          # Android companion app
│   ├── app/
│   │   ├── build.gradle              #   App module (SDK 35, Java 17)
│   │   ├── proguard-rules.pro        #   R8/ProGuard config
│   │   └── src/main/
│   │       ├── AndroidManifest.xml    #   Permissions + fullscreen
│   │       ├── java/.../MainActivity.java  # WebView + token auth
│   │       └── res/                  #   Layouts, themes, drawables
│   ├── build.gradle                  #   AGP 9.0.1 plugin
│   ├── settings.gradle               #   Project name
│   └── gradle/                       #   Wrapper (Gradle 9.3.1)
│
├── .env.example                      # Environment template
├── .htaccess                         # Root → public/ rewrite
├── composer.json                     # Dependencies & autoloading
├── Makefile                          # Dev shortcuts (PHP + Android)
└── LICENSE
```

---

## 🚀 Getting Started

### Prerequisites

- **PHP 8.2+** with extensions: `curl`, `pdo_mysql`, `json`, `mbstring`
- **Composer** 2.x
- **MySQL** 5.7+ / MariaDB 10.3+
- **Apache** with `mod_rewrite` enabled

### Installation

```bash
# Clone the repository
git clone https://github.com/your-org/ms-daily-dashboard.git
cd ms-daily-dashboard

# Install dependencies
make dev

# Create environment file
make env

# Edit .env with your credentials
nano .env

# Setup database (auto-creates DB, tables, and seeds default users)
make db-setup

# Start the server
make serve
```

### Configuration

Copy `.env.example` to `.env` and update the values:

```env
# Application
APP_DEBUG=false
APP_TIMEZONE=Asia/Jakarta

# Database (Login system)
DB_HOST=localhost
DB_PORT=3306
DB_USERNAME=dashboard
DB_PASSWORD=dashboard
DB_DATABASE=login

# Odoo API
ODOO_URL=https://your-odoo-instance.com:13070
ODOO_DB=your_db
ODOO_LOGIN=your_email@example.com
ODOO_PASSWORD=your_password

# Company IDs for Odoo queries
ODOO_COMPANY_IDS=13,18,7,5,2,17,4,14,15,12,3,8,19,20,21,22

# Partner rotation order
PARTNER_ROTATION=ARTAJASA,DANA,MANDIRI,MTI,NDP,OVO

# Seconds before auto-redirect to next partner
SLIDESHOW_REDIRECT_SECONDS=120
```

---

## 🖥️ Usage

### Development Server

```bash
make serve
# → http://localhost:8080
```

### Apache Virtual Host

Point your document root to the `public/` directory:

```apache
<VirtualHost *:80>
    ServerName dashboard.local
    DocumentRoot /path/to/ms-daily-dashboard/public

    <Directory /path/to/ms-daily-dashboard/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

### Cron Jobs (Data Export)

Set up cron to periodically fetch data from Odoo:

```cron
# Export tasks every 30 minutes
*/30 * * * * cd /path/to/project && php bin/export-tasks.php >> /dev/null 2>&1

# Export real-time tasks every 2 minutes
*/2  * * * * cd /path/to/project && php bin/export-tasks-now.php >> /dev/null 2>&1

# Export stages every hour
0    * * * * cd /path/to/project && php bin/export-stages.php >> /dev/null 2>&1

# Export SLA deadlines every hour
0    * * * * cd /path/to/project && php bin/export-sla.php >> /dev/null 2>&1
```

---

## 🛣️ Routes

| Method | Route | Description |
|--------|-------|-------------|
| `GET` | `/` | Redirect to first partner dashboard |
| `GET` | `/login` | Login page |
| `POST` | `/login/process` | Authentication endpoint |
| `GET` | `/dashboard/{partner}` | Partner dashboard (e.g., `/dashboard/ARTAJASA`) |
| `GET` | `/technician/{partner}` | Technician summary page |
| `GET` | `/logout` | Clear session & redirect to login |

---

## 📈 Dashboard Views

Each partner dashboard cycles through **6 chart views** in a timed slideshow:

| # | View | Duration | Chart Type |
|---|------|----------|------------|
| 1 | Last Week Summary | 10s | Stacked column + line |
| 2 | Current Week Summary | 12s | Stacked column + line |
| 3 | Today Real-Time | 7s | Stacked column |
| 4 | Stage Pie Chart | 12s | Pie chart |
| 5 | Stage × Ticket Type | 17s | Multiple pie charts |
| 6 | SLA Deadline | 20s | 3 pie charts (prev month / days / hours) |

After completing all views (~78s), the cycle repeats until the **120-second redirect** timer sends the browser to the next partner.

---

## 🧱 Architecture

```
Browser Request
      │
      ▼
 .htaccess (root)
      │ rewrite to public/
      ▼
 public/index.php (Front Controller)
      │
      ├─► SessionAuth        → Cookie + MySQL session validation
      ├─► Config              → .env singleton loader
      ├─► Partners            → Partner list & rotation logic
      │
      ├─► TaskDataService     → Last week / running week / real-time data
      ├─► StageDataService    → Stage pie / ticket type breakdown
      ├─► SlaDataService      → SLA deadline grouping
      └─► TechnicianDataService → Top 10 rankings
              │
              ▼
        templates/*.php       → Highcharts rendering
```

---

## 🔧 Make Commands

| Command | Description |
|---------|-------------|
| `make db-setup` | Create database, tables, and seed users |
| `make db-setup-force` | Same as above, skip confirmation |
| `make dev` | Install all dependencies (including dev) |
| `make install` | Install production dependencies only |
| `make serve` | Start PHP dev server on port 8080 |
| `make test` | Run PHPUnit tests |
| `make lint` | Check PHP syntax across `src/` |
| `make clean` | Remove `vendor/` and `composer.lock` |
| `make env` | Create `.env` from `.env.example` |
| `make export-tasks` | Manually run task export |
| `make export-tasks-now` | Manually run real-time export |
| `make export-stages` | Manually run stage export |
| `make export-sla` | Manually run SLA export |
| `make apk-debug` | Build Android debug APK |
| `make apk-release` | Build Android release APK |
| `make apk-clean` | Clean Android build artifacts |
| `make apk-install` | Build & install debug APK on connected device |

---

## 📱 Android App

A companion **WebView APK** that shows the dashboard fullscreen — no login screen, no URL bar.

### How it works

1. The app loads the dashboard URL with `?token=<APP_TOKEN>` appended
2. `SessionAuth::isTokenAuthenticated()` validates the token against `APP_TOKEN` in `.env`
3. The user sees the live rotating dashboard immediately — no manual login required

### Build the APK

```bash
# Prerequisites: Java 17+ and Android SDK (or Android Studio)
make apk-debug

# Output: android/app/build/outputs/apk/debug/app-debug.apk
```

### Configuration

Edit `DASHBOARD_URL` and `APP_TOKEN` in [android/app/build.gradle](android/app/build.gradle):

```gradle
// Debug — emulator loopback to host machine
buildConfigField "String", "DASHBOARD_URL", '"http://192.168.1.12:8090"'
buildConfigField "String", "APP_TOKEN", '"ms-dashboard-2026-secret"'

// Release — production server
buildConfigField "String", "DASHBOARD_URL", '"https://your-dashboard.example.com"'
```

### Tech

| Component | Version |
|-----------|---------|
| Android Gradle Plugin | 9.0.1 |
| Gradle | 9.3.1 |
| compileSdk / targetSdk | 35 |
| minSdk | 24 (Android 7.0) |
| Java | 17 |
| UI | Fullscreen immersive + SwipeRefreshLayout |

---

## 🛡️ Tech Stack

| Layer | Technology |
|-------|-----------|
| **Language** | PHP 8.2+ (strict types) |
| **Autoloading** | Composer PSR-4 (`MsDashboard\`) |
| **Configuration** | vlucas/phpdotenv |
| **Database** | MySQL / MariaDB (PDO) |
| **External API** | Odoo JSON-RPC |
| **Frontend** | AdminLTE 3 + Bootstrap 5 (CDN) |
| **Charts** | Highcharts.js (CDN) |
| **Auth** | SHA-256 + cookie sessions |
| **Server** | Apache with mod_rewrite |
| **Mobile** | Android WebView (Java, AGP 9.0.1) |

---

## 📄 License

This project is licensed under the **MIT License** — see the [LICENSE](LICENSE) file for details.
