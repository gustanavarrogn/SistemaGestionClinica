<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DoctorController extends Controller
{
    public function index()
    {
        $doctores = Doctor::orderBy('nombre')->paginate(15);
        return view('doctores.index', compact('doctores'));
    }

    public function create()
    {
        return view('doctores.create');
    }

    public function store(Request $request)
    {
        // En esta fase los doctores no tienen inicio de sesión independiente.
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', 'unique:doctors,email'],
            'especialidad' => ['required', 'string', 'max:120'],
        ]);

        Doctor::create($datos);
        return redirect()->route('doctores.index')->with('exito', 'Doctora registrada correctamente.');
    }

    public function edit($id)
    {
        $doctor = Doctor::findOrFail($id);
        return view('doctores.edit', compact('doctor'));
    }

    public function update(Request $request, $id)
    {
        $doctor = Doctor::findOrFail($id);
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', Rule::unique('doctors', 'email')->ignore($doctor->doctor_id, 'doctor_id')],
            'especialidad' => ['required', 'string', 'max:120'],
        ]);

        $doctor->update($datos);
        return redirect()->route('doctores.index')->with('exito', 'Doctora actualizada correctamente.');
    }

    public function destroy($id)
    {
        $doctor = Doctor::findOrFail($id);
        if ($doctor->appointments()->whereIn('status', ['Pendiente', 'Confirmado', 'En proceso'])->exists()) {
            return back()->withErrors(['eliminar' => 'La doctora tiene citas activas. Debe completarlas o cancelarlas antes de desactivarla.']);
        }

        // Baja lógica: se conserva el historial médico y de citas.
        $doctor->delete();
        return redirect()->route('doctores.index')->with('exito', 'Doctora desactivada correctamente.');
    }
}
