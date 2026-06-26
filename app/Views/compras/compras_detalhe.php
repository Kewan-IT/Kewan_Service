<?php $appUrl = $_ENV['APP_URL'] ?? ''; ?>

<?php
$statusLabels = [
    'rascunho'               => ['label'=>'Rascunho',       'badge'=>'secondary', 'icon'=>'bi-pencil-square'],
    'enviada'                => ['label'=>'Enviada',         'badge'=>'primary',   'icon'=>'bi-truck'],
    'parcialmente_recebida'  => ['label'=>'Parc. Recebida',  'badge'=>'warning',   'icon'=>'bi-box-arrow-in-down'],
    'recebida'               => ['label'=>'Recebida',        'badge'=>'success',   'icon'=>'bi-check-circle'],
    'cancelada'              => ['label'=>'Cancelada',       'badge'=>'danger',    'icon'=>'bi-x-circle'],
];
$s        = $statusLabels[$compra['status']] ?? ['label'=>$compra['status'],'badge'=>'secondary','icon'=>'bi-circle'];
$podeReceber  = in_array($compra['status'], ['rascunho','enviada','parcialmente_recebida']);
$podeCancelar = in_array($compra['status'], ['rascunho','enviada']);
?>

<!-- Flash messages -->
<?php if (!empty($flash_sucesso)): ?>
<span id="kf-flash-sucesso" data-msg="<?= htmlspecialchars($flash_sucesso) ?>" hidden></span>
<?php endif; ?>
<?php if (!empty($flash_erro)): ?>
<span id="kf-flash-erro" data-msg="<?= htmlspecialchars($flash_erro) ?>" hidden></span>
<?php endif; ?>

<!-- Cabeçalho -->
<div class="d-flex align-items-start justify-content-between mb-4 flex-wrap gap-2">
  <div>
    <h1 class="page-title"><?= htmlspecialchars($compra['numero_compra']) ?></h1>
    <p class="page-subtitle">
      <span class="badge bg-<?= $s['badge'] ?> me-2">
        <i class="bi <?= $s['icon'] ?> me-1"></i><?= $s['label'] ?>
      </span>
      Criada em <?= date('d/m/Y H:i', strtotime($compra['criado_em'])) ?>
      por <?= htmlspecialchars($compra['usuario_nome']) ?>
    </p>
  </div>
  <div class="d-flex gap-2 flex-wrap">
    <?php if ($podeCancelar): ?>
    <button type="button" class="btn btn-outline-danger btn-sm"
            onclick="confirmarCancelar()">
      <i class="bi bi-x-circle me-1"></i>Cancelar Compra
    </button>
    <?php endif; ?>
    <a href="<?= $appUrl ?>/compras/<?= $compra['id'] ?>/pdf"
   target="_blank" class="btn btn-success btn-sm">
  <i class="bi bi-file-earmark-pdf me-1"></i>Gerar PDF
</a>
    <a href="<?= $appUrl ?>/compras" class="btn btn-outline-secondary btn-sm">
      <i class="bi bi-arrow-left me-1"></i>Voltar
    </a>
  </div>
</div>

