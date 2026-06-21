<?php
// app/Views/funcionarios/contrato_pdf.php
// Contrato Individual de Trabalho — gerado com base na Lei n.º 23/2007,
// de 1 de Agosto (Lei do Trabalho da República de Moçambique),
// aplicável ao sector privado.
//
// NOTA: este documento é gerado automaticamente como modelo de apoio.
// Recomenda-se revisão por um técnico de recursos humanos / jurista antes
// de o considerar definitivo, sobretudo quanto a cláusulas específicas do
// caso concreto (ex.: termo do contrato, cláusulas de não concorrência).

$tipoLabels = [
    'efectivo'           => 'Contrato de Trabalho por Tempo Indeterminado',
    'temporario'         => 'Contrato de Trabalho a Termo',
    'estagio'            => 'Contrato de Estágio Profissional',
    'prestacao_servicos' => 'Contrato de Prestação de Serviços',
];
$tipoTitulo = $tipoLabels[$f['tipo_contrato']] ?? 'Contrato Individual de Trabalho';

// Período experimental — Art. 50.º da Lei n.º 23/2007: regra geral 90 dias;
// pessoal de direcção e quadros técnicos superiores, 180 dias.
$cargoLower   = mb_strtolower($f['cargo_nome'] ?? '');
$ehDirecaoOuTecnicoSuperior = (
    str_contains($cargoLower, 'director') ||
    str_contains($cargoLower, 'diretor')  ||
    str_contains($cargoLower, 'gerente')  ||
    str_contains($cargoLower, 'farmacêutico')
);
$diasExperiencia = $ehDirecaoOuTecnicoSuperior ? 180 : 90;

$generoTrabalhador = ($f['sexo'] === 'F') ? 'a' : 'o';
$sexoLabel          = ($f['sexo'] === 'F') ? 'Feminino' : (($f['sexo'] === 'M') ? 'Masculino' : 'Outro');

$estadoCivilLabels = [
    'solteiro'        => 'Solteiro(a)',
    'casado'          => 'Casado(a)',
    'divorciado'      => 'Divorciado(a)',
    'viuvo'           => 'Viúvo(a)',
    'uniao_de_facto'  => 'União de Facto',
];

$nomeEmpregador = $config['nome_farmacia'] ?? ($_ENV['APP_NAME'] ?? 'KewanFarma');
$enderecoEmpregador = $config['endereco_farmacia'] ?: '—';
$nuitEmpregador     = $config['nuit_farmacia'] ?: '—';

