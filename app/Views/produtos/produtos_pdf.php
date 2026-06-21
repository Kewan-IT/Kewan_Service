<?php
// app/Views/produtos/produtos_pdf.php
// Listagem completa de produtos, pronta para impressão / guardar como PDF
?>
<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Listagem de Produtos</title>
  <style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family:'Segoe UI', Arial, sans-serif; font-size:12px; color:#1a1a1a; background:#f5f5f5; }

    .no-print { position:fixed; top:20px; right:20px; display:flex; gap:10px; z-index:999; }
    .btn-print, .btn-voltar {
      padding:10px 20px; border-radius:8px; font-size:14px; font-weight:600;
      cursor:pointer; display:flex; align-items:center; gap:8px;
      box-shadow:0 2px 8px rgba(0,0,0,.15); text-decoration:none;
    }
    .btn-print { background:#1a7f5a; color:#fff; border:none; }
    .btn-print:hover { background:#156347; }
    .btn-voltar { background:#fff; color:#333; border:1px solid #ddd; }

    .page { width:297mm; min-height:210mm; background:#fff; margin:30px auto; padding:14mm; box-shadow:0 4px 24px rgba(0,0,0,.12); }

    .header { display:flex; justify-content:space-between; align-items:flex-start; padding-bottom:12px; border-bottom:3px solid #1a7f5a; margin-bottom:14px; }
    .company-name { font-size:20px; font-weight:700; color:#1a7f5a; letter-spacing:-.3px; }
    .company-sub { font-size:11px; color:#666; margin-top:2px; }
    .doc-title { text-align:right; }
    .doc-title h1 { font-size:18px; font-weight:700; color:#1a1a1a; text-transform:uppercase; letter-spacing:1px; }
    .doc-meta { font-size:11px; color:#666; margin-top:4px; }

    .filtros-box { background:#f0f7f3; border-left:4px solid #1a7f5a; padding:6px 12px; border-radius:6px; font-size:11px; margin-bottom:12px; }

    table { width:100%; border-collapse:collapse; margin-bottom:16px; }
    thead { display:table-header-group; }
    tr { page-break-inside:avoid; }
    th { background:#1a7f5a; color:#fff; text-align:left; padding:7px 6px; font-size:10px; text-transform:uppercase; letter-spacing:.3px; }
    th.text-end, td.text-end { text-align:right; }
    th.text-center, td.text-center { text-align:center; }
    td { padding:6px; border-bottom:1px solid #eee; font-size:11px; vertical-align:top; }
    tbody tr:nth-child(even) { background:#fafafa; }
    .cat-group td { background:#eef6f1; font-weight:700; color:#1a7f5a; font-size:11px; padding:8px 6px; }

    .badge { display:inline-block; font-size:8.5px; padding:1px 5px; border-radius:4px; font-weight:600; }
    .badge-rx { background:#fff3cd; color:#856404; border:1px solid #ffc107; }
    .badge-ctrl { background:#f8d7da; color:#842029; border:1px solid #dc3545; }
    .badge-baixo { background:#fee2e2; color:#991b1b; }

    .resumo { display:flex; gap:14px; margin:10px 0 16px; }
    .resumo-box { flex:1; background:#f8f9fa; border-radius:8px; padding:8px 12px; text-align:center; }
    .resumo-box .num { font-size:16px; font-weight:700; color:#1a7f5a; }
    .resumo-box .lbl { font-size:10px; color:#888; text-transform:uppercase; }

    .sign-area { display:flex; justify-content:space-between; margin-top:50px; page-break-inside:avoid; }
    .sign-box { width:42%; text-align:center; }
    .sign-line { border-top:1px solid #333; padding-top:6px; font-size:11px; }
    .sign-role { font-weight:700; font-size:12px; margin-bottom:2px; }
    .sign-name { font-size:10px; color:#888; min-height:14px; }

    .footer-doc { text-align:center; font-size:9px; color:#aaa; margin-top:20px; }

    @media print {
      body { background:#fff; }
      .no-print { display:none; }
      .page { box-shadow:none; margin:0; width:auto; }
      @page { size: A4 landscape; margin: 10mm; }
    }
  </style>
</head>
<body>

  <div class="no-print">
    <button class="btn-print" onclick="window.print()">🖨 Imprimir / Guardar PDF</button>
    <a class="btn-voltar" href="<?= $appUrl ?>/produtos">← Voltar</a>
  </div>

  <div class="page">
    <div class="header">
      <div>
        <?php if (!empty($config['logo_farmacia'])): ?>
        <div style="margin-bottom:6px">
          <img src="<?= $appUrl ?>/uploads/<?= htmlspecialchars($config['logo_farmacia']) ?>"
               alt="Logo da farmácia" style="max-width:90px; max-height:50px; object-fit:contain;">
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
        <h1>Listagem de Produtos</h1>
        <div class="doc-meta">Emitido em <?= date('d/m/Y H:i') ?></div>
        <?php if (!empty($usuarioNome)): ?><div class="doc-meta">Por: <?= htmlspecialchars($usuarioNome) ?></div><?php endif; ?>
      </div>
    </div>

    <?php if ($pesquisa || $filtroLabel || !empty($fornecedorNome)): ?>
    <div class="filtros-box">
      <strong>Filtros aplicados:</strong>
      <?= implode(' · ', array_filter([
        $pesquisa ? 'Pesquisa: "' . htmlspecialchars($pesquisa) . '"' : null,
        $filtroLabel ? htmlspecialchars($filtroLabel) : null,
        !empty($fornecedorNome) ? 'Fornecedor: ' . htmlspecialchars($fornecedorNome) : null,
      ])) ?>
    </div>
    <?php endif; ?>

    <div class="resumo">
      <div class="resumo-box"><div class="num"><?= count($produtos) ?></div><div class="lbl">Produtos listados</div></div>
      <div class="resumo-box"><div class="num"><?= array_sum(array_column($produtos, 'estoque_actual')) ?></div><div class="lbl">Unidades em stock</div></div>
      <div class="resumo-box"><div class="num"><?= number_format(array_sum(array_map(fn($p) => $p['estoque_actual'] * $p['preco_venda'], $produtos)), 2, ',', '.') ?> MZN</div><div class="lbl">Valor de stock (venda)</div></div>
      <div class="resumo-box"><div class="num"><?= count(array_filter($produtos, fn($p) => $p['stock_baixo'])) ?></div><div class="lbl">Com stock baixo</div></div>
    </div>

    <table>
      <thead>
        <tr>
          <th style="width:18%">Produto</th>
          <th class="text-center" style="width:11%">Código Barras</th>
          <th style="width:10%">Fornecedor</th>
          <th class="text-center" style="width:7%">Unidade</th>
          <th class="text-end" style="width:8%">Preço Compra</th>
          <th class="text-end" style="width:8%">Preço Venda</th>
          <th class="text-center" style="width:6%">Stock</th>
          <th class="text-center" style="width:6%">Stock Mín.</th>
          <th class="text-center" style="width:9%">Próx. Validade</th>
          <th class="text-center" style="width:9%">Observações</th>
        </tr>
      </thead>
      <tbody>
        <?php $catAtual = null; foreach ($produtos as $p): ?>
          <?php if ($p['categoria_nome'] !== $catAtual): $catAtual = $p['categoria_nome']; ?>
          <tr class="cat-group"><td colspan="10"><?= htmlspecialchars($catAtual) ?></td></tr>
          <?php endif; ?>
          <tr>
            <td>
              <?= htmlspecialchars($p['nome']) ?>
              <?php if (!empty($p['principio_ativo'])): ?>
                <div style="color:#888;font-size:10px"><?= htmlspecialchars($p['principio_ativo']) ?></div>
              <?php endif; ?>
            </td>
            <td class="text-center"><?= htmlspecialchars($p['codigo_barras'] ?: '—') ?></td>
            <td><?= htmlspecialchars($p['fornecedor_nome'] ?? '—') ?></td>
            <td class="text-center"><?= htmlspecialchars($p['unidade_medida']) ?></td>
            <td class="text-end"><?= number_format($p['preco_compra'], 2, ',', '.') ?></td>
            <td class="text-end fw-bold"><?= number_format($p['preco_venda'], 2, ',', '.') ?></td>
            <td class="text-center <?= $p['stock_baixo'] ? 'text-danger fw-bold' : '' ?>"><?= $p['estoque_actual'] ?></td>
            <td class="text-center"><?= $p['estoque_min'] ?></td>
            <td class="text-center"><?= $p['proxima_validade'] ? date('d/m/Y', strtotime($p['proxima_validade'])) : '—' ?></td>
            <td class="text-center">
              <?php if (!empty($p['requer_receita'])): ?><span class="badge badge-rx">RX</span><?php endif; ?>
              <?php if (!empty($p['controlado'])): ?><span class="badge badge-ctrl">CTRL</span><?php endif; ?>
              <?php if (!empty($p['stock_baixo'])): ?><span class="badge badge-baixo">BAIXO</span><?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($produtos)): ?>
          <tr><td colspan="10" class="text-center text-muted py-3">Nenhum produto encontrado para os filtros aplicados.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>

    <div class="sign-area">
      <div class="sign-box">
        <div class="sign-line">
          <div class="sign-role">Director(a) da Farmácia</div>
          <div class="sign-name">Nome e assinatura</div>
        </div>
      </div>
      <div class="sign-box">
        <div class="sign-line">
          <div class="sign-role">Proprietário(a)</div>
          <div class="sign-name">Nome e assinatura</div>
        </div>
      </div>
    </div>

    <div class="footer-doc">
      Documento gerado automaticamente pelo sistema <?= htmlspecialchars($config['nome_farmacia'] ?? 'KewanFarma') ?> em <?= date('d/m/Y H:i') ?>
    </div>
  </div>

</body>
</html>
