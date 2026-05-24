<?php
$APP = $_ENV['APP_URL'] ?? '';

$tipoLabels = [
    'proximos' => 'A vencer',
    'vencidos' => 'Já vencidos',
    'todos'    => 'Todos',
];
?>

<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
  <div>
    <h1 class="h4 fw-bold mb-0" style="color:var(--kf-primary)">
      <i class="bi bi-calendar-x me-2"></i>Lotes a Vencer
    </h1>
    <p class="text-muted small mb-0 mt-1">Controlo de validades e prevenção de perdas</p>
  </div>
  <div class="d-flex gap-2">
    <a href="<?= $APP ?>/relatorios/lotes-a-vencer/pdf?<?= http_build_query($_GET) ?>"
       target="_blank" class="btn btn-sm btn-success">
      <i class="bi bi-file-earmark-pdf me-1"></i>Exportar PDF
    </a>
    <a href="<?= $APP ?>/relatorios" class="btn btn-sm btn-outline-secondary">
      <i class="bi bi-arrow-left me-1"></i>Voltar
    </a>
  </div>
</div>

<!-- Filtros -->
<div class="card border-0 shadow-sm mb-4">
  <div class="card-body p-3">
    <form method="GET" class="row g-2 align-items-end">

      <!-- Prazo -->
      <div class="col-12 col-md-3">
        <label class="form-label small mb-1 text-muted">Mostrar</label>
        <select name="tipo" class="form-select form-select-sm" onchange="this.form.submit()">
          <option value="proximos" <?= $tipo==='proximos' ? 'selected':'' ?>>A vencer dentro do prazo</option>
          <option value="vencidos" <?= $tipo==='vencidos' ? 'selected':'' ?>>Já vencidos</option>
          <option value="todos"    <?= $tipo==='todos'    ? 'selected':'' ?>>Vencidos + próximos</option>
        </select>
      </div>

      <!-- Prazo em dias -->
      <?php if ($tipo !== 'vencidos'): ?>
      <div class="col-12 col-md-3">
        <label class="form-label small mb-1 text-muted">Prazo</label>
        <div class="d-flex gap-1">
          <?php foreach ([30, 60, 90, 180] as $p): ?>
          <a href="?tipo=<?= $tipo ?>&prazo=<?= $p ?>&categoria_id=<?= urlencode($categoria) ?>"
             class="btn btn-sm flex-fill <?= $prazo == $p ? 'btn-success' : 'btn-outline-secondary' ?>"
             style="font-size:11px">
            <?= $p ?>d
          </a>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <!-- Categoria -->
      <div class="col-6 col-md-3">
        <label class="form-label small mb-1 text-muted">Categoria</label>
        <select name="categoria_id" class="form-select form-select-sm">
          <option value="">Todas</option>
          <?php foreach ($categorias as $cat): ?>
          <option value="<?= $cat['id'] ?>" <?= $categoria == $cat['id'] ? 'selected':'' ?>>
            <?= htmlspecialchars($cat['nome']) ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-6 col-md-2">
        <button type="submit" class="btn btn-sm w-100"
                style="background:var(--kf-primary);color:#fff;border:none">
          <i class="bi bi-funnel me-1"></i>Filtrar
        </button>
      </div>

    </form>
  </div>
</div>

<!-- Cards resumo -->
<div class="row g-3 mb-4">
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm border-start border-danger border-3">
      <div class="card-body p-3">
        <div class="fw-bold" style="font-size:24px;color:#dc3545"><?= $resumo['vencidos'] ?? 0 ?></div>
        <div class="text-muted small">Lotes vencidos</div>
        <div class="text-danger small fw-semibold"><?= $resumo['qtd_vencida'] ?? 0 ?> unidades</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm border-start border-warning border-3">
      <div class="card-body p-3">
        <div class="fw-bold" style="font-size:24px;color:#d97706"><?= $resumo['ate_30_dias'] ?? 0 ?></div>
        <div class="text-muted small">Vencem em 30 dias</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm border-start border-info border-3">
      <div class="card-body p-3">
        <div class="fw-bold" style="font-size:24px;color:#0891b2"><?= $resumo['ate_60_dias'] ?? 0 ?></div>
        <div class="text-muted small">Vencem em 60 dias</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm border-start border-secondary border-3">
      <div class="card-body p-3">
        <div class="fw-bold" style="font-size:24px;color:#6c757d"><?= $resumo['ate_90_dias'] ?? 0 ?></div>
        <div class="text-muted small">Vencem em 90 dias</div>
      </div>
    </div>
  </div>
</div>

<!-- Alerta de valor em risco -->
<?php
$valorRisco = array_sum(array_column($lotes, 'valor_em_risco'));
if ($valorRisco > 0):
?>
<div class="alert border-0 mb-4 d-flex align-items-center gap-3"
     style="background:#fff3cd;border-left:4px solid #ffc107 !important">
  <i class="bi bi-exclamation-triangle-fill text-warning fs-4"></i>
  <div>
    <strong>Valor em risco:</strong>
    <?= number_format($valorRisco, 2, ',', '.') ?> MZN em produtos
    <?= $tipo === 'vencidos' ? 'já vencidos' : "que vencem nos próximos {$prazo} dias" ?>.
    <span class="text-muted small">Considere promoções, devoluções ao fornecedor ou abate.</span>
  </div>
