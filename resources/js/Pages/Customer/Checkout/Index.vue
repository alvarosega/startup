<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import ShopLayout from '@/Layouts/ShopLayout.vue';
import StaticLocationMap from '@/Components/Customer/Maps/StaticLocationMap.vue';

import { 
    ArrowRight, AlertTriangle, Store, Truck, CreditCard, 
    Loader2, Sparkles, CheckCircle2, MapPin
} from 'lucide-vue-next';

const props = defineProps({
    cart: Object,
    pickup_logistics: Object,
    delivery_logistics: Object,
    customer_location: Object,
    config: Object
});

const form = useForm({
    delivery_type: 'pickup',
    payment_method: 'qr'
});

const deliveryType = ref('pickup'); 

// BLINDAJE: Verificamos si el usuario realmente tiene coordenadas válidas
const hasLocation = computed(() => {
    return props.customer_location?.lat != null && props.customer_location?.lng != null;
});

onMounted(() => {
    if (hasLocation.value) {
        deliveryType.value = 'delivery';
        form.delivery_type = 'delivery';
    } else {
        deliveryType.value = 'pickup';
        form.delivery_type = 'pickup';
    }
});

watch(deliveryType, (newVal) => {
    form.delivery_type = newVal;
});

const currentLogistics = computed(() => {
    return deliveryType.value === 'pickup' ? props.pickup_logistics : props.delivery_logistics;
});

// SEGURIDAD DE TIPOS: Forzamos que siempre sean números para evitar errores de .toFixed()
const subtotalProducts = computed(() => Number(props.cart?.total_price || 0));
const deliveryFee = computed(() => Number(currentLogistics.value?.delivery_fee || 0));
const serviceFee = computed(() => Number(currentLogistics.value?.service_fee || 0));

const finalTotal = computed(() => {
    return subtotalProducts.value + deliveryFee.value + serviceFee.value;
});

const isPaymentDisabled = computed(() => {
    if (form.processing) return true;
    if (deliveryType.value === 'delivery' && !currentLogistics.value?.is_available) return true;
    return false;
});
const 
generateIdempotencyKey = () => {
    return window.crypto.randomUUID(); // Estándar moderno de navegador
};

const submit = () => {
    if (isPaymentDisabled.value) return;
    
    form.post(route('customer.checkout.store'), {
        headers: {
            'X-Idempotency-Key': generateIdempotencyKey()
        },
        preserveScroll: true,
        onSuccess: () => {
            console.log('Orden procesada con éxito y sin duplicados.');
        },
        onError: (errors) => {
            console.error('Error en el checkout:', errors);
        }
    }); 
};
</script>

