# 🔧 Solução de Problemas - E-Condo Packages

## ❌ Erro: "Usuário ou senha inválidos"

### 🎯 Solução Rápida

**Execute o script de correção de senhas:**

1. Abra o navegador
2. Acesse: `http://localhost/e-condo/fix_passwords.php`
3. O script irá corrigir automaticamente as senhas
4. Após a correção, faça login com:
   - **Usuário**: `admin`
   - **Senha**: `admin123`

### 📝 Solução Manual (via phpMyAdmin)

Se o script acima não funcionar:

1. Acesse: `http://localhost/phpmyadmin`
2. Selecione o banco `econdo_packages`
3. Clique na tabela `users`
4. Clique em "SQL" no topo
5. Execute este comando:

```sql
-- Gerar hash correto para senha 'admin123'
UPDATE users 
SET password = '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhqe'
WHERE username IN ('admin', 'porteiro1', 'adm1');
```

6. Tente fazer login novamente

### 🔍 Verificar se os Usuários Existem

Execute no phpMyAdmin:

```sql
SELECT id, username, full_name, role, status 
FROM users;
```

**Resultado esperado:**
- 3 usuários: admin, porteiro1, adm1
- Todos com status 'ativo'

### 🛠️ Recriar Usuários do Zero

Se os usuários não existirem:

```sql
-- Deletar usuários existentes (se houver)
DELETE FROM users;

-- Criar novos usuários
INSERT INTO users (username, password, full_name, email, role, status) VALUES
('admin', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhqe', 'Administrador do Sistema', 'admin@econdo.com', 'admin', 'ativo'),
('porteiro1', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhqe', 'Porteiro Principal', 'porteiro@econdo.com', 'porteiro', 'ativo'),
('adm1', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhqe', 'Administração Interna', 'administracao@econdo.com', 'administracao', 'ativo');
```

---

## 🗄️ Problemas com Banco de Dados

### Banco não existe

```sql
CREATE DATABASE econdo_packages 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;
```

### Tabelas não existem

1. Acesse phpMyAdmin
2. Selecione o banco `econdo_packages`
3. Clique em "Importar"
4. Selecione: `C:\xampp\htdocs\e-condo\database\schema.sql`
5. Clique em "Executar"

### Erro de conexão

Verifique `config/config.php`:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'econdo_packages');
define('DB_USER', 'root');
define('DB_PASS', ''); // Vazio no XAMPP padrão
```

---

## 🌐 Problemas de Acesso

### Erro 404 - Página não encontrada

**Verifique:**
1. Apache está rodando no XAMPP
2. Arquivos estão em: `C:\xampp\htdocs\e-condo`
3. URL correta: `http://localhost/e-condo`

### Página em branco

**Ative exibição de erros:**

Em `config/config.php`, mude para:

```php
define('ENVIRONMENT', 'development');
```

Isso mostrará os erros PHP.

### Erro de permissão

**Windows:**
- Não há problemas de permissão geralmente
- Certifique-se que o XAMPP tem permissão de leitura/escrita

---

## 📱 Problemas com WhatsApp

### Notificações não são enviadas

**Verifique:**
1. WhatsApp está habilitado em: Administração → Configurações
2. Token e Phone ID estão corretos
3. Teste a conexão na página de configurações

### Erro ao testar conexão

**Possíveis causas:**
- Token expirado (gere um permanente)
- Phone ID incorreto
- Número de telefone em formato errado (use apenas números com DDD)

---

## 🔐 Criar Novo Usuário Manualmente

```sql
-- Senha será: novasenha123
INSERT INTO users (username, password, full_name, email, role, status) 
VALUES (
    'novousuario',
    '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhqe',
    'Nome do Usuário',
    'email@exemplo.com',
    'admin', -- ou 'porteiro' ou 'administracao'
    'ativo'
);
```

**Depois execute `fix_passwords.php` para gerar hash correto!**

---

## 🆘 Resetar Sistema Completamente

**Se nada funcionar, reset completo:**

1. **Deletar banco:**
   ```sql
   DROP DATABASE IF EXISTS econdo_packages;
   ```

2. **Recriar banco:**
   ```sql
   CREATE DATABASE econdo_packages 
   CHARACTER SET utf8mb4 
   COLLATE utf8mb4_unicode_ci;
   ```

3. **Importar schema:**
   - phpMyAdmin → Importar → `schema.sql`

4. **Corrigir senhas:**
   - Acesse: `http://localhost/e-condo/fix_passwords.php`

5. **Fazer login:**
   - Usuário: `admin`
   - Senha: `admin123`

---

## 📞 Checklist de Diagnóstico

Antes de pedir ajuda, verifique:

- [ ] XAMPP está rodando (Apache + MySQL)
- [ ] Banco `econdo_packages` existe
- [ ] Tabelas foram criadas (11 tabelas)
- [ ] Usuários existem na tabela `users`
- [ ] Arquivo `config/config.php` tem credenciais corretas
- [ ] URL está correta: `http://localhost/e-condo`
- [ ] Executou `fix_passwords.php`

---

## 🎯 Comandos Úteis

### Verificar versão do PHP
```bash
php -v
```

### Verificar se MySQL está rodando
```bash
# No XAMPP Control Panel
MySQL → Status: Running
```

### Ver logs de erro do PHP
```
C:\xampp\apache\logs\error.log
```

### Ver logs do MySQL
```
C:\xampp\mysql\data\mysql_error.log
```

---

## ✅ Após Resolver

1. Faça login com sucesso
2. Altere a senha padrão
3. Configure o WhatsApp (opcional)
4. Cadastre villages, casas e condôminos
5. Teste o fluxo completo

---

**Precisa de mais ajuda?**
- Verifique: README.md
- Instalação: INSTALL.md
- Início rápido: QUICK_START.md
