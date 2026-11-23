#!/usr/bin/env python3
"""Integra únicamente el menú/enlaces del integrante 5 en un clon actualizado.

No sustituye controladores, migraciones, rutas, modelos ni configuraciones.
Ejecutar en la raíz del proyecto: py integrar_vistas.py  (o python integrar_vistas.py)
"""
from pathlib import Path
import shutil
import sys
from datetime import datetime

ROOT = Path(__file__).resolve().parent
LAYOUT = Path('resources/views/layouts/medicontrol.blade.php')
FICHA = Path('resources/views/pacientes/show.blade.php')
MIS_CITAS = Path('resources/views/paciente/mis-citas.blade.php')

PARCHES = {
    LAYOUT: [
        (
            "<link href=\"{{ asset('css/medicontrol.css') }}\" rel=\"stylesheet\">",
            "<link href=\"{{ asset('css/medicontrol.css') }}\" rel=\"stylesheet\">\n    @stack('styles')",
            "@stack('styles')",
        ),
        (
            "<li class=\"nav-item\"><a class=\"nav-link {{ request()->routeIs('recepcionista.agenda') ? 'active' : '' }}\" href=\"{{ route('recepcionista.agenda') }}\">Agenda del día</a></li>",
            "<li class=\"nav-item\"><a class=\"nav-link {{ request()->routeIs('recepcionista.agenda') ? 'active' : '' }}\" href=\"{{ route('recepcionista.agenda') }}\">Agenda del día</a></li>\n"
            "                            @if(auth()->user()->rol === 'admin')\n"
            "                            <li class=\"nav-item\"><a class=\"nav-link {{ request()->routeIs('reportes.*') ? 'active' : '' }}\" href=\"{{ route('reportes.index') }}\">Reportes</a></li>\n"
            "                            @endif",
            "request()->routeIs('reportes.*')",
        ),
    ],
    FICHA: [
        (
            "<a class=\"btn btn-primary\" href=\"{{ route('pacientes.edit', $paciente->paciente_id) }}\">Editar ficha</a>",
            "<a class=\"btn btn-primary\" href=\"{{ route('pacientes.edit', $paciente->paciente_id) }}\">Editar ficha</a>"
            "<a class=\"btn btn-outline-primary\" href=\"{{ route('documentos.index', $paciente->paciente_id) }}\">Ver documentos</a>",
            "route('documentos.index', $paciente->paciente_id)",
        ),
    ],
    MIS_CITAS: [
        (
            "<a class=\"btn btn-primary\" href=\"{{ route('citas.create') }}\">+ Solicitar cita</a>",
            "<div class=\"d-flex flex-wrap gap-2\">\n"
            "        @if($paciente)\n"
            "            <a class=\"btn btn-outline-primary\" href=\"{{ route('documentos.index', $paciente->paciente_id) }}\">Mis documentos</a>\n"
            "        @endif\n"
            "        <a class=\"btn btn-primary\" href=\"{{ route('citas.create') }}\">+ Solicitar cita</a>\n"
            "    </div>",
            "Mis documentos</a>",
        ),
    ],
}

def integrar():
    if not (ROOT / 'artisan').is_file() or not (ROOT / 'routes/web.php').is_file():
        sys.exit("ERROR: ubicá el contenido del ZIP en la raíz del proyecto Laravel, junto a 'artisan'.")
    rutas = (ROOT / 'routes/web.php').read_text(encoding='utf-8')
    faltan = [ruta for ruta in ('documentos.store', 'documentos.index', 'documentos.descargar', 'documentos.destroy', 'reportes.index', 'reportes.pdf', 'reportes.excel') if ruta not in rutas]
    if faltan:
        sys.exit('ERROR: el clon no contiene aún las rutas necesarias: ' + ', '.join(faltan) + '. Actualizá desde GitHub antes de integrar.')

    nuevos = {}
    for archivo, cambios in PARCHES.items():
        destino = ROOT / archivo
        if not destino.is_file():
            sys.exit(f'ERROR: no existe {archivo}. No se modificó ningún archivo.')
        original = destino.read_text(encoding='utf-8')
        cambiado = original
        for ancla, remplazo, marca in cambios:
            if marca in cambiado:
                print(f'  Ya integrado: {archivo} — {marca}')
                continue
            if cambiado.count(ancla) != 1:
                sys.exit(f'ERROR: no se encontró el punto de inserción esperado en {archivo}. No se modificó ningún archivo. Revisar manualmente.')
            cambiado = cambiado.replace(ancla, remplazo, 1)
        if cambiado != original:
            nuevos[destino] = (original, cambiado)

    if not nuevos:
        print('Integración existente: no hay cambios que aplicar.')
        return

    respaldo = ROOT / '.fase3_backup' / datetime.now().strftime('%Y%m%d_%H%M%S')
    for destino, (original, cambiado) in nuevos.items():
        relativa = destino.relative_to(ROOT)
        copia = respaldo / relativa
        copia.parent.mkdir(parents=True, exist_ok=True)
        copia.write_text(original, encoding='utf-8')
    for destino, (original, cambiado) in nuevos.items():
        destino.write_text(cambiado, encoding='utf-8')
        print('  Modificado:', destino.relative_to(ROOT))
    exclude = ROOT / '.git/info/exclude'
    if exclude.is_file():
        contenido = exclude.read_text(encoding='utf-8')
        if '.fase3_backup/' not in contenido:
            with exclude.open('a', encoding='utf-8') as f:
                f.write('\n.fase3_backup/\n')
    print('\nLISTO: menú/enlaces integrados sin alterar controladores ni rutas.')
    print('Respaldo local en:', respaldo.relative_to(ROOT))
    print('Ejecutá: php artisan optimize:clear && php artisan view:cache && php artisan route:list')

if __name__ == '__main__':
    integrar()
