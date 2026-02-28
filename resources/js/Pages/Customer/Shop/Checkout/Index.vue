<script setup>
import { ref, computed, watch } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import ShopLayout from '@/Layouts/ShopLayout.vue';
import { 
    MapPin, ShieldCheck, ArrowRight, AlertTriangle, 
    Store, Truck, CreditCard, PackageCheck, Loader2, Sparkles,
    CheckCircle2, Split
} from 'lucide-vue-next';

const props = defineProps({
    cart: Object,
    pickup_logistics: Object,
    addresses: { type: Array, default: () => [] },
    default_address_id: [String, Number] 
});

// --- ESTADOS REACTIVOS ---
const deliveryType = ref('pickup'); 
const paymentType = ref('total'); // Por defecto el camino feliz

const form = useForm({
    delivery_type: 'pickup',
    address_id: null,
    payment_type: 'total'
});

// --- LÓGICA DE ESCENARIOS Y COBERTURA ---
const canDoDelivery = computed(() => props.addresses.length > 0);

if (canDoDelivery.value && props.default_address_id) {
    form.address_id = props.default_address_id;
    deliveryType.value = 'delivery'; 
}

watch(deliveryType, (newVal) => {
    form.delivery_type = newVal;
    if (newVal === 'delivery' && !form.address_id && canDoDelivery.value) {
        form.address_id = props.addresses[0].id;
    }
    if (newVal === 'pickup') form.address_id = null;
});

watch(paymentType, (newVal) => {
    form.payment_type = newVal;
});

// --- MOTOR FINANCIERO FRONTEND ---
const currentLogistics = computed(() => {
    if (deliveryType.value === 'pickup') return props.pickup_logistics;
    if (form.address_id) {
        const addr = props.addresses.find(a => a.id === form.address_id);
        return addr ? addr.logistics : null;
    }
    return { delivery_fee: 0, service_fee: 0, total_logistics: 0, is_penalty_applied: false, penalty_amount: 0 };
});

const subtotalProducts = computed(() => props.cart?.subtotal || 0);
const logisticsTotal = computed(() => currentLogistics.value?.total_logistics || 0);

const finalTotal = computed(() => subtotalProducts.value + logisticsTotal.value);

// Lógica dura idéntica al Backend: 100% Logística + 30% Productos
const advanceAmount = computed(() => {
    if (paymentType.value === 'total') return finalTotal.value;
    return logisticsTotal.value + (subtotalProducts.value * 0.30);
});

const balanceAmount = computed(() => {
    return finalTotal.value - advanceAmount.value;
});

// --- SUBMIT ---
const submit = () => {
    if (deliveryType.value === 'delivery' && !form.address_id) return;
    form.post(route('customer.checkout.store')); 
};
</script>

