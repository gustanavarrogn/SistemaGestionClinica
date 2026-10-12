# MediControl · Fase 2 · Integrante 5

El sistema tiene **tres roles de acceso**: `admin`, `recepcionista` y `paciente`. Las doctoras figuran como personal médico para asignar citas, pero **no inician sesión** en esta fase.

## Cómo abrirlo en Windows

1. Descomprime y entra a la carpeta `MediControl` que contiene `artisan`.
2. Enciende MySQL (XAMPP) y revisa la conexión en `.env`.
3. Ejecuta `PREPARAR_EN_WINDOWS.bat` para instalar lo pendiente y cargar datos de ejemplo. No ejecutes `migrate:fresh`.
4. Ejecuta `INICIAR_MEDICONTROL.bat` y abre `http://127.0.0.1:8000/login`.

## Credenciales para pruebas

Están únicamente en `DATOS_DE_PRUEBA.txt` (no aparecen en el login). Si las cuentas existían, el seeder no cambia contraseñas anteriores.

## Cambios aplicados

- Se retiró la caja de accesos rápidos del login. Se conserva el botón para ver la contraseña.
- Se mantienen los tres roles de la Fase 1 y las vistas correspondientes.
- Ya no se crean accesos para doctores, solo sus fichas para asignarles citas.
- `php artisan db:seed` desactiva antiguas cuentas vinculadas al personal médico, si existían, sin eliminar doctores ni citas.
- El administrador y recepción siguen pudiendo gestionar estados de citas con las restricciones existentes.

## Comprobaciones mínimas

- Inicio y cierre de sesión con los tres roles.
- Registro público de pacientes y formulario de contraseña.
- CRUD de pacientes y doctores, sin login médico.
- Solicitud de cita, confirmación por administración/recepción y consulta de estado por paciente.
- Acceso restringido de recepción a la información clínica.
