<?php

namespace App\Http\Controllers;

use App\Models\ApiKey;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class ApiKeySettingsController extends Controller
{
    public function edit(Request $request): View
    {
        return view('settings.api-keys', [
            'keys' => $request->user()->apiKeys()->orderBy('id')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:100']]);

        [, $plain] = ApiKey::issue($request->user(), $data['name']);

        return Redirect::route('settings.api-keys.edit')
            ->with('status', 'api-key-made')
            ->with('api_key_plain', $plain);
    }

    public function revoke(Request $request, ApiKey $apiKey): RedirectResponse
    {
        abort_unless($apiKey->user_id === $request->user()->id, 404);

        $apiKey->revoke();

        return Redirect::route('settings.api-keys.edit')->with('status', 'api-key-revoked');
    }
}
