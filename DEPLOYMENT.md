# 🚀 Guia de Deploy - E-Condo Packages

Este guia fornece instruções para colocar o sistema em produção.

## 📋 Pré-Deploy Checklist

### Segurança
- [ ] Alterar todas as senhas padrão
- [ ] Gerar novas senhas fortes para usuários
- [ ] Configurar senha do banco de dados
- [ ] Revisar permissões de arquivos
- [ ] Habilitar HTTPS
- [ ] Configurar firewall
- [ ] Desabilitar exibição de erros PHP

### Configurações
- [ ] Ajustar `ENVIRONMENT` para `production` em `config/config.php`
- [ ] Configurar `SITE_URL` correto
- [ ] Configurar credenciais do banco
- [ ] Configurar WhatsApp API (token permanente)
- [ ] Revisar configurações de sessão
- [ ] Configurar timezone correto

### Banco de Dados
- [ ] Criar backup do banco de dados
- [ ] Configurar backup automático
- [ ] Otimizar tabelas
- [ ] Revisar índices
- [ ] Configurar usuário MySQL específico (não usar root)

### Performance
- [ ] Habilitar cache de opcode (OPcache)
- [ ] Configurar compressão GZIP
- [ ] Otimizar imagens (se houver)
- [ ] Configurar cache de navegador
- [ ] Revisar queries lentas

## 🔧 Configurações de Produção

### 1. Configurar Ambiente

Edite `config/config.php`:

```php
// Mudar para production
define('ENVIRONMENT', 'production');

// Desabilitar erros
if (ENVIRONMENT === 'production') {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', BASE_PATH . 'logs/php-errors.log');
}
```

### 2. Configurar Banco de Dados

```php
// Usar credenciais específicas
define('DB_HOST', 'localhost');
define('DB_NAME', 'econdo_packages');
define('DB_USER', 'econdo_user'); // NÃO usar root
define('DB_PASS', 'senha_forte_aqui');
```

Criar usuário MySQL específico:

```sql
CREATE USER 'econdo_user'@'localhost' IDENTIFIED BY 'senha_forte_aqui';
GRANT SELECT, INSERT, UPDATE, DELETE ON econdo_packages.* TO 'econdo_user'@'localhost';
FLUSH PRIVILEGES;
```

### 3. Configurar .htaccess

Descomente as linhas de segurança em `.htaccess`:

```apache
# Forçar HTTPS
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Desabilitar erros
php_flag display_errors Off
php_flag display_startup_errors Off
```

### 4. Permissões de Arquivos

```bash
# Arquivos: 644
find . -type f -exec chmod 644 {} \;

# Diretórios: 755
find . -type d -exec chmod 755 {} \;

# Proteger config
chmod 600 config/config.php

# Criar diretório de logs
mkdir logs
chmod 755 logs
```

### 5. SSL/HTTPS

**Opção A: Let's Encrypt (Gratuito)**

```bash
# Instalar Certbot
sudo apt-get install certbot python3-certbot-apache

# Obter certificado
sudo certbot --apache -d seudominio.com.br
```

**Opção B: Certificado Comercial**

1. Comprar certificado SSL
2. Instalar no Apache
3. Configurar VirtualHost

### 6. Configurar Backup Automático

**Script de Backup (backup.sh)**

```bash
#!/bin/bash
# Backup do banco de dados

DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/backups/econdo"
DB_NAME="econdo_packages"
DB_USER="econdo_user"
DB_PASS="senha_forte_aqui"

# Criar diretório se não existir
mkdir -p $BACKUP_DIR

# Backup do banco
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME | gzip > $BACKUP_DIR/db_$DATE.sql.gz

# Backup dos arquivos
tar -czf $BACKUP_DIR/files_$DATE.tar.gz /var/www/html/e-condo

# Manter apenas últimos 30 dias
find $BACKUP_DIR -name "*.gz" -mtime +30 -delete

echo "Backup concluído: $DATE"
```

**Agendar no Cron (diário às 2h)**

```bash
crontab -e

# Adicionar linha:
0 2 * * * /path/to/backup.sh >> /var/log/econdo-backup.log 2>&1
```

## 🌐 Deploy em Servidor Web

### Apache (Recomendado)

**VirtualHost Configuration**

```apache
<VirtualHost *:80>
    ServerName seudominio.com.br
    ServerAlias www.seudominio.com.br
    
    DocumentRoot /var/www/html/e-condo
    
    <Directory /var/www/html/e-condo>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/econdo-error.log
    CustomLog ${APACHE_LOG_DIR}/econdo-access.log combined
    
    # Redirecionar para HTTPS
    RewriteEngine on
    RewriteCond %{SERVER_NAME} =seudominio.com.br [OR]
    RewriteCond %{SERVER_NAME} =www.seudominio.com.br
    RewriteRule ^ https://%{SERVER_NAME}%{REQUEST_URI} [END,NE,R=permanent]
</VirtualHost>

<VirtualHost *:443>
    ServerName seudominio.com.br
    ServerAlias www.seudominio.com.br
    
    DocumentRoot /var/www/html/e-condo
    
    <Directory /var/www/html/e-condo>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/econdo-ssl-error.log
    CustomLog ${APACHE_LOG_DIR}/econdo-ssl-access.log combined
    
    SSLEngine on
    SSLCertificateFile /etc/letsencrypt/live/seudominio.com.br/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/seudominio.com.br/privkey.pem
</VirtualHost>
```

