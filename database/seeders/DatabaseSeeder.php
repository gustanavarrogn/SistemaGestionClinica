<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command?->warn('No se permiten cuentas de demostración en producción.');
            return;
        }

        // Estos usuarios son de prueba; firstOrCreate no cambia contraseñas existentes.
        User::firstOrCreate(['email' => 'admin@medicontrol.test'], [
            'name' => 'Administración Demo', 'rol' => 'admin',
            'password' => Hash::make('Demo12345!'), 'is_active' => true,
        ]);
        User::firstOrCreate(['email' => 'recepcion@medicontrol.test'], [
            'name' => 'Recepción Demo', 'rol' => 'recepcionista',
            'password' => Hash::make('Demo12345!'), 'is_active' => true,
        ]);
        $paciente = Patient::firstOrCreate(['email' => 'paciente@medicontrol.test'], [
            'nombre' => 'Sofía Martínez', 'telefono' => '7000-1234',
            'birth_date' => '1998-04-16', 'tipo_de_sangre' => 'O+',
            'alergias' => 'Penicilina (ficticio)', 'enfermedades_cronicas' => 'Ninguna (ficticio)',
        ]);
        $cuentaPaciente = User::firstOrCreate(['email' => 'paciente@medicontrol.test'], [
            'name' => 'Sofía Martínez', 'rol' => 'paciente',
            'password' => Hash::make('Demo12345!'), 'patient_id' => $paciente->paciente_id,
            'is_active' => true,
        ]);
        if (!$cuentaPaciente->patient_id) $cuentaPaciente->update(['patient_id' => $paciente->paciente_id]);

        $equipo = [
            ['nombre' => 'Dra. Valeria Rivas', 'email' => 'doctora@medicontrol.test', 'especialidad' => 'Medicina general'],
            ['nombre' => 'Dra. Elena Morales', 'email' => 'doctora2@medicontrol.test', 'especialidad' => 'Medicina familiar'],
        ];
        $doctores = [];
        foreach ($equipo as $dato) {
            $doctor = Doctor::firstOrCreate(['email' => $dato['email']], $dato);
            $doctores[] = $doctor;
        }

        // Si esta base fue usada con la versión de cinco accesos, desactivamos
        // sus cuentas médicas antiguas sin borrar doctores ni historiales de citas.
        $cuentasMedicasAnteriores = Doctor::withTrashed()->whereNotNull('user_id')->pluck('user_id');
        User::whereIn('id', $cuentasMedicasAnteriores)->update(['is_active' => false]);

        // Citas de ejemplo para comprobar los cambios de estado en las dos agendas.
        $fecha = Carbon::today()->addDay();
        if ($fecha->isSunday()) $fecha->addDay();
        foreach ($doctores as $pos => $doctor) {
            $diaCita = $fecha->copy()->addDays($pos);
            if ($diaCita->isSunday()) $diaCita->addDay();
            Appointment::firstOrCreate([
                'paciente_id' => $paciente->paciente_id,
                'doctor_id' => $doctor->doctor_id,
                'fecha' => $diaCita->toDateString(),
                'hora' => $pos === 0 ? '09:00:00' : '10:00:00',
            ], [
                'razon' => $pos === 0 ? 'Consulta general (prueba)' : 'Evaluación médica (prueba)',
                'status' => $pos === 0 ? 'Pendiente' : 'Confirmado',
            ]);
        }
    }
}
