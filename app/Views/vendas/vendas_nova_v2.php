<?php
// app/Views/vendas/vendas_nova.php
$APP = $_ENV['APP_URL'] ?? '';
$cp  = $clientePresel ?? null;

// Carrinho vindo da sessão (preservado entre pesquisas)
$carrinhoSessao = $_SESSION['carrinho_balcao'] ?? [];
?>
<style>
.pos-grid { display:grid; grid-template-columns:1fr 360px; gap:16px; }
.bloco { background:#fff; border-radius:10px; box-shadow:0 1px 4px rgba(0,0,0,.07); overflow:hidden; margin-bottom:0; }
.bloco-header { padding:11px 16px; border-bottom:1px solid #f0f0f0; display:flex; align-items:center; gap:8px; font-weight:600; font-size:.85rem; }
.bloco-body { padding:14px 16px; }
.prod-item { cursor:pointer; padding:9px 14px; border-bottom:1px solid #f0f0f0; display:flex; align-items:center; justify-content:space-between; gap:10px; transition:background .1s; }
.prod-item:hover { background:#f0f9f4; }
.prod-item:last-child { border-bottom:none; }
.badge-rx   { font-size:10px; background:#fee2e2; color:#b91c1c; border:1px solid #fca5a5; padding:1px 5px; border-radius:3px; }
.badge-ctrl { font-size:10px; background:#fef3c7; color:#92400e; border:1px solid #fcd34d; padding:1px 5px; border-radius:3px; }
.forma-btn  { font-size:11px; font-weight:600; padding:7px 4px; border-radius:6px; }
.forma-btn.ativo { background:var(--kf-primary)!important; color:#fff!important; border-color:var(--kf-primary)!important; }
.cart-th { font-size:11px; font-weight:600; text-transform:uppercase; color:#999; letter-spacing:.04em; }
.cart-td { font-size:13px; vertical-align:middle; }
.total-box { background:#f0f9f4; border-radius:8px; padding:12px 14px; }
.troco-box { border-radius:8px; padding:8px 14px; text-align:center; }
@media(max-width:900px){ .pos-grid{ grid-template-columns:1fr; } }
</style>

<div class="d-flex align-items-center justify-content-between mb-3">
  <div>
    <h1 class="h4 fw-bold mb-0" style="color:var(--kf-primary)"><i class="bi bi-cart-plus me-2"></i>Nova Venda</h1>
    <p class="text-muted small mb-0" id="hora"><?= date('d/m/Y H:i:s') ?></p>
  </div>
  <div class="d-flex gap-2">
    <a href="<?= $APP ?>/vendas/nova?limpar=1" class="btn btn-sm btn-outline-danger" title="Limpar carrinho">
      <i class="bi bi-trash3"></i>
    </a>
    <a href="<?= $APP ?>/vendas" class="btn btn-sm btn-outline-secondary">
      <i class="bi bi-arrow-left me-1"></i>Voltar
    </a>
  </div>
</div>

<?php if (!empty($flash_sucesso ?? '')): ?>
<div class="alert alert-success alert-dismissible fade show mb-3">
  <?= htmlspecialchars($flash_sucesso) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
<?php if (!empty($flash_erro ?? '')): ?>
<div class="alert alert-danger alert-dismissible fade show mb-3">
  <?= htmlspecialchars($flash_erro) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="pos-grid">

  <!-- ═══════ COLUNA ESQUERDA ═══════ -->
  <div class="d-flex flex-column gap-3">

    <!-- PESQUISA PRODUTO -->
    <div class="bloco">
      <div class="bloco-header">
        <i class="bi bi-search text-success"></i>Pesquisar Produto
      </div>
      <div class="bloco-body pb-2">
        <form method="GET" action="<?= $APP ?>/vendas/nova" id="form-pesq">
          <?php if ($cp): ?>
          <input type="hidden" name="cliente_id" value="<?= (int)$cp['id'] ?>">
          <?php endif; ?>
          <input type="hidden" name="qc" value="<?= htmlspecialchars($qc ?? '') ?>">
          <div class="input-group">
            <input type="text" name="q" id="campo-q"
                   class="form-control form-control-lg"
                   placeholder="Nome, código ou princípio activo..."
                   value="<?= htmlspecialchars($q ?? '') ?>"
                   autocomplete="off" autofocus>
            <button type="submit" class="btn btn-success px-3">
              <i class="bi bi-search"></i>
            </button>
            <?php if (!empty($q)): ?>
            <a href="<?= $APP ?>/vendas/nova<?= $cp ? '?cliente_id='.(int)$cp['id'] : '' ?>"
               class="btn btn-outline-secondary px-3"><i class="bi bi-x-lg"></i></a>
            <?php endif; ?>
          </div>
        </form>

        <?php if (!empty($q) && empty($produtos)): ?>
        <div class="text-muted text-center py-3 small">
          <i class="bi bi-search me-1"></i>Nenhum produto encontrado para "<?= htmlspecialchars($q) ?>"
        </div>
        <?php elseif (!empty($produtos)): ?>
        <div class="border rounded mt-2" style="max-height:260px;overflow-y:auto">
          <?php foreach ($produtos as $p): ?>
          <div class="prod-item" onclick="addProd(<?= htmlspecialchars(json_encode([
            'id'    => (int)$p['id'],
            'nome'  => $p['nome'],
            'preco' => (float)$p['preco_venda'],
            'stock' => (int)$p['estoque_actual'],
            'rx'    => (int)$p['requer_receita'],
            'ctrl'  => (int)$p['controlado'],
            'und'   => $p['unidade_medida'],
          ]), ENT_QUOTES) ?>)">
            <div>
              <div class="fw-semibold" style="font-size:13px">
                <?= htmlspecialchars($p['nome']) ?>
                <?php if ($p['requer_receita']): ?><span class="badge-rx ms-1">RX</span><?php endif; ?>
                <?php if ($p['controlado']): ?><span class="badge-ctrl ms-1">Ctrl</span><?php endif; ?>
              </div>
              <div class="text-muted" style="font-size:11px">
                <?= htmlspecialchars($p['categoria_nome']) ?>
                · Stock: <?= $p['estoque_actual'] ?> <?= htmlspecialchars($p['unidade_medida']) ?>
              </div>
            </div>
            <div class="text-end flex-shrink-0">
              <div class="fw-bold text-success" style="font-size:13px">
                MT <?= number_format((float)$p['preco_venda'],2,',','.') ?>
              </div>
              <?php if ((int)$p['estoque_actual'] <= 0): ?>
              <div class="text-danger" style="font-size:10px">Sem stock</div>
              <?php endif; ?>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <div class="text-muted mt-2" style="font-size:11px">
          <i class="bi bi-info-circle me-1"></i>Clique no produto para adicionar ao carrinho
        </div>
      </div>
    </div>

    <!-- CARRINHO -->
    <div class="bloco flex-grow-1">
      <div class="bloco-header d-flex justify-content-between">
        <span><i class="bi bi-cart3 text-success me-2"></i>Carrinho</span>
        <span class="badge bg-success rounded-pill" id="badge-n">0</span>
      </div>
      <div id="carrinho-vazio" class="text-center py-4 text-muted">
        <i class="bi bi-cart-x" style="font-size:2.5rem;display:block;margin-bottom:8px"></i>
        <small>Nenhum produto adicionado</small>
      </div>
      <div id="carrinho-wrap" class="d-none">
        <div class="table-responsive">
          <table class="table align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th class="ps-3 cart-th">Produto</th>
                <th class="text-center cart-th" style="width:110px">Qtd</th>
                <th class="text-end cart-th" style="width:95px">Preço</th>
                <th class="text-end cart-th" style="width:105px">Subtotal</th>
                <th style="width:34px"></th>
              </tr>
            </thead>
            <tbody id="tbody"></tbody>
          </table>
        </div>
        <div class="px-3 py-2 border-top d-flex align-items-center gap-2">
          <label class="text-muted small mb-0" style="min-width:80px">Desconto (MZN):</label>
          <input type="number" id="desc-geral" class="form-control form-control-sm"
                 style="max-width:110px" min="0" step="0.01" value="0" oninput="recalc()">
        </div>
      </div>
    </div>

  </div><!-- /esquerda -->

  <!-- ═══════ COLUNA DIREITA ═══════ -->
  <div class="d-flex flex-column gap-3">

    <!-- CLIENTE -->
    <div class="bloco">
      <div class="bloco-header">
        <i class="bi bi-person text-success"></i>Cliente
        <small class="text-muted fw-normal">(opcional)</small>
      </div>
      <div class="bloco-body">
        <?php if ($cp): ?>
        <div class="d-flex align-items-center gap-2 p-2 bg-light rounded mb-1">
          <i class="bi bi-person-check-fill text-success fs-5"></i>
          <div class="flex-fill">
            <div class="fw-semibold" style="font-size:13px"><?= htmlspecialchars($cp['nome']) ?></div>
            <div class="text-muted" style="font-size:11px"><?= htmlspecialchars($cp['telefone'] ?? '') ?></div>
          </div>
          <a href="<?= $APP ?>/vendas/nova<?= !empty($q)?'?q='.urlencode($q):'' ?>"
             class="btn btn-sm btn-outline-secondary py-0 px-2"><i class="bi bi-x"></i></a>
        </div>
        <input type="hidden" id="cli-id" value="<?= (int)$cp['id'] ?>">
        <?php else: ?>
        <form method="GET" action="<?= $APP ?>/vendas/nova">
          <?php if (!empty($q)): ?><input type="hidden" name="q" value="<?= htmlspecialchars($q) ?>"><?php endif; ?>
          <div class="input-group input-group-sm mb-1">
            <input type="text" name="qc" class="form-control"
                   placeholder="Nome, telefone ou NUIT..."
                   value="<?= htmlspecialchars($qc ?? '') ?>" autocomplete="off">
            <button type="submit" class="btn btn-outline-secondary"><i class="bi bi-search"></i></button>
            <?php if (!empty($qc)): ?>
            <a href="<?= $APP ?>/vendas/nova<?= !empty($q)?'?q='.urlencode($q):'' ?>"
               class="btn btn-outline-secondary"><i class="bi bi-x"></i></a>
            <?php endif; ?>
          </div>
        </form>
        <?php if (!empty($qc) && empty($clientes)): ?>
        <div class="text-muted text-center py-2 small">Cliente não encontrado.</div>
        <?php elseif (!empty($clientes)): ?>
        <div class="border rounded" style="max-height:150px;overflow-y:auto">
          <?php foreach ($clientes as $cl): ?>
          <div class="prod-item" onclick="selCli(<?= (int)$cl['id'] ?>)">
            <div>
              <div class="fw-semibold" style="font-size:12px"><?= htmlspecialchars($cl['nome']) ?></div>
              <div class="text-muted" style="font-size:11px">
                <?= htmlspecialchars($cl['telefone'] ?? '') ?>
                <?= $cl['nuit'] ? ' · NUIT: '.htmlspecialchars($cl['nuit']) : '' ?>
              </div>
            </div>
            <i class="bi bi-chevron-right text-muted" style="font-size:10px"></i>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <input type="hidden" id="cli-id" value="">
        <?php endif; ?>
        <div class="text-muted mt-1" style="font-size:11px">Deixe em branco para venda de balcão</div>
      </div>
    </div>

    <!-- PAGAMENTO -->
    <div class="bloco">
      <div class="bloco-header">
        <i class="bi bi-cash-stack text-success"></i>Pagamento
      </div>
      <div class="bloco-body">

        <!-- Totais -->
        <div class="total-box mb-3">
          <div class="d-flex justify-content-between mb-1" style="font-size:13px">
            <span class="text-muted">Subtotal</span><span id="disp-sub">0,00 MZN</span>
          </div>
          <div class="d-flex justify-content-between mb-2" style="font-size:13px">
            <span class="text-muted">Desconto</span><span class="text-danger" id="disp-desc">- 0,00 MZN</span>
          </div>
          <div class="d-flex justify-content-between fw-bold" style="font-size:20px;border-top:2px solid #cde8dd;padding-top:8px">
            <span>TOTAL</span><span style="color:var(--kf-primary)" id="disp-tot">0,00 MZN</span>
          </div>
        </div>

        <!-- Forma pagamento -->
        <label class="form-label fw-semibold mb-2" style="font-size:12px">Forma de Pagamento</label>
        <div class="row g-1 mb-3">
          <?php foreach ([
            ['dinheiro','Dinheiro','bi-cash'],
            ['mpesa','M-Pesa','bi-phone'],
            ['emola','e-Mola','bi-phone-fill'],
            ['cartao_debito','Débito','bi-credit-card'],
            ['cartao_credito','Crédito','bi-credit-card-2-front'],
            ['transferencia','Transfer.','bi-bank'],
          ] as [$val,$lbl,$ico]): ?>
          <div class="col-4">
            <button type="button" class="btn btn-outline-secondary w-100 forma-btn <?= $val==='dinheiro'?'ativo':'' ?>"
                    data-forma="<?= $val ?>" onclick="selForma(this)">
              <i class="bi <?= $ico ?> d-block" style="font-size:14px;margin-bottom:2px"></i><?= $lbl ?>
            </button>
          </div>
          <?php endforeach; ?>
        </div>

        <!-- Valor recebido -->
        <label class="form-label fw-semibold mb-1" style="font-size:12px">Valor Recebido (MZN)</label>
        <input type="number" id="val-pago" class="form-control form-control-lg fw-bold text-end mb-2"
               min="0" step="0.01" placeholder="0,00" oninput="calcTroco()">
        <div class="row g-1 mb-2" id="atalhos">
          <div class="col-3"><button type="button" class="btn btn-outline-secondary btn-sm w-100" onclick="exato()">Exacto</button></div>
          <div class="col-3"><button type="button" class="btn btn-outline-secondary btn-sm w-100" onclick="addV(500)">+500</button></div>
          <div class="col-3"><button type="button" class="btn btn-outline-secondary btn-sm w-100" onclick="addV(1000)">+1k</button></div>
          <div class="col-3"><button type="button" class="btn btn-outline-secondary btn-sm w-100" onclick="addV(2000)">+2k</button></div>
        </div>

        <div class="troco-box d-none mb-2" id="troco-box">
          <div style="font-size:11px;color:#666;text-transform:uppercase">Troco</div>
          <div id="disp-troco" class="fw-bold text-success" style="font-size:22px">0,00 MZN</div>
        </div>

        <div class="alert alert-warning py-2 px-3 mb-2 d-none" id="alerta-rx" style="font-size:12px">
          <i class="bi bi-exclamation-triangle me-1"></i>Produto(s) com <strong>receita obrigatória</strong>.
        </div>

        <textarea id="obs" class="form-control mb-3" rows="2" style="font-size:13px"
                  placeholder="Observações (opcional)..."></textarea>

        <button type="button" id="btn-fin" class="btn btn-success w-100 py-3 fw-bold"
                style="font-size:16px" onclick="finalizar()" disabled>
          <i class="bi bi-check-circle me-2"></i>Finalizar Venda
        </button>
      </div>
    </div>

  </div><!-- /direita -->
</div><!-- /grid -->

<!-- Formulário oculto de submissão -->
<form id="form-sub" method="POST" action="<?= $APP ?>/vendas/nova" style="display:none">
  <input type="hidden" name="csrf_token"     value="<?= htmlspecialchars($csrf_token) ?>">
  <input type="hidden" name="itens_json"     id="h-itens">
  <input type="hidden" name="cliente_id"     id="h-cli">
  <input type="hidden" name="forma_pagamento" id="h-forma" value="dinheiro">
  <input type="hidden" name="desconto"       id="h-desc"  value="0">
  <input type="hidden" name="subtotal"       id="h-sub"   value="0">
  <input type="hidden" name="total"          id="h-tot"   value="0">
  <input type="hidden" name="valor_pago"     id="h-pago"  value="0">
  <input type="hidden" name="observacoes"    id="h-obs">
</form>

<script>
// ── Estado inicial vindo da sessão PHP ───────────────────────────────
let cart  = <?= json_encode(array_values($carrinhoSessao), JSON_UNESCAPED_UNICODE) ?>;
let forma = 'dinheiro';

setInterval(() => {
  const d = new Date(), el = document.getElementById('hora');
  if (el) el.textContent = d.toLocaleDateString('pt-MZ')+' '+d.toLocaleTimeString('pt-MZ');
}, 1000);

// Renderizar carrinho imediatamente (pode haver items da sessão)
render();

// ── Adicionar produto ─────────────────────────────────────────────────
function addProd(p) {
  if (p.stock <= 0) { alert('Produto sem stock.'); return; }
  const i = cart.findIndex(x => x.id === p.id);
  if (i >= 0) {
    if (cart[i].qty >= p.stock) { alert('Stock máximo: '+p.stock); return; }
    cart[i].qty++;
  } else {
    cart.push({ id:p.id, nome:p.nome, preco:parseFloat(p.preco), qty:1, max:p.stock, rx:p.rx });
  }
  salvarSessao();
  render();
}

function salvarSessao() {
  // Guardar carrinho na sessão via fetch silencioso
  fetch('<?= $APP ?>/vendas/nova/carrinho', {
    method: 'POST',
    credentials: 'include',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ carrinho: cart, csrf: '<?= htmlspecialchars($csrf_token) ?>' })
  }).catch(() => {}); // silencioso se falhar
}

// ── Render ────────────────────────────────────────────────────────────
function render() {
  const vazio = document.getElementById('carrinho-vazio');
  const wrap  = document.getElementById('carrinho-wrap');
  const badge = document.getElementById('badge-n');
  const tbody = document.getElementById('tbody');
  const btnF  = document.getElementById('btn-fin');
  const alRx  = document.getElementById('alerta-rx');

  if (!cart.length) {
    vazio.classList.remove('d-none'); wrap.classList.add('d-none');
    badge.textContent='0'; btnF.disabled=true; alRx.classList.add('d-none');
    recalc(); return;
  }
  vazio.classList.add('d-none'); wrap.classList.remove('d-none');
  badge.textContent = cart.length; btnF.disabled=false;
  alRx.classList.toggle('d-none', !cart.some(i=>i.rx));

  tbody.innerHTML = cart.map((it,i) => `
    <tr>
      <td class="ps-3 cart-td">
        <div class="fw-semibold" style="font-size:13px">${esc(it.nome)}${it.rx?'<span class="badge-rx ms-1">RX</span>':''}</div>
        <div class="text-muted" style="font-size:11px">${fmt(it.preco)}/un.</div>
      </td>
      <td class="text-center">
        <div class="d-flex align-items-center justify-content-center gap-1">
          <button type="button" class="btn btn-sm btn-outline-secondary px-2 py-0" onclick="chQ(${i},-1)">−</button>
          <input type="number" class="form-control form-control-sm text-center fw-bold"
                 style="width:48px" min="1" max="${it.max}" value="${it.qty}"
                 onchange="setQ(${i},this.value)">
          <button type="button" class="btn btn-sm btn-outline-secondary px-2 py-0" onclick="chQ(${i},1)">+</button>
        </div>
      </td>
      <td class="text-end cart-td">${fmt(it.preco)}</td>
      <td class="text-end fw-bold cart-td" style="color:var(--kf-primary)">${fmt(it.preco*it.qty)}</td>
      <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="rem(${i})"><i class="bi bi-trash3"></i></button></td>
    </tr>`).join('');
  recalc();
}

function chQ(i,d){ const n=cart[i].qty+d; if(n<1){rem(i);return;} if(n>cart[i].max){alert('Stock máx: '+cart[i].max);return;} cart[i].qty=n; salvarSessao(); render(); }
function setQ(i,v){ cart[i].qty=Math.max(1,Math.min(parseInt(v)||1,cart[i].max)); salvarSessao(); render(); }
function rem(i){ cart.splice(i,1); salvarSessao(); render(); }

// ── Totais ────────────────────────────────────────────────────────────
function recalc() {
  const sub  = cart.reduce((s,i)=>s+i.preco*i.qty,0);
  const desc = Math.max(0,parseFloat(document.getElementById('desc-geral').value)||0);
  const tot  = Math.max(0,sub-desc);
  document.getElementById('disp-sub').textContent  = fmt(sub);
  document.getElementById('disp-desc').textContent = '- '+fmt(desc);
  document.getElementById('disp-tot').textContent  = fmt(tot);
  document.getElementById('h-sub').value  = sub.toFixed(2);
  document.getElementById('h-tot').value  = tot.toFixed(2);
  document.getElementById('h-desc').value = desc.toFixed(2);
  calcTroco();
}

function calcTroco() {
  const tot  = parseFloat(document.getElementById('h-tot').value)||0;
  const pago = parseFloat(document.getElementById('val-pago').value)||0;
  const box  = document.getElementById('troco-box');
  const disp = document.getElementById('disp-troco');
  document.getElementById('h-pago').value = pago.toFixed(2);
  if (pago>0) {
    box.classList.remove('d-none');
    const tr = pago-tot;
    disp.textContent = fmt(Math.abs(tr));
    disp.className = 'fw-bold '+(tr>=0?'text-success':'text-danger');
    box.style.background = tr>=0?'#d1e7dd':'#f8d7da';
  } else { box.classList.add('d-none'); }
}

// ── Forma pagamento ───────────────────────────────────────────────────
function selForma(btn) {
  document.querySelectorAll('.forma-btn').forEach(b=>b.classList.remove('ativo'));
  btn.classList.add('ativo'); forma=btn.dataset.forma;
  document.getElementById('h-forma').value=forma;
  if (forma!=='dinheiro') {
    exato();
    document.getElementById('atalhos').style.cssText='opacity:.4;pointer-events:none';
  } else {
    document.getElementById('atalhos').style.cssText='';
  }
}
function exato(){ const t=parseFloat(document.getElementById('h-tot').value)||0; document.getElementById('val-pago').value=t.toFixed(2); calcTroco(); }
function addV(v){ document.getElementById('val-pago').value=(parseFloat(document.getElementById('val-pago').value)||0+v).toFixed(2); calcTroco(); }

// ── Seleccionar cliente ───────────────────────────────────────────────
function selCli(id) {
  const url = new URL(window.location.href);
  url.searchParams.set('cliente_id', id);
  url.searchParams.delete('qc');
  window.location.href = url.toString();
}

// ── Finalizar ─────────────────────────────────────────────────────────
function finalizar() {
  if (!cart.length) { alert('Adicione pelo menos um produto.'); return; }
  const tot  = parseFloat(document.getElementById('h-tot').value)||0;
  const pago = parseFloat(document.getElementById('val-pago').value)||0;
  if (forma==='dinheiro' && pago<tot) { alert('Valor recebido insuficiente.'); document.getElementById('val-pago').focus(); return; }
  if (pago<=0) exato();
  document.getElementById('h-itens').value = JSON.stringify(cart.map(i=>({produto_id:i.id,quantidade:i.qty,desconto_item:0})));
  document.getElementById('h-cli').value   = document.getElementById('cli-id').value||'';
  document.getElementById('h-obs').value   = document.getElementById('obs').value;
  document.getElementById('h-pago').value  = (parseFloat(document.getElementById('val-pago').value)||tot).toFixed(2);
  const btn=document.getElementById('btn-fin');
  btn.disabled=true; btn.innerHTML='<span class="spinner-border spinner-border-sm me-2"></span>A processar...';
  document.getElementById('form-sub').submit();
}

function fmt(v){ return parseFloat(v||0).toLocaleString('pt-MZ',{minimumFractionDigits:2,maximumFractionDigits:2})+' MZN'; }
function esc(s){ return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
</script>
