<?php
require_once '../../config/config.php';
require_once '../includes/auth_check.php';

$db = getCampaignDB();
$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: index.php");
    exit();
}

try {
    $stmt = $db->prepare("SELECT * FROM campaigns WHERE id = ?");
    $stmt->execute([$id]);
    $campaign = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$campaign) {
        die("<div style='padding:50px; text-align:center; font-family:sans-serif;'><h2>Campaign not found.</h2><a href='index.php'>Return to Dashboard</a></div>");
    }

    $stmtQ = $db->prepare("SELECT * FROM campaign_questions WHERE campaign_id = ? ORDER BY sort_order ASC");
    $stmtQ->execute([$id]);
    $questions = $stmtQ->fetchAll(PDO::FETCH_ASSOC);

    $stmtA = $db->prepare("SELECT * FROM campaign_assets WHERE campaign_id = ? LIMIT 1");
    $stmtA->execute([$id]);
    $asset = $stmtA->fetch(PDO::FETCH_ASSOC);

    $stmtCount = $db->prepare("SELECT COUNT(*) FROM submissions WHERE campaign_id = ?");
    $stmtCount->execute([$id]);
    $submission_count = $stmtCount->fetchColumn();

} catch (PDOException $e) {
    error_log("View Campaign Error: " . $e->getMessage());
    die("A database error occurred.");
}

