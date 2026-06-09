CREATE DATABASE IF NOT EXISTS gamestore
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE gamestore;

DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS newsletter_subscribers;
DROP TABLE IF EXISTS discount_codes;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS platforms;
DROP TABLE IF EXISTS categories;

CREATE TABLE categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE platforms (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE products (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    stock INT UNSIGNED NOT NULL DEFAULT 0,
    image VARCHAR(255) NOT NULL,
    category_id INT UNSIGNED NOT NULL,
    platform_id INT UNSIGNED NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_products_category
        FOREIGN KEY (category_id) REFERENCES categories(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,

    CONSTRAINT fk_products_platform
        FOREIGN KEY (platform_id) REFERENCES platforms(id)
        ON DELETE RESTRICT ON UPDATE CASCADE
);

CREATE TABLE discount_codes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    discount_type ENUM('percent', 'amount') NOT NULL,
    discount_value DECIMAL(10,2) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    valid_from DATETIME NULL,
    valid_to DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE newsletter_subscribers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(150) NOT NULL UNIQUE,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE orders (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL,
    phone VARCHAR(30) NOT NULL,
    address TEXT NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    delivery_method VARCHAR(50) NOT NULL,
    total_amount_before_discount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    discount_code_id INT UNSIGNED NULL,
    discount_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    total_amount DECIMAL(10,2) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_orders_discount_code
        FOREIGN KEY (discount_code_id) REFERENCES discount_codes(id)
        ON DELETE SET NULL ON UPDATE CASCADE
);

CREATE TABLE order_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    quantity INT UNSIGNED NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    line_total DECIMAL(10,2) NOT NULL,

    CONSTRAINT fk_order_items_order
        FOREIGN KEY (order_id) REFERENCES orders(id)
        ON DELETE CASCADE ON UPDATE CASCADE,

    CONSTRAINT fk_order_items_product
        FOREIGN KEY (product_id) REFERENCES products(id)
        ON DELETE RESTRICT ON UPDATE CASCADE
);

INSERT INTO categories (name) VALUES
('RPG'),
('Akcja'),
('Wyścigi'),
('Sportowe'),
('Sci-Fi'),
('Strategia');

INSERT INTO platforms (name) VALUES
('PC'),
('PS5'),
('Xbox Series X');

INSERT INTO products (title, description, price, stock, image, category_id, platform_id) VALUES
('Cyber Quest', 'Cyberpunkowe RPG w mrocznym mieście przyszłości.', 129.00, 15, 'images/cyber-quest.png', 1, 1),
('Racing X', 'Dynamiczne wyścigi futurystycznych samochodów.', 119.00, 12, 'images/racing-x.png', 3, 2),
('Battle Arena', 'Taktyczna strzelanka osadzona w świecie po wojnie.', 139.00, 8, 'images/battle-arena.png', 2, 3),
('Mystic World', 'Epicka przygoda fantasy z otwartym światem.', 109.00, 10, 'images/mystic-world.png', 1, 1),
('Space Ops', 'Kosmiczna gra akcji z bitwami statków.', 129.00, 9, 'images/space-ops.png', 5, 2),
('Football Stars', 'Symulator piłki nożnej dla fanów sportu.', 99.00, 20, 'images/football-stars.png', 4, 3),
('Shadow Realm', 'Mroczne RPG akcji w świecie cieni i ruin.', 119.00, 7, 'images/shadow-realm.png', 1, 1),
('Legends of Valor', 'Przygodowa gra fantasy z bohaterami i zamkami.', 139.00, 11, 'images/legends-of-valor.png', 1, 2),
('Neon Drift', 'Arcade racing w neonowym mieście przyszłości.', 89.00, 13, 'images/neon-drift.png', 3, 1);

INSERT INTO discount_codes (code, discount_type, discount_value, is_active, valid_from, valid_to) VALUES
('GAME10', 'percent', 10.00, 1, '2026-01-01 00:00:00', '2026-12-31 23:59:59'),
('START20', 'amount', 20.00, 1, '2026-01-01 00:00:00', '2026-12-31 23:59:59'),
('RPG15', 'percent', 15.00, 1, '2026-01-01 00:00:00', '2026-12-31 23:59:59');