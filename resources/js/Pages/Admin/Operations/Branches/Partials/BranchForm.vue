<script setup>
import { onMounted, onUnmounted, ref, watch } from 'vue';
import { Link } from '@inertiajs/vue3';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

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

// Coordenadas iniciales por defecto para el encuadre del mapa en altas limpias
const DEFAULT_LAT = -16.5000;
const DEFAULT_LNG = -68.1500;

/**
 * Sincroniza y redibuja la capa geométrica del polígono de cobertura en el canvas de Leaflet.
 */
const drawPolygonLayer = () => {
    if (polygonInstance) {
        mapInstance.removeLayer(polygonInstance);
    }

    if (props.form.coverage_polygon && props.form.coverage_polygon.length > 0) {
        // Mapeo inverso: Leaflet exige [latitud, longitud] para renderizar, el DTO usa [longitud, latitud]
        const leafletCoords = props.form.coverage_polygon.map(coord => [coord[1], coord[0]]);
        polygonInstance = L.polygon(leafletCoords, {
            color: 'hsl(var(--primary))',
            fillColor: 'hsl(var(--primary))',
            fillOpacity: 0.15,
            weight: 2
        }).addTo(mapInstance);
    }
};

/**
 * Remueve el último vértice añadido al polígono.
 */
const removeLastVertex = () => {
    if (props.form.coverage_polygon.length > 0) {
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

onMounted(() => {
    // Configuración del punto de vista inicial basado en la existencia de coordenadas
    const initLat = props.form.latitude ? props.form.latitude : DEFAULT_LAT;
    const initLng = props.form.longitude ? props.form.longitude : DEFAULT_LNG;

    // Inicialización del contenedor Leaflet suprimiendo controles innecesarios
    mapInstance = L.map(mapContainer.value, {
        zoomControl: true,
        attributionControl: false
    }).setView([initLat, initLng], 13);

    // Inyección de capa de mosaicos OpenStreetMap estándar
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19
    }).addTo(mapInstance);

    // Inicialización o renderizado condicional del marcador operacional de la sucursal
    markerInstance = L.marker([initLat, initLng], {
        draggable: true
    }).addTo(mapInstance);

    // Si es una creación limpia, forzamos las coordenadas iniciales del marcador al form
    if (!props.form.latitude || !props.form.longitude) {
        props.form.latitude = initLat;
        props.form.longitude = initLng;
    }

    // Escucha de eventos de arrastre del marcador (Dragend) para actualizar la ubicación base
    markerInstance.on('dragend', (e) => {
        const position = e.target.getLatLng();
        props.form.latitude = Number(position.lat.toFixed(6));
        props.form.longitude = Number(position.lng.toFixed(6));
    });

    // Escucha de eventos de clic en el canvas del mapa para capturar vértices del polígono de geofencing
    mapInstance.on('click', (e) => {
        // Inyección simétrica en el arreglo: [longitud, latitud] conforme al DTO de MySQL
        props.form.coverage_polygon.push([Number(e.latlng.lng.toFixed(6)), Number(e.latlng.lat.toFixed(6))]);
        drawPolygonLayer();
    });

    // Renderizado inicial del polígono en caso de edición
    if (props.isEdit) {
        drawPolygonLayer();
        // Ajuste de encuadre automático si existen vértices guardados
        if (props.form.coverage_polygon.length > 0) {
            const leafletCoords = props.form.coverage_polygon.map(coord => [coord[1], coord[0]]);
            mapInstance.fitBounds(L.polygon(leafletCoords).getBounds());
        }
    }
});

onUnmounted(() => {
    // Purga total de instancias y listeners del motor GIS para mitigar fugas de memoria
    if (mapInstance) {
        mapInstance.off();
        mapInstance.remove();
    }
});
</script>

