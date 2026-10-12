<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rules\Password;

class UserManagementController extends Controller
{
    public function index()
    {
        // Se ocultan accesos médicos antiguos que ya no forman parte de esta fase.
        $usuarios = User::whereDoesntHave('doctor')->orderBy('name')->paginate(15);
        return view('usuarios.index', compact('usuarios'));
    }

    public function create()
    {
        return view('usuarios.create');
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'rol' => ['required', Rule::in(['admin', 'recepcionista', 'paciente'])],
        ]);

        DB::transaction(function () use ($datos) {
            $pacienteId = null;
            if ($datos['rol'] === 'paciente') {
                // Si la ficha ya existe, se debe vincular de forma explícita en recepción.
                if (Patient::where('email', $datos['email'])->exists()) {
                    throw ValidationException::withMessages(['email' => 'Ya existe una ficha con ese correo. La cuenta debe vincularse desde recepción.']);
                }
                $pacienteId = Patient::create(['nombre' => $datos['name'], 'email' => $datos['email']])->paciente_id;
            }
            User::create([
                'name' => $datos['name'], 'email' => $datos['email'],
                'password' => Hash::make($datos['password']), 'rol' => $datos['rol'],
                'patient_id' => $pacienteId, 'is_active' => true,
            ]);
        });

        return redirect()->route('usuarios.index')->with('exito', 'Cuenta creada correctamente.');
    }

    public function edit(User $usuario)
    {
        abort_if($usuario->isDoctor(), 403, 'Las cuentas médicas se editan desde Doctores.');
        return view('usuarios.edit', compact('usuario'));
    }

    public function update(Request $request, User $usuario)
    {
        abort_if($usuario->isDoctor(), 403, 'Las cuentas médicas se editan desde Doctores.');
        $datos = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'rol' => ['required', Rule::in(['admin', 'recepcionista', 'paciente'])],
            'is_active' => ['required', 'boolean'],
            'password' => ['nullable', 'confirmed', Password::min(8)],
        ]);

        // Evita que el administrador se quite su propio acceso o desactive al último administrador.
        $cambiaRol = $datos['rol'] !== 'admin' || !$datos['is_active'];
        if (($usuario->id === auth()->id() && $cambiaRol) ||
            ($usuario->isManagementAdmin() && $cambiaRol &&
             User::where('rol', 'admin')->where('is_active', true)->whereDoesntHave('doctor')->count() <= 1)) {
            return back()->withErrors(['rol' => 'No se puede quitar el acceso al último administrador ni modificar el rol de la sesión actual.']);
        }

        DB::transaction(function () use ($usuario, $datos) {
            if ($datos['rol'] === 'paciente' && !$usuario->patient_id) {
                $ficha = Patient::where('email', $usuario->email)->first();
                if ($ficha && User::where('patient_id', $ficha->paciente_id)->where('id', '!=', $usuario->id)->exists()) {
                    throw ValidationException::withMessages(['rol' => 'Esta ficha está vinculada a otra cuenta.']);
                }
                $usuario->patient_id = ($ficha ?? Patient::create(['nombre' => $datos['name'], 'email' => $usuario->email]))->paciente_id;
            }
            $usuario->name = $datos['name'];
            $usuario->rol = $datos['rol'];
            $usuario->is_active = (bool) $datos['is_active'];
            if (!empty($datos['password'])) $usuario->password = Hash::make($datos['password']);
            $usuario->save();
        });
        return redirect()->route('usuarios.index')->with('exito', 'Cuenta y permisos actualizados.');
    }
}
