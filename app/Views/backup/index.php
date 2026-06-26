<?php $APP = $_ENV['APP_URL'] ?? ''; ?>

<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
  <div>
    <h1 class="page-title mb-1">
      <i class="bi bi-cloud-download me-2"></i>Backup
    </h1>
    <p class="page-subtitle mb-0">Faça cópias de segurança da base de dados e configure o backup automático.</p>
  </div>
  <a href="<?= $APP ?>/dashboard" class="btn btn-outline-secondary btn-sm">
    <i class="bi bi-arrow-left me-1"></i>Voltar ao painel
  </a>
</div>

<?php if (!empty($flash_sucesso)): ?>
<span id="kf-flash-sucesso" data-msg="<?= htmlspecialchars($flash_sucesso) ?>" hidden></span>
<?php endif; ?>

<?php if (!empty($flash_erro)): ?>
<span id="kf-flash-erro" data-msg="<?= htmlspecialchars($flash_erro) ?>" hidden></span>
<?php endif; ?>

<div class="row g-4 mb-4">

  <!-- ── Backup Manual ─────────────────────────────────────────── -->
  <div class="col-12 col-lg-6">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-white border-bottom-0 py-3">
        <h6 class="fw-bold mb-0"><i class="bi bi-database-add me-2 text-primary"></i>Backup Manual</h6>
      </div>
      <div class="card-body d-flex flex-column">
        <p class="text-muted small mb-3">
          Cria imediatamente uma cópia completa da base de dados no servidor.
          Use quando precisar de uma cópia antes de alterações importantes.
        </p>
        <div class="alert alert-light border mb-3">
          <i class="bi bi-info-circle me-2"></i>
          O ficheiro é guardado em <code>storage/backups/</code> no servidor.
          Pode descarregá-lo após a criação.
        </div>
        <form method="POST" action="<?= $APP ?>/backup/fazer" class="mt-auto">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
          <button type="submit" class="btn btn-primary w-100"
                  onclick="return confirm('Deseja criar um backup agora?')">
            <i class="bi bi-database-add me-2"></i>Fazer backup agora
          </button>
        </form>
      </div>
    </div>
  </div>

  <!-- ── Hora do Backup Automático ────────────────────────────── -->
  <div class="col-12 col-lg-6">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-white border-bottom-0 py-3">
        <h6 class="fw-bold mb-0"><i class="bi bi-clock me-2 text-success"></i>Hora do Backup Automático</h6>
      </div>
      <div class="card-body d-flex flex-column">
        <p class="text-muted small mb-3">
          Define a hora diária em que o servidor deve executar o backup automático.
          Esta hora é usada para configurar a tarefa <strong>cron</strong> no servidor.
        </p>

        <form method="POST" action="<?= $APP ?>/backup/configurar-hora">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
          <div class="mb-3">
            <label class="form-label small fw-semibold">Hora do backup automático</label>
            <input type="time" name="backup_hora_automatico" class="form-control"
                   value="<?= htmlspecialchars($backup_hora) ?>" required>
            <div class="text-muted small mt-1">Formato 24h. Ex: 19:30 = 19h30 todos os dias.</div>
          </div>

          <?php
            [$hh, $mm] = explode(':', $backup_hora . ':00');
            $cron_expr = ltrim($mm, '0') ?: '0';
            $cron_expr .= ' ' . (ltrim($hh, '0') ?: '0') . ' * * *';
          ?>
          <div class="alert alert-light border mb-3 p-2">
            <div class="small fw-semibold mb-1"><i class="bi bi-terminal me-1"></i>Linha cron correspondente:</div>
            <code class="small"><?= $cron_expr ?> /usr/bin/php /caminho/do/projeto/backup-automatico.php</code>
          </div>

          <button type="submit" class="btn btn-success w-100 mt-auto">
            <i class="bi bi-save me-2"></i>Guardar hora do backup
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- ── Lista de Backups ──────────────────────────────────────────── -->
<div class="card border-0 shadow-sm">
  <div class="card-header bg-white border-bottom-0 py-3 d-flex align-items-center justify-content-between">
    <h6 class="fw-bold mb-0"><i class="bi bi-archive me-2"></i>Backups disponíveis</h6>
    <span class="badge bg-secondary"><?= count($backups) ?> ficheiro(s)</span>
  </div>
  <div class="card-body p-0">

    <?php if (empty($backups)): ?>
    <div class="text-center py-5 text-muted">
      <i class="bi bi-inbox display-5 d-block mb-2"></i>
      Nenhum backup encontrado. Clique em <strong>Fazer backup agora</strong> para criar o primeiro.
    </div>

    <?php else: ?>
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th class="ps-3">Ficheiro</th>
            <th>Data</th>
            <th>Tamanho</th>
            <th class="text-end pe-3">Acções</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($backups as $b): ?>
          <tr>
            <td class="ps-3">
              <i class="bi bi-file-earmark-code me-2 text-primary"></i>
              <span class="small font-monospace"><?= htmlspecialchars($b['nome']) ?></span>
            </td>
            <td class="small text-muted"><?= htmlspecialchars($b['data']) ?></td>
            <td class="small text-muted"><?= htmlspecialchars($b['tamanho']) ?></td>
            <td class="text-end pe-3">
              <a href="<?= $APP ?>/backup/descarregar?ficheiro=<?= urlencode($b['nome']) ?>"
                 class="btn btn-sm btn-outline-primary me-1">
                <i class="bi bi-download me-1"></i>Descarregar
              </a>
              <form method="POST" action="<?= $APP ?>/backup/apagar" class="d-inline">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                <input type="hidden" name="ficheiro" value="<?= htmlspecialchars($b['nome']) ?>">
                <button type="submit" class="btn btn-sm btn-outline-danger"
                        onclick="return confirm('Apagar este backup permanentemente?')">
                  <i class="bi bi-trash me-1"></i>Apagar
                </button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>

  </div>
  <?php if (!empty($backups)): ?>
  <div class="card-footer bg-white border-top-0 py-2">
    <small class="text-muted">
      <i class="bi bi-info-circle me-1"></i>
      Backups com mais de 30 dias são eliminados automaticamente pelo script de backup automático.
    </small>
  </div>
  <?php endif; ?>
</div>
