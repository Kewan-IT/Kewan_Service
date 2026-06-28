-- Tabela de devoluções (total e parcial)
CREATE TABLE IF NOT EXISTS devolucoes (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    venda_id      INT UNSIGNED NOT NULL,
    usuario_id    INT UNSIGNED NOT NULL,
    tipo          ENUM('total','parcial') NOT NULL DEFAULT 'total',
    motivo        TEXT NOT NULL,
    valor_total   DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    criado_em     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (venda_id) REFERENCES vendas(id),
    INDEX idx_venda (venda_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Itens de cada devolução
CREATE TABLE IF NOT EXISTS devolucao_itens (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    devolucao_id    INT UNSIGNED NOT NULL,
    item_venda_id   INT UNSIGNED NOT NULL,
    produto_id      INT UNSIGNED NOT NULL,
    lote_id         INT UNSIGNED NULL,
    quantidade      INT UNSIGNED NOT NULL,
    preco_unitario  DECIMAL(12,2) NOT NULL,
    subtotal        DECIMAL(12,2) NOT NULL,
    FOREIGN KEY (devolucao_id)  REFERENCES devolucoes(id),
    FOREIGN KEY (item_venda_id) REFERENCES itens_venda(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Adicionar coluna qty_devolvida em itens_venda para controlar devoluções parciais
ALTER TABLE itens_venda
    ADD COLUMN IF NOT EXISTS qty_devolvida INT UNSIGNED NOT NULL DEFAULT 0;
