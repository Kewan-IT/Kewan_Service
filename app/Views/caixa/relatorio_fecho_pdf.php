<?php
// app/Views/caixa/relatorio_fecho_pdf.php
// Relatório detalhado de fecho de caixa

$formas = [
    'dinheiro'       => ['💵', 'Dinheiro'],
    'mpesa'          => ['📱', 'M-Pesa'],
    'emola'          => ['📲', 'e-Mola'],
    'cartao_debito'  => ['💳', 'Cartão Débito'],
    'cartao_credito' => ['💳', 'Cartão Crédito'],
    'transferencia'  => ['🏦', 'Transferência'],
    'credito'        => ['📋', 'Crédito'],
];
$tiposLabel = [
    'venda'      => ['entrada', 'Venda'],
    'entrada'    => ['entrada', 'Entrada'],
    'suprimento' => ['entrada', 'Suprimento'],
    'devolucao'  => ['entrada', 'Devolução'],
    'sangria'    => ['saida',   'Sangria'],
    'saida'      => ['saida',   'Saída'],
];

$nomeEmpresa  = $config['nome_farmacia'] ?? ($_ENV['APP_NAME'] ?? 'KewanFarma');
$enderecoEmp  = $config['endereco_farmacia'] ?? '';
$nuitEmp      = $config['nuit_farmacia'] ?? '';

// Cálculos
$saldoInicial  = (float)$s['saldo_inicial'];
$totalEntradas = (float)$s['total_entradas'];
$totalSaidas   = (float)$s['total_saidas'];
$totalVendas   = (float)$s['total_vendas'];
$saldoEsperado = (float)$s['saldo_esperado'];
$saldoFinal    = isset($s['saldo_final']) ? (float)$s['saldo_final'] : null;
$diferenca     = isset($s['diferenca'])   ? (float)$s['diferenca']   : null;

// Duração da sessão
$seg     = $s['fechado_em'] ? (strtotime($s['fechado_em']) - strtotime($s['aberto_em'])) : 0;
$durH    = floor($seg / 3600);
$durM    = floor(($seg % 3600) / 60);
$duracao = $seg > 0 ? "{$durH}h {$durM}min" : '—';

// Saldo dinheiro em caixa (apenas dinheiro + suprimentos - sangrias)
$totalDinheiro = 0;
foreach ($pagamentos as $pg) {
    if ($pg['forma_pagamento'] === 'dinheiro') {
        $totalDinheiro += (float)$pg['total_valor'];
    }
}
// Adicionar suprimentos, subtrair sangrias dos manuais
$totalSuprimentos = 0; $totalSangrias = 0;
foreach ($manuais as $mv) {
    if ($mv['tipo'] === 'suprimento') $totalSuprimentos += (float)$mv['valor'];
    if ($mv['tipo'] === 'sangria')    $totalSangrias    += (float)$mv['valor'];
}
$saldoDinheiro = $saldoInicial + $totalDinheiro + $totalSuprimentos - $totalSangrias;

$numRelatorio = 'RFC-' . date('Y', strtotime($s['aberto_em'])) . '-' . str_pad((string)$s['id'], 5, '0', STR_PAD_LEFT);

