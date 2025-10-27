# 🚀 Guia Rápido - E-Condo Packages

## ⚡ Instalação em 5 Minutos

### 1️⃣ Preparar Ambiente
```bash
# Certifique-se que XAMPP está instalado e rodando
# Apache: ✅ Rodando
# MySQL: ✅ Rodando
```

### 2️⃣ Criar Banco de Dados
1. Acesse: `http://localhost/phpmyadmin`
2. Clique em **"Novo"**
3. Nome: `econdo_packages`
4. Collation: `utf8mb4_unicode_ci`
5. Clique em **"Criar"**
6. Vá em **"Importar"**
7. Selecione: `database/schema.sql`
8. Clique em **"Executar"**

### 3️⃣ Acessar Sistema
1. Abra: `http://localhost/e-condo`
2. Login: `admin`
3. Senha: `admin123`

### 4️⃣ Configurar WhatsApp (Opcional)
1. Acesse: **Administração** → **Configurações**
2. Preencha Token e Phone ID
3. Habilite WhatsApp
4. Teste a conexão

### 5️⃣ Cadastrar Dados Iniciais
1. **Villages**: Cadastros → Villages → Nova Village
2. **Casas**: Cadastros → Casas → Nova Casa
3. **Condôminos**: Cadastros → Condôminos → Novo Condômino

## 🎯 Fluxo de Uso

### Receber Encomenda
```
Encomendas → Receber Encomenda
↓
Selecionar Village/Casa/Condômino
↓
Registrar
↓
✅ Código gerado + WhatsApp enviado
```

### Transferir para Administração
```
Encomendas → Transferir Encomendas
↓
Selecionar encomendas
↓
Transferir
↓
✅ Movidas para Administração
```

### Retirar Encomenda
```
Encomendas → Retirar Encomenda
↓
Digite código (ex: PKG123456789)
↓
Verificar dados
↓
Confirmar retirada
↓
✅ WhatsApp de confirmação enviado
```

## 👥 Usuários Padrão

| Usuário | Senha | Perfil |
|---------|-------|--------|
| admin | admin123 | Administrador |
| porteiro1 | admin123 | Porteiro |
| adm1 | admin123 | Administração |

⚠️ **Altere as senhas após primeiro acesso!**

## 📱 WhatsApp - Configuração Rápida

### Obter Credenciais
1. https://developers.facebook.com/
2. Criar App → Business
3. Adicionar produto WhatsApp
4. Copiar **Token** e **Phone Number ID**

### Configurar no Sistema
1. Admin → Configurações
2. Colar Token e Phone ID
3. Habilitar
4. Testar

## 🔧 Problemas Comuns

### Não consegue logar
- Verifique se o banco foi criado
- Confirme que o schema.sql foi importado
- Teste conexão em `config/config.php`

### WhatsApp não envia
- Verifique se está habilitado
- Confirme Token e Phone ID
- Teste a conexão
- Verifique formato do telefone (apenas números com DDD)

### Erro 404
- Confirme URL: `http://localhost/e-condo`
- Verifique se Apache está rodando
- Confirme que arquivos estão em `C:\xampp\htdocs\e-condo`

## 📚 Documentação Completa

- **README.md**: Visão geral e características
- **INSTALL.md**: Instalação detalhada passo a passo
- **Este arquivo**: Guia rápido de início

## ✅ Checklist Inicial

- [ ] XAMPP rodando
- [ ] Banco criado e importado
- [ ] Login funcionando
- [ ] Senhas alteradas
- [ ] Villages cadastradas
- [ ] Casas cadastradas
- [ ] Condôminos cadastrados
- [ ] WhatsApp configurado (opcional)
- [ ] Primeira encomenda testada

## 🎉 Pronto!

Seu sistema está configurado e pronto para uso!

**Próximo passo**: Cadastre seus condôminos e comece a registrar encomendas.

---

💡 **Dica**: Use o Dashboard para visualizar estatísticas em tempo real!
