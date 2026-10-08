<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    $user = auth()->user();

    if ($user->rol === 'admin') {
        return redirect()->route('admin.dashboard');
    } elseif ($user->rol === 'recepcionista') {
        return redirect()->route('recepcionista.agenda');
    } else {
        return redirect()->route('paciente.mis-citas');
    }
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
// Ruta para el Administrador (Doctora)
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');
});

// Ruta para la Recepcionista
Route::middleware(['auth', 'role:recepcionista'])->group(function () {
    Route::get('/recepcionista/agenda', function () {
        return view('recepcionista.agenda');
    })->name('recepcionista.agenda');
});

// Ruta para el Paciente
Route::middleware(['auth', 'role:paciente'])->group(function () {
    Route::get('/paciente/mis-citas', function () {
        return view('paciente.mis-citas');
    })->name('paciente.mis-citas');
});
require __DIR__.'/auth.php';
