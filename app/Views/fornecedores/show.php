<?php
$APP = $_ENV['APP_URL'] ?? '';
$f   = $fornecedor;
$statusLabels = [
    'rascunho'              => ['label'=>'Rascunho',      'badge'=>'secondary'],
    'enviada'               => ['label'=>'Enviada',        'badge'=>'primary'],
    'parcialmente_recebida' => ['label'=>'Parc. Recebida', 'badge'=>'warning'],
    'recebida'              => ['label'=>'Recebida',       'badge'=>'success'],
    'cancelada'             => ['label'=>'Cancelada',      'badge'=>'danger'],
];
?>

<!-- Cabeçalho -->
<div class="d-flex align-items-start justify-content-between mb-4 flex-wrap gap-2">
  <div>
    <h1 class="h4 fw-bold mb-1" style="color:var(--kf-primary)">
      <i class="bi bi-building me-2"></i><?= htmlspecialchars($f['nome']) ?>
    </h1>
    <span class="badge bg-<?= $f['ativo'] ? 'success' : 'secondary' ?>">
      <?= $f['ativo'] ? 'Activo' : 'Inactivo' ?>
    </span>
  </div>
  <div class="d-flex gap-2 flex-wrap">
    <a href="<?= $APP ?>/fornecedores/<?= $f['id'] ?>/editar"
       class="btn btn-sm btn-outline-secondary">
      <i class="bi bi-pencil me-1"></i>Editar
    </a>
    <form action="<?= $APP ?>/fornecedores/<?= $f['id'] ?>/toggle" method="POST" class="d-inline">
      <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
      <button type="submit" class="btn btn-sm btn-outline-<?= $f['ativo'] ? 'warning' : 'success' ?>">
        <i class="bi bi-toggle-<?= $f['ativo'] ? 'on' : 'off' ?> me-1"></i>
        <?= $f['ativo'] ? 'Desactivar' : 'Activar' ?>
      </button>
    </form>
    <a href="<?= $APP ?>/compras/nova" class="btn btn-sm" style="background:var(--kf-primary);color:#fff;border:none">
      <i class="bi bi-truck me-1"></i>Nova Compra
    </a>
    <a href="<?= $APP ?>/fornecedores" class="btn btn-sm btn-outline-secondary">
      <i class="bi bi-arrow-left me-1"></i>Voltar
    </a>
  </div>
</div>

<div class="row g-4">

  <!-- Dados do fornecedor -->
  <div class="col-12 col-md-4">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-white py-3">
        <h6 class="fw-bold mb-0"><i class="bi bi-info-circle me-2 text-success"></i>Dados do Fornecedor</h6>
      </div>
      <div class="card-body py-3">
        <?php
        $campos = [
          ['NUIT',      $f['nuit']     ?? null, 'bi-card-text'],
          ['Telefone',  $f['telefone'] ?? null, 'bi-telephone'],
          ['Email',     $f['email']    ?? null, 'bi-envelope'],
          ['Endereço',  $f['endereco'] ?? null, 'bi-geo-alt'],
          ['Cidade',    $f['cidade']   ?? null, 'bi-building'],
          ['País',      $f['pais']     ?? null, 'bi-globe'],
        ];
        foreach ($campos as [$lbl, $val, $icon]):
          if (!$val) continue;
        ?>
        <div class="d-flex gap-2 mb-2 align-items-start" style="font-size:13px">
          <i class="bi <?= $icon ?> text-muted mt-1" style="font-size:12px;flex-shrink:0"></i>
          <div>
            <div class="text-muted" style="font-size:11px"><?= $lbl ?></div>
            <div class="fw-semibold"><?= htmlspecialchars($val) ?></div>
          </div>
        </div>
        <?php endforeach; ?>

        <div class="border-top pt-2 mt-2">
          <div class="text-muted" style="font-size:11px">Registado em</div>
          <div class="fw-semibold" style="font-size:13px"><?= date('d/m/Y', strtotime($f['criado_em'])) ?></div>
        </div>
      </div>
    </div>
  </div>

  <!-- Histórico de compras -->
  <div class="col-12 col-md-8">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
        <h6 class="fw-bold mb-0"><i class="bi bi-clock-history me-2 text-success"></i>Últimas Compras</h6>
        <a href="<?= $APP ?>/compras?q=<?= urlencode($f['nome']) ?>" class="btn btn-sm btn-outline-secondary">
          Ver todas
        </a>
      </div>
      <div class="card-body p-0">
        <?php if (empty($compras)): ?>
        <div class="text-center py-4">
          <i class="bi bi-truck text-muted" style="font-size:2.5rem"></i>
          <p class="text-muted mt-2 mb-2 small">Nenhuma compra registada ainda.</p>
          <a href="<?= $APP ?>/compras/nova" class="btn btn-sm" style="background:var(--kf-primary);color:#fff;border:none">
            <i class="bi bi-plus-lg me-1"></i>Criar compra
          </a>
        </div>
        <?php else: ?>
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th class="ps-3" style="font-size:12px">Número</th>
                <th class="text-center" style="font-size:12px;width:110px">Data</th>
                <th class="text-center" style="font-size:12px;width:130px">Estado</th>
                <th class="text-end pe-3" style="font-size:12px;width:130px">Total</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($compras as $c):
                $s = $statusLabels[$c['status']] ?? ['label'=>$c['status'],'badge'=>'secondary'];
              ?>
              <tr>
                <td class="ps-3">
                  <a href="<?= $APP ?>/compras/<?= $c['id'] ?>"
                     class="fw-semibold text-decoration-none" style="color:var(--kf-primary); font-size:13px">
                    <?= htmlspecialchars($c['numero_compra']) ?>
                  </a>
                </td>
                <td class="text-center" style="font-size:13px">
                  <?= date('d/m/Y', strtotime($c['data_pedido'])) ?>
                </td>
                <td class="text-center">
                  <span class="badge bg-<?= $s['badge'] ?>"><?= $s['label'] ?></span>
                </td>
                <td class="text-end pe-3 fw-semibold text-success" style="font-size:13px">
                  <?= number_format($c['total'], 2, ',', '.') ?> MZN
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

</div>
