<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceLine;
use Illuminate\Validation\Validator;

/**
 * Lines as the invoice forms submit them: `lines[<key>][description|quantity|rate_usd|is_percentage|applies_to[]]`,
 * where applies_to names other lines by their submitted key.
 */
class InvoiceLines
{
    public static function rules(): array
    {
        return [
            'lines'                  => ['required', 'array', 'min:1'],
            'lines.*.description'    => ['required', 'string', 'max:255'],
            'lines.*.quantity'       => ['required', 'numeric'],
            'lines.*.rate_usd'       => ['required', 'numeric'],
            'lines.*.is_percentage'  => ['nullable', 'boolean'],
            'lines.*.applies_to'     => ['nullable', 'array'],
            'lines.*.applies_to.*'   => ['integer'],
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

    /** Each percentage line must pick at least one other submitted line. */
    public static function validateTargets(Validator $validator): void
    {
        $lines = $validator->getData()['lines'] ?? [];
        if (! is_array($lines)) {
            return;
        }

        foreach ($lines as $key => $line) {
            if (empty($line['is_percentage'])) {
                continue;
            }
            if (self::targets($lines, $key, $line) === []) {
                $validator->errors()->add("lines.{$key}.applies_to", 'Pick at least one other line this percentage applies to.');
            }
        }
    }

    /** The submitted total, rounded to cents. */
    public static function total(array $lines): float
    {
        $amounts = [];
        foreach ($lines as $key => $line) {
            if (empty($line['is_percentage'])) {
                $amounts[$key] = (float) $line['quantity'] * (float) $line['rate_usd'];
            }
        }
        foreach ($lines as $key => $line) {
            if (! empty($line['is_percentage'])) {
                $base = array_sum(array_map(fn ($t) => $amounts[$t] ?? 0, self::targets($lines, $key, $line)));
                $amounts[$key] = $base * (float) $line['rate_usd'] / 100;
            }
        }

        return round(array_sum($amounts), 2);
    }

    /** Replace the invoice's lines with the submitted ones and recalculate its total. */
    public static function replaceFor(Invoice $invoice, array $lines): void
    {
        InvoiceLine::withoutEvents(function () use ($invoice, $lines) {
            $invoice->lines()->delete();

            $ids = [];
            $position = 0;
            foreach ($lines as $key => $line) {
                $ids[$key] = $invoice->lines()->create([
                    'position'      => ++$position,
                    'description'   => $line['description'],
                    'quantity'      => ! empty($line['is_percentage']) ? 1 : $line['quantity'],
                    'rate_usd'      => $line['rate_usd'],
                    'is_percentage' => ! empty($line['is_percentage']),
                ])->id;
            }

            foreach ($lines as $key => $line) {
                if (! empty($line['is_percentage'])) {
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
            $l->description, round((float) $l->quantity, 4), round((float) $l->rate_usd, 4), $l->is_percentage,
        ])->values()->all();
        $submitted = array_values(array_map(fn ($l) => [
            $l['description'], round((float) (! empty($l['is_percentage']) ? 1 : $l['quantity']), 4), round((float) $l['rate_usd'], 4), ! empty($l['is_percentage']),
        ], $lines));

        return $current !== $submitted;
    }

    /** Submitted keys this percentage line applies to: other lines that exist. */
    private static function targets(array $lines, $key, array $line): array
    {
        $targets = array_map('intval', (array) ($line['applies_to'] ?? []));

        return array_values(array_filter($targets, fn ($t) => (string) $t !== (string) $key && array_key_exists($t, $lines)));
    }
}
