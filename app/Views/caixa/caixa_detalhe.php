<?php
$APP  = $_ENV['APP_URL'] ?? '';
$s    = $sessao;
$isAdmin = in_array($_SESSION['perfil'] ?? '', ['admin', 'farmaceutico']);
$formas = ['dinheiro'=>'Dinheiro','mpesa'=>'M-Pesa','emola'=>'e-Mola',
           'cartao_debito'=>'Débito','cartao_credito'=>'Crédito','transferencia'=>'Transferência'];
$tiposMov = [
  'venda'      => ['success','bi-cart-check','Venda',true],
  'entrada'    => ['success','bi-arrow-down-circle','Entrada',true],
  'suprimento' => ['info','bi-plus-circle','Suprimento',true],
  'devolucao'  => ['warning','bi-arrow-counterclockwise','Devolução',true],
  'sangria'    => ['danger','bi-arrow-up-circle','Sangria',false],
  'saida'      => ['danger','bi-dash-circle','Saída',false],
];
$duracao = '';
if ($s['fechado_em']) {
  $seg  = strtotime($s['fechado_em']) - strtotime($s['aberto_em']);
  $h    = floor($seg/3600); $m = floor(($seg%3600)/60);
  $duracao = "{$h}h {$m}min";
}
?>

<style>
@media print {
  .no-print { display:none!important; }
  .card { box-shadow:none!important; border:1px solid #ddd!important; }
}
.mov-row { padding:8px 14px; border-bottom:1px solid #f0f0f0; font-size:.82rem; }
.mov-row:last-child { border-bottom:none; }
</style>

<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2 no-print">
  <div>
    <h1 class="h4 fw-bold mb-0" style="color:var(--kf-primary)">
      Sessão de Caixa #<?= $s['id'] ?>
    </h1>
    <p class="text-muted small mb-0">
      <?= date('d/m/Y H:i', strtotime($s['aberto_em'])) ?>
      <?= $s['fechado_em'] ? ' → '.date('d/m/Y H:i', strtotime($s['fechado_em'])) : '' ?>
      <?= $duracao ? " ($duracao)" : '' ?>
    </p>
  </div>
  <div class="d-flex gap-2">
    <a href="<?= $APP ?>/caixa/<?= $s['id'] ?>/relatorio" target="_blank"
       class="btn btn-sm btn-success">
      <i class="bi bi-file-earmark-bar-graph me-1"></i>Relatório de Fecho
    </a>
    <button class="btn btn-outline-secondary btn-sm" onclick="window.print()">
      <i class="bi bi-printer me-1"></i>Imprimir
    </button>
    <a href="<?= $APP ?>/caixa" class="btn btn-outline-secondary btn-sm">
      <i class="bi bi-arrow-left me-1"></i>Voltar
    </a>
  </div>
</div>

<?php if (!empty($_GET['relatorio'])): ?>
<script>
  document.addEventListener('DOMContentLoaded', function(){
    window.open('<?= $APP ?>/caixa/<?= $s['id'] ?>/relatorio', '_blank');
  });
</script>
<?php endif; ?>

<div class="row g-4">

  <!-- Coluna esquerda: resumo financeiro -->
  <div class="col-12 col-md-5">

    <!-- Info geral -->
    <div class="card border-0 shadow-sm mb-3">
      <div class="card-header bg-white py-3 fw-semibold" style="font-size:.85rem;color:var(--kf-primary)">
        <i class="bi bi-info-circle me-2"></i>Resumo da Sessão
      </div>
      <div class="card-body p-0">
        <?php
        $linhas = [
          ['Operador',       $s['usuario_nome']],
          ['Estado',         $s['status'] === 'aberto' ? '<span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">Aberta</span>' : '<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill">Fechada</span>'],
          ['Abertura',       date('d/m/Y H:i', strtotime($s['aberto_em']))],
          ['Fecho',          $s['fechado_em'] ? date('d/m/Y H:i', strtotime($s['fechado_em'])) : '—'],
          ['Duração',        $duracao ?: '—'],
        ];
        foreach ($linhas as [$lbl,$val]):
        ?>
        <div class="d-flex justify-content-between px-3 py-2 border-bottom" style="font-size:.83rem">
          <span class="text-muted"><?= $lbl ?></span>
          <span class="fw-semibold"><?= is_string($val) && str_contains($val,'badge') ? $val : htmlspecialchars((string)$val) ?></span>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Balanço -->
    <div class="card border-0 shadow-sm mb-3">
      <div class="card-header bg-white py-3 fw-semibold" style="font-size:.85rem;color:var(--kf-primary)">
        <i class="bi bi-calculator me-2"></i>Balanço
      </div>
      <div class="card-body">
        <div class="d-flex justify-content-between mb-2" style="font-size:.85rem">
          <span class="text-muted">Fundo inicial</span>
          <span>MT <?= number_format((float)$s['saldo_inicial'],2,',','.') ?></span>
        </div>
        <div class="d-flex justify-content-between mb-2" style="font-size:.85rem">
          <span class="text-muted">Total entradas</span>
          <span class="text-success">+MT <?= number_format((float)$s['total_entradas'],2,',','.') ?></span>
        </div>
        <div class="d-flex justify-content-between mb-3" style="font-size:.85rem">
          <span class="text-muted">Total saídas</span>
          <span class="text-danger">-MT <?= number_format((float)$s['total_saidas'],2,',','.') ?></span>
        </div>
        <div class="d-flex justify-content-between fw-bold border-top pt-2 mb-2">
          <span>Saldo esperado</span>
          <span style="color:var(--kf-primary)">MT <?= number_format((float)$s['saldo_esperado'],2,',','.') ?></span>
        </div>
        <?php if ($s['saldo_final'] !== null): ?>
        <div class="d-flex justify-content-between fw-bold mb-2">
          <span>Saldo contado</span>
          <span>MT <?= number_format((float)$s['saldo_final'],2,',','.') ?></span>
        </div>
        <?php $dif = (float)$s['diferenca']; ?>
        <div class="p-2 rounded text-center mt-2"
             style="background:<?= abs($dif)<0.01 ? '#d1e7dd' : ($dif>0 ? '#d1e7dd' : '#f8d7da') ?>">
          <?php if (abs($dif) < 0.01): ?>
          <div class="fw-bold text-success"><i class="bi bi-check-circle me-1"></i>Sem diferença</div>
          <?php elseif ($dif > 0): ?>
          <div class="fw-bold text-success"><i class="bi bi-arrow-up me-1"></i>Sobra MT <?= number_format(abs($dif),2,',','.') ?></div>
          <?php else: ?>
          <div class="fw-bold text-danger"><i class="bi bi-arrow-down me-1"></i>Falta MT <?= number_format(abs($dif),2,',','.') ?></div>
          <?php endif; ?>
        </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Por forma de pagamento -->
    <?php if (!empty($pagamentos)): ?>
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white py-3 fw-semibold" style="font-size:.85rem;color:var(--kf-primary)">
        <i class="bi bi-pie-chart me-2"></i>Vendas por Pagamento
      </div>
      <div class="card-body p-0">
        <?php foreach ($pagamentos as $pg): ?>
        <div class="d-flex justify-content-between px-3 py-2 border-bottom" style="font-size:.83rem">
          <div>
            <div class="fw-semibold"><?= $formas[$pg['forma_pagamento']] ?? $pg['forma_pagamento'] ?></div>
            <div class="text-muted"><?= $pg['total_vendas'] ?> venda(s)</div>
          </div>
          <div class="fw-bold text-success">MT <?= number_format((float)$pg['total_valor'],2,',','.') ?></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

  </div>

  <!-- Coluna direita: movimentos -->
  <div class="col-12 col-md-7">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
        <span class="fw-semibold" style="font-size:.85rem;color:var(--kf-primary)">
          <i class="bi bi-list-ul me-2"></i>Movimentos
          <span class="badge rounded-pill ms-1" style="background:var(--kf-primary-light);color:var(--kf-primary)">
            <?= count($movimentos) ?>
          </span>
        </span>
      </div>
      <div class="card-body p-0" style="max-height:600px;overflow-y:auto">
        <?php if (empty($movimentos)): ?>
        <div class="text-center py-4 text-muted small">Nenhum movimento.</div>
        <?php else: ?>
        <?php foreach ($movimentos as $m):
          [$mc,$mi,$ml,$isEnt] = $tiposMov[$m['tipo']] ?? ['secondary','bi-circle',$m['tipo'],true];
        ?>
        <div class="mov-row d-flex align-items-start gap-2">
          <i class="bi <?= $mi ?> text-<?= $mc ?> mt-1 flex-shrink-0"></i>
          <div class="flex-fill">
            <div class="d-flex justify-content-between">
              <span class="fw-semibold"><?= $ml ?>
                <?php if ($m['numero_venda']): ?>
                <span class="text-muted fw-normal">· <?= htmlspecialchars($m['numero_venda']) ?></span>
                <?php endif; ?>
              </span>
              <span class="fw-bold <?= $isEnt ? 'text-success' : 'text-danger' ?>">
                <?= $isEnt ? '+' : '-' ?>MT <?= number_format((float)$m['valor'],2,',','.') ?>
              </span>
            </div>
            <div class="text-muted" style="font-size:.75rem">
              <?= htmlspecialchars($m['descricao'] ?? '') ?>
              · <?= date('d/m H:i', strtotime($m['criado_em'])) ?>
              · <?= htmlspecialchars($m['usuario_nome'] ?? '') ?>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>

</div>
