<?php
// app/Views/clientes/form.php
$APP = $_ENV['APP_URL'] ?? '';
$c   = $cliente ?? [];
$e   = $erros   ?? [];
function oldC(array $c, string $k, $d = ''): string { return htmlspecialchars((string)($c[$k] ?? $d)); }
function selC(array $c, string $k, $v): string { return ($c[$k] ?? '') == $v ? 'selected':''; }
?>

<style>
.form-section { background:#fff; border-radius:10px; padding:1.25rem 1.5rem; margin-bottom:1rem; box-shadow:0 1px 4px rgba(0,0,0,.07); }
.form-section-title { font-weight:600; font-size:.8rem; text-transform:uppercase; letter-spacing:.05em; color:var(--kf-primary); border-bottom:1px solid var(--kf-primary-light); padding-bottom:.5rem; margin-bottom:1rem; display:flex; align-items:center; gap:.5rem; }
.required::after { content:" *"; color:#dc3545; }
</style>

<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
  <h1 class="h4 fw-bold mb-0" style="color:var(--kf-primary)">
    <i class="bi bi-person-<?= $modo === 'criar' ? 'plus' : 'gear' ?> me-2"></i>
    <?= $modo === 'criar' ? 'Novo Cliente' : 'Editar Cliente' ?>
  </h1>
  <a href="<?= $APP ?>/clientes" class="btn btn-sm btn-outline-secondary">
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
      action="<?= $APP ?>/clientes/<?= $modo === 'editar' ? ($c['id'].'/editar') : 'novo' ?>"
      novalidate>
  <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

  <div class="row g-0">
    <div class="col-12 col-lg-8 pe-lg-3">

      <!-- Dados pessoais -->
      <div class="form-section">
        <div class="form-section-title"><i class="bi bi-person"></i> Dados Pessoais</div>
        <div class="row g-3">
          <div class="col-12">
            <label class="form-label small required">Nome completo</label>
            <input type="text" name="nome" class="form-control form-control-sm <?= isset($e['nome']) ? 'is-invalid':'' ?>"
                   value="<?= oldC($c,'nome') ?>" required>
            <?php if (isset($e['nome'])): ?><div class="invalid-feedback"><?= $e['nome'] ?></div><?php endif; ?>
          </div>
          <div class="col-sm-4">
            <label class="form-label small">Data de nascimento</label>
            <input type="date" name="data_nascimento" class="form-control form-control-sm"
                   value="<?= oldC($c,'data_nascimento') ?>">
          </div>
          <div class="col-sm-4">
            <label class="form-label small">Sexo</label>
            <select name="sexo" class="form-select form-select-sm">
              <option value="">—</option>
              <option value="M"     <?= selC($c,'sexo','M') ?>>Masculino</option>
              <option value="F"     <?= selC($c,'sexo','F') ?>>Feminino</option>
              <option value="outro" <?= selC($c,'sexo','outro') ?>>Outro</option>
            </select>
          </div>
          <div class="col-sm-4">
            <label class="form-label small">Estado</label>
            <div class="form-check form-switch mt-2">
              <input class="form-check-input" type="checkbox" name="ativo" id="ativo"
                     <?= ($c['ativo'] ?? 1) ? 'checked':'' ?>>
              <label class="form-check-label small" for="ativo">Cliente activo</label>
            </div>
          </div>
        </div>
      </div>

      <!-- Identificação -->
      <div class="form-section">
        <div class="form-section-title"><i class="bi bi-card-text"></i> Identificação</div>
        <div class="row g-3">
          <div class="col-sm-6">
            <label class="form-label small">NUIT</label>
            <input type="text" name="nuit" class="form-control form-control-sm <?= isset($e['nuit']) ? 'is-invalid':'' ?>"
                   value="<?= oldC($c,'nuit') ?>" placeholder="9 dígitos">
            <?php if (isset($e['nuit'])): ?><div class="invalid-feedback"><?= $e['nuit'] ?></div><?php endif; ?>
          </div>
          <div class="col-sm-6">
            <label class="form-label small">Número do BI</label>
            <input type="text" name="bi" class="form-control form-control-sm"
                   value="<?= oldC($c,'bi') ?>">
          </div>
        </div>
      </div>

      <!-- Contactos -->
      <div class="form-section">
        <div class="form-section-title"><i class="bi bi-telephone"></i> Contactos</div>
        <div class="row g-3">
          <div class="col-sm-4">
            <label class="form-label small">Telefone</label>
            <input type="tel" name="telefone" class="form-control form-control-sm"
                   value="<?= oldC($c,'telefone') ?>" placeholder="ex: 84 xxx xxxx">
          </div>
          <div class="col-sm-8">
            <label class="form-label small">Email</label>
            <input type="email" name="email" class="form-control form-control-sm <?= isset($e['email']) ? 'is-invalid':'' ?>"
                   value="<?= oldC($c,'email') ?>">
            <?php if (isset($e['email'])): ?><div class="invalid-feedback"><?= $e['email'] ?></div><?php endif; ?>
          </div>
          <div class="col-12">
            <label class="form-label small">Endereço</label>
            <input type="text" name="endereco" class="form-control form-control-sm"
                   value="<?= oldC($c,'endereco') ?>" placeholder="Rua, bairro, cidade">
          </div>
        </div>
      </div>

      <!-- Observações -->
      <div class="form-section">
        <div class="form-section-title"><i class="bi bi-chat-text"></i> Observações</div>
        <textarea name="observacoes" class="form-control form-control-sm" rows="3"
                  placeholder="Alergias, notas clínicas, preferências..."><?= oldC($c,'observacoes') ?></textarea>
      </div>

    </div>

    <!-- Lateral: dicas -->
    <div class="col-12 col-lg-4">
      <div class="form-section" style="background:var(--kf-primary-light);border:1px solid var(--kf-primary-light)">
        <div class="form-section-title"><i class="bi bi-lightbulb"></i> Dicas</div>
        <ul class="small text-muted ps-3 mb-0">
          <li class="mb-2">O <strong>NUIT</strong> é obrigatório para emissão de factura com nome do cliente.</li>
          <li class="mb-2">O <strong>telefone</strong> facilita o registo rápido no balcão.</li>
          <li class="mb-2">As <strong>observações</strong> são visíveis apenas internamente — use-as para alergias ou notas clínicas relevantes.</li>
          <li>Clientes inactivos não aparecem na pesquisa do balcão.</li>
        </ul>
      </div>
    </div>
  </div>

  <div class="d-flex gap-2 justify-content-end mt-2 mb-4">
    <a href="<?= $APP ?>/clientes" class="btn btn-outline-secondary">
      <i class="bi bi-x-lg me-1"></i>Cancelar
    </a>
    <button type="submit" class="btn px-4" style="background:var(--kf-primary);color:#fff;border:none">
      <i class="bi bi-check-lg me-1"></i>
      <?= $modo === 'criar' ? 'Registar Cliente' : 'Guardar Alterações' ?>
    </button>
  </div>
</form>
