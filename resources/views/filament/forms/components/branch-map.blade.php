{{-- Interactive Leaflet Map for Branch Form --}}
{{-- Expects: $lat, $lng, $mapId --}}
@php
    $safeLat  = is_numeric($lat)  ? (float) $lat  : -7.5666;
    $safeLng  = is_numeric($lng)  ? (float) $lng  : 110.8166;
@endphp

<div style="width:100%"
    x-data="{
        lat: {{ $safeLat }},
        lng: {{ $safeLng }},
        map: null,
        marker: null,
        init() {
            this.loadLeaflet().then(() => {
                this.initMap();
            });
            
            // Watch jika server (Livewire) mengubah state lat/lng (misal via Geocode otomatis)
            this.$watch('lat', (value) => {
                if (this.map && this.marker) {
                    let current = this.marker.getLatLng();
                    if (current.lat !== parseFloat(value)) {
                        this.marker.setLatLng([value, this.lng]);
                        this.map.setView([value, this.lng]);
                    }
                }
            });
            
            this.$watch('lng', (value) => {
                if (this.map && this.marker) {
                    let current = this.marker.getLatLng();
                    if (current.lng !== parseFloat(value)) {
                        this.marker.setLatLng([this.lat, value]);
                        this.map.setView([this.lat, value]);
                    }
                }
            });
        },
        loadLeaflet() {
            return new Promise((resolve) => {
                if (window.L) {
                    resolve();
                    return;
                }
                
                let css = document.createElement('link');
                css.rel = 'stylesheet';
                css.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
                document.head.appendChild(css);
                
                let script = document.createElement('script');
                script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
                script.onload = () => resolve();
                document.head.appendChild(script);
            });
        },
        initMap() {
            let container = this.$refs.map;
            
            // Mencegah error 'Map container is already initialized' 
            // jika Alpine re-initialize tapi DOM dipertahankan Livewire
            if (this.map) {
                this.map.remove();
                this.map = null;
            }
            if (container && container._leaflet_id) {
                container._leaflet_id = null;
                container.innerHTML = '';
            }
            
            this.map = L.map(container).setView([this.lat, this.lng], 14);
            
            // Tambahkan filter gelap untuk map di dark mode
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap',
                className: 'map-tiles'
            }).addTo(this.map);

            this.marker = L.marker([this.lat, this.lng], { draggable: true })
                .addTo(this.map)
                .bindPopup('<b>Lokasi Cabang</b><br>Geser atau klik peta untuk mengubah posisi.');

            this.marker.on('dragend', (e) => {
                let pos = e.target.getLatLng();
                this.updateForm(pos.lat, pos.lng);
            });

            this.map.on('click', (e) => {
                this.marker.setLatLng(e.latlng);
                this.updateForm(e.latlng.lat, e.latlng.lng);
            });

            // Perbaiki issue ukuran map saat diload dalam modal Filament
            let invalidate = () => {
                if (this.map) this.map.invalidateSize();
            };
            setTimeout(invalidate, 100);
            setTimeout(invalidate, 500);
            setTimeout(invalidate, 1000);
            setTimeout(invalidate, 2000);
        },
        updateForm(lat, lng) {
            this.lat = lat;
            this.lng = lng;
            
            // Cari input berdasarkan placeholder karena ID dinamis di Filament
            let latInput = document.querySelector('input[placeholder=&quot;-7.5666&quot;]');
            let lngInput = document.querySelector('input[placeholder=&quot;110.8166&quot;]');
            
            if (latInput) {
                latInput.value = parseFloat(lat).toFixed(8);
                latInput.dispatchEvent(new Event('input', { bubbles: true }));
            }
            if (lngInput) {
                lngInput.value = parseFloat(lng).toFixed(8);
                lngInput.dispatchEvent(new Event('input', { bubbles: true }));
            }
        }
    }"
>
    <style>
        /* Dukungan dark mode untuk tiles map */
        .fi-theme-dark .map-tiles {
            filter: brightness(0.6) invert(1) contrast(3) hue-rotate(200deg) saturate(0.3) brightness(0.7);
        }
    </style>
    <!-- Tambahkan wire:ignore agar Livewire tidak menimpa elemen DOM Leaflet -->
    <div
        wire:ignore
        x-ref="map"
        style="width:100%; height:350px; border-radius:0.5rem; border:1px solid #374151; position:relative; z-index:10;"
    ></div>
    <p style="font-size:0.75rem; color:#6b7280; margin-top:0.375rem;">
        💡 Klik di peta atau geser marker untuk menyesuaikan posisi cabang secara manual.
    </p>
</div>
