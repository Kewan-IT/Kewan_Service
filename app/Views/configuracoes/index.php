<?php $APP = $_ENV['APP_URL'] ?? ''; ?>

<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
  <div>
    <h1 class="page-title mb-1">
      <i class="bi bi-gear me-2"></i>Configurações
    </h1>
    <p class="page-subtitle mb-0">Personalize a sua farmácia, o logo e os dados usados nos documentos.</p>
  </div>
  <a href="<?= $APP ?>/dashboard" class="btn btn-outline-secondary btn-sm">
    <i class="bi bi-arrow-left me-1"></i>Voltar ao painel
  </a>
</div>

<?php if (!empty($flash_sucesso)): ?>
<div class="alert alert-success py-2 px-3 mb-3">
  <i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($flash_sucesso) ?>
</div>
<?php endif; ?>

<?php if (!empty($flash_erro)): ?>
<div class="alert alert-danger py-2 px-3 mb-3">
  <i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($flash_erro) ?>
</div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data" action="<?= $APP ?>/configuracoes">
  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

  <div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom-0 py-3">
      <h6 class="fw-bold mb-0"><i class="bi bi-building me-2"></i>Identidade da farmácia</h6>
    </div>
    <div class="card-body">
      <div class="row g-3">
        <div class="col-12 col-lg-6">
          <label class="form-label small fw-semibold">Nome da farmácia</label>
          <input type="text" name="nome_farmacia" class="form-control"
                 value="<?= htmlspecialchars($config['nome_farmacia']) ?>" placeholder="KewanFarma">
        </div>
        <div class="col-12 col-lg-6">
          <label class="form-label small fw-semibold">Moeda</label>
          <input type="text" name="moeda" class="form-control text-uppercase"
                 value="<?= htmlspecialchars($config['moeda']) ?>" placeholder="MZN">
        </div>
        <div class="col-12 col-lg-6">
          <label class="form-label small fw-semibold">NUIT</label>
          <input type="text" name="nuit_farmacia" class="form-control"
                 value="<?= htmlspecialchars($config['nuit_farmacia']) ?>" placeholder="123456789">
        </div>
        <div class="col-12 col-lg-6">
          <label class="form-label small fw-semibold">Telefone</label>
          <input type="text" name="telefone_farmacia" class="form-control"
                 value="<?= htmlspecialchars($config['telefone_farmacia']) ?>" placeholder="+258 84 000 0000">
        </div>
        <div class="col-12">
          <label class="form-label small fw-semibold">Endereço</label>
          <textarea name="endereco_farmacia" class="form-control" rows="3"
                    placeholder="Rua, bairro, cidade, província"><?= htmlspecialchars($config['endereco_farmacia']) ?></textarea>
        </div>
        <div class="col-12 col-lg-6">
          <label class="form-label small fw-semibold">Email de contacto</label>
          <input type="email" name="email_farmacia" class="form-control"
                 value="<?= htmlspecialchars($config['email_farmacia']) ?>" placeholder="info@kewanfarma.mz">
        </div>
      </div>
    </div>
  </div>

  <div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom-0 py-3">
      <h6 class="fw-bold mb-0"><i class="bi bi-image me-2"></i>Logo da farmácia</h6>
    </div>
    <div class="card-body">
      <?php if (!empty($config['logo_farmacia'])): ?>
      <div class="d-flex align-items-center gap-3 mb-3">
        <img src="<?= $APP ?>/storage/uploads/<?= htmlspecialchars($config['logo_farmacia']) ?>"
             alt="Logo da farmácia"
             style="max-height:90px; width:auto; border:1px solid #e5e7eb; border-radius:12px; background:#fff; padding:4px;">
        <div>
          <div class="fw-semibold">Logo actual</div>
          <div class="text-muted small">Substitua o logo quando quiser atualizar a identidade visual.</div>
        </div>
      </div>
      <?php else: ?>
      <div class="alert alert-light border mb-3">
        <i class="bi bi-info-circle me-2"></i>Nenhum logo carregado ainda. O campo abaixo permite adicionar um logo para os documentos.
      </div>
      <?php endif; ?>

      <label class="form-label small fw-semibold">Enviar novo logo</label>
      <input type="file" name="logo_farmacia" class="form-control" accept="image/png,image/jpeg,image/webp">
      <div class="text-muted small mt-2">Formatos aceitos: PNG, JPG e WEBP. Recomendado 500x500 px.</div>
    </div>
  </div>

  <div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom-0 py-3">
      <h6 class="fw-bold mb-0"><i class="bi bi-file-earmark-text me-2"></i>Parâmetros dos documentos</h6>
    </div>
    <div class="card-body">
      <div class="row g-3">
        <div class="col-12 col-md-4">
          <label class="form-label small fw-semibold">Prefixo de vendas</label>
          <input type="text" name="prefixo_venda" class="form-control text-uppercase"
                 value="<?= htmlspecialchars($config['prefixo_venda']) ?>" placeholder="VD">
        </div>
        <div class="col-12 col-md-4">
          <label class="form-label small fw-semibold">Prefixo de compras</label>
          <input type="text" name="prefixo_compra" class="form-control text-uppercase"
                 value="<?= htmlspecialchars($config['prefixo_compra']) ?>" placeholder="CP">
        </div>
        <div class="col-12 col-md-4">
          <label class="form-label small fw-semibold">IVA (%)</label>
          <input type="number" name="iva_percentagem" class="form-control"
                 min="0" step="0.01" value="<?= htmlspecialchars($config['iva_percentagem']) ?>">
        </div>
        <div class="col-12 col-md-4">
          <label class="form-label small fw-semibold">Dias de alerta de validade</label>
          <input type="number" name="dias_alerta_validade" class="form-control"
                 min="1" step="1" value="<?= htmlspecialchars($config['dias_alerta_validade']) ?>">
        </div>
      </div>
      <div class="alert alert-light border mt-3 mb-0">
        <i class="bi bi-lightbulb me-2"></i>Estas informações são usadas nos talões e nos documentos emitidos pelo sistema.
      </div>
    </div>
  </div>

  <div class="d-flex justify-content-end">
    <button type="submit" class="btn btn-success">
      <i class="bi bi-save me-2"></i>Guardar configurações
    </button>
  </div>
</form>
