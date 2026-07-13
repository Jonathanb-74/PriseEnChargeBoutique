<x-mail::message>
{!! nl2br(e($body)) !!}

Cordialement,<br>
{{ config('app.name') }}
</x-mail::message>
