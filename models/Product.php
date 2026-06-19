<?php
/**
 * Product Model - NSBM Marketplace AI
 */

class Product {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll(int $limit = ITEMS_PER_PAGE, int $offset = 0, array $filters = []): array {
        $where = ['p.is_active = 1'];
        $params = [];

        if (!empty($filters['category_id'])) {
            $where[] = 'p.category_id = :category_id';
            $params[':category_id'] = $filters['category_id'];
        }

        if (!empty($filters['search'])) {
            $where[] = 'MATCH(p.name, p.description, p.short_description) AGAINST(:search IN BOOLEAN MODE)';
            $params[':search'] = $filters['search'] . '*';
        }

        if (!empty($filters['min_price'])) {
            $where[] = 'p.price >= :min_price';
            $params[':min_price'] = $filters['min_price'];
        }

        if (!empty($filters['max_price'])) {
            $where[] = 'p.price <= :max_price';
            $params[':max_price'] = $filters['max_price'];
        }

        if (!empty($filters['is_featured'])) {
            $where[] = 'p.is_featured = 1';
        }

        $orderBy = 'p.created_at DESC';
        if (!empty($filters['sort'])) {
            switch ($filters['sort']) {
                case 'price_asc': $orderBy = 'p.price ASC'; break;
                case 'price_desc': $orderBy = 'p.price DESC'; break;
                case 'name_asc': $orderBy = 'p.name ASC'; break;
                case 'rating': $orderBy = 'p.rating DESC'; break;
                case 'popular': $orderBy = 'p.views DESC'; break;
                default: $orderBy = 'p.created_at DESC';
            }
        }

        $whereClause = implode(' AND ', $where);

        $sql = "SELECT p.*, c.name as category_name, c.slug as category_slug 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE {$whereClause} 
                ORDER BY {$orderBy} 
                LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function getCount(array $filters = []): int {
        $where = ['p.is_active = 1'];
        $params = [];

        if (!empty($filters['category_id'])) {
            $where[] = 'p.category_id = :category_id';
            $params[':category_id'] = $filters['category_id'];
        }

        if (!empty($filters['search'])) {
            $where[] = 'MATCH(p.name, p.description, p.short_description) AGAINST(:search IN BOOLEAN MODE)';
            $params[':search'] = $filters['search'] . '*';
        }

        if (!empty($filters['min_price'])) {
            $where[] = 'p.price >= :min_price';
            $params[':min_price'] = $filters['min_price'];
        }

        if (!empty($filters['max_price'])) {
            $where[] = 'p.price <= :max_price';
            $params[':max_price'] = $filters['max_price'];
        }

        $whereClause = implode(' AND ', $where);

        $sql = "SELECT COUNT(*) FROM products p WHERE {$whereClause}";
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    public function getById(int $id): ?array {
        $stmt = $this->db->prepare(
            "SELECT p.*, c.name as category_name, c.slug as category_slug 
             FROM products p 
             LEFT JOIN categories c ON p.category_id = c.id 
             WHERE p.id = :id"
        );
        $stmt->execute([':id' => $id]);
        $product = $stmt->fetch();
        return $product ?: null;
    }

    public function getBySlug(string $slug): ?array {
        $stmt = $this->db->prepare(
            "SELECT p.*, c.name as category_name, c.slug as category_slug 
             FROM products p 
             LEFT JOIN categories c ON p.category_id = c.id 
             WHERE p.slug = :slug AND p.is_active = 1"
        );
        $stmt->execute([':slug' => $slug]);
        $product = $stmt->fetch();
        
        if ($product) {
            // Increment views
            $this->db->prepare("UPDATE products SET views = views + 1 WHERE id = :id")
                     ->execute([':id' => $product['id']]);
        }
        
        return $product ?: null;
    }

    public function getFeatured(int $limit = 8): array {
        $stmt = $this->db->prepare(
            "SELECT p.*, c.name as category_name 
             FROM products p 
             LEFT JOIN categories c ON p.category_id = c.id 
             WHERE p.is_featured = 1 AND p.is_active = 1 
             ORDER BY p.created_at DESC 
             LIMIT :limit"
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getRelated(int $categoryId, int $excludeId, int $limit = 4): array {
        $stmt = $this->db->prepare(
            "SELECT p.*, c.name as category_name 
             FROM products p 
             LEFT JOIN categories c ON p.category_id = c.id 
             WHERE p.category_id = :category_id AND p.id != :exclude_id AND p.is_active = 1 
             ORDER BY RAND() 
             LIMIT :limit"
        );
        $stmt->bindValue(':category_id', $categoryId, PDO::PARAM_INT);
        $stmt->bindValue(':exclude_id', $excludeId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function create(array $data): int {
        $stmt = $this->db->prepare(
            "INSERT INTO products (category_id, name, slug, description, short_description, price, sale_price, sku, stock_quantity, image, is_featured, is_active) 
             VALUES (:category_id, :name, :slug, :description, :short_description, :price, :sale_price, :sku, :stock_quantity, :image, :is_featured, :is_active)"
        );
        $stmt->execute([
            ':category_id' => $data['category_id'],
            ':name' => $data['name'],
            ':slug' => slugify($data['name']),
            ':description' => $data['description'],
            ':short_description' => $data['short_description'] ?? null,
            ':price' => $data['price'],
            ':sale_price' => $data['sale_price'] ?: null,
            ':sku' => $data['sku'] ?? null,
            ':stock_quantity' => $data['stock_quantity'] ?? 0,
            ':image' => $data['image'] ?? null,
            ':is_featured' => $data['is_featured'] ?? 0,
            ':is_active' => $data['is_active'] ?? 1
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool {
        $fields = [];
        $params = [':id' => $id];

        foreach ($data as $key => $value) {
            if (in_array($key, ['category_id', 'name', 'description', 'short_description', 'price', 'sale_price', 'sku', 'stock_quantity', 'image', 'is_featured', 'is_active'])) {
                $fields[] = "{$key} = :{$key}";
                $params[":{$key}"] = $value;
            }
        }

        if (!empty($data['name'])) {
            $fields[] = "slug = :slug";
            $params[':slug'] = slugify($data['name']);
        }

        if (empty($fields)) return false;

        $sql = "UPDATE products SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM products WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function getForAI(): array {
        $stmt = $this->db->query(
            "SELECT p.id, p.name, p.short_description, p.price, p.sale_price, p.rating, p.stock_quantity, c.name as category 
             FROM products p 
             LEFT JOIN categories c ON p.category_id = c.id 
             WHERE p.is_active = 1 AND p.stock_quantity > 0 
             ORDER BY p.rating DESC"
        );
        return $stmt->fetchAll();
    }

    public function getStats(): array {
        $stmt = $this->db->query("SELECT COUNT(*) as total, SUM(stock_quantity) as total_stock, AVG(price) as avg_price FROM products WHERE is_active = 1");
        return $stmt->fetch();
    }
}
