<?php

namespace App\Listeners;

use App\Events\TicketCreated;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification; // <--- INI YANG KURANG TADI
use App\Models\User;

class SendTicketNotification
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(TicketCreated $event)
    {
        Log::info('1. Listener Berhasil Dipanggil. Tiket ID: ' . $event->ticket->id);

        // Cek User dengan role fh.admin
        $admins = User::where('role', 'fh.admin')->get();

        Log::info('2. Jumlah Admin Ditemukan: ' . $admins->count());

        if ($admins->count() > 0) {
            // Mengirim notifikasi menggunakan Facade Notification
            Notification::send($admins, new \App\Notifications\NewTicketCreated($event->ticket));

            Log::info('3. Perintah Kirim Notifikasi Dieksekusi.');
        } else {
            Log::error('3. GAGAL: Tidak ada user dengan role fh.admin');
        }
    }
}
