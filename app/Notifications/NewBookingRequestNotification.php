<?php

namespace App\Notifications;

use App\Models\BookingRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewBookingRequestNotification extends Notification
{
    use Queueable;

    public function __construct(private BookingRequest $booking) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $booking = $this->booking;

        return (new MailMessage)
            ->subject('Pengajuan sewa baru: '.$booking->room->code.', '.$booking->room->property->name)
            ->greeting('Halo '.$notifiable->name.',')
            ->line($booking->user->name.' mengajukan sewa kamar '.$booking->room->code.' di '.$booking->room->property->name.'.')
            ->line('Mulai: '.$booking->start_date->translatedFormat('d F Y').' · '.$booking->duration_months.' bulan')
            ->action('Tinjau pengajuan', route('admin.bookings.index'))
            ->line('Pengajuan otomatis kedaluwarsa jika tidak diproses.');
    }
}
