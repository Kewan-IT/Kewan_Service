<?php $appUrl = $_ENV['APP_URL'] ?? ''; ?>

<style>
.pos-wrapper { display:grid; grid-template-columns:1fr 380px; gap:20px; min-height:calc(100vh - 160px); }
.pos-left { display:flex; flex-direction:column; gap:16px; }
.pos-right { display:flex; flex-direction:column; gap:16px; }
.cart-table th { font-size:12px; font-weight:600; color:#888; text-transform:uppercase; letter-spacing:.04em; }
.cart-table td { font-size:14px; vertical-align:middle; }
.produto-resultado { cursor:pointer; padding:10px 14px; border-bottom:1px solid #f0f0f0; transition:background .1s; }
.produto-resultado:hover { background:#f0f9f4; }
.produto-resultado:last-child { border-bottom:none; }
.badge-receita { font-size:10px; background:#fee2e2; color:#b91c1c; border:1px solid #fca5a5; padding:2px 6px; border-radius:4px; }
/* Badges de lote */
.badge-lote-ok      { font-size:10px; background:#d1fae5; color:#065f46; border:1px solid #6ee7b7; padding:2px 6px; border-radius:4px; }
.badge-lote-aviso   { font-size:10px; background:#fef9c3; color:#713f12; border:1px solid #fde047; padding:2px 6px; border-radius:4px; }
.badge-lote-atencao { font-size:10px; background:#ffedd5; color:#9a3412; border:1px solid #fdba74; padding:2px 6px; border-radius:4px; }
.badge-lote-critico { font-size:10px; background:#fee2e2; color:#991b1b; border:1px solid #fca5a5; padding:2px 6px; border-radius:4px; }
.lote-info { font-size:11px; color:#6b7280; margin-top:2px; display:flex; align-items:center; gap:6px; }
@media(max-width:900px){
  .pos-wrapper { grid-template-columns:1fr; }
  .pos-right { order:-1; }
}
</style>

<?php
// ─── Alertas de lotes ───────────────────────────────────────────────
$alertas30 = $alertas30 ?? [];
$vencidos  = $vencidos  ?? [];
$totalAlertas = count($alertas30) + count($vencidos);
?>

<?php if ($totalAlertas > 0): ?>
<div class="alert alert-warning alert-dismissible fade show py-2 mb-3" role="alert">
  <i class="bi bi-exclamation-triangle-fill me-2"></i>
  <?php if (count($vencidos) > 0): ?>
    <strong><?= count($vencidos) ?> lote(s) vencido(s)</strong> com stock em armazém.
  <?php endif; ?>
  <?php if (count($alertas30) > 0): ?>
    <strong><?= count($alertas30) ?> lote(s)</strong> a vencer nos próximos 30 dias.
  <?php endif; ?>
  <a href="<?= $appUrl ?>/relatorios/lotes-a-vencer" class="alert-link ms-2">Ver relatório →</a>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="d-flex align-items-center justify-content-between mb-3">
  <div>
    <h1 class="page-title">Nova Venda</h1>
    <p class="page-subtitle" id="hora-actual"><?= date('d/m/Y H:i') ?></p>
  </div>
  <a href="<?= $appUrl ?>/vendas" class="btn btn-outline-secondary btn-sm">
    <i class="bi bi-arrow-left me-1"></i>Voltar
  </a>
</div>

<form id="form-venda" action="<?= $appUrl ?>/vendas/nova" method="POST">
  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
  <input type="hidden" name="itens_json" id="itens_json" value="[]">
  <input type="hidden" name="cliente_id" id="cliente_id_hidden" value="">
  <input type="hidden" name="subtotal" id="input_subtotal" value="0">
  <input type="hidden" name="total" id="input_total" value="0">
  <input type="hidden" name="desconto" id="input_desconto" value="0">
  <input type="hidden" name="valor_pago" id="input_valor_pago" value="0">
  <input type="hidden" name="forma_pagamento" id="input_forma_pagamento" value="dinheiro">

<div class="pos-wrapper">

  <!-- ── Coluna esquerda: produtos e carrinho ── -->
  <div class="pos-left">

    <!-- Pesquisa de produto -->
    <div class="card border-0 shadow-sm">
      <div class="card-body py-3">
        <label class="form-label fw-semibold mb-2">
          <i class="bi bi-search me-1 text-success"></i>Pesquisar Produto
        </label>
        <div class="input-group">
          <input type="text" id="pesquisa-produto" class="form-control form-control-lg"
                 placeholder="Nome, código de barras ou princípio activo..."
                 autocomplete="off">
          <button type="button" class="btn btn-success" onclick="focarPesquisa()">
            <i class="bi bi-upc-scan"></i>
          </button>
        </div>
        <div id="resultados-produtos" class="border rounded mt-1 bg-white d-none"
             style="max-height:320px;overflow-y:auto;position:relative;z-index:100">
        </div>
      </div>
    </div>

    <!-- Carrinho -->
    <div class="card border-0 shadow-sm flex-grow-1">
      <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
        <h6 class="fw-bold mb-0"><i class="bi bi-cart3 me-2 text-success"></i>Itens da Venda</h6>
        <span class="badge bg-success" id="badge-itens">0 itens</span>
      </div>
      <div class="card-body p-0">
        <div id="carrinho-vazio" class="text-center py-5">
          <i class="bi bi-cart-x text-muted" style="font-size:3rem"></i>
          <p class="text-muted mt-3 mb-0">Nenhum produto adicionado.</p>
          <small class="text-muted">Pesquise um produto acima.</small>
        </div>
        <div id="carrinho-tabela" class="d-none">
          <div class="table-responsive">
            <table class="table cart-table align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th class="ps-3">Produto / Lote (FEFO)</th>
                  <th class="text-center" style="width:100px">Qtd</th>
                  <th class="text-end" style="width:110px">Preço</th>
                  <th class="text-end" style="width:110px">Subtotal</th>
                  <th style="width:40px"></th>
                </tr>
              </thead>
              <tbody id="tbody-carrinho"></tbody>
            </table>
          </div>
          <!-- Desconto geral -->
          <div class="px-3 py-2 border-top">
            <div class="d-flex align-items-center gap-2">
              <label class="text-muted mb-0" style="font-size:13px;min-width:80px">Desconto (MZN):</label>
              <input type="number" id="desconto-geral" class="form-control form-control-sm"
                     style="max-width:120px" min="0" step="0.01" value="0"
                     oninput="recalcularTotal()">
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>

  <!-- ── Coluna direita: cliente + pagamento ── -->
  <div class="pos-right">

    <!-- Cliente -->
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white py-3">
        <h6 class="fw-bold mb-0"><i class="bi bi-person me-2 text-success"></i>Cliente</h6>
      </div>
      <div class="card-body py-3">
        <div class="input-group mb-2">
          <input type="text" id="pesquisa-cliente" class="form-control"
                 placeholder="Nome ou telefone..." autocomplete="off">
          <button type="button" class="btn btn-outline-secondary" onclick="limparCliente()">
            <i class="bi bi-x-lg"></i>
          </button>
        </div>
        <div id="resultados-clientes" class="border rounded bg-white d-none"
             style="max-height:180px;overflow-y:auto;z-index:100;position:relative">
        </div>
        <div id="cliente-selecionado" class="d-none">
          <div class="d-flex align-items-center gap-2 p-2 bg-light rounded">
            <i class="bi bi-person-check-fill text-success"></i>
            <div>
              <div class="fw-semibold" id="cliente-nome" style="font-size:13px"></div>
              <div class="text-muted" id="cliente-telefone" style="font-size:12px"></div>
            </div>
          </div>
        </div>
        <div class="text-muted mt-1" style="font-size:11px">
          Opcional — deixe em branco para venda balcão
        </div>
      </div>
    </div>

    <!-- Pagamento -->
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white py-3">
        <h6 class="fw-bold mb-0"><i class="bi bi-cash-stack me-2 text-success"></i>Pagamento</h6>
      </div>
      <div class="card-body py-3">

        <!-- Totais -->
        <div class="mb-3 p-3 bg-light rounded">
          <div class="d-flex justify-content-between mb-1" style="font-size:13px">
            <span class="text-muted">Subtotal</span>
            <span id="display-subtotal">0,00 MZN</span>
          </div>
          <div class="d-flex justify-content-between mb-2" style="font-size:13px">
            <span class="text-muted">Desconto</span>
            <span class="text-danger" id="display-desconto">-0,00 MZN</span>
          </div>
          <div class="d-flex justify-content-between fw-bold" style="font-size:18px;border-top:2px solid #dee2e6;padding-top:8px">
            <span>TOTAL</span>
            <span class="text-success" id="display-total">0,00 MZN</span>
          </div>
        </div>

        <!-- Forma de pagamento -->
        <label class="form-label fw-semibold mb-2" style="font-size:13px">Forma de Pagamento</label>
        <div class="row g-2 mb-3" id="formas-pagamento">
          <?php
          $formas = [
            ['dinheiro','Dinheiro','bi-cash'],
            ['mpesa','M-Pesa','bi-phone'],
            ['emola','e-Mola','bi-phone-fill'],
            ['cartao_debito','Débito','bi-credit-card'],
            ['cartao_credito','Crédito','bi-credit-card-2-front'],
            ['transferencia','Transfer.','bi-bank'],
          ];
          foreach ($formas as [$val,$label,$icon]):
          ?>
          <div class="col-4">
            <button type="button"
                    class="btn btn-outline-secondary w-100 py-2 forma-btn <?= $val==='dinheiro'?'active btn-success text-white border-success':'' ?>"
                    data-forma="<?= $val ?>" onclick="selecionarForma(this)"
                    style="font-size:11px;font-weight:600">
              <i class="bi <?= $icon ?> d-block mb-1" style="font-size:16px"></i>
              <?= $label ?>
            </button>
          </div>
          <?php endforeach; ?>
        </div>

        <!-- Valor pago -->
        <label class="form-label fw-semibold mb-1" style="font-size:13px">Valor Recebido (MZN)</label>
        <input type="number" id="valor-pago-input" class="form-control form-control-lg fw-bold text-end mb-2"
               min="0" step="0.01" placeholder="0,00" oninput="calcularTroco()">

        <!-- Atalhos de valor -->
        <div class="row g-1 mb-3" id="atalhos-valor">
          <div class="col-3"><button type="button" class="btn btn-outline-secondary btn-sm w-100" onclick="valorExato()">Exacto</button></div>
          <div class="col-3"><button type="button" class="btn btn-outline-secondary btn-sm w-100" onclick="adicionarValor(500)">+500</button></div>
          <div class="col-3"><button type="button" class="btn btn-outline-secondary btn-sm w-100" onclick="adicionarValor(1000)">+1000</button></div>
          <div class="col-3"><button type="button" class="btn btn-outline-secondary btn-sm w-100" onclick="adicionarValor(2000)">+2000</button></div>
        </div>

        <!-- Troco -->
        <div class="p-2 rounded mb-3 text-center" id="troco-box" style="background:#f0f9f4;display:none!important">
          <div class="text-muted" style="font-size:11px">TROCO</div>
          <div class="fw-bold text-success" id="display-troco" style="font-size:22px">0,00 MZN</div>
        </div>

        <!-- Observações -->
        <label class="form-label fw-semibold mb-1" style="font-size:12px">Observações (opcional)</label>
        <textarea name="observacoes" class="form-control mb-3" rows="2" style="font-size:13px"
                  placeholder="Notas sobre a venda..."></textarea>

        <!-- Botão finalizar -->
        <button type="button" id="btn-finalizar" class="btn btn-success w-100 py-3 fw-bold"
                style="font-size:16px" onclick="finalizarVenda()" disabled>
          <i class="bi bi-check-circle me-2"></i>Finalizar Venda
        </button>

        <div id="alerta-receita" class="alert alert-warning py-2 px-3 mt-2 mb-0 d-none" style="font-size:12px">
          <i class="bi bi-exclamation-triangle me-1"></i>
          Atenção: A venda contém produto(s) que requerem <strong>receita médica</strong>.
        </div>
      </div>
    </div>

  </div>
</div>
</form>

<script>
const APP_URL     = '<?= $appUrl ?>';
let carrinho      = [];
let clienteActual = null;
let formaActual   = 'dinheiro';

// ── Hora em tempo real ──
setInterval(() => {
  const now = new Date();
  document.getElementById('hora-actual').textContent =
    now.toLocaleDateString('pt-MZ') + ' ' + now.toLocaleTimeString('pt-MZ');
}, 1000);

// ── Pesquisa de produto ──
let timerProduto;
let _ignorarInputProduto = false;
document.getElementById('pesquisa-produto').addEventListener('input', function () {
  if (_ignorarInputProduto) return;
  clearTimeout(timerProduto);
  const q = this.value.trim();
  if (q.length < 2) { fecharResultados('resultados-produtos'); return; }
  timerProduto = setTimeout(() => pesquisarProduto(q), 300);
});

document.getElementById('pesquisa-produto').addEventListener('keydown', function (e) {
  if (e.key === 'Escape') fecharResultados('resultados-produtos');
});

let _produtosCache = {};

// ── Badge de validade ──
function badgeLote(diasParaVencer, numeroLote) {
  if (!numeroLote) return '';
  let cls = 'badge-lote-ok', txt = 'OK';
  if (diasParaVencer <= 30)      { cls = 'badge-lote-critico'; txt = `${diasParaVencer}d`; }
  else if (diasParaVencer <= 60) { cls = 'badge-lote-atencao'; txt = `${diasParaVencer}d`; }
  else if (diasParaVencer <= 90) { cls = 'badge-lote-aviso';   txt = `${diasParaVencer}d`; }
  return `<span class="${cls}">Lote: ${escHtml(numeroLote)} · ${txt}</span>`;
}

async function pesquisarProduto(q) {
  try {
    const res  = await fetch(`${APP_URL}/api/produtos/pesquisar?q=${encodeURIComponent(q)}`);
    const json = await res.json();
    const data = Array.isArray(json) ? json : (json.produtos || []);
    const div  = document.getElementById('resultados-produtos');

    if (!data.length) {
      div.innerHTML = '<div class="p-3 text-muted text-center" style="font-size:13px">Nenhum produto encontrado.</div>';
      div.classList.remove('d-none');
      return;
    }

    data.forEach(p => { _produtosCache[p.id] = p; });

    div.innerHTML = data.map(p => {
      // Validade do lote FEFO mais antigo
      let loteHtml = '';
      if (p.proxima_validade) {
        const dias = Math.round((new Date(p.proxima_validade) - new Date()) / 86400000);
        loteHtml = badgeLote(dias, p.numero_lote || 'FEFO');
      }
      return `
      <div class="produto-resultado" data-produto-id="${p.id}">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <div class="fw-semibold" style="font-size:13px">${escHtml(p.nome)}
              ${p.requer_receita ? '<span class="badge-receita ms-1">RX</span>' : ''}
            </div>
            <div class="lote-info">${escHtml(p.categoria_nome || p.categoria || '')} ${loteHtml}</div>
          </div>
          <div class="text-end">
            <div class="fw-bold text-success" style="font-size:13px">${formatMZN(p.preco_venda)}</div>
            <div class="text-muted" style="font-size:11px">Stock: ${p.estoque_actual}</div>
          </div>
        </div>
      </div>`;
    }).join('');

    div.querySelectorAll('.produto-resultado').forEach(el => {
      el.addEventListener('click', function() {
        const pid = parseInt(this.dataset.produtoId);
        adicionarAoCarrinho(_produtosCache[pid]);
      });
    });

    div.classList.remove('d-none');
  } catch(e) { console.error(e); }
}

// ── Adicionar ao carrinho com info de lote FEFO ──
async function adicionarAoCarrinho(produto) {
  fecharResultados('resultados-produtos');
  _ignorarInputProduto = true;
  document.getElementById('pesquisa-produto').value = '';
  _ignorarInputProduto = false;
  document.getElementById('pesquisa-produto').focus();

  const idx = carrinho.findIndex(i => i.produto_id === produto.id);
  if (idx >= 0) {
    if (carrinho[idx].quantidade < produto.estoque_actual) {
      carrinho[idx].quantidade++;
      recalcularItem(idx);
    } else {
      alert(`Stock insuficiente. Disponível: ${produto.estoque_actual}`);
    }
  } else {
    // Buscar lotes disponíveis para mostrar info FEFO
    let loteInfo = null;
    try {
      const r = await fetch(`${APP_URL}/api/produtos/${produto.id}/lotes`);
      const lotes = await r.json();
      if (lotes.length > 0) {
        loteInfo = lotes[0]; // primeiro = mais antigo (FEFO)
      }
    } catch(e) { /* ignora erro de rede */ }

    carrinho.push({
      produto_id:       produto.id,
      nome:             produto.nome,
      preco_unitario:   parseFloat(produto.preco_venda),
      quantidade:       1,
      desconto_item:    0,
      subtotal:         parseFloat(produto.preco_venda),
      estoque_max:      produto.estoque_actual,
      requer_receita:   produto.requer_receita,
      lote_info:        loteInfo, // {numero_lote, validade, dias_para_vencer, status_validade}
    });
  }

  renderCarrinho();
}

function recalcularItem(idx) {
  const item = carrinho[idx];
  item.subtotal = (item.preco_unitario * item.quantidade) - item.desconto_item;
  item.subtotal = Math.max(0, item.subtotal);
}

// ── Renderizar carrinho com info de lote ──
function renderCarrinho() {
  const tbody   = document.getElementById('tbody-carrinho');
  const vazio   = document.getElementById('carrinho-vazio');
  const tabela  = document.getElementById('carrinho-tabela');
  const badge   = document.getElementById('badge-itens');
  const alertaRx= document.getElementById('alerta-receita');

  if (!carrinho.length) {
    vazio.classList.remove('d-none');
    tabela.classList.add('d-none');
    badge.textContent = '0 itens';
    alertaRx.classList.add('d-none');
    document.getElementById('btn-finalizar').disabled = true;
    recalcularTotal();
    return;
  }

  vazio.classList.add('d-none');
  tabela.classList.remove('d-none');
  badge.textContent = carrinho.length + (carrinho.length === 1 ? ' item' : ' itens');

  const temReceita = carrinho.some(i => i.requer_receita == 1);
  alertaRx.classList.toggle('d-none', !temReceita);

  tbody.innerHTML = carrinho.map((item, idx) => {
    // Info do lote FEFO
    let loteHtml = '<span style="color:#9ca3af;font-size:10px">Sem lote registado</span>';
    if (item.lote_info) {
      const l = item.lote_info;
      const dataFmt = l.validade ? new Date(l.validade).toLocaleDateString('pt-MZ') : '';
      loteHtml = badgeLote(l.dias_para_vencer, l.numero_lote) +
        (dataFmt ? `<span style="color:#9ca3af;font-size:10px"> Val: ${dataFmt}</span>` : '');
    }
    return `
    <tr>
      <td class="ps-3">
        <div class="fw-semibold" style="font-size:13px">${escHtml(item.nome)}
          ${item.requer_receita ? '<span class="badge-receita ms-1">RX</span>' : ''}
        </div>
        <div class="lote-info">${loteHtml}</div>
      </td>
      <td class="text-center">
        <div class="d-flex align-items-center justify-content-center gap-1">
          <button type="button" class="btn btn-sm btn-outline-secondary px-2 py-0"
                  onclick="alterarQty(${idx}, -1)">−</button>
          <input type="number" class="form-control form-control-sm text-center px-1 fw-bold"
                 style="width:52px" min="1" max="${item.estoque_max}"
                 value="${item.quantidade}"
                 onchange="setQty(${idx}, this.value)">
          <button type="button" class="btn btn-sm btn-outline-secondary px-2 py-0"
                  onclick="alterarQty(${idx}, 1)">+</button>
        </div>
      </td>
      <td class="text-end" style="font-size:13px">${formatMZN(item.preco_unitario)}</td>
      <td class="text-end fw-semibold text-success" style="font-size:14px">${formatMZN(item.subtotal)}</td>
      <td>
        <button type="button" class="btn btn-sm btn-outline-danger"
                onclick="removerItem(${idx})" title="Remover">
          <i class="bi bi-trash3"></i>
        </button>
      </td>
    </tr>`;
  }).join('');

  document.getElementById('btn-finalizar').disabled = false;
  recalcularTotal();
}

function alterarQty(idx, delta) {
  const novaQty = carrinho[idx].quantidade + delta;
  if (novaQty < 1) { removerItem(idx); return; }
  if (novaQty > carrinho[idx].estoque_max) {
    alert(`Stock máximo: ${carrinho[idx].estoque_max}`);
    return;
  }
  carrinho[idx].quantidade = novaQty;
  recalcularItem(idx);
  renderCarrinho();
}

function setQty(idx, val) {
  const qty = Math.max(1, Math.min(parseInt(val) || 1, carrinho[idx].estoque_max));
  carrinho[idx].quantidade = qty;
  recalcularItem(idx);
  renderCarrinho();
}

function removerItem(idx) {
  carrinho.splice(idx, 1);
  renderCarrinho();
}

// ── Totais ──
function recalcularTotal() {
  const subtotal  = carrinho.reduce((s, i) => s + i.subtotal, 0);
  const desconto  = parseFloat(document.getElementById('desconto-geral').value) || 0;
  const total     = Math.max(0, subtotal - desconto);

  document.getElementById('display-subtotal').textContent = formatMZN(subtotal);
  document.getElementById('display-desconto').textContent = '-' + formatMZN(desconto);
  document.getElementById('display-total').textContent    = formatMZN(total);

  document.getElementById('input_subtotal').value = subtotal.toFixed(2);
  document.getElementById('input_total').value    = total.toFixed(2);
  document.getElementById('input_desconto').value = desconto.toFixed(2);

  calcularTroco();
}

function calcularTroco() {
  const total     = parseFloat(document.getElementById('input_total').value) || 0;
  const valorPago = parseFloat(document.getElementById('valor-pago-input').value) || 0;
  const troco     = valorPago - total;
  const box       = document.getElementById('troco-box');
  const disp      = document.getElementById('display-troco');

  document.getElementById('input_valor_pago').value = valorPago.toFixed(2);

  if (valorPago > 0) {
    box.style.display = 'block';
    disp.textContent  = formatMZN(Math.max(0, troco));
    disp.className    = troco >= 0 ? 'fw-bold text-success' : 'fw-bold text-danger';
    disp.style.fontSize = '22px';
  } else {
    box.style.display = 'none';
  }
}

// ── Forma de pagamento ──
function selecionarForma(btn) {
  document.querySelectorAll('.forma-btn').forEach(b => {
    b.classList.remove('active','btn-success','text-white','border-success');
    b.classList.add('btn-outline-secondary');
  });
  btn.classList.add('active','btn-success','text-white','border-success');
  btn.classList.remove('btn-outline-secondary');
  formaActual = btn.dataset.forma;
  document.getElementById('input_forma_pagamento').value = formaActual;

  if (formaActual !== 'dinheiro') {
    valorExato();
    document.getElementById('atalhos-valor').style.opacity = '0.4';
  } else {
    document.getElementById('atalhos-valor').style.opacity = '1';
  }
}

function valorExato() {
  const total = parseFloat(document.getElementById('input_total').value) || 0;
  document.getElementById('valor-pago-input').value = total.toFixed(2);
  calcularTroco();
}

function adicionarValor(v) {
  const atual = parseFloat(document.getElementById('valor-pago-input').value) || 0;
  document.getElementById('valor-pago-input').value = (atual + v).toFixed(2);
  calcularTroco();
}

// ── Pesquisa de cliente ──
let timerCliente;
document.getElementById('pesquisa-cliente').addEventListener('input', function () {
  clearTimeout(timerCliente);
  const q = this.value.trim();
  if (q.length < 2) { fecharResultados('resultados-clientes'); return; }
  timerCliente = setTimeout(() => pesquisarCliente(q), 300);
});

async function pesquisarCliente(q) {
  try {
    const res  = await fetch(`${APP_URL}/api/clientes/pesquisar?q=${encodeURIComponent(q)}`);
    const data = await res.json();
    const div  = document.getElementById('resultados-clientes');

    if (!data.length) {
      div.innerHTML = '<div class="p-3 text-muted text-center" style="font-size:13px">Cliente não encontrado.</div>';
      div.classList.remove('d-none');
      return;
    }

    div.innerHTML = data.map(c => `
      <div class="produto-resultado" onclick='selecionarCliente(${JSON.stringify(c).replace(/'/g,"&#39;")})'>
        <div class="fw-semibold" style="font-size:13px">${escHtml(c.nome)}</div>
        <div class="text-muted" style="font-size:11px">${c.telefone || ''} ${c.nuit ? '| NUIT: '+c.nuit : ''}</div>
      </div>`).join('');

    div.classList.remove('d-none');
  } catch(e) { console.error(e); }
}

function selecionarCliente(cliente) {
  clienteActual = cliente;
  document.getElementById('cliente_id_hidden').value = cliente.id;
  document.getElementById('cliente-nome').textContent = cliente.nome;
  document.getElementById('cliente-telefone').textContent = cliente.telefone || '';
  document.getElementById('cliente-selecionado').classList.remove('d-none');
  document.getElementById('pesquisa-cliente').classList.add('d-none');
  fecharResultados('resultados-clientes');
}

function limparCliente() {
  clienteActual = null;
  document.getElementById('cliente_id_hidden').value = '';
  document.getElementById('cliente-selecionado').classList.add('d-none');
  document.getElementById('pesquisa-cliente').classList.remove('d-none');
  document.getElementById('pesquisa-cliente').value = '';
}

// ── Finalizar venda ──
function finalizarVenda() {
  if (!carrinho.length) { alert('Adicione pelo menos um produto.'); return; }

  const total     = parseFloat(document.getElementById('input_total').value) || 0;
  const valorPago = parseFloat(document.getElementById('valor-pago-input').value) || 0;

  if (formaActual === 'dinheiro' && valorPago < total) {
    alert('O valor recebido é inferior ao total da venda.');
    document.getElementById('valor-pago-input').focus();
    return;
  }

  if (valorPago <= 0) { valorExato(); }

  document.getElementById('itens_json').value = JSON.stringify(
    carrinho.map(i => ({
      produto_id:    i.produto_id,
      quantidade:    i.quantidade,
      desconto_item: i.desconto_item,
    }))
  );

  const btn = document.getElementById('btn-finalizar');
  btn.disabled = true;
  btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>A processar...';

  document.getElementById('form-venda').submit();
}

// ── Utilitários ──
function fecharResultados(id) {
  document.getElementById(id)?.classList.add('d-none');
}
function focarPesquisa() {
  document.getElementById('pesquisa-produto').focus();
}
function formatMZN(val) {
  return parseFloat(val).toLocaleString('pt-MZ', {minimumFractionDigits:2, maximumFractionDigits:2}) + ' MZN';
}
function escHtml(str) {
  return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

document.addEventListener('click', function(e) {
  if (!e.target.closest('#pesquisa-produto') && !e.target.closest('#resultados-produtos')) {
    fecharResultados('resultados-produtos');
  }
  if (!e.target.closest('#pesquisa-cliente') && !e.target.closest('#resultados-clientes')) {
    fecharResultados('resultados-clientes');
  }
});

document.getElementById('pesquisa-produto').focus();
</script>
