-- ============================================
-- FLEX & BLISS DATABASE - FULL DEPLOYMENT
-- ============================================
-- IMPORTANT: This script will reset and recreate all tables
-- ============================================

-- Disable foreign key checks for the entire script
SET @OLD_FOREIGN_KEY_CHECKS = @@FOREIGN_KEY_CHECKS;
SET FOREIGN_KEY_CHECKS = 0;

-- Create and select database
CREATE DATABASE IF NOT EXISTS flexbliss_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE flexbliss_db;

-- ============================================
-- DROP ALL EXISTING TABLES (Clean Slate)
-- ============================================
DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS stock_alerts;
DROP TABLE IF EXISTS coupons;
DROP TABLE IF EXISTS password_resets;
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS wishlist;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS user_sessions;
DROP TABLE IF EXISTS activity_logs;
DROP TABLE IF EXISTS admin_users;
DROP TABLE IF EXISTS users;

-- ============================================
-- END RESET
-- ============================================
-- ================================================
-- Flex & Bliss E-commerce Database Schema
-- For XAMPP Localhost Deployment
-- ================================================

-- Create database
CREATE DATABASE IF NOT EXISTS flexbliss_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE flexbliss_db;

-- ================================================
-- Users Table
-- Stores registered user information
-- ================================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    phone VARCHAR(50),
    address TEXT,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================
