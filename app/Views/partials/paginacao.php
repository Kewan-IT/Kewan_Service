<?php
/**
 * Componente de paginação reutilizável — KewanFarma
 *
 * Variáveis esperadas (passadas via extract ou directamente no scope):
 *   $paginacao  — array com keys: data, total, per_page, current_page, last_page
 *   $queryBase  — string com os parâmetros GET actuais SEM page e SEM per_page
 *                 ex: "q=aspirina&status=activo"
 *
 * Uso nas views:
 *   <?php include __DIR__ . '/../partials/paginacao.php'; ?>
 */

$total        = $paginacao['total']        ?? 0;
$perPage      = $paginacao['per_page']     ?? 10;
$currentPage  = $paginacao['current_page'] ?? 1;
$lastPage     = $paginacao['last_page']    ?? 1;

// Reconstruir query string sem page e per_page
$params = $_GET;
unset($params['page'], $params['per_page']);
$qs = http_build_query($params);
$sep = $qs ? '&' : '';
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 px-3 py-3 border-top">

  <!-- Info + selector de itens por página -->
  <div class="d-flex align-items-center gap-3 flex-wrap">
    <small class="text-muted">
      <?php
        $inicio = $total === 0 ? 0 : ($currentPage - 1) * $perPage + 1;
        $fim    = min($currentPage * $perPage, $total);
        echo "A mostrar <strong>" . $inicio . "–" . $fim . "</strong> de <strong>" . $total . "</strong>";
      ?>
    </small>

    <!-- Selector de registos por página -->
    <form method="GET" class="d-flex align-items-center gap-1 mb-0">
      <?php foreach ($params as $k => $v): ?>
        <input type="hidden" name="<?= htmlspecialchars($k) ?>" value="<?= htmlspecialchars($v) ?>">
      <?php endforeach; ?>
      <input type="hidden" name="page" value="1">
      <label class="text-muted small mb-0 me-1">Mostrar:</label>
      <select name="per_page" class="form-select form-select-sm" style="width:auto"
              onchange="this.form.submit()">
        <?php foreach ([10, 20, 50] as $opt): ?>
          <option value="<?= $opt ?>" <?= (int)$perPage === $opt ? 'selected' : '' ?>>
            <?= $opt ?> por página
          </option>
        <?php endforeach; ?>
      </select>
    </form>
  </div>

  <!-- Navegação de páginas -->
  <?php if ($lastPage > 1): ?>
  <nav aria-label="Paginação">
    <ul class="pagination pagination-sm mb-0">

      <!-- Anterior -->
      <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
        <a class="page-link" href="?<?= $qs . $sep ?>page=<?= $currentPage - 1 ?>&per_page=<?= $perPage ?>">
          <i class="bi bi-chevron-left" style="font-size:.75rem"></i>
        </a>
      </li>

      <?php
      // Janela de páginas: mostra até 5 links centrados na página actual
      $window = 2;
      $start  = max(1, $currentPage - $window);
      $end    = min($lastPage, $currentPage + $window);

      if ($start > 1): ?>
        <li class="page-item">
          <a class="page-link" href="?<?= $qs . $sep ?>page=1&per_page=<?= $perPage ?>">1</a>
        </li>
        <?php if ($start > 2): ?>
          <li class="page-item disabled"><span class="page-link">…</span></li>
        <?php endif;
      endif;

      for ($i = $start; $i <= $end; $i++): ?>
        <li class="page-item <?= $i === $currentPage ? 'active' : '' ?>">
          <a class="page-link" href="?<?= $qs . $sep ?>page=<?= $i ?>&per_page=<?= $perPage ?>"><?= $i ?></a>
        </li>
      <?php endfor;

      if ($end < $lastPage): ?>
        <?php if ($end < $lastPage - 1): ?>
          <li class="page-item disabled"><span class="page-link">…</span></li>
        <?php endif; ?>
        <li class="page-item">
          <a class="page-link" href="?<?= $qs . $sep ?>page=<?= $lastPage ?>&per_page=<?= $perPage ?>"><?= $lastPage ?></a>
        </li>
      <?php endif; ?>

      <!-- Próximo -->
      <li class="page-item <?= $currentPage >= $lastPage ? 'disabled' : '' ?>">
        <a class="page-link" href="?<?= $qs . $sep ?>page=<?= $currentPage + 1 ?>&per_page=<?= $perPage ?>">
          <i class="bi bi-chevron-right" style="font-size:.75rem"></i>
        </a>
      </li>

    </ul>
  </nav>
  <?php endif; ?>

</div>
