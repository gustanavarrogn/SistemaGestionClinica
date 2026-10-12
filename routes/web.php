<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserManagementController;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route(auth()->check() ? 'dashboard' : 'login'));
Route::get('/dashboard', function () {
    $usuario = auth()->user();
    return redirect()->route(match ($usuario->rol) {
        'admin' => 'admin.dashboard',
        'recepcionista' => 'recepcionista.agenda',
        default => 'paciente.mis-citas',
    });
})->middleware('auth')->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', function () {
        return view('admin.dashboard', [
            'totalPacientes' => Patient::count(),
            'totalDoctores' => Doctor::count(),
            'citasHoy' => Appointment::whereDate('fecha', today())->count(),
            'proximas' => Appointment::with(['patient', 'doctor'])->whereDate('fecha', '>=', today())->orderBy('fecha')->orderBy('hora')->take(5)->get(),
        ]);
    })->name('admin.dashboard');

    // Gestión de cuentas: admin, recepción y pacientes. Doctoras se crean desde Doctores.
    Route::get('/usuarios', [UserManagementController::class, 'index'])->name('usuarios.index');
    Route::get('/usuarios/crear', [UserManagementController::class, 'create'])->name('usuarios.create');
    Route::post('/usuarios', [UserManagementController::class, 'store'])->name('usuarios.store');
    Route::get('/usuarios/{usuario}/editar', [UserManagementController::class, 'edit'])->name('usuarios.edit');
    Route::put('/usuarios/{usuario}', [UserManagementController::class, 'update'])->name('usuarios.update');

    Route::get('/doctores', [DoctorController::class, 'index'])->name('doctores.index');
    Route::get('/doctores/crear', [DoctorController::class, 'create'])->name('doctores.create');
    Route::post('/doctores', [DoctorController::class, 'store'])->name('doctores.store');
    Route::get('/doctores/{id}/editar', [DoctorController::class, 'edit'])->name('doctores.edit');
    Route::put('/doctores/{id}', [DoctorController::class, 'update'])->name('doctores.update');
    Route::delete('/doctores/{id}', [DoctorController::class, 'destroy'])->name('doctores.destroy');
});

Route::middleware(['auth', 'role:admin,recepcionista'])->group(function () {
    Route::get('/recepcionista/agenda', [AppointmentController::class, 'agenda'])->name('recepcionista.agenda');
});
Route::middleware(['auth', 'role:paciente'])->group(function () {
    Route::get('/paciente/mis-citas', [AppointmentController::class, 'misCitas'])->name('paciente.mis-citas');
    Route::get('/paciente/estados', [AppointmentController::class, 'estadosPaciente'])->name('paciente.estados');
});
Route::middleware(['auth', 'role:admin,recepcionista'])->group(function () {
    Route::get('/pacientes', [PatientController::class, 'index'])->name('pacientes.index');
    Route::get('/pacientes/crear', [PatientController::class, 'create'])->name('pacientes.create');
    Route::post('/pacientes', [PatientController::class, 'store'])->name('pacientes.store');
    Route::get('/pacientes/{id}/editar', [PatientController::class, 'edit'])->name('pacientes.edit');
    Route::put('/pacientes/{id}', [PatientController::class, 'update'])->name('pacientes.update');
    Route::delete('/pacientes/{id}', [PatientController::class, 'destroy'])->name('pacientes.destroy');
});
Route::middleware(['auth', 'role:admin,recepcionista'])->group(function () {
    Route::get('/pacientes/{id}', [PatientController::class, 'show'])->name('pacientes.show');
});
Route::middleware('auth')->group(function () {
    Route::get('/citas', [AppointmentController::class, 'index'])->name('citas.index');
    Route::get('/citas/{id}', [AppointmentController::class, 'show'])->whereNumber('id')->name('citas.show');
    Route::put('/citas/{id}/cancelar', [AppointmentController::class, 'cancelar'])->name('citas.cancelar');
    Route::get('/disponibilidad', [AppointmentController::class, 'disponibilidad'])->name('citas.disponibilidad');
});
Route::middleware(['auth', 'role:admin,recepcionista,paciente'])->group(function () {
    Route::get('/citas/crear', [AppointmentController::class, 'create'])->name('citas.create');
    Route::post('/citas', [AppointmentController::class, 'store'])->name('citas.store');
});
Route::middleware(['auth', 'role:admin,recepcionista'])->group(function () {
    Route::put('/citas/{id}/estado', [AppointmentController::class, 'cambiarEstado'])->name('citas.estado');
});

require __DIR__.'/auth.php';
