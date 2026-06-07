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
      padding: 20px 18px 16px;
      display: flex;
      align-items: center;
      gap: 10px;
      border-bottom: 1px solid rgba(255,255,255,.07);
      flex-shrink: 0;
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

    .perfil-badge {
      font-size: 10px;
      padding: 1px 7px;
      border-radius: 20px;
      font-weight: 600;
    }

    .perfil-admin        { background: rgba(220,53,69,.25);  color: #ff8a8a; }
    .perfil-diretor      { background: rgba(111,66,193,.25); color: #c4b5fd; }
    .perfil-farmaceutico { background: rgba(26,127,90,.35);  color: #6ee7b7; }
    .perfil-caixa        { background: rgba(13,110,253,.25); color: #93c5fd; }
    .perfil-tecnico      { background: rgba(253,126,20,.25); color: #fca96a; }

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

    .breadcrumb { margin: 0; font-size: 13px; }
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

    /* ── Notification dropdown ─────────────────────────── */
    .notif-wrapper { position: relative; }

    .notif-badge {
      position: absolute;
      top: 3px; right: 3px;
      min-width: 17px; height: 17px;
      border-radius: 9px;
      background: #dc3545;
      border: 1.5px solid #fff;
      font-size: 10px;
      font-weight: 700;
      color: #fff;
      display: none;
      align-items: center;
      justify-content: center;
      line-height: 1;
      padding: 0 3px;
    }

    .notif-dropdown {
      display: none;
      position: absolute;
      top: calc(100% + 8px);
      right: 0;
      width: 320px;
      background: #fff;
      border: 1.5px solid #e4e9e6;
      border-radius: 14px;
      box-shadow: 0 8px 32px rgba(0,0,0,.13);
      z-index: 9990;
      overflow: hidden;
    }
    .notif-dropdown.open { display: block; }

    .notif-header {
      padding: 12px 16px 10px;
      border-bottom: 1px solid #f0f3f1;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    .notif-header span { font-size: 13px; font-weight: 700; color: #1a2e27; }
    .notif-header a { font-size: 11px; color: var(--kf-primary); text-decoration: none; }
    .notif-header a:hover { text-decoration: underline; }

    .notif-list { max-height: 340px; overflow-y: auto; }

    .notif-item {
      display: flex;
      align-items: flex-start;
      gap: 10px;
      padding: 11px 16px;
      border-bottom: 1px solid #f5f7f5;
      text-decoration: none;
      color: inherit;
      transition: background .12s;
    }
    .notif-item:last-child { border-bottom: none; }
    .notif-item:hover { background: #f4f9f6; }

    .notif-icon {
      width: 32px; height: 32px; border-radius: 9px;
      display: flex; align-items: center; justify-content: center;
      font-size: 15px; flex-shrink: 0; margin-top: 1px;
    }
    .notif-icon.danger  { background: #fff0f0; color: #dc3545; }
    .notif-icon.warning { background: #fff8e1; color: #f59e0b; }
    .notif-icon.info    { background: #e8f4fd; color: #0d6efd; }

    .notif-body { flex: 1; min-width: 0; }
    .notif-title { font-size: 12.5px; font-weight: 600; color: #1a2e27; margin-bottom: 1px; }
    .notif-desc  { font-size: 11.5px; color: #888; line-height: 1.4; }

    .notif-empty {
      padding: 28px 16px;
      text-align: center;
      color: #aaa;
      font-size: 12.5px;
    }
    .notif-empty i { font-size: 28px; display: block; margin-bottom: 6px; color: #d0d7d3; }

    .notif-footer {
      padding: 10px 16px;
      border-top: 1px solid #f0f3f1;
      text-align: center;
    }
    .notif-footer a { font-size: 12px; color: var(--kf-primary); text-decoration: none; font-weight: 600; }
    .notif-footer a:hover { text-decoration: underline; }

    .kf-main {
      margin-left: var(--kf-sidebar-width);
      padding-top: var(--kf-header-h);
      min-height: 100vh;
    }

    .kf-content { padding: 28px 28px 40px; }

    .page-title { font-size: 20px; font-weight: 700; color: #1a2e27; margin-bottom: 4px; }
    .page-subtitle { font-size: 13px; color: #888; margin-bottom: 24px; }

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

<?php
// Determinar nível de acesso
$perfil       = $_SESSION['perfil'] ?? '';
$isAdmin      = in_array($perfil, ['admin', 'diretor']);
$isRestrito   = in_array($perfil, ['caixa', 'tecnico', 'farmaceutico']);
?>

<div class="sidebar-overlay" id="overlay" onclick="toggleSidebar()"></div>

<!-- ── Sidebar ── -->
<aside class="kf-sidebar" id="sidebar">

  <div class="sidebar-brand">
    <div class="sidebar-brand-icon"><i class="bi bi-capsule-pill"></i></div>
    <div class="sidebar-brand-text">
      <h2>KewanFarma</h2>
      <span>Sistema de Gestão</span>
    </div>
  </div>

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
      <span class="perfil-badge perfil-<?= $perfil ?>">
        <?= match($perfil) {
              'admin'        => 'Administrador',
              'diretor'      => 'Director',
              'farmaceutico' => 'Farmacêutico',
              'caixa'        => 'Caixa',
              'tecnico'      => 'Técnico',
              default        => ucfirst($perfil)
            } ?>
      </span>
    </div>
  </div>

  <nav class="sidebar-nav">

    <?php if ($isAdmin): ?>
    <!-- ══ MENU COMPLETO — ADMIN / DIRECTOR ══ -->

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

    <div class="nav-section-label">Gestão</div>
    <a href="<?= $_ENV['APP_URL'] ?? '' ?>/funcionarios" class="nav-item <?= ($activePage ?? '') === 'funcionarios' ? 'active' : '' ?>">
      <i class="bi bi-person-badge"></i> Funcionários
    </a>
    <a href="<?= $_ENV['APP_URL'] ?? '' ?>/relatorios" class="nav-item <?= ($activePage ?? '') === 'relatorios' ? 'active' : '' ?>">
      <i class="bi bi-bar-chart-line"></i> Relatórios
    </a>

    <div class="nav-section-label">Sistema</div>
    <a href="<?= $_ENV['APP_URL'] ?? '' ?>/configuracoes" class="nav-item <?= ($activePage ?? '') === 'configuracoes' ? 'active' : '' ?>">
      <i class="bi bi-gear"></i> Configurações
    </a>
    <a href="<?= $_ENV['APP_URL'] ?? '' ?>/backup" class="nav-item <?= ($activePage ?? '') === 'backup' ? 'active' : '' ?>">
      <i class="bi bi-cloud-download"></i> Backup
    </a>

    <?php else: ?>
    <!-- ══ MENU RESTRITO — CAIXA / TÉCNICO / FARMACÊUTICO ══ -->

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

    <div class="nav-section-label">Sistema</div>
    <a href="<?= $_ENV['APP_URL'] ?? '' ?>/backup" class="nav-item <?= ($activePage ?? '') === 'backup' ? 'active' : '' ?>">
      <i class="bi bi-cloud-download"></i> Backup
    </a>

    <?php endif; ?>

  </nav>

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
        <li class="breadcrumb-item">
          <a href="<?= $_ENV['APP_URL'] ?? '' ?>/<?= $isAdmin ? 'dashboard' : 'vendas/nova' ?>"
             style="color:#888;text-decoration:none">Início</a>
        </li>
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
    <?php if ($isAdmin): ?>
    <div class="notif-wrapper" id="notif-wrapper">
      <button class="btn-header-icon" id="notif-btn" title="Notificações"
              onclick="toggleNotifDropdown(event)">
        <i class="bi bi-bell"></i>
        <span class="notif-badge" id="notif-badge"></span>
      </button>

      <div class="notif-dropdown" id="notif-dropdown">
        <div class="notif-header">
          <span>Notificações</span>
          <a href="<?= $_ENV['APP_URL'] ?? '' ?>/relatorios">Ver relatórios</a>
        </div>
        <div class="notif-list" id="notif-list">
          <div class="notif-empty">
            <i class="bi bi-hourglass-split"></i>A carregar...
          </div>
        </div>
        <div class="notif-footer" id="notif-footer" style="display:none">
          <a href="<?= $_ENV['APP_URL'] ?? '' ?>/produtos?filtro=stock_baixo">Ver todos os alertas de stock</a>
        </div>
      </div>
    </div>
    <?php endif; ?>
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

<div id="alerta-sessao">
  <div class="d-flex align-items-center gap-2 mb-2">
    <i class="bi bi-clock-history text-warning fs-5"></i>
    <strong>Sessão prestes a expirar</strong>
  </div>
  <p class="mb-2" style="font-size:12px;color:#666">A sua sessão expira em <strong id="tempo-sessao">5:00</strong>. Clique para continuar.</p>
  <button class="btn btn-sm btn-warning w-100" onclick="renovarSessao()">Manter sessão activa</button>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
function toggleSidebar() {
  document.getElementById('sidebar').classList.toggle('open');
  document.getElementById('overlay').classList.toggle('open');
}

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

<?php if ($isAdmin): ?>
// ── Notificações dropdown ──────────────────────────────────────
const APP_URL = '<?= $_ENV['APP_URL'] ?? '' ?>';
let notifCarregadas = false;

function toggleNotifDropdown(e) {
  e.stopPropagation();
  const dropdown = document.getElementById('notif-dropdown');
  const isOpen   = dropdown.classList.contains('open');
  dropdown.classList.toggle('open');
  if (!isOpen && !notifCarregadas) carregarNotificacoes();
}

document.addEventListener('click', function(e) {
  const wrapper = document.getElementById('notif-wrapper');
  if (wrapper && !wrapper.contains(e.target)) {
    document.getElementById('notif-dropdown').classList.remove('open');
  }
});

function carregarNotificacoes() {
  fetch(APP_URL + '/api/estoque/alertas')
    .then(r => r.json())
    .then(d => {
      notifCarregadas = true;
      const list   = document.getElementById('notif-list');
      const badge  = document.getElementById('notif-badge');
      const footer = document.getElementById('notif-footer');
      const total  = (d.stock_baixo || 0) + (d.a_vencer || 0);

      // Badge no sino
      if (total > 0) {
        badge.textContent = total > 99 ? '99+' : total;
        badge.style.display = 'flex';
      } else {
        badge.style.display = 'none';
      }

      // Construir itens
      let html = '';

      if (d.stock_baixo > 0) {
        html += `
          <a class="notif-item" href="${APP_URL}/produtos?filtro=stock_baixo">
            <span class="notif-icon danger"><i class="bi bi-box-seam"></i></span>
            <span class="notif-body">
              <span class="notif-title">Stock abaixo do mínimo</span>
              <span class="notif-desc">${d.stock_baixo} produto(s) com stock insuficiente — reposição necessária.</span>
            </span>
          </a>`;
      }

      if (d.a_vencer > 0) {
        html += `
          <a class="notif-item" href="${APP_URL}/relatorios/lotes-a-vencer">
            <span class="notif-icon warning"><i class="bi bi-calendar-x"></i></span>
            <span class="notif-body">
              <span class="notif-title">Lotes a vencer</span>
              <span class="notif-desc">${d.a_vencer} lote(s) com validade próxima ou expirada.</span>
            </span>
          </a>`;
      }

      if (html === '') {
        html = `<div class="notif-empty">
          <i class="bi bi-check-circle"></i>Sem alertas pendentes
        </div>`;
        footer.style.display = 'none';
      } else {
        footer.style.display = 'block';
      }

      list.innerHTML = html;
    })
    .catch(() => {
      document.getElementById('notif-list').innerHTML =
        '<div class="notif-empty"><i class="bi bi-wifi-off"></i>Não foi possível carregar</div>';
    });
}

// Carrega badge silenciosamente ao abrir a página (sem abrir o dropdown)
fetch(APP_URL + '/api/estoque/alertas')
  .then(r => r.json())
  .then(d => {
    const total = (d.stock_baixo || 0) + (d.a_vencer || 0);
    if (total > 0) {
      const badge = document.getElementById('notif-badge');
      badge.textContent = total > 99 ? '99+' : total;
      badge.style.display = 'flex';
    }
  }).catch(() => {});

// Verificação silenciosa do backup automático (só para admins)
fetch(APP_URL + '/api/backup/verificar')
  .then(r => r.json())
  .then(d => {
    if (d.executou) {
      console.info('[KewanFarma] Backup automático executado:', d.arquivo);
    }
  }).catch(() => {});
<?php endif; ?>

</script>

</body>
</html>
