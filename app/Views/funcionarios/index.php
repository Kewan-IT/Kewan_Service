<?php
// app/Views/funcionarios/index.php
$APP = $_ENV['APP_URL'] ?? '';
?>

<!-- ── Cabeçalho da página ── -->
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
  <div>
    <h1 class="h4 fw-bold mb-0" style="color:var(--kf-primary)">
      <i class="bi bi-person-badge me-2"></i>Funcionários
    </h1>
    <p class="text-muted small mb-0 mt-1">Gestão da equipa da farmácia</p>
  </div>
  <?php if (($_SESSION['perfil'] ?? '') === 'admin'): ?>
  <a href="<?= $APP ?>/funcionarios/novo" class="btn btn-sm" style="background:var(--kf-primary);color:#fff;border:none">
    <i class="bi bi-plus-lg me-1"></i> Novo Funcionário
  </a>
  <?php endif; ?>
</div>

<!-- ── Cards de estatísticas ── -->
<div class="row g-3 mb-4">
  <?php
  $statsCards = [
    ['label'=>'Total','val'=>$stats['total'],'icon'=>'bi-people','color'=>'#1a7f5a'],
    ['label'=>'Activos','val'=>$stats['activos'],'icon'=>'bi-person-check','color'=>'#198754'],
    ['label'=>'Inactivos','val'=>$stats['inactivos'],'icon'=>'bi-person-x','color'=>'#dc3545'],
    ['label'=>'Com Acesso','val'=>$stats['com_acesso'],'icon'=>'bi-shield-lock','color'=>'#0d6efd'],
  ];
  foreach ($statsCards as $card): ?>
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body d-flex align-items-center gap-3 p-3">
        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
             style="width:42px;height:42px;background:<?= $card['color'] ?>18">
          <i class="bi <?= $card['icon'] ?> fs-5" style="color:<?= $card['color'] ?>"></i>
        </div>
        <div>
          <div class="fw-bold fs-5 lh-1"><?= $card['val'] ?></div>
          <div class="text-muted small"><?= $card['label'] ?></div>
        </div>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- ── Filtros de pesquisa ── -->
