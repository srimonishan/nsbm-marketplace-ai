-- ============================================
-- NSBM Marketplace AI - Complete Database Schema
-- MySQL 8.0+
-- ============================================

CREATE DATABASE IF NOT EXISTS nsbm_marketplace
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE nsbm_marketplace;

-- ============================================
-- USERS TABLE
-- ============================================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    address TEXT DEFAULT NULL,
    city VARCHAR(100) DEFAULT NULL,
    avatar VARCHAR(500) DEFAULT NULL,
    role ENUM('customer', 'admin') DEFAULT 'customer',
    status ENUM('active', 'inactive', 'banned') DEFAULT 'active',
    email_verified_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- ============================================
-- CATEGORIES TABLE
-- ============================================
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,
    description TEXT DEFAULT NULL,
    image VARCHAR(500) DEFAULT NULL,
    parent_id INT DEFAULT NULL,
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL,
    INDEX idx_slug (slug),
    INDEX idx_active (is_active)
) ENGINE=InnoDB;

-- ============================================
-- PRODUCTS TABLE
-- ============================================
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name VARCHAR(300) NOT NULL,
    slug VARCHAR(300) NOT NULL UNIQUE,
    description TEXT NOT NULL,
    short_description VARCHAR(500) DEFAULT NULL,
    price DECIMAL(10,2) NOT NULL,
    sale_price DECIMAL(10,2) DEFAULT NULL,
    sku VARCHAR(100) DEFAULT NULL UNIQUE,
    stock_quantity INT DEFAULT 0,
    image VARCHAR(500) DEFAULT NULL,
    gallery JSON DEFAULT NULL,
    tags JSON DEFAULT NULL,
    is_featured TINYINT(1) DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    views INT DEFAULT 0,
    rating DECIMAL(3,2) DEFAULT 0.00,
    total_reviews INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    INDEX idx_category (category_id),
    INDEX idx_slug (slug),
    INDEX idx_featured (is_featured),
    INDEX idx_active (is_active),
    INDEX idx_price (price),
    FULLTEXT idx_search (name, description, short_description)
) ENGINE=InnoDB;

-- ============================================
-- ORDERS TABLE
-- ============================================
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_number VARCHAR(50) NOT NULL UNIQUE,
    subtotal DECIMAL(10,2) NOT NULL,
    tax DECIMAL(10,2) DEFAULT 0.00,
    shipping_fee DECIMAL(10,2) DEFAULT 0.00,
    discount DECIMAL(10,2) DEFAULT 0.00,
    total DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded') DEFAULT 'pending',
    payment_method VARCHAR(100) DEFAULT 'card',
    payment_status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',
    shipping_address TEXT NOT NULL,
    shipping_city VARCHAR(100) NOT NULL,
    shipping_phone VARCHAR(20) NOT NULL,
    notes TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_order_number (order_number),
    INDEX idx_status (status),
    INDEX idx_created (created_at)
) ENGINE=InnoDB;

-- ============================================
-- ORDER ITEMS TABLE
-- ============================================
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    product_name VARCHAR(300) NOT NULL,
    product_image VARCHAR(500) DEFAULT NULL,
    quantity INT NOT NULL DEFAULT 1,
    price DECIMAL(10,2) NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_order (order_id),
    INDEX idx_product (product_id)
) ENGINE=InnoDB;

-- ============================================
-- CUSTOMER NOTIFICATIONS TABLE
-- ============================================
CREATE TABLE notifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_id INT DEFAULT NULL,
    type VARCHAR(50) NOT NULL DEFAULT 'order_status',
    title VARCHAR(200) NOT NULL,
    message VARCHAR(500) NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL,
    INDEX idx_notification_user (user_id, is_read),
    INDEX idx_notification_created (created_at)
) ENGINE=InnoDB;

-- ============================================
-- REVIEWS TABLE
-- ============================================
CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    user_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT DEFAULT NULL,
    is_approved TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_product (product_id),
    INDEX idx_user (user_id)
) ENGINE=InnoDB;

-- ============================================
-- WISHLIST TABLE
-- ============================================
CREATE TABLE wishlist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_wishlist (user_id, product_id)
) ENGINE=InnoDB;

-- ============================================
-- CONTACT MESSAGES TABLE
-- ============================================
CREATE TABLE contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    email VARCHAR(255) NOT NULL,
    subject VARCHAR(300) NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    replied_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_read (is_read)
) ENGINE=InnoDB;

