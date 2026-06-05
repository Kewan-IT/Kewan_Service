# Guia de Testes - Sistema de Controle de Acesso

## Como Testar o Sistema de Permissões

### Pré-requisitos
- Sistema KewanFarma rodando
- Usuários com diferentes perfis criados no banco de dados

### 1. Testar com Perfil ADMIN

#### Login
1. Acesse `/auth/login`
2. Faça login com usuário admin

#### Verificações
- ✅ Dashboard deve estar acessível (`/dashboard`)
- ✅ Menu de navegação deve mostrar TODAS as opções:
  - Dashboard
  - Nova Venda
  - Vendas
  - Caixa
  - Clientes
  - Produtos
  - Compras
  - Fornecedores
  - Funcionários
  - Relatórios
  - Configurações
  - Backup

#### Testes de Acesso
- ✅ `/funcionarios` → Deve funcionar
- ✅ `/produtos` → Deve funcionar
- ✅ `/compras` → Deve funcionar
- ✅ `/fornecedores` → Deve funcionar
- ✅ `/relatorios` → Deve funcionar
- ✅ `/configuracoes` → Deve funcionar
- ✅ `/vendas` → Deve funcionar
- ✅ `/caixa` → Deve funcionar

---

### 2. Testar com Perfil CAIXA

#### Login
1. Acesse `/auth/login`
2. Faça login com usuário caixa

#### Verificações
- ❌ Dashboard NÃO deve estar acessível (redirecionará para 403)
- ✅ Menu de navegação deve mostrar APENAS:
  - Nova Venda
  - Vendas
  - Caixa
  - Backup (na seção Sistema)

#### Testes de Acesso
- ❌ `/dashboard` → Erro 403
- ✅ `/vendas` → Deve funcionar
- ✅ `/vendas/nova` → Deve funcionar
- ✅ `/caixa` → Deve funcionar
- ✅ `/configuracoes/fazer-backup` → Deve funcionar
- ❌ `/funcionarios` → Erro 403
- ❌ `/produtos` → Erro 403
- ❌ `/compras` → Erro 403
- ❌ `/fornecedores` → Erro 403
- ❌ `/relatorios` → Erro 403
- ❌ `/configuracoes` → Erro 403 (só backup)

---

### 3. Testar com Perfil FARMACEUTICO

#### Login
1. Acesse `/auth/login`
2. Faça login com usuário farmacêutico

#### Verificações
- ❌ Dashboard NÃO deve estar acessível (redirecionará para 403)
- ✅ Menu de navegação deve mostrar APENAS:
  - Nova Venda
  - Vendas
  - Caixa
  - Backup (na seção Sistema)

#### Testes de Acesso
- ❌ `/dashboard` → Erro 403
- ✅ `/vendas` → Deve funcionar
- ✅ `/vendas/nova` → Deve funcionar
- ✅ `/caixa` → Deve funcionar
- ✅ `/configuracoes/fazer-backup` → Deve funcionar
- ❌ `/funcionarios` → Erro 403
- ❌ `/produtos` → Erro 403
- ❌ `/compras` → Erro 403
- ❌ `/fornecedores` → Erro 403
- ❌ `/relatorios` → Erro 403
- ❌ `/configuracoes` → Erro 403 (só backup)

---

### 4. Testar com Perfil TECNICO

#### Login
1. Acesse `/auth/login`
2. Faça login com usuário técnico

#### Verificações
- ❌ Dashboard NÃO deve estar acessível (redirecionará para 403)
- ✅ Menu de navegação deve mostrar APENAS:
  - Nova Venda
  - Vendas
  - Caixa
  - Backup (na seção Sistema)

#### Testes de Acesso
- ❌ `/dashboard` → Erro 403
- ✅ `/vendas` → Deve funcionar
- ✅ `/vendas/nova` → Deve funcionar
- ✅ `/caixa` → Deve funcionar
- ✅ `/configuracoes/fazer-backup` → Deve funcionar
- ❌ `/funcionarios` → Erro 403
- ❌ `/produtos` → Erro 403
- ❌ `/compras` → Erro 403
- ❌ `/fornecedores` → Erro 403
- ❌ `/relatorios` → Erro 403
- ❌ `/configuracoes` → Erro 403 (só backup)

---

## Testes de Segurança

### Teste 1: Tentar Acessar URL Diretamente
**Objetivo**: Verificar se a validação no backend funciona

1. Login como **caixa**
2. Tente acessar `/produtos` diretamente na URL
3. **Resultado esperado**: Erro 403 (Acesso Negado)
4. **Não deve**: Permitir acesso ou redirecionar silenciosamente

