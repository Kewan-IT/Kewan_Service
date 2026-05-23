<?php
$APP    = $_ENV['APP_URL'] ?? '';
$formas = ['dinheiro'=>'Dinheiro','mpesa'=>'M-Pesa','emola'=>'e-Mola',
           'cartao_debito'=>'Débito','cartao_credito'=>'Crédito','transferencia'=>'Transferência'];
?>
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
  <div>
    <h1 class="h4 fw-bold mb-0" style="color:var(--kf-primary)"><i class="bi bi-receipt me-2"></i>Vendas</h1>
    <p class="text-muted small mb-0">Histórico e gestão de vendas</p>
  </div>
  <a href="<?= $APP ?>/vendas/nova" class="btn btn-success"><i class="bi bi-cart-plus-fill me-1"></i>Nova Venda</a>
</div>
<div class="row g-3 mb-4">
  <?php foreach ([
    ['Vendas Hoje','bi-receipt',$resumo['total_vendas']??0],
    ['Total Hoje','bi-cash-stack','MT '.number_format($resumo['valor_total']??0,2,',','.')],
    ['Ticket Médio','bi-graph-up','MT '.number_format($resumo['ticket_medio']??0,2,',','.')],
    ['Descontos','bi-tag','MT '.number_format($resumo['descontos']??0,2,',','.')],
  ] as [$lbl,$icon,$val]): ?>
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body d-flex align-items-center gap-3 p-3">
        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
             style="width:42px;height:42px;background:var(--kf-primary-light)">
          <i class="bi <?= $icon ?> fs-5" style="color:var(--kf-primary)"></i>
        </div>
        <div><div class="fw-bold fs-5 lh-1"><?= $val ?></div><div class="text-muted small"><?= $lbl ?></div></div>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<div class="card border-0 shadow-sm mb-4">
  <div class="card-body p-3">
    <form method="GET" action="<?= $APP ?>/vendas" class="row g-2 align-items-end">
      <div class="col-12 col-md-3">
        <input type="text" name="busca" class="form-control form-control-sm" placeholder="Nº venda ou cliente..." value="<?= htmlspecialchars($filtros['busca']) ?>">
      </div>
      <div class="col-6 col-md-2">
        <select name="status" class="form-select form-select-sm">
          <option value="">Todos estados</option>
          <option value="concluida" <?= $filtros['status']==='concluida'?'selected':'' ?>>Concluída</option>
          <option value="cancelada" <?= $filtros['status']==='cancelada'?'selected':'' ?>>Cancelada</option>
        </select>
      </div>
      <div class="col-6 col-md-2">
        <select name="forma_pagamento" class="form-select form-select-sm">
          <option value="">Todos pagamentos</option>
          <?php foreach ($formas as $v=>$l): ?><option value="<?= $v ?>" <?= $filtros['forma_pagamento']===$v?'selected':'' ?>><?= $l ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="col-6 col-md-2">
        <input type="date" name="data_inicio" class="form-control form-control-sm" value="<?= $filtros['data_inicio'] ?>">
      </div>
      <div class="col-6 col-md-2">
        <input type="date" name="data_fim" class="form-control form-control-sm" value="<?= $filtros['data_fim'] ?>">
      </div>
      <div class="col-12 col-md-1">
        <button type="submit" class="btn btn-sm w-100" style="background:var(--kf-primary);color:#fff;border:none"><i class="bi bi-funnel"></i></button>
      </div>
    </form>
  </div>
</div>
<div class="card border-0 shadow-sm">
  <div class="card-body p-0">
    <?php if (empty($vendas)): ?>
    <div class="text-center py-5 text-muted"><i class="bi bi-receipt fs-1 d-block mb-2"></i>Nenhuma venda encontrada.</div>
    <?php else: ?>
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0" style="font-size:.875rem">
        <thead style="background:var(--kf-primary-light)">
          <tr>
            <th class="ps-3 py-2 fw-semibold" style="color:var(--kf-primary)">Nº Venda</th>
            <th class="py-2 fw-semibold" style="color:var(--kf-primary)">Data</th>
            <th class="py-2 fw-semibold d-none d-md-table-cell" style="color:var(--kf-primary)">Cliente</th>
            <th class="py-2 fw-semibold d-none d-md-table-cell" style="color:var(--kf-primary)">Pagamento</th>
            <th class="py-2 fw-semibold text-end" style="color:var(--kf-primary)">Total</th>
            <th class="py-2 fw-semibold" style="color:var(--kf-primary)">Estado</th>
            <th class="pe-3 py-2"></th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($vendas as $v):
          [$cor,$lbl] = match($v['status']) {
            'concluida'=>['success','Concluída'],'cancelada'=>['danger','Cancelada'],
            default=>['secondary',ucfirst($v['status'])]
          };
        ?>
          <tr>
            <td class="ps-3 fw-semibold"><?= htmlspecialchars($v['numero_venda']) ?></td>
            <td class="text-muted small"><?= date('d/m/Y H:i', strtotime($v['criado_em'])) ?></td>
            <td class="d-none d-md-table-cell"><?= htmlspecialchars($v['cliente_nome'] ?? 'Balcão') ?></td>
            <td class="d-none d-md-table-cell text-muted"><?= $formas[$v['forma_pagamento']] ?? $v['forma_pagamento'] ?></td>
            <td class="text-end fw-bold" style="color:var(--kf-primary)">MT <?= number_format((float)$v['total'],2,',','.') ?></td>
            <td><span class="badge bg-<?= $cor ?>-subtle text-<?= $cor ?> border border-<?= $cor ?>-subtle rounded-pill px-2"><?= $lbl ?></span></td>
            <td class="pe-3"><a href="<?= $APP ?>/vendas/<?= $v['id'] ?>" class="btn btn-sm btn-outline-secondary py-0 px-2"><i class="bi bi-eye"></i></a></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>
</div>
