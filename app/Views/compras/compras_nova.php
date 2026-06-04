<?php
$appUrl = $_ENV['APP_URL'] ?? '';
$csrf_token = $csrf_token ?? '';
$fornecedores = $fornecedores ?? [];
$categorias = $categorias ?? [];
?>

<style>
.produto-resultado { cursor:pointer; padding:10px 14px; border-bottom:1px solid #f0f0f0; transition:background .1s; }
.produto-resultado:hover { background:#f0f9f4; }
.produto-resultado:last-child { border-bottom:none; }
</style>

<div class="d-flex align-items-center justify-content-between mb-3">
  <div>
    <h1 class="page-title">Nova Compra</h1>
    <p class="page-subtitle">Encomenda a fornecedor</p>
  </div>
  <a href="<?= $appUrl ?>/compras" class="btn btn-outline-secondary btn-sm">
    <i class="bi bi-arrow-left me-1"></i>Voltar
  </a>
</div>

<form id="form-compra" action="<?= $appUrl ?>/compras/nova" method="POST">
  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
  <input type="hidden" name="itens_json" id="itens_json" value="[]">

<div class="row g-4">

  <!-- Coluna esquerda: produtos -->
  <div class="col-12 col-lg-8">

    <!-- Pesquisa de produto -->
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-body py-3">
        <div class="d-flex justify-content-between align-items-center mb-2 gap-2 flex-wrap">
          <label class="form-label fw-semibold mb-0">
            <i class="bi bi-search me-1 text-success"></i>Adicionar Produto
          </label>
          <button type="button" id="btn-toggle-novo-produto" class="btn btn-sm btn-outline-success">
            <i class="bi bi-plus-circle me-1"></i>Novo produto
          </button>
        </div>
        <div class="input-group">
          <input type="text" id="pesquisa-produto" class="form-control"
                 placeholder="Pesquisar por nome ou código de barras..."
                 autocomplete="off">
        </div>
        <div id="resultados-produtos" class="border rounded mt-1 bg-white d-none"
             style="max-height:260px;overflow-y:auto;position:relative;z-index:100"></div>

        <div id="form-novo-produto" class="d-none mt-3 border rounded p-3 bg-light-subtle">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="fw-bold mb-0">Criar produto rapidamente</h6>
            <button type="button" id="btn-fechar-novo-produto" class="btn btn-sm btn-outline-secondary">
              <i class="bi bi-x-lg"></i>
            </button>
          </div>
          <div class="row g-2">
            <div class="col-12 col-md-6">
              <label class="form-label small mb-1">Nome</label>
              <input type="text" id="novo-produto-nome" class="form-control form-control-sm" placeholder="Ex.: Amoxicilina 500mg">
            </div>
            <div class="col-12 col-md-6">
              <label class="form-label small mb-1">Categoria</label>
              <select id="novo-produto-categoria" class="form-select form-select-sm">
                <option value="">Seleccione uma categoria</option>
                <?php foreach ($categorias as $categoria): ?>
                <option value="<?= (int)$categoria['id'] ?>">
                  <?= htmlspecialchars($categoria['pai_nome'] ? $categoria['pai_nome'] . ' > ' . $categoria['nome'] : $categoria['nome']) ?>
                </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-12 col-md-4">
              <label class="form-label small mb-1">Código de barras</label>
              <input type="text" id="novo-produto-codigo" class="form-control form-control-sm" placeholder="Opcional">
            </div>
            <div class="col-12 col-md-4">
              <label class="form-label small mb-1">Unidade</label>
              <input type="text" id="novo-produto-unidade" class="form-control form-control-sm" value="unidade">
            </div>
            <div class="col-12 col-md-4">
              <label class="form-label small mb-1">Stock mínimo</label>
              <input type="number" id="novo-produto-min" class="form-control form-control-sm" min="0" value="5">
            </div>
            <div class="col-12 col-md-6">
              <label class="form-label small mb-1">Preço de compra</label>
              <input type="number" id="novo-produto-compra" class="form-control form-control-sm" min="0" step="0.01" value="0">
            </div>
            <div class="col-12 col-md-6">
              <label class="form-label small mb-1">Preço de venda</label>
              <input type="number" id="novo-produto-venda" class="form-control form-control-sm" min="0" step="0.01" value="0">
            </div>
            <div class="col-12">
              <label class="form-label small mb-1">Observações</label>
              <textarea id="novo-produto-descricao" class="form-control form-control-sm" rows="2" placeholder="Opcional"></textarea>
            </div>
          </div>
          <div class="d-flex justify-content-end mt-3">
            <button type="button" id="btn-salvar-novo-produto" class="btn btn-sm btn-success">
              <i class="bi bi-check-circle me-1"></i>Salvar e adicionar
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Tabela de itens -->
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
        <h6 class="fw-bold mb-0"><i class="bi bi-list-ul me-2 text-success"></i>Itens da Compra</h6>
        <span class="badge bg-success" id="badge-itens">0 itens</span>
      </div>
      <div class="card-body p-0">
        <div id="itens-vazio" class="text-center py-5">
          <i class="bi bi-box-seam text-muted" style="font-size:3rem"></i>
          <p class="text-muted mt-3 mb-0">Nenhum produto adicionado.</p>
          <small class="text-muted">Pesquise um produto acima.</small>
        </div>
        <div id="itens-tabela" class="d-none">
          <div class="table-responsive">
            <table class="table align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th class="ps-3" style="font-size:12px">Produto</th>
                  <th class="text-center" style="width:90px;font-size:12px">Qtd.</th>
                  <th class="text-end" style="width:120px;font-size:12px">Preço Unit.</th>
                  <th class="text-center" style="width:120px;font-size:12px">Lote</th>
                  <th class="text-center" style="width:120px;font-size:12px">Validade</th>
                  <th class="text-end" style="width:110px;font-size:12px">Subtotal</th>
                  <th style="width:40px"></th>
                </tr>
              </thead>
              <tbody id="tbody-itens"></tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

  </div>

  <!-- Coluna direita: fornecedor + totais -->
  <div class="col-12 col-lg-4">

    <!-- Fornecedor -->
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-header bg-white py-3">
        <h6 class="fw-bold mb-0"><i class="bi bi-building me-2 text-success"></i>Fornecedor</h6>
      </div>
      <div class="card-body py-3">
        <select name="fornecedor_id" id="fornecedor_id" class="form-select" required>
          <option value="">Seleccione um fornecedor...</option>
          <?php foreach ($fornecedores as $f): ?>
          <option value="<?= $f['id'] ?>"><?= htmlspecialchars($f['nome']) ?></option>
          <?php endforeach; ?>
        </select>
        <?php if (empty($fornecedores)): ?>
        <div class="alert alert-warning py-2 mt-2 mb-0" style="font-size:12px">
          <i class="bi bi-exclamation-triangle me-1"></i>
          Nenhum fornecedor activo.
          <a href="<?= $appUrl ?>/fornecedores/novo" target="_blank" class="alert-link">
            Registar fornecedor →
          </a>
        </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Detalhes da encomenda -->
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-header bg-white py-3">
        <h6 class="fw-bold mb-0"><i class="bi bi-file-text me-2 text-success"></i>Detalhes</h6>
      </div>
      <div class="card-body py-3">
        <div class="mb-3">
          <label class="form-label small fw-semibold">Nº Fatura do Fornecedor</label>
          <input type="text" name="numero_fatura" class="form-control form-control-sm"
                 placeholder="Opcional">
        </div>
        <div class="mb-3">
          <label class="form-label small fw-semibold">Data do Pedido</label>
          <input type="date" name="data_pedido" class="form-control form-control-sm"
                 value="<?= date('Y-m-d') ?>">
        </div>
        <div class="mb-3">
          <label class="form-label small fw-semibold">Data de Entrega Prevista</label>
          <input type="date" name="data_entrega" class="form-control form-control-sm">
        </div>
        <div class="mb-3">
          <label class="form-label small fw-semibold">Estado</label>
          <select name="status" class="form-select form-select-sm">
            <option value="rascunho">Rascunho</option>
            <option value="enviada">Enviada ao Fornecedor</option>
          </select>
        </div>
        <div class="mb-0">
          <label class="form-label small fw-semibold">Observações</label>
          <textarea name="observacoes" class="form-control form-control-sm" rows="2"
                    placeholder="Notas sobre a encomenda..."></textarea>
        </div>
      </div>
    </div>

    <!-- Totais -->
    <div class="card border-0 shadow-sm">
      <div class="card-body py-3">
        <div class="mb-2 d-flex justify-content-between" style="font-size:13px">
          <span class="text-muted">Subtotal</span>
          <span id="display-subtotal">0,00 MZN</span>
        </div>
        <div class="mb-3 d-flex align-items-center gap-2" style="font-size:13px">
          <span class="text-muted" style="min-width:70px">Desconto</span>
          <input type="number" name="desconto" id="desconto-input" class="form-control form-control-sm"
                 style="max-width:110px;text-align:right" min="0" step="0.01" value="0"
                 oninput="recalcularTotal()">
          <span class="text-muted">MZN</span>
        </div>
        <div class="d-flex justify-content-between fw-bold border-top pt-2" style="font-size:18px">
          <span>TOTAL</span>
          <span class="text-success" id="display-total">0,00 MZN</span>
        </div>

        <button type="button" id="btn-guardar" class="btn btn-success w-100 py-2 mt-3 fw-bold"
                onclick="guardarCompra()" disabled>
          <i class="bi bi-check-circle me-2"></i>Guardar Compra
        </button>
      </div>
    </div>

  </div>
</div>
</form>

<script>
const APP_URL = '<?= $appUrl ?>';
let itens = [];
let _produtosCache = {};
let _ignorarInput = false;

// ── Pesquisa de produto ──
let timer;
document.getElementById('pesquisa-produto').addEventListener('input', function () {
  if (_ignorarInput) return;
  clearTimeout(timer);
  const q = this.value.trim();
  if (q.length < 2) { fecharResultados(); return; }
  timer = setTimeout(() => pesquisarProduto(q), 300);
});

document.getElementById('btn-toggle-novo-produto').addEventListener('click', function () {
  document.getElementById('form-novo-produto').classList.toggle('d-none');
  if (!document.getElementById('form-novo-produto').classList.contains('d-none')) {
    document.getElementById('novo-produto-nome').focus();
  }
});

document.getElementById('btn-fechar-novo-produto').addEventListener('click', function () {
  document.getElementById('form-novo-produto').classList.add('d-none');
});

document.getElementById('btn-salvar-novo-produto').addEventListener('click', salvarNovoProduto);

document.addEventListener('click', function(e) {
  if (!e.target.closest('#pesquisa-produto') && !e.target.closest('#resultados-produtos')) {
    fecharResultados();
  }
});

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

    div.innerHTML = data.map(p => `
      <div class="produto-resultado" data-produto-id="${p.id}">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <div class="fw-semibold" style="font-size:13px">${escHtml(p.nome)}</div>
            <div class="text-muted" style="font-size:11px">${escHtml(p.categoria || p.categoria_nome || '')}</div>
          </div>
          <div class="text-end">
            <div class="text-muted" style="font-size:11px">Preço compra: ${formatMZN(p.preco_compra || 0)}</div>
            <div class="text-muted" style="font-size:11px">Stock: ${p.estoque_actual}</div>
          </div>
        </div>
      </div>`).join('');

    div.querySelectorAll('.produto-resultado').forEach(el => {
      el.addEventListener('click', function() {
        adicionarItem(_produtosCache[parseInt(this.dataset.produtoId)]);
      });
    });

    div.classList.remove('d-none');
  } catch(e) { console.error(e); }
}