### Teste 2: Verificar Requisições AJAX
**Objetivo**: Validar que APIs também respeitam permissões

1. Login como **caixa**
2. Abra o DevTools (F12)
3. Na aba Console, execute:
   ```javascript
   fetch('/api/estoque/alertas')
     .then(r => r.json())
     .then(d => console.log(d));
   ```
4. **Resultado esperado**: Erro 403 (JSON com "erro": "Acesso negado")

### Teste 3: Verificar Menu Dinâmico
**Objetivo**: Validar que o menu filtra itens corretamente

1. Login com diferentes perfis
2. Inspecione o HTML do menu (`<nav class="sidebar-nav">`)
3. **Admin**: Deve conter todos os links
4. **Caixa/Técnico/Farmacêutico**: Deve conter apenas Vendas, Caixa, Backup

---

## Script de Teste Rápido

Para criar usuários de teste com diferentes perfis:

```sql
-- Admin (se não existir)
INSERT INTO usuarios (nome, email, senha_hash, perfil, ativo) 
VALUES ('Admin Teste', 'admin@test.com', '$2y$12$...', 'admin', 1);

-- Caixa
INSERT INTO usuarios (nome, email, senha_hash, perfil, ativo) 
VALUES ('Caixa Teste', 'caixa@test.com', '$2y$12$...', 'caixa', 1);

-- Farmacêutico
INSERT INTO usuarios (nome, email, senha_hash, perfil, ativo) 
VALUES ('Farm Teste', 'farm@test.com', '$2y$12$...', 'farmaceutico', 1);

-- Técnico
INSERT INTO usuarios (nome, email, senha_hash, perfil, ativo) 
VALUES ('Tec Teste', 'tec@test.com', '$2y$12$...', 'tecnico', 1);
```

---

## Checklist de Testes

Marque cada item conforme testar:

### Admin
- [ ] Login bem-sucedido
- [ ] Acesso a Dashboard
- [ ] Acesso a Funcionários
- [ ] Acesso a Produtos
- [ ] Acesso a Clientes
- [ ] Acesso a Compras
- [ ] Acesso a Fornecedores
- [ ] Acesso a Relatórios
- [ ] Acesso a Configurações
- [ ] Acesso a Vendas
- [ ] Acesso a Caixa
- [ ] Acesso a Backup

### Caixa / Farmacêutico / Técnico
- [ ] Login bem-sucedido
- [ ] Erro 403 ao tentar acessar Dashboard
- [ ] Erro 403 ao tentar acessar Funcionários
- [ ] Erro 403 ao tentar acessar Produtos
- [ ] Erro 403 ao tentar acessar Clientes
- [ ] Erro 403 ao tentar acessar Compras
- [ ] Erro 403 ao tentar acessar Fornecedores
- [ ] Erro 403 ao tentar acessar Relatórios
- [ ] Erro 403 ao tentar acessar Configurações (geral)
- [ ] Acesso a Vendas
- [ ] Acesso a Caixa
- [ ] Acesso a Backup
- [ ] Menu mostra apenas opções permitidas

---

## Resultados Esperados

✅ **Sistema funcionando corretamente se:**
- Admin acessa TODAS as funcionalidades
- Caixa/Farmacêutico/Técnico acessam apenas Vendas, Caixa e Backup
- Tentativas de acesso não autorizado resultam em erro 403
- Menu dinâmico mostra apenas opções disponíveis
- URLs diretas são bloqueadas para usuários sem permissão

❌ **Problemas em:**
- Usuários conseguindo acessar funcionalidades restritas
- Menu mostrando opções que não deveria
- Erros não controlados ao invés de 403
- Mensagens de erro confusas

---

## Debugging

Se encontrar problemas:

1. **Verifique o perfil do usuário:**
   ```php
   echo $_SESSION['perfil']; // Na view
   ```

2. **Verifique as permissões:**
   ```php
   var_dump(AuthMiddleware::temAcesso('vendas'));
   ```

3. **Verifique logs do servidor:**
   ```
   tail -f storage/logs/error.log
   ```

4. **Verifique o arquivo de rotas:**
   ```
   grep -n "funcionalidade" routes/web.php
   ```

---

## Relatório de Testes

Quando terminar todos os testes, documente:
- Data e hora dos testes
- Usuários testados
- Navegadores/dispositivos utilizados
- Qualquer problema encontrado
- Mudanças necessárias

Exemplo:
```
Data: 2025-06-04
Testador: [Nome]
Resultado: ✅ Todos os testes passaram
Navegador: Chrome 125
Observações: Sistema funcionando conforme esperado
```
