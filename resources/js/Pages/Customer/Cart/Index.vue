<script setup>
import { ref, computed } from 'vue';
import { Head, router, usePage, Link } from '@inertiajs/vue3';
import ShopLayout from '@/Layouts/ShopLayout.vue';
import { 
    Trash2, Plus, Minus, ArrowRight, AlertTriangle, 
    ShoppingBag, Loader2, PackageOpen, CreditCard, 
    Tag, TrendingDown, Zap, Layers, Cpu 
} from 'lucide-vue-next';

const props = defineProps({
    cart: Object 
});

const page = usePage();
const user = computed(() => page.props.auth?.customer);
const processingItem = ref(null);
const isUpdating = ref(false);

const cartItems = computed(() => {
    if (!props.cart?.items) return [];
    return Array.isArray(props.cart.items) ? props.cart.items : props.cart.items.data;
});

const hasStockErrors = computed(() => {
    return cartItems.value.some(item => item.quantity > item.max_stock);
});

const updateQuantity = (item, newQty) => {
    if (newQty < 1 || newQty > item.max_stock || isUpdating.value) return;
    
    isUpdating.value = true;
    processingItem.value = item.id;
    
    router.patch(route('customer.cart.update', item.id), { quantity: newQty }, {
        preserveScroll: true,
        preserveState: true,
        only: ['cart', 'flash'], // Solo pedimos el carrito actualizado
        onFinish: () => {
            processingItem.value = null;
            isUpdating.value = false;
        },
    });
};
const removeItem = (id) => {
    // RESTAURADO: Funcionalidad original intacta
    if(confirm('¿Quitar elemento del carrito?')) {
        router.delete(route('customer.cart.remove', id), { preserveScroll: true });
    }
};

const proceedToCheckout = () => {
    if (isUpdating.value || hasStockErrors.value) return; // Impedir checkout si hay red en vuelo
    if (!user.value) { router.visit(route('customer.login')); return; }
    router.visit(route('customer.checkout.index'));
};
</script>

