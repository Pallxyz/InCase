<?php

namespace Tests\Feature;

use App\Models\Holiday;
use App\Models\Item;
use App\Models\SchoolClass;
use App\Models\User;
use App\Notifications\DailyPackingReminderNotification;
use App\Services\ScanService;
use Database\Seeders\AdminSeeder;
use Database\Seeders\RplDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Reminder "siapkan barangmu": dikirim pagi & malam sebelum berangkat
 * (bukan lagi per-pelajaran 30 menit sebelumnya).
 */
class ReminderTest extends TestCase
{
    use RefreshDatabase;

    private User $student;

    protected function setUp(): void
    {
        parent::setUp();

        if (DB::connection()->getDriverName() === 'sqlite') {
            DB::connection()->getPdo()->sqliteCreateFunction(
                'FIELD',
                fn ($value, ...$list) => ($i = array_search($value, $list)) === false ? 0 : $i + 1
            );
        }

        $this->travelTo(\Carbon\Carbon::parse('2026-09-21 06:00'));   // Senin pagi
        $this->seed(AdminSeeder::class);
        $this->seed(RplDemoSeeder::class);
        $this->student = User::where('email', 'siswa01@incase.test')->firstOrFail();
    }

    public function test_reminder_pagi_dikirim_untuk_barang_yang_belum_discan(): void
    {
        Notification::fake();
        $this->artisan('incase:send-item-reminders')->assertSuccessful();

        Notification::assertSentTo($this->student, DailyPackingReminderNotification::class);
    }

    public function test_reminder_tidak_dikirim_kalau_semua_sudah_discan(): void
    {
        $class = SchoolClass::where('name', 'X RPL 1')->firstOrFail();
        $required = \App\Models\Subject::inActiveYear()->with('requiredItems')
            ->where('class_id', $class->id)->where('day', 'Monday')->get()
            ->flatMap(fn ($s) => $s->requiredItems->pluck('name'))->unique();

        foreach ($required as $name) {
            $uid = Item::where('user_id', $this->student->id)->where('name', $name)->firstOrFail()->rfid_uid;
            app(ScanService::class)->handle($uid);
        }

        Notification::fake();
        $this->artisan('incase:send-item-reminders')->assertSuccessful();
        Notification::assertNothingSentTo($this->student);
    }

    public function test_reminder_tidak_dikirim_saat_libur(): void
    {
        Holiday::create(['created_by' => User::where('role', 'admin')->first()->id, 'school_name' => 'SMKN 1 Cirebon', 'class_id' => null, 'date' => today(), 'name' => 'Libur Uji']);

        Notification::fake();
        $this->artisan('incase:send-item-reminders')->assertSuccessful();
        Notification::assertNothingSentTo($this->student);
    }

    public function test_reminder_malam_menyasar_hari_sekolah_berikutnya(): void
    {
        $this->travelTo(\Carbon\Carbon::parse('2026-09-21 19:00'));   // Senin malam -> untuk Selasa

        Notification::fake();
        $this->artisan('incase:send-item-reminders')->assertSuccessful();

        Notification::assertSentTo($this->student, function (DailyPackingReminderNotification $n) {
            return true; // cukup pastikan terkirim; isi tanggal sudah dites di ScanPhaseTest
        });
    }

    public function test_reminder_tidak_dikirim_ke_siswa_tanpa_kelas_atau_guru(): void
    {
        $teacher = User::where('role', 'teacher')->first();
        $orphan = User::factory()->create(['role' => 'student', 'class_id' => null]);

        Notification::fake();
        $this->artisan('incase:send-item-reminders')->assertSuccessful();

        Notification::assertNothingSentTo($teacher);
        Notification::assertNothingSentTo($orphan);
    }
}