<script setup>
    import { ref, onMounted, watch } from 'vue';
    import "leaflet/dist/leaflet.css";
    import { LMap, LTileLayer, LMarker, LControlZoom } from "@vue-leaflet/vue-leaflet";
    import L from 'leaflet';
    
    const props = defineProps({
        modelValueLat: Number,
        modelValueLng: Number,
        modelValueAddress: String,
        modelValueBranchId: { type: [Number, String], default: null },
        activeBranches: { type: Array, default: () => [] },
        markers: { type: Array, default: () => [] },
        center: { type: Array, default: () => [-16.5000, -68.1500] },
        height: { type: String, default: '400px' },
        zoom: { type: Number, default: 13 }
    });
    
    const emit = defineEmits([
        'update:modelValueLat', 
        'update:modelValueLng', 
        'update:modelValueAddress', 
        'update:modelValueBranchId', 
        'coverage-status-change'
    ]);
    
    const zoom = ref(props.zoom);
    const mapRef = ref(null);
    const center = ref(props.center);
    const markerPosition = ref(props.modelValueLat ? [props.modelValueLat, props.modelValueLng] : props.center);
    const isInsideCoverage = ref(false);
    const closestBranchName = ref('');
    
    // --- LEAFLET SETUP ---
    onMounted(() => {
        if (typeof L !== 'undefined') {
            delete L.Icon.Default.prototype._getIconUrl;
            L.Icon.Default.mergeOptions({
                iconRetinaUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png',
                iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
                shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
            });
        }
        
        if (props.modelValueLat) {
            validateCoverage(props.modelValueLat, props.modelValueLng);
        }
    });
    
    // --- VALIDACIÓN DE COBERTURA ---
    const isPointInPolygon = (point, vs) => {
        let x = point[0], y = point[1];
        let inside = false;
        for (let i = 0, j = vs.length - 1; i < vs.length; j = i++) {
            let xi = vs[i][0], yi = vs[i][1];
            let xj = vs[j][0], yj = vs[j][1];
            let intersect = ((yi > y) != (yj > y)) && (x < (xj - xi) * (y - yi) / (yj - yi) + xi);
            if (intersect) inside = !inside;
        }
        return inside;
    };
    
    const validateCoverage = (lat, lng) => {
        let covered = false;
        let detectedBranchId = null;
        closestBranchName.value = '';
    
        for (const branch of props.activeBranches) {
            if (branch.coverage_polygon && branch.coverage_polygon.length > 2) {
                if (isPointInPolygon([lat, lng], branch.coverage_polygon)) {
                    covered = true;
                    closestBranchName.value = branch.name;
                    detectedBranchId = branch.id;
                    break;
                }
            }
        }
    
        isInsideCoverage.value = covered;
        emit('update:modelValueBranchId', detectedBranchId);
        emit('coverage-status-change', covered);
    };
    
    // --- DRAG MARKER ---
    const onMarkerDragEnd = async (event) => {
        const { lat, lng } = event.target.getLatLng();
        markerPosition.value = [lat, lng];
        
        emit('update:modelValueLat', lat);
        emit('update:modelValueLng', lng);
    
        // Validamos y emitimos el ID
        validateCoverage(lat, lng);
    
        // Geocoding
        try {
            const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`);
            const data = await response.json();
            if (data && data.display_name) {
                emit('update:modelValueAddress', data.display_name);
            }
        } catch (error) {
            console.error("Error geocoding:", error);
        }
    };
    
    // --- REFRESH MAP ---
    const refreshMap = () => {
        if (mapRef.value && mapRef.value.leafletObject) {
            mapRef.value.leafletObject.invalidateSize();
            if (props.modelValueLat) {
                mapRef.value.leafletObject.setView([props.modelValueLat, props.modelValueLng]);
            }
        }
    };
    
    defineExpose({ refreshMap });
    
    // Watch for props changes
    watch(() => props.center, (newCenter) => {
        center.value = newCenter;
    });
    
    watch(() => props.modelValueLat, (newLat) => {
        if (newLat && props.modelValueLng) {
            markerPosition.value = [newLat, props.modelValueLng];
            validateCoverage(newLat, props.modelValueLng);
        }
    });
    </script>
    
    <template>
        <div class="space-y-3">
            <!-- MAP CONTAINER -->
            <div class="rounded-xl border border-border overflow-hidden relative z-0" :style="{ height: height }">
                <l-map ref="mapRef" 
                       v-model:zoom="zoom" 
                       :center="center" 
                       :use-global-leaflet="false"
                       class="h-full w-full">
                    <l-tile-layer 
                        url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
                        attribution='&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                        layer-type="base"
                        name="OpenStreetMap"
                    />
                    <l-control-zoom position="bottomright" />
                    
                    <!-- MARKERS FOR BRANCHES INDEX -->
                    <l-marker 
                        v-for="marker in markers" 
                        :key="marker.id"
                        :lat-lng="[marker.latitude, marker.longitude]"
                        :icon="L.icon({
                            iconUrl: marker.is_active 
                                ? 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png'
                                : 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-red.png',
                            iconSize: [25, 41],
                            iconAnchor: [12, 41]
                        })"
                    >
                        <l-tooltip :options="{ permanent: false, direction: 'top' }">
                            {{ marker.name }} ({{ marker.city }})
                        </l-tooltip>
                    </l-marker>
                    
                    <!-- DRAGGABLE MARKER FOR SELECTION -->
                    <l-marker 
                        v-if="modelValueLat !== undefined"
                        :lat-lng="markerPosition" 
                        draggable 
                        @dragend="onMarkerDragEnd"
                    />
                </l-map>
                
                <!-- COVERAGE STATUS OVERLAY -->
                <div class="absolute top-3 left-3 right-3 z-[1000] pointer-events-none">
                    <div v-if="isInsideCoverage && closestBranchName" 
                         class="badge badge-success inline-flex items-center gap-1 shadow-lg animate-in">
                        ✓ Cobertura Disponible ({{ closestBranchName }})
                    </div>
                    <div v-else-if="!isInsideCoverage && modelValueLat" 
                         class="badge badge-warning inline-flex items-center gap-1 shadow-lg animate-in">
                        ⚠️ Fuera de cobertura
                    </div>
                </div>
            </div>
    
            <!-- STATUS MESSAGES -->
            <transition 
                enter-active-class="transition duration-300 ease-out" 
                enter-from-class="opacity-0 -translate-y-2" 
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition duration-200 ease-in" 
                leave-from-class="opacity-100 translate-y-0" 
                leave-to-class="opacity-0 -translate-y-2"
            >
                <div v-if="!isInsideCoverage && modelValueLat" class="alert alert-warning animate-in">
                    <p class="text-sm font-bold">Estás fuera de nuestra zona de reparto actual</p>
                    <p class="text-xs mt-1">Guardaremos tu dirección para futuras expansiones.</p>
                </div>
                <div v-else-if="isInsideCoverage" class="alert alert-success animate-in">
                    <p class="text-sm font-bold">¡Tenemos cobertura!</p>
                    <p class="text-xs mt-1">
                        Atendido por: <strong>{{ closestBranchName }}</strong>
                    </p>
                </div>
            </transition>
        </div>
    </template>
    
    <style scoped>
    .leaflet-container {
        background-color: hsl(var(--muted) / 0.3);
    }
    </style>