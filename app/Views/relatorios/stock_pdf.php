<?php
// app/Views/relatorios/stock_pdf.php
?>
<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <title>Relatório de Stock — <?= date('d/m/Y') ?></title>
  <style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family:'Segoe UI',Arial,sans-serif; font-size:12px; color:#1a1a1a; background:#f5f5f5; }
    .no-print { position:fixed; top:20px; right:20px; display:flex; gap:10px; z-index:999; }
    .btn-print { background:#1a7f5a; color:#fff; border:none; padding:10px 20px; border-radius:8px; font-size:14px; font-weight:600; cursor:pointer; display:flex; align-items:center; gap:8px; box-shadow:0 2px 8px rgba(0,0,0,.2); }
    .btn-voltar { background:#fff; color:#333; border:1px solid #ddd; padding:10px 20px; border-radius:8px; font-size:14px; cursor:pointer; text-decoration:none; display:flex; align-items:center; gap:8px; }
    .page { width:210mm; min-height:297mm; background:#fff; margin:30px auto; padding:16mm 18mm; box-shadow:0 4px 24px rgba(0,0,0,.12); }
    .header { display:flex; justify-content:space-between; align-items:flex-start; padding-bottom:12px; border-bottom:3px solid #1a7f5a; margin-bottom:16px; }
    .company-name { font-size:20px; font-weight:700; color:#1a7f5a; }
    .company-sub  { font-size:11px; color:#666; margin-top:2px; }
    .doc-title h1 { font-size:18px; font-weight:700; text-transform:uppercase; letter-spacing:1px; text-align:right; }
    .doc-date     { font-size:12px; color:#1a7f5a; font-weight:600; text-align:right; margin-top:3px; }
    .resumo-grid  { display:grid; grid-template-columns:repeat(4,1fr); gap:10px; margin-bottom:16px; }
    .resumo-box   { background:#f9fafb; border:1px solid #e8ecef; border-radius:6px; padding:10px 12px; text-align:center; }
    .resumo-val   { font-size:15px; font-weight:700; color:#1a7f5a; }
    .resumo-lbl   { font-size:10px; color:#666; text-transform:uppercase; letter-spacing:.05em; margin-top:2px; }
    .section-title{ font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.08em; color:#1a7f5a; border-bottom:1px solid #e0e0e0; padding-bottom:5px; margin:14px 0 8px; }
    table { width:100%; border-collapse:collapse; }
    thead tr { background:#1a7f5a; color:#fff; }
    thead th  { padding:6px 8px; font-size:10px; font-weight:600; text-transform:uppercase; }
    tbody tr:nth-child(even) { background:#f6fbf8; }
    tbody tr.danger  { background:#fff5f5; }
    tbody tr.warning { background:#fffbeb; }
    tbody td  { padding:6px 8px; font-size:11px; border-bottom:1px solid #eee; }
    tfoot td  { padding:7px 8px; font-weight:700; font-size:12px; border-top:2px solid #1a7f5a; background:#f0fbf6; }
    .text-right  { text-align:right; }
    .text-center { text-align:center; }
    .badge { display:inline-block; padding:2px 7px; border-radius:10px; font-size:10px; font-weight:600; }
    .badge-success { background:#d1fae5; color:#065f46; }
    .badge-warning { background:#fef3c7; color:#92400e; }
    .badge-danger  { background:#fee2e2; color:#991b1b; }
    .footer { margin-top:20px; padding-top:12px; border-top:1px solid #e0e0e0; display:flex; justify-content:space-between; }
    .footer-left { font-size:10px; color:#888; }
    @media print {
      body { background:#fff; }
      .no-print { display:none !important; }
      .page { margin:0; padding:12mm 15mm; box-shadow:none; width:100%; }
    }
  </style>
</head>
<body>

<div class="no-print">
  <a href="<?= $appUrl ?>/relatorios/stock" class="btn-voltar">← Voltar</a>
  <button class="btn-print" onclick="window.print()">⬇ Guardar / Imprimir PDF</button>
</div>

<div class="page">

  <div class="header">
    <div>
      <?php if (!empty($config['logo_farmacia'])): ?>
      <div style="margin-bottom:8px">
        <img src="<?= $appUrl ?>/storage/uploads/<?= htmlspecialchars($config['logo_farmacia']) ?>"
             alt="Logo da farmácia"
             style="max-width:100px; max-height:60px; object-fit:contain;">
      </div>
      <?php endif; ?>
      <div class="company-name"><?= htmlspecialchars($config['nome_farmacia'] ?? $_ENV['APP_NAME'] ?? 'KewanFarma') ?></div>
      <div class="company-sub"><?= htmlspecialchars($config['endereco_farmacia'] ?: 'Farmácia') ?></div>
      <?php if (!empty($config['telefone_farmacia']) || !empty($config['email_farmacia']) || !empty($config['nuit_farmacia'])): ?>
      <div class="company-sub">
        <?= implode(' • ', array_filter([
          !empty($config['telefone_farmacia']) ? 'Tel: ' . htmlspecialchars($config['telefone_farmacia']) : null,
          !empty($config['email_farmacia']) ? 'Email: ' . htmlspecialchars($config['email_farmacia']) : null,
          !empty($config['nuit_farmacia']) ? 'NUIT: ' . htmlspecialchars($config['nuit_farmacia']) : null,
        ])) ?>
      </div>
      <?php endif; ?>
    </div>
    <div>
      <div class="doc-title"><h1>Relatório de Stock</h1></div>
      <div class="doc-date">Gerado em <?= date('d/m/Y H:i') ?></div>
    </div>
  </div>

  <!-- Resumo -->
  <div class="resumo-grid">
    <div class="resumo-box">
      <div class="resumo-val"><?= $resumo['total_produtos'] ?></div>
      <div class="resumo-lbl">Total Produtos</div>
    </div>
    <div class="resumo-box">
      <div class="resumo-val" style="color:#d97706"><?= $resumo['stock_baixo'] ?></div>
      <div class="resumo-lbl">Stock Baixo</div>
    </div>
    <div class="resumo-box">
      <div class="resumo-val" style="color:#dc2626"><?= $resumo['esgotados'] ?></div>
      <div class="resumo-lbl">Esgotados</div>
    </div>
    <div class="resumo-box">
      <div class="resumo-val"><?= number_format($resumo['valor_total'],2,',','.') ?></div>
      <div class="resumo-lbl">Valor Total (MZN)</div>
    </div>
  </div>

  <!-- Tabela -->
  <div class="section-title">Inventário Detalhado (<?= count($produtos) ?> produtos)</div>
  <table>
    <thead>
      <tr>
        <th>Produto</th>
        <th>Categoria</th>
        <th class="text-center">Stock Act.</th>
        <th class="text-center">Stock Mín.</th>
        <th class="text-right">Preço Compra</th>
        <th class="text-right">Preço Venda</th>
        <th class="text-right">Valor Stock</th>
        <th class="text-center">Estado</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($produtos as $p):
        if ($p['estoque_actual'] <= 0) {
            $cls   = 'danger';
            $badge = ['label'=>'Esgotado',    'cls'=>'badge-danger'];
        } elseif ($p['estoque_actual'] <= $p['estoque_min']) {
            $cls   = 'warning';
            $badge = ['label'=>'Stock Baixo', 'cls'=>'badge-warning'];
        } else {
            $cls   = '';
            $badge = ['label'=>'Normal',      'cls'=>'badge-success'];
        }
        $fator      = max(1, (float)($p['fator_conversao'] ?? 1));
        $uVenda     = $p['unidade_venda'] ?? 'unidade';
        $custoUnit  = $fator > 0 ? $p['preco_compra'] / $fator : $p['preco_compra'];
        $lucroUnit  = $p['preco_venda'] - $custoUnit;
        $margem     = $p['preco_venda'] > 0 ? round($lucroUnit / $p['preco_venda'] * 100, 1) : 0;
        $valorStock = $p['estoque_actual'] * $custoUnit;
      ?>
      <tr class="<?= $cls ?>">
        <td style="font-weight:600"><?= htmlspecialchars($p['nome']) ?></td>
        <td><?= htmlspecialchars($p['categoria_nome']) ?></td>
        <td class="text-center" style="font-weight:700"><?= $p['estoque_actual'] ?> <small><?= htmlspecialchars($uVenda) ?></small></td>
        <td class="text-center"><?= $p['estoque_min'] ?></td>
        <td class="text-right">
          <?= number_format($custoUnit,2,',','.') ?>
          <?php if ($fator > 1): ?><br><small style="color:#9ca3af"><?= number_format($p['preco_compra'],2,',','.') ?>/<?= htmlspecialchars($p['unidade_compra'] ?? 'cx') ?></small><?php endif; ?>
        </td>
        <td class="text-right"><?= number_format($p['preco_venda'],2,',','.') ?></td>
        <td class="text-right" style="font-weight:600;color:<?= $margem >= 0 ? '#1a7f5a' : '#dc2626' ?>"><?= $margem ?>%</td>
        <td class="text-right" style="font-weight:600;color:#1a7f5a"><?= number_format($valorStock,2,',','.') ?></td>
        <td class="text-center"><span class="badge <?= $badge['cls'] ?>"><?= $badge['label'] ?></span></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
    <tfoot>
      <tr>
        <td colspan="7">TOTAL INVENTÁRIO</td>
        <td class="text-right" style="color:#1a7f5a"><?= number_format($resumo['valor_total'],2,',','.') ?> MZN</td>
        <td></td>
      </tr>
    </tfoot>
  </table>

  <div class="footer">
    <div class="footer-left">
      <?= htmlspecialchars($config['nome_farmacia'] ?? $_ENV['APP_NAME'] ?? 'KewanFarma') ?><br>
      <?= htmlspecialchars(trim(implode(' • ', array_filter([
        !empty($config['endereco_farmacia']) ? htmlspecialchars($config['endereco_farmacia']) : null,
        !empty($config['telefone_farmacia']) ? 'Tel: ' . htmlspecialchars($config['telefone_farmacia']) : null,
        !empty($config['email_farmacia']) ? 'Email: ' . htmlspecialchars($config['email_farmacia']) : null,
        !empty($config['nuit_farmacia']) ? 'NUIT: ' . htmlspecialchars($config['nuit_farmacia']) : null,
      ]))) ?: 'Farmácia') ?><br>
      Gerado em <?= date('d/m/Y \à\s H:i') ?>
    </div>
  </div>

</div>
</body>
</html>
