<?php
// app/Views/clientes/form.php
$APP = $_ENV['APP_URL'] ?? '';
$c   = $cliente ?? [];
$e   = $erros   ?? [];
function oldC(array $c, string $k, $d = ''): string { return htmlspecialchars((string)($c[$k] ?? $d)); }
function selC(array $c, string $k, $v): string { return ($c[$k] ?? '') == $v ? 'selected':''; }
$tipo = $c['tipo_cliente'] ?? 'singular';
?>

<style>
.form-section { background:#fff; border-radius:10px; padding:1.25rem 1.5rem; margin-bottom:1rem; box-shadow:0 1px 4px rgba(0,0,0,.07); }
.form-section-title { font-weight:600; font-size:.8rem; text-transform:uppercase; letter-spacing:.05em; color:var(--kf-primary); border-bottom:1px solid var(--kf-primary-light); padding-bottom:.5rem; margin-bottom:1rem; display:flex; align-items:center; gap:.5rem; }
.required::after { content:" *"; color:#dc3545; }

/* Tipo selector */
.tipo-selector { display:flex; gap:.75rem; margin-bottom:1.25rem; }
.tipo-btn {
  flex:1; display:flex; align-items:center; gap:.75rem;
  padding:.75rem 1rem; border-radius:10px; cursor:pointer;
  border:2px solid #e0e0e0; background:#fafafa; transition:all .18s;
  user-select:none;
}
.tipo-btn:hover { border-color:var(--kf-primary); background:#f0faf5; }
.tipo-btn.active {
  border-color:var(--kf-primary); background:var(--kf-primary-light);
  box-shadow:0 0 0 3px rgba(26,127,90,.12);
}
.tipo-btn .tipo-icon {
  width:40px; height:40px; border-radius:50%; display:flex;
  align-items:center; justify-content:center; font-size:1.25rem;
  background:#e9ecef; color:#555; transition:all .18s; flex-shrink:0;
}
.tipo-btn.active .tipo-icon { background:var(--kf-primary); color:#fff; }
.tipo-btn .tipo-label { font-weight:600; font-size:.9rem; color:#333; }
.tipo-btn .tipo-sub   { font-size:.75rem; color:#888; }

/* Campos condicionais */
.fields-singular, .fields-instituicao { transition:opacity .2s; }
.fields-singular.hidden, .fields-instituicao.hidden { display:none !important; }

/* Badge tipo */
.badge-tipo-singular     { background:#e8f5e9; color:#2e7d32; border:1px solid #c8e6c9; }
.badge-tipo-instituicao  { background:#e3f2fd; color:#1565c0; border:1px solid #bbdefb; }
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
  <input type="hidden" name="tipo_cliente" id="input_tipo_cliente" value="<?= htmlspecialchars($tipo) ?>">

  <div class="row g-0">
    <div class="col-12 col-lg-8 pe-lg-3">

      <!-- Tipo de Cliente -->
      <div class="form-section">
        <div class="form-section-title"><i class="bi bi-tag"></i> Tipo de Cliente</div>
        <div class="tipo-selector">
          <div class="tipo-btn <?= $tipo === 'singular' ? 'active' : '' ?>" onclick="setTipo('singular')" id="btn_singular">
            <div class="tipo-icon"><i class="bi bi-person-fill"></i></div>
            <div>
              <div class="tipo-label">Singular</div>
              <div class="tipo-sub">Pessoa física / individual</div>
            </div>
          </div>
          <div class="tipo-btn <?= $tipo === 'instituicao' ? 'active' : '' ?>" onclick="setTipo('instituicao')" id="btn_instituicao">
            <div class="tipo-icon"><i class="bi bi-building-fill"></i></div>
            <div>
              <div class="tipo-label">Instituição</div>
              <div class="tipo-sub">Empresa, organização ou entidade</div>
            </div>
          </div>
        </div>
      </div>

      <!-- CAMPOS SINGULAR -->
      <div class="fields-singular <?= $tipo !== 'singular' ? 'hidden' : '' ?>" id="fields_singular">

        <div class="form-section">
          <div class="form-section-title"><i class="bi bi-person"></i> Dados Pessoais</div>
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label small required">Nome completo</label>
              <input type="text" name="nome" class="form-control form-control-sm <?= isset($e['nome']) ? 'is-invalid':'' ?>"
                     value="<?= oldC($c,'nome') ?>" placeholder="Nome e apelido">
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
                <input class="form-check-input" type="checkbox" name="ativo" id="ativo_s"
                       <?= ($c['ativo'] ?? 1) ? 'checked':'' ?>>
                <label class="form-check-label small" for="ativo_s">Cliente activo</label>
              </div>
            </div>
          </div>
        </div>

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

      </div><!-- /fields_singular -->


      <!-- CAMPOS INSTITUIÇÃO -->
      <div class="fields-instituicao <?= $tipo !== 'instituicao' ? 'hidden' : '' ?>" id="fields_instituicao">

        <div class="form-section">
          <div class="form-section-title"><i class="bi bi-building"></i> Dados da Instituição</div>
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label small required">Nome / Razão Social</label>
              <input type="text" name="nome" class="form-control form-control-sm <?= isset($e['nome']) ? 'is-invalid':'' ?>"
                     value="<?= oldC($c,'nome') ?>" placeholder="Nome completo da entidade ou empresa">
              <?php if (isset($e['nome'])): ?><div class="invalid-feedback"><?= $e['nome'] ?></div><?php endif; ?>
            </div>
            <div class="col-sm-6">
              <label class="form-label small">Nome Comercial</label>
              <input type="text" name="nome_comercial" class="form-control form-control-sm"
                     value="<?= oldC($c,'nome_comercial') ?>" placeholder="Nome pelo qual é conhecido">
            </div>
            <div class="col-sm-6">
              <label class="form-label small">Sector de Actividade</label>
              <select name="sector" class="form-select form-select-sm">
                <option value="">— Seleccionar —</option>
                <?php
                $sectores = ['Saúde','Educação','Comércio','Indústria','Serviços','Governo/Estado','ONG / Sem fins lucrativos','Outro'];
                foreach ($sectores as $s): ?>
                <option value="<?= $s ?>" <?= selC($c,'sector',$s) ?>><?= $s ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-sm-6">
              <label class="form-label small">Pessoa de Contacto</label>
              <input type="text" name="pessoa_contacto" class="form-control form-control-sm"
                     value="<?= oldC($c,'pessoa_contacto') ?>" placeholder="Nome do responsável">
            </div>
            <div class="col-sm-6">
              <label class="form-label small">Estado</label>
              <div class="form-check form-switch mt-2">
                <input class="form-check-input" type="checkbox" name="ativo" id="ativo_i"
                       <?= ($c['ativo'] ?? 1) ? 'checked':'' ?>>
                <label class="form-check-label small" for="ativo_i">Cliente activo</label>
              </div>
            </div>
          </div>
        </div>

        <div class="form-section">
          <div class="form-section-title"><i class="bi bi-file-earmark-text"></i> Identificação Fiscal</div>
          <div class="row g-3">
            <div class="col-sm-6">
              <label class="form-label small required">NUIT</label>
              <input type="text" name="nuit" class="form-control form-control-sm <?= isset($e['nuit']) ? 'is-invalid':'' ?>"
                     value="<?= oldC($c,'nuit') ?>" placeholder="NUIT da instituição">
              <div class="form-text">Obrigatório para emissão de factura.</div>
              <?php if (isset($e['nuit'])): ?><div class="invalid-feedback"><?= $e['nuit'] ?></div><?php endif; ?>
            </div>
            <div class="col-sm-6">
              <label class="form-label small">Nº de Registo Comercial</label>
              <input type="text" name="bi" class="form-control form-control-sm"
                     value="<?= oldC($c,'bi') ?>" placeholder="Número de registo">
            </div>
          </div>
        </div>

      </div><!-- /fields_instituicao -->


      <!-- CONTACTOS (comuns) -->
      <div class="form-section">
        <div class="form-section-title"><i class="bi bi-telephone"></i> Contactos</div>
        <div class="row g-3">
          <div class="col-sm-4">
            <label class="form-label small">Telefone</label>
            <input type="tel" name="telefone" class="form-control form-control-sm"
                   value="<?= oldC($c,'telefone') ?>" placeholder="ex: 84 xxx xxxx">
          </div>
          <div class="col-sm-4">
            <label class="form-label small">Telefone Alternativo</label>
            <input type="tel" name="telefone2" class="form-control form-control-sm"
                   value="<?= oldC($c,'telefone2') ?>" placeholder="Opcional">
          </div>
          <div class="col-sm-4">
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

      <!-- OBSERVAÇÕES (comuns) -->
      <div class="form-section">
        <div class="form-section-title"><i class="bi bi-chat-text"></i> Observações</div>
        <textarea name="observacoes" class="form-control form-control-sm" rows="3"
                  placeholder="Notas internas, condições especiais, preferências..."><?= oldC($c,'observacoes') ?></textarea>
      </div>

    </div>

    <!-- Lateral: dicas dinâmicas -->
    <div class="col-12 col-lg-4">

      <div class="form-section dica-singular <?= $tipo !== 'singular' ? 'hidden' : '' ?>"
           style="background:var(--kf-primary-light);border:1px solid var(--kf-primary-light)">
        <div class="form-section-title"><i class="bi bi-person-badge"></i> Cliente Singular</div>
        <ul class="small text-muted ps-3 mb-0">
          <li class="mb-2">Pessoa física / individual.</li>
          <li class="mb-2">O <strong>NUIT</strong> é necessário para factura com nome do cliente.</li>
          <li class="mb-2">O <strong>BI</strong> é útil para identificação no balcão.</li>
          <li>Clientes inactivos não aparecem na pesquisa do balcão.</li>
        </ul>
      </div>

      <div class="form-section dica-instituicao <?= $tipo !== 'instituicao' ? 'hidden' : '' ?>"
           style="background:#e3f2fd;border:1px solid #bbdefb">
        <div class="form-section-title" style="color:#1565c0;border-color:#bbdefb">
          <i class="bi bi-building"></i> Cliente Instituição
        </div>
        <ul class="small text-muted ps-3 mb-0">
          <li class="mb-2">Empresa, organização, hospital, escola ou entidade estatal.</li>
          <li class="mb-2">O <strong>NUIT</strong> é <strong>obrigatório</strong> para facturas em nome da instituição.</li>
          <li class="mb-2">Indique a <strong>Pessoa de Contacto</strong> para facilitar o atendimento.</li>
          <li>O <strong>Nº de Registo Comercial</strong> (equivalente ao BI) serve para dossier fiscal.</li>
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

<script>
function setTipo(tipo) {
  document.getElementById('input_tipo_cliente').value = tipo;

  ['singular','instituicao'].forEach(t => {
    const btn    = document.getElementById('btn_' + t);
    const fields = document.getElementById('fields_' + t);
    const dica   = document.querySelector('.dica-' + t);
    if (t === tipo) {
      btn.classList.add('active');
      fields.classList.remove('hidden');
      if (dica) dica.classList.remove('hidden');
    } else {
      btn.classList.remove('active');
      fields.classList.add('hidden');
      if (dica) dica.classList.add('hidden');
    }
  });
}
// Inicializar ao carregar
document.addEventListener('DOMContentLoaded', () => setTipo(document.getElementById('input_tipo_cliente').value || 'singular'));
</script>
