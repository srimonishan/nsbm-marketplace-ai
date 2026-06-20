<?php
/**
 * Category Model - GreenLink Market
 */

class Category {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll(bool $activeOnly = true): array {
        $sql = "SELECT c.*, (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id AND p.is_active = 1) as product_count 
                FROM categories c";
        if ($activeOnly) {
            $sql .= " WHERE c.is_active = 1";
        }
        $sql .= " ORDER BY c.sort_order ASC, c.name ASC";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM categories WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $category = $stmt->fetch();
        return $category ?: null;
    }

    public function getBySlug(string $slug): ?array {
        $stmt = $this->db->prepare("SELECT * FROM categories WHERE slug = :slug AND is_active = 1");
        $stmt->execute([':slug' => $slug]);
        $category = $stmt->fetch();
        return $category ?: null;
    }

    public function create(array $data): int {
        $stmt = $this->db->prepare(
            "INSERT INTO categories (name, slug, description, image, sort_order, is_active) 
             VALUES (:name, :slug, :description, :image, :sort_order, :is_active)"
        );
        $stmt->execute([
            ':name' => $data['name'],
            ':slug' => slugify($data['name']),
            ':description' => $data['description'] ?? null,
            ':image' => $data['image'] ?? null,
            ':sort_order' => $data['sort_order'] ?? 0,
            ':is_active' => $data['is_active'] ?? 1
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool {
        $stmt = $this->db->prepare(
            "UPDATE categories SET name = :name, slug = :slug, description = :description, 
             image = :image, sort_order = :sort_order, is_active = :is_active WHERE id = :id"
        );
        return $stmt->execute([
            ':id' => $id,
            ':name' => $data['name'],
            ':slug' => slugify($data['name']),
            ':description' => $data['description'] ?? null,
            ':image' => $data['image'] ?? null,
            ':sort_order' => $data['sort_order'] ?? 0,
            ':is_active' => $data['is_active'] ?? 1
        ]);
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM categories WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
