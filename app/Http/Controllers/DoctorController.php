<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use Illuminate\Http\Request;

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
        $datos = $request->validate([
            'nombre'       => 'required|string|max:120',
            'email'        => 'nullable|email|max:255',
            'especialidad' => 'nullable|string|max:120',
        ], [
            'nombre.required' => 'El nombre de la doctora es obligatorio.',
        ]);

        Doctor::create($datos);

        return redirect()->route('doctores.index')
                         ->with('exito', 'Doctora registrada correctamente.');
    }

    public function edit($id)
    {
        $doctor = Doctor::findOrFail($id);

        return view('doctores.edit', compact('doctor'));
    }

    public function update(Request $request, $id)
    {
        $doctor = Doctor::findOrFail($id);

        $doctor->update($request->validate([
            'nombre'       => 'required|string|max:120',
            'email'        => 'nullable|email|max:255',
            'especialidad' => 'nullable|string|max:120',
        ]));

        return redirect()->route('doctores.index')
                         ->with('exito', 'Datos actualizados.');
    }

    public function destroy($id)
    {
        $doctor = Doctor::findOrFail($id);

        if ($doctor->appointments()->count() > 0) {
            return back()->withErrors([
                'eliminar' => 'No se puede eliminar una doctora que tiene citas registradas.'
            ]);
        }

        $doctor->delete();

        return redirect()->route('doctores.index')
                         ->with('exito', 'Doctora eliminada.');
    }
}