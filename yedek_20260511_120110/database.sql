CREATE DATABASE IF NOT EXISTS zrt CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE zrt;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tc_kimlik VARCHAR(50) NULL,
    kurumsal_id VARCHAR(50) NULL,
    bireysel_id VARCHAR(50) NULL,
    sifre VARCHAR(50) NOT NULL,
    giris_tipi VARCHAR(20) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Varsayılan admin kullanıcısı (şifre: admin123 - bcrypt hashlenmiş hali)
INSERT INTO admins (username, password) 
SELECT 'admin', '$2y$10$P2E2QxQj/jQ9g8/Z/7M/6.qTjB2W.8E0Q2XhR.vD6U6.T/D6E.A6a'
WHERE NOT EXISTS (SELECT 1 FROM admins WHERE username = 'admin');