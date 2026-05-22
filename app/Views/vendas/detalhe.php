<?php
$appUrl  = $_ENV['APP_URL'] ?? '';
$v       = $venda;
$isAdmin = in_array($_SESSION['perfil'], ['admin','farmaceutico']);
$formas  = [
  'dinheiro'=>'Dinheiro','mpesa'=>'M-Pesa','emola'=>'e-Mola',
  'cartao_debito'=>'Débito','cartao_credito'=>'Crédito','transferencia'=>'Transferência',
];
[$cor,$label] = match($v['status']) {
  'concluida' => ['success','Concluída'],
  'cancelada' => ['danger', 'Cancelada'],
  'devolvida' => ['warning','Devolvida'],
  default     => ['secondary',$v['status']],
};
?>

<!-- Cabeçalho -->
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
  <div>
    <h1 class="page-title">Venda <?= htmlspecialchars($v['numero_venda']) ?></h1>
    <p class="page-subtitle"><?= date('d/m/Y \à\s H:i', strtotime($v['criado_em'])) ?>
      &bull; <?= htmlspecialchars($v['usuario_nome']) ?>
    </p>
  </div>
  <div class="d-flex gap-2 flex-wrap">
    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.print()">
      <i class="bi bi-printer me-1"></i>Imprimir Talão
    </button>
    <?php if ($v['status'] === 'concluida' && $isAdmin): ?>
    <button type="button" class="btn btn-outline-danger btn-sm"
            data-bs-toggle="modal" data-bs-target="#modalCancelar">
      <i class="bi bi-x-circle me-1"></i>Cancelar Venda
    </button>
    <?php endif; ?>
  </div>
</div>

<div class="row g-4">

  <!-- Coluna principal: itens -->
  <div class="col-md-8">
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
        <h6 class="fw-bold mb-0"><i class="bi bi-list-ul me-2 text-success"></i>Itens Vendidos</h6>
        <span class="badge bg-<?= $cor ?>"><?= $label ?></span>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th class="ps-4">Produto</th>
                <th class="text-center">Qtd</th>
                <th class="text-end">Preço</th>
                <th class="text-end pe-4">Subtotal</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($v['itens'] as $item): ?>
              <tr>
                <td class="ps-4">
                  <div class="fw-semibold" style="font-size:13px"><?= htmlspecialchars($item['produto_nome']) ?></div>
                  <?php if ($item['numero_lote']): ?>
                  <div class="text-muted" style="font-size:11px">Lote: <?= htmlspecialchars($item['numero_lote']) ?></div>
                  <?php endif; ?>
                </td>
                <td class="text-center fw-bold"><?= $item['quantidade'] ?></td>
                <td class="text-end" style="font-size:13px"><?= number_format($item['preco_unitario'],2,',','.') ?></td>
                <td class="text-end pe-4 fw-semibold text-success" style="font-size:14px">
                  <?= number_format($item['subtotal'],2,',','.') ?>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
            <tfoot class="table-light">
              <tr>
                <td colspan="3" class="text-end fw-semibold ps-4" style="font-size:13px">Subtotal:</td>
                <td class="text-end pe-4"><?= number_format($v['subtotal'],2,',','.') ?> MZN</td>
              </tr>
              <?php if ($v['desconto'] > 0): ?>
              <tr>
                <td colspan="3" class="text-end text-danger ps-4" style="font-size:13px">Desconto:</td>
                <td class="text-end pe-4 text-danger">-<?= number_format($v['desconto'],2,',','.') ?> MZN</td>
              </tr>
              <?php endif; ?>
              <tr>
                <td colspan="3" class="text-end fw-bold ps-4" style="font-size:16px">TOTAL:</td>
                <td class="text-end pe-4 fw-bold text-success" style="font-size:18px">
                  <?= number_format($v['total'],2,',','.') ?> MZN
                </td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- Coluna lateral: info -->
  <div class="col-md-4">

    <!-- Pagamento -->
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-header bg-white py-3">
        <h6 class="fw-bold mb-0"><i class="bi bi-cash me-2 text-success"></i>Pagamento</h6>
      </div>
      <div class="card-body" style="font-size:13px">
        <div class="d-flex justify-content-between py-2 border-bottom">
          <span class="text-muted">Forma</span>
          <span class="fw-semibold"><?= $formas[$v['forma_pagamento']] ?? $v['forma_pagamento'] ?></span>
        </div>
        <div class="d-flex justify-content-between py-2 border-bottom">
          <span class="text-muted">Total</span>
          <span class="fw-bold text-success"><?= number_format($v['total'],2,',','.') ?> MZN</span>
        </div>
        <div class="d-flex justify-content-between py-2 border-bottom">
          <span class="text-muted">Valor pago</span>
          <span><?= number_format($v['valor_pago'],2,',','.') ?> MZN</span>
        </div>
        <div class="d-flex justify-content-between py-2">
          <span class="text-muted">Troco</span>
          <span class="fw-bold"><?= number_format(max(0, $v['valor_pago'] - $v['total']),2,',','.') ?> MZN</span>
        </div>
      </div>
    </div>

    <!-- Cliente -->
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-header bg-white py-3">
        <h6 class="fw-bold mb-0"><i class="bi bi-person me-2 text-success"></i>Cliente</h6>
      </div>
      <div class="card-body" style="font-size:13px">
        <?php if ($v['cliente_nome']): ?>
        <div class="fw-semibold"><?= htmlspecialchars($v['cliente_nome']) ?></div>
        <?php if ($v['cliente_telefone']): ?>
        <div class="text-muted"><?= htmlspecialchars($v['cliente_telefone']) ?></div>
        <?php endif; ?>
        <?php if ($v['cliente_nuit']): ?>
        <div class="text-muted">NUIT: <?= htmlspecialchars($v['cliente_nuit']) ?></div>
        <?php endif; ?>
        <?php else: ?>
        <span class="text-muted">Venda balcão (sem cliente)</span>
        <?php endif; ?>
      </div>
    </div>

    <!-- Acções rápidas -->
    <div class="d-grid gap-2">
      <a href="<?= $appUrl ?>/vendas/nova" class="btn btn-success">
        <i class="bi bi-cart-plus me-1"></i>Nova Venda
      </a>
      <a href="<?= $appUrl ?>/vendas" class="btn btn-outline-secondary">
        <i class="bi bi-list-ul me-1"></i>Ver todas as vendas
      </a>
    </div>

  </div>
