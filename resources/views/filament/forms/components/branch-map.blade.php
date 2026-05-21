{{-- Interactive Leaflet Map for Branch Form --}}
{{-- Expects: $lat, $lng, $mapId, $latStateKey, $lngStateKey --}}

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<div
    x-data="{
        map: null,
        marker: null,
        lat: {{ $lat ?? -7.5666 }},
        lng: {{ $lng ?? 110.8166 }},
        mapId: '{{ $mapId }}',
        latKey: '{{ $latStateKey }}',
        lngKey: '{{ $lngStateKey }}',

        init() {
            this.$nextTick(() => {
                this.initMap();

                // Listen for changes from Filament form fields (when user types)
                Livewire.on('branch-coords-update', ({ lat, lng }) => {
                    if (lat && lng) {
                        this.lat = parseFloat(lat);
                        this.lng = parseFloat(lng);
                        this.moveMarker(this.lat, this.lng);
                    }
                });
            });
        },

        initMap() {
            if (this.map) {
                this.map.remove();
                this.map = null;
            }

            this.map = L.map(this.mapId).setView([this.lat, this.lng], 13);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href=\"https://www.openstreetmap.org/copyright\">OpenStreetMap</a> contributors'
            }).addTo(this.map);

            // Red icon for branch
            const redIcon = L.divIcon({
                className: '',
                html: `<div style=\"
                    width: 24px; height: 24px;
                    background: #ef4444;
                    border: 3px solid white;
                    border-radius: 50% 50% 50% 0;
                    transform: rotate(-45deg);
                    box-shadow: 0 2px 8px rgba(0,0,0,0.4);
                \"></div>`,
                iconSize: [24, 24],
                iconAnchor: [12, 24],
            });

            this.marker = L.marker([this.lat, this.lng], {
                draggable: true,
                icon: redIcon
            }).addTo(this.map).bindPopup('<b>Lokasi Cabang</b><br>Geser untuk menyesuaikan posisi.').openPopup();

            // When user drags the marker, update Filament form fields
            this.marker.on('dragend', (e) => {
                const pos = e.target.getLatLng();
                this.lat = parseFloat(pos.lat.toFixed(8));
                this.lng = parseFloat(pos.lng.toFixed(8));
                this.$dispatch('set-branch-lat', this.lat);
                this.$dispatch('set-branch-lng', this.lng);
            });

            // When user clicks on map, move marker there
            this.map.on('click', (e) => {
                this.lat = parseFloat(e.latlng.lat.toFixed(8));
                this.lng = parseFloat(e.latlng.lng.toFixed(8));
                this.moveMarker(this.lat, this.lng);
                this.$dispatch('set-branch-lat', this.lat);
                this.$dispatch('set-branch-lng', this.lng);
            });

            setTimeout(() => this.map.invalidateSize(), 400);
        },

        moveMarker(lat, lng) {
            if (this.marker) {
                this.marker.setLatLng([lat, lng]);
                this.map.setView([lat, lng], this.map.getZoom());
            }
        }
    }"
    id="{{ $mapId }}-wrapper"
    style="width: 100%"
>
    <div
        id="{{ $mapId }}"
        style="width: 100%; height: 350px; border-radius: 0.5rem; overflow: hidden; border: 1px solid #374151; z-index: 0; position: relative;"
    ></div>
    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5">
        💡 Klik di peta atau geser marker merah untuk menyesuaikan posisi cabang secara manual.
    </p>
</div>
