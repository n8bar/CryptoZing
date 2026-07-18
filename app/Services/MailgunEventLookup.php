<?php

namespace App\Services;

use App\Models\InvoiceDelivery;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MailgunEventLookup
{
    /**
     * Did the provider accept/deliver the message for this delivery?
     *
     *  true  — a matching accepted/delivered event exists (it went out)
     *  false — queried successfully, no matching event (treat as not sent)
     *  null  — could not determine (not configured / API error); caller must not act
     */
    public function wasAccepted(InvoiceDelivery $delivery): ?bool
    {
        $domain = config('services.mailgun.domain');
        $secret = config('services.mailgun.secret');
        $endpoint = config('services.mailgun.endpoint', 'api.mailgun.net');

        if (! $domain || ! $secret) {
            return null;
        }

        try {
            $response = Http::withBasicAuth('api', $secret)
                ->timeout(10)
                ->get("https://{$endpoint}/v3/{$domain}/events", [
                    'recipient' => $delivery->recipient,
                    'event' => 'accepted OR delivered',
                    'limit' => 50,
                ]);

            if (! $response->successful()) {
                Log::warning('mailgun_event_lookup.http_error', [
                    'delivery_id' => $delivery->id,
                    'status' => $response->status(),
                ]);

                return null;
            }

            foreach ((array) data_get($response->json(), 'items', []) as $item) {
                if ((string) data_get($item, 'user-variables.delivery_id') === (string) $delivery->id) {
                    return true;
                }
            }

            return false;
        } catch (\Throwable $e) {
            Log::warning('mailgun_event_lookup.exception', [
                'delivery_id' => $delivery->id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
