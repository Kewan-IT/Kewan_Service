<?php
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<h2>Redefinir senha</h2>
<p class="subtitle">
  Olá, <strong><?= htmlspecialchars($nome) ?></strong>. Defina a sua nova senha de acesso.
</p>

<?php if (!empty($erro)): ?>
<div class="alert alert-danger d-flex align-items-center gap-2 py-2" role="alert">
  <i class="bi bi-exclamation-triangle-fill"></i>
  <span><?= htmlspecialchars($erro) ?></span>
</div>
<?php endif; ?>

<form action="<?= $_ENV['APP_URL'] ?? '' ?>/auth/reset" method="POST" novalidate id="form-reset">
  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
  <input type="hidden" name="token"  value="<?= htmlspecialchars($token) ?>">
  <input type="hidden" name="email"  value="<?= htmlspecialchars($email) ?>">

  <div class="mb-3">
    <label for="nova_senha" class="form-label">
      <i class="bi bi-lock me-1"></i>Nova senha
    </label>
    <div class="input-group">
      <input type="password" id="nova_senha" name="nova_senha"
             class="form-control" placeholder="Mínimo 8 caracteres"
             autocomplete="new-password" autofocus required minlength="8"
             oninput="verificarForca(this.value)">
      <button type="button" class="btn btn-outline-secondary"
              onclick="toggleVis('nova_senha','olho1')" tabindex="-1">
        <i class="bi bi-eye" id="olho1"></i>
      </button>
    </div>
    <!-- Barra de força da senha -->
    <div style="margin-top:6px;height:4px;background:#eee;border-radius:4px;overflow:hidden">
      <div id="forca-bar" style="height:100%;width:0%;border-radius:4px;transition:width .3s,background .3s"></div>
    </div>
    <div id="forca-txt" style="font-size:11px;color:#aaa;margin-top:3px"></div>
  </div>

  <div class="mb-4">
    <label for="confirmar_senha" class="form-label">
      <i class="bi bi-lock-fill me-1"></i>Confirmar nova senha
    </label>
    <div class="input-group">
      <input type="password" id="confirmar_senha" name="confirmar_senha"
             class="form-control" placeholder="Repita a nova senha"
             autocomplete="new-password" required minlength="8"
             oninput="verificarMatch()">
      <button type="button" class="btn btn-outline-secondary"
              onclick="toggleVis('confirmar_senha','olho2')" tabindex="-1">
        <i class="bi bi-eye" id="olho2"></i>
      </button>
    </div>
    <div id="match-txt" style="font-size:11px;margin-top:3px"></div>
  </div>

  <button type="submit" class="btn-login" id="btn-submit">
    <i class="bi bi-check-circle me-2"></i>Guardar nova senha
  </button>
</form>

<div class="text-center mt-4">
  <a href="<?= $_ENV['APP_URL'] ?? '' ?>/auth/login" class="link-recuperar">
    <i class="bi bi-arrow-left me-1"></i>Voltar ao login
  </a>
</div>

<script>
function toggleVis(id, olhoId) {
  const el = document.getElementById(id);
  const ic = document.getElementById(olhoId);
  el.type = el.type === 'password' ? 'text' : 'password';
  ic.className = el.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
}

function verificarForca(v) {
  const bar = document.getElementById('forca-bar');
  const txt = document.getElementById('forca-txt');
  let score = 0;
  if (v.length >= 8)  score++;
  if (v.length >= 12) score++;
  if (/[A-Z]/.test(v)) score++;
  if (/[0-9]/.test(v)) score++;
  if (/[^A-Za-z0-9]/.test(v)) score++;
  const niveis = [
    [0, '#fee2e2', ''],
    [1, '#fca5a5', 'Muito fraca'],
    [2, '#fb923c', 'Fraca'],
    [3, '#facc15', 'Razoável'],
    [4, '#4ade80', 'Boa'],
    [5, '#22c55e', 'Forte'],
  ];
  const [, cor, label] = niveis[Math.min(score, 5)];
  bar.style.width  = (score * 20) + '%';
  bar.style.background = cor;
  txt.textContent = label;
  txt.style.color = score >= 4 ? '#166534' : score >= 3 ? '#854d0e' : '#991b1b';
}

function verificarMatch() {
  const a = document.getElementById('nova_senha').value;
  const b = document.getElementById('confirmar_senha').value;
  const m = document.getElementById('match-txt');
  const btn = document.getElementById('btn-submit');
  if (!b) { m.textContent = ''; btn.disabled = false; return; }
  if (a === b) {
    m.textContent = '✓ As senhas coincidem';
    m.style.color = '#166534';
    btn.disabled = false;
  } else {
    m.textContent = '✗ As senhas não coincidem';
    m.style.color = '#991b1b';
    btn.disabled = true;
  }
}
</script>
