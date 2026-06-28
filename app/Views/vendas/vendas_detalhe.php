<?php
$APP    = $_ENV['APP_URL'] ?? '';
$v      = $venda;
$isAdmin= in_array($_SESSION['perfil']??'', ['admin','diretor','farmaceutico']);
$formas = ['dinheiro'=>'Dinheiro','mpesa'=>'M-Pesa','emola'=>'e-Mola',
           'cartao_debito'=>'Débito','cartao_credito'=>'Crédito','transferencia'=>'Transferência'];
[$cor,$lbl] = match($v['status']) {
  'concluida'=>['success','Concluída'], 'cancelada'=>['danger','Cancelada'],
  'devolvida'=>['warning','Devolvida'], default=>['secondary',ucfirst($v['status'])]
};

// Calcular qty disponível para devolução por item
$itensComDisp = array_map(function($item) {
    $item['qty_disponivel'] = max(0, (int)$item['quantidade'] - (int)($item['qty_devolvida'] ?? 0));
    return $item;
}, $v['itens']);

$podeCancelar  = $v['status'] === 'concluida' && $isAdmin;
$podeDevolver  = in_array($v['status'], ['concluida','devolvida']) && $isAdmin
                 && count(array_filter($itensComDisp, fn($i) => $i['qty_disponivel'] > 0)) > 0;
