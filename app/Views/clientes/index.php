<?php
// app/Views/clientes/index.php
$APP = $_ENV['APP_URL'] ?? '';
?>

<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
  <div>
    <h1 class="h4 fw-bold mb-0" style="color:var(--kf-primary)">
      <i class="bi bi-people me-2"></i>Clientes
    </h1>
    <p class="text-muted small mb-0 mt-1">Base de clientes da farmácia</p>
  </div>
  <a href="<?= $APP ?>/clientes/novo" class="btn btn-sm" style="background:var(--kf-primary);color:#fff;border:none">
    <i class="bi bi-plus-lg me-1"></i>Novo Cliente
  </a>
</div>

<!-- Stats -->
<div class="row g-3 mb-4">
  <?php
  $cards = [
    ['Total',      $stats['total'],     'bi-people',       '#1a7f5a'],
    ['Activos',    $stats['activos'],   'bi-person-check', '#198754'],
    ['Inactivos',  $stats['inactivos'], 'bi-person-x',     '#6c757d'],
    ['Novos mês',  $stats['novos_mes'],       'bi-person-plus',    '#0d6efd'],
    ['Singulares', $stats['total_singular'],  'bi-person',         '#1a7f5a'],
    ['Instituições',$stats['total_instituicao'],'bi-building',       '#1565c0'],
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
    <form method="GET" action="<?= $APP ?>/clientes" class="row g-2 align-items-end">
      <div class="col-12 col-md-6">
        <label class="form-label small mb-1 text-muted">Pesquisar</label>
        <div class="input-group input-group-sm">
          <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
          <input type="text" name="q" class="form-control form-control-sm border-start-0"
                 placeholder="Nome, telefone, NUIT, BI ou email..."
                 value="<?= htmlspecialchars($pesquisa) ?>">
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
      <div class="col-6 col-md-2">
        <label class="form-label small mb-1 text-muted">Tipo</label>
        <select name="tipo" class="form-select form-select-sm">
          <option value="">Todos</option>
          <option value="singular"    <?= ($tipo ?? '') === 'singular'    ? 'selected':'' ?>>Singular</option>
          <option value="instituicao" <?= ($tipo ?? '') === 'instituicao' ? 'selected':'' ?>>Instituição</option>
        </select>
      </div>
      <div class="col-6 col-md-2 d-flex gap-2">
        <button type="submit" class="btn btn-sm flex-fill" style="background:var(--kf-primary);color:#fff;border:none">
          <i class="bi bi-funnel me-1"></i>Filtrar
        </button>
        <?php if ($pesquisa || $status): ?>
        <a href="<?= $APP ?>/clientes" class="btn btn-sm btn-outline-secondary"><i class="bi bi-x"></i></a>
        <?php endif; ?>
      </div>
    </form>
  </div>
</div>

<!-- Tabela -->
<div class="card border-0 shadow-sm">
  <div class="card-body p-0">
    <?php if (empty($paginacao['data'])): ?>
    <div class="text-center py-5 text-muted">
      <i class="bi bi-people fs-1 d-block mb-2"></i>
      <?= $pesquisa || $status ? 'Nenhum cliente encontrado.' : 'Ainda não há clientes registados.' ?>
    </div>
    <?php else: ?>
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0" style="font-size:.875rem">
        <thead style="background:var(--kf-primary-light)">
          <tr>
            <th class="ps-3 py-2 fw-semibold" style="color:var(--kf-primary)">Cliente</th>
            <th class="py-2 fw-semibold d-none d-sm-table-cell" style="color:var(--kf-primary)">Tipo</th>
              <th class="py-2 fw-semibold d-none d-md-table-cell" style="color:var(--kf-primary)">Contacto</th>
            <th class="py-2 fw-semibold d-none d-lg-table-cell" style="color:var(--kf-primary)">Compras</th>
            <th class="py-2 fw-semibold d-none d-lg-table-cell" style="color:var(--kf-primary)">Total gasto</th>
            <th class="py-2 fw-semibold d-none d-md-table-cell" style="color:var(--kf-primary)">Última visita</th>
            <th class="py-2 fw-semibold" style="color:var(--kf-primary)">Estado</th>
            <th class="pe-3 py-2"></th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($paginacao['data'] as $c): ?>
          <tr>
            <td class="ps-3">
              <div class="d-flex align-items-center gap-2">
                <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold flex-shrink-0"
                     style="width:34px;height:34px;background:var(--kf-primary);font-size:13px">
                  <?= mb_strtoupper(mb_substr($c['nome'], 0, 1)) ?>
                </div>
                <div>
                  <div class="fw-semibold lh-1"><?= htmlspecialchars($c['nome']) ?></div>
                  <?php if ($c['nuit']): ?>
                  <div class="text-muted small">NUIT: <?= htmlspecialchars($c['nuit']) ?></div>
                  <?php elseif ($c['bi']): ?>
                  <div class="text-muted small">BI: <?= htmlspecialchars($c['bi']) ?></div>
                  <?php endif; ?>
                </div>
              </div>
            </td>
            <td class="d-none d-sm-table-cell">
              <?php
              $tipoCli = $c['tipo_cliente'] ?? 'singular';
              if ($tipoCli === 'instituicao'):
              ?><span style="background:#e3f2fd;color:#1565c0;border:1px solid #bbdefb;font-size:.7rem;padding:.2em .55em;border-radius:20px;font-weight:600;white-space:nowrap"><i class="bi bi-building me-1"></i>Instituição</span><?php
              else:
              ?><span style="background:#e8f5e9;color:#2e7d32;border:1px solid #c8e6c9;font-size:.7rem;padding:.2em .55em;border-radius:20px;font-weight:600;white-space:nowrap"><i class="bi bi-person me-1"></i>Singular</span><?php endif; ?>
            </td>
            <td class="d-none d-md-table-cell">
              <div><?= htmlspecialchars($c['telefone'] ?? '—') ?></div>
              <?php if ($c['email']): ?>
              <div class="text-muted small"><?= htmlspecialchars($c['email']) ?></div>
              <?php endif; ?>
            </td>
            <td class="d-none d-lg-table-cell text-center">
              <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill">
                <?= (int)$c['total_compras'] ?>
              </span>
            </td>
            <td class="d-none d-lg-table-cell fw-semibold" style="color:var(--kf-primary)">
              MT <?= number_format((float)($c['total_gasto'] ?? 0), 2, ',', '.') ?>
            </td>
            <td class="d-none d-md-table-cell text-muted small">
              <?= $c['ultima_compra'] ? date('d/m/Y', strtotime($c['ultima_compra'])) : '—' ?>
            </td>
            <td>
              <?php if ($c['ativo']): ?>
              <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2">Activo</span>
              <?php else: ?>
              <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2">Inactivo</span>
              <?php endif; ?>
            </td>
            <td class="pe-3 text-end">
              <a href="<?= $APP ?>/clientes/<?= $c['id'] ?>"
                 class="btn btn-sm btn-outline-secondary py-0 px-2" title="Ver ficha">
                <i class="bi bi-eye"></i>
              </a>
              <a href="<?= $APP ?>/clientes/<?= $c['id'] ?>/editar"
                 class="btn btn-sm btn-outline-secondary py-0 px-2 ms-1" title="Editar">
                <i class="bi bi-pencil"></i>
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <?php include __DIR__ . '/../partials/paginacao.php'; ?>
    <?php endif; ?>
  </div>
</div>
