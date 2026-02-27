<?php

namespace App\Providers;

use App\Events\KYCSubmitted;
use App\Events\FirstPaymentProcessed;
use App\Events\MonthlyPaymentProcessed;
use App\Listeners\SendKYCSubmittedEmails;
use App\Listeners\SendFirstPaymentEmails;
use App\Listeners\SendMonthlyPaymentEmails;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        KYCSubmitted::class => [
            SendKYCSubmittedEmails::class,
        ],
        FirstPaymentProcessed::class => [
            SendFirstPaymentEmails::class,
        ],
        MonthlyPaymentProcessed::class => [
            SendMonthlyPaymentEmails::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
