<x-filament-panels::page>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <style>
        .leaflet-container {
            z-index: 1 !important; /* Prevents overlap with Filament dropdowns */
        }
    </style>

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

    @if (count($mapLocations) > 1)
        <x-filament::section>
            <x-slot name="heading">
                🗺️ Visualisasi Peta Rute
            </x-slot>
            <x-slot name="description">
                Titik awal dan lokasi pasien yang perlu dikunjungi hari ini.
            </x-slot>

            <div
                wire:key="{{ $mapId }}"
                x-data="{
                    locations: @js($mapLocations),
                    mapId: '{{ $mapId }}',
                    map: null,
                    initMap() {
                        if (!window.L) {
                            setTimeout(() => this.initMap(), 100);
                            return;
                        }
                        this.map = L.map(this.mapId).setView([this.locations[0].lat, this.locations[0].lng], 13);
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            attribution: '&copy; OpenStreetMap'
                        }).addTo(this.map);
                        
                        const bounds = [];
                        const latlngs = [];
                        
                        this.locations.forEach((loc) => {
                            const isBranch = loc.is_branch;
                            const markerColor = isBranch ? '#ef4444' : '#3b82f6';
                            L.circleMarker([loc.lat, loc.lng], {
                                color: markerColor,
                                fillColor: markerColor,
                                fillOpacity: 1,
                                radius: isBranch ? 8 : 6,
                            }).addTo(this.map).bindPopup('<b>' + loc.name + '</b>');
                            
                            bounds.push([loc.lat, loc.lng]);
                            if (!isBranch) {
                                // Connect route points, we can optionally connect branch to first patient if desired
                                latlngs.push([loc.lat, loc.lng]);
                            }
                        });
                        
                        // Draw lines between patients
                        if (latlngs.length > 0) {
                            // Optionally draw a dashed line from Branch (index 0) to first patient (index 1)
                            if (this.locations.length > 1) {
                                L.polyline([
                                    [this.locations[0].lat, this.locations[0].lng],
                                    [latlngs[0][0], latlngs[0][1]]
                                ], {color: '#ef4444', dashArray: '5, 5', weight: 2}).addTo(this.map);
                            }
                            
                            // Line between patients
                            L.polyline(latlngs, {color: '#3b82f6', weight: 2}).addTo(this.map);
                        }
                        
                        if (bounds.length > 0) {
                            this.map.fitBounds(bounds, {padding: [50, 50]});
                        }
                        
                        setTimeout(() => this.map.invalidateSize(), 500);
                    }
                }"
                x-init="initMap()"
                id="{{ $mapId }}"
                class="w-full rounded-lg overflow-hidden border border-gray-300 dark:border-gray-700 relative z-0 shadow-sm"
                style="min-height: 400px;"
            ></div>
        </x-filament::section>
    @endif

    {{ $this->table }}
</x-filament-panels::page>
