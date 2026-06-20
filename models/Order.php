<?php
/**
 * Order Model - GreenLink Market
 */

class Order {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll(int $limit = ADMIN_ITEMS_PER_PAGE, int $offset = 0, array $filters = []): array {
        $where = ['1=1'];
        $params = [];

        if (!empty($filters['status'])) {
            $where[] = 'o.status = :status';
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['user_id'])) {
            $where[] = 'o.user_id = :user_id';
            $params[':user_id'] = $filters['user_id'];
        }

        $whereClause = implode(' AND ', $where);

        $stmt = $this->db->prepare(
            "SELECT o.*, u.first_name, u.last_name, u.email 
             FROM orders o 
             LEFT JOIN users u ON o.user_id = u.id 
             WHERE {$whereClause} 
             ORDER BY o.created_at DESC 
             LIMIT :limit OFFSET :offset"
        );
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array {
        $stmt = $this->db->prepare(
            "SELECT o.*, u.first_name, u.last_name, u.email 
             FROM orders o 
             LEFT JOIN users u ON o.user_id = u.id 
             WHERE o.id = :id"
        );
        $stmt->execute([':id' => $id]);
        $order = $stmt->fetch();
        return $order ?: null;
    }

    public function getItems(int $orderId): array {
        $stmt = $this->db->prepare(
            "SELECT oi.*, p.slug as product_slug, p.image as product_image 
             FROM order_items oi 
             LEFT JOIN products p ON oi.product_id = p.id 
             WHERE oi.order_id = :order_id"
        );
        $stmt->execute([':order_id' => $orderId]);
        return $stmt->fetchAll();
    }

    public function create(array $data, array $items): int {
        $this->db->beginTransaction();
        
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO orders (user_id, order_number, subtotal, tax, shipping_fee, discount, total, payment_method, shipping_address, shipping_city, shipping_phone, notes) 
                 VALUES (:user_id, :order_number, :subtotal, :tax, :shipping_fee, :discount, :total, :payment_method, :shipping_address, :shipping_city, :shipping_phone, :notes)"
            );
            $stmt->execute([
                ':user_id' => $data['user_id'],
                ':order_number' => generateOrderNumber(),
                ':subtotal' => $data['subtotal'],
                ':tax' => $data['tax'],
                ':shipping_fee' => $data['shipping_fee'],
                ':discount' => $data['discount'] ?? 0,
                ':total' => $data['total'],
                ':payment_method' => $data['payment_method'] ?? 'card',
                ':shipping_address' => $data['shipping_address'],
                ':shipping_city' => $data['shipping_city'],
                ':shipping_phone' => $data['shipping_phone'],
                ':notes' => $data['notes'] ?? null
            ]);

            $orderId = (int) $this->db->lastInsertId();

            // Insert order items
            $itemStmt = $this->db->prepare(
                "INSERT INTO order_items (order_id, product_id, product_name, product_image, quantity, price, total) 
                 VALUES (:order_id, :product_id, :product_name, :product_image, :quantity, :price, :total)"
            );

            foreach ($items as $item) {
                $itemStmt->execute([
                    ':order_id' => $orderId,
                    ':product_id' => $item['product_id'],
                    ':product_name' => $item['product_name'],
                    ':product_image' => $item['product_image'] ?? null,
                    ':quantity' => $item['quantity'],
                    ':price' => $item['price'],
                    ':total' => $item['price'] * $item['quantity']
                ]);

                // Update stock
                $this->db->prepare("UPDATE products SET stock_quantity = stock_quantity - :qty WHERE id = :id")
                         ->execute([':qty' => $item['quantity'], ':id' => $item['product_id']]);
            }

            $this->db->commit();
            return $orderId;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function updateStatus(int $id, string $status): bool {
        $stmt = $this->db->prepare("UPDATE orders SET status = :status WHERE id = :id");
        return $stmt->execute([':id' => $id, ':status' => $status]);
    }

    public function updatePaymentStatus(int $id, string $status): bool {
        $stmt = $this->db->prepare("UPDATE orders SET payment_status = :status WHERE id = :id");
        return $stmt->execute([':id' => $id, ':status' => $status]);
    }

    public function getCount(array $filters = []): int {
        $where = ['1=1'];
        $params = [];

        if (!empty($filters['status'])) {
            $where[] = 'status = :status';
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['user_id'])) {
            $where[] = 'user_id = :user_id';
            $params[':user_id'] = $filters['user_id'];
        }

        $whereClause = implode(' AND ', $where);
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM orders WHERE {$whereClause}");
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function getRevenue(string $period = 'month'): array {
        switch ($period) {
            case 'week':
                $sql = "SELECT DATE(created_at) as date, SUM(total) as revenue, COUNT(*) as orders 
                        FROM orders WHERE payment_status = 'paid' AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) 
                        GROUP BY DATE(created_at) ORDER BY date";
                break;
            case 'month':
                $sql = "SELECT DATE(created_at) as date, SUM(total) as revenue, COUNT(*) as orders 
                        FROM orders WHERE payment_status = 'paid' AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) 
                        GROUP BY DATE(created_at) ORDER BY date";
                break;
            case 'year':
                $sql = "SELECT DATE_FORMAT(created_at, '%Y-%m') as date, SUM(total) as revenue, COUNT(*) as orders 
                        FROM orders WHERE payment_status = 'paid' AND created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH) 
                        GROUP BY DATE_FORMAT(created_at, '%Y-%m') ORDER BY date";
                break;
            default:
                $sql = "SELECT DATE(created_at) as date, SUM(total) as revenue, COUNT(*) as orders 
                        FROM orders WHERE payment_status = 'paid' 
                        GROUP BY DATE(created_at) ORDER BY date DESC LIMIT 30";
        }

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function getTotalRevenue(): float {
        $stmt = $this->db->query("SELECT COALESCE(SUM(total), 0) FROM orders WHERE payment_status = 'paid'");
        return (float) $stmt->fetchColumn();
    }

    public function getRecent(int $limit = 5): array {
        $stmt = $this->db->prepare(
            "SELECT o.*, u.first_name, u.last_name 
             FROM orders o LEFT JOIN users u ON o.user_id = u.id 
             ORDER BY o.created_at DESC LIMIT :limit"
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getStatusCounts(): array {
        $stmt = $this->db->query(
            "SELECT status, COUNT(*) as count FROM orders GROUP BY status"
        );
        $results = $stmt->fetchAll();
        $counts = [];
        foreach ($results as $row) {
            $counts[$row['status']] = $row['count'];
        }
        return $counts;
    }
}
