<x-layouts::app.sidebar :title="$title ?? null">
    <flux:main class="bg-slate-50 dark:bg-slate-950">
        <div class="flex justify-end px-6 pt-4">
            <x-locale-switcher />
        </div>
        {{ $slot }}
    </flux:main>
</x-layouts::app.sidebar>
