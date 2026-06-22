<?php
/**
 * Customer Notification Model
 */

class Notification {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function createOrderStatusNotification(array $order, string $status): int {
        $labels = [
            'pending' => ['Order received', 'Your order is pending confirmation.'],
            'processing' => ['Order processing', 'Your order is now being prepared.'],
            'shipped' => ['Order shipped', 'Your order has been shipped and is on its way.'],
            'delivered' => ['Order delivered', 'Your order has been marked as delivered.'],
            'cancelled' => ['Order cancelled', 'Your order has been cancelled.']
        ];
        [$title, $message] = $labels[$status] ?? ['Order updated', 'The status of your order has changed.'];

        $stmt = $this->db->prepare(
            'INSERT INTO notifications (user_id, order_id, type, title, message)
             VALUES (:user_id, :order_id, :type, :title, :message)'
        );
        $stmt->execute([
            ':user_id' => $order['user_id'],
            ':order_id' => $order['id'],
            ':type' => 'order_status',
            ':title' => $title,
            ':message' => $message . ' Order ' . $order['order_number']
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function getForUser(int $userId, int $limit = 20): array {
        $stmt = $this->db->prepare(
            'SELECT id, order_id, type, title, message, is_read, created_at
             FROM notifications
             WHERE user_id = :user_id
             ORDER BY created_at DESC, id DESC
             LIMIT :limit'
        );
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getUnreadCount(int $userId): int {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM notifications WHERE user_id = :user_id AND is_read = 0'
        );
        $stmt->execute([':user_id' => $userId]);
        return (int) $stmt->fetchColumn();
    }

    public function markRead(int $id, int $userId): bool {
        $stmt = $this->db->prepare(
            'UPDATE notifications SET is_read = 1 WHERE id = :id AND user_id = :user_id'
        );
        $stmt->execute([':id' => $id, ':user_id' => $userId]);
        return $stmt->rowCount() > 0;
    }

    public function markAllRead(int $userId): bool {
        $stmt = $this->db->prepare(
            'UPDATE notifications SET is_read = 1 WHERE user_id = :user_id AND is_read = 0'
        );
        return $stmt->execute([':user_id' => $userId]);
    }
}
