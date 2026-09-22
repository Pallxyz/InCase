<?php

namespace App\Http\Controllers;

use App\Models\Holiday;
use App\Models\Item;
use App\Models\ScanLog;
use App\Models\Subject;
use App\Services\ReturnCheckService;
use App\Services\SchoolDayResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display dashboard based on authenticated user role.
     */
    public function index(): View|RedirectResponse
    {
        $user = Auth::user();

        // Admin belum punya dashboard sendiri (langkah berikutnya),
        // sementara diarahkan ke halaman Tahun Ajaran.
        if ($user->role === 'admin') {
            return redirect()->route('academic-years.index');
        }
        $today = now()->englishDayOfWeek;

        /*
        |--------------------------------------------------------------------------
        | STUDENT DASHBOARD
        |--------------------------------------------------------------------------
        */
        if ($user->role === 'student') {
            $todaySubjects = Subject::with([
                    'teacher',
                    'schoolClass',
                    'requiredItems',
                ])
                ->where('class_id', $user->class_id)
                ->inActiveYear()
                ->where('day', $today)
                ->where('is_active', true)
                ->orderBy('start_time')
                ->get();

            // Hari libur (sekolah / kelas ini): tidak ada jadwal & barang wajib hari ini.
            $holiday = Holiday::findFor($user->school_name, $user->class_id, today());

            if ($holiday) {
                $todaySubjects = collect();
            }

            // Ruang pengganti hari ini (mis. pindah ke masjid) menggantikan ruang biasa.
            Subject::applyRoomChanges($todaySubjects, today());

            $todayScans = ScanLog::with('item')
                ->where('user_id', $user->id)
                ->where('phase', 'packing')          // scan persiapan (bukan scan cek pulang)
                ->whereDate('for_date', today())     // untuk hari sekolah ini (termasuk scan semalam)
                ->latest('scanned_at')
                ->get();

            $scannedItemIds = $todayScans
                ->pluck('item_id')
                ->unique();

            // Barang WAJIB hari ini = gabungan barang wajib semua pelajaran hari ini
            // (nama yang sama di beberapa pelajaran cuma dihitung sekali).
            $requiredNames = $todaySubjects
                ->flatMap(fn ($subject) => $subject->requiredItems->pluck('name'))
                ->map(fn ($name) => trim($name))
                ->filter()
                ->unique(fn ($name) => Str::lower($name))
                ->values();

            // Barang milik siswa, dikelompokkan per nama (dicocokkan sama seperti ScanService).
            $myItemsByName = Item::where('user_id', $user->id)
                ->get()
                ->groupBy(fn ($item) => Str::lower(trim($item->name)));

            // Satu baris per barang wajib. Kalau siswa punya barangnya, pakai barang itu
            // (utamakan yang sudah discan hari ini). Kalau belum terdaftar, tetap
            // ditampilkan sebagai "belum terdeteksi" supaya kelihatan masih kurang.
            $items = $requiredNames
                ->map(function ($name) use ($myItemsByName, $scannedItemIds) {
                    $owned = $myItemsByName->get(Str::lower($name));

                    if (! $owned) {
                        return new Item(['name' => $name]);
                    }

                    return $owned->first(fn ($item) => $scannedItemIds->contains($item->id))
                        ?? $owned->first();
                })
                ->sortBy(fn ($item) => Str::lower($item->name))
                ->values();

            $packedCount = $items
                ->filter(fn ($item) => $item->id && $scannedItemIds->contains($item->id))
                ->count();

            $totalItems = $items->count();

            $progress = $totalItems > 0
                ? round(($packedCount / $totalItems) * 100)
                : 0;

            // Cek pulang: barang yang dibawa pagi tapi belum kembali ke tas.
            $returnCheckOpen = app(SchoolDayResolver::class)->returnCheckOpen($user, now());
            $returns = app(ReturnCheckService::class);
            $notReturned = $returnCheckOpen ? $returns->unreturned($user, today()) : collect();
            $resolutions = $returnCheckOpen ? $returns->resolutions($user, today()) : collect();

            return view('dashboard.index', [
                'role' => 'student',
                'returnCheckOpen' => $returnCheckOpen,
                'notReturned' => $notReturned,
                'resolutions' => $resolutions,
                'user' => $user,
                'todaySubjects' => $todaySubjects,
                'items' => $items,
                'todayScans' => $todayScans,
                'packedCount' => $packedCount,
                'totalItems' => $totalItems,
                'progress' => $progress,
                'holiday' => $holiday,
                // Teacher only
                'subjectCount' => 0,
                'studentCount' => 0,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | TEACHER DASHBOARD
        |--------------------------------------------------------------------------
        */
        $todaySubjects = Subject::with([
                'schoolClass',
                'requiredItems',
            ])
            ->where('teacher_id', $user->id)
            ->inActiveYear()
            ->where('day', $today)
            ->where('is_active', true)
            ->orderBy('start_time')
            ->get();

        // Kelas yang sedang libur tidak dihitung.
        $todaySubjects = $todaySubjects->reject(
            fn ($subject) => Holiday::findFor($user->school_name, $subject->class_id, today())
        )->values();

        Subject::applyRoomChanges($todaySubjects, today());

        $subjectCount = Subject::inActiveYear()->where(
            'teacher_id',
            $user->id
        )->count();

        $studentCount = $todaySubjects
            ->pluck('class_id')
            ->filter()
            ->unique()
            ->pipe(function ($classes) {
                return \App\Models\User::where('role', 'student')
                    ->whereIn('class_id', $classes)
                    ->count();
            });

        return view('dashboard.index', [
            'role' => 'teacher',
            'user' => $user,
            'todaySubjects' => $todaySubjects,
            'subjectCount' => $subjectCount,
            'studentCount' => $studentCount,
            // Student only
            'items' => collect(),
            'todayScans' => collect(),
            'packedCount' => 0,
            'totalItems' => 0,
            'progress' => 0,
            'holiday' => null,
        ]);
    }
}