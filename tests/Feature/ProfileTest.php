<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
        $response->assertSee('name="show_invoice_ids"', false);
        $response->assertSee(route('settings.notifications.edit'), false);
        $response->assertDontSee('Auto email paid receipts', false);
        $response->assertDontSee('Show overpayment gratuity note to clients', false);
        $response->assertDontSee('Show QR refresh reminder to clients', false);
    }

    public function test_profile_page_includes_password_visibility_toggles(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
        $response->assertSee('Show current password');
        $response->assertSee('Show new password');
        $response->assertSee('Show password confirmation');
        $response->assertSee("x-bind:type=\"showCurrentPassword ? 'text' : 'password'\"", false);
        $response->assertSee("x-bind:type=\"showNewPassword ? 'text' : 'password'\"", false);
        $response->assertSee("x-bind:type=\"showPasswordConfirmation ? 'text' : 'password'\"", false);
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create([
            'show_overpayment_gratuity_note' => false,
            'show_qr_refresh_reminder' => false,
            'auto_receipt_emails' => true,
        ]);

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'show_invoice_ids' => true,
                'auto_receipt_emails' => false,
                'show_overpayment_gratuity_note' => true,
                'show_qr_refresh_reminder' => true,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
        $this->assertTrue($user->show_invoice_ids);
        $this->assertTrue($user->auto_receipt_emails);
        $this->assertFalse($user->show_overpayment_gratuity_note);
        $this->assertFalse($user->show_qr_refresh_reminder);
    }

    public function test_settings_index_redirects_to_profile_tab(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('settings.index'))
            ->assertRedirect(route('profile.edit'));
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($user->fresh());
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->delete('/profile', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrorsIn('userDeletion', 'password')
            ->assertRedirect('/profile');

        $this->assertNotNull($user->fresh());
    }

    public function test_user_with_invoices_and_recorded_payments_can_delete_their_account(): void
    {
        $user = User::factory()->create();
        $invoice = $this->makeInvoice($user, ['number' => 'INV-DEL-PAID']);
        $reattributionTarget = $this->makeInvoice($user, ['number' => 'INV-DEL-TARGET']);
        $trashedInvoice = $this->makeInvoice($user, ['number' => 'INV-DEL-TRASHED']);

        $this->makePayment($invoice, ['txid' => 'tx-del-detected']);
        $this->makePayment($invoice, [
            'txid' => 'tx-del-reattributed',
            'accounting_invoice_id' => $reattributionTarget->id,
            'reattributed_at' => now(),
            'reattributed_by_user_id' => $user->id,
            'reattribute_reason' => 'Counts toward the other invoice',
        ]);
        $this->makePayment($trashedInvoice, ['txid' => 'tx-del-trashed']);
        $trashedInvoice->delete();

        $this->makeSessionRow($user);

        $response = $this
            ->actingAs($user)
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($user->fresh());
        $this->assertDatabaseMissing('invoices', ['user_id' => $user->id]);
        $this->assertDatabaseMissing('clients', ['user_id' => $user->id]);
        $this->assertDatabaseMissing('invoice_payments', ['invoice_id' => $invoice->id]);
        $this->assertDatabaseMissing('invoice_payments', ['accounting_invoice_id' => $reattributionTarget->id]);
        $this->assertDatabaseMissing('invoice_payments', ['invoice_id' => $trashedInvoice->id]);
        $this->assertDatabaseMissing('sessions', ['user_id' => $user->id]);
    }

    public function test_account_deletion_leaves_other_users_records_untouched(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $userInvoice = $this->makeInvoice($user, ['number' => 'INV-DEL-MINE']);
        $this->makePayment($userInvoice, ['txid' => 'tx-del-mine']);

        $otherInvoice = $this->makeInvoice($other, ['number' => 'INV-KEEP-OTHER']);
        $this->makePayment($otherInvoice, ['txid' => 'tx-keep-other']);
        $this->makeSessionRow($other);

        $this
            ->actingAs($user)
            ->delete('/profile', [
                'password' => 'password',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertNull($user->fresh());
        $this->assertNotNull($other->fresh());
        $this->assertDatabaseHas('invoices', ['id' => $otherInvoice->id]);
        $this->assertDatabaseHas('invoice_payments', ['invoice_id' => $otherInvoice->id, 'txid' => 'tx-keep-other']);
        $this->assertDatabaseHas('sessions', ['user_id' => $other->id]);
    }

    private function makeClient(User $owner, array $overrides = []): Client
    {
        $defaults = [
            'user_id' => $owner->id,
            'name' => 'Client ' . uniqid(),
            'email' => 'client@example.com',
            'notes' => null,
        ];

        return Client::create(array_merge($defaults, $overrides));
    }

    private function makeInvoice(User $owner, array $overrides = []): Invoice
    {
        $client = $this->makeClient($owner);

        $defaults = [
            'user_id' => $owner->id,
            'client_id' => $client->id,
            'number' => 'INV-' . strtoupper(uniqid()),
            'description' => 'Services',
            'amount_usd' => 150,
            'btc_rate' => 50000,
            'amount_btc' => 0.003,
            'payment_address' => 'tb1qw508d6qejxtdg4y5r3zarvary0c5xw7k3l0zz',
            'status' => 'draft',
            'invoice_date' => Carbon::now()->toDateString(),
            'due_date' => Carbon::now()->addWeek()->toDateString(),
        ];

        $invoice = Invoice::create(array_merge($defaults, $overrides));

        return $invoice->refresh();
    }

    private function makePayment(Invoice $invoice, array $overrides = []): InvoicePayment
    {
        $defaults = [
            'invoice_id' => $invoice->id,
            'txid' => 'tx-' . uniqid(),
            'sats_received' => 20_000,
            'detected_at' => now(),
            'confirmed_at' => now(),
            'usd_rate' => 50_000,
            'fiat_amount' => 10.00,
        ];

        return InvoicePayment::create(array_merge($defaults, $overrides));
    }

    private function makeSessionRow(User $user): void
    {
        DB::table('sessions')->insert([
            'id' => Str::random(40),
            'user_id' => $user->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
            'payload' => base64_encode(serialize([])),
            'last_activity' => now()->getTimestamp(),
        ]);
    }
}