<template>
    <ShopLayout>
        <div class="container mx-auto px-4 py-8 max-w-6xl mb-24">
            
            <div class="mb-10 text-center lg:text-left">
                <h1 class="font-black text-4xl text-foreground uppercase tracking-tighter italic">
                    Finalizar <span class="text-primary">Pedido</span>
                </h1>
                <p class="text-xs text-foreground/40 font-mono mt-2 uppercase tracking-widest">
                    Confirmación de parámetros y logística segura
                </p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
                
                <div class="lg:col-span-8 space-y-8">
                    
                    <section class="bg-surface/40 backdrop-blur-xl rounded-[32px] border border-white/10 p-8">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <label class="cursor-pointer group">
                                <input type="radio" v-model="deliveryType" value="pickup" class="peer hidden">
                                <div class="h-full border-2 rounded-[28px] p-6 flex items-center gap-5 transition-all peer-checked:border-primary peer-checked:bg-primary/5 border-white/5 bg-black/20">
                                    <Store :size="28" class="text-foreground/30 peer-checked:text-primary shrink-0" />
                                    <div>
                                        <span class="block font-black text-sm uppercase">Retiro Local</span>
                                        <span class="text-[10px] font-bold text-emerald-500 uppercase tracking-widest bg-emerald-500/10 px-2 py-0.5 rounded mt-1 inline-block">Bs 0.00</span>
                                    </div>
                                </div>
                            </label>

                            <label class="cursor-pointer group" :class="{ 'opacity-50 pointer-events-none': !hasLocation }">
                                <input type="radio" v-model="deliveryType" value="delivery" class="peer hidden" :disabled="!hasLocation">
                                <div class="h-full border-2 rounded-[28px] p-6 flex flex-col justify-center transition-all peer-checked:border-primary peer-checked:bg-primary/5 border-white/5 bg-black/20"
                                     :class="{ 'border-f1-red/30 bg-f1-red/5': !hasLocation }">
                                    <div class="flex items-center gap-5">
                                        <Truck :size="28" class="text-foreground/30 peer-checked:text-primary shrink-0" 
                                               :class="{ 'text-f1-red/50': !hasLocation }" />
                                        <div>
                                            <span class="block font-black text-sm uppercase">A Domicilio</span>
                                            <span v-if="hasLocation" class="text-[10px] font-bold text-primary uppercase tracking-widest bg-primary/10 px-2 py-0.5 rounded mt-1 inline-block">
                                                Bs {{ Number(delivery_logistics?.delivery_fee || 0).toFixed(2) }}
                                            </span>
                                            <span v-else class="text-[9px] font-bold text-f1-red uppercase tracking-widest mt-1 block leading-tight">
                                                Configura tu ubicación en el perfil
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </section>

                    <section v-if="deliveryType === 'delivery' && hasLocation" class="space-y-4 animate-in slide-in-from-top-4 duration-500">
                        <div class="bg-surface/40 backdrop-blur-xl rounded-[32px] border border-white/10 p-2 overflow-hidden">
                            <StaticLocationMap 
                                :lat="Number(customer_location.lat)" 
                                :lng="Number(customer_location.lng)" 
                                address="Ubicación de tu Perfil" 
                            />
                        </div>

                        <div class="flex items-center justify-between px-6 py-4 bg-white/5 rounded-2xl border border-white/5">
                            <div class="flex items-center gap-3">
                                <MapPin :size="18" class="text-primary" />
                                <span class="text-[10px] font-black uppercase tracking-widest text-foreground/60">Distancia Estimada</span>
                            </div>
                            <span class="font-mono font-black text-sm text-foreground">{{ Number(delivery_logistics?.distance_km || 0).toFixed(2) }} KM</span>
                        </div>

                        <div v-if="!delivery_logistics?.is_available" class="p-4 bg-f1-red/10 border border-f1-red/20 rounded-2xl flex gap-3 items-center">
                            <AlertTriangle class="text-f1-red shrink-0" :size="20" />
                            <p class="text-[10px] font-black text-f1-red uppercase tracking-tight">{{ delivery_logistics?.error_message }}</p>
                        </div>
                    </section>
                </div>

                <div class="lg:col-span-4">
                    <div class="bg-surface/60 backdrop-blur-2xl p-8 rounded-[40px] border border-white/10 shadow-2xl lg:sticky lg:top-28">
                        <h3 class="font-black text-foreground/80 text-lg mb-8 uppercase tracking-tighter flex items-center gap-2 italic">
                            <CreditCard :size="20" class="text-primary"/> Recibo Final
                        </h3>
                        
                        <div class="space-y-5 mb-8">
                            <div class="flex justify-between text-sm">
                                <span class="text-foreground/40 font-bold uppercase text-[10px] tracking-widest">Subtotal Neto</span>
                                <span class="font-mono font-black">Bs {{ subtotalProducts.toFixed(2) }}</span>
                            </div>
                            
                            <div class="flex justify-between items-start">
                                <div class="flex flex-col">
                                    <span class="text-foreground/40 font-bold uppercase text-[10px] tracking-widest">Fee Digital</span>
                                    <span v-if="currentLogistics?.savings_loyalty > 0" class="text-[9px] font-black text-emerald-400 uppercase mt-1 flex items-center gap-1">
                                        <Sparkles :size="10"/> Loyalty Discount
                                    </span>
                                </div>
                                <span class="font-mono font-black">Bs {{ serviceFee.toFixed(2) }}</span>
                            </div>

                            <div class="flex justify-between text-sm">
                                <span class="text-foreground/40 font-bold uppercase text-[10px] tracking-widest">Logística de Envío</span>
                                <span v-if="deliveryType === 'pickup'" class="text-emerald-500 font-black text-[10px] uppercase">Free</span>
                                <span v-else class="font-mono font-black">Bs {{ deliveryFee.toFixed(2) }}</span>
                            </div>

                            <div class="h-px bg-white/5 my-4"></div>
                            
                            <div class="flex justify-between items-end">
                                <span class="text-[10px] font-black text-foreground/20 uppercase mb-2 tracking-[0.2em]">Total</span>
                                <span class="text-5xl font-black text-primary tracking-tighter drop-shadow-[0_0_15px_rgba(var(--primary-rgb),0.3)]">
                                    <span class="text-base font-mono mr-1">Bs</span>{{ finalTotal.toFixed(2) }}
                                </span>
                            </div>
                        </div>

                        <div class="bg-primary/10 border border-primary/20 p-4 rounded-2xl flex gap-3 mb-8">
                            <CheckCircle2 class="text-primary shrink-0" :size="20" />
                            <p class="text-[10px] font-bold text-foreground/70 leading-tight uppercase">
                                Reserva válida por <span class="text-primary">{{ config?.reservation_minutes }} min</span>.
                            </p>
                        </div>

                        <button @click="submit" :disabled="isPaymentDisabled" 
                            class="w-full h-20 bg-primary hover:bg-primary/90 text-white font-black rounded-[24px] shadow-xl shadow-primary/20 flex flex-col items-center justify-center transition-all active:scale-95 disabled:opacity-20 disabled:grayscale">
                            
                            <template v-if="form.processing">
                                <Loader2 class="animate-spin" :size="24"/>
                            </template>
                            <template v-else>
                                <div class="flex items-center gap-2 text-lg uppercase italic tracking-tighter">
                                    Confirmar Pedido <ArrowRight :size="20" stroke-width="3"/>
                                </div>
                                <span class="text-[9px] font-bold opacity-60 uppercase tracking-widest">Generar Comprobante QR</span>
                            </template>
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </ShopLayout>
</template>