<script setup>
import { ref, computed, watch } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import ShopLayout from '@/Layouts/ShopLayout.vue';
import { 
    MapPin, ShieldCheck, ArrowRight, AlertTriangle, 
    Store, Truck, CreditCard, PackageCheck 
} from 'lucide-vue-next';

const props = defineProps({
    cart: Object,
    addresses: Array,
    default_address_id: [String, Number] 
});

const deliveryType = ref('pickup'); 

const form = useForm({
    delivery_type: 'pickup',
    address_id: null
});

const validAddresses = computed(() => {
    return props.addresses.filter(addr => addr.branch_id === props.cart.branch_id);
});

// Inicialización inteligente
if (props.default_address_id && validAddresses.value.find(a => a.id === props.default_address_id)) {
    form.address_id = props.default_address_id;
    deliveryType.value = 'delivery'; // Si hay default, pre-seleccionar delivery
} else if (validAddresses.value.length > 0) {
    form.address_id = validAddresses.value[0].id;
}

watch(deliveryType, (newVal) => {
    form.delivery_type = newVal;
    if (newVal === 'delivery' && !form.address_id && validAddresses.value.length > 0) {
        form.address_id = validAddresses.value[0].id;
    }
    if (newVal === 'pickup') {
        form.address_id = null;
    }
});

const submit = () => {
    form.delivery_type = deliveryType.value;
    if (form.delivery_type === 'delivery' && !form.address_id) {
        return alert('Selecciona una dirección para el envío.');
    }
    form.post(route('checkout.store'));
};
</script>

