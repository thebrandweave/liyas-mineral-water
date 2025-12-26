<?php
require_once __DIR__ . '/../config/config.php';

$db = getCampaignDB();
$today = date('Y-m-d');

$stmt = $db->prepare("
    SELECT c.*, ca.file_path, ca.file_type
    FROM campaigns c
    LEFT JOIN campaign_assets ca ON ca.campaign_id = c.id
    WHERE c.status='active'
      AND c.start_date <= ?
      AND (c.end_date IS NULL OR c.end_date >= ?)
    LIMIT 1
");
$stmt->execute([$today, $today]);
$campaign = $stmt->fetch(PDO::FETCH_ASSOC);
if(!$campaign) die("No active campaign");

$q = $db->prepare("SELECT * FROM campaign_questions WHERE campaign_id=? ORDER BY sort_order");
$q->execute([$campaign['id']]);
$questions = $q->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($campaign['title']) ?> | LIYAS Premium</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <style>
        :root {
            --primary: #4ad2e2;
            --primary-dark: #1e99a8;
            --text-main: #1e293b;
            --text-light: #94a3b8;
            --bg-soft: #f8fafc;
        }

        * { margin:0; padding:0; box-sizing:border-box; -webkit-font-smoothing: antialiased; }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: var(--bg-soft);
            color: var(--text-main);
            overflow-x: hidden;
        }

        /* --- LOGO --- */
        .logo {
            position: fixed;
            top: 25px;
            left: 30px;
            z-index: 1000;
        }
        .logo img { height: 45px; }

        /* --- HERO SECTION --- */
        .hero {
            position: sticky;
            top: 0;
            height: 85vh; /* Reduced height */
            background: radial-gradient(circle at center, #5ee7f6 0%, #22b8cf 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            overflow: hidden;
            z-index: 1;
            padding-top: 60px;
        }

        .bubbles { position: absolute; width: 100%; height: 100%; top: 0; left: 0; z-index: 0; pointer-events: none; }
        .bubble {
            position: absolute;
            bottom: -100px;
            background: rgba(255,255,255,0.25);
            border-radius: 50%;
            animation: rise 12s infinite linear;
        }

        @keyframes rise {
            0% { bottom: -100px; transform: translateX(0); opacity: 0; }
            50% { opacity: 0.4; }
            100% { bottom: 100vh; transform: translateX(50px); opacity: 0; }
        }

        .campaign-header h1 {
            font-size: clamp(32px, 5vw, 64px); /* Smaller header */
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: -1px;
            color: white;
            z-index: 2;
            position: relative;
        }

        .hero-main-content {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 30px;
            max-width: 1000px;
            width: 100%;
            padding: 0 30px;
            z-index: 2;
            flex-grow: 1;
        }

        .hero-description { flex: 1; color: white; }
        .hero-description h2 { font-size: 1.6rem; margin-bottom: 10px; font-weight: 600; }
        .hero-description p { font-size: 1rem; line-height: 1.5; opacity: 0.9; font-weight: 300; }

        .bottle-small {
            height: 35vh; /* Smaller bottle */
            filter: drop-shadow(0 20px 40px rgba(0,0,0,0.15));
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }

        /* --- REFINED FORM SECTION --- */
        .wave-container {
            position: relative;
            z-index: 10;
            background: #ffffff;
            border-radius: 50px 50px 0 0;
            box-shadow: 0 -20px 40px rgba(0,0,0,0.05);
            padding: 60px 20px;
            margin-top: -8vh;
            min-height: 60vh;
        }

        .form-box {
            width: 100%;
            max-width: 550px; /* Narrower, more elegant box */
            margin: 0 auto;
        }

        .progress-container {
            width: 100%;
            height: 4px;
            background: #f1f5f9;
            border-radius: 10px;
            margin-bottom: 40px;
        }
        .progress-bar {
            height: 100%;
            width: 0%;
            background: var(--primary);
            transition: 0.6s cubic-bezier(0.2, 0.8, 0.2, 1);
        }

        .step-card {
            display: none;
            animation: fadeIn 0.4s ease-out;
        }
        .step-card.active { display: block; }

        @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }

        .step-tag {
            color: var(--primary);
            font-weight: 700;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-bottom: 8px;
            display: block;
        }

        .step-card h2 {
            font-size: 22px; /* Balanced size */
            font-weight: 600;
            margin-bottom: 30px;
            color: var(--text-main);
        }

        .field input:not([type="file"]) {
            width: 100%;
            padding: 12px 0;
            border: none;
            border-bottom: 2px solid #e2e8f0;
            font-size: 18px;
            font-weight: 400;
            background: transparent;
            transition: 0.3s;
            color: var(--text-main);
        }

        .field input:focus { outline: none; border-color: var(--primary); }

        .file-wrapper {
            border: 2px dashed #cbd5e1;
            padding: 30px;
            border-radius: 12px;
            text-align: center;
            background: #f8fafc;
        }

        /* --- NAVIGATION BUTTONS --- */
        .nav-controls {
            display: flex;
            gap: 15px;
            margin-top: 40px;
        }

        button {
            padding: 14px 28px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 15px;
            cursor: pointer;
            transition: 0.2s;
            border: none;
        }

        .btn-next, .btn-submit {
            background: var(--primary);
            color: white;
            flex: 2;
        }

        .btn-back {
            background: transparent;
            color: var(--text-light);
            flex: 1;
            text-decoration: underline;
        }

        button:hover { opacity: 0.85; transform: translateY(-1px); }

        @media(max-width: 768px) {
            .hero-main-content { flex-direction: column; text-align: center; padding-top: 20px; }
            .hero { height: auto; padding-bottom: 100px; }
            .logo { left: 20px; top: 20px; }
            .logo img { height: 35px; }
            .wave-container { margin-top: -5vh; }
        }
    </style>
