<?php
// app/Views/clientes/show.php
$APP = $_ENV['APP_URL'] ?? '';
$c   = $cliente;
?>

<style>
.ficha-section { background:#fff; border-radius:10px; padding:1.25rem 1.5rem; margin-bottom:1rem; box-shadow:0 1px 4px rgba(0,0,0,.07); }
.ficha-section-title { font-weight:600; font-size:.8rem; text-transform:uppercase; letter-spacing:.05em; color:var(--kf-primary); border-bottom:1px solid var(--kf-primary-light); padding-bottom:.5rem; margin-bottom:1rem; display:flex; align-items:center; gap:.5rem; }
.dado-label { font-size:.72rem; text-transform:uppercase; letter-spacing:.04em; color:#999; font-weight:500; margin-bottom:1px; }
.dado-valor { font-size:.9rem; color:#1a2e27; }
.avatar-lg { width:72px; height:72px; border-radius:50%; background:var(--kf-primary); display:flex; align-items:center; justify-content:center; font-size:2rem; color:#fff; font-weight:700; flex-shrink:0; }
</style>

<!-- Cabeçalho -->
<div class="d-flex align-items-start justify-content-between mb-4 flex-wrap gap-2">
  <div class="d-flex gap-3 align-items-center">
    <div class="avatar-lg"><?= mb_strtoupper(mb_substr($c['nome'], 0, 1)) ?></div>
    <div>
      <h1 class="h4 fw-bold mb-1" style="color:var(--kf-primary)"><?= htmlspecialchars($c['nome']) ?></h1>
      <div class="d-flex gap-2 flex-wrap align-items-center">
        <?php if ($c['ativo']): ?>
        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2">Activo</span>
        <?php else: ?>
        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2">Inactivo</span>
        <?php endif; ?>
        <?php if ($c['telefone']): ?>
        <span class="text-muted small"><i class="bi bi-telephone me-1"></i><?= htmlspecialchars($c['telefone']) ?></span>
        <?php endif; ?>
        <span class="text-muted small">Cliente desde <?= date('d/m/Y', strtotime($c['criado_em'])) ?></span>
      </div>
    </div>
  </div>
  <div class="d-flex gap-2">
    <a href="<?= $APP ?>/clientes/<?= $c['id'] ?>/editar" class="btn btn-sm btn-outline-secondary">
      <i class="bi bi-pencil me-1"></i>Editar
    </a>
    <a href="<?= $APP ?>/clientes" class="btn btn-sm btn-outline-secondary">
      <i class="bi bi-arrow-left me-1"></i>Voltar
    </a>
  </div>
</div>

<div class="row g-0">
  <!-- Coluna principal -->
  <div class="col-12 col-xl-8 pe-xl-3">

    <!-- Dados pessoais -->
    <div class="ficha-section">
      <div class="ficha-section-title"><i class="bi bi-person"></i> Dados Pessoais</div>
      <div class="row g-3">
        <div class="col-sm-4">
          <div class="dado-label">Data de nascimento</div>
          <div class="dado-valor"><?= $c['data_nascimento'] ? date('d/m/Y', strtotime($c['data_nascimento'])) : '—' ?></div>
        </div>
        <div class="col-sm-4">
          <div class="dado-label">Sexo</div>
          <div class="dado-valor"><?= match($c['sexo'] ?? '') { 'M'=>'Masculino','F'=>'Feminino',default=>'—' } ?></div>
        </div>
        <div class="col-sm-4">
          <div class="dado-label">NUIT</div>
          <div class="dado-valor"><?= htmlspecialchars($c['nuit'] ?? '—') ?></div>
        </div>
        <div class="col-sm-4">
          <div class="dado-label">Número do BI</div>
          <div class="dado-valor"><?= htmlspecialchars($c['bi'] ?? '—') ?></div>
        </div>
        <div class="col-sm-4">
          <div class="dado-label">Telefone</div>
          <div class="dado-valor"><?= htmlspecialchars($c['telefone'] ?? '—') ?></div>
        </div>
        <div class="col-sm-4">
          <div class="dado-label">Email</div>
          <div class="dado-valor"><?= htmlspecialchars($c['email'] ?? '—') ?></div>
        </div>
        <?php if ($c['endereco']): ?>
        <div class="col-12">
          <div class="dado-label">Endereço</div>
          <div class="dado-valor"><?= htmlspecialchars($c['endereco']) ?></div>
        </div>
        <?php endif; ?>
        <?php if ($c['observacoes']): ?>
        <div class="col-12">
          <div class="dado-label">Observações</div>
          <div class="dado-valor text-muted" style="font-size:.85rem"><?= nl2br(htmlspecialchars($c['observacoes'])) ?></div>
        </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Histórico de compras -->
    <div class="ficha-section">
      <div class="ficha-section-title">
        <i class="bi bi-receipt"></i> Histórico de Compras
        <span class="badge rounded-pill ms-1" style="background:var(--kf-primary-light);color:var(--kf-primary)">
          <?= count($vendas) ?>
        </span>
      </div>

      <?php if (empty($vendas)): ?>
      <p class="text-muted small text-center py-2 mb-0">Nenhuma compra registada</p>
      <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size:.82rem">
          <thead style="background:var(--kf-primary-light)">
            <tr>
              <th class="ps-2 py-2 fw-semibold" style="color:var(--kf-primary)">Nº Venda</th>
              <th class="py-2 fw-semibold d-none d-sm-table-cell" style="color:var(--kf-primary)">Data</th>
              <th class="py-2 fw-semibold d-none d-md-table-cell" style="color:var(--kf-primary)">Itens</th>
              <th class="py-2 fw-semibold d-none d-md-table-cell" style="color:var(--kf-primary)">Pagamento</th>
              <th class="py-2 fw-semibold" style="color:var(--kf-primary)">Total</th>
              <th class="py-2 fw-semibold" style="color:var(--kf-primary)">Estado</th>
              <th class="pe-2 py-2"></th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($vendas as $v): ?>
            <tr>
              <td class="ps-2 fw-semibold"><?= htmlspecialchars($v['numero_venda']) ?></td>
              <td class="d-none d-sm-table-cell text-muted"><?= date('d/m/Y H:i', strtotime($v['criado_em'])) ?></td>
              <td class="d-none d-md-table-cell text-center"><?= $v['total_itens'] ?></td>
              <td class="d-none d-md-table-cell text-muted"><?= ucfirst(str_replace('_',' ',$v['forma_pagamento'])) ?></td>
              <td class="fw-bold" style="color:var(--kf-primary)">MT <?= number_format((float)$v['total'],2,',','.') ?></td>
              <td>
                <?php
                $sc = match($v['status']) { 'concluida'=>'success','cancelada'=>'danger','devolvida'=>'warning', default=>'secondary' };
                $sl = match($v['status']) { 'concluida'=>'Concluída','cancelada'=>'Cancelada','devolvida'=>'Devolvida', default=>ucfirst($v['status']) };
                ?>
                <span class="badge bg-<?= $sc ?>-subtle text-<?= $sc ?> border border-<?= $sc ?>-subtle rounded-pill">
                  <?= $sl ?>
                </span>
              </td>
              <td class="pe-2">
                <a href="<?= $APP ?>/vendas/<?= $v['id'] ?>"
                   class="btn btn-sm btn-outline-secondary py-0 px-2" title="Ver venda">
                  <i class="bi bi-eye"></i>
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>
    </div>

  </div><!-- /col principal -->

  <!-- Coluna lateral -->
  <div class="col-12 col-xl-4">

    <!-- Resumo financeiro -->
    <div class="ficha-section">
      <div class="ficha-section-title"><i class="bi bi-graph-up"></i> Resumo</div>
      <div class="row g-3 text-center">
        <div class="col-6">
          <div class="dado-label">Total de compras</div>
          <div class="fw-bold fs-4" style="color:var(--kf-primary)"><?= (int)$c['total_compras'] ?></div>
        </div>
        <div class="col-6">
          <div class="dado-label">Total gasto</div>
          <div class="fw-bold fs-5" style="color:var(--kf-primary)">
            MT <?= number_format((float)($c['total_gasto'] ?? 0), 0, ',', '.') ?>
          </div>
        </div>
        <div class="col-12">
          <div class="dado-label">Última visita</div>
          <div class="fw-semibold">
            <?= $c['ultima_compra'] ? date('d/m/Y', strtotime($c['ultima_compra'])) : '—' ?>
          </div>
        </div>
      </div>
    </div>

    <!-- Produtos favoritos -->
    <?php if (!empty($favoritos)): ?>
    <div class="ficha-section">
      <div class="ficha-section-title"><i class="bi bi-star"></i> Produtos Favoritos</div>
      <?php foreach ($favoritos as $i => $fav): ?>
      <div class="d-flex align-items-center justify-content-between py-1 <?= $i < count($favoritos)-1 ? 'border-bottom':'' ?>">
        <div>
          <div class="small fw-semibold"><?= htmlspecialchars($fav['nome']) ?></div>
          <div class="text-muted" style="font-size:.73rem"><?= $fav['total_qty'] ?> unidades compradas</div>
        </div>
        <div class="text-end">
          <div class="small fw-bold" style="color:var(--kf-primary)">
            MT <?= number_format((float)$fav['total_gasto'], 0, ',', '.') ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Acção rápida -->
    <div class="ficha-section text-center">
      <a href="<?= $APP ?>/vendas/nova?cliente_id=<?= $c['id'] ?>"
         class="btn w-100" style="background:var(--kf-primary);color:#fff;border:none">
        <i class="bi bi-cart-plus me-2"></i>Nova Venda para este Cliente
      </a>
    </div>

  </div>
</div>
