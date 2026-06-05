# Sistema de Controle de Acesso - KewanFarma

## Visão Geral

O sistema de permissões do KewanFarma controla o acesso de usuários a diferentes funcionalidades com base no seu perfil/cargo.

## Perfis de Usuário

O sistema possui 4 perfis de usuário:

| Perfil | Descrição | Acesso |
|--------|-----------|--------|
| `admin` | Administrador do Sistema | Acesso Total |
| `farmaceutico` | Farmacêutico | Caixa, Venda, Backup |
| `caixa` | Operador de Caixa | Caixa, Venda, Backup |
| `tecnico` | Técnico de Farmácia | Caixa, Venda, Backup |

## Funcionalidades do Sistema

As funcionalidades disponíveis no sistema são:

| Funcionalidade | Descrição | Admin | Farmacêutico | Caixa | Técnico |
|---|---|---|---|---|---|
| `dashboard` | Dashboard Principal | ✅ | ❌ | ❌ | ❌ |
| `funcionarios` | Gestão de Funcionários | ✅ | ❌ | ❌ | ❌ |
| `produtos` | Gestão de Produtos | ✅ | ❌ | ❌ | ❌ |
| `clientes` | Gestão de Clientes | ✅ | ❌ | ❌ | ❌ |
| `vendas` | Registro e Consulta de Vendas | ✅ | ✅ | ✅ | ✅ |
| `compras` | Gestão de Compras | ✅ | ❌ | ❌ | ❌ |
| `fornecedores` | Gestão de Fornecedores | ✅ | ❌ | ❌ | ❌ |
| `caixa` | Movimento do Caixa | ✅ | ✅ | ✅ | ✅ |
| `relatorios` | Relatórios do Sistema | ✅ | ❌ | ❌ | ❌ |
| `configuracoes` | Configurações do Sistema | ✅ | ❌ | ❌ | ❌ |
| `backup` | Backup Manual | ✅ | ✅ | ✅ | ✅ |

## Implementação Técnica

### 1. Middleware de Autenticação e Autorização

O arquivo [app/Middleware/AuthMiddleware.php](app/Middleware/AuthMiddleware.php) contém os métodos para controlar acesso:

#### Métodos Disponíveis

- **`check()`**: Verifica se o usuário está autenticado
  ```php
  AuthMiddleware::check(); // Redireciona para login se não autenticado
  ```

- **`requirePerfil(...$perfis)`**: Verifica se o usuário tem um perfil específico
  ```php
  AuthMiddleware::requirePerfil('admin', 'farmaceutico');
  ```

- **`requireFuncionalidade(...$funcionalidades)`**: Verifica se o usuário tem acesso a uma funcionalidade
  ```php
  AuthMiddleware::requireFuncionalidade('vendas', 'caixa');
  ```

- **`temAcesso($funcionalidade)`**: Retorna true/false se o usuário tem acesso
  ```php
  if (AuthMiddleware::temAcesso('vendas')) {
      // Mostrar opção
  }
  ```

- **`checkAjax()`**: Verifica autenticação para requisições AJAX
  ```php
  AuthMiddleware::checkAjax();
  ```

- **`checkAjaxFuncionalidade(...$funcionalidades)`**: Verifica autorização para AJAX
  ```php
  AuthMiddleware::checkAjaxFuncionalidade('vendas', 'caixa');
  ```

### 2. Definindo Rotas com Restrições

As rotas são definidas em [routes/web.php](routes/web.php) com suporte a permissões:

```php
// Rota com restrição de funcionalidade
$router->get('/vendas', ['VendaController', 'index'], ['funcionalidade' => 'vendas']);

// Rota com restrição de múltiplas funcionalidades (o usuário precisa ter acesso a UMA delas)
$router->post('/vendas/nova', ['VendaController', 'store'], ['funcionalidade' => 'vendas']);
```

### 3. Controladores

Dentro dos controladores, você pode usar o middleware para verificações adicionais:

```php
<?php
namespace App\Controllers;

use App\Middleware\AuthMiddleware;

class VendaController {
    public function index() {
        AuthMiddleware::requireFuncionalidade('vendas');
        // ... resto do código
    }
}
```

### 4. Views (Templates)

Nas views, você pode usar o método `temAcesso()` para mostrar/ocultar elementos condicionalmente:

