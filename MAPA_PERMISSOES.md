# Mapa de Permissões - KewanFarma

## 🔐 Resumo das Restrições Implementadas

```
┌─────────────────────────────────────────────────────────────────────┐
│                    MATRIZ DE CONTROLE DE ACESSO                     │
└─────────────────────────────────────────────────────────────────────┘

                    │ Admin │ Farm. │ Caixa │ Técnico │
────────────────────┼───────┼───────┼───────┼─────────┤
Dashboard           │  ✅   │  ❌   │  ❌   │   ❌    │
Funcionários        │  ✅   │  ❌   │  ❌   │   ❌    │
Produtos            │  ✅   │  ❌   │  ❌   │   ❌    │
Clientes            │  ✅   │  ❌   │  ❌   │   ❌    │
Compras             │  ✅   │  ❌   │  ❌   │   ❌    │
Fornecedores        │  ✅   │  ❌   │  ❌   │   ❌    │
Relatórios          │  ✅   │  ❌   │  ❌   │   ❌    │
Configurações       │  ✅   │  ❌   │  ❌   │   ❌    │
────────────────────┼───────┼───────┼───────┼─────────┤
Vendas              │  ✅   │  ✅   │  ✅   │   ✅    │
Caixa               │  ✅   │  ✅   │  ✅   │   ✅    │
Backup              │  ✅   │  ✅   │  ✅   │   ✅    │
────────────────────┴───────┴───────┴───────┴─────────┘
```

---

## 📊 Detalhamento por Funcionalidade

### 🏠 DASHBOARD
- **Acesso:** Apenas ADMIN
- **Bloqueado para:** Farmacêutico, Caixa, Técnico
- **Erro:** HTTP 403 - Acesso Negado

### 👥 FUNCIONÁRIOS
- **Acesso:** Apenas ADMIN
- **Rutas bloqueadas:**
  - GET `/funcionarios`
  - POST `/funcionarios/novo`
  - GET `/funcionarios/{id}`
  - GET `/funcionarios/{id}/editar`
  - POST `/funcionarios/{id}/editar`
  - POST `/funcionarios/{id}/credenciais`

### 📦 PRODUTOS
- **Acesso:** Apenas ADMIN
- **Rotas bloqueadas:**
  - GET `/produtos`
  - POST `/produtos/novo`
  - GET `/produtos/{id}`
  - GET `/produtos/{id}/editar`
  - POST `/produtos/{id}/editar`
  - POST `/produtos/{id}/lote`

### 🧑 CLIENTES
- **Acesso:** Apenas ADMIN
- **Rotas bloqueadas:**
  - GET `/clientes`
  - POST `/clientes/novo`
  - GET `/clientes/{id}`
  - GET `/clientes/{id}/editar`
  - POST `/clientes/{id}/editar`

### 🛒 VENDAS
- **Acesso:** ADMIN, Farmacêutico, Caixa, Técnico
- **Rotas liberadas:**
  - GET `/vendas/nova`
  - POST `/vendas/nova`
  - GET `/vendas`
  - GET `/vendas/{id}`
  - GET `/vendas/{id}/talao`
  - POST `/vendas/{id}/cancelar`
  - POST `/vendas/nova/carrinho`

### 🏪 CAIXA
- **Acesso:** ADMIN, Farmacêutico, Caixa, Técnico
- **Rotas liberadas:**
  - GET `/caixa`
  - POST `/caixa/abrir`
  - POST `/caixa/fechar`
  - POST `/caixa/movimento`
  - GET `/caixa/{id}`

### 🚚 COMPRAS
- **Acesso:** Apenas ADMIN
- **Rotas bloqueadas:**
  - GET `/compras`
  - POST `/compras/nova`
  - GET `/compras/{id}`
  - GET `/compras/{id}/pdf`
  - POST `/compras/{id}/receber`
  - POST `/compras/{id}/cancelar`

### 🏢 FORNECEDORES
- **Acesso:** Apenas ADMIN
- **Rotas bloqueadas:**
  - GET `/fornecedores`
  - POST `/fornecedores/novo`
  - GET `/fornecedores/{id}`
  - GET `/fornecedores/{id}/editar`
  - POST `/fornecedores/{id}/editar`
  - POST `/fornecedores/{id}/toggle`

### 📈 RELATÓRIOS
- **Acesso:** Apenas ADMIN
- **Rotas bloqueadas:**
  - GET `/relatorios`
  - GET `/relatorios/vendas`
  - GET `/relatorios/vendas/pdf`
  - GET `/relatorios/stock`
  - GET `/relatorios/stock/pdf`
  - GET `/relatorios/lotes-a-vencer`
  - GET `/relatorios/funcionarios`
  - GET `/relatorios/lotes-a-vencer/pdf`
  - GET `/relatorios/funcionarios/pdf`

