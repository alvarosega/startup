<script setup>
import { ref, onMounted, nextTick } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import DriverLayout from '@/Layouts/DriverLayout.vue';

const props = defineProps({
    activeOrder: Object,
});

const isLoading = ref(false);
const showDeliveryModal = ref(false);

const form = useForm({
    otp: '',
});

const icons = {
    destination: L.icon({ iconUrl: 'https://cdn-icons-png.flaticon.com/512/10603/10603708.png', iconSize: [40, 40], iconAnchor: [20, 40] })
};

const openGoogleMaps = (lat, lng) => {
    if(lat && lng) window.open(`http://maps.google.com/maps?q=${lat},${lng}`, '_blank');
};

onMounted(() => {
    if (props.activeOrder) initReferenceMap();
});

const initReferenceMap = async () => {
    await nextTick();
    const mapElement = document.getElementById('route-map');
    if (!mapElement || !props.activeOrder?.delivery_data) return;

    const lat = props.activeOrder.delivery_data.lat;
    const lng = props.activeOrder.delivery_data.lng;

    const map = L.map('route-map', { zoomControl: false, dragging: true, touchZoom: true, scrollWheelZoom: false }).setView([lat, lng], 16);
    L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', { maxZoom: 20 }).addTo(map);
    L.marker([lat, lng], { icon: icons.destination }).addTo(map);
};

const markAsArrived = () => {
    isLoading.value = true;
    router.post(route('driver.orders.arrived', props.activeOrder.id), {}, {
        onFinish: () => isLoading.value = false
    });
};

const submitCompleteOrder = () => {
    form.post(route('driver.orders.complete', props.activeOrder.id), {
        onSuccess: () => { showDeliveryModal.value = false; }
    });
};
</script>

<template>
    <DriverLayout>
        <div class="w-full h-full bg-gray-50 flex flex-col overflow-hidden pb-24">
            
            <div class="bg-white shadow-sm border-b border-gray-200 p-4 z-20 shrink-0 flex justify-between items-center">
                <div>
                    <h1 class="text-sm font-black text-gray-900 uppercase tracking-widest">Hoja de Ruta</h1>
                    <p class="text-[10px] font-bold mt-0.5 text-blue-600 uppercase tracking-tighter">
                        Operación en Curso
                    </p>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto p-4 space-y-4">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="bg-gray-900 p-4 text-white flex justify-between items-center">
                        <div>
                            <span class="bg-blue-500/20 text-blue-300 text-[10px] font-bold px-2 py-0.5 rounded uppercase tracking-wider mb-1 inline-block">
                                {{ activeOrder.status === 'arrived' ? 'En Puerta' : 'En Tránsito' }}
                            </span>
                            <h2 class="text-xl font-black font-mono">#{{ activeOrder.code }}</h2>
                        </div>
                        <a :href="'tel:' + activeOrder.customer?.phone" class="flex flex-col items-center p-2 bg-white/10 rounded-lg hover:bg-white/20 transition-colors">
                            <svg class="w-5 h-5 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                            <span class="text-[9px] font-bold uppercase tracking-widest">Llamar</span>
                        </a>
                    </div>

                    <div class="p-5">
                        <p class="text-[10px] text-gray-400 uppercase tracking-widest font-bold mb-1">Coordenada de Entrega</p>
                        <p class="text-sm font-bold text-gray-900 leading-relaxed">{{ activeOrder.delivery_data?.address }}</p>
                        <p class="text-xs text-gray-500 mt-1">Ref: {{ activeOrder.delivery_data?.reference || 'Sin referencias' }}</p>
                        
                        <div class="h-px bg-gray-100 my-4"></div>

                        <div class="flex justify-between items-center">
                            <div>
                                <p class="text-[10px] text-gray-400 uppercase tracking-widest font-bold">Liquidación</p>
                                <p class="text-xl font-black text-green-600">Bs. {{ activeOrder.delivery_fee }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-[10px] text-gray-400 uppercase tracking-widest font-bold">Cobro Requerido</p>
                                <p class="text-sm font-black text-red-600">Bs. {{ activeOrder.payment_method === 'cash' ? activeOrder.total_amount : '0.00' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <button @click="openGoogleMaps(activeOrder.delivery_data?.lat, activeOrder.delivery_data?.lng)" class="w-full bg-blue-50 text-blue-700 border border-blue-200 py-4 rounded-xl flex items-center justify-center gap-2 font-black text-xs uppercase tracking-widest shadow-sm hover:bg-blue-100 transition-colors active:scale-95">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    Navegación Externa
                </button>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden relative">
                    <div class="absolute top-2 left-2 z-[400] bg-white/90 backdrop-blur px-2 py-1 rounded text-[9px] font-black text-gray-900 uppercase tracking-widest border border-gray-200">Destino Estático</div>
                    <div id="route-map" class="w-full h-48 z-0 bg-gray-100"></div>
                </div>

                <div class="pt-4">
                    <button v-if="activeOrder.status === 'dispatched'" @click="markAsArrived" :disabled="isLoading" 
                        class="w-full bg-gray-900 text-white text-sm font-black py-5 rounded-2xl shadow-lg hover:bg-gray-800 uppercase tracking-widest disabled:opacity-50 transition-all active:scale-95">
                        {{ isLoading ? 'Procesando...' : 'Notificar Llegada' }}
                    </button>
                    
                    <button v-else-if="activeOrder.status === 'arrived'" @click="showDeliveryModal = true" 
                        class="w-full bg-green-600 text-white text-sm font-black py-5 rounded-2xl shadow-lg hover:bg-green-700 uppercase tracking-widest transition-all active:scale-95">
                        Finalizar Entrega
                    </button>
                </div>
            </div>
        </div>

        <div v-if="showDeliveryModal" class="fixed inset-0 z-[100] flex items-end md:items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
            <div class="bg-white w-full max-w-sm rounded-[2.5rem] p-8 shadow-2xl">
                <div class="flex justify-between items-center mb-6">
                    <h4 class="font-black uppercase italic text-gray-900">Validar Entrega</h4>
                    <button @click="showDeliveryModal = false" class="text-gray-400"><X :size="24" /></button>
                </div>
                
                <p class="text-[10px] text-gray-500 font-bold mb-6 uppercase text-center">
                    Solicita al cliente su PIN de 4 dígitos para cerrar la operación comercial.
                </p>

                <input v-model="form.otp" type="text" maxlength="4" placeholder="____" 
                       class="w-full text-center text-4xl font-mono font-black tracking-[0.5em] border-2 border-gray-100 bg-gray-50 rounded-2xl py-5 focus:border-green-500 focus:ring-0 uppercase mb-4"
                       @input="form.clearErrors()">

                <p v-if="form.errors.otp" class="text-[10px] text-red-500 font-black text-center mb-4 uppercase">{{ form.errors.otp }}</p>

                <button @click="submitCompleteOrder" :disabled="form.processing || form.otp.length < 4"
                        class="w-full py-4 bg-green-600 text-white rounded-2xl font-black uppercase tracking-widest text-xs shadow-lg disabled:opacity-30 active:scale-95">
                    {{ form.processing ? 'Registrando...' : 'Confirmar Recepción' }}
                </button>
            </div>
        </div>
    </DriverLayout>
</template>

<style>
.leaflet-control-attribution { display: none; }
</style>