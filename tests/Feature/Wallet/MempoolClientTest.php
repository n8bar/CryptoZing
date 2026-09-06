<?php

namespace Tests\Feature\Wallet;

use App\Services\Blockchain\MempoolClient;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class MempoolClientTest extends TestCase
{
    private const BASE = 'https://mempool.example/testnet4/api';

    private function client(): MempoolClient
    {
        return new MempoolClient([
            'mempool' => ['testnet4_base' => self::BASE, 'timeout' => 1.0],
        ]);
    }

    /**
     * #187: when a pooled request fails at the transport level, Http::pool()
     * hands back the ConnectionException itself in that slot. The client must
     * treat it as a failed fetch for that address and keep the rest of the batch.
     */
    public function test_transport_failure_for_one_address_does_not_abort_the_batch(): void
    {
        Http::fake([
            self::BASE.'/address/tb1qdown/txs' => Http::failedConnection('cURL error 28: Connection timed out'),
            self::BASE.'/address/tb1qup/txs' => Http::response([
                ['txid' => 'tx-up', 'status' => ['confirmed' => true], 'vout' => []],
            ], 200),
        ]);

        Log::spy();

        $transactions = $this->client()->transactionsForAddresses('testnet4', ['tb1qdown', 'tb1qup']);

        $this->assertSame([], $transactions['tb1qdown']);
        $this->assertSame('tx-up', $transactions['tb1qup'][0]['txid']);

        Log::shouldHaveReceived('warning')
            ->once()
            ->withArgs(function (string $message, array $context): bool {
                return $message === 'Mempool transactions fetch failed'
                    && $context['address'] === 'tb1qdown'
                    && $context['status'] === null
                    && str_contains((string) $context['error'], 'cURL error 28');
            });
    }
}
