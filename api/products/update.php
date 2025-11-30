<?php
/**
 * Update Product API
 * Market Place OutFit
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../../classes/User.php';
require_once __DIR__ . '/../../classes/Product.php';

// Check admin
if (!User::isLoggedIn() || !User::isAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Akses ditolak']);
    exit;
}

$product = new Product();

$id = $_POST['id'] ?? null;
if (!$id) {
    echo json_encode(['success' => false, 'message' => 'ID produk diperlukan']);
    exit;
}

$data = [
    'category_id' => $_POST['category_id'] ?? '',
    'name' => $_POST['name'] ?? '',
    'description' => $_POST['description'] ?? '',
    'price' => $_POST['price'] ?? 0,
    'stock' => $_POST['stock'] ?? 0
];

if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $data['image'] = $_FILES['image'];
}

$result = $product->update((int)$id, $data);
echo json_encode($result);
