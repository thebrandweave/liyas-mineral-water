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
    
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <style>
        :root {
            --brand-cyan: #4ad2e2;
            --brand-dark: #0f172a;
        }

        * { margin:0; padding:0; box-sizing:border-box; -webkit-font-smoothing: antialiased; }
        
        body {
            font-family: 'Outfit', sans-serif;
            background: #ffffff;
            color: var(--brand-dark);
            overflow: hidden; 
        }

        .bg-blobs {
            position: fixed;
            width: 100vw; height: 100vh;
            z-index: -1; overflow: hidden;
            background: #f8fafc;
        }
        .blob {
            position: absolute;
            background: var(--brand-cyan);
            filter: blur(80px);
            border-radius: 50%;
            opacity: 0.15;
            animation: move 20s infinite alternate;
        }
        @keyframes move { from { transform: translate(0,0); } to { transform: translate(100px, 100px); } }

        .page-container {
            display: flex;
            height: 100vh;
            width: 100%;
        }

        /* --- REFINED BRAND PANEL --- */
        .brand-panel {
            flex: 0 0 45%; /* Fixed width for better balance */
            background: linear-gradient(160deg, #0ea5e9 0%, #22d3ee 100%);
            display: flex;
            flex-direction: column;
            padding: 80px;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .logo-fixed {
            height: 40px;
            margin-bottom: 60px;
            align-self: flex-start;
        }

        .brand-content {
            display: flex;
            flex-direction: column;
            gap: 30px;
            z-index: 2;
        }

        .brand-panel h1 {
            font-size: 4rem;
            font-weight: 800;
            line-height: 1;
            text-transform: uppercase;
            letter-spacing: -1px;
        }

        /* Smaller, controlled product image */
        .brand-product-img {
            width: 180px; /* Smaller size */
            height: auto;
            filter: drop-shadow(0 20px 40px rgba(0,0,0,0.15));
            animation: float 6s ease-in-out infinite;
            margin: 10px 0;
        }

        .brand-panel p {
            font-size: 1.2rem;
            opacity: 0.9;
            max-width: 400px;
            font-weight: 300;
            line-height: 1.6;
        }

        @keyframes float { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-15px); } }

        /* --- FORM PANEL (Full Coverage) --- */
        .form-panel {
            flex: 1;
            display: flex;
            align-items: center;
            padding: 60px 10%;
            z-index: 10;
        }

        .form-card {
            width: 100%;
            max-width: 600px;
        }

        .step-indicator {
            font-size: 14px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 4px;
            color: var(--brand-cyan);
            margin-bottom: 20px;
            display: block;
        }

        .question-text {
            font-size: 42px;
            font-weight: 600;
            margin-bottom: 50px;
            color: var(--brand-dark);
            line-height: 1.1;
        }

        .input-group {
            position: relative;
            margin-bottom: 60px;
        }

        .input-group input:not([type="file"]) {
            width: 100%;
            border: none;
            border-bottom: 3px solid #e2e8f0;
            padding: 15px 0;
            font-size: 32px;
            background: transparent;
            outline: none;
            transition: 0.4s;
            color: var(--brand-dark);
        }

        .input-group input:focus {
            border-color: var(--brand-cyan);
        }

        .action-area {
            display: flex;
            gap: 24px;
            align-items: center;
        }

        .btn-primary {
            background: var(--brand-dark);
            color: white;
            border: none;
            padding: 22px 45px;
            border-radius: 15px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
        }

        .btn-primary:hover {
            background: var(--brand-cyan);
            transform: translateY(-3px);
        }

        .btn-back {
            background: none;
            border: none;
            color: #94a3b8;
            font-weight: 600;
            cursor: pointer;
        }

        @media (max-width: 1024px) {
            .page-container { flex-direction: column; overflow-y: auto; }
            .brand-panel { flex: none; padding: 40px; height: auto; }
            .brand-panel h1 { font-size: 2.5rem; }
            .brand-product-img { width: 120px; }
            .form-panel { padding: 60px 40px; }
        }
    </style>
