<?php $appUrl = $_ENV['APP_URL'] ?? ''; ?>
 
<?php
$statusLabels = [
    'rascunho'               => ['label'=>'Rascunho',       'badge'=>'secondary'],
    'enviada'                => ['label'=>'Enviada',         'badge'=>'primary'],
    'parcialmente_recebida'  => ['label'=>'Parc. Recebida',  'badge'=>'warning'],
    'recebida'               => ['label'=>'Recebida',        'badge'=>'success'],
    'cancelada'              => ['label'=>'Cancelada',       'badge'=>'danger'],
];
?>
 
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
  <div>
    <h1 class="page-title">Compras</h1>
    <p class="page-subtitle">Encomendas a fornecedores</p>
  </div>
  <a href="<?= $appUrl ?>/compras/nova" class="btn btn-success d-flex align-items-center gap-2">
    <i class="bi bi-truck"></i> Nova Compra
  </a>
</div>
 
<!-- Estatísticas -->
<div class="row g-3 mb-4">
  <?php
  $cards = [
    ['label'=>'Total Compras',   'valor'=>$stats['total'],      'icon'=>'bi-receipt',        'cor'=>'primary', 'sufixo'=>''],
    ['label'=>'Rascunhos',       'valor'=>$stats['rascunhos'],  'icon'=>'bi-pencil-square',  'cor'=>'secondary','sufixo'=>''],
    ['label'=>'Enviadas',        'valor'=>$stats['enviadas'],   'icon'=>'bi-truck',          'cor'=>'info',    'sufixo'=>''],
    ['label'=>'Valor Este Mês',  'valor'=>number_format($stats['valor_mes'],2,',','.'), 'icon'=>'bi-cash-stack', 'cor'=>'success', 'sufixo'=>' MZN'],
  ];
  foreach ($cards as $c): ?>
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body d-flex align-items-center gap-3 py-3">
        <div class="rounded-circle d-flex align-items-center justify-content-center bg-<?= $c['cor'] ?> bg-opacity-10"
             style="width:42px;height:42px;flex-shrink:0">
          <i class="bi <?= $c['icon'] ?> text-<?= $c['cor'] ?>"></i>
        </div>
        <div>
          <div class="fw-bold lh-1" style="font-size:16px"><?= $c['valor'] ?><?= $c['sufixo'] ?></div>
          <div class="text-muted" style="font-size:12px"><?= $c['label'] ?></div>
        </div>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>
 
<!-- Filtros -->
<div class="card border-0 shadow-sm mb-4">
  <div class="card-body py-3">
    <form method="GET" class="row g-2 align-items-end">
      <div class="col-12 col-md-5">
        <label class="form-label small fw-semibold mb-1">Pesquisar</label>
        <input type="text" name="q" class="form-control form-control-sm"
               placeholder="Número da compra ou fornecedor..."
               value="<?= htmlspecialchars($q) ?>">
      </div>
      <div class="col-6 col-md-3">
        <label class="form-label small fw-semibold mb-1">Estado</label>
        <select name="status" class="form-select form-select-sm">
          <option value="">Todos</option>
          <?php foreach ($statusLabels as $val => $info): ?>
          <option value="<?= $val ?>" <?= $status === $val ? 'selected' : '' ?>><?= $info['label'] ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-6 col-md-2">
        <button type="submit" class="btn btn-success btn-sm w-100">
          <i class="bi bi-search me-1"></i>Filtrar
        </button>
      </div>
      <?php if ($q || $status): ?>
      <div class="col-6 col-md-2">
        <a href="<?= $appUrl ?>/compras" class="btn btn-outline-secondary btn-sm w-100">
          <i class="bi bi-x me-1"></i>Limpar
        </a>
      </div>
      <?php endif; ?>
    </form>
  </div>
</div>
 
<!-- Flash messages -->
<?php if ($flash_sucesso ?? null): ?>
<div class="alert alert-success alert-dismissible fade show py-2" role="alert">
  <i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($flash_sucesso) ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
<?php if ($flash_erro ?? null): ?>
<div class="alert alert-danger alert-dismissible fade show py-2" role="alert">
  <i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($flash_erro) ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
 
<!-- Tabela -->
<div class="card border-0 shadow-sm">
  <div class="card-body p-0">
    <?php if (empty($paginacao['data'])): ?>
    <div class="text-center py-5">
      <i class="bi bi-truck text-muted" style="font-size:3rem"></i>
      <p class="text-muted mt-3 mb-0">Nenhuma compra encontrada.</p>
      <a href="<?= $appUrl ?>/compras/nova" class="btn btn-success btn-sm mt-3">
        <i class="bi bi-plus-circle me-1"></i>Criar primeira compra
      </a>
    </div>
    <?php else: ?>
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th class="ps-3" style="width:140px">Número</th>
            <th>Fornecedor</th>
            <th class="text-center" style="width:120px">Data Pedido</th>
            <th class="text-center" style="width:130px">Estado</th>
            <th class="text-end" style="width:130px">Total</th>
            <th class="text-center" style="width:80px">Acções</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($paginacao['data'] as $c): ?>
          <tr>
            <td class="ps-3">
              <a href="<?= $appUrl ?>/compras/<?= $c['id'] ?>" class="fw-semibold text-decoration-none text-success">
                <?= htmlspecialchars($c['numero_compra']) ?>
              </a>
            </td>
            <td>
              <div class="fw-semibold" style="font-size:13px"><?= htmlspecialchars($c['fornecedor_nome']) ?></div>
              <?php if ($c['numero_fatura']): ?>
              <div class="text-muted" style="font-size:11px">Fatura: <?= htmlspecialchars($c['numero_fatura']) ?></div>
              <?php endif; ?>
            </td>
            <td class="text-center" style="font-size:13px">
              <?= date('d/m/Y', strtotime($c['data_pedido'])) ?>
              <?php if ($c['data_entrega']): ?>
              <div class="text-muted" style="font-size:11px">Entrega: <?= date('d/m/Y', strtotime($c['data_entrega'])) ?></div>
              <?php endif; ?>
            </td>
            <td class="text-center">
              <?php $s = $statusLabels[$c['status']] ?? ['label'=>$c['status'],'badge'=>'secondary']; ?>
              <span class="badge bg-<?= $s['badge'] ?>"><?= $s['label'] ?></span>
            </td>
            <td class="text-end fw-bold text-success" style="font-size:14px">
              <?= number_format($c['total'], 2, ',', '.') ?> MZN
            </td>
            <td class="text-center">
              <a href="<?= $appUrl ?>/compras/<?= $c['id'] ?>"
                 class="btn btn-sm btn-outline-secondary" title="Ver detalhe">
                <i class="bi bi-eye"></i>
              </a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
 
    <!-- Paginação -->
    <?php if ($paginacao['last_page'] > 1): ?>
    <div class="d-flex justify-content-between align-items-center px-3 py-3 border-top">
      <small class="text-muted">
        <?= $paginacao['total'] ?> compras encontradas
      </small>
      <nav>
        <ul class="pagination pagination-sm mb-0">
          <?php for ($i = 1; $i <= $paginacao['last_page']; $i++): ?>
          <li class="page-item <?= $i === $paginacao['current_page'] ? 'active' : '' ?>">
            <a class="page-link"
               href="?q=<?= urlencode($q) ?>&status=<?= urlencode($status) ?>&page=<?= $i ?>">
              <?= $i ?>
            </a>
          </li>
          <?php endfor; ?>
        </ul>
      </nav>
    </div>
    <?php endif; ?>
    <?php endif; ?>
  </div>
</div>
