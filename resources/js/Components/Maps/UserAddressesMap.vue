<script setup>
    import { ref, onMounted, computed } from 'vue';
    import "leaflet/dist/leaflet.css";
    import { LMap, LTileLayer, LMarker, LPopup, LTooltip } from "@vue-leaflet/vue-leaflet";
    import L from 'leaflet';
    
    const props = defineProps({
        addresses: { type: Array, default: () => [] }
    });
    
    const zoom = ref(13);
    // Centramos el mapa en la primera dirección del usuario, o en un default si no tiene.
    const center = computed(() => {
        if (props.addresses.length > 0) {
            return [props.addresses[0].latitude, props.addresses[0].longitude];
        }
        return [-16.5000, -68.1500]; // La Paz Default
    });
    
    // Fix Iconos Leaflet
    onMounted(() => {
        delete L.Icon.Default.prototype._getIconUrl;
        L.Icon.Default.mergeOptions({
            iconRetinaUrl: new URL('leaflet/dist/images/marker-icon-2x.png', import.meta.url).href,
            iconUrl: new URL('leaflet/dist/images/marker-icon.png', import.meta.url).href,
            shadowUrl: new URL('leaflet/dist/images/marker-shadow.png', import.meta.url).href,
        });
    });
    </script>
    
    <template>
        <div class="h-[300px] w-full rounded-xl overflow-hidden border border-gray-200 shadow-inner relative z-0">
            <l-map 
                v-model:zoom="zoom" 
                :center="center" 
                :use-global-leaflet="false"
            >
                <l-tile-layer url="https://tile.openstreetmap.org/{z}/{x}/{y}.png"></l-tile-layer>
    
                <l-marker 
                    v-for="addr in addresses" 
                    :key="addr.id"
                    :lat-lng="[addr.latitude, addr.longitude]"
                >
                    <l-tooltip>{{ addr.alias }}</l-tooltip>
                    <l-popup>
                        <div class="text-sm">
                            <strong class="block text-gray-900">{{ addr.alias }}</strong>
                            <span class="text-gray-600">{{ addr.address }}</span>
                            <div v-if="!addr.branch_id" class="mt-1 text-xs text-yellow-600 font-bold bg-yellow-100 p-1 rounded inline-block">
                                Sin Cobertura
                            </div>
                        </div>
                    </l-popup>
                </l-marker>
            </l-map>
        </div>
    </template>