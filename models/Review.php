<?php
/**
 * Review Model - NSBM Marketplace AI
 */

class Review {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getByProduct(int $productId, bool $approvedOnly = true): array {
        $sql = "SELECT r.*, u.first_name, u.last_name 
                FROM reviews r 
                LEFT JOIN users u ON r.user_id = u.id 
                WHERE r.product_id = :product_id";
        if ($approvedOnly) {
            $sql .= " AND r.is_approved = 1";
        }
        $sql .= " ORDER BY r.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':product_id' => $productId]);
        return $stmt->fetchAll();
    }

    public function create(array $data): int {
        $stmt = $this->db->prepare(
            "INSERT INTO reviews (product_id, user_id, rating, comment) 
             VALUES (:product_id, :user_id, :rating, :comment)"
        );
        $stmt->execute([
            ':product_id' => $data['product_id'],
            ':user_id' => $data['user_id'],
            ':rating' => $data['rating'],
            ':comment' => $data['comment'] ?? null
        ]);

        // Update product rating
        $this->updateProductRating($data['product_id']);

        return (int) $this->db->lastInsertId();
    }

    private function updateProductRating(int $productId): void {
        $stmt = $this->db->prepare(
            "UPDATE products SET 
             rating = (SELECT AVG(rating) FROM reviews WHERE product_id = :id AND is_approved = 1),
             total_reviews = (SELECT COUNT(*) FROM reviews WHERE product_id = :id2 AND is_approved = 1)
             WHERE id = :id3"
        );
        $stmt->execute([':id' => $productId, ':id2' => $productId, ':id3' => $productId]);
    }

    public function approve(int $id): bool {
        $stmt = $this->db->prepare("UPDATE reviews SET is_approved = 1 WHERE id = :id");
        $result = $stmt->execute([':id' => $id]);
        
        // Get product_id and update rating
        $review = $this->db->prepare("SELECT product_id FROM reviews WHERE id = :id");
        $review->execute([':id' => $id]);
        $row = $review->fetch();
        if ($row) {
            $this->updateProductRating($row['product_id']);
        }
        
        return $result;
    }

    public function delete(int $id): bool {
        $review = $this->db->prepare("SELECT product_id FROM reviews WHERE id = :id");
        $review->execute([':id' => $id]);
        $row = $review->fetch();
        
        $stmt = $this->db->prepare("DELETE FROM reviews WHERE id = :id");
        $result = $stmt->execute([':id' => $id]);
        
        if ($row) {
            $this->updateProductRating($row['product_id']);
        }
        
        return $result;
    }
}
