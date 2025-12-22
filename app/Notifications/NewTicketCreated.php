<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewTicketCreated extends Notification
{
    use Queueable;

    public $ticket;

    public function __construct($ticket)
    {
        $this->ticket = $ticket;
    }

    // Simpan ke database agar muncul di lonceng aplikasi
    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            // Judul Notifikasi
            'title' => 'Tiket Baru Masuk',

            // Pesan: Tampilkan Nomor Tiket & Nama Peminta
            'message' => "Tiket #{$this->ticket->ticket_num} baru saja dibuat oleh {$this->ticket->requester_name}. Segera proses!",

            // Link: Langsung buka tiket tersebut saat diklik
            'link' => route('fh.index', ['open_ticket_id' => $this->ticket->id]),

            'ticket_id' => $this->ticket->id,
            'type' => 'new_ticket'
        ];
    }
}
