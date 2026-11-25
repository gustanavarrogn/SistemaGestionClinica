# Release Notes — MediControl

## Version 1.0 — Fase 4 (05 de diciembre de 2026)

Version final del sistema, lista para produccion.

### Correcciones de esta fase

**Exportacion a Excel**
- Problema: el reporte en CSV se abria con todos los datos en una sola columna.
- Causa: el archivo usaba coma como separador y Excel en espanol espera punto y coma.
- Solucion: se cambio el separador a punto y coma. El archivo ahora abre en columnas sin ningun paso extra.
- Archivo: `app/Http/Controllers/ReportController.php`

**Validacion del telefono**
- Problema: el campo de telefono aceptaba cualquier texto, incluso letras.
- Solucion: se agrego una validacion en el servidor. El telefono debe tener 8 digitos, con guion opcional. Acepta 7000-1234 y 70001234.
- Se aplica tanto al registrar como al editar un paciente.
- Archivo: `app/Http/Controllers/PatientController.php`

### Pruebas realizadas

| Caso | Resultado |
| --- | --- |
| Descargar el reporte en Excel y abrirlo | Cada dato en su propia celda |
| Descargar el reporte en PDF | Se genera con tablas y acentos correctos |
| Registrar paciente con telefono 7000-1234 | Aceptado |
| Registrar paciente con telefono 70001234 | Aceptado |
| Registrar paciente con telefono 123 | Rechazado con mensaje |
| Registrar paciente con letras en el telefono | Rechazado con mensaje |

### Limitaciones conocidas

- El reporte se exporta como CSV, no como archivo de Excel con formato. Si se abre y se guarda desde Excel, este avisa que el formato no conserva estilos.
- La validacion del telefono usa el formato de El Salvador, de 8 digitos. No acepta numeros internacionales.