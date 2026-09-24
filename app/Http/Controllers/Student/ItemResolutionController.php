<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\ItemResolution;
use App\Models\User;
use App\Services\ReturnCheckService;
use App\Services\SchoolDayResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Barang yang dibawa pagi tapi tidak kembali saat cek pulang:
 * siswa menjelaskan "dikumpulkan" atau "hilang".
 *
 * Ada langkah konfirmasi ("kamu yakin?"): request harus membawa confirmed=1.
 * Tampilan akan mengirim confirmed=1 hanya setelah siswa menekan "Iya, yakin".
 * Kalau siswa memilih "bentar, tanya teman dulu", tidak ada request yang dikirim.
 */
class ItemResolutionController extends Controller
{
    public function store(
        Request $request,
        Item $item,
        ReturnCheckService $returns,
        SchoolDayResolver $days,
    ): RedirectResponse {
        abort_if($item->user_id !== Auth::id(), 403);

        $data = $request->validate([
            'status' => ['required', Rule::in([ItemResolution::SUBMITTED, ItemResolution::LOST])],
            'confirmed' => ['accepted'],
        ], [
            'confirmed.accepted' => 'Konfirmasi dulu: yakin dengan pilihanmu?',
        ]);

        // "Dikumpulkan / terbawa teman" cuma masuk akal untuk barang seperti buku.
        // Barang pribadi (botol minum, dompet, dll) yang tidak kembali cuma bisa "hilang".
        if ($data['status'] === ItemResolution::SUBMITTED && ! $item->canBeSubmitted()) {
            throw ValidationException::withMessages([
                'status' => "{$item->name} adalah barang pribadi, tidak bisa dijelaskan \"dikumpulkan\". Pilih \"hilang\".",
            ]);
        }

        $student = User::findOrFail(Auth::id());

        if (! $days->returnCheckOpen($student, now())) {
            throw ValidationException::withMessages([
                'item' => 'Pengecekan pulang belum dibuka (setelah pelajaran terakhir selesai).',
            ]);
        }

        if (! $returns->unreturned($student, today())->contains('id', $item->id)) {
            throw ValidationException::withMessages([
                'item' => 'Barang ini tidak termasuk barang yang belum kembali hari ini.',
            ]);
        }

        // whereDate() dipakai (bukan updateOrCreate dengan '==') supaya cocok
        // walau kolom `date` tersimpan dengan jam 00:00:00 di sebagian database.
        $resolution = ItemResolution::where('user_id', $student->id)
            ->where('item_id', $item->id)
            ->whereDate('date', today())
            ->first();

        if ($resolution) {
            $resolution->update(['status' => $data['status']]);
        } else {
            ItemResolution::create([
                'user_id' => $student->id,
                'item_id' => $item->id,
                'date' => today()->toDateString(),
                'status' => $data['status'],
            ]);
        }

        $label = $data['status'] === ItemResolution::SUBMITTED ? 'dikumpulkan' : 'hilang';

        return back()->with('success', "Dicatat: {$item->name} {$label}.");
    }
}