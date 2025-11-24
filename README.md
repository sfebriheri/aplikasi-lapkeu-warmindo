# 📊 LAPKEU Warmindo - Web-Based Financial Statement System

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![PHP Version](https://img.shields.io/badge/PHP-%3E%3D7.4-blue)](https://www.php.net/)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-%3E%3D12-336791)](https://www.postgresql.org/)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind%20CSS-3.3.0-06B6D4)](https://tailwindcss.com/)
[![CodeIgniter](https://img.shields.io/badge/CodeIgniter-4.6.3-EF4223)](https://codeigniter.com/)

**LAPKEU** (Laporan Keuangan - Financial Statement) is a professional, modern web-based accounting and financial statement management system designed for small to medium enterprises. Built with CodeIgniter, PostgreSQL, and Tailwind CSS, it provides a robust solution for managing financial transactions, generating accurate reports, and maintaining compliance with accounting standards.

---

## 📑 Table of Contents

- [Features](#features)
- [System Requirements](#system-requirements)
- [Quick Start](#quick-start)
- [Installation Guide](#installation-guide)
- [Project Structure](#project-structure)
- [Architecture](#architecture)
- [Technology Stack](#technology-stack)
- [Implementation Guide - Working with Layouts](#implementation-guide---working-with-layouts)
- [User Manual](#user-manual)
- [API Reference](#api-reference)
- [Development Guide](#development-guide)
- [Troubleshooting](#troubleshooting)
- [Contributing](#contributing)
- [Support](#support)
- [License](#license)

---

## ✨ Features

### Core Accounting Features
- **📖 Chart of Accounts Management**
    - Create and manage general ledger accounts
    - Support for multiple account types (Asset, Liability, Equity, Revenue, Expense)
    - Account hierarchy and categorization
    - Real-time account balances

- **📝 Journal Entry Management**
    - Create, edit, and delete journal entries
    - Double-entry bookkeeping system
    - Automatic debit/credit validation
    - Draft and approved status tracking
    - Audit trail of all transactions

- **📊 Financial Reports**
    - Trial Balance
    - Income Statement (P&L)
    - Balance Sheet
    - General Ledger
    - Cash Flow Statement
    - Customizable report date ranges

- **🏢 Business Modules**
    - User and role management
    - Multi-user access with permission levels
    - Transaction history and audit logs
    - File uploads for supporting documents

### Technical Features
- **🔐 Security**
    - CSRF protection on all forms
    - XSS filtering for all input
    - Password hashing with bcrypt
    - Session-based authentication
    - Environment-based credentials
    - SQL injection prevention

- **🎨 User Interface**
    - Modern, responsive design with Tailwind CSS
    - Mobile-friendly interface
    - Intuitive navigation
    - Professional color scheme
    - Real-time form validation
    - Accessible components (ARIA labels)

- **⚡ Performance**
    - Database indexes on frequently searched columns
    - Query optimization
    - Account balance caching
    - Efficient pagination
    - Minified CSS/JS assets

- **📱 Responsive Design**
    - Desktop, tablet, and mobile support
    - Touch-friendly controls
    - Adaptive layouts
    - Print-optimized reports

---

## 🖥️ System Requirements

### Minimum Requirements
```
PHP:                       7.4 or higher
PostgreSQL:         12 or higher
MySQL/MariaDB:      5.7 (for legacy support)
Memory:                 512 MB
Storage:               500 MB (for application + data)
```

### Recommended Requirements
```
PHP:                       8.1 or higher
PostgreSQL:         14 or higher
Node.js:               16 LTS or higher
Memory:                 2 GB
Storage:               2 GB
Processor:           2 cores or higher
```

### Software Dependencies
- **Web Server:** Apache 2.4+ (with mod_rewrite) OR Nginx
- **Database:** PostgreSQL 12+
- **PHP Extensions:**
    - `pdo_pgsql` - PostgreSQL driver
    - `mbstring` - String handling
    - `openssl` - SSL/TLS support
    - `curl` - HTTP requests
    - `json` - JSON handling
    - `gd` - Image processing

### Development Tools
- **Composer** - PHP dependency management
- **npm** - JavaScript/Node package manager
- **Git** - Version control
- **Terminal/CLI** - Command-line access

---

## ⚡ Quick Start

### 1. Clone Repository
```bash
git clone https://github.com/sfebriheri/aplikasi-lapkeu-warmindo.git
cd aplikasi-lapkeu-warmindo
```

### 2. Environment Setup
```bash
cp .env.example .env
# Edit .env with your database credentials
nano .env
```

### 3. Install Dependencies
```bash
composer install
npm install
```

### 4. Create Database
```bash
createdb -U postgres lapkeu_warmindo
```

### 5. Run Migrations
```bash
php spark migrate
```

### 6. Build Frontend Assets
```bash
npm run build
```

### 7. Set Permissions
```bash
chmod -R 755 app/
chmod -R 755 public/
chmod -R 755 writable/
```

### 8. Start Development Server
```bash
php spark serve
```

### 9. Access Application
```
URL: http://localhost:8080/
Email: admin@lapkeu.local
Password: Admin@123456
```

⚠️ **Change default password immediately!**

---

## 📁 Installation Guide

### Detailed Prerequisites

#### Linux (Ubuntu/Debian)
```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install Apache/Nginx
sudo apt install -y apache2 nginx

# Install PHP and extensions
sudo apt install -y php php-cli php-fpm php-pdo php-pgsql \
                                        php-mbstring php-openssl php-curl php-json php-gd

# Install PostgreSQL
sudo apt install -y postgresql postgresql-contrib

# Install Node.js
curl -fsSL https://deb.nodesource.com/setup_16.x | sudo -E bash -
sudo apt install -y nodejs
```

#### macOS
```bash
# Using Homebrew
brew install php@8.1 postgresql node

# Enable mod_rewrite for Apache
sudo a2enmod rewrite
```

#### Windows
Download and install:
- [PHP 8.1+](https://windows.php.net/download/)
- [PostgreSQL](https://www.postgresql.org/download/windows/)
- [Node.js](https://nodejs.org/)
- [Git Bash](https://git-scm.com/download/win)

### Web Server Configuration

#### Apache (.htaccess)
```apache
<IfModule mod_rewrite.c>
        RewriteEngine On
        RewriteBase /aplikasi-lapkeu-warmindo/

        # Remove trailing slashes
        RewriteCond %{REQUEST_FILENAME} !-d
        RewriteRule ^(.+)/$ $1 [L, R=301]

        # Handle requests
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteCond %{REQUEST_FILENAME} !-d
        RewriteRule ^(.*)$ index.php/$1 [L]
</IfModule>

# Security headers
<IfModule mod_headers.c>
        Header set X-Content-Type-Options "nosniff"
        Header set X-Frame-Options "SAMEORIGIN"
        Header set X-XSS-Protection "1; mode=block"
</IfModule>

# Disable directory listing
Options -Indexes
```

#### Nginx
```nginx
server {
        listen 80;
        server_name lapkeu.local;
        root /var/www/aplikasi-lapkeu-warmindo;

        index index.php index.html;

        # Log files
        access_log /var/log/nginx/lapkeu_access.log;
        error_log /var/log/nginx/lapkeu_error.log;

        # URL rewriting
        location / {
                try_files $uri $uri/ /index.php?$query_string;
        }

        # PHP processing
        location ~ \.php$ {
                fastcgi_pass unix:/var/run/php-fpm.sock;
                fastcgi_index index.php;
                fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
                include fastcgi_params;
        }

        # Security
        location ~ /\. {
                deny all;
        }
}
```

### Database Setup

#### Create Database User
```sql
CREATE USER lapkeu WITH PASSWORD 'your_secure_password';
CREATE DATABASE lapkeu_warmindo OWNER lapkeu;

-- Grant privileges
GRANT CONNECT ON DATABASE lapkeu_warmindo TO lapkeu;
GRANT USAGE ON SCHEMA public TO lapkeu;
GRANT CREATE ON SCHEMA public TO lapkeu;
```

#### Run Migrations
```bash
# Run all pending migrations
php spark migrate

# Rollback one migration
php spark migrate:rollback

# Refresh all migrations
php spark migrate:refresh
```

---

## 📂 Project Structure

```
aplikasi-lapkeu-warmindo/
├── app/
│   ├── Config/                             # CodeIgniter 4 configuration
│   │   ├── Routes.php                      # Route definitions
│   │   ├── Database.php                    # Database configuration
│   │   ├── Filters.php                     # Filter configuration
│   │   └── ...
│   ├── Controllers/                        # Request handlers
│   │   ├── Auth.php                        # Authentication
│   │   ├── Admin.php                       # Dashboard
│   │   ├── Master.php                      # Master data
│   │   ├── Jp.php                          # Journal entries
│   │   └── ...
│   ├── Models/                             # Database interaction
│   │   ├── UserModel.php                   # User operations
│   │   ├── AdminModel.php
│   │   ├── AccountingModel.php
│   │   └── ...
│   ├── Views/                              # HTML templates
│   │   ├── auth/                           # Login/Register
│   │   ├── admin/                          # Dashboard pages
│   │   ├── templates/                      # Layout templates
│   │   ├── laporan/                        # Report templates
│   │   └── ...
│   ├── Database/
│   │   └── Migrations/                     # Database migrations
│   │       ├── 001_CreateUserTable.php
│   │       ├── 002_CreateUserRoleTable.php
│   │       ├── 003_CreateAccountingTables.php
│   │       └── ...
│   ├── Filters/                            # Custom filters
│   │   ├── AuthFilter.php                  # Authentication filter
│   │   └── ...
│   ├── Helpers/                            # Helper functions
│   │   ├── TugasakhirHelper.php            # Currency formatting helpers
│   │   └── ...
│   ├── Libraries/                          # Custom libraries
│   │   ├── CurrencyHandler.php             # Currency handling
│   │   └── DompdfGen.php                   # PDF generation wrapper
│   └── ...
├── public/
│   ├── index.php                           # CodeIgniter 4 entry point
│   ├── assets/
│   │   ├── css/
│   │   │   ├── input.css                   # Tailwind entry point
│   │   │   └── output.css                  # Compiled CSS (generated)
│   │   ├── scss/                           # SCSS source files
│   │   ├── js/                             # JavaScript files
│   │   ├── img/                            # Images
│   │   │   └── profile/                    # User profile pictures
│   │   └── uploads/                        # User uploads
│   └── .htaccess                           # Apache rewrite rules
├── writable/                               # Application logs & cache
│   ├── cache/                              # Cache files
│   ├── logs/                               # Application logs
│   └── uploads/                            # Uploaded files
├── .env                                    # Environment variables (gitignored)
├── .env.example                            # Environment template
├── .gitignore                              # Git ignore rules
├── package.json                            # Node.js dependencies
├── package-lock.json                       # Dependency lock file
├── tailwind.config.js                      # Tailwind configuration
├── postcss.config.js                       # PostCSS configuration
├── composer.json                           # PHP dependencies
├── composer.lock                           # PHP dependency lock
├── INSTALLATION.md                         # Installation guide
├── MIGRATION_TO_CI4.md                     # Migration guide from CI3
├── README.md                               # This file
└── spark                                   # CodeIgniter 4 CLI tool
```

---

## 🏗️ Architecture

### MVC Pattern
```
Request → Router → Controller → Model → View → Response
```

### Application Flow
```
1. User submits form/request
2. Router directs to appropriate Controller
3. Controller validates input
4. Controller calls Model for data operations
5. Model interacts with Database
6. Controller passes data to View
7. View renders HTML response
8. Response sent to user browser
```

### Database Schema

#### Core Tables
- **user** - User accounts and credentials
- **user_role** - Role definitions and permissions
- **user_token** - Password reset tokens
- **chart_of_accounts** - General ledger accounts
- **journal_entries** - Transaction headers
- **journal_details** - Transaction line items
- **account_balance** - Performance cache

### Security Layers
```
Input Validation
         ↓
XSS Filtering
         ↓
SQL Injection Prevention (Prepared Statements)
         ↓
CSRF Token Validation
         ↓
Authentication Check
         ↓
Authorization Check
         ↓
Rate Limiting (Future)
```

---

## 💻 Technology Stack

### Backend
| Technology | Version | Purpose |
|------------|---------|---------|
| **PHP** | 7.4 - 8.2+ | Server-side language |
| **CodeIgniter** | 4.6.3 | MVC Framework |
| **PostgreSQL** | 12+ | Primary database |
| **MySQL/MariaDB** | 5.7+ | Legacy database support |
| **Composer** | Latest | PHP dependency management |

### Frontend
| Technology | Version | Purpose |
|------------|---------|---------|
| **HTML5** | Modern | Semantic markup |
| **CSS3 / Tailwind CSS** | 3.3.0 | Utility-first CSS framework |
| **SCSS/SASS** | 1.63.6 | CSS preprocessing |
| **JavaScript (Vanilla)** | ES6+ | Client-side interactivity |
| **Alpine.js** | 3.12.0 | Lightweight reactive framework |
| **Chart.js** | 3.9.1 | Data visualization |
| **DataTables.net** | 1.13.4 | Advanced table functionality |
| **Axios** | 1.4.0 | HTTP requests |
| **SweetAlert2** | 11.7.12 | Beautiful alert dialogs |

### Build Tools & DevTools
| Tool | Version | Purpose |
|------|---------|---------|
| **Node.js** | 16 LTS+ | JavaScript runtime |
| **npm** | 8+ | JavaScript package manager |
| **Tailwind CSS CLI** | 3.3.0 | CSS compilation & minification |
| **PostCSS** | 8.4.24 | CSS transformation |
| **Autoprefixer** | 10.4.14 | CSS vendor prefixes |
| **SASS** | 1.63.6 | CSS preprocessing |

### Web Servers (Supported)
| Server | Version |
|--------|---------|
| **Apache** | 2.4+ (with mod_rewrite) |
| **Nginx** | Latest stable |

### Development & Testing
| Tool | Purpose |
|------|---------|
| **Git** | Version control |
| **PHPUnit** | PHP testing framework |
| **Postman** | API testing |
| **VS Code / PhpStorm** | Code editor/IDE |

---

## 📐 Implementation Guide - Working with Layouts

### Overview

The application uses a **modular template system** with shared layouts. The main dashboard is composed of multiple template parts loaded sequentially. This guide explains how to implement and modify the main layout.

### Main Layout Architecture

The application uses a **5-part template composition pattern**:

```
1. dash_header.php      → HTML <head>, CSS/JS includes, page structure
2. adm_sidebar.php      → Left navigation sidebar (role-based)
3. adm_header.php       → Top navigation bar with user info
4. admin/index.php      → Main content area
5. adm_footer.php       → Footer, modals, closing tags
```

### Template Files Location

All layout templates are located in: **`application/views/templates/`**

```
application/views/templates/
├── dash_header.php     # Main HTML container & head
├── adm_sidebar.php     # Left sidebar navigation
├── adm_header.php      # Top navbar & user dropdown
├── adm_footer.php      # Footer & closing HTML
├── auth_header.php     # Auth pages header
└── auth_footer.php     # Auth pages footer
```

### Implementing Main Content Pages

#### Step 1: Create Controller Method

**File:** `application/controllers/Admin.php`

```php
<?php
class Admin extends CI_Controller {

    public function __construct() {
        parent::__construct();
        // Check authentication
        if (!$this->session->userdata('email')) {
            redirect('auth');
        }
        $this->load->model('User_model');
    }

    /**
     * Main Dashboard
     */
    public function index() {
        try {
            // Get user data
            $user_email = $this->session->userdata('email');
            $user = $this->db->get_where('user', ['email' => $user_email])->row_array();

            // Prepare data array
            $data['judul'] = 'Menu Utama';  // Page title
            $data['active'] = 'active';     // Active state for sidebar
            $data['user'] = $user;          // User info for navbar

            // IMPORTANT: Load templates in correct order
            $this->load->view('templates/dash_header', $data);
            $this->load->view('templates/adm_sidebar', $data);
            $this->load->view('templates/adm_header', $data);
            $this->load->view('admin/index', $data);           // Your main content
            $this->load->view('templates/adm_footer');

        } catch (Exception $e) {
            log_message('error', $e->getMessage());
            show_error('Error loading dashboard');
        }
    }
}
```

#### Step 2: Create Your Content View

**File:** `application/views/admin/your_page.php`

```html
<!-- Main Content Container -->
<div class="container mx-auto px-4 py-6">

    <!-- Page Header -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">
            <?php echo isset($judul) ? htmlspecialchars($judul) : 'Page'; ?>
        </h1>
    </div>

    <!-- Content Area -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

        <!-- Card Example -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold mb-4">Card Title</h3>
            <p class="text-gray-600">Your content here</p>
        </div>

    </div>

</div>
```

### Key Variables & Conventions

#### Data Array Variables
```php
$data['judul']      // Page title (displayed in page header)
$data['active']     // CSS class for active sidebar item
$data['user']       // Current user array from database
$data['page_name']  // Optional: current page identifier
```

#### Available in Templates
```php
// In dash_header.php, adm_sidebar.php, adm_header.php:
$judul              // Page title
$active             // Active state
$user               // User object with: id, email, nama, role_id, gambar

// In admin/index.php (your content):
// All variables from $data array are accessible
```

### Using Tailwind CSS Classes

The application uses **Tailwind CSS 3.3.0** for styling. No custom CSS needed in most cases.

```html
<!-- Spacing -->
<div class="p-6 m-4 py-8">Padding & Margin</div>

<!-- Colors -->
<div class="bg-blue-500 text-white">Background & Text</div>

<!-- Layout -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3">Responsive Grid</div>

<!-- Flexbox -->
<div class="flex justify-between items-center">Flex Layout</div>

<!-- Responsive -->
<div class="hidden md:block">Hidden on mobile, visible on tablet+</div>
```

### Interactive Elements with Alpine.js

For dynamic features without page reload:

```html
<!-- Toggle Example -->
<div x-data="{ open: false }">
    <button @click="open = !open" class="btn">Toggle Menu</button>
    <div x-show="open" class="menu">Menu Content</div>
</div>

<!-- Form Handling -->
<form x-data="{ loading: false }" @submit.prevent="submitForm()">
    <input type="text" x-model="formData.name" required>
    <button :disabled="loading" type="submit">
        <span x-show="!loading">Submit</span>
        <span x-show="loading">Loading...</span>
    </button>
</form>
```

### Adding Charts with Chart.js

```html
<div class="bg-white rounded-lg shadow-md p-6">
    <canvas id="myChart"></canvas>
</div>

<script>
    // After all DOM loads
    const ctx = document.getElementById('myChart').getContext('2d');
    const chart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Jan', 'Feb', 'Mar'],
            datasets: [{
                label: 'Sales',
                data: [12, 19, 3],
                backgroundColor: 'rgba(59, 130, 246, 0.5)'
            }]
        }
    });
</script>
```

### Building & Compiling Assets

```bash
# Development (watch mode for CSS changes)
npm run dev

# Production (minified CSS)
npm run build

# SCSS compilation
npm run scss:build

# Build all assets
npm run build:all
```

### Common Layout Patterns

#### Breadcrumb Navigation
```html
<nav class="flex items-center space-x-2 text-sm text-gray-600 mb-6">
    <a href="/admin" class="hover:text-blue-600">Home</a>
    <span>/</span>
    <span>Current Page</span>
</nav>
```

#### Alert Messages
```html
<?php if ($this->session->flashdata('message')): ?>
    <div class="alert alert-success">
        <?php echo $this->session->flashdata('message'); ?>
    </div>
<?php endif; ?>
```

#### Loading Spinner
```html
<div x-data="{ loading: false }" @loading.window="loading = true" @loaded.window="loading = false">
    <div x-show="loading" class="flex justify-center">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500"></div>
    </div>
</div>
```

#### Button Styles
```html
<!-- Primary Button -->
<button class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">Primary</button>

<!-- Secondary Button -->
<button class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded">Secondary</button>

<!-- Danger Button -->
<button class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded">Delete</button>
```

#### Modal Dialog
```html
<div x-data="{ open: false }" class="relative">
    <button @click="open = true">Open Modal</button>

    <div x-show="open" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
        <div class="bg-white rounded-lg p-6 max-w-md w-full">
            <h2 class="text-xl font-bold mb-4">Modal Title</h2>
            <p class="text-gray-600 mb-6">Modal content goes here</p>
            <button @click="open = false" class="btn btn-primary">Close</button>
        </div>
    </div>
</div>
```

### Role-Based Layout Adjustments

Check user role in your templates:

```html
<?php if ($user['role_id'] == 1): ?>
    <!-- Owner-only content -->
    <div>Only visible to owners</div>
<?php elseif ($user['role_id'] == 2): ?>
    <!-- Admin content -->
    <div>Admin section</div>
<?php endif; ?>
```

### File Upload in Forms

```html
<form method="post" action="/admin/upload" enctype="multipart/form-data">
    <?php echo $this->security->get_csrf_token_name(); ?>
    <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>"
           value="<?php echo $this->security->get_csrf_hash(); ?>">

    <input type="file" name="file" required>
    <button type="submit">Upload</button>
</form>
```

### Responsive Tables

```html
<div class="overflow-x-auto">
    <table class="w-full border-collapse">
        <thead class="bg-gray-100">
            <tr>
                <th class="px-4 py-2 text-left">Column 1</th>
                <th class="px-4 py-2 text-left">Column 2</th>
                <th class="px-4 py-2 text-right">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): ?>
            <tr class="border-b hover:bg-gray-50">
                <td class="px-4 py-2"><?php echo htmlspecialchars($item['name']); ?></td>
                <td class="px-4 py-2"><?php echo htmlspecialchars($item['description']); ?></td>
                <td class="px-4 py-2 text-right">
                    <a href="#" class="text-blue-600 hover:text-blue-800 mr-2">Edit</a>
                    <a href="#" class="text-red-600 hover:text-red-800">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
```

---

## 📖 User Manual

### Getting Started

#### Login
1. Navigate to application URL
2. Enter email and password
3. Click "Login"
4. You'll be redirected to Dashboard

#### Password Reset
1. Click "Forgot Password" on login page
2. Enter your email
3. Check email for reset link
4. Click link and create new password

#### Changing Password
1. Go to Profile Settings
2. Click "Change Password"
3. Enter current password
4. Enter new password (minimum 8 characters)
5. Confirm new password
6. Click "Update"

### Account Management

#### User Types
- **Admin** - Full system access
- **Accountant** - Financial transaction management
- **User** - Limited transaction viewing
- **Auditor** - Report viewing only

#### Creating Users (Admin Only)
1. Go to Settings → User Management
2. Click "Add User"
3. Fill in user details:
    - Name (required)
    - Email (must be unique)
    - Password (minimum 8 characters)
    - Role (Admin, Accountant, User, Auditor)
    - Status (Active/Inactive)
4. Click "Create User"
5. User receives email confirmation

### Managing Accounts

#### Creating Chart of Accounts
1. Go to Accounting → Chart of Accounts
2. Click "Add Account"
3. Fill in details:
    - **Account Code** (e.g., 1000, 2000)
    - **Account Name** (e.g., Cash, Sales)
    - **Account Type** (Asset, Liability, Equity, Revenue, Expense)
    - **Category** (Main category)
    - **Subcategory** (If applicable)
    - **Normal Balance** (Debit or Credit)
4. Click "Create"

#### Viewing Account Details
1. Go to Accounting → Chart of Accounts
2. Click on account name
3. View:
    - Account information
    - Current balance
    - Recent transactions
    - Account history

### Recording Transactions

#### Creating Journal Entry
1. Go to Accounting → Journal Entries
2. Click "New Entry"
3. Fill in:
    - **Entry Date** (Date of transaction)
    - **Description** (Transaction details)
    - **Debit Entries** (Amount to debit)
    - **Credit Entries** (Amount to credit)
4. Ensure debit total = credit total
5. Click "Save as Draft"
6. Review and click "Submit for Approval"

#### Approving Journal Entries (Admin)
1. Go to Accounting → Pending Approvals
2. Review entry details
3. Click "Approve" or "Reject"
4. Add approval notes if needed
5. Approved entries update account balances

### Generating Reports

#### Trial Balance
1. Go to Reports → Trial Balance
2. Select date range
3. Click "Generate"
4. View or export report

#### Income Statement
1. Go to Reports → Income Statement
2. Select period (Month/Year/Custom)
3. Click "Generate"
4. Shows Revenue - Expenses = Net Income

#### Balance Sheet
1. Go to Reports → Balance Sheet
2. Select as-of date
3. Click "Generate"
4. Shows Assets = Liabilities + Equity

#### Exporting Reports
1. Open any report
2. Click "Export"
3. Choose format:
    - PDF
    - Excel
    - CSV
4. File downloads automatically

---

## 🔌 API Reference

### Authentication Endpoints

#### Login
```http
POST /auth/index
Content-Type: application/x-www-form-urlencoded

email=user@example.com&password=password123
```

**Response:**
```json
{
    "success": true,
    "message": "Login successful",
    "user": {
        "id": 1,
        "email": "user@example.com",
        "nama": "User Name",
        "role_id": 1
    }
}
```

#### Register
```http
POST /auth/register
Content-Type: multipart/form-data

nama=New User&email=new@example.com&password1=password&password2=password&gambar=[file]
```

#### Logout
```http
GET /auth/logout
```

### User Endpoints

#### Get User Profile
```http
GET /api/user/profile
Authorization: Bearer {token}
```

#### Update User Profile
```http
PUT /api/user/profile
Authorization: Bearer {token}
Content-Type: application/json

{
    "nama": "Updated Name",
    "email": "newemail@example.com"
}
```

### Accounting Endpoints

#### Get Chart of Accounts
```http
GET /api/accounting/chart-of-accounts?type=Asset
Authorization: Bearer {token}
```

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "code": "1000",
            "name": "Cash",
            "account_type": "Asset",
            "balance": 50000.00
        }
    ]
}
```

#### Create Journal Entry
```http
POST /api/accounting/journal-entries
Authorization: Bearer {token}
Content-Type: application/json

{
    "entry_date": "2024-01-15",
    "description": "Sales transaction",
    "details": [
        { "account_id": 1, "debit_amount": 100000, "credit_amount": 0 },
        { "account_id": 2, "debit_amount": 0, "credit_amount": 100000 }
    ]
}
```

#### Get Account Balance
```http
GET /api/accounting/accounts/1/balance?period=2024-01
Authorization: Bearer {token}
```

#### Get Trial Balance
```http
GET /api/accounting/trial-balance?start_date=2024-01-01&end_date=2024-01-31
Authorization: Bearer {token}
```

---

## 🔨 Development Guide

### Setting Up Development Environment

#### 1. Clone and Install
```bash
git clone https://github.com/sfebriheri/aplikasi-lapkeu-warmindo.git
cd aplikasi-lapkeu-warmindo
composer install
npm install
```

#### 2. Configure Environment
```bash
cp .env.example .env
# Edit .env with development database
```

#### 3. Create Database
```bash
createdb -U postgres lapkeu_warmindo_dev
```

#### 4. Run Migrations
```bash
php spark migrate
```

#### 5. Start Development Servers
```bash
# Terminal 1: Watch CSS changes
npm run dev

# Terminal 2: Run CodeIgniter development server
php spark serve
```

### Code Style Guidelines

#### PHP Coding Standards
```php
// Use spaces for indentation (4 spaces)
class MyClass {

        // Class properties
        private $property = 'value';

        // Method documentation
        /**
         * Method description
         *
         * @param string $param Description
         * @return bool Description
         */
        public function myMethod($param) {
                // Implementation
                return true;
        }
}
```

#### Variable Naming
```php
$userEmail = 'user@example.com';     // camelCase for variables
$totalAmount = 1000.00;               // descriptive names
const APP_VERSION = '1.0.0';          // CONSTANT_CASE for constants
class UserModel { }                    // PascalCase for classes
function getUserData() { }             // camelCase for functions
```

#### Comments
```php
// Single-line comment for brief notes

/**
 * Multi-line comment for detailed documentation
 * Includes parameter and return descriptions
 */

// TODO: Future enhancement
// FIXME: Known issue that needs fixing
```

### Creating Migrations

#### Generate Migration File
```bash
php index.php cli make:migration create_transactions_table
```

#### Migration Structure
```php
<?php
class Migration_Create_transactions_table extends CI_Migration {

        public function up() {
                // Create new tables/columns
                $sql = "
                        CREATE TABLE transactions (
                                id SERIAL PRIMARY KEY,
                                date DATE NOT NULL,
                                amount NUMERIC(15, 2),
                                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                        )
                ";
                $this->db->query($sql);
        }

        public function down() {
                // Rollback changes
                $this->db->query("DROP TABLE IF EXISTS transactions");
        }
}
```

### Creating Models

#### Model Template
```php
<?php
class Transaction_model extends CI_Model {

        protected $table = 'transactions';

        public function __construct() {
                parent::__construct();
                $this->load->database();
        }

        /**
         * Get transaction by ID
         */
        public function get_transaction($id) {
                try {
                        $query = $this->db->where('id', $id)->get($this->table);
                        return $query->num_rows() > 0 ? $query->row_array() : null;
                } catch (Exception $e) {
                        log_message('error', $e->getMessage());
                        return null;
                }
        }

        /**
         * Create transaction
         */
        public function create_transaction($data) {
                try {
                        return $this->db->insert($this->table, $data);
                } catch (Exception $e) {
                        log_message('error', $e->getMessage());
                        return false;
                }
        }
}
```

### Creating Controllers

#### Controller Template
```php
<?php
class Transactions extends CI_Controller {

        public function __construct() {
                parent::__construct();
                // Check authentication
                if (!$this->session->userdata('email')) {
                        redirect('auth');
                }

                $this->load->model('Transaction_model');
        }

        /**
         * Display transactions list
         */
        public function index() {
                try {
                        $data['transactions'] = $this->Transaction_model->get_all_transactions();
                        $this->load->view('transactions/index', $data);
                } catch (Exception $e) {
                        $this->session->set_flashdata('message', 'Error: ' . $e->getMessage());
                        redirect('dashboard');
                }
        }
}
```

### Frontend Development

#### Building CSS
```bash
# Development (watch mode)
npm run dev

# Production (minified)
npm run build

# Build all assets
npm run build:all
```

### Testing

#### Run Tests
```bash
php index.php cli test
```

### Debugging

#### Enable Debug Mode
```bash
# In .env
CI_ENVIRONMENT=development
DEBUG_MODE=true
```

#### View Logs
```bash
tail -f application/logs/log-*.php
```

---

## 🐛 Troubleshooting

### Database Connection Error

**Error:** `Connection refused`

**Solutions:**
1. Verify PostgreSQL is running:
   ```bash
   sudo systemctl status postgresql
   ```

2. Check `.env` credentials:
   ```env
   DB_HOST=localhost
   DB_USER=postgres
   DB_PASS=your_password
   DB_NAME=lapkeu_warmindo
   ```

3. Test connection:
   ```bash
   psql -U postgres -h localhost -d lapkeu_warmindo
   ```

### Permission Denied Error

**Error:** `Permission denied`

**Solutions:**
```bash
# Fix directory permissions
sudo chown -R www-data:www-data /var/www/aplikasi-lapkeu-warmindo
sudo chmod -R 755 application/
sudo chmod -R 755 assets/
sudo chmod -R 777 application/logs/
sudo chmod -R 777 application/cache/
```

### 404 Not Found

**Error:** `404 Not Found` on all routes except home

**Solutions:**
1. Enable mod_rewrite:
   ```bash
   sudo a2enmod rewrite
   sudo systemctl restart apache2
   ```

2. Verify `.htaccess` in root directory

### CSRF Token Error

**Error:** `CSRF token mismatch`

**Solutions:**
1. Verify CSRF is enabled in config.php
2. Include CSRF token in all forms

### Slow Queries

**Solution:** Check database indexes
```sql
-- View all indexes
\d chart_of_accounts

-- Create missing indexes
CREATE INDEX idx_journal_date ON journal_entries(entry_date);
```

---

## 🤝 Contributing

We welcome contributions! Please:
1. Fork the repository
2. Create feature branch
3. Make your changes
4. Submit pull request

---

## 📞 Support

- **Documentation:** Check [INSTALLATION.md](./INSTALLATION.md)
- **Issues:** [GitHub Issues](https://github.com/sfebriheri/aplikasi-lapkeu-warmindo/issues)
- **Email:** support@lapkeu.local

---

## 📄 License

This project is licensed under the **MIT License** - see the [LICENSE](./LICENSE) file for details.

---

## 🙏 Credits

Built with:
- [CodeIgniter](https://codeigniter.com/)
- [PostgreSQL](https://www.postgresql.org/)
- [Tailwind CSS](https://tailwindcss.com/)
- [Alpine.js](https://alpinejs.dev/)
- [Chart.js](https://www.chartjs.org/)

---

**Status:** Production Ready | **Version:** 1.0.0 | **Last Updated:** January 2024
