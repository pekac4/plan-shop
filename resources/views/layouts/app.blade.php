<x-layouts::app.sidebar :title="$title ?? null">
    <flux:main class="bg-slate-50 dark:bg-slate-950">
        {{ $slot }}
    </flux:main>
</x-layouts::app.sidebar>
