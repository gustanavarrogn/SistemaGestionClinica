# MediControl – Sistema de Gestión de Citas Médicas

Aplicación web para gestionar pacientes, personal médico y citas de una clínica. Proyecto de la asignatura **DWS901 – Desarrollo de Aplicaciones Web con Software Interpretado en el Servidor**, Fase 2 (núcleo del Back-End).

## Tecnologías

| Componente | Tecnología |
|---|---|
| Framework | Laravel 12 (PHP 8.2+) |
| Base de datos | MySQL / MariaDB (XAMPP) |
| Autenticación | Laravel Breeze |
| Vistas | Blade + Tailwind CSS, compilado con Vite |
| Control de versiones | Git / GitHub |

## Equipo y responsabilidades

| Integrante | Responsabilidad |
|---|---|
| #1 | Configuración del entorno y estructura del proyecto |
| #2 | Servidor, ruteo y lógica de negocio |
| #3 | Base de datos y persistencia (migraciones, modelos, CRUD) |
| #4 | Autenticación y control de acceso por roles |
| #5 | Vistas Blade y conexión con el Back-End |
| #6 | Control de versiones, pruebas (QA), documentación y entrega |

---

## 1. Requisitos previos

- **XAMPP** con PHP **8.2 o superior** (incluye MySQL/MariaDB y phpMyAdmin).
- **Composer** 2.x — https://getcomposer.org
- **Node.js** 18 o superior (incluye npm).
- **Git**.

Comprueba en una terminal:

```bash
php -v
composer -V
node -v
```

### Configuración de PHP en XAMPP

1. Agrega `C:\xampp\php` a la variable de entorno **Path** de Windows (si `php -v` dice que no se reconoce).
2. Abre `C:\xampp\php\php.ini` (XAMPP → Apache → Config → PHP (php.ini)) y quita el `;` de estas líneas:

   ```ini
   extension=zip
   extension=intl
   extension=gd
   ```

   Sin `extension=zip`, `composer install` falla con *"The zip extension and unzip/7z commands are both missing"*.
3. Cierra y vuelve a abrir la terminal.

## 2. Instalación

### 2.1 Clonar el repositorio

```bash
git clone https://github.com/gustanavarrogn/SistemaGestionClinica.git
cd SistemaGestionClinica
```

> **Recomendado:** clona el proyecto **fuera de OneDrive** (por ejemplo en `C:\proyectos`). OneDrive bloquea carpetas como `bootstrap/cache` y vuelve lentos `composer install` y `npm install`.

### 2.2 Crear la base de datos

1. Abre el **XAMPP Control Panel** y enciende **Apache** y **MySQL**.
2. Entra a `http://localhost/phpmyadmin` → **Nueva** → nombre `medicontrol` → **Crear** (solo la base vacía).

### 2.3 Configurar el archivo `.env`

```bash
copy .env.example .env
```