$numeroContrato = 'CT-' . date('Y') . '-' . str_pad((string)$f['id'], 4, '0', STR_PAD_LEFT);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Contrato de Trabalho — <?= htmlspecialchars($f['nome_completo']) ?></title>
  <style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family:'Segoe UI', Arial, sans-serif; font-size:13px; color:#1a1a1a; background:#f5f5f5; line-height:1.55; }

    .no-print { position:fixed; top:20px; right:20px; display:flex; gap:10px; z-index:999; }
    .btn-print, .btn-voltar {
      padding:10px 20px; border-radius:8px; font-size:14px; font-weight:600;
      cursor:pointer; display:flex; align-items:center; gap:8px;
      box-shadow:0 2px 8px rgba(0,0,0,.15); text-decoration:none;
    }
    .btn-print { background:#1a7f5a; color:#fff; border:none; }
    .btn-print:hover { background:#156347; }
    .btn-voltar { background:#fff; color:#333; border:1px solid #ddd; }

    .page { width:210mm; min-height:297mm; background:#fff; margin:30px auto; padding:20mm; box-shadow:0 4px 24px rgba(0,0,0,.12); }

    .header { display:flex; justify-content:space-between; align-items:flex-start; padding-bottom:14px; border-bottom:3px solid #1a7f5a; margin-bottom:18px; }
    .company-name { font-size:18px; font-weight:700; color:#1a7f5a; }
    .company-sub { font-size:10.5px; color:#666; margin-top:2px; }
    .doc-meta { text-align:right; font-size:10.5px; color:#666; }
    .doc-meta strong { color:#1a1a1a; }

    h1.titulo { text-align:center; font-size:16px; text-transform:uppercase; letter-spacing:.5px; margin:18px 0 6px; color:#1a1a1a; }
    .subtitulo { text-align:center; font-size:11px; color:#666; margin-bottom:22px; }

    .preambulo { text-align:justify; font-size:12.5px; margin-bottom:18px; }
    .preambulo strong { color:#1a7f5a; }

    .partes { background:#f8f9fa; border-radius:8px; padding:12px 16px; margin-bottom:18px; font-size:12px; }
    .partes .parte { margin-bottom:8px; }
    .partes .parte:last-child { margin-bottom:0; }
    .partes .rotulo { font-weight:700; color:#1a7f5a; text-transform:uppercase; font-size:10px; letter-spacing:.4px; }

    .clausula { margin-bottom:14px; page-break-inside:avoid; }
    .clausula h2 { font-size:12.5px; color:#1a7f5a; margin-bottom:4px; text-transform:uppercase; letter-spacing:.2px; }
    .clausula p { text-align:justify; font-size:12px; margin-bottom:4px; }
    .clausula ol, .clausula ul { margin-left:18px; font-size:12px; text-align:justify; }
    .clausula li { margin-bottom:3px; }

    .nota-legal { background:#fff3cd; border-left:4px solid #ffc107; padding:8px 12px; border-radius:6px; font-size:10.5px; margin:14px 0; }

    .sign-area { display:flex; justify-content:space-between; margin-top:50px; page-break-inside:avoid; }
    .sign-box { width:45%; text-align:center; }
    .sign-line { border-top:1px solid #333; padding-top:6px; font-size:11px; }
    .sign-role { font-weight:700; font-size:11.5px; margin-bottom:2px; }
    .sign-name { font-size:10px; color:#888; min-height:14px; }

    .testemunhas { display:flex; justify-content:space-between; margin-top:46px; page-break-inside:avoid; }
    .test-box { width:45%; text-align:center; font-size:10.5px; }
    .test-line { border-top:1px solid #999; padding-top:5px; color:#666; }

    .footer-doc { text-align:center; font-size:9px; color:#aaa; margin-top:24px; }

    @media print {
      body { background:#fff; }
      .no-print { display:none; }
      .page { box-shadow:none; margin:0; }
    }
  </style>
</head>
<body>

  <div class="no-print">
    <button class="btn-print" onclick="window.print()">🖨 Imprimir / Guardar PDF</button>
    <a class="btn-voltar" href="<?= $appUrl ?>/funcionarios/<?= $f['id'] ?>">← Voltar</a>
  </div>

  <div class="page">

    <div class="header">
      <div>
        <?php if (!empty($config['logo_farmacia'])): ?>
        <div style="margin-bottom:6px">
          <img src="<?= $appUrl ?>/uploads/<?= htmlspecialchars($config['logo_farmacia']) ?>"
               alt="Logo" style="max-width:90px; max-height:50px; object-fit:contain;">
        </div>
        <?php endif; ?>
        <div class="company-name"><?= htmlspecialchars($nomeEmpregador) ?></div>
        <div class="company-sub"><?= htmlspecialchars($enderecoEmpregador) ?></div>
        <div class="company-sub">NUIT: <?= htmlspecialchars($nuitEmpregador) ?></div>
      </div>
      <div class="doc-meta">
        <div>Nº do Contrato: <strong><?= $numeroContrato ?></strong></div>
        <div>Data de emissão: <strong><?= date('d/m/Y') ?></strong></div>
      </div>
    </div>

    <h1 class="titulo"><?= htmlspecialchars($tipoTitulo) ?></h1>
    <div class="subtitulo">
      Celebrado nos termos da Lei n.º 23/2007, de 1 de Agosto (Lei do Trabalho),
      aplicável às relações de trabalho no sector privado da República de Moçambique
    </div>

    <div class="partes">
      <div class="parte">
        <div class="rotulo">Primeiro Outorgante — Entidade Empregadora</div>
        <?= htmlspecialchars($nomeEmpregador) ?>, com sede em <?= htmlspecialchars($enderecoEmpregador) ?>,
        titular do NUIT n.º <?= htmlspecialchars($nuitEmpregador) ?>,
        doravante designada por <strong>"Empregador"</strong>.
      </div>
      <div class="parte">
        <div class="rotulo">Segundo Outorgante — Trabalhador<?= $generoTrabalhador === 'a' ? 'a' : '' ?></div>
        <?= htmlspecialchars($f['nome_completo']) ?>,
        de nacionalidade <?= htmlspecialchars($f['nacionalidade']) ?>,
        natural de <?= htmlspecialchars($f['naturalidade'] ?: '—') ?>,
        nascid<?= $generoTrabalhador ?> em <?= date('d/m/Y', strtotime($f['data_nascimento'])) ?>,
        estado civil: <?= htmlspecialchars($estadoCivilLabels[$f['estado_civil']] ?? '—') ?>,
        sexo: <?= $sexoLabel ?>,
        portador(a) do Bilhete de Identidade n.º <?= htmlspecialchars($f['bi_numero']) ?>
        <?php if (!empty($f['nuit'])): ?>, NUIT n.º <?= htmlspecialchars($f['nuit']) ?><?php endif; ?>,
        residente em <?= htmlspecialchars($f['endereco']) ?><?= $f['bairro'] ? ', Bairro ' . htmlspecialchars($f['bairro']) : '' ?>,
        <?= htmlspecialchars($f['cidade']) ?>, <?= htmlspecialchars($f['provincia']) ?>,
        doravante designad<?= $generoTrabalhador ?> por <strong>"Trabalhador<?= $generoTrabalhador ?>"</strong>.
      </div>
    </div>

    <p class="preambulo">
      Entre o <strong>Empregador</strong> e o(a) <strong>Trabalhador(a)</strong> acima identificados, é celebrado
      o presente contrato individual de trabalho, que se rege pelas cláusulas seguintes e, em tudo o que nelas
      não estiver previsto, pelo disposto na Lei n.º 23/2007, de 1 de Agosto, e demais legislação complementar
      aplicável ao trabalho no sector privado em Moçambique.
    </p>

    <div class="clausula">
      <h2>Cláusula 1.ª — Objecto e Categoria Profissional</h2>
      <p>
        O Trabalhador é admitido ao serviço do Empregador para exercer as funções correspondentes à categoria
        profissional de <strong><?= htmlspecialchars($f['cargo_nome']) ?></strong>, obrigando-se a executar
        as tarefas inerentes a tal categoria, bem como outras funções afins ou funcionalmente ligadas que lhe
        sejam confiadas pela hierarquia da empresa, nos termos do artigo 31.º da Lei n.º 23/2007.
      </p>
    </div>

    <div class="clausula">
      <h2>Cláusula 2.ª — Local de Trabalho</h2>
      <p>
        O Trabalhador exercerá a sua actividade nas instalações do Empregador, sitas em
        <?= htmlspecialchars($enderecoEmpregador) ?>, podendo ser deslocado temporariamente para outro local,
        dentro dos limites legalmente previstos, sempre que as necessidades de serviço o justifiquem.
      </p>
    </div>

    <div class="clausula">
      <h2>Cláusula 3.ª — Duração do Contrato</h2>
      <?php if ($f['tipo_contrato'] === 'efectivo'): ?>
      <p>
        O presente contrato é celebrado por <strong>tempo indeterminado</strong>, com início em
        <strong><?= date('d/m/Y', strtotime($f['data_admissao'])) ?></strong>, nos termos do artigo 41.º
        da Lei n.º 23/2007.
      </p>
      <?php elseif ($f['tipo_contrato'] === 'temporario'): ?>
      <p>
        O presente contrato é celebrado <strong>a termo</strong>, nos termos e para os efeitos do artigo 42.º
        e seguintes da Lei n.º 23/2007, com início em
        <strong><?= date('d/m/Y', strtotime($f['data_admissao'])) ?></strong>, em razão de necessidade
        temporária e transitória de mão-de-obra, devendo o seu termo e eventuais renovações ser
        expressamente acordados por escrito entre as partes, dentro dos limites legalmente estabelecidos.
      </p>
      <?php elseif ($f['tipo_contrato'] === 'estagio'): ?>
      <p>
        O presente contrato reveste a natureza de <strong>estágio profissional</strong>, com início em
        <strong><?= date('d/m/Y', strtotime($f['data_admissao'])) ?></strong>, tendo por finalidade a
        aquisição de experiência prática e o desenvolvimento de competências profissionais do Trabalhador,
        nos termos da legislação aplicável ao estágio profissional em Moçambique.
      </p>
      <?php else: ?>
      <p>
        O presente contrato tem por objecto a prestação de serviços, com início em
        <strong><?= date('d/m/Y', strtotime($f['data_admissao'])) ?></strong>, não conferindo, por si só,
        a qualidade de trabalhador subordinado nos termos da Lei n.º 23/2007, regendo-se as suas condições
        específicas pelo acordo de prestação de serviços celebrado entre as partes e pela legislação civil
        aplicável.
      </p>
      <?php endif; ?>
    </div>

    <?php if (in_array($f['tipo_contrato'], ['efectivo', 'temporario'])): ?>
    <div class="clausula">
      <h2>Cláusula 4.ª — Período Experimental</h2>
      <p>
        Nos termos do artigo 50.º da Lei n.º 23/2007, as partes acordam num período experimental de
        <strong><?= $diasExperiencia ?> dias</strong>, contados a partir da data de início de funções,
        durante o qual qualquer das partes pode fazer cessar o contrato sem necessidade de aviso prévio
        nem invocação de justa causa, não havendo lugar a qualquer indemnização, salvo o disposto na lei.
      </p>
    </div>
    <?php endif; ?>

    <div class="clausula">
      <h2>Cláusula 5.ª — Período Normal de Trabalho</h2>
      <p>
        O período normal de trabalho é de <strong>8 (oito) horas diárias e 48 (quarenta e oito) horas
        semanais</strong>, nos termos do artigo 88.º da Lei n.º 23/2007, podendo o horário concreto de
        entrada, saída e intervalos ser fixado pelo Empregador em regulamento interno, respeitando os
        limites legais e o direito ao descanso semanal e a feriados.
      </p>
    </div>

    <div class="clausula">
      <h2>Cláusula 6.ª — Retribuição</h2>
      <p>
        Pelo trabalho prestado, o Trabalhador tem direito a uma retribuição mensal ilíquida de
        <strong><?= number_format($f['salario'], 2, ',', '.') ?> MZN (Meticais)</strong>, a pagar até ao
        último dia útil de cada mês, mediante transferência bancária ou outro meio acordado entre as
        partes, sujeita às deduções legalmente obrigatórias, nomeadamente Imposto sobre o Rendimento de
        Pessoas Singulares (IRPS) e contribuições para a Segurança Social.
      </p>
    </div>

    <div class="clausula">
      <h2>Cláusula 7.ª — Segurança Social</h2>
      <p>
        O Empregador obriga-se a inscrever o Trabalhador no Instituto Nacional de Segurança Social (INSS)
        e a processar as respectivas contribuições, nos termos e percentagens fixados pela legislação de
        segurança social em vigor.
      </p>
    </div>

    <div class="clausula">
      <h2>Cláusula 8.ª — Férias, Faltas e Licenças</h2>
      <p>
        O Trabalhador goza do direito a férias remuneradas, bem como ao regime de faltas e licenças,
        nos termos previstos nos artigos 94.º e seguintes da Lei n.º 23/2007 e demais legislação
        complementar aplicável, em função da sua antiguidade ao serviço do Empregador.
      </p>
    </div>

    <div class="clausula">
      <h2>Cláusula 9.ª — Deveres do Trabalhador</h2>
      <ol type="a">
        <li>Comparecer ao serviço com assiduidade e pontualidade;</li>
        <li>Realizar o trabalho com zelo, diligência e correcção;</li>
        <li>Cumprir as ordens e instruções do Empregador relacionadas com a execução do trabalho;</li>
        <li>Guardar sigilo profissional sobre informações de clientes, prescrições médicas e dados
            confidenciais da farmácia, atendendo à natureza sensível da actividade farmacêutica;</li>
        <li>Zelar pela conservação dos bens, equipamentos e instalações que lhe forem confiados;</li>
        <li>Observar as normas internas, de ética profissional e de boas práticas farmacêuticas aplicáveis
            à função.</li>
      </ol>
    </div>

    <div class="clausula">
      <h2>Cláusula 10.ª — Deveres do Empregador</h2>
      <ol type="a">
        <li>Pagar pontualmente a retribuição devida;</li>
        <li>Proporcionar boas condições de higiene, saúde e segurança no trabalho;</li>
        <li>Tratar o Trabalhador com respeito e urbanidade, não o discriminando por motivo de sexo,
            raça, religião, convicção política ou estado civil;</li>
        <li>Proporcionar formação profissional adequada ao desenvolvimento das funções do Trabalhador,
            sempre que aplicável;</li>
        <li>Cumprir as demais obrigações decorrentes da Lei n.º 23/2007 e da legislação complementar
            aplicável.</li>
      </ol>
    </div>

    <div class="clausula">
      <h2>Cláusula 11.ª — Cessação do Contrato</h2>
      <p>
        O presente contrato cessa nos casos e nos termos previstos na Lei n.º 23/2007, nomeadamente por
        caducidade, revogação por acordo das partes, denúncia por iniciativa do Trabalhador (com observância
        do aviso prévio legalmente exigido) ou rescisão com justa causa por qualquer das partes, nos termos
        e com os fundamentos previstos na lei.
      </p>
    </div>

    <div class="clausula">
      <h2>Cláusula 12.ª — Confidencialidade e Protecção de Dados</h2>
      <p>
        O Trabalhador compromete-se a manter sigilo absoluto sobre toda a informação confidencial a que
        tenha acesso no exercício das suas funções, incluindo dados pessoais de clientes e informação
        comercial do Empregador, mesmo após a cessação do presente contrato.
      </p>
    </div>

    <div class="clausula">
      <h2>Cláusula 13.ª — Legislação Aplicável e Foro</h2>
      <p>
        O presente contrato rege-se pela Lei n.º 23/2007, de 1 de Agosto, e demais legislação complementar
        da República de Moçambique aplicável às relações de trabalho no sector privado. Para dirimir
        quaisquer litígios emergentes do presente contrato, é competente o tribunal do trabalho da área
        da sede do Empregador.
      </p>
    </div>

    <div class="clausula">
      <h2>Cláusula 14.ª — Disposições Finais</h2>
      <p>
        O presente contrato é elaborado em dois exemplares de igual valor, destinando-se um a cada uma
        das partes, que o assinam por o terem lido, compreendido e aceite livremente.
      </p>
    </div>

    <div class="nota-legal">
      <strong>Nota:</strong> este contrato é gerado automaticamente como modelo de apoio com base na
      Lei n.º 23/2007, de 1 de Agosto (Lei do Trabalho). Recomenda-se a sua revisão por um(a)
      técnico(a) de recursos humanos ou jurista antes da assinatura definitiva, em particular quanto a
      cláusulas específicas do caso concreto e eventuais actualizações legislativas posteriores.
    </div>

    <p style="margin-top:18px; font-size:12px;">
      Feito em <?= htmlspecialchars($f['cidade'] ?: $enderecoEmpregador) ?>, aos <?= date('d/m/Y') ?>.
    </p>

    <div class="sign-area">
      <div class="sign-box">
        <div class="sign-line">
          <div class="sign-role">Pelo Empregador</div>
          <div class="sign-name"><?= htmlspecialchars($nomeEmpregador) ?></div>
        </div>
      </div>
      <div class="sign-box">
        <div class="sign-line">
          <div class="sign-role">O(A) Trabalhador(a)</div>
          <div class="sign-name"><?= htmlspecialchars($f['nome_completo']) ?></div>
        </div>
      </div>
    </div>

    <div class="testemunhas">
      <div class="test-box">
        <div class="test-line">Testemunha 1 — Nome e assinatura</div>
      </div>
      <div class="test-box">
        <div class="test-line">Testemunha 2 — Nome e assinatura</div>
      </div>
    </div>

    <div class="footer-doc">
      Documento gerado automaticamente pelo sistema <?= htmlspecialchars($nomeEmpregador) ?> em <?= date('d/m/Y H:i') ?>
      · Nº do Contrato: <?= $numeroContrato ?>
    </div>

  </div>

</body>
</html>
