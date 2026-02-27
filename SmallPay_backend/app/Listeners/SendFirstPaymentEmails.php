<?php

namespace App\Listeners;

use App\Events\FirstPaymentProcessed;
use App\Services\EmailService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendFirstPaymentEmails implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(private EmailService $emailService)
    {
    }

    public function handle(FirstPaymentProcessed $event): void
    {
        try {
            Log::info("Handling FirstPaymentProcessed event for user {$event->user->id}");
            $this->emailService->sendFirstPaymentEmails($event->payment);
        } catch (\Exception $e) {
            Log::error("Error in SendFirstPaymentEmails listener: {$e->getMessage()}");
            throw $e;
        }
    }
}
