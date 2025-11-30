<?php
/**
 * Product Class
 * Market Place OutFit
 */

require_once __DIR__ . '/Database.php';

class Product
{
    private Database $db;
    private string $table = 'products';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get all products
     */
    public function getAll(): array
    {
        return $this->db->select(
            "SELECT p.*, c.name as category_name
             FROM {$this->table} p
             LEFT JOIN categories c ON p.category_id = c.id
             ORDER BY p.created_at DESC"
        );
    }

    /**
     * Get products by category
     */
    public function getByCategory(int $categoryId): array
    {
        return $this->db->select(
            "SELECT p.*, c.name as category_name
             FROM {$this->table} p
             LEFT JOIN categories c ON p.category_id = c.id
             WHERE p.category_id = :category_id
             ORDER BY p.created_at DESC",
            ['category_id' => $categoryId]
        );
    }

    /**
     * Get products with stock > 0
     */
    public function getAvailable(): array
    {
        return $this->db->select(
            "SELECT p.*, c.name as category_name
             FROM {$this->table} p
             LEFT JOIN categories c ON p.category_id = c.id
             WHERE p.stock > 0
             ORDER BY p.created_at DESC"
        );
    }

    /**
     * Get available products by category
     */
    public function getAvailableByCategory(int $categoryId): array
    {
        return $this->db->select(
            "SELECT p.*, c.name as category_name
             FROM {$this->table} p
             LEFT JOIN categories c ON p.category_id = c.id
             WHERE p.category_id = :category_id AND p.stock > 0
             ORDER BY p.created_at DESC",
            ['category_id' => $categoryId]
        );
    }

    /**
     * Search products
     */
    public function search(string $keyword): array
    {
        $keyword = '%' . $keyword . '%';
        return $this->db->select(
            "SELECT p.*, c.name as category_name
             FROM {$this->table} p
             LEFT JOIN categories c ON p.category_id = c.id
             WHERE p.name LIKE :keyword OR p.description LIKE :keyword2
             ORDER BY p.created_at DESC",
            ['keyword' => $keyword, 'keyword2' => $keyword]
        );
    }

    /**
     * Find product by ID
     */
    public function findById(int $id): ?array
    {
        return $this->db->selectOne(
            "SELECT p.*, c.name as category_name
             FROM {$this->table} p
             LEFT JOIN categories c ON p.category_id = c.id
             WHERE p.id = :id",
            ['id' => $id]
        );
    }

    /**
     * Create new product
     */
    public function create(array $data): array
    {
        // Validate required fields
        if (empty($data['name']) || empty($data['category_id']) || !isset($data['price'])) {
            return ['success' => false, 'message' => 'Nama, kategori, dan harga harus diisi'];
        }

        // Validate price
        if (!is_numeric($data['price']) || $data['price'] < 0) {
            return ['success' => false, 'message' => 'Harga harus berupa angka positif'];
        }

        // Validate stock
        $stock = isset($data['stock']) ? (int)$data['stock'] : 0;
        if ($stock < 0) {
            return ['success' => false, 'message' => 'Stok tidak boleh negatif'];
        }

        // Handle image upload
        $imageName = null;
        if (isset($data['image']) && $data['image']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = $this->uploadImage($data['image']);
            if (!$uploadResult['success']) {
                return $uploadResult;
            }
            $imageName = $uploadResult['filename'];
        }

        $productId = $this->db->insert($this->table, [
            'category_id' => (int)$data['category_id'],
            'name' => htmlspecialchars($data['name']),
            'description' => $data['description'] ?? null,
            'price' => (float)$data['price'],
            'stock' => $stock,
            'image' => $imageName
        ]);

        return [
            'success' => true,
            'message' => 'Produk berhasil ditambahkan',
            'product_id' => $productId
        ];
    }

