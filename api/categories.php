<?php
/**
 * Categories API - GreenLink Market
 */

require_once __DIR__ . '/../config/init.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$categoryModel = new Category();

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            $category = $categoryModel->getById((int)$_GET['id']);
            if ($category) {
                jsonResponse(['success' => true, 'data' => $category]);
            } else {
                jsonResponse(['success' => false, 'message' => 'Category not found'], 404);
            }
        } else {
            $activeOnly = !isAdmin() || ($_GET['active_only'] ?? '1') === '1';
            $categories = $categoryModel->getAll($activeOnly);
            jsonResponse(['success' => true, 'data' => $categories]);
        }
        break;

    case 'POST':
        if (!isAdmin()) {
            jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        $data = json_decode(file_get_contents('php://input'), true);
        try {
            $id = $categoryModel->create($data);
            jsonResponse(['success' => true, 'message' => 'Category created', 'id' => $id], 201);
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
        if ($id && $categoryModel->update($id, $data)) {
            jsonResponse(['success' => true, 'message' => 'Category updated']);
        } else {
            jsonResponse(['success' => false, 'message' => 'Update failed'], 400);
        }
        break;

    case 'DELETE':
        if (!isAdmin()) {
            jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        $id = (int)($_GET['id'] ?? 0);
        if ($id && $categoryModel->delete($id)) {
            jsonResponse(['success' => true, 'message' => 'Category deleted']);
        } else {
            jsonResponse(['success' => false, 'message' => 'Delete failed'], 400);
        }
        break;

    default:
        jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}
