<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreItemRequest;
use App\Http\Requests\UpdateItemRequest;
use App\Models\Item;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
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
        StoreItemRequest $request
    ): RedirectResponse {

        $data = $request->validated();

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
        UpdateItemRequest $request,
        Item $item
    ): RedirectResponse {

        abort_if(
            $item->user_id !== Auth::id(),
            403
        );

        $item->update(
            $request->validated()
        );

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