<?php
/**
 * Admin API - NSBM Marketplace AI
 */

require_once __DIR__ . '/../config/init.php';

header('Content-Type: application/json');

if (!isAdmin()) {
    jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
}

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'dashboard':
        $productModel = new Product();
        $userModel = new User();
        $orderModel = new Order();

        $stats = [
            'total_products' => $productModel->getCount(),
            'total_customers' => $userModel->getCount('customer'),
            'total_orders' => $orderModel->getCount(),
            'total_revenue' => $orderModel->getTotalRevenue(),
            'order_status' => $orderModel->getStatusCounts(),
            'recent_orders' => $orderModel->getRecent(5),
            'recent_customers' => $userModel->getRecent(5),
            'revenue_data' => $orderModel->getRevenue('month')
        ];

        jsonResponse(['success' => true, 'data' => $stats]);
        break;

    case 'customers':
        $userModel = new User();
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = ADMIN_ITEMS_PER_PAGE;
        $offset = ($page - 1) * $limit;
        
        $customers = $userModel->getAll($limit, $offset);
        $total = $userModel->getCount();

        jsonResponse([
            'success' => true,
            'data' => $customers,
            'pagination' => [
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'pages' => ceil($total / $limit)
            ]
        ]);
        break;

    case 'messages':
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT 50");
        jsonResponse(['success' => true, 'data' => $stmt->fetchAll()]);
        break;

    case 'update_customer':
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
            jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
        }
        $data = json_decode(file_get_contents('php://input'), true);
        $id = (int)($_GET['id'] ?? 0);
        $userModel = new User();
        if ($id && $userModel->update($id, $data)) {
            jsonResponse(['success' => true, 'message' => 'Customer updated']);
        } else {
            jsonResponse(['success' => false, 'message' => 'Update failed'], 400);
        }
        break;

    default:
        jsonResponse(['success' => false, 'message' => 'Invalid action'], 400);
}
