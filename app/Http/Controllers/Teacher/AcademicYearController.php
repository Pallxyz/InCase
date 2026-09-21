<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAcademicYearRequest;
use App\Models\AcademicYear;
use App\Models\Subject;
use App\Services\AcademicYearExporter;
use App\Services\ScheduleConflictChecker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AcademicYearController extends Controller
{
    public function index(): View
    {
        $academicYears = AcademicYear::withCount('subjects')
            ->orderByDesc('year_start')
            ->orderByDesc('semester')
            ->get();

        return view('academic-years.index', compact('academicYears'));
    }

    public function store(StoreAcademicYearRequest $request): RedirectResponse
    {
        $academicYear = AcademicYear::create($request->validated());

        // Kalau ini tahun ajaran pertama, langsung aktifkan.
        if (AcademicYear::count() === 1) {
            $academicYear->activate();
        }

        return redirect()
            ->route('academic-years.index')
            ->with('success', 'Tahun ajaran berhasil ditambahkan.');
    }

    public function activate(AcademicYear $academicYear): RedirectResponse
    {
        $academicYear->activate();

        return redirect()
            ->route('academic-years.index')
            ->with('success', "Tahun ajaran {$academicYear->label} berhasil diaktifkan.");
    }

    public function destroy(AcademicYear $academicYear): RedirectResponse
    {
        abort_if($academicYear->is_active, 422, 'Tidak bisa menghapus tahun ajaran yang sedang aktif.');
        abort_if($academicYear->subjects()->exists(), 422, 'Tidak bisa menghapus tahun ajaran yang masih punya jadwal.');

        $academicYear->delete();

        return redirect()
            ->route('academic-years.index')
            ->with('success', 'Tahun ajaran berhasil dihapus.');
    }

    /**
     * Salin jadwal dari tahun ajaran lain ke tahun ajaran ini
     * (biasanya saat ganti semester).
     *
     * - yang disalin: mapel, kelas, guru, hari, jam, ruang, DAN barang wajib
     * - yang TIDAK disalin: PR dan tanda ujian (itu khusus semester lama)
     * - jadwal yang bentrok / sudah ada di tahun ajaran tujuan dilewati,
     *   jadi aman kalau tombolnya ke-klik dua kali
     */
    public function copySchedules(Request $request, AcademicYear $academicYear): RedirectResponse
    {
        $data = $request->validate([
            'source_id' => ['required', 'integer', 'exists:academic_years,id'],
        ]);

        $source = AcademicYear::findOrFail($data['source_id']);

        if ($source->id === $academicYear->id) {
            return back()->withErrors([
                'source_id' => 'Tahun ajaran asal dan tujuan tidak boleh sama.',
            ]);
        }

        $checker = app(ScheduleConflictChecker::class);
        $copied = 0;
        $skipped = 0;

        DB::transaction(function () use ($source, $academicYear, $checker, &$copied, &$skipped) {
            $oldSubjects = Subject::with('requiredItems')
                ->where('academic_year_id', $source->id)
                ->where('is_active', true)
                ->orderBy('id')
                ->get();

            foreach ($oldSubjects as $old) {
                $conflicts = $checker->check([
                    'class_id' => $old->class_id,
                    'day' => $old->day,
                    'start_time' => $old->start_time,
                    'end_time' => $old->end_time,
                    'location' => $old->location,
                ], $academicYear->id, $old->teacher_id);

                if ($conflicts !== []) {
                    $skipped++;
                    continue;
                }

                $new = Subject::create([
                    'teacher_id' => $old->teacher_id,
                    'class_id' => $old->class_id,
                    'academic_year_id' => $academicYear->id,
                    'name' => $old->name,
                    'location' => $old->location,
                    'day' => $old->day,
                    'start_time' => $old->start_time,
                    'end_time' => $old->end_time,
                    'is_active' => true,
                ]);

                foreach ($old->requiredItems as $item) {
                    $new->requiredItems()->create(['name' => $item->name]);
                }

                $copied++;
            }
        });

        if ($copied === 0 && $skipped === 0) {
            return back()->with('success', "Tidak ada jadwal aktif di {$source->label} yang bisa disalin.");
        }

        return redirect()
            ->route('academic-years.index')
            ->with('success', "{$copied} jadwal disalin dari {$source->label} ke {$academicYear->label}"
                . ($skipped ? ", {$skipped} dilewati karena sudah ada/bentrok." : '.'));
    }

    /**
     * Unduh jadwal satu tahun ajaran sebagai CSV (bisa dibuka di Excel/Google Sheets).
     * Bisa dipakai untuk tahun ajaran mana pun, termasuk yang sudah tidak aktif.
     */
    public function export(AcademicYear $academicYear, AcademicYearExporter $exporter): StreamedResponse
    {
        return response()->streamDownload(
            function () use ($academicYear, $exporter) {
                $out = fopen('php://output', 'w');
                $exporter->write($academicYear, $out);
                fclose($out);
            },
            $exporter->filename($academicYear),
            ['Content-Type' => 'text/csv; charset=UTF-8']
        );
    }
}