<?php
require_once '../config/config.php';
require_once 'includes/auth_check.php';

// --- Fetch Data ---
$total_products = 0;
$total_categories = 0;
$total_admins = 0;
$total_orders = 0;
$pending_orders = 0;
$total_revenue = 0;
$recent_orders = [];
$monthly_revenue = [];
$monthly_orders = [];

try {
    $total_products = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
    $total_categories = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
    $total_admins = $pdo->query("SELECT COUNT(*) FROM admins")->fetchColumn();
    $total_orders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    $pending_orders = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn();
    
    // Total revenue
    $revenue_stmt = $pdo->query("SELECT SUM(total_amount) FROM orders WHERE status != 'cancelled'");
    $total_revenue = $revenue_stmt ? (float)$revenue_stmt->fetchColumn() : 0;
    
    // Fetch recent orders
    $recent_orders_stmt = $pdo->query("
        SELECT o.order_id, o.customer_name, o.customer_email, o.total_amount, o.created_at, o.status
        FROM orders o
        ORDER BY o.created_at DESC
        LIMIT 10
    ");
    $recent_orders = $recent_orders_stmt ? $recent_orders_stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    
    // Monthly revenue for last 6 months
    $monthly_revenue_stmt = $pdo->query("
        SELECT 
            DATE_FORMAT(created_at, '%Y-%m') as month,
            SUM(total_amount) as revenue
        FROM orders 
        WHERE status != 'cancelled' 
        AND created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(created_at, '%Y-%m')
        ORDER BY month ASC
    ");
    $monthly_revenue_data = $monthly_revenue_stmt ? $monthly_revenue_stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    
    // Monthly orders for last 6 months
    $monthly_orders_stmt = $pdo->query("
        SELECT 
            DATE_FORMAT(created_at, '%Y-%m') as month,
            COUNT(*) as order_count
        FROM orders 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(created_at, '%Y-%m')
        ORDER BY month ASC
    ");
    $monthly_orders_data = $monthly_orders_stmt ? $monthly_orders_stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    
    // Format for Chart.js
    $months = [];
    $revenues = [];
    $orders_count = [];
    
    for ($i = 5; $i >= 0; $i--) {
        $date = date('Y-m', strtotime("-$i months"));
        $months[] = date('M Y', strtotime("-$i months"));
        
        $revenue = 0;
        $order_count = 0;
        foreach ($monthly_revenue_data as $row) {
            if ($row['month'] == $date) {
                $revenue = (float)$row['revenue'];
                break;
            }
        }
        foreach ($monthly_orders_data as $row) {
            if ($row['month'] == $date) {
                $order_count = (int)$row['order_count'];
                break;
            }
        }
        $revenues[] = $revenue;
        $orders_count[] = $order_count;
    }
    
} catch (PDOException $e) {
    // Handle error silently
}

$admin_name = htmlspecialchars($_SESSION['admin_name'] ?? 'Admin');
$current_page = "dashboard";

// Format currency
function formatCurrency($amount) {
    return '₹' . number_format($amount, 2);
}

// Map order status to badge class
function getStatusBadgeClass($status) {
    $status = strtolower($status);
    $badgeMap = [
        'pending' => 'badge-pending',
        'processing' => 'badge-processing',
        'shipped' => 'badge-processing',
        'delivered' => 'badge-completed',
        'cancelled' => 'badge-cancelled'
    ];
    return $badgeMap[$status] ?? 'badge-pending';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Liyas Admin</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/jpeg" href="../assets/images/logo/logo-bg.jpg">
    
    <!-- Google Font: Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    
    <!-- Styles -->
    <link rel="stylesheet" href="assets/css/prody-admin.css">
    
    <!-- Chart.js (deferred so it doesn't block initial render) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js" defer></script>
    
    <style>
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
        
        .stat-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
        }
        
        .stat-card-icon {
            width: 48px;
            height: 48px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }
        
        .stat-card-icon.blue { background: var(--blue-light); color: var(--blue-dark); }
        .stat-card-icon.green { background: var(--green-light); color: var(--green); }
        .stat-card-icon.yellow { background: var(--yellow-light); color: #92400e; }
        .stat-card-icon.red { background: #fee2e2; color: var(--red-dark); }
        
        .stat-card-title {
            font-size: 14px;
            color: var(--text-secondary);
            margin-bottom: 0.5rem;
        }
        
        .stat-card-value {
            font-size: 24px;
            font-weight: 600;
            color: var(--text-primary);
        }
        
        .chart-card {
            background: var(--bg-white);
            border: 1px solid var(--border-light);
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .chart-wrapper {
            position: relative;
            height: 300px;
            margin-top: 1rem;
        }
        
        .dashboard-header {
            background: var(--bg-white);
            border-bottom: 1px solid var(--border-light);
            padding: 1.5rem;
        }
        
        .dashboard-title {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .dashboard-subtitle {
            font-size: 14px;
            color: var(--text-secondary);
        }
        
        .table-responsive-wrapper {
            width: 100%;
            overflow-x: auto;
            overflow-y: visible;
            -webkit-overflow-scrolling: touch;
            position: relative;
        }
        
        .table-responsive-wrapper table {
            min-width: 750px;
            width: 100%;
        }
        
        /* Mobile optimizations */
        @media (max-width: 768px) {
            .table-responsive-wrapper {
                overflow-x: scroll;
                -webkit-overflow-scrolling: touch;
                scrollbar-width: thin;
                scrollbar-color: var(--border-medium) transparent;
            }
            
            .table-responsive-wrapper::-webkit-scrollbar {
                height: 8px;
            }
            
            .table-responsive-wrapper::-webkit-scrollbar-track {
                background: var(--bg-main);
                border-radius: 4px;
            }
            
            .table-responsive-wrapper::-webkit-scrollbar-thumb {
                background: var(--border-medium);
                border-radius: 4px;
            }
            
            .table-responsive-wrapper::-webkit-scrollbar-thumb:hover {
                background: var(--text-secondary);
            }
            
            .table-responsive-wrapper table {
                min-width: 850px;
            }
            
            .table-responsive-wrapper table th,
            .table-responsive-wrapper table td {
                padding: 0.75rem 1rem;
                font-size: 13px;
            }
            
            .table-responsive-wrapper table th:first-child,
            .table-responsive-wrapper table td:first-child {
                position: sticky;
                left: 0;
                background: var(--bg-white);
                z-index: 10;
                box-shadow: 2px 0 4px rgba(0,0,0,0.05);
            }
            
            .table-responsive-wrapper table th:last-child,
            .table-responsive-wrapper table td:last-child {
                min-width: 180px;
            }
        }
        
        @media (max-width: 480px) {
            .table-responsive-wrapper table {
                min-width: 950px;
            }
            
            .table-responsive-wrapper table th,
            .table-responsive-wrapper table td {
                padding: 0.5rem 0.75rem;
                font-size: 12px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="header">
                <div class="breadcrumb">
                    <i class='bx bx-home'></i>
                    <span>Dashboard</span>
                </div>
                <div class="header-actions"></div>
            </div>
            
            <div class="content-area">
                <!-- Dashboard Header -->
                <div class="dashboard-header" style="margin-bottom: 1.5rem;">
                    <h1 class="dashboard-title">Welcome back, <?= $admin_name ?>!</h1>
                    <p class="dashboard-subtitle">Here's what's happening with your business today.</p>
                </div>
                
                <!-- Stats Grid -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-card-header">
                            <div class="stat-card-icon blue">
                                <i class='bx bx-shopping-bag'></i>
                            </div>
                        </div>
                        <div class="stat-card-title">Total Products</div>
                        <div class="stat-card-value"><?= number_format($total_products) ?></div>
                        <div style="margin-top: 0.5rem;">
                            <a href="products/index.php" style="color: var(--blue); font-size: 13px; text-decoration: none;">View all →</a>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-card-header">
                            <div class="stat-card-icon green">
                                <i class='bx bx-cart'></i>
                            </div>
                        </div>
                        <div class="stat-card-title">Total Orders</div>
                        <div class="stat-card-value"><?= number_format($total_orders) ?></div>
                        <div style="margin-top: 0.5rem;">
                            <a href="orders/index.php" style="color: var(--green); font-size: 13px; text-decoration: none;">View all →</a>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-card-header">
                            <div class="stat-card-icon yellow">
                                <i class='bx bx-time'></i>
                            </div>
                        </div>
                        <div class="stat-card-title">Pending Orders</div>
                        <div class="stat-card-value"><?= number_format($pending_orders) ?></div>
                        <div style="margin-top: 0.5rem;">
                            <a href="orders/index.php?filter=pending" style="color: #92400e; font-size: 13px; text-decoration: none;">View pending →</a>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-card-header">
                            <div class="stat-card-icon red">
                                <i class='bx bx-rupee'></i>
                            </div>
                        </div>
                        <div class="stat-card-title">Total Revenue</div>
                        <div class="stat-card-value"><?= formatCurrency($total_revenue) ?></div>
                        <div style="margin-top: 0.5rem; font-size: 13px; color: var(--text-secondary);">
                            All time
                        </div>
                    </div>
                </div>
                
                <!-- Chart Section -->
                <div class="chart-card">
                    <div class="table-header" style="border-bottom: 1px solid var(--border-light); padding-bottom: 1rem; margin-bottom: 1rem;">
                        <div class="table-title">
                            Revenue & Orders Overview
                        </div>
                        <div class="table-actions">
                            <button class="table-btn" onclick="refreshChart()">
                                <i class='bx bx-refresh'></i>
                                <span>Refresh</span>
                            </button>
                        </div>
                    </div>
                    <div class="chart-legend" style="margin-bottom: 1rem;">
                        <div class="legend-item">
                            <div class="legend-line" style="background: var(--blue);"></div>
                            <span>Revenue (₹)</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-line" style="background: var(--green);"></div>
                            <span>Orders Count</span>
                        </div>
                    </div>
                    <div class="chart-wrapper">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
                
                <!-- Recent Orders Table -->
                <div class="table-card">
                    <div class="table-header">
                        <div class="table-title">
                            Recent Orders
                            <!-- <i class='bx bx-chevron-down'></i> -->
                        </div>
                        <div class="table-actions">
                            <a href="orders/index.php" class="table-btn">
                                <i class='bx bx-list-ul'></i>
                                <span>View All</span>
                            </a>
                        </div>
                    </div>
                    
                    <div class="table-responsive-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>Customer</th>
                                    <th>Email</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($recent_orders)): ?>
                                    <tr>
                                        <td colspan="6" style="text-align: center; padding: 2rem;">
                                            No orders found. <a href="orders/index.php" style="color: var(--blue);">View all orders</a>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($recent_orders as $order): ?>
                                        <tr>
                                            <td><strong><?= htmlspecialchars($order['customer_name'] ?? 'N/A') ?></strong></td>
                                            <td><?= htmlspecialchars($order['customer_email'] ?? 'N/A') ?></td>
                                            <td><strong><?= formatCurrency($order['total_amount']) ?></strong></td>
                                            <td>
                                                <span class="badge <?= getStatusBadgeClass($order['status']) ?>">
                                                    <?= ucfirst($order['status']) ?>
                                                </span>
                                            </td>
                                            <td><?= date('d M Y', strtotime($order['created_at'])) ?></td>
                                            <td>
                                                <a href="orders/view.php?id=<?= $order['order_id'] ?>" class="btn-action btn-view">
                                                    <i class='bx bx-show'></i> View
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Chart.js Configuration
        function initRevenueChart() {
            if (!window.Chart) {
                console.warn('Chart.js not loaded yet, retrying...');
                setTimeout(initRevenueChart, 100);
                return;
            }
            
            const canvas = document.getElementById('revenueChart');
            if (!canvas) return;
            
            const ctx = canvas.getContext('2d');
            const revenueChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?= json_encode($months) ?>,
                datasets: [
                    {
                        label: 'Revenue (₹)',
                        data: <?= json_encode($revenues) ?>,
                        borderColor: 'rgb(59, 130, 246)',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.4,
                        fill: true,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Orders Count',
                        data: <?= json_encode($orders_count) ?>,
                        borderColor: 'rgb(5, 150, 105)',
                        backgroundColor: 'rgba(5, 150, 105, 0.1)',
                        tension: 0.4,
                        fill: true,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    }
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Revenue (₹)'
                        },
                        ticks: {
                            callback: function(value) {
                                return '₹' + value.toLocaleString();
                            }
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Orders Count'
                        },
                        grid: {
                            drawOnChartArea: false
                        }
                    }
                },
                interaction: {
                    mode: 'nearest',
                    axis: 'x',
                    intersect: false
                }
            }
            });
            
            // Store chart instance globally for refresh function
            window.revenueChart = revenueChart;
        }
        
        // Initialize chart when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initRevenueChart);
        } else {
            initRevenueChart();
        }
        
        function refreshChart() {
            location.reload();
        }
    </script>
</body>
</html>