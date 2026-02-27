<?php

namespace App\Notifications;

use App\Models\KYC;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class KYCRejectedNotification extends Notification
{
    use Queueable;

    public function __construct(protected KYC $kyc)
    {
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Votre vérification d\'identité (KYC) a été rejetée')
            ->view('emails.kyc_rejected', [
                'user' => $notifiable,
                'kyc' => $this->kyc,
            ]);
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'kyc_rejected',
            'kyc_id' => $this->kyc->id,
            'reason' => $this->kyc->rejection_reason,
            'message' => 'Votre vérification d\'identité a été rejetée',
        ];
    }
}
