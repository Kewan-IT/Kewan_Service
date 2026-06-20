-- ============================================================
-- Migração: Promoção por Lote + Devolução ao Fornecedor por Lote
-- Aplicar com: mysql -u utilizador -p kewanfarma < migrations/promocao_devolucao_lotes.sql
-- ============================================================

-- 1. Campos de promoção no LOTE (não no produto — afecta só aquele lote)
ALTER TABLE lotes
  ADD COLUMN em_promocao         TINYINT(1)     NOT NULL DEFAULT 0      AFTER observacoes,
  ADD COLUMN preco_promocional   DECIMAL(12,2)  NULL     DEFAULT NULL   AFTER em_promocao,
  ADD COLUMN promocao_motivo     VARCHAR(150)   NULL     DEFAULT NULL   AFTER preco_promocional,
  ADD COLUMN promocao_usuario_id INT UNSIGNED   NULL     DEFAULT NULL   AFTER promocao_motivo,
  ADD COLUMN promocao_criado_em  TIMESTAMP      NULL     DEFAULT NULL   AFTER promocao_usuario_id,
  ADD KEY idx_lotes_em_promocao (em_promocao);

-- 2. Tabela de devoluções ao fornecedor (sempre amarrada a um lote específico)
CREATE TABLE IF NOT EXISTS devolucoes_fornecedor (
  id              INT UNSIGNED   NOT NULL AUTO_INCREMENT,
  numero_devolucao VARCHAR(30)   NOT NULL,
  lote_id         INT UNSIGNED   NOT NULL,
  produto_id      INT UNSIGNED   NOT NULL,
  fornecedor_id   INT UNSIGNED   DEFAULT NULL,
  numero_lote     VARCHAR(60)    NOT NULL,
  quantidade      INT            NOT NULL,
  motivo          ENUM('validade','vencido','avariado','outro') NOT NULL DEFAULT 'validade',
  observacoes     TEXT           DEFAULT NULL,
  valor_unitario  DECIMAL(12,2)  NOT NULL DEFAULT 0.00,
  valor_total     DECIMAL(12,2)  GENERATED ALWAYS AS (quantidade * valor_unitario) STORED,
  usuario_id      INT UNSIGNED   DEFAULT NULL,
  criado_em       TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_dev_numero (numero_devolucao),
  KEY idx_dev_lote      (lote_id),
  KEY idx_dev_produto   (produto_id),
  KEY idx_dev_fornecedor(fornecedor_id),
  CONSTRAINT fk_dev_lote
    FOREIGN KEY (lote_id) REFERENCES lotes (id) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_dev_produto
    FOREIGN KEY (produto_id) REFERENCES produtos (id) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_dev_fornecedor
    FOREIGN KEY (fornecedor_id) REFERENCES fornecedores (id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Devoluções de produtos ao fornecedor, registadas por lote específico';
