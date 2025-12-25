<?php
require_once '../../config/config.php';
require_once '../includes/auth_check.php';

$db = getCampaignDB();
$campaign_id = $_GET['id'] ?? null;

$sql = "SELECT s.*, c.title as campaign_name,
        (SELECT COUNT(*) FROM submission_media WHERE submission_id = s.id) as media_count 
        FROM submissions s 
        JOIN campaigns c ON s.campaign_id = c.id";
if ($campaign_id) $sql .= " WHERE s.campaign_id = " . intval($campaign_id);
$sql .= " ORDER BY s.submitted_at DESC";

$submissions = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Submissions - Liyas Admin</title>
    <link rel="stylesheet" href="../assets/css/prody-admin.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
    <div class="container">
        <?php include '../includes/sidebar.php'; ?>
        <div class="main-content">
            <div class="header"><div class="breadcrumb"><span>Campaigns / Submissions</span></div></div>
            <div class="content-area">
                <div class="table-card">
                    <div class="table-header"><div class="table-title">User Submissions</div></div>
                    <div class="table-responsive-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>Customer Name</th>
                                    <th>Email & Phone</th>
                                    <th>Campaign</th>
                                    <th>Date Submitted</th>
                                    <th style="text-align: right;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($submissions as $sub): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($sub['full_name']) ?></strong></td>
                                    <td>
                                        <div><?= htmlspecialchars($sub['email']) ?></div>
                                        <div style="font-size: 11px; color: var(--text-secondary);"><?= htmlspecialchars($sub['phone_number']) ?></div>
                                    </td>
                                    <td>
    <span class="badge badge-processing"><?= htmlspecialchars($sub['campaign_name']) ?></span>
    <?php if ($sub['media_count'] > 0): ?>
        <div style="font-size: 11px; color: #2563eb; margin-top: 5px;">
            <i class='bx bx-image-add'></i> <?= $sub['media_count'] ?> Media File(s)
        </div>
    <?php endif; ?>
</td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>