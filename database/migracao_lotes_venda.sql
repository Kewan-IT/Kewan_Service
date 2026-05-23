-- ============================================================
-- Migração: Controlo de Lotes nas Vendas
-- Aplica APENAS se as colunas ainda não existirem
-- ============================================================

-- 1. Garantir coluna lote_id em itens_venda (já existe na schema, mas por segurança)
ALTER TABLE itens_venda
  MODIFY COLUMN lote_id INT UNSIGNED DEFAULT NULL;

-- 2. Tabela de movimentos_stock com suporte a lote_id
CREATE TABLE IF NOT EXISTS movimentos_stock (
  id          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  produto_id  INT UNSIGNED  NOT NULL,
  lote_id     INT UNSIGNED  DEFAULT NULL,
  tipo        ENUM('entrada','saida','ajuste') NOT NULL,
  quantidade  INT           NOT NULL,
  referencia  VARCHAR(60)   DEFAULT NULL,   -- ex: VD-2025-00001
  usuario_id  INT UNSIGNED  DEFAULT NULL,
  observacoes TEXT          DEFAULT NULL,
  criado_em   TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_ms_produto  (produto_id),
  KEY idx_ms_lote     (lote_id),
  KEY idx_ms_criado   (criado_em),
  CONSTRAINT fk_ms_produto FOREIGN KEY (produto_id) REFERENCES produtos (id) ON DELETE RESTRICT,
  CONSTRAINT fk_ms_lote    FOREIGN KEY (lote_id)    REFERENCES lotes    (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Rastreabilidade de movimentos de stock por lote';

-- 3. View: lotes a vencer (usada por alertas e relatórios)
CREATE OR REPLACE VIEW vw_lotes_a_vencer AS
SELECT
  l.id,
  l.numero_lote,
  l.validade,
  l.quantidade,
  DATEDIFF(l.validade, CURDATE()) AS dias_para_vencer,
  p.id   AS produto_id,
  p.nome AS produto_nome,
  p.unidade_medida,
  CASE
    WHEN DATEDIFF(l.validade, CURDATE()) <= 30 THEN 'critico'
    WHEN DATEDIFF(l.validade, CURDATE()) <= 60 THEN 'atencao'
    WHEN DATEDIFF(l.validade, CURDATE()) <= 90 THEN 'aviso'
    ELSE 'ok'
  END AS status_validade
FROM lotes l
JOIN produtos p ON p.id = l.produto_id
WHERE l.quantidade > 0
  AND l.validade >= CURDATE()
ORDER BY l.validade ASC;

-- 4. View: lotes vencidos com stock residual
CREATE OR REPLACE VIEW vw_lotes_vencidos AS
SELECT
  l.id,
  l.numero_lote,
  l.validade,
  l.quantidade,
  DATEDIFF(CURDATE(), l.validade) AS dias_vencido,
  p.id   AS produto_id,
  p.nome AS produto_nome,
  p.unidade_medida
FROM lotes l
JOIN produtos p ON p.id = l.produto_id
WHERE l.quantidade > 0
  AND l.validade < CURDATE()
ORDER BY l.validade ASC;

-- 5. View: rastreabilidade por venda
CREATE OR REPLACE VIEW vw_rastreabilidade_vendas AS
SELECT
  iv.id           AS item_id,
  v.numero_venda,
  v.criado_em     AS data_venda,
  p.nome          AS produto_nome,
  l.numero_lote,
  l.validade      AS lote_validade,
  iv.quantidade,
  iv.preco_unitario,
  iv.subtotal,
  c.nome          AS cliente_nome,
  u.nome          AS vendedor_nome
FROM itens_venda iv
JOIN vendas v          ON v.id = iv.venda_id
JOIN produtos p        ON p.id = iv.produto_id
LEFT JOIN lotes l      ON l.id = iv.lote_id
LEFT JOIN clientes c   ON c.id = v.cliente_id
LEFT JOIN usuarios u   ON u.id = v.usuario_id
ORDER BY v.criado_em DESC;
