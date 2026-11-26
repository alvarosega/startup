<script setup>
import { onMounted, onUnmounted, ref, nextTick } from 'vue';
import { Link } from '@inertiajs/vue3';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

// Corrección de iconos estándar de Leaflet en empaquetadores modernos (Vite)
delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
    iconRetinaUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png',
    iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
    shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
});

const props = defineProps({
    form: {
        type: Object,
        required: true
    },
    isEdit: {
        type: Boolean,
        required: true
    }
});

defineEmits(['submit']);

const mapContainer = ref(null);
let mapInstance = null;
let markerInstance = null;
let polygonInstance = null;

// Coordenadas por defecto (La Paz, Bolivia)
const DEFAULT_LAT = -16.50836;
const DEFAULT_LNG = -68.12628;

/**
 * Sincroniza y redibuja la capa geométrica del polígono de cobertura en Leaflet.
 */
const drawPolygonLayer = () => {
    if (polygonInstance && mapInstance) {
        mapInstance.removeLayer(polygonInstance);
        polygonInstance = null;
    }

    if (props.form.coverage_polygon && props.form.coverage_polygon.length > 0 && mapInstance) {
        // Formato interno: [longitud, latitud] -> Leaflet: [latitud, longitud]
        const leafletCoords = props.form.coverage_polygon.map(coord => [Number(coord[1]), Number(coord[0])]);
        polygonInstance = L.polygon(leafletCoords, {
            color: '#ef4444',
            fillColor: '#ef4444',
            fillOpacity: 0.18,
            weight: 2
        }).addTo(mapInstance);
    }
};

/**
 * Remueve el último vértice añadido al polígono.
 */
const removeLastVertex = () => {
    if (props.form.coverage_polygon && props.form.coverage_polygon.length > 0) {
        props.form.coverage_polygon.pop();
        drawPolygonLayer();
    }
};

/**
 * Purga la totalidad de los vértices del polígono de cobertura.
 */
const clearPolygon = () => {
    props.form.coverage_polygon = [];
    drawPolygonLayer();
};

onMounted(async () => {
    await nextTick();

    if (!mapContainer.value) {
        return;
    }

    const initLat = (props.form.latitude !== null && props.form.latitude !== undefined && props.form.latitude !== 0)
        ? Number(props.form.latitude)
        : DEFAULT_LAT;

    const initLng = (props.form.longitude !== null && props.form.longitude !== undefined && props.form.longitude !== 0)
        ? Number(props.form.longitude)
        : DEFAULT_LNG;

    mapInstance = L.map(mapContainer.value, {
        zoomControl: true,
        attributionControl: false
    }).setView([initLat, initLng], 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19
    }).addTo(mapInstance);

    markerInstance = L.marker([initLat, initLng], {
        draggable: true
    }).addTo(mapInstance);

    if (!props.form.latitude || !props.form.longitude) {
        props.form.latitude = initLat;
        props.form.longitude = initLng;
    }

    markerInstance.on('dragend', (e) => {
        const position = e.target.getLatLng();
        props.form.latitude = Number(position.lat.toFixed(6));
        props.form.longitude = Number(position.lng.toFixed(6));
    });

    mapInstance.on('click', (e) => {
        // Almacenar en formato [longitud, latitud] para MySQL GIS
        props.form.coverage_polygon.push([
            Number(e.latlng.lng.toFixed(6)),
            Number(e.latlng.lat.toFixed(6))
        ]);
        drawPolygonLayer();
    });

    if (props.form.coverage_polygon && props.form.coverage_polygon.length > 0) {
        drawPolygonLayer();
        const leafletCoords = props.form.coverage_polygon.map(coord => [Number(coord[1]), Number(coord[0])]);
        mapInstance.fitBounds(L.polygon(leafletCoords).getBounds());
    }

    // Recálculo de dimensiones tras montaje
    setTimeout(() => {
        if (mapInstance) {
            mapInstance.invalidateSize();
        }
    }, 250);
});

onUnmounted(() => {
    if (mapInstance) {
        mapInstance.off();
        mapInstance.remove();
        mapInstance = null;
    }
});
</script>

