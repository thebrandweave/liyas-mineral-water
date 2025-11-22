# QR Reward System - Quick Setup Guide

## ✅ System is Ready!

The QR reward system has been integrated into your existing project and uses your `liyas_international` database.

## 🚀 Quick Start

### 1. Create QR Tables in Database

The QR tables are already defined in `config/requirements/schema.sql` (lines 77-97). Run this SQL:

```sql
-- Connect to your database
USE liyas_international;

-- Create QR Codes Table
CREATE TABLE IF NOT EXISTS qr_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(64) UNIQUE NOT NULL,
    is_used TINYINT(1) DEFAULT 0,
    scanned_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_code (code),
    INDEX idx_is_used (is_used)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create Reward Logs Table
CREATE TABLE IF NOT EXISTS reward_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    qr_code_id INT NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    redeemed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (qr_code_id) REFERENCES qr_codes(id) ON DELETE CASCADE,
    INDEX idx_qr_code_id (qr_code_id),
    INDEX idx_redeemed_at (redeemed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

Or use the standalone file:
```bash
mysql -u root -p liyas_international < sql/qr_tables_only.sql
```

### 2. Install QR Code Library (Optional but Recommended)

**Option A: Using Composer**
```bash
composer require endroid/qr-code
```

**Option B: Manual Download**
- Download from: https://github.com/endroid/qr-code
- Place in `vendor/endroid/qr-code/`

**Note:** If the library is not installed, the system will create placeholder QR images (still functional for testing).

### 3. Generate QR Codes

```bash
php scripts/generate_qr.php 1000 https://yourdomain.com
```

This will:
- Generate 1000 unique QR codes
- Save images to `uploads/qrs/`
- Store codes in database
- Create QR images (or placeholders if library not installed)

### 4. Test the System

**Via Web Interface:**
```
http://localhost/liyas-mineral-water/public/index.php?code=YOUR_CODE
```

**Via API:**
```
http://localhost/liyas-mineral-water/public/qr-check.php?code=YOUR_CODE
```

**Run Test Script:**
```bash
php scripts/test_redeem.php
```

## 📁 File Locations

- **Redeem Page**: `public/index.php`
- **Validation API**: `public/qr-check.php`
- **QR Generator**: `scripts/generate_qr.php`
- **Database Config**: `config/db.php` (uses `liyas_international` database)
- **Schema**: `config/requirements/schema.sql` (lines 77-97)

## 🔒 Security Features

- ✅ SQL injection protection (prepared statements)
- ✅ Race condition prevention (database locking)
- ✅ Input validation
- ✅ Transaction support
- ✅ Secure error handling

## 📊 Database Structure

The system uses your existing `liyas_international` database with two new tables:

1. **qr_codes** - Stores QR codes and redemption status
2. **reward_logs** - Tracks redemption history

## 🎯 Usage Examples

### Generate 1000 Codes
```bash
php scripts/generate_qr.php 1000 https://mydomain.com
```

### Generate 5000 Codes
```bash
php scripts/generate_qr.php 5000 https://mydomain.com
```

### Test Redemption
```bash
php scripts/test_redeem.php
```

## 🌐 Access URLs

- **Redeem Page**: `http://yourdomain.com/public/index.php`
- **Direct Redeem**: `http://yourdomain.com/public/index.php?code=XXXXX`
- **API Endpoint**: `http://yourdomain.com/public/qr-check.php?code=XXXXX`

## ⚠️ Important Notes

1. The system uses your existing `liyas_international` database
2. QR tables are in `config/requirements/schema.sql` (already added)
3. Database config matches your main project settings
4. QR images are saved to `uploads/qrs/` directory

## 🐛 Troubleshooting

**Database Connection Error:**
- Check `config/db.php` matches your database settings
- Verify database `liyas_international` exists

**QR Library Not Found:**
- Install via Composer: `composer require endroid/qr-code`
- Or system will use placeholder images (still works!)

**Permission Errors:**
- Ensure `uploads/qrs/` directory is writable
- Check file permissions: `chmod 755 uploads/qrs/`

## ✅ System Status

- ✅ Database configuration: Ready (uses `liyas_international`)
- ✅ Schema: Ready (tables defined in `config/requirements/schema.sql`)
- ✅ QR Generator: Ready (`scripts/generate_qr.php`)
- ✅ Redeem Page: Ready (`public/index.php`)
- ✅ Validation API: Ready (`public/qr-check.php`)
- ✅ Test Script: Ready (`scripts/test_redeem.php`)

**Next Step:** Create the database tables and generate your first batch of QR codes!

