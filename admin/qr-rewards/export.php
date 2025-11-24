<?php
require_once '../../config/config.php';
require_once '../includes/auth_check.php';

// Get filter parameters (same as index.php)
$filter = $_GET['filter'] ?? 'all'; // all, used, unused
$search = $_GET['search'] ?? '';

// Build query (same logic as index.php)
$where_conditions = [];
$params = [];

if ($filter === 'used') {
    $where_conditions[] = "c.is_used = 1";
} elseif ($filter === 'unused') {
    $where_conditions[] = "c.is_used = 0";
}

if (!empty($search)) {
    $where_conditions[] = "c.reward_code LIKE :search";
    $params[':search'] = "%$search%";
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Fetch all reward codes (no pagination for export)
try {
    $query = "SELECT c.id, c.reward_code, c.is_used, c.used_at, c.created_at,
                     c.customer_name, c.customer_phone, c.customer_email, c.customer_address
              FROM codes c 
              $where_clause 
              ORDER BY c.created_at DESC";
    $stmt = $pdo->prepare($query);
    // Bind search parameter if exists
    if (!empty($params)) {
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
    }
    $stmt->execute();
    $reward_codes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Export error: " . $e->getMessage());
    die("Error exporting data. Please try again.");
}

// Set headers for Excel download
$filename = 'reward_codes_export_' . date('Y-m-d_His');
if ($filter !== 'all') {
    $filename .= '_' . $filter;
}
if (!empty($search)) {
    $filename .= '_search';
}
$filename .= '.csv';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

// Create output stream
$output = fopen('php://output', 'w');

// Add BOM for UTF-8 (Excel compatibility)
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Add CSV headers
$headers = [
    'ID',
    'Reward Code',
    'Status',
    'Customer Name',
    'Customer Phone',
    'Customer Email',
    'Customer Address',
    'Redeemed At',
    'Created At'
];
fputcsv($output, $headers);

// Add data rows
foreach ($reward_codes as $code) {
    $row = [
        $code['id'],
        $code['reward_code'],
        $code['is_used'] ? 'Redeemed' : 'Available',
        $code['customer_name'] ?? '',
        $code['customer_phone'] ?? '',
        $code['customer_email'] ?? '',
        $code['customer_address'] ?? '',
        $code['used_at'] ? date('d-m-Y H:i:s', strtotime($code['used_at'])) : '',
        date('d-m-Y H:i:s', strtotime($code['created_at']))
    ];
    fputcsv($output, $row);
}

fclose($output);
exit;
