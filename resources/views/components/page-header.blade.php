@props(['title'])

<header class="bg-white dark:bg-gray-800 shadow">
    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 flex flex-wrap items-center justify-between gap-3">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ $title }}</h2>

        @isset($actions)
            <div class="flex flex-wrap gap-2">
                {{ $actions }}
            </div>
        @endisset
    </div>
</header>
