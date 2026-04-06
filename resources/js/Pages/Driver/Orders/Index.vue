<script setup>
import { ref, computed } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import DriverLayout from '@/Layouts/DriverLayout.vue';
import { Truck, RefreshCw, Hash, MapPin, X } from 'lucide-vue-next';

const props = defineProps({
    driver: Object,
    availableOrders: Array,
});

const isOnline = computed(() => props.driver.is_online);
const showOtpModal = ref(false);
const selectedOrderId = ref(null);

const form = useForm({
    otp: '',
});

const toggleOnline = () => {
    router.post(route('driver.status.toggle'), { is_online: !isOnline.value }, { preserveScroll: true });
};

const refreshOrders = () => {
    router.reload({ only: ['availableOrders'] });
};

const openOtpInput = (orderId) => {
    selectedOrderId.value = orderId;
    form.clearErrors();
    form.otp = '';
    showOtpModal.value = true;
};

const submitTakeOrder = () => {
    form.post(route('driver.orders.take', selectedOrderId.value), {
        onSuccess: () => {
            showOtpModal.value = false;
        },
    });
};
</script>

<template>
    <DriverLayout>
        <div class="max-w-md mx-auto p-4 space-y-6 pb-24">
            
            <div class="bg-white p-5 rounded-[2rem] shadow-sm border flex items-center justify-between">
                <div>
                    <p class="text-[10px] font-black uppercase text-gray-400 tracking-widest">Mi Disponibilidad</p>
                    <h2 class="text-lg font-black uppercase italic" :class="isOnline ? 'text-green-600' : 'text-gray-400'">
                        {{ isOnline ? 'En Línea' : 'Desconectado' }}
                    </h2>
                </div>
                <button @click="toggleOnline" 
                    class="w-14 h-8 rounded-full transition-all relative border-2"
                    :class="isOnline ? 'bg-green-500 border-green-600' : 'bg-gray-200 border-gray-300'">
                    <div class="absolute top-1 w-5 h-5 bg-white rounded-full transition-all shadow-sm"
                         :class="isOnline ? 'left-7' : 'left-1'"></div>
                </button>
            </div>

            <div v-if="isOnline" class="space-y-4">
                <div class="flex justify-between items-center px-2">
                    <h3 class="text-xs font-black uppercase text-gray-500 italic">Carga Lista ({{ availableOrders.length }})</h3>
                    <button @click="refreshOrders" class="p-2 text-blue-600 hover:bg-blue-50 rounded-full transition-colors">
                        <RefreshCw :size="18" />
                    </button>
                </div>

                <div v-if="availableOrders.length > 0" class="space-y-3">
                    <div v-for="order in availableOrders" :key="order.id" 
                        class="bg-white border rounded-[2rem] p-5 shadow-sm hover:border-blue-400 transition-all group">
                        <div class="flex justify-between items-center mb-3">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center"><Hash :size="14" /></div>
                                <span class="font-mono font-black text-sm">{{ order.code }}</span>
                            </div>
                            <span class="text-xs font-black text-green-600 italic">Bs. {{ order.delivery_fee }}</span>
                        </div>
                        <div class="flex items-start gap-2 mb-5">
                            <MapPin :size="14" class="text-gray-400 shrink-0 mt-0.5" />
                            <p class="text-[11px] font-bold text-gray-600 line-clamp-2 uppercase leading-tight">{{ order.delivery_data.address }}</p>
                        </div>
                        <button @click="openOtpInput(order.id)" 
                            class="w-full py-3 bg-gray-900 text-white rounded-xl text-[10px] font-black uppercase tracking-[0.2em] transition-colors active:scale-95">
                            Reclamar Orden
                        </button>
                    </div>
                </div>

                <div v-else class="py-12 text-center bg-white rounded-[2.5rem] border border-dashed border-gray-300">
                    <Truck :size="40" class="mx-auto text-gray-300 mb-3" />
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Sin despacho pendiente</p>
                </div>
            </div>

            <div v-else class="py-20 text-center space-y-4">
                <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto text-gray-400">
                    <Truck :size="32" />
                </div>
                <p class="text-xs font-black text-gray-400 uppercase italic">Ponte en línea para ver la carga</p>
            </div>
        </div>

        <div v-if="showOtpModal" class="fixed inset-0 z-[100] flex items-end md:items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
            <div class="bg-white w-full max-w-sm rounded-[2.5rem] p-8 shadow-2xl">
                <div class="flex justify-between items-center mb-6">
                    <h4 class="font-black uppercase italic text-gray-900">Validar Extracción</h4>
                    <button @click="showOtpModal = false" class="text-gray-400"><X :size="24" /></button>
                </div>
                
                <p class="text-[10px] text-gray-500 font-bold mb-6 uppercase text-center">
                    Ingresa el PIN de seguridad dictado por el administrador de base.
                </p>

                <input v-model="form.otp" type="text" maxlength="5" placeholder="_____" 
                       class="w-full text-center text-4xl font-mono font-black tracking-[0.5em] border-2 border-gray-100 bg-gray-50 rounded-2xl py-5 focus:border-blue-500 focus:ring-0 uppercase mb-4"
                       @input="form.clearErrors()">

                <p v-if="form.errors.otp" class="text-[10px] text-red-500 font-black text-center mb-4 uppercase">{{ form.errors.otp }}</p>

                <button @click="submitTakeOrder" :disabled="form.processing || form.otp.length < 5"
                        class="w-full py-4 bg-blue-600 text-white rounded-2xl font-black uppercase tracking-widest text-xs shadow-lg disabled:opacity-30 active:scale-95">
                    {{ form.processing ? 'Sincronizando...' : 'Confirmar' }}
                </button>
            </div>
        </div>
    </DriverLayout>
</template>