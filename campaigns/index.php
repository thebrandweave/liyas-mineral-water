<?php
require_once __DIR__ . '/../config/config.php';

$db = getCampaignDB();
$today = date('Y-m-d');

/* Ongoing campaigns */
$stmtOngoing = $db->prepare("
    SELECT c.*, ca.file_path
    FROM campaigns c
    LEFT JOIN campaign_assets ca ON ca.campaign_id = c.id
    WHERE c.status = 'active'
      AND c.start_date <= ?
      AND (c.end_date IS NULL OR c.end_date >= ?)
    ORDER BY c.start_date ASC
");
$stmtOngoing->execute([$today, $today]);
$ongoing = $stmtOngoing->fetchAll(PDO::FETCH_ASSOC);

/* Upcoming campaigns */
$stmtUpcoming = $db->prepare("
    SELECT c.*, ca.file_path
    FROM campaigns c
    LEFT JOIN campaign_assets ca ON ca.campaign_id = c.id
    WHERE c.status = 'active'
      AND c.start_date > ?
    ORDER BY c.start_date ASC
");
$stmtUpcoming->execute([$today]);
$upcoming = $stmtUpcoming->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>LIYAS Campaigns</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link rel="icon" href="../assets/images/logo/logo-bg.jpg">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

<style>
:root{
    --liyas-blue:#4ad2e2;
    --liyas-blue-dark:#22b8cf;
    --bg:#f4fcfe;
    --card:#ffffff;
    --text:#0f172a;
    --muted:#64748b;
}

*{box-sizing:border-box}
body{
    margin:0;
    font-family:'Poppins',sans-serif;
    background:var(--bg);
    color:var(--text);
}

/* Top logo only */
.top-bar{
    padding:16px 24px;
}
.top-bar img{
    height:48px;
}

/* Page header */
.page-header{
    text-align:center;
    padding:30px 16px 10px;
}
.page-header h1{
    margin:0;
    color:var(--liyas-blue-dark);
    font-size:28px;
    font-weight:600;
}
.page-header p{
    margin-top:6px;
    font-size:14px;
    color:var(--muted);
}

/* Container */
.container{
    max-width:1100px;
    margin:auto;
    padding:24px 16px 60px;
}

.section-title{
    font-size:18px;
    font-weight:600;
    margin:32px 0 16px;
    display:flex;
    align-items:center;
    gap:8px;
}

/* Grid */
.grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(260px,1fr));
    gap:20px;
}

/* Card */
.card{
    background:var(--card);
    border-radius:14px;
    box-shadow:0 10px 25px rgba(0,0,0,.05);
    overflow:hidden;
    display:flex;
    flex-direction:column;
}

.card img{
    width:100%;
    height:180px;
    object-fit:cover;
    background:#e6f8fb;
}

.card-body{
    padding:16px;
    flex:1;
}

.badge{
    display:inline-block;
    font-size:11px;
    font-weight:600;
    padding:4px 10px;
    border-radius:999px;
    background:#e0fbff;
    color:var(--liyas-blue-dark);
    margin-bottom:8px;
}

.card-title{
    font-size:16px;
    font-weight:600;
    margin-bottom:4px;
}

.card-meta{
    font-size:13px;
    color:var(--muted);
}

.card-footer{
    padding:16px;
}

.btn{
    width:100%;
    display:block;
    text-align:center;
    padding:11px;
    border-radius:10px;
    background:var(--liyas-blue);
    color:#fff;
    text-decoration:none;
    font-weight:500;
}

.btn.disabled{
    background:#bfeff6;
    pointer-events:none;
}
</style>
</head>

<body>

<!-- LOGO ONLY -->
<div class="top-bar">
    <img src="../assets/images/logo/logo.png" alt="LIYAS">
</div>

<!-- HEADER -->
<div class="page-header">
    <h1>LIYAS Campaigns</h1>
    <p>Scan • Participate • Win</p>
</div>

<div class="container">

<!-- ONGOING -->
<div class="section-title">🟢 Ongoing Campaigns</div>

<?php if(empty($ongoing)): ?>
    <p style="color:var(--muted)">No active campaigns right now.</p>
<?php else: ?>
<div class="grid">
<?php foreach($ongoing as $c): ?>
    <div class="card">
        <?php if(!empty($c['file_path'])): ?>
            <img src="../<?= htmlspecialchars($c['file_path']) ?>" alt="">
        <?php else: ?>
            <img src="../assets/images/placeholder.jpg" alt="">
        <?php endif; ?>

        <div class="card-body">
            <span class="badge">LIVE</span>
            <div class="card-title"><?= htmlspecialchars($c['title']) ?></div>
            <div class="card-meta">
                Ends: <?= $c['end_date'] ? date('d M Y',strtotime($c['end_date'])) : 'Ongoing' ?>
            </div>
        </div>
        <div class="card-footer">
            <a class="btn" href="view.php?slug=<?= urlencode($c['slug']) ?>">Join Campaign</a>
        </div>
    </div>
<?php endforeach; ?>
</div>
<?php endif; ?>

<!-- UPCOMING -->
<div class="section-title">🔵 Upcoming Campaigns</div>

<?php if(empty($upcoming)): ?>
    <p style="color:var(--muted)">No upcoming campaigns announced.</p>
<?php else: ?>
<div class="grid">
<?php foreach($upcoming as $c): ?>
    <div class="card">
        <?php if(!empty($c['file_path'])): ?>
            <img src="../<?= htmlspecialchars($c['file_path']) ?>" alt="">
        <?php else: ?>
            <img src="../assets/images/placeholder.jpg" alt="">
        <?php endif; ?>

        <div class="card-body">
            <span class="badge">COMING SOON</span>
            <div class="card-title"><?= htmlspecialchars($c['title']) ?></div>
            <div class="card-meta">
                Starts: <?= date('d M Y',strtotime($c['start_date'])) ?>
            </div>
        </div>
        <div class="card-footer">
            <span class="btn disabled">Coming Soon</span>
        </div>
    </div>
<?php endforeach; ?>
</div>
<?php endif; ?>

</div>

</body>
</html>
