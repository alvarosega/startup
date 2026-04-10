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
    
    isUpdating.value = true; // Bloqueo total de la UI
    processingItem.value = item.id;
    
    router.patch(route('customer.cart.update', item.id), { quantity: newQty }, {
        preserveScroll: true,
        only: ['cart'], // REGLA QUERY LAW: Solo recargar la prop del carrito
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
        <Head title="Tu Carrito" />

        <div class="du-cyber-canvas min-h-[100svh] pb-[140px] lg:pb-32 pt-8 relative z-10 transition-colors duration-500">
            <div class="max-w-7xl mx-auto px-4 lg:px-8">
                
                <div class="flex items-end justify-between mb-10 border-b border-border/20 pb-4">
                    <div class="header-standard mb-0">
                        <div class="title-block-wrapper">
                            <ShoppingBag :size="18" class="text-black dark:text-white fill-current" />
                            <h1 class="text-2xl md:text-3xl text-black dark:text-white">Tu Pedido</h1>
                        </div>
                    </div>
                    <div class="text-right pb-2" v-if="cart">
                        <p class="font-mono text-xs md:text-sm text-foreground/60 font-black tracking-widest">
                            [{{ cart.total_items }}] UNIDADES
                        </p>
                        <p class="font-mono text-[9px] text-foreground/40 uppercase tracking-[0.2em] mt-1">
                            SYS_ID: {{ cart.id?.substring(0,8) || 'DRAFT' }}
                        </p>
                    </div>
                </div>

                <div v-if="cartItems.length > 0" class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-start">
                    
                    <div class="lg:col-span-8 space-y-6">
                        <div v-for="item in cartItems" :key="item.id" class="flex flex-col gap-2">
                            
                            <div class="glass-chassis p-4 md:p-6 flex flex-col sm:flex-row gap-5 md:gap-8 transition-all duration-500 relative overflow-hidden group"
                                 :class="[
                                     item.quantity > item.max_stock ? 'border-destructive/50 shadow-[0_0_15px_rgba(var(--destructive-rgb),0.1)]' : '',
                                     processingItem === item.id ? 'opacity-50 pointer-events-none scale-[0.98]' : ''
                                 ]">
                                
                                <div v-if="item.quantity > item.max_stock" class="absolute top-0 left-0 w-full bg-destructive/10 border-b border-destructive/20 text-destructive text-[9px] font-black uppercase tracking-[0.2em] px-4 py-2 flex items-center justify-center gap-2">
                                    <AlertTriangle :size="12" /> Límite excedido (Max: {{ item.max_stock }})
                                </div>

                                <div class="w-full sm:w-32 h-32 shrink-0 rounded-2xl sku-stage-bg p-3 flex items-center justify-center relative mt-6 sm:mt-0"
                                     :class="{'mt-10 sm:mt-6': item.quantity > item.max_stock}">
                                    <img :src="item.image" class="w-full h-full object-contain group-hover:scale-110 transition-transform duration-500 drop-shadow-hardware">
                                </div>
                                
                                <div class="flex-1 flex flex-col justify-between pt-1">
                                    <div>
                                        <div class="flex justify-between items-start gap-4">
                                            <div>
                                                <h3 class="font-black text-sm md:text-base uppercase leading-tight text-foreground tracking-tight">
                                                    {{ item.name }}
                                                </h3>
                                                
                                                <div v-if="item.is_bundle && item.components" class="mt-3 flex flex-col gap-1.5 border-l-2 border-border/40 pl-3">
                                                    <p v-for="(comp, i) in item.components" :key="i" class="text-[9px] text-foreground/60 font-black uppercase tracking-widest flex items-center gap-2">
                                                        <span class="text-primary">{{ comp.qty }}x</span> {{ comp.name }}
                                                    </p>
                                                </div>
                                            </div>

                                            <button @click="removeItem(item.id)" class="w-8 h-8 rounded-lg bg-foreground/5 flex items-center justify-center text-foreground/40 hover:bg-destructive/10 hover:text-destructive transition-colors active:scale-90 shrink-0">
                                                <Loader2 v-if="processingItem === item.id" :size="14" class="animate-spin" />
                                                <Trash2 v-else :size="14" stroke-width="2.5"/>
                                            </button>
                                        </div>

                                        <div class="mt-3 flex items-center gap-2 flex-wrap">
                                            <span v-if="item.price_label !== 'REGULAR'" 
                                                  class="text-[8px] font-black px-2 py-1 rounded-md flex items-center gap-1 uppercase tracking-[0.2em]"
                                                  :class="{
                                                      'bg-warning/10 text-warning border border-warning/20': item.price_label === 'OFFER',
                                                      'bg-purple-500/10 text-purple-500 border border-purple-500/20': item.is_bundle,
                                                      'bg-primary/10 text-primary border border-primary/20': !item.is_bundle && item.price_label !== 'OFFER'
                                                  }">
                                                <Zap :size="10" v-if="item.price_label === 'OFFER'"/>
                                                <Layers :size="10" v-else-if="item.is_bundle"/>
                                                <Tag :size="10" v-else/>
                                                {{ item.price_label }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="flex items-end justify-between mt-6 pt-4 border-t border-border/20">
                                        <div class="flex items-center bg-foreground/5 border border-border/30 rounded-xl p-1 shadow-inner">
                                            <button @click="updateQuantity(item, item.quantity - 1)" :disabled="processingItem === item.id || item.quantity <= 1" class="w-8 h-8 flex items-center justify-center rounded-lg bg-background text-foreground hover:opacity-80 disabled:opacity-30 transition-all shadow-sm">
                                                <Minus :size="14" stroke-width="3"/>
                                            </button>
                                            <span class="w-12 text-center font-mono font-black text-sm text-foreground">
                                                {{ item.quantity }}
                                            </span>
                                            <button @click="updateQuantity(item, item.quantity + 1)" :disabled="processingItem === item.id || item.quantity >= item.max_stock" class="w-8 h-8 flex items-center justify-center rounded-lg bg-background text-foreground hover:opacity-80 disabled:opacity-30 transition-all shadow-sm">
                                                <Plus :size="14" stroke-width="3"/>
                                            </button>
                                        </div>
                                        
                                        <div class="text-right">
                                            <span v-if="item.line_savings > 0" class="block text-[9px] font-mono font-black text-emerald-500 uppercase tracking-widest mb-1">
                                                AHORRO: Bs. {{ item.line_savings.toFixed(2) }}
                                            </span>
                                            <div class="flex items-baseline justify-end gap-1 font-mono">
                                                <span class="text-[10px] font-bold text-foreground/40 uppercase">Bs.</span>
                                                <span class="text-xl md:text-2xl font-black text-foreground tracking-tighter">{{ item.subtotal.toFixed(2) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div v-if="!item.is_bundle && item.upsell" class="bg-foreground/5 border border-border/20 border-l-2 border-l-primary p-4 rounded-[1.5rem] flex items-start gap-4">
                                <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center text-primary shrink-0">
                                    <Cpu :size="14" />
                                </div>
                                <div>
                                    <p class="text-[9px] font-black uppercase tracking-[0.2em] text-primary mb-1">Alerta de Eficiencia</p>
                                    <p class="text-xs font-bold text-foreground/70 uppercase tracking-tighter leading-snug">
                                        Adiciona <span class="text-foreground font-black">{{ item.upsell.needed_quantity }} uds</span> más a tu configuración y el costo unitario bajará a <span class="font-mono text-primary font-black">Bs. {{ item.upsell.potential_price.toFixed(2) }}</span>.
                                    </p>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div v-if="cartItems.length > 0" class="hidden lg:block lg:col-span-4">
                        <div class="glass-titanium rounded-[2.5rem] p-8 sticky top-28 shadow-2xl">
                            <div class="header-standard mb-8">
                                <div class="title-block-wrapper">
                                    <CreditCard :size="16" class="text-black dark:text-white" />
                                    <h2 class="text-black dark:text-white">Liquidación</h2>
                                </div>
                            </div>
                            
                            <div class="space-y-4 mb-8 text-xs font-bold text-foreground/60 uppercase tracking-widest font-mono">
                                <div class="flex justify-between items-center">
                                    <span>Valor Crudo</span>
                                    <span>Bs. {{ (cart.total_price + (cart.total_savings || 0)).toFixed(2) }}</span>
                                </div>
                                
                                <div v-if="cart.total_savings > 0" class="flex justify-between items-center text-emerald-500 pt-2 border-t border-border/20">
                                    <span class="flex items-center gap-2"><TrendingDown :size="14"/> Eficiencia</span>
                                    <span>- Bs. {{ cart.total_savings.toFixed(2) }}</span>
                                </div>
                            </div>

                            <div class="border-t border-border/30 pt-6 mb-8">
                                <div class="flex justify-between items-end">
                                    <span class="text-[10px] font-black uppercase tracking-[0.2em] text-foreground/50 mb-1">Total a Despachar</span>
                                    <span class="text-4xl font-mono font-black text-foreground leading-none tracking-tighter">
                                        <span class="text-lg text-foreground/40 mr-1">Bs.</span>{{ cart.total_price.toFixed(2) }}
                                    </span>
                                </div>
                            </div>

                            <button @click="proceedToCheckout" 
                                    :disabled="hasStockErrors || isUpdating" 
                                    class="w-full h-16 bg-foreground text-background rounded-2xl font-black uppercase text-xs tracking-[0.2em] shadow-2xl flex items-center justify-center gap-3 active:scale-95 transition-all disabled:opacity-30 disabled:cursor-not-allowed">
                                <Loader2 v-if="isUpdating" class="animate-spin" />
                                <template v-else>
                                    {{ user ? 'Ejecutar Pago' : 'Autenticar Socio' }} <ArrowRight stroke-width="3" :size="18"/>
                                </template>
                            </button>
                        </div>
                    </div>

                    <div v-if="cartItems.length === 0" class="text-center py-32 glass-chassis rounded-[3rem] mt-8 flex flex-col items-center">
                        <PackageOpen :size="64" class="text-foreground/20 mb-6" stroke-width="1"/>
                        <h2 class="text-2xl font-black uppercase tracking-tighter mb-3 italic">Bandeja Vacía</h2>
                        <p class="text-foreground/50 text-[10px] uppercase font-black tracking-[0.2em] mb-10 max-w-sm">No hay hardware en tu radar de despacho. Inicia una exploración de catálogo.</p>
                        <Link :href="route('customer.index')" class="h-14 px-8 bg-foreground text-background font-black text-[10px] uppercase tracking-widest rounded-2xl flex items-center justify-center gap-3 transition-transform active:scale-95 shadow-xl">
                            <ShoppingBag :size="16" stroke-width="2.5"/> Explorar Catálogo
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="cartItems.length > 0" class="lg:hidden fixed bottom-0 left-0 w-full z-[5000] pb-safe bg-background/90 backdrop-blur-2xl border-t border-border/20 shadow-[0_-15px_40px_rgba(0,0,0,0.15)] dark:shadow-[0_-15px_40px_rgba(0,0,0,0.5)]">
            <div class="px-6 py-4 flex items-center justify-between gap-4">
                <div class="flex flex-col">
                    <span class="text-[9px] font-black uppercase tracking-[0.2em] text-foreground/50 mb-0.5">Total a Despachar</span>
                    <span class="text-2xl font-mono font-black text-foreground leading-none tracking-tighter">
                        <span class="text-sm text-foreground/40 mr-1">Bs.</span>{{ cart.total_price.toFixed(2) }}
                    </span>
                </div>
                
                <button @click="proceedToCheckout" 
                        :disabled="hasStockErrors || isUpdating" 
                        class="flex-1 max-w-[180px] h-14 bg-foreground text-background rounded-2xl font-black uppercase text-[10px] tracking-[0.2em] shadow-xl flex items-center justify-center gap-2 active:scale-95 transition-all disabled:opacity-30">
                    <Loader2 v-if="isUpdating" class="animate-spin" :size="16" />
                    <template v-else>
                        Pagar <ArrowRight stroke-width="3" :size="14"/>
                    </template>
                </button>
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