# Maruti Sales & Services &mdash; CRM, Quotation & Targets System
**Built by Maxeco Energy for Maruti Sales & Services, Guwahati, Assam.**
*(A Complete IT Solution &mdash; Computer Hardware, CCTV & Security, Networking, Software & AMC)*

🌐 **Live Remote Web App**: [https://maxecoenergytech.github.io/maruti/](https://maxecoenergytech.github.io/maruti/)  
*(Instant quotation generator with official letterhead, Indian GST calculation, and digital signature &mdash; zero installation required, runs on any phone, tablet, or PC).*

---

## 1. System Overview

A lightweight, enterprise-grade web-based business management system designed specifically for Indian IT sales & service firms. Optimized for shared hosting (cPanel) or basic VPS servers with zero heavy dependencies.

- **Stack**: Laravel 11, PHP 8.3 / 8.2, Filament v3 Admin Panel, MySQL / SQLite, DomPDF, Tailwind CSS.
- **Company Defaults**:
  - **Firm Name**: `MARUTI SALES & SERVICES`
  - **Tagline**: `(A Complete IT Solution)`
  - **Address**: Mahabir Market, Ground Floor, opp. Hotel Siroy Lily, Solapara Road, Paltan Bazar, Guwahati - 781008, Assam
  - **GSTIN**: `18ABGFM5224K1Z2` | **PAN**: `ABGFM5224K` | **State Code**: `18` (Assam)
  - **Phone**: `+91 70022 53021` | **Email**: `smarutisales@gmail.com`

---

## 2. Server Requirements

- **PHP**: `^8.2` or `^8.3`
- **Database**: MySQL 8.0+ / MariaDB 10.4+ (or SQLite 3.35+ for local testing)
- **Composer**: `^2.x`
- **Required PHP Extensions**:
  - `bcmath`, `ctype`, `curl`, `dom`, `fileinfo`, `gd`, `intl`, `mbstring`, `openssl`, `pdo_mysql` (or `pdo_sqlite`), `tokenizer`, `xml`, `zip`

---

## 3. Quick Installation (Local / Staging)

```bash
# 1. Clone the repository
git clone https://github.com/maxeco-energy/maruti-crm.git
cd maruti-crm

# 2. Install dependencies (no heavy assets or build tools needed)
composer install

# 3. Environment setup
cp .env.example .env
php artisan key:generate

# 4. Configure Database in .env
# For SQLite (default development):
touch database/database.sqlite
# Or for MySQL:
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_DATABASE=maruti_crm
# DB_USERNAME=maruti_user
# DB_PASSWORD=your_password

# 5. Run Migrations & Seed Demo Data
php artisan migrate --seed

# 6. Start the local server
php artisan serve --port=8000
```
Open [http://127.0.0.1:8000/admin](http://127.0.0.1:8000/admin) in your web browser.

---

## 4. Default Seeded Credentials

| Role | Name | Email | Default Password | Access Level |
| :--- | :--- | :--- | :--- | :--- |
| **Owner / Admin** | Dipankar Das | `admin@marutisales.in` | `Admin@123` | Full firm access, targets, settings, users, all reports |
| **Sales Executive** | Rahul Sharma | `rahul@marutisales.in` | `Sales@123` | Own clients, prospects, quotations, follow-ups & targets |
| **Sales Executive** | Priya Kalita | `priya@marutisales.in` | `Sales@123` | Own clients, prospects, quotations, follow-ups & targets |

> [!IMPORTANT]
> **Force Password Change on First Login**:
> For security, all seeded users have `must_change_password` enabled. Upon first signing in, you are automatically redirected to `/admin/change-password` to set a secure 8+ character password before accessing the system.

---

## 5. Deployment Guide: cPanel Shared Hosting

1. **Upload Files**:
   - Upload project files to a folder outside `public_html` (e.g. `/home/username/maruti_crm`).
   - Move the contents of `/public` into `/home/username/public_html` (or set document root in cPanel to `/home/username/maruti_crm/public`).
   - If public contents were moved, update `index.php` paths:
     ```php
     require __DIR__.'/../maruti_crm/vendor/autoload.php';
     $app = require_once __DIR__.'/../maruti_crm/bootstrap/app.php';
     ```
2. **Database Setup**:
   - In cPanel MySQL Databases, create a database (e.g. `cpaneluser_maruti`) and user with full privileges.
   - Update `.env`:
     ```env
     APP_ENV=production
     APP_DEBUG=false
     APP_URL=https://yourdomain.com
     DB_CONNECTION=mysql
     DB_HOST=localhost
     DB_DATABASE=cpaneluser_maruti
     DB_USERNAME=cpaneluser_dbuser
     DB_PASSWORD=YourPassword
     ```
3. **Run Migrations & Caches via Terminal / SSH**:
   ```bash
   php artisan migrate --force --seed
   php artisan storage:link
   php artisan optimize
   ```
4. **File Permissions**:
   ```bash
   chmod -R 775 storage bootstrap/cache
   ```

---

## 6. Deployment Guide: Linux VPS (Ubuntu / Nginx)

1. **Server Provisioning**:
   ```bash
   sudo apt update && sudo apt install -y nginx php8.3-fpm php8.3-mysql php8.3-sqlite3 \
       php8.3-mbstring php8.3-xml php8.3-bcmath php8.3-curl php8.3-gd php8.3-intl php8.3-zip unzip git
   ```
2. **Deploy Code**:
   ```bash
   cd /var/www
   git clone https://github.com/maxeco-energy/maruti-crm.git maruti
   cd maruti
   composer install --no-dev --optimize-autoloader
   cp .env.example .env
   php artisan key:generate
   # Configure MySQL in .env
   php artisan migrate --force --seed
   php artisan storage:link
   php artisan optimize
   sudo chown -R www-data:www-data /var/www/maruti/storage /var/www/maruti/bootstrap/cache
   sudo chmod -R 775 /var/www/maruti/storage /var/www/maruti/bootstrap/cache
   ```
3. **Nginx Site Configuration (`/etc/nginx/sites-available/maruti`)**:
   ```nginx
   server {
       listen 80;
       server_name crm.marutisales.in;
       root /var/www/maruti/public;

       index index.php index.html;
       charset utf-8;

       location / {
           try_files $uri $uri/ /index.php?$query_string;
       }

       location = /favicon.ico { access_log off; log_not_found off; }
       location = /robots.txt  { access_log off; log_not_found off; }

       error_page 404 /index.php;

       location ~ \.php$ {
           fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
           fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
           include fastcgi_params;
       }

       location ~ /\.(?!well-known).* {
           deny all;
       }
   }
   ```
   Enable site: `sudo ln -s /etc/nginx/sites-available/maruti /etc/nginx/sites-enabled/ && sudo systemctl reload nginx`.

---

## 7. Scheduled Tasks & Automation (Cron Job)

The application uses Laravel's unified scheduler for automated maintenance. You only need to configure **one single cron entry** in your cPanel or VPS crontab:

```cron
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

### Registered Scheduled Jobs:
1. **Quotation Auto-Expiry (`quotations:expire`)**:
   - **Frequency**: Daily at `00:05` (midnight).
   - **Behavior**: Inspects all active quotations where `valid_until < today()` and status is `Draft`, `Sent`, or `Under Negotiation`. Marks them as `Expired` and logs audit activity with previous status.
2. **Database Backup & Retention (`app:backup-database`)**:
   - **Frequency**: Daily at `02:00` (nightly).
   - **Behavior**: Generates a timestamped SQL/SQLite backup outside the public root into `storage/app/backups/`. Keeps the last 14 backups and automatically prunes older ones.

---

## 8. Database Backup & Disaster Recovery

Backups are securely stored outside the web root at:
```
storage/app/backups/maruti_backup_YYYY-mm-dd_His.sql (or .sqlite)
```

### Manual Backup:
```bash
php artisan app:backup-database
# To customize retention count:
php artisan app:backup-database --keep=30
```

### Restoration Procedure:

- **MySQL (Production)**:
  ```bash
  # 1. Place the application into maintenance mode
  php artisan down

  # 2. Restore database from backup file
  mysql -u maruti_user -p maruti_crm < storage/app/backups/maruti_backup_2026-11-13_020000.sql

  # 3. Clear caches and bring application back online
  php artisan optimize:clear
  php artisan optimize
  php artisan up
  ```

- **SQLite (Development / Testing)**:
  ```bash
  cp storage/app/backups/maruti_backup_2026-11-13_020000.sqlite database/database.sqlite
  php artisan optimize:clear
  ```

---

## 9. Application Update Workflow

To update the production installation with new releases:

```bash
php artisan down
git pull origin master
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan optimize:clear
php artisan optimize
php artisan up
```

---

## 10. User Guide & Key Features

### For Owners / Administrators:
1. **Firm-Wide Dashboard**:
   - Real-time stat cards: Active Clients, Open Pipeline Value, Quotations Issued this Month, Won Sales this Month, Due Tasks, Overdue Tasks.
   - Target progress bar tracking firm-wide monthly won revenue against target.
   - Quotation status distribution doughnut chart & Pipeline stage bar chart.
   - Top 5 clients by won revenue and quotations expiring within 7 days.
2. **Targets Management**:
   - Set monthly (1-12) and annual (`month = 0`) targets per sales executive.
   - Automatic live tracking: Achieved = sum of `grand_total` of latest-revision `Accepted` quotations counted by `accepted_at` date.
   - Variance and % achievement badges (Green $\ge 100\%$, Amber $50-99\%$, Red $< 50\%$).
3. **Reports & Exports**:
   - Access **Operations $\rightarrow$ Reports & Analytics**.
   - 4 reports: Quotation Register, Client-wise Sales Summary, Executive Target Achievement, Prospect Conversion & Pipeline.
   - Filter by date range (defaults to current FY), executive, client, status.
   - One-click **Export to CSV** and **Export to Excel** (with plain numbers and formatted currency cells).
4. **Company Letterhead Settings**:
   - Update firm name, phone, email, address, bank remittance details, authorized signatory title, jurisdiction note, and letterhead stamp.

### For Sales Executives:
1. **Scoped Privacy & Visibility**:
   - Executives only see prospects assigned to them, quotations created by them or for clients assigned to them, and their own monthly targets.
2. **Prospects / Leads Pipeline & Kanban**:
   - Filterable table or interactive Kanban board (`/admin/prospects/kanban`).
   - Drag and drop cards between stages (`New`, `Contacted`, `Quoted`, `Negotiation`, `Won`, `Lost`).
   - One-click **Convert to Client** pre-populates company name, contact, phone, email, and prompts for GSTIN.
3. **Follow-ups & Overdue Alerts**:
   - Setting next follow-up date on a lead automatically syncs or creates a linked follow-up task.
   - Overdue tasks highlighted prominently in red; tasks due today highlighted in amber.
4. **Quotation Generation & Live GST Engine**:
   - Auto-numbering sequence: `MSS/2026-27/0001` (resets every 1 April in atomic DB transaction).
   - Selecting a client automatically snapshots billing details, GSTIN, and State Code.
   - Catalog items auto-fill rate, unit, HSN, and GST%.
   - **GST Engine**:
     - Client state code `18` (Assam) $\rightarrow$ Intra-state (`CGST 50% + SGST 50%`).
     - Any other state code $\rightarrow$ Inter-state (`IGST 100%`).
     - Line-item discounts, round-off to nearest rupee, and Indian currency words (*"Rupees Two Lakh Sixty-Seven Thousand Thirty-Four Only"*).
5. **PDF, Revisions & Sharing**:
   - One-click **Preview PDF** (clean DejaVu Sans print layout with ₹ symbol, letterhead, bank details, and signatory).
   - One-click **Download PDF**.
   - One-click **Create Revision** creates draft `/R1`, `/R2`, clones items, and supersedes the old version.
   - One-click **WhatsApp Share** (`https://wa.me/...`) and **Email Share** (`mailto:...`).

---

## 11. GitHub Actions CI/CD & Deployment

The repository includes a ready-to-run GitHub Actions configuration in `.github/workflows/`:

1. **Continuous Integration (`ci.yml`)**:
   - Triggers automatically on every push or pull request to `master`/`main`.
   - Spins up an Ubuntu runner with PHP 8.3 and all required extensions.
   - Installs dependencies with Composer caching.
   - Executes the 15-category Developer System Audit (`php artisan app:audit-system --detailed`).
   - Runs the full 53-test PHPUnit test suite.
   - Ensures zero regressions before merging or deploying.

2. **Automated Production Deployment (`deploy.yml.example`)**:
   - Easily deploy to your production VPS or cPanel via SSH upon pushing to `master`.
   - To enable:
     1. Add repository secrets in GitHub: `SSH_HOST`, `SSH_USER`, `SSH_KEY`, `SSH_PORT`.
     2. Rename `.github/workflows/deploy.yml.example` to `.github/workflows/deploy.yml`.

3. **Pushing to your GitHub Repository**:
   ```bash
   git remote add origin https://github.com/YOUR_USERNAME/YOUR_REPO.git
   git branch -M master
   git push -u origin master
   ```

---

## 12. Security & Compliance Highlights

- **Authentication & Roles**: Filament v3 guard, bcrypt password hashing (12 rounds).
- **Brute Force Protection**: Built-in login rate throttling.
- **CSRF Protection**: Token verification enforced across all forms and AJAX/Livewire endpoints.
- **Authorization Policies**: Granular Eloquent policies on every resource (`UserPolicy`, `ClientPolicy`, `ProspectPolicy`, `FollowUpPolicy`, `ProductPolicy`, `QuotationPolicy`, `TargetPolicy`).
- **Data Protection**: Quotations and exports cannot be accessed without authenticated session; unauthorized roles receive `403 Forbidden`.
- **Database Backups**: Automatically retained outside `public_html`.
