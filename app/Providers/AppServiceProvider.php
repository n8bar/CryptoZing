<?php

namespace App\Providers;

use App\Models\Client;
use App\Models\Invoice;
use App\Policies\ClientPolicy;
use App\Policies\InvoicePolicy;
use App\Services\Blockchain\MempoolClient;
use App\Services\ConfirmationPolicy;
use App\Listeners\ApplyMailAlias;
use App\Services\MailAlias;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(MempoolClient::class, function ($app) {
            return new MempoolClient(config('blockchain'));
        });

        $this->app->singleton(ConfirmationPolicy::class, function ($app) {
            return new ConfirmationPolicy(
                (string) $app['config']->get('blockchain.confirmation_tiers', ConfirmationPolicy::DEFAULT_TIERS)
            );
        });

        $this->app->singleton(MailAlias::class, function ($app) {
            $config = $app['config']->get('mail.aliasing', []);

            return new MailAlias(
                $config['domain'] ?? null,
                (bool) ($config['enabled'] ?? false)
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(MessageSending::class, ApplyMailAlias::class);

        Gate::policy(Client::class, ClientPolicy::class);
        Gate::policy(Invoice::class, InvoicePolicy::class);
    }
}
