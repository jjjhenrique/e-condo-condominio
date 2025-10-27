@echo off
echo ========================================
echo Iniciando Evolution API
echo ========================================
echo.

REM Verificar se Docker está instalado
docker --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ERRO: Docker nao esta instalado!
    echo Instale o Docker Desktop: https://www.docker.com/products/docker-desktop
    pause
    exit /b 1
)

echo Docker detectado!
echo.

REM Verificar se o container já existe
docker ps -a | findstr evolution-api >nul 2>&1
if %errorlevel% equ 0 (
    echo Container evolution-api encontrado. Iniciando...
    docker start evolution-api
    if %errorlevel% equ 0 (
        echo.
        echo ========================================
        echo Evolution API iniciada com sucesso!
        echo ========================================
        echo.
        echo URL: http://localhost:8080
        echo Documentacao: http://localhost:8080/docs
        echo.
        echo Configure no painel admin:
        echo - URL: http://localhost:8080
        echo - API Key: B6D711FCDE4D4FD5936544120E713976
        echo.
        pause
        exit /b 0
    ) else (
        echo ERRO ao iniciar container!
        pause
        exit /b 1
    )
)

echo Container nao encontrado. Criando novo...
echo.

REM Criar e iniciar novo container
docker run -d ^
  --name evolution-api ^
  -p 8080:8080 ^
  -e AUTHENTICATION_API_KEY=B6D711FCDE4D4FD5936544120E713976 ^
  -e SERVER_URL=http://localhost:8080 ^
  atendai/evolution-api:latest

if %errorlevel% equ 0 (
    echo.
    echo ========================================
    echo Evolution API criada e iniciada!
    echo ========================================
    echo.
    echo Aguarde 10 segundos para a API inicializar...
    timeout /t 10 /nobreak >nul
    echo.
    echo URL: http://localhost:8080
    echo Documentacao: http://localhost:8080/docs
    echo.
    echo IMPORTANTE - Configure no painel admin:
    echo 1. Acesse: http://localhost/e-condo/admin/settings.php
    echo 2. URL da Evolution API: http://localhost:8080
    echo 3. API Key: B6D711FCDE4D4FD5936544120E713976
    echo 4. Nome da Instancia: econdo
    echo 5. Habilite o WhatsApp
    echo 6. Salve as configuracoes
    echo.
    echo Proximo passo: Criar instancia e conectar WhatsApp
    echo Execute: create-instance.bat
    echo.
) else (
    echo.
    echo ERRO ao criar container!
    echo Verifique se a porta 8080 esta disponivel.
    echo.
)

pause
