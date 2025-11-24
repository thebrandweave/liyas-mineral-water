# Deployment Guide

## Quick Fix for HTTP 500 Error

The error occurs because `config/db_config.php` is not on the live server (it's in `.gitignore` for security).

### Solution 1: Create db_config.php on Live Server (Recommended)

**Via FTP/File Manager:**
1. Log into your hosting control panel (cPanel/File Manager)
2. Navigate to: `public_html/config/` (or your project root)
3. Create a new file: `db_config.php`
4. Copy the content from `db_config.example.php` and update it:

```php
<?php
// Set to live
$ENVIRONMENT = 'live';

if ($ENVIRONMENT === 'live') {
    $host = "localhost";
    $dbname = "u232955123_liyas_inter";
    $username = "u232955123_liyas";
    $password = "Brandweave@24"; // Your password
}

// ... rest of the file from db_config.example.php
```

**Via SSH:**
```bash
cd /path/to/your/project
cp config/db_config.example.php config/db_config.php
nano config/db_config.php
# Change $ENVIRONMENT = 'live' and add password
```

### Solution 2: Use Fallback (Temporary)

The code now has a fallback that will use live settings if `db_config.php` doesn't exist. However, it's better to create the file properly.

## Post-Deployment Checklist

1. ✅ Create `config/db_config.php` on live server
2. ✅ Set `$ENVIRONMENT = 'live'` in db_config.php
3. ✅ Add live database password
4. ✅ Verify database connection works
5. ✅ Test admin login: `https://liyasinternational.com/admin/login.php`
6. ✅ Test redeem page: `https://liyasinternational.com/redeem`

## Database Setup on Live

1. Import the database schema:
   ```sql
   -- Run this in phpMyAdmin or via SSH
   -- Import: config/requirements/schema.sql
   ```

2. Create admin user (if needed):
   ```sql
   INSERT INTO admins (username, email, password_hash, role) 
   VALUES ('admin', 'admin@liyasinternational.com', '$2y$10$...', 'superadmin');
   ```

## File Permissions

Make sure these directories are writable:
```bash
chmod 755 uploads/
chmod 755 uploads/qrs/
```

## Environment Variables (Alternative)

If your hosting supports environment variables, you can use:
- `DB_HOST`
- `DB_NAME`
- `DB_USER`
- `DB_PASS`

Then update `db_config.php` to read from `$_ENV`.

## Troubleshooting

### Still getting 500 error?
1. Check error logs in cPanel → Error Logs
2. Enable error display temporarily:
   ```php
   ini_set('display_errors', 1);
   error_reporting(E_ALL);
   ```
3. Check file permissions on `config/` directory

### Database connection fails?
1. Verify database credentials in cPanel
2. Check if database user has proper permissions
3. Verify database name is correct: `u232955123_liyas_inter`

