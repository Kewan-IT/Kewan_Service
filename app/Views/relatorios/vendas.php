<?php
$APP = $_ENV['APP_URL'] ?? '';

$pagamentosLabel = [
    'dinheiro'       => ['label'=>'Dinheiro',      'icon'=>'bi-cash',          'cor'=>'success'],
    'mpesa'          => ['label'=>'M-Pesa',         'icon'=>'bi-phone',         'cor'=>'warning'],
    'emola'          => ['label'=>'e-Mola',         'icon'=>'bi-phone-fill',    'cor'=>'info'],
    'cartao_debito'  => ['label'=>'Cartão Débito',  'icon'=>'bi-credit-card',   'cor'=>'primary'],
    'cartao_credito' => ['label'=>'Cartão Crédito', 'icon'=>'bi-credit-card-2-front','cor'=>'danger'],
    'transferencia'  => ['label'=>'Transferência',  'icon'=>'bi-bank',          'cor'=>'secondary'],
    'credito'        => ['label'=>'Crédito',        'icon'=>'bi-clock-history', 'cor'=>'dark'],
];

$statusLabel = [
    'concluida' => ['label'=>'Concluída','badge'=>'success'],
    'cancelada' => ['label'=>'Cancelada','badge'=>'danger'],
    'pendente'  => ['label'=>'Pendente', 'badge'=>'warning'],
    'devolvida' => ['label'=>'Devolvida','badge'=>'secondary'],
];
?>

<!-- Cabeçalho -->
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
  <div>
    <h1 class="h4 fw-bold mb-0" style="color:var(--kf-primary)">
      <i class="bi bi-receipt-cutoff me-2"></i>Relatório de Vendas
    </h1>
    <p class="text-muted small mb-0 mt-1">
      <?= date('d/m/Y', strtotime($filtros['data_inicio'])) ?>
      até
      <?= date('d/m/Y', strtotime($filtros['data_fim'])) ?>
    </p>
  </div>
  <div class="d-flex gap-2">
    <a href="<?= $APP ?>/relatorios/vendas/pdf?<?= http_build_query($filtros) ?>"
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
      <div class="col-6 col-md-2">
        <label class="form-label small mb-1 text-muted">Data Início</label>
        <input type="date" name="data_inicio" class="form-control form-control-sm"
               value="<?= $filtros['data_inicio'] ?>">
      </div>
      <div class="col-6 col-md-2">
        <label class="form-label small mb-1 text-muted">Data Fim</label>
        <input type="date" name="data_fim" class="form-control form-control-sm"
               value="<?= $filtros['data_fim'] ?>">
      </div>
      <div class="col-6 col-md-2">
        <label class="form-label small mb-1 text-muted">Funcionário</label>
        <select name="funcionario_id" class="form-select form-select-sm">
          <option value="">Todos</option>
          <?php foreach ($funcionarios as $fu): ?>
          <option value="<?= $fu['id'] ?>" <?= $filtros['funcionario_id'] == $fu['id'] ? 'selected':'' ?>>
            <?= htmlspecialchars($fu['nome']) ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-6 col-md-2">
        <label class="form-label small mb-1 text-muted">Pagamento</label>
        <select name="forma_pagamento" class="form-select form-select-sm">
          <option value="">Todos</option>
          <?php foreach ($pagamentosLabel as $val => $info): ?>
          <option value="<?= $val ?>" <?= $filtros['forma_pagamento'] === $val ? 'selected':'' ?>>
            <?= $info['label'] ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-6 col-md-2">
        <label class="form-label small mb-1 text-muted">Estado</label>
        <select name="status" class="form-select form-select-sm">
          <option value="">Todos</option>
          <option value="concluida" <?= $filtros['status']==='concluida' ? 'selected':'' ?>>Concluídas</option>
          <option value="cancelada" <?= $filtros['status']==='cancelada' ? 'selected':'' ?>>Canceladas</option>
        </select>
      </div>
      <div class="col-6 col-md-2 d-flex gap-1">
        <button type="submit" class="btn btn-sm flex-fill"
                style="background:var(--kf-primary);color:#fff;border:none">
          <i class="bi bi-funnel me-1"></i>Filtrar
        </button>
        <a href="<?= $APP ?>/relatorios/vendas" class="btn btn-sm btn-outline-secondary">
          <i class="bi bi-x"></i>
        </a>
      </div>
    </form>

    <!-- Atalhos de período -->
    <div class="d-flex gap-2 mt-2 flex-wrap">
      <?php
      $atalhos = [
        'Hoje'        => ['data_inicio'=>date('Y-m-d'),      'data_fim'=>date('Y-m-d')],
        'Esta semana' => ['data_inicio'=>date('Y-m-d', strtotime('monday this week')), 'data_fim'=>date('Y-m-d')],
        'Este mês'    => ['data_inicio'=>date('Y-m-01'),     'data_fim'=>date('Y-m-d')],
        'Mês anterior'=> ['data_inicio'=>date('Y-m-01', strtotime('first day of last month')), 'data_fim'=>date('Y-m-t', strtotime('last month'))],
        'Este ano'    => ['data_inicio'=>date('Y-01-01'),    'data_fim'=>date('Y-m-d')],
      ];
      foreach ($atalhos as $label => $datas):
        $params = array_merge($filtros, $datas);
      ?>
      <a href="?<?= http_build_query($params) ?>"
         class="btn btn-xs btn-outline-secondary py-0 px-2"
         style="font-size:11px;border-radius:20px">
        <?= $label ?>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- Resumo cards -->
