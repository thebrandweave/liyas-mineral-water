# QR Reward System

A secure, one-time QR code redemption system built with PHP.

## Features

- ✅ One-time use QR codes
- ✅ Secure validation with database locking
- ✅ Bulk QR code generation (1000+ codes)
- ✅ Beautiful, responsive redeem interface
- ✅ API-based validation
- ✅ Reward logging and tracking
- ✅ Race condition protection

## Installation

### 1. Database Setup

The QR reward system uses your existing `liyas_international` database. The tables are already defined in `config/requirements/schema.sql` (lines 71-97).

To add just the QR tables to your existing database:

```bash
# Option 1: Import only QR tables
mysql -u root -p liyas_international < sql/qr_tables_only.sql

# Option 2: The tables are already in config/requirements/schema.sql
# Just run the CREATE TABLE statements for qr_codes and reward_logs
```

Or manually run the SQL from `config/requirements/schema.sql` (lines 77-97).

### 2. Configure Database

The system uses your existing database configuration. The `config/db.php` file is already configured to use:
- Database: `liyas_international`
- Host: `localhost`
- User: `root`
- Password: (empty by default)

If you need to change these, edit `config/db.php`.

### 3. Install Dependencies

**Option A: Using Composer (Recommended)**
```bash
composer require endroid/qr-code
```

**Option B: Manual Installation**
- Download phpqrcode library from: https://github.com/endroid/qr-code
- Place in `vendor/endroid/qr-code/` or `includes/phpqrcode/`

### 4. Set Permissions

```bash
chmod 755 uploads/qrs/
chown www-data:www-data uploads/qrs/
```

### 5. Generate QR Codes

```bash
php scripts/generate_qr.php 1000 https://yourdomain.com
```

Parameters:
- `1000` - Number of QR codes to generate
- `https://yourdomain.com` - Base URL for QR codes

## Usage

### Redeem QR Code

Users can redeem codes via:
- **Web Interface**: `https://yourdomain.com/public/index.php?code=XXXXX`
- **Direct URL**: `https://yourdomain.com/public/redeem?code=XXXXX`
- **API**: `https://yourdomain.com/public/qr-check.php?code=XXXXX`

### API Response Format

**Success:**
```json
{
    "success": true,
    "message": "🎉 Reward redeemed successfully!",
    "status": "success",
    "code": "ABC123XYZ",
    "redeemed_at": "2024-01-15 10:30:00"
}
```

**Already Redeemed:**
```json
{
    "success": false,
    "message": "This QR code has already been redeemed",
    "status": "warning",
    "scanned_at": "2024-01-15 09:15:00"
}
```

**Not Found:**
```json
{
    "success": false,
    "message": "QR code not found",
    "status": "error"
}
```

## Project Structure

```
liyas-mineral-water/
├── config/
│   ├── db.php                      # QR system database config
│   ├── config.php                  # Main project config
│   └── requirements/
│       └── schema.sql              # Database schema (includes QR tables)
├── public/
│   ├── index.php                   # QR Redeem page (frontend)
│   ├── qr-check.php                # Validation API
│   └── .htaccess                   # Security settings
├── scripts/
│   ├── generate_qr.php             # Bulk QR generator
│   ├── test_redeem.php              # Test script
│   └── install_dependencies.php
├── sql/
│   └── qr_tables_only.sql           # QR tables only (optional)
├── uploads/
│   └── qrs/                         # Generated QR images
└── README.md
```

## Security Features

- ✅ SQL injection protection (prepared statements)
- ✅ Race condition prevention (database locking)
- ✅ Input validation and sanitization
- ✅ Secure error handling
- ✅ Transaction support
- ✅ IP and user agent logging

## Database Schema

### qr_codes Table
- `id` - Primary key
- `code` - Unique QR code string
- `is_used` - Boolean flag (0/1)
- `scanned_at` - Timestamp of redemption
- `created_at` - Creation timestamp

### reward_logs Table
- `id` - Primary key
- `qr_code_id` - Foreign key to qr_codes
- `ip_address` - Redeemer's IP
- `user_agent` - Browser/client info
- `redeemed_at` - Redemption timestamp

## Performance

- Indexed database queries
- Optimized for bulk generation (1000+ codes)
- Transaction-based operations
- Efficient file I/O

## Testing

1. Generate test codes:
```bash
php scripts/generate_qr.php 10 https://yourdomain.com
```

2. Test redemption:
- Visit: `public/index.php?code=YOUR_CODE`
- Or use API: `public/qr-check.php?code=YOUR_CODE`

3. Verify one-time use:
- Try redeeming the same code twice
- Second attempt should show "Already redeemed"

## Troubleshooting

### QR Library Not Found
- Install via Composer: `composer require endroid/qr-code`
- Or download manually and place in `vendor/` or `includes/`

### Permission Errors
```bash
chmod 755 uploads/qrs/
chown www-data:www-data uploads/qrs/
```

### Database Connection Failed
- Check credentials in `config/db.php`
- Verify database exists: `mysql -u root -p -e "SHOW DATABASES;"`
- Verify QR tables exist: `mysql -u root -p liyas_international -e "SHOW TABLES LIKE 'qr_%';"`
- Import QR tables: `mysql -u root -p liyas_international < sql/qr_tables_only.sql`

## License

MIT License - Feel free to use and modify.

## Support

For issues or questions, please check:
- Database connection settings
- File permissions
- PHP error logs
- Web server error logs

