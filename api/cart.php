<?php
/**
 * Cart API - GreenLink Market
 * Cart is managed client-side via localStorage, this validates items
 */

require_once __DIR__ . '/../config/init.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $action = $data['action'] ?? '';

    switch ($action) {
        case 'validate':
            // Validate cart items and return current prices/stock
            $items = $data['items'] ?? [];
            $productModel = new Product();
            $validatedItems = [];
            $subtotal = 0;

            foreach ($items as $item) {
                $product = $productModel->getById((int)$item['id']);
                if ($product && $product['is_active'] && $product['stock_quantity'] > 0) {
                    $price = $product['sale_price'] ?: $product['price'];
                    $qty = min((int)$item['quantity'], $product['stock_quantity']);
                    $validatedItems[] = [
                        'id' => $product['id'],
                        'name' => $product['name'],
                        'price' => $price,
                        'original_price' => $product['price'],
                        'image' => $product['image'],
                        'quantity' => $qty,
                        'stock' => $product['stock_quantity'],
                        'total' => $price * $qty
                    ];
                    $subtotal += $price * $qty;
                }
            }

            $tax = $subtotal * TAX_RATE;
            $shipping = $subtotal >= FREE_SHIPPING_THRESHOLD ? 0 : SHIPPING_FEE;
            $total = $subtotal + $tax + $shipping;

            jsonResponse([
                'success' => true,
                'data' => [
                    'items' => $validatedItems,
                    'subtotal' => $subtotal,
                    'tax' => $tax,
                    'shipping' => $shipping,
                    'total' => $total,
                    'free_shipping_threshold' => FREE_SHIPPING_THRESHOLD
                ]
            ]);
            break;

        default:
            jsonResponse(['success' => false, 'message' => 'Invalid action'], 400);
    }
} else {
    jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}