</head>
<body>

<div class="logo">
    <img src="../assets/images/logo/logo.png" alt="LIYAS Logo">
</div>

<section class="hero" id="hero-section">
    <div class="bubbles" id="bubbles"></div>
    <div class="campaign-header" data-aos="fade-down">
        <h1><?= htmlspecialchars($campaign['title']) ?></h1>
    </div>

    <div class="hero-main-content">
        <div class="hero-description" data-aos="fade-up">
            <h2>Luxury in Every Drop</h2>
            <p><?= nl2br(htmlspecialchars($campaign['description'] ?? 'Join the elite. Refresh your lifestyle.')) ?></p>
        </div>
        <div class="hero-image-wrap" data-aos="zoom-in">
            <?php if($campaign['file_type']==='image'): ?>
                <img src="../<?= htmlspecialchars($campaign['file_path']) ?>" class="bottle-small" alt="Product">
            <?php endif; ?>
        </div>
    </div>
</section>

<div class="wave-container">
    <div class="form-box">
        <div class="progress-container">
            <div class="progress-bar" id="progressBar"></div>
        </div>

        <form id="campaignForm" method="POST" action="submit.php" enctype="multipart/form-data">
            <input type="hidden" name="campaign_id" value="<?= $campaign['id'] ?>">

            <div class="step-card active">
                <span class="step-tag">Step 01</span>
                <h2>What is your full name?</h2>
                <div class="field">
                    <input name="full_name" placeholder="Your name" required autofocus>
                </div>
            </div>

            <div class="step-card">
                <span class="step-tag">Step 02</span>
                <h2>How can we contact you?</h2>
                <div class="field">
                    <input type="email" name="email" placeholder="Email Address" required style="margin-bottom:20px">
                    <input name="phone_number" placeholder="Phone Number" required>
                </div>
            </div>

            <?php foreach($questions as $index => $q): ?>
            <div class="step-card">
                <span class="step-tag">Step 0<?= $index + 3 ?></span>
                <h2><?= htmlspecialchars($q['question_label']) ?></h2>
                <div class="field">
                    <?php if(in_array($q['field_type'],['text','number'])): ?>
                        <input name="answers[<?= $q['id'] ?>]" placeholder="Your answer..." <?= $q['is_required']?'required':'' ?>>
                    <?php else: ?>
                        <div class="file-wrapper">
                            <input type="file" name="media[<?= $q['id'] ?>]" <?= $q['is_required']?'required':'' ?>>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>

            <div class="nav-controls">
                <button type="button" class="btn-back" id="prevBtn" onclick="changeStep(-1)">Back</button>
                <button type="button" class="btn-next" id="nextBtn" onclick="changeStep(1)">Continue</button>
                <button type="submit" class="btn-submit" id="submitBtn" style="display:none;">Submit Entry</button>
            </div>
        </form>
    </div>
</div>

<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    AOS.init({ once: true, duration: 800 });

    const container = document.getElementById('bubbles');
    for (let i = 0; i < 12; i++) {
        const b = document.createElement('div');
        b.className = 'bubble';
        const size = Math.random() * 20 + 10 + 'px';
        b.style.width = size; b.style.height = size;
        b.style.left = Math.random() * 100 + '%';
        b.style.animationDelay = Math.random() * 5 + 's';
        container.appendChild(b);
    }

    let currentStep = 0;
    const steps = document.querySelectorAll(".step-card");
    const progressBar = document.getElementById("progressBar");

    function updateUI() {
        steps.forEach((step, i) => step.classList.toggle("active", i === currentStep));
        document.getElementById("prevBtn").style.visibility = (currentStep === 0) ? "hidden" : "visible";
        
        if (currentStep === steps.length - 1) {
            document.getElementById("nextBtn").style.display = "none";
            document.getElementById("submitBtn").style.display = "block";
        } else {
            document.getElementById("nextBtn").style.display = "block";
            document.getElementById("submitBtn").style.display = "none";
        }

        const pct = ((currentStep + 1) / steps.length) * 100;
        progressBar.style.width = pct + "%";
    }

    function changeStep(n) {
        if (n === 1) {
            const inputs = steps[currentStep].querySelectorAll("input");
            let valid = true;
            inputs.forEach(i => { if(!i.checkValidity()) { i.reportValidity(); valid = false; } });
            if (!valid) return;
        }
        currentStep += n;
        updateUI();
    }

    updateUI();
</script>

</body>
</html>