?>
<style>
.talao { max-width:420px; margin:0 auto; font-family:monospace; }
@media print {
  .no-print { display:none!important; }
  .talao { max-width:100%; border:none!important; box-shadow:none!important; }
}
.badge-devolvido { background:#fef3c7;color:#92400e;border:1px solid #fcd34d;font-size:.7rem;padding:1px 6px;border-radius:4px; }
</style>

<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2 no-print">
  <div>
    <h1 class="h4 fw-bold mb-0" style="color:var(--kf-primary)">
      Venda <?= htmlspecialchars($v['numero_venda']) ?>
    </h1>
    <p class="text-muted small mb-0">
      <?= date('d/m/Y \à\s H:i', strtotime($v['criado_em'])) ?>
      &bull; <?= htmlspecialchars($v['usuario_nome'] ?? '') ?>
    </p>
  </div>
  <div class="d-flex gap-2 flex-wrap">
    <button class="btn btn-outline-secondary btn-sm" onclick="window.print()">
      <i class="bi bi-printer me-1"></i>Imprimir
    </button>
    <?php if ($podeDevolver): ?>
    <button class="btn btn-outline-warning btn-sm" data-bs-toggle="modal" data-bs-target="#modalDevParcial">
      <i class="bi bi-arrow-return-left me-1"></i>Devolução Parcial
    </button>
    <?php endif; ?>
    <?php if ($podeCancelar): ?>
    <button class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modalCancelar">
      <i class="bi bi-x-circle me-1"></i>Devolução Total
    </button>
    <?php endif; ?>
    <a href="<?= $APP ?>/vendas" class="btn btn-outline-secondary btn-sm">
      <i class="bi bi-arrow-left me-1"></i>Voltar
    </a>
  </div>
</div>

<?php if (!empty($flash_sucesso)): ?>
<span id="kf-flash-sucesso" data-msg="<?= htmlspecialchars($flash_sucesso) ?>" hidden></span>
<?php endif; ?>
<?php if (!empty($flash_erro)): ?>
<span id="kf-flash-erro" data-msg="<?= htmlspecialchars($flash_erro) ?>" hidden></span>
<?php endif; ?>

<div class="row g-4">
  <!-- Talão -->
  <div class="col-12 col-md-5">
    <div class="card border-0 shadow-sm talao p-4">
      <div class="text-center mb-3">
        <div class="fw-bold fs-5" style="color:var(--kf-primary)">KewanFarma</div>
        <div class="text-muted small">Talão de Venda</div>
        <div class="fw-bold mt-1"><?= htmlspecialchars($v['numero_venda']) ?></div>
        <div class="text-muted small"><?= date('d/m/Y H:i', strtotime($v['criado_em'])) ?></div>
      </div>
      <hr>
      <?php if ($v['cliente_nome']): ?>
      <div class="mb-2 small">
        <strong>Cliente:</strong> <?= htmlspecialchars($v['cliente_nome']) ?><br>
        <?php if ($v['cliente_nuit']): ?><strong>NUIT:</strong> <?= htmlspecialchars($v['cliente_nuit']) ?><?php endif; ?>
      </div>
      <hr>
      <?php endif; ?>

      <?php foreach ($itensComDisp as $item): ?>
      <div class="d-flex justify-content-between mb-1 small">
        <div>
          <div><?= htmlspecialchars($item['produto_nome']) ?></div>
          <div class="text-muted"><?= $item['quantidade'] ?>x MT <?= number_format((float)$item['preco_unitario'],2,',','.') ?></div>
          <?php if ((int)($item['qty_devolvida'] ?? 0) > 0): ?>
          <span class="badge-devolvido"><i class="bi bi-arrow-return-left"></i> <?= $item['qty_devolvida'] ?> devolvido(s)</span>
          <?php endif; ?>
        </div>
        <div class="fw-semibold">MT <?= number_format((float)$item['subtotal'],2,',','.') ?></div>
      </div>
      <?php endforeach; ?>

      <hr>
      <div class="d-flex justify-content-between small mb-1">
        <span class="text-muted">Subtotal</span>
        <span>MT <?= number_format((float)$v['subtotal'],2,',','.') ?></span>
      </div>
      <?php if ($v['desconto'] > 0): ?>
      <div class="d-flex justify-content-between small mb-1">
        <span class="text-muted">Desconto</span>
        <span class="text-danger">-MT <?= number_format((float)$v['desconto'],2,',','.') ?></span>
      </div>
      <?php endif; ?>
      <div class="d-flex justify-content-between fw-bold mt-2" style="font-size:1.1rem">
        <span>TOTAL</span>
        <span style="color:var(--kf-primary)">MT <?= number_format((float)$v['total'],2,',','.') ?></span>
      </div>
      <hr>
      <div class="small">
        <div class="d-flex justify-content-between">
          <span class="text-muted">Pagamento</span>
          <span><?= $formas[$v['forma_pagamento']] ?? $v['forma_pagamento'] ?></span>
        </div>
        <div class="d-flex justify-content-between">
          <span class="text-muted">Valor recebido</span>
          <span>MT <?= number_format((float)$v['valor_pago'],2,',','.') ?></span>
        </div>
        <?php $troco = (float)$v['valor_pago'] - (float)$v['total']; ?>
        <?php if ($troco > 0): ?>
        <div class="d-flex justify-content-between">
          <span class="text-muted">Troco</span>
          <span class="text-success fw-bold">MT <?= number_format($troco,2,',','.') ?></span>
        </div>
        <?php endif; ?>
      </div>
      <hr>
      <div class="text-center text-muted small">
        <span class="badge bg-<?= $cor ?>-subtle text-<?= $cor ?> border border-<?= $cor ?>-subtle rounded-pill px-3">
          <?= $lbl ?>
        </span>
        <div class="mt-2">Obrigado pela sua preferência!</div>
      </div>
    </div>
  </div>

  <!-- Detalhes -->
  <div class="col-12 col-md-7 no-print">
    <!-- Itens -->
    <div class="card border-0 shadow-sm mb-3">
      <div class="card-header bg-white fw-semibold py-3" style="font-size:.85rem;color:var(--kf-primary)">
        <i class="bi bi-list-ul me-2"></i>Itens da Venda
      </div>
      <div class="card-body p-0">
        <table class="table align-middle mb-0" style="font-size:.875rem">
          <thead class="table-light">
            <tr>
              <th class="ps-3">Produto</th>
              <th class="text-center">Qtd</th>
              <th class="text-center">Devolvido</th>
              <th class="text-end">Preço unit.</th>
              <th class="text-end pe-3">Subtotal</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($itensComDisp as $item): ?>
            <tr <?= (int)($item['qty_devolvida'] ?? 0) >= (int)$item['quantidade'] ? 'class="table-warning opacity-75"' : '' ?>>
              <td class="ps-3">
                <div class="fw-semibold"><?= htmlspecialchars($item['produto_nome']) ?></div>
                <?php if (!empty($item['numero_lote'])): ?>
                <div class="d-flex align-items-center gap-2 mt-1">
                  <span style="font-size:11px;background:#d1fae5;color:#065f46;border:1px solid #6ee7b7;padding:1px 6px;border-radius:4px;">
                    <i class="bi bi-tag me-1"></i>Lote: <?= htmlspecialchars($item['numero_lote']) ?>
                  </span>
                  <?php if (!empty($item['lote_validade'])): ?>
                  <span style="font-size:11px;color:#6b7280;">Val: <?= date('d/m/Y', strtotime($item['lote_validade'])) ?></span>
                  <?php endif; ?>
                </div>
                <?php endif; ?>
              </td>
              <td class="text-center"><?= $item['quantidade'] ?> <?= htmlspecialchars($item['unidade_medida']) ?></td>
              <td class="text-center">
                <?php if ((int)($item['qty_devolvida'] ?? 0) > 0): ?>
                  <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-2">
                    <?= $item['qty_devolvida'] ?>
                  </span>
                <?php else: ?>
                  <span class="text-muted small">—</span>
                <?php endif; ?>
              </td>
              <td class="text-end">MT <?= number_format((float)$item['preco_unitario'],2,',','.') ?></td>
              <td class="text-end pe-3 fw-bold" style="color:var(--kf-primary)">MT <?= number_format((float)$item['subtotal'],2,',','.') ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Histórico de devoluções -->
    <?php if (!empty($devolucoes)): ?>
    <div class="card border-0 shadow-sm mb-3">
      <div class="card-header bg-white fw-semibold py-3" style="font-size:.85rem;color:#92400e">
        <i class="bi bi-arrow-return-left me-2"></i>Histórico de Devoluções
      </div>
      <div class="card-body p-0">
        <?php foreach ($devolucoes as $dev): ?>
        <div class="px-3 py-2 border-bottom" style="font-size:.85rem">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <span class="badge rounded-pill <?= $dev['tipo']==='total' ? 'bg-danger-subtle text-danger border border-danger-subtle' : 'bg-warning-subtle text-warning border border-warning-subtle' ?> me-2">
                <?= $dev['tipo'] === 'total' ? 'Total' : 'Parcial' ?>
              </span>
              <span class="fw-semibold"><?= htmlspecialchars($dev['usuario_nome'] ?? '') ?></span>
              <span class="text-muted ms-2"><?= date('d/m/Y H:i', strtotime($dev['criado_em'])) ?></span>
            </div>
            <div class="fw-bold text-danger">-MT <?= number_format((float)$dev['valor_total'],2,',','.') ?></div>
          </div>
          <?php if (!empty($dev['motivo'])): ?>
          <div class="text-muted mt-1"><i class="bi bi-chat-text me-1"></i><?= htmlspecialchars($dev['motivo']) ?></div>
          <?php endif; ?>
          <?php if (!empty($dev['itens'])): ?>
          <div class="mt-2 ms-2">
            <?php foreach ($dev['itens'] as $di): ?>
            <div class="text-muted small">
              &bull; <?= htmlspecialchars($di['produto_nome']) ?>
              — <?= $di['quantidade'] ?> un. &times; MT <?= number_format((float)$di['preco_unitario'],2,',','.') ?>
              = MT <?= number_format((float)$di['subtotal'],2,',','.') ?>
            </div>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <?php if ($v['observacoes']): ?>
    <div class="card border-0 shadow-sm">
      <div class="card-body">
        <div class="text-muted small fw-semibold mb-1">Observações</div>
        <div style="font-size:.875rem"><?= nl2br(htmlspecialchars($v['observacoes'])) ?></div>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>

<!-- ══ MODAL: DEVOLUÇÃO TOTAL ══ -->
<?php if ($podeCancelar): ?>
<div class="modal fade" id="modalCancelar" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" action="<?= $APP ?>/vendas/<?= $v['id'] ?>/cancelar">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
        <div class="modal-header border-0">
          <h5 class="modal-title text-danger"><i class="bi bi-x-circle me-2"></i>Devolução Total</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="alert alert-danger py-2 small">
            <i class="bi bi-exclamation-triangle me-1"></i>
            <strong>Todos</strong> os produtos serão devolvidos ao stock. Esta acção não pode ser desfeita.
          </div>
          <div class="mb-3">
            <label class="form-label small fw-semibold">Motivo da devolução <span class="text-danger">*</span></label>
            <textarea name="motivo" class="form-control" rows="3" required placeholder="Ex: Cliente desistiu, produto trocado..."></textarea>
          </div>
          <div class="table-responsive">
            <table class="table table-sm mb-0" style="font-size:.8rem">
              <thead class="table-light"><tr><th>Produto</th><th class="text-center">Qtd</th><th class="text-end">Valor</th></tr></thead>
              <tbody>
              <?php foreach ($itensComDisp as $item): ?>
              <tr>
                <td><?= htmlspecialchars($item['produto_nome']) ?></td>
                <td class="text-center"><?= $item['quantidade'] ?></td>
                <td class="text-end">MT <?= number_format((float)$item['subtotal'],2,',','.') ?></td>
              </tr>
              <?php endforeach; ?>
              </tbody>
              <tfoot>
                <tr class="fw-bold">
                  <td colspan="2">Total a devolver</td>
                  <td class="text-end text-danger">MT <?= number_format((float)$v['total'],2,',','.') ?></td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
        <div class="modal-footer border-0">
          <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-danger btn-sm"><i class="bi bi-x-circle me-1"></i>Confirmar Devolução Total</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- ══ MODAL: DEVOLUÇÃO PARCIAL ══ -->
<?php if ($podeDevolver): ?>
<div class="modal fade" id="modalDevParcial" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <form method="POST" action="<?= $APP ?>/vendas/<?= $v['id'] ?>/devolver" id="formDevParcial">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
        <div class="modal-header border-0">
          <h5 class="modal-title" style="color:#92400e"><i class="bi bi-arrow-return-left me-2"></i>Devolução Parcial</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p class="text-muted small mb-3">
            Indique a quantidade a devolver de cada produto. Deixe <strong>0</strong> nos que não serão devolvidos.
          </p>

          <div class="table-responsive mb-3">
            <table class="table align-middle mb-0" style="font-size:.875rem">
              <thead class="table-light">
                <tr>
                  <th class="ps-3">Produto</th>
                  <th class="text-center">Comprou</th>
                  <th class="text-center">Já devolveu</th>
                  <th class="text-center">Disponível</th>
                  <th class="text-center" style="width:130px">Qtd a devolver</th>
                </tr>
              </thead>
              <tbody>
              <?php foreach ($itensComDisp as $item): ?>
              <tr <?= $item['qty_disponivel'] == 0 ? 'class="opacity-50"' : '' ?>>
                <td class="ps-3">
                  <div class="fw-semibold"><?= htmlspecialchars($item['produto_nome']) ?></div>
                  <?php if (!empty($item['numero_lote'])): ?>
                  <span style="font-size:11px;color:#6b7280;">Lote: <?= htmlspecialchars($item['numero_lote']) ?></span>
                  <?php endif; ?>
                </td>
                <td class="text-center"><?= $item['quantidade'] ?></td>
                <td class="text-center">
                  <?php if ((int)($item['qty_devolvida'] ?? 0) > 0): ?>
                  <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-2">
                    <?= $item['qty_devolvida'] ?>
                  </span>
                  <?php else: ?>
                  <span class="text-muted">0</span>
                  <?php endif; ?>
                </td>
                <td class="text-center fw-semibold <?= $item['qty_disponivel'] > 0 ? 'text-success' : 'text-muted' ?>">
                  <?= $item['qty_disponivel'] ?>
                </td>
                <td class="text-center">
                  <input type="number"
                         name="dev_qty[<?= $item['id'] ?>]"
                         class="form-control form-control-sm text-center dev-qty-input"
                         value="0"
                         min="0"
                         max="<?= $item['qty_disponivel'] ?>"
                         <?= $item['qty_disponivel'] == 0 ? 'disabled' : '' ?>
                         data-preco="<?= (float)$item['preco_unitario'] ?>"
                         data-max="<?= $item['qty_disponivel'] ?>">
                </td>
              </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>

          <!-- Resumo dinâmico -->
          <div class="d-flex justify-content-between align-items-center px-2 py-2 rounded"
               style="background:#fef3c7;border:1px solid #fcd34d">
            <span class="fw-semibold small" style="color:#92400e">
              <i class="bi bi-calculator me-1"></i>Valor a devolver ao cliente:
            </span>
            <span class="fw-bold fs-5 text-danger" id="totalDevolucao">MT 0,00</span>
          </div>

          <div class="mt-3">
            <label class="form-label small fw-semibold">Motivo <span class="text-danger">*</span></label>
            <textarea name="motivo" class="form-control" rows="2" required
                      placeholder="Ex: Produto errado, cliente desistiu de um item..."></textarea>
          </div>
        </div>
        <div class="modal-footer border-0">
          <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-warning btn-sm" id="btnConfirmarDev" disabled>
            <i class="bi bi-arrow-return-left me-1"></i>Confirmar Devolução
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
(function() {
  function actualizarTotal() {
    let total = 0;
    let temQty = false;
    document.querySelectorAll('.dev-qty-input').forEach(function(input) {
      const qty   = parseInt(input.value) || 0;
      const preco = parseFloat(input.dataset.preco) || 0;
      const max   = parseInt(input.dataset.max) || 0;
      if (qty > max) input.value = max;
      if (qty > 0) { total += qty * preco; temQty = true; }
    });
    document.getElementById('totalDevolucao').textContent =
      'MT ' + total.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    document.getElementById('btnConfirmarDev').disabled = !temQty;
  }
  document.querySelectorAll('.dev-qty-input').forEach(function(el) {
    el.addEventListener('input', actualizarTotal);
  });
})();
</script>
<?php endif; ?>
