<?php

namespace Tests\Feature\Donations;

use App\Mail\DonationReceivedMail;
use App\Models\Donation;
use App\Services\Blockchain\MempoolClient;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class DonationPaymentSyncTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        config(['donations.notify_email' => 'operator@example.test']);
        Mail::fake();
    }

    private function makePendingDonation(string $address, int $index): Donation
    {
        return Donation::query()->create([
            'derivation_index' => $index,
            'address' => $address,
            'network' => 'testnet4',
            'usd_amount_requested' => 25.00,
            'status' => 'pending',
            'allocated_at' => now(),
        ]);
    }

    public function test_confirmed_payment_marks_donation_paid_and_queues_one_operator_mail(): void
    {
        $paidDonation = $this->makePendingDonation('tb1qdonation0', 0);
        $untouchedDonation = $this->makePendingDonation('tb1qdonation1', 1);

        $this->mock(MempoolClient::class, function ($mock) {
            $mock->shouldReceive('transactionsForAddresses')
                ->andReturn([
                    'tb1qdonation0' => [
                        [
                            'txid' => 'donation-tx-1',
                            'status' => ['confirmed' => true, 'block_height' => 250000],
                            'vout' => [
                                ['scriptpubkey_address' => 'tb1qdonation0', 'value' => 50000],
                            ],
                        ],
                    ],
                    'tb1qdonation1' => [],
                ]);
            $mock->shouldReceive('tipHeight')->andReturn(250000);
        });

        $this->artisan('wallet:watch-payments')->assertSuccessful();

        $this->assertDatabaseHas('donations', [
            'id' => $paidDonation->id,
            'status' => 'paid',
            'txid' => 'donation-tx-1',
            'sats_received' => 50000,
        ]);
        $this->assertNotNull($paidDonation->fresh()->paid_at);

        $this->assertDatabaseHas('donations', [
            'id' => $untouchedDonation->id,
            'status' => 'pending',
            'txid' => null,
        ]);

        Mail::assertQueued(DonationReceivedMail::class, function (DonationReceivedMail $mail) {
            return $mail->hasTo('operator@example.test');
        });
        Mail::assertQueuedCount(1);
    }

    /**
     * DONATIONS.md: the operator is notified when a donation is first seen,
     * before the confirmation gate settles it. The row stays pending.
     */
    public function test_unconfirmed_payment_stays_pending_and_queues_the_first_seen_operator_mail(): void
    {
        $donation = $this->makePendingDonation('tb1qdonation0', 0);

        $this->mock(MempoolClient::class, function ($mock) {
            $mock->shouldReceive('transactionsForAddresses')
                ->andReturn([
                    'tb1qdonation0' => [
                        [
                            'txid' => 'donation-tx-1',
                            'status' => ['confirmed' => false],
                            'vout' => [
                                ['scriptpubkey_address' => 'tb1qdonation0', 'value' => 50000],
                            ],
                        ],
                    ],
                ]);
            $mock->shouldReceive('tipHeight')->andReturn(250000);
        });

        $this->artisan('wallet:watch-payments')->assertSuccessful();

        $this->assertDatabaseHas('donations', [
            'id' => $donation->id,
            'status' => 'pending',
            'txid' => 'donation-tx-1',
            'sats_received' => 50000,
        ]);
        $this->assertNull($donation->fresh()->paid_at);
        $this->assertNotNull($donation->fresh()->notified_at);

        Mail::assertQueued(DonationReceivedMail::class, function (DonationReceivedMail $mail) {
            return $mail->hasTo('operator@example.test');
        });
        Mail::assertQueuedCount(1);
    }

    public function test_first_seen_mail_is_not_repeated_on_later_passes_or_at_confirmation(): void
    {
        $donation = $this->makePendingDonation('tb1qdonation0', 0);

        $unconfirmed = [
            'tb1qdonation0' => [
                [
                    'txid' => 'donation-tx-1',
                    'status' => ['confirmed' => false],
                    'vout' => [
                        ['scriptpubkey_address' => 'tb1qdonation0', 'value' => 50000],
                    ],
                ],
            ],
        ];
        $confirmed = [
            'tb1qdonation0' => [
                [
                    'txid' => 'donation-tx-1',
                    'status' => ['confirmed' => true, 'block_height' => 250000],
                    'vout' => [
                        ['scriptpubkey_address' => 'tb1qdonation0', 'value' => 50000],
                    ],
                ],
            ],
        ];

        $this->mock(MempoolClient::class, function ($mock) use ($unconfirmed, $confirmed) {
            $mock->shouldReceive('transactionsForAddresses')
                ->andReturn($unconfirmed, $unconfirmed, $confirmed);
            $mock->shouldReceive('tipHeight')->andReturn(250000);
        });

        $this->artisan('wallet:watch-payments')->assertSuccessful();
        Mail::assertQueuedCount(1);
        $this->assertSame('pending', $donation->fresh()->status);

        $this->artisan('wallet:watch-payments')->assertSuccessful();
        $this->artisan('wallet:watch-payments')->assertSuccessful();

        $this->assertDatabaseHas('donations', [
            'id' => $donation->id,
            'status' => 'paid',
            'txid' => 'donation-tx-1',
            'sats_received' => 50000,
        ]);
        $this->assertNotNull($donation->fresh()->paid_at);

        Mail::assertQueuedCount(1);
    }

    public function test_confirmed_payment_below_a_raised_fewest_tier_stays_pending(): void
    {
        config()->set('blockchain.confirmation_tiers', '*:2');
        app()->forgetInstance(\App\Services\ConfirmationPolicy::class);

        $donation = $this->makePendingDonation('tb1qdonation0', 0);

        $this->mock(MempoolClient::class, function ($mock) {
            $mock->shouldReceive('transactionsForAddresses')
                ->andReturn([
                    'tb1qdonation0' => [
                        [
                            'txid' => 'donation-tx-1',
                            'status' => ['confirmed' => true, 'block_height' => 250000],
                            'vout' => [
                                ['scriptpubkey_address' => 'tb1qdonation0', 'value' => 50000],
                            ],
                        ],
                    ],
                ]);
            $mock->shouldReceive('tipHeight')->andReturn(250000);
        });

        $this->artisan('wallet:watch-payments')->assertSuccessful();

        $this->assertDatabaseHas('donations', [
            'id' => $donation->id,
            'status' => 'pending',
        ]);
    }

    public function test_only_confirmed_transactions_count_toward_the_paid_total(): void
    {
        $donation = $this->makePendingDonation('tb1qdonation0', 0);

        $this->mock(MempoolClient::class, function ($mock) {
            $mock->shouldReceive('transactionsForAddresses')
                ->andReturn([
                    'tb1qdonation0' => [
                        [
                            'txid' => 'donation-tx-2',
                            'status' => ['confirmed' => false],
                            'vout' => [
                                ['scriptpubkey_address' => 'tb1qdonation0', 'value' => 20000],
                            ],
                        ],
                        [
                            'txid' => 'donation-tx-1',
                            'status' => ['confirmed' => true, 'block_height' => 250000],
                            'vout' => [
                                ['scriptpubkey_address' => 'tb1qdonation0', 'value' => 30000],
                                ['scriptpubkey_address' => 'tb1qothers', 'value' => 99999],
                            ],
                        ],
                    ],
                ]);
            $mock->shouldReceive('tipHeight')->andReturn(250001);
        });

        $this->artisan('wallet:watch-payments')->assertSuccessful();

        $this->assertDatabaseHas('donations', [
            'id' => $donation->id,
            'status' => 'paid',
            'sats_received' => 30000,
        ]);
    }

    public function test_unnotified_paid_donation_is_retried_on_the_next_run(): void
    {
        $donation = $this->makePendingDonation('tb1qdonation0', 0);
        $donation->forceFill([
            'status' => 'paid',
            'txid' => 'donation-tx-1',
            'sats_received' => 50000,
            'paid_at' => now(),
            'notified_at' => null,
        ])->save();

        $this->mock(MempoolClient::class, function ($mock) {
            $mock->shouldReceive('transactionsForAddresses')->andReturn([]);
        });

        $this->artisan('wallet:watch-payments')->assertSuccessful();

        Mail::assertQueued(DonationReceivedMail::class);
        $this->assertNotNull($donation->fresh()->notified_at);
    }

    public function test_sync_is_idempotent_for_already_paid_donations(): void
    {
        $paidDonation = $this->makePendingDonation('tb1qdonation0', 0);

        $this->mock(MempoolClient::class, function ($mock) {
            $mock->shouldReceive('transactionsForAddresses')
                ->andReturn([
                    'tb1qdonation0' => [
                        [
                            'txid' => 'donation-tx-1',
                            'status' => ['confirmed' => true, 'block_height' => 250000],
                            'vout' => [
                                ['scriptpubkey_address' => 'tb1qdonation0', 'value' => 50000],
                            ],
                        ],
                    ],
                ]);
            $mock->shouldReceive('tipHeight')->andReturn(250000);
        });

        $this->artisan('wallet:watch-payments')->assertSuccessful();
        $this->artisan('wallet:watch-payments')->assertSuccessful();

        $this->assertSame('paid', $paidDonation->fresh()->status);
        Mail::assertQueuedCount(1);
    }
}
