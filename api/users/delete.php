<?php
/**
 * Delete User API
 * Market Place OutFit
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../../classes/User.php';

// Check admin
if (!User::isLoggedIn() || !User::isAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Akses ditolak']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$id = $input['id'] ?? null;

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'ID user diperlukan']);
    exit;
}

$user = new User();
$result = $user->delete((int)$id);
echo json_encode($result);
