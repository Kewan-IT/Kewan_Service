<?php
// app/Views/funcionarios/boletim_pdf.php
// Boletim individual de dados do funcionário — para arquivo, RH e referência interna.

$estadoCivilLabels = [
    'solteiro'       => 'Solteiro(a)',
    'casado'         => 'Casado(a)',
    'divorciado'     => 'Divorciado(a)',
    'viuvo'          => 'Viúvo(a)',
    'uniao_de_facto' => 'União de Facto',
];
$sexoLabels = ['M' => 'Masculino', 'F' => 'Feminino', 'outro' => 'Outro'];
$escolaridadeLabels = [
    'primario'       => 'Ensino Primário',
    'secundario'     => 'Ensino Secundário',
    'tecnico_medio'  => 'Técnico Médio',
    'licenciatura'   => 'Licenciatura',
    'mestrado'       => 'Mestrado',
    'doutoramento'   => 'Doutoramento',
];
$contratoLabels = [
    'efectivo'           => 'Por Tempo Indeterminado',
    'temporario'         => 'A Termo',
    'estagio'            => 'Estágio Profissional',
    'prestacao_servicos' => 'Prestação de Serviços',
];
$statusLabels = [
    'activo'    => ['#1a7f5a', 'Activo'],
    'inactivo'  => ['#6c757d', 'Inactivo'],
    'suspenso'  => ['#d97706', 'Suspenso'],
    'desligado' => ['#dc3545', 'Desligado'],
];
[$statusCor, $statusLabel] = $statusLabels[$f['status']] ?? ['#6c757d', $f['status']];

$nomeEmpregador = $config['nome_farmacia'] ?? ($_ENV['APP_NAME'] ?? 'KewanFarma');
$numeroBoletim  = 'BOL-' . date('Y') . '-' . str_pad((string)$f['id'], 4, '0', STR_PAD_LEFT);

