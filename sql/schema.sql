-- VivaBem — Schema MySQL
-- Importe no phpMyAdmin ou: mysql -u root < sql/schema.sql

CREATE DATABASE IF NOT EXISTS vivabem
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE vivabem;

CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(180) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