Edita la conexión según tu MySQL:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306        # usa 3307 si cambiaste el puerto en XAMPP
DB_DATABASE=medicontrol
DB_USERNAME=root
DB_PASSWORD=
```

> El archivo `.env` es personal: **no se sube a GitHub**.

### 2.4 Instalar dependencias y preparar la base

**Opción A – Instalador automático (Windows):**

```bash
.\PREPARAR_EN_WINDOWS.bat
```

Instala dependencias PHP, genera la clave, ejecuta migraciones y carga datos de prueba. Cuando pregunte si MySQL está encendido, responde **S**.

**Opción B – Manual:**

```bash
composer install
php artisan key:generate
php artisan migrate
php artisan db:seed
```

### 2.5 Compilar los estilos

```bash
npm install
npm run build
```

> En **PowerShell**, si aparece *"la ejecución de scripts está deshabilitada"*, usa `npm.cmd install` y `npm.cmd run build`, o ejecuta una vez:
> `Set-ExecutionPolicy -Scope CurrentUser RemoteSigned`

## 3. Ejecución

```bash
php artisan serve
```

(o doble clic en `INICIAR_MEDICONTROL.bat`)

Abre **http://127.0.0.1:8000/login**. Deja la terminal abierta mientras uses la aplicación.

Para las siguientes veces solo se necesita: encender **MySQL** en XAMPP y ejecutar `php artisan serve`.

> ⚠️ **No ejecutes `php artisan migrate:fresh`**: borra todas las tablas y los datos registrados.

## 4. Usuarios de prueba

Creados por el seeder (`php artisan db:seed`). Son solo para entorno local.

| Rol | Correo | Contraseña |
|---|---|---|
| Administrador | admin@medicontrol.test | Demo12345! |
| Recepcionista | recepcion@medicontrol.test | Demo12345! |
| Paciente | paciente@medicontrol.test | Demo12345! |

El seeder también crea dos doctoras (Dra. Valeria Rivas y Dra. Elena Morales) y dos citas de ejemplo. Las doctoras **no inician sesión**: se registran como personal médico para asignarles citas.

## 5. Roles y permisos

| Módulo | Administrador | Recepcionista | Paciente |
|---|:---:|:---:|:---:|
| Panel de administración | ✅ | — | — |
| Pacientes (crear, ver, editar) | ✅ | ✅ | — |
| Doctores (CRUD) | ✅ | — | — |
| Usuarios y roles | ✅ | — | — |
| Agenda del día / cambiar estado de citas | ✅ | ✅ | — |
| Agendar citas | ✅ | ✅ | ✅ (solicitud) |
| Mis citas / cancelar su cita | — | — | ✅ |

Las rutas están protegidas con el middleware `role`. Si un usuario intenta entrar a una sección sin permiso, recibe **403 – No tiene permiso para acceder a esta sección**.

## 6. Reglas de negocio implementadas

- Horario de atención de **8:00 a 17:00**, en bloques de **30 minutos** (última cita 16:30).
- La clínica **no atiende domingos**.
- No se pueden agendar citas en fechas u horas **pasadas**.
- Una doctora **no puede tener dos citas a la misma hora**.
- Un paciente **no puede tener dos citas el mismo día**.
- Las citas solicitadas por un paciente o por recepción quedan **Pendientes** hasta que recepción o administración las confirmen; las que crea el administrador quedan **Confirmadas**.
- Flujo de estados: Pendiente → Confirmado → En proceso → Completada (o Cancelada / No-show).
- El paciente solo puede **cancelar con al menos 12 horas** de anticipación.
- No se puede desactivar una doctora con **citas activas**.
- El paciente ve el estado de sus citas actualizado automáticamente cada 25 segundos.
- El registro público crea la cuenta **y** la ficha de paciente.

## 7. Pruebas

### Pruebas automatizadas

```bash
php artisan test
```

Incluye pruebas de acceso por rol, registro público, seeder y gestión de doctoras/usuarios (`tests/Feature/MediControlFlujoTest.php` y `tests/Feature/MediControlRolesYAgendaTest.php`).

Las pruebas usan una base SQLite en memoria (no tocan `medicontrol`). Requieren activar `extension=pdo_sqlite` y `extension=sqlite3` en `php.ini`.

### Pruebas manuales (Fase 2)

Se ejecutaron 29 casos de prueba sobre login, roles, gestión de pacientes, CRUD de doctores, reglas de agenda y registro. Resultados y evidencias en el documento PDF de entrega.

## 8. Solución de problemas

| Error | Solución |
|---|---|
| `could not find driver` al ejecutar `php artisan test` | Activa `extension=pdo_sqlite` y `extension=sqlite3` en `php.ini`. |
| `Port 3306 in use` al encender MySQL | Otro MySQL usa el puerto. Cambia XAMPP a 3307 y pon `DB_PORT=3307` en `.env`. |
| `The zip extension and unzip/7z commands are both missing` | Activa `extension=zip` en `php.ini`. |
| `bootstrap\cache directory must be present and writable` | Ejecuta `attrib -r bootstrap\cache /s /d` o mueve el proyecto fuera de OneDrive. |
| `npm.ps1 no se puede cargar…` | Usa `npm.cmd` o cambia la política de ejecución (ver 2.5). |
| `Vite manifest not found` | Ejecuta `npm run build`. |
| `SQLSTATE[HY000] [2002]` / no conecta a la BD | Verifica que MySQL esté encendido y que `DB_PORT` coincida con el de XAMPP. |

## 9. Estructura principal

```
app/Http/Controllers/   Controladores (citas, pacientes, doctores, usuarios, auth)
app/Http/Middleware/    RoleMiddleware y EnsureActiveUser
app/Models/             User, Patient, Doctor, Appointment
database/migrations/    Tablas users, patients, doctors, appointments y ajustes
database/seeders/       Datos de prueba
resources/views/        Vistas Blade por módulo
routes/web.php          Rutas protegidas por rol
tests/Feature/          Pruebas automatizadas
```

## 10. Limitaciones conocidas y pendientes

- Algunos mensajes del sistema (por ejemplo, el error de inicio de sesión) aparecen en inglés porque `APP_LOCALE=en`.
- El botón "Eliminar" de doctores realiza una **desactivación** (soft delete) para conservar el historial.
- Cuando el paciente agenda o cancela una cita, se le redirige al listado general `/citas` en lugar de "Mis citas".
