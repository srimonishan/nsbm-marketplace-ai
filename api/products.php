<?php
/**
 * Products API - NSBM Marketplace AI
 */

require_once __DIR__ . '/../config/init.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

$method = $_SERVER['REQUEST_METHOD'];
$productModel = new Product();

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            $product = $productModel->getById((int)$_GET['id']);
            if ($product) {
                jsonResponse(['success' => true, 'data' => $product]);
            } else {
                jsonResponse(['success' => false, 'message' => 'Product not found'], 404);
            }
        } elseif (isset($_GET['featured'])) {
            $products = $productModel->getFeatured((int)($_GET['limit'] ?? 8));
            jsonResponse(['success' => true, 'data' => $products]);
        } else {
            $filters = [
                'category_id' => $_GET['category_id'] ?? null,
                'search' => $_GET['search'] ?? null,
                'min_price' => $_GET['min_price'] ?? null,
                'max_price' => $_GET['max_price'] ?? null,
                'sort' => $_GET['sort'] ?? null,
                'is_featured' => $_GET['featured'] ?? null
            ];
            $page = max(1, (int)($_GET['page'] ?? 1));
            $limit = (int)($_GET['limit'] ?? ITEMS_PER_PAGE);
            $offset = ($page - 1) * $limit;

            $products = $productModel->getAll($limit, $offset, $filters);
            $total = $productModel->getCount($filters);

            jsonResponse([
                'success' => true,
                'data' => $products,
                'pagination' => [
                    'total' => $total,
                    'page' => $page,
                    'limit' => $limit,
                    'pages' => ceil($total / $limit)
                ]
            ]);
        }
        break;

    case 'POST':
        if (!isAdmin()) {
            jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        $data = json_decode(file_get_contents('php://input'), true);
        try {
            $id = $productModel->create($data);
            jsonResponse(['success' => true, 'message' => 'Product created', 'id' => $id], 201);
        } catch (Exception $e) {
            jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
        break;

    case 'PUT':
        if (!isAdmin()) {
            jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        $data = json_decode(file_get_contents('php://input'), true);
        $id = (int)($_GET['id'] ?? 0);
        if ($id && $productModel->update($id, $data)) {
            jsonResponse(['success' => true, 'message' => 'Product updated']);
        } else {
            jsonResponse(['success' => false, 'message' => 'Update failed'], 400);
        }
        break;

    case 'DELETE':
        if (!isAdmin()) {
            jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        $id = (int)($_GET['id'] ?? 0);
        if ($id && $productModel->delete($id)) {
            jsonResponse(['success' => true, 'message' => 'Product deleted']);
        } else {
            jsonResponse(['success' => false, 'message' => 'Delete failed'], 400);
        }
        break;

    default:
        jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}