</div>
<?php endif; ?>

<!-- Tabela -->
<div class="card border-0 shadow-sm">
  <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
    <h6 class="fw-bold mb-0">
      <i class="bi bi-table me-2 text-success"></i>
      <?= $tipoLabels[$tipo] ?? 'Lotes' ?>
      <?= $tipo !== 'vencidos' ? "— próximos {$prazo} dias" : '' ?>
    </h6>
    <span class="badge bg-<?= $tipo === 'vencidos' ? 'danger' : 'warning text-dark' ?>">
      <?= count($lotes) ?> lotes
    </span>
  </div>
  <div class="card-body p-0">
    <?php if (empty($lotes)): ?>
    <div class="text-center py-5 text-muted">
      <i class="bi bi-check-circle fs-1 d-block mb-2 text-success"></i>
      <?php if ($tipo === 'vencidos'): ?>
        Nenhum lote vencido. 
      <?php else: ?>
        Nenhum lote a vencer nos próximos <?= $prazo ?> dias.
      <?php endif; ?>
    </div>
    <?php else: ?>
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0" style="font-size:12px">
        <thead class="table-light">
          <tr>
            <th class="ps-3">Produto</th>
            <th>Categoria</th>
            <th>Fornecedor</th>
            <th class="text-center" style="width:110px">Nº Lote</th>
            <th class="text-center" style="width:80px">Qtd.</th>
            <th class="text-center" style="width:110px">Validade</th>
            <th class="text-center" style="width:100px">Dias</th>
            <th class="text-end"    style="width:120px">Valor Risco</th>
            <th class="text-center" style="width:110px">Acção Sugerida</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($lotes as $l):
            $dias    = (int)$l['dias_para_vencer'];
            $vencido = $dias < 0;

            if ($vencido) {
                $rowCls  = 'table-danger';
                $diasStr = '<span class="badge bg-danger">Vencido há ' . abs($dias) . 'd</span>';
                $accao   = '<span class="badge bg-danger" style="font-size:10px">Abater/Devolver</span>';
            } elseif ($dias <= 15) {
                $rowCls  = 'table-danger';
                $diasStr = '<span class="badge bg-danger">' . $dias . ' dias</span>';
                $accao   = '<span class="badge bg-warning text-dark" style="font-size:10px">Promoção urgente</span>';
            } elseif ($dias <= 30) {
                $rowCls  = 'table-warning';
                $diasStr = '<span class="badge bg-warning text-dark">' . $dias . ' dias</span>';
                $accao   = '<span class="badge bg-warning text-dark" style="font-size:10px">Promoção</span>';
            } elseif ($dias <= 60) {
                $rowCls  = '';
                $diasStr = '<span class="badge bg-info text-dark">' . $dias . ' dias</span>';
                $accao   = '<span class="badge bg-info text-dark" style="font-size:10px">Monitorizar</span>';
            } else {
                $rowCls  = '';
                $diasStr = '<span class="badge bg-secondary">' . $dias . ' dias</span>';
                $accao   = '<span class="badge bg-secondary" style="font-size:10px">Normal</span>';
            }
          ?>
          <tr class="<?= $rowCls ?>">
            <td class="ps-3">
              <div class="fw-semibold"><?= htmlspecialchars($l['produto_nome']) ?></div>
              <div class="text-muted" style="font-size:10px"><?= htmlspecialchars($l['unidade_medida']) ?></div>
            </td>
            <td class="text-muted"><?= htmlspecialchars($l['categoria_nome']) ?></td>
            <td class="text-muted"><?= htmlspecialchars($l['fornecedor_nome'] ?? '—') ?></td>
            <td class="text-center fw-semibold"><?= htmlspecialchars($l['numero_lote']) ?></td>
            <td class="text-center fw-bold"><?= $l['quantidade'] ?></td>
            <td class="text-center"><?= date('d/m/Y', strtotime($l['validade'])) ?></td>
            <td class="text-center"><?= $diasStr ?></td>
            <td class="text-end fw-semibold <?= $vencido ? 'text-danger' : '' ?>">
              <?= number_format($l['valor_em_risco'], 2, ',', '.') ?> MZN
            </td>
            <td class="text-center"><?= $accao ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
        <?php if ($valorRisco > 0): ?>
        <tfoot class="table-light">
          <tr>
            <td colspan="7" class="ps-3 fw-bold">TOTAL VALOR EM RISCO</td>
            <td class="text-end fw-bold text-danger" style="font-size:14px">
              <?= number_format($valorRisco, 2, ',', '.') ?> MZN
            </td>
            <td></td>
          </tr>
        </tfoot>
        <?php endif; ?>
      </table>
    </div>
    <?php endif; ?>
  </div>
</div>
