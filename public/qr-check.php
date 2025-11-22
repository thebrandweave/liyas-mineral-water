<?php
/**
 * Reward Code Validation API
 * Validates reward codes via API
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

// Get and sanitize reward code
$reward_code = isset($_GET['code']) ? trim($_GET['code']) : '';

// Validate code format
if (empty($reward_code)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Reward code is required',
        'status' => 'error'
    ]);
    exit;
}

try {
    // Check if reward code exists and get current status
    $stmt = $pdo->prepare("SELECT id, reward_code, is_used, used_at FROM codes WHERE reward_code = ?");
    $stmt->execute([$reward_code]);
    $code_data = $stmt->fetch();
    
    if (!$code_data) {
        // Code doesn't exist
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid code',
            'status' => 'error'
        ]);
        exit;
    }
    
    // Check if already used
    if ($code_data['is_used'] == 1) {
        http_response_code(200);
        echo json_encode([
            'success' => false,
            'message' => 'Already redeemed',
            'status' => 'warning',
            'used_at' => $code_data['used_at']
        ]);
        exit;
    }
    
    // Code is valid and available
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Success',
        'status' => 'valid',
        'code' => $code_data['reward_code']
    ]);
    
} catch (PDOException $e) {
    error_log("Reward code validation error: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while processing your request',
        'status' => 'error'
    ]);
}
