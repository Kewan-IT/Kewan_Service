<?php
$APP = $_ENV['APP_URL'] ?? '';
$stats = $stats ?? [];
$aniversariantes_mes = $aniversariantes_mes ?? [];
$ultimas_vendas = $ultimas_vendas ?? [];
$stock_baixo = $stock_baixo ?? [];
$lotes_criticos = $lotes_criticos ?? [];
$funcionarios = $funcionarios ?? [];
$filtros = $filtros ?? ['periodo' => 'mes', 'data_inicio' => '', 'data_fim' => '', 'funcionario_id' => ''];

$formatMoeda = static fn(float|int|string|null $valor): string => 'MT ' . number_format((float) $valor, 2, ',', '.');
$badgeStatus = static function (string $status): string {
    return match ($status) {
        'concluida' => 'success',
        'pendente' => 'warning',
        'cancelada' => 'danger',
        default => 'secondary',
    };
};
?>

<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
  <div>
    <h1 class="page-title mb-1">
      Bem-vindo, <?= htmlspecialchars(explode(' ', $_SESSION['usuario_nome'] ?? 'Utilizador')[0]) ?>!
    </h1>
    <p class="page-subtitle mb-0">
      Visão geral operacional da KewanFarma
    </p>
  </div>
  <div class="d-flex gap-2 flex-wrap">
    <a href="<?= $APP ?>/vendas/novo" class="btn btn-sm" style="background:var(--kf-primary);color:#fff;border:none">
      <i class="bi bi-cart-plus me-1"></i>Nova venda
    </a>
    <a href="<?= $APP ?>/produtos/novo" class="btn btn-sm btn-outline-secondary">
      <i class="bi bi-box-seam me-1"></i>Novo produto
    </a>
  </div>
</div>

