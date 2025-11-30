<?php
/**
 * Read Products API
 * Market Place OutFit
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../../classes/Product.php';

$product = new Product();

$id = $_GET['id'] ?? null;
$categoryId = $_GET['category_id'] ?? null;
$search = $_GET['search'] ?? null;

if ($id) {
    $data = $product->findById((int)$id);
    if ($data) {
        echo json_encode(['success' => true, 'product' => $data]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Produk tidak ditemukan']);
    }
} elseif ($categoryId) {
    $data = $product->getAvailableByCategory((int)$categoryId);
    echo json_encode(['success' => true, 'products' => $data]);
} elseif ($search) {
    $data = $product->search($search);
    echo json_encode(['success' => true, 'products' => $data]);
} else {
    $data = $product->getAvailable();
    echo json_encode(['success' => true, 'products' => $data]);
}
