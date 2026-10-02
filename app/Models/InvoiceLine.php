<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

class InvoiceLine extends Model
{
    protected $fillable = [
        'invoice_id', 'position', 'description', 'quantity', 'rate_usd', 'is_percentage', 'applies_to',
    ];

    protected $casts = [
        'position'      => 'integer',
        'quantity'      => 'decimal:4',
        'rate_usd'      => 'decimal:4',
        'is_percentage' => 'boolean',
        'applies_to'    => 'array',
    ];

    protected static function booted(): void
    {
        $recalculate = static function (InvoiceLine $line): void {
            $line->invoice?->recalculateTotalFromLines();
        };

        static::saved($recalculate);
        static::deleted($recalculate);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * This line's USD amount. A percentage line needs the amounts of the
     * lines it applies to, keyed by line id.
     */
    public function amountUsd(Collection $amountsById): float
    {
        if ($this->is_percentage) {
            $base = collect($this->applies_to ?? [])->sum(fn ($id) => (float) ($amountsById[$id] ?? 0));

            return $base * (float) $this->rate_usd / 100;
        }

        return (float) $this->quantity * (float) $this->rate_usd;
    }
}
