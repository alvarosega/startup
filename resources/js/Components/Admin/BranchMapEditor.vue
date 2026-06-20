<script setup>
    import { onMounted, ref } from 'vue';
    import L from 'leaflet';
    import 'leaflet/dist/leaflet.css';
    import '@geoman-io/leaflet-geoman-free';
    import '@geoman-io/leaflet-geoman-free/dist/leaflet-geoman.css';
    
    const props = defineProps({
        modelValue: Array, 
        height: { type: String, default: '400px' }
    });
    
    const emit = defineEmits(['update:modelValue']);
    const mapContainer = ref(null);
    let map = null;
    
    onMounted(() => {
        // La Paz por defecto
        map = L.map(mapContainer.value).setView([-16.5000, -68.1193], 13);
    
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: 'OpenStreetMap'
        }).addTo(map);
    
        map.pm.addControls({
            position: 'topleft',
            drawCircle: false,
            drawMarker: false,
            drawPolyline: false,
            drawRectangle: true,
            drawPolygon: true,
            editMode: true,
            dragMode: false,
            cutPolygon: false,
            removalMode: true,
        });
    
        // Si es edición, dibujamos lo existente
        if (props.modelValue && props.modelValue.length > 0) {
            const polygon = L.polygon(props.modelValue, { color: '#3b82f6' }).addTo(map);
            map.fitBounds(polygon.getBounds());
            polygon.on('pm:edit', updateCoordinates);
        }
    
        map.on('pm:create', (e) => {
            // Borrar polígonos anteriores (solo 1 permitido)
            map.eachLayer((layer) => {
                if (layer instanceof L.Polygon && layer !== e.layer) {
                    map.removeLayer(layer);
                }
            });
            e.layer.on('pm:edit', updateCoordinates);
            updateCoordinates();
        });
    
        map.on('pm:remove', () => emit('update:modelValue', null));
    });
    
    function updateCoordinates() {
        const layers = map.pm.getGeomanLayers();
        if (layers.length > 0) {
            // Extraemos lat/lng limpios
            const latlngs = layers[0].getLatLngs()[0].map(c => [c.lat, c.lng]);
            emit('update:modelValue', latlngs);
        } else {
            emit('update:modelValue', null);
        }
    }
    </script>
    
    <template>
        <div ref="mapContainer" :style="{ height: height, width: '100%', zIndex: 0 }" class="rounded border border-gray-600"></div>
    </template>