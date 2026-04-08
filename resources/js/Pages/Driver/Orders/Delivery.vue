<script setup>
import { computed } from 'vue';
import { useForm, Head } from '@inertiajs/vue3';
import { Map, Phone, CheckCircle, Navigation, Key, User } from 'lucide-vue-next';
import DriverLayout from '@/Layouts/DriverLayout.vue';

const props = defineProps({ order: Object });

// Formularios independientes para los dos pasos
const arrivedForm = useForm({});
const deliverForm = useForm({ delivery_otp: '' });

const markArrived = () => arrivedForm.post(route('driver.orders.arrived', props.order.code));
const submitDeliver = () => deliverForm.post(route('driver.orders.deliver', props.order.code));

const mapUrl = computed(() => {
    const lat = props.order.delivery_data?.lat;
    const lng = props.order.delivery_data?.lng;
    return `https://www.google.com/maps/search/?api=1&query=${lat},${lng}`;
});
</script>

<template>
    <DriverLayout>
        <Head :title="'Entrega #' + order.code" />
        <div class="p-4 space-y-4 pb-24">
            
            <div class="text-center pt-4 pb-2">
                <h1 class="text-2xl font-black uppercase italic tracking-tighter">Última <span class="text-primary">Milla</span></h1>
                <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest mt-1">Orden #{{ order.code }}</p>
            </div>

            <div class="bg-card border border-border rounded-3xl p-6 shadow-sm">
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-12 h-12 bg-muted rounded-2xl flex items-center justify-center text-muted-foreground">
                        <User :size="24" />
                    </div>
                    <div class="flex-1">
                        <p class="text-[9px] font-black uppercase tracking-widest text-muted-foreground">Entregar a</p>
                        <p class="text-sm font-black uppercase">{{ order.customer.name }}</p>
                    </div>
                    <a :href="'tel:' + order.customer.phone" class="w-12 h-12 bg-emerald-500/10 text-emerald-500 rounded-2xl flex items-center justify-center hover:bg-emerald-500 hover:text-white transition-colors">
                        <Phone :size="20" />
                    </a>
                </div>

                <a :href="mapUrl" target="_blank" class="w-full py-4 bg-blue-500/10 text-blue-600 rounded-2xl font-black uppercase text-xs tracking-widest flex items-center justify-center gap-2 hover:bg-blue-500 hover:text-white transition-all">
                    <Navigation :size="16" /> Abrir en Google Maps
                </a>
            </div>

            <div v-if="order.status === 'dispatched'" class="pt-8">
                <button @click="markArrived" :disabled="arrivedForm.processing"
                    class="w-full h-20 bg-foreground text-background rounded-[2rem] font-black uppercase text-sm tracking-widest shadow-2xl active:scale-95 transition-all flex flex-col items-center justify-center">
                    <span v-if="arrivedForm.processing" class="w-6 h-6 border-2 border-background border-t-transparent rounded-full animate-spin"></span>
                    <template v-else>
                        <Map :size="24" class="mb-1" />
                        He llegado al destino
                    </template>
                </button>
                <p class="text-center text-[9px] font-bold text-muted-foreground uppercase mt-4">Presiona solo cuando estés en la puerta del cliente.</p>
            </div>

            <div v-if="order.status === 'arrived'" class="bg-amber-500/10 border-2 border-amber-500/30 rounded-[2.5rem] p-8 mt-4 animate-in slide-in-from-bottom-4">
                <div class="text-center mb-6">
                    <Key class="mx-auto text-amber-500 mb-3" :size="32" />
                    <h3 class="text-sm font-black uppercase tracking-widest text-amber-600">Validación de Entrega</h3>
                    <p class="text-[10px] font-bold text-amber-600/70 mt-1">Solicita el PIN de 4 dígitos al cliente para liberar el paquete.</p>
                </div>

                <div class="space-y-4">
                    <input v-model="deliverForm.delivery_otp" type="number" maxlength="4" placeholder="0000"
                        class="w-full text-center text-5xl font-mono font-black tracking-[0.3em] bg-background border-none rounded-3xl py-6 focus:ring-4 focus:ring-amber-500/20 transition-all text-amber-600">
                    <p v-if="deliverForm.errors.delivery_otp" class="text-[10px] text-destructive font-black uppercase mt-2 text-center">{{ deliverForm.errors.delivery_otp }}</p>

                    <button @click="submitDeliver" :disabled="deliverForm.delivery_otp.length < 4 || deliverForm.processing"
                        class="w-full h-16 bg-amber-500 text-white rounded-2xl font-black uppercase text-sm tracking-widest shadow-xl active:scale-95 transition-all disabled:opacity-30 mt-4">
                        {{ deliverForm.processing ? 'Validando...' : 'Confirmar Entrega' }}
                    </button>
                </div>
            </div>

        </div>
    </DriverLayout>
</template>