### Nginx (Alternativa)

```nginx
server {
    listen 80;
    server_name seudominio.com.br www.seudominio.com.br;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name seudominio.com.br www.seudominio.com.br;
    
    root /var/www/html/e-condo;
    index index.php index.html;
    
    ssl_certificate /etc/letsencrypt/live/seudominio.com.br/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/seudominio.com.br/privkey.pem;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    location ~ /\.ht {
        deny all;
    }
    
    location ~* \.(jpg|jpeg|png|gif|ico|css|js)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

## 📊 Monitoramento

### Logs a Monitorar

1. **Logs do Apache/Nginx**
   - `/var/log/apache2/econdo-error.log`
   - `/var/log/apache2/econdo-access.log`

2. **Logs do PHP**
   - `/var/www/html/e-condo/logs/php-errors.log`

3. **Logs do Sistema**
   - Via painel: Administração → Logs do Sistema

### Métricas Importantes

- Taxa de encomendas recebidas/dia
- Taxa de retirada
- Tempo médio de permanência
- Sucesso de envio WhatsApp
- Erros de sistema

## 🔒 Hardening de Segurança

### PHP (php.ini)

```ini
# Desabilitar funções perigosas
disable_functions = exec,passthru,shell_exec,system,proc_open,popen

# Limitar recursos
max_execution_time = 30
max_input_time = 60
memory_limit = 128M
post_max_size = 10M
upload_max_filesize = 10M

# Segurança
expose_php = Off
display_errors = Off
log_errors = On
error_log = /var/log/php/error.log

# Sessões
session.cookie_httponly = 1
session.cookie_secure = 1
session.use_strict_mode = 1
```

### MySQL (my.cnf)

```ini
[mysqld]
# Segurança
local-infile = 0
skip-symbolic-links = 1

# Performance
max_connections = 100
innodb_buffer_pool_size = 256M
query_cache_size = 32M
```

### Firewall

```bash
# UFW (Ubuntu)
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw allow 22/tcp
sudo ufw enable

# Bloquear acesso direto ao MySQL
sudo ufw deny 3306/tcp
```

## 🧪 Testes Pós-Deploy

### Checklist de Testes

- [ ] Login funciona
- [ ] Dashboard carrega corretamente
- [ ] Recebimento de encomenda funciona
- [ ] WhatsApp envia notificações
- [ ] Transferência funciona
- [ ] Retirada funciona
- [ ] Relatórios geram corretamente
- [ ] Exportação Excel funciona
- [ ] Cadastros funcionam (CRUD completo)
- [ ] Logs são registrados
- [ ] HTTPS está ativo
- [ ] Backup automático está configurado

### Teste de Carga

```bash
# Instalar Apache Bench
sudo apt-get install apache2-utils

# Teste de carga (100 requisições, 10 simultâneas)
ab -n 100 -c 10 https://seudominio.com.br/
```

## 📞 Suporte Pós-Deploy

### Contatos Importantes

- **Suporte Técnico**: [seu-email@empresa.com]
- **WhatsApp API**: https://developers.facebook.com/support
- **Hospedagem**: [suporte da hospedagem]

### Documentação

- README.md
- INSTALL.md
- Este arquivo (DEPLOYMENT.md)

## 🔄 Atualizações Futuras

### Processo de Atualização

1. Fazer backup completo
2. Testar em ambiente de staging
3. Colocar site em manutenção
4. Aplicar atualizações
5. Executar migrations (se houver)
6. Testar funcionalidades críticas
7. Retirar modo manutenção
8. Monitorar logs

### Página de Manutenção

Criar `maintenance.html`:

```html
<!DOCTYPE html>
<html>
<head>
    <title>Manutenção - E-Condo</title>
    <style>
        body { font-family: Arial; text-align: center; padding: 50px; }
        h1 { color: #667eea; }
    </style>
</head>
<body>
    <h1>🔧 Sistema em Manutenção</h1>
    <p>Estamos realizando melhorias. Voltaremos em breve!</p>
</body>
</html>
```

## ✅ Checklist Final de Deploy

- [ ] Ambiente de produção configurado
- [ ] Banco de dados criado e importado
- [ ] Credenciais de produção configuradas
- [ ] HTTPS habilitado e funcionando
- [ ] Backup automático configurado
- [ ] Logs configurados
- [ ] Monitoramento ativo
- [ ] Firewall configurado
- [ ] Permissões de arquivos corretas
- [ ] Senhas padrão alteradas
- [ ] WhatsApp API configurado
- [ ] Testes realizados
- [ ] Documentação entregue
- [ ] Treinamento de usuários realizado

---

**Sistema pronto para produção! 🚀**

**Data de Deploy**: _____________
**Responsável**: _____________
**Versão**: 1.0.0
