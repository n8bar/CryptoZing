<?php

namespace App\Providers;

use App\Events\InvoicePaid;
use App\Listeners\RefreshQueueHeartbeat;
use App\Listeners\SendInvoiceReceipt;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Queue\Events\Looping;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        InvoicePaid::class => [
            SendInvoiceReceipt::class.'@handle',
        ],
        Looping::class => [
            RefreshQueueHeartbeat::class.'@handle',
        ],
    ];

    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
