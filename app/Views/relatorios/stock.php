<?php
$APP = $_ENV['APP_URL'] ?? '';
$filtro_categoria = $_GET['categoria_id'] ?? '';
$filtro_estado    = $_GET['estado']       ?? '';
$filtro_busca     = trim($_GET['q']       ?? '');
?>

<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
  <div>
    <h1 class="h4 fw-bold mb-0" style="color:var(--kf-primary)">
      <i class="bi bi-boxes me-2"></i>Relatório de Stock
    </h1>
    <p class="text-muted small mb-0 mt-1">Estado actual do inventário</p>
  </div>
  <div class="d-flex gap-2">
    <a href="<?= $APP ?>/relatorios/stock/pdf?<?= http_build_query($_GET) ?>"
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
      <div class="col-12 col-md-4">
        <label class="form-label small mb-1 text-muted">Pesquisar</label>
        <input type="text" name="q" class="form-control form-control-sm"
               placeholder="Nome ou código de barras..."
               value="<?= htmlspecialchars($filtro_busca) ?>">
      </div>
      <div class="col-6 col-md-3">
        <label class="form-label small mb-1 text-muted">Categoria</label>
        <select name="categoria_id" class="form-select form-select-sm">
          <option value="">Todas</option>
          <?php foreach (($categorias ?? []) as $cat): ?>
          <option value="<?= $cat['id'] ?>" <?= $filtro_categoria == $cat['id'] ? 'selected':'' ?>>
            <?= htmlspecialchars($cat['nome']) ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-6 col-md-3">
        <label class="form-label small mb-1 text-muted">Estado</label>
        <select name="estado" class="form-select form-select-sm">
          <option value="">Todos</option>
          <option value="normal"   <?= $filtro_estado==='normal'   ? 'selected':'' ?>>Normal</option>
          <option value="baixo"    <?= $filtro_estado==='baixo'    ? 'selected':'' ?>>Stock Baixo</option>
          <option value="esgotado" <?= $filtro_estado==='esgotado' ? 'selected':'' ?>>Esgotado</option>
        </select>
      </div>
      <div class="col-12 col-md-2 d-flex gap-1">
        <button type="submit" class="btn btn-sm flex-fill"
                style="background:var(--kf-primary);color:#fff;border:none">
          <i class="bi bi-funnel me-1"></i>Filtrar
        </button>
        <?php if ($filtro_busca || $filtro_categoria || $filtro_estado): ?>
        <a href="<?= $APP ?>/relatorios/stock" class="btn btn-sm btn-outline-secondary">
          <i class="bi bi-x"></i>
        </a>
        <?php endif; ?>
      </div>
    </form>
  </div>
</div>

<!-- Cards resumo -->
<div class="row g-3 mb-4">
  <?php
  $cards = [
    ['Total Produtos',   $resumo['total_produtos'] ?? 0,                                'bi-boxes',                '#1a7f5a'],
    ['Stock Baixo',      $resumo['stock_baixo'] ?? 0,                                   'bi-exclamation-triangle', '#ffc107'],
    ['Esgotados',        $resumo['esgotados'] ?? 0,                                     'bi-x-circle',             '#dc3545'],
    ['Valor Inventário', number_format($resumo['valor_total'] ?? 0,2,',','.') . ' MZN', 'bi-cash-stack',           '#0d6efd'],
  ];
  foreach ($cards as [$lbl, $val, $icon, $cor]):
  ?>
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body d-flex align-items-center gap-3 p-3">
        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
             style="width:44px;height:44px;background:<?= $cor ?>18">
          <i class="bi <?= $icon ?> fs-5" style="color:<?= $cor ?>"></i>
        </div>
        <div>
          <div class="fw-bold lh-1" style="font-size:16px"><?= $val ?></div>
          <div class="text-muted" style="font-size:12px"><?= $lbl ?></div>
        </div>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- Gráfico por categoria -->
<?php if (!empty($por_categoria)): ?>
<div class="card border-0 shadow-sm mb-4">
  <div class="card-header bg-white py-3">
    <h6 class="fw-bold mb-0"><i class="bi bi-bar-chart me-2 text-success"></i>Stock por Categoria</h6>
  </div>
  <div class="card-body">
    <canvas id="grafico-categorias" height="80"></canvas>
  </div>
</div>
<?php endif; ?>

