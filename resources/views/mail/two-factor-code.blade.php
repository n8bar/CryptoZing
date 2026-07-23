@component('mail::message', ['user' => $user])
# Verification code

Hi {{ $user->name ?? 'there' }},

Use this code to continue signing in to {{ $user->effectiveMailBrandName() }}:

@component('mail::panel')
# {{ $code }}
@endcomponent

This code expires in 10 minutes. If you didn't request it, you can safely ignore this email — your account is unchanged.

Thanks,<br>
{{ $user->effectiveMailBrandName() }}
@endcomponent
