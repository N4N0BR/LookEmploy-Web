@echo off
title Atualizar Autoload - LookEmploy
color 0E

echo ==========================================
echo   ATUALIZANDO AUTOLOAD DO COMPOSER
echo ==========================================
echo.

cd /d "%~dp0api_chat"

echo Regenerando autoload...
echo.

composer dump-autoload

if errorlevel 1 (
    echo.
    echo [ERRO] Falha ao atualizar autoload
    echo.
    echo Tente executar manualmente:
    echo cd api_chat
    echo composer dump-autoload
    pause
    exit /b 1
)

echo.
echo ==========================================
echo   AUTOLOAD ATUALIZADO COM SUCESSO!
echo ==========================================
echo.
echo Agora você pode iniciar o servidor:
echo   iniciar_chat_seguro.bat
echo.

pause
