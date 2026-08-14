<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\NormalizesWalletKeyInput;
use Illuminate\Foundation\Http\FormRequest;

class WalletKeyPreviewRequest extends FormRequest
{
    use NormalizesWalletKeyInput;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->prepareWalletKeyInput();
    }

    public function rules(): array
    {
        $network = config('wallet.default_network', 'testnet');

        return $this->walletKeyRules($network, 256);
    }

    public function messages(): array
    {
        return $this->walletKeyMessages();
    }
}