-- Orders Table
-- Stores order/purchase information
-- ================================================
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    order_id VARCHAR(50) NOT NULL UNIQUE,
    customer_name VARCHAR(255) NOT NULL,
    customer_email VARCHAR(255) NOT NULL,
    customer_phone VARCHAR(50) NOT NULL,
    customer_address TEXT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(50) DEFAULT 'bKash',
    status ENUM('pending', 'confirmed', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_order_id (order_id),
    INDEX idx_customer_email (customer_email),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================
-- Order Items Table
-- Stores individual items in each order
-- ================================================
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    product_price DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    INDEX idx_order_id (order_id),
    INDEX idx_product_id (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================
-- Categories Table
-- Stores product categories
-- ================================================
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    display_name VARCHAR(100) NOT NULL,
    icon_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================
-- Products Table
-- Stores all product information
-- ================================================
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    category_id INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    original_price DECIMAL(10,2),
    image_path VARCHAR(255) NOT NULL,
    description TEXT,
    rating DECIMAL(3,2) DEFAULT 4.5,
    reviews INT DEFAULT 0,
    badge VARCHAR(50),
    in_stock BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    INDEX idx_category_id (category_id),
    INDEX idx_in_stock (in_stock),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================
-- Insert Categories (from existing products.js)
-- ================================================
INSERT INTO categories (name, display_name, icon_path) VALUES
('gypsum', 'Gypsum Decor', 'gypsum_decor_icon.png'),
('candles', 'Scented Candles', 'scented_candles_icon.png'),
('jewelry', 'Jewelry & Accessories', 'jewelry_icon.png'),
('concrete', 'Concrete Items', 'concrete_items_icon.png'),
('soap', 'Soap & Care', 'soap_care_icon.png'),
('decorative', 'Home Decor', 'home_decor_icon.png');

-- ================================================
-- Insert Products (migrated from products.js)
-- ================================================
-- Gypsum Products
INSERT INTO products (name, category_id, price, original_price, image_path, description, rating, reviews, badge, in_stock) VALUES
('Angel Wings Decorative Set', 1, 1500.00, 2000.00, 'images/gypsum_angel_wings_set_8_1764174400055.png', 'Elegant handcrafted gypsum angel wings wall decor, perfect for bohemian and modern interiors', 4.9, 87, 'Bestseller', TRUE),
('Modern Gypsum Vase', 1, 1200.00, 1600.00, 'images/gypsum_modern_vase_1764174422134.png', 'Contemporary gypsum vase with smooth finish, ideal for dried flowers or standalone decor', 4.8, 124, 'New', TRUE),
('Large Round Decorative Tray', 1, 1800.00, 2400.00, 'images/gypsum_large_round_tray_1764174423213.png', 'Handmade gypsum tray with intricate details, perfect for displaying jewelry or decorative items', 4.7, 156, NULL, TRUE),
('White Oval Serving Tray', 1, 1600.00, 2100.00, 'images/gypsum_white_oval_tray_1764174424847.png', 'Classic oval gypsum tray with elegant edges, versatile for home styling', 4.6, 98, NULL, TRUE),
('Angel Wings Wall Art (Small)', 1, 1300.00, 1700.00, 'images/gypsum_angel_wings_set_6_1764174425697.png', 'Smaller angel wings set perfect for creating a gallery wall or shelf display', 4.8, 145, 'Popular', TRUE),
('Elegant Candlestick Holder', 1, 900.00, 1200.00, 'images/gypsum_candlestick_holder_1764174426683.png', 'Hand-sculpted gypsum candlestick holder adds warmth and elegance to any space', 4.7, 112, NULL, TRUE),

-- Scented Candles
('Lavender Dreams Candle', 2, 800.00, 1000.00, 'https://images.unsplash.com/photo-1602874801006-fb2e969c9a9c?w=500&h=500&fit=crop', 'Calming lavender scented soy candle with 40-hour burn time, handpoured in glass jar', 4.9, 203, 'Bestseller', TRUE),
('Vanilla Bean Bliss', 2, 750.00, 950.00, 'https://images.unsplash.com/photo-1603006905003-be475563bc59?w=500&h=500&fit=crop', 'Sweet vanilla bean scented candle with natural soy wax, creates cozy ambiance', 4.8, 189, 'New', TRUE),
('Ocean Breeze Candle', 2, 850.00, 1100.00, 'https://images.unsplash.com/photo-1598511757337-fe2cafc31ba0?w=500&h=500&fit=crop', 'Fresh ocean-inspired scent with hints of sea salt and citrus', 4.7, 156, NULL, TRUE),
('Rose Garden Candle', 2, 900.00, 1200.00, 'https://images.unsplash.com/photo-1587070180347-5cc6adfbfe96?w=500&h=500&fit=crop', 'Romantic rose petals scent, handcrafted with essential oils', 4.9, 178, 'Popular', TRUE),
('Cinnamon Spice Candle', 2, 800.00, 1000.00, 'https://images.unsplash.com/photo-1603006905174-bc173a27c90e?w=500&h=500&fit=crop', 'Warm cinnamon and spice blend perfect for fall and winter evenings', 4.6, 134, NULL, TRUE),
('Sandalwood & Amber', 2, 950.00, 1250.00, 'https://images.unsplash.com/photo-1602874801007-7ecf0c8b9e0f?w=500&h=500&fit=crop', 'Sophisticated woody aroma with amber undertones, premium soy wax', 4.8, 167, 'Luxury', TRUE),

-- Jewelry & Accessories
('Bohemian Beaded Necklace', 3, 1400.00, 1800.00, 'https://images.unsplash.com/photo-1599643478518-a784e5dc4c8f?w=500&h=500&fit=crop', 'Handcrafted beaded necklace with natural stones and unique bohemian design', 4.7, 145, 'Trending', TRUE),
('Gold Plated Earrings Set', 3, 1200.00, 1600.00, 'https://images.unsplash.com/photo-1535632066927-ab7c9ab60908?w=500&h=500&fit=crop', 'Elegant gold-plated earrings set, hypoallergenic and nickel-free', 4.9, 198, 'Bestseller', TRUE),
('Crystal Charm Bracelet', 3, 1100.00, 1500.00, 'https://images.unsplash.com/photo-1611591437281-460bfbe1220a?w=500&h=500&fit=crop', 'Delicate bracelet with sparkling crystal charms, adjustable size', 4.8, 176, 'New', TRUE),
('Statement Ring Collection', 3, 1600.00, 2100.00, 'https://images.unsplash.com/photo-1605100804763-247f67b3557e?w=500&h=500&fit=crop', 'Set of 3 statement rings in silver and gold tones, stackable design', 4.6, 123, NULL, TRUE),
('Pearl Drop Earrings', 3, 1800.00, 2400.00, 'https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?w=500&h=500&fit=crop', 'Classic freshwater pearl earrings with sterling silver hooks', 4.9, 156, 'Luxury', TRUE),
('Layered Chain Necklace', 3, 1500.00, 2000.00, 'https://images.unsplash.com/photo-1599643477877-530eb83abc8e?w=500&h=500&fit=crop', 'Trendy layered necklace with mixed metals and pendant details', 4.7, 189, NULL, TRUE),

-- Concrete / Cement Products
('Concrete Mini Plant Pot Set', 4, 1200.00, 1600.00, 'https://images.unsplash.com/photo-1485955900006-10f4d324d411?w=500&h=500&fit=crop', 'Set of 3 handmade concrete mini plant pots perfect for succulents and small plants', 4.8, 142, 'Trending', TRUE),
('Designer Concrete Lamp Base', 4, 2200.00, 2800.00, 'https://lh3.googleusercontent.com/d/sfnU7W1kYP4kETmnN', 'Modern geometric concrete lamp base with matte finish, suitable for any shade', 4.9, 98, 'Bestseller', TRUE),
('Cement Door Nameplate', 4, 800.00, 1100.00, 'https://images.unsplash.com/photo-1606902965551-dce093cda6e7?w=500&h=500&fit=crop', 'Personalized cement nameplate for door or wall, modern industrial design', 4.7, 156, 'Custom', TRUE),

-- Soap & Personal Care
('Organic Lavender Soap Bar', 5, 450.00, 600.00, 'https://images.unsplash.com/photo-1600857062241-98e5dba60f0008?w=500&h=500&fit=crop', 'Handmade organic soap with natural lavender essential oils, gentle on skin', 4.9, 234, 'Bestseller', TRUE),
('Colorful Bath Bomb Set', 5, 650.00, 850.00, 'https://images.unsplash.com/photo-1608571423902-eed4a5ad8108?w=500&h=500&fit=crop', 'Set of 6 fizzy bath bombs in vibrant colors with essential oils and natural ingredients', 4.8, 189, 'Popular', TRUE),
('Organic Lavender Soap Bar OL', 5, 500.00, 650.00, 'images/organic_lavender_soap_ol.png', 'Premium organic lavender soap bar with soothing aroma.', 4.9, 12, 'New', TRUE),

-- Home Decor Items
('Ceramic Flower Vase Set', 6, 1300.00, 1700.00, 'https://images.unsplash.com/photo-1578500494198-246f612d3b3d?w=500&h=500&fit=crop', 'Set of 3 modern ceramic vases in complementary colors and heights', 4.8, 134, 'Popular', TRUE),
('Decorative Dreamcatcher', 6, 1100.00, 1500.00, 'https://images.unsplash.com/photo-1520699697851-3dc68aa3ca19?w=500&h=500&fit=crop', 'Bohemian dreamcatcher with feathers and beads, wall hanging decor', 4.7, 167, 'Handmade', TRUE),
('Wooden Photo Frame Set', 6, 1400.00, 1900.00, 'https://images.unsplash.com/photo-1513519245088-0e3f7a0bf520?w=500&h=500&fit=crop', 'Rustic wooden frames in various sizes, perfect for gallery walls', 4.6, 145, NULL, TRUE),
('Macrame Wall Hanging', 6, 1600.00, 2200.00, 'https://images.unsplash.com/photo-1610992826576-45e8bebd585d?w=500&h=500&fit=crop', 'Handwoven macrame wall art in natural cotton, boho chic style', 4.9, 178, 'Trending', TRUE),
('Bohemian Macramé Wall Art', 6, 1800.00, 2400.00, 'https://images.unsplash.com/photo-1598624443135-0c0d10bfd07f?w=500&h=500&fit=crop', 'Large handwoven macramé wall hanging in natural cotton with intricate patterns', 4.9, 198, 'Trending', TRUE),
('Feather Dream Catcher', 6, 950.00, 1300.00, 'https://images.unsplash.com/photo-1582655299221-2609d760ce8e?w=500&h=500&fit=crop', 'Handmade dream catcher with natural feathers and beads, boho chic design', 4.6, 145, NULL, TRUE),
('Wooden Keychain Collection', 6, 350.00, 500.00, 'https://images.unsplash.com/photo-1594044956901-a9b2e5d76d1d?w=500&h=500&fit=crop', 'Set of 5 laser-cut wooden keychains with unique designs, perfect for gifts', 4.7, 178, 'Gift', TRUE),
('Engraved Wood Art Piece', 6, 2500.00, 3200.00, 'https://images.unsplash.com/photo-1604762524889-8a22f71ed1b1?w=500&h=500&fit=crop', 'Custom engraved wooden art piece with intricate details, perfect statement decor', 4.9, 112, 'Luxury', TRUE);

-- ================================================
-- Sample Data (Optional - for testing)
-- ================================================
-- Uncomment below to add sample data for testing

-- INSERT INTO users (name, email, phone, address) VALUES
-- ('Test User', 'test@example.com', '01234567890', 'Dhaka, Bangladesh');

-- INSERT INTO orders (order_id, customer_name, customer_email, customer_phone, customer_address, total_amount, status) VALUES
-- ('ORD-20241127-00001', 'Test Customer', 'customer@example.com', '01987654321', 'Gulshan, Dhaka', 3200.00, 'pending');

-- INSERT INTO order_items (order_id, product_id, product_name, product_price, quantity, subtotal) VALUES
-- (1, 1, 'Angel Wings Decorative Set', 1500.00, 2, 3000.00),
-- (1, 7, 'Lavender Dreams Candle', 800.00, 1, 800.00);

-- ================================================
-- Database Setup Complete
-- ================================================

-- ================================================
-- Security Update: Add authentication tables
-- Run this AFTER the initial database.sql import
-- ================================================

USE flexbliss_db;

-- ================================================
-- Modify Users Table - Add password_hash column
-- ================================================
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS password_hash VARCHAR(255) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS is_verified TINYINT(1) DEFAULT 0;

-- ================================================
-- Admin Users Table
-- ================================================
CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    full_name VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    INDEX idx_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================
-- Insert Default Admin User
-- Username: shovon | Password: shovon021
INSERT INTO admin_users (username, password_hash, email, full_name) VALUES
('shovon', '$2y$10$EqNEB2FwgVCtlKVxpG98Zu9eue2dmLvr3yas3UPA8uIlBpeGUI/wee', 'shovon@flexbliss.com', 'Shovon')
ON DUPLICATE KEY UPDATE password_hash = '$2y$10$EqNEB2FwgVCtlKVxpG98Zu9eue2dmLvr3yas3UPA8uIlBpeGUI/wee';

-- ================================================
-- Activity Logs Table (replaces flat file logs)
-- ================================================
CREATE TABLE IF NOT EXISTS activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type ENUM('registration', 'order', 'login', 'admin_action', 'error') NOT NULL,
    message TEXT NOT NULL,
    user_id INT NULL,
    admin_id INT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_type (type),
    INDEX idx_created_at (created_at),
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================
-- Sessions Table (optional, for database sessions)
-- ================================================
CREATE TABLE IF NOT EXISTS user_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    session_token VARCHAR(255) NOT NULL UNIQUE,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_session_token (session_token),
    INDEX idx_expires_at (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- Premium Features Database Update
-- Run this after database_security_update.sql

-- Wishlist table for persistent wishlist
CREATE TABLE IF NOT EXISTS wishlist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_wishlist_item (user_id, product_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Add user_id to orders if not exists (for linking orders to logged-in users)
-- This allows us to show order history in dashboard
-- Note: Run each statement separately if your MySQL version doesn't support IF NOT EXISTS for columns
ALTER TABLE orders ADD COLUMN user_id INT NULL;
ALTER TABLE orders ADD CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL;

-- Index for faster order lookups (ignore duplicate key errors if already exists)
CREATE INDEX idx_orders_user_id ON orders(user_id);
CREATE INDEX idx_wishlist_user_id ON wishlist(user_id);

-- Complete E-commerce Features Database Update
-- Run this after database_premium_update.sql

-- ============================================
-- PHASE 1: CRITICAL FEATURES
-- ============================================

-- 1.1 Password Reset Table
CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    token VARCHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    used TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_token (token),
    INDEX idx_email (email)
);

-- 1.2 Email Verification (add columns to users)
ALTER TABLE users ADD COLUMN IF NOT EXISTS email_verified TINYINT(1) DEFAULT 0;
ALTER TABLE users ADD COLUMN IF NOT EXISTS verification_token VARCHAR(64) NULL;

-- ============================================
-- PHASE 2: IMPORTANT FEATURES
-- ============================================

-- 2.1 Product Reviews
CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    user_id INT NOT NULL,
    rating TINYINT NOT NULL,
    title VARCHAR(255) NULL,
    comment TEXT,
    helpful_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_product_review (user_id, product_id)
);

-- 2.3 Coupon System
CREATE TABLE IF NOT EXISTS coupons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) UNIQUE NOT NULL,
    discount_type ENUM('percentage', 'fixed') NOT NULL,
    discount_value DECIMAL(10,2) NOT NULL,
    min_order DECIMAL(10,2) DEFAULT 0,
    max_uses INT DEFAULT NULL,
    used_count INT DEFAULT 0,
    expires_at DATETIME NULL,
    active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Sample Coupons
INSERT INTO coupons (code, discount_type, discount_value, min_order, max_uses) VALUES
('WELCOME10', 'percentage', 10.00, 500, 100),
('FLAT100', 'fixed', 100.00, 1000, 50),
('ARMANSIR479', 'percentage', 15.00, 0, 1000);

-- ============================================
-- PHASE 3: NICE-TO-HAVE FEATURES
-- ============================================

-- 3.3 Stock Alerts
CREATE TABLE IF NOT EXISTS stock_alerts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    email VARCHAR(255) NOT NULL,
    user_id INT NULL,
    notified TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_product_email (product_id, email)
);

-- ============================================
-- RE-ENABLE FOREIGN KEY CHECKS
-- ============================================
SET FOREIGN_KEY_CHECKS = 1;

-- ============================================
-- DATABASE SETUP COMPLETE!
-- ============================================
-- Admin Login: username = shovon, password = shovon021
-- ============================================
