<?php

namespace App\Jobs;

use App\Models\PaymentSchedule;
use App\Services\EmailService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendPaymentRemindersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(EmailService $emailService): void
    {
        try {
            // Récupérer toutes les échéances qui arrivent dans 3 jours exactement
            $threeDaysFromNow = now()->addDays(3)->startOfDay();
            $threeDaysFromNowEnd = now()->addDays(3)->endOfDay();

            $schedules = PaymentSchedule::whereBetween('due_date', [$threeDaysFromNow, $threeDaysFromNowEnd])
                ->where('status', '!=', 'paid')
                ->with(['order', 'order.user'])
                ->get();

            Log::info("Found " . $schedules->count() . " payment reminders to send");

            foreach ($schedules as $schedule) {
                if ($schedule->order && $schedule->order->user) {
                    $emailService->sendPaymentDueReminderEmails($schedule);
                    Log::info("Reminder sent for schedule {$schedule->id}");
                }
            }

            Log::info("Payment reminders job completed successfully");
        } catch (\Exception $e) {
            Log::error("Error in SendPaymentRemindersJob: {$e->getMessage()}");
            throw $e;
        }
    }
}
