<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $items = Auth::user()->items()->latest()->get();

        $requiredItems = $items->map(function ($item) {
            return [
                'id' => $item->id,
                'icon' => $item->icon,
                'name' => $item->name,
                'category' => $item->category,
                'compartment' => $item->compartment ?: '—',
                'signal' => $item->signal_label,
                'lastScan' => $item->last_scan_text,
                'packed' => $item->status === 'connected',
            ];
        })->values();

        return view('dashboard.index', compact('requiredItems'));
    }
}