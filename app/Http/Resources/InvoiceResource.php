<?php

namespace App\Http\Resources;

use App\Models\Invoice;
use App\Models\InvoiceLine;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * An invoice as the developer API returns it; see docs/API.md §6.
 *
 * @mixin Invoice
 */
class InvoiceResource extends JsonResource
{
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->api_id,
            'number'     => $this->number,
            'status'     => $this->status,
            'client'     => ['name' => $this->client?->name, 'email' => $this->client?->email],
            'lines'      => $this->linesArray(),
            'total_usd'  => self::usd($this->amount_usd),
            'paid_usd'   => self::usd($this->sumPaymentsUsd()),
            'public_url' => $this->public_url,
            'payment'    => $this->hasNothingToPay() ? null : [
                'address'          => $this->payment_address,
                'amount_btc'       => $this->amount_btc === null ? null : number_format((float) $this->amount_btc, 8, '.', ''),
                'uri'              => $this->bitcoin_uri,
                'rate_usd_per_btc' => $this->btc_rate === null ? null : self::usd($this->btc_rate),
            ],
            'mail_state'    => $this->mailState(),
            'payment_state' => $this->paymentState(),
            'due_date'      => $this->due_date?->toDateString(),
            'created_at'    => $this->created_at?->utc()->format('Y-m-d\TH:i:s\Z'),
        ];
    }

    /** Lines in order; applies_to names earlier lines by position, starting at 0. */
    private function linesArray(): array
    {
        $lines = $this->lines()->get();
        $amounts = $this->resource->lineAmounts();
        $positionById = $lines->pluck('id')->flip();

        return $lines->map(function (InvoiceLine $line) use ($amounts, $positionById) {
            $out = ['description' => $line->description, 'kind' => $line->kind];

            if ($line->kind === 'item') {
                $out['quantity'] = number_format((float) $line->quantity, 4, '.', '');
                $out['rate_usd'] = self::usd($line->rate_usd, 4);
            } elseif ($line->kind === 'percentage') {
                $out['rate_usd'] = self::trim($line->rate_usd);
                $out['applies_to'] = collect($line->applies_to ?? [])->map(fn ($id) => $positionById[$id] ?? null)->filter(fn ($p) => $p !== null)->values()->all();
            }

            $out['amount_usd'] = self::usd($amounts[$line->id] ?? 0);

            return $out;
        })->all();
    }

    /** Two decimals, or up to $max when the value carries more. */
    private static function usd(mixed $value, int $max = 2): string
    {
        $full = number_format((float) $value, $max, '.', '');
        [$whole, $fraction] = explode('.', $full) + [1 => ''];
        $fraction = str_pad(rtrim($fraction, '0'), 2, '0');

        return $whole . '.' . $fraction;
    }

    private static function trim(mixed $value): string
    {
        return rtrim(rtrim(number_format((float) $value, 4, '.', ''), '0'), '.');
    }
}
