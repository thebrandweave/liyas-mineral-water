# QR Reward System - Testing Guide

## ✅ Test Results

All system tests passed successfully! The QR reward system is ready to use.

## 🧪 What Was Tested

1. ✅ Database connection
2. ✅ QR tables exist (`qr_codes`, `reward_logs`)
3. ✅ Uploads directory is writable
4. ✅ Generated 10 test QR codes
5. ✅ Redemption flow works
6. ✅ Double redemption prevention works
7. ✅ All required files exist

## 🌐 Manual Testing Steps

### 1. Test Admin Panel

**URL:** `http://localhost/liyas-mineral-water/admin/qr-rewards/index.php`

**What to check:**
- [ ] Login to admin panel first (if not logged in)
- [ ] Navigate to "QR Rewards" in sidebar
- [ ] See statistics cards (Total, Redeemed, Available, Recent)
- [ ] See the list of QR codes in the table
- [ ] Test search functionality
- [ ] Test filter (All, Redeemed, Available)
- [ ] Test pagination (if more than 50 codes)
- [ ] Click "Copy" button to copy redeem URL
- [ ] Click "View" link to see QR image (if available)

### 2. Test Public Redeem Page

**URL:** `http://localhost/liyas-mineral-water/public/index.php`

**What to check:**
- [ ] Page loads with redeem interface
- [ ] Enter a test QR code (use one from admin panel)
- [ ] Click "Redeem Code" button
- [ ] See success message for unused codes
- [ ] Try redeeming the same code again
- [ ] See "Already redeemed" message

### 3. Test API Endpoint

**URL:** `http://localhost/liyas-mineral-water/public/qr-check.php?code=YOUR_CODE`

**What to check:**
- [ ] Replace `YOUR_CODE` with an actual code from database
- [ ] First request returns success JSON
- [ ] Second request returns "already redeemed" JSON
- [ ] Invalid code returns "not found" JSON

### 4. Test with Browser Developer Tools

1. Open browser DevTools (F12)
2. Go to Network tab
3. Redeem a code
4. Check the API response in Network tab
5. Verify JSON response format

## 📝 Test QR Codes Generated

The system has generated 10 test QR codes. You can find them:
- In database: `qr_codes` table
- In admin panel: QR Rewards page
- QR images: `uploads/qrs/` directory

## 🔍 Sample Test Codes

Use these codes for testing (if they exist in your database):

1. Check admin panel for actual codes
2. Or run: `SELECT code FROM qr_codes LIMIT 5;` in MySQL

## 🎯 Test Scenarios

### Scenario 1: Successful Redemption
1. Get an unused QR code from admin panel
2. Go to public redeem page
3. Enter the code
4. Click "Redeem Code"
5. ✅ Should show success message
6. Check admin panel - code should show as "Redeemed"

### Scenario 2: Double Redemption Prevention
1. Use the same code from Scenario 1
2. Try to redeem again
3. ✅ Should show "Already redeemed" message
4. API should return warning status

### Scenario 3: Invalid Code
1. Enter a random code like "INVALID123"
2. Try to redeem
3. ✅ Should show "QR code not found" message

### Scenario 4: Search and Filter
1. Go to admin QR Rewards page
2. Use search box to find a specific code
3. Use filter dropdown to show only "Redeemed" codes
4. ✅ Should filter results correctly

## 🐛 Troubleshooting

### If admin panel shows "No QR codes found"
- Run: `C:\xampp\php\php.exe scripts/generate_qr.php 10 http://localhost/liyas-mineral-water`
- Or use the test script which auto-generates codes

### If redemption doesn't work
- Check database connection in `config/db.php`
- Verify `qr_codes` table exists
- Check PHP error logs

### If QR images don't show
- Check `uploads/qrs/` directory permissions
- Verify images were generated
- Check file paths in code

## 📊 Expected Results

### Admin Panel Should Show:
- 4 statistics cards with numbers
- Table with QR codes
- Search and filter options
- Pagination (if > 50 codes)

### Public Redeem Should:
- Show beautiful redeem interface
- Accept QR codes
- Show success/error messages
- Prevent double redemption

### API Should Return:
```json
// Success
{
    "success": true,
    "message": "🎉 Reward redeemed successfully!",
    "status": "success"
}

// Already Redeemed
{
    "success": false,
    "message": "This QR code has already been redeemed",
    "status": "warning"
}

// Not Found
{
    "success": false,
    "message": "QR code not found",
    "status": "error"
}
```

## ✅ Next Steps

1. ✅ System is tested and working
2. Generate more QR codes: `C:\xampp\php\php.exe scripts/generate_qr.php 1000 http://yourdomain.com`
3. Customize the public redeem page design
4. Add more features as needed

## 🎉 System Status: READY FOR USE!

All components are working correctly. You can now:
- Generate QR codes in bulk
- Monitor redemptions in admin panel
- Allow users to redeem codes
- Track redemption history

