<?php
/**
 * QR Code Redeem Page
 * Collects customer data and processes redemption
 */

require_once __DIR__ . '/../config/config.php';

// Get code from URL parameter
$code = isset($_GET['code']) ? trim($_GET['code']) : '';
$qr_status = null;
$qr_data = null;

// Check QR code status if code is provided
if (!empty($code)) {
    try {
        $stmt = $pdo->prepare("SELECT id, code, is_used, scanned_at FROM qr_codes WHERE code = ?");
        $stmt->execute([$code]);
        $qr_data = $stmt->fetch();
        
        if ($qr_data) {
            if ($qr_data['is_used'] == 1) {
                $qr_status = 'already_used';
            } else {
                $qr_status = 'valid';
            }
        } else {
            $qr_status = 'not_found';
        }
    } catch (PDOException $e) {
        $qr_status = 'error';
    }
}

// Handle form submission
$submitted = false;
$success = false;
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['code'])) {
    $submitted = true;
    $code = trim($_POST['code']);
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    
    // Validate inputs
    if (empty($name) || empty($phone) || empty($address)) {
        $error_message = 'Please fill in all fields (Name, Phone, and Address)';
    } else {
        try {
            $pdo->beginTransaction();
            
            // Check and lock QR code
            $stmt = $pdo->prepare("SELECT id, code, is_used FROM qr_codes WHERE code = ? FOR UPDATE");
            $stmt->execute([$code]);
            $qr = $stmt->fetch();
            
            if (!$qr) {
                $error_message = 'QR code not found';
                $pdo->rollBack();
            } elseif ($qr['is_used'] == 1) {
                $error_message = 'This QR code has already been redeemed';
                $pdo->rollBack();
            } else {
                // Mark QR as used
                $updateStmt = $pdo->prepare("UPDATE qr_codes SET is_used = 1, scanned_at = NOW() WHERE id = ? AND is_used = 0");
                $updateStmt->execute([$qr['id']]);
                
                if ($updateStmt->rowCount() > 0) {
                    // Save customer data
                    $ip_address = $_SERVER['REMOTE_ADDR'] ?? null;
                    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;
                    
                    $logStmt = $pdo->prepare("
                        INSERT INTO reward_logs (qr_code_id, customer_name, customer_phone, customer_address, ip_address, user_agent) 
                        VALUES (?, ?, ?, ?, ?, ?)
                    ");
                    $logStmt->execute([$qr['id'], $name, $phone, $address, $ip_address, $user_agent]);
                    
                    $pdo->commit();
                    $success = true;
                } else {
                    $error_message = 'This QR code has already been redeemed';
                    $pdo->rollBack();
                }
            }
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error_message = 'An error occurred. Please try again.';
            error_log("QR redemption error: " . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redeem QR Code - LIYAS Mineral Water</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 500px;
            width: 100%;
            padding: 40px;
            text-align: center;
        }

        .logo {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
        }

        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
        }

        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
            font-size: 14px;
        }

        input[type="text"],
        input[type="tel"],
        textarea {
            width: 100%;
            padding: 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 16px;
            font-family: inherit;
            transition: border-color 0.3s;
        }

        input:focus, textarea:focus {
            outline: none;
            border-color: #667eea;
        }

        textarea {
            resize: vertical;
            min-height: 80px;
        }

        .btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            margin-top: 10px;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.4);
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .message {
            margin-top: 20px;
            padding: 15px;
            border-radius: 10px;
            font-size: 14px;
            text-align: left;
        }

        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .message.warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }

        .instagram-box {
            margin-top: 25px;
            padding: 20px;
            background: linear-gradient(135deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%);
            border-radius: 15px;
            color: white;
        }

        .instagram-box h3 {
            margin-bottom: 10px;
            font-size: 18px;
        }

        .instagram-box a {
            color: white;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 10px;
        }

        .instagram-box a:hover {
            text-decoration: underline;
        }

        .code-display {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-family: monospace;
            font-size: 14px;
            color: #667eea;
            font-weight: 600;
        }

        .hidden {
            display: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">💧</div>
        <h1>🎁 Redeem Your Reward</h1>
        <p class="subtitle">LIYAS Mineral Water - Thank you for choosing us!</p>

        <?php if ($success): ?>
            <!-- Success Message -->
            <div class="message success">
                <h3 style="margin-bottom: 10px;">✅ Reward Claimed Successfully!</h3>
                <p>Thank you, <strong><?= htmlspecialchars($_POST['name']) ?></strong>! Your reward has been registered.</p>
            </div>

            <div class="instagram-box">
                <h3><i class="fab fa-instagram"></i> Follow Us on Instagram</h3>
                <p>Stay updated with our latest offers, promotions, and rewards!</p>
                <a href="https://instagram.com/liyasmineralwater" target="_blank">
                    <i class="fab fa-instagram"></i> Follow @liyasmineralwater
                </a>
            </div>

        <?php elseif ($qr_status === 'already_used'): ?>
            <!-- Already Used -->
            <div class="message warning">
                <h3 style="margin-bottom: 10px;">⚠️ Already Redeemed</h3>
                <p>This QR code has already been used. Each code can only be redeemed once.</p>
                <p style="margin-top: 10px; font-size: 12px;">Redeemed on: <?= date('M d, Y H:i', strtotime($qr_data['scanned_at'])) ?></p>
            </div>

        <?php elseif ($qr_status === 'not_found'): ?>
            <!-- Not Found -->
            <div class="message error">
                <h3 style="margin-bottom: 10px;">❌ Invalid QR Code</h3>
                <p>This QR code was not found in our system. Please check the code and try again.</p>
            </div>

        <?php elseif ($qr_status === 'valid' || !empty($code)): ?>
            <!-- Customer Data Form -->
            <?php if ($error_message): ?>
                <div class="message error">
                    <?= htmlspecialchars($error_message) ?>
                </div>
            <?php endif; ?>

            <div class="code-display">
                Code: <?= htmlspecialchars($code) ?>
            </div>

            <form method="POST" action="" id="redeemForm">
                <input type="hidden" name="code" value="<?= htmlspecialchars($code) ?>">
                
                <div class="form-group">
                    <label for="name"><i class="fas fa-user"></i> Full Name *</label>
                    <input 
                        type="text" 
                        name="name" 
                        id="name" 
                        placeholder="Enter your full name"
                        required
                        autofocus
                        value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="phone"><i class="fas fa-phone"></i> Phone Number *</label>
                    <input 
                        type="tel" 
                        name="phone" 
                        id="phone" 
                        placeholder="Enter your phone number"
                        required
                        value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="address"><i class="fas fa-map-marker-alt"></i> Address *</label>
                    <textarea 
                        name="address" 
                        id="address" 
                        placeholder="Enter your complete address"
                        required
                    ><?= htmlspecialchars($_POST['address'] ?? '') ?></textarea>
                </div>

                <button type="submit" class="btn" id="submitBtn">
                    <i class="fas fa-gift"></i> Claim Reward
                </button>
            </form>

        <?php else: ?>
            <!-- Initial QR Code Input -->
            <div class="code-display" style="display: none;" id="codeDisplay"></div>

            <form method="GET" action="" id="codeForm">
                <div class="form-group">
                    <label for="codeInput">Enter or Scan QR Code</label>
                    <input 
                        type="text" 
                        name="code" 
                        id="codeInput" 
                        placeholder="Enter QR code here"
                        autocomplete="off"
                        required
                        autofocus
                    >
                </div>
                <button type="submit" class="btn">
                    <i class="fas fa-qrcode"></i> Check Code
                </button>
            </form>
        <?php endif; ?>

        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e0e0e0; color: #999; font-size: 12px;">
            <p>One-time use only • Each code can be redeemed once</p>
        </div>
    </div>

    <script>
        // Handle form submission
        document.getElementById('redeemForm')?.addEventListener('submit', function(e) {
            const btn = document.getElementById('submitBtn');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
        });
    </script>
</body>
</html>
