<?php
require_once '../../config/config.php';
require_once '../includes/auth_check.php';
require_once '../includes/activity_logger.php';

$admin_name = htmlspecialchars($_SESSION['admin_name'] ?? 'Admin');
$current_page = "activity-logs";
$page_title = "Activity Logs";

// Set timezone to Indian Standard Time
date_default_timezone_set('Asia/Kolkata');

/**
 * Format timestamp to IST
 */
function formatIST($dbTimestamp) {
    if (empty($dbTimestamp)) return '';
    try {
        $dt = new DateTime($dbTimestamp);
        $dt->setTimezone(new DateTimeZone('Asia/Kolkata'));
        return $dt->format('d M Y, h:i A');
    } catch (Exception $e) {
        return date('d M Y, h:i A', strtotime($dbTimestamp));
    }
}

/**
 * Get badge class for action type
 */
function getActionBadgeClass($action_type) {
    $classes = [
        'create' => 'badge-success',
        'update' => 'badge-info',
        'delete' => 'badge-error',
        'login' => 'badge-success',
        'logout' => 'badge-secondary',
        'view' => 'badge-info',
        'export' => 'badge-info',
        'generate' => 'badge-success',
    ];
    return $classes[$action_type] ?? 'badge-secondary';
}

/**
 * Get icon for action type
 */
function getActionIcon($action_type) {
    $icons = [
        'create' => 'bx-plus-circle',
        'update' => 'bx-edit',
        'delete' => 'bx-trash',
        'login' => 'bx-log-in',
        'logout' => 'bx-log-out',
        'view' => 'bx-show',
        'export' => 'bx-download',
        'generate' => 'bx-qr',
    ];
    return $icons[$action_type] ?? 'bx-info-circle';
}

// Search and filter parameters
$search = $_GET['search'] ?? '';
$filter_action = $_GET['filter_action'] ?? 'all';
$filter_entity = $_GET['filter_entity'] ?? 'all';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 50;
$offset = ($page - 1) * $per_page;

// Build WHERE clause
$where_conditions = [];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "(description LIKE :search OR admin_name LIKE :search)";
    $params[':search'] = "%$search%";
}

if ($filter_action !== 'all') {
    $where_conditions[] = "action_type = :action_type";
    $params[':action_type'] = $filter_action;
}

