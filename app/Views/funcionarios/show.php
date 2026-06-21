<?php
// app/Views/funcionarios/show.php
$APP = $_ENV['APP_URL'] ?? '';
$f   = $funcionario;
$isAdmin = ($_SESSION['perfil'] ?? '') === 'admin';

$statusMap = [
  'activo'    => ['success', 'Activo'],
  'inactivo'  => ['secondary', 'Inactivo'],
  'suspenso'  => ['warning', 'Suspenso'],
  'desligado' => ['danger', 'Desligado'],
];
[$sc, $sl] = $statusMap[$f['status']] ?? ['secondary', $f['status']];

$perfilMap = [
  'admin'        => ['primary', 'Administrador'],
  'farmaceutico' => ['info', 'Farmacêutico'],
  'caixa'        => ['warning', 'Caixa'],
  'tecnico'      => ['secondary', 'Técnico'],
];
[$pc, $pl] = $perfilMap[$f['perfil'] ?? ''] ?? ['secondary', '—'];
?>

<style>
.ficha-section { background:#fff; border-radius:10px; padding:1.25rem 1.5rem; margin-bottom:1rem; box-shadow:0 1px 4px rgba(0,0,0,.07); }
.ficha-section-title { font-weight:600; font-size:.8rem; text-transform:uppercase; letter-spacing:.05em; color:var(--kf-primary); border-bottom:1px solid var(--kf-primary-light); padding-bottom:.5rem; margin-bottom:1rem; display:flex; align-items:center; gap:.5rem; }
.dado-label { font-size:.72rem; text-transform:uppercase; letter-spacing:.04em; color:#999; font-weight:500; margin-bottom:1px; }
.dado-valor { font-size:.9rem; color:#1a2e27; }
.foto-funcionario { width:100px; height:100px; border-radius:50%; object-fit:cover; border:4px solid var(--kf-primary-light); }
.foto-placeholder-lg { width:100px; height:100px; border-radius:50%; background:var(--kf-primary-light); display:flex; align-items:center; justify-content:center; font-size:2.5rem; color:var(--kf-primary); border:4px solid var(--kf-primary-light); }
</style>

<!-- Cabeçalho -->
<div class="d-flex align-items-start justify-content-between mb-4 flex-wrap gap-2">
  <div class="d-flex align-items-center gap-3 flex-wrap">
    <?php if (!empty($f['foto_url'])): ?>
    <img src="<?= $APP ?>/uploads/<?= htmlspecialchars($f['foto_url']) ?>"
         class="foto-funcionario" alt="<?= htmlspecialchars($f['nome_completo']) ?>">
    <?php else: ?>
    <div class="foto-placeholder-lg">
      <?= mb_strtoupper(mb_substr($f['nome_completo'], 0, 1)) ?>
    </div>
    <?php endif; ?>
    <div>
      <h1 class="h4 fw-bold mb-1" style="color:var(--kf-primary)"><?= htmlspecialchars($f['nome_completo']) ?></h1>
      <div class="d-flex align-items-center gap-2 flex-wrap">
        <span class="text-muted small"><?= htmlspecialchars($f['cargo_nome']) ?></span>
        <span class="badge bg-<?= $sc ?>-subtle text-<?= $sc ?> border border-<?= $sc ?>-subtle rounded-pill px-2">
          <?= $sl ?>
        </span>
        <span class="text-muted small">Nº <?= htmlspecialchars($f['numero_funcionario']) ?></span>
      </div>
    </div>
  </div>
  <div class="d-flex gap-2 flex-wrap">
    <a href="<?= $APP ?>/funcionarios/<?= $f['id'] ?>/contrato" target="_blank"
       class="btn btn-sm btn-outline-success">
      <i class="bi bi-file-earmark-text me-1"></i>Contrato de Trabalho
    </a>
    <?php if ($isAdmin): ?>
    <a href="<?= $APP ?>/funcionarios/<?= $f['id'] ?>/editar"
       class="btn btn-sm btn-outline-secondary">
      <i class="bi bi-pencil me-1"></i>Editar
    </a>
    <?php endif; ?>
    <a href="<?= $APP ?>/funcionarios" class="btn btn-sm btn-outline-secondary">
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
        <?php
        $campos = [
          ['Data de nascimento', $f['data_nascimento'] ? date('d/m/Y', strtotime($f['data_nascimento'])) : '—'],
          ['Sexo', match($f['sexo']??'') { 'M'=>'Masculino', 'F'=>'Feminino', default=>'Outro' }],
          ['Estado civil', $f['estado_civil'] ? ucwords(str_replace('_',' ',$f['estado_civil'])) : '—'],
          ['Nacionalidade', $f['nacionalidade'] ?? '—'],
          ['Naturalidade', $f['naturalidade'] ?? '—'],
        ];
        foreach ($campos as [$lbl, $val]):
        ?>
        <div class="col-6 col-md-4">
          <div class="dado-label"><?= $lbl ?></div>
          <div class="dado-valor"><?= htmlspecialchars((string)$val) ?></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Identificação -->
    <div class="ficha-section">
      <div class="ficha-section-title"><i class="bi bi-card-text"></i> Identificação</div>
      <div class="row g-3">
        <?php
        $idCampos = [
          ['Nº BI', $f['bi_numero']],
          ['Validade BI', $f['bi_validade'] ? date('d/m/Y', strtotime($f['bi_validade'])) : '—'],
          ['NUIT', $f['nuit'] ?? '—'],
          ['NRPS', $f['nrps'] ?? '—'],
        ];
        foreach ($idCampos as [$lbl, $val]):
        ?>
        <div class="col-6 col-md-3">
          <div class="dado-label"><?= $lbl ?></div>
          <div class="dado-valor"><?= htmlspecialchars((string)$val) ?></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Contactos -->
    <div class="ficha-section">
      <div class="ficha-section-title"><i class="bi bi-telephone"></i> Contactos</div>
      <div class="row g-3">
        <div class="col-sm-4">
          <div class="dado-label">Telefone principal</div>
          <div class="dado-valor"><?= htmlspecialchars($f['telefone_principal'] ?? '—') ?></div>
        </div>
        <div class="col-sm-4">
          <div class="dado-label">Telefone alternativo</div>
          <div class="dado-valor"><?= htmlspecialchars($f['telefone_alternativo'] ?? '—') ?></div>
        </div>
        <div class="col-sm-4">
          <div class="dado-label">Email pessoal</div>
          <div class="dado-valor"><?= htmlspecialchars($f['email_pessoal'] ?? '—') ?></div>
        </div>
        <div class="col-sm-8">
          <div class="dado-label">Endereço</div>
          <div class="dado-valor">
            <?= htmlspecialchars($f['endereco'] ?? '') ?>
            <?php if ($f['bairro']): ?>, Bairro <?= htmlspecialchars($f['bairro']) ?><?php endif; ?>
            — <?= htmlspecialchars($f['cidade'] ?? '') ?>, <?= htmlspecialchars($f['provincia'] ?? '') ?>
          </div>
        </div>
        <?php if ($f['emergencia_nome']): ?>
        <div class="col-sm-4">
          <div class="dado-label">Emergência</div>
          <div class="dado-valor">
            <?= htmlspecialchars($f['emergencia_nome']) ?>
            <?php if ($f['emergencia_parentesco']): ?>
            <span class="text-muted">(<?= htmlspecialchars($f['emergencia_parentesco']) ?>)</span>
            <?php endif; ?>
            <?php if ($f['emergencia_telefone']): ?>
            <br><?= htmlspecialchars($f['emergencia_telefone']) ?>
            <?php endif; ?>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Dados profissionais -->
    <div class="ficha-section">
      <div class="ficha-section-title"><i class="bi bi-briefcase"></i> Dados Profissionais</div>
      <div class="row g-3">
        <?php
        $profCampos = [
          ['Cargo', $f['cargo_nome']],
          ['Data de admissão', $f['data_admissao'] ? date('d/m/Y', strtotime($f['data_admissao'])) : '—'],
          ['Tipo de contrato', ucwords(str_replace('_',' ',$f['tipo_contrato']??''))],
          ['Salário', 'MT ' . number_format((float)($f['salario']??0), 2, ',', '.')],
        ];
        foreach ($profCampos as [$lbl, $val]):
        ?>
        <div class="col-6 col-md-3">
          <div class="dado-label"><?= $lbl ?></div>
          <div class="dado-valor"><?= htmlspecialchars((string)$val) ?></div>
        </div>
        <?php endforeach; ?>
        <?php if ($f['nivel_escolaridade']): ?>
        <div class="col-sm-4">
          <div class="dado-label">Nível escolar</div>
          <div class="dado-valor"><?= ucwords(str_replace('_',' ',$f['nivel_escolaridade'])) ?></div>
        </div>
        <?php endif; ?>
        <?php if ($f['curso']): ?>
        <div class="col-sm-5">
          <div class="dado-label">Curso / Instituição</div>
          <div class="dado-valor">
            <?= htmlspecialchars($f['curso']) ?>
            <?php if ($f['instituicao']): ?> — <?= htmlspecialchars($f['instituicao']) ?><?php endif; ?>
            <?php if ($f['ano_conclusao']): ?> (<?= $f['ano_conclusao'] ?>)<?php endif; ?>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </div>

    <?php if ($f['observacoes']): ?>
    <div class="ficha-section">
      <div class="ficha-section-title"><i class="bi bi-chat-text"></i> Observações</div>
      <p class="mb-0 text-muted" style="font-size:.9rem"><?= nl2br(htmlspecialchars($f['observacoes'])) ?></p>
    </div>
    <?php endif; ?>

    <!-- Documentos anexos -->
    <div class="ficha-section">
      <div class="ficha-section-title d-flex justify-content-between align-items-center">
        <span><i class="bi bi-paperclip"></i> Documentos Anexos</span>
        <span class="badge rounded-pill" style="background:var(--kf-primary-light);color:var(--kf-primary)">
          <?= count($documentos) ?>
        </span>
      </div>

      <?php
      // Doc identificação principal
      if (!empty($f['doc_identificacao_url'])):
      ?>
      <div class="d-flex align-items-center justify-content-between p-2 rounded mb-2"
           style="background:#f8f9fa">
        <div class="d-flex align-items-center gap-2">
          <i class="bi bi-file-earmark-pdf text-danger fs-5"></i>
          <div>
            <div class="small fw-semibold">Documento de Identificação</div>
            <div class="text-muted" style="font-size:.75rem"><?= htmlspecialchars($f['doc_identificacao_nome'] ?? '') ?></div>
          </div>
        </div>
        <a href="<?= $APP ?>/uploads/<?= htmlspecialchars($f['doc_identificacao_url']) ?>"
           target="_blank" class="btn btn-sm btn-outline-secondary py-0 px-2">
          <i class="bi bi-eye"></i>
        </a>
      </div>
      <?php endif; ?>

      <?php if (!empty($f['doc_complementar_url'])): ?>
      <div class="d-flex align-items-center justify-content-between p-2 rounded mb-2"
           style="background:#f8f9fa">
        <div class="d-flex align-items-center gap-2">
          <i class="bi bi-file-earmark-pdf text-danger fs-5"></i>
          <div>
            <div class="small fw-semibold">Documento Complementar</div>
            <div class="text-muted" style="font-size:.75rem"><?= htmlspecialchars($f['doc_complementar_nome'] ?? '') ?></div>
          </div>
        </div>
        <a href="<?= $APP ?>/uploads/<?= htmlspecialchars($f['doc_complementar_url']) ?>"
           target="_blank" class="btn btn-sm btn-outline-secondary py-0 px-2">
          <i class="bi bi-eye"></i>
        </a>
      </div>
      <?php endif; ?>

      <?php foreach ($documentos as $doc): ?>
      <div class="d-flex align-items-center justify-content-between p-2 rounded mb-2"
           style="background:#f8f9fa">
        <div class="d-flex align-items-center gap-2">
          <i class="bi bi-file-earmark-text text-secondary fs-5"></i>
          <div>
            <div class="small fw-semibold"><?= htmlspecialchars($doc['titulo']) ?></div>
            <div class="text-muted" style="font-size:.75rem">
              <?= ucfirst($doc['tipo']) ?> · <?= htmlspecialchars($doc['ficheiro_nome']) ?>
            </div>
          </div>
        </div>
        <a href="<?= $APP ?>/uploads/<?= htmlspecialchars($doc['ficheiro_url']) ?>"
           target="_blank" class="btn btn-sm btn-outline-secondary py-0 px-2">
          <i class="bi bi-eye"></i>
        </a>
      </div>
      <?php endforeach; ?>

      <?php if (!$f['doc_identificacao_url'] && !$f['doc_complementar_url'] && empty($documentos)): ?>
      <p class="text-muted small mb-0 text-center py-2">Nenhum documento carregado</p>
      <?php endif; ?>
    </div>

    <!-- Histórico de credenciais -->
    <?php if ($historico && $isAdmin): ?>
    <div class="ficha-section">
      <div class="ficha-section-title"><i class="bi bi-clock-history"></i> Histórico de Credenciais</div>
      <div style="max-height:200px;overflow-y:auto">
        <?php foreach ($historico as $h): ?>
        <div class="d-flex align-items-start gap-2 pb-2 mb-2 border-bottom" style="font-size:.8rem">
          <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
               style="width:26px;height:26px;background:var(--kf-primary-light)">
            <i class="bi bi-shield-check" style="color:var(--kf-primary);font-size:.7rem"></i>
          </div>
          <div>
            <span class="fw-semibold"><?= ucfirst($h['acao']) ?></span>
            <?php if ($h['perfil_novo']): ?>
            → <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill"><?= $h['perfil_novo'] ?></span>
            <?php endif; ?>
            <div class="text-muted">
              por <?= htmlspecialchars($h['executado_por_nome'] ?? '—') ?>
              · <?= date('d/m/Y H:i', strtotime($h['criado_em'])) ?>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

  </div><!-- /col principal -->

  <!-- Coluna lateral: acesso ao sistema -->
  <div class="col-12 col-xl-4">
    <div class="ficha-section">
      <div class="ficha-section-title"><i class="bi bi-shield-lock"></i> Acesso ao Sistema</div>

      <?php if ($f['usuario_id']): ?>
      <!-- Já tem acesso -->
      <div class="text-center mb-3">
        <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-2"
             style="width:52px;height:52px;background:var(--kf-primary-light)">
          <i class="bi bi-shield-check fs-4" style="color:var(--kf-primary)"></i>
        </div>
        <span class="badge bg-<?= $pc ?>-subtle text-<?= $pc ?> border border-<?= $pc ?>-subtle rounded-pill px-3 py-1 fs-6">
          <?= $pl ?>
        </span>
        <div class="text-muted small mt-2"><?= htmlspecialchars($f['usuario_email'] ?? '') ?></div>
        <?php if ($f['ultimo_login']): ?>
        <div class="text-muted small">Último acesso: <?= date('d/m/Y H:i', strtotime($f['ultimo_login'])) ?></div>
        <?php endif; ?>
        <?php if (!$f['acesso_activo']): ?>
        <div class="alert alert-warning py-2 px-3 mt-2 small">
          <i class="bi bi-exclamation-triangle me-1"></i>Conta desactivada
        </div>
        <?php endif; ?>
        <?php if (!empty($f['bloqueado_ate']) && strtotime($f['bloqueado_ate']) > time()): ?>
        <div class="alert alert-danger py-2 px-3 mt-2 small">
          <i class="bi bi-lock me-1"></i>Conta bloqueada até <?= date('d/m/Y H:i', strtotime($f['bloqueado_ate'])) ?>
          <span class="text-muted">(<?= $f['tentativas_login'] ?> tentativas)</span>
        </div>
        <?php endif; ?>
      </div>
      <?php if ($isAdmin): ?>
      <hr class="my-3">
      <p class="small text-muted mb-2">Actualizar credenciais:</p>
      <?php endif; ?>
      <?php else: ?>
      <!-- Sem acesso -->
      <div class="text-center mb-3">
        <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-2"
             style="width:52px;height:52px;background:#f8f9fa">
          <i class="bi bi-shield-x fs-4 text-muted"></i>
        </div>
        <p class="text-muted small">Este funcionário ainda não tem acesso ao sistema.</p>
      </div>
      <?php endif; ?>

      <?php if ($isAdmin): ?>
      <form method="POST" action="<?= $APP ?>/funcionarios/<?= $f['id'] ?>/credenciais">
        <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
        <div class="mb-2">
          <label class="form-label small">Email de acesso</label>
          <input type="email" name="email" class="form-control form-control-sm"
                 value="<?= htmlspecialchars($f['usuario_email'] ?? $f['email_pessoal'] ?? '') ?>"
                 required placeholder="email@kewanfarma.mz">
        </div>
        <div class="mb-2">
          <label class="form-label small">Perfil de acesso</label>
          <select name="perfil" class="form-select form-select-sm" required>
            <option value="">Seleccionar...</option>
            <option value="admin"        <?= ($f['perfil']??'') === 'admin'        ? 'selected':'' ?>>Administrador</option>
            <option value="farmaceutico" <?= ($f['perfil']??'') === 'farmaceutico' ? 'selected':'' ?>>Farmacêutico</option>
            <option value="caixa"        <?= ($f['perfil']??'') === 'caixa'        ? 'selected':'' ?>>Caixa</option>
            <option value="tecnico"      <?= ($f['perfil']??'') === 'tecnico'      ? 'selected':'' ?>>Técnico</option>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label small">
            <?= $f['usuario_id'] ? 'Nova senha (deixe em branco para manter)' : 'Senha de acesso' ?>
          </label>
          <input type="password" name="senha" class="form-control form-control-sm"
                 <?= !$f['usuario_id'] ? 'required' : '' ?>
                 placeholder="Mínimo 8 caracteres" minlength="8" autocomplete="new-password">
        </div>
        <button type="submit" class="btn btn-sm w-100" style="background:var(--kf-primary);color:#fff;border:none">
          <i class="bi bi-shield-<?= $f['usuario_id'] ? 'check' : 'plus' ?> me-1"></i>
          <?= $f['usuario_id'] ? 'Actualizar Credenciais' : 'Criar Acesso ao Sistema' ?>
        </button>
      </form>
      <?php endif; ?>
    </div>

    <!-- Resumo rápido -->
    <div class="ficha-section">
      <div class="ficha-section-title"><i class="bi bi-info-circle"></i> Resumo</div>
      <div class="row g-2" style="font-size:.82rem">
        <div class="col-6">
          <div class="text-muted">Admissão</div>
          <div class="fw-semibold"><?= $f['data_admissao'] ? date('d/m/Y', strtotime($f['data_admissao'])) : '—' ?></div>
        </div>
        <div class="col-6">
          <div class="text-muted">Salário</div>
          <div class="fw-semibold">MT <?= number_format((float)($f['salario']??0), 0, ',', '.') ?></div>
        </div>
        <div class="col-12 mt-1">
          <div class="text-muted">Registado em</div>
          <div class="fw-semibold"><?= date('d/m/Y H:i', strtotime($f['criado_em'])) ?></div>
        </div>
        <?php if ($f['criado_por_nome']): ?>
        <div class="col-12">
          <div class="text-muted">Registado por</div>
          <div class="fw-semibold"><?= htmlspecialchars($f['criado_por_nome']) ?></div>
        </div>
        <?php endif; ?>
      </div>
    </div>

  </div><!-- /col lateral -->
</div>

<?php if (!empty($abrir_contrato)): ?>
<script>
window.open('<?= $APP ?>/funcionarios/<?= $f['id'] ?>/contrato', '_blank');
</script>
<?php endif; ?>
