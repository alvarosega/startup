<script setup>
import { ref, onMounted, onUnmounted, watch, nextTick } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import DriverLayout from '@/Layouts/DriverLayout.vue';

const props = defineProps({
    driver: Object,
    branch: Object,
    activeOrder: Object,
    pendingOrders: Array
});

const isTracking = ref(props.driver?.is_online || false);
const isLoading = ref(false);
const errorMsg = ref('');
const currentPendingOrders = ref(props.pendingOrders || []);
const otpCode = ref('');
const otpError = ref('');
const pickupCode = ref(''); // Nuevo ref para el PIN de la tienda
const pickupError = ref('');

let watchId = null;
let pollingInterval = null;
let map = null;
let driverMarker = null;
let destinationMarker = null;

const icons = {
    driver: L.icon({ iconUrl: 'https://cdn-icons-png.flaticon.com/512/7583/7583091.png', iconSize: [36, 36], iconAnchor: [18, 36] }),
    destination: L.icon({ iconUrl: 'https://cdn-icons-png.flaticon.com/512/10603/10603708.png', iconSize: [40, 40], iconAnchor: [20, 40] })
};

// Formateador de tiempo ("Hace 5 min")
const formatTimeAgo = (dateString) => {
    const diff = Math.floor((new Date() - new Date(dateString)) / 60000);
    if (diff < 1) return 'Justo ahora';
    if (diff < 60) return `Hace ${diff} min`;
    return `Hace ${Math.floor(diff / 60)} h`;
};

// Abrir URL de Google Maps
const openGoogleMaps = (lat, lng) => {
    if(lat && lng) {
        window.open(`https://www.google.com/maps/search/?api=1&query=${lat},${lng}`, '_blank');
    }
};
const submitVerifyPickup = (orderId) => {
    if (pickupCode.value.length !== 5) {
        pickupError.value = "Ingresa los 5 caracteres";
        return;
    }
    isLoading.value = true;
    pickupError.value = '';
    
    router.post(`/driver/orders/${orderId}/pickup`, { pickup_otp: pickupCode.value }, {
        onSuccess: () => { 
            isLoading.value = false; 
            pickupCode.value = '';
            // No hace falta reload si Inertia actualiza los props, 
            // pero si el mapa no reacciona, el reload asegura limpieza.
            window.location.reload(); 
        },
        onError: (errors) => { 
            isLoading.value = false;
            if (errors.pickup_otp) pickupError.value = errors.pickup_otp;
        }
    });
};
onMounted(() => {
    if (props.activeOrder) {
        initReferenceMap();
    }
    if (isTracking.value) {
        startTrackingLocal();
        if (!props.activeOrder) startPolling();
    }
});

onUnmounted(() => {
    stopTrackingLocal();
    stopPolling();
    if (map) map.remove();
});

// Inicializar mapa referencial (Solo visible si hay activeOrder)
const initReferenceMap = async () => {
    await nextTick(); // Asegurar que el div existe
    const mapElement = document.getElementById('driver-map');
    if (!mapElement) return;

    map = L.map('driver-map', { zoomControl: false, dragging: false, touchZoom: false, scrollWheelZoom: false }).setView([-16.4897, -68.1193], 15);
    L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', { maxZoom: 20 }).addTo(map);

    if (props.activeOrder?.delivery_data) {
        const lat = props.activeOrder.delivery_data.lat;
        const lng = props.activeOrder.delivery_data.lng;
        destinationMarker = L.marker([lat, lng], { icon: icons.destination }).addTo(map);
        map.setView([lat, lng], 15);
    }
};

const startPolling = () => {
    if (props.activeOrder) return; 
    pollingInterval = setInterval(async () => {
        if (!isTracking.value) return;
        try {
            const response = await axios.get('/driver/orders/poll');
            currentPendingOrders.value = response.data;
        } catch (error) {}
    }, 10000); 
};

const stopPolling = () => { if (pollingInterval) clearInterval(pollingInterval); };

const toggleTracking = () => {
    isLoading.value = true;
    errorMsg.value = '';
    const targetStatus = !isTracking.value;

    router.post('/driver/status/toggle', { is_online: targetStatus }, {
        onSuccess: () => {
            isTracking.value = targetStatus;
            if (targetStatus) {
                startTrackingLocal();
                if (!props.activeOrder) startPolling();
            } else {
                stopTrackingLocal();
                stopPolling();
                currentPendingOrders.value = [];
            }
            isLoading.value = false;
        },
        onError: () => { errorMsg.value = "Fallo de comunicación."; isLoading.value = false; }
    });
};