-- ============================================
-- AI CHAT HISTORY TABLE
-- ============================================
CREATE TABLE ai_chat_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    session_id VARCHAR(100) NOT NULL,
    assistant_type ENUM('shopping', 'gift', 'hero') NOT NULL,
    user_message TEXT NOT NULL,
    ai_response TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_session (session_id),
    INDEX idx_type (assistant_type)
) ENGINE=InnoDB;

-- ============================================
-- SETTINGS TABLE
-- ============================================
CREATE TABLE settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT DEFAULT NULL,
    setting_group VARCHAR(50) DEFAULT 'general',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================
-- SAMPLE DATA - ADMIN USER
-- ============================================
-- Password for admin: admin123 | Password for students: student123
INSERT INTO users (first_name, last_name, email, password, role, status, email_verified_at) VALUES
('Admin', 'NSBM', 'admin@nsbm.ac.lk', '$2y$10$33Ax6QNkXMMqHQF.3bWEvuflRVjjujfu641h0IaPB3E8cbHlsu5.S', 'admin', 'active', NOW()),
('Kasun', 'Perera', 'kasun@students.nsbm.ac.lk', '$2y$10$Lpos00shhOtbHJZTfvb/e.w7WyNnCdOlylB9b5J17FWGRUTQWGlmi', 'customer', 'active', NOW()),
('Nethmi', 'Silva', 'nethmi@students.nsbm.ac.lk', '$2y$10$Lpos00shhOtbHJZTfvb/e.w7WyNnCdOlylB9b5J17FWGRUTQWGlmi', 'customer', 'active', NOW()),
('Dinesh', 'Fernando', 'dinesh@students.nsbm.ac.lk', '$2y$10$Lpos00shhOtbHJZTfvb/e.w7WyNnCdOlylB9b5J17FWGRUTQWGlmi', 'customer', 'active', NOW()),
('Sachini', 'Jayawardena', 'sachini@students.nsbm.ac.lk', '$2y$10$Lpos00shhOtbHJZTfvb/e.w7WyNnCdOlylB9b5J17FWGRUTQWGlmi', 'customer', 'active', NOW()),
('Sri', 'Monishan', 'sri.monishan@students.nsbm.ac.lk', '$2y$10$Lpos00shhOtbHJZTfvb/e.w7WyNnCdOlylB9b5J17FWGRUTQWGlmi', 'customer', 'active', NOW()),
('Test', 'User NSBM', 'test.user@students.nsbm.ac.lk', '$2y$10$Lpos00shhOtbHJZTfvb/e.w7WyNnCdOlylB9b5J17FWGRUTQWGlmi', 'customer', 'active', NOW());

-- ============================================
-- SAMPLE DATA - CATEGORIES
-- ============================================
INSERT INTO categories (name, slug, description, image, sort_order) VALUES
('Electronics', 'electronics', 'Laptops, phones, tablets, and accessories for tech-savvy students', 'electronics.jpg', 1),
('Books & Stationery', 'books-stationery', 'Academic textbooks, notebooks, and study materials', 'books.jpg', 2),
('Fashion & Apparel', 'fashion-apparel', 'Trendy clothing, shoes, and accessories for campus life', 'fashion.jpg', 3),
('Food & Beverages', 'food-beverages', 'Snacks, drinks, and meal options for busy students', 'food.jpg', 4),
('Sports & Fitness', 'sports-fitness', 'Sports equipment, gym gear, and fitness accessories', 'sports.jpg', 5),
('Art & Crafts', 'art-crafts', 'Creative supplies, handmade items, and artistic materials', 'art.jpg', 6),
('Services', 'services', 'Tutoring, design services, and freelance offerings', 'services.jpg', 7),
('Dorm & Living', 'dorm-living', 'Room essentials, decor, and daily living items', 'dorm.jpg', 8);

