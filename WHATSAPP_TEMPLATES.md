# Templates de Mensagens WhatsApp - E-Condo Packages

Este documento contém todos os templates de mensagens que o sistema E-Condo Packages envia via Evolution API (WhatsApp).

---

## 📋 Lista de Templates

O sistema possui **2 templates principais** de notificação:

1. **Encomenda Recebida** - Notifica quando uma encomenda chega na portaria
2. **Encomenda Retirada** - Confirma quando a encomenda foi retirada

---

## 1️⃣ Template: Encomenda Recebida

### **Nome do Template:** `package_received`

### **Categoria:** UTILITY (Serviço)

### **Idioma:** Português (pt_BR)

### **Conteúdo:**

```

Olá *{{1}}*!

📦 Sua encomenda chegou na portaria.

*Código:* {{2}}

🔗 Ver QR Code: {{3}}

Apresente o código ou QR Code para retirar.
```

### **Variáveis:**
- `{{1}}` - Nome do condômino (ex: João)
- `{{2}}` - Código de rastreamento (ex: PKG000123456)
- `{{3}}` - URL do QR Code (ex: https://seudominio.com/e-condo/api/qrcode.php?code=PKG000123456)

### **Exemplo Real:**
```
📦 Encomenda Recebida

Olá João!

Sua encomenda chegou na portaria.

Código: PKG000123456

🔗 Ver QR Code: https://seudominio.com/e-condo/api/qrcode.php?code=PKG000123456

Apresente o código ou QR Code para retirar.
```

### **Quando é Enviado:**
- Quando o porteiro registra o recebimento de uma encomenda no sistema
- Automaticamente após o cadastro da encomenda

### **Objetivo:**
Notificar o condômino que sua encomenda chegou e fornecer o código de retirada

---

## 2️⃣ Template: Encomenda Retirada

### **Nome do Template:** `package_picked_up`

### **Categoria:** UTILITY (Serviço)

### **Idioma:** Português (pt_BR)

### **Conteúdo:**

```
Olá {{1}}, sua encomenda com código *{{2}}* foi retirada com sucesso.

Obrigado!
```

### **Variáveis:**
- `{{1}}` - Nome do condômino (ex: João)
- `{{2}}` - Código de rastreamento (ex: PKG000123456)

### **Exemplo Real:**
```
Olá João, sua encomenda com código PKG000123456 foi retirada com sucesso.

Obrigado!
```

### **Quando é Enviado:**
- Quando o porteiro confirma a retirada da encomenda no sistema
- Automaticamente após registrar a retirada

### **Objetivo:**
Confirmar ao condômino que a encomenda foi retirada com sucesso

---

## 📝 Informações Adicionais para Aprovação

### **Sobre o Sistema:**
- **Nome:** E-Condo Packages
- **Tipo:** Sistema de Gestão de Encomendas para Condomínios
- **Propósito:** Controlar recebimento e retirada de encomendas na portaria

### **Uso das Mensagens:**
- **Frequência:** Enviadas apenas quando há eventos reais (recebimento/retirada)
- **Opt-in:** Condôminos cadastrados automaticamente consentem ao fornecer telefone
- **Opt-out:** Condôminos podem solicitar remoção do cadastro a qualquer momento

### **Conformidade:**
- ✅ Mensagens transacionais (não marketing)
- ✅ Conteúdo relevante e útil para o destinatário
- ✅ Enviadas apenas para números cadastrados
- ✅ Relacionadas a serviço contratado (gestão de encomendas)

---

## 🔧 Configuração Técnica

### **Evolution API - Envio de Mensagens:**

A Evolution API não requer templates pré-aprovados como a Meta Cloud API. As mensagens são enviadas diretamente como texto simples.

#### Endpoint para enviar mensagem de texto:

```
POST {EVOLUTION_API_URL}/message/sendText/{instance_name}
Headers:
  apikey: {sua_api_key}
  Content-Type: application/json
```

#### Payload para Template 1: package_received

```json
{
  "number": "5511999999999",
  "text": "📦 *Encomenda Recebida*\n\nOlá *João*!\n\nSua encomenda chegou na portaria.\n\n*Código:* PKG000123456\n\n🔗 Ver QR Code: https://seudominio.com/e-condo/api/qrcode.php?code=PKG000123456\n\nApresente o código ou QR Code para retirar."
}
```

#### Payload para Template 2: package_picked_up

```json
{
  "number": "5511999999999",
  "text": "Olá João, sua encomenda com código *PKG000123456* foi retirada com sucesso.\n\nObrigado!"
}
```

---

## 📱 Exemplo de Uso via API

### Enviar mensagem de texto com Evolution API:

```bash
curl -X POST "http://localhost:8080/message/sendText/minha_instancia" \
  -H "apikey: sua_api_key_aqui" \
  -H "Content-Type: application/json" \
  -d '{
    "number": "5511999999999",
    "text": "📦 *Encomenda Recebida*\n\nOlá *João*!\n\nSua encomenda chegou na portaria.\n\n*Código:* PKG000123456"
  }'
```

### Enviar imagem (QR Code) com Evolution API:

```bash
curl -X POST "http://localhost:8080/message/sendMedia/minha_instancia" \
  -H "apikey: sua_api_key_aqui" \
  -H "Content-Type: application/json" \
  -d '{
    "number": "5511999999999",
    "mediatype": "image",
    "media": "https://seudominio.com/e-condo/api/generate_qrcode.php?code=PKG000123456",
    "caption": "📱 QR Code para retirada\nCódigo: PKG000123456"
  }'
```

---

## ✅ Checklist para Configuração

Antes de usar o sistema, certifique-se de:

- [ ] Ter a Evolution API instalada e rodando
- [ ] Criar uma instância na Evolution API
- [ ] Obter a API Key global da Evolution API
- [ ] Conectar o WhatsApp escaneando o QR Code
- [ ] Configurar a URL da Evolution API no sistema (config.php)
- [ ] Configurar a API Key no painel administrativo
- [ ] Configurar o nome da instância no painel administrativo
- [ ] Testar o envio de mensagens

---

## 🔗 Links Úteis

- [Evolution API - Documentação Oficial](https://doc.evolution-api.com/)
- [Evolution API - GitHub](https://github.com/EvolutionAPI/evolution-api)
- [Evolution API - Instalação](https://doc.evolution-api.com/v2/pt/install/docker)
- [Evolution API - Endpoints](https://doc.evolution-api.com/v2/pt/endpoints)

---

## 📞 Suporte

Para dúvidas sobre a configuração da Evolution API, consulte a documentação oficial em https://doc.evolution-api.com/ ou acesse o repositório no GitHub.

---

**Documento gerado em:** <?= date('d/m/Y H:i') ?>  
**Sistema:** E-Condo Packages v1.0  
**Autor:** Sistema Automatizado
