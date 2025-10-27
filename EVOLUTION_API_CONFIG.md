# Configuração da Evolution API - E-Condo Packages

Este guia explica como configurar a Evolution API para usar com o sistema E-Condo Packages.

---

## 📋 Pré-requisitos

- Docker e Docker Compose instalados
- Porta 8080 disponível (ou outra porta de sua escolha)
- WhatsApp instalado no celular

---

## 🚀 Instalação da Evolution API

### Opção 1: Docker Compose (Recomendado)

1. Crie um arquivo `docker-compose.yml`:

```yaml
version: '3.8'

services:
  evolution-api:
    image: atendai/evolution-api:latest
    container_name: evolution-api
    ports:
      - "8080:8080"
    environment:
      - SERVER_URL=http://localhost:8080
      - AUTHENTICATION_API_KEY=sua_api_key_segura_aqui
      - DATABASE_ENABLED=true
      - DATABASE_PROVIDER=postgresql
      - DATABASE_CONNECTION_URI=postgresql://postgres:password@postgres:5432/evolution
      - DATABASE_SAVE_DATA_INSTANCE=true
      - DATABASE_SAVE_DATA_NEW_MESSAGE=true
      - DATABASE_SAVE_MESSAGE_UPDATE=true
      - DATABASE_SAVE_DATA_CONTACTS=true
      - DATABASE_SAVE_DATA_CHATS=true
    volumes:
      - evolution_instances:/evolution/instances
    networks:
      - evolution-network
    depends_on:
      - postgres

  postgres:
    image: postgres:15-alpine
    container_name: evolution-postgres
    environment:
      - POSTGRES_USER=postgres
      - POSTGRES_PASSWORD=password
      - POSTGRES_DB=evolution
    volumes:
      - postgres_data:/var/lib/postgresql/data
    networks:
      - evolution-network

volumes:
  evolution_instances:
  postgres_data:

networks:
  evolution-network:
    driver: bridge
```

2. Inicie os containers:

```bash
docker-compose up -d
```

3. Verifique se está rodando:

```bash
docker-compose logs -f evolution-api
```

### Opção 2: Docker Run (Simples)

```bash
docker run -d \
  --name evolution-api \
  -p 8080:8080 \
  -e AUTHENTICATION_API_KEY=sua_api_key_segura_aqui \
  -e SERVER_URL=http://localhost:8080 \
  atendai/evolution-api:latest
```

---

## 🔧 Configuração no E-Condo Packages

### 1. Configurar URL da Evolution API

Edite o arquivo `config/config.php`:

```php
// URL base da Evolution API (sem barra no final)
define('EVOLUTION_API_URL', 'http://localhost:8080');
```

Se a Evolution API estiver em outro servidor:

```php
define('EVOLUTION_API_URL', 'http://seu-servidor.com:8080');
```

### 2. Criar uma Instância

Use a API ou interface web da Evolution API para criar uma instância:

```bash
curl -X POST "http://localhost:8080/instance/create" \
  -H "apikey: sua_api_key_segura_aqui" \
  -H "Content-Type: application/json" \
  -d '{
    "instanceName": "econdo",
    "qrcode": true
  }'
```

### 3. Conectar o WhatsApp

Obtenha o QR Code para conectar:

```bash
curl -X GET "http://localhost:8080/instance/connect/econdo" \
  -H "apikey: sua_api_key_segura_aqui"
```

Escaneie o QR Code retornado com seu WhatsApp.

### 4. Configurar no Painel Administrativo

1. Acesse o painel administrativo do E-Condo
2. Vá em **Configurações > WhatsApp**
3. Preencha os campos:
   - **API Key**: `sua_api_key_segura_aqui`
   - **Nome da Instância**: `econdo`
   - **Habilitar WhatsApp**: Marque a opção
4. Clique em **Salvar**

### 5. Testar a Conexão

No painel administrativo, use a opção "Testar Conexão" para enviar uma mensagem de teste.

---

## 📡 Endpoints Principais da Evolution API

### Verificar Status da Instância

```bash
GET /instance/connectionState/{instanceName}
Headers:
  apikey: sua_api_key
```

### Enviar Mensagem de Texto

```bash
POST /message/sendText/{instanceName}
Headers:
  apikey: sua_api_key
  Content-Type: application/json
Body:
{
  "number": "5511999999999",
  "text": "Sua mensagem aqui"
}
```

### Enviar Imagem/Mídia

```bash
POST /message/sendMedia/{instanceName}
Headers:
  apikey: sua_api_key
  Content-Type: application/json
Body:
{
  "number": "5511999999999",
  "mediatype": "image",
  "media": "https://exemplo.com/imagem.png",
  "caption": "Legenda da imagem"
}
```

---

## 🔒 Segurança

### Recomendações:

1. **Use uma API Key forte**: Gere uma chave aleatória e segura
2. **Não exponha a porta publicamente**: Use um proxy reverso (Nginx/Apache)
3. **Use HTTPS**: Configure SSL/TLS em produção
4. **Firewall**: Restrinja acesso à porta da Evolution API
5. **Backup**: Faça backup regular dos dados da instância

### Exemplo de API Key Segura:

```bash
# Gerar uma API Key aleatória
openssl rand -base64 32
```

---

## 🐛 Troubleshooting

### Problema: Instância não conecta

**Solução:**
1. Verifique se o QR Code foi escaneado corretamente
2. Verifique os logs: `docker logs evolution-api`
3. Reinicie a instância

### Problema: Mensagens não são enviadas

**Solução:**
1. Verifique o status da instância: `GET /instance/connectionState/{instanceName}`
2. Confirme que o número está no formato correto: `5511999999999`
3. Verifique se a API Key está correta

### Problema: Erro de conexão

**Solução:**
1. Verifique se a Evolution API está rodando: `docker ps`
2. Teste a conexão: `curl http://localhost:8080`
3. Verifique o firewall

---

## 📚 Documentação Adicional

- [Evolution API - Documentação Oficial](https://doc.evolution-api.com/)
- [Evolution API - GitHub](https://github.com/EvolutionAPI/evolution-api)
- [Evolution API - Swagger/OpenAPI](http://localhost:8080/docs)

---

## 🆘 Suporte

Para problemas com a Evolution API, consulte:
- Documentação oficial: https://doc.evolution-api.com/
- Issues no GitHub: https://github.com/EvolutionAPI/evolution-api/issues
- Comunidade no Discord/Telegram

---

**Última atualização:** 21/10/2025  
**Sistema:** E-Condo Packages v1.0
