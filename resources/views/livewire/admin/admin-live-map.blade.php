<div wire:ignore>
    <div id="liveMap" style="height: 75vh; width: 100%;" class="de-card overflow-hidden"></div>

    <script>
        document.addEventListener('livewire:navigated', () => {
            initMap();
        });

        document.addEventListener('DOMContentLoaded', () => {
            initMap();
        });

        let map, markers = { riders: {}, orders: {} };

        function initMap() {
            if (map) return;
            
            map = L.map('liveMap', { zoomControl: false }).setView([30.0444, 31.2357], 13);
            L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
                attribution: '&copy; OpenStreetMap &copy; CARTO'
            }).addTo(map);

            L.control.zoom({ position: 'bottomright' }).addTo(map);
            
            updateMarkers(@json($riders), @json($orders));
        }

        window.addEventListener('livewire:load', () => {
            Livewire.on('dataUpdated', (data) => {
                updateMarkers(data.riders, data.orders);
            });
        });

        // Handle events from Livewire component
        @if(isset($this))
        document.addEventListener('livewire:init', () => {
            Livewire.on('echo:admin.orders,OrderStatusUpdated', (event) => {
                @this.loadData();
            });
            Livewire.on('echo:admin.riders,RiderLocationUpdated', (event) => {
                @this.loadData();
            });
        });
        @endif

        function updateMarkers(riders, orders) {
            if (!map) return;

            // Update Riders
            riders.forEach(rider => {
                if (!rider.lat || !rider.lng) return;
                const key = `rider_${rider.id}`;
                const iconClass = rider.is_available ? 'available' : 'busy';
                if (markers.riders[key]) {
                    markers.riders[key].setLatLng([rider.lat, rider.lng]);
                } else {
                    markers.riders[key] = L.marker([rider.lat, rider.lng], {
                        icon: L.divIcon({
                            html: `<div class="map-marker rider-marker ${iconClass}"><i class="fas fa-motorcycle"></i></div>`,
                            className: '', iconSize: [25, 25]
                        })
                    }).addTo(map).bindPopup(`<b>${rider.name}</b><br>${rider.is_available ? 'Available' : 'Busy'}`);
                }
            });

            // Update Orders (Restaurant locations)
            orders.forEach(order => {
                const key = `order_${order.id}`;
                if (!markers.orders[key]) {
                    markers.orders[key] = L.marker([order.lat, order.lng], {
                        icon: L.divIcon({
                            html: `<div class="map-marker res-marker active"><i class="fas fa-store"></i></div>`,
                            className: '', iconSize: [30, 30]
                        })
                    }).addTo(map).bindPopup(`<b>Order #${order.id}</b><br>${order.restaurant_name}<br>Status: ${order.status}`);
                }
            });
        }
    </script>
</div>