<div class="card border-0 shadow-sm mb-4">
  <div class="card-body p-3">
    <form method="GET" action="<?= $APP ?>/funcionarios" class="row g-2 align-items-end">
      <div class="col-12 col-md-4">
        <label class="form-label small mb-1 text-muted">Pesquisar</label>
        <div class="input-group input-group-sm">
          <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
          <input type="text" name="q" class="form-control form-control-sm border-start-0"
                 placeholder="Nome, nº funcionário, BI, telefone..."
                 value="<?= htmlspecialchars($pesquisa) ?>">
        </div>
      </div>
      <div class="col-6 col-md-3">
        <label class="form-label small mb-1 text-muted">Estado</label>
        <select name="status" class="form-select form-select-sm">
          <option value="">Todos os estados</option>
          <option value="activo"    <?= $status === 'activo'    ? 'selected' : '' ?>>Activo</option>
          <option value="inactivo"  <?= $status === 'inactivo'  ? 'selected' : '' ?>>Inactivo</option>
          <option value="suspenso"  <?= $status === 'suspenso'  ? 'selected' : '' ?>>Suspenso</option>
          <option value="desligado" <?= $status === 'desligado' ? 'selected' : '' ?>>Desligado</option>
        </select>
      </div>
      <div class="col-6 col-md-3">
        <label class="form-label small mb-1 text-muted">Cargo</label>
        <select name="cargo" class="form-select form-select-sm">
          <option value="0">Todos os cargos</option>
          <?php foreach ($cargos as $c): ?>
          <option value="<?= $c['id'] ?>" <?= $cargo_id === (int)$c['id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($c['nome']) ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-12 col-md-2 d-flex gap-2">
        <button type="submit" class="btn btn-sm flex-fill" style="background:var(--kf-primary);color:#fff;border:none">
          <i class="bi bi-funnel me-1"></i>Filtrar
        </button>
        <?php if ($pesquisa || $status || $cargo_id): ?>
        <a href="<?= $APP ?>/funcionarios" class="btn btn-sm btn-outline-secondary">
          <i class="bi bi-x"></i>
        </a>
        <?php endif; ?>
      </div>
    </form>
  </div>
</div>

<!-- ── Tabela ── -->
<div class="card border-0 shadow-sm">
  <div class="card-body p-0">
    <?php if (empty($paginacao['data'])): ?>
    <div class="text-center py-5 text-muted">
      <i class="bi bi-person-x fs-1 d-block mb-2"></i>
      <?= $pesquisa || $status || $cargo_id ? 'Nenhum funcionário encontrado com os filtros seleccionados.' : 'Ainda não há funcionários registados.' ?>
    </div>
    <?php else: ?>
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0" style="font-size:.875rem">
        <thead style="background:var(--kf-primary-light)">
          <tr>
            <th class="ps-3 py-2 fw-semibold" style="color:var(--kf-primary);width:40px">#</th>
            <th class="py-2 fw-semibold" style="color:var(--kf-primary)">Funcionário</th>
            <th class="py-2 fw-semibold d-none d-md-table-cell" style="color:var(--kf-primary)">Cargo</th>
            <th class="py-2 fw-semibold d-none d-lg-table-cell" style="color:var(--kf-primary)">Contacto</th>
            <th class="py-2 fw-semibold" style="color:var(--kf-primary)">Estado</th>
            <th class="py-2 fw-semibold d-none d-md-table-cell" style="color:var(--kf-primary)">Acesso</th>
            <th class="pe-3 py-2"></th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($paginacao['data'] as $f): ?>
          <tr>
            <td class="ps-3 text-muted small"><?= htmlspecialchars($f['numero_funcionario']) ?></td>
            <td>
              <div class="d-flex align-items-center gap-2">
                <?php if ($f['foto_url']): ?>
                  <img src="<?= $APP ?>/uploads/<?= htmlspecialchars($f['foto_url']) ?>"
                       class="rounded-circle object-fit-cover" width="34" height="34"
                       alt="<?= htmlspecialchars($f['nome_completo']) ?>">
                <?php else: ?>
                  <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold flex-shrink-0"
                       style="width:34px;height:34px;background:var(--kf-primary);font-size:13px">
                    <?= mb_strtoupper(mb_substr($f['nome_completo'], 0, 1)) ?>
                  </div>
                <?php endif; ?>
                <div>
                  <div class="fw-semibold lh-1"><?= htmlspecialchars($f['nome_completo']) ?></div>
                  <div class="text-muted small"><?= htmlspecialchars($f['numero_funcionario']) ?></div>
                </div>
              </div>
            </td>
            <td class="d-none d-md-table-cell text-muted"><?= htmlspecialchars($f['cargo']) ?></td>
            <td class="d-none d-lg-table-cell">
              <div><?= htmlspecialchars($f['telefone_principal']) ?></div>
              <?php if ($f['email_pessoal']): ?>
              <div class="text-muted small"><?= htmlspecialchars($f['email_pessoal']) ?></div>
              <?php endif; ?>
            </td>
            <td>
              <?php
              $badgeMap = [
                'activo'    => ['success', 'Activo'],
                'inactivo'  => ['secondary', 'Inactivo'],
                'suspenso'  => ['warning', 'Suspenso'],
                'desligado' => ['danger', 'Desligado'],
              ];
              [$bc, $bl] = $badgeMap[$f['status']] ?? ['secondary', $f['status']];
              ?>
              <span class="badge bg-<?= $bc ?>-subtle text-<?= $bc ?> border border-<?= $bc ?>-subtle rounded-pill px-2">
                <?= $bl ?>
              </span>
            </td>
            <td class="d-none d-md-table-cell">
              <?php if ($f['usuario_id']): ?>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2">
                  <i class="bi bi-shield-check me-1"></i><?= ucfirst($f['perfil'] ?? '') ?>
                </span>
              <?php else: ?>
                <span class="text-muted small"><i class="bi bi-shield-x me-1"></i>Sem acesso</span>
              <?php endif; ?>
            </td>
            <td class="pe-3 text-end">
              <a href="<?= $APP ?>/funcionarios/<?= $f['id'] ?>"
                 class="btn btn-sm btn-outline-secondary py-0 px-2" title="Ver ficha">
                <i class="bi bi-eye"></i>
              </a>
              <?php if (($_SESSION['perfil'] ?? '') === 'admin'): ?>
              <a href="<?= $APP ?>/funcionarios/<?= $f['id'] ?>/editar"
                 class="btn btn-sm btn-outline-secondary py-0 px-2 ms-1" title="Editar">
                <i class="bi bi-pencil"></i>
              </a>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <!-- Paginação -->
    <?php if ($paginacao['last_page'] > 1): ?>
    <div class="d-flex align-items-center justify-content-between px-3 py-2 border-top" style="font-size:.8rem">
      <div class="text-muted">
        Mostrando <?= count($paginacao['data']) ?> de <?= $paginacao['total'] ?> funcionários
      </div>
      <nav>
        <ul class="pagination pagination-sm mb-0">
          <?php for ($p = 1; $p <= $paginacao['last_page']; $p++): ?>
          <li class="page-item <?= $p === $paginacao['current_page'] ? 'active' : '' ?>">
            <a class="page-link"
               href="?q=<?= urlencode($pesquisa) ?>&status=<?= urlencode($status) ?>&cargo=<?= $cargo_id ?>&page=<?= $p ?>">
              <?= $p ?>
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
