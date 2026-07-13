@props(['property', 'label' => 'Signature'])

<div>
    <x-input-label :value="$label" />
    <div x-data="signaturePad('{{ $property }}')" class="mt-1">
        <div class="border border-gray-300 dark:border-gray-700 rounded-md overflow-hidden bg-white">
            <canvas x-ref="canvas" class="w-full h-40 block" style="touch-action: none;"></canvas>
        </div>
        <button type="button" x-on:click="clear()" class="text-xs text-gray-500 dark:text-gray-400 mt-1">
            Effacer la signature
        </button>
    </div>
</div>
