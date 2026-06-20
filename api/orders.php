<?php
/**
 * Orders API - GreenLink Market
 */

require_once __DIR__ . '/../config/init.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$orderModel = new Order();

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            $order = $orderModel->getById((int)$_GET['id']);
            if ($order) {
                $order['items'] = $orderModel->getItems($order['id']);
                jsonResponse(['success' => true, 'data' => $order]);
            } else {
                jsonResponse(['success' => false, 'message' => 'Order not found'], 404);
            }
        } else {
            $filters = [];
            if (!isAdmin() && isLoggedIn()) {
                $filters['user_id'] = $_SESSION['user_id'];
            }
            if (isset($_GET['status'])) {
                $filters['status'] = $_GET['status'];
            }
            
            $page = max(1, (int)($_GET['page'] ?? 1));
            $limit = (int)($_GET['limit'] ?? ADMIN_ITEMS_PER_PAGE);
            $offset = ($page - 1) * $limit;

            $orders = $orderModel->getAll($limit, $offset, $filters);
            $total = $orderModel->getCount($filters);

            jsonResponse([
                'success' => true,
                'data' => $orders,
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
        if (!isLoggedIn()) {
            jsonResponse(['success' => false, 'message' => 'Please login to place an order'], 401);
        }

        $data = json_decode(file_get_contents('php://input'), true);
        
        if (empty($data['items']) || empty($data['shipping_address']) || empty($data['shipping_city']) || empty($data['shipping_phone'])) {
            jsonResponse(['success' => false, 'message' => 'Missing required fields'], 400);
        }

        $productModel = new Product();
        $subtotal = 0;
        $orderItems = [];

        foreach ($data['items'] as $item) {
            $product = $productModel->getById((int)$item['product_id']);
            if (!$product) {
                jsonResponse(['success' => false, 'message' => 'Product not found: ' . $item['product_id']], 400);
            }
            if ($product['stock_quantity'] < $item['quantity']) {
                jsonResponse(['success' => false, 'message' => 'Insufficient stock for: ' . $product['name']], 400);
            }

            $price = $product['sale_price'] ?: $product['price'];
            $subtotal += $price * $item['quantity'];
            
            $orderItems[] = [
                'product_id' => $product['id'],
                'product_name' => $product['name'],
                'product_image' => $product['image'],
                'quantity' => $item['quantity'],
                'price' => $price
            ];
        }

        $tax = $subtotal * TAX_RATE;
        $shippingFee = $subtotal >= FREE_SHIPPING_THRESHOLD ? 0 : SHIPPING_FEE;
        $total = $subtotal + $tax + $shippingFee;

        try {
            $orderId = $orderModel->create([
                'user_id' => $_SESSION['user_id'],
                'subtotal' => $subtotal,
                'tax' => $tax,
                'shipping_fee' => $shippingFee,
                'total' => $total,
                'payment_method' => $data['payment_method'] ?? 'card',
                'shipping_address' => $data['shipping_address'],
                'shipping_city' => $data['shipping_city'],
                'shipping_phone' => $data['shipping_phone'],
                'notes' => $data['notes'] ?? null
            ], $orderItems);

            // Simulate payment success
            $orderModel->updatePaymentStatus($orderId, 'paid');
            $orderModel->updateStatus($orderId, 'processing');

            jsonResponse([
                'success' => true,
                'message' => 'Order placed successfully!',
                'order_id' => $orderId
            ], 201);
        } catch (Exception $e) {
            jsonResponse(['success' => false, 'message' => 'Order creation failed'], 500);
        }
        break;

    case 'PUT':
        if (!isAdmin()) {
            jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        $id = (int)($_GET['id'] ?? 0);
        
        if (!empty($data['status'])) {
            $orderModel->updateStatus($id, $data['status']);
        }
        if (!empty($data['payment_status'])) {
            $orderModel->updatePaymentStatus($id, $data['payment_status']);
        }
        
        jsonResponse(['success' => true, 'message' => 'Order updated']);
        break;

    default:
        jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}