$current_page = "campaigns";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campaign Details | <?= htmlspecialchars($campaign['title']) ?></title>
    
    <link rel="icon" type="image/jpeg" href="../../assets/images/logo/logo-bg.jpg">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../assets/css/prody-admin.css">
    
    <style>
        :root {
            --bg-main: #f8fafc;
            --card-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.02);
            --primary: #2563eb;
            --slate-700: #334155;
            --slate-500: #64748b;
        }

        body { font-family: 'Inter', sans-serif; background-color: var(--bg-main); }

        /* Professional Header */
        .campaign-hero {
            background: white;
            padding: 2rem;
            border-radius: 16px;
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 1px solid #e2e8f0;
            box-shadow: var(--card-shadow);
        }

        .hero-title-area h1 { font-size: 24px; font-weight: 700; color: #0f172a; margin: 0 0 8px 0; }
        .hero-meta { display: flex; gap: 15px; align-items: center; color: var(--slate-500); font-size: 14px; }
        
        /* Stats Card */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .stat-icon {
            width: 48px; height: 48px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 24px; background: #eff6ff; color: var(--primary);
        }
        .stat-info .label { font-size: 12px; font-weight: 600; color: var(--slate-500); text-transform: uppercase; }
        .stat-info .value { font-size: 15px; font-weight: 700; color: #1e293b; }

        /* Main Grid Layout */
        .details-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 2rem; }
        .card { 
            background: white; 
            border-radius: 16px; 
            border: 1px solid #e2e8f0; 
            box-shadow: var(--card-shadow);
            overflow: hidden;
            margin-bottom: 2rem;
        }
        .card-header { 
            padding: 1.25rem 1.5rem; 
            border-bottom: 1px solid #f1f5f9; 
            display: flex; 
            align-items: center; 
            gap: 10px;
            font-weight: 600;
            color: #0f172a;
        }
        .card-body { padding: 1.5rem; }

        /* Questions Styling */
        .q-row {
            padding: 1rem;
            border-radius: 8px;
            border: 1px solid #f1f5f9;
            margin-bottom: 0.75rem;
            display: flex;
            justify-content: space-between;
            background: #fcfcfd;
            transition: transform 0.2s;
        }
        .q-row:hover { transform: translateX(5px); border-color: var(--primary); }
        .q-label { font-weight: 500; color: #334155; }
        
        /* Badge Styling */
        .badge-pill {
            padding: 4px 12px;
            border-radius: 99px;
            font-size: 12px;
            font-weight: 600;
        }
        .bg-success-lite { background: #dcfce7; color: #166534; }
        .bg-blue-lite { background: #dbeafe; color: #1e40af; }

        .url-box {
            background: #f8fafc;
            padding: 12px;
            border-radius: 8px;
            border: 1px dashed #cbd5e1;
            font-family: monospace;
            color: var(--primary);
            word-break: break-all;
        }

        @media (max-width: 992px) {
            .details-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="container">
        <?php include '../includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="campaign-hero">
                <div class="hero-title-area">
                    <div class="hero-meta">
                        <span><i class='bx bx-calendar'></i> Created on <?= date('M d, Y', strtotime($campaign['start_date'])) ?></span>
                        <span class="badge-pill <?= ($campaign['status'] == 'active') ? 'bg-success-lite' : 'bg-blue-lite' ?>">
                            <i class='bx bxs-circle' style="font-size: 8px; vertical-align: middle;"></i> <?= ucfirst($campaign['status']) ?>
                        </span>
                    </div>
                    <h1><?= htmlspecialchars($campaign['title']) ?></h1>
                </div>
                <div class="hero-actions">
                    <a href="edit.php?id=<?= $id ?>" class="table-btn" style="background: var(--primary); color:white; border:none; padding: 10px 20px; border-radius: 8px;">
                        <i class='bx bx-edit-alt'></i> Edit Configuration
                    </a>
                </div>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon"><i class='bx bx-group'></i></div>
                    <div class="stat-info">
                        <div class="label">Total Entries</div>
                        <div class="value"><?= number_format($submission_count) ?></div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: #fef2f2; color: #ef4444;"><i class='bx bx-time-five'></i></div>
                    <div class="stat-info">
                        <div class="label">End Date</div>
                        <div class="value"><?= $campaign['end_date'] ? date('d M Y', strtotime($campaign['end_date'])) : 'Ongoing' ?></div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: #f0fdf4; color: #22c55e;"><i class='bx bx-link-external'></i></div>
                    <div class="stat-info">
                        <div class="label">Public Link</div>
                        <div class="value" style="font-size: 14px;">/<?= htmlspecialchars($campaign['slug']) ?></div>
                    </div>
                </div>
            </div>

            <div class="details-grid">
                <div class="left-col">
                    <div class="card">
                        <div class="card-header"><i class='bx bx-list-ul'></i> Form Fields Configuration</div>
                        <div class="card-body">
                            <?php if (empty($questions)): ?>
                                <div style="text-align:center; padding: 2rem; color: var(--slate-500);">
                                    <i class='bx bx-comment-error' style="font-size: 40px; opacity: 0.5;"></i>
                                    <p>No custom questions configured for this campaign.</p>
                                </div>
                            <?php else: ?>
                                <?php foreach($questions as $q): ?>
                                    <div class="q-row">
                                        <div>
                                            <div class="q-label"><?= htmlspecialchars($q['question_label']) ?></div>
                                            <div style="font-size: 12px; color: var(--slate-500); margin-top: 4px;">
                                                Type: <span style="text-transform: capitalize;"><?= $q['field_type'] ?></span>
                                            </div>
                                        </div>
                                        <div style="text-align: right;">
                                            <span class="badge-pill" style="background: #f1f5f9; color: #475569;">
                                                <?= $q['is_required'] ? 'Required' : 'Optional' ?>
                                            </span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="right-col">
                    <div class="card">
                        <div class="card-header"><i class='bx bx-image-alt'></i> Promotional Asset</div>
                        <div class="card-body">
                            <?php if ($asset): ?>
                                <p style="font-size: 13px; color: var(--slate-500); margin-bottom: 1rem;">Attached <?= strtoupper($asset['file_type']) ?> for the landing page:</p>
                                <a href="../../<?= $asset['file_path'] ?>" target="_blank" class="poster-preview-btn" style="width: 100%; justify-content: center; background: #f8fafc; border: 1px solid #e2e8f0; padding: 12px;">
                                    <i class='bx bx-show-alt'></i> Preview Asset
                                </a>
                            <?php else: ?>
                                <div style="padding: 1rem; background: #fff7ed; border-radius: 8px; color: #9a3412; font-size: 13px;">
                                    <i class='bx bx-error-circle'></i> No poster or PDF has been attached.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header"><i class='bx bx-share-alt'></i> Share Campaign</div>
                        <div class="card-body">
                            <p style="font-size: 13px; color: var(--slate-500); margin-bottom: 0.5rem;">Landing Page Slug:</p>
                            <div class="url-box">
                                domain.com/<?= htmlspecialchars($campaign['slug']) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>