const startTrackingLocal = () => {
    if (!("geolocation" in navigator)) return;
    watchId = navigator.geolocation.watchPosition(
        (position) => {
            const { latitude, longitude } = position.coords;
            if (map && props.activeOrder) {
                if (driverMarker) {
                    driverMarker.setLatLng([latitude, longitude]);
                } else {
                    driverMarker = L.marker([latitude, longitude], { icon: icons.driver, zIndexOffset: 1000 }).addTo(map);
                }
                // Ajustar vista para incluir conductor y destino
                if(destinationMarker) {
                    const bounds = L.latLngBounds([driverMarker.getLatLng(), destinationMarker.getLatLng()]);
                    map.fitBounds(bounds, { padding: [30, 30] });
                }
            }
            axios.post('/driver/telemetry/update', { latitude, longitude }).catch(() => {});
        },
        (error) => {},
        { enableHighAccuracy: true, maximumAge: 0, timeout: 5000 }
    );
};

const stopTrackingLocal = () => {
    if (watchId) { navigator.geolocation.clearWatch(watchId); watchId = null; }
};

const takeOrder = (orderId) => {
    isLoading.value = true;
    router.post(`/driver/orders/${orderId}/take`, {}, {
        onSuccess: () => { window.location.reload(); }, // Recargar forzado para montar el mapa correctamente
        onError: (errors) => {
            isLoading.value = false;
            if (errors.order_conflict) errorMsg.value = errors.order_conflict;
        }
    });
};

const markAsArrived = (orderId) => {
    isLoading.value = true;
    router.post(`/driver/orders/${orderId}/arrived`, {}, {
        onSuccess: () => { isLoading.value = false; },
        onError: () => { isLoading.value = false; }
    });
};