<template>
    <ShopLayout>
        <div class="container mx-auto px-4 py-8 pb-48 lg:pb-12">
            
            <div class="mb-8">
                <h1 class="font-display font-black text-3xl text-foreground uppercase tracking-tight italic">
                    Finalizar <span class="text-primary">Compra</span>
                </h1>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
                
                <div class="lg:col-span-2 space-y-6">
                    
                    <section class="bg-card rounded-3xl border border-border p-6 shadow-sm">
                        <h2 class="font-bold text-foreground text-xs uppercase tracking-widest mb-6 flex items-center gap-2 opacity-60">
                            01. Método de Entrega
                        </h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <label class="cursor-pointer relative group">
                                <input type="radio" v-model="deliveryType" value="pickup" class="peer hidden">
                                <div class="h-full border-2 rounded-2xl p-5 flex items-center gap-4 transition-all duration-300 
                                            peer-checked:border-primary peer-checked:bg-primary/5 border-border bg-background hover:border-primary/40">
                                    <div class="w-12 h-12 rounded-xl bg-muted flex items-center justify-center peer-checked:bg-primary text-muted-foreground peer-checked:text-primary-foreground transition-colors">
                                        <Store :size="24" />
                                    </div>
                                    <div>
                                        <span class="block font-black text-sm text-foreground">Recojo en Tienda</span>
                                        <span class="text-[10px] font-bold text-success uppercase">Gratis</span>
                                    </div>
                                </div>
                            </label>

                            <label :class="['relative group', canDoDelivery ? 'cursor-pointer' : 'cursor-not-allowed opacity-50']">
                                <input type="radio" v-model="deliveryType" value="delivery" :disabled="!canDoDelivery" class="peer hidden">
                                <div class="h-full border-2 rounded-2xl p-5 flex items-center gap-4 transition-all duration-300 
                                            peer-checked:border-primary peer-checked:bg-primary/5 border-border bg-background">
                                    <div class="w-12 h-12 rounded-xl bg-muted flex items-center justify-center text-muted-foreground transition-colors">
                                        <Truck :size="24" />
                                    </div>
                                    <div>
                                        <span class="block font-black text-sm text-foreground">Envío a Domicilio</span>
                                        <span v-if="!canDoDelivery" class="text-[10px] font-bold text-destructive uppercase">Sin Cobertura</span>
                                        <span v-else class="text-[10px] font-bold text-primary uppercase">Sujeto a distancia</span>
                                    </div>
                                </div>
                            </label>
                        </div>

                        <div v-if="!canDoDelivery && deliveryType === 'delivery'" class="mt-4 p-4 bg-destructive/5 border border-destructive/20 rounded-2xl flex gap-3 items-start animate-in zoom-in duration-300">
                            <AlertTriangle class="text-destructive shrink-0" :size="20" />
                            <div>
                                <p class="text-sm font-bold text-destructive">Ubicación fuera de zona</p>
                                <p class="text-xs text-muted-foreground mt-1">
                                    Tu ubicación actual no coincide con el área de reparto de esta sucursal. 
                                    <Link :href="route('customer.profile.addresses')" class="text-primary font-bold underline ml-1">Cambiar ubicación</Link>
                                </p>
                            </div>
                        </div>
                    </section>

                    <section v-if="deliveryType === 'delivery' && canDoDelivery" class="bg-card rounded-3xl border border-border p-6 shadow-sm animate-in slide-in-from-top-4 duration-500">
                        <h2 class="font-bold text-foreground text-xs uppercase tracking-widest mb-6 opacity-60">02. Confirmar Dirección</h2>
                        <div class="space-y-3">
                            <label v-for="addr in addresses" :key="addr.id" 
                                class="flex items-start gap-4 p-5 border-2 rounded-2xl cursor-pointer transition-all bg-background"
                                :class="form.address_id === addr.id ? 'border-primary bg-primary/5 shadow-md' : 'border-border hover:border-primary/20'">
                                
                                <input type="radio" :value="addr.id" v-model="form.address_id" class="mt-1 accent-primary w-4 h-4">
                                
                                <div class="flex-1">
                                    <div class="flex justify-between items-start">
                                        <span class="font-black text-sm uppercase tracking-tight">{{ addr.alias }}</span>
                                        <div class="text-right">
                                            <span class="block font-mono font-bold text-xs">Bs {{ addr.logistics.delivery_fee.toFixed(2) }}</span>
                                            <span class="block text-[9px] text-muted-foreground uppercase font-bold">Costo de Envío</span>
                                        </div>
                                    </div>
                                    <p class="text-xs text-muted-foreground mt-1 line-clamp-1">{{ addr.address }}</p>
                                </div>
                            </label>
                        </div>
                    </section>

                    <section class="bg-card rounded-3xl border border-border p-6 shadow-sm">
                        <h2 class="font-bold text-foreground text-xs uppercase tracking-widest mb-6 flex items-center gap-2 opacity-60">
                            03. Modalidad de Pago
                        </h2>
                        
                        <div class="space-y-4">
                            <label class="cursor-pointer relative block">
                                <input type="radio" v-model="paymentType" value="total" class="peer hidden">
                                <div class="border-2 rounded-2xl p-5 transition-all duration-300 peer-checked:border-success peer-checked:bg-success/5 border-border bg-background hover:border-success/40">
                                    <div class="flex justify-between items-start mb-2">
                                        <div class="flex items-center gap-3">
                                            <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center transition-colors border-muted-foreground peer-checked:border-success">
                                                <div class="w-2.5 h-2.5 rounded-full bg-success opacity-0 peer-checked:opacity-100 transition-opacity"></div>
                                            </div>
                                            <span class="font-black text-sm text-foreground">Pago Total (Recomendado)</span>
                                        </div>
                                        <span class="text-[10px] font-black text-success bg-success/10 px-2 py-1 rounded uppercase tracking-wider flex items-center gap-1">
                                            <ShieldCheck :size="12"/> Garantía 100%
                                        </span>
                                    </div>
                                    <div class="pl-9">
                                        <p class="text-xs text-muted-foreground leading-relaxed">
                                            Paga el total ahora. Cuentas con nuestra <strong class="text-foreground">Garantía de Reembolso Inmediato</strong> si ocurre algún inconveniente con la entrega. Sin demoras en la puerta.
                                        </p>
                                    </div>
                                </div>
                            </label>

                            <label class="cursor-pointer relative block">
                                <input type="radio" v-model="paymentType" value="partial" class="peer hidden">
                                <div class="border-2 rounded-2xl p-5 transition-all duration-300 peer-checked:border-warning peer-checked:bg-warning/5 border-border bg-background">
                                    <div class="flex justify-between items-start mb-2">
                                        <div class="flex items-center gap-3">
                                            <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center transition-colors border-muted-foreground peer-checked:border-warning">
                                                <div class="w-2.5 h-2.5 rounded-full bg-warning opacity-0 peer-checked:opacity-100 transition-opacity"></div>
                                            </div>
                                            <span class="font-black text-sm text-foreground">Anticipo de Reserva</span>
                                        </div>
                                        <span class="text-[10px] font-black text-warning uppercase tracking-wider flex items-center gap-1">
                                            <Split :size="12"/> 2 Pasos
                                        </span>
                                    </div>
                                    <div class="pl-9">
                                        <p class="text-xs text-muted-foreground leading-relaxed">
                                            Paga solo los costos de envío y el <strong class="text-foreground">30% de los productos</strong> hoy (Bs {{ advanceAmount.toFixed(2) }}). 
                                        </p>
                                        <div v-if="paymentType === 'partial'" class="mt-3 p-3 bg-warning/10 rounded-xl border border-warning/20 flex gap-2 animate-in fade-in">
                                            <AlertTriangle class="text-warning shrink-0" :size="16" />
                                            <p class="text-[10px] font-bold text-warning-foreground">
                                                IMPORTANTE: Deberás subir un SEGUNDO comprobante con el saldo (Bs {{ balanceAmount.toFixed(2) }}) cuando el conductor esté en camino. La entrega no se realizará hasta que el administrador valide el segundo pago.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </section>
                </div>

                <div class="lg:col-span-1">
                    <div class="bg-card p-8 rounded-3xl border border-border shadow-2xl sticky top-28 transition-all">
                        <h3 class="font-black text-foreground text-lg mb-8 flex items-center gap-2 italic uppercase">
                            <CreditCard :size="20" class="text-primary"/> Total a Pagar
                        </h3>
                        
                        <div class="space-y-4 mb-6">
                            <div class="flex justify-between text-sm">
                                <span class="text-muted-foreground font-medium">Subtotal Productos</span>
                                <span class="font-mono font-bold">Bs {{ subtotalProducts.toFixed(2) }}</span>
                            </div>
                            
                            <div class="flex justify-between items-start">
                                <div class="flex flex-col">
                                    <span class="text-sm text-muted-foreground font-medium">Servicio Digital</span>
                                    <span v-if="currentLogistics?.savings > 0" class="text-[10px] font-black text-success uppercase flex items-center gap-1">
                                        <Sparkles :size="10"/> ¡Ahorro Socio!
                                    </span>
                                </div>
                                <div class="flex flex-col items-end">
                                    <span v-if="currentLogistics?.savings > 0" class="text-xs text-muted-foreground/50 line-through font-mono">
                                        Bs {{ currentLogistics.original_service_fee?.toFixed(2) }}
                                    </span>
                                    <span class="font-mono font-bold text-sm">Bs {{ currentLogistics?.service_fee?.toFixed(2) || '0.00' }}</span>
                                </div>
                            </div>

                            <div class="flex justify-between text-sm">
                                <span class="text-muted-foreground font-medium">Envío</span>
                                <span class="font-bold text-success text-xs" v-if="deliveryType === 'pickup'">GRATIS</span>
                                <span class="font-mono font-bold" v-else>Bs {{ currentLogistics?.delivery_fee?.toFixed(2) || '0.00' }}</span>
                            </div>

                            <div class="h-px bg-border my-2"></div>
                            
                            <div v-if="paymentType === 'total'" class="flex justify-between items-end">
                                <span class="font-black text-foreground text-lg">TOTAL AHORA</span>
                                <span class="font-black text-4xl text-primary tracking-tighter shadow-glow">
                                    Bs {{ finalTotal.toFixed(2) }}
                                </span>
                            </div>

                            <div v-else class="space-y-3">
                                <div class="flex justify-between items-end opacity-50">
                                    <span class="font-bold text-foreground text-xs uppercase tracking-wider">Total Orden</span>
                                    <span class="font-black text-lg text-foreground tracking-tighter">Bs {{ finalTotal.toFixed(2) }}</span>
                                </div>
                                <div class="flex justify-between items-end border-l-4 border-warning pl-3">
                                    <span class="font-black text-warning text-sm uppercase">Anticipo Hoy</span>
                                    <span class="font-black text-3xl text-warning tracking-tighter">Bs {{ advanceAmount.toFixed(2) }}</span>
                                </div>
                                <div class="flex justify-between items-end border-l-4 border-muted pl-3">
                                    <span class="font-bold text-muted-foreground text-[10px] uppercase">Saldo en Ruta</span>
                                    <span class="font-bold text-sm text-muted-foreground">Bs {{ balanceAmount.toFixed(2) }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="bg-primary/5 border border-primary/10 p-4 rounded-2xl flex gap-3 items-start mb-8">
                            <CheckCircle2 class="w-5 h-5 text-primary shrink-0" />
                            <p class="text-[11px] text-muted-foreground leading-snug">
                                Al confirmar, reservaremos tu stock por <strong class="text-foreground">10 minutos</strong>. Sube tu comprobante en la siguiente pantalla.
                            </p>
                        </div>

                        <button @click="submit" :disabled="form.processing || (deliveryType === 'delivery' && !form.address_id)" 
                            class="w-full bg-primary hover:bg-primary/90 text-primary-foreground font-black py-5 rounded-2xl shadow-xl shadow-primary/20 flex items-center justify-center gap-2 transition-all active:scale-[0.95] disabled:opacity-50 disabled:grayscale uppercase">
                            <span v-if="form.processing" class="flex items-center gap-2"><Loader2 class="animate-spin" :size="20"/> Reservando...</span>
                            <span v-else class="flex items-center gap-2">Pagar Bs {{ advanceAmount.toFixed(2) }} <ArrowRight :size="20"/></span>
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </ShopLayout>
</template>

<style scoped>
.shadow-glow { text-shadow: 0 0 15px rgba(var(--primary-rgb), 0.3); }
</style>