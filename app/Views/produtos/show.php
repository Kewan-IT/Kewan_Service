<?php
// app/Views/produtos/show.php
$APP    = $_ENV['APP_URL'] ?? '';
$p      = $produto;
$isEdit = in_array($_SESSION['perfil'] ?? '', ['admin', 'farmaceutico']);

$diasValidade = null;
if (!empty($lotes)) {
    $vals = array_filter(array_map(fn($l) => $l['quantidade'] > 0 ? (int)$l['dias_validade'] : null, $lotes));
    if ($vals) $diasValidade = min($vals);
}
?>

<style>
.ficha-section { background:#fff; border-radius:10px; padding:1.25rem 1.5rem; margin-bottom:1rem; box-shadow:0 1px 4px rgba(0,0,0,.07); }
.ficha-section-title { font-weight:600; font-size:.8rem; text-transform:uppercase; letter-spacing:.05em; color:var(--kf-primary); border-bottom:1px solid var(--kf-primary-light); padding-bottom:.5rem; margin-bottom:1rem; display:flex; align-items:center; gap:.5rem; }
.dado-label { font-size:.72rem; text-transform:uppercase; letter-spacing:.04em; color:#999; font-weight:500; margin-bottom:1px; }
.dado-valor { font-size:.9rem; color:#1a2e27; }
.lote-row { border-radius:8px; padding:.6rem .75rem; margin-bottom:.4rem; }
.lote-ok   { background:#d1e7dd; }
.lote-warn { background:#fff3cd; }
.lote-crit { background:#f8d7da; }
.lote-exp  { background:#e9ecef; }
.mov-dot { width:8px; height:8px; border-radius:50%; flex-shrink:0; margin-top:5px; }
</style>

<!-- Cabeçalho -->
<div class="d-flex align-items-start justify-content-between mb-4 flex-wrap gap-2">
  <div class="d-flex gap-3 align-items-start flex-wrap">
    <?php if ($p['imagem_url']): ?>
    <img src="<?= $APP ?>/uploads/<?= htmlspecialchars($p['imagem_url']) ?>"
         style="width:80px;height:80px;object-fit:cover;border-radius:10px;border:2px solid var(--kf-primary-light)"
         alt="<?= htmlspecialchars($p['nome']) ?>">
    <?php else: ?>
    <div style="width:80px;height:80px;border-radius:10px;background:var(--kf-primary-light);display:flex;align-items:center;justify-content:center;font-size:2rem;color:var(--kf-primary)">
      <i class="bi bi-capsule"></i>
    </div>
    <?php endif; ?>
    <div>
      <h1 class="h4 fw-bold mb-1" style="color:var(--kf-primary)"><?= htmlspecialchars($p['nome']) ?></h1>
      <div class="d-flex align-items-center gap-2 flex-wrap">
        <?php if ($p['principio_ativo']): ?>
        <span class="text-muted small"><?= htmlspecialchars($p['principio_ativo']) ?></span>
        <?php endif; ?>
        <?php if ($p['requer_receita']): ?>
        <span class="badge" style="background:#fff3cd;color:#856404;border:1px solid #ffc107;font-size:.7rem">
          <i class="bi bi-file-medical me-1"></i>Receita
        </span>
        <?php endif; ?>
        <?php if ($p['controlado']): ?>
        <span class="badge" style="background:#f8d7da;color:#842029;border:1px solid #dc3545;font-size:.7rem">
          <i class="bi bi-shield-lock me-1"></i>Controlado
        </span>
        <?php endif; ?>
        <?php if (!$p['ativo']): ?>
        <span class="badge bg-secondary">Inactivo</span>
        <?php endif; ?>
      </div>
      <div class="text-muted small mt-1">
        <?= htmlspecialchars($p['categoria_pai_nome'] ?? '') ?>
        <?= $p['categoria_pai_nome'] ? ' › ' : '' ?>
        <?= htmlspecialchars($p['categoria_nome']) ?>
      </div>
    </div>
  </div>
  <div class="d-flex gap-2 flex-wrap">
    <?php if ($isEdit): ?>
    <a href="<?= $APP ?>/produtos/<?= $p['id'] ?>/editar"
       class="btn btn-sm btn-outline-secondary">
      <i class="bi bi-pencil me-1"></i>Editar
    </a>
    <?php endif; ?>
    <a href="<?= $APP ?>/produtos" class="btn btn-sm btn-outline-secondary">
      <i class="bi bi-arrow-left me-1"></i>Voltar
    </a>
  </div>
</div>

<div class="row g-0">
  <!-- Coluna principal -->
  <div class="col-12 col-xl-8 pe-xl-3">

    <!-- Dados gerais -->
    <div class="ficha-section">
      <div class="ficha-section-title"><i class="bi bi-info-circle"></i> Informações Gerais</div>
      <div class="row g-3">
        <div class="col-sm-4">
          <div class="dado-label">Código de barras</div>
          <div class="dado-valor"><?= htmlspecialchars($p['codigo_barras'] ?? '—') ?></div>
        </div>
        <div class="col-sm-4">
          <div class="dado-label">Unidade de medida</div>
          <div class="dado-valor"><?= htmlspecialchars($p['unidade_medida']) ?></div>
        </div>
        <div class="col-sm-4">
          <div class="dado-label">Fornecedor</div>
          <div class="dado-valor"><?= htmlspecialchars($p['fornecedor_nome'] ?? '—') ?></div>
        </div>
        <?php if ($p['descricao']): ?>
        <div class="col-12">
          <div class="dado-label">Descrição</div>
          <div class="dado-valor" style="font-size:.85rem"><?= nl2br(htmlspecialchars($p['descricao'])) ?></div>
        </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Lotes -->
    <div class="ficha-section">
      <div class="ficha-section-title d-flex justify-content-between w-100">
        <span><i class="bi bi-layers"></i> Lotes em Stock</span>
        <?php if ($isEdit): ?>
        <button class="btn btn-sm py-0 px-2" style="background:var(--kf-primary);color:#fff;border:none;font-size:.75rem"
                data-bs-toggle="modal" data-bs-target="#modalLote">
          <i class="bi bi-plus me-1"></i>Adicionar Lote
        </button>
        <?php endif; ?>
      </div>

      <?php if (empty($lotes)): ?>
      <p class="text-muted small text-center py-2 mb-0">Nenhum lote registado</p>
      <?php else: ?>
      <?php foreach ($lotes as $l):
        $dias = (int)$l['dias_validade'];
        if ($l['quantidade'] == 0)   { $cls = 'lote-exp';  $icon = 'bi-x-circle text-secondary'; }
        elseif ($dias < 0)           { $cls = 'lote-exp';  $icon = 'bi-x-circle text-secondary'; }
        elseif ($dias <= 30)         { $cls = 'lote-crit'; $icon = 'bi-exclamation-circle text-danger'; }
        elseif ($dias <= 90)         { $cls = 'lote-warn'; $icon = 'bi-exclamation-triangle text-warning'; }
        else                         { $cls = 'lote-ok';   $icon = 'bi-check-circle text-success'; }
      ?>
      <div class="lote-row <?= $cls ?>">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
          <div class="d-flex align-items-center gap-2">
            <i class="bi <?= $icon ?>"></i>
            <div>
              <div class="fw-semibold small"><?= htmlspecialchars($l['numero_lote']) ?></div>
              <div class="text-muted" style="font-size:.75rem">
                Validade: <?= date('d/m/Y', strtotime($l['validade'])) ?>
                <?php if ($dias >= 0): ?>
                (<?= $dias ?> dias)
                <?php else: ?>
                <span class="text-danger fw-bold">(VENCIDO)</span>
                <?php endif; ?>
              </div>
            </div>
          </div>
          <div class="fw-bold"><?= $l['quantidade'] ?> <?= htmlspecialchars($p['unidade_medida']) ?></div>
        </div>
      </div>
      <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <!-- Histórico de movimentos -->
    <div class="ficha-section">
      <div class="ficha-section-title"><i class="bi bi-clock-history"></i> Histórico de Stock</div>
      <?php if (empty($movimentos)): ?>
      <p class="text-muted small text-center py-2 mb-0">Nenhum movimento registado</p>
      <?php else: ?>
      <div style="max-height:280px;overflow-y:auto">
        <?php foreach ($movimentos as $m):
          $tipoMap = [
            'entrada'              => ['success', 'bi-arrow-down-circle', '+'],
            'saida'                => ['danger',  'bi-arrow-up-circle',   '−'],
            'ajuste_positivo'      => ['info',    'bi-plus-circle',       '+'],
            'ajuste_negativo'      => ['warning', 'bi-dash-circle',       '−'],
            'devolucao_cliente'    => ['secondary','bi-arrow-counterclockwise', '+'],
            'devolucao_fornecedor' => ['secondary','bi-arrow-counterclockwise', '−'],
            'perda'                => ['danger',  'bi-trash',             '−'],
            'vencimento'           => ['secondary','bi-calendar-x',       '−'],
          ];
          [$mc, $mi, $sinal] = $tipoMap[$m['tipo']] ?? ['secondary','bi-circle','?'];
        ?>
        <div class="d-flex gap-2 align-items-start pb-2 mb-2 border-bottom" style="font-size:.8rem">
          <div class="mov-dot mt-1 bg-<?= $mc ?>"></div>
          <div class="flex-fill">
            <div class="d-flex justify-content-between">
              <span class="fw-semibold">
                <?= ucfirst(str_replace('_',' ',$m['tipo'])) ?>
                <?php if ($m['numero_lote']): ?>
                <span class="text-muted fw-normal">· Lote <?= htmlspecialchars($m['numero_lote']) ?></span>
                <?php endif; ?>
              </span>
              <span class="text-<?= $mc ?> fw-bold"><?= $sinal ?><?= $m['quantidade'] ?></span>
            </div>
            <div class="text-muted">
              <?= $m['quantidade_anterior'] ?> → <?= $m['quantidade_posterior'] ?>
              <?php if ($m['referencia']): ?> · <?= htmlspecialchars($m['referencia']) ?><?php endif; ?>
              <?php if ($m['usuario_nome']): ?> · <?= htmlspecialchars($m['usuario_nome']) ?><?php endif; ?>
            </div>
            <div class="text-muted" style="font-size:.7rem"><?= date('d/m/Y H:i', strtotime($m['criado_em'])) ?></div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>

  </div><!-- /col principal -->

  <!-- Coluna lateral -->
  <div class="col-12 col-xl-4">

    <!-- Preços -->
    <div class="ficha-section">
      <div class="ficha-section-title"><i class="bi bi-currency-exchange"></i> Preços</div>
      <?php
        $fator      = max(1, (float)($p['fator_conversao'] ?? 1));
        $uCompra    = $p['unidade_compra'] ?? 'caixa';
        $uVenda     = $p['unidade_venda']  ?? 'unidade';
        $custoUnit  = $p['preco_compra'] > 0 ? $p['preco_compra'] / $fator : 0;
        $lucroUnit  = $p['preco_venda'] - $custoUnit;
        $lucroCaixa = $lucroUnit * $fator;
        $margem     = $p['preco_venda'] > 0 ? round($lucroUnit / $p['preco_venda'] * 100, 1) : 0;
      ?>
      <div class="row g-2 text-center">
        <div class="col-6">
          <div class="dado-label">Compra <small class="text-muted">(por <?= htmlspecialchars($uCompra) ?>)</small></div>
          <div class="fw-bold" style="font-size:1.1rem">MT <?= number_format((float)$p['preco_compra'],2,',','.') ?></div>
          <?php if ($fator > 1): ?>
          <div class="text-muted" style="font-size:11px">MT <?= number_format($custoUnit,2,',','.') ?>/<?= htmlspecialchars($uVenda) ?></div>
          <?php endif; ?>
        </div>
        <div class="col-6">
          <div class="dado-label">Venda <small class="text-muted">(por <?= htmlspecialchars($uVenda) ?>)</small></div>
          <div class="fw-bold" style="font-size:1.1rem;color:var(--kf-primary)">MT <?= number_format((float)$p['preco_venda'],2,',','.') ?></div>
        </div>
        <?php if ($p['preco_compra'] > 0): ?>
        <div class="col-12 mt-1 pt-2" style="border-top:1px solid #e9ecef">
          <div class="row g-1 text-center">
            <div class="col-4">
              <div class="dado-label" style="font-size:10px">Margem</div>
              <div class="fw-bold <?= $margem >= 0 ? 'text-success' : 'text-danger' ?>"><?= $margem ?>%</div>
            </div>
            <div class="col-4">
              <div class="dado-label" style="font-size:10px">Lucro/<?= htmlspecialchars($uVenda) ?></div>
              <div class="fw-bold <?= $lucroUnit >= 0 ? 'text-success' : 'text-danger' ?>">MT <?= number_format($lucroUnit,2,',','.') ?></div>
            </div>
            <div class="col-4">
              <div class="dado-label" style="font-size:10px">Lucro/<?= htmlspecialchars($uCompra) ?></div>
              <div class="fw-bold <?= $lucroCaixa >= 0 ? 'text-success' : 'text-danger' ?>">MT <?= number_format($lucroCaixa,2,',','.') ?></div>
            </div>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Stock -->
    <div class="ficha-section">
      <div class="ficha-section-title"><i class="bi bi-box-seam"></i> Stock</div>
      <?php
      $stockActual = (int)$p['estoque_actual'];
      $stockMin    = (int)$p['estoque_min'];
      $pct = $stockMin > 0 ? min(100, round($stockActual / $stockMin * 100)) : 100;
      if ($stockActual == 0)            { $cor = 'danger';  $txt = 'Sem stock'; }
      elseif ($stockActual < $stockMin) { $cor = 'warning'; $txt = 'Stock baixo'; }
      else                              { $cor = 'success'; $txt = 'Stock normal'; }
      ?>
      <div class="text-center mb-3">
        <div class="fw-bold" style="font-size:2rem;color:var(--kf-<?= $cor === 'warning' ? 'primary' : $cor ?>)">
          <?= $stockActual ?>
        </div>
        <div class="text-muted small"><?= htmlspecialchars($p['unidade_medida']) ?> em stock</div>
        <span class="badge bg-<?= $cor ?>-subtle text-<?= $cor ?> border border-<?= $cor ?>-subtle rounded-pill mt-1">
          <?= $txt ?>
        </span>
      </div>
      <div class="progress mb-2" style="height:8px">
        <div class="progress-bar bg-<?= $cor ?>" style="width:<?= $pct ?>%"></div>
      </div>
      <div class="d-flex justify-content-between" style="font-size:.75rem;color:#999">
        <span>0</span>
        <span>Mínimo: <?= $stockMin ?></span>
      </div>

      <?php if ($diasValidade !== null && $diasValidade <= 90): ?>
      <div class="alert <?= $diasValidade <= 30 ? 'alert-danger' : 'alert-warning' ?> py-2 px-3 mt-3 small mb-0">
        <i class="bi bi-calendar-x me-1"></i>
        Lote mais próximo vence em <strong><?= $diasValidade ?> dias</strong>
      </div>
      <?php endif; ?>
    </div>

    <!-- Resumo -->
    <div class="ficha-section">
      <div class="ficha-section-title"><i class="bi bi-info-circle"></i> Resumo</div>
      <div style="font-size:.82rem" class="row g-2">
        <div class="col-12">
          <div class="text-muted">Registado em</div>
          <div class="fw-semibold"><?= date('d/m/Y H:i', strtotime($p['criado_em'])) ?></div>
        </div>
        <div class="col-12">
          <div class="text-muted">Última actualização</div>
          <div class="fw-semibold"><?= date('d/m/Y H:i', strtotime($p['actualizado_em'])) ?></div>
        </div>
      </div>
    </div>

  </div><!-- /col lateral -->
</div>

<!-- Modal: Adicionar Lote -->
<?php if ($isEdit): ?>
<div class="modal fade" id="modalLote" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" action="<?= $APP ?>/produtos/<?= $p['id'] ?>/lote">
        <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
        <div class="modal-header" style="background:var(--kf-primary-light)">
          <h5 class="modal-title" style="color:var(--kf-primary)">
            <i class="bi bi-layers me-2"></i>Adicionar Lote
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label small">Número do lote <span class="text-danger">*</span></label>
            <input type="text" name="numero_lote" class="form-control form-control-sm" required
                   placeholder="ex: LOT-2025-001">
          </div>
          <div class="row g-3">
            <div class="col-6">
              <label class="form-label small">Quantidade <span class="text-danger">*</span></label>
              <input type="number" name="quantidade" class="form-control form-control-sm" required min="1">
            </div>
            <div class="col-6">
              <label class="form-label small">Validade <span class="text-danger">*</span></label>
              <input type="date" name="validade" class="form-control form-control-sm" required
                     min="<?= date('Y-m-d') ?>">
            </div>
          </div>
          <div class="mt-3">
            <label class="form-label small">Observações</label>
            <input type="text" name="observacoes" class="form-control form-control-sm"
                   placeholder="Opcional">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-sm px-4" style="background:var(--kf-primary);color:#fff;border:none">
            <i class="bi bi-check-lg me-1"></i>Adicionar
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php endif; ?>
