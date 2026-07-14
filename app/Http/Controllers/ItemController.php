<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    public function index()
    {
        $items = auth()->user()->items()->latest()->get();

        return view('items.index', compact('items'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:255'],
            'rfid' => ['nullable', 'string', 'max:255', 'unique:items,rfid'],
            'compartment' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $item = auth()->user()->items()->create([
            ...$validated,
            'status' => !empty($validated['rfid']) ? 'not_scanned' : 'no_rfid',
        ]);

        return response()->json(['item' => $item]);
    }

    public function update(Request $request, Item $item)
    {
        abort_unless($item->user_id === auth()->id(), 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:255'],
            'rfid' => ['nullable', 'string', 'max:255', 'unique:items,rfid,' . $item->id],
            'compartment' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $item->update($validated);

        return response()->json(['item' => $item->fresh()]);
    }

    public function destroy(Item $item)
    {
        abort_unless($item->user_id === auth()->id(), 403);

        $item->delete();

        return response()->json(['success' => true]);
    }
}