if ($filter_entity !== 'all') {
    $where_conditions[] = "entity_type = :entity_type";
    $params[':entity_type'] = $filter_entity;
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Get total count for pagination
try {
    $count_query = "SELECT COUNT(*) FROM activity_logs $where_clause";
    $count_stmt = $pdo->prepare($count_query);
    foreach ($params as $key => $value) {
        $count_stmt->bindValue($key, $value);
    }
    $count_stmt->execute();
    $total_records = $count_stmt->fetchColumn();
    $total_pages = ceil($total_records / $per_page);
} catch (PDOException $e) {
    $total_records = 0;
    $total_pages = 0;
    error_log("Activity logs count error: " . $e->getMessage());
}

// Fetch activity logs
try {
    $query = "SELECT activity_id, admin_id, admin_name, action_type, entity_type, entity_id, 
                     description, ip_address, created_at
              FROM activity_logs 
              $where_clause
              ORDER BY created_at DESC 
              LIMIT :limit OFFSET :offset";
    
    $stmt = $pdo->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $activity_logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $activity_logs = [];
    error_log("Activity logs fetch error: " . $e->getMessage());
}

// Get unique action types and entity types for filters
try {
    $action_types = $pdo->query("SELECT DISTINCT action_type FROM activity_logs ORDER BY action_type")->fetchAll(PDO::FETCH_COLUMN);
    $entity_types = $pdo->query("SELECT DISTINCT entity_type FROM activity_logs WHERE entity_type IS NOT NULL ORDER BY entity_type")->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $action_types = [];
    $entity_types = [];
}

// Get statistics
try {
    $total_logs = $pdo->query("SELECT COUNT(*) FROM activity_logs")->fetchColumn();
    $today_logs = $pdo->query("SELECT COUNT(*) FROM activity_logs WHERE DATE(created_at) = CURDATE()")->fetchColumn();
    $this_week_logs = $pdo->query("SELECT COUNT(*) FROM activity_logs WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetchColumn();
} catch (PDOException $e) {
    $total_logs = 0;
    $today_logs = 0;
    $this_week_logs = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Favicon -->
    <link rel="icon" type="image/jpeg" href="../../assets/images/logo/logo-bg.jpg">
    <link rel="shortcut icon" type="image/jpeg" href="../../assets/images/logo/logo-bg.jpg">
    <link rel="apple-touch-icon" href="../../assets/images/logo/logo-bg.jpg">
    <link rel="icon" type="image/jpeg" sizes="32x32" href="../../assets/images/logo/logo-bg.jpg">
    <link rel="icon" type="image/jpeg" sizes="16x16" href="../../assets/images/logo/logo-bg.jpg">
    
    <!-- Google Font: Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../assets/css/prody-admin.css">
    <title><?= $page_title ?> - Liyas Admin</title>
    <style>
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .stat-card {
            background: var(--bg-white);
            border: 1px solid var(--border-light);
            border-radius: 8px;
            padding: 1rem;
        }
        
        .stat-card-title {
            font-size: 13px;
            color: var(--text-secondary);
            margin-bottom: 0.5rem;
        }
        
        .stat-card-value {
            font-size: 24px;
            font-weight: 600;
            color: var(--text-primary);
        }
        
        .filters-container {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            align-items: center;
        }
        
        .filter-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .filter-group label {
            font-size: 14px;
            color: var(--text-secondary);
            white-space: nowrap;
        }
        
        .filter-group select,
        .filter-group input {
            padding: 0.5rem 0.75rem;
            border: 1px solid var(--border-light);
            border-radius: 6px;
            font-size: 14px;
            font-family: inherit;
        }
        
        .activity-item {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            padding: 1rem;
            border-bottom: 1px solid var(--border-light);
            transition: background 0.2s;
        }
        
        .activity-item:hover {
            background: var(--bg-light);
        }
        
        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
        }
        
        .activity-icon.create { background: var(--green-light); color: var(--green); }
        .activity-icon.update { background: var(--blue-light); color: var(--blue-dark); }
        .activity-icon.delete { background: #fee2e2; color: var(--red-dark); }
        .activity-icon.login { background: var(--green-light); color: var(--green); }
        .activity-icon.logout { background: #e5e7eb; color: #6b7280; }
        .activity-icon.view { background: var(--blue-light); color: var(--blue-dark); }
        .activity-icon.export { background: var(--blue-light); color: var(--blue-dark); }
        .activity-icon.generate { background: var(--green-light); color: var(--green); }
        .activity-icon.default { background: #e5e7eb; color: #6b7280; }
        
        .activity-content {
            flex: 1;
            min-width: 0;
        }
        
        .activity-header {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.25rem;
            flex-wrap: wrap;
        }
        
        .activity-description {
            color: var(--text-primary);
            font-size: 14px;
            margin-bottom: 0.25rem;
            word-wrap: break-word;
        }
        
        .activity-meta {
            display: flex;
            align-items: center;
            gap: 1rem;
            font-size: 12px;
            color: var(--text-secondary);
            flex-wrap: wrap;
        }
        
        .activity-meta-item {
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }
        
        .table-responsive-wrapper {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        
        @media (max-width: 768px) {
            .filters-container {
                flex-direction: column;
                align-items: stretch;
            }
            
            .filter-group {
                width: 100%;
            }
            
            .filter-group select,
            .filter-group input {
                width: 100%;
            }
            
            .activity-item {
                flex-direction: column;
            }
            
            .activity-meta {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <?php include '../includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="header">
                <div class="breadcrumb">
                    <i class='bx bx-home'></i>
                    <span>Activity Logs</span>
                </div>
                <div class="header-actions">
                    <form action="index.php" method="GET" style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                        <input type="hidden" name="filter_action" value="<?= htmlspecialchars($filter_action) ?>">
                        <input type="hidden" name="filter_entity" value="<?= htmlspecialchars($filter_entity) ?>">
                        <input type="search" name="search" placeholder="Search activities..." value="<?= htmlspecialchars($search) ?>" style="padding: 0.5rem 0.75rem; border: 1px solid var(--border-light); border-radius: 6px; font-size: 14px; font-family: inherit; min-width: 200px;">
                        <button type="submit" class="header-btn" style="padding: 0.5rem;">
                            <i class='bx bx-search'></i>
                        </button>
                    </form>
                </div>
            </div>
            
            <div class="content-area">
                <!-- Statistics -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-card-title">Total Activities</div>
                        <div class="stat-card-value"><?= number_format($total_logs) ?></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-card-title">Today</div>
                        <div class="stat-card-value"><?= number_format($today_logs) ?></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-card-title">This Week</div>
                        <div class="stat-card-value"><?= number_format($this_week_logs) ?></div>
                    </div>
                </div>
                
                <!-- Filters -->
                <form method="GET" action="index.php" class="filters-container">
                    <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
                    
                    <div class="filter-group">
                        <label for="filter_action">Action:</label>
                        <select name="filter_action" id="filter_action" onchange="this.form.submit()">
                            <option value="all" <?= $filter_action === 'all' ? 'selected' : '' ?>>All Actions</option>
                            <?php foreach ($action_types as $action): ?>
                                <option value="<?= htmlspecialchars($action) ?>" <?= $filter_action === $action ? 'selected' : '' ?>>
                                    <?= ucfirst(htmlspecialchars($action)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="filter_entity">Entity:</label>
                        <select name="filter_entity" id="filter_entity" onchange="this.form.submit()">
                            <option value="all" <?= $filter_entity === 'all' ? 'selected' : '' ?>>All Entities</option>
                            <?php foreach ($entity_types as $entity): ?>
                                <option value="<?= htmlspecialchars($entity) ?>" <?= $filter_entity === $entity ? 'selected' : '' ?>>
                                    <?= ucfirst(htmlspecialchars($entity)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <?php if ($filter_action !== 'all' || $filter_entity !== 'all'): ?>
                        <a href="index.php" class="btn btn-secondary" style="padding: 0.5rem 1rem; font-size: 14px;">
                            Clear Filters
                        </a>
                    <?php endif; ?>
                </form>
                
                <!-- Activity Logs Table -->
                <div class="table-card">
                    <div class="table-header">
                        <div class="table-title">
                            Activity Logs
                            <?php if ($total_records > 0): ?>
                                <span style="font-size: 14px; font-weight: normal; color: var(--text-secondary);">
                                    (<?= number_format($total_records) ?> total)
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="table-responsive-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th style="width: 60px;">Icon</th>
                                    <th>Activity</th>
                                    <th>Admin</th>
                                    <th>Entity</th>
                                    <th>IP Address</th>
                                    <th>Date & Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($activity_logs)): ?>
                                    <tr>
                                        <td colspan="6" style="text-align: center; padding: 2rem;">
                                            No activity logs found.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($activity_logs as $log): ?>
                                        <tr>
                                            <td style="text-align: center;">
                                                <div class="activity-icon <?= htmlspecialchars($log['action_type']) ?>">
                                                    <i class='bx <?= getActionIcon($log['action_type']) ?>'></i>
                                                </div>
                                            </td>
                                            <td>
                                                <div style="font-weight: 500; margin-bottom: 0.25rem;">
                                                    <span class="badge <?= getActionBadgeClass($log['action_type']) ?>">
                                                        <?= ucfirst(htmlspecialchars($log['action_type'])) ?>
                                                    </span>
                                                </div>
                                                <div style="font-size: 13px; color: var(--text-secondary);">
                                                    <?= htmlspecialchars($log['description']) ?>
                                                </div>
                                            </td>
                                            <td>
                                                <strong><?= htmlspecialchars($log['admin_name']) ?></strong>
                                                <div style="font-size: 12px; color: var(--text-secondary);">
                                                    ID: <?= $log['admin_id'] ?>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if ($log['entity_type']): ?>
                                                    <span style="font-weight: 500;"><?= ucfirst(htmlspecialchars($log['entity_type'])) ?></span>
                                                    <?php if ($log['entity_id']): ?>
                                                        <span style="color: var(--text-secondary);">#<?= $log['entity_id'] ?></span>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span style="color: var(--text-secondary);">—</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span style="font-family: monospace; font-size: 12px;">
                                                    <?= htmlspecialchars($log['ip_address'] ?? '—') ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span style="font-size: 13px;">
                                                    <?= formatIST($log['created_at']) ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <div class="pagination" style="padding: 1rem; border-top: 1px solid var(--border-light);">
                            <?php
                            $query_params = [];
                            if (!empty($search)) $query_params['search'] = $search;
                            if ($filter_action !== 'all') $query_params['filter_action'] = $filter_action;
                            if ($filter_entity !== 'all') $query_params['filter_entity'] = $filter_entity;
                            $query_string = !empty($query_params) ? '&' . http_build_query($query_params) : '';
                            ?>
                            
                            <?php if ($page > 1): ?>
                                <a href="?page=<?= $page - 1 ?><?= $query_string ?>" class="pagination-btn">
                                    <i class='bx bx-chevron-left'></i> Previous
                                </a>
                            <?php endif; ?>
                            
                            <span style="margin: 0 1rem; color: var(--text-secondary);">
                                Page <?= $page ?> of <?= $total_pages ?>
                            </span>
                            
                            <?php if ($page < $total_pages): ?>
                                <a href="?page=<?= $page + 1 ?><?= $query_string ?>" class="pagination-btn">
                                    Next <i class='bx bx-chevron-right'></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