-- ============================================
-- SAMPLE DATA - PRODUCTS
-- ============================================
INSERT INTO products (category_id, name, slug, description, short_description, price, sale_price, sku, stock_quantity, is_featured, rating, total_reviews) VALUES
(1, 'MacBook Air M2 - Student Edition', 'macbook-air-m2-student', 'The perfect laptop for university students. Lightweight, powerful, and with all-day battery life. Ideal for coding, design, and academic work at NSBM.', 'Lightweight powerhouse for students', 389999.00, 359999.00, 'ELEC-001', 15, 1, 4.80, 24),
(1, 'Sony WH-1000XM5 Headphones', 'sony-wh1000xm5', 'Industry-leading noise cancellation headphones. Perfect for studying in the library or commuting to campus. Premium sound quality.', 'Premium noise-cancelling headphones', 89999.00, 79999.00, 'ELEC-002', 30, 1, 4.70, 18),
(1, 'iPad Air with Apple Pencil Bundle', 'ipad-air-pencil-bundle', 'Take notes digitally, sketch designs, and stay productive. Comes with Apple Pencil for the ultimate student experience.', 'Digital note-taking powerhouse', 179999.00, NULL, 'ELEC-003', 20, 1, 4.60, 15),
(1, 'Logitech MX Master 3S Mouse', 'logitech-mx-master-3s', 'Ergonomic wireless mouse perfect for long study sessions. Ultra-precise scrolling and customizable buttons.', 'Ergonomic wireless productivity mouse', 24999.00, 21999.00, 'ELEC-004', 45, 0, 4.50, 12),
(2, 'Data Structures & Algorithms Textbook', 'dsa-textbook', 'Comprehensive guide to data structures and algorithms. Essential for computer science students at NSBM.', 'Essential CS textbook', 8500.00, 7200.00, 'BOOK-001', 50, 1, 4.90, 32),
(2, 'Premium Notebook Set (5-Pack)', 'premium-notebook-set', 'High-quality A4 notebooks with 200 pages each. Smooth paper perfect for lectures and study notes.', '5 premium A4 notebooks', 3500.00, NULL, 'BOOK-002', 100, 0, 4.40, 28),
(2, 'Scientific Calculator - Casio FX-991EX', 'casio-fx991ex', 'Advanced scientific calculator for engineering and mathematics students. Solar powered with 552 functions.', 'Advanced scientific calculator', 12500.00, 10999.00, 'BOOK-003', 35, 0, 4.70, 20),
(3, 'NSBM Branded Hoodie - Limited Edition', 'nsbm-branded-hoodie', 'Show your university pride with this premium quality hoodie. Available in green and black. Limited edition campus collection.', 'Limited edition campus hoodie', 5500.00, 4999.00, 'FASH-001', 75, 1, 4.80, 45),
(3, 'Canvas Backpack - Student Pro', 'canvas-backpack-pro', 'Durable canvas backpack with laptop compartment, multiple pockets, and water-resistant coating. Perfect for daily campus use.', 'Durable student backpack', 7999.00, 6999.00, 'FASH-002', 40, 1, 4.60, 22),
(3, 'Running Shoes - Campus Edition', 'running-shoes-campus', 'Comfortable and stylish running shoes perfect for campus walks and gym sessions. Breathable mesh upper.', 'Comfortable campus running shoes', 14999.00, 12999.00, 'FASH-003', 25, 0, 4.30, 16),
(4, 'Premium Coffee Subscription Box', 'premium-coffee-box', 'Monthly subscription of premium Sri Lankan coffee beans. Stay energized during exam season with artisan blends.', 'Monthly artisan coffee delivery', 4500.00, NULL, 'FOOD-001', 60, 1, 4.90, 38),
(4, 'Healthy Snack Bundle', 'healthy-snack-bundle', 'Curated selection of healthy snacks for study sessions. Includes nuts, dried fruits, protein bars, and green tea.', 'Curated healthy study snacks', 2800.00, 2500.00, 'FOOD-002', 80, 0, 4.50, 25),
(5, 'Yoga Mat - Premium Eco-Friendly', 'yoga-mat-premium', 'Extra thick eco-friendly yoga mat. Perfect for campus yoga sessions and home workouts. Non-slip surface.', 'Eco-friendly premium yoga mat', 6500.00, 5999.00, 'SPRT-001', 30, 0, 4.40, 14),
(5, 'Resistance Band Set (5 Levels)', 'resistance-band-set', 'Complete set of 5 resistance bands for home workouts. Includes carrying bag and exercise guide.', 'Complete home workout band set', 3999.00, 3499.00, 'SPRT-002', 55, 0, 4.60, 19),
(6, 'Watercolor Paint Set - Professional', 'watercolor-paint-set', 'Professional grade watercolor set with 48 colors. Includes brushes, palette, and carrying case. Perfect for art students.', 'Professional 48-color watercolor set', 8999.00, 7999.00, 'ART-001', 20, 1, 4.80, 11),
(6, 'Handmade Ceramic Mug Collection', 'handmade-ceramic-mugs', 'Set of 3 handmade ceramic mugs by NSBM art students. Each piece is unique with beautiful glazing.', 'Unique handmade ceramic mugs', 4500.00, NULL, 'ART-002', 15, 0, 4.90, 8),
(7, 'Web Development Tutoring (10 Sessions)', 'web-dev-tutoring', 'One-on-one tutoring sessions for web development. Covers HTML, CSS, JavaScript, PHP, and MySQL. By senior CS students.', '10 personalized coding sessions', 25000.00, 22000.00, 'SERV-001', 10, 1, 5.00, 7),
(7, 'Logo Design Service - Premium Package', 'logo-design-premium', 'Professional logo design service by design faculty students. Includes 3 concepts, unlimited revisions, and all file formats.', 'Professional logo design package', 15000.00, NULL, 'SERV-002', 20, 0, 4.70, 13),
(8, 'LED Desk Lamp - Smart Study Light', 'led-desk-lamp-smart', 'Adjustable LED desk lamp with multiple brightness levels and color temperatures. USB charging port included.', 'Smart adjustable study lamp', 7500.00, 6499.00, 'DORM-001', 40, 0, 4.50, 17),
(8, 'Room Organizer Set - Minimalist', 'room-organizer-minimalist', 'Complete room organization set including shelf organizers, drawer dividers, and wall hooks. Modern minimalist design.', 'Complete minimalist room organizer', 5999.00, 4999.00, 'DORM-002', 25, 0, 4.30, 9);

