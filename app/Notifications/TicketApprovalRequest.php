<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketApprovalRequest extends Notification
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
            ->subject('Approval Needed: Ticket #' . $this->wo->ticket_num)
            ->greeting('Halo Admin ' . $this->wo->requester_division)
            ->line('Ada tiket baru membutuhkan persetujuan Anda.')
            ->line('**Pelapor:** ' . $this->wo->requester_name)
            ->line('**Masalah:** ' . $this->wo->description)
            ->action('Login untuk Approve', url('/'))
            ->line('Terima kasih.');
    }
}
