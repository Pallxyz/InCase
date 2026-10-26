<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\ItemResolution;
use App\Models\User;
use App\Notifications\ItemLostNotification;
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
 * Barang elektronik (laptop, charger, dll) yang dilaporkan hilang:
 *  - wajib menyertakan keterangan (terakhir dilihat kapan/di mana)
 *  - semua admin otomatis diberi notifikasi
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

        $isElectronics = $item->category === 'Electronics';

        $data = $request->validate([
            'status' => ['required', Rule::in([ItemResolution::SUBMITTED, ItemResolution::LOST])],
            'confirmed' => ['accepted'],
            'note' => [
                Rule::requiredIf(fn () => $isElectronics && $request->input('status') === ItemResolution::LOST),
                'nullable',
                'string',
                'min:5',
                'max:500',
            ],
        ], [
            'confirmed.accepted' => 'Konfirmasi dulu: yakin dengan pilihanmu?',
            'note.required' => 'Barang elektronik yang hilang wajib diisi keterangan (terakhir dilihat kapan/di mana).',
            'note.min' => 'Keterangannya terlalu singkat, jelaskan sedikit lebih lengkap.',
        ]);

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

        $note = $data['status'] === ItemResolution::LOST
            ? (isset($data['note']) ? trim($data['note']) : null)
            : null;

        $resolution = ItemResolution::where('user_id', $student->id)
            ->where('item_id', $item->id)
            ->whereDate('date', today())
            ->first();

        $wasAlreadyLost = $resolution && $resolution->status === ItemResolution::LOST;

        if ($resolution) {
            $resolution->update(['status' => $data['status'], 'note' => $note]);
        } else {
            ItemResolution::create([
                'user_id' => $student->id,
                'item_id' => $item->id,
                'date' => today()->toDateString(),
                'status' => $data['status'],
                'note' => $note,
            ]);
        }

        if ($isElectronics && $data['status'] === ItemResolution::LOST && ! $wasAlreadyLost) {
            User::where('role', 'admin')->get()->each(
                fn (User $admin) => $admin->notify(new ItemLostNotification($item, $student, $note))
            );
        }

        $label = $data['status'] === ItemResolution::SUBMITTED ? 'dikumpulkan' : 'hilang';

        return back()->with('success', "Dicatat: {$item->name} {$label}."
            . ($isElectronics && $data['status'] === ItemResolution::LOST ? ' Admin sudah diberi tahu.' : ''));
    }
}