<x-mail::message :mail-title="$emailTitle ?? null">
{!! nl2br(e($body)) !!}

{!! nl2br(e(\App\Models\Setting::emailSignature())) !!}
</x-mail::message>
