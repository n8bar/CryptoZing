<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Donation extends Model
{
    protected $fillable = [
        'derivation_index',
        'address',
        'network',
        'usd_amount_requested',
        'btc_amount_requested',
        'status',
        'txid',
        'sats_received',
        'paid_at',
        'notified_at',
        'allocated_at',
    ];

    protected function casts(): array
    {
        return [
            'derivation_index' => 'integer',
            'usd_amount_requested' => 'decimal:2',
            'btc_amount_requested' => 'decimal:8',
            'sats_received' => 'integer',
            'paid_at' => 'datetime',
            'notified_at' => 'datetime',
            'allocated_at' => 'datetime',
        ];
    }

    public function requestedAmountLabel(): string
    {
        if ($this->btc_amount_requested !== null) {
            return rtrim(rtrim(number_format((float) $this->btc_amount_requested, 8, '.', ''), '0'), '.') . ' BTC';
        }

        return '$' . number_format((float) $this->usd_amount_requested, 2) . ' USD';
    }

    public function bitcoinUriForAmount(?float $amountBtc): ?string
    {
        if (! $this->address) {
            return null;
        }

        $params = [];
        if ($amountBtc !== null && $amountBtc > 0) {
            $params['amount'] = number_format($amountBtc, 8, '.', '');
        }
        $params['label'] = 'CryptoZing donation';

        $query = http_build_query($params, '', '&', PHP_QUERY_RFC3986);

        return 'bitcoin:' . $this->address . ($query ? ('?' . $query) : '');
    }
}
