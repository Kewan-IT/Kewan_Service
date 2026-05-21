<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h1 class="page-title">
      Bem-vindo, <?= htmlspecialchars(explode(' ', $_SESSION['usuario_nome'] ?? 'Utilizador')[0]) ?>!
    </h1>
    <p class="page-subtitle">
      Painel de controlo da KewanFarma
    </p>
  </div>
</div>

<div class="row g-3">
  <div class="col-12">
    <div class="alert alert-success d-flex align-items-center gap-2">
      <i class="bi bi-check-circle-fill fs-5"></i>
      <span>Sistema instalado com sucesso! Módulos em desenvolvimento.</span>
    </div>
  </div>
</div>
