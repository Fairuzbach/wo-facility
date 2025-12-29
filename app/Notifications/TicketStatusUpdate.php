<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketStatusUpdate extends Notification
{
    use Queueable;

    protected $wo;
    protected $statusType; // 'in_progress', 'completed', 'cancelled'

    public function __construct($wo, $statusType)
    {
        $this->wo = $wo;
        $this->statusType = $statusType;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    // Tambahkan properti $statusType di __construct seperti diskusi sebelumnya
    public function toMail($notifiable)
    {
        $subject = 'Update Tiket #' . $this->wo->ticket_num;
        $pesan = '';
        $line2 = '';

        switch ($this->statusType) {
            // --- TAMBAHAN BARU: STATUS CREATED ---
            case 'created':
                $subject .= ': Berhasil Dibuat';
                $pesan = 'Laporan Anda telah kami terima dan sedang menunggu persetujuan Supervisor.';
                $line2 = 'Mohon cek email secara berkala untuk update selanjutnya.';
                break;
            // -------------------------------------

            case 'in_progress':
                $subject .= ': Sedang Dikerjakan';
                $pesan = 'Tiket Anda sedang dikerjakan oleh teknisi kami.';
                break;
            case 'completed':
                $subject .= ': Selesai';
                $pesan = 'Pekerjaan telah selesai. Catatan: ' . ($this->wo->completion_note ?? '-');
                break;
            case 'cancelled':
                $subject .= ': Dibatalkan';
                $pesan = 'Mohon maaf, tiket Anda dibatalkan.';
                break;
        }

        $mail = (new MailMessage)
            ->subject($subject)
            ->greeting('Halo ' . $this->wo->requester_name)
            ->line('Status tiket Anda diperbarui.')
            ->line('**Nomor Tiket:** ' . $this->wo->ticket_num)
            ->line($pesan);

        if ($line2) {
            $mail->line($line2);
        }

        return $mail->line('Terima kasih.');
    }
}
