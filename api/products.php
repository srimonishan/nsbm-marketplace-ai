<?php
/**
 * Products API - GreenLink Market
 */

require_once __DIR__ . '/../config/init.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

$method = $_SERVER['REQUEST_METHOD'];
if ($method === 'POST' && ($_POST['_method'] ?? '') === 'PUT') {
    $method = 'PUT';
}
$productModel = new Product();

function getProductRequestData(): array {
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    if (str_contains(strtolower($contentType), 'multipart/form-data')) {
        return $_POST;
    }
    return json_decode(file_get_contents('php://input'), true) ?: [];
}

function applyProductImage(array $data, bool $isUpdate): array {
    if (!empty($data['remove_image'])) {
        $data['image'] = null;
        return $data;
    }

    $file = $_FILES['image_file'] ?? null;
    if ($file && $file['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Image upload failed. Please try another file.');
        }
        if ((int) $file['size'] > MAX_PRODUCT_IMAGE_SIZE) {
            throw new RuntimeException('Image must be 5 MB or smaller.');
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        $extensions = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp'
        ];
        if (!isset($extensions[$mime]) || @getimagesize($file['tmp_name']) === false) {
            throw new RuntimeException('Only valid JPG, PNG, and WebP images are allowed.');
        }

        $directory = UPLOAD_PATH . 'products/';
        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            throw new RuntimeException('Product image directory is unavailable.');
        }

        $filename = bin2hex(random_bytes(16)) . '.' . $extensions[$mime];
        if (!move_uploaded_file($file['tmp_name'], $directory . $filename)) {
            throw new RuntimeException('Could not save the uploaded image.');
        }
        chmod($directory . $filename, 0644);
        $data['image'] = 'assets/uploads/products/' . $filename;
        return $data;
    }

    $imageUrl = trim((string) ($data['image_url'] ?? ''));
    if ($imageUrl !== '') {
        if (!filter_var($imageUrl, FILTER_VALIDATE_URL)
            || !in_array(strtolower((string) parse_url($imageUrl, PHP_URL_SCHEME)), ['http', 'https'], true)) {
            throw new RuntimeException('Enter a valid HTTP or HTTPS image URL.');
        }
        $data['image'] = $imageUrl;
    } elseif ($isUpdate) {
        unset($data['image']);
    } else {
        $data['image'] = null;
    }

    unset($data['image_url'], $data['image_file'], $data['_method'], $data['remove_image']);
    return $data;
}

function decorateProductImage(array $product): array {
    $product['image_url'] = productImageUrl($product['image'] ?? null);
    return $product;
}

function deleteUploadedProductImage(?string $image): void {
    $image = ltrim(str_replace('\\', '/', (string) $image), '/');
    if (!str_starts_with($image, 'assets/uploads/products/')) return;

    $directory = realpath(UPLOAD_PATH . 'products');
    $file = realpath(APP_ROOT . '/' . $image);
    if ($directory && $file && dirname($file) === $directory && is_file($file)) {
        unlink($file);
    }
}

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            $product = $productModel->getById((int)$_GET['id']);
            if ($product) {
                jsonResponse(['success' => true, 'data' => decorateProductImage($product)]);
            } else {
                jsonResponse(['success' => false, 'message' => 'Product not found'], 404);
            }
        } elseif (isset($_GET['featured'])) {
            $products = $productModel->getFeatured((int)($_GET['limit'] ?? 8));
            $products = array_map('decorateProductImage', $products);
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
            $products = array_map('decorateProductImage', $products);
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
        $data = getProductRequestData();
        try {
            $data = applyProductImage($data, false);
            $id = $productModel->create($data);
            jsonResponse(['success' => true, 'message' => 'Product created', 'id' => $id], 201);
        } catch (Exception $e) {
            deleteUploadedProductImage($data['image'] ?? null);
            jsonResponse(['success' => false, 'message' => $e->getMessage()], 400);
        }
        break;

    case 'PUT':
        if (!isAdmin()) {
            jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        $data = getProductRequestData();
        $id = (int)($_GET['id'] ?? 0);
        try {
            $existingProduct = $id ? $productModel->getById($id) : null;
            $data = applyProductImage($data, true);
            if ($id && $productModel->update($id, $data)) {
                if (array_key_exists('image', $data)
                    && ($existingProduct['image'] ?? null) !== $data['image']) {
                    deleteUploadedProductImage($existingProduct['image'] ?? null);
                }
                jsonResponse(['success' => true, 'message' => 'Product updated']);
            }
            jsonResponse(['success' => false, 'message' => 'Update failed'], 400);
        } catch (Exception $e) {
            if (isset($data['image']) && ($existingProduct['image'] ?? null) !== $data['image']) {
                deleteUploadedProductImage($data['image']);
            }
            jsonResponse(['success' => false, 'message' => $e->getMessage()], 400);
        }
        break;

    case 'DELETE':
        if (!isAdmin()) {
            jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        $id = (int)($_GET['id'] ?? 0);
        $existingProduct = $id ? $productModel->getById($id) : null;
        if ($id && $productModel->delete($id)) {
            deleteUploadedProductImage($existingProduct['image'] ?? null);
            jsonResponse(['success' => true, 'message' => 'Product deleted']);
        } else {
            jsonResponse(['success' => false, 'message' => 'Delete failed'], 400);
        }
        break;

    default:
        jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}