function adicionarItem(produto) {
  fecharResultados();
  _ignorarInput = true;
  document.getElementById('pesquisa-produto').value = '';
  _ignorarInput = false;
  document.getElementById('pesquisa-produto').focus();

  const idx = itens.findIndex(i => i.produto_id === produto.id);
  if (idx >= 0) {
    itens[idx].quantidade++;
    recalcularItem(idx);
  } else {
    const precoCompra = parseFloat(produto.preco_compra || produto.preco_venda || 0);
    itens.push({
      produto_id:     produto.id,
      nome:           produto.nome,
      preco_unitario: precoCompra,
      quantidade:     1,
      subtotal:       precoCompra,
      numero_lote:    '',
      validade_lote:  '',
    });
  }
  renderItens();
}

async function salvarNovoProduto() {
  const nome = document.getElementById('novo-produto-nome').value.trim();
  const categoriaId = document.getElementById('novo-produto-categoria').value;
  const codigo = document.getElementById('novo-produto-codigo').value.trim();
  const unidade = document.getElementById('novo-produto-unidade').value.trim() || 'unidade';
  const estoqueMin = document.getElementById('novo-produto-min').value;
  const precoCompra = document.getElementById('novo-produto-compra').value;
  const precoVenda = document.getElementById('novo-produto-venda').value;
  const descricao = document.getElementById('novo-produto-descricao').value.trim();

  if (!nome || !categoriaId) {
    alert('Preencha o nome e a categoria do produto.');
    return;
  }

  const btn = document.getElementById('btn-salvar-novo-produto');
  const original = btn.innerHTML;
  btn.disabled = true;
  btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Salvando...';

  try {
    const res = await fetch(`${APP_URL}/api/produtos/inline`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-Token': document.querySelector('input[name="csrf_token"]').value,
      },
      body: JSON.stringify({
        nome,
        categoria_id: parseInt(categoriaId, 10),
        codigo_barras: codigo || null,
        unidade_medida: unidade,
        estoque_min: parseInt(estoqueMin, 10) || 0,
        preco_compra: precoCompra,
        preco_venda: precoVenda,
        principio_ativo: null,
        descricao,
      }),
    });

    const json = await res.json();
    if (!res.ok) {
      const msg = json.errors ? Object.values(json.errors).join('\n') : (json.erro || 'Erro ao salvar produto.');
      alert(msg);
      return;
    }

    const produto = json.produto;
    produto.estoque_actual = produto.estoque_actual ?? 0;
    _produtosCache[produto.id] = produto;
    adicionarItem(produto);
    document.getElementById('form-novo-produto').classList.add('d-none');
    document.getElementById('novo-produto-nome').value = '';
    document.getElementById('novo-produto-categoria').value = '';
    document.getElementById('novo-produto-codigo').value = '';
    document.getElementById('novo-produto-unidade').value = 'unidade';
    document.getElementById('novo-produto-min').value = '5';
    document.getElementById('novo-produto-compra').value = '0';
    document.getElementById('novo-produto-venda').value = '0';
    document.getElementById('novo-produto-descricao').value = '';
  } catch (e) {
    console.error(e);
    alert('Não foi possível salvar o produto agora.');
  } finally {
    btn.disabled = false;
    btn.innerHTML = original;
  }
}

