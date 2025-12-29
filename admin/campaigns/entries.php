<?php
require_once '../../config/config.php';
require_once '../includes/auth_check.php';

$current_page = "entries";
$db = getCampaignDB();

/* Fetch entries */
$stmt = $db->query("
    SELECT 
        s.id,
        s.full_name,
        s.email,
        s.phone_number,
        s.submitted_at,
        c.title AS campaign_title
    FROM submissions s
    JOIN campaigns c ON c.id = s.campaign_id
    ORDER BY s.submitted_at DESC
");
$entries = $stmt->fetchAll(PDO::FETCH_ASSOC);
$totalEntries = count($entries);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Entries / Leads</title>

<link rel="stylesheet" href="../assets/css/prody-admin.css">
<link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

<style>
/* ===== TOP CONTEXT BAR ===== */
.top-context{
    background:linear-gradient(135deg,#f8fafc,#ffffff);
    border:1px solid #e5e7eb;
    border-radius:18px;
    padding:18px 24px;
    display:flex;
    align-items:center;
    justify-content:space-between;
    margin-bottom:22px;
    box-shadow:0 8px 22px rgba(15,23,42,.05);
}
.context-left{
    display:flex;
    align-items:center;
    gap:16px;
}
.context-icon{
    width:46px;
    height:46px;
    border-radius:14px;
    background:#e0f2fe;
    color:#0369a1;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:22px;
}
.context-left h1{
    font-size:20px;
    font-weight:700;
    margin:0;
    color:#0f172a;
}
.context-left p{
    font-size:13px;
    margin-top:3px;
    color:#64748b;
}
.context-right{
    display:flex;
    align-items:center;
    gap:14px;
}
.context-stat{
    background:#f8fafc;
    padding:10px 16px;
    border-radius:12px;
    text-align:center;
    min-width:110px;
}
.context-stat span{
    font-size:12px;
    color:#64748b;
}
.context-stat strong{
    font-size:20px;
    color:#0f172a;
}
.context-btn{
    background:#0ea5e9;
    border:none;
    color:#fff;
    padding:10px 16px;
    border-radius:12px;
    font-size:13px;
    font-weight:600;
    opacity:.6;
    cursor:not-allowed;
}

/* ===== TABLE CARD ===== */
.table-card{
    background:#ffffff;
    border-radius:18px;
    box-shadow:0 10px 30px rgba(15,23,42,.06);
    border:1px solid #e5e7eb;
    overflow:hidden;
}
table{
    width:100%;
    border-collapse:collapse;
}
thead{
    background:#f8fafc;
}
th{
    padding:14px 16px;
    font-size:12px;
    text-transform:uppercase;
    letter-spacing:.05em;
    color:#64748b;
    font-weight:600;
    border-bottom:1px solid #e5e7eb;
}
td{
    padding:16px;
    font-size:14px;
    color:#0f172a;
    border-bottom:1px solid #f1f5f9;
}
tbody tr:hover{
    background:#f8fafc;
}

/* BADGE */
.campaign-badge{
    padding:4px 10px;
    background:#e0f2fe;
    color:#0369a1;
    font-size:12px;
    font-weight:600;
    border-radius:999px;
}

/* BUTTON */
.view-btn{
    background:#0ea5e9;
    color:#fff;
    padding:6px 14px;
    border-radius:999px;
    font-size:12px;
    font-weight:600;
    text-decoration:none;
}
.view-btn:hover{
    background:#0284c7;
}

/* EMPTY STATE */
.empty-state{
    padding:60px;
    text-align:center;
    color:#94a3b8;
}
.empty-state i{
    font-size:36px;
    margin-bottom:10px;
    display:block;
}
</style>
</head>

<body>

<?php include '../includes/sidebar.php'; ?>

<div class="main-content">

    <!-- CONTEXT HEADER -->
    <div class="top-context">
        <div class="context-left">
            <div class="context-icon">
                <i class='bx bx-group'></i>
            </div>
            <div>
                <h1>Entries / Leads</h1>
                <p>Campaign Engine → User Submissions</p>
            </div>
        </div>

        <div class="context-right">
            <div class="context-stat">
                <span>Total Entries</span>
                <strong><?= $totalEntries ?></strong>
            </div>
            <button class="context-btn">Export CSV</button>
        </div>
    </div>

    <!-- TABLE -->
    <div class="table-card">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Campaign</th>
                    <th>Submitted On</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>

            <?php if(!$entries): ?>
                <tr>
                    <td colspan="7">
                        <div class="empty-state">
                            <i class='bx bx-inbox'></i>
                            <strong>No entries yet</strong><br>
                            <span style="font-size:13px">
                                Submissions will appear here once users participate
                            </span>
                        </div>
                    </td>
                </tr>
            <?php endif; ?>

            <?php foreach($entries as $i=>$e): ?>
                <tr>
                    <td><?= $i+1 ?></td>
                    <td><strong><?= htmlspecialchars($e['full_name']) ?></strong></td>
                    <td><?= htmlspecialchars($e['email']) ?></td>
                    <td><?= htmlspecialchars($e['phone_number']) ?></td>
                    <td>
                        <span class="campaign-badge">
                            <?= htmlspecialchars($e['campaign_title']) ?>
                        </span>
                    </td>
                    <td><?= date('d M Y, h:i A', strtotime($e['submitted_at'])) ?></td>
                    <td>
                        <a href="view-entry.php?id=<?= $e['id'] ?>" class="view-btn">
                            View
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>

            </tbody>
        </table>
    </div>

</div>

</body>
</html>