<template>
    <form @submit.prevent="$emit('submit')" class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
        
        <!-- Columna Izquierda: Parámetros y Tarifas -->
        <div class="glass-card p-5 md:p-6 rounded-2xl border border-white/60 dark:border-white/10 shadow-sm space-y-5">
            
            <div class="border-b border-border/50 pb-2.5">
                <h3 class="text-xs font-bold text-foreground uppercase tracking-wider flex items-center gap-1.5">
                    <span class="material-symbols-rounded text-primary text-[18px]">badge</span>
                    <span>Identificación y Ubicación Física</span>
                </h3>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                <div class="space-y-1.5">
                    <label class="text-[11px] font-semibold text-muted-foreground uppercase tracking-wider">Nombre de Sucursal *</label>
                    <input v-model="form.name" type="text" required class="admin-input" :class="{ 'border-error': form.errors.name }" placeholder="Centro de Distribución Norte" />
                    <p v-if="form.errors.name" class="text-error text-[11px] font-medium">{{ form.errors.name }}</p>
                </div>
                <div class="space-y-1.5">
                    <label class="text-[11px] font-semibold text-muted-foreground uppercase tracking-wider">Ciudad / Jurisdicción *</label>
                    <input v-model="form.city" type="text" required class="admin-input" :class="{ 'border-error': form.errors.city }" placeholder="La Paz" />
                    <p v-if="form.errors.city" class="text-error text-[11px] font-medium">{{ form.errors.city }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                <div class="space-y-1.5">
                    <label class="text-[11px] font-semibold text-muted-foreground uppercase tracking-wider">Teléfono Operativo</label>
                    <input v-model="form.phone" type="text" class="admin-input font-mono" :class="{ 'border-error': form.errors.phone }" placeholder="22445566" />
                    <p v-if="form.errors.phone" class="text-error text-[11px] font-medium">{{ form.errors.phone }}</p>
                </div>
                <div class="space-y-1.5">
                    <label class="text-[11px] font-semibold text-muted-foreground uppercase tracking-wider">Dirección Física</label>
                    <input v-model="form.address" type="text" class="admin-input" :class="{ 'border-error': form.errors.address }" placeholder="Av. Troncal Nro 450" />
                    <p v-if="form.errors.address" class="text-error text-[11px] font-medium">{{ form.errors.address }}</p>
                </div>
            </div>

            <div class="border-b border-border/50 pb-2.5 pt-1">
                <h3 class="text-xs font-bold text-foreground uppercase tracking-wider flex items-center gap-1.5">
                    <span class="material-symbols-rounded text-primary text-[18px]">payments</span>
                    <span>Tarifas, Capacidad y Reglas de Despacho</span>
                </h3>
            </div>

            <div class="space-y-1.5">
                <div class="flex items-center justify-between">
                    <label class="text-[11px] font-semibold text-muted-foreground uppercase tracking-wider">Límite de Pedidos Concurrentes</label>
                    <span class="text-[10px] text-muted-foreground">Kill-switch automático por saturación</span>
                </div>
                <input 
                    v-model.number="form.max_active_orders" 
                    type="number" 
                    step="1" 
                    min="1" 
                    max="1000" 
                    class="admin-input font-mono" 
                    :class="{ 'border-error': form.errors.max_active_orders }" 
                    placeholder="Vacío para capacidad ilimitada" 
                />
                <p v-if="form.errors.max_active_orders" class="text-error text-[11px] font-medium">{{ form.errors.max_active_orders }}</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                <div class="space-y-1.5">
                    <label class="text-[11px] font-semibold text-muted-foreground uppercase tracking-wider">Tarifa Base (Bs) *</label>
                    <input v-model.number="form.delivery_base_fee" type="number" step="0.01" min="0" required class="admin-input font-mono" :class="{ 'border-error': form.errors.delivery_base_fee }" />
                    <p v-if="form.errors.delivery_base_fee" class="text-error text-[11px] font-medium">{{ form.errors.delivery_base_fee }}</p>
                </div>
                <div class="space-y-1.5">
                    <label class="text-[11px] font-semibold text-muted-foreground uppercase tracking-wider">Precio / KM (Bs) *</label>
                    <input v-model.number="form.delivery_price_per_km" type="number" step="0.01" min="0" required class="admin-input font-mono" :class="{ 'border-error': form.errors.delivery_price_per_km }" />
                    <p v-if="form.errors.delivery_price_per_km" class="text-error text-[11px] font-medium">{{ form.errors.delivery_price_per_km }}</p>
                </div>
                <div class="space-y-1.5">
                    <label class="text-[11px] font-semibold text-muted-foreground uppercase tracking-wider">Surge Mult. *</label>
                    <input v-model.number="form.surge_multiplier" type="number" step="0.01" min="1" required class="admin-input font-mono" :class="{ 'border-error': form.errors.surge_multiplier }" />
                    <p v-if="form.errors.surge_multiplier" class="text-error text-[11px] font-medium">{{ form.errors.surge_multiplier }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                <div class="space-y-1.5">
                    <label class="text-[11px] font-semibold text-muted-foreground uppercase tracking-wider">Monto Mínimo (Bs) *</label>
                    <input v-model.number="form.min_order_amount" type="number" step="0.01" min="0" required class="admin-input font-mono" :class="{ 'border-error': form.errors.min_order_amount }" />
                    <p v-if="form.errors.min_order_amount" class="text-error text-[11px] font-medium">{{ form.errors.min_order_amount }}</p>
                </div>
                <div class="space-y-1.5">
                    <label class="text-[11px] font-semibold text-muted-foreground uppercase tracking-wider">Orden Pequeña (Bs) *</label>
                    <input v-model.number="form.small_order_fee" type="number" step="0.01" min="0" required class="admin-input font-mono" :class="{ 'border-error': form.errors.small_order_fee }" />
                    <p v-if="form.errors.small_order_fee" class="text-error text-[11px] font-medium">{{ form.errors.small_order_fee }}</p>
                </div>
                <div class="space-y-1.5">
                    <label class="text-[11px] font-semibold text-muted-foreground uppercase tracking-wider">% Fee Servicio *</label>
                    <input v-model.number="form.base_service_fee_percentage" type="number" step="0.01" min="0" max="100" required class="admin-input font-mono" :class="{ 'border-error': form.errors.base_service_fee_percentage }" />
                    <p v-if="form.errors.base_service_fee_percentage" class="text-error text-[11px] font-medium">{{ form.errors.base_service_fee_percentage }}</p>
                </div>
            </div>

            <!-- Flags Operativos -->
            <div class="flex flex-col sm:flex-row sm:items-center gap-5 pt-2">
                <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                    <input v-model="form.is_active" type="checkbox" class="rounded-md border-border/80 text-primary focus:ring-primary/20 bg-card/60 w-4 h-4" />
                    <span class="text-xs font-bold text-foreground uppercase tracking-wide">Nodo Activo</span>
                </label>
                <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                    <input v-model="form.is_default" type="checkbox" class="rounded-md border-border/80 text-primary focus:ring-primary/20 bg-card/60 w-4 h-4" />
                    <span class="text-xs font-bold text-foreground uppercase tracking-wide">Nodo Principal</span>
                </label>
            </div>

            <div class="pt-4 border-t border-border/50 flex items-center justify-end gap-3">
                <Link 
                    :href="route('admin.operations.branches.index')" 
                    class="px-4 py-2 rounded-xl border border-border/80 text-xs font-semibold text-muted-foreground hover:text-foreground hover:bg-foreground/[0.04] transition-all"
                    :class="{ 'pointer-events-none opacity-50': form.processing }"
                >
                    Cancelar
                </Link>
                <button 
                    type="submit" 
                    :disabled="form.processing" 
                    class="admin-btn-primary inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider py-2 px-4 shadow-sm"
                >
                    <span class="material-symbols-rounded text-[18px]">save</span>
                    <span>{{ form.processing ? 'Guardando...' : (isEdit ? 'Actualizar Parámetros' : 'Crear Sucursal') }}</span>
                </button>
            </div>
        </div>

        <!-- Columna Derecha: Delimitación Geofencing con Leaflet -->
        <div class="glass-card p-5 md:p-6 rounded-2xl border border-white/60 dark:border-white/10 shadow-sm space-y-4">
            <div class="border-b border-border/50 pb-2.5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <div>
                    <h3 class="text-xs font-bold text-foreground uppercase tracking-wider flex items-center gap-1.5">
                        <span class="material-symbols-rounded text-primary text-[18px]">share_location</span>
                        <span>Delimitación Geofencing de Cobertura</span>
                    </h3>
                    <p class="text-[11px] text-muted-foreground mt-0.5">
                        Arrastra el pin para ubicar el centro. Haz clics en el mapa para trazar el polígono.
                    </p>
                </div>
                <div class="flex items-center gap-1.5 shrink-0">
                    <button 
                        type="button" 
                        @click="removeLastVertex" 
                        class="px-2.5 py-1 text-[11px] font-semibold rounded-xl border border-border/80 hover:bg-foreground/[0.05] text-foreground transition-all disabled:opacity-40" 
                        :disabled="form.coverage_polygon.length === 0"
                    >
                        Deshacer Vértice
                    </button>
                    <button 
                        type="button" 
                        @click="clearPolygon" 
                        class="px-2.5 py-1 text-[11px] font-semibold rounded-xl border border-destructive/20 text-destructive hover:bg-destructive/10 transition-all disabled:opacity-40" 
                        :disabled="form.coverage_polygon.length === 0"
                    >
                        Limpiar Polígono
                    </button>
                </div>
            </div>

            <!-- Contenedor del Mapa Leaflet -->
            <div ref="mapContainer" class="h-[380px] w-full rounded-2xl border border-border/80 overflow-hidden shadow-inner z-10 relative"></div>

            <!-- Telemetría Geográfica -->
            <div class="grid grid-cols-2 gap-3 p-3.5 rounded-xl bg-card/60 backdrop-blur-md border border-border/60 text-xs font-mono">
                <div>
                    <span class="text-muted-foreground block text-[10px] uppercase font-sans font-bold">Latitud Centro</span>
                    <span class="text-foreground font-semibold">{{ form.latitude || 'Sin fijar' }}</span>
                </div>
                <div>
                    <span class="text-muted-foreground block text-[10px] uppercase font-sans font-bold">Longitud Centro</span>
                    <span class="text-foreground font-semibold">{{ form.longitude || 'Sin fijar' }}</span>
                </div>
                <div class="col-span-2 border-t border-border/40 pt-2 flex items-center justify-between font-sans">
                    <span class="text-muted-foreground text-[10px] uppercase font-bold">Vértices Geofence (Mín. 3):</span>
                    <span :class="form.coverage_polygon.length >= 3 ? 'badge-success text-[10px]' : 'badge-warning text-[10px]'">
                        {{ form.coverage_polygon.length }} vértices registrados
                    </span>
                </div>
                <p v-if="form.errors.coverage_polygon" class="col-span-2 text-error text-[11px] font-sans font-medium mt-1">
                    {{ form.errors.coverage_polygon }}
                </p>
            </div>
        </div>

    </form>
</template>
