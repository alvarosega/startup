<template>
    <DriverLayout>
    <div class="h-screen w-full relative bg-gray-900 overflow-hidden flex flex-col">
        
        <div class="absolute top-0 left-0 w-full z-[1000] p-4 pointer-events-none">
            <div class="pointer-events-auto bg-white/95 backdrop-blur rounded-xl shadow-lg p-4 flex justify-between items-center border border-gray-200">
                <div>
                    <h1 class="text-sm font-black text-gray-800 uppercase tracking-widest">Unidad Operativa</h1>
                    <p class="text-[10px] font-bold mt-1" :class="isTracking ? 'text-green-600' : 'text-gray-400'">
                        <span class="inline-block w-2 h-2 rounded-full mr-1" :class="isTracking ? 'bg-green-500 animate-pulse' : 'bg-gray-400'"></span>
                        {{ isTracking ? 'En Servicio' : 'Desconectado' }}
                    </p>
                </div>
                <button
                    @click="toggleTracking"
                    :disabled="isLoading"
                    class="py-2 px-4 rounded-lg font-bold text-white uppercase text-[10px] tracking-widest transition-all shadow-md disabled:opacity-50"
                    :class="isTracking ? 'bg-red-600 hover:bg-red-700' : 'bg-blue-600 hover:bg-blue-700'"
                >
                    {{ isLoading ? '...' : (isTracking ? 'Finalizar' : 'Iniciar') }}
                </button>
            </div>
            
            <div v-if="errorMsg" class="mt-2 p-2 bg-red-50 border border-red-200 text-red-600 text-xs rounded font-mono pointer-events-auto shadow-md">
                {{ errorMsg }}
            </div>
        </div>

        <div id="driver-map" class="w-full h-full z-0"></div>

        <div class="absolute bottom-0 left-0 w-full z-[1000] pb-6 px-4 pointer-events-none">
            <div class="pointer-events-auto">
                
                <transition name="slide-up">
                    <div v-if="selectedOrder" class="bg-white rounded-xl shadow-2xl border-t-4 border-blue-600 p-5 relative overflow-hidden">
                        
                        <button @click="clearSelection" class="absolute top-3 right-3 text-gray-400 hover:text-gray-700 bg-gray-100 rounded-full p-1">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>

                        <div class="mb-3">
                            <span class="bg-blue-100 text-blue-800 text-[10px] font-bold px-2 py-0.5 rounded uppercase tracking-wider mb-2 inline-block">Objetivo Seleccionado</span>
                            <h3 class="text-sm font-black text-gray-900 font-mono">#{{ selectedOrder.code }}</h3>
                            <p class="text-xs text-gray-600 mt-1 leading-relaxed pr-6">
                                {{ selectedOrder.delivery_data?.address || 'Coordenadas GPS exactas (Sin descripción)' }}
                            </p>
                            <p class="text-[10px] text-gray-400 mt-1">Indicaciones: {{ selectedOrder.delivery_data?.reference || 'Ninguna' }}</p>
                        </div>
                        
                        <div class="flex justify-between items-end border-t border-gray-100 pt-3">
                            <div>
                                <p class="text-[10px] text-gray-400 uppercase tracking-widest font-bold">Liquidación</p>
                                <p class="text-xl font-black text-green-600">Bs. {{ selectedOrder.delivery_fee }}</p>
                            </div>
                            <button @click="takeOrder(selectedOrder.id)" class="bg-gray-900 text-white text-xs font-bold px-6 py-3 rounded-lg hover:bg-gray-800 transition-colors shadow-lg uppercase tracking-wider">
                                Aceptar Pedido
                            </button>
                        </div>
                    </div>
                </transition>

                <div v-if="!selectedOrder && isTracking" class="flex justify-center mt-4">
                    <span class="bg-black/80 backdrop-blur text-white text-[10px] font-bold px-4 py-2 rounded-full uppercase tracking-widest shadow-lg">
                        Toca un pin azul para ver detalles
                    </span>
                </div>

            </div>
        </div>

    </div>
</DriverLayout>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import DriverLayout from '@/Layouts/DriverLayout.vue';

const props = defineProps({
    driver: Object,
    branch: Object,
    pendingOrders: Array
});

const isTracking = ref(props.driver?.is_online || false);
const isLoading = ref(false);
const errorMsg = ref('');
const selectedOrder = ref(null);

