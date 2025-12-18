<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewTicketCreated extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct($ticket)
    {
        $this->ticket = $ticket;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via($notifiable)
    {
        return ['database']; // Simpan ke database (buat icon lonceng)
        // return ['mail', 'database']; // Jika ingin kirim email juga
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->line('The introduction to the notification.')
            ->action('Notification Action', url('/'))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray($notifiable)
    {
        return [
            'ticket_num' => $this->ticket->ticket_num,
            'message' => 'Tiket baru fasilitas: ' . $this->ticket->category,

            // UBAH BARIS LINK MENJADI SEPERTI INI:
            // Kita kirim parameter 'open_ticket_id' ke route index
            'link' => route('fh.index', ['open_ticket_id' => $this->ticket->id])
        ];
    }
}
