<?php
$appUrl  = $_ENV['APP_URL'] ?? '';
$f       = $funcionario;
$isAdmin = $_SESSION['perfil'] === 'admin';
$badge   = match($f['status']) {
  'activo'    => ['success',   'Activo'],
  'inactivo'  => ['secondary', 'Inactivo'],
  'suspenso'  => ['warning',   'Suspenso'],
  'desligado' => ['danger',    'Desligado'],
  default     => ['secondary', $f['status']],
};
?>

<div class="card border-0 shadow-sm mb-4">
  <div class="card-body p-4">
    <div class="d-flex align-items-center gap-4 flex-wrap">
      <div class="rounded-circle overflow-hidden bg-light border d-flex align-items-center justify-content-center flex-shrink-0"
           style="width:90px;height:90px">
        <?php if (!empty($f['foto_url'])): ?>
          <img src="<?= $appUrl ?>/storage/<?= htmlspecialchars($f['foto_url']) ?>"
               style="width:100%;height:100%;object-fit:cover" alt="Foto">
        <?php else: ?>
          <i class="bi bi-person-fill text-secondary" style="font-size:2.5rem"></i>
        <?php endif; ?>
      </div>
      <div class="flex-grow-1">
        <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
          <h2 class="h5 fw-bold mb-0"><?= htmlspecialchars($f['nome_completo']) ?></h2>
          <span class="badge bg-<?= $badge[0] ?> bg-opacity-15 text-<?= $badge[0] ?> border border-<?= $badge[0] ?>-subtle">
            <?= $badge[1] ?>
          </span>
        </div>
        <div class="text-muted mb-2" style="font-size:13px">
          <?= htmlspecialchars($f['cargo_nome']) ?> &bull; Nº <?= htmlspecialchars($f['numero_funcionario']) ?>
        </div>
        <div class="d-flex gap-3 flex-wrap" style="font-size:13px">
          <span><i class="bi bi-telephone me-1 text-success"></i><?= htmlspecialchars($f['telefone_principal']) ?></span>
          <?php if ($f['email_pessoal']): ?>
          <span><i class="bi bi-envelope me-1 text-success"></i><?= htmlspecialchars($f['email_pessoal']) ?></span>
          <?php endif; ?>
          <span><i class="bi bi-geo-alt me-1 text-success"></i><?= htmlspecialchars($f['cidade']) ?></span>
        </div>
      </div>
      <?php if ($isAdmin): ?>
      <div class="d-flex gap-2 flex-wrap">
        <a href="<?= $appUrl ?>/funcionarios/<?= $f['id'] ?>/editar" class="btn btn-outline-primary btn-sm">
          <i class="bi bi-pencil me-1"></i>Editar
        </a>
        <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalCredenciais">
          <i class="bi bi-key me-1"></i><?= $f['usuario_id'] ? 'Gerir Acesso' : 'Atribuir Acesso' ?>
        </button>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<div class="row g-4">
  <div class="col-md-7">

    <!-- Dados pessoais -->
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-header bg-white py-3">
        <h6 class="fw-bold mb-0"><i class="bi bi-person me-2 text-success"></i>Dados Pessoais</h6>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <?php
          $campos = [
            'Data Nascimento' => $f['data_nascimento'] ? date('d/m/Y', strtotime($f['data_nascimento'])) : '—',
            'Sexo'            => match($f['sexo']) { 'M'=>'Masculino','F'=>'Feminino',default=>$f['sexo'] },
            'Estado Civil'    => $f['estado_civil'] ? ucfirst(str_replace('_',' ',$f['estado_civil'])) : '—',
            'Nacionalidade'   => $f['nacionalidade'] ?? '—',
            'Nº BI'           => $f['bi_numero'],
            'Validade BI'     => $f['bi_validade'] ? date('d/m/Y', strtotime($f['bi_validade'])) : '—',
            'NUIT'            => $f['nuit'] ?? '—',
            'NRPS'            => $f['nrps'] ?? '—',
          ];
          foreach ($campos as $label => $valor):
          ?>
          <div class="col-6">
            <div class="text-muted" style="font-size:11px;text-transform:uppercase;letter-spacing:.05em"><?= $label ?></div>
            <div style="font-size:14px;font-weight:500"><?= htmlspecialchars((string)$valor) ?></div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <!-- Dados profissionais -->
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-header bg-white py-3">
        <h6 class="fw-bold mb-0"><i class="bi bi-briefcase me-2 text-success"></i>Dados Profissionais</h6>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <?php
          $prof = [
            'Cargo'         => $f['cargo_nome'],
            'Contrato'      => ucfirst(str_replace('_',' ',$f['tipo_contrato'])),
            'Data Admissão' => date('d/m/Y', strtotime($f['data_admissao'])),
            'Salário'       => number_format($f['salario'],2,',','.') . ' MZN',
            'Escolaridade'  => $f['nivel_escolaridade'] ? ucfirst(str_replace('_',' ',$f['nivel_escolaridade'])) : '—',
            'Curso'         => $f['curso'] ?? '—',
            'Instituição'   => $f['instituicao'] ?? '—',
            'Ano Conclusão' => $f['ano_conclusao'] ?? '—',
          ];
          foreach ($prof as $label => $valor):
          ?>
          <div class="col-6">
            <div class="text-muted" style="font-size:11px;text-transform:uppercase;letter-spacing:.05em"><?= $label ?></div>
            <div style="font-size:14px;font-weight:500"><?= htmlspecialchars((string)$valor) ?></div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

  </div>

  <div class="col-md-5">

    <!-- Acesso ao sistema -->
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-header bg-white py-3">
        <h6 class="fw-bold mb-0"><i class="bi bi-shield-lock me-2 text-success"></i>Acesso ao Sistema</h6>
      </div>
      <div class="card-body">
        <?php if ($f['usuario_id']): ?>
        <div class="d-flex gap-2 mb-2">
          <span class="badge bg-<?= $f['usuario_ativo'] ? 'success' : 'danger' ?>">
            <?= $f['usuario_ativo'] ? 'Activo' : 'Bloqueado' ?>
          </span>
          <span class="badge bg-secondary bg-opacity-15 text-secondary border">
            <?= ucfirst($f['usuario_perfil']) ?>
          </span>
        </div>
        <div style="font-size:13px"><i class="bi bi-envelope me-1 text-muted"></i><?= htmlspecialchars($f['usuario_email']) ?></div>
        <?php if ($f['ultimo_login']): ?>
        <div class="text-muted mt-1" style="font-size:12px">
          Último acesso: <?= date('d/m/Y H:i', strtotime($f['ultimo_login'])) ?>
        </div>
        <?php endif; ?>
        <?php else: ?>
        <div class="text-center py-3">
          <i class="bi bi-person-slash text-muted" style="font-size:2rem"></i>
          <p class="text-muted mt-2 mb-2" style="font-size:13px">Sem acesso ao sistema</p>
          <?php if ($isAdmin): ?>
          <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalCredenciais">
            <i class="bi bi-key me-1"></i>Atribuir acesso
          </button>
          <?php endif; ?>
        </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Contactos -->
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-header bg-white py-3">
        <h6 class="fw-bold mb-0"><i class="bi bi-telephone me-2 text-success"></i>Contactos</h6>
      </div>
      <div class="card-body" style="font-size:13px">
        <div class="mb-2">
          <div class="text-muted" style="font-size:11px">Telefone Principal</div>
          <strong><?= htmlspecialchars($f['telefone_principal']) ?></strong>
        </div>
        <?php if ($f['telefone_alternativo']): ?>
        <div class="mb-2">
          <div class="text-muted" style="font-size:11px">Telefone Alternativo</div>
          <?= htmlspecialchars($f['telefone_alternativo']) ?>
        </div>
        <?php endif; ?>
        <div class="mb-2">
          <div class="text-muted" style="font-size:11px">Endereço</div>
          <?= htmlspecialchars($f['endereco']) ?>, <?= htmlspecialchars($f['cidade']) ?>
        </div>
        <?php if ($f['emergencia_nome']): ?>
        <hr>
        <div class="text-muted" style="font-size:11px;text-transform:uppercase">Emergência</div>
        <div><?= htmlspecialchars($f['emergencia_nome']) ?>
          <?php if ($f['emergencia_parentesco']): ?>(<?= htmlspecialchars($f['emergencia_parentesco']) ?>)<?php endif; ?>
          &mdash; <?= htmlspecialchars($f['emergencia_telefone'] ?? '') ?>
        </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Documentos -->
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white py-3">
        <h6 class="fw-bold mb-0"><i class="bi bi-file-earmark me-2 text-success"></i>Documentos</h6>
      </div>
      <div class="card-body">
        <?php if (!$f['doc_identificacao_url'] && !$f['doc_complementar_url']): ?>
        <p class="text-muted text-center py-2 mb-0" style="font-size:13px">Sem documentos carregados.</p>
        <?php endif; ?>
        <?php if ($f['doc_identificacao_url']): ?>
        <div class="d-flex align-items-center gap-2 p-2 border rounded mb-2">
          <i class="bi bi-file-earmark-pdf text-danger fs-5"></i>
          <div class="flex-grow-1 min-width-0">
            <div style="font-size:13px;font-weight:500">BI / Passaporte</div>
            <div class="text-muted text-truncate" style="font-size:11px"><?= htmlspecialchars($f['doc_identificacao_nome'] ?? '') ?></div>
          </div>
          <a href="<?= $appUrl ?>/storage/<?= htmlspecialchars($f['doc_identificacao_url']) ?>"
             target="_blank" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a>
        </div>
        <?php endif; ?>
        <?php if ($f['doc_complementar_url']): ?>
        <div class="d-flex align-items-center gap-2 p-2 border rounded">
          <i class="bi bi-file-earmark-pdf text-danger fs-5"></i>
          <div class="flex-grow-1 min-width-0">
            <div style="font-size:13px;font-weight:500">CV / Certificado</div>
            <div class="text-muted text-truncate" style="font-size:11px"><?= htmlspecialchars($f['doc_complementar_nome'] ?? '') ?></div>
          </div>
          <a href="<?= $appUrl ?>/storage/<?= htmlspecialchars($f['doc_complementar_url']) ?>"
             target="_blank" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a>
        </div>
        <?php endif; ?>
      </div>
    </div>

  </div>
