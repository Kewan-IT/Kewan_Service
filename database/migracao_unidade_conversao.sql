-- ================================================================
-- Migração: Unidade de Compra vs Unidade de Venda
-- Permite comprar em caixas e vender por cartela, unidade, etc.
-- ================================================================

ALTER TABLE produtos
    ADD COLUMN IF NOT EXISTS unidade_compra    VARCHAR(50)  NOT NULL DEFAULT 'caixa'   AFTER unidade_medida,
    ADD COLUMN IF NOT EXISTS unidade_venda     VARCHAR(50)  NOT NULL DEFAULT 'unidade' AFTER unidade_compra,
    ADD COLUMN IF NOT EXISTS fator_conversao   DECIMAL(10,3) NOT NULL DEFAULT 1        AFTER unidade_venda,
    ADD COLUMN IF NOT EXISTS preco_compra_unitario DECIMAL(10,2) GENERATED ALWAYS AS
        (ROUND(preco_compra / fator_conversao, 2)) VIRTUAL AFTER fator_conversao;

-- Índice para relatórios
ALTER TABLE produtos
    ADD INDEX IF NOT EXISTS idx_fator_conversao (fator_conversao);
