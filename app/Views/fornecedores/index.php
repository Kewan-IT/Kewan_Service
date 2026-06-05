<?php
$APP = $_ENV['APP_URL'] ?? '';
?>

<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
  <div>
    <h1 class="h4 fw-bold mb-0" style="color:var(--kf-primary)">
      <i class="bi bi-building me-2"></i>Fornecedores
    </h1>
    <p class="text-muted small mb-0 mt-1">Gestão de fornecedores e distribuidores</p>
  </div>
  <a href="<?= $APP ?>/fornecedores/novo" class="btn btn-sm" style="background:var(--kf-primary);color:#fff;border:none">
    <i class="bi bi-plus-lg me-1"></i>Novo Fornecedor
  </a>
</div>


<!-- Stats -->
<div class="row g-3 mb-4">
  <?php
  $cards = [
    ['Total',      $stats['total'],      'bi-building',       '#1a7f5a'],
    ['Activos',    $stats['activos'],    'bi-building-check', '#198754'],
    ['Inactivos',  $stats['inactivos'],  'bi-building-x',     '#6c757d'],
    ['Novos 30d',  $stats['novos_mes'],  'bi-building-add',   '#0d6efd'],
  ];
  foreach ($cards as [$lbl, $val, $icon, $cor]):
  ?>
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body d-flex align-items-center gap-3 p-3">
        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
             style="width:42px;height:42px;background:<?= $cor ?>18">
          <i class="bi <?= $icon ?> fs-5" style="color:<?= $cor ?>"></i>
        </div>
        <div>
          <div class="fw-bold fs-5 lh-1"><?= $val ?></div>
          <div class="text-muted small"><?= $lbl ?></div>
        </div>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- Filtros -->
<div class="card border-0 shadow-sm mb-4">
  <div class="card-body p-3">
    <form method="GET" class="row g-2 align-items-end">
      <div class="col-12 col-md-6">
        <label class="form-label small mb-1 text-muted">Pesquisar</label>
        <div class="input-group input-group-sm">
          <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
          <input type="text" name="q" class="form-control form-control-sm border-start-0"
                 placeholder="Nome, NUIT, telefone ou cidade..."
                 value="<?= htmlspecialchars($q) ?>">
        </div>
      </div>
      <div class="col-6 col-md-3">
        <label class="form-label small mb-1 text-muted">Estado</label>
        <select name="status" class="form-select form-select-sm">
          <option value="">Todos</option>
          <option value="activo"   <?= $status === 'activo'   ? 'selected':'' ?>>Activos</option>
          <option value="inactivo" <?= $status === 'inactivo' ? 'selected':'' ?>>Inactivos</option>
        </select>
      </div>
      <div class="col-6 col-md-3 d-flex gap-2">
        <button type="submit" class="btn btn-sm flex-fill" style="background:var(--kf-primary);color:#fff;border:none">
          <i class="bi bi-funnel me-1"></i>Filtrar
        </button>
        <?php if ($q || $status): ?>
        <a href="<?= $APP ?>/fornecedores" class="btn btn-sm btn-outline-secondary">
          <i class="bi bi-x"></i>
        </a>
        <?php endif; ?>
      </div>
    </form>
  </div>
</div>

<!-- Tabela -->
<div class="card border-0 shadow-sm">
  <div class="card-body p-0">
    <?php if (empty($paginacao['data'])): ?>
    <div class="text-center py-5">
      <i class="bi bi-building text-muted" style="font-size:3rem"></i>
      <p class="text-muted mt-3 mb-1">Nenhum fornecedor encontrado.</p>
      <a href="<?= $APP ?>/fornecedores/novo" class="btn btn-sm mt-2" style="background:var(--kf-primary);color:#fff;border:none">
        <i class="bi bi-plus-lg me-1"></i>Registar primeiro fornecedor
      </a>
    </div>
    <?php else: ?>
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th class="ps-3">Nome</th>
            <th>NUIT</th>
            <th>Contacto</th>
            <th>Cidade</th>
            <th class="text-center">Estado</th>
            <th class="text-center" style="width:90px">Acções</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($paginacao['data'] as $f): ?>
          <tr>
            <td class="ps-3">
              <a href="<?= $APP ?>/fornecedores/<?= $f['id'] ?>"
                 class="fw-semibold text-decoration-none" style="color:var(--kf-primary)">
                <?= htmlspecialchars($f['nome']) ?>
              </a>
            </td>
            <td class="text-muted small"><?= htmlspecialchars($f['nuit'] ?? '—') ?></td>
            <td>
              <?php if ($f['telefone']): ?>
              <div class="small"><i class="bi bi-telephone me-1 text-muted"></i><?= htmlspecialchars($f['telefone']) ?></div>
              <?php endif; ?>
              <?php if ($f['email']): ?>
              <div class="small text-muted"><i class="bi bi-envelope me-1"></i><?= htmlspecialchars($f['email']) ?></div>
              <?php endif; ?>
              <?php if (!$f['telefone'] && !$f['email']): ?>
              <span class="text-muted small">—</span>
              <?php endif; ?>
            </td>
            <td class="text-muted small"><?= htmlspecialchars($f['cidade'] ?? '—') ?></td>
            <td class="text-center">
              <span class="badge bg-<?= $f['ativo'] ? 'success' : 'secondary' ?>">
                <?= $f['ativo'] ? 'Activo' : 'Inactivo' ?>
              </span>
            </td>
            <td class="text-center">
              <a href="<?= $APP ?>/fornecedores/<?= $f['id'] ?>/editar"
                 class="btn btn-sm btn-outline-secondary" title="Editar">
                <i class="bi bi-pencil"></i>
              </a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <?php if ($paginacao['last_page'] > 1): ?>
    <div class="d-flex justify-content-between align-items-center px-3 py-3 border-top">
      <small class="text-muted"><?= $paginacao['total'] ?> fornecedores</small>
      <nav>
        <ul class="pagination pagination-sm mb-0">
          <?php for ($i = 1; $i <= $paginacao['last_page']; $i++): ?>
          <li class="page-item <?= $i === $paginacao['current_page'] ? 'active' : '' ?>">
            <a class="page-link" href="?q=<?= urlencode($q) ?>&status=<?= urlencode($status) ?>&page=<?= $i ?>"><?= $i ?></a>
          </li>
          <?php endfor; ?>
        </ul>
      </nav>
    </div>
    <?php endif; ?>
    <?php endif; ?>
  </div>
</div>
