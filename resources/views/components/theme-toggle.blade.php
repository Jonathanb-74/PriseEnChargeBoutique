@php
    $toggleScript = <<<'JS'
        if (document.documentElement.classList.contains('dark')) {
            document.documentElement.classList.remove('dark');
            localStorage.theme = 'light';
        } else {
            document.documentElement.classList.add('dark');
            localStorage.theme = 'dark';
        }
    JS;
@endphp

<button
    type="button"
    x-data
    x-on:click="{{ $toggleScript }}"
    {{ $attributes->merge(['class' => 'inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-900 focus:outline-none transition duration-150 ease-in-out']) }}
    aria-label="Basculer le thème clair / sombre"
>
    {{-- Icone soleil : visible en mode sombre, clic pour repasser en clair --}}
    <svg class="h-5 w-5 hidden dark:block" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1.5m0 15V21m9-9h-1.5M4.5 12H3m15.364 6.364l-1.06-1.06M6.697 6.697l-1.06-1.06m12.727 0l-1.06 1.06M6.697 17.303l-1.06 1.06M16.5 12a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z" />
    </svg>

    {{-- Icone lune : visible en mode clair, clic pour passer en sombre --}}
    <svg class="h-5 w-5 block dark:hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z" />
    </svg>
</button>