<template>
    <ShopLayout>
        <div class="container mx-auto px-4 py-8 pb-48 lg:pb-12 min-h-full relative">
            
            <div class="mb-8">
                <h1 class="font-display font-black text-3xl text-foreground uppercase tracking-tight italic">
                    Confirmar <span class="text-primary">Pedido</span>
                </h1>
                <p class="text-xs text-muted-foreground mt-1 font-medium">Revisa los detalles antes de pagar.</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
                
                <div class="lg:col-span-2 space-y-6">
                    
                    <section class="bg-card rounded-2xl border border-border p-5 shadow-sm">
                        <h2 class="font-bold text-foreground text-sm uppercase tracking-wider mb-4 flex items-center gap-2">
                            <PackageCheck :size="18" class="text-primary"/> Método de Entrega
                        </h2>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <label class="cursor-pointer relative group">
                                <input type="radio" v-model="deliveryType" value="pickup" class="peer hidden">
                                <div class="h-full border-2 rounded-2xl p-4 flex flex-col items-center justify-center gap-3 transition-all duration-200 
                                            peer-checked:border-primary peer-checked:bg-primary/5 peer-checked:shadow-md
                                            border-border bg-background hover:border-primary/30">
                                    <div class="p-3 rounded-full bg-muted/50 peer-checked:bg-primary peer-checked:text-primary-foreground transition-colors text-muted-foreground">
                                        <Store :size="24" />
                                    </div>
                                    <div class="text-center">
                                        <span class="block font-bold text-sm text-foreground">Recojo en Tienda</span>
                                        <span class="inline-block mt-1 text-[10px] font-bold text-success bg-success/10 px-2 py-0.5 rounded-full border border-success/20">Gratis</span>
                                    </div>
                                </div>
                            </label>

                            <label class="cursor-pointer relative group">
                                <input type="radio" v-model="deliveryType" value="delivery" class="peer hidden">
                                <div class="h-full border-2 rounded-2xl p-4 flex flex-col items-center justify-center gap-3 transition-all duration-200 
                                            peer-checked:border-primary peer-checked:bg-primary/5 peer-checked:shadow-md
                                            border-border bg-background hover:border-primary/30">
                                    <div class="p-3 rounded-full bg-muted/50 peer-checked:bg-primary peer-checked:text-primary-foreground transition-colors text-muted-foreground">
                                        <Truck :size="24" />
                                    </div>
                                    <div class="text-center">
                                        <span class="block font-bold text-sm text-foreground">Delivery</span>
                                        <span class="block mt-1 text-[10px] text-muted-foreground">A tu ubicación</span>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </section>

                    <section class="bg-card rounded-2xl border border-border p-5 shadow-sm animate-in fade-in slide-in-from-top-2 duration-300">
                        
                        <div v-if="deliveryType === 'pickup'" class="space-y-3">
                            <h2 class="font-bold text-foreground text-sm uppercase tracking-wider flex items-center gap-2">
                                <MapPin :size="18" class="text-primary" /> Punto de Recojo
                            </h2>
                            <div class="bg-muted/20 p-4 rounded-xl border border-border flex gap-3 items-start">
                                <div class="p-2 bg-background rounded-lg border border-border shrink-0">
                                    <Store :size="20" class="text-foreground"/>
                                </div>
                                <div>
                                    <p class="font-black text-foreground text-sm">{{ cart.branch_name }}</p>
                                    <p class="text-xs text-muted-foreground mt-1 leading-relaxed">
                                        Tu pedido estará reservado por <strong>24 horas</strong>. Presenta tu comprobante QR al llegar.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div v-else>
                            <div class="flex justify-between items-center mb-4">
                                <h2 class="font-bold text-foreground text-sm uppercase tracking-wider flex items-center gap-2">
                                    <MapPin :size="18" class="text-primary" /> Dirección de Entrega
                                </h2>
                                <Link :href="route('addresses.create')" class="text-xs font-bold text-primary hover:underline bg-primary/10 px-3 py-1.5 rounded-lg transition-colors">
                                    + Nueva
                                </Link>
                            </div>

                            <div v-if="validAddresses.length === 0" class="text-center py-8 bg-warning/5 rounded-xl border border-warning/20">
                                <AlertTriangle class="mx-auto text-warning mb-2" :size="24"/>
                                <p class="text-sm text-warning font-bold">Sin cobertura guardada.</p>
                                <p class="text-xs text-muted-foreground mt-1 max-w-xs mx-auto">
                                    No tienes direcciones registradas para la zona de <strong>{{ cart.branch_name }}</strong>.
                                </p>
                            </div>

                            <div v-else class="space-y-3">
                                <label v-for="addr in validAddresses" :key="addr.id" 
                                    class="flex items-start gap-3 p-4 border-2 rounded-xl cursor-pointer transition-all hover:border-primary/30 group bg-background"
                                    :class="form.address_id === addr.id ? 'border-primary bg-primary/5 shadow-md' : 'border-border'">
                                    
                                    <div class="mt-0.5 relative">
                                        <input type="radio" :value="addr.id" v-model="form.address_id" class="peer appearance-none w-5 h-5 rounded-full border-2 border-muted-foreground checked:border-primary checked:bg-primary transition-all">
                                        <div class="absolute inset-0 m-auto w-2 h-2 rounded-full bg-white scale-0 peer-checked:scale-100 transition-transform"></div>
                                    </div>
                                    
                                    <div class="flex-1">
                                        <div class="flex justify-between items-start">
                                            <span class="block font-black text-sm text-foreground group-hover:text-primary transition-colors">{{ addr.alias }}</span>
                                            <span class="text-[9px] font-bold bg-success/10 text-success px-1.5 py-0.5 rounded uppercase tracking-wider">Cobertura OK</span>
                                        </div>
                                        <span class="block text-xs text-muted-foreground mt-0.5 line-clamp-1">{{ addr.address }}</span>
                                        <span class="block text-[10px] text-muted-foreground/60 mt-1 italic">{{ addr.details }}</span>
                                    </div>
                                </label>
                            </div>
                            <p v-if="form.errors.address_id" class="text-destructive text-xs mt-3 font-bold bg-destructive/10 p-2 rounded-lg text-center">
                                {{ form.errors.address_id }}
                            </p>
                        </div>
                    </section>

                    <section class="bg-card rounded-2xl border border-border p-5 shadow-sm">
                        <h2 class="font-bold text-foreground text-sm uppercase tracking-wider mb-4">
                            Resumen de Productos <span class="text-muted-foreground ml-1">({{ cart.items.length }})</span>
                        </h2>
                        <div class="divide-y divide-border/50">
                            <div v-for="item in cart.items" :key="item.name" class="py-3 flex justify-between items-center">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg bg-background border border-border flex items-center justify-center text-xs font-bold text-muted-foreground">
                                        x{{ item.quantity }}
                                    </div>
                                    <div>
                                        <p class="font-bold text-sm text-foreground line-clamp-1">{{ item.name }}</p>
                                        <p class="text-[10px] text-muted-foreground">Unit: Bs {{ parseFloat(item.unit_price).toFixed(2) }}</p>
                                    </div>
                                </div>
                                <p class="font-bold text-sm text-foreground">Bs {{ item.subtotal.toFixed(2) }}</p>
                            </div>
                        </div>
                    </section>
                </div>

                <div class="hidden lg:block lg:col-span-1">
                    <div class="bg-card p-6 rounded-2xl border border-border shadow-xl sticky top-24">
                        <h3 class="font-black text-foreground text-lg mb-6 flex items-center gap-2">
                            <CreditCard :size="20" class="text-primary"/> Total a Pagar
                        </h3>
                        
                        <div class="space-y-3 mb-6">
                            <div class="flex justify-between text-sm text-muted-foreground">
                                <span>Subtotal Productos</span>
                                <span class="font-mono text-foreground font-bold">Bs {{ cart.total.toFixed(2) }}</span>
                            </div>
                            <div class="flex justify-between text-sm text-muted-foreground">
                                <span>Costo de Envío</span>
                                <span class="font-bold text-success" v-if="deliveryType === 'pickup'">GRATIS</span>
                                <span class="font-bold text-primary" v-else>Por Calcular</span> 
                            </div>
                            <div class="h-px bg-border my-2"></div>
                            <div class="flex justify-between items-end">
                                <span class="font-black text-foreground text-lg">TOTAL</span>
                                <span class="font-black text-3xl text-primary tracking-tighter shadow-glow">
                                    Bs {{ cart.total.toFixed(2) }}
                                </span>
                            </div>
                        </div>

                        <div class="bg-primary/5 border border-primary/10 p-3 rounded-xl flex gap-3 items-start mb-6">
                            <ShieldCheck class="w-5 h-5 text-primary shrink-0 mt-0.5" />
                            <p class="text-xs text-foreground/80 leading-relaxed">
                                Al confirmar, tu stock quedará <strong class="text-primary">reservado por 5 minutos</strong> para el pago.
                            </p>
                        </div>

                        <button @click="submit" :disabled="form.processing" 
                            class="w-full bg-primary hover:bg-primary/90 text-primary-foreground font-black py-4 rounded-xl shadow-lg shadow-primary/20 flex items-center justify-center gap-2 transition-all active:scale-[0.98] disabled:opacity-50 disabled:grayscale">
                            <span v-if="form.processing" class="flex items-center gap-2"><Loader2 class="animate-spin" :size="18"/> Procesando...</span>
                            <span v-else class="flex items-center gap-2">Confirmar y Pagar <ArrowRight :size="20"/></span>
                        </button>
                        
                        <div v-if="$page.props.errors.error" class="mt-4 p-3 bg-destructive/10 text-destructive text-xs font-bold rounded-xl border border-destructive/20 flex items-center gap-2">
                            <AlertTriangle :size="16"/> {{ $page.props.errors.error }}
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div class="lg:hidden fixed bottom-[60px] left-0 right-0 z-30 px-4 pb-2 pt-4 bg-gradient-to-t from-background via-background/95 to-transparent">
            <div class="bg-card/90 backdrop-blur-xl border border-border p-4 rounded-2xl shadow-[0_0_30px_-5px_rgba(0,0,0,0.3)] flex flex-col gap-4 ring-1 ring-white/5">
                
                <div class="flex justify-between items-end border-b border-border/50 pb-3">
                    <div class="flex flex-col">
                        <span class="text-[10px] text-muted-foreground font-bold uppercase tracking-wider">Total Final</span>
                        <span class="text-2xl font-black text-foreground tracking-tighter">
                            Bs {{ cart.total.toFixed(2) }}
                        </span>
                    </div>
                    <div class="text-right">
                        <span class="text-[10px] block text-muted-foreground uppercase font-bold">Envío</span>
                        <span class="text-xs font-bold text-success bg-success/10 px-2 py-0.5 rounded" v-if="deliveryType === 'pickup'">GRATIS</span>
                        <span class="text-xs font-bold text-primary" v-else>+ Calc</span>
                    </div>
                </div>

                <button @click="submit" 
                        :disabled="form.processing"
                        class="w-full bg-primary hover:bg-primary/90 text-primary-foreground font-black py-3.5 rounded-xl shadow-lg shadow-primary/20 flex items-center justify-center gap-2 text-sm uppercase tracking-wide disabled:opacity-50 disabled:grayscale transition-all active:scale-[0.98]">
                    <span v-if="form.processing">Procesando...</span>
                    <span v-else>Confirmar Pedido <ArrowRight :size="18" stroke-width="3"/></span>
                </button>
            </div>
        </div>

    </ShopLayout>
</template>

<style scoped>
.shadow-glow {
    text-shadow: 0 0 20px rgba(var(--primary-rgb), 0.5);
}
</style>