```php
<?php
use App\Middleware\AuthMiddleware;
?>

<?php if (AuthMiddleware::temAcesso('vendas')): ?>
    <a href="/vendas" class="nav-link">Vendas</a>
<?php endif; ?>
```

## Estrutura de Permissões no Middleware

As permissões estão definidas no array `$permissoes` da classe `AuthMiddleware`:

```php
private static array $permissoes = [
    'admin' => [
        'dashboard',
        'funcionarios',
        'produtos',
        'clientes',
        'vendas',
        'compras',
        'fornecedores',
        'caixa',
        'relatorios',
        'configuracoes',
        'backup'
    ],
    'farmaceutico' => [
        'caixa',
        'vendas',
        'backup'
    ],
    'caixa' => [
        'caixa',
        'vendas',
        'backup'
    ],
    'tecnico' => [
        'caixa',
        'vendas',
        'backup'
    ]
];
```

## Como Adicionar Novas Funcionalidades

### 1. Adicione a funcionalidade ao array de permissões

```php
private static array $permissoes = [
    'admin' => [
        // ... outras funcionalidades
        'minha_nova_funcionalidade'
    ],
    'farmaceutico' => [
        'minha_nova_funcionalidade'
    ],
    // ...
];
```

### 2. Configure as rotas

```php
$router->get('/minha-funcionalidade', ['MeuController', 'index'], 
    ['funcionalidade' => 'minha_nova_funcionalidade']);
```

### 3. Use o middleware no controlador (opcional)

```php
public function index() {
    AuthMiddleware::requireFuncionalidade('minha_nova_funcionalidade');
    // ... resto do código
}
```

### 4. Controle visibilidade nas views

```php
<?php if (AuthMiddleware::temAcesso('minha_nova_funcionalidade')): ?>
    <!-- mostrar opção -->
<?php endif; ?>
```

## Tratamento de Erros

Quando um usuário tenta acessar uma funcionalidade sem permissão:

1. **Em páginas normais**: Exibe erro 403 (Acesso Negado)
   - Arquivo: [app/Views/errors/403.php](app/Views/errors/403.php)

2. **Em requisições AJAX**: Retorna JSON
   ```json
   {
       "erro": "Acesso negado"
   }
   ```

## Fluxo de Autenticação

1. **Login**: O usuário insere email e senha
2. **Validação**: As credenciais são verificadas
3. **Sessão**: Os dados do usuário (incluindo o perfil) são armazenados em `$_SESSION`
4. **Requisição**: Quando acessa uma rota, o middleware verifica as permissões
5. **Autorização**: Se tiver acesso, a ação prossegue; caso contrário, exibe erro 403

## Boas Práticas

1. **Sempre usar o middleware** nas rotas que requerem autenticação
2. **Controlar a UI**: Use `temAcesso()` para esconder links não disponíveis
3. **Validar no controlador**: Para camadas adicionais de segurança
4. **Documentar permissões**: Adicione comentários nas rotas explicando quem tem acesso
5. **Testes**: Teste com diferentes perfis para garantir as restrições funcionam

## Segurança

⚠️ **Importante**: O controle de acesso é aplicado em 2 níveis:

1. **Frontend**: Ocultando elementos nas views
2. **Backend**: Validando permissões no middleware

Nunca confie apenas no controle frontend. O backend sempre deve validar as permissões antes de executar ações sensíveis.

## Exemplo Completo

### Adicionar Nova Rota com Restrição

```php
// routes/web.php
$router->get('/relatorios/vendas', ['RelatorioController', 'vendas'], 
    ['funcionalidade' => 'relatorios']);
```

### Controlador

```php
// app/Controllers/RelatorioController.php
public function vendas() {
    AuthMiddleware::requireFuncionalidade('relatorios');
    
    // Código da ação...
}
```

### View

```php
// app/Views/layouts/sidebar.php
<?php if (AuthMiddleware::temAcesso('relatorios')): ?>
    <a href="/relatorios/vendas" class="nav-link">
        <i class="bi bi-graph-up"></i> Relatórios
    </a>
<?php endif; ?>
```

## Suporte

Para dúvidas ou necessidade de alterar permissões, contacte o desenvolvedor responsável pelo sistema de segurança.
