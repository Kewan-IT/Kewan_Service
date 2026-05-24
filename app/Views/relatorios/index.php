<?php $APP = $_ENV['APP_URL'] ?? ''; ?>

<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h1 class="h4 fw-bold mb-0" style="color:var(--kf-primary)">
      <i class="bi bi-bar-chart-line me-2"></i>Relatórios
    </h1>
    <p class="text-muted small mb-0 mt-1">Análise e exportação de dados do sistema</p>
  </div>
</div>

<div class="row g-4">

  <?php
  $relatorios = [
    [
      'href'    => '/relatorios/vendas',
      'icon'    => 'bi-receipt-cutoff',
      'cor'     => '#1a7f5a',
      'titulo'  => 'Vendas',
      'desc'    => 'Total de vendas por período, funcionário e forma de pagamento. Inclui gráfico diário e exportação PDF.',
      'tags'    => ['Período', 'Funcionário', 'Pagamento', 'PDF'],
    ],
    [
      'href'    => '/relatorios/stock',
      'icon'    => 'bi-boxes',
      'cor'     => '#0d6efd',
      'titulo'  => 'Stock',
      'desc'    => 'Estado actual do inventário com valor em stock, alertas de stock mínimo e produtos sem stock.',
      'tags'    => ['Categoria', 'Stock baixo', 'Valor', 'PDF'],
    ],
    [
      'href'    => '/relatorios/lotes-a-vencer',
      'icon'    => 'bi-calendar-x',
      'cor'     => '#dc3545',
      'titulo'  => 'Lotes a Vencer',
      'desc'    => 'Medicamentos com data de validade próxima ou já vencidos. Filtre por 30, 60 ou 90 dias.',
      'tags'    => ['30 dias', '60 dias', '90 dias', 'Vencidos'],
    ],
    [
      'href'    => '/relatorios/funcionarios',
      'icon'    => 'bi-people',
      'cor'     => '#6f42c1',
      'titulo'  => 'Funcionários',
      'desc'    => 'Desempenho de vendas por funcionário. Ranking, número de vendas e valor total no período.',
      'tags'    => ['Período', 'Ranking', 'Desempenho', 'PDF'],
    ],
  ];
  foreach ($relatorios as $r):
  ?>
  <div class="col-12 col-md-6">
    <a href="<?= $APP . $r['href'] ?>" class="text-decoration-none">
      <div class="card border-0 shadow-sm h-100 relatorio-card">
        <div class="card-body p-4">
          <div class="d-flex align-items-start gap-3">
            <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                 style="width:52px;height:52px;background:<?= $r['cor'] ?>18">
              <i class="bi <?= $r['icon'] ?> fs-4" style="color:<?= $r['cor'] ?>"></i>
            </div>
            <div class="flex-fill">
              <h5 class="fw-bold mb-1" style="color:<?= $r['cor'] ?>"><?= $r['titulo'] ?></h5>
              <p class="text-muted small mb-3" style="line-height:1.5"><?= $r['desc'] ?></p>
              <div class="d-flex flex-wrap gap-1">
                <?php foreach ($r['tags'] as $tag): ?>
                <span class="badge rounded-pill"
                      style="background:<?= $r['cor'] ?>18;color:<?= $r['cor'] ?>;font-weight:500;font-size:11px">
                  <?= $tag ?>
                </span>
                <?php endforeach; ?>
              </div>
            </div>
            <i class="bi bi-chevron-right text-muted mt-1"></i>
          </div>
        </div>
      </div>
    </a>
  </div>
  <?php endforeach; ?>

</div>

<style>
.relatorio-card { transition: transform .15s, box-shadow .15s; cursor: pointer; }
.relatorio-card:hover { transform: translateY(-3px); box-shadow: 0 6px 20px rgba(0,0,0,.12) !important; }
</style>