function campo(string $lbl, ?string $val): string {
    $v = htmlspecialchars((string)($val ?: '—'));
    return "<div class=\"campo\"><div class=\"campo-lbl\">{$lbl}</div><div class=\"campo-val\">{$v}</div></div>";
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Boletim — <?= htmlspecialchars($f['nome_completo']) ?></title>
  <style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family:'Segoe UI', Arial, sans-serif; font-size:12.5px; color:#1a1a1a; background:#f5f5f5; line-height:1.5; }

    .no-print { position:fixed; top:20px; right:20px; display:flex; gap:10px; z-index:999; }
    .btn-print, .btn-voltar {
      padding:10px 20px; border-radius:8px; font-size:14px; font-weight:600;
      cursor:pointer; display:flex; align-items:center; gap:8px;
      box-shadow:0 2px 8px rgba(0,0,0,.15); text-decoration:none;
    }
    .btn-print  { background:#1a7f5a; color:#fff; border:none; }
    .btn-print:hover { background:#156347; }
    .btn-voltar { background:#fff; color:#333; border:1px solid #ddd; }

    .page { width:210mm; min-height:297mm; background:#fff; margin:30px auto;
            padding:16mm 18mm; box-shadow:0 4px 24px rgba(0,0,0,.12); }

    /* ── Cabeçalho ── */
    .header { display:flex; justify-content:space-between; align-items:flex-start;
              padding-bottom:12px; border-bottom:3px solid #1a7f5a; margin-bottom:16px; }
    .company-name { font-size:18px; font-weight:700; color:#1a7f5a; }
    .company-sub  { font-size:10.5px; color:#666; margin-top:2px; }
    .doc-meta     { text-align:right; font-size:10.5px; color:#666; }
    .doc-meta strong { color:#1a1a1a; }

    /* ── Identificação do funcionário ── */
    .func-id { display:flex; gap:18px; align-items:flex-start;
               background:#f8fffe; border-radius:10px;
               border:1px solid #d0efe4; padding:14px 16px; margin-bottom:16px; }
    .func-foto { width:88px; height:88px; border-radius:50%; object-fit:cover;
                 border:3px solid #1a7f5a; flex-shrink:0; }
    .func-foto-ph { width:88px; height:88px; border-radius:50%; background:#e0f4eb;
                    display:flex; align-items:center; justify-content:center;
                    font-size:2.4rem; color:#1a7f5a; flex-shrink:0; border:3px solid #1a7f5a; }
    .func-nome { font-size:17px; font-weight:700; color:#1a1a1a; margin-bottom:4px; }
    .func-cargo { font-size:13px; color:#1a7f5a; font-weight:600; margin-bottom:6px; }
    .func-badges { display:flex; flex-wrap:wrap; gap:6px; align-items:center; }
    .badge { display:inline-block; font-size:10px; font-weight:700; padding:3px 8px;
             border-radius:20px; letter-spacing:.2px; }
    .badge-num   { background:#f0fdf4; color:#166534; border:1px solid #bbf7d0; }
    .badge-adm   { background:#eff6ff; color:#1e40af; border:1px solid #bfdbfe; }

    /* ── Secções ── */
    .secao { margin-bottom:14px; }
    .secao-titulo {
      font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.5px;
      color:#fff; background:#1a7f5a; padding:5px 10px; border-radius:6px 6px 0 0;
      margin-bottom:0; display:flex; align-items:center; gap:6px;
    }
    .secao-corpo { border:1px solid #d0efe4; border-top:none; border-radius:0 0 6px 6px;
                   padding:10px 12px; background:#fff; }

    .campo-grid { display:grid; grid-template-columns:repeat(3, 1fr); gap:10px 14px; }
    .campo-grid-2 { display:grid; grid-template-columns:repeat(2, 1fr); gap:10px 14px; }
    .campo-grid-4 { display:grid; grid-template-columns:repeat(4, 1fr); gap:10px 14px; }
    .campo { min-width:0; }
    .campo-lbl { font-size:9.5px; text-transform:uppercase; letter-spacing:.4px; color:#888; font-weight:600; margin-bottom:1px; }
    .campo-val { font-size:12.5px; color:#1a1a1a; word-break:break-word; }
    .campo-val-dest { font-size:13.5px; font-weight:700; color:#1a7f5a; }

    /* ── Documentos ── */
    .doc-item { display:flex; align-items:center; gap:8px; padding:6px 0; border-bottom:1px solid #f0f0f0; }
    .doc-item:last-child { border-bottom:none; }
    .doc-icon { font-size:18px; flex-shrink:0; }
    .doc-nome { font-size:12px; flex:1; }
    .doc-tipo { font-size:10px; color:#888; }

    /* ── Status badge inline ── */
    .status-badge { display:inline-block; font-size:10px; font-weight:700; padding:3px 10px;
                    border-radius:20px; }

    /* ── Assinaturas ── */
    .sign-area { display:flex; justify-content:space-between; margin-top:40px; page-break-inside:avoid; }
    .sign-box  { width:44%; text-align:center; }
    .sign-line { border-top:1px solid #333; padding-top:6px; font-size:11px; }
    .sign-role { font-weight:700; font-size:11.5px; margin-bottom:2px; }
    .sign-sub  { font-size:10px; color:#888; }
    .sign-data { font-size:10px; color:#aaa; margin-top:4px; }

    .footer-doc { text-align:center; font-size:9px; color:#aaa; margin-top:20px; border-top:1px solid #eee; padding-top:10px; }

    /* ── Rodapé confidencial ── */
    .conf { background:#fff3cd; border-left:3px solid #ffc107; padding:5px 10px;
            border-radius:4px; font-size:10px; color:#856404; margin-bottom:14px; }

    @media print {
      body { background:#fff; }
      .no-print { display:none; }
      .page { box-shadow:none; margin:0; padding:12mm 14mm; }
      @page { size: A4 portrait; margin: 10mm; }
    }
  </style>
</head>
<body>

  <div class="no-print">
    <button class="btn-print" onclick="window.print()">🖨 Imprimir / Guardar PDF</button>
    <a class="btn-voltar" href="<?= $appUrl ?>/funcionarios/<?= $f['id'] ?>">← Voltar</a>
  </div>

  <div class="page">

    <!-- ── Cabeçalho ── -->
    <div class="header">
      <div>
        <?php if (!empty($config['logo_farmacia'])): ?>
        <div style="margin-bottom:5px">
          <img src="<?= $appUrl ?>/uploads/<?= htmlspecialchars($config['logo_farmacia']) ?>"
               alt="Logo" style="max-width:80px;max-height:44px;object-fit:contain">
        </div>
        <?php endif; ?>
        <div class="company-name"><?= htmlspecialchars($nomeEmpregador) ?></div>
        <div class="company-sub"><?= htmlspecialchars($config['endereco_farmacia'] ?: '') ?></div>
        <?php if (!empty($config['nuit_farmacia'])): ?>
        <div class="company-sub">NUIT: <?= htmlspecialchars($config['nuit_farmacia']) ?></div>
        <?php endif; ?>
      </div>
      <div class="doc-meta">
        <div style="font-size:14px;font-weight:700;color:#1a1a1a;text-transform:uppercase;letter-spacing:.5px">Boletim Individual</div>
        <div style="margin-top:4px">Nº <strong><?= $numeroBoletim ?></strong></div>
        <div>Emitido em: <strong><?= date('d/m/Y H:i') ?></strong></div>
      </div>
    </div>

    <div class="conf">🔒 Documento de uso interno — contém dados pessoais protegidos pela lei. Não divulgar sem autorização.</div>

    <!-- ── Identificação do funcionário ── -->
    <div class="func-id">
      <?php if (!empty($f['foto_url'])): ?>
      <img src="<?= $appUrl ?>/uploads/<?= htmlspecialchars($f['foto_url']) ?>"
           class="func-foto" alt="Foto">
      <?php else: ?>
      <div class="func-foto-ph">👤</div>
      <?php endif; ?>
      <div style="flex:1">
        <div class="func-nome"><?= htmlspecialchars($f['nome_completo']) ?></div>
        <div class="func-cargo"><?= htmlspecialchars($f['cargo_nome'] ?? '—') ?></div>
        <div class="func-badges">
          <span class="badge badge-num">Nº <?= htmlspecialchars($f['numero_funcionario']) ?></span>
          <span class="status-badge" style="background:<?= $statusCor ?>22;color:<?= $statusCor ?>;border:1px solid <?= $statusCor ?>44"><?= $statusLabel ?></span>
          <?php if (!empty($f['data_admissao'])): ?>
          <span class="badge badge-adm">Admissão: <?= date('d/m/Y', strtotime($f['data_admissao'])) ?></span>
          <?php endif; ?>
        </div>
        <div style="margin-top:8px">
          <span class="campo-lbl">Salário mensal</span>
          <div class="campo-val-dest">MZN <?= number_format((float)($f['salario'] ?? 0), 2, ',', '.') ?></div>
        </div>
      </div>
    </div>

    <!-- ── Dados Pessoais ── -->
    <div class="secao">
      <div class="secao-titulo">👤 Dados Pessoais</div>
      <div class="secao-corpo">
        <div class="campo-grid-4">
          <?= campo('Data de Nascimento', $f['data_nascimento'] ? date('d/m/Y', strtotime($f['data_nascimento'])) : null) ?>
          <?= campo('Sexo', $sexoLabels[$f['sexo']] ?? $f['sexo']) ?>
          <?= campo('Estado Civil', $estadoCivilLabels[$f['estado_civil'] ?? ''] ?? '—') ?>
          <?= campo('Nacionalidade', $f['nacionalidade']) ?>
          <?= campo('Naturalidade', $f['naturalidade']) ?>
          <?= campo('Nº BI', $f['bi_numero']) ?>
          <?= campo('Validade BI', $f['bi_validade'] ? date('d/m/Y', strtotime($f['bi_validade'])) : null) ?>
          <?= campo('NUIT', $f['nuit']) ?>
        </div>
        <?php if (!empty($f['nrps'])): ?>
        <div class="campo-grid-4" style="margin-top:8px">
          <?= campo('NRPS (Seg. Social)', $f['nrps']) ?>
        </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- ── Contactos ── -->
    <div class="secao">
      <div class="secao-titulo">📞 Contactos</div>
      <div class="secao-corpo">
        <div class="campo-grid">
          <?= campo('Telefone Principal', $f['telefone_principal']) ?>
          <?= campo('Telefone Alternativo', $f['telefone_alternativo']) ?>
          <?= campo('Email Pessoal', $f['email_pessoal']) ?>
        </div>
        <div style="margin-top:8px" class="campo-grid-2">
          <?= campo('Endereço', trim(($f['endereco'] ?? '') . ($f['bairro'] ? ', Bairro ' . $f['bairro'] : ''))) ?>
          <?= campo('Cidade / Província', ($f['cidade'] ?? '') . ', ' . ($f['provincia'] ?? '')) ?>
        </div>
        <?php if (!empty($f['emergencia_nome'])): ?>
        <div style="margin-top:8px; padding-top:8px; border-top:1px dashed #e0e0e0">
          <div class="campo-lbl" style="margin-bottom:6px">Contacto de Emergência</div>
          <div class="campo-grid">
            <?= campo('Nome', $f['emergencia_nome']) ?>
            <?= campo('Parentesco', $f['emergencia_parentesco']) ?>
            <?= campo('Telefone', $f['emergencia_telefone']) ?>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- ── Dados Profissionais ── -->
    <div class="secao">
      <div class="secao-titulo">💼 Dados Profissionais</div>
      <div class="secao-corpo">
        <div class="campo-grid-4">
          <?= campo('Cargo / Função', $f['cargo_nome']) ?>
          <?= campo('Tipo de Contrato', $contratoLabels[$f['tipo_contrato'] ?? ''] ?? ucwords(str_replace('_', ' ', $f['tipo_contrato'] ?? ''))) ?>
          <?= campo('Data de Admissão', $f['data_admissao'] ? date('d/m/Y', strtotime($f['data_admissao'])) : null) ?>
          <?= campo('Data de Saída', $f['data_saida'] ? date('d/m/Y', strtotime($f['data_saida'])) : null) ?>
        </div>
        <?php if ($f['nivel_escolaridade'] || $f['curso']): ?>
        <div style="margin-top:8px; padding-top:8px; border-top:1px dashed #e0e0e0">
          <div class="campo-lbl" style="margin-bottom:6px">Habilitações Académicas</div>
          <div class="campo-grid">
            <?= campo('Nível', $escolaridadeLabels[$f['nivel_escolaridade'] ?? ''] ?? $f['nivel_escolaridade']) ?>
            <?= campo('Curso', $f['curso']) ?>
            <?= campo('Instituição / Ano', trim(($f['instituicao'] ?? '') . ($f['ano_conclusao'] ? ' (' . $f['ano_conclusao'] . ')' : ''))) ?>
          </div>
        </div>
        <?php endif; ?>
        <?php if ($f['observacoes']): ?>
        <div style="margin-top:8px; padding-top:8px; border-top:1px dashed #e0e0e0">
          <?= campo('Observações', $f['observacoes']) ?>
        </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- ── Documentos ── -->
    <?php $totalDocs = (!empty($f['doc_identificacao_url']) ? 1 : 0) + (!empty($f['doc_complementar_url']) ? 1 : 0) + count($documentos); ?>
    <?php if ($totalDocs > 0): ?>
    <div class="secao">
      <div class="secao-titulo">📎 Documentos Arquivados (<?= $totalDocs ?>)</div>
      <div class="secao-corpo">
        <?php if (!empty($f['doc_identificacao_url'])): ?>
        <div class="doc-item">
          <span class="doc-icon">📄</span>
          <div class="doc-nome">
            <div class="fw-semibold"><?= htmlspecialchars($f['doc_identificacao_nome'] ?? 'Documento de Identificação') ?></div>
            <div class="doc-tipo">Bilhete de Identidade / Identificação</div>
          </div>
          <span style="font-size:10px;color:#1a7f5a;font-weight:600">✓ Arquivado</span>
        </div>
        <?php endif; ?>
        <?php if (!empty($f['doc_complementar_url'])): ?>
        <div class="doc-item">
          <span class="doc-icon">📄</span>
          <div class="doc-nome">
            <div class="fw-semibold"><?= htmlspecialchars($f['doc_complementar_nome'] ?? 'Documento Complementar') ?></div>
            <div class="doc-tipo">Documento Complementar</div>
          </div>
          <span style="font-size:10px;color:#1a7f5a;font-weight:600">✓ Arquivado</span>
        </div>
        <?php endif; ?>
        <?php foreach ($documentos as $doc): ?>
        <div class="doc-item">
          <span class="doc-icon">📑</span>
          <div class="doc-nome">
            <div class="fw-semibold"><?= htmlspecialchars($doc['titulo']) ?></div>
            <div class="doc-tipo"><?= ucfirst($doc['tipo']) ?> · <?= htmlspecialchars($doc['ficheiro_nome']) ?></div>
          </div>
          <span style="font-size:10px;color:#1a7f5a;font-weight:600">✓ Arquivado</span>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <!-- ── Campos em Branco para arquivo físico ── -->
    <div class="secao" style="border:1px dashed #ccc;border-radius:8px;padding:10px 12px;background:#fafafa">
      <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.4px;color:#888;margin-bottom:8px">
        Para preenchimento pelo departamento de RH
      </div>
      <div class="campo-grid" style="gap:16px">
        <div>
          <div class="campo-lbl">Verificado por</div>
          <div style="border-bottom:1px solid #999;height:22px;margin-top:4px"></div>
        </div>
        <div>
          <div class="campo-lbl">Cargo</div>
          <div style="border-bottom:1px solid #999;height:22px;margin-top:4px"></div>
        </div>
        <div>
          <div class="campo-lbl">Data de verificação</div>
          <div style="border-bottom:1px solid #999;height:22px;margin-top:4px"></div>
        </div>
      </div>
    </div>

    <!-- ── Assinaturas ── -->
    <div class="sign-area">
      <div class="sign-box">
        <div class="sign-line">
          <div class="sign-role">O(A) Funcionário(a)</div>
          <div class="sign-sub"><?= htmlspecialchars($f['nome_completo']) ?></div>
          <div class="sign-data">Data: ______ / ______ / __________</div>
        </div>
      </div>
      <div class="sign-box">
        <div class="sign-line">
          <div class="sign-role">Director(a) / RH</div>
          <div class="sign-sub"><?= htmlspecialchars($nomeEmpregador) ?></div>
          <div class="sign-data">Data: ______ / ______ / __________</div>
        </div>
      </div>
    </div>

    <div class="footer-doc">
      <?= htmlspecialchars($nomeEmpregador) ?> · Boletim Nº <?= $numeroBoletim ?> · Gerado em <?= date('d/m/Y H:i') ?>
      · Documento confidencial — uso interno
    </div>

  </div>
</body>
</html>
