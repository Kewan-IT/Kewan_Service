<!DOCTYPE html>
<html lang="pt" dir="ltr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex, nofollow">
  <title><?= htmlspecialchars($titulo ?? 'KewanFarma') ?> — KewanFarma</title>

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

  <style>
    :root {
      --kf-primary:      #1a7f5a;
      --kf-primary-dark: #0d5c41;
      --kf-primary-light:#e8f5f0;
      --kf-accent:       #27ae60;
      --kf-sidebar-bg:   #0f2d24;
      --kf-text:         #1a2e27;
    }

    *, *::before, *::after { box-sizing: border-box; }

    body {
      min-height: 100vh;
      background: #f0f4f2;
      font-family: 'Segoe UI', system-ui, sans-serif;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 1rem;
    }

    .auth-wrapper {
      width: 100%;
      max-width: 960px;
      min-height: 560px;
      background: #fff;
      border-radius: 20px;
      box-shadow: 0 8px 40px rgba(0,0,0,.10);
      overflow: hidden;
      display: flex;
    }

    /* ── Painel esquerdo (branding) ── */
    .auth-brand {
      width: 42%;
      background: linear-gradient(150deg, var(--kf-primary) 0%, var(--kf-primary-dark) 100%);
      padding: 48px 40px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      color: #fff;
      position: relative;
      overflow: hidden;
    }

    .auth-brand::before {
      content: '';
      position: absolute;
      top: -60px; right: -60px;
      width: 220px; height: 220px;
      border-radius: 50%;
      background: rgba(255,255,255,.06);
    }
    .auth-brand::after {
      content: '';
      position: absolute;
      bottom: -80px; left: -40px;
      width: 280px; height: 280px;
      border-radius: 50%;
      background: rgba(255,255,255,.05);
    }

    .brand-logo {
      display: flex;
      align-items: center;
      gap: 12px;
      position: relative;
      z-index: 1;
    }

    .brand-logo-icon {
      width: 52px; height: 52px;
      background: rgba(255,255,255,.18);
      border-radius: 14px;
      display: flex; align-items: center; justify-content: center;
      font-size: 26px;
    }

    .brand-logo-text h1 {
      font-size: 22px;
      font-weight: 700;
      margin: 0;
      letter-spacing: -.3px;
    }

    .brand-logo-text span {
      font-size: 12px;
      opacity: .75;
    }

    .brand-info {
      position: relative;
      z-index: 1;
    }

    .brand-info h2 {
      font-size: 26px;
      font-weight: 700;
      line-height: 1.3;
      margin-bottom: 12px;
    }

    .brand-info p {
      font-size: 13.5px;
      opacity: .8;
      line-height: 1.7;
      margin: 0;
    }

    .brand-features {
      position: relative;
      z-index: 1;
      display: flex;
      flex-direction: column;
      gap: 10px;
    }

    .brand-feature {
      display: flex;
      align-items: center;
      gap: 10px;
      font-size: 13px;
      opacity: .85;
    }

    .brand-feature i {
      font-size: 16px;
      opacity: .9;
    }

    /* ── Painel direito (formulário) ── */
    .auth-form-panel {
      flex: 1;
      padding: 48px 44px;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }

    .auth-form-panel h2 {
      font-size: 22px;
      font-weight: 700;
      color: var(--kf-text);
      margin-bottom: 6px;
    }

    .auth-form-panel .subtitle {
      font-size: 14px;
      color: #6c757d;
      margin-bottom: 32px;
    }

    .form-label {
      font-size: 13px;
      font-weight: 600;
      color: #444;
      margin-bottom: 6px;
    }

    .form-control {
      border-radius: 10px;
      border: 1.5px solid #e0e0e0;
      padding: .65rem 1rem;
      font-size: 14px;
      transition: border-color .2s, box-shadow .2s;
    }

    .form-control:focus {
      border-color: var(--kf-primary);
      box-shadow: 0 0 0 3px rgba(26,127,90,.12);
    }

    .input-group .form-control { border-radius: 10px 0 0 10px; }
    .input-group .btn-outline-secondary {
      border-radius: 0 10px 10px 0;
      border: 1.5px solid #e0e0e0;
      border-left: 0;
      color: #888;
      background: #fafafa;
    }
    .input-group .btn-outline-secondary:hover { background: #f0f0f0; }

    .btn-login {
      background: var(--kf-primary);
      border: none;
      border-radius: 10px;
      padding: .7rem;
      font-size: 15px;
      font-weight: 600;
      color: #fff;
      transition: background .2s, transform .1s;
      width: 100%;
    }

    .btn-login:hover  { background: var(--kf-primary-dark); }
    .btn-login:active { transform: scale(.98); }

    .alert { border-radius: 10px; font-size: 13.5px; }

    .link-recuperar {
      color: var(--kf-primary);
      font-size: 13px;
      text-decoration: none;
    }
    .link-recuperar:hover { text-decoration: underline; }

    .auth-footer {
      font-size: 12px;
      color: #aaa;
      text-align: center;
      margin-top: 32px;
    }

    @media (max-width: 680px) {
      .auth-brand { display: none; }
      .auth-form-panel { padding: 36px 28px; }
    }
  </style>
</head>
<body>

<div class="auth-wrapper">

  <!-- Painel de branding -->
  <div class="auth-brand">
    <div class="brand-logo">
      <div class="brand-logo-icon">
        <i class="bi bi-capsule-pill"></i>
      </div>
      <div class="brand-logo-text">
        <h1>KewanFarma</h1>
        <span>Sistema de Gestão</span>
      </div>
    </div>

    <div class="brand-info">
      <h2>Gestão inteligente para a sua farmácia</h2>
      <p>Controle de stock, vendas, funcionários e relatórios num só lugar — de forma simples e segura.</p>
    </div>

    <div class="brand-features">
      <div class="brand-feature"><i class="bi bi-shield-check"></i> Acesso seguro por perfil</div>
      <div class="brand-feature"><i class="bi bi-graph-up-arrow"></i> Relatórios em tempo real</div>
      <div class="brand-feature"><i class="bi bi-boxes"></i> Controlo total de stock</div>
      <div class="brand-feature"><i class="bi bi-receipt"></i> Emissão de talões de venda</div>
    </div>
  </div>

  <!-- Painel do formulário -->
  <div class="auth-form-panel">
    <?= $content ?>
    <div class="auth-footer">
      KewanFarma &copy; <?= date('Y') ?> &mdash; Todos os direitos reservados
    </div>
  </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
