@echo off
setlocal
cd /d "%~dp0"
if not exist artisan (
 echo No se encontro artisan. Abre la carpeta interna MediControl.
 pause
 exit /b 1
)
echo Verificando rutas y migraciones...
php artisan route:list
php artisan migrate:status
if errorlevel 1 (
 echo No se pudieron consultar las migraciones. Revisa tu archivo .env y MySQL.
 pause
 exit /b 1
)
echo.
echo Para ejecutar pruebas automatizadas usa: php artisan test --filter=MediControlRolesYAgendaTest
pause
