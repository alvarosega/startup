<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import ShopLayout from '@/Layouts/ShopLayout.vue';
import { ArrowLeft, Truck, User, Phone, MapPin } from 'lucide-vue-next';
import axios from 'axios';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

const props = defineProps({
    order: Object,
    initialDriverLocation: Object
});

const map = ref(null);
const driverMarker = ref(null);
const customerMarker = ref(null);
const routeLine = ref(null);
const pollingInterval = ref(null);
const currentStatus = ref(props.order.status);
const deliveryOtp = ref(null);


const customerLat = props.order.delivery_data?.lat;
const customerLng = props.order.delivery_data?.lng;
const driverDetails = props.order.driver?.details;

onMounted(() => {
    initMap();
    startPolling();
});

onUnmounted(() => {
    if (pollingInterval.value) clearInterval(pollingInterval.value);
    if (map.value) map.value.remove();
});

const initMap = () => {
    // Inicializar mapa centrado en el cliente o coordenadas por defecto si falla
    const centerLat = customerLat || -16.5; 
    const centerLng = customerLng || -68.1;

    map.value = L.map('tracking-map', {
        zoomControl: false,
        attributionControl: false
    }).setView([centerLat, centerLng], 15);

    L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png').addTo(map.value);

    // Icono A: Cliente (Negro)
    const customerIcon = L.divIcon({
        className: 'custom-div-icon',
        html: `<div style="background-color: #000; width: 20px; height: 20px; border-radius: 50%; border: 3px solid white; box-shadow: 0 4px 6px rgba(0,0,0,0.3);"></div>`,
        iconSize: [20, 20],
        iconAnchor: [10, 10]
    });

    // Icono B: Driver (Primario/Azul)
    const driverIcon = L.divIcon({
        className: 'custom-div-icon',
        html: `<div style="background-color: #2563eb; width: 24px; height: 24px; border-radius: 50%; border: 3px solid white; box-shadow: 0 4px 6px rgba(0,0,0,0.3);"></div>`,
        iconSize: [24, 24],
        iconAnchor: [12, 12]
    });

    if (customerLat && customerLng) {
        customerMarker.value = L.marker([customerLat, customerLng], { icon: customerIcon }).addTo(map.value);
    }

    if (props.initialDriverLocation) {
        updateDriverPosition(props.initialDriverLocation.latitude, props.initialDriverLocation.longitude, driverIcon);
    }
};

const updateDriverPosition = (lat, lng, icon = null) => {
    // Actualizar Marcador
    if (!driverMarker.value) {
        driverMarker.value = L.marker([lat, lng], { icon: icon }).addTo(map.value);
    } else {
        driverMarker.value.setLatLng([lat, lng]);
    }

    // Trazar y actualizar polilínea recta
    if (customerLat && customerLng) {
        const latlngs = [
            [lat, lng],
            [customerLat, customerLng]
        ];
        
        if (!routeLine.value) {
            routeLine.value = L.polyline(latlngs, { 
                color: '#2563eb', 
                weight: 4, 
                dashArray: '10, 10', 
                opacity: 0.7 
            }).addTo(map.value);
        } else {
            routeLine.value.setLatLngs(latlngs);
        }

        // Ajustar el zoom matemáticamente para que ambos puntos sean visibles
        map.value.fitBounds(L.latLngBounds(latlngs), { padding: [50, 50] });
    }
};

const startPolling = () => {
    pollingInterval.value = setInterval(async () => {
        try {
            const response = await axios.get(route('customer.orders.telemetry', props.order.id));
            if (response.data.active) {
                currentStatus.value = response.data.status; // Actualizamos estado reactivo
                deliveryOtp.value = response.data.otp;       // Capturamos el PIN si existe

                if (response.data.coords) {
                    updateDriverPosition(response.data.coords.latitude, response.data.coords.longitude);
                }
            }
        } catch (error) {
            console.error("Fallo de telemetría:", error);
        }
    }, 10000); 
};
</script>