<div class="card border-0 shadow-sm mb-4">
  <div class="card-body p-3">
    <form method="GET" action="<?= $APP ?>/dashboard" class="row g-2 align-items-end">
      <div class="col-12 col-md-4">
        <label class="form-label small mb-1 text-muted">Período</label>
        <select id="periodo" name="periodo" class="form-select form-select-sm">
          <option value="hoje" <?= $filtros['periodo'] === 'hoje' ? 'selected' : '' ?>>Hoje</option>
          <option value="7d" <?= $filtros['periodo'] === '7d' ? 'selected' : '' ?>>Últimos 7 dias</option>
          <option value="30d" <?= $filtros['periodo'] === '30d' ? 'selected' : '' ?>>Últimos 30 dias</option>
          <option value="mes" <?= $filtros['periodo'] === 'mes' ? 'selected' : '' ?>>Mês actual</option>
          <option value="custom" <?= $filtros['periodo'] === 'custom' ? 'selected' : '' ?>>Personalizado</option>
        </select>
      </div>

      <div id="filtros-custom" class="col-12 col-md-4 <?= $filtros['periodo'] === 'custom' ? '' : 'd-none' ?>">
        <label class="form-label small mb-1 text-muted">Data inicial</label>
        <input type="date" name="data_inicio" class="form-control form-control-sm" value="<?= htmlspecialchars($filtros['data_inicio'] ?? '') ?>">
      </div>

      <div id="filtros-custom-fim" class="col-12 col-md-4 <?= $filtros['periodo'] === 'custom' ? '' : 'd-none' ?>">
        <label class="form-label small mb-1 text-muted">Data final</label>
        <input type="date" name="data_fim" class="form-control form-control-sm" value="<?= htmlspecialchars($filtros['data_fim'] ?? '') ?>">
      </div>

      <div class="col-12 col-md-4">
        <label class="form-label small mb-1 text-muted">Funcionário</label>
        <select name="funcionario_id" class="form-select form-select-sm">
          <option value="">Todos os funcionários</option>
          <?php foreach ($funcionarios as $funcionario): ?>
          <option value="<?= (int)$funcionario['id'] ?>" <?= (string)($filtros['funcionario_id'] ?? '') === (string)$funcionario['id'] ? 'selected' : '' ?>><?= htmlspecialchars($funcionario['nome']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-12 col-md-2 d-flex gap-2">
        <button type="submit" class="btn btn-sm flex-fill" style="background:var(--kf-primary);color:#fff;border:none">
          <i class="bi bi-funnel me-1"></i>Aplicar
        </button>
        <a href="<?= $APP ?>/dashboard" class="btn btn-sm btn-outline-secondary">Limpar</a>
      </div>
    </form>
  </div>
</div>

<div class="row g-3 mb-4">
  <div class="col-12 col-md-6 col-xl-4">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <div class="text-muted small">Vendas no período</div>
            <div class="fw-bold fs-3 mt-1"><?= (int) ($stats['vendas_periodo'] ?? 0) ?></div>
          </div>
          <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:42px;height:42px;background:#e8f5ef;color:var(--kf-primary)">
            <i class="bi bi-cart-check fs-5"></i>
          </div>
        </div>
        <div class="text-muted small mt-2">Receita: <?= $formatMoeda($stats['receita_periodo'] ?? 0) ?></div>
      </div>
    </div>
  </div>

  <div class="col-12 col-md-6 col-xl-4">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <div class="text-muted small">Vendas este mês</div>
            <div class="fw-bold fs-3 mt-1"><?= (int) ($stats['vendas_mes'] ?? 0) ?></div>
          </div>
          <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:42px;height:42px;background:#eef4ff;color:#0d6efd">
            <i class="bi bi-graph-up fs-5"></i>
          </div>
        </div>
        <div class="text-muted small mt-2">Receita: <?= $formatMoeda($stats['receita_mes'] ?? 0) ?></div>
      </div>
    </div>
  </div>

  <div class="col-12 col-md-6 col-xl-4">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <div class="text-muted small">Produtos activos</div>
            <div class="fw-bold fs-3 mt-1"><?= (int) ($stats['produtos_ativos'] ?? 0) ?></div>
          </div>
          <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:42px;height:42px;background:#fff4e5;color:#fd7e14">
            <i class="bi bi-boxes fs-5"></i>
          </div>
        </div>
        <div class="text-muted small mt-2">Stock baixo: <?= (int) ($stats['stock_baixo'] ?? 0) ?></div>
      </div>
    </div>
  </div>

  <div class="col-12 col-md-6 col-xl-4">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <div class="text-muted small">Clientes activos</div>
            <div class="fw-bold fs-3 mt-1"><?= (int) ($stats['clientes_activos'] ?? 0) ?></div>
          </div>
          <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:42px;height:42px;background:#f3e8ff;color:#7c3aed">
            <i class="bi bi-people fs-5"></i>
          </div>
        </div>
        <div class="text-muted small mt-2">Receitas pendentes: <?= (int) ($stats['receitas_pendentes'] ?? 0) ?></div>
      </div>
    </div>
  </div>

  <div class="col-12 col-md-6 col-xl-4">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <div class="text-muted small">Lotes críticos</div>
            <div class="fw-bold fs-3 mt-1"><?= (int) ($stats['lotes_criticos'] ?? 0) ?></div>
          </div>
          <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:42px;height:42px;background:#fff1f2;color:#dc3545">
            <i class="bi bi-calendar-x fs-5"></i>
          </div>
        </div>
        <div class="text-muted small mt-2">Vencidos: <?= (int) ($stats['lotes_vencidos'] ?? 0) ?></div>
      </div>
    </div>
  </div>
</div>

<div class="row g-3 mb-4">
  <div class="col-12">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-0 pb-0">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
          <div>
            <h2 class="h6 fw-bold mb-0">Aniversariantes do mês</h2>
            <div class="small text-muted">Funcionários que comemoram aniversário neste mês</div>
          </div>
          <span class="badge bg-primary-subtle text-primary"><?= count($aniversariantes_mes) ?> funcionário(s)</span>
        </div>
      </div>
      <div class="card-body">
        <?php if ($aniversariantes_mes): ?>
        <div class="row g-3">
          <?php foreach ($aniversariantes_mes as $funcionario): ?>
          <div class="col-12 col-md-6 col-xl-4">
            <div class="d-flex align-items-center gap-3 border rounded p-3 h-100">
              <?php if (!empty($funcionario['foto_url'])): ?>
              <img src="<?= $APP ?>/storage/uploads/<?= htmlspecialchars($funcionario['foto_url']) ?>"
                   class="rounded-circle"
                   style="width:72px;height:72px;object-fit:cover;border:3px solid var(--kf-primary-light)"
                   alt="<?= htmlspecialchars($funcionario['nome_completo']) ?>">
              <?php else: ?>
              <div class="rounded-circle d-flex align-items-center justify-content-center"
                   style="width:72px;height:72px;background:var(--kf-primary-light);color:var(--kf-primary);font-size:1.5rem;font-weight:700">
                <?= htmlspecialchars(mb_strtoupper(mb_substr($funcionario['nome_completo'], 0, 1))) ?>
              </div>
              <?php endif; ?>
              <div class="flex-grow-1 min-w-0">
                <div class="fw-semibold text-truncate"><?= htmlspecialchars($funcionario['nome_completo']) ?></div>
                <div class="small text-muted">
                  Aniversário: <?= str_pad((string) ($funcionario['dia_nascimento'] ?? ''), 2, '0', STR_PAD_LEFT) ?> de <?= htmlspecialchars($funcionario['mes_nome'] ?? '') ?>
                </div>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="text-muted">Não há aniversariantes neste mês.</div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php
$grafico_vendas = $grafico_vendas ?? ['labels' => [], 'qtds' => [], 'valores' => []];
$grafico_pagamento = $grafico_pagamento ?? ['labels' => [], 'valores' => []];
?>

<div class="row g-3 mb-4">
  <div class="col-12 col-xl-8">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-white border-0 pb-0 d-flex justify-content-between align-items-center">
        <div>
          <h2 class="h6 fw-bold mb-0">Vendas no período selecionado</h2>
          <div class="small text-muted">Evolução da receita e volume de vendas</div>
        </div>
      </div>
      <div class="card-body">
        <?php if (!empty($grafico_vendas['labels'])): ?>
        <canvas id="grafico-vendas-line" height="140"></canvas>
        <?php else: ?>
        <div class="text-muted">Ainda não há dados suficientes para exibir o gráfico.</div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="col-12 col-xl-4">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-white border-0 pb-0">
        <h2 class="h6 fw-bold mb-0">Vendas por forma de pagamento</h2>
      </div>
      <div class="card-body">
        <?php if (!empty($grafico_pagamento['labels'])): ?>
        <canvas id="grafico-pagamento" height="220"></canvas>
        <?php else: ?>
        <div class="text-muted">Ainda não há dados suficientes para exibir o gráfico.</div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<div class="row g-3">
  <div class="col-12 col-xl-7">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-white border-0 pb-0">
        <h2 class="h6 fw-bold mb-0">Últimas vendas</h2>
      </div>
      <div class="card-body">
        <?php if ($ultimas_vendas): ?>
        <div class="table-responsive">
          <table class="table table-sm align-middle mb-0">
            <thead>
              <tr class="text-muted">
                <th>Venda</th>
                <th>Cliente</th>
                <th>Pagamento</th>
                <th>Total</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($ultimas_vendas as $venda): ?>
              <tr>
                <td class="fw-semibold"><?= htmlspecialchars($venda['numero_venda']) ?></td>
                <td><?= htmlspecialchars($venda['cliente_nome']) ?></td>
                <td><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $venda['forma_pagamento']))) ?></td>
                <td><?= $formatMoeda($venda['total']) ?></td>
                <td><span class="badge bg-<?= $badgeStatus($venda['status']) ?>"><?= htmlspecialchars($venda['status']) ?></span></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php else: ?>
        <div class="text-muted">Ainda não há vendas registadas para o filtro aplicado.</div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="col-12 col-xl-5">
    <div class="card border-0 shadow-sm h-100 mb-3">
      <div class="card-header bg-white border-0 pb-0">
        <h2 class="h6 fw-bold mb-0">Alertas de stock</h2>
      </div>
      <div class="card-body">
        <?php if ($stock_baixo): ?>
        <div class="d-flex flex-column gap-2">
          <?php foreach ($stock_baixo as $produto): ?>
          <div class="d-flex justify-content-between align-items-center border rounded p-2">
            <div>
              <div class="fw-semibold"><?= htmlspecialchars($produto['nome']) ?></div>
              <div class="small text-muted"><?= (int) $produto['estoque_actual'] ?> / <?= (int) $produto['estoque_min'] ?> <?= htmlspecialchars($produto['unidade_medida']) ?></div>
            </div>
            <a href="<?= $APP ?>/produtos/<?= (int) $produto['id'] ?>" class="btn btn-sm btn-outline-secondary">Ver</a>
          </div>
          <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="text-muted">Não há produtos com stock baixo no momento.</div>
        <?php endif; ?>
      </div>
    </div>

    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-white border-0 pb-0">
        <h2 class="h6 fw-bold mb-0">Lotes próximos do vencimento</h2>
      </div>
      <div class="card-body">
        <?php if ($lotes_criticos): ?>
        <div class="d-flex flex-column gap-2">
          <?php foreach ($lotes_criticos as $lote): ?>
          <div class="d-flex justify-content-between align-items-center border rounded p-2">
            <div>
              <div class="fw-semibold"><?= htmlspecialchars($lote['produto_nome']) ?></div>
              <div class="small text-muted">Lote <?= htmlspecialchars($lote['numero_lote']) ?> • <?= (int) $lote['quantidade'] ?> <?= htmlspecialchars($lote['unidade_medida']) ?> • vence em <?= (int) $lote['dias_para_vencer'] ?> dia(s)</div>
            </div>
            <span class="badge bg-warning text-dark">Atenção</span>
          </div>
          <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="text-muted">Não há lotes críticos nos próximos 30 dias.</div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
