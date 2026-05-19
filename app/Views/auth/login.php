<?php
// Gerar token CSRF se não existir
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<h2>Bem-vindo de volta</h2>
<p class="subtitle">Introduza as suas credenciais para aceder ao sistema.</p>

<?php if (!empty($erro)): ?>
  <div class="alert alert-danger d-flex align-items-center gap-2 py-2" role="alert">
    <i class="bi bi-exclamation-triangle-fill"></i>
    <span><?= htmlspecialchars($erro) ?></span>
  </div>
<?php endif; ?>

<?php if (!empty($aviso)): ?>
  <div class="alert alert-info d-flex align-items-center gap-2 py-2" role="alert">
    <i class="bi bi-info-circle-fill"></i>
    <span><?= htmlspecialchars($aviso) ?></span>
  </div>
<?php endif; ?>

<form action="<?= $_ENV['APP_URL'] ?? '' ?>/auth/login" method="POST" novalidate>
  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

  <!-- Email -->
  <div class="mb-3">
    <label for="email" class="form-label">
      <i class="bi bi-envelope me-1"></i>Email
    </label>
    <input
      type="email"
      id="email"
      name="email"
      class="form-control"
      placeholder="utilizador@kewanfarma.mz"
      value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
      autocomplete="email"
      autofocus
      required
    >
  </div>

  <!-- Senha -->
  <div class="mb-2">
    <label for="senha" class="form-label">
      <i class="bi bi-lock me-1"></i>Senha
    </label>
    <div class="input-group">
      <input
        type="password"
        id="senha"
        name="senha"
        class="form-control"
        placeholder="••••••••"
        autocomplete="current-password"
        required
      >
      <button
        type="button"
        class="btn btn-outline-secondary"
        onclick="toggleSenha()"
        tabindex="-1"
        aria-label="Mostrar ou ocultar senha"
      >
        <i class="bi bi-eye" id="icone-olho"></i>
      </button>
    </div>
  </div>

  <!-- Esqueceu a senha -->
  <div class="text-end mb-4">
    <a href="<?= $_ENV['APP_URL'] ?? '' ?>/auth/recuperar" class="link-recuperar">
      Esqueceu a senha?
    </a>
  </div>

  <!-- Botão de login -->
  <button type="submit" class="btn-login" id="btn-login">
    <span id="btn-texto"><i class="bi bi-box-arrow-in-right me-2"></i>Entrar</span>
    <span id="btn-loading" class="d-none">
      <span class="spinner-border spinner-border-sm me-2"></span>A entrar...
    </span>
  </button>
</form>

<script>
function toggleSenha() {
  const campo = document.getElementById('senha');
  const icone = document.getElementById('icone-olho');
  if (campo.type === 'password') {
    campo.type = 'text';
    icone.className = 'bi bi-eye-slash';
  } else {
    campo.type = 'password';
    icone.className = 'bi bi-eye';
  }
}

document.querySelector('form').addEventListener('submit', function () {
  document.getElementById('btn-texto').classList.add('d-none');
  document.getElementById('btn-loading').classList.remove('d-none');
  document.getElementById('btn-login').disabled = true;
});
</script>
