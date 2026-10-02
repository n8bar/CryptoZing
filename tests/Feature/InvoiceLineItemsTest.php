<?php

namespace Tests\Feature;

use App\Models\InvoiceLine;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\CreatesTestInvoices;

class InvoiceLineItemsTest extends TestCase
{
    use DatabaseTransactions;
    use CreatesTestInvoices;

    public function test_total_is_the_sum_of_lines_rounded_to_cents(): void
    {
        $invoice = $this->makeInvoice();

        $invoice->lines()->create(['position' => 1, 'description' => 'Design', 'quantity' => 3, 'rate_usd' => 33.333]);
        $invoice->lines()->create(['position' => 2, 'description' => 'Hosting', 'quantity' => 1, 'rate_usd' => 10]);

        $this->assertSame('110.00', $invoice->fresh()->amount_usd);
    }

    public function test_negative_line_reduces_the_total(): void
    {
        $invoice = $this->makeInvoice();

        $invoice->lines()->create(['position' => 1, 'description' => 'Design', 'quantity' => 1, 'rate_usd' => 100]);
        $invoice->lines()->create(['position' => 2, 'description' => 'Loyalty discount', 'quantity' => 1, 'rate_usd' => -15]);

        $this->assertSame('85.00', $invoice->fresh()->amount_usd);
    }

    public function test_percentage_line_applies_to_the_lines_it_picks(): void
    {
        $invoice = $this->makeInvoice();

        $taxable = $invoice->lines()->create(['position' => 1, 'description' => 'Parts', 'quantity' => 2, 'rate_usd' => 50]);
        $invoice->lines()->create(['position' => 2, 'description' => 'Labor', 'quantity' => 1, 'rate_usd' => 200]);
        $invoice->lines()->create([
            'position' => 3, 'description' => 'Sales tax', 'quantity' => 1, 'rate_usd' => 8.5,
            'is_percentage' => true, 'applies_to' => [$taxable->id],
        ]);

        $this->assertSame('308.50', $invoice->fresh()->amount_usd);
    }

    public function test_removing_a_line_updates_the_total(): void
    {
        $invoice = $this->makeInvoice();

        $invoice->lines()->create(['position' => 1, 'description' => 'Design', 'quantity' => 1, 'rate_usd' => 100]);
        $extra = $invoice->lines()->create(['position' => 2, 'description' => 'Extra', 'quantity' => 1, 'rate_usd' => 25]);

        $extra->delete();

        $this->assertSame('100.00', $invoice->fresh()->amount_usd);
        $this->assertInstanceOf(InvoiceLine::class, $invoice->lines()->first());
    }
}
