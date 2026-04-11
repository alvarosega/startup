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
                        <div v-for="item in cartItems" :key="item.id" class="relative group">
                            <div class="product-card p-4 md:p-5 flex gap-6 items-center" 
                                 :class="{'opacity-50': processingItem === item.id}">
                                
                                <div class="w-24 h-24 shrink-0 rounded-2xl sku-stage-bg p-2">
                                    <img :src="item.image" class="w-full h-full object-contain drop-shadow-hardware">
                                </div>

                                <div class="flex-1 min-w-0">
                                    <div class="flex justify-between items-start">
                                        <h3 class="text-sm md:text-base font-black uppercase leading-tight truncate pr-4">
                                            {{ item.name }}
                                        </h3>
                                        <button @click="removeItem(item.id)" class="text-foreground/20 hover:text-destructive transition-colors p-1">
                                            <Trash2 :size="16" />
                                        </button>
                                    </div>

                                    <div class="mt-4 flex items-center justify-between">
                                        <div class="flex items-center bg-foreground/5 rounded-xl p-1 border border-border/10">
                                            <button @click="updateQuantity(item, item.quantity - 1)" 
                                                    class="w-8 h-8 flex items-center justify-center rounded-lg bg-background shadow-sm hover:scale-105 active:scale-90 transition-all">
                                                <Minus :size="14" stroke-width="3"/>
                                            </button>
                                            <span class="w-10 text-center font-mono font-black text-xs">{{ item.quantity }}</span>
                                            <button @click="updateQuantity(item, item.quantity + 1)" 
                                                    class="w-8 h-8 flex items-center justify-center rounded-lg bg-background shadow-sm hover:scale-105 active:scale-90 transition-all">
                                                <Plus :size="14" stroke-width="3"/>
                                            </button>
                                        </div>
                                        
                                        <div class="text-right">
                                            <span class="block text-[10px] font-mono font-black text-foreground/30 uppercase tracking-tighter">Subtotal</span>
                                            <span class="text-xl font-mono font-black tracking-tighter">Bs. {{ item.subtotal.toFixed(2) }}</span>
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
/* LIENZO MAESTRO */
.du-cyber-canvas { background-color: hsl(var(--background)); background-image: linear-gradient(to bottom, hsl(var(--background)) 0%, hsl(var(--background)) 40%, transparent 100%), linear-gradient(to right, #0ed2da, #5f29c7); background-attachment: fixed; }
.du-cyber-canvas::before { content: ""; position: absolute; inset: 0; z-index: 0; background-image: linear-gradient(90deg, hsl(var(--border) / 0.4) 1px, transparent 1px); background-size: 50px 100%; pointer-events: none; mask-image: linear-gradient(to bottom, rgba(0, 0, 0, 1) 0%, rgba(0, 0, 0, 0) 70%); -webkit-mask-image: linear-gradient(to bottom, rgba(0, 0, 0, 1) 0%, rgba(0, 0, 0, 0) 70%); }
.dark .du-cyber-canvas { background-image: linear-gradient(to bottom, hsl(var(--background)) 0%, hsl(var(--background)) 50%, transparent 100%), linear-gradient(to right, #0ed2da33, #5f29c733); }

/* CHASIS DE CRISTAL (40px Blur) */
.glass-chassis { background-color: hsl(var(--background) / 0.6); backdrop-filter: blur(40px) saturate(200%); -webkit-backdrop-filter: blur(40px) saturate(200%); border: 1px solid hsl(var(--border) / 0.4); border-radius: 2.5rem; }
.dark .glass-chassis { background-color: hsl(var(--background) / 0.8); border-color: hsl(var(--border) / 0.3); }

/* TITANIUM MATTE (Sidebar) */
.glass-titanium { background: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0.05) 100%); backdrop-filter: blur(25px) saturate(180%); -webkit-backdrop-filter: blur(25px) saturate(180%); border: 1px solid hsl(var(--border)/0.3); }
.dark .glass-titanium { background: linear-gradient(135deg, rgba(10, 10, 10, 0.7) 0%, rgba(10, 10, 10, 0.4) 100%); border-color: rgba(255, 255, 255, 0.08); }

/* BANDEJA DE HARDWARE */
.sku-stage-bg { background-image: linear-gradient(135deg, hsl(var(--primary-50)) 0%, hsl(var(--primary)) 46%, hsl(var(--accent)) 100%); box-shadow: rgba(0, 0, 0, 0.1) 0px -15px 20px 0px inset, rgba(0, 0, 0, 0.05) 0px -30px 25px 0px inset, 0 15px 25px -10px rgba(0, 0, 0, 0.2); border: 1px solid rgba(255, 255, 255, 0.4); }
.dark .sku-stage-bg { background-image: linear-gradient(135deg, #1a1a1a 0%, hsl(var(--primary)) 46%, #000000 100%); box-shadow: rgba(0, 0, 0, 0.4) 0px -15px 20px 0px inset, 0 15px 25px -10px rgba(0, 0, 0, 0.6); border-color: rgba(255, 255, 255, 0.05); }
.drop-shadow-hardware { filter: drop-shadow(0 8px 8px rgba(0,0,0,0.25)); }

/* ENCABEZADO PRISMÁTICO */
.header-standard { display: flex; align-items: flex-end; gap: 1rem; }
.title-block-wrapper { position: relative; display: flex; align-items: center; gap: 0.85rem; flex-grow: 1; padding-bottom: 8px; }
.title-block-wrapper h1, .title-block-wrapper h2 { font-size: 11px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.4em; }
.title-block-wrapper::after { content: ''; position: absolute; bottom: 0; left: 0; width: 100%; height: 1px; background: linear-gradient(to right, #ff0000, #ff7f00, #ffff00, #00ff00, #0000ff, #4b0082, #8b00ff); opacity: 0.8; }

.pb-safe { padding-bottom: calc(env(safe-area-inset-bottom) + 1rem); }
</style>