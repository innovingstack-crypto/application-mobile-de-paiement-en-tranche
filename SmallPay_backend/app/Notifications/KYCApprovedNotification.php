<?php

namespace App\Notifications;

use App\Models\KYC;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class KYCApprovedNotification extends Notification
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
            ->subject('Votre vérification d\'identité (KYC) a été approuvée')
            ->view('emails.kyc_approved', [
                'user' => $notifiable,
                'kyc' => $this->kyc,
            ]);
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'kyc_approved',
            'kyc_id' => $this->kyc->id,
            'message' => 'Votre vérification d\'identité a été approuvée',
        ];
    }
}
