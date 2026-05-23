<?php
// app/Views/vendas/vendas_nova.php
$APP = $_ENV['APP_URL'] ?? '';

// Produtos já em carrinho (guardados na sessão em caso de erro)
$carrinhoSessao = $_SESSION['carrinho_temp'] ?? [];
unset($_SESSION['carrinho_temp']);

// Cliente pré-seleccionado
$cp = $clientePresel ?? null;
?>

<style>
.pos-grid { display:grid; grid-template-columns:1fr 370px; gap:18px; }
.secao { background:#fff; border-radius:10px; box-shadow:0 1px 4px rgba(0,0,0,.07); overflow:hidden; }
.secao-header { padding:12px 16px; border-bottom:1px solid #f0f0f0; display:flex; align-items:center; justify-content:between; gap:8px; }
.secao-body { padding:16px; }
.prod-linha { cursor:pointer; padding:10px 14px; border-bottom:1px solid #f0f0f0; transition:background .12s; display:flex; align-items:center; justify-content:space-between; gap:10px; }
.prod-linha:hover { background:#f0f9f4; }
.prod-linha:last-child { border-bottom:none; }
.badge-rx  { font-size:10px; background:#fee2e2; color:#b91c1c; border:1px solid #fca5a5; padding:1px 5px; border-radius:3px; }
.badge-ctrl { font-size:10px; background:#fef3c7; color:#92400e; border:1px solid #fcd34d; padding:1px 5px; border-radius:3px; }
.forma-btn { font-size:11px; font-weight:600; padding:8px 4px; }
.forma-btn.ativo { background:var(--kf-primary)!important; color:#fff!important; border-color:var(--kf-primary)!important; }
.cart-empty { text-align:center; padding:40px 20px; color:#aaa; }
.cart-table th { font-size:11px; font-weight:600; text-transform:uppercase; color:#999; letter-spacing:.04em; }
.cart-table td { font-size:13px; vertical-align:middle; }
.total-box { background:#f0f9f4; border-radius:8px; padding:14px; }
.total-row { display:flex; justify-content:space-between; font-size:13px; margin-bottom:4px; }
.total-final { font-size:22px; font-weight:700; color:var(--kf-primary); border-top:2px solid #cde8dd; padding-top:10px; margin-top:6px; display:flex; justify-content:space-between; }
.troco-box { background:#d1e7dd; border-radius:8px; padding:10px 14px; text-align:center; margin-top:10px; }
@media(max-width:900px) { .pos-grid { grid-template-columns:1fr; } }
</style>

<!-- Cabeçalho -->
<div class="d-flex align-items-center justify-content-between mb-3">
  <div>
    <h1 class="h4 fw-bold mb-0" style="color:var(--kf-primary)">
      <i class="bi bi-cart-plus me-2"></i>Nova Venda
    </h1>
    <p class="text-muted small mb-0" id="hora"><?= date('d/m/Y H:i:s') ?></p>
  </div>
  <a href="<?= $APP ?>/vendas" class="btn btn-sm btn-outline-secondary">
    <i class="bi bi-arrow-left me-1"></i>Voltar
  </a>
</div>

<?php if (!empty($_SESSION['flash_erro'])): ?>
<div class="alert alert-danger alert-dismissible fade show mb-3">
  <?= htmlspecialchars($_SESSION['flash_erro']) ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php unset($_SESSION['flash_erro']); endif; ?>

<div class="pos-grid">

  <!-- ═══════════════════ COLUNA ESQUERDA ═══════════════════ -->
  <div class="d-flex flex-column gap-3">

    <!-- PESQUISA DE PRODUTO -->
    <div class="secao">
      <div class="secao-header">
        <i class="bi bi-search text-success me-2"></i>
        <span class="fw-semibold">Pesquisar Produto</span>
      </div>
      <div class="secao-body pb-0">
        <!-- Formulário GET — pesquisa pelo servidor sem AJAX -->
        <form method="GET" action="<?= $APP ?>/vendas/nova" id="form-pesquisa-prod">
          <!-- Preservar cliente seleccionado e carrinho -->
          <?php if ($cp): ?>
          <input type="hidden" name="cliente_id" value="<?= (int)$cp['id'] ?>">
          <?php endif; ?>
          <input type="hidden" name="qc" value="<?= htmlspecialchars($qc ?? '') ?>">

          <div class="input-group mb-0">
            <input type="text" name="q" id="campo-pesquisa"
                   class="form-control form-control-lg"
                   placeholder="Nome, código de barras ou princípio activo..."
                   value="<?= htmlspecialchars($q) ?>"
                   autocomplete="off" autofocus>
            <button type="submit" class="btn btn-success">
              <i class="bi bi-search"></i>
            </button>
            <?php if ($q): ?>
            <a href="<?= $APP ?>/vendas/nova<?= $cp ? '?cliente_id='.(int)$cp['id'] : '' ?>"
               class="btn btn-outline-secondary" title="Limpar">
              <i class="bi bi-x-lg"></i>
            </a>
            <?php endif; ?>
          </div>
        </form>

        <!-- Resultados da pesquisa -->
        <?php if ($q && empty($produtos)): ?>
        <div class="text-muted text-center py-3" style="font-size:13px">
          <i class="bi bi-search me-1"></i>Nenhum produto encontrado para "<?= htmlspecialchars($q) ?>"
        </div>
        <?php elseif ($produtos): ?>
        <div class="mt-2 border rounded" style="max-height:280px;overflow-y:auto">
          <?php foreach ($produtos as $p): ?>
          <div class="prod-linha" onclick="adicionarProduto(<?= htmlspecialchars(json_encode([
            'id'            => (int)$p['id'],
            'nome'          => $p['nome'],
            'preco_venda'   => (float)$p['preco_venda'],
            'estoque_actual'=> (int)$p['estoque_actual'],
            'requer_receita'=> (int)$p['requer_receita'],
            'controlado'    => (int)$p['controlado'],
            'unidade_medida'=> $p['unidade_medida'],
            'categoria_nome'=> $p['categoria_nome'],
          ]), ENT_QUOTES) ?>)">
            <div>
              <div class="fw-semibold" style="font-size:13px">
                <?= htmlspecialchars($p['nome']) ?>
                <?php if ($p['requer_receita']): ?><span class="badge-rx ms-1">RX</span><?php endif; ?>
                <?php if ($p['controlado']): ?><span class="badge-ctrl ms-1">Ctrl</span><?php endif; ?>
              </div>
              <div class="text-muted" style="font-size:11px"><?= htmlspecialchars($p['categoria_nome']) ?> · Stock: <?= $p['estoque_actual'] ?> <?= htmlspecialchars($p['unidade_medida']) ?></div>
            </div>
            <div class="text-end flex-shrink-0">
              <div class="fw-bold text-success" style="font-size:14px">
                MT <?= number_format((float)$p['preco_venda'], 2, ',', '.') ?>
              </div>
              <?php if ($p['estoque_actual'] <= 0): ?>
              <div class="text-danger" style="font-size:11px"><i class="bi bi-x-circle me-1"></i>Sem stock</div>
              <?php elseif ($p['estoque_actual'] < 5): ?>
              <div class="text-warning" style="font-size:11px"><i class="bi bi-exclamation-circle me-1"></i>Stock baixo</div>
              <?php endif; ?>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="py-2 text-muted" style="font-size:11px">
          <i class="bi bi-keyboard me-1"></i>Prima Enter para pesquisar · clique no produto para adicionar
        </div>
      </div>
    </div>

    <!-- CARRINHO -->
    <div class="secao flex-grow-1">
      <div class="secao-header d-flex justify-content-between">
        <span>
          <i class="bi bi-cart3 text-success me-2"></i>
          <span class="fw-semibold">Itens da Venda</span>
        </span>
        <span class="badge bg-success rounded-pill" id="badge-itens">0</span>
      </div>
      <div class="secao-body p-0">
        <div id="carrinho-vazio" class="cart-empty">
          <i class="bi bi-cart-x" style="font-size:2.5rem;display:block;margin-bottom:10px"></i>
          Nenhum produto adicionado.<br>
          <small>Pesquise e clique no produto.</small>
        </div>
        <div id="carrinho-wrap" class="d-none">
          <div class="table-responsive">
            <table class="table cart-table align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th class="ps-3">Produto</th>
                  <th class="text-center" style="width:110px">Qtd</th>
                  <th class="text-end"    style="width:100px">Preço</th>
                  <th class="text-end"    style="width:110px">Subtotal</th>
                  <th style="width:36px"></th>
                </tr>
              </thead>
              <tbody id="tbody-cart"></tbody>
            </table>
          </div>
          <div class="px-3 py-2 border-top d-flex align-items-center gap-2">
            <label class="text-muted small mb-0" style="min-width:80px">Desconto (MZN):</label>
            <input type="number" id="desconto-geral" class="form-control form-control-sm"
                   style="max-width:110px" min="0" step="0.01" value="0"
                   oninput="recalcular()">
          </div>
        </div>
      </div>
    </div>

  </div><!-- /coluna esquerda -->

  <!-- ═══════════════════ COLUNA DIREITA ═══════════════════ -->
  <div class="d-flex flex-column gap-3">

    <!-- CLIENTE -->
    <div class="secao">
      <div class="secao-header">
        <i class="bi bi-person text-success me-2"></i>
        <span class="fw-semibold">Cliente</span>
        <small class="text-muted ms-1">(opcional)</small>
      </div>
      <div class="secao-body">
        <?php if ($cp): ?>
        <!-- Cliente pré-seleccionado -->
        <div class="d-flex align-items-center gap-2 p-2 bg-light rounded mb-2">
          <i class="bi bi-person-check-fill text-success fs-5"></i>
          <div class="flex-fill">
            <div class="fw-semibold" style="font-size:13px"><?= htmlspecialchars($cp['nome']) ?></div>
            <div class="text-muted" style="font-size:11px"><?= htmlspecialchars($cp['telefone'] ?? '') ?></div>
          </div>
          <a href="<?= $APP ?>/vendas/nova<?= $q ? '?q='.urlencode($q) : '' ?>"
             class="btn btn-sm btn-outline-secondary py-0 px-2" title="Remover">
            <i class="bi bi-x"></i>
          </a>
        </div>
        <input type="hidden" id="cliente_id_val" value="<?= (int)$cp['id'] ?>">
        <?php else: ?>
        <!-- Formulário pesquisa cliente -->
        <form method="GET" action="<?= $APP ?>/vendas/nova" id="form-pesquisa-cli">
          <?php if ($q): ?>
          <input type="hidden" name="q" value="<?= htmlspecialchars($q) ?>">
          <?php endif; ?>
          <div class="input-group input-group-sm mb-1">
            <input type="text" name="qc" class="form-control"
                   placeholder="Nome, telefone ou NUIT..."
                   value="<?= htmlspecialchars($qc) ?>" autocomplete="off">
            <button type="submit" class="btn btn-outline-secondary">
              <i class="bi bi-search"></i>
            </button>
            <?php if ($qc): ?>
            <a href="<?= $APP ?>/vendas/nova<?= $q ? '?q='.urlencode($q) : '' ?>"
               class="btn btn-outline-secondary" title="Limpar">
              <i class="bi bi-x"></i>
            </a>
            <?php endif; ?>
          </div>
        </form>

        <?php if ($qc && empty($clientes)): ?>
        <div class="text-muted text-center py-2" style="font-size:12px">Cliente não encontrado.</div>
        <?php elseif ($clientes): ?>
        <div class="border rounded" style="max-height:160px;overflow-y:auto">
          <?php foreach ($clientes as $cl): ?>
          <div class="prod-linha" onclick="selecionarCliente(<?= (int)$cl['id'] ?>)">
            <div>
              <div class="fw-semibold" style="font-size:12px"><?= htmlspecialchars($cl['nome']) ?></div>
              <div class="text-muted" style="font-size:11px">
                <?= htmlspecialchars($cl['telefone'] ?? '') ?>
                <?= $cl['nuit'] ? ' · NUIT: '.htmlspecialchars($cl['nuit']) : '' ?>
              </div>
            </div>
            <i class="bi bi-chevron-right text-muted" style="font-size:11px"></i>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <input type="hidden" id="cliente_id_val" value="">
        <?php endif; ?>

        <div class="text-muted mt-1" style="font-size:11px">
          Deixe em branco para venda de balcão anónima
        </div>
      </div>
    </div>

    <!-- PAGAMENTO -->
    <div class="secao">
      <div class="secao-header">
        <i class="bi bi-cash-stack text-success me-2"></i>
        <span class="fw-semibold">Pagamento</span>
      </div>
      <div class="secao-body">

        <!-- Totais -->
        <div class="total-box mb-3">
          <div class="total-row"><span class="text-muted">Subtotal</span><span id="disp-subtotal">0,00 MZN</span></div>
          <div class="total-row"><span class="text-muted">Desconto</span><span class="text-danger" id="disp-desconto">- 0,00 MZN</span></div>
          <div class="total-final"><span>TOTAL</span><span id="disp-total">0,00 MZN</span></div>
        </div>

        <!-- Forma de pagamento -->
        <label class="form-label fw-semibold mb-2" style="font-size:12px">Forma de Pagamento</label>
        <div class="row g-1 mb-3">
          <?php
          $formas = [
            ['dinheiro','Dinheiro','bi-cash'],
            ['mpesa','M-Pesa','bi-phone'],
            ['emola','e-Mola','bi-phone-fill'],
            ['cartao_debito','Débito','bi-credit-card'],
            ['cartao_credito','Crédito','bi-credit-card-2-front'],
            ['transferencia','Transfer.','bi-bank'],
          ];
          foreach ($formas as [$val,$lbl,$ico]):
          ?>
          <div class="col-4">
            <button type="button"
                    class="btn btn-outline-secondary w-100 forma-btn <?= $val==='dinheiro'?'ativo':'' ?>"
                    data-forma="<?= $val ?>" onclick="selForma(this)">
              <i class="bi <?= $ico ?> d-block" style="font-size:15px;margin-bottom:2px"></i>
              <?= $lbl ?>
            </button>
          </div>
          <?php endforeach; ?>
        </div>

        <!-- Valor recebido -->
        <label class="form-label fw-semibold mb-1" style="font-size:12px">Valor Recebido (MZN)</label>
        <input type="number" id="valor-pago" class="form-control form-control-lg fw-bold text-end mb-2"
               min="0" step="0.01" placeholder="0,00" oninput="calcTroco()">
        <div class="row g-1 mb-2" id="atalhos">
          <div class="col-3"><button type="button" class="btn btn-outline-secondary btn-sm w-100" onclick="exato()">Exacto</button></div>
          <div class="col-3"><button type="button" class="btn btn-outline-secondary btn-sm w-100" onclick="addVal(500)">+500</button></div>
          <div class="col-3"><button type="button" class="btn btn-outline-secondary btn-sm w-100" onclick="addVal(1000)">+1k</button></div>
          <div class="col-3"><button type="button" class="btn btn-outline-secondary btn-sm w-100" onclick="addVal(2000)">+2k</button></div>
        </div>

        <!-- Troco -->
        <div class="troco-box d-none" id="troco-box">
          <div class="text-muted" style="font-size:11px;text-transform:uppercase;letter-spacing:.05em">Troco</div>
          <div id="disp-troco" class="fw-bold text-success" style="font-size:22px">0,00 MZN</div>
        </div>

        <!-- Alerta receita -->
        <div class="alert alert-warning py-2 px-3 mt-2 mb-2 d-none" id="alerta-rx" style="font-size:12px">
          <i class="bi bi-exclamation-triangle me-1"></i>Produto(s) com <strong>receita obrigatória</strong>.
        </div>

        <!-- Observações -->
        <textarea id="obs-venda" class="form-control mt-2 mb-3" rows="2" style="font-size:13px"
                  placeholder="Observações (opcional)..."></textarea>

        <!-- Botão finalizar -->
        <button type="button" id="btn-fin" class="btn btn-success w-100 py-3 fw-bold"
                style="font-size:16px" onclick="finalizar()" disabled>
          <i class="bi bi-check-circle me-2"></i>Finalizar Venda
        </button>
      </div>
    </div>

  </div><!-- /coluna direita -->
</div>

<!-- Formulário oculto de submissão -->
<form id="form-submeter" method="POST" action="<?= $APP ?>/vendas/nova" style="display:none">
  <input type="hidden" name="csrf_token"      value="<?= htmlspecialchars($csrf_token) ?>">
  <input type="hidden" name="itens_json"       id="h-itens">
  <input type="hidden" name="cliente_id"       id="h-cliente">
  <input type="hidden" name="forma_pagamento"  id="h-forma" value="dinheiro">
  <input type="hidden" name="desconto"         id="h-desconto" value="0">
  <input type="hidden" name="subtotal"         id="h-subtotal" value="0">
  <input type="hidden" name="total"            id="h-total" value="0">
  <input type="hidden" name="valor_pago"       id="h-valor-pago" value="0">
  <input type="hidden" name="observacoes"      id="h-obs">
</form>

<script>
// ── Estado ────────────────────────────────────────────────────────────
let carrinho  = [];
let forma     = 'dinheiro';

// Hora
setInterval(() => {
  const d = new Date();
  const el = document.getElementById('hora');
  if (el) el.textContent = d.toLocaleDateString('pt-MZ') + ' ' + d.toLocaleTimeString('pt-MZ');
}, 1000);

// ── Adicionar produto ao carrinho ─────────────────────────────────────
function adicionarProduto(p) {
  if (parseInt(p.estoque_actual) <= 0) {
    alert('Este produto não tem stock disponível.');
    return;
  }
  const idx = carrinho.findIndex(i => i.id === p.id);
  if (idx >= 0) {
    if (carrinho[idx].qty < p.estoque_actual) {
      carrinho[idx].qty++;
    } else {
      alert('Stock máximo atingido: ' + p.estoque_actual);
      return;
    }
  } else {
    carrinho.push({ id: p.id, nome: p.nome, preco: parseFloat(p.preco_venda),
                    qty: 1, max: parseInt(p.estoque_actual),
                    rx: p.requer_receita, ctrl: p.controlado });
  }
  render();
  // Limpar pesquisa de produto
  const campo = document.getElementById('campo-pesquisa');
  if (campo) { campo.value = ''; campo.focus(); }
}

// ── Render carrinho ───────────────────────────────────────────────────
function render() {
  const vazio = document.getElementById('carrinho-vazio');
  const wrap  = document.getElementById('carrinho-wrap');
  const badge = document.getElementById('badge-itens');
  const tbody = document.getElementById('tbody-cart');
  const btnFin= document.getElementById('btn-fin');
  const alertRx = document.getElementById('alerta-rx');

  if (!carrinho.length) {
    vazio.classList.remove('d-none'); wrap.classList.add('d-none');
    badge.textContent = '0'; btnFin.disabled = true;
    alertRx.classList.add('d-none');
    recalcular(); return;
  }

  vazio.classList.add('d-none'); wrap.classList.remove('d-none');
  badge.textContent = carrinho.length;
  btnFin.disabled   = false;
  alertRx.classList.toggle('d-none', !carrinho.some(i => i.rx));

  tbody.innerHTML = carrinho.map((it, i) => {
    const sub = it.preco * it.qty;
    return `<tr>
      <td class="ps-3">
        <div class="fw-semibold" style="font-size:13px">${esc(it.nome)}
          ${it.rx ? '<span class="badge-rx ms-1">RX</span>' : ''}
        </div>
        <div class="text-muted" style="font-size:11px">${fmt(it.preco)} / un.</div>
      </td>
      <td class="text-center">
        <div class="d-flex align-items-center justify-content-center gap-1">
          <button type="button" class="btn btn-sm btn-outline-secondary px-2 py-0" onclick="chQty(${i},-1)">−</button>
          <input type="number" class="form-control form-control-sm text-center fw-bold"
                 style="width:50px" min="1" max="${it.max}" value="${it.qty}"
                 onchange="setQty(${i},this.value)">
          <button type="button" class="btn btn-sm btn-outline-secondary px-2 py-0" onclick="chQty(${i},1)">+</button>
        </div>
      </td>
      <td class="text-end" style="font-size:13px">${fmt(it.preco)}</td>
      <td class="text-end fw-semibold text-success" style="font-size:14px">${fmt(sub)}</td>
      <td>
        <button type="button" class="btn btn-sm btn-outline-danger" onclick="remover(${i})">
          <i class="bi bi-trash3"></i>
        </button>
      </td>
    </tr>`;
  }).join('');

  recalcular();
}

function chQty(i, d) {
  const n = carrinho[i].qty + d;
  if (n < 1) { remover(i); return; }
  if (n > carrinho[i].max) { alert('Stock máximo: ' + carrinho[i].max); return; }
  carrinho[i].qty = n; render();
}
function setQty(i, v) {
  carrinho[i].qty = Math.max(1, Math.min(parseInt(v)||1, carrinho[i].max)); render();
}
function remover(i) { carrinho.splice(i,1); render(); }

// ── Totais ────────────────────────────────────────────────────────────
function recalcular() {
  const sub  = carrinho.reduce((s,i) => s + i.preco * i.qty, 0);
  const desc = Math.max(0, parseFloat(document.getElementById('desconto-geral').value)||0);
  const tot  = Math.max(0, sub - desc);
  document.getElementById('disp-subtotal').textContent = fmt(sub);
  document.getElementById('disp-desconto').textContent = '- ' + fmt(desc);
  document.getElementById('disp-total').textContent    = fmt(tot);
  document.getElementById('h-subtotal').value = sub.toFixed(2);
  document.getElementById('h-total').value    = tot.toFixed(2);
  document.getElementById('h-desconto').value = desc.toFixed(2);
  calcTroco();
}

function calcTroco() {
  const tot  = parseFloat(document.getElementById('h-total').value)||0;
  const pago = parseFloat(document.getElementById('valor-pago').value)||0;
  const box  = document.getElementById('troco-box');
  const disp = document.getElementById('disp-troco');
  document.getElementById('h-valor-pago').value = pago.toFixed(2);
  if (pago > 0) {
    box.classList.remove('d-none');
    const tr = pago - tot;
    disp.textContent = fmt(Math.abs(tr));
    disp.className = 'fw-bold ' + (tr >= 0 ? 'text-success' : 'text-danger');
    box.style.background = tr >= 0 ? '#d1e7dd' : '#f8d7da';
  } else {
    box.classList.add('d-none');
  }
}

// ── Forma de pagamento ────────────────────────────────────────────────
function selForma(btn) {
  document.querySelectorAll('.forma-btn').forEach(b => b.classList.remove('ativo'));
  btn.classList.add('ativo');
  forma = btn.dataset.forma;
  document.getElementById('h-forma').value = forma;
  if (forma !== 'dinheiro') {
    exato();
    document.getElementById('atalhos').style.opacity = '.4';
    document.getElementById('atalhos').style.pointerEvents = 'none';
  } else {
    document.getElementById('atalhos').style.opacity = '1';
    document.getElementById('atalhos').style.pointerEvents = 'auto';
  }
}

function exato() {
  const tot = parseFloat(document.getElementById('h-total').value)||0;
  document.getElementById('valor-pago').value = tot.toFixed(2);
  calcTroco();
}
function addVal(v) {
  const cur = parseFloat(document.getElementById('valor-pago').value)||0;
  document.getElementById('valor-pago').value = (cur+v).toFixed(2);
  calcTroco();
}

// ── Seleccionar cliente (redireciona com cliente_id) ──────────────────
function selecionarCliente(id) {
  const url = new URL(window.location.href);
  url.searchParams.set('cliente_id', id);
  url.searchParams.delete('qc');
  window.location.href = url.toString();
}

// ── Finalizar ─────────────────────────────────────────────────────────
function finalizar() {
  if (!carrinho.length) { alert('Adicione pelo menos um produto.'); return; }

  const tot  = parseFloat(document.getElementById('h-total').value)||0;
  const pago = parseFloat(document.getElementById('valor-pago').value)||0;

  if (forma === 'dinheiro' && pago < tot) {
    alert('O valor recebido é inferior ao total da venda.');
    document.getElementById('valor-pago').focus(); return;
  }
  if (pago <= 0) exato();

  document.getElementById('h-itens').value   = JSON.stringify(
    carrinho.map(i => ({ produto_id: i.id, quantidade: i.qty, desconto_item: 0 }))
  );
  document.getElementById('h-cliente').value = document.getElementById('cliente_id_val').value || '';
  document.getElementById('h-obs').value     = document.getElementById('obs-venda').value;
  document.getElementById('h-valor-pago').value = (pago > 0 ? pago : tot).toFixed(2);

  const btn = document.getElementById('btn-fin');
  btn.disabled = true;
  btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>A processar...';
  document.getElementById('form-submeter').submit();
}

// ── Utilitários ───────────────────────────────────────────────────────
function fmt(v) { return parseFloat(v||0).toLocaleString('pt-MZ',{minimumFractionDigits:2,maximumFractionDigits:2})+' MZN'; }
function esc(s) { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
</script>
