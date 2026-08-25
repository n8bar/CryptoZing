<?php

namespace App\Listeners;

use App\Services\MailAlias;
use Illuminate\Mail\Events\MessageSending;
use Symfony\Component\Mime\Address;

/**
 * Rewrites every outbound recipient to the alias domain (#175).
 *
 * This hangs off MessageSending rather than any single mail path, so a new
 * mailable is covered without remembering to opt in. PRODUCT_SPEC.md scopes
 * aliasing to outbound mail, not to invoice delivery.
 */
class ApplyMailAlias
{
    public function __construct(private readonly MailAlias $alias)
    {
    }

    public function handle(MessageSending $event): void
    {
        if (! $this->alias->isEnabled()) {
            return;
        }

        $message = $event->message;

        foreach (['To', 'Cc', 'Bcc'] as $header) {
            $existing = $message->{'get'.$header}();

            if ($existing === []) {
                continue;
            }

            $message->{lcfirst($header)}(...$this->convertAll($existing));
        }
    }

    /**
     * @param  array<int, Address>  $addresses
     * @return array<int, Address>
     */
    private function convertAll(array $addresses): array
    {
        return array_map(
            fn (Address $address) => new Address(
                (string) $this->alias->convert($address->getAddress()),
                $address->getName()
            ),
            $addresses
        );
    }
}
