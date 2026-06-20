Esta pasta armazena os ficheiros enviados pela aplicação.

Estrutura:
  funcionarios/fotos/   → fotos de perfil dos funcionários
  funcionarios/docs/    → documentos de identificação dos funcionários
  produtos/             → imagens dos produtos
  logos/                → logotipo da farmácia

IMPORTANTE: Esta pasta deve ter permissões de escrita pelo servidor web.
Em Linux/Apache: chmod -R 775 public/uploads && chown -R www-data:www-data public/uploads
