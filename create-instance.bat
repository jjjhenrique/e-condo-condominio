@echo off
echo ========================================
echo Criar Instancia WhatsApp - Evolution API
echo ========================================
echo.

set API_KEY=B6D711FCDE4D4FD5936544120E713976
set API_URL=http://localhost:8080
set INSTANCE_NAME=econdo

echo Criando instancia "%INSTANCE_NAME%"...
echo.

curl -X POST "%API_URL%/instance/create" ^
  -H "apikey: %API_KEY%" ^
  -H "Content-Type: application/json" ^
  -d "{\"instanceName\":\"%INSTANCE_NAME%\",\"qrcode\":true}"

echo.
echo.
echo ========================================
echo Obtendo QR Code para conectar WhatsApp
echo ========================================
echo.

timeout /t 3 /nobreak >nul

curl -X GET "%API_URL%/instance/connect/%INSTANCE_NAME%" ^
  -H "apikey: %API_KEY%"

echo.
echo.
echo ========================================
echo Instrucoes:
echo ========================================
echo.
echo 1. Copie o codigo QR Code (base64) da resposta acima
echo 2. Cole em um decodificador online: https://base64.guru/converter/decode/image
echo 3. Ou acesse: http://localhost:8080/instance/connect/%INSTANCE_NAME%
echo 4. Escaneie o QR Code com seu WhatsApp
echo 5. Aguarde a conexao ser estabelecida
echo.
echo Para verificar status:
echo curl -X GET "%API_URL%/instance/connectionState/%INSTANCE_NAME%" -H "apikey: %API_KEY%"
echo.

pause