const vendasLabels = <?= json_encode($grafico_vendas['labels']) ?>;
const vendasQtds = <?= json_encode($grafico_vendas['qtds']) ?>;
const vendasValores = <?= json_encode($grafico_vendas['valores']) ?>;
const pagamentoLabels = <?= json_encode($grafico_pagamento['labels']) ?>;
const pagamentoValores = <?= json_encode($grafico_pagamento['valores']) ?>;

const periodo = document.getElementById('periodo');
const customFields = [document.getElementById('filtros-custom'), document.getElementById('filtros-custom-fim')];

function toggleCustomPeriodo() {
  const mostrar = periodo.value === 'custom';
  customFields.forEach(el => el.classList.toggle('d-none', !mostrar));
}

toggleCustomPeriodo();
periodo.addEventListener('change', toggleCustomPeriodo);

if (vendasLabels.length) {
  new Chart(document.getElementById('grafico-vendas-line'), {
    type: 'line',
    data: {
      labels: vendasLabels,
      datasets: [
        {
          label: 'Receita (MT)',
          data: vendasValores,
          borderColor: '#1a7f5a',
          backgroundColor: '#1a7f5a22',
          borderWidth: 2,
          tension: 0.3,
          fill: true,
          yAxisID: 'y'
        },
        {
          label: 'Nº Vendas',
          data: vendasQtds,
          borderColor: '#0d6efd',
          backgroundColor: '#0d6efd',
          borderWidth: 2,
          tension: 0.3,
          yAxisID: 'y1'
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { position: 'top' } },
      scales: {
        y: {
          beginAtZero: true,
          ticks: { callback: value => `MT ${Number(value).toLocaleString('pt-MZ')}` }
        },
        y1: {
          beginAtZero: true,
          position: 'right',
          grid: { drawOnChartArea: false },
          ticks: { stepSize: 1 }
        }
      }
    }
  });
}

if (pagamentoLabels.length) {
  new Chart(document.getElementById('grafico-pagamento'), {
    type: 'doughnut',
    data: {
      labels: pagamentoLabels,
      datasets: [{
        data: pagamentoValores,
        backgroundColor: ['#1a7f5a', '#0d6efd', '#fd7e14', '#7c3aed', '#dc3545', '#6c757d'],
        borderWidth: 0
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { position: 'bottom' } }
    }
  });
}
</script>
