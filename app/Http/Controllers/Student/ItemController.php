<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Subject;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ItemController extends Controller
{
    /**
     * Display all items owned by the authenticated student.
     */
    public function index(): View
    {
        $user = Auth::user();

        $items = Item::where('user_id', $user->id)
            ->latest()
            ->get();

        // Opsi dropdown nama barang di form Tambah: barang wajib dari jadwal
        // kelas siswa, dikelompokkan per kategori. Kalau kelas belum punya
        // jadwal (atau siswa belum punya class_id), semua kategori kosong dan
        // siswa cuma bisa pakai opsi "Lainnya (ketik sendiri)".
        $itemOptions = array_fill_keys(Item::CATEGORIES, []);

        if ($user->class_id) {
            Subject::where('class_id', $user->class_id)
                ->where('is_active', true)
                ->with('requiredItems')
                ->get()
                ->flatMap(fn ($subject) => $subject->requiredItems->pluck('name'))
                ->map(fn ($name) => trim($name))
                ->filter()
                ->unique(fn ($name) => mb_strtolower($name))
                ->sort()
                ->each(function ($name) use (&$itemOptions) {
                    $itemOptions[Item::categoryFor($name)][] = $name;
                });
        }

        return view('items.index', compact('items', 'itemOptions'));
    }

    /**
     * Not used.
     */
    public function create(): RedirectResponse
    {
        return redirect()->route('items.index');
    }

    /**
     * Store new item.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('items', 'name')->where('user_id', Auth::id()),
            ],
            'category'    => ['required', Rule::in(Item::CATEGORIES)],
            'rfid_uid'    => 'nullable|string|max:255|unique:items,rfid_uid',
            'quantity'    => 'required|integer|min:0',
            'description' => 'nullable|max:500',
            'status'      => 'required|in:active,archived',
        ], [
            'name.required' => 'Nama barang wajib diisi.',
            'name.unique'   => 'Barang dengan nama ini sudah ada di daftarmu.',
        ]);

        $data['user_id'] = Auth::id();
        $data['name'] = trim($data['name']);

        Item::create($data);

        return redirect()
            ->route('items.index')
            ->with('success', 'Item created successfully.');
    }

    /**
     * Display single item.
     */
    public function show(Item $item): JsonResponse
    {
        abort_if($item->user_id !== Auth::id(), 403);

        return response()->json($item);
    }

    /**
     * Get item for edit modal.
     */
    public function edit(Item $item): JsonResponse
    {
        abort_if($item->user_id !== Auth::id(), 403);

        return response()->json($item);
    }

    /**
     * Update item. Nama dan kategori SENGAJA dikunci (tidak diterima dari request)
     * supaya nama barang tetap cocok persis dengan barang wajib di jadwal. Yang
     * boleh diubah cuma UID RFID, jumlah, deskripsi, dan status.
     */
    public function update(Request $request, Item $item): RedirectResponse
    {
        abort_if($item->user_id !== Auth::id(), 403);

        $data = $request->validate([
            'rfid_uid'    => 'nullable|string|max:255|unique:items,rfid_uid,' . $item->id,
            'quantity'    => 'required|integer|min:0',
            'description' => 'nullable|max:500',
            'status'      => 'required|in:active,archived',
        ]);

        $item->update($data);

        return redirect()
            ->route('items.index')
            ->with('success', 'Item updated successfully.');
    }

    /**
     * Delete item. Idempoten: kalau barangnya sudah kehapus duluan (mis. klik
     * dobel tombol Hapus), tetap redirect sukses, bukan lempar 404 Not Found.
     */
    public function destroy(int $item): RedirectResponse
    {
        $model = Item::find($item);

        if ($model) {
            abort_if($model->user_id !== Auth::id(), 403);
            $model->delete();
        }

        return redirect()
            ->route('items.index')
            ->with('success', 'Item deleted successfully.');
    }
}