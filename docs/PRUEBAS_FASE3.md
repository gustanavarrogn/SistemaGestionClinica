# MediControl · Fase 3 (Sprint III) · Matriz de pruebas y registro de defectos

Responsable de QA e integración: Integrante #6 (Gustavo Adolfo Navarro Martínez).
Versión evaluada: rama `main` — Release Candidate `v0.3.0-rc1`.

**Estados del resultado**

- **Aprobada:** el caso se ejecutó y cumplió el resultado esperado.
- **Fallida:** el caso no cumple; tiene un defecto asociado.
- **Pendiente:** el caso todavía no se ejecuta en esta versión.

Cada caso se ejecuta en localhost (XAMPP + `php artisan serve`) con los usuarios de `DATOS_DE_PRUEBA.txt`, en Edge/Chrome de escritorio y en vista móvil (DevTools, 390 px).

## 1. Matriz de pruebas de integración

| ID | Módulo | Caso de prueba | Rol | Resultado esperado | Resultado | Defecto |
|---|---|---|---|---|---|---|
| P3-01 | Autenticación | Iniciar sesión con credenciales válidas | Admin, Recepción, Paciente | Entra y redirige al panel de su rol | Aprobada (automatizada) | |
| P3-02 | Autenticación | Iniciar sesión con contraseña incorrecta | Cualquiera | Mensaje de error, no entra | Aprobada (automatizada) | |
| P3-03 | Roles | Paciente intenta abrir `/reportes`, `/doctores` o `/usuarios` | Paciente | 403 (acceso denegado) | Pendiente | |
| P3-04 | Roles | Recepción intenta abrir `/reportes` | Recepción | 403 | Pendiente | |
| P3-05 | Pacientes | Registrar paciente con datos válidos | Admin, Recepción | Se guarda y muestra mensaje de éxito | Pendiente | |
| P3-06 | Pacientes | Registrar paciente con campos obligatorios vacíos | Admin, Recepción | Validación en cliente y en servidor; no se guarda | Pendiente | |
| P3-07 | Pacientes | Buscar paciente por nombre, teléfono o correo | Admin, Recepción | La tabla filtra los resultados | Pendiente | |
| P3-08 | Pacientes | Editar y eliminar paciente | Admin | Cambios guardados; botón eliminar solo para admin | Pendiente | |
| P3-09 | Doctores | Crear, editar y eliminar doctora | Admin | CRUD completo con mensajes | Pendiente | |
| P3-10 | Citas | Agendar cita en horario disponible | Paciente, Recepción | Cita en estado Pendiente | Pendiente | |
| P3-11 | Citas | Agendar cita en horario ocupado o fecha pasada | Paciente | La regla de negocio la rechaza con mensaje | Pendiente | |
| P3-12 | Citas | Cambiar estado (Confirmado, Completada, No-show) | Admin, Recepción | Estado actualizado en la agenda | Pendiente | |
| P3-13 | Citas | Paciente cancela su cita | Paciente | Estado Cancelada; no puede cancelar citas ajenas | Pendiente | |
| P3-14 | Documentos | Subir PDF/JPG/PNG de hasta 5 MB a un paciente | Admin, Recepción | Se guarda y aparece en la lista de documentos | Fallida | D-01 |
| P3-15 | Documentos | Subir archivo .exe o mayor de 5 MB | Admin, Recepción | Rechazo con "Solo se permiten archivos PDF, JPG o PNG" / "no puede pesar más de 5 MB" | Pendiente | |
| P3-16 | Documentos | Ver y descargar documentos propios | Paciente | Lista y descarga funcionan | Fallida | D-01 |
| P3-17 | Documentos | Paciente intenta descargar documento de otro paciente (cambiando el id en la URL) | Paciente | 403 | Pendiente | |
| P3-18 | Documentos | Descargar documento inexistente | Cualquiera | 404 (no error 500) | Pendiente | |
| P3-19 | Documentos | Eliminar documento | Admin / Recepción | Admin elimina; regla de recepción definida | Fallida | D-04 |
| P3-20 | Reportes | Abrir reportes con totales e indicadores | Admin | Total de citas, por estado, por doctora, por mes y tasa de inasistencia | Fallida | D-01 |
| P3-21 | Reportes | Filtrar por rango de fechas; fecha final anterior a la inicial | Admin | Filtra; con rango inválido muestra "La fecha final no puede ser anterior a la inicial." | Fallida | D-01 |
| P3-22 | Reportes | Visualizar gráficos | Admin | Gráficos de citas por estado, doctora y mes | Fallida | D-02 |
| P3-23 | Reportes | Exportar a PDF | Admin | Descarga `reporte_medicontrol_AAAA-MM-DD.pdf` | Fallida | D-01 |
| P3-24 | Reportes | Exportar a Excel (CSV) | Admin | Descarga CSV que abre en Excel con acentos correctos (BOM UTF-8) | Pendiente | |
| P3-25 | Navegación | Acceder a Reportes y Documentos desde el menú | Admin, Recepción | Enlaces visibles según rol | Fallida | D-03 |
| P3-26 | Interfaz | Revisar vistas en escritorio y móvil (390 px) | Todos | Diseño adaptable, sin desbordes | Pendiente | |
| P3-27 | Compatibilidad | Repetir el flujo principal en Chrome y Edge | Todos | Mismo comportamiento | Pendiente | |
| P3-28 | Automatizadas | `php artisan test` | — | Todas las pruebas en verde | Aprobada (34/34, 99 aserciones) | D-08 |

