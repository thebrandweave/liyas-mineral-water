<?php
require_once '../../config/config.php';
require_once '../includes/auth_check.php';

// --- Fetch Data from CAMPAIGN DB ---
try {
    $db_camp = getCampaignDB(); 
    $total_active = $db_camp->query("SELECT COUNT(*) FROM campaigns WHERE status = 'active'")->fetchColumn();
    $total_entries = $db_camp->query("SELECT COUNT(*) FROM submissions")->fetchColumn();
    
    // Joint query to detect if a poster/PDF is attached
    $query = "SELECT c.*, 
              (SELECT COUNT(*) FROM submissions s WHERE s.campaign_id = c.id) as entry_count,
              ca.file_path, ca.file_type
              FROM campaigns c 
              LEFT JOIN campaign_assets ca ON c.id = ca.campaign_id
              ORDER BY c.created_at DESC";
    $campaign_list = $db_camp->query($query)->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Campaign Index Error: " . $e->getMessage());
}

$admin_name = htmlspecialchars($_SESSION['admin_name'] ?? 'ashika');
$current_page = "campaigns";

function formatNum($num) { return number_format($num); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campaigns - Liyas Admin</title>
    <link rel="icon" type="image/jpeg" href="../../assets/images/logo/logo-bg.jpg">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../assets/css/prody-admin.css">
    <style>
        .welcome-card {
            background: #fff;
            border: 1px solid var(--border-light);
            border-radius: 8px;
            padding: 2.5rem 1.5rem;
            margin-bottom: 1.5rem;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .stat-card {
            background: var(--bg-white);
            border: 1px solid var(--border-light);
            border-radius: 33px; 
            padding: 1.5rem;
        }
        .stat-card-icon {
            width: 48px;
            height: 48px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 1rem;
        }
        .stat-card-icon.blue { background: var(--blue-light); color: var(--blue-dark); }
        .stat-card-icon.green { background: var(--green-light); color: var(--green); }
        .stat-card-title { font-size: 14px; color: var(--text-secondary); margin-bottom: 0.5rem; }
        .stat-card-value { font-size: 24px; font-weight: 600; color: var(--text-primary); }
        
        /* Fixed Action Button Colors */
        .btn-action.btn-view { background: #10b981; color: white; } /* Green for View Config */
        .btn-action.btn-edit { background: #3b82f6; color: white; } /* Blue for Edit */
        .lead-link { font-size: 11px; color: #10b981; text-decoration: none; font-weight: 600; }
        .lead-link:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <?php include '../includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="header">
                <div class="breadcrumb">
                    <i class='bx bx-home'></i>
                    <span>Dashboard</span>
                </div>
            </div>
            
            <div class="content-area">
                <div class="welcome-card">
                    <h1 class="dashboard-title" style="font-size: 24px; font-weight: 600; color: #0f172a; margin-bottom: 0.5rem;">
                        Welcome back, <?= $admin_name ?>!
                    </h1>
                    <p class="dashboard-subtitle" style="font-size: 14px; color: #64748b;">
                        Here's what's happening with your campaigns today.
                    </p>
                </div>
                
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-card-header">
                            <div class="stat-card-icon blue"><i class='bx bx-qr-scan'></i></div>
                        </div>
                        <div class="stat-card-title">Active Campaigns</div>
                        <div class="stat-card-value"><?= formatNum($total_active) ?></div>
                        <div style="margin-top: 0.5rem;">
                            <span style="color: var(--text-secondary); font-size: 13px;">Currently running</span>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-card-header">
                            <div class="stat-card-icon green"><i class='bx bx-group'></i></div>
                        </div>
                        <div class="stat-card-title">Total Entries</div>
                        <div class="stat-card-value"><?= formatNum($total_entries) ?></div>
                        <div style="margin-top: 0.5rem;">
                            <a href="submissions.php" style="color: var(--green); font-size: 13px; text-decoration: none;">View all entries →</a>
                        </div>
                    </div>
                    <div style="visibility: hidden;" class="stat-card"></div>
                    <div style="visibility: hidden;" class="stat-card"></div>
                </div>
                
                <div class="table-card">
                    <div class="table-header">
                        <div class="table-title">Live Campaigns</div>
                        <div class="table-actions">
                            <a href="create.php" class="table-btn" style="background: var(--blue); color: white; border: none; padding: 10px 18px; border-radius: 10px;">
                                <i class='bx bx-plus'></i> <span>Create Campaign</span>
                            </a>
                        </div>
                    </div>
                    
                    <div class="table-responsive-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>Campaign</th>
                                    <th>Status</th>
                                    <th>Timeline</th>
                                    <th>Slug</th>
                                    <th>Submissions</th>
                                    <th style="text-align: right;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($campaign_list as $row): ?>
                                <tr>
                                    <td>
                                        <div style="display:flex; align-items:center; gap:10px;">
                                            <?php if ($row['file_path']): ?>
                                                <a href="../../<?= $row['file_path'] ?>" target="_blank" title="View Poster" style="color:#3b82f6; background:#eff6ff; padding:5px; border-radius:6px; display:flex;">
                                                    <i class='bx bx-file'></i>
                                                </a>
                                            <?php endif; ?>
                                            <strong><?= htmlspecialchars($row['title']) ?></strong>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge <?= ($row['status'] == 'active') ? 'badge-completed' : 'badge-cancelled' ?>">
                                            <?= ucfirst($row['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div style="font-size: 13px; font-weight:500;"><?= date('d M Y', strtotime($row['start_date'])) ?></div>
                                        <div style="font-size: 11px; color: var(--text-secondary);">to <?= $row['end_date'] ? date('d M Y', strtotime($row['end_date'])) : 'Ongoing' ?></div>
                                    </td>
                                    <td style="color: var(--blue); font-family: monospace;">/<?= $row['slug'] ?></td>
                                    <td>
                                        <strong><?= formatNum($row['entry_count']) ?></strong>
                                        <div><a href="submissions.php?id=<?= $row['id'] ?>" class="lead-link">View Leads</a></div>
                                    </td>
                                    <td style="text-align: right;">
                                        <div style="display: flex; gap: 8px; justify-content: flex-end;">
                                            <a href="view.php?id=<?= $row['id'] ?>" class="btn-action btn-view" title="View Config"><i class='bx bx-show'></i></a>
                                            <a href="edit.php?id=<?= $row['id'] ?>" class="btn-action btn-edit" title="Edit Campaign"><i class='bx bx-edit'></i></a>
                                        </div>
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