<!-- Tabela -->
<div class="card border-0 shadow-sm">
  <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
    <h6 class="fw-bold mb-0"><i class="bi bi-table me-2 text-success"></i>Inventário Detalhado</h6>
    <span class="badge bg-success"><?= count($produtos ?? []) ?> produtos</span>
  </div>
  <div class="card-body p-0">
    <?php if (empty($produtos)): ?>
    <div class="text-center py-5 text-muted">
      <i class="bi bi-boxes fs-1 d-block mb-2"></i>Nenhum produto encontrado.
    </div>
    <?php else: ?>
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0" style="font-size:12px">
        <thead class="table-light">
          <tr>
            <th class="ps-3">Produto</th>
            <th>Categoria</th>
            <th class="text-center" style="width:90px">Stock Act.</th>
            <th class="text-center" style="width:90px">Stock Mín.</th>
            <th class="text-end"    style="width:110px">Preço Compra</th>
            <th class="text-end"    style="width:110px">Preço Venda</th>
            <th class="text-end"    style="width:130px">Valor Stock</th>
            <th class="text-center" style="width:100px">Estado</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($produtos as $p):
            if ($p['estoque_actual'] <= 0) {
                $estado = ['label'=>'Esgotado',    'badge'=>'danger'];
                $rowCls = 'table-danger';
            } elseif ($p['estoque_actual'] <= $p['estoque_min']) {
                $estado = ['label'=>'Stock Baixo', 'badge'=>'warning'];
                $rowCls = 'table-warning';
            } else {
                $estado = ['label'=>'Normal',      'badge'=>'success'];
                $rowCls = '';
            }
            $valorStock = $p['estoque_actual'] * $p['preco_compra'];
          ?>
          <tr class="<?= $rowCls ?>">
            <td class="ps-3">
              <div class="fw-semibold"><?= htmlspecialchars($p['nome']) ?></div>
              <?php if ($p['codigo_barras']): ?>
              <div class="text-muted" style="font-size:10px"><?= htmlspecialchars($p['codigo_barras']) ?></div>
              <?php endif; ?>
            </td>
            <td class="text-muted"><?= htmlspecialchars($p['categoria_nome']) ?></td>
            <td class="text-center fw-bold <?= $p['estoque_actual'] <= 0 ? 'text-danger' : ($p['estoque_actual'] <= $p['estoque_min'] ? 'text-warning' : 'text-success') ?>">
              <?= $p['estoque_actual'] ?>
            </td>
            <td class="text-center text-muted"><?= $p['estoque_min'] ?></td>
            <td class="text-end text-muted"><?= number_format($p['preco_compra'],2,',','.') ?> MZN</td>
            <td class="text-end"><?= number_format($p['preco_venda'],2,',','.') ?> MZN</td>
            <td class="text-end fw-semibold" style="color:var(--kf-primary)">
              <?= number_format($valorStock,2,',','.') ?> MZN
            </td>
            <td class="text-center">
              <span class="badge bg-<?= $estado['badge'] ?>"><?= $estado['label'] ?></span>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
        <tfoot class="table-light">
          <tr>
            <td colspan="6" class="ps-3 fw-bold">TOTAL INVENTÁRIO</td>
            <td class="text-end fw-bold text-success" style="font-size:14px">
              <?= number_format($resumo['valor_total'] ?? 0,2,',','.') ?> MZN
            </td>
            <td></td>
          </tr>
        </tfoot>
      </table>
    </div>
    <?php endif; ?>
  </div>
</div>

<?php if (!empty($por_categoria)): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
const cats   = <?= json_encode(array_column($por_categoria, 'categoria_nome')) ?>;
const qtds   = <?= json_encode(array_map(fn($c) => (int)$c['total_stock'], $por_categoria)) ?>;
const valores= <?= json_encode(array_map(fn($c) => round($c['valor_stock'],2), $por_categoria)) ?>;

new Chart(document.getElementById('grafico-categorias'), {
  type: 'bar',
  data: {
    labels: cats,
    datasets: [
      {
        label: 'Unidades em Stock',
        data: qtds,
        backgroundColor: '#1a7f5a33',
        borderColor: '#1a7f5a',
        borderWidth: 2,
        borderRadius: 4,
        yAxisID: 'y',
      },
      {
        label: 'Valor (MZN)',
        data: valores,
        type: 'line',
        borderColor: '#0d6efd',
        backgroundColor: '#0d6efd',
        pointRadius: 4,
        borderWidth: 2,
        tension: 0.3,
        yAxisID: 'y1',
      }
    ]
  },
  options: {
    responsive: true,
    interaction: { mode: 'index', intersect: false },
    plugins: { legend: { position: 'top' } },
    scales: {
      y:  { position: 'left',  beginAtZero: true, ticks: { stepSize: 1 } },
      y1: { position: 'right', beginAtZero: true, grid: { drawOnChartArea: false },
            ticks: { callback: v => v.toLocaleString('pt-MZ') + ' MZN' } },
    }
  }
});
</script>
<?php endif; ?>
