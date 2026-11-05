# MediControl · Fase 3 · Integrante 3 – Base de Datos y Modelos

## 1. Migraciones nuevas

| Archivo | Qué hace |
|---|---|
| `2026_10_30_010000_add_archivo_to_appointments_table.php` | Agrega a `appointments`: `archivo_ruta`, `archivo_nombre_original`, `archivo_mime`, `archivo_tamano`, `archivo_subido_en` (todas opcionales). |
| `2026_10_30_020000_add_report_indexes_to_appointments_table.php` | Índices en `fecha`, `status` y (`doctor_id`, `fecha`) para que los reportes sean rápidos. |
| `2026_11_02_010000_create_documentos_table.php` | Crea la tabla `documentos` (ver sección 4). |

Aplicarlas (sin borrar datos):

```bash
php artisan migrate
php artisan storage:link   # una sola vez, para que los archivos subidos tengan URL pública
```

Revertirlas: `php artisan migrate:rollback --step=3`

## 2. Modelo `Appointment` (actualizado)

- Los campos del archivo ya están en `$fillable`.
- `$cita->tieneArchivo()` y `$cita->archivo_url` (URL pública del archivo).
- Scopes para filtrar: `->entreFechas($desde, $hasta)`, `->delDoctor($id)`, `->conEstado('Completada')`.

Ejemplo para quien hace la subida del archivo (controlador):

```php
$archivo = $request->file('archivo');               // validar: 'archivo' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120'
$cita->update([
    'archivo_ruta'            => $archivo->store('citas', 'public'),
    'archivo_nombre_original' => $archivo->getClientOriginalName(),
    'archivo_mime'            => $archivo->getMimeType(),
    'archivo_tamano'          => $archivo->getSize(),
    'archivo_subido_en'       => now(),
]);
```

## 3. Consultas para reportes – `app/Services/ReporteService.php`

Todos los métodos aceptan filtros opcionales `$desde`, `$hasta` (Y-m-d) y, donde aplica, `$doctorId`. Las citas eliminadas (soft delete) no se cuentan.

| Método | Devuelve | Tipo |
|---|---|---|
| `resumenGeneral()` | Total de citas, conteo por estado, % completadas / canceladas / no-show, citas con archivo, pacientes atendidos, total pacientes y doctores | Query Builder + Eloquent |
| `citasPorEstado()` | Total por cada estado (incluye los que tienen 0) | Query Builder |
| `citasPorDoctor()` | Por doctor: total, completadas, canceladas, no-show, pacientes distintos, % completadas | Query Builder (JOIN) |
| `citasPorEspecialidad()` | Total por especialidad | Query Builder (JOIN) |
| `citasPorMes()` | Por mes `YYYY-MM`: total, completadas, canceladas | Query Builder |
| `citasPorDiaSemana()` | Total por día (Domingo…Sábado) | Query Builder |
| `citasPorHora()` | Horas con más citas | Query Builder |
| `pacientesFrecuentes()` | Pacientes con más citas (con completadas y no-show) | Eloquent `withCount` |
| `pacientesNuevosPorMes()` | Pacientes registrados por mes | Query Builder |
| `pacientesPorTipoSangre()` | Pacientes por tipo de sangre | Eloquent |
| `listadoCitas()` | Listado detallado con paciente y doctor (para tabla, PDF o Excel) | Eloquent |
| `citasConArchivo()` | Citas que tienen archivo adjunto | Eloquent |

Uso desde el controlador de reportes:

```php
use App\Services\ReporteService;

public function index(Request $request, ReporteService $reportes)
{
    $desde = $request->input('desde');
    $hasta = $request->input('hasta');

    return view('reportes.index', [
        'resumen'      => $reportes->resumenGeneral($desde, $hasta),
        'porEstado'    => $reportes->citasPorEstado($desde, $hasta),
        'porDoctor'    => $reportes->citasPorDoctor($desde, $hasta),
        'porMes'       => $reportes->citasPorMes($desde, $hasta),
    ]);
}
```

En Blade: `{{ $resumen['total_citas'] }}`, `@foreach ($porDoctor as $fila) {{ $fila->nombre }} – {{ $fila->total }} @endforeach`.

## 4. Tabla `documentos` y modelo `Documento`

Un paciente (y opcionalmente una cita) puede tener **varios** documentos: recetas, resultados de laboratorio, órdenes médicas, imágenes, constancias.

| Columna | Tipo | Nota |
|---|---|---|
| `documento_id` | PK | |
| `paciente_id` | FK → `patients` | obligatorio; si se borra el paciente se borran sus documentos |
| `appointment_id` | FK → `appointments` | opcional; si se borra la cita queda en NULL |
| `subido_por` | FK → `users` | opcional; quién lo subió |
| `tipo` | enum | `Receta`, `Resultado de laboratorio`, `Orden médica`, `Imagen médica`, `Constancia`, `Otro` (por defecto `Otro`) |
| `titulo`, `descripcion` | texto | |
| `ruta`, `nombre_original`, `mime`, `tamano` | datos del archivo | el archivo físico va en `storage/app/public/documentos` |
| `created_at`, `updated_at`, `deleted_at` | | borrado suave (soft delete) |

**Relaciones:** `$paciente->documentos`, `$cita->documentos`, `$usuario->documentosSubidos`, `$documento->patient`, `$documento->appointment`, `$documento->subidoPor`.

**Ayudas del modelo:** `$documento->url`, `$documento->tamano_legible` ("244 KB"), `esPdf()`, `esImagen()`, `archivoExiste()`, y la constante `Documento::TIPOS` para llenar un `<select>`.

**Filtros:** `Documento::delPaciente($id)->delTipo('Receta')->deLaCita($citaId)->entreFechas($desde, $hasta)->get()`.

**Borrado:** `$documento->delete()` lo oculta y conserva el archivo; `$documento->forceDelete()` lo elimina de la base y borra el archivo del disco.

Ejemplo para quien programe la subida (controlador):

```php
$request->validate([
    'archivo' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
    'tipo'    => 'required|in:'.implode(',', \App\Models\Documento::TIPOS),
    'titulo'  => 'required|string|max:150',
]);

$archivo = $request->file('archivo');
Documento::create([
    'paciente_id'     => $paciente->paciente_id,
    'appointment_id'  => $request->input('appointment_id'),   // opcional
    'subido_por'      => auth()->id(),
    'tipo'            => $request->tipo,
    'titulo'          => $request->titulo,
    'descripcion'     => $request->descripcion,
    'ruta'            => $archivo->store('documentos', 'public'),
    'nombre_original' => $archivo->getClientOriginalName(),
    'mime'            => $archivo->getMimeType(),
    'tamano'          => $archivo->getSize(),
]);
```

**Reportes nuevos en `ReporteService`:** `documentosPorTipo()` (incluye tipos con 0 y bytes usados), `documentosPorMes()`, `documentosPorPaciente()`, `listadoDocumentos()`, y `resumenGeneral()` ahora incluye `total_documentos`.
