<?php
/**
 * Update User API
 * Market Place OutFit
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../../classes/User.php';

// Check login
if (!User::isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Silakan login terlebih dahulu']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$id = $input['id'] ?? User::getCurrentUserId();

// Only admin can update other users
if (!User::isAdmin() && (int)$id !== User::getCurrentUserId()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Akses ditolak']);
    exit;
}

// Only admin can change roles
if (!User::isAdmin() && isset($input['role'])) {
    unset($input['role']);
}

$user = new User();
$result = $user->update((int)$id, $input);
echo json_encode($result);
