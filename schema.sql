-- Database creation
CREATE DATABASE IF NOT EXISTS inventory_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE inventory_db;

-- Inventory table
CREATE TABLE IF NOT EXISTS inventory (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_name VARCHAR(255) NOT NULL,
    price DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    quantity INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sample test data
INSERT INTO inventory (product_name, price, quantity) VALUES
('Anchor Milk Powder 400g', 1150.00, 18),
('Sunlight Soap 115g', 160.00, 4),
('Munchee Super Cream Cracker', 260.00, 2),
('Watawala Tea 200g', 420.00, 12),
('Araliya Keeri Samba 5kg', 1450.00, 0),
('Dettol Original Soap 100g', 220.00, 7);
