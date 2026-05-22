<?php
// app/Views/produtos/index.php
$APP    = $_ENV['APP_URL'] ?? '';
$isEdit = in_array($_SESSION['perfil'] ?? '', ['admin', 'farmaceutico']);
?>

<style>
.produto-card { transition:box-shadow .15s,transform .15s; border:1px solid #e8f0ec; border-radius:10px; overflow:hidden; background:#fff; }
.produto-card:hover { box-shadow:0 4px 16px rgba(26,127,90,.12); transform:translateY(-2px); }
.produto-img { width:100%; height:130px; object-fit:cover; background:var(--kf-primary-light); }
.produto-img-placeholder { width:100%; height:130px; background:var(--kf-primary-light); display:flex; align-items:center; justify-content:center; font-size:2.5rem; color:var(--kf-primary); }
.badge-receita { background:#fff3cd; color:#856404; border:1px solid #ffc107; }
.badge-controlado { background:#f8d7da; color:#842029; border:1px solid #dc3545; }
.stock-ok { color:#198754; }
.stock-baixo { color:#dc3545; }
.stock-zero { color:#6c757d; }
</style>

<!-- Cabeçalho -->
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
  <div>
    <h1 class="h4 fw-bold mb-0" style="color:var(--kf-primary)">
      <i class="bi bi-boxes me-2"></i>Produtos
    </h1>
    <p class="text-muted small mb-0 mt-1">Catálogo farmacêutico</p>
  </div>
  <?php if ($isEdit): ?>
  <a href="<?= $APP ?>/produtos/novo" class="btn btn-sm" style="background:var(--kf-primary);color:#fff;border:none">
    <i class="bi bi-plus-lg me-1"></i>Novo Produto
  </a>
  <?php endif; ?>
</div>

<!-- Stats -->
<div class="row g-3 mb-4">
  <?php
  $cards = [
    ['Total', $stats['total'],       'bi-boxes',          '#1a7f5a', ''],
    ['Stock Baixo', $stats['stock_baixo'], 'bi-exclamation-triangle', '#dc3545', 'stock_baixo'],
    ['Sem Stock',  $stats['sem_stock'],    'bi-x-circle',    '#6c757d', 'sem_stock'],
    ['A Vencer',   $stats['lotes_a_vencer'],'bi-calendar-x', '#fd7e14', ''],
    ['c/ Receita', $stats['com_receita'],  'bi-file-medical','#0d6efd', 'receita'],
  ];
  foreach ($cards as [$lbl, $val, $icon, $cor, $f]):
  $href = $f ? "$APP/produtos?filtro=$f" : "$APP/produtos";
  ?>
  <div class="col-6 col-md-4 col-lg">
    <a href="<?= $href ?>" class="text-decoration-none">
      <div class="card border-0 shadow-sm h-100 <?= $filtro === $f && $f ? 'border border-2' : '' ?>"
           style="<?= $filtro === $f && $f ? "border-color:$cor!important" : '' ?>">
        <div class="card-body d-flex align-items-center gap-3 p-3">
          <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
               style="width:40px;height:40px;background:<?= $cor ?>18">
            <i class="bi <?= $icon ?> fs-5" style="color:<?= $cor ?>"></i>
          </div>
          <div>
            <div class="fw-bold fs-5 lh-1" style="color:<?= $cor ?>"><?= $val ?></div>
            <div class="text-muted small"><?= $lbl ?></div>
          </div>
        </div>
      </div>
    </a>
  </div>
  <?php endforeach; ?>
</div>

<!-- Filtros -->
<div class="card border-0 shadow-sm mb-4">
  <div class="card-body p-3">
    <form method="GET" action="<?= $APP ?>/produtos" class="row g-2 align-items-end">
      <div class="col-12 col-md-4">
        <label class="form-label small mb-1 text-muted">Pesquisar</label>
        <div class="input-group input-group-sm">
          <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
          <input type="text" name="q" class="form-control form-control-sm border-start-0"
                 placeholder="Nome, código de barras, princípio activo..."
                 value="<?= htmlspecialchars($pesquisa) ?>">
        </div>
      </div>
      <div class="col-6 col-md-3">
        <label class="form-label small mb-1 text-muted">Categoria</label>
        <select name="cat" class="form-select form-select-sm">
          <option value="0">Todas</option>
          <?php foreach ($categorias as $c): ?>
          <option value="<?= $c['id'] ?>" <?= $categoria === (int)$c['id'] ? 'selected' : '' ?>>
            <?= $c['pai_nome'] ? '└ ' : '' ?><?= htmlspecialchars($c['nome']) ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-6 col-md-3">
        <label class="form-label small mb-1 text-muted">Filtro</label>
        <select name="filtro" class="form-select form-select-sm">
          <option value="">Todos os produtos</option>
          <option value="stock_baixo" <?= $filtro === 'stock_baixo' ? 'selected':'' ?>>Stock baixo</option>
          <option value="sem_stock"   <?= $filtro === 'sem_stock'   ? 'selected':'' ?>>Sem stock</option>
          <option value="receita"     <?= $filtro === 'receita'     ? 'selected':'' ?>>Requer receita</option>
          <option value="controlado"  <?= $filtro === 'controlado'  ? 'selected':'' ?>>Controlados</option>
        </select>
      </div>
      <div class="col-12 col-md-2 d-flex gap-2">
        <button type="submit" class="btn btn-sm flex-fill" style="background:var(--kf-primary);color:#fff;border:none">
          <i class="bi bi-funnel me-1"></i>Filtrar
        </button>
        <?php if ($pesquisa || $categoria || $filtro): ?>
        <a href="<?= $APP ?>/produtos" class="btn btn-sm btn-outline-secondary"><i class="bi bi-x"></i></a>
        <?php endif; ?>
      </div>
    </form>
  </div>
</div>

<!-- Grelha de produtos -->
<?php if (empty($paginacao['data'])): ?>
<div class="text-center py-5 text-muted">
  <i class="bi bi-box-seam fs-1 d-block mb-2"></i>
  <?= $pesquisa || $categoria || $filtro ? 'Nenhum produto encontrado com os filtros seleccionados.' : 'Ainda não há produtos registados.' ?>
  <?php if ($isEdit): ?>
  <div class="mt-3">
    <a href="<?= $APP ?>/produtos/novo" class="btn btn-sm" style="background:var(--kf-primary);color:#fff;border:none">
      <i class="bi bi-plus-lg me-1"></i>Registar primeiro produto
    </a>
  </div>
  <?php endif; ?>
</div>
<?php else: ?>

<div class="row g-3 mb-3">
  <?php foreach ($paginacao['data'] as $p): ?>
  <div class="col-6 col-md-4 col-lg-3">
    <div class="produto-card h-100 d-flex flex-column">
      <!-- Imagem -->
      <a href="<?= $APP ?>/produtos/<?= $p['id'] ?>">
        <?php if ($p['imagem_url']): ?>
        <img src="<?= $APP ?>/storage/uploads/<?= htmlspecialchars($p['imagem_url']) ?>"
             class="produto-img" alt="<?= htmlspecialchars($p['nome']) ?>">
        <?php else: ?>
        <div class="produto-img-placeholder">
          <i class="bi bi-capsule"></i>
        </div>
        <?php endif; ?>
      </a>

      <div class="p-2 d-flex flex-column flex-fill">
        <!-- Badges -->
        <div class="d-flex gap-1 flex-wrap mb-1">
          <?php if ($p['requer_receita']): ?>
          <span class="badge badge-receita" style="font-size:.65rem"><i class="bi bi-file-medical me-1"></i>Receita</span>
          <?php endif; ?>
          <?php if ($p['controlado']): ?>
          <span class="badge badge-controlado" style="font-size:.65rem"><i class="bi bi-shield-lock me-1"></i>Controlado</span>
          <?php endif; ?>
        </div>

        <!-- Nome -->
        <a href="<?= $APP ?>/produtos/<?= $p['id'] ?>" class="text-decoration-none">
          <div class="fw-semibold lh-sm mb-1" style="font-size:.85rem;color:#1a2e27">
            <?= htmlspecialchars($p['nome']) ?>
          </div>
        </a>
        <?php if ($p['principio_ativo']): ?>
        <div class="text-muted mb-1" style="font-size:.73rem"><?= htmlspecialchars($p['principio_ativo']) ?></div>
        <?php endif; ?>
        <div class="text-muted mb-2" style="font-size:.73rem"><?= htmlspecialchars($p['categoria_nome']) ?></div>

        <div class="mt-auto">
          <!-- Preço -->
          <div class="fw-bold mb-1" style="color:var(--kf-primary);font-size:.95rem">
            MT <?= number_format((float)$p['preco_venda'], 2, ',', '.') ?>
          </div>

          <!-- Stock -->
          <?php
          if ($p['estoque_actual'] == 0)          { $sc='stock-zero'; $si='bi-x-circle'; }
          elseif ($p['stock_baixo'])              { $sc='stock-baixo'; $si='bi-exclamation-circle'; }
          else                                    { $sc='stock-ok'; $si='bi-check-circle'; }
          ?>
          <div class="d-flex align-items-center justify-content-between">
            <span class="<?= $sc ?>" style="font-size:.78rem">
              <i class="bi <?= $si ?> me-1"></i><?= $p['estoque_actual'] ?> <?= htmlspecialchars($p['unidade_medida']) ?>
            </span>
            <?php if ($isEdit): ?>
            <a href="<?= $APP ?>/produtos/<?= $p['id'] ?>/editar"
               class="btn btn-outline-secondary py-0 px-1" style="font-size:.7rem" title="Editar">
              <i class="bi bi-pencil"></i>
            </a>
            <?php endif; ?>
          </div>

          <!-- Alerta validade -->
          <?php if ($p['proxima_validade']): ?>
          <?php $dias = (int) ((strtotime($p['proxima_validade']) - time()) / 86400); ?>
          <?php if ($dias <= 90): ?>
          <div class="mt-1 <?= $dias <= 30 ? 'text-danger' : 'text-warning' ?>" style="font-size:.7rem">
            <i class="bi bi-calendar-x me-1"></i>Vence em <?= $dias ?> dias
          </div>
          <?php endif; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- Paginação -->
<?php if ($paginacao['last_page'] > 1): ?>
<div class="d-flex align-items-center justify-content-between mt-2" style="font-size:.8rem">
  <div class="text-muted">
    <?= count($paginacao['data']) ?> de <?= $paginacao['total'] ?> produtos
  </div>
  <nav>
    <ul class="pagination pagination-sm mb-0">
      <?php for ($pg = 1; $pg <= $paginacao['last_page']; $pg++): ?>
      <li class="page-item <?= $pg === $paginacao['current_page'] ? 'active' : '' ?>">
        <a class="page-link"
           href="?q=<?= urlencode($pesquisa) ?>&cat=<?= $categoria ?>&filtro=<?= urlencode($filtro) ?>&page=<?= $pg ?>">
          <?= $pg ?>
        </a>
      </li>
      <?php endfor; ?>
    </ul>
  </nav>
</div>
<?php endif; ?>
<?php endif; ?>