</head>
<body>

<div class="bg-blobs">
    <div class="blob" style="width: 500px; height: 500px; top: -100px; right: -100px;"></div>
    <div class="blob" style="width: 400px; height: 400px; bottom: -50px; left: -50px;"></div>
</div>

<div class="page-container">
    <div class="brand-panel">
        <img src="../assets/images/logo/logo.png" alt="LIYAS" class="logo-fixed">
        
        <div class="brand-content" data-aos="fade-up">
            <h1><?= htmlspecialchars($campaign['title']) ?></h1>
            
            <?php if($campaign['file_type']==='image'): ?>
                <img src="../<?= htmlspecialchars($campaign['file_path']) ?>" class="brand-product-img" alt="Product">
            <?php endif; ?>

            <p><?= nl2br(htmlspecialchars($campaign['description'] ?? 'Purity defined. Excellence delivered.')) ?></p>
        </div>
    </div>

    <div class="form-panel">
        <div class="form-card" data-aos="fade-left">
            <form id="campaignForm" method="POST" action="submit.php" enctype="multipart/form-data">
                <input type="hidden" name="campaign_id" value="<?= $campaign['id'] ?>">

                <div class="step-card active">
                    <span class="step-indicator">Step 01</span>
                    <h2 class="question-text">May we have your <br><span style="color:var(--brand-cyan)">full name?</span></h2>
                    <div class="input-group">
                        <input name="full_name" placeholder="John Doe" required autofocus>
                    </div>
                </div>

                <div class="step-card" style="display:none">
                    <span class="step-indicator">Step 02</span>
                    <h2 class="question-text">How can we <br><span style="color:var(--brand-cyan)">contact you?</span></h2>
                    <div class="input-group">
                        <input type="email" name="email" placeholder="Email Address" required style="margin-bottom:30px">
                        <input name="phone_number" placeholder="Phone Number" required>
                    </div>
                </div>

                <?php foreach($questions as $index => $q): ?>
                <div class="step-card" style="display:none">
                    <span class="step-indicator">Step 0<?= $index + 3 ?></span>
                    <h2 class="question-text"><?= htmlspecialchars($q['question_label']) ?></h2>
                    <div class="input-group">
                        <?php if(in_array($q['field_type'],['text','number'])): ?>
                            <input name="answers[<?= $q['id'] ?>]" placeholder="Type here..." <?= $q['is_required']?'required':'' ?>>
                        <?php else: ?>
                            <input type="file" name="media[<?= $q['id'] ?>]" <?= $q['is_required']?'required':'' ?>>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>

                <div class="action-area">
                    <button type="button" class="btn-back" id="prevBtn" onclick="changeStep(-1)" style="visibility:hidden">Back</button>
                    <button type="button" class="btn-primary" id="nextBtn" onclick="changeStep(1)">Continue</button>
                    <button type="submit" class="btn-primary" id="submitBtn" style="display:none;">Submit Entry</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    AOS.init({ duration: 800, once: true });

    let currentStep = 0;
    const steps = document.querySelectorAll(".step-card");

    function updateUI() {
        steps.forEach((step, i) => {
            step.style.display = (i === currentStep) ? "block" : "none";
        });
        document.getElementById("prevBtn").style.visibility = (currentStep === 0) ? "hidden" : "visible";
        
        if (currentStep === steps.length - 1) {
            document.getElementById("nextBtn").style.display = "none";
            document.getElementById("submitBtn").style.display = "block";
        } else {
            document.getElementById("nextBtn").style.display = "block";
            document.getElementById("submitBtn").style.display = "none";
        }
    }

    function changeStep(n) {
        if (n === 1) {
            const currentInputs = steps[currentStep].querySelectorAll("input");
            let valid = true;
            currentInputs.forEach(i => { if(!i.checkValidity()) { i.reportValidity(); valid = false; } });
            if (!valid) return;
        }
        currentStep += n;
        updateUI();
    }
</script>

</body>
</html>