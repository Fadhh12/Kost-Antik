<?php

namespace App\Notifications;

use App\Models\PropertySubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewPropertySubmissionNotification extends Notification
{
    use Queueable;

    public function __construct(private PropertySubmission $submission) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $submission = $this->submission;

        return (new MailMessage)
            ->subject('Pengajuan kost baru: '.$submission->name)
            ->greeting('Halo '.$notifiable->name.',')
            ->line($submission->contact_name.' mendaftarkan kost "'.$submission->name.'" di '.$submission->city.'.')
            ->line('Kontak: '.$submission->contact_phone)
            ->action('Tinjau pengajuan', route('admin.property-submissions.index'))
            ->line('Pengajuan menunggu persetujuan sebelum tampil di katalog publik.');
    }
}
