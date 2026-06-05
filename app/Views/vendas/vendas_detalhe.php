<?php
$APP    = $_ENV['APP_URL'] ?? '';
$v      = $venda;
$isAdmin= in_array($_SESSION['perfil']??'', ['admin','farmaceutico']);
$formas = ['dinheiro'=>'Dinheiro','mpesa'=>'M-Pesa','emola'=>'e-Mola',
           'cartao_debito'=>'Débito','cartao_credito'=>'Crédito','transferencia'=>'Transferência'];
[$cor,$lbl] = match($v['status']) {
  'concluida'=>['success','Concluída'], 'cancelada'=>['danger','Cancelada'],
  'devolvida'=>['warning','Devolvida'], default=>['secondary',ucfirst($v['status'])]
};
?>
<style>
.talao { max-width:420px; margin:0 auto; font-family:monospace; }
@media print {
  .no-print { display:none!important; }
  .talao { max-width:100%; border:none!important; box-shadow:none!important; }
}
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
    <?php if ($v['status']==='concluida' && $isAdmin): ?>
    <button class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modalCancelar">
      <i class="bi bi-x-circle me-1"></i>Cancelar
    </button>
    <?php endif; ?>
    <a href="<?= $APP ?>/vendas" class="btn btn-outline-secondary btn-sm">
      <i class="bi bi-arrow-left me-1"></i>Voltar
    </a>
  </div>
</div>

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

      <!-- Itens -->
      <?php foreach ($v['itens'] as $item): ?>
      <div class="d-flex justify-content-between mb-1 small">
        <div>
          <div><?= htmlspecialchars($item['produto_nome']) ?></div>
          <div class="text-muted"><?= $item['quantidade'] ?>x MT <?= number_format((float)$item['preco_unitario'],2,',','.') ?></div>
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
    <div class="card border-0 shadow-sm mb-3">
      <div class="card-header bg-white fw-semibold py-3" style="font-size:.85rem;color:var(--kf-primary)">
        <i class="bi bi-list-ul me-2"></i>Itens
      </div>
      <div class="card-body p-0">
        <table class="table align-middle mb-0" style="font-size:.875rem">
          <thead class="table-light">
            <tr>
              <th class="ps-3">Produto</th>
              <th class="text-center">Qtd</th>
              <th class="text-end">Preço unit.</th>
              <th class="text-end pe-3">Subtotal</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($v['itens'] as $item): ?>
            <tr>
              <td class="ps-3">
                <div class="fw-semibold"><?= htmlspecialchars($item['produto_nome']) ?></div>
                <?php if (!empty($item['numero_lote'])): ?>
                <div class="d-flex align-items-center gap-2 mt-1">
                  <span style="font-size:11px;background:#d1fae5;color:#065f46;border:1px solid #6ee7b7;padding:1px 6px;border-radius:4px;">
                    <i class="bi bi-tag me-1"></i>Lote: <?= htmlspecialchars($item['numero_lote']) ?>
                  </span>
                  <?php if (!empty($item['lote_validade'])): ?>
                  <span style="font-size:11px;color:#6b7280;">
                    Val: <?= date('d/m/Y', strtotime($item['lote_validade'])) ?>
                  </span>
                  <?php endif; ?>
                </div>
                <?php else: ?>
                <div class="text-muted" style="font-size:11px;margin-top:2px;">Sem rastreio de lote</div>
                <?php endif; ?>
              </td>
              <td class="text-center"><?= $item['quantidade'] ?> <?= htmlspecialchars($item['unidade_medida']) ?></td>
              <td class="text-end">MT <?= number_format((float)$item['preco_unitario'],2,',','.') ?></td>
              <td class="text-end pe-3 fw-bold" style="color:var(--kf-primary)">MT <?= number_format((float)$item['subtotal'],2,',','.') ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

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

<!-- Modal cancelar -->
<?php if ($v['status']==='concluida' && $isAdmin): ?>
<div class="modal fade" id="modalCancelar" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" action="<?= $APP ?>/vendas/<?= $v['id'] ?>/cancelar">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
        <div class="modal-header border-0">
          <h5 class="modal-title text-danger"><i class="bi bi-x-circle me-2"></i>Cancelar Venda</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p class="text-muted small">Esta acção irá repor o stock dos produtos. Não pode ser desfeita.</p>
          <label class="form-label small">Motivo do cancelamento</label>
          <textarea name="motivo" class="form-control" rows="3" required placeholder="Descreva o motivo..."></textarea>
        </div>
        <div class="modal-footer border-0">
          <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Não cancelar</button>
          <button type="submit" class="btn btn-danger btn-sm">Confirmar cancelamento</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php endif; ?>
