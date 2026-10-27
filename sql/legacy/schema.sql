-- VivaBem — Schema MySQL
-- Importe no phpMyAdmin ou: mysql -u root < sql/schema.sql
-- Banco já existente? Use sql/migrate_company.sql

CREATE DATABASE IF NOT EXISTS vivabem
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE vivabem;

CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(180) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  auth_provider VARCHAR(20) NOT NULL DEFAULT 'password',
  google_id VARCHAR(255) NULL,
  role ENUM('family', 'caregiver', 'company') NOT NULL DEFAULT 'family',
  status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  company_name VARCHAR(180) NULL,
  legal_name VARCHAR(180) NULL,
  cnpj VARCHAR(18) NULL,
  phone VARCHAR(20) NULL,
  contact_name VARCHAR(120) NULL,
  cpf VARCHAR(14) NULL,
  specialties VARCHAR(255) NULL,
  clinic_id INT UNSIGNED NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_users_cnpj (cnpj),
  UNIQUE KEY uq_users_cpf (cpf),
  UNIQUE KEY uq_users_google_id (google_id),
  CONSTRAINT fk_users_clinic FOREIGN KEY (clinic_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;
