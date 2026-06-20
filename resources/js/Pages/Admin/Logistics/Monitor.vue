<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import AdminLayout from '@/Layouts/AdminLayout.vue'; 

const props = defineProps({
    branches: Array,
    orders: Array,
    initialDrivers: Object, // Trae lat/lng de Redis
    mapCenter: Object
});

let map = null;
const driverMarkers = {}; 
// Mantenemos el estado de telemetría reactivo
const activeTelemetry = ref(props.initialDrivers || {});

// Computada para mezclar los datos de MySQL (todos) con Redis (los que emiten)
// Asumiendo que recibes los drivers crudos como prop 'allDrivers'. Si no la envías, 
// puedes iterar sobre initialDrivers por ahora, pero lo ideal es pasar 'allDrivers' desde el Controller.
const icons = {
    branch: L.icon({ iconUrl: 'https://cdn-icons-png.flaticon.com/512/2555/2555572.png', iconSize: [40, 40], iconAnchor: [20, 40] }),
    driver: L.icon({ iconUrl: 'https://cdn-icons-png.flaticon.com/512/7583/7583091.png', iconSize: [35, 35], iconAnchor: [17, 35] })
};

onMounted(() => {
    initMap();
    plotBranches();
    plotInitialDrivers();
    listenToTelemetry();
});

onUnmounted(() => {
    if (map) map.remove();
    if (window.Echo) window.Echo.leave('admin.logistics');
});

const initMap = () => {
    map = L.map('logistics-map', { zoomControl: false }).setView([props.mapCenter.lat, props.mapCenter.lng], 13);
    L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
        maxZoom: 20
    }).addTo(map);
};

const plotBranches = () => {
    if (!props.branches) return;
    props.branches.forEach(branch => {
        if(branch.latitude && branch.longitude) {
            L.marker([branch.latitude, branch.longitude], { icon: icons.branch })
             .addTo(map)
             .bindPopup(`<b>${branch.name}</b><br>Centro de Distribución`);
        }
    });
};

const plotInitialDrivers = () => {
    if (!props.initialDrivers) return;
    for (const [id, driver] of Object.entries(props.initialDrivers)) {
        createOrUpdateDriverMarker(id, driver.lat, driver.lng, driver.details?.license_plate || 'Sin Placa');
    }
};

const createOrUpdateDriverMarker = (id, lat, lng, plate = 'Desconocido') => {
    if (driverMarkers[id]) {
        driverMarkers[id].setLatLng([lat, lng]);
    } else {
        const marker = L.marker([lat, lng], { icon: icons.driver }).addTo(map);
        marker.bindTooltip(`Placa: ${plate}`, { permanent: false, direction: 'top' });
        driverMarkers[id] = marker;
    }
};

const listenToTelemetry = () => {
    if (!window.Echo) return;
    window.Echo.private('admin.logistics')
        .listen('.driver.moved', (e) => {
            createOrUpdateDriverMarker(e.driverId, e.lat, e.lng);
            if (!activeTelemetry.value[e.driverId]) {
                activeTelemetry.value[e.driverId] = { lat: e.lat, lng: e.lng };
            }
        });
};
</script>

<template>
    <AdminLayout>
        <div class="h-[calc(100vh-64px)] w-full flex bg-black">
            
            <div class="w-80 flex-shrink-0 bg-gray-900 border-r border-gray-800 flex flex-col z-[1001] shadow-2xl">
                <div class="p-4 border-b border-gray-800">
                    <h1 class="text-lg font-bold text-white uppercase tracking-wider">Centro de Mando</h1>
                    <p class="text-[10px] text-green-400 flex items-center gap-1 mt-1">
                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></span> Sistema en línea
                    </p>
                </div>

                <div class="flex-1 overflow-y-auto p-4 space-y-6 custom-scrollbar">
                    
                    <section>
                        <div class="flex justify-between items-center mb-3">
                            <h2 class="text-[11px] font-black text-gray-500 uppercase tracking-widest">Flota Activa</h2>
                            <span class="bg-gray-800 text-gray-300 text-[10px] px-2 py-0.5 rounded font-mono">{{ Object.keys(activeTelemetry).length }}</span>
                        </div>
                        
                        <div class="space-y-2">
                            <div v-for="(drv, id) in activeTelemetry" :key="id" class="bg-gray-800/50 border border-gray-700 p-3 rounded-lg flex justify-between items-center">
                                <div>
                                    <p class="font-bold text-xs text-white">{{ drv.details?.license_plate || 'Sin Placa' }}</p>
                                    <p class="text-[10px] text-gray-400 font-mono mt-0.5">{{ drv.phone }}</p>
                                </div>
                                <div class="px-2 py-1 bg-green-900/30 border border-green-800 rounded text-[9px] font-bold text-green-400 uppercase">
                                    En Ruta
                                </div>
                            </div>
                            <div v-if="Object.keys(activeTelemetry).length === 0" class="text-center p-4 border border-dashed border-gray-700 rounded-lg">
                                <p class="text-xs text-gray-500">Ningún vehículo emitiendo señal.</p>
                            </div>
                        </div>
                    </section>

                    <section>
                        <div class="flex justify-between items-center mb-3">
                            <h2 class="text-[11px] font-black text-gray-500 uppercase tracking-widest">Órdenes Abiertas</h2>
                            <span class="bg-gray-800 text-gray-300 text-[10px] px-2 py-0.5 rounded font-mono">{{ orders?.length || 0 }}</span>
                        </div>

                        <div class="space-y-2">
                            <div v-for="order in orders" :key="order.id" class="bg-gray-800/50 border border-gray-700 p-3 rounded-lg">
                                <div class="flex justify-between items-start mb-2">
                                    <p class="font-bold text-xs text-white font-mono">#{{ order.code }}</p>
                                    <span class="text-[9px] font-bold uppercase px-1.5 py-0.5 rounded" 
                                        :class="order.status === 'preparing' ? 'bg-amber-900/50 text-amber-400 border border-amber-800' : 'bg-blue-900/50 text-blue-400 border border-blue-800'">
                                        {{ order.status }}
                                    </span>
                                </div>
                            </div>
                            <div v-if="orders.length === 0" class="text-center p-4 border border-dashed border-gray-700 rounded-lg">
                                <p class="text-xs text-gray-500">Sin operaciones en curso.</p>
                            </div>
                        </div>
                    </section>

                </div>
            </div>

            <div id="logistics-map" class="flex-grow z-0"></div>

        </div>
    </AdminLayout>
</template>

<style>
#logistics-map { width: 100%; height: 100%; z-index: 10; background: #0f172a; }
.leaflet-popup-content-wrapper { background: #1f2937; color: white; border: 1px solid #374151; }
.leaflet-popup-tip { background: #1f2937; }
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #374151; border-radius: 4px; }
</style>