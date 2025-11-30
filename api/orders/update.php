<?php
/**
 * Update Order API
 * Market Place OutFit
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../../classes/User.php';
require_once __DIR__ . '/../../classes/Order.php';

// Check admin
if (!User::isLoggedIn() || !User::isAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Akses ditolak']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

$id = $input['id'] ?? null;
$status = $input['status'] ?? null;

if (!$id || !$status) {
    echo json_encode(['success' => false, 'message' => 'ID dan status diperlukan']);
    exit;
}

$order = new Order();
$result = $order->updateStatus((int)$id, $status);
echo json_encode($result);
