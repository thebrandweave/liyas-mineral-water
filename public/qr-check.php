<?php
/**
 * QR Code Validation API
 * Checks if QR code is valid and available for redemption
 * Note: Actual redemption happens in index.php after customer data is collected
 */

require_once __DIR__ . '/../config/config.php';

// Set JSON response header
header('Content-Type: application/json');

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed',
        'status' => 'error'
    ]);
    exit;
}

// Get and sanitize code
$code = isset($_GET['code']) ? trim($_GET['code']) : '';

// Validate code format
if (empty($code)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'QR code is required',
        'status' => 'error'
    ]);
    exit;
}

if (!preg_match('/^[a-zA-Z0-9]{8,64}$/', $code)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid QR code format',
        'status' => 'error'
    ]);
    exit;
}

try {
    // Check if code exists and get current status
    $stmt = $pdo->prepare("SELECT id, code, is_used, scanned_at FROM qr_codes WHERE code = ?");
    $stmt->execute([$code]);
    $qr = $stmt->fetch();
    
    if (!$qr) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'QR code not found',
            'status' => 'error'
        ]);
        exit;
    }
    
    // Check if already used
    if ($qr['is_used'] == 1) {
        http_response_code(200);
        echo json_encode([
            'success' => false,
            'message' => 'This QR code has already been redeemed',
            'status' => 'warning',
            'scanned_at' => $qr['scanned_at']
        ]);
        exit;
    }
    
    // Code is valid and available
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'QR code is valid. Please fill in your details to claim the reward.',
        'status' => 'valid',
        'code' => $qr['code']
    ]);
    
} catch (PDOException $e) {
    error_log("QR validation error: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while processing your request',
        'status' => 'error'
    ]);
}
