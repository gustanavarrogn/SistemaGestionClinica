<?php

namespace App\Http\Controllers;

use App\Models\Documento;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    const EXTENSIONES_PERMITIDAS = 'pdf,jpg,jpeg,png';
    const PESO_MAXIMO_KB = 5120;

    public function index($pacienteId)
    {
        $paciente = Patient::findOrFail($pacienteId);

        $this->verificarAcceso($paciente);

        $documentos = Documento::with('usuario')
            ->where('paciente_id', $pacienteId)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('documentos.index', compact('paciente', 'documentos'));
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'archivo'        => 'required|file|mimes:'.self::EXTENSIONES_PERMITIDAS.'|max:'.self::PESO_MAXIMO_KB,
            'paciente_id'    => 'required|exists:patients,paciente_id',
            'appointment_id' => 'nullable|exists:appointments,appointment_id',
            'tipo'           => 'required|in:receta,examen,otro',
        ], [
            'archivo.required'     => 'Debe seleccionar un archivo.',
            'archivo.file'         => 'Lo seleccionado no es un archivo valido.',
            'archivo.mimes'        => 'Solo se permiten archivos PDF, JPG o PNG.',
            'archivo.max'          => 'El archivo no puede pesar mas de 5 MB.',
            'paciente_id.required' => 'Debe indicar a que paciente pertenece el documento.',
            'tipo.required'        => 'Debe indicar el tipo de documento.',
            'tipo.in'              => 'El tipo debe ser receta, examen u otro.',
        ]);

        $archivo = $request->file('archivo');

        $nombreGuardado = 'doc_'.$datos['paciente_id'].'_'.time().'.'.$archivo->getClientOriginalExtension();

        $ruta = $archivo->storeAs('documentos', $nombreGuardado, 'local');

        if (!$ruta) {
            return back()->withInput()->withErrors([
                'archivo' => 'No se pudo guardar el archivo. Intente de nuevo.'
            ]);
        }

        Documento::create([
            'paciente_id'     => $datos['paciente_id'],
            'appointment_id'  => $datos['appointment_id'] ?? null,
            'nombre_original' => $archivo->getClientOriginalName(),
            'ruta'            => $ruta,
            'tipo'            => $datos['tipo'],
            'mime'            => $archivo->getClientMimeType(),
            'peso_kb'         => (int) ceil($archivo->getSize() / 1024),
            'subido_por'      => auth()->id(),
        ]);

        return redirect()->route('documentos.index', $datos['paciente_id'])
                         ->with('exito', 'Documento guardado correctamente.');
    }

    public function descargar($id)
    {
        $documento = Documento::findOrFail($id);

        $this->verificarAcceso($documento->paciente);

        if (!Storage::disk('local')->exists($documento->ruta)) {
            return back()->withErrors([
                'descargar' => 'El archivo ya no se encuentra en el servidor.'
            ]);
        }

        return Storage::disk('local')->download($documento->ruta, $documento->nombre_original);
    }

    public function destroy($id)
    {
        abort_unless(auth()->user()->rol === 'admin', 403);

        $documento = Documento::findOrFail($id);
        $pacienteId = $documento->paciente_id;

        if (Storage::disk('local')->exists($documento->ruta)) {
            Storage::disk('local')->delete($documento->ruta);
        }

        $documento->delete();

        return redirect()->route('documentos.index', $pacienteId)
                         ->with('exito', 'Documento eliminado.');
    }

    private function verificarAcceso(Patient $paciente): void
    {
        if (auth()->user()->rol === 'paciente') {
            $propio = auth()->user()->patient;

            if (!$propio || $propio->paciente_id !== $paciente->paciente_id) {
                abort(403, 'No tiene permiso para ver estos documentos.');
            }
        }
    }
}