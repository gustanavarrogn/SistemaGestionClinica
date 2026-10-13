<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'consentimiento' => ['accepted'],
        ], [
            'consentimiento.accepted' => 'Debes aceptar el tratamiento de tus datos para registrarte.',
        ]);

        // Si la clínica ya tiene una ficha con ese correo, debe vincularla el personal.
        // No se asocia automáticamente un historial clínico a una cuenta recién creada.
        if (Patient::where('email', $datos['email'])->exists()) {
            return back()->withInput($request->except('password', 'password_confirmation'))
                ->withErrors(['email' => 'Ya existe una ficha con este correo. Contacta a recepción para vincular tu cuenta.']);
        }

        $usuario = DB::transaction(function () use ($datos) {
            $paciente = Patient::create([
                'nombre' => $datos['name'],
                'email' => $datos['email'],
            ]);

            return User::create([
                'name' => $datos['name'],
                'email' => $datos['email'],
                'password' => Hash::make($datos['password']),
                'rol' => 'paciente',
                'patient_id' => $paciente->paciente_id,
            ]);
        });

        event(new Registered($usuario));
        Auth::login($usuario);

        return redirect()->route('dashboard');
    }
}
