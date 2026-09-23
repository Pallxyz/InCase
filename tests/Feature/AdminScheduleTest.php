<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use Database\Seeders\AdminSeeder;
use Database\Seeders\RplDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Admin bisa membuat/mengedit/menghapus jadwal SIAPA PUN (semua kelas & guru).
 * Guru tetap hanya bisa mengelola jadwalnya sendiri.
 */
class AdminScheduleTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $teacherA;
    private User $teacherB;
    private SchoolClass $classX;

    protected function setUp(): void
    {
        parent::setUp();

        if (DB::connection()->getDriverName() === 'sqlite') {
            DB::connection()->getPdo()->sqliteCreateFunction(
                'FIELD',
                fn ($value, ...$list) => ($i = array_search($value, $list)) === false ? 0 : $i + 1
            );
        }

        $this->seed(AdminSeeder::class);
        $this->seed(RplDemoSeeder::class);

        $this->admin = User::where('role', 'admin')->firstOrFail();
        $this->teacherA = User::where('role', 'teacher')->firstOrFail();
        $this->teacherB = User::where('role', 'teacher')->where('id', '!=', $this->teacherA->id)->firstOrFail();
        $this->classX = SchoolClass::where('name', 'X RPL 1')->firstOrFail();
    }

    private function payload(array $override = []): array
    {
        return array_merge([
            'class_id' => $this->classX->id,
            'teacher_id' => $this->teacherA->id,
            'name' => 'Jadwal Dari Admin',
            'location' => 'R777',
            'day' => 'Monday',
            'start_time' => '13:00',
            'end_time' => '14:00',
            'required_items' => 'Laptop, Charger',
        ], $override);
    }

    public function test_admin_bisa_membuat_jadwal_untuk_guru_manapun(): void
    {
        $response = $this->actingAs($this->admin)->post('/subjects', $this->payload());
        $response->assertSessionHasNoErrors();

        $subject = Subject::where('name', 'Jadwal Dari Admin')->firstOrFail();
        $this->assertSame($this->teacherA->id, $subject->teacher_id);
        $this->assertSame(AcademicYear::active()->id, $subject->academic_year_id);
        $this->assertSame(2, $subject->requiredItems()->count());
    }

    public function test_admin_wajib_memilih_guru(): void
    {
        $response = $this->actingAs($this->admin)->post('/subjects', $this->payload(['teacher_id' => null]));
        $response->assertSessionHasErrors('teacher_id');
    }

    public function test_admin_tidak_bisa_memilih_akun_yang_bukan_guru(): void
    {
        $response = $this->actingAs($this->admin)->post('/subjects', $this->payload(['teacher_id' => $this->admin->id]));
        $response->assertSessionHasErrors('teacher_id');

        $student = User::where('role', 'student')->firstOrFail();
        $this->actingAs($this->admin)->post('/subjects', $this->payload(['teacher_id' => $student->id]))
            ->assertSessionHasErrors('teacher_id');
    }

    public function test_guru_tidak_bisa_membuat_jadwal_untuk_guru_lain(): void
    {
        // Guru mengirim teacher_id milik guru lain -> tetap dikunci ke dirinya sendiri.
        $this->actingAs($this->teacherA)->post('/subjects', $this->payload(['teacher_id' => $this->teacherB->id]))
            ->assertSessionHasNoErrors();

        $subject = Subject::where('name', 'Jadwal Dari Admin')->firstOrFail();
        $this->assertSame($this->teacherA->id, $subject->teacher_id, 'guru tidak boleh membuat jadwal atas nama guru lain');
    }

    public function test_admin_bisa_mengedit_dan_menghapus_jadwal_guru_manapun(): void
    {
        $subject = Subject::where('teacher_id', $this->teacherA->id)->firstOrFail();

        $this->actingAs($this->admin)->put("/subjects/{$subject->id}", [
            'class_id' => $subject->class_id, 'teacher_id' => $this->teacherA->id,
            'name' => 'Sudah Diedit Admin', 'location' => $subject->location, 'day' => $subject->day,
            'start_time' => substr($subject->start_time, 0, 5), 'end_time' => substr($subject->end_time, 0, 5),
            'is_active' => 1,
        ])->assertSessionHasNoErrors();

        $subject->refresh();
        $this->assertSame('Sudah Diedit Admin', $subject->name);

        $this->actingAs($this->admin)->delete("/subjects/{$subject->id}")->assertSessionHas('success');
        $this->assertModelMissing($subject);
    }

    public function test_admin_bisa_memindahkan_jadwal_ke_guru_lain_yang_kosong(): void
    {
        // Jam 13:00 sengaja dipakai karena tidak dipakai jadwal seed manapun (aman dari bentrok).
        $this->actingAs($this->admin)->post('/subjects', $this->payload(['teacher_id' => $this->teacherA->id]));
        $subject = Subject::where('name', 'Jadwal Dari Admin')->firstOrFail();

        $this->actingAs($this->admin)->put("/subjects/{$subject->id}", [
            'class_id' => $subject->class_id, 'teacher_id' => $this->teacherB->id,
            'name' => $subject->name, 'location' => $subject->location, 'day' => $subject->day,
            'start_time' => substr($subject->start_time, 0, 5), 'end_time' => substr($subject->end_time, 0, 5),
            'is_active' => 1,
        ])->assertSessionHasNoErrors();

        $this->assertSame($this->teacherB->id, $subject->fresh()->teacher_id);
    }

    public function test_admin_ditolak_kalau_guru_tujuan_sedang_bentrok(): void
    {
        $classY = SchoolClass::where('name', 'X RPL 2')->firstOrFail();

        // Jam 13:00 sengaja dipakai (tidak dipakai jadwal seed manapun) supaya konflik
        // yang terdeteksi murni buatan test ini, bukan tabrakan tak terduga dgn data seed.
        $this->actingAs($this->admin)->post('/subjects', $this->payload([
            'class_id' => $classY->id, 'teacher_id' => $this->teacherB->id, 'location' => 'RuangLain',
        ]))->assertSessionHasNoErrors();   // teacherB sekarang sibuk Senin 13:00-14:00

        $this->actingAs($this->admin)->post('/subjects', $this->payload([
            'teacher_id' => $this->teacherA->id, 'name' => 'Jadwal Kedua',
        ]))->assertSessionHasNoErrors();
        $subject = Subject::where('name', 'Jadwal Kedua')->firstOrFail();

        // Coba pindahkan jadwal kedua ke teacherB, yang sudah sibuk di jam yang sama.
        $this->actingAs($this->admin)->put("/subjects/{$subject->id}", [
            'class_id' => $subject->class_id, 'teacher_id' => $this->teacherB->id,
            'name' => $subject->name, 'location' => $subject->location, 'day' => $subject->day,
            'start_time' => substr($subject->start_time, 0, 5), 'end_time' => substr($subject->end_time, 0, 5),
            'is_active' => 1,
        ])->assertSessionHasErrors('start_time');

        $this->assertSame($this->teacherA->id, $subject->fresh()->teacher_id, 'gagal pindah -> tetap milik guru lama');
    }

    public function test_guru_tidak_bisa_mengedit_atau_menghapus_jadwal_guru_lain(): void
    {
        $subject = Subject::where('teacher_id', $this->teacherB->id)->firstOrFail();

        $this->actingAs($this->teacherA)->put("/subjects/{$subject->id}", [
            'class_id' => $subject->class_id, 'name' => 'Coba Edit', 'location' => $subject->location,
            'day' => $subject->day, 'start_time' => substr($subject->start_time, 0, 5),
            'end_time' => substr($subject->end_time, 0, 5), 'is_active' => 1,
        ])->assertForbidden();

        $this->actingAs($this->teacherA)->delete("/subjects/{$subject->id}")->assertForbidden();
        $this->assertModelExists($subject);
    }

    public function test_admin_melihat_jadwal_semua_guru_sedangkan_guru_hanya_miliknya(): void
    {
        $adminView = $this->actingAs($this->admin)->get('/subjects')->assertOk();
        $this->assertSame(90, $adminView->viewData('subjects')->count());
        $this->assertGreaterThan(0, $adminView->viewData('teachers')->count());

        $teacherView = $this->actingAs($this->teacherA)->get('/subjects')->assertOk();
        $this->assertTrue($teacherView->viewData('subjects')->every(
            fn ($s) => $s->teacher_id === $this->teacherA->id
        ));
        $this->assertCount(0, $teacherView->viewData('teachers'));
    }

    public function test_bentrok_tetap_dicek_walau_dibuat_admin(): void
    {
        $existing = Subject::where('class_id', $this->classX->id)->where('day', 'Monday')->firstOrFail();

        $this->actingAs($this->admin)->post('/subjects', $this->payload([
            'day' => $existing->day,
            'start_time' => substr($existing->start_time, 0, 5),
            'end_time' => substr($existing->end_time, 0, 5),
        ]))->assertSessionHasErrors('class_id');
    }
}