const submitCompleteOrder = (orderId) => {
    if (otpCode.value.length !== 4) {
        otpError.value = "Ingresa los 4 dígitos";
        return;
    }
    isLoading.value = true;
    otpError.value = '';
    router.post(`/driver/orders/${orderId}/complete`, { otp: otpCode.value }, {
        onSuccess: () => { 
            isLoading.value = false; 
            otpCode.value = ''; 
            window.location.reload(); // Recargar al terminar para volver al radar
        },
        onError: (errors) => { 
            isLoading.value = false;
            if (errors.otp) otpError.value = errors.otp;
        }
    });
};
</script>
<template>
    <DriverLayout>
        <div class="w-full h-full bg-gray-50 flex flex-col overflow-hidden">
            
            <div class="bg-white shadow-sm border-b border-gray-200 p-4 z-20 shrink-0 flex justify-between items-center">
                <div>
                    <h1 class="text-sm font-black text-gray-900 uppercase tracking-widest">
                        {{ activeOrder ? 'Viaje en Curso' : 'Radar Operativo' }}
                    </h1>
                    <p class="text-[10px] font-bold mt-0.5" :class="isTracking ? 'text-green-600' : 'text-gray-400'">
                        <span class="inline-block w-2 h-2 rounded-full mr-1" :class="isTracking ? 'bg-green-500 animate-pulse' : 'bg-gray-400'"></span>
                        {{ isTracking ? 'Conectado' : 'Desconectado' }}
                    </p>
                </div>
                <button @click="toggleTracking" :disabled="isLoading"
                    class="py-2 px-5 rounded-lg font-bold text-white uppercase text-[10px] tracking-widest transition-all shadow-md disabled:opacity-50"
                    :class="isTracking ? 'bg-red-600 hover:bg-red-700' : 'bg-blue-600 hover:bg-blue-700'">
                    {{ isLoading ? '...' : (isTracking ? 'Desconectar' : 'Conectar') }}
                </button>
            </div>

            <div class="flex-1 overflow-y-auto p-4">
                
                <div v-if="errorMsg" class="mb-4 p-3 bg-red-50 border border-red-200 text-red-600 text-xs rounded-xl font-mono shadow-sm">
                    {{ errorMsg }}
                </div>

                <div v-if="activeOrder" class="space-y-4 pb-10">
                    
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="bg-gray-900 p-4 text-white flex justify-between items-center">
                            <div>
                                <span class="bg-blue-500/20 text-blue-300 text-[10px] font-bold px-2 py-0.5 rounded uppercase tracking-wider mb-1 inline-block">
                                    {{ activeOrder.status === 'preparing' ? 'En Base' : (activeOrder.status === 'arrived' ? 'En Destino' : 'En Camino') }}
                                </span>
                                <h2 class="text-xl font-black font-mono">#{{ activeOrder.code }}</h2>
                            </div>
                            <a :href="'tel:' + activeOrder.customer?.phone" class="flex flex-col items-center p-2 bg-white/10 rounded-lg hover:bg-white/20 transition-colors">
                                <svg class="w-5 h-5 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                <span class="text-[9px] font-bold uppercase tracking-widest">Llamar</span>
                            </a>
                        </div>

                        <div class="p-5">
                            <p class="text-[10px] text-gray-400 uppercase tracking-widest font-bold mb-1">Destino de Entrega</p>
                            <p class="text-sm font-bold text-gray-900 leading-relaxed">{{ activeOrder.delivery_data?.address }}</p>
                            <p class="text-xs text-gray-500 mt-1">Ref: {{ activeOrder.delivery_data?.reference || 'N/A' }}</p>
                            
                            <div class="h-px bg-gray-100 my-4"></div>

                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="text-[10px] text-gray-400 uppercase tracking-widest font-bold">Ganancia</p>
                                    <p class="text-xl font-black text-green-600">Bs. {{ activeOrder.delivery_fee }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-[10px] text-gray-400 uppercase tracking-widest font-bold">Cobro en Puerta</p>
                                    <p class="text-sm font-black text-red-600">Bs. {{ activeOrder.balance_amount || '0.00' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button @click="openGoogleMaps(activeOrder.delivery_data?.lat, activeOrder.delivery_data?.lng)" class="w-full bg-blue-50 text-blue-700 border border-blue-200 py-3 rounded-xl flex items-center justify-center gap-2 font-bold text-xs uppercase tracking-widest shadow-sm hover:bg-blue-100 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        Navegar con Google Maps
                    </button>

                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden relative">
                        <div class="absolute top-2 left-2 z-[400] bg-white/90 backdrop-blur px-2 py-1 rounded text-[9px] font-bold text-gray-600 uppercase">Mapa Referencial</div>
                        <div id="driver-map" class="w-full h-48 z-0"></div>
                    </div>

                    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200">
                        <div v-if="activeOrder.status === 'preparing'" class="space-y-4">
                            <div class="bg-primary/5 border border-primary/20 rounded-xl p-4 text-center">
                                <p class="text-xs font-black text-primary uppercase tracking-widest mb-3">Punto de Recogida</p>
                                <div class="space-y-3">
                                    <p class="text-[10px] text-gray-500 font-bold uppercase">Ingresa el PIN de la sucursal</p>
                                    <input v-model="pickupCode" type="text" maxlength="5" placeholder="ABC12" 
                                        class="w-full text-center text-3xl font-black font-mono tracking-[0.3em] bg-white border-2 border-primary/30 rounded-xl py-3 uppercase focus:border-primary focus:ring-0"
                                        @input="pickupError = ''">
                                    <p v-if="pickupError" class="text-[10px] font-bold text-red-500 mt-1">{{ pickupError }}</p>
                                </div>
                            </div>
                            <button @click="submitVerifyPickup(activeOrder.id)" 
                                    :disabled="isLoading || pickupCode.length !== 5" 
                                    class="w-full bg-primary text-primary-foreground text-sm font-black py-4 rounded-xl shadow-lg hover:bg-primary/90 uppercase tracking-widest disabled:opacity-50 transition-all">
                                {{ isLoading ? 'Verificando...' : 'Iniciar Viaje' }}
                            </button>
                        </div>

                        <div v-else-if="activeOrder.status === 'dispatched'" class="space-y-4">
                            <div class="p-4 bg-blue-50 border border-blue-100 rounded-xl text-center">
                                <p class="text-[10px] font-black text-blue-600 uppercase mb-1">Orden en Tránsito</p>
                                <p class="text-xs text-gray-600 font-bold">Navega hacia el punto de entrega y presiona el botón al llegar.</p>
                            </div>
                            <button @click="markAsArrived(activeOrder.id)" 
                                    :disabled="isLoading" 
                                    class="w-full bg-blue-600 text-white text-sm font-black py-5 rounded-xl shadow-lg hover:bg-blue-700 uppercase tracking-widest disabled:opacity-50 transition-all flex items-center justify-center gap-2">
                                <svg v-if="!isLoading" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path></svg>
                                {{ isLoading ? 'Notificando...' : 'Llegué al Destino' }}
                            </button>
                        </div>

                        <div v-else-if="activeOrder.status === 'arrived'" class="space-y-4">
                            <div class="text-center">
                                <div class="inline-block px-3 py-1 bg-green-100 text-green-700 text-[10px] font-black rounded-full mb-3 uppercase tracking-tighter">Destino Alcanzado</div>
                                <p class="text-xs text-gray-500 font-bold uppercase tracking-widest mb-2">Ingresa el PIN del cliente</p>
                                <input v-model="otpCode" type="text" maxlength="4" placeholder="0000" 
                                    class="w-full text-center text-4xl font-black font-mono tracking-[0.5em] bg-gray-50 border-2 border-gray-300 rounded-xl py-4 focus:border-black focus:ring-0 transition-colors"
                                    @input="otpError = ''">
                                <p v-if="otpError" class="text-xs font-bold text-red-500 mt-2">{{ otpError }}</p>
                            </div>

                            <button @click="submitCompleteOrder(activeOrder.id)" 
                                    :disabled="isLoading || otpCode.length !== 4" 
                                    class="w-full bg-black text-white text-sm font-black py-4 rounded-xl shadow-lg hover:bg-gray-800 uppercase tracking-widest disabled:opacity-50 transition-all flex justify-center items-center gap-2">
                                Confirmar Entrega
                            </button>
                        </div>
                    </div>

                </div>

                <div v-else class="space-y-4 pb-10">
                    <div v-if="!isTracking" class="text-center py-12">
                        <div class="w-16 h-16 bg-gray-200 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636a9 9 0 010 12.728m0 0l-2.829-2.829m2.829 2.829L21 21M15.536 8.464a5 5 0 010 7.072m0 0l-2.829-2.829m-4.243-2.829a4 4 0 115.656 5.656L6.343 21 3 21v-3.343l8.485-8.486z"></path></svg>
                        </div>
                        <h3 class="text-lg font-black text-gray-900">Modo Fuera de Línea</h3>
                        <p class="text-sm text-gray-500 mt-2 max-w-xs mx-auto">Conéctate para empezar a recibir notificaciones de pedidos listos para entrega.</p>
                    </div>

                    <div v-else-if="currentPendingOrders.length === 0" class="text-center py-12">
                        <div class="w-8 h-8 border-4 border-blue-500 border-t-transparent rounded-full animate-spin mx-auto mb-4"></div>
                        <h3 class="text-lg font-black text-gray-900">Buscando envíos...</h3>
                        <p class="text-sm text-gray-500 mt-2">No hay pedidos pendientes en tu sucursal en este momento.</p>
                    </div>

                    <div v-else class="space-y-3">
                        <div v-for="order in currentPendingOrders" :key="order.id" class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                            <div class="flex justify-between items-start mb-3">
                                <div>
                                    <p class="text-[10px] text-gray-400 uppercase font-bold tracking-widest">Orden Pendiente</p>
                                    <h3 class="text-sm font-black font-mono">#{{ order.code }}</h3>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs font-bold text-gray-500">{{ formatTimeAgo(order.created_at) }}</p>
                                </div>
                            </div>
                            
                            <div class="flex gap-2 items-start mb-4 bg-gray-50 p-2 rounded-lg">
                                <svg class="w-4 h-4 text-gray-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path></svg>
                                <p class="text-xs text-gray-700 leading-tight line-clamp-2">{{ order.delivery_data?.address }}</p>
                            </div>

                            <div class="flex items-center justify-between border-t border-gray-100 pt-3">
                                <div>
                                    <p class="text-[10px] text-gray-400 uppercase font-bold tracking-widest">Ganancia</p>
                                    <p class="text-lg font-black text-green-600">Bs {{ order.delivery_fee }}</p>
                                </div>
                                <div class="flex gap-2">
                                    <button @click="openGoogleMaps(order.delivery_data?.lat, order.delivery_data?.lng)" class="p-2.5 bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 transition-colors" title="Ver en Google Maps">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-5.447a8 8 0 0111.314 0L15 20"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                    </button>
                                    
                                    <button @click="takeOrder(order.id)" :disabled="isLoading" class="bg-gray-900 text-white text-xs font-bold px-5 py-2.5 rounded-lg uppercase tracking-wider hover:bg-gray-800 disabled:opacity-50 transition-colors">
                                        Aceptar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </DriverLayout>
</template>



<style>
.leaflet-control-attribution { display: none; }
</style>