<template>
    <form @submit.prevent="$emit('submit')" class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
        
        <div class="space-y-5 bg-card border border-border p-5 rounded-md shadow-flat">
            
            <div class="border-b border-border pb-2">
                <h3 class="text-xs font-bold text-foreground uppercase tracking-wide">Identificación y Ubicación</h3>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Nombre de Sucursal *</label>
                    <input v-model="form.name" type="text" required class="admin-input" :class="{ 'border-error': form.errors.name }" placeholder="Centro de Distribución Norte" />
                    <p v-if="form.errors.name" class="text-error text-xs font-medium mt-1">{{ form.errors.name }}</p>
                </div>
                <div class="space-y-1.5">
                    <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Ciudad / Jurisdicción *</label>
                    <input v-model="form.city" type="text" required class="admin-input" :class="{ 'border-error': form.errors.city }" placeholder="La Paz" />
                    <p v-if="form.errors.city" class="text-error text-xs font-medium mt-1">{{ form.errors.city }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Teléfono Operativo</label>
                    <input v-model="form.phone" type="text" class="admin-input font-mono" :class="{ 'border-error': form.errors.phone }" placeholder="+51999888777" />
                    <p v-if="form.errors.phone" class="text-error text-xs font-medium mt-1">{{ form.errors.phone }}</p>
                </div>
                <div class="space-y-1.5">
                    <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Dirección Física</label>
                    <input v-model="form.address" type="text" class="admin-input" :class="{ 'border-error': form.errors.address }" placeholder="Av. Troncal Nro 450" />
                    <p v-if="form.errors.address" class="text-error text-xs font-medium mt-1">{{ form.errors.address }}</p>
                </div>
            </div>

            <div class="border-b border-border pb-2 pt-2">
                <h3 class="text-xs font-bold text-foreground uppercase tracking-wide">Configuración de Tarifas Logísticas</h3>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="space-y-1.5">
                    <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Tarifa Base *</label>
                    <input v-model.number="form.delivery_base_fee" type="number" step="0.01" min="0" required class="admin-input font-mono" :class="{ 'border-error': form.errors.delivery_base_fee }" />
                    <p v-if="form.errors.delivery_base_fee" class="text-error text-xs font-medium mt-1">{{ form.errors.delivery_base_fee }}</p>
                </div>
                <div class="space-y-1.5">
                    <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Precio por KM *</label>
                    <input v-model.number="form.delivery_price_per_km" type="number" step="0.01" min="0" required class="admin-input font-mono" :class="{ 'border-error': form.errors.delivery_price_per_km }" />
                    <p v-if="form.errors.delivery_price_per_km" class="text-error text-xs font-medium mt-1">{{ form.errors.delivery_price_per_km }}</p>
                </div>
                <div class="space-y-1.5">
                    <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Multiplicador Alta Demanda *</label>
                    <input v-model.number="form.surge_multiplier" type="number" step="0.01" min="1" required class="admin-input font-mono" :class="{ 'border-error': form.errors.surge_multiplier }" />
                    <p v-if="form.errors.surge_multiplier" class="text-error text-xs font-medium mt-1">{{ form.errors.surge_multiplier }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="space-y-1.5">
                    <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Monto Mínimo Orden *</label>
                    <input v-model.number="form.min_order_amount" type="number" step="0.01" min="0" required class="admin-input font-mono" :class="{ 'border-error': form.errors.min_order_amount }" />
                    <p v-if="form.errors.min_order_amount" class="text-error text-xs font-medium mt-1">{{ form.errors.min_order_amount }}</p>
                </div>
                <div class="space-y-1.5">
                    <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Cargo Orden Pequeña *</label>
                    <input v-model.number="form.small_order_fee" type="number" step="0.01" min="0" required class="admin-input font-mono" :class="{ 'border-error': form.errors.small_order_fee }" />
                    <p v-if="form.errors.small_order_fee" class="text-error text-xs font-medium mt-1">{{ form.errors.small_order_fee }}</p>
                </div>
                <div class="space-y-1.5">
                    <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">% Comisión de Servicio *</label>
                    <input v-model.number="form.base_service_fee_percentage" type="number" step="0.01" min="0" max="100" required class="admin-input font-mono" :class="{ 'border-error': form.errors.base_service_fee_percentage }" />
                    <p v-if="form.errors.base_service_fee_percentage" class="text-error text-xs font-medium mt-1">{{ form.errors.base_service_fee_percentage }}</p>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row sm:items-center gap-4 pt-2">
                <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                    <input v-model="form.is_active" type="checkbox" class="rounded border-input text-primary focus:ring-ring bg-card w-4 h-4" />
                    <span class="text-xs font-bold text-foreground uppercase tracking-wide">Nodo Activo / Operativo</span>
                </label>
                <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                    <input v-model="form.is_default" type="checkbox" class="rounded border-input text-primary focus:ring-ring bg-card w-4 h-4" />
                    <span class="text-xs font-bold text-foreground uppercase tracking-wide">Establecer como Nodo Principal</span>
                </label>
            </div>

            <div class="pt-4 border-t border-border flex items-center justify-end gap-3">
                <Link :href="route('admin.operations.branches.index')" class="px-3 py-2 bg-secondary text-secondary-foreground font-semibold rounded-md text-sm hover:bg-neutral-200 dark:hover:bg-neutral-800" :class="{ 'pointer-events-none opacity-50': form.processing }">
                    Cancelar
                </Link>
                <button type="submit" :disabled="form.processing" class="admin-btn-primary inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider">
                    <span class="material-symbols-rounded text-base">save</span>
                    <span>{{ form.processing ? 'Sincronizando Nodo...' : 'Guardar Parámetros' }}</span>
                </button>
            </div>
        </div>

        <div class="space-y-4 bg-card border border-border p-5 rounded-md shadow-flat">
            <div class="border-b border-border pb-2 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <div>
                    <h3 class="text-xs font-bold text-foreground uppercase tracking-wide">Límites Geofencing de Cobertura</h3>
                    <p class="text-[11px] text-muted-foreground mt-0.5">Arrastre el marcador para fijar la sucursal. Haga clics sobre el mapa para trazar el polígono.</p>
                </div>
                <div class="flex items-center gap-1.5 shrink-0">
                    <button type="button" @click="removeLastVertex" class="px-2 py-1 bg-secondary text-secondary-foreground text-xs font-medium rounded border border-border hover:bg-neutral-200" :disabled="form.coverage_polygon.length === 0">
                        Deshacer
                    </button>
                    <button type="button" @click="clearPolygon" class="px-2 py-1 bg-destructive/5 text-destructive text-xs font-medium rounded border border-destructive/10 hover:bg-destructive/10" :disabled="form.coverage_polygon.length === 0">
                        Limpiar Polígono
                    </button>
                </div>
            </div>

            <div ref="mapContainer" class="h-[360px] w-full rounded-md border border-input bg-muted z-10 relative"></div>

            <div class="grid grid-cols-2 gap-4 bg-neutral-100 dark:bg-neutral-800/40 p-3 rounded-md text-xs font-mono">
                <div>
                    <span class="text-muted-foreground block text-[10px] uppercase font-sans font-bold">Latitud Sucursal</span>
                    <span class="text-foreground font-semibold">{{ form.latitude || 'Sin fijar' }}</span>
                </div>
                <div>
                    <span class="text-muted-foreground block text-[10px] uppercase font-sans font-bold">Longitud Sucursal</span>
                    <span class="text-foreground font-semibold">{{ form.longitude || 'Sin fijar' }}</span>
                </div>
                <div class="col-span-2 border-t border-border/60 pt-2">
                    <span class="text-muted-foreground block text-[10px] uppercase font-sans font-bold mb-1">Vértices Capturados en Polígono (Mínimo 3 requerido)</span>
                    <span :class="form.coverage_polygon.length >= 3 ? 'text-success font-bold' : 'text-warning font-bold'">
                        {{ form.coverage_polygon.length }} puntos registrados.
                    </span>
                    <p v-if="form.errors.coverage_polygon" class="text-error text-xs font-medium mt-1 font-sans">{{ form.errors.coverage_polygon }}</p>
                </div>
            </div>
        </div>

    </form>
</template>