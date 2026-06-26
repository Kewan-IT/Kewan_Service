<?php
$APP    = $_ENV['APP_URL'] ?? '';
$csrf   = $csrf_token ?? '';
$isAdmin = in_array($_SESSION['perfil'] ?? '', ['admin', 'farmaceutico']);

$formas = ['dinheiro'=>'Dinheiro','mpesa'=>'M-Pesa','emola'=>'e-Mola',
           'cartao_debito'=>'Débito','cartao_credito'=>'Crédito','transferencia'=>'Transferência'];
$tiposMov = [
  'venda'      => ['success', 'bi-cart-check',     'Venda'],
  'entrada'    => ['success', 'bi-arrow-down-circle','Entrada'],
  'suprimento' => ['info',    'bi-plus-circle',     'Suprimento'],
  'devolucao'  => ['warning', 'bi-arrow-counterclockwise','Devolução'],
  'sangria'    => ['danger',  'bi-arrow-up-circle', 'Sangria'],
  'saida'      => ['danger',  'bi-dash-circle',     'Saída'],
];
?>

<style>
.caixa-card { border-radius:12px; padding:20px; color:#fff; }
.mov-row { padding:8px 14px; border-bottom:1px solid #f0f0f0; font-size:.82rem; }
.mov-row:last-child { border-bottom:none; }
.mov-dot { width:8px;height:8px;border-radius:50%;flex-shrink:0;margin-top:4px; }
</style>

<!-- Cabeçalho -->
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
  <div>
    <h1 class="h4 fw-bold mb-0" style="color:var(--kf-primary)">
      <i class="bi bi-cash-register me-2"></i>Caixa
    </h1>
    <p class="text-muted small mb-0">Gestão do fundo de caixa</p>
  </div>
  <?php if ($caixaAberta): ?>
  <div class="d-flex gap-2">
    <button class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#modalMovimento">
      <i class="bi bi-plus-slash-minus me-1"></i>Movimento
    </button>
    <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#modalFechar">
      <i class="bi bi-lock me-1"></i>Fechar Caixa
    </button>
  </div>
  <?php else: ?>
  <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalAbrir">
    <i class="bi bi-unlock me-1"></i>Abrir Caixa
  </button>
  <?php endif; ?>
</div>

<?php if (!empty($flash_sucesso)): ?>
<span id="kf-flash-sucesso" data-msg="<?= htmlspecialchars($flash_sucesso) ?>" hidden></span>
<?php endif; ?>
<?php if (!empty($flash_erro)): ?>
<span id="kf-flash-erro" data-msg="<?= htmlspecialchars($flash_erro) ?>" hidden></span>
<?php endif; ?>

<!-- Estado da caixa -->
<?php if ($caixaAberta): ?>

<!-- CAIXA ABERTA -->
<div class="row g-3 mb-4">
  <!-- Card estado -->
  <div class="col-12 col-md-4">
    <div class="caixa-card shadow" style="background:linear-gradient(135deg,var(--kf-primary),#0d5c3a)">
      <div class="d-flex align-items-center gap-3 mb-3">
        <div class="rounded-circle bg-white bg-opacity-25 d-flex align-items-center justify-content-center"
             style="width:48px;height:48px">
          <i class="bi bi-unlock-fill fs-4 text-white"></i>
        </div>
        <div>
          <div class="fw-bold fs-5">Caixa Aberta</div>
          <div class="opacity-75 small"><?= date('d/m/Y H:i', strtotime($caixaAberta['aberto_em'])) ?></div>
        </div>
      </div>
      <div class="border-top border-white border-opacity-25 pt-3">
        <div class="d-flex justify-content-between mb-1 small">
          <span class="opacity-75">Operador</span>
          <span class="fw-semibold"><?= htmlspecialchars($caixaAberta['usuario_nome']) ?></span>
        </div>
        <div class="d-flex justify-content-between mb-1 small">
          <span class="opacity-75">Fundo inicial</span>
          <span class="fw-semibold">MT <?= number_format((float)$caixaAberta['saldo_inicial'],2,',','.') ?></span>
        </div>
        <?php
        $saldoEsp = (float)$caixaAberta['saldo_inicial'] + (float)$caixaAberta['total_entradas'] - (float)$caixaAberta['total_saidas'];
        ?>
        <div class="d-flex justify-content-between small border-top border-white border-opacity-25 pt-2 mt-2">
          <span class="opacity-75">Saldo esperado</span>
          <span class="fw-bold fs-6">MT <?= number_format($saldoEsp,2,',','.') ?></span>
        </div>
      </div>
    </div>
  </div>

  <!-- Cards totais -->
  <div class="col-12 col-md-8">
    <div class="row g-3 h-100">
      <?php
      $totais = [
        ['Vendas', $caixaAberta['total_vendas'], 'bi-cart-check', '#198754'],
        ['Entradas', $caixaAberta['total_entradas'], 'bi-arrow-down-circle', '#0d6efd'],
        ['Saídas', $caixaAberta['total_saidas'], 'bi-arrow-up-circle', '#dc3545'],
        ['Saldo Líquido', max(0,$saldoEsp), 'bi-wallet2', '#1a7f5a'],
      ];
      foreach ($totais as [$lbl,$val,$icon,$cor]):
      ?>
      <div class="col-6">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body d-flex align-items-center gap-3 p-3">
            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                 style="width:40px;height:40px;background:<?= $cor ?>18">
              <i class="bi <?= $icon ?> fs-5" style="color:<?= $cor ?>"></i>
            </div>
            <div>
              <div class="fw-bold" style="font-size:1rem;color:<?= $cor ?>">
                MT <?= number_format((float)$val,2,',','.') ?>
              </div>
              <div class="text-muted small"><?= $lbl ?></div>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<div class="row g-3 mb-4">

  <!-- Resumo por pagamento -->
  <?php if (!empty($pagamentos)): ?>
  <div class="col-12 col-md-5">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-white py-3 fw-semibold" style="font-size:.85rem;color:var(--kf-primary)">
        <i class="bi bi-pie-chart me-2"></i>Vendas por Pagamento
      </div>
      <div class="card-body p-0">
        <?php foreach ($pagamentos as $pg): ?>
        <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom" style="font-size:.82rem">
          <div>
            <div class="fw-semibold"><?= $formas[$pg['forma_pagamento']] ?? $pg['forma_pagamento'] ?></div>
            <div class="text-muted"><?= $pg['total_vendas'] ?> venda(s)</div>
          </div>
          <div class="fw-bold text-success">MT <?= number_format((float)$pg['total_valor'],2,',','.') ?></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <!-- Últimos movimentos -->
  <div class="col-12 col-md-<?= !empty($pagamentos) ? '7' : '12' ?>">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
        <span class="fw-semibold" style="font-size:.85rem;color:var(--kf-primary)">
          <i class="bi bi-clock-history me-2"></i>Últimos Movimentos
        </span>
        <a href="<?= $APP ?>/caixa/<?= $caixaAberta['id'] ?>" class="btn btn-outline-secondary btn-sm py-0 px-2" style="font-size:.75rem">
          Ver todos
        </a>
      </div>
      <div class="card-body p-0" style="max-height:280px;overflow-y:auto">
        <?php if (empty($movimentos)): ?>
        <div class="text-center py-4 text-muted small">Nenhum movimento ainda.</div>
        <?php else: ?>
        <?php foreach ($movimentos as $m):
          [$mc,$mi,$ml] = $tiposMov[$m['tipo']] ?? ['secondary','bi-circle',$m['tipo']];
          $isEntrada = in_array($m['tipo'], ['venda','entrada','suprimento','devolucao']);
        ?>
        <div class="mov-row d-flex align-items-start gap-2">
          <div class="mov-dot mt-1 bg-<?= $mc ?>"></div>
          <div class="flex-fill">
            <div class="d-flex justify-content-between">
              <span class="fw-semibold"><?= $ml ?>
                <?php if ($m['numero_venda']): ?>
                <span class="text-muted fw-normal">· <?= htmlspecialchars($m['numero_venda']) ?></span>
                <?php endif; ?>
              </span>
              <span class="fw-bold <?= $isEntrada ? 'text-success' : 'text-danger' ?>">
                <?= $isEntrada ? '+' : '-' ?>MT <?= number_format((float)$m['valor'],2,',','.') ?>
              </span>
            </div>
            <div class="text-muted" style="font-size:.75rem">
              <?= htmlspecialchars($m['descricao'] ?? '') ?>
              · <?= date('H:i', strtotime($m['criado_em'])) ?>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>

</div>

<?php else: ?>

<!-- CAIXA FECHADA -->
<div class="card border-0 shadow-sm mb-4">
  <div class="card-body text-center py-5">
    <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3"
         style="width:72px;height:72px;background:#f8d7da">
      <i class="bi bi-lock-fill text-danger" style="font-size:2rem"></i>
    </div>
    <h5 class="fw-bold text-danger mb-1">Caixa Fechada</h5>
    <p class="text-muted small mb-3">Abra a caixa para iniciar as operações do dia.</p>
    <button class="btn btn-success btn-lg" data-bs-toggle="modal" data-bs-target="#modalAbrir">
      <i class="bi bi-unlock me-2"></i>Abrir Caixa Agora
    </button>
  </div>
</div>

<?php endif; ?>

<!-- Histórico de sessões -->
<div class="card border-0 shadow-sm">
  <div class="card-header bg-white py-3 fw-semibold" style="font-size:.85rem;color:var(--kf-primary)">
    <i class="bi bi-calendar3 me-2"></i>Histórico de Sessões
  </div>
  <div class="card-body p-0">
    <?php if (empty($historico['data'])): ?>
    <div class="text-center py-4 text-muted small">Nenhuma sessão registada.</div>
    <?php else: ?>
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0" style="font-size:.82rem">
        <thead style="background:var(--kf-primary-light)">
          <tr>
            <th class="ps-3 py-2 fw-semibold" style="color:var(--kf-primary)">#</th>
            <th class="py-2 fw-semibold" style="color:var(--kf-primary)">Abertura</th>
            <th class="py-2 fw-semibold d-none d-md-table-cell" style="color:var(--kf-primary)">Fecho</th>
            <th class="py-2 fw-semibold d-none d-md-table-cell" style="color:var(--kf-primary)">Operador</th>
            <th class="py-2 fw-semibold text-end" style="color:var(--kf-primary)">Vendas</th>
            <th class="py-2 fw-semibold text-end" style="color:var(--kf-primary)">Saldo final</th>
            <th class="py-2 fw-semibold" style="color:var(--kf-primary)">Estado</th>
            <th class="pe-3 py-2"></th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($historico['data'] as $s): ?>
        <tr>
          <td class="ps-3 text-muted">#<?= $s['id'] ?></td>
          <td><?= date('d/m/Y H:i', strtotime($s['aberto_em'])) ?></td>
          <td class="d-none d-md-table-cell text-muted">
            <?= $s['fechado_em'] ? date('d/m/Y H:i', strtotime($s['fechado_em'])) : '—' ?>
          </td>
          <td class="d-none d-md-table-cell"><?= htmlspecialchars($s['usuario_nome']) ?></td>
          <td class="text-end fw-semibold text-success">MT <?= number_format((float)$s['total_vendas'],2,',','.') ?></td>
          <td class="text-end fw-bold" style="color:var(--kf-primary)">
            <?= $s['saldo_final'] !== null ? 'MT '.number_format((float)$s['saldo_final'],2,',','.') : '—' ?>
          </td>
          <td>
            <?php if ($s['status'] === 'aberto'): ?>
            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2">Aberta</span>
            <?php else: ?>
            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2">Fechada</span>
            <?php endif; ?>
          </td>
          <td class="pe-3">
            <a href="<?= $APP ?>/caixa/<?= $s['id'] ?>" class="btn btn-sm btn-outline-secondary py-0 px-2">
              <i class="bi bi-eye"></i>
            </a>
          </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php
    // Reutilizar partial de paginação com $historico como $paginacao
    $paginacao = $historico;
    include __DIR__ . '/../partials/paginacao.php';
    unset($paginacao);
    ?>
    <?php endif; ?>
  </div>
</div>

<!-- ══════════════════ MODAIS ══════════════════ -->

<!-- Modal Abrir Caixa -->
<div class="modal fade" id="modalAbrir" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" action="<?= $APP ?>/caixa/abrir">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
        <div class="modal-header" style="background:var(--kf-primary-light)">
          <h5 class="modal-title fw-bold" style="color:var(--kf-primary)">
            <i class="bi bi-unlock me-2"></i>Abrir Caixa
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label fw-semibold">Fundo inicial (MZN)</label>
            <input type="number" name="saldo_inicial" class="form-control form-control-lg fw-bold text-end"
                   min="0" step="0.01" value="0" required placeholder="0,00">
            <div class="form-text">Valor em dinheiro físico disponível para troco.</div>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Observações</label>
            <textarea name="observacoes" class="form-control" rows="2" placeholder="Opcional..."></textarea>
          </div>
          <div class="alert alert-info py-2 px-3 small mb-0">
            <i class="bi bi-info-circle me-1"></i>
            A caixa será aberta em nome de <strong><?= htmlspecialchars($_SESSION['usuario_nome'] ?? '') ?></strong>
            às <?= date('H:i') ?>.
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-success px-4">
            <i class="bi bi-unlock me-1"></i>Abrir Caixa
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Fechar Caixa -->
<?php if ($caixaAberta): ?>
<div class="modal fade" id="modalFechar" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" action="<?= $APP ?>/caixa/fechar">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
        <div class="modal-header bg-danger bg-opacity-10">
          <h5 class="modal-title fw-bold text-danger">
            <i class="bi bi-lock me-2"></i>Fechar Caixa
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <!-- Resumo antes de fechar -->
          <div class="p-3 bg-light rounded mb-3" style="font-size:.85rem">
            <div class="d-flex justify-content-between mb-1">
              <span class="text-muted">Fundo inicial</span>
              <span>MT <?= number_format((float)$caixaAberta['saldo_inicial'],2,',','.') ?></span>
            </div>
            <div class="d-flex justify-content-between mb-1">
              <span class="text-muted">Total entradas</span>
              <span class="text-success">+MT <?= number_format((float)$caixaAberta['total_entradas'],2,',','.') ?></span>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <span class="text-muted">Total saídas</span>
              <span class="text-danger">-MT <?= number_format((float)$caixaAberta['total_saidas'],2,',','.') ?></span>
            </div>
            <div class="d-flex justify-content-between fw-bold border-top pt-2">
              <span>Saldo esperado</span>
              <span style="color:var(--kf-primary)">MT <?= number_format($saldoEsp ?? 0,2,',','.') ?></span>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Saldo contado (MZN) <span class="text-danger">*</span></label>
            <input type="number" name="saldo_final" id="saldo-contado"
                   class="form-control form-control-lg fw-bold text-end"
                   min="0" step="0.01" required placeholder="0,00"
                   oninput="calcDif()">
            <div id="dif-box" class="mt-2 p-2 rounded text-center d-none" style="font-size:.85rem">
              <span id="dif-label"></span>
            </div>
          </div>
          <div>
            <label class="form-label fw-semibold">Observações</label>
            <textarea name="observacoes" class="form-control" rows="2" placeholder="Opcional..."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-danger px-4">
            <i class="bi bi-lock me-1"></i>Fechar Caixa
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Movimento Manual -->
<div class="modal fade" id="modalMovimento" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" action="<?= $APP ?>/caixa/movimento">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
        <div class="modal-header" style="background:var(--kf-primary-light)">
          <h5 class="modal-title fw-bold" style="color:var(--kf-primary)">
            <i class="bi bi-plus-slash-minus me-2"></i>Movimento Manual
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label fw-semibold">Tipo de Movimento <span class="text-danger">*</span></label>
            <div class="row g-2">
              <?php foreach ([
                ['sangria',    'Sangria',    'bi-arrow-up-circle',   'danger'],
                ['suprimento', 'Suprimento', 'bi-plus-circle',       'success'],
                ['entrada',    'Entrada',    'bi-arrow-down-circle', 'primary'],
                ['saida',      'Saída',      'bi-dash-circle',       'warning'],
              ] as [$val,$lbl,$ico,$cor]): ?>
              <div class="col-6">
                <label class="d-flex align-items-center gap-2 p-2 border rounded cursor-pointer"
                       style="cursor:pointer">
                  <input type="radio" name="tipo" value="<?= $val ?>" required>
                  <i class="bi <?= $ico ?> text-<?= $cor ?>"></i>
                  <span class="small fw-semibold"><?= $lbl ?></span>
                </label>
              </div>
              <?php endforeach; ?>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Valor (MZN) <span class="text-danger">*</span></label>
            <input type="number" name="valor" class="form-control form-control-lg fw-bold text-end"
                   min="0.01" step="0.01" required placeholder="0,00">
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Descrição <span class="text-danger">*</span></label>
            <input type="text" name="descricao" class="form-control" required
                   placeholder="Ex: Sangria para banco, pagamento de despesa...">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-warning px-4 fw-bold">
            <i class="bi bi-check-lg me-1"></i>Registar
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php endif; ?>

<script>
function calcDif() {
  const esp   = <?= $saldoEsp ?? 0 ?>;
  const cont  = parseFloat(document.getElementById('saldo-contado').value) || 0;
  const dif   = cont - esp;
  const box   = document.getElementById('dif-box');
  const lbl   = document.getElementById('dif-label');
  if (cont > 0) {
    box.classList.remove('d-none');
    if (Math.abs(dif) < 0.01) {
      box.style.background='#d1e7dd'; lbl.textContent='✓ Sem diferença';
      lbl.className='fw-bold text-success';
    } else if (dif > 0) {
      box.style.background='#d1e7dd';
      lbl.innerHTML='<i class="bi bi-arrow-up me-1"></i>Sobra MT '+Math.abs(dif).toLocaleString('pt-MZ',{minimumFractionDigits:2});
      lbl.className='fw-bold text-success';
    } else {
      box.style.background='#f8d7da';
      lbl.innerHTML='<i class="bi bi-arrow-down me-1"></i>Falta MT '+Math.abs(dif).toLocaleString('pt-MZ',{minimumFractionDigits:2});
      lbl.className='fw-bold text-danger';
    }
  } else { box.classList.add('d-none'); }
}
</script>
