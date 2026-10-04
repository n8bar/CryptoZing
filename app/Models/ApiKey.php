<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * A developer API key. Only the SHA-256 hash is stored; the plain key is
 * returned once from issue() and never again.
 */
class ApiKey extends Model
{
    protected $fillable = ['user_id', 'name', 'key_hash', 'last_used_at', 'revoked_at'];

    protected $hidden = ['key_hash'];

    protected $casts = [
        'last_used_at' => 'datetime',
        'revoked_at'   => 'datetime',
    ];

    /** Make a key for the issuer. Returns [model, plain key]. */
    public static function issue(User $issuer, string $name): array
    {
        $plain = 'cz_' . Str::random(40);

        $key = $issuer->apiKeys()->create([
            'name'     => $name,
            'key_hash' => hash('sha256', $plain),
        ]);

        return [$key, $plain];
    }

    /** The live key matching a plain key, or null. */
    public static function findByPlain(string $plain): ?self
    {
        return static::where('key_hash', hash('sha256', $plain))->whereNull('revoked_at')->first();
    }

    public function revoke(): void
    {
        $this->forceFill(['revoked_at' => now()])->save();
    }

    public function touchUsed(): void
    {
        $this->forceFill(['last_used_at' => now()])->save();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
