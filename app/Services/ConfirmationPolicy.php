<?php

namespace App\Services;

/**
 * Resolves how many confirmations a payment needs before it counts, scaled by
 * the invoice's USD value at creation (spec: PARTIAL_PAYMENTS+CONFIRMATIONS.md
 * §Confirmation Gate). The ladder is "maxUsdExclusive:confirmations" pairs,
 * comma-separated, with "*" as the catch-all top tier.
 */
class ConfirmationPolicy
{
    public const DEFAULT_TIERS = '500:1,5000:2,50000:3,*:6';

    /** @var array<int, array{max: float|null, confirmations: int}> */
    private array $tiers;

    public function __construct(string $tiers)
    {
        $this->tiers = $this->parse($tiers) ?? $this->parse(self::DEFAULT_TIERS);
    }

    /**
     * Unknown value is treated as the top tier: when we cannot price the risk,
     * we demand the most confirmations, never the fewest.
     */
    public function requiredConfirmations(?float $usdValue): int
    {
        $last = end($this->tiers);
        if ($usdValue === null) {
            return $last['confirmations'];
        }

        foreach ($this->tiers as $tier) {
            if ($tier['max'] === null || $usdValue < $tier['max']) {
                return $tier['confirmations'];
            }
        }

        return $last['confirmations'];
    }

    public function minimumRequired(): int
    {
        return min(array_column($this->tiers, 'confirmations'));
    }

    /**
     * @return array<int, array{max: float|null, confirmations: int}>|null
     */
    private function parse(string $tiers): ?array
    {
        $parsed = [];

        foreach (explode(',', $tiers) as $pair) {
            [$max, $confirmations] = array_pad(explode(':', trim($pair), 2), 2, null);

            if ($confirmations === null || ! ctype_digit($confirmations)) {
                return null;
            }

            if ($max !== '*' && ! is_numeric($max)) {
                return null;
            }

            $parsed[] = [
                'max' => $max === '*' ? null : (float) $max,
                'confirmations' => (int) $confirmations,
            ];
        }

        if ($parsed === []) {
            return null;
        }

        usort($parsed, fn ($a, $b) => ($a['max'] ?? INF) <=> ($b['max'] ?? INF));

        return $parsed;
    }
}
