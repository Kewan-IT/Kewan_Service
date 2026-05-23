<?php
$APP = $_ENV['APP_URL'] ?? '';
$f   = $fornecedor ?? [];
$e   = $erros      ?? [];
function oldF(array $f, string $k, $d = ''): string { return htmlspecialchars((string)($f[$k] ?? $d)); }
?>

<style>
.form-section { background:#fff; border-radius:10px; padding:1.25rem 1.5rem; margin-bottom:1rem; box-shadow:0 1px 4px rgba(0,0,0,.07); }
.form-section-title { font-weight:600; font-size:.8rem; text-transform:uppercase; letter-spacing:.05em; color:var(--kf-primary); border-bottom:1px solid var(--kf-primary-light); padding-bottom:.5rem; margin-bottom:1rem; display:flex; align-items:center; gap:.5rem; }
.required::after { content:" *"; color:#dc3545; }
</style>

<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
  <h1 class="h4 fw-bold mb-0" style="color:var(--kf-primary)">
    <i class="bi bi-building-<?= $modo === 'criar' ? 'add' : 'gear' ?> me-2"></i>
    <?= $modo === 'criar' ? 'Novo Fornecedor' : 'Editar Fornecedor' ?>
  </h1>
  <a href="<?= $APP ?>/fornecedores" class="btn btn-sm btn-outline-secondary">
    <i class="bi bi-arrow-left me-1"></i>Voltar
  </a>
</div>

<?php if ($e): ?>
<div class="alert alert-danger alert-dismissible fade show d-flex gap-2 align-items-start mb-4">
  <i class="bi bi-exclamation-triangle-fill flex-shrink-0 mt-1"></i>
  <div>
    <strong>Corrija os erros:</strong>
    <ul class="mb-0 mt-1 ps-3">
      <?php foreach ($e as $msg): ?><li><?= htmlspecialchars($msg) ?></li><?php endforeach; ?>
    </ul>
  </div>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<form method="POST"
      action="<?= $APP ?>/fornecedores/<?= $modo === 'editar' ? ($f['id'].'/editar') : 'novo' ?>"
      novalidate>
  <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

  <div class="row g-0">
    <div class="col-12 col-lg-8 pe-lg-3">

      <!-- Identificação -->
      <div class="form-section">
        <div class="form-section-title"><i class="bi bi-building"></i>Identificação</div>
        <div class="row g-3">
          <div class="col-12">
            <label class="form-label small required">Nome da empresa / fornecedor</label>
            <input type="text" name="nome"
                   class="form-control form-control-sm <?= isset($e['nome']) ? 'is-invalid':'' ?>"
                   value="<?= oldF($f,'nome') ?>" required autofocus
                   placeholder="Ex: Farmácia Central Distribuidora, Lda">
            <?php if (isset($e['nome'])): ?>
            <div class="invalid-feedback"><?= $e['nome'] ?></div>
            <?php endif; ?>
          </div>
          <div class="col-sm-6">
            <label class="form-label small">NUIT</label>
            <input type="text" name="nuit"
                   class="form-control form-control-sm <?= isset($e['nuit']) ? 'is-invalid':'' ?>"
                   value="<?= oldF($f,'nuit') ?>" placeholder="9 dígitos">
            <?php if (isset($e['nuit'])): ?>
            <div class="invalid-feedback"><?= $e['nuit'] ?></div>
            <?php endif; ?>
          </div>
          <div class="col-sm-6">
            <label class="form-label small">Estado</label>
            <div class="form-check form-switch mt-2">
              <input class="form-check-input" type="checkbox" name="ativo" id="ativo"
                     <?= ($f['ativo'] ?? 1) ? 'checked':'' ?>>
              <label class="form-check-label small" for="ativo">Fornecedor activo</label>
            </div>
          </div>
        </div>
      </div>

      <!-- Contactos -->
      <div class="form-section">
        <div class="form-section-title"><i class="bi bi-telephone"></i>Contactos</div>
        <div class="row g-3">
          <div class="col-sm-5">
            <label class="form-label small">Telefone</label>
            <input type="tel" name="telefone" class="form-control form-control-sm"
                   value="<?= oldF($f,'telefone') ?>" placeholder="ex: 84 xxx xxxx">
          </div>
          <div class="col-sm-7">
            <label class="form-label small">Email</label>
            <input type="email" name="email"
                   class="form-control form-control-sm <?= isset($e['email']) ? 'is-invalid':'' ?>"
                   value="<?= oldF($f,'email') ?>" placeholder="email@empresa.co.mz">
            <?php if (isset($e['email'])): ?>
            <div class="invalid-feedback"><?= $e['email'] ?></div>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- Localização -->
      <div class="form-section">
        <div class="form-section-title"><i class="bi bi-geo-alt"></i>Localização</div>
        <div class="row g-3">
          <div class="col-12">
            <label class="form-label small">Endereço</label>
            <input type="text" name="endereco" class="form-control form-control-sm"
                   value="<?= oldF($f,'endereco') ?>" placeholder="Rua, nº, bairro">
          </div>
          <div class="col-sm-5">
            <label class="form-label small">Cidade</label>
            <input type="text" name="cidade" class="form-control form-control-sm"
                   value="<?= oldF($f,'cidade') ?>" placeholder="ex: Maputo">
          </div>
          <div class="col-sm-7">
            <label class="form-label small">País</label>
            <input type="text" name="pais" class="form-control form-control-sm"
                   value="<?= oldF($f,'pais','Moçambique') ?>">
          </div>
        </div>
      </div>

    </div>

    <!-- Lateral: dicas -->
    <div class="col-12 col-lg-4">
      <div class="form-section" style="background:var(--kf-primary-light);border:1px solid var(--kf-primary-light)">
        <div class="form-section-title"><i class="bi bi-lightbulb"></i>Dicas</div>
        <ul class="small text-muted ps-3 mb-0">
          <li class="mb-2">O <strong>NUIT</strong> é necessário para emissão de facturas de compra.</li>
          <li class="mb-2">Forneça o <strong>email</strong> para envio automático de encomendas (funcionalidade futura).</li>
          <li class="mb-2">Fornecedores <strong>inactivos</strong> não aparecem na criação de novas compras.</li>
          <li>Pode <strong>reactivar</strong> um fornecedor a qualquer momento na sua ficha.</li>
        </ul>
      </div>

      <?php if ($modo === 'editar'): ?>
      <div class="form-section mt-3">
        <div class="form-section-title"><i class="bi bi-info-circle"></i>Informação</div>
        <div class="small text-muted">
          <div class="mb-1">Registado em: <strong><?= date('d/m/Y', strtotime($f['criado_em'])) ?></strong></div>
          <?php if ($f['actualizado_em'] !== $f['criado_em']): ?>
          <div>Actualizado em: <strong><?= date('d/m/Y H:i', strtotime($f['actualizado_em'])) ?></strong></div>
          <?php endif; ?>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <div class="d-flex gap-2 justify-content-end mt-2 mb-4">
    <a href="<?= $APP ?>/fornecedores" class="btn btn-outline-secondary">
      <i class="bi bi-x-lg me-1"></i>Cancelar
    </a>
    <button type="submit" class="btn px-4" style="background:var(--kf-primary);color:#fff;border:none">
      <i class="bi bi-check-lg me-1"></i>
      <?= $modo === 'criar' ? 'Registar Fornecedor' : 'Guardar Alterações' ?>
    </button>
  </div>
</form>
