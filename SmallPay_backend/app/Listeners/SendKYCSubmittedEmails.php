<?php

namespace App\Listeners;

use App\Events\KYCSubmitted;
use App\Services\EmailService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendKYCSubmittedEmails implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(private EmailService $emailService)
    {
    }

    public function handle(KYCSubmitted $event): void
    {
        try {
            Log::info("Handling KYCSubmitted event for user {$event->user->id}");
            $this->emailService->sendKYCSubmittedEmails($event->kyc);
        } catch (\Exception $e) {
            Log::error("Error in SendKYCSubmittedEmails listener: {$e->getMessage()}");
            throw $e;
        }
    }
}
