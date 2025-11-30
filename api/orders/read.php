<?php
/**
 * Read Orders API
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

$order = new Order();
$id = $_GET['id'] ?? null;

if ($id) {
    $orderData = $order->findById((int)$id);

    // Check ownership or admin
    if (!$orderData) {
        echo json_encode(['success' => false, 'message' => 'Pesanan tidak ditemukan']);
        exit;
    }

    if (!User::isAdmin() && $orderData['user_id'] !== User::getCurrentUserId()) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Akses ditolak']);
        exit;
    }

    $items = $order->getItems((int)$id);
    echo json_encode([
        'success' => true,
        'order' => $orderData,
        'items' => $items
    ]);
} else {
    if (User::isAdmin()) {
        $orders = $order->getAll();
    } else {
        $orders = $order->getByUser(User::getCurrentUserId());
    }
    echo json_encode(['success' => true, 'orders' => $orders]);
}
