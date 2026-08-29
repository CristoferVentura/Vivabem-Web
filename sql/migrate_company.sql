-- VivaBem — Migração: roles, clínica e cuidadores
-- Execute no phpMyAdmin (banco: vivabem) se a tabela users já existir.
-- Não rode duas vezes: as colunas já terão sido criadas.

USE vivabem;

ALTER TABLE users
  ADD COLUMN role ENUM('family', 'caregiver', 'company') NOT NULL DEFAULT 'family' AFTER password,
  ADD COLUMN status ENUM('active', 'inactive') NOT NULL DEFAULT 'active' AFTER role,
  ADD COLUMN company_name VARCHAR(180) NULL AFTER status,
  ADD COLUMN legal_name VARCHAR(180) NULL AFTER company_name,
  ADD COLUMN cnpj VARCHAR(18) NULL AFTER legal_name,
  ADD COLUMN phone VARCHAR(20) NULL AFTER cnpj,
  ADD COLUMN contact_name VARCHAR(120) NULL AFTER phone,
  ADD COLUMN cpf VARCHAR(14) NULL AFTER contact_name,
  ADD COLUMN specialties VARCHAR(255) NULL AFTER cpf,
  ADD COLUMN clinic_id INT UNSIGNED NULL AFTER specialties,
  ADD UNIQUE KEY uq_users_cnpj (cnpj),
  ADD UNIQUE KEY uq_users_cpf (cpf),
  ADD CONSTRAINT fk_users_clinic FOREIGN KEY (clinic_id) REFERENCES users(id) ON DELETE SET NULL;
