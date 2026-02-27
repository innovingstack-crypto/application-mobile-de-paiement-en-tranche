<?php

namespace App\Console;

use App\Jobs\SendPaymentRemindersJob;
use App\Jobs\SendPaymentOverdueNotificationsJob;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Envoyer les rappels d'échéance à 8h chaque matin
        // Cela envoie un email pour toutes les échéances dans 3 jours
        $schedule->job(new SendPaymentRemindersJob)
            ->dailyAt('08:00')
            ->timezone('Africa/Douala')
            ->withoutOverlapping()
            ->onSuccess(function () {
                \Illuminate\Support\Facades\Log::info('Payment reminders sent successfully');
            })
            ->onFailure(function () {
                \Illuminate\Support\Facades\Log::error('Payment reminders job failed');
            });

        // Envoyer les notifications de retard à 10h chaque matin
        // Cela envoie un email pour toutes les échéances dépassées
        $schedule->job(new SendPaymentOverdueNotificationsJob)
            ->dailyAt('10:00')
            ->timezone('Africa/Douala')
            ->withoutOverlapping()
            ->onSuccess(function () {
                \Illuminate\Support\Facades\Log::info('Overdue notifications sent successfully');
            })
            ->onFailure(function () {
                \Illuminate\Support\Facades\Log::error('Overdue notifications job failed');
            });
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
