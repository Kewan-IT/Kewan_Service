<?php
$pagamentosLabel = [
    'dinheiro'       => 'Dinheiro',
    'mpesa'          => 'M-Pesa',
    'emola'          => 'e-Mola',
    'cartao_debito'  => 'Cartão Débito',
    'cartao_credito' => 'Cartão Crédito',
    'transferencia'  => 'Transferência',
    'credito'        => 'Crédito',
];
$statusLabel = [
    'concluida' => 'Concluída',
    'cancelada' => 'Cancelada',
    'pendente'  => 'Pendente',
    'devolvida' => 'Devolvida',
];
?>
<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <title>Relatório de Vendas — <?= date('d/m/Y', strtotime($filtros['data_inicio'])) ?> a <?= date('d/m/Y', strtotime($filtros['data_fim'])) ?></title>
  <style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family:'Segoe UI',Arial,sans-serif; font-size:12px; color:#1a1a1a; background:#f5f5f5; }

    .no-print {
      position:fixed; top:20px; right:20px; display:flex; gap:10px; z-index:999;
    }
    .btn-print {
      background:#1a7f5a; color:#fff; border:none; padding:10px 20px;
      border-radius:8px; font-size:14px; font-weight:600; cursor:pointer;
      display:flex; align-items:center; gap:8px; box-shadow:0 2px 8px rgba(0,0,0,.2);
    }
    .btn-voltar {
      background:#fff; color:#333; border:1px solid #ddd; padding:10px 20px;
      border-radius:8px; font-size:14px; cursor:pointer; text-decoration:none;
      display:flex; align-items:center; gap:8px;
    }

    .page { width:210mm; min-height:297mm; background:#fff; margin:30px auto; padding:16mm 18mm; box-shadow:0 4px 24px rgba(0,0,0,.12); }

    .header { display:flex; justify-content:space-between; align-items:flex-start; padding-bottom:12px; border-bottom:3px solid #1a7f5a; margin-bottom:16px; }
    .company-name { font-size:20px; font-weight:700; color:#1a7f5a; }
    .company-sub  { font-size:11px; color:#666; margin-top:2px; }
    .doc-title h1 { font-size:18px; font-weight:700; text-transform:uppercase; letter-spacing:1px; text-align:right; }
    .doc-period   { font-size:12px; color:#1a7f5a; font-weight:600; text-align:right; margin-top:3px; }

    .resumo-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:10px; margin-bottom:16px; }
    .resumo-box  { background:#f9fafb; border:1px solid #e8ecef; border-radius:6px; padding:10px 12px; text-align:center; }
    .resumo-val  { font-size:16px; font-weight:700; color:#1a7f5a; }
    .resumo-lbl  { font-size:10px; color:#666; text-transform:uppercase; letter-spacing:.05em; margin-top:2px; }

    .section-title { font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.08em; color:#1a7f5a; border-bottom:1px solid #e0e0e0; padding-bottom:5px; margin:14px 0 8px; }

    table { width:100%; border-collapse:collapse; margin-bottom:12px; }
    thead tr { background:#1a7f5a; color:#fff; }
    thead th  { padding:6px 8px; font-size:10px; font-weight:600; text-transform:uppercase; letter-spacing:.04em; }
    tbody tr:nth-child(even) { background:#f6fbf8; }
    tbody td  { padding:6px 8px; font-size:11px; border-bottom:1px solid #eee; vertical-align:middle; }
    tfoot td  { padding:7px 8px; font-weight:700; font-size:12px; border-top:2px solid #1a7f5a; background:#f0fbf6; }
    .text-right { text-align:right; }
    .text-center{ text-align:center; }

    .two-col { display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:14px; }
    .box { background:#f9fafb; border:1px solid #e8ecef; border-radius:6px; padding:10px 12px; }
    .box-row { display:flex; justify-content:space-between; padding:3px 0; font-size:11px; border-bottom:1px solid #f0f0f0; }
    .box-row:last-child { border:none; }
    .box-label { color:#666; }
    .box-value { font-weight:600; }

    .footer { margin-top:24px; padding-top:12px; border-top:1px solid #e0e0e0; display:flex; justify-content:space-between; align-items:flex-end; }
    .footer-left { font-size:10px; color:#888; }
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
  <a href="<?= $appUrl ?>/relatorios/vendas?<?= http_build_query($filtros) ?>" class="btn-voltar">← Voltar</a>
  <button class="btn-print" onclick="window.print()">⬇ Guardar / Imprimir PDF</button>
</div>

<div class="page">

  <!-- Cabeçalho -->
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
    <div class="doc-title">
      <h1>Relatório de Vendas</h1>
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
      <div class="resumo-val"><?= number_format($resumo['total_vendas'] ?? 0) ?></div>
      <div class="resumo-lbl">Total Vendas</div>
    </div>
    <div class="resumo-box">
      <div class="resumo-val"><?= number_format($resumo['valor_total'] ?? 0, 2, ',', '.') ?></div>
      <div class="resumo-lbl">Valor Total (MZN)</div>
    </div>
    <div class="resumo-box">
      <div class="resumo-val"><?= number_format($resumo['ticket_medio'] ?? 0, 2, ',', '.') ?></div>
      <div class="resumo-lbl">Ticket Médio (MZN)</div>
    </div>
    <div class="resumo-box">
      <div class="resumo-val"><?= number_format($resumo['total_descontos'] ?? 0, 2, ',', '.') ?></div>
      <div class="resumo-lbl">Descontos (MZN)</div>
    </div>
  </div>

  <!-- Pagamento + Funcionários -->
  <div class="two-col">

    <div>
      <div class="section-title">Por Forma de Pagamento</div>
      <div class="box">
        <?php foreach ($por_pagamento as $pg): ?>
        <div class="box-row">
          <span class="box-label"><?= $pagamentosLabel[$pg['forma_pagamento']] ?? $pg['forma_pagamento'] ?></span>
          <span class="box-value"><?= number_format($pg['valor_total'],2,',','.') ?> MZN (<?= $pg['total_vendas'] ?>)</span>
        </div>
        <?php endforeach; ?>
        <?php if (empty($por_pagamento)): ?>
        <div class="box-row"><span class="box-label">Sem dados</span></div>
        <?php endif; ?>
      </div>
    </div>

    <div>
      <div class="section-title">Por Funcionário</div>
      <div class="box">
        <?php foreach ($por_funcionario as $fu): ?>
        <div class="box-row">
          <span class="box-label"><?= htmlspecialchars($fu['funcionario_nome'] ?? '—') ?></span>
          <span class="box-value"><?= number_format($fu['valor_total'],2,',','.') ?> MZN (<?= $fu['total_vendas'] ?>)</span>
        </div>
        <?php endforeach; ?>
        <?php if (empty($por_funcionario)): ?>
        <div class="box-row"><span class="box-label">Sem dados</span></div>
        <?php endif; ?>
      </div>
    </div>

  </div>

  <!-- Tabela de vendas -->
  <div class="section-title">Detalhe das Vendas (<?= count($vendas) ?> registos)</div>
  <table>
    <thead>
      <tr>
        <th>Nº Venda</th>
        <th>Data/Hora</th>
        <th>Cliente</th>
        <th>Funcionário</th>
        <th class="text-center">Pagamento</th>
        <th class="text-right">Total (MZN)</th>
        <th class="text-center">Estado</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($vendas as $v): ?>
      <tr>
        <td style="font-weight:600;color:#1a7f5a"><?= htmlspecialchars($v['numero_venda']) ?></td>
        <td><?= date('d/m/Y H:i', strtotime($v['criado_em'])) ?></td>
        <td><?= htmlspecialchars($v['cliente_nome']) ?></td>
        <td><?= htmlspecialchars($v['funcionario_nome'] ?? '—') ?></td>
        <td class="text-center"><?= $pagamentosLabel[$v['forma_pagamento']] ?? $v['forma_pagamento'] ?></td>
        <td class="text-right" style="font-weight:600"><?= number_format($v['total'],2,',','.') ?></td>
        <td class="text-center"><?= $statusLabel[$v['status']] ?? $v['status'] ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
    <tfoot>
      <tr>
        <td colspan="5">TOTAL</td>
        <td class="text-right" style="color:#1a7f5a"><?= number_format($resumo['valor_total'] ?? 0,2,',','.') ?></td>
        <td></td>
      </tr>
    </tfoot>
  </table>

  <!-- Rodapé -->
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
    <div class="assinatura">
      <div class="assinatura-linha"></div>
      Responsável
    </div>
  </div>

</div>
</body>
</html>
