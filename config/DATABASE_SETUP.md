# Database Configuration Guide

## Quick Setup

### For Local Development (XAMPP)
1. Open `config/db_config.php`
2. Make sure this line is active:
   ```php
   $ENVIRONMENT = 'local';
   ```
3. Comment out the live line:
   ```php
   // $ENVIRONMENT = 'live';
   ```

### For Live Production
1. Open `config/db_config.php`
2. Comment out the local line:
   ```php
   // $ENVIRONMENT = 'local';
   ```
3. Uncomment the live line:
   ```php
   $ENVIRONMENT = 'live';
   ```
4. **IMPORTANT**: Add your live database password:
   ```php
   $password = "YOUR_LIVE_PASSWORD_HERE";
   ```

## Current Configuration

### Local (XAMPP)
- **Host**: localhost
- **Database**: liyas_international
- **Username**: root
- **Password**: (empty)

### Live (Production)
- **Host**: localhost
- **Database**: u232955123_liyas_inter
- **Username**: u232955123_liyas
- **Password**: ⚠️ **Add your password in db_config.php**

## Security Notes

- `db_config.php` is in `.gitignore` to keep passwords secure
- Never commit database passwords to Git
- Use `db_config.example.php` as a template (without passwords)

## How to Switch

Simply edit `config/db_config.php` and change:
```php
$ENVIRONMENT = 'local';  // For local
// $ENVIRONMENT = 'live'; // For live
```

To:
```php
// $ENVIRONMENT = 'local'; // For local
$ENVIRONMENT = 'live';     // For live
```

## Testing Connection

After switching, test the connection by:
1. Visiting any admin page
2. Or running: `php -r "require 'config/config.php'; echo 'Connected to: ' . getEnvironment();"`

