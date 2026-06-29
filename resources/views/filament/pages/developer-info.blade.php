<x-filament-panels::page>
    <x-filament::section>
        <div style="display: flex; align-items: flex-start; gap: 1.5rem;">
            <div style="flex-shrink: 0;">
                <div style="height: 5rem; width: 5rem; border-radius: 9999px; background-color: #eaf4f1; display: flex; align-items: center; justify-content: center; color: #87A878; font-weight: bold; font-size: 1.5rem;">
                    AF
                </div>
            </div>
            <div>
                <h2 style="font-size: 1.5rem; font-weight: bold; margin: 0; padding: 0;">Muhammad Adam Fahmil 'Ilmi</h2>
                <p style="color: #6b7280; margin-bottom: 1rem; margin-top: 0.25rem;">Pengembang Acufara AI Clinic</p>
                
                <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                    <a href="mailto:adamfahmil020@gmail.com" style="color: #87A878; text-decoration: none; display: flex; align-items: center; gap: 0.5rem;">
                        <x-heroicon-m-envelope style="width: 1.25rem; height: 1.25rem; flex-shrink: 0;" /> adamfahmil020@gmail.com
                    </a>
                    <a href="https://www.linkedin.com/in/adamfahmil/" target="_blank" style="color: #87A878; text-decoration: none; display: flex; align-items: center; gap: 0.5rem;">
                        <x-heroicon-m-link style="width: 1.25rem; height: 1.25rem; flex-shrink: 0;" /> https://www.linkedin.com/in/adamfahmil/
                    </a>
                    <a href="https://github.com/adamfahmil96/" target="_blank" style="color: #87A878; text-decoration: none; display: flex; align-items: center; gap: 0.5rem;">
                        <x-heroicon-m-code-bracket style="width: 1.25rem; height: 1.25rem; flex-shrink: 0;" /> https://github.com/adamfahmil96/
                    </a>
                </div>
            </div>
        </div>
    </x-filament::section>

    <!-- Header Change Logs -->
    <div style="margin-top: 1.5rem; display: flex; flex-direction: column; gap: 1rem;">
        <h3 class="text-gray-950 dark:text-white" style="font-size: 1.25rem; font-weight: bold; margin: 0; padding: 0;">Catatan Perubahan (Change Logs)</h3>

        <!-- Change Log v2.0 -->
        <x-filament::section collapsible>
            <x-slot name="heading">
                Acufara v2.0 (Saat Ini)
            </x-slot>
            <x-slot name="description">
                Penambahan riwayat medis (penyakit & alergi) pada form booking dan notifikasi WhatsApp
            </x-slot>
            
            @include('filament.pages.changelogs.v2-0')
        </x-filament::section>

        <!-- Change Log v1.2 -->
        <x-filament::section collapsible collapsed>
            <x-slot name="heading">
                Acufara v1.2
            </x-slot>
            <x-slot name="description">
                Optimasi performa kalender appointment, database indexing, dan masking data finansial untuk akun demo.
            </x-slot>
            
            @include('filament.pages.changelogs.v1-2')
        </x-filament::section>

        <!-- Change Log v1.1 -->
        <x-filament::section collapsible collapsed>
            <x-slot name="heading">
                Acufara v1.1
            </x-slot>
            <x-slot name="description">
                Pembaruan fitur AI, optimalisasi rute, peta geocoding dan infrastruktur PWA/GCS
            </x-slot>
            
            @include('filament.pages.changelogs.v1-1')
        </x-filament::section>

        <!-- Change Log v1.0 -->
        <x-filament::section collapsible collapsed>
            <x-slot name="heading">
                Acufara v1.0 (Rilis Awal / MVP)
            </x-slot>
            <x-slot name="description">
                Pembangunan fondasi infrastruktur, operasional klinik, dan integrasi WhatsApp OTP
            </x-slot>
            
            @include('filament.pages.changelogs.v1-0')
        </x-filament::section>
    </div>
</x-filament-panels::page>
