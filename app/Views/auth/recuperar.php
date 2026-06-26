<?php
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$senhaTemp = $senha_temp ?? null;
$smtpOk    = $smtp_ok    ?? false;
?>

<h2>Recuperar senha</h2>
<p class="subtitle">Introduza o seu email registado no sistema.</p>

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

<?php if ($senhaTemp): ?>
<div style="background:#fff8e1;border:2px solid #ffc107;border-radius:12px;padding:18px;margin-bottom:20px">
  <div style="font-weight:700;font-size:13px;color:#856404;margin-bottom:10px;display:flex;align-items:center;gap:6px">
    <span style="font-size:18px">🔑</span> Senha temporária gerada com sucesso
  </div>

  <div style="font-size:11.5px;color:#555;margin-bottom:12px;line-height:1.5">
    Use esta senha para fazer login. Será pedido que a altere imediatamente após entrar.
  </div>

  <!-- Campo de input readonly — mais fácil de seleccionar/copiar que um <code> -->
  <div style="margin-bottom:10px">
    <label style="font-size:10px;text-transform:uppercase;letter-spacing:.4px;color:#888;font-weight:600;display:block;margin-bottom:4px">Senha temporária</label>
    <div style="display:flex;gap:6px">
      <input type="text" id="campo-senha-temp" readonly
             value="<?= htmlspecialchars($senhaTemp, ENT_QUOTES) ?>"
             style="flex:1;background:#fff;border:2px solid #ffc107;border-radius:8px;
                    padding:10px 14px;font-size:20px;font-weight:700;letter-spacing:3px;
                    color:#1a1a1a;font-family:monospace;outline:none;cursor:text"
             onclick="this.select()">
      <button type="button" id="btn-copiar"
              onclick="copiarSenha()"
              style="background:#ffc107;border:none;border-radius:8px;padding:10px 16px;
                     cursor:pointer;font-size:15px;font-weight:700;white-space:nowrap;
                     color:#1a1a1a;transition:background .2s">
        📋 Copiar
      </button>
    </div>
    <div id="copiado-msg"
         style="font-size:11px;color:#1a7f5a;margin-top:5px;display:none;font-weight:600">
      ✓ Copiado para a área de transferência!
    </div>
  </div>

  <div style="background:#fff3cd;border-radius:6px;padding:8px 12px;font-size:11px;color:#856404">
    ⚠️ <strong>Esta senha só é mostrada uma vez.</strong> Copie-a agora antes de sair desta página.
  </div>
</div>

<script>
function copiarSenha() {
  var input = document.getElementById('campo-senha-temp');
  var senha = input.value;            // valor directo — sem textContent, sem trim extra
  input.select();
  input.setSelectionRange(0, 99999);  // mobile

  if (navigator.clipboard && navigator.clipboard.writeText) {
    navigator.clipboard.writeText(senha).then(function() {
      mostrarCopiado();
    }).catch(function() {
      // Fallback para navegadores mais antigos
      document.execCommand('copy');
      mostrarCopiado();
    });
  } else {
    document.execCommand('copy');
    mostrarCopiado();
  }
}

function mostrarCopiado() {
  var msg = document.getElementById('copiado-msg');
  var btn = document.getElementById('btn-copiar');
  msg.style.display = 'block';
  btn.textContent = '✓ Copiado!';
  btn.style.background = '#4ade80';
  setTimeout(function() {
    msg.style.display = 'none';
    btn.textContent = '📋 Copiar';
    btn.style.background = '#ffc107';
  }, 3500);
}
</script>
<?php endif; ?>

<?php if (!$smtpOk): ?>
<div style="background:#f0f9ff;border:1px solid #bae6fd;border-radius:8px;
            padding:10px 14px;margin-bottom:16px;font-size:12px;color:#0369a1">
  <i class="bi bi-info-circle me-1"></i>
  <strong>Email não configurado:</strong> o sistema gera uma senha temporária como alternativa.
  O administrador pode configurar o SMTP em <strong>Configurações → Email SMTP</strong>.
</div>
<?php endif; ?>

<form action="<?= $_ENV['APP_URL'] ?? '' ?>/auth/recuperar" method="POST" novalidate>
  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

  <div class="mb-4">
    <label for="email" class="form-label">
      <i class="bi bi-envelope me-1"></i>Email da conta
    </label>
    <input type="email" id="email" name="email" class="form-control"
           placeholder="utilizador@farmacia.mz"
           autocomplete="email" autofocus required>
  </div>

  <button type="submit" class="btn-login">
    <i class="bi bi-send me-2"></i>
    <?= $smtpOk ? 'Enviar instruções por email' : 'Gerar senha temporária' ?>
  </button>
</form>

<div class="text-center mt-4">
  <a href="<?= $_ENV['APP_URL'] ?? '' ?>/auth/login" class="link-recuperar">
    <i class="bi bi-arrow-left me-1"></i>Voltar ao login
  </a>
</div>
