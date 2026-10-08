<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    public function index(Request $request)
    {
        $buscar = $request->input('buscar');

        $pacientes = Patient::when($buscar, function ($consulta) use ($buscar) {
                $consulta->where('nombre', 'like', "%{$buscar}%")
                         ->orWhere('telefono', 'like', "%{$buscar}%")
                         ->orWhere('email', 'like', "%{$buscar}%");
            })
            ->orderBy('nombre')
            ->paginate(15)
            ->withQueryString();

        return view('pacientes.index', compact('pacientes', 'buscar'));
    }

    public function create()
    {
        return view('pacientes.create');
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'nombre'                => 'required|string|max:120',
            'telefono'              => 'nullable|string|max:30',
            'email'                 => 'nullable|email|max:255',
            'birth_date'            => 'nullable|date|before:today',
            'tipo_de_sangre'        => 'nullable|string|max:3',
            'alergias'              => 'nullable|string',
            'enfermedades_cronicas' => 'nullable|string',
        ], [
            'nombre.required'   => 'El nombre del paciente es obligatorio.',
            'birth_date.before' => 'La fecha de nacimiento no puede ser futura.',
            'email.email'       => 'El correo no tiene un formato valido.',
        ]);

        Patient::create($datos);

        return redirect()->route('pacientes.index')
                         ->with('exito', 'Paciente registrado correctamente.');
    }

    public function show($id)
    {
        $paciente = Patient::with(['appointments' => function ($consulta) {
            $consulta->orderBy('fecha', 'desc')->orderBy('hora', 'desc');
        }, 'appointments.doctor'])->findOrFail($id);

        $verClinico = auth()->user()->rol === 'admin';

        return view('pacientes.show', compact('paciente', 'verClinico'));
    }

    public function edit($id)
    {
        $paciente = Patient::findOrFail($id);
        $verClinico = auth()->user()->rol === 'admin';

        return view('pacientes.edit', compact('paciente', 'verClinico'));
    }

    public function update(Request $request, $id)
    {
        $paciente = Patient::findOrFail($id);

        $reglas = [
            'nombre'     => 'required|string|max:120',
            'telefono'   => 'nullable|string|max:30',
            'email'      => 'nullable|email|max:255',
            'birth_date' => 'nullable|date|before:today',
        ];

        if (auth()->user()->rol === 'admin') {
            $reglas['tipo_de_sangre']        = 'nullable|string|max:3';
            $reglas['alergias']              = 'nullable|string';
            $reglas['enfermedades_cronicas'] = 'nullable|string';
        }

        $paciente->update($request->validate($reglas));

        return redirect()->route('pacientes.show', $id)
                         ->with('exito', 'Datos del paciente actualizados.');
    }

    public function destroy($id)
    {
        $paciente = Patient::findOrFail($id);

        if ($paciente->appointments()->count() > 0) {
            return back()->withErrors([
                'eliminar' => 'No se puede eliminar un paciente que tiene citas registradas.'
            ]);
        }

        $paciente->delete();

        return redirect()->route('pacientes.index')
                         ->with('exito', 'Paciente eliminado.');
    }
}