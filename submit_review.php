<?php
// Set content type to JSON and start the session
header('Content-Type: application/json');
require_once 'config/config.php';

// Initialize response
$response = [
    'success' => false,
    'message' => 'An unknown error occurred.'
];

// 1. Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'You must be logged in to submit a review.';
    echo json_encode($response);
    exit;
}

// 2. Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Invalid request method.';
    echo json_encode($response);
    exit;
}

// 3. Get and validate inputs
$user_id = $_SESSION['user_id'];
$product_id = isset($_POST['product_id']) ? filter_var($_POST['product_id'], FILTER_VALIDATE_INT) : null;
$rating = isset($_POST['rating']) ? filter_var($_POST['rating'], FILTER_VALIDATE_INT) : null;
$review_text = isset($_POST['review_text']) ? trim($_POST['review_text']) : null;

if (!$product_id) {
    $response['message'] = 'Invalid product specified.';
    echo json_encode($response);
    exit;
}

if (!$rating || $rating < 1 || $rating > 5) {
    $response['message'] = 'Please select a rating between 1 and 5.';
    echo json_encode($response);
    exit;
}

if (empty($review_text)) {
    $response['message'] = 'Please enter your review text.';
    echo json_encode($response);
    exit;
}

// 4. Insert into the database
try {
    $sql = "INSERT INTO reviews (product_id, user_id, rating, review_text, status) VALUES (?, ?, ?, ?, 'pending')";
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute([$product_id, $user_id, $rating, $review_text])) {
        $response['success'] = true;
        $response['message'] = 'Thank you! Your review has been submitted for approval.';
    } else {
        $response['message'] = 'Failed to save your review. Please try again.';
    }

} catch (PDOException $e) {
    // Check for duplicate entry error (user reviewing the same product twice)
    if ($e->getCode() == '23000') { // Integrity constraint violation
        $response['message'] = 'You have already submitted a review for this product.';
    } else {
        $response['message'] = 'A database error occurred. Please try again later.';
        // In a production app, you should log the actual error message ($e->getMessage())
    }
}

// 5. Return the JSON response
echo json_encode($response);
?>