function recalcularItem(idx) {
  itens[idx].subtotal = itens[idx].preco_unitario * itens[idx].quantidade;
}

function renderItens() {
  const tbody  = document.getElementById('tbody-itens');
  const vazio  = document.getElementById('itens-vazio');
  const tabela = document.getElementById('itens-tabela');
  const badge  = document.getElementById('badge-itens');

  if (!itens.length) {
    vazio.classList.remove('d-none');
    tabela.classList.add('d-none');
    badge.textContent = '0 itens';
    document.getElementById('btn-guardar').disabled = true;
    recalcularTotal();
    return;
  }

  vazio.classList.add('d-none');
  tabela.classList.remove('d-none');
  badge.textContent = itens.length + (itens.length === 1 ? ' item' : ' itens');

  tbody.innerHTML = itens.map((item, idx) => `
    <tr>
      <td class="ps-3">
        <div class="fw-semibold" style="font-size:13px">${escHtml(item.nome)}</div>
      </td>
      <td class="text-center">
        <input type="number" class="form-control form-control-sm text-center px-1 fw-bold"
               style="width:60px" min="1" value="${item.quantidade}"
               onchange="setQty(${idx}, this.value)">
      </td>
      <td class="text-end">
        <input type="number" class="form-control form-control-sm text-end px-1"
               style="width:90px" min="0" step="0.01" value="${item.preco_unitario.toFixed(2)}"
               onchange="setPreco(${idx}, this.value)">
      </td>
      <td class="text-center">
        <input type="text" class="form-control form-control-sm text-center px-1"
               style="width:90px" placeholder="Lote" value="${escHtml(item.numero_lote)}"
               onchange="setLote(${idx}, this.value)">
      </td>
      <td class="text-center">
        <input type="date" class="form-control form-control-sm px-1"
               style="width:110px" value="${item.validade_lote}"
               onchange="setValidade(${idx}, this.value)">
      </td>
      <td class="text-end fw-semibold text-success" style="font-size:14px">
        ${formatMZN(item.subtotal)}
      </td>
      <td>
        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removerItem(${idx})">
          <i class="bi bi-trash3"></i>
        </button>
      </td>
    </tr>`).join('');

  document.getElementById('btn-guardar').disabled = false;
  recalcularTotal();
}

