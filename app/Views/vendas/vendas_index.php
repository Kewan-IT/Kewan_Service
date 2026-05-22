<?php
$appUrl = $_ENV['APP_URL'] ?? '';
$formas = [
  'dinheiro'=>'Dinheiro','mpesa'=>'M-Pesa','emola'=>'e-Mola',
  'cartao_debito'=>'Débito','cartao_credito'=>'Crédito','transferencia'=>'Transferência',
];
?>

<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
  <div>
    <h1 class="page-title">Vendas</h1>
    <p class="page-subtitle">Histórico e gestão de vendas</p>
  </div>
  <a href="<?= $appUrl ?>/vendas/nova" class="btn btn-success d-flex align-items-center gap-2">
    <i class="bi bi-cart-plus-fill"></i> Nova Venda
  </a>
</div>

<!-- Resumo do dia -->
<div class="row g-3 mb-4">
  <?php
  $cards = [
    ['label'=>'Vendas Hoje',    'valor'=>$resumo['total_vendas'],                                         'icon'=>'bi-receipt',       'cor'=>'primary', 'sufixo'=>''],
    ['label'=>'Total Hoje',     'valor'=>number_format($resumo['valor_total'],2,',','.'),                  'icon'=>'bi-cash-stack',    'cor'=>'success', 'sufixo'=>' MZN'],
    ['label'=>'Ticket Médio',   'valor'=>number_format($resumo['ticket_medio'],2,',','.'),                 'icon'=>'bi-graph-up',      'cor'=>'info',    'sufixo'=>' MZN'],
    ['label'=>'Canceladas',     'valor'=>$resumo['canceladas'],                                            'icon'=>'bi-x-circle',      'cor'=>'danger',  'sufixo'=>''],
  ];
  foreach ($cards as $c):
  ?>
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
      <div class="col-md-3">
        <label class="form-label small fw-semibold mb-1">Pesquisar</label>
        <input type="text" name="busca" value="<?= htmlspecialchars($filtros['busca']) ?>"
               class="form-control" placeholder="Nº venda ou cliente...">
      </div>
      <div class="col-md-2">
        <label class="form-label small fw-semibold mb-1">De</label>
        <input type="date" name="data_inicio" value="<?= $filtros['data_inicio'] ?>" class="form-control">
      </div>
      <div class="col-md-2">
        <label class="form-label small fw-semibold mb-1">Até</label>
        <input type="date" name="data_fim" value="<?= $filtros['data_fim'] ?>" class="form-control">
      </div>
      <div class="col-md-2">
        <label class="form-label small fw-semibold mb-1">Estado</label>
        <select name="status" class="form-select">
          <option value="">Todos</option>
          <option value="concluida"  <?= $filtros['status']==='concluida'?'selected':'' ?>>Concluída</option>
          <option value="cancelada"  <?= $filtros['status']==='cancelada'?'selected':'' ?>>Cancelada</option>
          <option value="devolvida"  <?= $filtros['status']==='devolvida'?'selected':'' ?>>Devolvida</option>
        </select>
      </div>
      <div class="col-md-3 d-flex gap-2">
        <button type="submit" class="btn btn-success flex-fill">
          <i class="bi bi-funnel me-1"></i>Filtrar
        </button>
        <a href="<?= $appUrl ?>/vendas" class="btn btn-outline-secondary">
          <i class="bi bi-x-lg"></i>
        </a>
      </div>
    </form>
  </div>
</div>

<!-- Lista de vendas -->
<div class="card border-0 shadow-sm">
  <div class="card-body p-0">
    <?php if (empty($vendas)): ?>
    <div class="text-center py-5">
      <i class="bi bi-receipt text-muted" style="font-size:3rem"></i>
      <p class="text-muted mt-3">Nenhuma venda encontrada.</p>
      <a href="<?= $appUrl ?>/vendas/nova" class="btn btn-success btn-sm">
        <i class="bi bi-cart-plus me-1"></i>Registar primeira venda
      </a>
    </div>
    <?php else: ?>
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th class="ps-4">Nº Venda</th>
            <th>Cliente</th>
            <th class="d-none d-md-table-cell">Pagamento</th>
            <th class="d-none d-lg-table-cell text-center">Itens</th>
            <th class="text-end">Total</th>
            <th class="text-center">Estado</th>
            <th class="d-none d-md-table-cell">Data</th>
            <th class="text-end pe-4">Acções</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($vendas as $v): ?>
          <tr>
            <td class="ps-4">
              <span class="fw-semibold" style="font-size:13px;font-family:monospace">
                <?= htmlspecialchars($v['numero_venda']) ?>
              </span>
            </td>
            <td style="font-size:13px">
              <?= htmlspecialchars($v['cliente_nome'] ?? 'Balcão') ?>
              <div class="text-muted" style="font-size:11px"><?= htmlspecialchars($v['usuario_nome']) ?></div>
            </td>
            <td class="d-none d-md-table-cell">
              <?php
              $icones = ['dinheiro'=>'bi-cash','mpesa'=>'bi-phone','emola'=>'bi-phone-fill',
                         'cartao_debito'=>'bi-credit-card','cartao_credito'=>'bi-credit-card-2-front',
                         'transferencia'=>'bi-bank'];
              ?>
              <span style="font-size:12px">
                <i class="bi <?= $icones[$v['forma_pagamento']] ?? 'bi-cash' ?> me-1 text-success"></i>
                <?= $formas[$v['forma_pagamento']] ?? $v['forma_pagamento'] ?>
              </span>
            </td>
            <td class="d-none d-lg-table-cell text-center">
              <span class="badge bg-secondary bg-opacity-15 text-secondary"><?= $v['total_itens'] ?></span>
            </td>
            <td class="text-end fw-bold text-success" style="font-size:14px">
              <?= number_format($v['total'], 2, ',', '.') ?>
            </td>
            <td class="text-center">
              <?php
              [$cor,$label] = match($v['status']) {
                'concluida' => ['success','Concluída'],
                'cancelada' => ['danger', 'Cancelada'],
                'devolvida' => ['warning','Devolvida'],
                default     => ['secondary',$v['status']],
              };
              ?>
              <span class="badge bg-<?= $cor ?> bg-opacity-15 text-<?= $cor ?> border border-<?= $cor ?>-subtle"
                    style="font-size:11px"><?= $label ?></span>
            </td>
            <td class="d-none d-md-table-cell text-muted" style="font-size:12px">
              <?= date('d/m/Y H:i', strtotime($v['criado_em'])) ?>
            </td>
            <td class="text-end pe-4">
              <a href="<?= $appUrl ?>/vendas/<?= $v['id'] ?>"
                 class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-eye"></i>
              </a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <div class="px-4 py-2 border-top text-muted" style="font-size:12px">
      <?= count($vendas) ?> venda(s) encontrada(s)
    </div>
    <?php endif; ?>
  </div>
</div>
