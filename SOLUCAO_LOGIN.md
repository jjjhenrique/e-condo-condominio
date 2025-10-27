# 🔓 SOLUÇÃO DEFINITIVA - Erro de Login

## ⚡ SOLUÇÃO RÁPIDA (Recomendada)

### **Execute o instalador automático:**

1. **Abra seu navegador**
2. **Digite na barra de endereço:**
   ```
   http://localhost/e-condo/install.php
   ```
3. **Clique em "Acessar Sistema" quando terminar**
4. **Faça login com:**
   - Usuário: `admin`
   - Senha: `admin123`

---

## 🎯 O Que o Instalador Faz

✅ Testa conexão com MySQL  
✅ Cria o banco de dados (se não existir)  
✅ Cria todas as 11 tabelas  
✅ **Gera hash de senha CORRETO**  
✅ Cria os 3 usuários padrão  
✅ Configura o sistema  
✅ Mostra credenciais de login  

---

## 📋 Passo a Passo Detalhado

### **1. Verificar XAMPP**
- Abra o XAMPP Control Panel
- Certifique-se que está rodando:
  - ✅ Apache (botão verde "Running")
  - ✅ MySQL (botão verde "Running")

### **2. Executar Instalador**
```
http://localhost/e-condo/install.php
```

### **3. Aguardar Instalação**
O instalador mostrará:
- 📡 Passo 1: Testando MySQL
- 🗄️ Passo 2: Criando banco
- 📋 Passo 3: Criando tabelas
- 👥 Passo 4: Criando usuários
- ⚙️ Passo 5: Configurações

### **4. Login**
Quando aparecer "🎉 Instalação Concluída":
- Clique em "🚀 Acessar Sistema"
- Use: `admin` / `admin123`

---

## 🔧 Se o Instalador Não Funcionar

### **Opção A: Via phpMyAdmin (Manual)**

1. **Acesse:**
   ```
   http://localhost/phpmyadmin
   ```

2. **Clique em "SQL" (no topo)**

3. **Cole e execute este código:**
   ```sql
   -- Criar banco
   DROP DATABASE IF EXISTS econdo_packages;
   CREATE DATABASE econdo_packages CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   USE econdo_packages;
   ```

4. **Clique em "Importar"**
   - Escolha arquivo: `C:\xampp\htdocs\e-condo\database\schema.sql`
   - Clique em "Executar"

5. **Volte para "SQL" e execute:**
   ```sql
   -- Limpar usuários
   DELETE FROM users;
   
   -- Criar usuários com senha correta
   INSERT INTO users (username, password, full_name, email, role, status) VALUES
   ('admin', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhqe', 'Administrador do Sistema', 'admin@econdo.com', 'admin', 'ativo'),
   ('porteiro1', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhqe', 'Porteiro Principal', 'porteiro@econdo.com', 'porteiro', 'ativo'),
   ('adm1', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhqe', 'Administração Interna', 'administracao@econdo.com', 'administracao', 'ativo');
   ```

6. **Tente fazer login:**
   ```
   http://localhost/e-condo
   Usuário: admin
   Senha: admin123
   ```

---

## ✅ Checklist de Verificação

Antes de tentar login, confirme:

- [ ] XAMPP está aberto
- [ ] Apache está "Running" (verde)
- [ ] MySQL está "Running" (verde)
- [ ] Executou `install.php` OU importou SQL manualmente
- [ ] Banco `econdo_packages` existe no phpMyAdmin
- [ ] Tabela `users` tem 3 registros
- [ ] URL correta: `http://localhost/e-condo`

---

## 🎯 Credenciais Corretas

| Usuário | Senha | Perfil |
|---------|-------|--------|
| **admin** | **admin123** | Administrador |
| **porteiro1** | **admin123** | Porteiro |
| **adm1** | **admin123** | Administração |

---

## 🆘 Ainda Não Funciona?

### **Teste 1: Verificar se usuários existem**

No phpMyAdmin, execute:
```sql
SELECT * FROM users;
```

**Deve mostrar 3 usuários.**

### **Teste 2: Verificar senha**

No phpMyAdmin, execute:
```sql
SELECT username, password FROM users WHERE username = 'admin';
```

**O password deve começar com:** `$2y$10$`

### **Teste 3: Verificar config.php**

Abra: `C:\xampp\htdocs\e-condo\config\config.php`

Verifique se está assim:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'econdo_packages');
define('DB_USER', 'root');
define('DB_PASS', '');
```

---

## 📞 Arquivos de Ajuda Criados

1. ✅ **install.php** - Instalador automático (USE ESTE!)
2. ✅ **fix_passwords.php** - Corrige senhas
3. ✅ **TROUBLESHOOTING.md** - Guia de problemas
4. ✅ **Este arquivo** - Solução de login

---

## 🎉 Após Login Bem-Sucedido

1. ✅ Altere a senha padrão
2. ✅ Delete o arquivo `install.php`
3. ✅ Configure WhatsApp (opcional)
4. ✅ Cadastre villages e casas
5. ✅ Cadastre condôminos
6. ✅ Comece a usar!

---

## 💡 Dica Final

**Se NADA funcionar, faça um RESET COMPLETO:**

1. Abra phpMyAdmin
2. Delete o banco: `DROP DATABASE econdo_packages;`
3. Execute: `http://localhost/e-condo/install.php`
4. Pronto! ✅

---

**Precisa de mais ajuda? Verifique:**
- README.md
- INSTALL.md
- TROUBLESHOOTING.md
