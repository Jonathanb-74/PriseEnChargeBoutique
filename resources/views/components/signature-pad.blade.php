@props(['property', 'label' => 'Signature'])

<div>
    <x-input-label :value="$label" />
    <div x-data="signaturePad('{{ $property }}')" class="mt-1">
        <div :class="fullscreen ? 'fixed inset-0 z-50 bg-white p-4 flex flex-col' : 'relative'">
            <div x-show="fullscreen" class="flex items-center justify-between pb-3">
                <span class="text-sm font-medium text-gray-900">{{ $label }}</span>
                <div class="flex items-center gap-4">
                    <button type="button" x-on:click="clear()" class="text-sm text-gray-500">
                        Effacer
                    </button>
                    <button type="button" x-on:click="toggleFullscreen()" class="text-sm text-[rgb(var(--color-accent))] font-semibold">
                        Terminé
                    </button>
                </div>
            </div>
            <div :class="fullscreen ? 'flex-1 border border-gray-300 rounded-md overflow-hidden bg-white' : 'border border-gray-300 dark:border-gray-700 rounded-md overflow-hidden bg-white'">
                <canvas x-ref="canvas" :class="fullscreen ? 'w-full h-full block' : 'w-full h-40 block'" style="touch-action: none;"></canvas>
            </div>
        </div>
        <div class="flex items-center justify-between mt-1">
            <button type="button" x-on:click="clear()" class="text-xs text-gray-500 dark:text-gray-400">
                Effacer la signature
            </button>
            <button type="button" x-on:click="toggleFullscreen()" class="text-xs text-gray-500 dark:text-gray-400">
                Plein écran
            </button>
        </div>
    </div>
</div>
