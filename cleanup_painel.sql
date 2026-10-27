-- ============================================================
-- VivaBem — Limpeza das tabelas do painel antigo
-- Execute no phpMyAdmin (banco: vivabem) DEPOIS de fazer backup
-- ============================================================

USE vivabem;

-- Ordem segura: primeiro as tabelas filhas (com FOREIGN KEY),
-- depois a tabela users permanece intacta.

DROP TABLE IF EXISTS medications;
DROP TABLE IF EXISTS emergency_contacts;

-- A tabela users NÃO deve ser apagada.
-- Estrutura final esperada: apenas `users`.
