<?php
require_once __DIR__ . '/db.php';

// Fetch products from database
$stmt = $pdo->query("SELECT * FROM inventory ORDER BY id DESC");
$products = $stmt->fetchAll();

// Threshold for Low Stock
$lowStockThreshold = 5;

// Count stats
$totalItems = count($products);
$lowStockItems = 0;
foreach ($products as $p) {
    if ($p['quantity'] <= $lowStockThreshold) {
        $lowStockItems++;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Inventory & Stock Counter</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
    <!-- Header -->
    <header class="header">
        <div>
            <h1>📦 Product Inventory & Stock Counter</h1>
            <p>සාප්පුවේ භාණ්ඩ සහ Stock ප්‍රමාණය පහසුවෙන්ම කළමනාකරණය කරන්න</p>
        </div>
        <button type="button" class="btn btn-primary" onclick="openModal()">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            Add New Product
        </button>
    </header>

    <!-- Stats Summary Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <span>Total Items</span>
            <strong id="totalProductsCount"><?= $totalItems ?></strong>
        </div>
        <div class="stat-card alert">
            <span>Low Stock / Out of Stock (≤ 5)</span>
            <strong id="lowStockCount"><?= $lowStockItems ?></strong>
        </div>
    </div>

    <!-- Actions & Live Search Bar -->
    <div class="actions-bar">
        <div class="search-box">
            <svg viewBox="0 0 24 24">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
            <input type="text" id="searchInput" placeholder="Search product name in real-time...">
        </div>
    </div>

    <!-- Inventory Table -->
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th style="width: 80px;">ID</th>
                    <th>Product Name</th>
                    <th>Price</th>
                    <th style="width: 170px;">Stock Counter</th>
                    <th style="width: 160px;">Status Badge</th>
                    <th style="width: 60px; text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody id="inventoryTableBody">
                <?php if (empty($products)): ?>
                    <tr id="initialEmpty">
                        <td colspan="6" class="empty-state">No products found in database. Click "Add New Product" to get started!</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($products as $row): 
                        $qty = (int)$row['quantity'];
                        $isLow = ($qty <= $lowStockThreshold);
                        $isOutOfStock = ($qty <= 0);

                        if ($isOutOfStock) {
                            $badgeClass = 'badge-danger';
                            $badgeLabel = 'Out of Stock';
                        } elseif ($isLow) {
                            $badgeClass = 'badge-danger'; // Low stock displayed in red
                            $badgeLabel = 'Low Stock (' . $qty . ')';
                        } else {
                            $badgeClass = 'badge-success';
                            $badgeLabel = 'In Stock (' . $qty . ')';
                        }
                    ?>
                        <tr class="product-row" data-id="<?= $row['id'] ?>" data-name="<?= strtolower(htmlspecialchars($row['product_name'])) ?>">
                            <td>#<?= $row['id'] ?></td>
                            <td class="product-name"><?= htmlspecialchars($row['product_name']) ?></td>
                            <td class="price-text">Rs. <?= number_format($row['price'], 2) ?></td>
                            <td>
                                <div class="counter-box">
                                    <button type="button" class="btn-counter" data-change="-1" title="Decrease Stock">-</button>
                                    <span class="qty-val"><?= $qty ?></span>
                                    <button type="button" class="btn-counter" data-change="1" title="Increase Stock">+</button>
                                </div>
                            </td>
                            <td>
                                <span class="badge <?= $badgeClass ?>">
                                    <?= $badgeLabel ?>
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <button type="button" class="btn-delete" title="Delete product">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="3 6 5 6 21 6"></polyline>
                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>

                <!-- Row shown when live search finds no results -->
                <tr id="emptySearchRow" style="display: none;">
                    <td colspan="6" class="empty-state">No matching products found.</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Add New Product -->
<div class="modal-overlay" id="addProductModal">
    <div class="modal-card">
        <div class="modal-header">
            <h3>Add New Product</h3>
            <button type="button" class="close-btn" onclick="closeModal()">&times;</button>
        </div>
        <form id="addProductForm">
            <div class="form-group">
                <label for="p_name">Product Name *</label>
                <input type="text" id="p_name" name="product_name" required placeholder="e.g. Sunlight Soap 115g">
            </div>
            <div class="form-group">
                <label for="p_price">Price (Rs.) *</label>
                <input type="number" id="p_price" name="price" step="0.01" min="0" required placeholder="e.g. 150.00">
            </div>
            <div class="form-group">
                <label for="p_qty">Initial Quantity *</label>
                <input type="number" id="p_qty" name="quantity" min="0" required placeholder="e.g. 10">
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                <button type="button" class="btn" onclick="closeModal()" style="background: #e2e8f0; color: #334155;">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Product</button>
            </div>
        </form>
    </div>
</div>

<script src="script.js"></script>
</body>
</html>
