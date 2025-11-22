# Reward Code System

## Overview

The new reward code system uses a **single common QR code** for all bottles that redirects to the redeem page. Each bottle has a **unique reward code** printed on the sticker that users manually enter.

## How It Works

1. **All bottles** use ONE common QR code that redirects to: `https://mydomain.com/redeem`
2. **Each bottle** has a UNIQUE reward code printed on the backside of the sticker
   - Format: `Liyas-SFA123Fcg` (prefix + 6-10 random alphanumeric characters)
3. Users scan the QR code (or visit the redeem page) and manually enter their reward code
4. Backend validates the code and marks it as used

## Database Schema

```sql
CREATE TABLE codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reward_code VARCHAR(50) UNIQUE NOT NULL,
    is_used TINYINT(1) DEFAULT 0,
    used_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_reward_code (reward_code),
    INDEX idx_is_used (is_used)
);
```

## Backend Logic

- **If reward_code exists AND is_unused** → return "Success" + mark as used
- **If reward_code is already used** → return "Already redeemed"
- **If reward_code doesn't exist** → return "Invalid code"

## Files

### Public Pages
- `public/index.php` - Redeem page with manual code input
- `public/qr-check.php` - API endpoint for code validation

### Admin Pages
- `admin/qr-rewards/index.php` - View and manage reward codes
- `admin/qr-rewards/generate.php` - Generate new reward codes via web interface

### Scripts
- `scripts/generate_reward_codes.php` - Bulk code generator (CLI)

## Usage

### Generate Reward Codes (CLI)

```bash
php scripts/generate_reward_codes.php [count] [prefix]

# Examples:
php scripts/generate_reward_codes.php 1000 Liyas-
php scripts/generate_reward_codes.php 5000 Liyas-
```

### Generate Reward Codes (Web)

1. Go to Admin Panel → Reward Codes → Generate Codes
2. Enter number of codes and prefix
3. Click "Generate Codes"

### Redeem Flow

1. User scans QR code on bottle (or visits `/redeem`)
2. User finds unique reward code on sticker (e.g., `Liyas-SFA123Fcg`)
3. User enters code in the input box
4. System validates and marks as used

## Migration from Old System

If you're migrating from the old QR code system:

1. **Backup your database**
2. Run the migration script:
   ```bash
   mysql -u root -p liyas_international < sql/migrate_to_reward_codes.sql
   ```
3. Generate new reward codes using the generator script

## API Endpoint

### Validate Code

**GET** `/public/qr-check.php?code=REWARD_CODE`

**Response (Success):**
```json
{
    "success": true,
    "message": "Success",
    "status": "valid",
    "code": "Liyas-SFA123Fcg"
}
```

**Response (Already Used):**
```json
{
    "success": false,
    "message": "Already redeemed",
    "status": "warning",
    "used_at": "2024-01-15 10:30:00"
}
```

**Response (Invalid):**
```json
{
    "success": false,
    "message": "Invalid code",
    "status": "error"
}
```

## Code Format

- **Prefix**: Configurable (default: "Liyas-")
- **Suffix**: 6-10 random alphanumeric characters (A-Z, 0-9)
- **Example**: `Liyas-SFA123Fcg`, `Liyas-ABC456XYZ`, `Liyas-789DEF`

## Security Features

- ✅ SQL injection protection (prepared statements)
- ✅ Race condition prevention (database locking with FOR UPDATE)
- ✅ Input validation and sanitization
- ✅ Transaction support
- ✅ One-time use enforcement

## Notes

- All bottles use the **same QR code** pointing to `/redeem`
- Each bottle has a **unique reward code** on the sticker
- Codes are **case-insensitive** (stored uppercase)
- Codes can only be redeemed **once**
- No customer data is collected (simplified system)

