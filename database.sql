CREATE DATABASE IF NOT EXISTS fish_management;

USE fish_management;

-- Admin table
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

-- Fish table
CREATE TABLE IF NOT EXISTS fish (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    image VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Default admin account
-- Username: admin
-- Password: admin123
INSERT INTO admins (username, password)
VALUES (
    'admin',
    '$2y$12$ULKnN.vov9M8RuA7laKoF.wFMCuryvhg6sIRSeMU5RzA9IydqWChu'
)
ON DUPLICATE KEY UPDATE username = username;