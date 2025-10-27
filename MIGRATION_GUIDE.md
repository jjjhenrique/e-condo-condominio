# Guia de Migração - Meta Cloud API para Evolution API

Este guia explica como migrar o sistema E-Condo Packages da Meta Cloud API para a Evolution API.

---

## 📋 O que mudou?

### Antes (Meta Cloud API):
- Requeria aprovação de templates pela Meta
- Usava Token de Acesso + Phone Number ID
- Endpoint: `https://graph.facebook.com/v18.0`
- Limitações de mensagens e custos

### Agora (Evolution API):
- Mensagens livres, sem necessidade de aprovação
- Usa API Key + Nome da Instância
- Endpoint configurável (padrão: `http://localhost:8080`)
- Sem custos adicionais, auto-hospedado

---

## 🔄 Passo a Passo da Migração

### 1. Instalar a Evolution API

Siga as instruções em `EVOLUTION_API_CONFIG.md` para instalar a Evolution API.

**Resumo rápido com Docker:**

```bash
docker run -d \
  --name evolution-api \
  -p 8080:8080 \
  -e AUTHENTICATION_API_KEY=sua_api_key_segura_aqui \
  -e SERVER_URL=http://localhost:8080 \
  atendai/evolution-api:latest
```

### 2. Criar e Conectar uma Instância

```bash
# Criar instância
curl -X POST "http://localhost:8080/instance/create" \
  -H "apikey: sua_api_key_segura_aqui" \
  -H "Content-Type: application/json" \
  -d '{
    "instanceName": "econdo",
    "qrcode": true
  }'

# Obter QR Code
curl -X GET "http://localhost:8080/instance/connect/econdo" \
  -H "apikey: sua_api_key_segura_aqui"
```

Escaneie o QR Code com seu WhatsApp.

### 3. Atualizar o Banco de Dados

Execute o script de migração SQL:

```bash
# Via MySQL CLI
mysql -u root -p econdo_packages < database/migration_evolution_api.sql

# Ou via phpMyAdmin
# Importe o arquivo: database/migration_evolution_api.sql
```

**O que o script faz:**
- Remove configurações antigas (`whatsapp_api_token`, `whatsapp_phone_id`)
- Adiciona novas configurações (`evolution_api_url`, `evolution_api_key`, `evolution_instance_name`)
- Mantém a configuração de habilitação (`whatsapp_enabled`)

### 4. Configurar no Painel Administrativo

1. Acesse: `http://localhost/e-condo/admin/settings.php`
2. Faça login como administrador
3. Na seção "Configurações da Evolution API":
   - **URL da Evolution API**: `http://localhost:8080`
   - **API Key**: Cole a API Key configurada na Evolution API
   - **Nome da Instância**: `econdo` (ou o nome que você criou)
   - **Habilitar WhatsApp**: Marque a opção
4. Clique em **Salvar Configurações**

### 5. Testar a Conexão

1. Na mesma página de configurações, role até "Testar Conexão WhatsApp"
2. Digite um número de telefone (com DDD)
3. Clique em **Enviar Mensagem de Teste**
4. Verifique se a mensagem foi recebida no WhatsApp

---

## 🔍 Verificação Pós-Migração

### Verificar configurações no banco:

```sql
SELECT * FROM system_settings 
WHERE setting_key LIKE '%evolution%' 
   OR setting_key = 'whatsapp_enabled';
```

**Resultado esperado:**
```
+----+---------------------------+------------------------+--------------------------------+
| id | setting_key               | setting_value          | description                    |
+----+---------------------------+------------------------+--------------------------------+
|  1 | evolution_api_url         | http://localhost:8080  | URL da Evolution API           |
|  2 | evolution_api_key         | sua_api_key            | API Key da Evolution API       |
|  3 | evolution_instance_name   | econdo                 | Nome da instância              |
|  4 | whatsapp_enabled          | 1                      | Habilitar WhatsApp             |
+----+---------------------------+------------------------+--------------------------------+
```

### Verificar status da instância:

```bash
curl -X GET "http://localhost:8080/instance/connectionState/econdo" \
  -H "apikey: sua_api_key_segura_aqui"
```

**Resposta esperada:**
```json
{
  "instance": "econdo",
  "state": "open"
}
```

---

## 📝 Arquivos Modificados

Os seguintes arquivos foram atualizados para usar a Evolution API:

1. **`config/config.php`**
   - Novas constantes: `EVOLUTION_API_URL`, `EVOLUTION_API_KEY`, `EVOLUTION_INSTANCE_NAME`

2. **`app/helpers/WhatsAppService.php`**
   - Reescrito para usar endpoints da Evolution API
   - Novos métodos: `getInstanceStatus()`, `getQRCode()`

3. **`app/models/SystemSetting.php`**
   - Atualizado `getWhatsAppConfig()` para retornar configurações da Evolution API
   - Novos métodos: `getEvolutionApiUrl()`, `setEvolutionApiUrl()`

4. **`admin/settings.php`**
   - Interface atualizada com campos da Evolution API
   - Instruções de configuração atualizadas

5. **`database/schema.sql`**
   - Configurações iniciais alteradas para Evolution API

---

## ⚠️ Problemas Comuns

### Erro: "WhatsApp não está habilitado ou configurado"

**Solução:**
1. Verifique se as configurações estão salvas no banco
2. Confirme que `whatsapp_enabled = 1`
3. Verifique se API Key e Instance Name estão preenchidos

### Erro: "Connection refused" ou timeout

**Solução:**
1. Verifique se a Evolution API está rodando: `docker ps`
2. Teste a conexão: `curl http://localhost:8080`
3. Verifique o firewall

### Mensagens não são enviadas

**Solução:**
1. Verifique o status da instância (deve estar "open")
2. Confirme que o WhatsApp está conectado
3. Verifique os logs da Evolution API: `docker logs evolution-api`
4. Teste com um número válido (com código do país)

### QR Code não aparece

**Solução:**
1. A instância pode já estar conectada
2. Verifique o status: `GET /instance/connectionState/{instanceName}`
3. Se necessário, desconecte e reconecte

---

## 🔙 Rollback (Reverter para Meta Cloud API)

Se precisar reverter para a Meta Cloud API:

1. **Restaurar configurações antigas no banco:**

```sql
DELETE FROM system_settings WHERE setting_key IN ('evolution_api_url', 'evolution_api_key', 'evolution_instance_name');

INSERT INTO system_settings (setting_key, setting_value, description) VALUES
('whatsapp_api_token', 'seu_token_meta', 'Token de acesso da API do WhatsApp Business'),
('whatsapp_phone_id', 'seu_phone_id', 'ID do telefone do WhatsApp Business');
```

2. **Reverter arquivos do código** (use controle de versão Git)

---

## 📚 Documentação Adicional

- [Evolution API - Documentação](https://doc.evolution-api.com/)
- [Configuração Detalhada](EVOLUTION_API_CONFIG.md)
- [Templates de Mensagens](WHATSAPP_TEMPLATES.md)

---

## ✅ Checklist de Migração

- [ ] Evolution API instalada e rodando
- [ ] Instância criada e conectada
- [ ] Script de migração SQL executado
- [ ] Configurações atualizadas no painel admin
- [ ] Teste de conexão realizado com sucesso
- [ ] Primeira mensagem enviada e recebida
- [ ] Documentação revisada

---

**Data da Migração:** 22/10/2025  
**Versão do Sistema:** E-Condo Packages v1.0  
**Versão da Evolution API:** Latest
