<?php

namespace Tests\Feature;

use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MediControlFlujoTest extends TestCase
{
    use RefreshDatabase;

    public function test_el_administrador_puede_abrir_los_modulos(): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);
        $this->actingAs($admin)->get('/admin/dashboard')->assertOk();
        $this->actingAs($admin)->get('/pacientes')->assertOk();
        $this->actingAs($admin)->get('/doctores')->assertOk();
        $this->actingAs($admin)->get('/recepcionista/agenda')->assertOk();
    }

    public function test_el_paciente_no_accede_a_modulos_de_administracion(): void
    {
        $paciente = User::factory()->create(['rol' => 'paciente']);
        $this->actingAs($paciente)->get('/pacientes')->assertForbidden();
        $this->actingAs($paciente)->get('/doctores')->assertForbidden();
    }

    public function test_el_registro_publico_crea_cuenta_y_ficha_de_paciente(): void
    {
        $this->post('/register', [
            'name' => 'Paciente Nuevo',
            'email' => 'nuevo@medicontrol.test',
            'password' => 'Seguro12345!',
            'password_confirmation' => 'Seguro12345!',
            'consentimiento' => '1',
        ])->assertRedirect('/dashboard');

        $usuario = User::where('email', 'nuevo@medicontrol.test')->firstOrFail();
        $this->assertEquals('paciente', $usuario->rol);
        $this->assertNotNull($usuario->patient_id);
        $this->assertEquals($usuario->patient_id, Patient::where('email','nuevo@medicontrol.test')->firstOrFail()->paciente_id);
    }

    public function test_el_seeder_crea_tres_cuentas_y_doctoras_sin_login(): void
    {
        $this->seed();
        $this->assertEquals('admin', User::where('email','admin@medicontrol.test')->firstOrFail()->rol);
        $this->assertNotNull(User::where('email','paciente@medicontrol.test')->firstOrFail()->patient_id);
        $this->assertSame('recepcionista', User::where('email','recepcion@medicontrol.test')->firstOrFail()->rol);
        $this->assertSame(3, User::count());
        $this->assertSame(2, Doctor::count());
        $this->assertNull(User::where('email', 'doctora@medicontrol.test')->first());
    }
}
