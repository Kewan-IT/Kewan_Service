<?php
$tipoLabels = ['proximos'=>'A vencer', 'vencidos'=>'Já vencidos', 'todos'=>'Vencidos + Próximos'];
$valorRisco = array_sum(array_column($lotes, 'valor_em_risco'));
?>
<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <title>Lotes a Vencer — <?= date('d/m/Y') ?></title>
  <style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family:'Segoe UI',Arial,sans-serif; font-size:12px; color:#1a1a1a; background:#f5f5f5; }
    .no-print { position:fixed; top:20px; right:20px; display:flex; gap:10px; z-index:999; }
    .btn-print  { background:#1a7f5a; color:#fff; border:none; padding:10px 20px; border-radius:8px; font-size:14px; font-weight:600; cursor:pointer; display:flex; align-items:center; gap:8px; }
    .btn-voltar { background:#fff; color:#333; border:1px solid #ddd; padding:10px 20px; border-radius:8px; font-size:14px; cursor:pointer; text-decoration:none; display:flex; align-items:center; gap:8px; }
    .page  { width:210mm; min-height:297mm; background:#fff; margin:30px auto; padding:16mm 18mm; box-shadow:0 4px 24px rgba(0,0,0,.12); }
    .header { display:flex; justify-content:space-between; align-items:flex-start; padding-bottom:12px; border-bottom:3px solid #1a7f5a; margin-bottom:16px; }
    .company-name { font-size:20px; font-weight:700; color:#1a7f5a; }
    .company-sub  { font-size:11px; color:#666; margin-top:2px; }
    .doc-title h1 { font-size:18px; font-weight:700; text-transform:uppercase; letter-spacing:1px; text-align:right; }
    .doc-sub      { font-size:12px; color:#666; text-align:right; margin-top:3px; }
    .resumo-grid  { display:grid; grid-template-columns:repeat(4,1fr); gap:10px; margin-bottom:16px; }
    .resumo-box   { border-radius:6px; padding:10px 12px; text-align:center; }
    .resumo-box.red    { background:#fee2e2; border:1px solid #fca5a5; }
    .resumo-box.orange { background:#fef3c7; border:1px solid #fcd34d; }
    .resumo-box.blue   { background:#dbeafe; border:1px solid #93c5fd; }
    .resumo-box.grey   { background:#f3f4f6; border:1px solid #d1d5db; }
    .resumo-val   { font-size:18px; font-weight:700; }
    .resumo-lbl   { font-size:10px; color:#555; text-transform:uppercase; margin-top:2px; }
    .alerta { background:#fffbeb; border:1px solid #fcd34d; border-radius:6px; padding:10px 14px; margin-bottom:14px; font-size:12px; }
    .section-title { font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.08em; color:#1a7f5a; border-bottom:1px solid #e0e0e0; padding-bottom:5px; margin:14px 0 8px; }
    table { width:100%; border-collapse:collapse; }
    thead tr   { background:#1a7f5a; color:#fff; }
    thead th   { padding:6px 8px; font-size:10px; font-weight:600; text-transform:uppercase; }
    tbody tr.vencido  { background:#fee2e2; }
    tbody tr.urgente  { background:#fff1f2; }
    tbody tr.aviso    { background:#fffbeb; }
    tbody td   { padding:6px 8px; font-size:11px; border-bottom:1px solid #eee; vertical-align:middle; }
    tfoot td   { padding:7px 8px; font-weight:700; font-size:12px; border-top:2px solid #dc2626; background:#fee2e2; }
    .text-right  { text-align:right; }
    .text-center { text-align:center; }
    .badge { display:inline-block; padding:2px 7px; border-radius:10px; font-size:10px; font-weight:600; }
    .badge-red    { background:#fee2e2; color:#991b1b; }
    .badge-orange { background:#fef3c7; color:#92400e; }
    .badge-blue   { background:#dbeafe; color:#1e40af; }
    .badge-grey   { background:#f3f4f6; color:#374151; }
    .footer { margin-top:20px; padding-top:12px; border-top:1px solid #e0e0e0; font-size:10px; color:#888; }
    @media print {
      body { background:#fff; }
      .no-print { display:none !important; }
      .page { margin:0; padding:12mm 15mm; box-shadow:none; width:100%; }
    }
  </style>
</head>
<body>

<div class="no-print">
  <a href="<?= $appUrl ?>/relatorios/lotes-a-vencer" class="btn-voltar">← Voltar</a>
  <button class="btn-print" onclick="window.print()">⬇ Guardar / Imprimir PDF</button>
</div>

<div class="page">

  <div class="header">
    <div>
      <?php if (!empty($config['logo_farmacia'])): ?>
      <div style="margin-bottom:8px">
        <img src="<?= $appUrl ?>/uploads/<?= htmlspecialchars($config['logo_farmacia']) ?>"
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
      <div class="doc-title"><h1>Controlo de Validades</h1></div>
      <div class="doc-sub">
        <?= $tipoLabels[$tipo] ?? '' ?>
        <?= $tipo !== 'vencidos' ? "· Próximos {$prazo} dias" : '' ?>
        · Gerado em <?= date('d/m/Y H:i') ?>
      </div>
    </div>
  </div>

  <!-- Resumo -->
  <div class="resumo-grid">
    <div class="resumo-box red">
      <div class="resumo-val" style="color:#dc2626"><?= $resumo['vencidos'] ?? 0 ?></div>
      <div class="resumo-lbl">Lotes vencidos</div>
    </div>
    <div class="resumo-box orange">
      <div class="resumo-val" style="color:#d97706"><?= $resumo['ate_30_dias'] ?? 0 ?></div>
      <div class="resumo-lbl">Vencem em 30d</div>
    </div>
    <div class="resumo-box blue">
      <div class="resumo-val" style="color:#2563eb"><?= $resumo['ate_60_dias'] ?? 0 ?></div>
      <div class="resumo-lbl">Vencem em 60d</div>
    </div>
    <div class="resumo-box grey">
      <div class="resumo-val" style="color:#374151"><?= $resumo['ate_90_dias'] ?? 0 ?></div>
      <div class="resumo-lbl">Vencem em 90d</div>
    </div>
  </div>

  <!-- Alerta valor -->
  <?php if ($valorRisco > 0): ?>
  <div class="alerta">
    ⚠ <strong>Valor em risco:</strong>
    <?= number_format($valorRisco, 2, ',', '.') ?> MZN —
    Considere promoções, devoluções ao fornecedor ou abate controlado.
  </div>
  <?php endif; ?>

  <!-- Tabela -->
  <div class="section-title">Detalhe dos Lotes (<?= count($lotes) ?>)</div>
  <table>
    <thead>
      <tr>
        <th>Produto</th>
        <th>Categoria</th>
        <th>Fornecedor</th>
        <th class="text-center">Nº Lote</th>
        <th class="text-center">Qtd.</th>
        <th class="text-center">Validade</th>
        <th class="text-center">Dias</th>
        <th class="text-right">Valor Risco</th>
        <th class="text-center">Acção</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($lotes as $l):
        $dias    = (int)$l['dias_para_vencer'];
        $vencido = $dias < 0;
        if ($vencido)      { $cls = 'vencido'; $dStr = 'Vencido '.abs($dias).'d'; $bCls = 'badge-red'; $acc = 'Abater/Dev.'; }
        elseif ($dias<=15) { $cls = 'urgente'; $dStr = $dias.'d';  $bCls = 'badge-red';    $acc = 'Promoção urgente'; }
        elseif ($dias<=30) { $cls = 'aviso';   $dStr = $dias.'d';  $bCls = 'badge-orange'; $acc = 'Promoção'; }
        elseif ($dias<=60) { $cls = '';        $dStr = $dias.'d';  $bCls = 'badge-blue';   $acc = 'Monitorizar'; }
        else               { $cls = '';        $dStr = $dias.'d';  $bCls = 'badge-grey';   $acc = 'Normal'; }
      ?>
      <tr class="<?= $cls ?>">
        <td style="font-weight:600"><?= htmlspecialchars($l['produto_nome']) ?></td>
        <td><?= htmlspecialchars($l['categoria_nome']) ?></td>
        <td><?= htmlspecialchars($l['fornecedor_nome'] ?? '—') ?></td>
        <td class="text-center"><?= htmlspecialchars($l['numero_lote']) ?></td>
        <td class="text-center" style="font-weight:700"><?= $l['quantidade'] ?></td>
        <td class="text-center"><?= date('d/m/Y', strtotime($l['validade'])) ?></td>
        <td class="text-center"><span class="badge <?= $bCls ?>"><?= $dStr ?></span></td>
        <td class="text-right" style="font-weight:600"><?= number_format($l['valor_em_risco'],2,',','.') ?> MZN</td>
        <td class="text-center" style="font-size:10px"><?= $acc ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
    <?php if ($valorRisco > 0): ?>
    <tfoot>
      <tr>
        <td colspan="7">TOTAL VALOR EM RISCO</td>
        <td class="text-right" style="color:#dc2626"><?= number_format($valorRisco,2,',','.') ?> MZN</td>
        <td></td>
      </tr>
    </tfoot>
    <?php endif; ?>
  </table>

  <div class="footer">
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
</body>
</html>
