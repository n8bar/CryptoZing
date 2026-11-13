<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** An API create request's Idempotency-Key, tied to the invoice it made. */
class ApiIdempotencyKey extends Model
{
    protected $fillable = ['user_id', 'key', 'request_hash', 'invoice_id'];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class)->withTrashed();
    }
}
