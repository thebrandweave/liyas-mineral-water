<?php
/**
 * Dependency Installer
 * Helps install phpqrcode library if not using Composer
 */

echo "📦 QR Reward System - Dependency Installer\n";
echo str_repeat("=", 50) . "\n\n";

// Check if Composer is available
$composer_path = __DIR__ . '/../composer.json';
if (file_exists($composer_path)) {
    echo "✅ Composer detected\n";
    echo "Run: composer install\n\n";
    exit(0);
}

// Download phpqrcode library
echo "📥 Downloading phpqrcode library...\n";

$library_url = 'https://github.com/endroid/qr-code/archive/refs/heads/main.zip';
$download_path = __DIR__ . '/../vendor/temp.zip';
$extract_path = __DIR__ . '/../vendor/';

// Create vendor directory
if (!is_dir($extract_path)) {
    mkdir($extract_path, 0755, true);
}

// Alternative: Manual installation instructions
echo "\n📋 Manual Installation Instructions:\n";
echo str_repeat("-", 50) . "\n";
echo "Option 1: Using Composer (Recommended)\n";
echo "  cd " . dirname(__DIR__) . "\n";
echo "  composer require endroid/qr-code\n\n";

echo "Option 2: Download phpqrcode manually\n";
echo "  1. Download from: https://github.com/endroid/qr-code\n";
echo "  2. Extract to: " . $extract_path . "endroid/qr-code/\n";
echo "  3. Or use: " . __DIR__ . "/../includes/phpqrcode/qrlib.php\n\n";

echo "Option 3: Use simple placeholder (for testing)\n";
echo "  The generator will create placeholder images if library is not found.\n\n";

