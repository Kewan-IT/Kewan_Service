-- Migração: Adicionar suporte a tipo de cliente (Singular / Instituição)
-- Execute este script na sua base de dados MySQL/MariaDB

ALTER TABLE clientes
  ADD COLUMN tipo_cliente    ENUM('singular','instituicao') NOT NULL DEFAULT 'singular' AFTER id,
  ADD COLUMN nome_comercial  VARCHAR(150)  NULL AFTER nome,
  ADD COLUMN sector          VARCHAR(80)   NULL,
  ADD COLUMN pessoa_contacto VARCHAR(100)  NULL,
  ADD COLUMN telefone2       VARCHAR(30)   NULL;

-- Índice para filtrar por tipo
ALTER TABLE clientes ADD INDEX idx_tipo_cliente (tipo_cliente);

-- Clientes existentes ficam como 'singular' (já é o DEFAULT)
