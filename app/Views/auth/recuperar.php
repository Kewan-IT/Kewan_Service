<?php
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<h2>Recuperar senha</h2>
<p class="subtitle">Introduza o seu email e enviaremos as instruções para repor a sua senha.</p>

<?php if (!empty($sucesso)): ?>
  <div class="alert alert-success d-flex align-items-center gap-2 py-2" role="alert">
    <i class="bi bi-check-circle-fill"></i>
    <span><?= htmlspecialchars($sucesso) ?></span>
  </div>
<?php endif; ?>

<?php if (!empty($erro)): ?>
  <div class="alert alert-danger d-flex align-items-center gap-2 py-2" role="alert">
    <i class="bi bi-exclamation-triangle-fill"></i>
    <span><?= htmlspecialchars($erro) ?></span>
  </div>
<?php endif; ?>

<form action="<?= $_ENV['APP_URL'] ?? '' ?>/auth/recuperar" method="POST" novalidate>
  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

  <div class="mb-4">
    <label for="email" class="form-label">
      <i class="bi bi-envelope me-1"></i>Email da conta
    </label>
    <input
      type="email"
      id="email"
      name="email"
      class="form-control"
      placeholder="utilizador@kewanfarma.mz"
      autocomplete="email"
      autofocus
      required
    >
  </div>

  <button type="submit" class="btn-login">
    <i class="bi bi-send me-2"></i>Enviar instruções
  </button>
</form>

<div class="text-center mt-4">
  <a href="<?= $_ENV['APP_URL'] ?? '' ?>/auth/login" class="link-recuperar">
    <i class="bi bi-arrow-left me-1"></i>Voltar ao login
  </a>
</div>
