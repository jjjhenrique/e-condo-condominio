# Como Habilitar a Extensão GD no PHP (Opcional)

A extensão GD do PHP permite manipulação avançada de imagens. O sistema E-Condo funciona **sem ela**, mas você pode habilitá-la para recursos futuros.

## ✅ Sistema Atual

O sistema **NÃO REQUER** a extensão GD. Os QR Codes são gerados usando a API do Google Charts, que funciona perfeitamente sem dependências locais.

## 📝 Como Habilitar (Opcional)

### No XAMPP (Windows):

1. **Abra o arquivo php.ini:**
   - Localize: `C:\xampp\php\php.ini`
   - Ou acesse pelo XAMPP Control Panel: Config → PHP (php.ini)

2. **Procure pela linha:**
   ```ini
   ;extension=gd
   ```

3. **Remova o ponto e vírgula:**
   ```ini
   extension=gd
   ```

4. **Salve o arquivo**

5. **Reinicie o Apache:**
   - No XAMPP Control Panel, clique em "Stop" e depois "Start" no Apache

6. **Verifique se funcionou:**
   - Crie um arquivo `test_gd.php` com:
   ```php
   <?php
   if (extension_loaded('gd')) {
       echo "✅ Extensão GD está habilitada!";
       echo "<br>Versão: " . gd_info()['GD Version'];
   } else {
       echo "❌ Extensão GD não está habilitada";
   }
   ```
   - Acesse: `http://localhost/test_gd.php`

### No Linux (Ubuntu/Debian):

```bash
# Instalar extensão GD
sudo apt-get update
sudo apt-get install php-gd

# Reiniciar Apache
sudo service apache2 restart

# Ou reiniciar PHP-FPM
sudo service php8.1-fpm restart
```

### No Linux (CentOS/RHEL):

```bash
# Instalar extensão GD
sudo yum install php-gd

# Reiniciar Apache
sudo systemctl restart httpd
```

## 🔍 Verificar se GD está Habilitada

Execute no terminal:
```bash
php -m | grep gd
```

Ou crie um arquivo `phpinfo.php`:
```php
<?php phpinfo(); ?>
```

Procure por "gd" na página.

## ⚠️ Observação

**Você NÃO precisa habilitar a extensão GD para usar o E-Condo Packages.** O sistema funciona perfeitamente sem ela, usando a API do Google Charts para gerar QR Codes.

A extensão GD é útil apenas se você quiser:
- Gerar QR Codes offline (sem internet)
- Manipular imagens localmente
- Adicionar recursos avançados de imagem no futuro

## 📚 Mais Informações

- [Documentação oficial do GD](https://www.php.net/manual/pt_BR/book.image.php)
- [Google Charts QR Code API](https://developers.google.com/chart/infographics/docs/qr_codes)
