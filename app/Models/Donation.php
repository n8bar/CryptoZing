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
        'status',
        'txid',
        'sats_received',
        'paid_at',
        'allocated_at',
    ];

    protected function casts(): array
    {
        return [
            'derivation_index' => 'integer',
            'usd_amount_requested' => 'decimal:2',
            'sats_received' => 'integer',
            'paid_at' => 'datetime',
            'allocated_at' => 'datetime',
        ];
    }
}
