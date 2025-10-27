# 📦 Guia de Instalação - E-Condo Packages

Este guia fornece instruções detalhadas para instalar e configurar o sistema E-Condo Packages.

## 📋 Pré-requisitos

Antes de começar, certifique-se de ter:

- ✅ XAMPP instalado (ou Apache + PHP + MySQL)
- ✅ PHP 8.0 ou superior
- ✅ MySQL 8.0 ou superior
- ✅ Navegador web moderno (Chrome, Firefox, Edge)
- ✅ Conta no Facebook Developers (para WhatsApp API)

## 🚀 Instalação Passo a Passo

### 1. Instalar XAMPP

Se ainda não tiver o XAMPP instalado:

1. Baixe em: https://www.apachefriends.org/
2. Execute o instalador
3. Instale em `C:\xampp`
4. Inicie o **Apache** e **MySQL** no painel de controle do XAMPP

### 2. Copiar Arquivos do Sistema

1. Extraia todos os arquivos do sistema
2. Copie a pasta `e-condo` para:
   ```
   C:\xampp\htdocs\e-condo
   ```

3. Estrutura esperada:
   ```
   C:\xampp\htdocs\e-condo\
   ├── api/
   ├── app/
   ├── config/
   ├── database/
   ├── packages/
   ├── index.php
   ├── login.php
   └── ...
   ```

### 3. Criar Banco de Dados

#### Opção A: Via phpMyAdmin (Recomendado)

1. Abra o navegador e acesse: `http://localhost/phpmyadmin`

2. Clique em **"Novo"** na barra lateral esquerda

3. Crie o banco de dados:
   - **Nome**: `econdo_packages`
   - **Collation**: `utf8mb4_unicode_ci`
   - Clique em **"Criar"**

4. Importe o schema:
   - Selecione o banco `econdo_packages`
   - Clique na aba **"Importar"**
   - Clique em **"Escolher arquivo"**
   - Selecione: `C:\xampp\htdocs\e-condo\database\schema.sql`
   - Clique em **"Executar"**

5. Verifique se as tabelas foram criadas:
   - Você deve ver 11 tabelas criadas
   - 3 usuários padrão inseridos
   - Configurações iniciais carregadas

#### Opção B: Via Linha de Comando

```bash
# Abra o prompt de comando
cd C:\xampp\mysql\bin

# Execute o MySQL
mysql -u root -p

# Crie o banco (pressione Enter quando pedir senha, se não houver)
CREATE DATABASE econdo_packages CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE econdo_packages;

# Importe o schema
source C:/xampp/htdocs/e-condo/database/schema.sql;

# Verifique as tabelas
SHOW TABLES;

# Saia
exit;
```

### 4. Configurar Conexão com Banco de Dados

1. Abra o arquivo: `C:\xampp\htdocs\e-condo\config\config.php`

2. Localize as configurações do banco de dados:

```php
// ============================================
// CONFIGURAÇÕES DO BANCO DE DADOS
// ============================================
define('DB_HOST', 'localhost');
define('DB_NAME', 'econdo_packages');
define('DB_USER', 'root');
define('DB_PASS', '');
```

3. **Se necessário**, ajuste os valores:
   - `DB_HOST`: Geralmente `localhost`
   - `DB_NAME`: `econdo_packages` (não altere)
   - `DB_USER`: Usuário do MySQL (padrão: `root`)
   - `DB_PASS`: Senha do MySQL (padrão: vazio no XAMPP)

4. Salve o arquivo

### 5. Verificar Instalação

1. Abra o navegador

2. Acesse: `http://localhost/e-condo`

3. Você deve ver a tela de login

4. Teste com um dos usuários padrão:
   - **Usuário**: `admin`
   - **Senha**: `admin123`

5. Se o login funcionar, a instalação básica está completa! ✅

### 6. Configurar WhatsApp API (Opcional mas Recomendado)

#### 6.1. Criar App no Facebook Developers

1. Acesse: https://developers.facebook.com/

2. Faça login ou crie uma conta

3. Clique em **"Meus Apps"** → **"Criar App"**

4. Escolha o tipo: **"Business"**

5. Preencha:
   - **Nome do app**: "E-Condo Notifications" (ou outro nome)
   - **E-mail de contato**: Seu e-mail
   - **Conta de negócios**: Crie uma nova ou selecione existente

6. Clique em **"Criar App"**

#### 6.2. Adicionar WhatsApp Business

1. No painel do app, procure por **"WhatsApp"**

2. Clique em **"Configurar"**

3. Siga o assistente de configuração:
   - Aceite os termos
   - Configure um número de telefone de teste (fornecido pelo Facebook)
   - Ou adicione seu próprio número de negócios

#### 6.3. Obter Credenciais

1. No painel do WhatsApp, você verá:

   **Token de Acesso Temporário**:
   - Copie o token exibido
   - ⚠️ Este token expira em 24 horas

   **Phone Number ID**:
   - Copie o ID do número de telefone

