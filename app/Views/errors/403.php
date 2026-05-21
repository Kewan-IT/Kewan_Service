<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>403 — KewanFarma</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100 bg-light">
  <div class="text-center p-4">
    <i class="bi bi-shield-lock text-danger" style="font-size:4rem"></i>
    <h1 class="mt-3 fw-bold">403 — Acesso Negado</h1>
    <p class="text-muted">Não tem permissão para aceder a esta página.</p>
    <a href="<?= $_ENV['APP_URL'] ?? '' ?>/dashboard" class="btn btn-success mt-2">
      <i class="bi bi-arrow-left me-1"></i>Voltar ao Dashboard
    </a>
  </div>
</body>
</html>
