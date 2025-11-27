<?php
/**
 * Reward Code Redeem Page
 * QR code redirects to: liyasinternational.com/redeem
 * Flow: Customer Info → Reward Code → Success Message
 *
 * 🎨 Color Palette:
 * - Background: #FFFFFF (page), soft card border #F3F4F6
 * - Primary: #6366F1 (indigo) → #8B5CF6 (gradient accent)
 * - Success: #10B981 (emerald)
 * - Text: #111827 (heading), #6B7280 (muted)
 * - Borders: #E5E7EB
 *
 * 🎞️ Animation Techniques:
 * - card-enter: fade + slight upward motion on load (all steps)
 * - card-success: smooth scale + fade-in when success screen is shown
 * - pop-in: "pop" effect on success icon
 * - sweep-glow: subtle animated gradient glow behind success content
 */

require_once __DIR__ . '/../config/config.php';

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
        // Validate phone number: allow any country code prefix (with or without +), then exactly 10 digits
        // Extract all digits first
        $phone_cleaned = preg_replace('/[^0-9]/', '', $phone);
        
        // Determine if there's a country code
        $has_country_code = false;
        $phone_digits = $phone_cleaned;
        
        // If starts with +, definitely has country code
        if (preg_match('/^\+/', $phone)) {
            // Remove country code (1-4 digits after +)
            $phone_normalized = preg_replace('/^\+\d{1,4}\s*/', '', $phone);
            $phone_digits = preg_replace('/[^0-9]/', '', $phone_normalized);
            $has_country_code = true;
        } elseif (strlen($phone_cleaned) > 10) {
            // More than 10 digits, assume first 1-4 are country code
            // Try to match country code pattern (1-4 digits) followed by 10 digits
            if (preg_match('/^(\d{1,4})(\d{10})$/', $phone_cleaned, $matches)) {
                $phone_digits = $matches[2]; // Use the 10-digit phone number part
                $has_country_code = true;
            } else {
                // Fallback: take last 10 digits as phone number
                $phone_digits = substr($phone_cleaned, -10);
                $has_country_code = true;
            }
        }
        // If exactly 10 digits, treat as phone number only (no country code)
        
        if (strlen($phone_digits) !== 10) {
            $message = 'Phone number must be exactly 10 digits (with or without country code)';
            $message_type = 'error';
            $step = 'info';
        } elseif (!empty($email) && (strpos($email, '@') === false || !filter_var($email, FILTER_VALIDATE_EMAIL))) {
            // Validate email: must contain @ symbol and be valid email format (if provided)
            $message = 'Please enter a valid email address with @ symbol';
            $message_type = 'error';
            $step = 'info';
        } else {
            // Store in session to use in next step (use cleaned phone - 10 digits only)
            $_SESSION['redeem_customer'] = [
                'name' => $name,
                'phone' => $phone_digits,
                'email' => $email,
                'address' => $address
            ];
            $step = 'code';
        }
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

