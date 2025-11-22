# Correct URLs for QR Reward System

## ✅ Working URLs

### Admin Panel - Generate QR Code
```
http://localhost/liyas-mineral-water/admin/qr-rewards/generate.php
```

### Admin Panel - View All QR Codes
```
http://localhost/liyas-mineral-water/admin/qr-rewards/index.php
```

### Public Redeem Page
```
http://localhost/liyas-mineral-water/public/index.php
```

### Test Specific QR Code
```
http://localhost/liyas-mineral-water/public/index.php?code=YOUR_CODE_HERE
```

### View QR Image (if generated)
```
http://localhost/liyas-mineral-water/uploads/qrs/CODE.png
```

## 🔍 Troubleshooting "Not Found" Error

### Check 1: Verify XAMPP is Running
- Open XAMPP Control Panel
- Make sure Apache is running (green)
- Make sure MySQL is running (green)

### Check 2: Verify Project Path
Your project should be at:
```
C:\xampp\htdocs\liyas-mineral-water\
```

### Check 3: Test Basic Access
Try accessing:
```
http://localhost/liyas-mineral-water/
```

If this doesn't work, check:
- Apache is running
- Port 80 is not blocked
- Project folder exists in htdocs

### Check 4: Direct File Access
Try accessing files directly:
```
http://localhost/liyas-mineral-water/admin/index.php
http://localhost/liyas-mineral-water/public/index.php
```

## 📝 Common Issues

### Issue: "Not Found" for admin pages
**Solution:** Make sure you're logged in first:
```
http://localhost/liyas-mineral-water/admin/login.php
```

### Issue: "Not Found" for public pages
**Solution:** Check if `public/index.php` exists and is accessible

### Issue: QR images not loading
**Solution:** Check `uploads/qrs/` folder exists and has write permissions

## 🎯 Quick Test Steps

1. **Test Admin Login:**
   ```
   http://localhost/liyas-mineral-water/admin/login.php
   ```

2. **After Login, Generate QR:**
   ```
   http://localhost/liyas-mineral-water/admin/qr-rewards/generate.php
   ```

3. **Test Public Redeem:**
   ```
   http://localhost/liyas-mineral-water/public/index.php
   ```