</div>

<!-- Modal Credenciais -->
<?php if ($isAdmin): ?>
<div class="modal fade" id="modalCredenciais" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <form action="<?= $appUrl ?>/funcionarios/<?= $f['id'] ?>/credenciais" method="POST">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
        <div class="modal-header">
          <h5 class="modal-title fw-bold">
            <i class="bi bi-key me-2 text-success"></i>
            <?= $f['usuario_id'] ? 'Gerir Acesso' : 'Atribuir Acesso' ?>
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p class="text-muted mb-3" style="font-size:13px">
            Credenciais para <strong><?= htmlspecialchars($f['nome_completo']) ?></strong>
          </p>
          <div class="mb-3">
            <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
            <input type="email" name="email" class="form-control" required
                   value="<?= htmlspecialchars($f['usuario_email'] ?? $f['email_pessoal'] ?? '') ?>"
                   placeholder="email@kewanfarma.mz">
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">
              Senha <?= !$f['usuario_id'] ? '<span class="text-danger">*</span>' : '(deixe vazio para manter)' ?>
            </label>
            <div class="input-group">
              <input type="password" name="senha" id="modal-senha" class="form-control"
                     placeholder="Mínimo 8 caracteres" minlength="8"
                     <?= !$f['usuario_id'] ? 'required' : '' ?>>
              <button type="button" class="btn btn-outline-secondary"
                      onclick="var c=document.getElementById('modal-senha');c.type=c.type=='password'?'text':'password'">
                <i class="bi bi-eye"></i>
              </button>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Perfil <span class="text-danger">*</span></label>
            <select name="perfil" class="form-select" required>
              <option value="">Seleccione</option>
              <option value="admin"        <?= ($f['usuario_perfil']??'')==='admin'?'selected':'' ?>>Administrador</option>
              <option value="farmaceutico" <?= ($f['usuario_perfil']??'')==='farmaceutico'?'selected':'' ?>>Farmacêutico</option>
              <option value="caixa"        <?= ($f['usuario_perfil']??'')==='caixa'?'selected':'' ?>>Operador de Caixa</option>
              <option value="tecnico"      <?= ($f['usuario_perfil']??'')==='tecnico'?'selected':'' ?>>Técnico</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-success"><i class="bi bi-check-lg me-1"></i>Guardar</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php endif; ?>
