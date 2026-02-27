<?php

namespace App\Notifications;

use App\Models\KYC;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class NewKYCSubmissionNotification extends Notification
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
            ->subject('Nouvelle demande de vérification d\'identité - Action requise')
            ->view('emails.new_kyc_submission', [
                'admin' => $notifiable,
                'kyc' => $this->kyc,
                'user' => $this->kyc->user,
            ]);
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'new_kyc_submission',
            'kyc_id' => $this->kyc->id,
            'user_id' => $this->kyc->user_id,
            'user_name' => $this->kyc->user->name,
            'message' => 'Nouvelle demande de vérification d\'identité pour ' . $this->kyc->user->name,
        ];
    }
}
