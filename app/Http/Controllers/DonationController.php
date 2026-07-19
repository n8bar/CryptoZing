<?php

namespace App\Http\Controllers;

use App\Models\Donation;
use App\Services\BtcRate;
use App\Services\DonationAddressAllocator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DonationController extends Controller
{
    public function show(Request $request): View
    {
        $donation = $this->sessionDonation($request);

        $btcAmount = null;
        $bitcoinUri = null;

        if ($donation && $donation->status === 'pending') {
            $rate = BtcRate::current();
            $rateUsd = $rate['rate_usd'] ?? null;

            if ($rateUsd) {
                $btcAmount = number_format((float) $donation->usd_amount_requested / (float) $rateUsd, 8, '.', '');
            }

            $bitcoinUri = $donation->bitcoinUriForAmount($btcAmount !== null ? (float) $btcAmount : null);
        }

        return view('donate.show', [
            'donation' => $donation,
            'btcAmount' => $btcAmount,
            'bitcoinUri' => $bitcoinUri,
        ]);
    }

    public function allocate(Request $request, DonationAddressAllocator $allocator): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:1', 'max:25000'],
        ]);

        $sessionDonationId = $request->session()->get('donation_id');

        $donation = $allocator->allocate(
            is_numeric($sessionDonationId) ? (int) $sessionDonationId : null,
            (float) $validated['amount']
        );

        $request->session()->put('donation_id', $donation->id);

        return redirect()->route('donate.show');
    }

    public function status(Request $request): JsonResponse
    {
        $donation = $this->sessionDonation($request);

        return response()->json([
            'paid' => (bool) ($donation && $donation->status === 'paid'),
        ]);
    }

    private function sessionDonation(Request $request): ?Donation
    {
        $donationId = $request->session()->get('donation_id');

        return is_numeric($donationId) ? Donation::find((int) $donationId) : null;
    }
}
