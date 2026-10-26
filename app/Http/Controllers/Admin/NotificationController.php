<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();

        return view('admin.notifications.index', [
            'notifications' => $user->notifications()->latest()->paginate(15),
            'unreadCount' => $user->unreadNotifications()->count(),
        ]);
    }

    public function markRead(string $id): RedirectResponse
    {
        Auth::user()->notifications()->where('id', $id)->first()?->markAsRead();

        return back();
    }

    public function markAllRead(): RedirectResponse
    {
        Auth::user()->unreadNotifications->markAsRead();

        return back()->with('success', 'Semua notifikasi ditandai sudah dibaca.');
    }
}