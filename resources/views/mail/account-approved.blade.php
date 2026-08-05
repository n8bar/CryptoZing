@component('mail::message', ['user' => $user])
# Your account is approved

Hi {{ $user->name ?? 'there' }},

Your {{ config('app.name') }} account has been approved — you can sign in now.

@component('mail::button', ['url' => $loginUrl])
Sign in
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
