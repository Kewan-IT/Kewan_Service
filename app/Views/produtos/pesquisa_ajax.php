<!-- =============================================================
   KewanFarma — Widget de Pesquisa AJAX de Produtos
   Para usar no módulo de Vendas (balcão)
   Inclua este snippet na view de nova venda
   ============================================================= -->

<!-- Campo de pesquisa -->
<div class="position-relative" id="pesquisaProdutoWrap">
  <div class="input-group">
    <span class="input-group-text bg-white">
      <i class="bi bi-search text-muted" id="pesqIcon"></i>
    </span>
    <input type="text"
           id="pesquisaProduto"
           class="form-control border-start-0"
           placeholder="Pesquisar produto por nome, código de barras ou princípio activo..."
           autocomplete="off">
    <button class="btn btn-outline-secondary" type="button" onclick="limparPesquisa()" title="Limpar">
      <i class="bi bi-x"></i>
    </button>
  </div>

  <!-- Dropdown de resultados -->
  <div id="resultadosPesquisa"
       class="position-absolute w-100 bg-white border rounded-3 shadow-lg d-none"
       style="top:calc(100% + 4px);z-index:9999;max-height:420px;overflow-y:auto">
  </div>
</div>

<style>
.produto-resultado { padding:.6rem .85rem; cursor:pointer; border-bottom:1px solid #f0f0f0; transition:background .1s; }
.produto-resultado:hover { background:var(--kf-primary-light); }
.produto-resultado:last-child { border-bottom:none; }
.badge-rx  { background:#fff3cd; color:#856404; border:1px solid #ffc107; font-size:.65rem; }
.badge-ctl { background:#f8d7da; color:#842029; border:1px solid #dc3545; font-size:.65rem; }
.sem-stock { opacity:.55; cursor:not-allowed; }
</style>

<script>
(function() {
  const APP    = '<?= $_ENV["APP_URL"] ?? "" ?>';
  const input  = document.getElementById('pesquisaProduto');
  const box    = document.getElementById('resultadosPesquisa');
  const icon   = document.getElementById('pesqIcon');
  let   timer  = null;

  input.addEventListener('input', function() {
    clearTimeout(timer);
    const q = this.value.trim();
    if (q.length < 2) { box.classList.add('d-none'); box.innerHTML = ''; return; }

    icon.className = 'bi bi-hourglass-split text-muted';
    timer = setTimeout(() => pesquisar(q), 280);
  });

  // Fechar ao clicar fora
  document.addEventListener('click', e => {
    if (!document.getElementById('pesquisaProdutoWrap').contains(e.target)) {
      box.classList.add('d-none');
    }
  });

  // Fechar com Escape
  input.addEventListener('keydown', e => {
    if (e.key === 'Escape') { box.classList.add('d-none'); input.blur(); }
  });

  async function pesquisar(q) {
    try {
      const res  = await fetch(`${APP}/api/produtos/pesquisar?q=${encodeURIComponent(q)}`);
      const data = await res.json();
      icon.className = 'bi bi-search text-muted';
      renderResultados(data.produtos || []);
    } catch {
      icon.className = 'bi bi-search text-muted';
      box.innerHTML = '<div class="p-3 text-danger small"><i class="bi bi-exclamation-triangle me-1"></i>Erro ao pesquisar.</div>';
      box.classList.remove('d-none');
    }
  }

  function renderResultados(lista) {
    if (!lista.length) {
      box.innerHTML = '<div class="p-3 text-muted small text-center"><i class="bi bi-search me-1"></i>Nenhum produto encontrado</div>';
      box.classList.remove('d-none');
      return;
    }

    box.innerHTML = lista.map(p => {
      const semStock  = p.estoque_actual <= 0;
      const rxBadge   = p.requer_receita ? '<span class="badge badge-rx me-1">Receita</span>' : '';
      const ctlBadge  = p.controlado     ? '<span class="badge badge-ctl me-1">Controlado</span>' : '';
      const stockCls  = semStock ? 'text-danger' : (p.estoque_actual < 5 ? 'text-warning' : 'text-success');
      const stockIcon = semStock ? 'bi-x-circle' : 'bi-check-circle';
      const preco     = parseFloat(p.preco_venda).toLocaleString('pt-MZ', {minimumFractionDigits:2});

      return `
        <div class="produto-resultado ${semStock ? 'sem-stock' : ''}"
             onclick="${semStock ? '' : `selecionarProduto(${JSON.stringify(p).replace(/"/g,"'")})`}"
             data-id="${p.id}">
          <div class="d-flex align-items-center gap-2">
            ${p.imagem_url
              ? `<img src="${APP}/uploads/${p.imagem_url}" style="width:38px;height:38px;object-fit:cover;border-radius:6px;flex-shrink:0" alt="">`
              : `<div style="width:38px;height:38px;border-radius:6px;background:var(--kf-primary-light);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:1.1rem;color:var(--kf-primary)"><i class="bi bi-capsule"></i></div>`
            }
            <div class="flex-fill min-width-0">
              <div class="fw-semibold text-truncate" style="font-size:.875rem">${p.nome}</div>
              <div class="d-flex align-items-center gap-1 flex-wrap">
                ${rxBadge}${ctlBadge}
                <span class="text-muted" style="font-size:.73rem">${p.categoria}</span>
                ${p.principio_ativo ? `<span class="text-muted" style="font-size:.73rem">· ${p.principio_ativo}</span>` : ''}
              </div>
            </div>
            <div class="text-end flex-shrink-0">
              <div class="fw-bold" style="color:var(--kf-primary);font-size:.9rem">MT ${preco}</div>
              <div class="${stockCls}" style="font-size:.73rem">
                <i class="bi ${stockIcon} me-1"></i>${p.estoque_actual} ${p.unidade_medida}
              </div>
            </div>
          </div>
        </div>
      `;
    }).join('');

    box.classList.remove('d-none');
  }

  // Função global — chamada ao clicar num resultado
  window.selecionarProduto = function(produto) {
    // Fecha o dropdown
    box.classList.add('d-none');
    input.value = '';

    // Dispara evento customizado — o módulo de vendas escuta isto
    document.dispatchEvent(new CustomEvent('produtoSelecionado', { detail: produto }));
  };

  window.limparPesquisa = function() {
    input.value = '';
    box.classList.add('d-none');
    input.focus();
  };
})();
</script>

<!-- =============================================================
   COMO USAR NO MÓDULO DE VENDAS:
   
   1. Inclua este snippet onde quiser o campo de pesquisa:
      <?php include __DIR__ . '/../../produtos/pesquisa_ajax.php'; ?>

   2. Escute o evento 'produtoSelecionado' para adicionar à lista:

   document.addEventListener('produtoSelecionado', function(e) {
     const produto = e.detail;
     // produto contém: id, nome, preco_venda, estoque_actual,
     //                 requer_receita, controlado, lote_id, etc.
     adicionarItemVenda(produto);
   });

   3. Exemplo de adicionarItemVenda():

   function adicionarItemVenda(p) {
     if (p.requer_receita && !receitaConfirmada) {
       if (!confirm('Este produto requer receita médica. Continuar?')) return;
     }
     // adicionar à tabela de itens da venda...
   }
   ============================================================= -->
