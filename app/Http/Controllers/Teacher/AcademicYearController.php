<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAcademicYearRequest;
use App\Models\AcademicYear;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

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
}