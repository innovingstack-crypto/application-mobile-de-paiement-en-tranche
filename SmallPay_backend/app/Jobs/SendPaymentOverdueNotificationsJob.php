<?php

namespace App\Jobs;

use App\Models\PaymentSchedule;
use App\Services\EmailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendPaymentOverdueNotificationsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(EmailService $emailService): void
    {
        try {
            // Récupérer toutes les échéances qui sont passées et non payées
            $overdueSchedules = PaymentSchedule::where('due_date', '<', now())
                ->where('status', '!=', 'paid')
                ->with(['order', 'order.user'])
                ->get();

            Log::info("Found " . $overdueSchedules->count() . " overdue payment notifications to send");

            foreach ($overdueSchedules as $schedule) {
                if ($schedule->order && $schedule->order->user) {
                    // Marquer comme overdue si ce n'est pas déjà le cas
                    if ($schedule->status !== 'overdue') {
                        $schedule->markAsOverdue();
                    }

                    $emailService->sendPaymentOverdueEmails($schedule);
                    Log::info("Overdue notification sent for schedule {$schedule->id}");
                }
            }

            Log::info("Overdue notifications job completed successfully");
        } catch (\Exception $e) {
            Log::error("Error in SendPaymentOverdueNotificationsJob: {$e->getMessage()}");
            throw $e;
        }
    }
}
