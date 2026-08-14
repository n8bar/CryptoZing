<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\NormalizesWalletKeyInput;
use Illuminate\Foundation\Http\FormRequest;

class WalletAccountRequest extends FormRequest
{
    use NormalizesWalletKeyInput;

    protected $errorBag = 'walletAccount';

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

        return ['label' => ['required', 'string', 'max:64']]
            + $this->walletKeyRules($network, 255);
    }

    public function messages(): array
    {
        return $this->walletKeyMessages();
    }
}
