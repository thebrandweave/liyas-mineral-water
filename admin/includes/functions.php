<?php

// Set timezone for consistent date/time handling across the admin panel
date_default_timezone_set('Asia/Kolkata');

/**
 * Convert database timestamp to IST format
 */
function formatIST($dbTimestamp) {
	if (empty($dbTimestamp)) return '';
	try {
		$dt = new DateTime($dbTimestamp);
		$dt->setTimezone(new DateTimeZone('Asia/Kolkata'));
		return $dt->format('d-m-Y H:i:s');
	} catch (Exception $e) {
		return date('d-m-Y H:i:s', strtotime($dbTimestamp));
	}
}

/**
 * Format currency
 */
function formatCurrency($amount) {
	return '₹' . number_format((float)$amount, 2);
}

/**
 * Build WHERE clause + params for orders based on filter/search
 * @param string $filter
 * @param string $search
 * @param array $params Reference to an array for PDO bind values
 * @return string WHERE clause or empty string if no conditions
 */
function buildOrderFilterWhereClause($filter, $search, &$params) {
	$where_conditions = [];
	$params = [];

	if ($filter !== 'all' && in_array($filter, ['pending', 'processing', 'shipped', 'delivered', 'cancelled'])) {
		$where_conditions[] = "o.status = :status";
		$params[':status'] = $filter;
	}

	if (!empty($search)) {
		// Search across customer name, email, phone, and order ID
		$where_conditions[] = "(u.name LIKE :search OR u.email LIKE :search OR sa.phone_number LIKE :search OR o.order_id = :order_id_search)";
		$params[':search'] = "%$search%";
		// Cast search term to int for direct order_id match, use -1 if not numeric to avoid matching 0
		$params[':order_id_search'] = is_numeric($search) ? (int)$search : -1;
	}

	return !empty($where_conditions)
		? "WHERE " . implode(" AND ", $where_conditions)
		: "";
}

/**
 * Bind params to prepared statement
 * @param PDOStatement $stmt
 * @param array $params
 */
function bindFilterParams(PDOStatement $stmt, array $params) {
	foreach ($params as $key => $value) {
		// Use special handling for order_id_search if it's an integer
		if ($key === ':order_id_search' && is_int($value)) {
			$stmt->bindValue($key, $value, PDO::PARAM_INT);
		} else {
			$stmt->bindValue($key, $value);
		}
	}
}

/**
 * Build redirect URL preserving filter/search/page
 * @param string $filter
 * @param string $search
 * @param int $page
 * @param int|null $updated
 * @return string
 */
function buildOrderRedirectUrl($filter, $search, $page = 1, $updated = null) {
	$redirect_url = "index.php?filter=" . urlencode($filter);
	if (!empty($search)) {
		$redirect_url .= "&search=" . urlencode($search);
	}
	if ($page > 1) {
		$redirect_url .= "&page=" . (int)$page;
	}
	if ($updated !== null) {
		$redirect_url .= "&updated=" . (int)$updated;
	}
	return $redirect_url;
}

// Function for logging activities (if activity_logger.php is included later)
// Example placeholder, actual implementation might vary based on activity_logger.php
/*
function logActivity(PDO $pdo, $admin_id, $username, $action_type, $target_type, $target_id, $description) {
    // This function definition would typically come from activity_logger.php
    // For now, it's just a placeholder to avoid undefined function errors
}
*/

?>