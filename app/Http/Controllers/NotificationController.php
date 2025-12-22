<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function markAsRead($id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);

        $notification->markAsRead();

        return redirect($notification->data['link'] ?? route('dashboard'));
    }

    public function markAllRead()
    {
        // Ambil user yang sedang login, lalu tandai semua notifikasi "unread" menjadi "read"
        Auth::user()->unreadNotifications->markAsRead();

        // Kembali ke halaman sebelumnya
        return redirect()->back()->with('success', 'Semua notifikasi ditandai sudah dibaca.');
    }
}