2. Para gerar um **Token Permanente**:
   - Vá em **"Configurações"** → **"Básico"**
   - Copie o **"ID do App"** e **"Chave Secreta do App"**
   - Use a ferramenta de geração de token de longa duração:
     https://developers.facebook.com/docs/facebook-login/guides/access-tokens/get-long-lived

#### 6.4. Configurar no Sistema

1. Faça login no sistema como **admin**

2. Acesse: **Administração** → **Configurações**

3. Na seção **"WhatsApp API"**, preencha:
   - **Token da API**: Cole o token copiado
   - **ID do Telefone**: Cole o Phone Number ID
   - **Habilitar WhatsApp**: Marque a caixa

4. Clique em **"Salvar Configurações"**

#### 6.5. Testar Conexão

1. Na mesma página de configurações, role até **"Testar Conexão WhatsApp"**

2. Digite seu número de telefone (com DDD, apenas números)
   - Exemplo: `11987654321`

3. Clique em **"Enviar Mensagem de Teste"**

4. Você deve receber uma mensagem no WhatsApp

5. Se receber, a integração está funcionando! ✅

### 7. Configurações Adicionais

#### 7.1. Alterar Senhas Padrão

⚠️ **IMPORTANTE**: Altere as senhas dos usuários padrão!

1. Faça login como **admin**

2. Acesse: **Administração** → **Usuários**

3. Edite cada usuário e altere a senha

#### 7.2. Cadastrar Villages e Casas

1. Acesse: **Cadastros** → **Villages**

2. Clique em **"Nova Village"**

3. Preencha o nome e descrição

4. Após criar a village, acesse: **Cadastros** → **Casas**

5. Adicione as casas da village

#### 7.3. Cadastrar Condôminos

1. Acesse: **Cadastros** → **Condôminos**

2. Clique em **"Novo Condômino"**

3. Preencha os dados:
   - **Nome completo**
   - **CPF**: Apenas números ou formatado
   - **Telefone**: Com DDD (para WhatsApp)
   - **E-mail**
   - **Village e Casa**

4. Clique em **"Salvar"**

## 🔧 Solução de Problemas

### Erro: "Erro de conexão com o banco de dados"

**Causa**: Configurações incorretas ou MySQL não está rodando

**Solução**:
1. Verifique se o MySQL está rodando no XAMPP
2. Confirme as credenciais em `config/config.php`
3. Teste a conexão no phpMyAdmin

### Erro: "Call to undefined function password_hash()"

**Causa**: Versão do PHP muito antiga

**Solução**:
1. Verifique a versão do PHP: `php -v`
2. Atualize para PHP 8.0 ou superior
3. No XAMPP, baixe a versão mais recente

### Erro: "Headers already sent"

**Causa**: Espaços ou BOM antes do `<?php`

**Solução**:
1. Abra os arquivos em um editor que suporte UTF-8 sem BOM
2. Remova espaços em branco antes de `<?php`
3. Salve como UTF-8 sem BOM

### WhatsApp não envia mensagens

**Causa**: Configuração incorreta ou token expirado

**Solução**:
1. Verifique se o WhatsApp está habilitado
2. Confirme se o token e Phone ID estão corretos
3. Teste a conexão na página de configurações
4. Verifique se o token não expirou (gere um permanente)

### Página em branco ou erro 500

**Causa**: Erro de PHP não exibido

**Solução**:
1. Ative a exibição de erros em `config/config.php`:
   ```php
   define('ENVIRONMENT', 'development');
   ```
2. Verifique os logs do Apache em: `C:\xampp\apache\logs\error.log`

## 📞 Suporte

### Documentação Adicional

- **README.md**: Visão geral do sistema
- **Documentação WhatsApp API**: https://developers.facebook.com/docs/whatsapp

### Logs do Sistema

Para depuração, verifique:
- **Logs do Apache**: `C:\xampp\apache\logs\error.log`
- **Logs do MySQL**: `C:\xampp\mysql\data\mysql_error.log`
- **Logs do Sistema**: Acesse **Administração** → **Logs do Sistema**

## ✅ Checklist de Instalação

- [ ] XAMPP instalado e rodando
- [ ] Arquivos copiados para `htdocs/e-condo`
- [ ] Banco de dados criado
- [ ] Schema SQL importado
- [ ] Configurações do banco ajustadas
- [ ] Login funcionando
- [ ] Senhas padrão alteradas
- [ ] WhatsApp API configurado (opcional)
- [ ] Teste de envio de WhatsApp realizado
- [ ] Villages cadastradas
- [ ] Casas cadastradas
- [ ] Condôminos cadastrados
- [ ] Primeira encomenda testada

## 🎉 Próximos Passos

Após a instalação:

1. **Cadastre os dados básicos**:
   - Villages
   - Casas
   - Condôminos

2. **Teste o fluxo completo**:
   - Receba uma encomenda
   - Verifique se a notificação WhatsApp foi enviada
   - Transfira para administração
   - Registre a retirada

3. **Treine os usuários**:
   - Porteiros
   - Equipe de administração

4. **Configure backups regulares**:
   - Banco de dados
   - Arquivos do sistema

---

**Instalação concluída! O sistema está pronto para uso.** 🚀