-- ============================================
-- SAMPLE DATA - ORDERS
-- ============================================
INSERT INTO orders (user_id, order_number, subtotal, tax, shipping_fee, total, status, payment_status, shipping_address, shipping_city, shipping_phone) VALUES
(2, 'NSBM-2024-001', 89999.00, 4500.00, 500.00, 94999.00, 'delivered', 'paid', '123 Campus Road, Pitipana', 'Homagama', '0771234567'),
(3, 'NSBM-2024-002', 5500.00, 275.00, 300.00, 6075.00, 'shipped', 'paid', '45 University Lane', 'Homagama', '0779876543'),
(4, 'NSBM-2024-003', 179999.00, 9000.00, 0.00, 188999.00, 'processing', 'paid', '78 Green Path, NSBM', 'Homagama', '0765432198'),
(5, 'NSBM-2024-004', 25000.00, 1250.00, 0.00, 26250.00, 'pending', 'pending', '12 Scholar Avenue', 'Homagama', '0712345678'),
(2, 'NSBM-2024-005', 14999.00, 750.00, 350.00, 16099.00, 'delivered', 'paid', '123 Campus Road, Pitipana', 'Homagama', '0771234567');

INSERT INTO order_items (order_id, product_id, product_name, quantity, price, total) VALUES
(1, 2, 'Sony WH-1000XM5 Headphones', 1, 89999.00, 89999.00),
(2, 8, 'NSBM Branded Hoodie - Limited Edition', 1, 5500.00, 5500.00),
(3, 3, 'iPad Air with Apple Pencil Bundle', 1, 179999.00, 179999.00),
(4, 17, 'Web Development Tutoring (10 Sessions)', 1, 25000.00, 25000.00),
(5, 10, 'Running Shoes - Campus Edition', 1, 14999.00, 14999.00);

-- ============================================
-- SAMPLE DATA - REVIEWS
-- ============================================
INSERT INTO reviews (product_id, user_id, rating, comment, is_approved) VALUES
(1, 2, 5, 'Amazing laptop! Perfect for my CS coursework at NSBM. Battery lasts all day.', 1),
(2, 3, 5, 'Best headphones for studying in the library. Noise cancellation is incredible!', 1),
(8, 4, 4, 'Love the hoodie! Great quality and perfect fit. Proud to wear NSBM colors.', 1),
(5, 2, 5, 'This textbook is a must-have for any CS student. Very well explained.', 1),
(11, 5, 5, 'The coffee is amazing! Keeps me going during late-night study sessions.', 1);

-- ============================================
-- SAMPLE DATA - SETTINGS
-- ============================================
INSERT INTO settings (setting_key, setting_value, setting_group) VALUES
('site_name', 'NSBM Marketplace AI', 'general'),
('site_description', 'Premium AI-Powered Marketplace for NSBM Green University', 'general'),
('site_email', 'marketplace@nsbm.ac.lk', 'general'),
('site_phone', '+94 11 544 5000', 'general'),
('site_address', 'Mahenwaththa, Pitipana, Homagama, Sri Lanka', 'general'),
('currency', 'LKR', 'general'),
('currency_symbol', 'Rs.', 'general'),
('tax_rate', '5', 'general'),
('shipping_fee', '350', 'general'),
('free_shipping_threshold', '10000', 'general'),
('gemini_api_key', '', 'ai'),
('hero_headline', 'Discover Premium Products for Campus Life', 'homepage'),
('hero_subtitle', 'AI-Powered Shopping Experience for NSBM Students', 'homepage'),
('hero_cta', 'Shop Now', 'homepage');