<div class="row g-3 mb-4">
  <?php
  $cards = [
    ['Total Vendas',    number_format($resumo['total_vendas'] ?? 0),                           'bi-receipt',      '#1a7f5a'],
    ['Valor Total',     number_format($resumo['valor_total'] ?? 0, 2, ',', '.') . ' MZN',      'bi-cash-stack',   '#198754'],
    ['Ticket Médio',    number_format($resumo['ticket_medio'] ?? 0, 2, ',', '.') . ' MZN',     'bi-graph-up',     '#0d6efd'],
    ['Descontos',       number_format($resumo['total_descontos'] ?? 0, 2, ',', '.') . ' MZN',  'bi-tag',          '#6c757d'],
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

<div class="row g-4 mb-4">

  <!-- Gráfico de vendas por dia -->
  <div class="col-12 col-lg-8">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-white py-3">
        <h6 class="fw-bold mb-0"><i class="bi bi-bar-chart me-2 text-success"></i>Vendas por Dia</h6>
      </div>
      <div class="card-body">
        <?php if (empty($por_dia)): ?>
        <div class="text-center text-muted py-4">Sem dados para o período seleccionado.</div>
        <?php else: ?>
        <canvas id="grafico-dias" height="120"></canvas>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Por forma de pagamento -->
  <div class="col-12 col-lg-4">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-white py-3">
        <h6 class="fw-bold mb-0"><i class="bi bi-pie-chart me-2 text-success"></i>Por Pagamento</h6>
      </div>
      <div class="card-body p-0">
        <?php if (empty($por_pagamento)): ?>
        <div class="text-center text-muted py-4">Sem dados.</div>
        <?php else: ?>
        <?php
        $totalPag = array_sum(array_column($por_pagamento, 'valor_total'));
        foreach ($por_pagamento as $pg):
          $pct = $totalPag > 0 ? round($pg['valor_total'] / $totalPag * 100) : 0;
          $info = $pagamentosLabel[$pg['forma_pagamento']] ?? ['label'=>$pg['forma_pagamento'],'cor'=>'secondary'];
        ?>
        <div class="d-flex align-items-center gap-3 px-3 py-2 border-bottom">
          <div class="flex-fill">
            <div class="d-flex justify-content-between mb-1" style="font-size:12px">
              <span class="fw-semibold"><?= $info['label'] ?></span>
              <span class="text-muted"><?= $pct ?>%</span>
            </div>
            <div class="progress" style="height:5px">
              <div class="progress-bar bg-success" style="width:<?= $pct ?>%"></div>
            </div>
          </div>
          <div class="text-end flex-shrink-0" style="font-size:12px;min-width:90px">
            <div class="fw-bold"><?= number_format($pg['valor_total'],2,',','.') ?></div>
            <div class="text-muted"><?= $pg['total_vendas'] ?> vendas</div>
          </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>

</div>

<!-- Por funcionário -->
<?php if (!empty($por_funcionario)): ?>
<div class="card border-0 shadow-sm mb-4">
  <div class="card-header bg-white py-3">
    <h6 class="fw-bold mb-0"><i class="bi bi-people me-2 text-success"></i>Desempenho por Funcionário</h6>
  </div>
  <div class="table-responsive">
    <table class="table align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th class="ps-3" style="font-size:12px">Funcionário</th>
          <th class="text-center" style="font-size:12px;width:100px">Nº Vendas</th>
          <th class="text-end" style="font-size:12px;width:150px">Valor Total</th>
          <th class="text-end" style="font-size:12px;width:130px">Ticket Médio</th>
          <th class="ps-3" style="font-size:12px;width:180px">Participação</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $totalGeral = array_sum(array_column($por_funcionario, 'valor_total'));
        foreach ($por_funcionario as $i => $fu):
          $pct = $totalGeral > 0 ? round($fu['valor_total'] / $totalGeral * 100) : 0;
        ?>
        <tr>
          <td class="ps-3">
            <div class="d-flex align-items-center gap-2">
              <div class="rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center fw-bold"
                   style="width:30px;height:30px;font-size:12px;color:var(--kf-primary)">
                <?= $i + 1 ?>
              </div>
              <span class="fw-semibold" style="font-size:13px"><?= htmlspecialchars($fu['funcionario_nome'] ?? '—') ?></span>
            </div>
          </td>
          <td class="text-center fw-bold"><?= $fu['total_vendas'] ?></td>
          <td class="text-end fw-bold text-success" style="font-size:14px">
            <?= number_format($fu['valor_total'],2,',','.') ?> MZN
          </td>
          <td class="text-end text-muted" style="font-size:13px">
            <?= number_format($fu['ticket_medio'],2,',','.') ?> MZN
          </td>
          <td class="ps-3">
            <div class="d-flex align-items-center gap-2">
              <div class="progress flex-fill" style="height:6px">
                <div class="progress-bar bg-success" style="width:<?= $pct ?>%"></div>
              </div>
              <span class="text-muted" style="font-size:11px;width:32px"><?= $pct ?>%</span>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<!-- Tabela de vendas -->
<div class="card border-0 shadow-sm">
  <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
    <h6 class="fw-bold mb-0"><i class="bi bi-table me-2 text-success"></i>Detalhe das Vendas</h6>
    <span class="badge bg-success"><?= count($vendas) ?> registos</span>
  </div>
  <div class="card-body p-0">
    <?php if (empty($vendas)): ?>
    <div class="text-center py-5 text-muted">
      <i class="bi bi-receipt fs-1 d-block mb-2"></i>
      Nenhuma venda encontrada para os filtros seleccionados.
    </div>
    <?php else: ?>
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0" style="font-size:12px">
        <thead class="table-light">
          <tr>
            <th class="ps-3">Nº Venda</th>
            <th>Data/Hora</th>
            <th>Cliente</th>
            <th>Funcionário</th>
            <th class="text-center">Pagamento</th>
            <th class="text-center">Itens</th>
            <th class="text-end">Total</th>
            <th class="text-center">Estado</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($vendas as $v):
            $pg = $pagamentosLabel[$v['forma_pagamento']] ?? ['label'=>$v['forma_pagamento'],'cor'=>'secondary'];
            $st = $statusLabel[$v['status']] ?? ['label'=>$v['status'],'badge'=>'secondary'];
          ?>
          <tr>
            <td class="ps-3">
              <a href="<?= $APP ?>/vendas/<?= $v['id'] ?>/detalhe"
                 class="fw-semibold text-decoration-none" style="color:var(--kf-primary)">
                <?= htmlspecialchars($v['numero_venda']) ?>
              </a>
            </td>
            <td><?= date('d/m/Y H:i', strtotime($v['criado_em'])) ?></td>
            <td><?= htmlspecialchars($v['cliente_nome']) ?></td>
            <td><?= htmlspecialchars($v['funcionario_nome'] ?? '—') ?></td>
            <td class="text-center"><?= $pg['label'] ?></td>
            <td class="text-center"><?= $v['total_itens'] ?></td>
            <td class="text-end fw-semibold" style="color:var(--kf-primary)">
              <?= number_format($v['total'],2,',','.') ?> MZN
            </td>
            <td class="text-center">
              <span class="badge bg-<?= $st['badge'] ?>"><?= $st['label'] ?></span>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
        <tfoot class="table-light">
          <tr>
            <td colspan="6" class="ps-3 fw-bold">TOTAL</td>
            <td class="text-end fw-bold text-success" style="font-size:14px">
              <?= number_format($resumo['valor_total'] ?? 0, 2, ',', '.') ?> MZN
            </td>
            <td></td>
          </tr>
        </tfoot>
      </table>
    </div>
    <?php endif; ?>
  </div>
</div>

<!-- Chart.js -->
<?php if (!empty($por_dia)): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
const dias   = <?= json_encode(array_map(fn($d) => date('d/m', strtotime($d['dia'])), $por_dia)) ?>;
const totais = <?= json_encode(array_map(fn($d) => round($d['valor_total'],2), $por_dia)) ?>;
const qtds   = <?= json_encode(array_map(fn($d) => (int)$d['total_vendas'], $por_dia)) ?>;

new Chart(document.getElementById('grafico-dias'), {
  type: 'bar',
  data: {
    labels: dias,
    datasets: [
      {
        label: 'Valor (MZN)',
        data: totais,
        backgroundColor: '#1a7f5a22',
        borderColor: '#1a7f5a',
        borderWidth: 2,
        borderRadius: 4,
        yAxisID: 'y',
      },
      {
        label: 'Nº Vendas',
        data: qtds,
        type: 'line',
        borderColor: '#0d6efd',
        backgroundColor: '#0d6efd',
        pointRadius: 4,
        pointHoverRadius: 6,
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
      y:  { position: 'left',  beginAtZero: true,
            ticks: { callback: v => v.toLocaleString('pt-MZ') + ' MZN' } },
      y1: { position: 'right', beginAtZero: true, grid: { drawOnChartArea: false },
            ticks: { stepSize: 1 } },
    }
  }
});
</script>
<?php endif; ?>
