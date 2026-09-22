<?php

namespace Tests\Feature\Auth;

use App\Models\SchoolClass;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Registrasi publik: HANYA siswa, dan HANYA ke kelas yang sudah terdaftar.
 * Guru dibuatkan admin (lihat Feature/AdminTeacherTest).
 */
class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $this->get('/register')->assertStatus(200);
    }

    public function test_siswa_bisa_daftar_ke_kelas_yang_sudah_ada(): void
    {
        $class = SchoolClass::create(['name' => 'X RPL 1', 'major' => 'PPLG', 'grade' => 'X', 'school_name' => 'SMKN 1 Cirebon']);

        $response = $this->post('/register', [
            'name' => 'Siswa Baru',
            'email' => 'siswabaru@example.com',
            'class_id' => $class->id,
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));

        $user = \App\Models\User::where('email', 'siswabaru@example.com')->firstOrFail();
        $this->assertSame('student', $user->role);
        $this->assertSame($class->id, $user->class_id);
        $this->assertSame('SMKN 1 Cirebon', $user->school_name);
    }

    public function test_daftar_tanpa_kelas_ditolak(): void
    {
        $response = $this->post('/register', [
            'name' => 'Siswa Baru', 'email' => 'a@example.com',
            'password' => 'password', 'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors('class_id');
        $this->assertGuest();
    }

    public function test_daftar_dengan_kelas_yang_tidak_ada_ditolak(): void
    {
        $response = $this->post('/register', [
            'name' => 'Siswa Baru', 'email' => 'a@example.com', 'class_id' => 999,
            'password' => 'password', 'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors('class_id');
        $this->assertGuest();
    }

    public function test_tidak_bisa_daftar_sebagai_guru_atau_admin_lewat_form_publik(): void
    {
        $class = SchoolClass::create(['name' => 'X RPL 1', 'major' => 'PPLG', 'grade' => 'X', 'school_name' => 'SMKN 1 Cirebon']);

        $this->post('/register', [
            'name' => 'Coba Jadi Guru', 'email' => 'coba@example.com', 'class_id' => $class->id,
            'role' => 'teacher',   // dipaksa diabaikan / tidak berpengaruh
            'password' => 'password', 'password_confirmation' => 'password',
        ]);

        $user = \App\Models\User::where('email', 'coba@example.com')->first();
        $this->assertNotNull($user);
        $this->assertSame('student', $user->role, 'role kiriman dari client tidak boleh dipercaya');
    }
}