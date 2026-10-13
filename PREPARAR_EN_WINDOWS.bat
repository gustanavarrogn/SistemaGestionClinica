@echo off
setlocal
cd /d "%~dp0"
if not exist artisan (
  echo ERROR: Falta artisan. Debes ejecutar este archivo dentro de MediControl.
  pause
  exit /b 1
)
where php >nul 2>&1
if errorlevel 1 (
  echo PHP no esta agregado a PATH. Revisa XAMPP y Composer.
  pause
  exit /b 1
)
if not exist .env (
  copy .env.example .env >nul
  echo Configuracion .env creada. Revisa DB_PASSWORD si tu MySQL requiere clave.
)
if not exist vendor\autoload.php (
  echo Instalando dependencias PHP...
  call composer install
  if errorlevel 1 goto error
)
findstr /B /C:"APP_KEY=base64:" .env >nul 2>&1
if errorlevel 1 (
  php artisan key:generate
  if errorlevel 1 goto error
)
echo.
echo IMPORTANTE: Enciende MySQL de XAMPP y crea la base 'medicontrol'.
choice /C SN /N /M "MySQL esta encendido y la base medicontrol existe? [S/N] "
if errorlevel 2 goto cancel
php artisan migrate --force
if errorlevel 1 goto error
php artisan db:seed --force
if errorlevel 1 goto error
php artisan optimize:clear
if errorlevel 1 goto error
echo.
echo Listo. Datos demo cargados. Abre INICIAR_MEDICONTROL.bat
pause
exit /b 0
:cancel
echo Enciende MySQL, crea la base y ejecuta este instalador nuevamente.
pause
exit /b 1
:error
echo Error durante la configuracion. Revisa los mensajes de la terminal.
pause
exit /b 1
