<?php
/**
 * User Model - GreenLink Market
 */

class User {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll(int $limit = ADMIN_ITEMS_PER_PAGE, int $offset = 0): array {
        $stmt = $this->db->prepare(
            "SELECT id, first_name, last_name, email, phone, role, status, created_at 
             FROM users ORDER BY created_at DESC LIMIT :limit OFFSET :offset"
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array {
        $stmt = $this->db->prepare(
            "SELECT id, first_name, last_name, email, phone, address, city, avatar, role, status, created_at 
             FROM users WHERE id = :id"
        );
        $stmt->execute([':id' => $id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function getByEmail(string $email): ?array {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function create(array $data): int {
        $stmt = $this->db->prepare(
            "INSERT INTO users (first_name, last_name, email, password, phone, role, status, email_verified_at) 
             VALUES (:first_name, :last_name, :email, :password, :phone, :role, :status, NOW())"
        );
        $stmt->execute([
            ':first_name' => $data['first_name'],
            ':last_name' => $data['last_name'],
            ':email' => $data['email'],
            ':password' => password_hash($data['password'], PASSWORD_DEFAULT),
            ':phone' => $data['phone'] ?? null,
            ':role' => $data['role'] ?? 'customer',
            ':status' => 'active'
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool {
        $fields = [];
        $params = [':id' => $id];

        foreach ($data as $key => $value) {
            if (in_array($key, ['first_name', 'last_name', 'email', 'phone', 'address', 'city', 'avatar', 'status'])) {
                $fields[] = "{$key} = :{$key}";
                $params[":{$key}"] = $value;
            }
        }

        if (!empty($data['password'])) {
            $fields[] = "password = :password";
            $params[':password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        if (empty($fields)) return false;

        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = :id AND role != 'admin'");
        return $stmt->execute([':id' => $id]);
    }

    public function authenticate(string $email, string $password): ?array {
        $user = $this->getByEmail($email);
        if ($user && password_verify($password, $user['password'])) {
            if ($user['status'] !== 'active') {
                return null;
            }
            return $user;
        }
        return null;
    }

    public function getCount(string $role = ''): int {
        $sql = "SELECT COUNT(*) FROM users";
        $params = [];
        if ($role) {
            $sql .= " WHERE role = :role";
            $params[':role'] = $role;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function getRecent(int $limit = 5): array {
        $stmt = $this->db->prepare(
            "SELECT id, first_name, last_name, email, created_at FROM users 
             WHERE role = 'customer' ORDER BY created_at DESC LIMIT :limit"
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
