# KewanFarma — Sistema de Gestão de Farmácia

## Requisitos
- PHP >= 8.1
- MySQL >= 8.0
- Composer
- Apache com mod_rewrite activado

## Instalação

```bash
# 1. Instalar dependências
composer install

# 2. Copiar e configurar o ambiente
cp .env.example .env
# Editar .env com as credenciais do banco de dados

# 3. Criar o banco de dados
mysql -u root -p < database/kewanfarma_database.sql
mysql -u root -p < database/kewanfarma_funcionarios.sql

# 4. Permissões de escrita para storage
chmod -R 775 storage/

# 5. Apontar o Apache para a pasta public/
```

## Estrutura
- `app/`      — MVC: Controllers, Models, Views, Services, Middleware
- `core/`     — Motor do framework (Router, Database, View)
- `config/`   — Configurações da aplicação
- `routes/`   — Definição de rotas web e API
- `public/`   — Único ponto de acesso público
- `storage/`  — Uploads, logs, cache (fora do browser)
- `database/` — Scripts SQL e migrações

## Módulos
| Módulo | Controlador |
|---|---|
| Autenticação | AuthController |
| Dashboard | DashboardController |
| Funcionários | FuncionarioController |
| Produtos | ProdutoController |
| Clientes | ClienteController |
| Vendas | VendaController |
| Compras | CompraController |
| Caixa | CaixaController |
| Relatórios | RelatorioController |
| Configurações | ConfiguracaoController |