## 2. Registro de defectos

Encontrados por revisión de código de la rama `main` y por la ejecución de `php artisan test` el 8/11/2026.

| ID | Descripción | Prioridad | Responsable | Estado |
|---|---|---|---|---|
| D-01 | Faltan las vistas `reportes/index.blade.php`, `reportes/pdf.blade.php` y `documentos/index.blade.php`. Los controladores las llaman y Laravel responde "View not found" (error 500) en Reportes, Exportar PDF y Documentos. | Crítica | #5 | Abierto |
| D-02 | Reportes sin gráficos. Se sugiere Chart.js con `porEstado`, `porDoctora` y `porMes`, que ya envía `ReportController`. | Alta | #5 | Abierto |
| D-03 | El menú (`layouts/medicontrol.blade.php`) no tiene enlaces a Reportes ni a Documentos. | Media | #5 | Abierto |
| D-04 | Permisos inconsistentes al eliminar documentos: la ruta permite admin y recepción, pero el controlador solo permite admin (recepción recibe 403). | Media | #2 | Abierto |
| D-05 | `ReportController` repite consultas en lugar de usar `ReporteService` (#3), que ya calcula más métricas (por especialidad, pacientes frecuentes, % completadas). | Baja | #2 / #3 | Abierto |
| D-06 | El teléfono del paciente solo valida longitud (`max:30`); no aplica un formato (por ejemplo, la expresión regular `^[267]\d{3}-?\d{4}$` para El Salvador). | Media | #2 | Abierto |
| D-08 | Primera ejecución de `php artisan test`: 16 de 34 pruebas fallaban. Causa: `UserFactory` no definía `is_active` ni `rol`, y `EnsureActiveUser` cerraba la sesión de los usuarios de prueba (302 a /login). Además, `ExampleTest` esperaba 200 en `/` (la raíz redirige al login) y `RegistrationTest` no enviaba `consentimiento`. | Alta | #6 | Corregido (34/34 pruebas aprobadas) |
| D-07 | El commit `8bf0215` subió la carpeta `Claude outputs/` con 3 PDF duplicados de evidencias de la Fase 2. | Baja | #6 | Corregido (se elimina del repositorio y se agrega a `.gitignore`) |

## 3. Correcciones respecto al Sprint II

| Observación / pendiente de la Fase 2 | Corrección en Fase 3 | Commit |
|---|---|---|
| No existía gestión de archivos | Subida de documentos con validación de tipo y peso, descarga protegida y eliminación | `16488a0`, `8660bb1`, `58e8598` |
| No había reportes | Rutas de reportes con totales, inasistencia y exportación a PDF y CSV | `16488a0`, `5f3a6be`, `eca1b78` |
| Faltaban índices para consultas de reportes | Índices en `fecha`, `status` y (`doctor_id`, `fecha`) | `2af8f02` |
| Directorios de almacenamiento sin configurar | Carpetas de `storage` para archivos subidos | `8bbefd0` |
| Descarga de documentos devolvía error 500 si no existía | Responde 404 y valida el acceso por identificador del paciente | `8660bb1` |
| Pruebas automatizadas desactualizadas (16 fallaban) | Factory con `rol` e `is_active`; pruebas de inicio y registro alineadas con las reglas actuales | Fase 3, Integrante #6 |