</div>

<!-- Modal Cancelar -->
<?php if ($v['status'] === 'concluida' && $isAdmin): ?>
<div class="modal fade" id="modalCancelar" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <form action="<?= $appUrl ?>/vendas/<?= $v['id'] ?>/cancelar" method="POST">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
        <div class="modal-header">
          <h5 class="modal-title fw-bold text-danger">
            <i class="bi bi-x-circle me-2"></i>Cancelar Venda
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="alert alert-warning py-2" style="font-size:13px">
            <i class="bi bi-exclamation-triangle me-1"></i>
            O stock dos produtos será reposto automaticamente.
          </div>
          <label class="form-label fw-semibold">Motivo do cancelamento</label>
          <textarea name="motivo" class="form-control" rows="3" required
                    placeholder="Descreva o motivo do cancelamento..."></textarea>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Fechar</button>
          <button type="submit" class="btn btn-danger">
            <i class="bi bi-x-circle me-1"></i>Confirmar cancelamento
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- Talão para impressão -->
<div id="talao-impressao" style="display:none">
  <style>
    @media print {
      body > *:not(#talao-impressao) { display:none!important; }
      #talao-impressao { display:block!important; }
      .kf-sidebar, .kf-header { display:none!important; }
    }
  </style>
  <div style="font-family:monospace;max-width:300px;margin:0 auto;font-size:12px">
    <div style="text-align:center;margin-bottom:8px">
      <div style="font-size:16px;font-weight:bold">KewanFarma</div>
      <div>Talão de Venda</div>
      <div>━━━━━━━━━━━━━━━━━━━━━━━━━━━━</div>
    </div>
    <div>Nº: <strong><?= $v['numero_venda'] ?></strong></div>
    <div>Data: <?= date('d/m/Y H:i', strtotime($v['criado_em'])) ?></div>
    <div>Atendido por: <?= htmlspecialchars($v['usuario_nome']) ?></div>
    <?php if ($v['cliente_nome']): ?>
    <div>Cliente: <?= htmlspecialchars($v['cliente_nome']) ?></div>
    <?php endif; ?>
    <div>━━━━━━━━━━━━━━━━━━━━━━━━━━━━</div>
    <?php foreach ($v['itens'] as $item): ?>
    <div><?= htmlspecialchars(mb_substr($item['produto_nome'],0,20)) ?></div>
    <div style="display:flex;justify-content:space-between">
      <span><?= $item['quantidade'] ?> x <?= number_format($item['preco_unitario'],2,',','.') ?></span>
      <span><?= number_format($item['subtotal'],2,',','.') ?> MZN</span>
    </div>
    <?php endforeach; ?>
    <div>━━━━━━━━━━━━━━━━━━━━━━━━━━━━</div>
    <?php if ($v['desconto'] > 0): ?>
    <div style="display:flex;justify-content:space-between">
      <span>Desconto:</span><span>-<?= number_format($v['desconto'],2,',','.') ?> MZN</span>
    </div>
    <?php endif; ?>
    <div style="display:flex;justify-content:space-between;font-size:14px;font-weight:bold">
      <span>TOTAL:</span><span><?= number_format($v['total'],2,',','.') ?> MZN</span>
    </div>
    <div style="display:flex;justify-content:space-between">
      <span>Pago (<?= $formas[$v['forma_pagamento']] ?? '' ?>):</span>
      <span><?= number_format($v['valor_pago'],2,',','.') ?> MZN</span>
    </div>
    <div style="display:flex;justify-content:space-between">
      <span>Troco:</span>
      <span><?= number_format(max(0,$v['valor_pago']-$v['total']),2,',','.') ?> MZN</span>
    </div>
    <div style="text-align:center;margin-top:12px">
      <div>Obrigado pela sua preferência!</div>
      <div>━━━━━━━━━━━━━━━━━━━━━━━━━━━━</div>
    </div>
  </div>
</div>

<script>
window.addEventListener('beforeprint', () => {
  document.getElementById('talao-impressao').style.display = 'block';
});
window.addEventListener('afterprint', () => {
  document.getElementById('talao-impressao').style.display = 'none';
});
</script>
