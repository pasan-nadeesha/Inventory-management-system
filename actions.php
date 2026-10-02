<?php
// actions.php - Handles AJAX and form requests for Inventory
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/db.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// Low stock threshold constant
define('LOW_STOCK_THRESHOLD', 5);

// Function to determine stock badge details
function getStockStatus($qty) {
    if ($qty <= 0) {
        return [
            'label' => 'Out of Stock',
            'class' => 'badge-danger',
            'is_low' => true
        ];
    } elseif ($qty <= LOW_STOCK_THRESHOLD) {
        return [
            'label' => 'Low Stock (' . $qty . ')',
            'class' => 'badge-danger',
            'is_low' => true
        ];
    } else {
        return [
            'label' => 'In Stock (' . $qty . ')',
            'class' => 'badge-success',
            'is_low' => false
        ];
    }
}

try {
    if ($action === 'add') {
        $product_name = trim($_POST['product_name'] ?? '');
        $price = floatval($_POST['price'] ?? 0);
        $quantity = intval($_POST['quantity'] ?? 0);

        if (empty($product_name)) {
            echo json_encode(['status' => 'error', 'message' => 'Product name is required.']);
            exit;
        }

        if ($price < 0 || $quantity < 0) {
            echo json_encode(['status' => 'error', 'message' => 'Price and Quantity cannot be negative.']);
            exit;
        }

        $stmt = $pdo->prepare("INSERT INTO inventory (product_name, price, quantity) VALUES (?, ?, ?)");
        $stmt->execute([$product_name, $price, $quantity]);
        $newId = $pdo->lastInsertId();

        $status = getStockStatus($quantity);

        echo json_encode([
            'status' => 'success',
            'message' => 'Product added successfully!',
            'product' => [
                'id' => $newId,
                'product_name' => htmlspecialchars($product_name, ENT_QUOTES, 'UTF-8'),
                'price' => number_format($price, 2),
                'raw_price' => $price,
                'quantity' => $quantity,
                'status' => $status
            ]
        ]);
        exit;
    }

    if ($action === 'update_stock') {
        $id = intval($_POST['id'] ?? 0);
        $change = intval($_POST['change'] ?? 0); // +1 or -1

        if ($id <= 0 || ($change !== 1 && $change !== -1)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid parameters.']);
            exit;
        }

        // Get current quantity
        $stmt = $pdo->prepare("SELECT quantity FROM inventory WHERE id = ?");
        $stmt->execute([$id]);
        $item = $stmt->fetch();

        if (!$item) {
            echo json_encode(['status' => 'error', 'message' => 'Product not found.']);
            exit;
        }

        $new_quantity = $item['quantity'] + $change;
        if ($new_quantity < 0) {
            echo json_encode(['status' => 'error', 'message' => 'Stock cannot be less than 0.']);
            exit;
        }

        $updateStmt = $pdo->prepare("UPDATE inventory SET quantity = ? WHERE id = ?");
        $updateStmt->execute([$new_quantity, $id]);

        $status = getStockStatus($new_quantity);

        echo json_encode([
            'status' => 'success',
            'id' => $id,
            'quantity' => $new_quantity,
            'status_info' => $status
        ]);
        exit;
    }

    if ($action === 'delete') {
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid ID.']);
            exit;
        }

        $stmt = $pdo->prepare("DELETE FROM inventory WHERE id = ?");
        $stmt->execute([$id]);

        echo json_encode(['status' => 'success', 'message' => 'Product deleted.']);
        exit;
    }

    echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
