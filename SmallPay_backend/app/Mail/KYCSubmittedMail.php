<?php

namespace App\Mail;

use App\Models\KYC;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class KYCSubmittedMail extends Mailable
{
    use Queueable, SerializesModels;

    public KYC $kyc;
    public User $user;

    public function __construct(KYC $kyc, User $user)
    {
        $this->kyc = $kyc;
        $this->user = $user;
    }

    public function build()
    {
        return $this->subject('Formulaire KYC Soumis - SmallPay')
            ->view('emails.kyc_submitted')
            ->with([
                'user' => $this->user,
                'kyc' => $this->kyc,
            ]);
    }
}
