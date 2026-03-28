CREATE DATABASE goatshop CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE goatshop;

-- Bảng tài khoản
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','customer') DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Bảng sản phẩm
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    price DECIMAL(10,0) NOT NULL,
    code VARCHAR(50),
    image VARCHAR(255),
    category VARCHAR(255),
    description TEXT
);

-- Bảng đơn hàng (sẽ thêm sau nếu cần)
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    total DECIMAL(10,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

