<?php
// app/Views/compras/compras_pdf.php
// Página limpa para impressão / guardar como PDF
$appUrl = $_ENV['APP_URL'] ?? '';
$c      = $compra;

$statusLabels = [
    'rascunho'              => 'Rascunho',
    'enviada'               => 'Enviada',
    'parcialmente_recebida' => 'Parcialmente Recebida',
    'recebida'              => 'Recebida',
    'cancelada'             => 'Cancelada',
];
?>
<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Encomenda <?= htmlspecialchars($c['numero_compra']) ?></title>
  <style>
    * { margin:0; padding:0; box-sizing:border-box; }

    body {
      font-family: 'Segoe UI', Arial, sans-serif;
      font-size: 13px;
      color: #1a1a1a;
      background: #f5f5f5;
    }

    /* Botão de impressão — oculto no PDF */
    .no-print {
      position: fixed;
      top: 20px;
      right: 20px;
      display: flex;
      gap: 10px;
      z-index: 999;
    }
    .btn-print {
      background: #1a7f5a;
      color: #fff;
      border: none;
      padding: 10px 20px;
      border-radius: 8px;
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 8px;
      box-shadow: 0 2px 8px rgba(0,0,0,.2);
    }
    .btn-print:hover { background: #156347; }
    .btn-voltar {
      background: #fff;
      color: #333;
      border: 1px solid #ddd;
      padding: 10px 20px;
      border-radius: 8px;
      font-size: 14px;
      cursor: pointer;
      text-decoration: none;
      display: flex;
      align-items: center;
      gap: 8px;
      box-shadow: 0 2px 8px rgba(0,0,0,.1);
    }

    /* Página A4 */
    .page {
      width: 210mm;
      min-height: 297mm;
      background: #fff;
      margin: 30px auto;
      padding: 18mm 18mm 15mm 18mm;
      box-shadow: 0 4px 24px rgba(0,0,0,.12);
    }

    /* Cabeçalho da empresa */
    .header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      padding-bottom: 14px;
      border-bottom: 3px solid #1a7f5a;
      margin-bottom: 18px;
    }
    .company-name {
      font-size: 22px;
      font-weight: 700;
      color: #1a7f5a;
      letter-spacing: -.3px;
    }
    .company-sub {
      font-size: 11px;
      color: #666;
      margin-top: 2px;
    }
    .doc-title {
      text-align: right;
    }
    .doc-title h1 {
      font-size: 20px;
      font-weight: 700;
      color: #1a1a1a;
      text-transform: uppercase;
      letter-spacing: 1px;
    }
    .doc-number {
      font-size: 13px;
      color: #1a7f5a;
      font-weight: 600;
      margin-top: 3px;
    }
    .doc-status {
      display: inline-block;
      margin-top: 5px;
      padding: 3px 10px;
      border-radius: 20px;
      font-size: 11px;
      font-weight: 600;
      background: #e8f5ef;
      color: #1a7f5a;
      border: 1px solid #b2dfc8;
    }

    /* Grid de informação */
    .info-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 14px;
      margin-bottom: 20px;
    }
    .info-box {
      background: #f9fafb;
      border: 1px solid #e8ecef;
      border-radius: 8px;
      padding: 12px 14px;
    }
    .info-box-title {
      font-size: 10px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: .08em;
      color: #1a7f5a;
      margin-bottom: 8px;
      border-bottom: 1px solid #e8ecef;
      padding-bottom: 5px;
    }
    .info-row {
      display: flex;
      justify-content: space-between;
      margin-bottom: 4px;
      font-size: 12px;
    }
    .info-label { color: #666; }
    .info-value { font-weight: 600; color: #1a1a1a; text-align: right; }

    /* Tabela de itens */
    .items-title {
      font-size: 11px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: .08em;
      color: #1a7f5a;
      margin-bottom: 8px;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 16px;
    }
    thead tr {
      background: #1a7f5a;
      color: #fff;
    }
    thead th {
      padding: 8px 10px;
      font-size: 11px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: .05em;
    }
    thead th:first-child { border-radius: 4px 0 0 4px; }
    thead th:last-child  { border-radius: 0 4px 4px 0; }
    tbody tr:nth-child(even) { background: #f6fbf8; }
    tbody tr:last-child td { border-bottom: 2px solid #1a7f5a; }
    tbody td {
      padding: 9px 10px;
      font-size: 12px;
      border-bottom: 1px solid #eee;
      vertical-align: middle;
    }
    .text-right { text-align: right; }
    .text-center { text-align: center; }
    .produto-nome { font-weight: 600; }
    .produto-unidade { font-size: 10px; color: #888; }

    /* Totais */
    .totais {
      margin-left: auto;
      width: 260px;
    }
    .totais-row {
      display: flex;
      justify-content: space-between;
      padding: 5px 0;
      font-size: 13px;
      border-bottom: 1px solid #f0f0f0;
    }
    .totais-row:last-child {
      border-bottom: none;
      font-size: 16px;
      font-weight: 700;
      color: #1a7f5a;
      padding-top: 8px;
      margin-top: 4px;
      border-top: 2px solid #1a7f5a;
    }
    .totais-label { color: #555; }
    .totais-value { font-weight: 600; }

    /* Observações */
    .obs-box {
      margin-top: 20px;
      background: #fffbeb;
      border: 1px solid #fde68a;
      border-radius: 8px;
      padding: 10px 14px;
    }
    .obs-title {
      font-size: 10px;
      font-weight: 700;
      text-transform: uppercase;
      color: #92400e;
      margin-bottom: 4px;
    }
    .obs-text { font-size: 12px; color: #555; }

    /* Rodapé */
    .footer {
      margin-top: 30px;
      padding-top: 14px;
      border-top: 1px solid #e0e0e0;
      display: flex;
      justify-content: space-between;
      align-items: flex-end;
    }
    .footer-left { font-size: 11px; color: #888; }
    .assinatura {
      text-align: center;
      font-size: 11px;
      color: #555;
    }
    .assinatura-linha {
      width: 160px;
      border-top: 1px solid #999;
      margin: 28px auto 4px;
    }

    /* Print */
    @media print {
      body { background: #fff; }
      .no-print { display: none !important; }
      .page {
        margin: 0;
        padding: 12mm 15mm 12mm 15mm;
        box-shadow: none;
        width: 100%;
        min-height: auto;
      }
    }
  </style>
</head>
<body>

<!-- Botões (não aparecem no PDF) -->
<div class="no-print">
  <a href="<?= $appUrl ?>/compras/<?= $c['id'] ?>" class="btn-voltar">
    ← Voltar
  </a>
  <button class="btn-print" onclick="window.print()">
    ⬇ Guardar / Imprimir PDF
  </button>
</div>

<div class="page">

  <!-- Cabeçalho -->
  <div class="header">
    <div>
      <div class="company-name"><?= htmlspecialchars($_ENV['APP_NAME'] ?? 'KewanFarma') ?></div>
      <div class="company-sub">Farmácia</div>
    </div>
    <div class="doc-title">
      <h1>Ordem de Compra</h1>
      <div class="doc-number"><?= htmlspecialchars($c['numero_compra']) ?></div>
      <div class="doc-status"><?= $statusLabels[$c['status']] ?? $c['status'] ?></div>
    </div>
  </div>

  <!-- Info grid -->
  <div class="info-grid">

    <!-- Fornecedor -->
    <div class="info-box">
      <div class="info-box-title">📦 Fornecedor</div>
      <div style="font-weight:700;font-size:13px;margin-bottom:6px">
        <?= htmlspecialchars($c['fornecedor_nome']) ?>
      </div>
      <?php if ($c['fornecedor_telefone']): ?>
      <div class="info-row">
        <span class="info-label">Telefone</span>
        <span class="info-value"><?= htmlspecialchars($c['fornecedor_telefone']) ?></span>
      </div>
      <?php endif; ?>
      <?php if ($c['fornecedor_email']): ?>
      <div class="info-row">
        <span class="info-label">Email</span>
        <span class="info-value"><?= htmlspecialchars($c['fornecedor_email']) ?></span>
      </div>
      <?php endif; ?>
    </div>

    <!-- Detalhes da encomenda -->
    <div class="info-box">
      <div class="info-box-title">📋 Detalhes da Encomenda</div>
      <div class="info-row">
        <span class="info-label">Data do Pedido</span>
        <span class="info-value"><?= date('d/m/Y', strtotime($c['data_pedido'])) ?></span>
      </div>
      <?php if ($c['data_entrega']): ?>
      <div class="info-row">
        <span class="info-label">Entrega Prevista</span>
        <span class="info-value"><?= date('d/m/Y', strtotime($c['data_entrega'])) ?></span>
      </div>
      <?php endif; ?>
      <?php if ($c['numero_fatura']): ?>
      <div class="info-row">
        <span class="info-label">Nº Fatura</span>
        <span class="info-value"><?= htmlspecialchars($c['numero_fatura']) ?></span>
      </div>
      <?php endif; ?>
      <div class="info-row">
        <span class="info-label">Emitido por</span>
        <span class="info-value"><?= htmlspecialchars($c['usuario_nome']) ?></span>
      </div>
      <div class="info-row">
        <span class="info-label">Data de emissão</span>
        <span class="info-value"><?= date('d/m/Y H:i', strtotime($c['criado_em'])) ?></span>
      </div>
    </div>

  </div>

  <!-- Itens -->
  <div class="items-title">Produtos Encomendados</div>
  <table>
    <thead>
      <tr>
        <th style="width:35px">#</th>
        <th>Produto</th>
        <th class="text-center" style="width:80px">Qtd.</th>
        <th class="text-right" style="width:110px">Preço Unit.</th>
        <th class="text-right" style="width:110px">Subtotal</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($c['itens'] as $i => $item): ?>
      <tr>
        <td class="text-center" style="color:#888"><?= $i + 1 ?></td>
        <td>
          <div class="produto-nome"><?= htmlspecialchars($item['produto_nome']) ?></div>
          <?php if ($item['numero_lote']): ?>
          <div class="produto-unidade">Lote: <?= htmlspecialchars($item['numero_lote']) ?>
            <?php if ($item['validade_lote']): ?>
              · Val: <?= date('d/m/Y', strtotime($item['validade_lote'])) ?>
            <?php endif; ?>
          </div>
          <?php endif; ?>
        </td>
        <td class="text-center" style="font-weight:700"><?= $item['quantidade'] ?></td>
        <td class="text-right"><?= number_format($item['preco_unitario'], 2, ',', '.') ?> MZN</td>
        <td class="text-right" style="font-weight:600;color:#1a7f5a">
          <?= number_format($item['subtotal'], 2, ',', '.') ?> MZN
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <!-- Totais -->
  <div class="totais">
    <div class="totais-row">
      <span class="totais-label">Subtotal</span>
      <span class="totais-value"><?= number_format($c['subtotal'], 2, ',', '.') ?> MZN</span>
    </div>
    <?php if ($c['desconto'] > 0): ?>
    <div class="totais-row">
      <span class="totais-label">Desconto</span>
      <span class="totais-value" style="color:#dc3545">-<?= number_format($c['desconto'], 2, ',', '.') ?> MZN</span>
    </div>
    <?php endif; ?>
    <div class="totais-row">
      <span class="totais-label">TOTAL</span>
      <span class="totais-value"><?= number_format($c['total'], 2, ',', '.') ?> MZN</span>
    </div>
  </div>

  <!-- Observações -->
  <?php if ($c['observacoes']): ?>
  <div class="obs-box">
    <div class="obs-title">⚠ Observações</div>
    <div class="obs-text"><?= nl2br(htmlspecialchars($c['observacoes'])) ?></div>
  </div>
  <?php endif; ?>

  <!-- Rodapé -->
  <div class="footer">
    <div class="footer-left">
      <?= htmlspecialchars($_ENV['APP_NAME'] ?? 'KewanFarma') ?><br>
      Documento gerado em <?= date('d/m/Y \à\s H:i') ?>
    </div>
    <div class="assinatura">
      <div class="assinatura-linha"></div>
      Assinatura e Carimbo do Fornecedor
    </div>
  </div>

</div>

<script>
  // Abrir diálogo de impressão automaticamente (opcional)
  // window.onload = () => window.print();
</script>
</body>
</html>
