<?php
/**
 * Create Order API
 * Market Place OutFit
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../../classes/User.php';
require_once __DIR__ . '/../../classes/Order.php';

// Check login
if (!User::isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Silakan login terlebih dahulu']);
    exit;
}

$userId = User::getCurrentUserId();
$input = json_decode(file_get_contents('php://input'), true);

$shippingAddress = $input['shipping_address'] ?? '';

if (empty($shippingAddress)) {
    echo json_encode(['success' => false, 'message' => 'Alamat pengiriman diperlukan']);
    exit;
}

$order = new Order();
$result = $order->createFromCart($userId, $shippingAddress);
echo json_encode($result);
