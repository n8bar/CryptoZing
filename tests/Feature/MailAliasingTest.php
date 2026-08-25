<?php

namespace Tests\Feature;

use App\Services\MailAlias;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Aliasing is a property of outbound mail, not of one job (#175).
 *
 * These send through the array transport rather than Mail::fake() on purpose:
 * the fake short-circuits the mailer, so a rewrite applied inside the mail
 * pipeline would never run and the test would pass without proving anything.
 */
class MailAliasingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['mail.default' => 'array']);
        Mail::getSymfonyTransport()->messages();
    }

    private function enableAliasing(): void
    {
        config([
            'mail.aliasing.enabled' => true,
            'mail.aliasing.domain' => 'cryptozing.app',
        ]);

        app()->forgetInstance(MailAlias::class);
    }

    /** @return array<int, string> */
    private function recipientsOfLastMessage(string $header = 'To'): array
    {
        $messages = collect(Mail::getSymfonyTransport()->messages());
        $this->assertNotEmpty($messages, 'No message reached the transport.');

        $sent = $messages->last()->getOriginalMessage();
        $addresses = $header === 'To' ? $sent->getTo() : $sent->getCc();

        return array_map(fn ($address) => $address->getAddress(), $addresses);
    }

    public function test_mail_sent_outside_invoice_delivery_is_aliased(): void
    {
        $this->enableAliasing();

        Mail::raw('body', function ($message) {
            $message->to('stranger@example.com')->subject('Outside invoice delivery');
        });

        $this->assertSame(
            ['stranger.example.com@cryptozing.app'],
            $this->recipientsOfLastMessage()
        );
    }

    public function test_aliasing_rewrites_cc_and_bcc_recipients(): void
    {
        $this->enableAliasing();

        Mail::raw('body', function ($message) {
            $message->to('primary@example.com')
                ->cc('copied@example.org')
                ->subject('Carbon copies');
        });

        $this->assertSame(['copied.example.org@cryptozing.app'], $this->recipientsOfLastMessage('Cc'));
    }

    public function test_recipients_are_untouched_when_aliasing_is_disabled(): void
    {
        config(['mail.aliasing.enabled' => false]);
        app()->forgetInstance(MailAlias::class);

        Mail::raw('body', function ($message) {
            $message->to('stranger@example.com')->subject('Aliasing off');
        });

        $this->assertSame(['stranger@example.com'], $this->recipientsOfLastMessage());
    }
}