### ⚙️ CONFIGURAÇÕES
- **Acesso:** Apenas ADMIN (configurações gerais)
- **Rotas bloqueadas:**
  - GET `/configuracoes`
  - POST `/configuracoes`

### ☁️ BACKUP (Manual)
- **Acesso:** ADMIN, Farmacêutico, Caixa, Técnico
- **Rotas liberadas:**
  - POST `/configuracoes/fazer-backup`
  - POST `/configuracoes/deletar-backup`
  - GET `/configuracoes/download-backup`

### 🔍 APIs AJAX
- **Pesquisar Produtos:** Acesso a `vendas`
- **Pesquisar Clientes:** Acesso a `vendas`
- **Alertas de Estoque:** Acesso a `produtos` (admin)
- **Resumo Dashboard:** Qualquer usuário autenticado

---

## 🎯 Menu de Navegação por Perfil

### ✅ ADMIN
```
📊 Dashboard
  ↪ Nova Venda
  ↪ Vendas
  ↪ Caixa
  ↪ Clientes
  ↪ Produtos
  ↪ Compras
  ↪ Fornecedores
  ↪ Funcionários
  ↪ Relatórios
  ↪ Configurações
  ↪ Backup
```

### ❌ FARMACÊUTICO / CAIXA / TÉCNICO
```
  ↪ Nova Venda
  ↪ Vendas
  ↪ Caixa
  ↪ Backup
```

---

## 🛡️ Camadas de Segurança

### Nível 1: Frontend (View)
- Menu dinâmico usa `AuthMiddleware::temAcesso()`
- Apenas mostra links que o usuário pode acessar
- Previne cliques acidentais em opções não disponíveis

### Nível 2: Rota (Router)
- Middleware valida funcionalidade na rota
- `['funcionalidade' => 'vendas']` é verificado ANTES do controller
- Bloqueia acessos diretos por URL

### Nível 3: Backend (Controller)
- Controladores podem fazer validação adicional
- `AuthMiddleware::requireFuncionalidade()` pode ser usado
- Camada extra de segurança

---

## 📝 Arquivos de Configuração

### 1. Permissões Definidas Em:
📁 `app/Middleware/AuthMiddleware.php` (linhas 12-37)

```php
private static array $permissoes = [
    'admin' => ['dashboard', 'funcionarios', ...],
    'farmaceutico' => ['caixa', 'vendas', 'backup'],
    'caixa' => ['caixa', 'vendas', 'backup'],
    'tecnico' => ['caixa', 'vendas', 'backup']
];
```

### 2. Rotas Protegidas Em:
📁 `routes/web.php`

Todas as rotas agora incluem parâmetro `['funcionalidade' => '...']`

### 3. Menu Dinâmico Em:
📁 `app/Views/layouts/base.php` (linhas 362-444)

```php
<?php if (AuthMiddleware::temAcesso('dashboard')): ?>
    <!-- mostra link -->
<?php endif; ?>
```

---

## ⚡ Início Rápido

### Para Testar:
1. Faça login como **admin** → Vê tudo
2. Faça login como **caixa** → Vê apenas Vendas, Caixa, Backup
3. Tente acessar `/produtos` como caixa → Erro 403

### Para Adicionar Nova Funcionalidade:
1. Edite `$permissoes` em `AuthMiddleware.php`
2. Adicione `['funcionalidade' => 'nome']` na rota
3. Use `if (AuthMiddleware::temAcesso('nome'))` na view

### Para Mudar Permissões:
- Edite `$permissoes` em `AuthMiddleware.php`
- Não é necessário alterar rotas ou views

---

## 📚 Documentação Relacionada

- 📖 [SISTEMA_PERMISSOES.md](SISTEMA_PERMISSOES.md) - Guia técnico completo
- 🧪 [GUIA_TESTES_CONTROLE_ACESSO.md](GUIA_TESTES_CONTROLE_ACESSO.md) - Como testar
- 📋 [IMPLEMENTACAO_CONTROLE_ACESSO.md](IMPLEMENTACAO_CONTROLE_ACESSO.md) - Resumo das mudanças

---

## ✅ Status

**Implementação:** ✅ CONCLUÍDA  
**Testes:** ⏳ Aguardando validação do usuário  
**Documentação:** ✅ COMPLETA  
**Código:** ✅ SEM ERROS

---

**Última atualização:** 2025-06-04  
**Desenvolvedor:** Sistema de IA - GitHub Copilot
