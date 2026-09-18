USE medicaldata;

-- 1. Force the engine to forget the old 'ghost' tables
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS medicines;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS contact_messages;

-- 2. Create the Users table properly
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100),
    email VARCHAR(100) UNIQUE,
    password VARCHAR(255),
    role TINYINT(1) DEFAULT 1
);

-- 3. Create the table that was causing the Line 13 Fatal Error
CREATE TABLE contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    status ENUM('unread', 'read') DEFAULT 'unread'
);

-- 4. Create the medicines table (Inventory)
CREATE TABLE medicines (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255),
    quantity INT DEFAULT 0,
    price DECIMAL(10, 2)
);

-- 5. Insert YOUR specific admin login
INSERT INTO users (username, email, password, role) 
VALUES ('MIMS Admin', 'admin@mims.com', 'password123', 1);