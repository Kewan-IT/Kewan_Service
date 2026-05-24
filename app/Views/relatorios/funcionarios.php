<?php
$APP = $_ENV['APP_URL'] ?? '';

$perfisLabel = [
    'admin'         => ['label'=>'Administrador', 'badge'=>'danger'],
    'farmaceutico'  => ['label'=>'Farmacêutico',  'badge'=>'success'],
    'caixa'         => ['label'=>'Caixa',          'badge'=>'primary'],
    'tecnico'       => ['label'=>'Técnico',         'badge'=>'secondary'],
];
?>

<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
  <div>
    <h1 class="h4 fw-bold mb-0" style="color:var(--kf-primary)">
      <i class="bi bi-people me-2"></i>Relatório de Funcionários
    </h1>
    <p class="text-muted small mb-0 mt-1">
      <?= date('d/m/Y', strtotime($filtros['data_inicio'])) ?>
      até
      <?= date('d/m/Y', strtotime($filtros['data_fim'])) ?>
    </p>
  </div>
  <div class="d-flex gap-2">
    <a href="<?= $APP ?>/relatorios/funcionarios/pdf?<?= http_build_query($filtros) ?>"
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
      <div class="col-6 col-md-3">
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
        <label class="form-label small mb-1 text-muted">Perfil</label>
        <select name="perfil" class="form-select form-select-sm">
          <option value="">Todos</option>
          <?php foreach ($perfisLabel as $val => $info): ?>
          <option value="<?= $val ?>" <?= ($filtros['perfil'] ?? '') === $val ? 'selected':'' ?>>
            <?= $info['label'] ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-12 col-md-3 d-flex gap-1">
        <button type="submit" class="btn btn-sm flex-fill"
                style="background:var(--kf-primary);color:#fff;border:none">
          <i class="bi bi-funnel me-1"></i>Filtrar
        </button>
        <a href="<?= $APP ?>/relatorios/funcionarios" class="btn btn-sm btn-outline-secondary">
          <i class="bi bi-x"></i>
        </a>
      </div>
    </form>

    <!-- Atalhos período -->
    <div class="d-flex gap-2 mt-2 flex-wrap">
      <?php
      $atalhos = [
        'Hoje'         => ['data_inicio'=>date('Y-m-d'),    'data_fim'=>date('Y-m-d')],
        'Esta semana'  => ['data_inicio'=>date('Y-m-d', strtotime('monday this week')), 'data_fim'=>date('Y-m-d')],
        'Este mês'     => ['data_inicio'=>date('Y-m-01'),   'data_fim'=>date('Y-m-d')],
        'Mês anterior' => ['data_inicio'=>date('Y-m-01', strtotime('first day of last month')), 'data_fim'=>date('Y-m-t', strtotime('last month'))],
        'Este ano'     => ['data_inicio'=>date('Y-01-01'),  'data_fim'=>date('Y-m-d')],
      ];
      foreach ($atalhos as $label => $datas):
        $params = array_merge($filtros, $datas);
      ?>
      <a href="?<?= http_build_query($params) ?>"
         class="btn btn-xs btn-outline-secondary py-0 px-2" style="font-size:11px;border-radius:20px">
        <?= $label ?>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- Cards resumo geral -->
