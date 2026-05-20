<x-filament-panels::page>
    @if ($aiSuggestion)
        <x-filament::section>
            <x-slot name="heading">
                ✨ Rekomendasi Rute Perjalanan AI
            </x-slot>
            <x-slot name="description">
                Ini adalah saran otomatis dari AI. Mohon tetap periksa data asli di tabel untuk alamat pastinya.
            </x-slot>

            <div class="prose dark:prose-invert max-w-none">
                {!! str($aiSuggestion)->markdown() !!}
            </div>
        </x-filament::section>
    @endif

    {{ $this->table }}
</x-filament-panels::page>