<template>
    <ShopLayout>
        <Head title="Radar de Despacho" />

        <div class="w-full pb-32 pt-4 relative z-10">
            <div class="max-w-7xl mx-auto px-4 lg:px-8">
                
                <div class="flex items-end justify-between mb-8 border-b border-border/10 pb-6">
                    <div class="header-standard mb-0">
                        <div class="title-block-wrapper">
                            <Cpu :size="18" class="text-primary animate-pulse" />
                            <h1 class="text-3xl font-black italic tracking-tighter uppercase">Radar de Despacho</h1>
                        </div>
                    </div>
                    <div v-if="props.cart !== undefined" class="text-right hidden sm:block">
                        <span class="font-mono text-xs text-foreground/40 tracking-[0.3em] uppercase">Status: Validando Hardware</span>
                    </div>
                </div>

                <div v-if="props.cart === undefined" class="grid grid-cols-1 lg:grid-cols-12 gap-12">
                    <div class="lg:col-span-8 space-y-4">
                        <div v-for="i in 3" :key="i" class="h-32 w-full skeleton rounded-3xl"></div>
                    </div>
                    <div class="lg:col-span-4 h-[400px] skeleton rounded-3xl"></div>
                </div>

                <div v-else-if="cartItems.length === 0" class="py-32 flex flex-col items-center text-center">
                    <div class="w-20 h-20 rounded-full bg-foreground/5 flex items-center justify-center mb-6">
                        <PackageOpen :size="40" class="text-foreground/20" stroke-width="1"/>
                    </div>
                    <h2 class="text-2xl font-black uppercase tracking-tighter italic mb-2">Bandeja Vacía</h2>
                    <p class="text-xs font-bold text-foreground/40 uppercase tracking-widest mb-8 max-w-xs">No hay hardware en tu zona de despacho actual.</p>
                    <Link :href="route('customer.index')" class="btn-primary px-10 h-14 !rounded-2xl text-xs">
                        Explorar Catálogo
                    </Link>
                </div>

                <div v-else class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-start">
                    <div class="lg:col-span-8 space-y-4">
                        <div v-for="item in cartItems" :key="item.id" 
                            class="product-card p-4 flex flex-col md:flex-row gap-6 items-center manifest-row"
                            :class="{ 'is-processing': processingItem === item.id }">
                            
                            <div v-if="item.quantity > item.max_stock" 
                                class="absolute top-0 left-0 w-full bg-destructive text-white text-xs font-black uppercase tracking-widest px-4 py-1.5 flex items-center justify-center gap-2 z-20">
                                <AlertTriangle :size="14" /> Stock Insuficiente (Máximo: {{ item.max_stock }})
                            </div>

                            <div class="w-28 h-28 shrink-0 rounded-2xl sku-stage-bg p-3">
                                <img :src="item.image" class="w-full h-full object-contain drop-shadow-hardware group-hover:scale-110 transition-transform">
                            </div>

                            <div class="flex-1 w-full">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <span class="text-[10px] font-black text-primary uppercase tracking-[0.3em] mb-1 block">{{ item.brand_name }}</span>
                                        <h3 class="text-sm md:text-base font-black uppercase leading-tight tracking-tighter text-foreground">{{ item.name }}</h3>
                                    </div>
                                    <button @click="removeItem(item.id)" class="p-2 text-foreground/20 hover:text-destructive transition-colors">
                                        <Trash2 :size="18" />
                                    </button>
                                </div>

                                <div class="mt-6 flex flex-wrap items-end justify-between gap-4">
                                    <div class="flex items-center bg-foreground/5 rounded-xl p-1 border border-border/10">
                                        <button @click="updateQuantity(item, item.quantity - 1)" 
                                                :disabled="item.quantity <= 1"
                                                class="w-9 h-9 flex items-center justify-center rounded-lg bg-background shadow-sm active:scale-90 disabled:opacity-20 transition-all">
                                            <Minus :size="16" stroke-width="3"/>
                                        </button>
                                        <span class="w-12 text-center font-mono font-black text-sm">{{ item.quantity }}</span>
                                        <button @click="updateQuantity(item, item.quantity + 1)" 
                                                :disabled="item.quantity >= item.max_stock"
                                                class="w-9 h-9 flex items-center justify-center rounded-lg bg-background shadow-sm active:scale-90 disabled:opacity-20 transition-all">
                                            <Plus :size="16" stroke-width="3"/>
                                        </button>
                                    </div>

                                    <div class="text-right">
                                        <div v-if="item.line_savings > 0" class="text-emerald-500 font-mono text-[10px] font-black uppercase tracking-widest mb-1">
                                            Ahorro: Bs. {{ item.line_savings.toFixed(2) }}
                                        </div>
                                        <div class="flex items-baseline justify-end gap-1">
                                            <span class="text-xs font-black text-foreground/40 uppercase">Bs.</span>
                                            <span class="text-2xl font-mono font-black tracking-tighter">{{ item.subtotal.toFixed(2) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <aside class="lg:col-span-4 sticky top-28">
                        <div class="glass-titanium !rounded-3xl p-8 border border-primary/20 shadow-f1-glow">
                            <h2 class="text-xs font-black uppercase tracking-[0.3em] text-primary mb-8 flex items-center gap-2">
                                <CreditCard :size="14" /> Liquidación Final
                            </h2>

                            <div class="space-y-4 font-mono text-xs font-bold uppercase tracking-widest border-b border-border/10 pb-6 mb-6">
                                <div class="flex justify-between text-foreground/50">
                                    <span>Valor Crudo</span>
                                    <span>Bs. {{ (props.cart.total_price + (props.cart.total_savings || 0)).toFixed(2) }}</span>
                                </div>
                                <div v-if="props.cart.total_savings > 0" class="flex justify-between text-emerald-500">
                                    <span class="flex items-center gap-1"><TrendingDown :size="12"/> Ahorro Pack</span>
                                    <span>- Bs. {{ props.cart.total_savings.toFixed(2) }}</span>
                                </div>
                            </div>

                            <div class="flex justify-between items-end mb-8">
                                <span class="text-xs font-black uppercase tracking-widest text-foreground/40">Total</span>
                                <div class="text-right">
                                    <span class="text-4xl font-mono font-black tracking-tighter italic">
                                        <span class="text-base not-italic text-primary mr-1">Bs</span>{{ props.cart.total_price.toFixed(2) }}
                                    </span>
                                </div>
                            </div>

                            <button @click="proceedToCheckout" 
                                    :disabled="hasStockErrors || isUpdating"
                                    class="btn-primary w-full h-16 !rounded-2xl text-xs font-black uppercase tracking-[0.2em] shadow-apple-soft group">
                                <span class="group-hover:translate-x-1 transition-transform flex items-center justify-center gap-3">
                                    {{ user ? 'Ejecutar Pago' : 'Autenticar Socio' }} <ArrowRight :size="18" stroke-width="3" />
                                </span>
                            </button>
                        </div>
                    </aside>
                </div>
            </div>
        </div>
    </ShopLayout>
</template>
<style scoped>
.manifest-row {
    @apply relative overflow-hidden transition-all duration-500;
}
/* Efecto de borde técnico para el item procesándose */
.is-processing {
    @apply opacity-40 grayscale pointer-events-none scale-[0.98];
}
</style>