<?php

namespace Tests\Feature;

use App\Models\Doctor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MediControlRolesYAgendaTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['rol' => 'admin', 'is_active' => true]);
    }

    public function test_se_registra_doctora_sin_cuenta_de_acceso(): void
    {
        $this->actingAs($this->admin())->post('/doctores', [
            'nombre' => 'Dra. Nueva', 'email' => 'nueva@medicontrol.test',
            'especialidad' => 'Medicina familiar',
        ])->assertRedirect(route('doctores.index'));

        $this->assertNotNull(Doctor::where('email', 'nueva@medicontrol.test')->first());
        $this->assertNull(User::where('email', 'nueva@medicontrol.test')->first());
    }

    public function test_cuenta_medica_de_version_anterior_no_puede_entrar(): void
    {
        $usuario = User::factory()->create(['rol' => 'admin', 'is_active' => true]);
        Doctor::create([
            'nombre' => 'Dra. Prueba', 'email' => $usuario->email,
            'especialidad' => 'Medicina general', 'user_id' => $usuario->id,
        ]);
        $this->actingAs($usuario)->get('/admin/dashboard')->assertRedirect(route('login'));
        $this->actingAs($usuario)->get('/usuarios')->assertRedirect(route('login'));
    }

    public function test_solo_admin_puede_gestionar_doctoras_y_usuarios(): void
    {
        $recepcion = User::factory()->create(['rol' => 'recepcionista', 'is_active' => true]);
        $this->actingAs($recepcion)->get('/doctores')->assertForbidden();
        $this->actingAs($recepcion)->get('/usuarios')->assertForbidden();
        $this->actingAs($this->admin())->get('/doctores')->assertOk();
    }

    public function test_admin_cambia_roles_y_no_se_quita_su_propio_acceso(): void
    {
        $admin = $this->admin();
        $recepcion = User::factory()->create(['rol' => 'recepcionista', 'is_active' => true]);
        $this->actingAs($admin)->put(route('usuarios.update', $recepcion), [
            'name' => $recepcion->name, 'rol' => 'paciente', 'is_active' => '1',
        ])->assertRedirect(route('usuarios.index'));
        $this->assertNotNull($recepcion->fresh()->patient_id);
        $this->assertSame('paciente', $recepcion->fresh()->rol);
        $this->actingAs($admin)->put(route('usuarios.update', $admin), [
            'name' => $admin->name, 'rol' => 'recepcionista', 'is_active' => '0',
        ])->assertSessionHasErrors('rol');
        $this->assertSame('admin', $admin->fresh()->rol);
    }

    public function test_login_no_publica_cuentas_ficticias(): void
    {
        $this->get('/login')->assertOk()->assertDontSee('Accesos de demostración');
    }
}