<div class="row g-3 mb-4">
  <?php
  $cards = [
    ['Funcionários Activos', $resumo['total_funcionarios'],                              'bi-people',       '#1a7f5a'],
    ['Total Vendas',         $resumo['total_vendas'],                                    'bi-receipt',      '#0d6efd'],
    ['Valor Total',          number_format($resumo['valor_total']??0,2,',','.') . ' MZN','bi-cash-stack',   '#198754'],
    ['Melhor Ticket Médio',  number_format($resumo['melhor_ticket']??0,2,',','.') . ' MZN','bi-graph-up',  '#6f42c1'],
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

<!-- Ranking -->
<?php if (!empty($ranking)): ?>
<div class="row g-4 mb-4">

  <!-- Pódio top 3 -->
  <?php if (count($ranking) >= 1): ?>
  <div class="col-12 col-lg-4">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-white py-3">
        <h6 class="fw-bold mb-0"><i class="bi bi-trophy me-2 text-warning"></i>Top 3</h6>
      </div>
      <div class="card-body py-4">
        <?php
        $medalhas = ['🥇','🥈','🥉'];
        $cores    = ['#ffd700','#c0c0c0','#cd7f32'];
        foreach (array_slice($ranking, 0, 3) as $i => $fu):
          $pct = $ranking[0]['valor_total'] > 0
            ? round($fu['valor_total'] / $ranking[0]['valor_total'] * 100) : 100;
        ?>
        <div class="d-flex align-items-center gap-3 mb-3">
          <div style="font-size:28px;width:36px;text-align:center"><?= $medalhas[$i] ?? ($i+1) ?></div>
          <div class="flex-fill">
            <div class="fw-bold" style="font-size:13px"><?= htmlspecialchars($fu['nome']) ?></div>
            <div class="progress mt-1" style="height:6px">
              <div class="progress-bar" style="width:<?= $pct ?>%;background:<?= $cores[$i] ?? 'var(--kf-primary)' ?>"></div>
            </div>
          </div>
          <div class="text-end flex-shrink-0">
            <div class="fw-bold text-success" style="font-size:13px">
              <?= number_format($fu['valor_total'],2,',','.') ?>
            </div>
            <div class="text-muted" style="font-size:11px"><?= $fu['total_vendas'] ?> vendas</div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <!-- Gráfico comparativo -->
  <div class="col-12 col-lg-8">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-white py-3">
        <h6 class="fw-bold mb-0"><i class="bi bi-bar-chart me-2 text-success"></i>Comparação de Desempenho</h6>
      </div>
      <div class="card-body">
        <canvas id="grafico-funcionarios" height="140"></canvas>
      </div>
    </div>
  </div>

</div>

<!-- Tabela de ranking completo -->
<div class="card border-0 shadow-sm mb-4">
  <div class="card-header bg-white py-3">
    <h6 class="fw-bold mb-0"><i class="bi bi-table me-2 text-success"></i>Ranking Completo</h6>
  </div>
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0" style="font-size:12px">
      <thead class="table-light">
        <tr>
          <th class="ps-3 text-center" style="width:50px">Pos.</th>
          <th>Funcionário</th>
          <th>Perfil</th>
          <th class="text-center">Nº Vendas</th>
          <th class="text-center">Itens Vendidos</th>
          <th class="text-end">Ticket Médio</th>
          <th class="text-end">Valor Total</th>
          <th class="text-center">Participação</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $totalGeral = array_sum(array_column($ranking, 'valor_total'));
        foreach ($ranking as $i => $fu):
          $pct   = $totalGeral > 0 ? round($fu['valor_total'] / $totalGeral * 100) : 0;
          $perfil = $perfisLabel[$fu['perfil'] ?? ''] ?? ['label'=>($fu['perfil']??'—'),'badge'=>'secondary'];
        ?>
        <tr>
          <td class="ps-3 text-center">
            <?php if ($i === 0): ?>
              <span style="font-size:18px">🥇</span>
            <?php elseif ($i === 1): ?>
              <span style="font-size:18px">🥈</span>
            <?php elseif ($i === 2): ?>
              <span style="font-size:18px">🥉</span>
            <?php else: ?>
              <span class="text-muted fw-bold"><?= $i + 1 ?></span>
            <?php endif; ?>
          </td>
          <td>
            <div class="fw-semibold"><?= htmlspecialchars($fu['nome']) ?></div>
            <div class="text-muted" style="font-size:10px"><?= htmlspecialchars($fu['email'] ?? '') ?></div>
          </td>
          <td><span class="badge bg-<?= $perfil['badge'] ?>"><?= $perfil['label'] ?></span></td>
          <td class="text-center fw-bold"><?= $fu['total_vendas'] ?></td>
          <td class="text-center"><?= number_format($fu['total_itens'] ?? 0) ?></td>
          <td class="text-end text-muted"><?= number_format($fu['ticket_medio'],2,',','.') ?> MZN</td>
          <td class="text-end fw-bold text-success" style="font-size:14px">
            <?= number_format($fu['valor_total'],2,',','.') ?> MZN
          </td>
          <td class="text-center">
            <div class="d-flex align-items-center gap-2">
              <div class="progress flex-fill" style="height:6px">
                <div class="progress-bar bg-success" style="width:<?= $pct ?>%"></div>
              </div>
              <span class="text-muted" style="font-size:11px;min-width:32px"><?= $pct ?>%</span>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot class="table-light">
        <tr>
          <td colspan="3" class="ps-3 fw-bold">TOTAL</td>
          <td class="text-center fw-bold"><?= $resumo['total_vendas'] ?></td>
          <td></td>
          <td></td>
          <td class="text-end fw-bold text-success" style="font-size:14px">
            <?= number_format($resumo['valor_total']??0,2,',','.') ?> MZN
          </td>
          <td></td>
        </tr>
      </tfoot>
    </table>
  </div>
</div>

<!-- Detalhe por dia do funcionário seleccionado -->
<?php if (!empty($filtros['funcionario_id']) && !empty($por_dia)): ?>
<div class="card border-0 shadow-sm mb-4">
  <div class="card-header bg-white py-3">
    <h6 class="fw-bold mb-0">
      <i class="bi bi-calendar3 me-2 text-success"></i>
      Vendas por Dia — <?= htmlspecialchars($ranking[0]['nome'] ?? '') ?>
    </h6>
  </div>
  <div class="card-body">
    <canvas id="grafico-dias" height="100"></canvas>
  </div>
</div>
<?php endif; ?>

<?php endif; ?>

<?php if (empty($ranking)): ?>
<div class="text-center py-5 text-muted">
  <i class="bi bi-people fs-1 d-block mb-2"></i>
  Nenhuma venda encontrada para o período seleccionado.
</div>
<?php endif; ?>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
<?php if (!empty($ranking)): ?>
// Gráfico comparativo
new Chart(document.getElementById('grafico-funcionarios'), {
  type: 'bar',
  data: {
    labels: <?= json_encode(array_map(fn($f) => $f['nome'], $ranking)) ?>,
    datasets: [
      {
        label: 'Valor Vendido (MZN)',
        data: <?= json_encode(array_map(fn($f) => round($f['valor_total'],2), $ranking)) ?>,
        backgroundColor: <?= json_encode(array_map(fn($i) => ['#1a7f5a','#198754','#0d6efd','#6f42c1','#fd7e14','#20c997'][$i % 6], array_keys($ranking))) ?>,
        borderRadius: 5,
        yAxisID: 'y',
      },
      {
        label: 'Nº Vendas',
        data: <?= json_encode(array_map(fn($f) => (int)$f['total_vendas'], $ranking)) ?>,
        type: 'line',
        borderColor: '#dc3545',
        backgroundColor: '#dc3545',
        pointRadius: 5,
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
<?php endif; ?>

<?php if (!empty($filtros['funcionario_id']) && !empty($por_dia)): ?>
// Gráfico por dia
new Chart(document.getElementById('grafico-dias'), {
  type: 'bar',
  data: {
    labels: <?= json_encode(array_map(fn($d) => date('d/m', strtotime($d['dia'])), $por_dia)) ?>,
    datasets: [{
      label: 'Valor (MZN)',
      data: <?= json_encode(array_map(fn($d) => round($d['valor_total'],2), $por_dia)) ?>,
      backgroundColor: '#1a7f5a33',
      borderColor: '#1a7f5a',
      borderWidth: 2,
      borderRadius: 4,
    }]
  },
  options: {
    responsive: true,
    plugins: { legend: { display: false } },
    scales: {
      y: { beginAtZero: true, ticks: { callback: v => v.toLocaleString('pt-MZ') + ' MZN' } }
    }
  }
});
<?php endif; ?>
</script>
