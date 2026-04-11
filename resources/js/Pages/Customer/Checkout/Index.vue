<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
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
    // Si es delivery y no hay ubicación o la zona no está disponible
    if (deliveryType.value === 'delivery' && (!hasLocation.value || !props.delivery_logistics?.is_available)) return true;
    return false;
});
const 
generateIdempotencyKey = () => {
    return window.crypto.randomUUID(); // Estándar moderno de navegador
};

const submit = () => {
    if (isPaymentDisabled.value) return;
    
    form.post(route('customer.checkout.store'), {
        headers: { 'X-Idempotency-Key': generateIdempotencyKey() },
        preserveScroll: true,
        onStart: () => { /* Bloqueo de UI opcional */ },
        onError: (errors) => {
            // Notificar error de stock o logística mediante flash/toast
        }
    }); 
};
</script>

<template>
    <ShopLayout>
        <Head title="Protocolo de Despacho" />
        <div v-if="$page.props.errors.checkout_error || Object.keys($page.props.errors).length > 0" 
            class="mb-8 product-card bg-destructive/10 border-destructive/20 p-4 animate-in fade-in slide-in-from-top-2">
            <div class="flex items-center gap-3">
                <AlertTriangle class="text-destructive" :size="20" />
                <p class="text-xs font-black text-destructive uppercase tracking-widest">
                    Falla en Protocolo: {{ $page.props.errors.checkout_error || 'Error de Validación en Campos' }}
                </p>
            </div>
            <ul class="mt-2 ml-8 list-disc">
                <li v-for="(error, field) in $page.props.errors" :key="field" class="text-[10px] text-destructive/80 font-bold uppercase">
                    {{ field }}: {{ Array.isArray(error) ? error[0] : error }}
                </li>
            </ul>
        </div>

        <div class="w-full pb-32 pt-8 relative z-10">
            <div class="max-w-6xl mx-auto px-4 lg:px-8">
                
                <div class="mb-12 text-center lg:text-left border-b border-border/10 pb-8">
                    <h1 class="font-black text-4xl md:text-5xl text-foreground uppercase tracking-tighter italic leading-none">
                        Finalizar <span class="text-primary">Pedido</span>
                    </h1>
                    <div class="flex items-center justify-center lg:justify-start gap-3 mt-4">
                        <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <p class="text-xs text-foreground/40 font-mono uppercase tracking-[0.3em]">
                            Protocolo de Logística Segura v4.0
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-12">
                    
                    <div class="lg:col-span-8 space-y-10">
                        <section class="space-y-6">
                            <h2 class="text-xs font-black uppercase tracking-[0.3em] text-foreground/40 px-2">01. Selección de Modalidad</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <label class="cursor-pointer group">
                                    <input type="radio" v-model="deliveryType" value="pickup" class="peer hidden">
                                    <div class="product-card glass-manifest h-full p-6 flex items-center gap-6 border-2 border-transparent transition-all peer-checked:border-primary peer-checked:bg-primary/5 active:scale-[0.98]">
                                        <div class="w-14 h-14 rounded-2xl bg-foreground/5 flex items-center justify-center group-hover:scale-110 transition-transform">
                                            <Store :size="28" class="text-foreground/20 peer-checked:text-primary" />
                                        </div>
                                        <div>
                                            <span class="block font-black text-sm uppercase tracking-tight">Retiro Local</span>
                                            <span class="text-xs font-bold text-emerald-500 uppercase tracking-widest mt-1 inline-block">Sin Costo</span>
                                        </div>
                                    </div>
                                </label>

                                <label class="cursor-pointer group" :class="{ 'opacity-40 grayscale': !hasLocation }">
                                    <input type="radio" v-model="deliveryType" value="delivery" class="peer hidden" :disabled="!hasLocation">
                                    <div class="product-card glass-manifest h-full p-6 flex items-center gap-6 border-2 border-transparent transition-all peer-checked:border-primary peer-checked:bg-primary/5 active:scale-[0.98]">
                                        <div class="w-14 h-14 rounded-2xl bg-foreground/5 flex items-center justify-center group-hover:scale-110 transition-transform">
                                            <Truck :size="28" class="text-foreground/20 peer-checked:text-primary" />
                                        </div>
                                        <div class="flex-1">
                                            <span class="block font-black text-sm uppercase tracking-tight">A Domicilio</span>
                                            <span v-if="hasLocation" class="text-xs font-bold text-primary uppercase tracking-widest mt-1 inline-block">
                                                Bs {{ deliveryFee.toFixed(2) }}
                                            </span>
                                            <span v-else class="text-[10px] font-black text-destructive uppercase tracking-tighter mt-1 block leading-tight">
                                                Ubicación Requerida
                                            </span>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </section>

                        <section v-if="deliveryType === 'delivery' && hasLocation" class="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-700">
                            <h2 class="text-xs font-black uppercase tracking-[0.3em] text-foreground/40 px-2">02. Punto de Despacho</h2>
                            <div class="product-card glass-manifest p-3 overflow-hidden">
                                <div class="rounded-2xl overflow-hidden grayscale-[0.5] contrast-[1.2] hover:grayscale-0 transition-all duration-1000">
                                    <StaticLocationMap 
                                        :lat="Number(customer_location.lat)" 
                                        :lng="Number(customer_location.lng)" 
                                        address="Punto de Entrega Validado" 
                                    />
                                </div>
                                <div class="mt-4 px-4 py-3 flex items-center justify-between border-t border-border/10">
                                    <div class="flex items-center gap-3">
                                        <MapPin :size="16" class="text-primary" />
                                        <span class="text-xs font-black uppercase tracking-widest text-foreground/60">Alcance de Radar</span>
                                    </div>
                                    <span class="font-mono font-black text-sm text-foreground">{{ Number(delivery_logistics?.distance_km || 0).toFixed(2) }} KM</span>
                                </div>
                            </div>
                            
                            <div v-if="!delivery_logistics?.is_available" class="product-card bg-destructive/10 border-destructive/20 p-5 flex gap-4 items-center">
                                <AlertTriangle class="text-destructive shrink-0" :size="24" />
                                <p class="text-xs font-black text-destructive uppercase leading-relaxed">{{ delivery_logistics?.error_message }}</p>
                            </div>
                        </section>
                    </div>

                    <aside class="lg:col-span-4">
                        <div class="glass-titanium !rounded-3xl p-8 sticky top-28 shadow-apple-soft border border-primary/10">
                            <h3 class="font-black text-foreground text-lg mb-8 uppercase tracking-tighter flex items-center gap-3 italic border-b border-border/10 pb-6">
                                <CreditCard :size="20" class="text-primary"/> Recibo Final
                            </h3>
                            
                            <div class="space-y-5 mb-10 font-mono">
                                <div class="flex justify-between items-center text-xs">
                                    <span class="text-foreground/40 font-bold uppercase tracking-widest">Hardware Neto</span>
                                    <span class="font-black">Bs {{ subtotalProducts.toFixed(2) }}</span>
                                </div>
                                
                                <div class="flex justify-between items-start text-xs">
                                    <div class="flex flex-col">
                                        <span class="text-foreground/40 font-bold uppercase tracking-widest">Fee Digital</span>
                                        <span v-if="pickup_logistics?.savings_loyalty > 0" class="text-[10px] font-black text-emerald-500 uppercase mt-1">
                                            Loyalty Applied
                                        </span>
                                    </div>
                                    <span class="font-black">
                                        {{ pickup_logistics === undefined ? '---' : 'Bs ' + serviceFee.toFixed(2) }}
                                    </span>
                                </div>

                                <div class="flex justify-between items-center text-xs">
                                    <span class="text-foreground/40 font-bold uppercase tracking-widest">Logística</span>
                                    <span v-if="deliveryType === 'pickup'" class="text-emerald-500 font-black uppercase">Liberado</span>
                                    <span v-else class="font-black">Bs {{ deliveryFee.toFixed(2) }}</span>
                                </div>

                                <div class="h-px bg-border/10 my-6"></div>
                                
                                <div class="flex justify-between items-end">
                                    <span class="text-xs font-black text-foreground/30 uppercase mb-2 tracking-[0.3em]">Total</span>
                                    <span class="text-5xl font-black text-foreground tracking-tighter italic">
                                        <span class="text-lg text-primary mr-1.5 not-italic font-sans font-black">Bs</span>{{ finalTotal.toFixed(2) }}
                                    </span>
                                </div>
                            </div>

                            <div class="bg-primary/5 rounded-2xl p-4 flex gap-4 mb-8 border border-primary/10">
                                <CheckCircle2 class="text-primary shrink-0" :size="20" />
                                <p class="text-[10px] font-black text-foreground/60 leading-tight uppercase tracking-tight">
                                    Hardware reservado por <span class="text-primary">{{ config?.reservation_minutes }} min</span> bajo protocolos de seguridad.
                                </p>
                            </div>

                            <button @click="submit" :disabled="isPaymentDisabled" 
                                class="btn-primary w-full h-20 !rounded-2xl shadow-f1-glow flex flex-col items-center justify-center transition-all group overflow-hidden">
                                
                                <template v-if="form.processing">
                                    <Loader2 class="animate-spin" :size="24"/>
                                </template>
                                <template v-else>
                                    <div class="flex items-center gap-3 text-xl font-black uppercase italic tracking-tighter group-hover:scale-105 transition-transform">
                                        Confirmar <ArrowRight :size="24" stroke-width="3"/>
                                    </div>
                                    <span class="text-[10px] font-bold opacity-40 uppercase tracking-[0.3em] mt-1">Generar Comprobante QR</span>
                                </template>
                            </button>
                        </div>
                    </aside>

                </div>
            </div>
        </div>
    </ShopLayout>
</template>

<style scoped>
/* REUTILIZACIÓN DE CRISTALERÍA DEL CARRITO */
.glass-manifest {
    background-color: hsl(var(--card) / 0.5);
    backdrop-filter: blur(40px) saturate(200%);
    -webkit-backdrop-filter: blur(40px) saturate(200%);
    border-color: hsl(var(--border) / 0.4);
}

.dark .glass-manifest {
    background-color: hsl(var(--card) / 0.7);
}

.shadow-f1-glow {
    box-shadow: 0 0 25px -5px hsl(var(--primary) / 0.5);
}

.product-card { @apply !rounded-3xl; }
</style>