function fmt(float $v): string { return number_format($v, 2, ',', '.'); }
function h(?string $v): string { return htmlspecialchars((string)($v ?? '')); }
?>
<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Relatório de Fecho — Caixa #<?= $s['id'] ?></title>
  <style>
    *, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }
    body { font-family:'Segoe UI', Arial, sans-serif; font-size:12px; color:#1a1a1a; background:#eef0f3; line-height:1.5; }

    .no-print { position:fixed; top:18px; right:18px; display:flex; gap:10px; z-index:999; }
    .btn-print, .btn-voltar {
      padding:10px 22px; border-radius:8px; font-size:14px; font-weight:600; cursor:pointer;
      display:flex; align-items:center; gap:8px; box-shadow:0 2px 10px rgba(0,0,0,.15); text-decoration:none;
    }
    .btn-print  { background:#1a7f5a; color:#fff; border:none; }
    .btn-print:hover { background:#156347; }
    .btn-voltar { background:#fff; color:#333; border:1px solid #ddd; }

    .page { width:210mm; background:#fff; margin:24px auto; padding:14mm 16mm 16mm;
            box-shadow:0 6px 30px rgba(0,0,0,.14); }

    /* ── Cabeçalho ── */
    .header { display:flex; justify-content:space-between; align-items:flex-start;
              padding-bottom:12px; border-bottom:3px solid #1a7f5a; margin-bottom:14px; }
    .co-name { font-size:19px; font-weight:700; color:#1a7f5a; }
    .co-sub  { font-size:10px; color:#666; margin-top:2px; }
    .doc-right { text-align:right; }
    .doc-right h1 { font-size:15px; font-weight:700; text-transform:uppercase; letter-spacing:.6px; }
    .doc-right .num { font-size:11px; color:#1a7f5a; font-weight:600; margin-top:3px; }
    .doc-right .dt  { font-size:10px; color:#888; margin-top:2px; }

    /* ── Status banner ── */
    .status-banner { border-radius:8px; padding:9px 14px; display:flex; align-items:center;
                     gap:12px; margin-bottom:14px; font-size:12.5px; }
    .status-banner.fechado { background:#ecfdf5; border:1px solid #a7f3d0; }
    .status-banner.aberto  { background:#fffbeb; border:1px solid #fcd34d; }
    .status-banner .sb-icon { font-size:20px; }
    .status-banner .sb-info { flex:1; }
    .status-banner .sb-dur  { font-size:10px; color:#888; margin-top:1px; }

    /* ── Cartões de resumo ── */
    .resumo-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:10px; margin-bottom:14px; }
    .rcard { border-radius:8px; padding:10px 12px; text-align:center; }
    .rcard .rc-lbl  { font-size:9px; text-transform:uppercase; letter-spacing:.4px; font-weight:600; margin-bottom:3px; }
    .rcard .rc-val  { font-size:15px; font-weight:700; }
    .rcard .rc-sub  { font-size:9px; margin-top:2px; }
    .rcard-vendas   { background:#f0fdf4; border:1px solid #bbf7d0; }
    .rcard-vendas .rc-lbl, .rcard-vendas .rc-val { color:#166534; }
    .rcard-entradas { background:#eff6ff; border:1px solid #bfdbfe; }
    .rcard-entradas .rc-lbl, .rcard-entradas .rc-val { color:#1e40af; }
    .rcard-saidas   { background:#fff1f2; border:1px solid #fecdd3; }
    .rcard-saidas .rc-lbl, .rcard-saidas .rc-val { color:#9f1239; }
    .rcard-saldo    { background:#faf5ff; border:1px solid #e9d5ff; }
    .rcard-saldo .rc-lbl, .rcard-saldo .rc-val { color:#6b21a8; }

    /* ── Diferença ── */
    .dif-box { border-radius:8px; padding:9px 14px; display:flex; justify-content:space-between;
               align-items:center; margin-bottom:14px; }
    .dif-ok   { background:#ecfdf5; border:1px solid #a7f3d0; }
    .dif-sobra{ background:#ecfdf5; border:1px solid #a7f3d0; }
    .dif-falta{ background:#fff1f2; border:1px solid #fecdd3; }
    .dif-nd   { background:#f9fafb; border:1px solid #e5e7eb; }

    /* ── Secções ── */
    .sec { margin-bottom:14px; page-break-inside:avoid; }
    .sec-h { background:#1a7f5a; color:#fff; padding:6px 10px; border-radius:6px 6px 0 0;
             font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.4px;
             display:flex; align-items:center; gap:6px; }
    .sec-body { border:1px solid #d0efe4; border-top:none; border-radius:0 0 6px 6px; background:#fff; }

    /* ── Tabelas ── */
    table { width:100%; border-collapse:collapse; }
    th { background:#f0fdf4; color:#166534; text-align:left; padding:6px 8px;
         font-size:9.5px; text-transform:uppercase; letter-spacing:.3px; font-weight:700;
         border-bottom:2px solid #bbf7d0; }
    th.r, td.r { text-align:right; }
    th.c, td.c { text-align:center; }
    td { padding:5px 8px; font-size:11px; border-bottom:1px solid #f0f0f0; }
    tr:last-child td { border-bottom:none; }
    tbody tr:nth-child(even) td { background:#fafafa; }
    .tfoot-row td { background:#f0fdf4 !important; font-weight:700; font-size:11.5px;
                    border-top:2px solid #bbf7d0; border-bottom:none; }

    /* ── Pagamento badges ── */
    .pgbadge { display:inline-flex; align-items:center; gap:4px; font-size:10px;
               font-weight:600; padding:2px 7px; border-radius:20px; }
    .pg-dinheiro { background:#fef9c3; color:#713f12; }
    .pg-mpesa    { background:#dcfce7; color:#14532d; }
    .pg-emola    { background:#dbeafe; color:#1e3a5f; }
    .pg-cartao   { background:#ede9fe; color:#4c1d95; }
    .pg-outro    { background:#f3f4f6; color:#374151; }

    /* ── Produtos top ── */
    .prod-bar { background:#e0f2f1; height:6px; border-radius:3px; margin-top:2px; }
    .prod-bar-fill { background:#1a7f5a; height:6px; border-radius:3px; }

    /* ── Timeline horária ── */
    .timeline-grid { display:flex; flex-direction:column; gap:5px; padding:10px; }
    .tl-row { display:flex; align-items:center; gap:8px; }
    .tl-hora { width:38px; font-size:10px; color:#888; font-weight:600; flex-shrink:0; }
    .tl-bar-wrap { flex:1; background:#f0f0f0; border-radius:4px; height:14px; position:relative; }
    .tl-bar-fill { background:linear-gradient(90deg,#1a7f5a,#4ade80); height:14px; border-radius:4px; min-width:2px; }
    .tl-val { width:80px; font-size:10px; color:#166534; font-weight:700; text-align:right; flex-shrink:0; }
    .tl-cnt { width:50px; font-size:10px; color:#888; text-align:right; flex-shrink:0; }

    /* ── Manuais ── */
    .mov-chip { display:inline-block; font-size:9px; font-weight:700; padding:2px 7px;
                border-radius:20px; text-transform:uppercase; }
    .chip-entrada  { background:#dcfce7; color:#14532d; }
    .chip-saida    { background:#ffe4e6; color:#9f1239; }
    .chip-supr     { background:#dbeafe; color:#1e40af; }
    .chip-sangria  { background:#fef3c7; color:#92400e; }
    .chip-dev      { background:#fce7f3; color:#831843; }

    /* ── Estatísticas quick ── */
    .qstat-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:10px; padding:10px; }
    .qstat { text-align:center; padding:8px; background:#f8fffe; border-radius:6px; border:1px solid #d0efe4; }
    .qstat .qs-val { font-size:14px; font-weight:700; color:#1a7f5a; }
    .qstat .qs-lbl { font-size:9px; text-transform:uppercase; color:#888; letter-spacing:.3px; margin-top:2px; }

    /* ── Assinaturas ── */
    .sign-area { display:flex; justify-content:space-between; margin-top:36px; gap:20px; page-break-inside:avoid; }
    .sign-box  { flex:1; text-align:center; }
    .sign-line { border-top:1px solid #333; padding-top:6px; font-size:10.5px; }
    .sign-role { font-weight:700; font-size:11px; }
    .sign-name { font-size:10px; color:#888; min-height:12px; }

    .footer-doc { text-align:center; font-size:9px; color:#aaa; margin-top:18px;
                  border-top:1px solid #eee; padding-top:10px; }

    @media print {
      body { background:#fff; }
      .no-print { display:none; }
      .page { box-shadow:none; margin:0; }
      @page { size:A4 portrait; margin:10mm; }
    }
  </style>
</head>
<body>

<div class="no-print">
  <button class="btn-print" onclick="window.print()">🖨 Imprimir / Guardar PDF</button>
  <a class="btn-voltar" href="<?= $appUrl ?>/caixa/<?= $s['id'] ?>">← Voltar</a>
</div>

<div class="page">

  <!-- ══ Cabeçalho ══ -->
  <div class="header">
    <div>
      <?php if (!empty($config['logo_farmacia'])): ?>
      <div style="margin-bottom:6px">
        <img src="<?= $appUrl ?>/uploads/<?= h($config['logo_farmacia']) ?>"
             alt="Logo" style="max-width:80px;max-height:44px;object-fit:contain">
      </div>
      <?php endif; ?>
      <div class="co-name"><?= h($nomeEmpresa) ?></div>
      <?php if ($enderecoEmp): ?><div class="co-sub"><?= h($enderecoEmp) ?></div><?php endif; ?>
      <?php if ($nuitEmp): ?><div class="co-sub">NUIT: <?= h($nuitEmp) ?></div><?php endif; ?>
    </div>
    <div class="doc-right">
      <h1>Relatório de Fecho de Caixa</h1>
      <div class="num">Nº <?= $numRelatorio ?></div>
      <div class="dt">Emitido em <?= date('d/m/Y H:i') ?></div>
    </div>
  </div>

  <!-- ══ Banner de estado ══ -->
  <?php $fechada = $s['status'] === 'fechado'; ?>
  <div class="status-banner <?= $fechada ? 'fechado' : 'aberto' ?>">
    <span class="sb-icon"><?= $fechada ? '🔒' : '🔓' ?></span>
    <div class="sb-info">
      <strong>Sessão #<?= $s['id'] ?> — <?= $fechada ? 'Fechada' : 'Aberta' ?></strong> &nbsp;·&nbsp;
      Operador: <strong><?= h($s['usuario_nome']) ?></strong> &nbsp;·&nbsp;
      Abertura: <strong><?= date('d/m/Y H:i', strtotime($s['aberto_em'])) ?></strong>
      <?= $s['fechado_em'] ? ' &nbsp;·&nbsp; Fecho: <strong>'.date('d/m/Y H:i', strtotime($s['fechado_em'])).'</strong>' : '' ?>
      <div class="sb-dur">Duração da sessão: <strong><?= $duracao ?></strong></div>
    </div>
  </div>

  <!-- ══ 4 Cartões de resumo ══ -->
  <div class="resumo-grid">
    <div class="rcard rcard-vendas">
      <div class="rc-lbl">Vendas</div>
      <div class="rc-val">MT <?= fmt($totalVendas) ?></div>
      <div class="rc-sub"><?= count($vendas) ?> transacções</div>
    </div>
    <div class="rcard rcard-entradas">
      <div class="rc-lbl">Total Entradas</div>
      <div class="rc-val">MT <?= fmt($totalEntradas) ?></div>
      <div class="rc-sub">Incl. fundo inicial</div>
    </div>
    <div class="rcard rcard-saidas">
      <div class="rc-lbl">Total Saídas</div>
      <div class="rc-val">MT <?= fmt($totalSaidas) ?></div>
      <div class="rc-sub">Sangrias + despesas</div>
    </div>
    <div class="rcard rcard-saldo">
      <div class="rc-lbl">Saldo Esperado</div>
      <div class="rc-val">MT <?= fmt($saldoEsperado) ?></div>
      <div class="rc-sub">Calculado pelo sistema</div>
    </div>
  </div>

  <!-- ══ Balanço completo + diferença ══ -->
  <div class="sec">
    <div class="sec-h">💰 Balanço Financeiro</div>
    <div class="sec-body" style="padding:10px 14px">
      <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:10px 30px">
        <?php
        $linhasBalanco = [
          ['Fundo de abertura (saldo inicial)',        'MT '.fmt($saldoInicial),    '#1a7f5a',false],
          ['(+) Total de vendas registadas',           'MT '.fmt($totalVendas),     '#166534',false],
          ['(+) Entradas manuais / suprimentos',       'MT '.fmt($totalEntradas - $totalVendas), '#1d4ed8', false],
          ['(-) Sangrias / saídas manuais',            'MT '.fmt($totalSaidas),     '#9f1239',false],
          ['(=) Saldo esperado em caixa',              'MT '.fmt($saldoEsperado),   '#6b21a8',true],
          ['    do qual em dinheiro estimado',         'MT '.fmt($saldoDinheiro),   '#888',   false],
        ];
        foreach ($linhasBalanco as [$lbl,$val,$cor,$bold]):
        ?>
        <div style="display:flex;justify-content:space-between;padding:4px 0;
                    border-bottom:1px dotted #e0e0e0;
                    font-weight:<?= $bold?'700':'400' ?>">
          <span style="color:#555"><?= $lbl ?></span>
          <span style="color:<?= $cor ?>"><?= $val ?></span>
        </div>
        <?php endforeach; ?>
        <?php if ($saldoFinal !== null): ?>
        <div style="display:flex;justify-content:space-between;padding:4px 0;border-bottom:1px dotted #e0e0e0;font-weight:700">
          <span style="color:#555">Saldo contado (real)</span>
          <span style="color:#1a1a1a">MT <?= fmt($saldoFinal) ?></span>
        </div>
        <?php endif; ?>
      </div>

      <!-- Caixa de diferença -->
      <?php if ($diferenca !== null): ?>
      <?php
      $difAbs = abs($diferenca);
      $difCls = $difAbs < 0.01 ? 'dif-ok' : ($diferenca > 0 ? 'dif-sobra' : 'dif-falta');
      $difIcon = $difAbs < 0.01 ? '✅' : ($diferenca > 0 ? '⬆️' : '⬇️');
      $difTxt  = $difAbs < 0.01 ? 'Sem diferença — caixa confere exactamente.' :
                 ($diferenca > 0 ? 'Sobra de MT '.fmt($difAbs).' — verificar suprimentos não registados ou erros de contagem.' :
                                   'Falta de MT '.fmt($difAbs).' — verificar saídas não registadas ou erros de contagem.');
      ?>
      <div class="dif-box <?= $difCls ?>" style="margin-top:12px">
        <div>
          <strong style="font-size:13px"><?= $difIcon ?> <?= $difTxt ?></strong>
        </div>
        <div style="font-size:12px;font-weight:700;color:<?= $difAbs<0.01?'#166534':($diferenca>0?'#166534':'#9f1239') ?>">
          MT <?= fmt($difAbs) ?>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- ══ Pagamentos por forma ══ -->
  <?php if (!empty($pagamentos)): ?>
  <div class="sec">
    <div class="sec-h">💳 Vendas por Forma de Pagamento</div>
    <div class="sec-body">
      <table>
        <thead>
          <tr>
            <th>Forma de Pagamento</th>
            <th class="c">Nº Transacções</th>
            <th class="r">Total (MT)</th>
            <th class="r">% do Total</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $totalPag = array_sum(array_column($pagamentos, 'total_valor'));
          foreach ($pagamentos as $pg):
            [$icon, $nome] = $formas[$pg['forma_pagamento']] ?? ['💱', ucfirst($pg['forma_pagamento'])];
            $pct = $totalPag > 0 ? ($pg['total_valor'] / $totalPag * 100) : 0;
            $chipCls = match($pg['forma_pagamento']) {
              'dinheiro' => 'pg-dinheiro', 'mpesa' => 'pg-mpesa',
              'emola'    => 'pg-emola',
              'cartao_debito','cartao_credito' => 'pg-cartao',
              default    => 'pg-outro',
            };
          ?>
          <tr>
            <td>
              <span class="pgbadge <?= $chipCls ?>"><?= $icon ?> <?= h($nome) ?></span>
            </td>
            <td class="c"><?= (int)$pg['total_vendas'] ?></td>
            <td class="r"><strong><?= fmt((float)$pg['total_valor']) ?></strong></td>
            <td class="r">
              <div style="display:flex;align-items:center;justify-content:flex-end;gap:6px">
                <span><?= number_format($pct, 1) ?>%</span>
                <div style="width:50px;background:#f0f0f0;border-radius:3px;height:6px">
                  <div style="width:<?= min(100,$pct) ?>%;background:#1a7f5a;height:6px;border-radius:3px"></div>
                </div>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
        <tfoot>
          <tr class="tfoot-row">
            <td>TOTAL</td>
            <td class="c"><?= count($vendas) ?></td>
            <td class="r">MT <?= fmt($totalPag) ?></td>
            <td class="r">100%</td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
  <?php endif; ?>

  <!-- ══ Estatísticas de vendas ══ -->
  <?php if (!empty($statsVendas) && $statsVendas['total_vendas'] > 0): ?>
  <div class="sec">
    <div class="sec-h">📊 Estatísticas de Vendas</div>
    <div class="sec-body">
      <div class="qstat-grid">
        <div class="qstat">
          <div class="qs-val"><?= fmt((float)$statsVendas['ticket_medio']) ?> MT</div>
          <div class="qs-lbl">Ticket médio</div>
        </div>
        <div class="qstat">
          <div class="qs-val"><?= fmt((float)$statsVendas['maior_venda']) ?> MT</div>
          <div class="qs-lbl">Maior venda</div>
        </div>
        <div class="qstat">
          <div class="qs-val"><?= fmt((float)$statsVendas['menor_venda']) ?> MT</div>
          <div class="qs-lbl">Menor venda</div>
        </div>
        <div class="qstat">
          <div class="qs-val"><?= fmt((float)$statsVendas['total_descontos']) ?> MT</div>
          <div class="qs-lbl">Total descontos</div>
        </div>
        <div class="qstat">
          <div class="qs-val"><?= (int)$statsVendas['total_vendas'] ?></div>
          <div class="qs-lbl">Nº transacções</div>
        </div>
        <div class="qstat">
          <div class="qs-val"><?= $durH > 0 ? number_format($statsVendas['total_vendas'] / max(1,$durH),1) : $statsVendas['total_vendas'] ?>/h</div>
          <div class="qs-lbl">Vendas por hora</div>
        </div>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <!-- ══ Vendas por operador ══ -->
  <?php if (!empty($porOperador)): ?>
  <div class="sec">
    <div class="sec-h">👤 Desempenho por Operador</div>
    <div class="sec-body">
      <table>
        <thead>
          <tr>
            <th>Operador</th>
            <th class="c">Vendas</th>
            <th class="r">Total (MT)</th>
            <th class="r">Descontos (MT)</th>
            <th class="r">Ticket Médio</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($porOperador as $op): ?>
          <tr>
            <td><strong><?= h($op['operador']) ?></strong></td>
            <td class="c"><?= (int)$op['num_vendas'] ?></td>
            <td class="r text-success"><strong><?= fmt((float)$op['total']) ?></strong></td>
            <td class="r"><?= fmt((float)$op['total_descontos']) ?></td>
            <td class="r"><?= fmt((float)$op['total'] / max(1,(int)$op['num_vendas'])) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endif; ?>

  <!-- ══ Top produtos ══ -->
  <?php if (!empty($topProdutos)): ?>
  <div class="sec">
    <div class="sec-h">🏆 Top 10 Produtos Vendidos</div>
    <div class="sec-body">
      <table>
        <thead>
          <tr>
            <th style="width:30px" class="c">#</th>
            <th>Produto</th>
            <th class="c">Unid.</th>
            <th class="r">Qtd. Vendida</th>
            <th class="r">Valor (MT)</th>
            <th style="width:90px" class="r">% Receita</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $maxQtd = max(1, max(array_column($topProdutos, 'qtd_total')));
          foreach ($topProdutos as $i => $pr):
            $pct = $totalVendas > 0 ? ($pr['valor_total'] / $totalVendas * 100) : 0;
          ?>
          <tr>
            <td class="c" style="color:#888;font-weight:700"><?= $i+1 ?></td>
            <td>
              <div style="font-weight:600"><?= h($pr['produto']) ?></div>
              <div class="prod-bar">
                <div class="prod-bar-fill" style="width:<?= min(100, $pr['qtd_total']/$maxQtd*100) ?>%"></div>
              </div>
            </td>
            <td class="c" style="color:#888"><?= h($pr['unidade_medida']) ?></td>
            <td class="r"><strong><?= (int)$pr['qtd_total'] ?></strong></td>
            <td class="r"><?= fmt((float)$pr['valor_total']) ?></td>
            <td class="r">
              <span style="color:#1a7f5a;font-weight:600"><?= number_format($pct,1) ?>%</span>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endif; ?>

  <!-- ══ Timeline horária ══ -->
  <?php if (!empty($porHora)): ?>
  <div class="sec">
    <div class="sec-h">⏱ Distribuição de Vendas por Hora</div>
    <div class="sec-body">
      <?php
      $maxHora = max(1, max(array_column($porHora, 'total_hora')));
      $horaIdx = array_column($porHora, null, 'hora');
      // Descobrir hora min/max da sessão
      $horaMin = (int)date('G', strtotime($s['aberto_em']));
      $horaMax = $s['fechado_em'] ? (int)date('G', strtotime($s['fechado_em'])) : (int)date('G');
      ?>
      <div class="timeline-grid">
        <?php for ($h = $horaMin; $h <= $horaMax; $h++):
          $row = $horaIdx[$h] ?? null;
          $tot = $row ? (float)$row['total_hora'] : 0;
          $cnt = $row ? (int)$row['num_vendas'] : 0;
          $pct = $maxHora > 0 ? ($tot / $maxHora * 100) : 0;
        ?>
        <div class="tl-row">
          <div class="tl-hora"><?= sprintf('%02d', $h) ?>h</div>
          <div class="tl-bar-wrap">
            <?php if ($pct > 0): ?>
            <div class="tl-bar-fill" style="width:<?= min(100,$pct) ?>%"></div>
            <?php endif; ?>
          </div>
          <div class="tl-val"><?= $tot > 0 ? fmt($tot).' MT' : '—' ?></div>
          <div class="tl-cnt"><?= $cnt > 0 ? $cnt.' vd.' : '' ?></div>
        </div>
        <?php endfor; ?>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <!-- ══ Movimentos manuais ══ -->
  <?php if (!empty($manuais)): ?>
  <div class="sec">
    <div class="sec-h">🔧 Movimentos Manuais (Sangrias / Suprimentos / Outros)</div>
    <div class="sec-body">
      <table>
        <thead>
          <tr>
            <th>Tipo</th>
            <th>Descrição</th>
            <th>Operador</th>
            <th class="c">Hora</th>
            <th class="r">Valor (MT)</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($manuais as $mv):
            [$dir, $label] = $tiposLabel[$mv['tipo']] ?? ['entrada', ucfirst($mv['tipo'])];
            $chipCls = match($mv['tipo']) {
              'entrada'    => 'chip-entrada',
              'suprimento' => 'chip-supr',
              'sangria'    => 'chip-sangria',
              'saida'      => 'chip-saida',
              'devolucao'  => 'chip-dev',
              default      => '',
            };
          ?>
          <tr>
            <td><span class="mov-chip <?= $chipCls ?>"><?= h($label) ?></span></td>
            <td><?= h($mv['descricao']) ?></td>
            <td><?= h($mv['usuario_nome']) ?></td>
            <td class="c"><?= date('H:i', strtotime($mv['criado_em'])) ?></td>
            <td class="r" style="color:<?= $dir==='entrada'?'#166534':'#9f1239' ?>;font-weight:600">
              <?= $dir === 'entrada' ? '+' : '-' ?>MT <?= fmt((float)$mv['valor']) ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endif; ?>

  <!-- ══ Listagem completa das vendas ══ -->
  <?php if (!empty($vendas)): ?>
  <div class="sec" style="page-break-before:always">
    <div class="sec-h">🧾 Detalhe de Todas as Vendas (<?= count($vendas) ?>)</div>
    <div class="sec-body">
      <table>
        <thead>
          <tr>
            <th>Nº Venda</th>
            <th>Hora</th>
            <th>Cliente</th>
            <th>Operador</th>
            <th>Pagamento</th>
            <th class="r">Desconto</th>
            <th class="r">Total (MT)</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($vendas as $v):
            [$icon, $nomeForma] = $formas[$v['forma_pagamento']] ?? ['💱', h($v['forma_pagamento'])];
          ?>
          <tr>
            <td style="font-weight:600;color:#1a7f5a"><?= h($v['numero_venda']) ?></td>
            <td style="color:#888"><?= date('H:i', strtotime($v['criado_em'])) ?></td>
            <td><?= h($v['cliente']) ?></td>
            <td><?= h($v['operador']) ?></td>
            <td><?= $icon ?> <?= h($nomeForma) ?></td>
            <td class="r" style="color:<?= (float)$v['desconto']>0?'#9f1239':'#aaa' ?>">
              <?= (float)$v['desconto'] > 0 ? '-'.fmt((float)$v['desconto']) : '—' ?>
            </td>
            <td class="r"><strong><?= fmt((float)$v['total']) ?></strong></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
        <tfoot>
          <tr class="tfoot-row">
            <td colspan="5">TOTAL <?= count($vendas) ?> VENDAS</td>
            <td class="r">MT <?= fmt(array_sum(array_column($vendas, 'desconto'))) ?></td>
            <td class="r">MT <?= fmt(array_sum(array_column($vendas, 'total'))) ?></td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
  <?php endif; ?>

  <!-- ══ Observações da sessão ══ -->
  <?php if (!empty($s['observacoes'])): ?>
  <div class="sec">
    <div class="sec-h">📝 Observações</div>
    <div class="sec-body" style="padding:10px 14px;font-size:11.5px">
      <?= nl2br(h($s['observacoes'])) ?>
    </div>
  </div>
  <?php endif; ?>

  <!-- ══ Assinaturas ══ -->
  <div class="sign-area">
    <div class="sign-box">
      <div class="sign-line">
        <div class="sign-role">Operador de Caixa</div>
        <div class="sign-name"><?= h($s['usuario_nome']) ?></div>
      </div>
    </div>
    <div class="sign-box">
      <div class="sign-line">
        <div class="sign-role">Supervisor / Director</div>
        <div class="sign-name">Nome e assinatura</div>
      </div>
    </div>
    <div class="sign-box">
      <div class="sign-line">
        <div class="sign-role">Contabilidade / RH</div>
        <div class="sign-name">Nome e assinatura</div>
      </div>
    </div>
  </div>

  <div class="footer-doc">
    <?= h($nomeEmpresa) ?> &nbsp;·&nbsp; Relatório de Fecho Nº <?= $numRelatorio ?>
    &nbsp;·&nbsp; Sessão de Caixa #<?= $s['id'] ?>
    &nbsp;·&nbsp; Gerado em <?= date('d/m/Y H:i') ?>
    &nbsp;·&nbsp; Documento de uso interno
  </div>

</div>
</body>
</html>
