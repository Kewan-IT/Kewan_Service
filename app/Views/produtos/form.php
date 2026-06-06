<?php
// app/Views/produtos/form.php
$APP = $_ENV['APP_URL'] ?? '';
$p   = $produto ?? [];
$e   = $erros   ?? [];

function oldP(array $p, string $k, $def = ''): string { return htmlspecialchars((string)($p[$k] ?? $def)); }
function selP(array $p, string $k, $v): string { return ($p[$k] ?? '') == $v ? 'selected' : ''; }
?>

<style>
.form-section { background:#fff; border-radius:10px; padding:1.25rem 1.5rem; margin-bottom:1rem; box-shadow:0 1px 4px rgba(0,0,0,.07); }
.form-section-title { font-weight:600; font-size:.8rem; text-transform:uppercase; letter-spacing:.05em; color:var(--kf-primary); border-bottom:1px solid var(--kf-primary-light); padding-bottom:.5rem; margin-bottom:1rem; display:flex; align-items:center; gap:.5rem; }
.required::after { content:" *"; color:#dc3545; }
.img-preview { width:120px; height:120px; object-fit:cover; border-radius:8px; border:2px solid var(--kf-primary-light); }
.img-placeholder { width:120px; height:120px; border-radius:8px; background:var(--kf-primary-light); display:flex; align-items:center; justify-content:center; font-size:2.5rem; color:var(--kf-primary); }
.margem-display { font-size:.8rem; padding:.25rem .5rem; border-radius:4px; }
</style>

<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
  <h1 class="h4 fw-bold mb-0" style="color:var(--kf-primary)">
    <i class="bi bi-<?= $modo === 'criar' ? 'plus-circle' : 'pencil-square' ?> me-2"></i>
    <?= $modo === 'criar' ? 'Novo Produto' : 'Editar Produto' ?>
  </h1>
  <a href="<?= $APP ?>/produtos" class="btn btn-sm btn-outline-secondary">
    <i class="bi bi-arrow-left me-1"></i>Voltar
  </a>
</div>

<?php if ($e): ?>
<div class="alert alert-danger alert-dismissible fade show d-flex gap-2 align-items-start mb-4">
  <i class="bi bi-exclamation-triangle-fill flex-shrink-0 mt-1"></i>
  <div>
    <strong>Corrija os erros:</strong>
    <ul class="mb-0 mt-1 ps-3">
      <?php foreach ($e as $msg): ?><li><?= htmlspecialchars($msg) ?></li><?php endforeach; ?>
    </ul>
  </div>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<form method="POST"
      action="<?= $APP ?>/produtos/<?= $modo === 'editar' ? ($p['id'].'/editar') : 'novo' ?>"
      enctype="multipart/form-data" novalidate>
  <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

  <div class="row g-0">
    <!-- Coluna principal -->
    <div class="col-12 col-xl-8 pe-xl-3">

      <!-- Identificação -->
      <div class="form-section">
        <div class="form-section-title"><i class="bi bi-tag"></i> Identificação</div>
        <div class="row g-3">
          <div class="col-12">
            <label class="form-label small required">Nome do produto</label>
            <input type="text" name="nome" class="form-control form-control-sm <?= isset($e['nome']) ? 'is-invalid':'' ?>"
                   value="<?= oldP($p,'nome') ?>" placeholder="ex: Amoxicilina 500mg Cápsulas" required>
            <?php if (isset($e['nome'])): ?><div class="invalid-feedback"><?= $e['nome'] ?></div><?php endif; ?>
          </div>
          <div class="col-sm-6">
            <label class="form-label small">Princípio activo / DCI</label>
            <input type="text" name="principio_ativo" class="form-control form-control-sm"
                   value="<?= oldP($p,'principio_ativo') ?>" placeholder="ex: Amoxicilina">
          </div>
          <div class="col-sm-6">
            <label class="form-label small">Código de barras</label>
            <input type="text" name="codigo_barras" class="form-control form-control-sm <?= isset($e['codigo_barras']) ? 'is-invalid':'' ?>"
                   value="<?= oldP($p,'codigo_barras') ?>" placeholder="EAN-13 ou outro">
            <?php if (isset($e['codigo_barras'])): ?><div class="invalid-feedback"><?= $e['codigo_barras'] ?></div><?php endif; ?>
          </div>
          <div class="col-12">
            <label class="form-label small">Descrição</label>
            <textarea name="descricao" class="form-control form-control-sm" rows="2"
                      placeholder="Indicações, posologia, observações..."><?= oldP($p,'descricao') ?></textarea>
          </div>
        </div>
      </div>

      <!-- Classificação -->
      <div class="form-section">
        <div class="form-section-title"><i class="bi bi-diagram-3"></i> Classificação</div>
        <div class="row g-3">
          <div class="col-sm-6">
            <label class="form-label small required">Categoria</label>
            <select name="categoria_id" class="form-select form-select-sm <?= isset($e['categoria_id']) ? 'is-invalid':'' ?>">
              <option value="0">Seleccionar...</option>
              <?php foreach ($categorias as $c): ?>
              <option value="<?= $c['id'] ?>" <?= selP($p,'categoria_id',$c['id']) ?>>
                <?= $c['pai_nome'] ? '└ ' : '' ?><?= htmlspecialchars($c['nome']) ?>
              </option>
              <?php endforeach; ?>
            </select>
            <?php if (isset($e['categoria_id'])): ?><div class="invalid-feedback"><?= $e['categoria_id'] ?></div><?php endif; ?>
          </div>
          <div class="col-sm-6">
            <label class="form-label small">Fornecedor</label>
            <select name="fornecedor_id" class="form-select form-select-sm">
              <option value="0">— Nenhum —</option>
              <?php foreach ($fornecedores as $f): ?>
              <option value="<?= $f['id'] ?>" <?= selP($p,'fornecedor_id',$f['id']) ?>><?= htmlspecialchars($f['nome']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-sm-6">
            <label class="form-label small">Unidade de medida</label>
            <select name="unidade_medida" class="form-select form-select-sm">
              <?php foreach (['unidade','caixa','frasco','comprimido','ampola','saco','ml','g','kg','l'] as $u): ?>
              <option value="<?= $u ?>" <?= selP($p,'unidade_medida',$u) ?>><?= ucfirst($u) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <!-- Conversão compra / venda -->
        <div class="mt-3 p-3 rounded" style="background:#f0fdf4;border:1px solid #bbf7d0;">
          <div class="form-section-title mb-2" style="border:none;padding:0">
            <i class="bi bi-arrow-left-right"></i> Unidades de Compra e Venda
          </div>
          <p class="text-muted small mb-2">
            Define como o produto é comprado ao fornecedor e como é vendido ao cliente.<br>
            <strong>Exemplo:</strong> Compra-se <em>1 Caixa</em> com <em>10 Cartelas</em> — o stock fica em cartelas.
          </p>
          <div class="row g-3">
            <div class="col-sm-4">
              <label class="form-label small required">Unidade de compra</label>
              <select name="unidade_compra" id="unidade_compra" class="form-select form-select-sm" onchange="actualizarExemplo()">
                <?php foreach (['caixa','frasco','saco','fardo','kg','litro','unidade','blister','strip','pote'] as $u): ?>
                <option value="<?= $u ?>" <?= selP($p,'unidade_compra',$u) ?>><?= ucfirst($u) ?></option>
                <?php endforeach; ?>
              </select>
              <div class="form-text">Como compras ao fornecedor</div>
            </div>
            <div class="col-sm-4">
              <label class="form-label small required">Unidade de venda</label>
              <select name="unidade_venda" id="unidade_venda" class="form-select form-select-sm" onchange="actualizarExemplo()">
                <?php foreach (['unidade','cartela','comprimido','cápsula','ml','g','frasco','ampola','saqueta','dose'] as $u): ?>
                <option value="<?= $u ?>" <?= selP($p,'unidade_venda',$u) ?>><?= ucfirst($u) ?></option>
                <?php endforeach; ?>
              </select>
              <div class="form-text">Como vendes ao cliente</div>
            </div>
            <div class="col-sm-4">
              <label class="form-label small required">Factor de conversão</label>
              <input type="number" name="fator_conversao" id="fator_conversao"
                     class="form-control form-control-sm <?= isset($e['fator_conversao']) ? 'is-invalid':'' ?>"
                     value="<?= oldP($p,'fator_conversao','1') ?>"
                     min="1" step="1" oninput="actualizarExemplo()">
              <div class="form-text">Qtd. de venda por unidade de compra</div>
              <?php if (isset($e['fator_conversao'])): ?><div class="invalid-feedback"><?= $e['fator_conversao'] ?></div><?php endif; ?>
            </div>
          </div>
          <!-- Exemplo dinâmico -->
          <div class="mt-2 p-2 rounded bg-white border" id="exemplo-conversao" style="font-size:13px;color:#166534;">
            <i class="bi bi-info-circle me-1"></i>
            <span id="texto-exemplo">1 Caixa = 1 unidade(s) em stock</span>
          </div>
        </div>

        <!-- Checkboxes -->
        <div class="d-flex gap-4 mt-3 flex-wrap">
          <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="requer_receita" id="requer_receita"
                   <?= ($p['requer_receita'] ?? 0) ? 'checked' : '' ?>>
            <label class="form-check-label small" for="requer_receita">
              <i class="bi bi-file-medical text-warning me-1"></i>Requer receita médica
            </label>
          </div>
          <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="controlado" id="controlado"
                   <?= ($p['controlado'] ?? 0) ? 'checked' : '' ?>>
            <label class="form-check-label small" for="controlado">
              <i class="bi bi-shield-lock text-danger me-1"></i>Medicamento controlado
            </label>
          </div>
          <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="ativo" id="ativo"
                   <?= ($p['ativo'] ?? 1) ? 'checked' : '' ?>>
            <label class="form-check-label small" for="ativo">
              <i class="bi bi-toggle-on text-success me-1"></i>Produto activo
            </label>
          </div>
        </div>
      </div>

      <!-- Preços e stock -->
      <div class="form-section">
        <div class="form-section-title"><i class="bi bi-currency-exchange"></i> Preços e Stock</div>
        <div class="row g-3">
          <div class="col-sm-4">
            <label class="form-label small">Preço de compra (MZN) <span class="text-muted" style="font-size:10px" id="label-unid-compra">por caixa</span></label>
            <input type="number" name="preco_compra" id="precoCompra"
                   class="form-control form-control-sm"
                   value="<?= oldP($p,'preco_compra','0') ?>"
                   min="0" step="0.01" oninput="calcMargem()">
          </div>
          <div class="col-sm-4">
            <label class="form-label small required">Preço de venda (MZN) <span class="text-muted" style="font-size:10px" id="label-unid-venda">por unidade</span></label>
            <input type="number" name="preco_venda" id="precoVenda"
                   class="form-control form-control-sm <?= isset($e['preco_venda']) ? 'is-invalid':'' ?>"
                   value="<?= oldP($p,'preco_venda','0') ?>"
                   min="0.01" step="0.01" required oninput="calcMargem()">
            <?php if (isset($e['preco_venda'])): ?><div class="invalid-feedback"><?= $e['preco_venda'] ?></div><?php endif; ?>
          </div>
          <div class="col-sm-4">
            <label class="form-label small">Margem de lucro</label>
            <div id="margemDisplay" class="margem-display bg-light border text-center fw-bold mt-1">—</div>
          </div>
        </div>
        <!-- Detalhe do lucro por unidade -->
        <div id="lucro-detalhe" class="mt-2 p-2 rounded d-none" style="background:#f0fdf4;border:1px solid #bbf7d0;"></div>
        <div class="row g-3 mt-0">
          <div class="col-sm-4">
            <label class="form-label small">Stock mínimo (alerta)</label>
            <input type="number" name="estoque_min" class="form-control form-control-sm"
                   value="<?= oldP($p,'estoque_min','5') ?>" min="0">
            <div class="form-text">Alerta quando stock cair abaixo deste valor</div>
          </div>
          <?php if ($modo === 'editar'): ?>
          <div class="col-sm-4">
            <label class="form-label small text-muted">Stock actual</label>
            <div class="form-control form-control-sm bg-light text-muted">
              <?= oldP($p,'estoque_actual','0') ?> (só alterável via lotes)
            </div>
          </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Lote inicial (só no criar) -->
      <?php if ($modo === 'criar'): ?>
      <div class="form-section">
        <div class="form-section-title"><i class="bi bi-layers"></i> Lote Inicial (opcional)</div>
        <p class="text-muted small mb-3">Se já tem stock em mãos, registe o primeiro lote aqui.</p>
        <div class="row g-3">
          <div class="col-sm-4">
            <label class="form-label small">Número do lote</label>
            <input type="text" name="lote_numero" class="form-control form-control-sm"
                   placeholder="ex: LOT-2025-001">
          </div>
          <div class="col-sm-3">
            <label class="form-label small">Quantidade</label>
            <input type="number" name="lote_quantidade" class="form-control form-control-sm"
                   min="1" placeholder="0">
          </div>
          <div class="col-sm-3">
            <label class="form-label small">Validade</label>
            <input type="date" name="lote_validade" class="form-control form-control-sm"
                   min="<?= date('Y-m-d') ?>">
          </div>
          <div class="col-sm-2">
            <label class="form-label small">Obs.</label>
            <input type="text" name="lote_obs" class="form-control form-control-sm">
          </div>
        </div>
      </div>
      <?php endif; ?>

    </div><!-- /col principal -->

    <!-- Coluna lateral: imagem -->
    <div class="col-12 col-xl-4">
      <div class="form-section">
        <div class="form-section-title"><i class="bi bi-image"></i> Imagem do Produto</div>
        <div class="d-flex flex-column align-items-center gap-3">
          <?php if (!empty($p['imagem_url'])): ?>
          <img src="<?= $APP ?>/storage/uploads/<?= htmlspecialchars($p['imagem_url']) ?>"
               class="img-preview" id="imgPreview" alt="">
          <?php else: ?>
          <div class="img-placeholder" id="imgPlaceholder">
            <i class="bi bi-capsule"></i>
          </div>
          <img src="" class="img-preview d-none" id="imgPreview" alt="">
          <?php endif; ?>

          <label class="btn btn-sm btn-outline-secondary w-100" for="imagemInput">
            <i class="bi bi-upload me-1"></i>
            <?= !empty($p['imagem_url']) ? 'Substituir imagem' : 'Carregar imagem' ?>
          </label>
          <input type="file" name="imagem" id="imagemInput"
                 accept="image/jpeg,image/png,image/webp" class="d-none"
                 onchange="previewImagem(this)">
          <?php if (isset($e['imagem'])): ?>
          <div class="text-danger small"><?= $e['imagem'] ?></div>
          <?php endif; ?>
          <p class="text-muted small text-center mb-0">JPG, PNG ou WebP · máx. 5 MB</p>
        </div>
      </div>
    </div>
  </div>

  <!-- Botões -->
  <div class="d-flex gap-2 justify-content-end mt-2 mb-4">
    <a href="<?= $APP ?>/produtos" class="btn btn-outline-secondary">
      <i class="bi bi-x-lg me-1"></i>Cancelar
    </a>
    <button type="submit" class="btn px-4" style="background:var(--kf-primary);color:#fff;border:none">
      <i class="bi bi-check-lg me-1"></i>
      <?= $modo === 'criar' ? 'Registar Produto' : 'Guardar Alterações' ?>
    </button>
  </div>
</form>

<script>
function actualizarExemplo() {
  const compra = document.getElementById('unidade_compra')?.value || 'caixa';
  const venda  = document.getElementById('unidade_venda')?.value  || 'unidade';
  const fator  = parseInt(document.getElementById('fator_conversao')?.value) || 1;
  const texto  = document.getElementById('texto-exemplo');
  if (texto) {
    texto.textContent = `1 ${ucFirst(compra)} = ${fator} ${venda}(s) em stock`;
  }
  // Actualizar labels dos preços
  const lbCompra = document.getElementById('label-unid-compra');
  const lbVenda  = document.getElementById('label-unid-venda');
  if (lbCompra) lbCompra.textContent = `por ${compra}`;
  if (lbVenda)  lbVenda.textContent  = `por ${venda}`;
  // Recalcular margem com novo factor
  calcMargem();
}

function ucFirst(str) {
  return str ? str.charAt(0).toUpperCase() + str.slice(1) : '';
}

actualizarExemplo();

function calcMargem() {
  const compraCaixa = parseFloat(document.getElementById('precoCompra').value)    || 0;
  const vendaUnit   = parseFloat(document.getElementById('precoVenda').value)     || 0;
  const fator       = parseFloat(document.getElementById('fator_conversao').value) || 1;
  const el          = document.getElementById('margemDisplay');
  const elDetalhe   = document.getElementById('lucro-detalhe');

  if (compraCaixa > 0 && vendaUnit > 0 && fator > 0) {
    // Custo por unidade de venda (ex: custo por cartela)
    const custoUnit  = compraCaixa / fator;
    // Lucro por unidade de venda
    const lucroUnit  = vendaUnit - custoUnit;
    // Margem sobre o preço de venda (%)
    const margem     = (lucroUnit / vendaUnit * 100).toFixed(1);
    // Lucro por caixa/embalagem completa
    const lucroCaixa = (lucroUnit * fator).toFixed(2);

    const positivo = lucroUnit >= 0;
    el.textContent = margem + '%';
    el.className   = 'margem-display fw-bold text-center mt-1 ' + (positivo ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger');

    if (elDetalhe) {
      const uCompra = document.getElementById('unidade_compra')?.value || 'caixa';
      const uVenda  = document.getElementById('unidade_venda')?.value  || 'unidade';
      elDetalhe.innerHTML = `
        <div class="d-flex flex-wrap gap-3 justify-content-center" style="font-size:12px">
          <span>
            <span class="text-muted">Custo/${uVenda}:</span>
            <strong class="text-danger">MT ${custoUnit.toFixed(2)}</strong>
          </span>
          <span>
            <span class="text-muted">Lucro/${uVenda}:</span>
            <strong class="${positivo ? 'text-success' : 'text-danger'}">MT ${lucroUnit.toFixed(2)}</strong>
          </span>
          <span>
            <span class="text-muted">Lucro/${uCompra}:</span>
            <strong class="${positivo ? 'text-success' : 'text-danger'}">MT ${lucroCaixa}</strong>
          </span>
        </div>`;
      elDetalhe.classList.remove('d-none');
    }
  } else {
    el.textContent = '—';
    el.className   = 'margem-display bg-light border text-center fw-bold mt-1';
    if (elDetalhe) elDetalhe.classList.add('d-none');
  }
}
calcMargem();

function previewImagem(input) {
  if (!input.files[0]) return;
  const reader = new FileReader();
  reader.onload = e => {
    const img = document.getElementById('imgPreview');
    const ph  = document.getElementById('imgPlaceholder');
    img.src = e.target.result;
    img.classList.remove('d-none');
    if (ph) ph.classList.add('d-none');
  };
  reader.readAsDataURL(input.files[0]);
}
</script>
