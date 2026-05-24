<?php
$perfisLabel = [
    'admin'        => 'Administrador',
    'farmaceutico' => 'Farmacêutico',
    'caixa'        => 'Caixa',
    'tecnico'      => 'Técnico',
];
$medalhas = ['🥇','🥈','🥉'];
$totalGeral = array_sum(array_column($ranking, 'valor_total'));
?>
<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <title>Relatório de Funcionários — <?= date('d/m/Y') ?></title>
  <style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family:'Segoe UI',Arial,sans-serif; font-size:12px; color:#1a1a1a; background:#f5f5f5; }
    .no-print { position:fixed; top:20px; right:20px; display:flex; gap:10px; z-index:999; }
    .btn-print  { background:#1a7f5a; color:#fff; border:none; padding:10px 20px; border-radius:8px; font-size:14px; font-weight:600; cursor:pointer; }
    .btn-voltar { background:#fff; color:#333; border:1px solid #ddd; padding:10px 20px; border-radius:8px; font-size:14px; cursor:pointer; text-decoration:none; }
    .page { width:210mm; min-height:297mm; background:#fff; margin:30px auto; padding:16mm 18mm; box-shadow:0 4px 24px rgba(0,0,0,.12); }
    .header { display:flex; justify-content:space-between; align-items:flex-start; padding-bottom:12px; border-bottom:3px solid #1a7f5a; margin-bottom:16px; }
    .company-name { font-size:20px; font-weight:700; color:#1a7f5a; }
    .company-sub  { font-size:11px; color:#666; margin-top:2px; }
    .doc-title h1 { font-size:18px; font-weight:700; text-transform:uppercase; letter-spacing:1px; text-align:right; }
    .doc-period   { font-size:12px; color:#1a7f5a; font-weight:600; text-align:right; margin-top:3px; }
    .resumo-grid  { display:grid; grid-template-columns:repeat(4,1fr); gap:10px; margin-bottom:16px; }
    .resumo-box   { background:#f9fafb; border:1px solid #e8ecef; border-radius:6px; padding:10px 12px; text-align:center; }
    .resumo-val   { font-size:16px; font-weight:700; color:#1a7f5a; }
    .resumo-lbl   { font-size:10px; color:#666; text-transform:uppercase; margin-top:2px; }
    .section-title{ font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.08em; color:#1a7f5a; border-bottom:1px solid #e0e0e0; padding-bottom:5px; margin:14px 0 8px; }
    table { width:100%; border-collapse:collapse; margin-bottom:12px; }
    thead tr { background:#1a7f5a; color:#fff; }
    thead th  { padding:6px 8px; font-size:10px; font-weight:600; text-transform:uppercase; }
    tbody tr:nth-child(even) { background:#f6fbf8; }
    tbody tr.top1 { background:#fffbeb; }
    tbody td  { padding:7px 8px; font-size:11px; border-bottom:1px solid #eee; vertical-align:middle; }
    tfoot td  { padding:7px 8px; font-weight:700; font-size:12px; border-top:2px solid #1a7f5a; background:#f0fbf6; }
    .text-right  { text-align:right; }
    .text-center { text-align:center; }
    .progress-bar-wrap { background:#e5e7eb; border-radius:4px; height:6px; width:100%; }
    .progress-bar-fill { background:#1a7f5a; border-radius:4px; height:6px; }
    .footer { margin-top:20px; padding-top:12px; border-top:1px solid #e0e0e0; font-size:10px; color:#888; display:flex; justify-content:space-between; }
    .assinatura { text-align:center; font-size:10px; color:#555; }
    .assinatura-linha { width:150px; border-top:1px solid #999; margin:24px auto 4px; }
    @media print {
      body { background:#fff; }
      .no-print { display:none !important; }
      .page { margin:0; padding:12mm 15mm; box-shadow:none; width:100%; }
    }
  </style>
</head>
<body>

<div class="no-print">
  <a href="<?= $appUrl ?>/relatorios/funcionarios" class="btn-voltar">← Voltar</a>
  <button class="btn-print" onclick="window.print()">⬇ Guardar / Imprimir PDF</button>
</div>

<div class="page">

  <div class="header">
    <div>
      <div class="company-name"><?= htmlspecialchars($_ENV['APP_NAME'] ?? 'KewanFarma') ?></div>
      <div class="company-sub">Farmácia</div>
    </div>
    <div>
      <div class="doc-title"><h1>Desempenho de Funcionários</h1></div>
      <div class="doc-period">
        <?= date('d/m/Y', strtotime($filtros['data_inicio'])) ?>
        &nbsp;—&nbsp;
        <?= date('d/m/Y', strtotime($filtros['data_fim'])) ?>
      </div>
    </div>
  </div>

  <!-- Resumo -->
  <div class="resumo-grid">
    <div class="resumo-box">
      <div class="resumo-val"><?= $resumo['total_funcionarios'] ?></div>
      <div class="resumo-lbl">Funcionários</div>
    </div>
    <div class="resumo-box">
      <div class="resumo-val"><?= $resumo['total_vendas'] ?></div>
      <div class="resumo-lbl">Total Vendas</div>
    </div>
    <div class="resumo-box">
      <div class="resumo-val"><?= number_format($resumo['valor_total']??0,2,',','.') ?></div>
      <div class="resumo-lbl">Valor Total (MZN)</div>
    </div>
    <div class="resumo-box">
      <div class="resumo-val"><?= number_format($resumo['melhor_ticket']??0,2,',','.') ?></div>
      <div class="resumo-lbl">Melhor Ticket (MZN)</div>
    </div>
  </div>

  <!-- Ranking -->
  <div class="section-title">Ranking de Desempenho</div>
  <table>
    <thead>
      <tr>
        <th class="text-center" style="width:40px">Pos.</th>
        <th>Funcionário</th>
        <th class="text-center" style="width:100px">Perfil</th>
        <th class="text-center" style="width:80px">Nº Vendas</th>
        <th class="text-center" style="width:80px">Itens</th>
        <th class="text-right"  style="width:110px">Ticket Médio</th>
        <th class="text-right"  style="width:120px">Valor Total</th>
        <th style="width:120px">Participação</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($ranking as $i => $fu):
        $pct    = $totalGeral > 0 ? round($fu['valor_total'] / $totalGeral * 100) : 0;
        $perfil = $perfisLabel[$fu['perfil'] ?? ''] ?? ($fu['perfil'] ?? '—');
      ?>
      <tr class="<?= $i === 0 ? 'top1' : '' ?>">
        <td class="text-center" style="font-size:16px">
          <?= $medalhas[$i] ?? ($i+1) ?>
        </td>
        <td>
          <div style="font-weight:600"><?= htmlspecialchars($fu['nome']) ?></div>
          <div style="font-size:10px;color:#888"><?= htmlspecialchars($fu['email'] ?? '') ?></div>
        </td>
        <td class="text-center" style="font-size:10px"><?= $perfil ?></td>
        <td class="text-center" style="font-weight:700"><?= $fu['total_vendas'] ?></td>
        <td class="text-center"><?= number_format($fu['total_itens'] ?? 0) ?></td>
        <td class="text-right"><?= number_format($fu['ticket_medio'],2,',','.') ?> MZN</td>
        <td class="text-right" style="font-weight:700;color:#1a7f5a">
          <?= number_format($fu['valor_total'],2,',','.') ?> MZN
        </td>
        <td>
          <div style="display:flex;align-items:center;gap:6px">
            <div class="progress-bar-wrap" style="flex:1">
              <div class="progress-bar-fill" style="width:<?= $pct ?>%"></div>
            </div>
            <span style="font-size:10px;color:#666;min-width:28px"><?= $pct ?>%</span>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
    <tfoot>
      <tr>
        <td colspan="3">TOTAL</td>
        <td class="text-center"><?= $resumo['total_vendas'] ?></td>
        <td></td>
        <td></td>
        <td class="text-right" style="color:#1a7f5a"><?= number_format($resumo['valor_total']??0,2,',','.') ?> MZN</td>
        <td></td>
      </tr>
    </tfoot>
  </table>

  <div class="footer">
    <div><?= htmlspecialchars($_ENV['APP_NAME'] ?? 'KewanFarma') ?> · Gerado em <?= date('d/m/Y \à\s H:i') ?></div>
    <div class="assinatura">
      <div class="assinatura-linha"></div>
      Responsável
    </div>
  </div>

</div>
</body>
</html>
