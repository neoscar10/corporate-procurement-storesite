<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        \App\Events\Procurement\ProcurementApprovalRequested::class => [
            \App\Listeners\Procurement\SendProcurementApprovalRequest::class,
        ],
        \App\Events\Procurement\ProcurementApproved::class => [
            \App\Listeners\Procurement\SendProcurementApproved::class,
        ],
        \App\Events\Procurement\ProcurementRejected::class => [
            \App\Listeners\Procurement\SendProcurementRejected::class,
        ],
        \App\Events\Procurement\ProcurementPublished::class => [
            \App\Listeners\Procurement\SendProcurementPublished::class,
        ],

        // If/when you add a listener for request creation, uncomment:
        // \App\Events\Procurement\ProcurementRequestCreated::class => [
        //     \App\Listeners\Procurement\SendProcurementRequestCreated::class,
        // ],
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
