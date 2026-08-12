<?php

namespace App\Providers;

use App\Events\AppointmentWasCanceled;
use App\Events\AppointmentWasConfirmed;
use App\Events\NewAppointmentWasBooked;
use App\Events\NewContactWasRegistered;
use App\Events\NewSoftAppointmentWasBooked;
use App\Events\NewUserWasRegistered;
use App\Listeners\AutoConfigureUserPreferences;
use App\Listeners\LinkContactToExistingUser;
use App\Listeners\SendAppointmentCancellationNotification;
use App\Listeners\SendAppointmentConfirmationNotification;
use App\Listeners\SendBookingNotification;
use App\Listeners\SendMailUserWelcome;
use App\Listeners\SendSoftAppointmentValidationRequest;
use App\Listeners\UserEventListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, list<class-string>>
     */
    protected $listen = [
        NewUserWasRegistered::class => [
            AutoConfigureUserPreferences::class,
            SendMailUserWelcome::class,
        ],
        NewAppointmentWasBooked::class => [
            SendBookingNotification::class,
        ],
        NewContactWasRegistered::class => [
            LinkContactToExistingUser::class,
        ],
        AppointmentWasConfirmed::class => [
            SendAppointmentConfirmationNotification::class,
        ],
        AppointmentWasCanceled::class => [
            SendAppointmentCancellationNotification::class,
        ],
        NewSoftAppointmentWasBooked::class => [
            SendSoftAppointmentValidationRequest::class,
        ],
    ];

    /**
     * The subscriber classes to register.
     *
     * @var list<class-string>
     */
    protected $subscribe = [
        UserEventListener::class,
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
        return true;
    }
}
