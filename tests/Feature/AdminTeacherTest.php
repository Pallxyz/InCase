<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\AdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTeacherTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AdminSeeder::class);
        $this->admin = User::where('role', 'admin')->firstOrFail();
    }

    public function test_admin_bisa_membuat_akun_guru_dengan_password_acak(): void
    {
        $response = $this->actingAs($this->admin)->post('/teachers', [
            'name' => 'Guru Baru',
            'email' => 'gurubaru@incase.test',
        ]);

        $response->assertSessionHas('success');
        $response->assertSessionHas('generated_password');

        $teacher = User::where('email', 'gurubaru@incase.test')->firstOrFail();
        $this->assertSame('teacher', $teacher->role);
        $this->assertNull($teacher->class_id);
        $this->assertSame($this->admin->school_name, $teacher->school_name);

        // password acaknya benar-benar bisa dipakai login (logout dulu dari sesi admin)
        $password = session('generated_password');
        $this->post('/logout');
        $this->post('/login', ['email' => $teacher->email, 'password' => $password])->assertRedirect();
        $this->assertAuthenticatedAs($teacher);
    }

    public function test_guru_dan_siswa_tidak_bisa_membuat_akun_guru(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $student = User::factory()->create(['role' => 'student']);

        foreach ([$teacher, $student] as $user) {
            $this->actingAs($user)->post('/teachers', ['name' => 'X', 'email' => 'x@x.com'])->assertForbidden();
        }

        $this->assertSame(0, User::where('email', 'x@x.com')->count());
    }

    public function test_email_guru_tidak_boleh_dobel(): void
    {
        User::factory()->create(['email' => 'dobel@incase.test']);

        $this->actingAs($this->admin)->post('/teachers', ['name' => 'Guru', 'email' => 'dobel@incase.test'])
            ->assertSessionHasErrors('email');
    }

    public function test_admin_bisa_menghapus_akun_guru(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);

        $this->actingAs($this->admin)->delete("/teachers/{$teacher->id}")->assertSessionHas('success');
        $this->assertModelMissing($teacher);
    }

    public function test_tidak_bisa_menghapus_siswa_lewat_endpoint_ini(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        $this->actingAs($this->admin)->delete("/teachers/{$student->id}")->assertNotFound();
        $this->assertModelExists($student);
    }
}