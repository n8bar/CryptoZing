<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceLine;
use Illuminate\Validation\Validator;

/**
 * Lines as the invoice forms submit them: `lines[<key>][description|kind|quantity|rate_usd|applies_to[]]`.
 * Kind is item (the default), percentage, or subtotal. A percentage line's applies_to names lines above it
 * by their submitted key. A subtotal line sums the lines since the previous subtotal and stays out of the total.
 */
class InvoiceLines
{
    public static function rules(): array
    {
        return [
            'lines'                => ['required', 'array', 'min:1'],
            'lines.*.description'  => ['required', 'string', 'max:255'],
            'lines.*.kind'         => ['nullable', 'in:' . implode(',', InvoiceLine::KINDS)],
            'lines.*.quantity'     => ['required_unless:lines.*.kind,percentage,subtotal', 'nullable', 'numeric'],
            'lines.*.rate_usd'     => ['required_unless:lines.*.kind,subtotal', 'nullable', 'numeric'],
            'lines.*.applies_to'   => ['nullable', 'array'],
            'lines.*.applies_to.*' => ['integer'],
        ];
    }

    public static function messages(): array
    {
        return [
            'lines.*.quantity.required_unless' => 'The quantity field is required.',
            'lines.*.rate_usd.required_unless' => 'The rate (USD) field is required.',
        ];
    }

    /** Field names for validation messages, so an error reads "rate (USD)" rather than "lines.0.rate_usd". */
    public static function attributes(): array
    {
        return [
            'lines.*.description' => 'description',
            'lines.*.quantity'    => 'quantity',
            'lines.*.rate_usd'    => 'rate (USD)',
            'lines.*.applies_to'  => 'applies to',
        ];
    }

    /** Each percentage line must pick at least one line above it. */
    public static function validateTargets(Validator $validator): void
    {
        $lines = $validator->getData()['lines'] ?? [];
        if (! is_array($lines)) {
            return;
        }

        foreach ($lines as $key => $line) {
            if (self::kind($line) === 'percentage' && self::targets($lines, $key, $line) === []) {
                $validator->errors()->add("lines.{$key}.applies_to", 'Pick at least one line above this percentage applies to.');
            }
        }
    }

    /** The submitted total, rounded to cents. */
    public static function total(array $lines): float
    {
        return round(array_sum(self::amounts($lines, counted: true)), 2);
    }

    /** Replace the invoice's lines with the submitted ones and recalculate its total. */
    public static function replaceFor(Invoice $invoice, array $lines): void
    {
        InvoiceLine::withoutEvents(function () use ($invoice, $lines) {
            $invoice->lines()->delete();

            $ids = [];
            $position = 0;
            foreach ($lines as $key => $line) {
                $kind = self::kind($line);
                $ids[$key] = $invoice->lines()->create([
                    'position'    => ++$position,
                    'description' => $line['description'],
                    'kind'        => $kind,
                    'quantity'    => $kind === 'item' ? $line['quantity'] : 1,
                    'rate_usd'    => $kind === 'subtotal' ? 0 : $line['rate_usd'],
                ])->id;
            }

            foreach ($lines as $key => $line) {
                if (self::kind($line) === 'percentage') {
                    $targetIds = array_values(array_map(fn ($t) => $ids[$t], self::targets($lines, $key, $line)));
                    InvoiceLine::whereKey($ids[$key])->update(['applies_to' => json_encode($targetIds)]);
                }
            }
        });

        $invoice->recalculateTotalFromLines();
    }

    /** True when the submitted lines differ from the invoice's current lines. */
    public static function changed(Invoice $invoice, array $lines): bool
    {
        $current = $invoice->lines->map(fn (InvoiceLine $l) => [
            $l->description, $l->kind, round((float) $l->quantity, 4), round((float) $l->rate_usd, 4),
        ])->values()->all();
        $submitted = array_values(array_map(function ($l) {
            $kind = self::kind($l);

            return [
                $l['description'], $kind,
                round((float) ($kind === 'item' ? $l['quantity'] : 1), 4),
                round((float) ($kind === 'subtotal' ? 0 : $l['rate_usd']), 4),
            ];
        }, $lines));

        return $current !== $submitted;
    }

    private static function kind(array $line): string
    {
        return in_array($line['kind'] ?? null, InvoiceLine::KINDS, true) ? $line['kind'] : 'item';
    }

    /** Submitted keys this percentage line applies to: lines above it that exist. */
    private static function targets(array $lines, $key, array $line): array
    {
        $above = array_map('strval', array_slice(array_keys($lines), 0, array_search($key, array_keys($lines), false)));
        $targets = array_map('intval', (array) ($line['applies_to'] ?? []));

        return array_values(array_filter($targets, fn ($t) => in_array((string) $t, $above, true)));
    }

    /** Each submitted line's amount by key, in order; with $counted, subtotal lines are left out. */
    private static function amounts(array $lines, bool $counted = false): array
    {
        $amounts = [];
        $running = 0.0;
        foreach ($lines as $key => $line) {
            $kind = self::kind($line);
            $amounts[$key] = match ($kind) {
                'subtotal'   => $running,
                'percentage' => array_sum(array_map(fn ($t) => $amounts[$t] ?? 0, self::targets($lines, $key, $line))) * (float) $line['rate_usd'] / 100,
                default      => (float) $line['quantity'] * (float) $line['rate_usd'],
            };
            $running = $kind === 'subtotal' ? 0.0 : $running + $amounts[$key];
        }

        return $counted ? array_filter($amounts, fn ($a, $k) => self::kind($lines[$k]) !== 'subtotal', ARRAY_FILTER_USE_BOTH) : $amounts;
    }
}
