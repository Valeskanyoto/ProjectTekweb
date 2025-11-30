<?php
/**
 * Create User API
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

$user = new User();
$result = $user->register([
    'name' => $input['name'] ?? '',
    'email' => $input['email'] ?? '',
    'password' => $input['password'] ?? '',
    'phone' => $input['phone'] ?? '',
    'address' => $input['address'] ?? ''
]);

// If created and role specified, update role
if ($result['success'] && isset($input['role']) && $input['role'] === 'admin') {
    $user->update($result['user_id'], ['name' => $input['name'], 'email' => $input['email'], 'role' => 'admin']);
}

echo json_encode($result);