// Message style map for Tailwind
$message_classes = [
    'success' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
    'error'   => 'bg-red-50 text-red-700 border border-red-200',
    'warning' => 'bg-amber-50 text-amber-700 border border-amber-200',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redeem Your Reward - LIYAS International</title>

    <!-- Favicon -->
    <link rel="icon" type="image/jpeg" href="../assets/images/logo/logo-bg.jpg">
    <link rel="shortcut icon" type="image/jpeg" href="../assets/images/logo/logo-bg.jpg">
    <link rel="apple-touch-icon" href="../assets/images/logo/logo-bg.jpg">
    <link rel="icon" type="image/jpeg" sizes="32x32" href="../assets/images/logo/logo-bg.jpg">
    <link rel="icon" type="image/jpeg" sizes="16x16" href="../assets/images/logo/logo-bg.jpg">

    <!-- Tailwind CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Google Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Open Sans', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            purple: '#6366F1',
                            deep: '#8B5CF6',
                        }
                    }
                }
            }
        }
    </script>

    <style>
        body {
            font-family: 'Open Sans', system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        /* Card entry animation (all states) */
        .card-enter {
            animation: cardEnter 0.45s ease-out;
        }

        @keyframes cardEnter {
            0% {
                opacity: 0;
                transform: translateY(14px) scale(0.98);
            }
            100% {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        /* Success state card animation */
        .card-success-anim {
            animation: cardSuccess 0.55s ease-out;
        }

        @keyframes cardSuccess {
            0% {
                opacity: 0;
                transform: scale(0.95);
            }
            60% {
                opacity: 1;
                transform: scale(1.02);
            }
            100% {
                transform: scale(1);
            }
        }

        /* Pop-in for success icon */
        .pop-in {
            animation: popIn 0.5s cubic-bezier(0.18, 0.89, 0.32, 1.28);
        }

        @keyframes popIn {
            0% {
                opacity: 0;
                transform: scale(0.4);
            }
            60% {
                opacity: 1;
                transform: scale(1.15);
            }
            100% {
                transform: scale(1);
            }
        }

        /* Animated glow behind success content */
        .sweep-glow {
            position: absolute;
            inset: -40px;
            background: radial-gradient(circle at 0% 0%, rgba(99, 102, 241, 0.15), transparent 55%),
                        radial-gradient(circle at 100% 100%, rgba(139, 92, 246, 0.15), transparent 55%);
            filter: blur(2px);
            opacity: 0.9;
            pointer-events: none;
            animation: sweepGlow 6s ease-in-out infinite alternate;
            z-index: -1;
        }

        @keyframes sweepGlow {
            0% {
                transform: translateX(-10px) translateY(4px);
            }
            50% {
                transform: translateX(10px) translateY(-4px);
            }
            100% {
                transform: translateX(-4px) translateY(0);
            }
        }
    </style>
</head>
<body class="min-h-screen bg-white flex items-center justify-center px-4 py-10">
    <div class="w-full max-w-xl">
        <?php
            $cardBaseClasses = 'relative bg-white border border-slate-100 shadow-[0_18px_45px_rgba(15,23,42,0.12)] rounded-3xl px-6 py-7 sm:px-10 sm:py-10 card-enter';
            $cardClasses = $cardBaseClasses . ($step === 'success' ? ' card-success-anim' : '');
        ?>
        <div class="<?= $cardClasses ?>">
            <!-- Extra subtle background decor (white theme safe) -->
            <div class="pointer-events-none absolute -top-8 -right-6 h-20 w-20 rounded-full bg-brand-purple/5 blur-2xl"></div>
            <div class="pointer-events-none absolute -bottom-10 -left-10 h-24 w-24 rounded-full bg-brand-deep/5 blur-3xl"></div>

            <!-- Logo + Heading -->
            <div class="relative flex flex-col items-center gap-3 mb-6">
                <div class="flex h-16 w-16 items-center justify-center rounded-full shadow-lg ring-4 ring-white/90 bg-white p-2">
                    <img src="../assets/images/logo/logo.png" alt="LIYAS Logo" class="w-full h-full object-contain">
                </div>
                <h1 class="text-2xl sm:text-3xl font-semibold text-slate-900 flex items-center gap-2">
                    <span class="text-lg"></span> Redeem Your Reward
                </h1>
                <p class="text-sm text-slate-500">
                    LIYAS International &mdash; Thank you for choosing us!
                </p>
            </div>

            <!-- Step Indicator -->
            <div class="relative mb-6">
                <div class="flex items-center justify-center gap-3">
                    <?php
                        $circleBase = 'flex h-9 w-9 items-center justify-center rounded-full text-sm font-semibold';
                    ?>
                    <div class="<?=
                        $circleBase . ' ' .
                        ($step === 'info'
                            ? 'bg-brand-purple text-white shadow-md'
                            : (($step === 'code' || $step === 'success')
                                ? 'bg-emerald-500 text-white shadow-md'
                                : 'bg-slate-200 text-slate-500'))
                    ?>">1</div>

                    <div class="h-0.5 w-8 bg-slate-200/80"></div>

                    <div class="<?=
                        $circleBase . ' ' .
                        ($step === 'code'
                            ? 'bg-brand-purple text-white shadow-md'
                            : ($step === 'success'
                                ? 'bg-emerald-500 text-white shadow-md'
                                : 'bg-slate-200 text-slate-500'))
                    ?>">2</div>

                    <div class="h-0.5 w-8 bg-slate-200/80"></div>

                    <div class="<?=
                        $circleBase . ' ' .
                        ($step === 'success'
                            ? 'bg-brand-purple text-white shadow-md'
                            : 'bg-slate-200 text-slate-500')
                    ?>">3</div>
                </div>
                <p class="mt-3 text-center text-xs uppercase tracking-[0.2em] text-slate-400">
                    Step <?= $step === 'info' ? '1: Your Details' : ($step === 'code' ? '2: Enter Code' : '3: Completed') ?>
                </p>
            </div>

            <!-- Message -->
            <?php if ($message): ?>
                <?php $cls = $message_classes[$message_type] ?? 'bg-slate-50 text-slate-700 border border-slate-200'; ?>
                <div class="mb-5 rounded-2xl px-4 py-3 text-sm flex items-start gap-2 <?= $cls ?>">
                    <?php if ($message_type === 'success'): ?>
                        <i class="fa-solid fa-circle-check mt-0.5"></i>
                    <?php elseif ($message_type === 'warning'): ?>
                        <i class="fa-solid fa-triangle-exclamation mt-0.5"></i>
                    <?php else: ?>
                        <i class="fa-solid fa-circle-info mt-0.5"></i>
                    <?php endif; ?>
                    <span><?= htmlspecialchars($message) ?></span>
                </div>
            <?php endif; ?>

            <!-- STEP 1: INFO -->
            <?php if ($step === 'info'): ?>
                <form method="POST" action="" id="infoForm" class="space-y-4">
                    <input type="hidden" name="step" value="info">

                    <!-- Name -->
                    <div class="space-y-1.5">
                        <label for="name" class="flex items-center gap-1.5 text-sm font-medium text-slate-800">
                            <i class="fas fa-user text-xs text-brand-purple"></i>
                            Full Name
                            <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            name="name"
                            id="name"
                            placeholder="Enter your full name"
                            required
                            autofocus
                            value="<?= htmlspecialchars($customer['name'] ?? '') ?>"
                            class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none ring-0 transition focus:border-brand-purple focus:ring-2 focus:ring-brand-purple/40"
                        />
                    </div>

                    <!-- Phone -->
                    <div class="space-y-1.5">
                        <label for="phone" class="flex items-center gap-1.5 text-sm font-medium text-slate-800">
                            <i class="fas fa-phone text-xs text-brand-purple"></i>
                            Phone Number
                            <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="tel"
                            name="phone"
                            id="phone"
                            required
                            maxlength="20"
                            value="<?= htmlspecialchars($customer['phone'] ?? '') ?>"
                            class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none ring-0 transition focus:border-brand-purple focus:ring-2 focus:ring-brand-purple/40"
                        />
                    </div>

                    <!-- Email -->
                    <div class="space-y-1.5">
                        <label for="email" class="flex items-center gap-1.5 text-sm font-medium text-slate-800">
                            <i class="fas fa-envelope text-xs text-brand-purple"></i>
                            Email Address
                            <span class="text-xs font-normal text-slate-400">(Optional)</span>
                        </label>
                        <input
                            type="email"
                            name="email"
                            id="email"
                            placeholder="Enter your email address"
                            value="<?= htmlspecialchars($customer['email'] ?? '') ?>"
                            class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none ring-0 transition focus:border-brand-purple focus:ring-2 focus:ring-brand-purple/40"
                        />
                    </div>

                    <!-- Address -->
                    <div class="space-y-1.5">
                        <label for="address" class="flex items-center gap-1.5 text-sm font-medium text-slate-800">
                            <i class="fas fa-map-marker-alt text-xs text-brand-purple"></i>
                            Address
                            <span class="text-red-500">*</span>
                        </label>
                        <textarea
                            name="address"
                            id="address"
                            placeholder="Enter your complete address"
                            required
                            class="w-full min-h-[90px] rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none ring-0 transition focus:border-brand-purple focus:ring-2 focus:ring-brand-purple/40"
                        ><?= htmlspecialchars($customer['address'] ?? '') ?></textarea>
                    </div>

                    <!-- Info note -->
                    <div class="rounded-2xl bg-slate-50 px-4 py-3 text-xs text-slate-500 flex gap-2">
                        <i class="fa-solid fa-shield-halved mt-0.5 text-slate-400"></i>
                        <p>
                            Your details will only be used to verify your reward and for contacting you regarding delivery or prize updates.
                        </p>
                    </div>

                    <!-- Submit -->
                    <button
                        type="submit"
                        class="mt-2 flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-brand-purple to-brand-deep px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-brand-purple/30 transition hover:translate-y-[1px] hover:shadow-xl active:translate-y-[2px]"
                        id="submitBtn"
                    >
                        Continue to Next Step
                        <i class="fas fa-arrow-right text-xs"></i>
                    </button>
                </form>

            <!-- STEP 2: CODE -->
            <?php elseif ($step === 'code'): ?>
                <form method="POST" action="" id="codeForm" class="space-y-5">
                    <input type="hidden" name="step" value="code">

                    <!-- Summary -->
                    <div class="rounded-2xl bg-slate-50 px-4 py-3 text-xs text-slate-600 space-y-1.5">
                        <p class="font-semibold text-slate-800 flex items-center gap-2">
                            <i class="fa-solid fa-user-check text-brand-purple"></i>
                            Your Information
                        </p>
                        <p><span class="font-medium">Name:</span> <?= htmlspecialchars($customer['name']) ?></p>
                        <p><span class="font-medium">Phone:</span> <?= htmlspecialchars($customer['phone']) ?></p>
                        <?php if (!empty($customer['email'])): ?>
                            <p><span class="font-medium">Email:</span> <?= htmlspecialchars($customer['email']) ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Reward code -->
                    <div class="space-y-1.5">
                        <label for="reward_code" class="flex items-center gap-1.5 text-sm font-medium text-slate-800">
                            <i class="fas fa-ticket-alt text-xs text-brand-purple"></i>
                            Enter Reward Code
                            <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            name="reward_code"
                            id="reward_code"
                            autocomplete="off"
                            required
                            autofocus
                            maxlength="50"
                            class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm tracking-[0.15em] uppercase text-slate-900 shadow-sm outline-none ring-0 transition focus:border-brand-purple focus:ring-2 focus:ring-brand-purple/40"
                        />
                        <p class="text-[11px] text-slate-400">
                            You can find this code printed on the sticker. Please enter it exactly as shown.
                        </p>
                    </div>

                    <!-- Buttons -->
                    <div class="space-y-2">
                        <button
                            type="submit"
                            class="flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-emerald-500 to-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-emerald-500/30 transition hover:translate-y-[1px] hover:shadow-xl active:translate-y-[2px]"
                            id="submitBtn"
                        >
                            <i class="fas fa-gift text-xs"></i>
                            Redeem Code
                        </button>

                        <a
                            href="?step=info"
                            class="flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 shadow-sm transition hover:bg-slate-50"
                        >
                            <i class="fas fa-arrow-left text-xs"></i>
                            Back to Edit Information
                        </a>
                    </div>
                </form>

            <!-- STEP 3: SUCCESS -->
            <?php elseif ($step === 'success'): ?>
                <div class="relative space-y-5">
                    <!-- Animated glow background -->
                    <div class="sweep-glow rounded-3xl"></div>

                    <div class="relative rounded-3xl bg-emerald-50 px-5 py-4 border border-emerald-100 text-center">
                        <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-emerald-500 text-white shadow-md pop-in">
                            <i class="fa-solid fa-check text-xl"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-emerald-800 mb-1">
                            Reward Redeemed Successfully!
                        </h3>
                        <p class="text-sm text-emerald-700">
                            Thank you for choosing LIYAS International. Your entry has been recorded.
                        </p>
                    </div>

                    <div class="relative rounded-3xl bg-gradient-to-br from-[#f09433] via-[#dc2743] to-[#bc1888] px-5 py-5 text-white shadow-lg overflow-hidden">
                        <div class="pointer-events-none absolute -right-6 -top-6 h-20 w-20 rounded-full bg-white/10 blur-xl"></div>
                        <h3 class="mb-2 flex items-center gap-2 text-lg font-semibold">
                            <i class="fab fa-instagram"></i>
                            Follow Us on Instagram
                        </h3>
                        <p class="mb-4 text-sm leading-relaxed">
                            Follow our Instagram page to stay updated with offers and reward draws.
                            <span class="font-semibold">Only followers are eligible for the rewards draw!</span>
                        </p>
                        <a
                            href="https://instagram.com/liyasinternational"
                            target="_blank"
                            class="inline-flex items-center gap-2 rounded-full bg-white px-5 py-2 text-sm font-semibold text-[#bc1888] shadow-md transition hover:translate-y-[1px] hover:shadow-lg"
                        >
                            <i class="fab fa-instagram"></i>
                            Follow @liyasinternational
                        </a>
                    </div>

                    <div class="relative rounded-2xl bg-slate-50 px-4 py-3 text-xs text-slate-600 space-y-1.5">
                        <p class="font-semibold text-slate-800">What's next?</p>
                        <p>
                            Keep an eye on our Instagram page for announcements about winners, exclusive updates,
                            and upcoming promotions. Make sure you're following us so you don't miss anything!
                        </p>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Footer note -->
            <div class="mt-7 border-t border-slate-200/70 pt-4">
                <p class="text-center text-[11px] text-slate-400">
                    One-time use only &bull; Each code can be redeemed once
                </p>
            </div>
        </div>
    </div>

    <script>
        // Phone number validation: allow any country code prefix (with or without +), then exactly 10 digits
        const phoneInput = document.getElementById('phone');
        if (phoneInput) {
            phoneInput.addEventListener('input', function(e) {
                let value = e.target.value;
                
                // Check if starts with + (country code with +)
                if (value.trim().startsWith('+')) {
                    // Extract country code part (+, followed by 1-4 digits)
                    const countryCodeMatch = value.match(/^(\+\d{1,4})/);
                    if (countryCodeMatch) {
                        const countryCode = countryCodeMatch[1];
                        // Extract everything after country code
                        let afterPrefix = value.substring(countryCode.length).replace(/[^0-9\s]/g, '');
                        // Remove spaces
                        afterPrefix = afterPrefix.replace(/\s/g, '');
                        // Limit to 10 digits after country code
                        if (afterPrefix.length > 10) {
                            afterPrefix = afterPrefix.substring(0, 10);
                        }
                        // Format: country code followed by 10 digits (with optional space)
                        value = countryCode + (afterPrefix.length > 0 ? ' ' + afterPrefix : '');
                    } else {
                        // Just + or + with invalid format, allow user to type
                        if (value.match(/^\+\d*$/)) {
                            // Valid format, allow it
                        } else {
                            // Remove invalid characters, keep + and digits
                            value = value.replace(/[^+\d]/g, '');
                            // Limit country code to max 4 digits after +
                            const match = value.match(/^(\+\d{0,4})/);
                            if (match) {
                                value = match[1];
                            }
                        }
                    }
                } else {
                    // No +, check if starts with country code (1-4 digits) or just phone number
                    const allDigits = value.replace(/[^0-9]/g, '');
                    
                    // Check if first 1-4 digits could be a country code
                    // If total digits > 10, assume first 1-4 are country code
                    if (allDigits.length > 10) {
                        // Has country code without +
                        // Extract country code (1-4 digits) and phone (10 digits)
                        const countryCodeMatch = allDigits.match(/^(\d{1,4})(\d{10})/);
                        if (countryCodeMatch) {
                            const countryCode = countryCodeMatch[1];
                            const phoneDigits = countryCodeMatch[2];
                            value = countryCode + ' ' + phoneDigits;
                        } else {
                            // More than 14 digits, limit to first 4 as country code + 10 as phone
                            const countryCode = allDigits.substring(0, 4);
                            const phoneDigits = allDigits.substring(4, 14);
                            value = countryCode + ' ' + phoneDigits;
                        }
                    } else if (allDigits.length === 10) {
                        // Exactly 10 digits, treat as phone number only
                        value = allDigits;
                    } else {
                        // Less than 10 digits, allow user to continue typing
                        value = allDigits;
                    }
                }
                
                e.target.value = value;
                
                // Validate: extract all digits and check for exactly 10 phone digits
                let digitsOnly = value.replace(/[^0-9]/g, '');
                
                // Determine if there's a country code
                if (value.trim().startsWith('+')) {
                    // Country code with +, remove it (1-4 digits after +)
                    const countryCodeDigits = value.match(/^\+(\d{1,4})/);
                    if (countryCodeDigits) {
                        const ccDigits = countryCodeDigits[1].length;
                        digitsOnly = digitsOnly.substring(ccDigits);
                    }
                } else if (digitsOnly.length > 10) {
                    // Country code without +, remove first 1-4 digits
                    // Try to match country code pattern
                    const match = digitsOnly.match(/^(\d{1,4})(\d{10})/);
                    if (match) {
                        digitsOnly = match[2]; // Phone number part
                    } else {
                        // Fallback: remove first 4 digits
                        digitsOnly = digitsOnly.substring(4);
                    }
                }
                
                const isValid = digitsOnly.length === 10;
                
                // Visual feedback
                if (isValid) {
                    e.target.classList.remove('border-red-300');
                    e.target.classList.add('border-emerald-300');
                } else {
                    e.target.classList.remove('border-emerald-300');
                    if (digitsOnly.length > 0) {
                        e.target.classList.add('border-red-300');
                    } else {
                        e.target.classList.remove('border-red-300');
                    }
                }
            });
        }

        // Email validation: must contain @ symbol if provided
        const emailInput = document.getElementById('email');
        if (emailInput) {
            emailInput.addEventListener('blur', function(e) {
                const value = e.target.value.trim();
                if (value && (value.indexOf('@') === -1 || !value.includes('@'))) {
                    e.target.classList.add('border-red-300');
                    e.target.classList.remove('border-emerald-300');
                } else if (value && value.includes('@')) {
                    e.target.classList.remove('border-red-300');
                    e.target.classList.add('border-emerald-300');
                } else {
                    e.target.classList.remove('border-red-300', 'border-emerald-300');
                }
            });
        }

        // Form validation before submission
        document.getElementById('infoForm')?.addEventListener('submit', function(e) {
            let phone = phoneInput?.value.trim() || '';
            let phoneDigits = phone.replace(/[^0-9]/g, '');
            
            // Determine if there's a country code
            if (phone.trim().startsWith('+')) {
                // Has +, remove country code (1-4 digits after +)
                const match = phone.match(/^\+\d{1,4}\s*(.+)$/);
                if (match) {
                    phoneDigits = match[1].replace(/[^0-9]/g, '');
                } else {
                    // Just +, extract all digits and remove first 1-4
                    if (phoneDigits.length > 10) {
                        phoneDigits = phoneDigits.substring(phoneDigits.length - 10);
                    }
                }
            } else if (phoneDigits.length > 10) {
                // More than 10 digits without +, assume first 1-4 are country code
                // Try to match: 1-4 digits (country code) + 10 digits (phone)
                const match = phoneDigits.match(/^(\d{1,4})(\d{10})$/);
                if (match) {
                    phoneDigits = match[2]; // Use the 10-digit phone number part
                } else {
                    // Fallback: take last 10 digits
                    phoneDigits = phoneDigits.substring(phoneDigits.length - 10);
                }
            }
            // If exactly 10 digits, use as is (no country code)
            
            const email = emailInput?.value.trim();
            
            // Validate phone number: must have exactly 10 digits
            if (phoneDigits.length !== 10) {
                e.preventDefault();
                alert('Phone number must be exactly 10 digits (with or without country code)');
                phoneInput?.focus();
                return false;
            }
            
            // Validate email if provided
            if (email && (email.indexOf('@') === -1 || !email.includes('@') || !email.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/))) {
                e.preventDefault();
                alert('Please enter a valid email address with @ symbol');
                emailInput?.focus();
                return false;
            }
            
            // If validation passes, show loading state
            const btn = document.getElementById('submitBtn');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin text-xs"></i> Processing...';
            }
        });

        // Auto-uppercase reward code input
        document.getElementById('reward_code')?.addEventListener('input', function(e) {
            e.target.value = e.target.value.toUpperCase();
        });

        // Handle code form submission loading state
        document.getElementById('codeForm')?.addEventListener('submit', function() {
            const btn = document.getElementById('submitBtn');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin text-xs"></i> Processing...';
            }
        });
    </script>
</body>
</html>