<template>
    <ShopLayout>
        <Head title="Rastreo en Tiempo Real" />

        <div class="h-[calc(100vh-80px)] w-full flex flex-col md:flex-row relative bg-card">
            
            <div class="w-full md:w-96 shrink-0 bg-background z-10 flex flex-col border-b md:border-b-0 md:border-r border-border shadow-xl">
                
                <div class="p-6 border-b border-border">
                    <Link :href="route('customer.orders.history')" class="inline-flex items-center text-xs font-bold uppercase tracking-wider text-muted-foreground hover:text-foreground mb-6 transition-colors">
                        <ArrowLeft :size="14" class="mr-2" /> Volver a Pedidos
                    </Link>

                    <div>
                        <span class="font-mono text-xs text-muted-foreground">ID PEDIDO</span>
                        <h1 class="font-black text-2xl text-foreground tracking-tight">#{{ order.code }}</h1>
                    </div>
                </div>

                <div v-if="driverDetails" class="p-6 flex-1 overflow-y-auto">
                    <h3 class="text-xs font-black uppercase tracking-widest text-muted-foreground mb-4">Información del Conductor</h3>
                    
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-14 h-14 rounded-full bg-primary/10 border border-primary/20 flex items-center justify-center text-primary text-xl font-black uppercase shadow-sm">
                            {{ driverDetails.first_name.charAt(0) }}
                        </div>
                        <div>
                            <p class="font-black text-foreground">{{ driverDetails.first_name }} {{ driverDetails.last_name }}</p>
                            <div class="flex items-center gap-1.5 mt-1 text-xs font-bold text-muted-foreground bg-muted/50 px-2 py-1 rounded-md inline-flex">
                                <Truck :size="12" /> {{ driverDetails.vehicle_type }} • {{ driverDetails.license_plate }}
                            </div>
                        </div>
                    </div>
                    <div v-if="currentStatus === 'arrived' && deliveryOtp" class="p-6 bg-primary/10 border-b border-primary/20 animate-in fade-in slide-in-from-top-4 duration-500">
                        <div class="bg-card rounded-2xl p-6 text-center border-2 border-primary border-dashed relative overflow-hidden">
                            <div class="absolute -left-3 top-1/2 -translate-y-1/2 w-6 h-6 bg-primary/10 rounded-full border border-primary/20"></div>
                            <div class="absolute -right-3 top-1/2 -translate-y-1/2 w-6 h-6 bg-primary/10 rounded-full border border-primary/20"></div>

                            <p class="text-[10px] font-black uppercase tracking-widest text-primary mb-2">Tu conductor ha llegado</p>
                            <p class="text-xs font-medium text-muted-foreground mb-4">Dicta este PIN de seguridad para recibir tu pedido</p>
                            <h2 class="text-5xl font-black font-mono tracking-widest text-foreground">{{ deliveryOtp }}</h2>
                        </div>
                    </div>

                    <a :href="'tel:' + order.driver.phone" class="w-full flex justify-center items-center gap-2 bg-foreground text-background py-3 rounded-xl font-black uppercase tracking-widest text-[10px] hover:bg-foreground/90 transition-all shadow-md">
                        <Phone :size="14" /> Contactar Conductor
                    </a>
                    
                    <div class="mt-8 p-4 bg-muted/30 border border-border rounded-xl">
                        <p class="text-xs text-muted-foreground leading-relaxed">
                            <strong class="text-foreground">Importante:</strong> El conductor se dirigirá al punto de entrega especificado. Por favor, mantente atento a tu teléfono móvil.
                        </p>
                    </div>
                </div>
            </div>

            <div class="flex-1 h-full relative z-0">
                <div id="tracking-map" class="w-full h-full"></div>
                
                <div class="absolute top-4 left-1/2 -translate-x-1/2 z-[400] bg-background/90 backdrop-blur-md px-6 py-3 rounded-full border border-border shadow-lg flex items-center gap-3">
                    <div class="w-2.5 h-2.5 rounded-full bg-primary animate-pulse"></div>
                    <span class="text-xs font-black uppercase tracking-widest text-foreground">
                        {{ order.status === 'dispatched' ? 'En Tránsito' : 'Preparando' }}
                    </span>
                </div>
            </div>

        </div>
    </ShopLayout>
</template>

<style scoped>
/* Asegurar que los divs personalizados de leaflet se vean sobre el tile layer */
:deep(.custom-div-icon) {
    background: transparent;
    border: none;
}
</style>