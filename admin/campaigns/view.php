<?php
require_once '../../config/config.php';
require_once '../includes/auth_check.php';

$db = getCampaignDB();
$id = $_GET['id'] ?? null;
if (!$id) { header("Location: index.php"); exit(); }

$stmt = $db->prepare("SELECT * FROM campaigns WHERE id = ?");
$stmt->execute([$id]);
$campaign = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$campaign) die("Campaign not found.");

$stmtQ = $db->prepare("SELECT * FROM campaign_questions WHERE campaign_id = ? ORDER BY sort_order");
$stmtQ->execute([$id]);
$questions = $stmtQ->fetchAll(PDO::FETCH_ASSOC);

$stmtA = $db->prepare("SELECT * FROM campaign_assets WHERE campaign_id = ? LIMIT 1");
$stmtA->execute([$id]);
$asset = $stmtA->fetch(PDO::FETCH_ASSOC);

$current_page = "campaigns";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($campaign['title']) ?> – Campaign</title>

<link rel="stylesheet" href="../assets/css/prody-admin.css">
<link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

<style>
/* Page width */
.page {
    max-width: 1280px;
    margin: 0 auto;
}

/* Header */
.hero {
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:3rem 3.5rem;
    background:linear-gradient(135deg,#f8fafc,#ffffff);
    border-radius:28px;
    border:1px solid #e5e7eb;
    margin-bottom:3rem;
}
.hero h1 {
    font-size:32px;
    margin:8px 0 0;
}
.meta {
    font-size:14px;
    color:#64748b;
}
.status {
    background:#dcfce7;
    color:#166534;
    padding:6px 14px;
    border-radius:999px;
    font-weight:600;
    margin-left:10px;
}

/* Grid */
.main-grid {
    display:grid;
    grid-template-columns: 380px 1fr;
    gap:2.5rem;
    margin-bottom:3rem;
}

/* Left panel */
.side-card {
    background:#fff;
    border:1px solid #e5e7eb;
    border-radius:24px;
    padding:2rem;
}
.side-title {
    font-size:13px;
    text-transform:uppercase;
    letter-spacing:.05em;
    color:#64748b;
    margin-bottom:.5rem;
}
.slug {
    background:#f8fafc;
    padding:14px;
    border-radius:14px;
    font-family:monospace;
    color:#2563eb;
    margin-top:10px;
}

/* Asset */
.asset-stage {
    background:#f8fafc;
    border-radius:32px;
    padding:3rem;
    display:flex;
    justify-content:center;
    align-items:center;
    min-height:520px;
    box-shadow: inset 0 0 0 1px #e5e7eb;
}
.asset-stage img {
    max-height:440px;
    max-width:100%;
    object-fit:contain;
    border-radius:20px;
    box-shadow:0 40px 80px rgba(0,0,0,.15);
}

/* Questions */
.section {
    background:#fff;
    border:1px solid #e5e7eb;
    border-radius:28px;
    padding:2.5rem;
}
.q-row {
    padding:16px 18px;
    border-radius:14px;
    border:1px solid #f1f5f9;
    margin-bottom:12px;
    display:flex;
    justify-content:space-between;
    background:#fcfcfd;
}
.btn {
    background:var(--blue);
    color:#fff;
    padding:12px 22px;
    border-radius:14px;
    text-decoration:none;
    font-weight:500;
}
</style>
</head>

<body>
<div class="container">
<?php include '../includes/sidebar.php'; ?>

<div class="main-content">
<div class="page">

<!-- HERO -->
<div class="hero">
    <div>
        <div class="meta">
            Created on <?= date('d M Y', strtotime($campaign['start_date'])) ?>
            <span class="status"><?= ucfirst($campaign['status']) ?></span>
        </div>
        <h1><?= htmlspecialchars($campaign['title']) ?></h1>
    </div>
    <a href="edit.php?id=<?= $id ?>" class="btn">
        <i class='bx bx-edit'></i> Edit Configuration
    </a>
</div>

<!-- MAIN GRID -->
<div class="main-grid">

    <!-- LEFT -->
    <div class="side-card">
        <div class="side-title">Campaign Link</div>
        <div class="slug">domain.com/<?= htmlspecialchars($campaign['slug']) ?></div>

        <div class="side-title" style="margin-top:2rem;">Status</div>
        <strong><?= ucfirst($campaign['status']) ?></strong>
    </div>

    <!-- RIGHT -->
    <div class="asset-stage">
        <?php if ($asset && $asset['file_type'] === 'image'): ?>
            <img src="../../<?= $asset['file_path'] ?>" alt="Campaign Asset">
        <?php elseif ($asset): ?>
            <a href="../../<?= $asset['file_path'] ?>" target="_blank">View PDF</a>
        <?php else: ?>
            <div style="color:#64748b;">No asset uploaded</div>
        <?php endif; ?>
    </div>

</div>

<!-- QUESTIONS -->
<div class="section">
    <h3 style="margin-bottom:1.5rem;">Form Fields Configuration</h3>
    <?php if (!$questions): ?>
        <div style="color:#64748b;text-align:center;">
            No custom questions configured for this campaign.
        </div>
    <?php else: foreach($questions as $q): ?>
        <div class="q-row">
            <div>
                <strong><?= htmlspecialchars($q['question_label']) ?></strong>
                <div style="font-size:12px;color:#64748b;">
                    <?= ucfirst($q['field_type']) ?>
                </div>
            </div>
            <span><?= $q['is_required'] ? 'Required' : 'Optional' ?></span>
        </div>
    <?php endforeach; endif; ?>
</div>

</div>
</div>
</div>
</body>
</html>
