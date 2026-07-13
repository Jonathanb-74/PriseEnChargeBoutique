@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[rgb(var(--color-accent))] dark:focus:border-[rgb(var(--color-accent))] focus:ring-[rgb(var(--color-accent))] dark:focus:ring-[rgb(var(--color-accent))] rounded-md shadow-sm']) }}>
