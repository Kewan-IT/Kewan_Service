<?php
// app/Views/lotes/devolucao_pdf.php
// Comprovativo de devolução de lote ao fornecedor — impressão / guardar como PDF
$d = $devolucao;

$motivoLabels = [
    'validade' => 'Próximo da validade',
    'vencido'  => 'Produto vencido',
    'avariado' => 'Produto avariado',
    'outro'    => 'Outro',
];
?>
<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Devolução <?= htmlspecialchars($d['numero_devolucao']) ?></title>
  <style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family:'Segoe UI', Arial, sans-serif; font-size:13px; color:#1a1a1a; background:#f5f5f5; }

    .no-print { position:fixed; top:20px; right:20px; display:flex; gap:10px; z-index:999; }
    .btn-print, .btn-voltar {
      padding:10px 20px; border-radius:8px; font-size:14px; font-weight:600;
      cursor:pointer; display:flex; align-items:center; gap:8px;
      box-shadow:0 2px 8px rgba(0,0,0,.15); text-decoration:none;
    }
    .btn-print { background:#dc3545; color:#fff; border:none; }
    .btn-print:hover { background:#b02a37; }
    .btn-voltar { background:#fff; color:#333; border:1px solid #ddd; }

    .page { width:210mm; min-height:297mm; background:#fff; margin:30px auto; padding:18mm; box-shadow:0 4px 24px rgba(0,0,0,.12); }

    .header { display:flex; justify-content:space-between; align-items:flex-start; padding-bottom:14px; border-bottom:3px solid #dc3545; margin-bottom:18px; }
    .company-name { font-size:22px; font-weight:700; color:#dc3545; letter-spacing:-.3px; }
    .company-sub { font-size:11px; color:#666; margin-top:2px; }
    .doc-title { text-align:right; }
    .doc-title h1 { font-size:20px; font-weight:700; color:#1a1a1a; text-transform:uppercase; letter-spacing:1px; }
    .doc-number { font-size:13px; color:#dc3545; font-weight:600; margin-top:4px; }
    .doc-date { font-size:11px; color:#666; margin-top:2px; }

    .meta-grid { display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:20px; }
    .meta-box { background:#f8f9fa; border-radius:8px; padding:12px 14px; }
    .meta-box h3 { font-size:11px; text-transform:uppercase; color:#888; margin-bottom:6px; letter-spacing:.5px; }
    .meta-box p { font-size:13px; line-height:1.5; }

    table { width:100%; border-collapse:collapse; margin-bottom:20px; }
    th { background:#dc3545; color:#fff; text-align:left; padding:10px; font-size:11px; text-transform:uppercase; }
    th.text-end, td.text-end { text-align:right; }
    th.text-center, td.text-center { text-align:center; }
    td { padding:10px; border-bottom:1px solid #eee; font-size:13px; }

    .total-box { display:flex; justify-content:flex-end; margin-top:10px; }
    .total-box table { width:280px; margin:0; }
    .total-box td { border:none; padding:6px 10px; }
    .total-box .total-final td { font-weight:700; font-size:16px; color:#dc3545; border-top:2px solid #dc3545; }

    .obs-box { margin-top:18px; background:#fff3cd; border-left:4px solid #ffc107; padding:10px 14px; border-radius:6px; font-size:12px; }

    .sign-area { display:flex; justify-content:space-between; margin-top:60px; }
    .sign-line { width:45%; text-align:center; border-top:1px solid #333; padding-top:6px; font-size:11px; color:#555; }

    @media print {
      body { background:#fff; }
      .no-print { display:none; }
      .page { box-shadow:none; margin:0; }
    }
  </style>
</head>
<body>

  <div class="no-print">
    <button class="btn-print" onclick="window.print()">🖨 Imprimir / Guardar PDF</button>
    <a class="btn-voltar" href="<?= $appUrl ?>/relatorios/lotes-a-vencer">← Voltar</a>
  </div>

  <div class="page">
    <div class="header">
      <div>
        <div class="company-name">KewanFarma</div>
        <div class="company-sub">Comprovativo de Devolução ao Fornecedor</div>
      </div>
      <div class="doc-title">
        <h1>Devolução</h1>
        <div class="doc-number">Nº <?= htmlspecialchars($d['numero_devolucao']) ?></div>
        <div class="doc-date"><?= date('d/m/Y H:i', strtotime($d['criado_em'])) ?></div>
      </div>
    </div>

    <div class="meta-grid">
      <div class="meta-box">
        <h3>Fornecedor</h3>
        <p>
          <?= htmlspecialchars($d['fornecedor_nome'] ?? 'Não definido') ?><br>
          <?php if (!empty($d['fornecedor_telefone'])): ?>Tel: <?= htmlspecialchars($d['fornecedor_telefone']) ?><?php endif; ?>
        </p>
      </div>
      <div class="meta-box">
        <h3>Registado por</h3>
        <p><?= htmlspecialchars($d['usuario_nome'] ?? '—') ?></p>
      </div>
    </div>

    <table>
      <thead>
        <tr>
          <th>Produto</th>
          <th class="text-center">Nº Lote</th>
          <th class="text-center">Qtd. Devolvida</th>
          <th class="text-center">Motivo</th>
          <th class="text-end">Valor Unit.</th>
          <th class="text-end">Total</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td><?= htmlspecialchars($d['produto_nome']) ?> <span style="color:#888;font-size:11px">(<?= htmlspecialchars($d['unidade_medida']) ?>)</span></td>
          <td class="text-center"><?= htmlspecialchars($d['numero_lote']) ?></td>
          <td class="text-center"><?= (int)$d['quantidade'] ?></td>
          <td class="text-center"><?= htmlspecialchars($motivoLabels[$d['motivo']] ?? $d['motivo']) ?></td>
          <td class="text-end"><?= number_format($d['valor_unitario'], 2, ',', '.') ?> MZN</td>
          <td class="text-end"><?= number_format($d['valor_total'], 2, ',', '.') ?> MZN</td>
        </tr>
      </tbody>
    </table>

    <div class="total-box">
      <table>
        <tr class="total-final">
          <td>VALOR TOTAL DA DEVOLUÇÃO</td>
          <td class="text-end"><?= number_format($d['valor_total'], 2, ',', '.') ?> MZN</td>
        </tr>
      </table>
    </div>

    <?php if (!empty($d['observacoes'])): ?>
    <div class="obs-box">
      <strong>Observações:</strong> <?= nl2br(htmlspecialchars($d['observacoes'])) ?>
    </div>
    <?php endif; ?>

    <div class="sign-area">
      <div class="sign-line">Assinatura — Farmácia</div>
      <div class="sign-line">Assinatura — Fornecedor / Transportador</div>
    </div>
  </div>

</body>
</html>
