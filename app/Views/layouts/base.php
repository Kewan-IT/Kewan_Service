<!DOCTYPE html>
<html lang="pt" dir="ltr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($titulo ?? 'Dashboard') ?> — KewanFarma</title>

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

  <style>
    :root {
      --kf-primary:       #1a7f5a;
      --kf-primary-dark:  #0d5c41;
      --kf-primary-light: #e8f5f0;
      --kf-sidebar-bg:    #0f2d24;
      --kf-sidebar-hover: #1a4a35;
      --kf-sidebar-active:#1a7f5a;
      --kf-sidebar-text:  rgba(255,255,255,.75);
      --kf-sidebar-width: 245px;
      --kf-header-h:      60px;
    }

    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: 'Segoe UI', system-ui, sans-serif;
      background: #f4f6f5;
      color: #1a2e27;
      min-height: 100vh;
    }

    /* ── Sidebar ── */
    .kf-sidebar {
      position: fixed;
      top: 0; left: 0;
      width: var(--kf-sidebar-width);
      height: 100vh;
      background: var(--kf-sidebar-bg);
      display: flex;
      flex-direction: column;
      z-index: 1040;
      transition: transform .3s ease;
      overflow-y: auto;
      overflow-x: hidden;
    }

    .kf-sidebar::-webkit-scrollbar { width: 4px; }
    .kf-sidebar::-webkit-scrollbar-thumb { background: rgba(255,255,255,.1); border-radius: 4px; }

    .sidebar-brand {
      padding: 15px 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      border-bottom: 1px solid rgba(255,255,255,.07);
      flex-shrink: 0;
    }

    .sidebar-brand img {
      max-width: 100%;
      height: auto;
    }

    .sidebar-brand-icon {
      width: 38px; height: 38px;
      background: var(--kf-primary);
      border-radius: 10px;
      display: flex; align-items: center; justify-content: center;
      font-size: 18px;
      color: #fff;
      flex-shrink: 0;
    }

    .sidebar-brand-text h2 {
      font-size: 15px;
      font-weight: 700;
      color: #fff;
      margin: 0;
    }

    .sidebar-brand-text span {
      font-size: 11px;
      color: rgba(255,255,255,.45);
    }

    /* Utilizador na sidebar */
    .sidebar-user {
      padding: 14px 18px;
      display: flex;
      align-items: center;
      gap: 10px;
      border-bottom: 1px solid rgba(255,255,255,.07);
    }

    .sidebar-user-avatar {
      width: 36px; height: 36px;
      border-radius: 50%;
      background: rgba(255,255,255,.12);
      display: flex; align-items: center; justify-content: center;
      font-size: 15px;
      color: rgba(255,255,255,.8);
      flex-shrink: 0;
      overflow: hidden;
    }

    .sidebar-user-avatar img { width: 100%; height: 100%; object-fit: cover; }

    .sidebar-user-info { min-width: 0; }

    .sidebar-user-info strong {
      display: block;
      font-size: 12.5px;
      color: #fff;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .sidebar-user-info span {
      font-size: 11px;
      color: rgba(255,255,255,.45);
    }

    /* Badge de perfil */
    .perfil-badge {
      font-size: 10px;
      padding: 1px 7px;
      border-radius: 20px;
      font-weight: 600;
    }

    .perfil-admin        { background: rgba(220,53,69,.25);  color: #ff8a8a; }
    .perfil-farmaceutico { background: rgba(26,127,90,.35);  color: #6ee7b7; }
    .perfil-caixa        { background: rgba(13,110,253,.25); color: #93c5fd; }
    .perfil-tecnico      { background: rgba(253,126,20,.25); color: #fca96a; }

    /* Navegação */
    .sidebar-nav { padding: 12px 0; flex: 1; }

    .nav-section-label {
      padding: 10px 20px 4px;
      font-size: 10px;
      font-weight: 700;
      letter-spacing: .08em;
      color: rgba(255,255,255,.3);
      text-transform: uppercase;
    }

    .nav-item {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 9px 18px;
      color: var(--kf-sidebar-text);
      text-decoration: none;
      font-size: 13.5px;
      border-radius: 0;
      transition: background .15s, color .15s;
      position: relative;
    }

    .nav-item:hover {
      background: var(--kf-sidebar-hover);
      color: #fff;
      text-decoration: none;
    }

    .nav-item.active {
      background: var(--kf-sidebar-active);
      color: #fff;
    }

    .nav-item.active::before {
      content: '';
      position: absolute;
      left: 0; top: 0; bottom: 0;
      width: 3px;
      background: #fff;
      border-radius: 0 3px 3px 0;
    }

    .nav-item i { font-size: 16px; flex-shrink: 0; }

    .nav-badge {
      margin-left: auto;
      font-size: 10px;
      padding: 1px 7px;
      border-radius: 20px;
      background: rgba(220,53,69,.8);
      color: #fff;
      font-weight: 600;
    }

    /* Sidebar footer */
    .sidebar-footer {
      padding: 12px 18px;
      border-top: 1px solid rgba(255,255,255,.07);
      flex-shrink: 0;
    }

    .btn-logout {
      display: flex;
      align-items: center;
      gap: 8px;
      color: rgba(255,255,255,.5);
      font-size: 13px;
      text-decoration: none;
      padding: 6px 0;
      transition: color .15s;
      width: 100%;
      background: none;
      border: none;
      cursor: pointer;
    }

    .btn-logout:hover { color: #ff8a8a; }

    /* ── Header ── */
    .kf-header {
      position: fixed;
      top: 0;
      left: var(--kf-sidebar-width);
      right: 0;
      height: var(--kf-header-h);
      background: #fff;
      border-bottom: 1px solid #e8ede9;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 24px;
      z-index: 1030;
    }

    .header-left { display: flex; align-items: center; gap: 12px; }

    .btn-menu-toggle {
      display: none;
      background: none;
      border: none;
      font-size: 20px;
      color: #555;
      cursor: pointer;
      padding: 4px;
    }

    .breadcrumb {
      margin: 0;
      font-size: 13px;
    }

    .breadcrumb-item + .breadcrumb-item::before { color: #aaa; }
    .breadcrumb-item.active { color: var(--kf-primary); font-weight: 600; }

    .header-right { display: flex; align-items: center; gap: 8px; }

    .btn-header-icon {
      width: 36px; height: 36px;
      border-radius: 10px;
      background: none;
      border: 1.5px solid #e8ede9;
      display: flex; align-items: center; justify-content: center;
      font-size: 16px;
      color: #555;
      cursor: pointer;
      transition: background .15s, color .15s;
      position: relative;
      text-decoration: none;
    }

    .btn-header-icon:hover { background: var(--kf-primary-light); color: var(--kf-primary); }

    .notif-dot {
      position: absolute;
      top: 4px; right: 4px;
      width: 8px; height: 8px;
      border-radius: 50%;
      background: #dc3545;
      border: 1.5px solid #fff;
    }

    /* ── Conteúdo principal ── */
    .kf-main {
      margin-left: var(--kf-sidebar-width);
      padding-top: var(--kf-header-h);
      min-height: 100vh;
    }

    .kf-content {
      padding: 28px 28px 40px;
    }

    .page-title {
      font-size: 20px;
      font-weight: 700;
      color: #1a2e27;
      margin-bottom: 4px;
    }

    .page-subtitle {
      font-size: 13px;
      color: #888;
      margin-bottom: 24px;
    }

    /* ── Alerta de sessão a expirar ── */
    #alerta-sessao {
      position: fixed;
      bottom: 24px; right: 24px;
      background: #fff;
      border: 1.5px solid #ffc107;
      border-radius: 12px;
      padding: 14px 18px;
      box-shadow: 0 4px 20px rgba(0,0,0,.12);
      font-size: 13px;
      z-index: 9999;
      display: none;
      max-width: 320px;
    }

    /* ── Responsive ── */
    .sidebar-overlay {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,.4);
      z-index: 1039;
    }

    @media (max-width: 768px) {
      .kf-sidebar { transform: translateX(-100%); }
      .kf-sidebar.open { transform: translateX(0); }
      .sidebar-overlay.open { display: block; }
      .kf-header { left: 0; }
      .kf-main { margin-left: 0; }
      .btn-menu-toggle { display: flex; }
      .kf-content { padding: 20px 16px 32px; }
    }
  </style>
</head>
<body>

<!-- Overlay para mobile -->
<div class="sidebar-overlay" id="overlay" onclick="toggleSidebar()"></div>

<!-- ── Sidebar ── -->
<aside class="kf-sidebar" id="sidebar">

  <!-- Marca -->
  <div class="sidebar-brand">
    <img src="<?= $_ENV['APP_URL'] ?? '' ?>/storage/uploads/logos/logo_menu.png" alt="Logo KewanFarma" style="max-width: 180px; height: auto; display: block;">
  </div>

  <!-- Utilizador -->
  <div class="sidebar-user">
    <div class="sidebar-user-avatar">
      <?php if (!empty($_SESSION['foto_url'])): ?>
        <img src="<?= $_ENV['APP_URL'] ?? '' ?>/storage/<?= htmlspecialchars($_SESSION['foto_url']) ?>" alt="Foto">
      <?php else: ?>
        <i class="bi bi-person-fill"></i>
      <?php endif; ?>
    </div>
    <div class="sidebar-user-info">
      <strong><?= htmlspecialchars($_SESSION['usuario_nome'] ?? '') ?></strong>
      <span class="perfil-badge perfil-<?= $_SESSION['perfil'] ?? '' ?>">
        <?= match($_SESSION['perfil'] ?? '') {
              'admin'        => 'Administrador',
              'farmaceutico' => 'Farmacêutico',
              'caixa'        => 'Caixa',
              'tecnico'      => 'Técnico',
              default        => ucfirst($_SESSION['perfil'] ?? '')
            } ?>
      </span>
    </div>
  </div>

  <!-- Navegação -->
  <nav class="sidebar-nav">

    <div class="nav-section-label">Principal</div>
    <a href="<?= $_ENV['APP_URL'] ?? '' ?>/dashboard" class="nav-item <?= ($activePage ?? '') === 'dashboard' ? 'active' : '' ?>">
      <i class="bi bi-speedometer2"></i> Dashboard
    </a>

    <div class="nav-section-label">Balcão</div>
    <a href="<?= $_ENV['APP_URL'] ?? '' ?>/vendas/nova" class="nav-item <?= ($activePage ?? '') === 'venda-nova' ? 'active' : '' ?>">
      <i class="bi bi-cart-plus"></i> Nova Venda
    </a>
    <a href="<?= $_ENV['APP_URL'] ?? '' ?>/vendas" class="nav-item <?= ($activePage ?? '') === 'vendas' ? 'active' : '' ?>">
      <i class="bi bi-receipt"></i> Vendas
    </a>
    <a href="<?= $_ENV['APP_URL'] ?? '' ?>/caixa" class="nav-item <?= ($activePage ?? '') === 'caixa' ? 'active' : '' ?>">
      <i class="bi bi-cash-stack"></i> Caixa
    </a>
    <a href="<?= $_ENV['APP_URL'] ?? '' ?>/clientes" class="nav-item <?= ($activePage ?? '') === 'clientes' ? 'active' : '' ?>">
      <i class="bi bi-people"></i> Clientes
    </a>

    <div class="nav-section-label">Stock</div>
    <a href="<?= $_ENV['APP_URL'] ?? '' ?>/produtos" class="nav-item <?= ($activePage ?? '') === 'produtos' ? 'active' : '' ?>">
      <i class="bi bi-boxes"></i> Produtos
    </a>
    <a href="<?= $_ENV['APP_URL'] ?? '' ?>/compras" class="nav-item <?= ($activePage ?? '') === 'compras' ? 'active' : '' ?>">
      <i class="bi bi-truck"></i> Compras
    </a>
    <a href="<?= $_ENV['APP_URL'] ?? '' ?>/fornecedores" class="nav-item <?= ($activePage ?? '') === 'fornecedores' ? 'active' : '' ?>">
      <i class="bi bi-building"></i> Fornecedores
    </a>

    <?php if (in_array($_SESSION['perfil'] ?? '', ['admin', 'farmaceutico'])): ?>
    <div class="nav-section-label">Gestão</div>
    <a href="<?= $_ENV['APP_URL'] ?? '' ?>/funcionarios" class="nav-item <?= ($activePage ?? '') === 'funcionarios' ? 'active' : '' ?>">
      <i class="bi bi-person-badge"></i> Funcionários
    </a>
    <a href="<?= $_ENV['APP_URL'] ?? '' ?>/relatorios" class="nav-item <?= ($activePage ?? '') === 'relatorios' ? 'active' : '' ?>">
      <i class="bi bi-bar-chart-line"></i> Relatórios
    </a>
    <?php endif; ?>

    <?php if (($_SESSION['perfil'] ?? '') === 'admin'): ?>
    <div class="nav-section-label">Sistema</div>
    <a href="<?= $_ENV['APP_URL'] ?? '' ?>/configuracoes" class="nav-item <?= ($activePage ?? '') === 'configuracoes' ? 'active' : '' ?>">
      <i class="bi bi-gear"></i> Configurações
    </a>
    <?php endif; ?>

  </nav>

  <!-- Footer da sidebar -->
  <div class="sidebar-footer">
    <a href="<?= $_ENV['APP_URL'] ?? '' ?>/auth/logout" class="btn-logout"
       onclick="return confirm('Tem a certeza que deseja sair do sistema?')">
      <i class="bi bi-box-arrow-left"></i> Terminar sessão
    </a>
  </div>

</aside>

<!-- ── Header ── -->
<header class="kf-header">
  <div class="header-left">
    <button class="btn-menu-toggle" onclick="toggleSidebar()" aria-label="Menu">
      <i class="bi bi-list"></i>
    </button>

    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= $_ENV['APP_URL'] ?? '' ?>/dashboard" style="color:#888;text-decoration:none">Início</a></li>
        <?php if (!empty($breadcrumb)): ?>
          <?php foreach ($breadcrumb as $label => $url): ?>
            <?php if ($url): ?>
              <li class="breadcrumb-item"><a href="<?= $url ?>" style="color:#888;text-decoration:none"><?= htmlspecialchars($label) ?></a></li>
            <?php else: ?>
              <li class="breadcrumb-item active"><?= htmlspecialchars($label) ?></li>
            <?php endif; ?>
          <?php endforeach; ?>
        <?php else: ?>
          <li class="breadcrumb-item active"><?= htmlspecialchars($titulo ?? '') ?></li>
        <?php endif; ?>
      </ol>
    </nav>
  </div>

  <div class="header-right">
    <!-- Alertas de stock -->
    <a href="<?= $_ENV['APP_URL'] ?? '' ?>/produtos?filtro=stock_baixo"
       class="btn-header-icon" title="Alertas de stock">
      <i class="bi bi-bell"></i>
      <span class="notif-dot" id="notif-stock" style="display:none"></span>
    </a>
    <!-- Ajuda -->
    <button class="btn-header-icon" title="Ajuda" onclick="alert('Documentação em construção.')">
      <i class="bi bi-question-circle"></i>
    </button>
  </div>
</header>

<!-- ── Conteúdo principal ── -->
<main class="kf-main">
  <div class="kf-content">

    <?php if (!empty($flash_sucesso)): ?>
      <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 mb-4" role="alert">
        <i class="bi bi-check-circle-fill"></i>
        <span><?= htmlspecialchars($flash_sucesso) ?></span>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <?php if (!empty($flash_erro)): ?>
      <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 mb-4" role="alert">
        <i class="bi bi-exclamation-triangle-fill"></i>
        <span><?= htmlspecialchars($flash_erro) ?></span>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <?= $content ?>
  </div>
</main>

<!-- Alerta de sessão a expirar -->
<div id="alerta-sessao">
  <div class="d-flex align-items-center gap-2 mb-2">
    <i class="bi bi-clock-history text-warning fs-5"></i>
    <strong>Sessão prestes a expirar</strong>
  </div>
  <p class="mb-2" style="font-size:12px;color:#666">A sua sessão expira em <strong id="tempo-sessao">5:00</strong>. Clique para continuar.</p>
  <button class="btn btn-sm btn-warning w-100" onclick="renovarSessao()">Manter sessão activa</button>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Toggle da sidebar (mobile)
function toggleSidebar() {
  document.getElementById('sidebar').classList.toggle('open');
  document.getElementById('overlay').classList.toggle('open');
}

// Alerta de expiração de sessão (avisa 5 minutos antes)
const SESSION_LIFETIME = <?= (int)($_ENV['SESSION_LIFETIME'] ?? 7200) ?>;
let tempoRestante = SESSION_LIFETIME;
let alertaMostrado = false;

function renovarSessao() {
  fetch('<?= $_ENV['APP_URL'] ?? '' ?>/api/dashboard/resumo')
    .then(() => {
      tempoRestante = SESSION_LIFETIME;
      document.getElementById('alerta-sessao').style.display = 'none';
      alertaMostrado = false;
    });
}

setInterval(() => {
  tempoRestante--;
  if (tempoRestante <= 300 && !alertaMostrado) {
    document.getElementById('alerta-sessao').style.display = 'block';
    alertaMostrado = true;
  }
  if (alertaMostrado && tempoRestante > 0) {
    const m = Math.floor(tempoRestante / 60);
    const s = tempoRestante % 60;
    const el = document.getElementById('tempo-sessao');
    if (el) el.textContent = `${m}:${s.toString().padStart(2,'0')}`;
  }
  if (tempoRestante <= 0) {
    window.location.href = '<?= $_ENV['APP_URL'] ?? '' ?>/auth/logout';
  }
}, 1000);

// Verificar alertas de stock ao carregar
fetch('<?= $_ENV['APP_URL'] ?? '' ?>/api/estoque/alertas')
  .then(r => r.json())
  .then(d => {
    if (d.total > 0) {
      document.getElementById('notif-stock').style.display = 'block';
    }
  }).catch(() => {});
</script>

</body>
</html>
