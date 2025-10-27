# E-Condo Packages - Sistema de Gerenciamento de Encomendas

Sistema completo para gerenciamento de recebimento, transferência e retirada de encomendas em condomínios, com notificações automáticas via WhatsApp.

## 📋 Índice

- [Características](#características)
- [Requisitos](#requisitos)
- [Instalação](#instalação)
- [Configuração do WhatsApp API](#configuração-do-whatsapp-api)
- [Estrutura do Projeto](#estrutura-do-projeto)
- [Perfis de Acesso](#perfis-de-acesso)
- [Uso do Sistema](#uso-do-sistema)
- [Troubleshooting](#troubleshooting)

## 🚀 Características

- ✅ Recebimento de encomendas com geração automática de código único
- ✅ Notificações automáticas via WhatsApp (API oficial da Meta)
- ✅ Transferência de encomendas da portaria para administração
- ✅ Controle de retirada com confirmação
- ✅ Gestão completa de condôminos, villages e casas
- ✅ Relatórios e exportação para Excel
- ✅ Dashboard com estatísticas em tempo real
- ✅ Sistema de logs completo
- ✅ Controle de acesso por perfis (Admin, Porteiro, Administração)
- ✅ Interface responsiva e moderna (Bootstrap 5)

## 💻 Requisitos

- **Servidor Web**: Apache 2.4+ (XAMPP recomendado)
- **PHP**: 8.0 ou superior
- **MySQL**: 8.0 ou superior
- **Extensões PHP necessárias**:
  - PDO
  - PDO_MySQL
  - cURL
  - mbstring
  - json

## 📦 Instalação

### Passo 1: Copiar Arquivos

1. Copie todos os arquivos para a pasta `htdocs` do XAMPP:
   ```
   C:\xampp\htdocs\e-condo\
   ```

### Passo 2: Criar Banco de Dados

1. Abra o phpMyAdmin: `http://localhost/phpmyadmin`

2. Execute o arquivo SQL de criação do banco:
   ```
   database/schema.sql
   ```

   Ou crie manualmente:
   - Clique em "Novo" no phpMyAdmin
   - Nome do banco: `econdo_packages`
   - Collation: `utf8mb4_unicode_ci`
   - Clique em "Importar" e selecione o arquivo `schema.sql`

### Passo 3: Configurar Conexão

1. Edite o arquivo `config/config.php` e ajuste as configurações do banco:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'econdo_packages');
define('DB_USER', 'root');
define('DB_PASS', '');
```

### Passo 4: Acessar o Sistema

1. Abra o navegador e acesse:
   ```
   http://localhost/e-condo
   ```

2. Faça login com um dos usuários padrão:

| Usuário | Senha | Perfil |
|---------|-------|--------|
| admin | admin123 | Administrador |
| porteiro1 | admin123 | Porteiro |
| adm1 | admin123 | Administração |

⚠️ **IMPORTANTE**: Altere as senhas padrão após o primeiro acesso!

## 📱 Configuração do WhatsApp API

O sistema utiliza a **Meta Cloud API** (API oficial do WhatsApp Business).

### Passo 1: Criar Conta no Facebook Developers

1. Acesse: https://developers.facebook.com/
2. Crie uma conta ou faça login
3. Clique em "Meus Apps" → "Criar App"
4. Escolha "Business" como tipo de app
5. Preencha os dados do app

### Passo 2: Adicionar WhatsApp Business

1. No painel do app, clique em "Adicionar Produto"
2. Selecione "WhatsApp" → "Configurar"
3. Siga o assistente de configuração
4. Adicione um número de telefone de teste ou configure um número permanente

### Passo 3: Obter Credenciais

1. No painel do WhatsApp, você verá:
   - **Phone Number ID** (ID do Telefone)
   - **WhatsApp Business Account ID**
   - **Token de Acesso Temporário**

2. Para gerar um **Token Permanente**:
   - Vá em "Configurações" → "Básico"
   - Copie o "App ID" e "App Secret"
   - Gere um token de acesso de longa duração

### Passo 4: Configurar no Sistema

1. Faça login como **admin**
2. Acesse: **Administração** → **Configurações**
3. Na seção "WhatsApp API", preencha:
   - **Token da API**: Cole o token de acesso
   - **ID do Telefone**: Cole o Phone Number ID
   - **Habilitar WhatsApp**: Marque como "Sim"
4. Clique em "Salvar Configurações"

### Passo 5: Testar Integração

1. Na página de configurações, use a opção "Testar Conexão"
2. Digite um número de telefone (com DDD)
3. Você deve receber uma mensagem de teste

### Formato do Número de Telefone

Os números devem estar no formato:
- **Com código do país**: 5511987654321
- **Sem formatação**: apenas números
- O sistema adiciona automaticamente o código 55 (Brasil) se necessário

### Mensagens Enviadas

O sistema envia 2 tipos de mensagens:

1. **Ao receber encomenda**:
   ```
   Olá [NOME], sua encomenda chegou na portaria!
   Código para retirada: PKG123456789
   Apresente este código ao retirar sua encomenda.
   ```

2. **Ao retirar encomenda**:
   ```
   Olá [NOME], sua encomenda com código PKG123456789 foi retirada com sucesso.
   Obrigado!
   ```

## 📁 Estrutura do Projeto

```
e-condo/
├── api/                    # Endpoints API (AJAX)
│   ├── get_houses.php
│   └── get_residents.php
├── app/
│   ├── core/              # Classes principais
│   │   ├── Controller.php
│   │   ├── Database.php
│   │   └── Model.php
│   ├── helpers/           # Classes auxiliares
│   │   └── WhatsAppService.php
│   ├── models/            # Models (MVC)
│   │   ├── House.php
│   │   ├── Package.php
│   │   ├── Resident.php
│   │   ├── SystemLog.php
│   │   ├── SystemSetting.php
│   │   ├── User.php
│   │   └── Village.php
│   └── views/
│       └── layouts/       # Templates
│           ├── header.php
│           └── footer.php
├── admin/                 # Área administrativa
│   ├── users.php
│   ├── settings.php
│   └── logs.php
├── config/                # Configurações
│   └── config.php
├── database/              # Scripts SQL
│   └── schema.sql
├── houses/                # Gestão de casas
├── packages/              # Gestão de encomendas
│   ├── receive.php
│   ├── pickup.php
│   ├── transfer.php
│   ├── list.php
│   ├── search.php
│   └── view.php
├── reports/               # Relatórios
├── residents/             # Gestão de condôminos
├── villages/              # Gestão de villages
├── index.php              # Dashboard
├── login.php              # Login
├── logout.php             # Logout
└── README.md              # Este arquivo
```

## 👥 Perfis de Acesso

### Administrador
- Acesso total ao sistema
- Gerenciar usuários
- Configurar WhatsApp API
- Cadastrar condôminos, villages e casas
- Visualizar logs do sistema
- Gerar relatórios completos

### Porteiro
- Receber encomendas
- Transferir encomendas para administração
- Registrar retiradas
- Visualizar encomendas

### Administração
- Receber encomendas transferidas
- Registrar retiradas
- Transferir encomendas
- Visualizar encomendas e relatórios

## 📖 Uso do Sistema

### Receber Encomenda

1. Acesse: **Encomendas** → **Receber Encomenda**
2. Selecione a village
3. Selecione a casa
4. Selecione o condômino
5. Adicione observações (opcional)
6. Clique em "Registrar Encomenda"
7. Um código único será gerado (ex: PKG123456789)
8. O condômino receberá notificação via WhatsApp automaticamente

### Transferir Encomendas

1. Acesse: **Encomendas** → **Transferir Encomendas**
2. Selecione as encomendas pendentes na portaria
3. Clique em "Transferir para Administração"
4. As encomendas mudarão de localização

### Retirar Encomenda

1. Acesse: **Encomendas** → **Retirar Encomenda**
2. Digite o código fornecido pelo condômino
3. Verifique os dados do condômino
4. Confirme a retirada
5. Uma notificação de confirmação será enviada via WhatsApp

### Cadastrar Condômino

1. Acesse: **Cadastros** → **Condôminos** → **Novo Condômino**
2. Preencha os dados:
   - Nome completo
   - CPF (apenas números ou formatado)
   - Telefone (com DDD, para WhatsApp)
   - E-mail
   - Village e Casa
3. Clique em "Salvar"

### Gerar Relatórios

1. Acesse: **Relatórios** → **Relatório de Encomendas**
2. Selecione os filtros desejados
3. Clique em "Gerar Relatório"
4. Opções de exportação:
   - PDF
   - Excel (XLSX)

## 🔧 Troubleshooting

### Erro de Conexão com Banco de Dados

**Problema**: "Erro de conexão com o banco de dados"

**Solução**:
1. Verifique se o MySQL está rodando no XAMPP
2. Confirme as credenciais em `config/config.php`
3. Certifique-se que o banco `econdo_packages` foi criado

### WhatsApp não Envia Mensagens

**Problema**: Notificações não são enviadas

**Solução**:
1. Verifique se o WhatsApp está habilitado em **Configurações**
2. Confirme se o Token e Phone ID estão corretos
3. Teste a conexão na página de configurações
4. Verifique se o número de telefone está no formato correto (apenas números com DDD)
5. Consulte os logs em **Administração** → **Logs do Sistema**

### Erro 404 ao Acessar Páginas

**Problema**: Páginas não encontradas

**Solução**:
1. Verifique se todos os arquivos foram copiados corretamente
2. Confirme a URL base em `config/config.php`:
   ```php
   define('SITE_URL', 'http://localhost/e-condo');
   ```
3. Certifique-se que o Apache está rodando

### Sessão Expira Rapidamente

**Problema**: Sistema desloga automaticamente

**Solução**:
1. Ajuste o tempo de sessão em `config/config.php`:
   ```php
   define('SESSION_LIFETIME', 7200); // 2 horas em segundos
   ```

### Erro ao Importar SQL

**Problema**: Erro ao executar schema.sql

**Solução**:
1. Aumente o `max_allowed_packet` no MySQL
2. Execute o SQL em partes menores
3. Use o phpMyAdmin para importar

## 📞 Suporte

Para questões sobre a API do WhatsApp:
- Documentação oficial: https://developers.facebook.com/docs/whatsapp
- Suporte Meta: https://developers.facebook.com/support

## 📝 Licença

Este sistema foi desenvolvido para uso em condomínios. Todos os direitos reservados.

## 🔄 Atualizações

### Versão 1.0.0 (Atual)
- Sistema completo de gerenciamento de encomendas
- Integração com WhatsApp Business API
- Dashboard com estatísticas
- Relatórios e exportações
- Sistema de logs
- Controle de acesso por perfis

---

**Desenvolvido com ❤️ para facilitar a gestão de encomendas em condomínios**
