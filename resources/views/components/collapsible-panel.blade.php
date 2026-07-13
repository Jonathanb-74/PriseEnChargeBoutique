@props(['title', 'open' => false, 'summary' => null])

<div x-data="{ open: @js($open) }" class="bg-white dark:bg-gray-800 shadow sm:rounded-lg">
    <button type="button" @click="open = !open" class="w-full flex items-center justify-between gap-3 p-4 sm:p-6 text-left">
        <span class="font-medium text-gray-900 dark:text-gray-100">{{ $title }}</span>
        <span class="flex items-center gap-3 shrink-0">
            @if ($summary)
                <span class="text-xs text-gray-500 dark:text-gray-400">{{ $summary }}</span>
            @endif
            <svg x-bind:class="open ? 'rotate-180' : ''" class="h-4 w-4 text-gray-400 transition-transform duration-150" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
            </svg>
        </span>
    </button>
    <div x-show="open" x-transition class="px-4 sm:px-6 pb-4 sm:pb-6 border-t border-gray-100 dark:border-gray-700 pt-4">
        {{ $slot }}
    </div>
</div>
