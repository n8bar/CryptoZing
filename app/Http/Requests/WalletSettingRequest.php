<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\NormalizesWalletKeyInput;
use Illuminate\Foundation\Http\FormRequest;

class WalletSettingRequest extends FormRequest
{
    use NormalizesWalletKeyInput;

    /**
     * Determine if the user is authorized to make this request.
     */
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
