<?php
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!-- Banner de alerta obrigatório -->
<div style="background:linear-gradient(135deg,#1a7f5a,#0d5c41);border-radius:10px;
            padding:16px 18px;margin-bottom:22px;color:#fff">
  <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px">
    <span style="font-size:22px">🔐</span>
    <strong style="font-size:14px">Primeiro acesso detectado</strong>
  </div>
  <p style="font-size:12px;opacity:.88;margin:0;line-height:1.5">
    Olá, <strong><?= htmlspecialchars($nome) ?></strong>! Por razões de segurança,
    deve definir uma senha pessoal antes de continuar a usar o sistema.
    A senha temporária atribuída pelo administrador expira neste passo.
  </p>
</div>

<?php if (!empty($erro)): ?>
<div class="alert alert-danger d-flex align-items-center gap-2 py-2 mb-3" role="alert">
  <i class="bi bi-exclamation-triangle-fill flex-shrink-0"></i>
  <span><?= htmlspecialchars($erro) ?></span>
</div>
<?php endif; ?>

<form action="<?= $_ENV['APP_URL'] ?? '' ?>/auth/trocar-senha" method="POST" novalidate id="form-trocar">
  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

  <!-- Senha actual (temporária) -->
  <div class="mb-3">
    <label class="form-label" for="senha_atual">
      <i class="bi bi-key me-1"></i>Senha actual (temporária)
    </label>
    <div class="input-group">
      <input type="password" id="senha_atual" name="senha_atual"
             class="form-control" placeholder="Senha atribuída pelo administrador"
             autocomplete="current-password" autofocus required>
      <button type="button" class="btn btn-outline-secondary" tabindex="-1"
              onclick="toggleVis('senha_atual','olho0')">
        <i class="bi bi-eye" id="olho0"></i>
      </button>
    </div>
  </div>

  <hr style="border-color:#eee;margin:14px 0">

  <!-- Nova senha -->
  <div class="mb-3">
    <label class="form-label" for="nova_senha">
      <i class="bi bi-lock me-1"></i>Nova senha pessoal
    </label>
    <div class="input-group">
      <input type="password" id="nova_senha" name="nova_senha"
             class="form-control" placeholder="Mínimo 8 caracteres"
             autocomplete="new-password" required minlength="8"
             oninput="verificarForca(this.value); verificarMatch()">
      <button type="button" class="btn btn-outline-secondary" tabindex="-1"
              onclick="toggleVis('nova_senha','olho1')">
        <i class="bi bi-eye" id="olho1"></i>
      </button>
    </div>
    <!-- Barra de força -->
    <div style="margin-top:5px;height:5px;background:#eee;border-radius:4px;overflow:hidden">
      <div id="forca-bar" style="height:100%;width:0;border-radius:4px;transition:width .3s,background .3s"></div>
    </div>
    <div style="display:flex;justify-content:space-between;margin-top:3px">
      <span id="forca-txt" style="font-size:10.5px;color:#aaa"></span>
      <span style="font-size:10.5px;color:#aaa">Mín. 8 caracteres</span>
    </div>
    <!-- Requisitos visuais -->
    <div id="requisitos" style="margin-top:8px;display:none;background:#f8fffe;
         border:1px solid #d0efe4;border-radius:6px;padding:8px 10px;font-size:11px">
      <div id="req-len"  class="req">✗ Pelo menos 8 caracteres</div>
      <div id="req-mai"  class="req">✗ Uma letra maiúscula</div>
      <div id="req-num"  class="req">✗ Um número</div>
    </div>
  </div>

  <!-- Confirmar nova senha -->
  <div class="mb-4">
    <label class="form-label" for="confirmar_senha">
      <i class="bi bi-lock-fill me-1"></i>Confirmar nova senha
    </label>
    <div class="input-group">
      <input type="password" id="confirmar_senha" name="confirmar_senha"
             class="form-control" placeholder="Repita a nova senha"
             autocomplete="new-password" required minlength="8"
             oninput="verificarMatch()">
      <button type="button" class="btn btn-outline-secondary" tabindex="-1"
              onclick="toggleVis('confirmar_senha','olho2')">
        <i class="bi bi-eye" id="olho2"></i>
      </button>
    </div>
    <div id="match-txt" style="font-size:11px;margin-top:4px"></div>
  </div>

  <button type="submit" id="btn-submit" class="btn-login">
    <i class="bi bi-shield-check me-2"></i>Definir senha e entrar no sistema
  </button>
</form>

<div class="text-center mt-4" style="font-size:12px;color:#aaa">
  <a href="<?= $_ENV['APP_URL'] ?? '' ?>/auth/logout"
     style="color:#dc3545;text-decoration:none;font-size:12px">
    <i class="bi bi-box-arrow-left me-1"></i>Sair e voltar ao login
  </a>
</div>

<style>
.req { color: #dc3545; margin: 2px 0; transition: color .2s; }
.req.ok { color: #1a7f5a; }
</style>

<script>
function toggleVis(id, olhoId) {
  var el = document.getElementById(id);
  var ic = document.getElementById(olhoId);
  el.type = el.type === 'password' ? 'text' : 'password';
  ic.className = el.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
}

function verificarForca(v) {
  var bar = document.getElementById('forca-bar');
  var txt = document.getElementById('forca-txt');
  var req = document.getElementById('requisitos');

  if (v.length > 0) req.style.display = 'block';

  // Actualizar requisitos
  var ok = [v.length >= 8, /[A-Z]/.test(v), /[0-9]/.test(v)];
  var ids = ['req-len', 'req-mai', 'req-num'];
  var labels = ['✓ Pelo menos 8 caracteres', '✓ Uma letra maiúscula', '✓ Um número'];
  var nok = ['✗ Pelo menos 8 caracteres', '✗ Uma letra maiúscula', '✗ Um número'];
  ids.forEach(function(id, i) {
    var el = document.getElementById(id);
    el.textContent = ok[i] ? labels[i] : nok[i];
    el.className   = ok[i] ? 'req ok' : 'req';
  });

  var score = 0;
  if (v.length >= 8)  score++;
  if (v.length >= 12) score++;
  if (/[A-Z]/.test(v)) score++;
  if (/[0-9]/.test(v)) score++;
  if (/[^A-Za-z0-9]/.test(v)) score++;

  var niveis = [
    [0, '#f5f5f5', ''],
    [1, '#fca5a5', 'Muito fraca'],
    [2, '#fb923c', 'Fraca'],
    [3, '#facc15', 'Razoável'],
    [4, '#4ade80', 'Boa'],
    [5, '#22c55e', 'Forte ✓'],
  ];
  var n = niveis[Math.min(score, 5)];
  bar.style.width      = (score * 20) + '%';
  bar.style.background = n[1];
  txt.textContent      = n[2];
  txt.style.color      = score >= 4 ? '#166534' : score >= 3 ? '#854d0e' : '#991b1b';
}

function verificarMatch() {
  var a   = document.getElementById('nova_senha').value;
  var b   = document.getElementById('confirmar_senha').value;
  var msg = document.getElementById('match-txt');
  var btn = document.getElementById('btn-submit');
  if (!b) { msg.textContent = ''; btn.disabled = false; return; }
  if (a === b) {
    msg.innerHTML = '<span style="color:#1a7f5a;font-weight:600">✓ As senhas coincidem</span>';
    btn.disabled = false;
  } else {
    msg.innerHTML = '<span style="color:#dc3545">✗ As senhas não coincidem</span>';
    btn.disabled = true;
  }
}
</script>