let watchId = null;
let map = null;
let driverMarker = null;
let branchMarker = null;
let orderMarkers = [];

const icons = {
    driver: L.icon({ iconUrl: 'https://cdn-icons-png.flaticon.com/512/7583/7583091.png', iconSize: [36, 36], iconAnchor: [18, 36] }),
    branch: L.icon({ iconUrl: 'https://cdn-icons-png.flaticon.com/512/2555/2555572.png', iconSize: [44, 44], iconAnchor: [22, 44] }),
    order: L.icon({ iconUrl: 'https://cdn-icons-png.flaticon.com/512/10603/10603708.png', iconSize: [32, 32], iconAnchor: [16, 32] })
};

onMounted(() => {
    initMap();
    if (isTracking.value) startTrackingLocal();
});

onUnmounted(() => {
    stopTrackingLocal();
    if (map) map.remove();
});

const initMap = () => {
    // Si tenemos la sucursal, centramos el mapa ahí inicialmente. Si no, al centro de la ciudad.
    const initialLat = props.branch?.lat || -16.4897;
    const initialLng = props.branch?.lng || -68.1193;

    map = L.map('driver-map', { zoomControl: false }).setView([initialLat, initialLng], 14);
    
    L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', { maxZoom: 20 }).addTo(map);

    // 1. Graficar Sucursal
    if (props.branch?.lat && props.branch?.lng) {
        branchMarker = L.marker([props.branch.lat, props.branch.lng], { icon: icons.branch, zIndexOffset: 100 })
            .addTo(map)
            .bindTooltip('<b>Tu Sucursal Base</b>', { direction: 'top', permanent: false });
    }

    // 2. Graficar Pedidos
    plotPendingOrders();
};

const plotPendingOrders = () => {
    if (!props.pendingOrders) return;
    
    props.pendingOrders.forEach(order => {
        if (order.delivery_data?.lat && order.delivery_data?.lng) {
            const marker = L.marker([order.delivery_data.lat, order.delivery_data.lng], { icon: icons.order })
                .addTo(map);
            
            // Evento: Al hacer clic en el pin, centrar mapa y mostrar la tarjeta inferior
            marker.on('click', () => {
                selectedOrder.value = order;
                map.setView([order.delivery_data.lat, order.delivery_data.lng], 16, { animate: true });
            });

            orderMarkers.push(marker);
        }
    });
};

const clearSelection = () => {
    selectedOrder.value = null;
};

const toggleTracking = () => {
    isLoading.value = true;
    errorMsg.value = '';
    const targetStatus = !isTracking.value;

    router.post('/driver/status/toggle', { is_online: targetStatus }, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            isTracking.value = targetStatus;
            targetStatus ? startTrackingLocal() : stopTrackingLocal();
            isLoading.value = false;
        },
        onError: () => {
            errorMsg.value = "Fallo de comunicación.";
            isLoading.value = false;
        }
    });
};

const startTrackingLocal = () => {
    if (!("geolocation" in navigator)) {
        errorMsg.value = "Hardware GPS no detectado.";
        return;
    }

    watchId = navigator.geolocation.watchPosition(
        (position) => {
            const { latitude, longitude } = position.coords;

            if (driverMarker) {
                driverMarker.setLatLng([latitude, longitude]);
            } else {
                driverMarker = L.marker([latitude, longitude], { icon: icons.driver, zIndexOffset: 1000 }).addTo(map);
                map.setView([latitude, longitude], 15);
            }

            axios.post('/driver/telemetry/update', { latitude, longitude }).catch(() => {});
        },
        (error) => { errorMsg.value = "GPS sin señal o permiso denegado."; },
        { enableHighAccuracy: true, maximumAge: 0, timeout: 5000 }
    );
};

const stopTrackingLocal = () => {
    if (watchId) {
        navigator.geolocation.clearWatch(watchId);
        watchId = null;
    }
    if (driverMarker) {
        map.removeLayer(driverMarker);
        driverMarker = null;
    }
    clearSelection();
};

const takeOrder = (orderId) => {
    // Aquí implementaremos la lógica de toma de pedido
    console.log("Asignando pedido:", orderId);
};
</script>

<style>
.leaflet-control-attribution { display: none; }
.slide-up-enter-active, .slide-up-leave-active { transition: all 0.3s ease-out; }
.slide-up-enter-from, .slide-up-leave-to { transform: translateY(100%); opacity: 0; }
</style>