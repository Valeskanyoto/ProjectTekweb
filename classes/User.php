<?php
/**
 * User Class
 * Market Place OutFit
 */

require_once __DIR__ . '/Database.php';

class User
{
    private Database $db;
    private string $table = 'users';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Register new user
     */
    public function register(array $data): array
    {
        // Validate required fields
        if (empty($data['name']) || empty($data['email']) || empty($data['password'])) {
            return ['success' => false, 'message' => 'Semua field harus diisi'];
        }

        // Validate email format
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Format email tidak valid'];
        }

        // Check if email already exists
        if ($this->findByEmail($data['email'])) {
            return ['success' => false, 'message' => 'Email sudah terdaftar'];
        }

        // Validate password length
        if (strlen($data['password']) < 6) {
            return ['success' => false, 'message' => 'Password minimal 6 karakter'];
        }

        // Hash password
        $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT);

        // Insert user
        $userId = $this->db->insert($this->table, [
            'name' => htmlspecialchars($data['name']),
            'email' => $data['email'],
            'password' => $hashedPassword,
            'role' => 'user',
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null
        ]);

        return [
            'success' => true,
            'message' => 'Registrasi berhasil',
            'user_id' => $userId
        ];
    }

    /**
     * Login user
     */
    public function login(string $email, string $password): array
    {
        // Validate input
        if (empty($email) || empty($password)) {
            return ['success' => false, 'message' => 'Email dan password harus diisi'];
        }

        // Find user by email
        $user = $this->findByEmail($email);
        if (!$user) {
            return ['success' => false, 'message' => 'Email atau password salah'];
        }

        // Verify password
        if (!password_verify($password, $user['password'])) {
            return ['success' => false, 'message' => 'Email atau password salah'];
        }

        // Start session and set user data
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['logged_in'] = true;

        return [
            'success' => true,
            'message' => 'Login berhasil',
            'user' => [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'role' => $user['role']
            ]
        ];
    }

    /**
     * Logout user
     */
    public function logout(): array
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        session_unset();
        session_destroy();

        return ['success' => true, 'message' => 'Logout berhasil'];
    }

    /**
     * Check if user is logged in
     */
    public static function isLoggedIn(): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }

    /**
     * Check if user is admin
     */
    public static function isAdmin(): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    }

    /**
     * Get current logged in user
     */
    public static function getCurrentUser(): ?array
    {
        if (!self::isLoggedIn()) {
            return null;
        }
        return [
            'id' => $_SESSION['user_id'],
            'name' => $_SESSION['user_name'],
            'email' => $_SESSION['user_email'],
            'role' => $_SESSION['user_role']
        ];
    }

    /**
     * Get current user ID
     */
    public static function getCurrentUserId(): ?int
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return $_SESSION['user_id'] ?? null;
    }

    /**
     * Find user by email
     */
    public function findByEmail(string $email): ?array
    {
        return $this->db->selectOne(
            "SELECT * FROM {$this->table} WHERE email = :email",
            ['email' => $email]
        );
    }

    /**
     * Find user by ID
     */
    public function findById(int $id): ?array
    {
        return $this->db->selectOne(
            "SELECT id, name, email, role, phone, address, created_at, updated_at FROM {$this->table} WHERE id = :id",
            ['id' => $id]
        );
    }

    /**
     * Get all users
     */
    public function getAll(): array
    {
        return $this->db->select(
            "SELECT id, name, email, role, phone, address, created_at, updated_at FROM {$this->table} ORDER BY created_at DESC"
        );
    }

    /**
     * Get all users (non-admin)
     */
    public function getAllUsers(): array
    {
        return $this->db->select(
            "SELECT id, name, email, role, phone, address, created_at, updated_at FROM {$this->table} WHERE role = 'user' ORDER BY created_at DESC"
        );
    }

    /**
     * Update user profile
     */
    public function update(int $id, array $data): array
    {
        // Validate required fields
        if (empty($data['name']) || empty($data['email'])) {
            return ['success' => false, 'message' => 'Nama dan email harus diisi'];
        }

        // Check if email is used by other user
        $existingUser = $this->findByEmail($data['email']);
        if ($existingUser && $existingUser['id'] !== $id) {
            return ['success' => false, 'message' => 'Email sudah digunakan'];
        }

        $updateData = [
            'name' => htmlspecialchars($data['name']),
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null
        ];

        // Update role if provided (admin only)
        if (isset($data['role']) && in_array($data['role'], ['admin', 'user'])) {
            $updateData['role'] = $data['role'];
        }

        // Update password if provided
        if (!empty($data['password'])) {
            if (strlen($data['password']) < 6) {
                return ['success' => false, 'message' => 'Password minimal 6 karakter'];
            }
            $updateData['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        }

        $this->db->update($this->table, $updateData, 'id = :id', ['id' => $id]);

        // Update session if current user
        if (self::getCurrentUserId() === $id) {
            $_SESSION['user_name'] = $updateData['name'];
            $_SESSION['user_email'] = $updateData['email'];
            if (isset($updateData['role'])) {
                $_SESSION['user_role'] = $updateData['role'];
            }
        }

        return ['success' => true, 'message' => 'Profil berhasil diperbarui'];
    }

    /**
     * Delete user
     */
    public function delete(int $id): array
    {
        // Prevent deleting self
        if (self::getCurrentUserId() === $id) {
            return ['success' => false, 'message' => 'Tidak bisa menghapus akun sendiri'];
        }

        $user = $this->findById($id);
        if (!$user) {
            return ['success' => false, 'message' => 'User tidak ditemukan'];
        }

        $this->db->delete($this->table, 'id = :id', ['id' => $id]);

        return ['success' => true, 'message' => 'User berhasil dihapus'];
    }

    /**
     * Count all users
     */
    public function count(): int
    {
        $result = $this->db->selectOne("SELECT COUNT(*) as total FROM {$this->table}");
        return (int) $result['total'];
    }

    /**
     * Count users by role
     */
    public function countByRole(string $role): int
    {
        $result = $this->db->selectOne(
            "SELECT COUNT(*) as total FROM {$this->table} WHERE role = :role",
            ['role' => $role]
        );
        return (int) $result['total'];
    }
}
