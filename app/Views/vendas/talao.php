<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Talão <?= htmlspecialchars($venda['numero_venda']) ?> — KewanFarma</title>
  <style>
    @page  { margin: 5mm; }
    @media print {
      body  { padding: 0; margin: 0; }
      .no-print { display: none !important; }
    }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: 'Courier New', Courier, monospace;
      font-size: 12px;
      color: #000;
      background: #fff;
      max-width: 320px;
      margin: 0 auto;
      padding: 10px;
    }
    h1     { font-size: 16px; font-weight: bold; text-align: center; }
    h2     { font-size: 13px; font-weight: bold; text-align: center; }
    .c     { text-align: center; }
    .r     { text-align: right; }
    .b     { font-weight: bold; }
    .linha { border-top: 1px dashed #555; margin: 6px 0; }
    .row   { display: flex; justify-content: space-between; margin: 2px 0; }
    .row-b { display: flex; justify-content: space-between; font-weight: bold; font-size: 14px; margin: 3px 0; }
    .item-nome { word-break: break-word; }
    .btn-imp {
      display: block; width: 100%; padding: 10px;
      background: #1a7f5a; color: #fff;
      border: none; border-radius: 6px;
      cursor: pointer; font-size: 14px;
      margin-top: 16px;
    }
  </style>
</head>
<body>

  <!-- Cabeçalho -->
  <?php if (!empty($config['logo_farmacia'])): ?>
  <div class="c" style="margin-bottom:8px">
    <img src="<?= ($_ENV['APP_URL'] ?? '') ?>/storage/uploads/<?= htmlspecialchars($config['logo_farmacia']) ?>"
         alt="Logo da farmácia"
         style="max-width:100px; max-height:70px; object-fit:contain;">
  </div>
  <?php endif; ?>
  <h1><?= htmlspecialchars($config['nome_farmacia'] ?? 'KewanFarma') ?></h1>
  <?php if (!empty($config['endereco_farmacia'])): ?>
  <p class="c" style="font-size:11px"><?= htmlspecialchars($config['endereco_farmacia']) ?></p>
  <?php endif; ?>
  <?php if (!empty($config['telefone_farmacia'])): ?>
  <p class="c" style="font-size:11px">Tel: <?= htmlspecialchars($config['telefone_farmacia']) ?></p>
  <?php endif; ?>
  <?php if (!empty($config['email_farmacia'])): ?>
  <p class="c" style="font-size:11px">Email: <?= htmlspecialchars($config['email_farmacia']) ?></p>
  <?php endif; ?>
  <?php if (!empty($config['nuit_farmacia'])): ?>
  <p class="c" style="font-size:11px">NUIT: <?= htmlspecialchars($config['nuit_farmacia']) ?></p>
  <?php endif; ?>

  <div class="linha"></div>
  <h2>TALÃO DE VENDA</h2>
  <div class="linha"></div>

  <!-- Info da venda -->
  <div class="row"><span>Nº:</span><span class="b"><?= htmlspecialchars($venda['numero_venda']) ?></span></div>
  <div class="row"><span>Data:</span><span><?= date('d/m/Y H:i', strtotime($venda['criado_em'])) ?></span></div>
  <div class="row"><span>Atendido por:</span><span><?= htmlspecialchars($venda['usuario_nome'] ?? '') ?></span></div>
  <?php if (!empty($venda['cliente_nome'])): ?>
  <div class="row"><span>Cliente:</span><span><?= htmlspecialchars($venda['cliente_nome']) ?></span></div>
  <?php endif; ?>
  <?php if (!empty($venda['cliente_nuit'])): ?>
  <div class="row"><span>NUIT:</span><span><?= htmlspecialchars($venda['cliente_nuit']) ?></span></div>
  <?php endif; ?>

  <div class="linha"></div>
  <div class="b c">PRODUTOS</div>
  <div class="linha"></div>

  <!-- Itens -->
  <?php foreach ($venda['itens'] as $item): ?>
  <div class="item-nome"><?= htmlspecialchars($item['produto_nome']) ?></div>
  <div class="row">
    <span>
      <?= $item['quantidade'] ?> x
      MT <?= number_format($item['preco_unitario'], 2, ',', '.') ?>
    </span>
    <span>MT <?= number_format($item['subtotal'], 2, ',', '.') ?></span>
  </div>
  <?php if ($item['desconto_item'] > 0): ?>
  <div class="row r" style="color:#777;font-size:11px">
    <span>&nbsp;</span>
    <span>Desc: -MT <?= number_format($item['desconto_item'], 2, ',', '.') ?></span>
  </div>
  <?php endif; ?>
  <?php endforeach; ?>

  <div class="linha"></div>

  <!-- Totais -->
  <div class="row"><span>Subtotal:</span><span>MT <?= number_format($venda['subtotal'], 2, ',', '.') ?></span></div>
  <?php if ($venda['desconto'] > 0): ?>
  <div class="row" style="color:#c00">
    <span>Desconto:</span><span>-MT <?= number_format($venda['desconto'], 2, ',', '.') ?></span>
  </div>
  <?php endif; ?>
  <div class="linha"></div>
  <div class="row-b">
    <span>TOTAL:</span>
    <span>MT <?= number_format($venda['total'], 2, ',', '.') ?></span>
  </div>
  <div class="linha"></div>

  <!-- Pagamento -->
  <?php
  $fpLabels = [
    'dinheiro'      => 'Dinheiro',
    'mpesa'         => 'M-Pesa',
    'emola'         => 'e-Mola',
    'cartao_debito' => 'Cartão Débito',
    'cartao_credito'=> 'Cartão Crédito',
    'transferencia' => 'Transferência',
    'credito'       => 'Crédito',
  ];
  $fp = $fpLabels[$venda['forma_pagamento']] ?? $venda['forma_pagamento'];
  $troco = max(0, $venda['valor_pago'] - $venda['total']);
  ?>
  <div class="row"><span>Forma pag.:</span><span><?= $fp ?></span></div>
  <div class="row"><span>Valor pago:</span><span>MT <?= number_format($venda['valor_pago'], 2, ',', '.') ?></span></div>
  <?php if ($troco > 0): ?>
  <div class="row b"><span>Troco:</span><span>MT <?= number_format($troco, 2, ',', '.') ?></span></div>
  <?php endif; ?>

  <div class="linha"></div>
  <p class="c">Obrigado pela sua preferência!</p>
  <p class="c" style="font-size:10px;margin-top:3px">
    <?= date('d/m/Y \à\s H:i:s') ?>
  </p>

  <!-- Botão de impressão (não impresso) -->
  <button class="btn-imp no-print" onclick="window.print()">
    🖨️ Imprimir Talão
  </button>

  <script>
    // Auto-imprimir ao abrir (com pequeno delay para garantir render)
    window.addEventListener('load', function () {
      setTimeout(function () { window.print(); }, 500);
    });
  </script>
</body>
</html>
