<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
// StoreItemRequest dan UpdateItemRequest tidak dipakai lagi, boleh dihapus atau dikomen
use App\Models\Item;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request; // Menggunakan Request bawaan Laravel
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ItemController extends Controller
{
    /**
     * Display all items owned by the authenticated student.
     */
    public function index(): View
    {
        $items = Item::where('user_id', Auth::id())
            ->latest()
            ->get();

        return view('items.index', compact('items'));
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
    public function store(
        Request $request
    ): RedirectResponse {

        // Validasi langsung di sini agar kategori bebas (tidak dibatasi Rule::in)
        $data = $request->validate([
            'name'        => 'required|max:255',
            'category'    => 'required|string|max:255', // Diubah jadi string bebas
            'rfid_uid'    => 'nullable|string|max:255|unique:items,rfid_uid',
            'quantity'    => 'required|integer|min:0',
            'description' => 'nullable|max:500',
            'status'      => 'required|in:active,archived',
        ]);

        $data['user_id'] = Auth::id();

        Item::create($data);

        return redirect()
            ->route('items.index')
            ->with(
                'success',
                'Item created successfully.'
            );
    }

    /**
     * Display single item.
     */
    public function show(
        Item $item
    ): JsonResponse {

        abort_if(
            $item->user_id !== Auth::id(),
            403
        );

        return response()->json($item);
    }

    /**
     * Get item for edit modal.
     */
    public function edit(
        Item $item
    ): JsonResponse {

        abort_if(
            $item->user_id !== Auth::id(),
            403
        );

        return response()->json($item);
    }

    /**
     * Update item.
     */
    public function update(
        Request $request,
        Item $item
    ): RedirectResponse {

        abort_if(
            $item->user_id !== Auth::id(),
            403
        );

        // Validasi untuk update juga disamakan
        $data = $request->validate([
            'name'        => 'required|max:255',
            'category'    => 'required|string|max:255',
            'rfid_uid'    => 'nullable|string|max:255|unique:items,rfid_uid,' . $item->id, // Mengabaikan unik untuk item ini sendiri
            'quantity'    => 'required|integer|min:0',
            'description' => 'nullable|max:500',
            'status'      => 'required|in:active,archived',
        ]);

        $item->update($data);

        return redirect()
            ->route('items.index')
            ->with(
                'success',
                'Item updated successfully.'
            );
    }

    /**
     * Delete item.
     */
    public function destroy(
        Item $item
    ): RedirectResponse {

        abort_if(
            $item->user_id !== Auth::id(),
            403
        );

        $item->delete();

        return redirect()
            ->route('items.index')
            ->with(
                'success',
                'Item deleted successfully.'
            );
    }
}