<?php
/**
 * Read Users API
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

$user = new User();
$id = $_GET['id'] ?? null;

if ($id) {
    // Only admin can view other users
    if (!User::isAdmin() && (int)$id !== User::getCurrentUserId()) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Akses ditolak']);
        exit;
    }

    $userData = $user->findById((int)$id);
    if ($userData) {
        echo json_encode(['success' => true, 'user' => $userData]);
    } else {
        echo json_encode(['success' => false, 'message' => 'User tidak ditemukan']);
    }
} else {
    // Only admin can list all users
    if (!User::isAdmin()) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Akses ditolak']);
        exit;
    }

    $users = $user->getAll();
    echo json_encode(['success' => true, 'users' => $users]);
}