function setQty(idx, val) {
  itens[idx].quantidade = Math.max(1, parseInt(val) || 1);
  recalcularItem(idx);
  renderItens();
}

function setPreco(idx, val) {
  itens[idx].preco_unitario = Math.max(0, parseFloat(val) || 0);
  recalcularItem(idx);
  renderItens();
}

function setLote(idx, val)    { itens[idx].numero_lote   = val; sincronizarJson(); }
function setValidade(idx, val){ itens[idx].validade_lote = val; sincronizarJson(); }

function removerItem(idx) {
  itens.splice(idx, 1);
  renderItens();
}

function recalcularTotal() {
  const subtotal = itens.reduce((s, i) => s + i.subtotal, 0);
  const desconto = parseFloat(document.getElementById('desconto-input').value) || 0;
  const total    = Math.max(0, subtotal - desconto);
  document.getElementById('display-subtotal').textContent = formatMZN(subtotal);
  document.getElementById('display-total').textContent    = formatMZN(total);
  sincronizarJson();
}

function sincronizarJson() {
  document.getElementById('itens_json').value = JSON.stringify(
    itens.map(i => ({
      produto_id:     i.produto_id,
      quantidade:     i.quantidade,
      preco_unitario: i.preco_unitario,
      subtotal:       i.subtotal,
      numero_lote:    i.numero_lote    || null,
      validade_lote:  i.validade_lote  || null,
    }))
  );
}

function guardarCompra() {
  if (!itens.length) { alert('Adicione pelo menos um produto.'); return; }
  if (!document.getElementById('fornecedor_id').value) {
    alert('Seleccione um fornecedor.');
    document.getElementById('fornecedor_id').focus();
    return;
  }
  sincronizarJson();
  const btn = document.getElementById('btn-guardar');
  btn.disabled = true;
  btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>A guardar...';
  document.getElementById('form-compra').submit();
}

function fecharResultados() {
  document.getElementById('resultados-produtos').classList.add('d-none');
}

function formatMZN(val) {
  return parseFloat(val).toLocaleString('pt-MZ', {minimumFractionDigits:2, maximumFractionDigits:2}) + ' MZN';
}

function escHtml(str) {
  return String(str||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

document.getElementById('pesquisa-produto').focus();
</script>
