@echo off
cd /d "%~dp0"
if not exist artisan (
 echo No se encontro artisan. Abre la carpeta correcta del proyecto.
 pause
 exit /b 1
)
echo MediControl iniciado en http://127.0.0.1:8000/login
echo Deja abierta esta ventana mientras usas la aplicacion.
php artisan serve --host=127.0.0.1 --port=8000
pause
