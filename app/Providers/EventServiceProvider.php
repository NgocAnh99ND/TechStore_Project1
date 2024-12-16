<?php

namespace App\Providers;

use App\Events\GuestOrderPlaced;
use App\Events\OrderChangeStatus;
use App\Events\OrderPlaced;
use App\Listeners\NotificationOrderPlaced;
use App\Listeners\OrderNotificationHandler;
use App\Listeners\SendOrderCancellationEmail;
use App\Listeners\SendOrderConfirmationEmail;
use App\Listeners\SendOrderUpdateStatusEmail;
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
        GuestOrderPlaced::class => [
            NotificationOrderPlaced::class,
            SendOrderConfirmationEmail::class,
        ],
        OrderPlaced::class => [
            OrderNotificationHandler::class
        ]
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