    /**
     * Update product
     */
    public function update(int $id, array $data): array
    {
        // Check if product exists
        $product = $this->findById($id);
        if (!$product) {
            return ['success' => false, 'message' => 'Produk tidak ditemukan'];
        }

        // Validate required fields
        if (empty($data['name']) || empty($data['category_id']) || !isset($data['price'])) {
            return ['success' => false, 'message' => 'Nama, kategori, dan harga harus diisi'];
        }

        // Validate price
        if (!is_numeric($data['price']) || $data['price'] < 0) {
            return ['success' => false, 'message' => 'Harga harus berupa angka positif'];
        }

        // Validate stock
        $stock = isset($data['stock']) ? (int)$data['stock'] : 0;
        if ($stock < 0) {
            return ['success' => false, 'message' => 'Stok tidak boleh negatif'];
        }

        $updateData = [
            'category_id' => (int)$data['category_id'],
            'name' => htmlspecialchars($data['name']),
            'description' => $data['description'] ?? null,
            'price' => (float)$data['price'],
            'stock' => $stock
        ];

        // Handle image upload
        if (isset($data['image']) && $data['image']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = $this->uploadImage($data['image']);
            if (!$uploadResult['success']) {
                return $uploadResult;
            }
            // Delete old image
            if ($product['image']) {
                $this->deleteImage($product['image']);
            }
            $updateData['image'] = $uploadResult['filename'];
        }

        $this->db->update($this->table, $updateData, 'id = :id', ['id' => $id]);

        return ['success' => true, 'message' => 'Produk berhasil diperbarui'];
    }

    /**
     * Delete product
     */
    public function delete(int $id): array
    {
        $product = $this->findById($id);
        if (!$product) {
            return ['success' => false, 'message' => 'Produk tidak ditemukan'];
        }

        // Delete image if exists
        if ($product['image']) {
            $this->deleteImage($product['image']);
        }

        $this->db->delete($this->table, 'id = :id', ['id' => $id]);

        return ['success' => true, 'message' => 'Produk berhasil dihapus'];
    }

    /**
     * Update stock
     */
    public function updateStock(int $id, int $quantity): bool
    {
        $product = $this->findById($id);
        if (!$product) {
            return false;
        }

        $newStock = $product['stock'] + $quantity;
        if ($newStock < 0) {
            return false;
        }

        $this->db->update($this->table, ['stock' => $newStock], 'id = :id', ['id' => $id]);
        return true;
    }

    /**
     * Check stock availability
     */
    public function checkStock(int $id, int $quantity): bool
    {
        $product = $this->findById($id);
        return $product && $product['stock'] >= $quantity;
    }

    /**
     * Upload product image
     */
    private function uploadImage(array $file): array
    {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize = 2 * 1024 * 1024; // 2MB

        // Validate file type
        if (!in_array($file['type'], $allowedTypes)) {
            return ['success' => false, 'message' => 'Format gambar tidak valid (JPG, PNG, GIF, WEBP)'];
        }

        // Validate file size
        if ($file['size'] > $maxSize) {
            return ['success' => false, 'message' => 'Ukuran gambar maksimal 2MB'];
        }

        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('product_') . '.' . $extension;
        $uploadPath = __DIR__ . '/../assets/images/products/' . $filename;

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
            return ['success' => false, 'message' => 'Gagal mengupload gambar'];
        }

        return ['success' => true, 'filename' => $filename];
    }

    /**
     * Delete product image
     */
    private function deleteImage(string $filename): void
    {
        $filepath = __DIR__ . '/../assets/images/products/' . $filename;
        if (file_exists($filepath)) {
            unlink($filepath);
        }
    }

    /**
     * Count all products
     */
    public function count(): int
    {
        $result = $this->db->selectOne("SELECT COUNT(*) as total FROM {$this->table}");
        return (int) $result['total'];
    }

    /**
     * Get low stock products
     */
    public function getLowStock(int $threshold = 10): array
    {
        return $this->db->select(
            "SELECT p.*, c.name as category_name
             FROM {$this->table} p
             LEFT JOIN categories c ON p.category_id = c.id
             WHERE p.stock <= :threshold
             ORDER BY p.stock ASC",
            ['threshold' => $threshold]
        );
    }
}
