@php
    $details = isset($details) && is_string($details) && trim($details) !== '' ? $details : null;
@endphp

<x-error-layout code="403" title="Access denied">
    Sorry, you don't have permission.
    @if ($details)
        <span class="cz-error__detail">{{ $details }}</span>
    @endif
</x-error-layout>
