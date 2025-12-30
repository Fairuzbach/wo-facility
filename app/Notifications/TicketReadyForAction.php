<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketReadyForAction extends Notification
{
    use Queueable;

    protected $wo;

    public function __construct($wo)
    {
        $this->wo = $wo;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Tiket Siap Diproses: #' . $this->wo->ticket_num)
            ->greeting('Halo Tim Facilities')
            ->line('Tiket berikut sudah diapprove SPV dan siap dikerjakan.')
            ->line('**Lokasi:** ' . $this->wo->plant)
            ->line('**Kategori:** ' . $this->wo->category)
            ->action('Lihat Tiket', url('/'))
            ->line('Segera tugaskan teknisi.');
    }
}
