<?php
$APP  = $_ENV['APP_URL'] ?? '';
$mes  = date('F Y');
$hora = (int)date('H');
$saudacao = $hora < 12 ? 'Bom dia' : ($hora < 18 ? 'Boa tarde' : 'Boa noite');
$nome = explode(' ', $_SESSION['usuario_nome'] ?? 'Utilizador')[0];

// Helpers
function fmt($v) { return 'MT ' . number_format((float)$v, 2, ',', '.'); }
function fmtN($v) { return number_format((float)$v, 0, ',', '.'); }
function varClass($v) { return $v >= 0 ? 'text-success' : 'text-danger'; }
function varIcon($v) { return $v >= 0 ? 'bi-arrow-up-right' : 'bi-arrow-down-right'; }

// JSON para gráficos
$vendas30json   = json_encode($vendas30   ?? []);
$vendasHorajson = json_encode($vendasHora ?? []);
$topProdJson    = json_encode($topProdutos ?? []);
$pagJson        = json_encode($pagamentos  ?? []);
$catJson        = json_encode($categorias  ?? []);
$mesesJson      = json_encode($receitaMeses ?? []);
?>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>

<style>
.dash-kpi       { background:#fff;border-radius:14px;padding:1.2rem 1.4rem;box-shadow:0 2px 12px rgba(0,0,0,.06);border:1px solid #f0f0f0;position:relative;overflow:hidden; }
.dash-kpi-icon  { width:46px;height:46px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.3rem;flex-shrink:0; }
.dash-kpi-val   { font-size:1.55rem;font-weight:700;color:#111;line-height:1.1; }
.dash-kpi-label { font-size:.72rem;font-weight:500;text-transform:uppercase;letter-spacing:.06em;color:#888;margin-bottom:4px; }
.dash-kpi-sub   { font-size:.75rem;color:#aaa;margin-top:4px; }
.dash-card      { background:#fff;border-radius:14px;box-shadow:0 2px 12px rgba(0,0,0,.06);border:1px solid #f0f0f0; }
.dash-card-hdr  { display:flex;align-items:center;justify-content:space-between;padding:.9rem 1.25rem;border-bottom:1px solid #f4f4f4; }
.dash-card-ttl  { font-weight:700;font-size:.85rem;text-transform:uppercase;letter-spacing:.05em;color:#444;display:flex;align-items:center;gap:.5rem; }
.dash-card-body { padding:1rem 1.25rem; }
.kpi-stripe     { position:absolute;top:0;right:0;width:4px;height:100%;border-radius:0 14px 14px 0; }
.rank-row       { display:flex;align-items:center;gap:.75rem;padding:.5rem 0;border-bottom:1px solid #f5f5f5; }
.rank-row:last-child { border-bottom:none; }
.rank-pos       { width:24px;height:24px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.7rem;font-weight:700;flex-shrink:0; }
.avatar-sm      { width:36px;height:36px;border-radius:50%;object-fit:cover;background:#e8f5f0;display:flex;align-items:center;justify-content:center;font-size:.8rem;font-weight:700;color:#1a7f5a;flex-shrink:0; }
.bday-card      { display:flex;align-items:center;gap:.8rem;padding:.55rem .75rem;border-radius:10px;margin-bottom:.5rem; }
.bday-hoje      { background:linear-gradient(135deg,#fff9c4,#fff3cd);border:1px solid #ffd93d; }
.bday-proximo   { background:#f8f8f8;border:1px solid #eee; }
.stock-bar      { height:6px;border-radius:3px;background:#eee;overflow:hidden;margin-top:4px; }
.stock-fill     { height:100%;border-radius:3px;transition:width .5s; }
.lote-row       { display:flex;align-items:center;justify-content:space-between;padding:.5rem 0;border-bottom:1px solid #f5f5f5;font-size:.82rem; }
.lote-row:last-child { border-bottom:none; }
.badge-dias     { padding:2px 8px;border-radius:20px;font-size:.7rem;font-weight:600; }
.venda-row      { display:flex;align-items:center;justify-content:space-between;padding:.45rem 0;border-bottom:1px solid #f5f5f5;font-size:.81rem; }
.venda-row:last-child { border-bottom:none; }
.pag-badge      { padding:2px 8px;border-radius:20px;font-size:.68rem;font-weight:600;text-transform:uppercase; }
.refresh-btn    { background:none;border:1px solid #ddd;border-radius:8px;padding:3px 10px;font-size:.72rem;color:#888;cursor:pointer;transition:.2s; }
.refresh-btn:hover { background:#f0f9f4;border-color:#1a7f5a;color:#1a7f5a; }
.resultado-box  { text-align:center;padding:1rem; }
.resultado-val  { font-size:1.8rem;font-weight:800; }
@media(max-width:576px) { .dash-kpi-val { font-size:1.2rem; } .dash-kpi-icon { width:38px;height:38px;font-size:1rem; } }
</style>

<!-- ── CABEÇALHO ─────────────────────────────────────────────────── -->
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
  <div>
    <h1 class="page-title mb-0"><?= $saudacao ?>, <?= htmlspecialchars($nome) ?>! 👋</h1>
    <p class="page-subtitle mb-0">
      <i class="bi bi-calendar3 me-1"></i>
      <?= date('l, d \d\e F \d\e Y') ?> &nbsp;·&nbsp;
      <span id="relogio" style="font-weight:500;color:#1a7f5a"></span>
    </p>
  </div>
  <div class="d-flex gap-2">
    <span class="badge bg-success-subtle text-success px-3 py-2" style="font-size:.75rem;border-radius:8px">
      <i class="bi bi-circle-fill me-1" style="font-size:.5rem"></i> Sistema Online
    </span>
    <button class="refresh-btn" onclick="location.reload()">
      <i class="bi bi-arrow-clockwise me-1"></i>Actualizar
    </button>
  </div>
</div>

<!-- ── ROW 1: KPIs PRINCIPAIS ───────────────────────────────────── -->
<div class="row g-3 mb-3">

  <!-- Vendas hoje -->
  <div class="col-6 col-md-3">
    <div class="dash-kpi">
      <div class="kpi-stripe" style="background:#1a7f5a"></div>
      <div class="dash-kpi-label">Vendas Hoje</div>
      <div class="d-flex align-items-center gap-3">
        <div class="dash-kpi-icon" style="background:#e8f5f0;color:#1a7f5a">
          <i class="bi bi-cart-check"></i>
        </div>
        <div>
          <div class="dash-kpi-val"><?= fmtN($kpis['vendas']['total_vendas']) ?></div>
          <div class="dash-kpi-sub"><?= fmt($kpis['vendas']['valor_total']) ?></div>
        </div>
      </div>
      <div class="dash-kpi-sub mt-2">
        <?php if ($kpis['var_vendas'] != 0): ?>
        <i class="bi <?= varIcon($kpis['var_vendas']) ?> <?= varClass($kpis['var_vendas']) ?>"></i>
        <span class="<?= varClass($kpis['var_vendas']) ?>"><?= abs($kpis['var_vendas']) ?>%</span> vs ontem
        <?php else: ?>
        <span class="text-muted">— primeiro dia com dados</span>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Ticket médio -->
  <div class="col-6 col-md-3">
    <div class="dash-kpi">
      <div class="kpi-stripe" style="background:#0d6efd"></div>
      <div class="dash-kpi-label">Ticket Médio</div>
      <div class="d-flex align-items-center gap-3">
        <div class="dash-kpi-icon" style="background:#e8f0ff;color:#0d6efd">
          <i class="bi bi-receipt"></i>
        </div>
        <div>
          <div class="dash-kpi-val" style="font-size:1.2rem"><?= fmt($kpis['vendas']['ticket_medio']) ?></div>
          <div class="dash-kpi-sub">por venda hoje</div>
        </div>
      </div>
      <div class="dash-kpi-sub mt-2">
        Descontos: <?= fmt($kpis['vendas']['total_descontos']) ?>
      </div>
    </div>
  </div>

  <!-- Produtos em alerta -->
  <div class="col-6 col-md-3">
    <div class="dash-kpi">
      <div class="kpi-stripe" style="background:<?= $kpis['produtos']['stock_baixo'] > 0 ? '#f59e0b' : '#1a7f5a' ?>"></div>
      <div class="dash-kpi-label">Stock Crítico</div>
      <div class="d-flex align-items-center gap-3">
        <div class="dash-kpi-icon" style="background:#fff8e1;color:#f59e0b">
          <i class="bi bi-exclamation-triangle"></i>
        </div>
        <div>
          <div class="dash-kpi-val" style="color:<?= $kpis['produtos']['stock_baixo'] > 0 ? '#f59e0b' : '#1a7f5a' ?>"><?= $kpis['produtos']['stock_baixo'] ?></div>
          <div class="dash-kpi-sub"><?= $kpis['produtos']['sem_stock'] ?> esgotados</div>
        </div>
      </div>
      <div class="dash-kpi-sub mt-2">
        <?= $kpis['lotes_criticos'] ?> lotes a vencer · <?= $kpis['lotes_vencidos'] ?> vencidos
      </div>
    </div>
  </div>

  <!-- Resultado do mês -->
  <div class="col-6 col-md-3">
    <div class="dash-kpi">
      <?php $res = $resultadoMes['resultado']; ?>
      <div class="kpi-stripe" style="background:<?= $res >= 0 ? '#10b981' : '#ef4444' ?>"></div>
      <div class="dash-kpi-label">Resultado Mês</div>
      <div class="d-flex align-items-center gap-3">
        <div class="dash-kpi-icon" style="background:<?= $res >= 0 ? '#e8fff5' : '#fef2f2' ?>;color:<?= $res >= 0 ? '#10b981' : '#ef4444' ?>">
          <i class="bi bi-graph-up<?= $res < 0 ? '-arrow' : '' ?>"></i>
        </div>
        <div>
          <div class="dash-kpi-val" style="font-size:1.1rem;color:<?= $res >= 0 ? '#10b981' : '#ef4444' ?>"><?= fmt($res) ?></div>
          <div class="dash-kpi-sub">Vendas: <?= fmt($resultadoMes['vendas']) ?></div>
        </div>
      </div>
      <div class="dash-kpi-sub mt-2">Compras: <?= fmt($resultadoMes['compras']) ?></div>
    </div>
  </div>
</div>

<!-- ── ROW 2: GRÁFICO 30 DIAS + HORA DO DIA ─────────────────────── -->
<div class="row g-3 mb-3">
  <div class="col-12 col-lg-8">
    <div class="dash-card h-100">
      <div class="dash-card-hdr">
        <div class="dash-card-ttl"><i class="bi bi-graph-up" style="color:#1a7f5a"></i> Vendas — Últimos 30 Dias</div>
        <span style="font-size:.72rem;color:#aaa"><?= date('d/m/Y') ?></span>
      </div>
      <div class="dash-card-body" style="height:230px">
        <canvas id="chart30dias"></canvas>
      </div>
    </div>
  </div>
  <div class="col-12 col-lg-4">
    <div class="dash-card h-100">
      <div class="dash-card-hdr">
        <div class="dash-card-ttl"><i class="bi bi-clock" style="color:#0d6efd"></i> Pico de Vendas Hoje</div>
      </div>
      <div class="dash-card-body" style="height:230px">
        <canvas id="chartHoras"></canvas>
      </div>
    </div>
  </div>
</div>

<!-- ── ROW 3: PAGAMENTOS + CATEGORIAS + RECEITA MENSAL ──────────── -->
<div class="row g-3 mb-3">
  <div class="col-12 col-md-4">
    <div class="dash-card h-100">
      <div class="dash-card-hdr">
        <div class="dash-card-ttl"><i class="bi bi-credit-card" style="color:#8b5cf6"></i> Formas de Pagamento</div>
        <span style="font-size:.7rem;color:#aaa">Este mês</span>
      </div>
      <div class="dash-card-body" style="height:200px;display:flex;align-items:center">
        <canvas id="chartPagamentos"></canvas>
      </div>
    </div>
  </div>
  <div class="col-12 col-md-4">
    <div class="dash-card h-100">
      <div class="dash-card-hdr">
        <div class="dash-card-ttl"><i class="bi bi-grid" style="color:#f59e0b"></i> Vendas por Categoria</div>
        <span style="font-size:.7rem;color:#aaa">Este mês</span>
      </div>
      <div class="dash-card-body" style="height:200px;display:flex;align-items:center">
        <canvas id="chartCategorias"></canvas>
      </div>
    </div>
  </div>
  <div class="col-12 col-md-4">
    <div class="dash-card h-100">
      <div class="dash-card-hdr">
        <div class="dash-card-ttl"><i class="bi bi-bar-chart" style="color:#10b981"></i> Receita Mensal</div>
        <span style="font-size:.7rem;color:#aaa">Últimos 6 meses</span>
      </div>
      <div class="dash-card-body" style="height:200px">
        <canvas id="chartMeses"></canvas>
      </div>
    </div>
  </div>
</div>

<!-- ── ROW 4: TOP PRODUTOS + RANKING FUNCIONÁRIOS ───────────────── -->
<div class="row g-3 mb-3">
  <!-- Top Produtos -->
  <div class="col-12 col-lg-6">
    <div class="dash-card h-100">
      <div class="dash-card-hdr">
        <div class="dash-card-ttl"><i class="bi bi-trophy" style="color:#f59e0b"></i> Top 10 Produtos do Mês</div>
        <a href="<?= $APP ?>/relatorios/vendas" class="refresh-btn">Ver mais</a>
      </div>
      <div class="dash-card-body" style="height:320px">
        <canvas id="chartTopProd"></canvas>
      </div>
    </div>
  </div>

  <!-- Ranking Funcionários -->
  <div class="col-12 col-lg-6">
    <div class="dash-card h-100">
      <div class="dash-card-hdr">
        <div class="dash-card-ttl"><i class="bi bi-people" style="color:#1a7f5a"></i> Ranking de Vendas — <?= date('M/Y') ?></div>
      </div>
      <div class="dash-card-body">
        <?php if (empty($rankingFunc)): ?>
          <div class="text-center text-muted py-4"><i class="bi bi-info-circle"></i> Sem dados este mês</div>
        <?php else: ?>
        <?php
        $medalhas = ['🥇','🥈','🥉','4º','5º'];
        $cores    = ['#f59e0b','#9ca3af','#b45309','#6b7280','#9ca3af'];
        $maxVal   = max(array_column($rankingFunc,'valor_total')) ?: 1;
        foreach ($rankingFunc as $i => $f):
          $pct = round(($f['valor_total'] / $maxVal) * 100);
          $iniciais = strtoupper(substr($f['nome_completo'],0,1) . (strpos($f['nome_completo'],' ') !== false ? substr($f['nome_completo'], strpos($f['nome_completo'],' ')+1,1) : ''));
        ?>
        <div class="rank-row">
          <div class="rank-pos" style="background:<?= $cores[$i] ?>;color:#fff"><?= $medalhas[$i] ?></div>
          <?php if (!empty($f['foto_url'])): ?>
            <img src="<?= $APP ?>/uploads/<?= htmlspecialchars($f['foto_url']) ?>" class="avatar-sm" alt="">
          <?php else: ?>
            <div class="avatar-sm"><?= htmlspecialchars($iniciais) ?></div>
          <?php endif; ?>
          <div class="flex-grow-1 min-w-0">
            <div style="font-size:.83rem;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= htmlspecialchars($f['nome_completo']) ?></div>
            <div class="stock-bar">
              <div class="stock-fill" style="width:<?= $pct ?>%;background:<?= $cores[$i] ?>"></div>
            </div>
          </div>
          <div class="text-end flex-shrink-0" style="min-width:90px">
            <div style="font-size:.82rem;font-weight:700;color:#1a7f5a"><?= fmt($f['valor_total']) ?></div>
            <div style="font-size:.7rem;color:#aaa"><?= $f['total_vendas'] ?> vendas</div>
          </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- ── ROW 5: MELHOR FUNC MÊS + ANIVERSARIANTES ─────────────────── -->
<div class="row g-3 mb-3">

  <!-- Melhor funcionário -->
  <div class="col-12 col-md-5">
    <div class="dash-card h-100">
      <div class="dash-card-hdr">
        <div class="dash-card-ttl"><i class="bi bi-star-fill" style="color:#f59e0b"></i> Destaque do Mês</div>
        <span style="font-size:.7rem;color:#aaa"><?= date('F Y') ?></span>
      </div>
      <div class="dash-card-body">
        <?php if ($melhorFunc): ?>
        <?php
          $iniciais = strtoupper(substr($melhorFunc['nome_completo'],0,1) . (strpos($melhorFunc['nome_completo'],' ') !== false ? substr($melhorFunc['nome_completo'], strpos($melhorFunc['nome_completo'],' ')+1,1) : ''));
        ?>
        <div class="text-center py-2">
          <div style="position:relative;display:inline-block">
            <?php if (!empty($melhorFunc['foto_url'])): ?>
              <img src="<?= $APP ?>/uploads/<?= htmlspecialchars($melhorFunc['foto_url']) ?>"
                   style="width:80px;height:80px;border-radius:50%;object-fit:cover;border:3px solid #f59e0b" alt="">
            <?php else: ?>
              <div style="width:80px;height:80px;border-radius:50%;background:linear-gradient(135deg,#1a7f5a,#0d5c41);display:flex;align-items:center;justify-content:center;font-size:1.8rem;font-weight:700;color:#fff;border:3px solid #f59e0b;margin:0 auto">
                <?= htmlspecialchars($iniciais) ?>
              </div>
            <?php endif; ?>
            <span style="position:absolute;bottom:-4px;right:-4px;font-size:1.3rem">🏆</span>
          </div>
          <div class="mt-3 mb-1" style="font-size:1.05rem;font-weight:700"><?= htmlspecialchars($melhorFunc['nome_completo']) ?></div>
          <div class="text-muted mb-3" style="font-size:.8rem"><?= htmlspecialchars($melhorFunc['cargo']) ?></div>
          <div class="row g-2 text-center">
            <div class="col-4">
              <div style="background:#e8f5f0;border-radius:10px;padding:.5rem">
                <div style="font-size:1rem;font-weight:700;color:#1a7f5a"><?= $melhorFunc['total_vendas'] ?></div>
                <div style="font-size:.65rem;color:#888;text-transform:uppercase">Vendas</div>
              </div>
            </div>
            <div class="col-4">
              <div style="background:#fff8e1;border-radius:10px;padding:.5rem">
                <div style="font-size:.85rem;font-weight:700;color:#f59e0b"><?= fmt($melhorFunc['valor_total']) ?></div>
                <div style="font-size:.65rem;color:#888;text-transform:uppercase">Total</div>
              </div>
            </div>
            <div class="col-4">
              <div style="background:#e8f0ff;border-radius:10px;padding:.5rem">
                <div style="font-size:.85rem;font-weight:700;color:#0d6efd"><?= fmt($melhorFunc['ticket_medio']) ?></div>
                <div style="font-size:.65rem;color:#888;text-transform:uppercase">Ticket</div>
              </div>
            </div>
          </div>
        </div>
        <?php else: ?>
          <div class="text-center text-muted py-5">
            <i class="bi bi-person-x" style="font-size:2.5rem"></i>
            <p class="mt-2 mb-0">Sem vendas registadas este mês</p>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Aniversariantes -->
  <div class="col-12 col-md-7">
    <div class="dash-card h-100">
      <div class="dash-card-hdr">
        <div class="dash-card-ttl"><i class="bi bi-balloon-heart" style="color:#ec4899"></i> Aniversariantes — <?= date('F') ?></div>
        <span class="badge bg-pink-subtle" style="background:#fdf2f8;color:#ec4899;font-size:.7rem;border-radius:8px;padding:3px 10px">
          <?= count($aniversariantes) ?> este mês
        </span>
      </div>
      <div class="dash-card-body" style="max-height:290px;overflow-y:auto">
        <?php if (empty($aniversariantes)): ?>
          <div class="text-center text-muted py-4">
            <i class="bi bi-balloon" style="font-size:2rem"></i>
            <p class="mt-2 mb-0">Nenhum aniversariante este mês</p>
          </div>
        <?php else: ?>
          <?php foreach ($aniversariantes as $a):
            $iniciais = strtoupper(substr($a['nome_completo'],0,1) . (strpos($a['nome_completo'],' ') !== false ? substr($a['nome_completo'], strpos($a['nome_completo'],' ')+1,1) : ''));
            $isHoje = $a['status_aniversario'] === 'hoje';
          ?>
          <div class="bday-card <?= $isHoje ? 'bday-hoje' : 'bday-proximo' ?>">
            <?php if (!empty($a['foto_url'])): ?>
              <img src="<?= $APP ?>/uploads/<?= htmlspecialchars($a['foto_url']) ?>"
                   style="width:40px;height:40px;border-radius:50%;object-fit:cover;border:2px solid <?= $isHoje ? '#ffd93d' : '#ddd' ?>" alt="">
            <?php else: ?>
              <div style="width:40px;height:40px;border-radius:50%;background:<?= $isHoje ? 'linear-gradient(135deg,#ffd93d,#f59e0b)' : '#e8f5f0' ?>;display:flex;align-items:center;justify-content:center;font-weight:700;color:<?= $isHoje ? '#fff' : '#1a7f5a' ?>;font-size:.85rem;flex-shrink:0">
                <?= htmlspecialchars($iniciais) ?>
              </div>
            <?php endif; ?>
            <div class="flex-grow-1">
              <div style="font-size:.84rem;font-weight:600"><?= htmlspecialchars($a['nome_completo']) ?></div>
              <div style="font-size:.72rem;color:#888"><?= htmlspecialchars($a['cargo']) ?> · <?= $a['idade_proxima'] ?> anos</div>
            </div>
            <div class="text-end flex-shrink-0">
              <?php if ($isHoje): ?>
                <span style="font-size:.72rem;background:#ffd93d;color:#7a4f00;padding:3px 10px;border-radius:20px;font-weight:600">🎂 Hoje!</span>
              <?php else: ?>
                <span style="font-size:.72rem;color:#888">Dia <?= $a['dia_aniversario'] ?></span>
              <?php endif; ?>
            </div>
          </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- ── ROW 6: STOCK CRÍTICO + LOTES A VENCER + ÚLTIMAS VENDAS ───── -->
<div class="row g-3 mb-3">

  <!-- Stock crítico -->
  <div class="col-12 col-md-4">
    <div class="dash-card h-100">
      <div class="dash-card-hdr">
        <div class="dash-card-ttl"><i class="bi bi-exclamation-triangle" style="color:#f59e0b"></i> Stock Crítico</div>
        <a href="<?= $APP ?>/produtos?filtro=stock_baixo" class="refresh-btn">Ver todos</a>
      </div>
      <div class="dash-card-body" style="max-height:280px;overflow-y:auto">
        <?php if (empty($stockCritico)): ?>
          <div class="text-center text-muted py-4"><i class="bi bi-check-circle text-success" style="font-size:2rem"></i><p class="mt-2">Stock em ordem!</p></div>
        <?php else: ?>
          <?php foreach ($stockCritico as $s):
            $pct  = max(0, min(100, (int)($s['pct_stock'] ?? 0)));
            $cor  = $s['estoque_actual'] == 0 ? '#ef4444' : ($pct <= 30 ? '#f59e0b' : '#eab308');
          ?>
          <div style="padding:.5rem 0;border-bottom:1px solid #f5f5f5">
            <div class="d-flex justify-content-between align-items-start">
              <div style="font-size:.8rem;font-weight:600;flex:1;padding-right:.5rem"><?= htmlspecialchars($s['nome']) ?></div>
              <div class="text-end flex-shrink-0">
                <span style="font-size:.75rem;font-weight:700;color:<?= $cor ?>">
                  <?= $s['estoque_actual'] ?>/<?= $s['estoque_min'] ?> <?= htmlspecialchars($s['unidade_venda'] ?? '') ?>
                </span>
              </div>
            </div>
            <div class="stock-bar mt-1">
              <div class="stock-fill" style="width:<?= $pct ?>%;background:<?= $cor ?>"></div>
            </div>
          </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Lotes a vencer -->
  <div class="col-12 col-md-4">
    <div class="dash-card h-100">
      <div class="dash-card-hdr">
        <div class="dash-card-ttl"><i class="bi bi-calendar-x" style="color:#ef4444"></i> Lotes a Vencer</div>
        <a href="<?= $APP ?>/relatorios/lotes-a-vencer" class="refresh-btn">Ver todos</a>
      </div>
      <div class="dash-card-body" style="max-height:280px;overflow-y:auto">
        <?php if (empty($lotesAVencer)): ?>
          <div class="text-center text-muted py-4"><i class="bi bi-check-circle text-success" style="font-size:2rem"></i><p class="mt-2">Sem lotes críticos!</p></div>
        <?php else: ?>
          <?php foreach ($lotesAVencer as $l):
            $dias = (int)$l['dias_restantes'];
            $cor  = $dias <= 15 ? '#ef4444' : ($dias <= 30 ? '#f59e0b' : '#eab308');
            $bg   = $dias <= 15 ? '#fef2f2' : ($dias <= 30 ? '#fff8e1' : '#fefce8');
          ?>
          <div class="lote-row">
            <div>
              <div style="font-weight:600;font-size:.8rem"><?= htmlspecialchars($l['produto_nome']) ?></div>
              <div style="font-size:.7rem;color:#aaa">Lote: <?= htmlspecialchars($l['numero_lote']) ?> · <?= $l['quantidade'] ?> un.</div>
            </div>
            <span class="badge-dias" style="background:<?= $bg ?>;color:<?= $cor ?>">
              <?= $dias ?>d · <?= date('d/m', strtotime($l['validade'])) ?>
            </span>
          </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Últimas vendas -->
  <div class="col-12 col-md-4">
    <div class="dash-card h-100">
      <div class="dash-card-hdr">
        <div class="dash-card-ttl"><i class="bi bi-lightning" style="color:#1a7f5a"></i> Últimas Vendas</div>
        <a href="<?= $APP ?>/vendas" class="refresh-btn">Ver todas</a>
      </div>
      <div class="dash-card-body" style="max-height:280px;overflow-y:auto">
        <?php if (empty($ultimasVendas)): ?>
          <div class="text-center text-muted py-4">Sem vendas ainda</div>
        <?php else: ?>
          <?php
          $pagCores = [
            'dinheiro'        => ['bg'=>'#e8f5f0','color'=>'#1a7f5a','icon'=>'bi-cash'],
            'mpesa'           => ['bg'=>'#e8f0ff','color'=>'#0d6efd','icon'=>'bi-phone'],
            'emola'           => ['bg'=>'#fff8e1','color'=>'#f59e0b','icon'=>'bi-phone-fill'],
            'cartao_debito'   => ['bg'=>'#f3e8ff','color'=>'#8b5cf6','icon'=>'bi-credit-card'],
            'cartao_credito'  => ['bg'=>'#fce8ff','color'=>'#a855f7','icon'=>'bi-credit-card-2-front'],
            'transferencia'   => ['bg'=>'#e8fff5','color'=>'#10b981','icon'=>'bi-bank'],
            'credito'         => ['bg'=>'#fff3cd','color'=>'#856404','icon'=>'bi-clock'],
          ];
          foreach ($ultimasVendas as $v):
            $pStyle = $pagCores[$v['forma_pagamento']] ?? ['bg'=>'#f0f0f0','color'=>'#888','icon'=>'bi-cash'];
          ?>
          <div class="venda-row">
            <div>
              <div style="font-size:.8rem;font-weight:600"><?= htmlspecialchars($v['numero_venda']) ?></div>
              <div style="font-size:.7rem;color:#aaa"><?= htmlspecialchars($v['cliente']) ?> · <?= htmlspecialchars($v['funcionario']) ?></div>
            </div>
            <div class="text-end">
              <div style="font-size:.82rem;font-weight:700;color:#1a7f5a"><?= fmt($v['total']) ?></div>
              <span class="pag-badge" style="background:<?= $pStyle['bg'] ?>;color:<?= $pStyle['color'] ?>">
                <i class="bi <?= $pStyle['icon'] ?> me-1"></i><?= str_replace('_',' ',$v['forma_pagamento']) ?>
              </span>
            </div>
          </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- ── ROW 7: CAIXA + CLIENTES + RESUMO GERAL ──────────────────── -->
<div class="row g-3 mb-4">
  <div class="col-6 col-md-3">
    <div class="dash-kpi" style="text-align:center">
      <div class="dash-kpi-label">Entradas Caixa</div>
      <div class="dash-kpi-icon mx-auto mb-2" style="background:#e8fff5;color:#10b981"><i class="bi bi-arrow-down-left"></i></div>
      <div class="dash-kpi-val" style="color:#10b981"><?= fmt($caixaHoje['total_entradas']) ?></div>
      <div class="dash-kpi-sub"><?= $caixaHoje['total_movimentos'] ?> movimentos</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="dash-kpi" style="text-align:center">
      <div class="dash-kpi-label">Saídas Caixa</div>
      <div class="dash-kpi-icon mx-auto mb-2" style="background:#fef2f2;color:#ef4444"><i class="bi bi-arrow-up-right"></i></div>
      <div class="dash-kpi-val" style="color:#ef4444"><?= fmt($caixaHoje['total_saidas']) ?></div>
      <div class="dash-kpi-sub">hoje</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="dash-kpi" style="text-align:center">
      <div class="dash-kpi-label">Total Clientes</div>
      <div class="dash-kpi-icon mx-auto mb-2" style="background:#e8f0ff;color:#0d6efd"><i class="bi bi-people"></i></div>
      <div class="dash-kpi-val" style="color:#0d6efd"><?= fmtN($kpis['clientes']['activos']) ?></div>
      <div class="dash-kpi-sub"><?= fmtN($kpis['clientes']['total']) ?> no total</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="dash-kpi" style="text-align:center">
      <div class="dash-kpi-label">Total Produtos</div>
      <div class="dash-kpi-icon mx-auto mb-2" style="background:#f3e8ff;color:#8b5cf6"><i class="bi bi-capsule"></i></div>
      <div class="dash-kpi-val" style="color:#8b5cf6"><?= fmtN($kpis['produtos']['activos']) ?></div>
      <div class="dash-kpi-sub"><?= fmtN($kpis['produtos']['total']) ?> cadastrados</div>
    </div>
  </div>
</div>

<!-- ── SCRIPTS GRÁFICOS ───────────────────────────────────────────── -->
<script>
// Relógio em tempo real
function tick() {
  const now = new Date();
  document.getElementById('relogio').textContent =
    now.toLocaleTimeString('pt-MZ', {hour:'2-digit', minute:'2-digit', second:'2-digit'});
}
tick(); setInterval(tick, 1000);

// Dados do PHP
const dados30   = <?= $vendas30json ?>;
const dadosHora = <?= $vendasHorajson ?>;
const dadosProd = <?= $topProdJson ?>;
const dadosPag  = <?= $pagJson ?>;
const dadosCat  = <?= $catJson ?>;
const dadosMes  = <?= $mesesJson ?>;

// Paleta principal
const verde   = '#1a7f5a';
const verdeL  = 'rgba(26,127,90,.12)';
const cores   = ['#1a7f5a','#0d6efd','#f59e0b','#ef4444','#8b5cf6','#10b981','#ec4899','#06b6d4','#f97316','#6366f1'];

// ── Gráfico 30 dias ───────────────────────────────────────────────
(function() {
  // Preencher dias sem dados com 0
  const dias = [];
  const vals = [];
  const qtds = [];
  const hoje = new Date();
  for (let i = 29; i >= 0; i--) {
    const d = new Date(hoje); d.setDate(hoje.getDate()-i);
    const key = d.toISOString().substring(0,10);
    const row = dados30.find(r => r.dia === key);
    dias.push(d.toLocaleDateString('pt-MZ',{day:'2-digit',month:'2-digit'}));
    vals.push(row ? parseFloat(row.valor_total) : 0);
    qtds.push(row ? parseInt(row.total_vendas) : 0);
  }
  new Chart(document.getElementById('chart30dias'), {
    type: 'line',
    data: {
      labels: dias,
      datasets: [{
        label: 'Valor (MT)',
        data: vals,
        borderColor: verde,
        backgroundColor: verdeL,
        fill: true,
        tension: .35,
        pointRadius: 3,
        pointHoverRadius: 6,
        borderWidth: 2.5,
        yAxisID: 'y'
      },{
        label: 'Nº Vendas',
        data: qtds,
        borderColor: '#0d6efd',
        backgroundColor: 'transparent',
        borderDash: [5,3],
        tension: .35,
        pointRadius: 2,
        borderWidth: 1.5,
        yAxisID: 'y2'
      }]
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      interaction: { mode: 'index', intersect: false },
      plugins: { legend: { position: 'top', labels: { font: { size: 11 } } } },
      scales: {
        y:  { position:'left',  ticks: { callback: v => 'MT '+v.toLocaleString('pt-MZ'), font:{size:10} }, grid:{color:'#f0f0f0'} },
        y2: { position:'right', ticks: { font:{size:10} }, grid: { drawOnChartArea: false } }
      }
    }
  });
})();

// ── Gráfico por hora ──────────────────────────────────────────────
(function() {
  const horas = Array.from({length:24},(_,i)=>i+'h');
  const vals  = Array(24).fill(0);
  dadosHora.forEach(r => { vals[parseInt(r.hora)] = parseFloat(r.valor); });
  new Chart(document.getElementById('chartHoras'), {
    type: 'bar',
    data: {
      labels: horas,
      datasets: [{ label: 'Valor (MT)', data: vals, backgroundColor: cores[0]+'99', borderColor: cores[0], borderWidth: 1, borderRadius: 4 }]
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: {
        y: { ticks: { callback: v => 'MT '+v.toLocaleString(), font:{size:9} }, grid:{color:'#f0f0f0'} },
        x: { ticks: { font:{size:9} } }
      }
    }
  });
})();

// ── Pagamentos (donut) ─────────────────────────────────────────────
(function() {
  if (!dadosPag.length) return;
  const nomes = { dinheiro:'Dinheiro', mpesa:'M-Pesa', emola:'e-Mola', cartao_debito:'Débito', cartao_credito:'Crédito', transferencia:'Transf.', credito:'Crédito' };
  new Chart(document.getElementById('chartPagamentos'), {
    type: 'doughnut',
    data: {
      labels: dadosPag.map(r => nomes[r.forma_pagamento] || r.forma_pagamento),
      datasets: [{ data: dadosPag.map(r => parseFloat(r.valor_total)), backgroundColor: cores, borderWidth: 2, hoverOffset: 8 }]
    },
    options: {
      responsive: true, maintainAspectRatio: false, cutout: '65%',
      plugins: {
        legend: { position:'bottom', labels: { font:{size:10}, padding: 8 } },
        tooltip: { callbacks: { label: ctx => ' MT '+parseFloat(ctx.raw).toLocaleString('pt-MZ',{minimumFractionDigits:2}) } }
      }
    }
  });
})();

// ── Categorias (pie) ───────────────────────────────────────────────
(function() {
  if (!dadosCat.length) return;
  new Chart(document.getElementById('chartCategorias'), {
    type: 'pie',
    data: {
      labels: dadosCat.map(r => r.categoria),
      datasets: [{ data: dadosCat.map(r => parseFloat(r.valor)), backgroundColor: cores, borderWidth: 2, hoverOffset: 8 }]
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      plugins: {
        legend: { position:'bottom', labels: { font:{size:10}, padding:8 } },
        tooltip: { callbacks: { label: ctx => ' MT '+parseFloat(ctx.raw).toLocaleString('pt-MZ',{minimumFractionDigits:2}) } }
      }
    }
  });
})();

// ── Receita mensal (barras) ────────────────────────────────────────
(function() {
  if (!dadosMes.length) return;
  new Chart(document.getElementById('chartMeses'), {
    type: 'bar',
    data: {
      labels: dadosMes.map(r => r.mes_label),
      datasets: [{
        label: 'Receita (MT)',
        data: dadosMes.map(r => parseFloat(r.valor_total)),
        backgroundColor: dadosMes.map((_, i) => i === dadosMes.length-1 ? verde : verde+'55'),
        borderColor: verde,
        borderWidth: 1,
        borderRadius: 6
      }]
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: {
        y: { ticks: { callback: v => 'MT '+v.toLocaleString(), font:{size:9} }, grid:{color:'#f0f0f0'} },
        x: { ticks: { font:{size:9} } }
      }
    }
  });
})();

// ── Top Produtos (barras horizontais) ─────────────────────────────
(function() {
  if (!dadosProd.length) return;
  const nomes = dadosProd.map(r => r.nome.length > 22 ? r.nome.substring(0,22)+'…' : r.nome);
  const qtds  = dadosProd.map(r => parseInt(r.qtd_vendida));
  new Chart(document.getElementById('chartTopProd'), {
    type: 'bar',
    data: {
      labels: nomes,
      datasets: [{
        label: 'Qtd. Vendida',
        data: qtds,
        backgroundColor: cores,
        borderRadius: 5
      }]
    },
    options: {
      indexAxis: 'y',
      responsive: true, maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: {
        x: { ticks: { font:{size:10} }, grid:{color:'#f0f0f0'} },
        y: { ticks: { font:{size:10} } }
      }
    }
  });
})();

// Auto-refresh silencioso a cada 5 minutos
setTimeout(() => location.reload(), 5 * 60 * 1000);
</script>