<div class="row g-4">

  <!-- Itens da compra -->
  <div class="col-12 col-lg-8">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white py-3">
        <h6 class="fw-bold mb-0"><i class="bi bi-list-ul me-2 text-success"></i>Itens da Compra</h6>
      </div>

      <?php if ($podeReceber): ?>
      <form action="<?= $appUrl ?>/compras/<?= $compra['id'] ?>/receber" method="POST">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
      <?php endif; ?>

      <div class="table-responsive">
        <table class="table align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th class="ps-3" style="font-size:12px">Produto</th>
              <th class="text-center" style="width:80px;font-size:12px">Pedido</th>
              <th class="text-center" style="width:80px;font-size:12px">Recebido</th>
              <?php if ($podeReceber): ?>
              <th class="text-center" style="width:80px;font-size:12px">Receber</th>
              <th class="text-center" style="width:100px;font-size:12px">Lote</th>
              <th class="text-center" style="width:110px;font-size:12px">Validade</th>
              <?php endif; ?>
              <th class="text-end" style="width:120px;font-size:12px">Preço Unit.</th>
              <th class="text-end" style="width:120px;font-size:12px">Subtotal</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($compra['itens'] as $item):
              $pendente = $item['quantidade'] - $item['quantidade_recebida'];
              $completo = $pendente <= 0;
            ?>
            <tr class="<?= $completo ? 'table-success' : '' ?>">
              <td class="ps-3">
                <div class="fw-semibold" style="font-size:13px"><?= htmlspecialchars($item['produto_nome']) ?></div>
                <div class="text-muted" style="font-size:11px"><?= htmlspecialchars($item['unidade_medida']) ?></div>
              </td>
              <td class="text-center fw-semibold"><?= $item['quantidade'] ?></td>
              <td class="text-center">
                <span class="badge bg-<?= $item['quantidade_recebida'] >= $item['quantidade'] ? 'success' : ($item['quantidade_recebida'] > 0 ? 'warning' : 'secondary') ?>">
                  <?= $item['quantidade_recebida'] ?>
                </span>
              </td>
              <?php if ($podeReceber): ?>
              <td class="text-center">
                <?php if (!$completo): ?>
                <input type="number" name="receber[<?= $item['id'] ?>]"
                       class="form-control form-control-sm text-center px-1"
                       style="width:60px" min="0" max="<?= $pendente ?>"
                       value="<?= $pendente ?>" placeholder="0">
                <?php else: ?>
                <i class="bi bi-check-circle-fill text-success"></i>
                <?php endif; ?>
              </td>
              <td class="text-center">
                <?php if (!$completo): ?>
                <input type="text" name="lote[<?= $item['id'] ?>]"
                       class="form-control form-control-sm text-center px-1"
                       style="width:90px"
                       value="<?= htmlspecialchars($item['numero_lote'] ?? '') ?>"
                       placeholder="Lote">
                <?php else: ?>
                <span class="text-muted" style="font-size:12px"><?= htmlspecialchars($item['numero_lote'] ?? '—') ?></span>
                <?php endif; ?>
              </td>
              <td class="text-center">
                <?php if (!$completo): ?>
                <input type="date" name="validade[<?= $item['id'] ?>]"
                       class="form-control form-control-sm px-1"
                       style="width:110px"
                       value="<?= htmlspecialchars($item['validade_lote'] ?? '') ?>">
                <?php else: ?>
                <span class="text-muted" style="font-size:12px">
                  <?= $item['validade_lote'] ? date('d/m/Y', strtotime($item['validade_lote'])) : '—' ?>
                </span>
                <?php endif; ?>
              </td>
              <?php endif; ?>
              <td class="text-end" style="font-size:13px">
                <?= number_format($item['preco_unitario'], 2, ',', '.') ?> MZN
              </td>
              <td class="text-end fw-semibold text-success" style="font-size:14px">
                <?= number_format($item['subtotal'], 2, ',', '.') ?> MZN
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <?php if ($podeReceber): ?>
      <div class="card-footer bg-white py-3 d-flex justify-content-end">
        <button type="submit" class="btn btn-success fw-bold">
          <i class="bi bi-box-arrow-in-down me-2"></i>Confirmar Recepção de Mercadoria
        </button>
      </div>
      </form>
      <?php endif; ?>

    </div>
  </div>

  <!-- Coluna direita: info da compra -->
  <div class="col-12 col-lg-4">

    <!-- Fornecedor -->
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-header bg-white py-3">
        <h6 class="fw-bold mb-0"><i class="bi bi-building me-2 text-success"></i>Fornecedor</h6>
      </div>
      <div class="card-body py-3">
        <div class="fw-semibold"><?= htmlspecialchars($compra['fornecedor_nome']) ?></div>
        <?php if ($compra['fornecedor_telefone']): ?>
        <div class="text-muted small"><i class="bi bi-telephone me-1"></i><?= htmlspecialchars($compra['fornecedor_telefone']) ?></div>
        <?php endif; ?>
        <?php if ($compra['fornecedor_email']): ?>
        <div class="text-muted small"><i class="bi bi-envelope me-1"></i><?= htmlspecialchars($compra['fornecedor_email']) ?></div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Totais -->
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-header bg-white py-3">
        <h6 class="fw-bold mb-0"><i class="bi bi-calculator me-2 text-success"></i>Totais</h6>
      </div>
      <div class="card-body py-3">
        <div class="d-flex justify-content-between mb-1" style="font-size:13px">
          <span class="text-muted">Subtotal</span>
          <span><?= number_format($compra['subtotal'], 2, ',', '.') ?> MZN</span>
        </div>
        <?php if ($compra['desconto'] > 0): ?>
        <div class="d-flex justify-content-between mb-1" style="font-size:13px">
          <span class="text-muted">Desconto</span>
          <span class="text-danger">-<?= number_format($compra['desconto'], 2, ',', '.') ?> MZN</span>
        </div>
        <?php endif; ?>
        <div class="d-flex justify-content-between fw-bold border-top pt-2 mt-1" style="font-size:18px">
          <span>TOTAL</span>
          <span class="text-success"><?= number_format($compra['total'], 2, ',', '.') ?> MZN</span>
        </div>
      </div>
    </div>

    <!-- Detalhes -->
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white py-3">
        <h6 class="fw-bold mb-0"><i class="bi bi-info-circle me-2 text-success"></i>Detalhes</h6>
      </div>
      <div class="card-body py-3">
        <?php
        $detalhes = [
          ['Nº Fatura',       $compra['numero_fatura'] ?? '—'],
          ['Data do Pedido',  $compra['data_pedido']   ? date('d/m/Y', strtotime($compra['data_pedido'])) : '—'],
          ['Entrega Prevista',$compra['data_entrega']  ? date('d/m/Y', strtotime($compra['data_entrega'])) : '—'],
          ['Criado por',      $compra['usuario_nome']],
        ];
        foreach ($detalhes as [$label, $valor]):
        ?>
        <div class="d-flex justify-content-between mb-2" style="font-size:13px">
          <span class="text-muted"><?= $label ?></span>
          <span class="fw-semibold text-end"><?= htmlspecialchars((string)$valor) ?></span>
        </div>
        <?php endforeach; ?>

        <?php if ($compra['observacoes']): ?>
        <div class="border-top pt-2 mt-1">
          <div class="text-muted" style="font-size:12px">Observações</div>
          <div style="font-size:13px"><?= nl2br(htmlspecialchars($compra['observacoes'])) ?></div>
        </div>
        <?php endif; ?>
      </div>
    </div>

  </div>
</div>

<!-- Modal cancelar -->
<div class="modal fade" id="modalCancelar" tabindex="-1">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <div class="modal-header">
        <h6 class="modal-title">Cancelar Compra</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p class="mb-0">Tem a certeza que quer cancelar a compra <strong><?= htmlspecialchars($compra['numero_compra']) ?></strong>?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Não</button>
        <form action="<?= $appUrl ?>/compras/<?= $compra['id'] ?>/cancelar" method="POST" class="d-inline">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
          <button type="submit" class="btn btn-danger btn-sm">Sim, cancelar</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
function confirmarCancelar() {
  new bootstrap.Modal(document.getElementById('modalCancelar')).show();
}
</script>