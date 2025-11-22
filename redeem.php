<?php
/**
 * Reward Code Redeem Page
 * QR code redirects to: liyasinternational.com/redeem.php
 * Flow: Customer Info → Reward Code → Success Message
 */

require_once __DIR__ . '/config/config.php';

$step = isset($_GET['step']) ? $_GET['step'] : 'info';
$message = '';
$message_type = '';

// Handle customer info submission (Step 1)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['step']) && $_POST['step'] === 'info') {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');
    
    // Validate required fields
    if (empty($name) || empty($phone) || empty($address)) {
        $message = 'Please fill in all required fields (Name, Phone, and Address)';
        $message_type = 'error';
        $step = 'info';
    } else {
        // Store in session to use in next step
        $_SESSION['redeem_customer'] = [
            'name' => $name,
            'phone' => $phone,
            'email' => $email,
            'address' => $address
        ];
        $step = 'code';
    }
}

// Handle reward code submission (Step 2)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['step']) && $_POST['step'] === 'code') {
    $reward_code = trim($_POST['reward_code'] ?? '');
    
    // Check if customer info exists in session
    if (!isset($_SESSION['redeem_customer'])) {
        $message = 'Please fill in your information first';
        $message_type = 'error';
        $step = 'info';
    } elseif (empty($reward_code)) {
        $message = 'Please enter a reward code';
        $message_type = 'error';
        $step = 'code';
    } else {
        try {
            $pdo->beginTransaction();
            
            // Check if reward code exists and lock it
            $stmt = $pdo->prepare("SELECT id, reward_code, is_used, used_at FROM codes WHERE reward_code = ? FOR UPDATE");
            $stmt->execute([$reward_code]);
            $code_data = $stmt->fetch();
            
            if (!$code_data) {
                // Code doesn't exist
                $message = 'Invalid code';
                $message_type = 'error';
                $step = 'code';
                $pdo->rollBack();
            } elseif ($code_data['is_used'] == 1) {
                // Code already used
                $message = 'Already redeemed';
                $message_type = 'warning';
                $step = 'code';
                $pdo->rollBack();
            } else {
                // Code is valid and unused - mark as used and save customer data
                $customer = $_SESSION['redeem_customer'];
                $updateStmt = $pdo->prepare("
                    UPDATE codes 
                    SET is_used = 1, 
                        used_at = NOW(),
                        customer_name = ?,
                        customer_phone = ?,
                        customer_email = ?,
                        customer_address = ?
                    WHERE id = ? AND is_used = 0
                ");
                $updateStmt->execute([
                    $customer['name'],
                    $customer['phone'],
                    $customer['email'] ?: null,
                    $customer['address'],
                    $code_data['id']
                ]);
                
                if ($updateStmt->rowCount() > 0) {
                    $pdo->commit();
                    $step = 'success';
                    // Clear session data
                    unset($_SESSION['redeem_customer']);
                } else {
                    // Race condition - code was used between check and update
                    $message = 'Already redeemed';
                    $message_type = 'warning';
                    $step = 'code';
                    $pdo->rollBack();
                }
            }
        } catch (PDOException $e) {
            $pdo->rollBack();
            $message = 'An error occurred. Please try again.';
            $message_type = 'error';
            $step = 'code';
            error_log("Reward code redemption error: " . $e->getMessage());
        }
    }
}

