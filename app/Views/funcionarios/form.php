<?php
// app/Views/funcionarios/form.php
$APP = $_ENV['APP_URL'] ?? '';
$f   = $funcionario ?? [];
$e   = $erros ?? [];

// Helper: old value
function old(array $f, string $key, string $default = ''): string {
    return htmlspecialchars($f[$key] ?? $default);
}
function sel(array $f, string $key, string $val): string {
    return ($f[$key] ?? '') == $val ? 'selected' : '';
}
function chk(array $f, string $key, string $val): string {
    return ($f[$key] ?? '') == $val ? 'checked' : '';
}
?>

<style>
.form-section { background:#fff; border-radius:10px; padding:1.25rem 1.5rem; margin-bottom:1rem; box-shadow:0 1px 4px rgba(0,0,0,.07); }
.form-section-title { font-weight:600; font-size:.8rem; text-transform:uppercase; letter-spacing:.05em; color:var(--kf-primary); border-bottom:1px solid var(--kf-primary-light); padding-bottom:.5rem; margin-bottom:1rem; display:flex; align-items:center; gap:.5rem; }
.required::after { content:" *"; color:#dc3545; }
.foto-preview { width:90px; height:90px; border-radius:50%; object-fit:cover; border:3px solid var(--kf-primary-light); }
.foto-placeholder { width:90px; height:90px; border-radius:50%; background:var(--kf-primary-light); display:flex; align-items:center; justify-content:center; font-size:2rem; color:var(--kf-primary); border:3px solid var(--kf-primary-light); }
</style>

<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
  <div>
    <h1 class="h4 fw-bold mb-0" style="color:var(--kf-primary)">
      <i class="bi bi-person-<?= $modo === 'criar' ? 'plus' : 'gear' ?> me-2"></i>
      <?= $modo === 'criar' ? 'Novo Funcionário' : 'Editar Funcionário' ?>
    </h1>
  </div>
  <a href="<?= $APP ?>/funcionarios" class="btn btn-sm btn-outline-secondary">
    <i class="bi bi-arrow-left me-1"></i>Voltar
  </a>
</div>

<?php if ($e): ?>
<div class="alert alert-danger alert-dismissible fade show d-flex gap-2 align-items-start mb-4" role="alert">
  <i class="bi bi-exclamation-triangle-fill flex-shrink-0 mt-1"></i>
  <div>
    <strong>Corrija os seguintes erros:</strong>
    <ul class="mb-0 mt-1 ps-3">
      <?php foreach ($e as $campo => $msg): ?>
      <li><?= htmlspecialchars($msg) ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<form method="POST"
      action="<?= $APP ?>/funcionarios/<?= $modo === 'editar' ? ($f['id'] . '/editar') : 'novo' ?>"
      enctype="multipart/form-data"
      novalidate>
  <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

  <div class="row g-0">
    <!-- Coluna principal -->
    <div class="col-12 col-xl-8 pe-xl-3">

      <!-- 1. Dados pessoais -->
      <div class="form-section">
        <div class="form-section-title"><i class="bi bi-person"></i> Dados Pessoais</div>
        <div class="row g-3">
          <div class="col-12">
            <label class="form-label small required">Nome completo</label>
            <input type="text" name="nome_completo" class="form-control form-control-sm <?= isset($e['nome_completo']) ? 'is-invalid' : '' ?>"
                   value="<?= old($f,'nome_completo') ?>" required autocomplete="name">
            <?php if (isset($e['nome_completo'])): ?><div class="invalid-feedback"><?= $e['nome_completo'] ?></div><?php endif; ?>
          </div>
          <div class="col-sm-4">
            <label class="form-label small required">Data de nascimento</label>
            <input type="date" name="data_nascimento" class="form-control form-control-sm <?= isset($e['data_nascimento']) ? 'is-invalid' : '' ?>"
                   value="<?= old($f,'data_nascimento') ?>" max="<?= date('Y-m-d', strtotime('-16 years')) ?>">
            <?php if (isset($e['data_nascimento'])): ?><div class="invalid-feedback"><?= $e['data_nascimento'] ?></div><?php endif; ?>
          </div>
          <div class="col-sm-4">
            <label class="form-label small required">Sexo</label>
            <select name="sexo" class="form-select form-select-sm <?= isset($e['sexo']) ? 'is-invalid' : '' ?>">
              <option value="">Seleccionar...</option>
              <option value="M" <?= sel($f,'sexo','M') ?>>Masculino</option>
              <option value="F" <?= sel($f,'sexo','F') ?>>Feminino</option>
              <option value="outro" <?= sel($f,'sexo','outro') ?>>Outro</option>
            </select>
            <?php if (isset($e['sexo'])): ?><div class="invalid-feedback"><?= $e['sexo'] ?></div><?php endif; ?>
          </div>
          <div class="col-sm-4">
            <label class="form-label small">Estado civil</label>
            <select name="estado_civil" class="form-select form-select-sm">
              <option value="">—</option>
              <option value="solteiro"       <?= sel($f,'estado_civil','solteiro') ?>>Solteiro(a)</option>
              <option value="casado"         <?= sel($f,'estado_civil','casado') ?>>Casado(a)</option>
              <option value="divorciado"     <?= sel($f,'estado_civil','divorciado') ?>>Divorciado(a)</option>
              <option value="viuvo"          <?= sel($f,'estado_civil','viuvo') ?>>Viúvo(a)</option>
              <option value="uniao_de_facto" <?= sel($f,'estado_civil','uniao_de_facto') ?>>União de facto</option>
            </select>
          </div>
          <div class="col-sm-6">
            <label class="form-label small">Nacionalidade</label>
            <input type="text" name="nacionalidade" class="form-control form-control-sm"
                   value="<?= old($f,'nacionalidade','Moçambicana') ?>">
          </div>
          <div class="col-sm-6">
            <label class="form-label small">Naturalidade</label>
            <input type="text" name="naturalidade" class="form-control form-control-sm"
                   value="<?= old($f,'naturalidade') ?>">
          </div>
        </div>
      </div>

      <!-- 2. Identificação -->
      <div class="form-section">
        <div class="form-section-title"><i class="bi bi-card-text"></i> Identificação</div>
        <div class="row g-3">
          <div class="col-sm-4">
            <label class="form-label small required">Número do BI</label>
            <input type="text" name="bi_numero" class="form-control form-control-sm <?= isset($e['bi_numero']) ? 'is-invalid' : '' ?>"
                   value="<?= old($f,'bi_numero') ?>" placeholder="ex: 111234567A">
            <?php if (isset($e['bi_numero'])): ?><div class="invalid-feedback"><?= $e['bi_numero'] ?></div><?php endif; ?>
          </div>
          <div class="col-sm-4">
            <label class="form-label small">Validade do BI</label>
            <input type="date" name="bi_validade" class="form-control form-control-sm"
                   value="<?= old($f,'bi_validade') ?>">
          </div>
          <div class="col-sm-4">
            <label class="form-label small">NUIT</label>
            <input type="text" name="nuit" class="form-control form-control-sm"
                   value="<?= old($f,'nuit') ?>" placeholder="9 dígitos">
          </div>
          <div class="col-sm-6">
            <label class="form-label small">NRPS (Segurança Social)</label>
            <input type="text" name="nrps" class="form-control form-control-sm"
                   value="<?= old($f,'nrps') ?>">
          </div>
        </div>
      </div>

      <!-- 3. Contactos -->
      <div class="form-section">
        <div class="form-section-title"><i class="bi bi-telephone"></i> Contactos</div>
        <div class="row g-3">
          <div class="col-sm-4">
            <label class="form-label small required">Telefone principal</label>
            <input type="tel" name="telefone_principal" class="form-control form-control-sm <?= isset($e['telefone_principal']) ? 'is-invalid' : '' ?>"
                   value="<?= old($f,'telefone_principal') ?>" placeholder="ex: 84 xxx xxxx">
            <?php if (isset($e['telefone_principal'])): ?><div class="invalid-feedback"><?= $e['telefone_principal'] ?></div><?php endif; ?>
          </div>
          <div class="col-sm-4">
            <label class="form-label small">Telefone alternativo</label>
            <input type="tel" name="telefone_alternativo" class="form-control form-control-sm"
                   value="<?= old($f,'telefone_alternativo') ?>">
          </div>
          <div class="col-sm-4">
            <label class="form-label small">Email pessoal</label>
            <input type="email" name="email_pessoal" class="form-control form-control-sm <?= isset($e['email_pessoal']) ? 'is-invalid' : '' ?>"
                   value="<?= old($f,'email_pessoal') ?>">
            <?php if (isset($e['email_pessoal'])): ?><div class="invalid-feedback"><?= $e['email_pessoal'] ?></div><?php endif; ?>
          </div>
          <div class="col-12">
            <label class="form-label small required">Endereço</label>
            <input type="text" name="endereco" class="form-control form-control-sm <?= isset($e['endereco']) ? 'is-invalid' : '' ?>"
                   value="<?= old($f,'endereco') ?>" placeholder="Rua / Av., número">
            <?php if (isset($e['endereco'])): ?><div class="invalid-feedback"><?= $e['endereco'] ?></div><?php endif; ?>
          </div>
          <div class="col-sm-4">
            <label class="form-label small">Bairro</label>
            <input type="text" name="bairro" class="form-control form-control-sm"
                   value="<?= old($f,'bairro') ?>">
          </div>
          <div class="col-sm-4">
            <label class="form-label small">Cidade</label>
            <input type="text" name="cidade" class="form-control form-control-sm"
                   value="<?= old($f,'cidade','Quelimane') ?>">
          </div>
          <div class="col-sm-4">
            <label class="form-label small">Província</label>
            <input type="text" name="provincia" class="form-control form-control-sm"
                   value="<?= old($f,'provincia','Zambézia') ?>">
          </div>
        </div>
      </div>

      <!-- 4. Emergência -->
      <div class="form-section">
        <div class="form-section-title"><i class="bi bi-heart-pulse"></i> Contacto de Emergência</div>
        <div class="row g-3">
          <div class="col-sm-5">
            <label class="form-label small">Nome</label>
            <input type="text" name="emergencia_nome" class="form-control form-control-sm"
                   value="<?= old($f,'emergencia_nome') ?>">
          </div>
          <div class="col-sm-3">
            <label class="form-label small">Parentesco</label>
            <input type="text" name="emergencia_parentesco" class="form-control form-control-sm"
                   value="<?= old($f,'emergencia_parentesco') ?>" placeholder="ex: Cônjuge">
          </div>
          <div class="col-sm-4">
            <label class="form-label small">Telefone</label>
            <input type="tel" name="emergencia_telefone" class="form-control form-control-sm"
                   value="<?= old($f,'emergencia_telefone') ?>">
          </div>
        </div>
      </div>

      <!-- 5. Dados profissionais -->
      <div class="form-section">
        <div class="form-section-title"><i class="bi bi-briefcase"></i> Dados Profissionais</div>
        <div class="row g-3">
          <div class="col-sm-4">
            <label class="form-label small required">Nº Funcionário</label>
            <input type="text" name="numero_funcionario" class="form-control form-control-sm <?= isset($e['numero_funcionario']) ? 'is-invalid' : '' ?>"
                   value="<?= old($f,'numero_funcionario',$numero_sugerido??'') ?>"
                   <?= $modo === 'editar' ? 'readonly' : '' ?>>
            <?php if (isset($e['numero_funcionario'])): ?><div class="invalid-feedback"><?= $e['numero_funcionario'] ?></div><?php endif; ?>
          </div>
          <div class="col-sm-4">
            <label class="form-label small required">Cargo</label>
            <select name="cargo_id" class="form-select form-select-sm <?= isset($e['cargo_id']) ? 'is-invalid' : '' ?>">
              <option value="0">Seleccionar...</option>
              <?php foreach ($cargos as $c): ?>
              <option value="<?= $c['id'] ?>" <?= sel($f,'cargo_id',$c['id']) ?>><?= htmlspecialchars($c['nome']) ?></option>
              <?php endforeach; ?>
            </select>
            <?php if (isset($e['cargo_id'])): ?><div class="invalid-feedback"><?= $e['cargo_id'] ?></div><?php endif; ?>
          </div>
          <div class="col-sm-4">
            <label class="form-label small required">Data de admissão</label>
            <input type="date" name="data_admissao" class="form-control form-control-sm <?= isset($e['data_admissao']) ? 'is-invalid' : '' ?>"
                   value="<?= old($f,'data_admissao') ?>">
            <?php if (isset($e['data_admissao'])): ?><div class="invalid-feedback"><?= $e['data_admissao'] ?></div><?php endif; ?>
          </div>
          <div class="col-sm-4">
            <label class="form-label small">Tipo de contrato</label>
            <select name="tipo_contrato" class="form-select form-select-sm">
              <option value="efectivo"           <?= sel($f,'tipo_contrato','efectivo') ?>>Efectivo</option>
              <option value="temporario"         <?= sel($f,'tipo_contrato','temporario') ?>>Temporário</option>
              <option value="estagio"            <?= sel($f,'tipo_contrato','estagio') ?>>Estágio</option>
              <option value="prestacao_servicos" <?= sel($f,'tipo_contrato','prestacao_servicos') ?>>Prestação de Serviços</option>
            </select>
          </div>
          <div class="col-sm-4">
            <label class="form-label small">Salário (MZN)</label>
            <input type="number" name="salario" class="form-control form-control-sm"
                   value="<?= old($f,'salario','0') ?>" min="0" step="0.01">
          </div>
          <div class="col-sm-4">
            <label class="form-label small">Estado</label>
            <select name="status" class="form-select form-select-sm">
              <option value="activo"    <?= sel($f,'status','activo') ?>>Activo</option>
              <option value="inactivo"  <?= sel($f,'status','inactivo') ?>>Inactivo</option>
              <option value="suspenso"  <?= sel($f,'status','suspenso') ?>>Suspenso</option>
              <option value="desligado" <?= sel($f,'status','desligado') ?>>Desligado</option>
            </select>
          </div>
        </div>
      </div>

      <!-- 6. Habilitações -->
      <div class="form-section">
        <div class="form-section-title"><i class="bi bi-mortarboard"></i> Habilitações Literárias</div>
        <div class="row g-3">
          <div class="col-sm-4">
            <label class="form-label small">Nível de escolaridade</label>
            <select name="nivel_escolaridade" class="form-select form-select-sm">
              <option value="">—</option>
              <option value="primario"         <?= sel($f,'nivel_escolaridade','primario') ?>>Primário</option>
              <option value="secundario"       <?= sel($f,'nivel_escolaridade','secundario') ?>>Secundário</option>
              <option value="tecnico_medio"    <?= sel($f,'nivel_escolaridade','tecnico_medio') ?>>Técnico Médio</option>
              <option value="licenciatura"     <?= sel($f,'nivel_escolaridade','licenciatura') ?>>Licenciatura</option>
              <option value="mestrado"         <?= sel($f,'nivel_escolaridade','mestrado') ?>>Mestrado</option>
              <option value="doutoramento"     <?= sel($f,'nivel_escolaridade','doutoramento') ?>>Doutoramento</option>
            </select>
          </div>
          <div class="col-sm-4">
            <label class="form-label small">Curso</label>
            <input type="text" name="curso" class="form-control form-control-sm"
                   value="<?= old($f,'curso') ?>" placeholder="ex: Farmácia">
          </div>
          <div class="col-sm-4">
            <label class="form-label small">Ano de conclusão</label>
            <input type="number" name="ano_conclusao" class="form-control form-control-sm"
                   value="<?= old($f,'ano_conclusao') ?>" min="1980" max="<?= date('Y') ?>">
          </div>
          <div class="col-sm-8">
            <label class="form-label small">Instituição</label>
            <input type="text" name="instituicao" class="form-control form-control-sm"
                   value="<?= old($f,'instituicao') ?>">
          </div>
        </div>
      </div>

      <!-- 7. Observações -->
      <div class="form-section">
        <div class="form-section-title"><i class="bi bi-chat-text"></i> Observações</div>
        <textarea name="observacoes" class="form-control form-control-sm" rows="3"
                  placeholder="Notas internas sobre este funcionário..."><?= old($f,'observacoes') ?></textarea>
      </div>

    </div><!-- /col principal -->

    <!-- Coluna lateral: foto + documentos -->
    <div class="col-12 col-xl-4">

      <!-- Foto tipo passe -->
      <div class="form-section text-center">
        <div class="form-section-title justify-content-center"><i class="bi bi-camera"></i> Foto Tipo Passe</div>
        <div class="d-flex justify-content-center mb-3" id="fotoWrap">
          <?php if (!empty($f['foto_url'])): ?>
          <img src="<?= $APP ?>/uploads/<?= htmlspecialchars($f['foto_url']) ?>"
               class="foto-preview" id="fotoPreview" alt="Foto">
          <?php else: ?>
          <div class="foto-placeholder" id="fotoPlaceholder">
            <i class="bi bi-person-fill"></i>
          </div>
          <img src="" class="foto-preview d-none" id="fotoPreview" alt="Pré-visualização">
          <?php endif; ?>
        </div>
        <label class="btn btn-sm btn-outline-secondary w-100" for="fotoInput">
          <i class="bi bi-upload me-1"></i>Escolher foto (JPG/PNG)
        </label>
        <input type="file" name="foto" id="fotoInput" accept="image/jpeg,image/png,image/webp" class="d-none">
        <?php if (isset($e['foto'])): ?>
        <div class="text-danger small mt-1"><?= $e['foto'] ?></div>
        <?php endif; ?>
        <p class="text-muted small mt-2 mb-0">Máximo 5 MB · JPG, PNG ou WebP</p>
      </div>

      <!-- Doc identificação -->
      <div class="form-section">
        <div class="form-section-title"><i class="bi bi-file-earmark-person"></i> Doc. de Identificação</div>
        <?php if (!empty($f['doc_identificacao_url'])): ?>
        <a href="<?= $APP ?>/uploads/<?= htmlspecialchars($f['doc_identificacao_url']) ?>"
           target="_blank" class="btn btn-sm btn-outline-success w-100 mb-2">
          <i class="bi bi-file-earmark-pdf me-1"></i>
          <?= htmlspecialchars($f['doc_identificacao_nome'] ?? 'Ver documento') ?>
        </a>
        <?php endif; ?>
        <label class="btn btn-sm btn-outline-secondary w-100 mb-1" for="docIdInput">
          <i class="bi bi-upload me-1"></i>
          <?= !empty($f['doc_identificacao_url']) ? 'Substituir PDF' : 'Carregar PDF do BI/Passaporte' ?>
        </label>
        <input type="file" name="doc_identificacao" id="docIdInput" accept="application/pdf" class="d-none"
               onchange="mostrarNomeFicheiro(this,'docIdNome')">
        <div id="docIdNome" class="text-muted small mt-1"></div>
        <?php if (isset($e['doc_identificacao'])): ?>
        <div class="text-danger small"><?= $e['doc_identificacao'] ?></div>
        <?php endif; ?>
        <p class="text-muted small mt-2 mb-0">Máximo 5 MB · apenas PDF</p>
      </div>

      <!-- Doc complementar -->
      <div class="form-section">
        <div class="form-section-title"><i class="bi bi-file-earmark-plus"></i> Documento Complementar</div>
        <?php if (!empty($f['doc_complementar_url'])): ?>
        <a href="<?= $APP ?>/uploads/<?= htmlspecialchars($f['doc_complementar_url']) ?>"
           target="_blank" class="btn btn-sm btn-outline-success w-100 mb-2">
          <i class="bi bi-file-earmark-pdf me-1"></i>
          <?= htmlspecialchars($f['doc_complementar_nome'] ?? 'Ver documento') ?>
        </a>
        <?php endif; ?>
        <label class="btn btn-sm btn-outline-secondary w-100 mb-1" for="docCompInput">
          <i class="bi bi-upload me-1"></i>
          <?= !empty($f['doc_complementar_url']) ? 'Substituir PDF' : 'Carregar PDF (CV, contrato...)' ?>
        </label>
        <input type="file" name="doc_complementar" id="docCompInput" accept="application/pdf" class="d-none"
               onchange="mostrarNomeFicheiro(this,'docCompNome')">
        <div id="docCompNome" class="text-muted small mt-1"></div>
        <?php if (isset($e['doc_complementar'])): ?>
        <div class="text-danger small"><?= $e['doc_complementar'] ?></div>
        <?php endif; ?>
        <p class="text-muted small mt-2 mb-0">Máximo 5 MB · apenas PDF</p>
      </div>

    </div><!-- /col lateral -->
  </div><!-- /row -->

  <!-- Botões de acção -->
  <div class="d-flex gap-2 justify-content-end mt-2 mb-4">
    <a href="<?= $APP ?>/funcionarios" class="btn btn-outline-secondary">
      <i class="bi bi-x-lg me-1"></i>Cancelar
    </a>
    <button type="submit" class="btn px-4" style="background:var(--kf-primary);color:#fff;border:none">
      <i class="bi bi-check-lg me-1"></i>
      <?= $modo === 'criar' ? 'Registar Funcionário' : 'Guardar Alterações' ?>
    </button>
  </div>
</form>

<script>
// Pré-visualização da foto
document.getElementById('fotoInput').addEventListener('change', function() {
  const file = this.files[0];
  if (!file) return;
  const reader = new FileReader();
  reader.onload = e => {
    const preview = document.getElementById('fotoPreview');
    const placeholder = document.getElementById('fotoPlaceholder');
    preview.src = e.target.result;
    preview.classList.remove('d-none');
    if (placeholder) placeholder.classList.add('d-none');
  };
  reader.readAsDataURL(file);
});

// Mostrar nome do ficheiro seleccionado
function mostrarNomeFicheiro(input, targetId) {
  const el = document.getElementById(targetId);
  if (el) el.textContent = input.files[0] ? '📎 ' + input.files[0].name : '';
}
</script>
