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
        $rateUnavailable = false;
        $changeMode = $request->boolean('change') && $donation && $donation->status === 'pending';

        $usdEquivalent = null;

        if (! $changeMode && $donation && $donation->status === 'pending') {
            $rate = BtcRate::current();
            $rateUsd = $rate['rate_usd'] ?? null;

            if ($donation->btc_amount_requested !== null) {
                $btcAmount = number_format((float) $donation->btc_amount_requested, 8, '.', '');
                $usdEquivalent = $rateUsd
                    ? round((float) $donation->btc_amount_requested * (float) $rateUsd, 2)
                    : null;
            } elseif ($rateUsd) {
                $btcAmount = number_format((float) $donation->usd_amount_requested / (float) $rateUsd, 8, '.', '');
            } else {
                $rateUnavailable = true;
            }

            $bitcoinUri = $donation->bitcoinUriForAmount($btcAmount !== null ? (float) $btcAmount : null);
        }

        $prefillUnit = $changeMode && $donation->btc_amount_requested !== null ? 'btc' : 'usd';

        return view('donate.show', [
            'donation' => $donation,
            'btcAmount' => $btcAmount,
            'bitcoinUri' => $bitcoinUri,
            'rateUnavailable' => $rateUnavailable,
            'usdEquivalent' => $usdEquivalent,
            'poolBusy' => (bool) $request->session()->get('donation_pool_busy'),
            'changeMode' => $changeMode,
            'prefillUnit' => $prefillUnit,
            'prefillAmount' => $changeMode
                ? ($prefillUnit === 'btc'
                    ? rtrim(rtrim(number_format((float) $donation->btc_amount_requested, 8, '.', ''), '0'), '.')
                    : $donation->usd_amount_requested)
                : null,
        ]);
    }

    public function allocate(Request $request, DonationAddressAllocator $allocator): RedirectResponse
    {
        if ($request->filled('preset_amount')) {
            $request->merge(['amount' => $request->input('preset_amount'), 'unit' => 'usd']);
        }

        $request->validate(['unit' => ['nullable', 'in:usd,btc']]);
        $unit = $request->input('unit') === 'btc' ? 'btc' : 'usd';

        $validated = $request->validate([
            'amount' => $unit === 'btc'
                ? ['required', 'numeric', 'min:0.00001', 'max:1']
                : ['required', 'numeric', 'min:1', 'max:25000'],
        ]);

        $sessionDonationId = $request->session()->get('donation_id');

        $donation = $allocator->allocate(
            is_numeric($sessionDonationId) ? (int) $sessionDonationId : null,
            $unit,
            (float) $validated['amount']
        );

        if (! $donation) {
            return redirect()->route('donate.show')->with('donation_pool_busy', true);
        }

        $request->session()->put('donation_id', $donation->id);

        return redirect()->route('donate.show');
    }

    public function reset(Request $request): RedirectResponse
    {
        $request->session()->forget('donation_id');

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