// Get customer data from session if exists
$customer = $_SESSION['redeem_customer'] ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redeem Your Reward - LIYAS International</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
            max-width: 550px;
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

        .step-indicator {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 30px;
        }

        .step {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 14px;
        }

        .step.active {
            background: #667eea;
            color: white;
        }

        .step.completed {
            background: #22c55e;
            color: white;
        }

        .step.inactive {
            background: #e0e0e0;
            color: #999;
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

        label .required {
            color: #ef4444;
        }

        input[type="text"],
        input[type="tel"],
        input[type="email"],
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

        .btn-secondary {
            background: #6b7280;
            margin-top: 10px;
        }

        .message {
            margin-top: 20px;
            padding: 15px;
            border-radius: 10px;
            font-size: 14px;
            text-align: center;
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
            padding: 25px;
            background: linear-gradient(135deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%);
            border-radius: 15px;
            color: white;
        }

        .instagram-box h3 {
            margin-bottom: 15px;
            font-size: 20px;
        }

        .instagram-box p {
            margin-bottom: 15px;
            line-height: 1.6;
        }

        .instagram-box a {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 12px 24px;
            background: white;
            color: #bc1888;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 600;
            transition: transform 0.2s;
        }

        .instagram-box a:hover {
            transform: scale(1.05);
        }

        .info-text {
            margin-top: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 10px;
            font-size: 13px;
            color: #666;
            text-align: left;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">💧</div>
        <h1>🎁 Redeem Your Reward</h1>
        <p class="subtitle">LIYAS International - Thank you for choosing us!</p>

        <!-- Step Indicator -->
        <div class="step-indicator">
            <div class="step <?= $step === 'info' ? 'active' : ($step === 'code' || $step === 'success' ? 'completed' : 'inactive') ?>">1</div>
            <div class="step <?= $step === 'code' ? 'active' : ($step === 'success' ? 'completed' : 'inactive') ?>">2</div>
            <div class="step <?= $step === 'success' ? 'active' : 'inactive' ?>">3</div>
        </div>

        <?php if ($message): ?>
            <div class="message <?= $message_type ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <?php if ($step === 'info'): ?>
            <!-- Step 1: Customer Information -->
            <form method="POST" action="" id="infoForm">
                <input type="hidden" name="step" value="info">
                
                <div class="form-group">
                    <label for="name">
                        <i class="fas fa-user"></i> Full Name <span class="required">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="name" 
                        id="name" 
                        placeholder="Enter your full name"
                        required
                        autofocus
                        value="<?= htmlspecialchars($customer['name'] ?? '') ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="phone">
                        <i class="fas fa-phone"></i> Phone Number <span class="required">*</span>
                    </label>
                    <input 
                        type="tel" 
                        name="phone" 
                        id="phone" 
                        placeholder="Enter your phone number"
                        required
                        value="<?= htmlspecialchars($customer['phone'] ?? '') ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="email">
                        <i class="fas fa-envelope"></i> Email Address <span style="color: #666; font-weight: normal;">(Optional)</span>
                    </label>
                    <input 
                        type="email" 
                        name="email" 
                        id="email" 
                        placeholder="Enter your email address"
                        value="<?= htmlspecialchars($customer['email'] ?? '') ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="address">
                        <i class="fas fa-map-marker-alt"></i> Address <span class="required">*</span>
                    </label>
                    <textarea 
                        name="address" 
                        id="address" 
                        placeholder="Enter your complete address"
                        required
                    ><?= htmlspecialchars($customer['address'] ?? '') ?></textarea>
                </div>

                <button type="submit" class="btn" id="submitBtn">
                    Continue to Next Step <i class="fas fa-arrow-right"></i>
                </button>
            </form>

        <?php elseif ($step === 'code'): ?>
            <!-- Step 2: Reward Code -->
            <form method="POST" action="" id="codeForm">
                <input type="hidden" name="step" value="code">
                
                <div class="info-text" style="margin-bottom: 20px;">
                    <strong>Your Information:</strong><br>
                    Name: <?= htmlspecialchars($customer['name']) ?><br>
                    Phone: <?= htmlspecialchars($customer['phone']) ?><br>
                    <?php if (!empty($customer['email'])): ?>
                        Email: <?= htmlspecialchars($customer['email']) ?><br>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="reward_code">
                        <i class="fas fa-ticket-alt"></i> Enter Reward Code <span class="required">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="reward_code" 
                        id="reward_code" 
                        placeholder="e.g., Liyas-SFA123Fcg"
                        autocomplete="off"
                        required
                        autofocus
                        style="text-transform: uppercase; letter-spacing: 1px;"
                        maxlength="50"
                    >
                </div>

                <button type="submit" class="btn" id="submitBtn">
                    <i class="fas fa-gift"></i> Redeem Code
                </button>
                <a href="?step=info" class="btn btn-secondary" style="text-decoration: none; display: block;">
                    <i class="fas fa-arrow-left"></i> Back to Edit Information
                </a>
            </form>

        <?php elseif ($step === 'success'): ?>
            <!-- Step 3: Success Message -->
            <div class="message success">
                <h3 style="margin-bottom: 10px; font-size: 24px;">✅ Reward Redeemed Successfully!</h3>
                <p style="font-size: 16px;">Thank you for choosing LIYAS International!</p>
            </div>

            <div class="instagram-box">
                <h3><i class="fab fa-instagram"></i> Follow Us on Instagram</h3>
                <p style="font-size: 15px; line-height: 1.8;">
                    Please follow our Instagram page for updates.<br>
                    <strong>Only followers are eligible for the rewards draw!</strong>
                </p>
                <a href="https://instagram.com/liyasinternational" target="_blank">
                    <i class="fab fa-instagram"></i> Follow @liyasinternational
                </a>
            </div>

            <div style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 10px; font-size: 13px; color: #666;">
                <p><strong>What's next?</strong></p>
                <p style="margin-top: 10px;">Stay tuned to our Instagram page for exclusive updates, promotions, and reward draws. Make sure you're following us to be eligible!</p>
            </div>
        <?php endif; ?>

        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e0e0e0; color: #999; font-size: 12px;">
            <p>One-time use only • Each code can be redeemed once</p>
        </div>
    </div>

    <script>
        // Auto-uppercase reward code input
        document.getElementById('reward_code')?.addEventListener('input', function(e) {
            e.target.value = e.target.value.toUpperCase();
        });

        // Handle form submission
        document.getElementById('infoForm')?.addEventListener('submit', function(e) {
            const btn = document.getElementById('submitBtn');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            }
        });

        document.getElementById('codeForm')?.addEventListener('submit', function(e) {
            const btn = document.getElementById('submitBtn');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            }
        });
    </script>
</body>
</html>

