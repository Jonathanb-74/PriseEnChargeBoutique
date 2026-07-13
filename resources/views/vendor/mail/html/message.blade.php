@props(['mailTitle' => null])
<x-mail::layout>
{{-- Header --}}
<x-slot:header>
<x-mail::header :url="config('app.url')">
@if ($logoUrl = \App\Models\Setting::pdfLogoUrl())
<img src="{{ $logoUrl }}" alt="{{ $mailTitle ?? config('app.name') }}" style="max-height: 50px; max-width: 220px; width: auto; height: auto; display: block; margin: 0 auto 8px;"><br>
@endif
{{ $mailTitle ?? config('app.name') }}
</x-mail::header>
</x-slot:header>

{{-- Body --}}
{!! $slot !!}

{{-- Subcopy --}}
@isset($subcopy)
<x-slot:subcopy>
<x-mail::subcopy>
{!! $subcopy !!}
</x-mail::subcopy>
</x-slot:subcopy>
@endisset

{{-- Footer --}}
<x-slot:footer>
<x-mail::footer>
© {{ date('Y') }} {{ config('app.name') }}. {{ __('All rights reserved.') }}
</x-mail::footer>
</x-slot:footer>
</x-mail::layout>
