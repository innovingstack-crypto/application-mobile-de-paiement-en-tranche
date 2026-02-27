<?php

namespace App\Listeners;

use App\Events\MonthlyPaymentProcessed;
use App\Services\EmailService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendMonthlyPaymentEmails implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(private EmailService $emailService)
    {
    }

    public function handle(MonthlyPaymentProcessed $event): void
    {
        try {
            Log::info("Handling MonthlyPaymentProcessed event for user {$event->user->id}");
            $this->emailService->sendMonthlyPaymentEmails($event->payment);
        } catch (\Exception $e) {
            Log::error("Error in SendMonthlyPaymentEmails listener: {$e->getMessage()}");
            throw $e;
        }
    }
}
