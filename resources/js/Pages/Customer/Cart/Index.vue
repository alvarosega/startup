<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, router, usePage, Link } from '@inertiajs/vue3';
import ShopLayout from '@/Layouts/ShopLayout.vue';
import { Trash2, Plus, Minus, ArrowRight, AlertTriangle, ShoppingBag, Loader2, PackageOpen, CreditCard, Tag, TrendingDown, Zap } from 'lucide-vue-next';

const props = defineProps({
    cart: Object 
});

const page = usePage();
const user = computed(() => page.props.auth?.user);
const processingItem = ref(null);

const cartItems = computed(() => {
    if (!props.cart?.items) return [];
    return Array.isArray(props.cart.items) ? props.cart.items : props.cart.items.data;
});

const hasStockErrors = computed(() => {
    return cartItems.value.some(item => item.quantity > item.max_stock);
});



const updateQuantity = (item, newQty) => {
    if (newQty < 1 || newQty > item.max_stock) return;
    processingItem.value = item.id;
    router.patch(route('customer.cart.update', item.id), { quantity: newQty }, {
        preserveScroll: true,
        onFinish: () => processingItem.value = null,
    });
};

const removeItem = (id) => {
    if(confirm('¿Quitar producto del carrito?')) {
        router.delete(route('customer.cart.remove', id), { preserveScroll: true });
    }
};

const proceedToCheckout = () => {
    if (!user.value) { router.visit(route('login')); return; }
    router.visit(route('customer.checkout.index'));
};
</script>

<template>
    <ShopLayout>
        <Head title="Tu Carrito" />

        <div class="container mx-auto px-4 py-8 max-w-6xl">
            <div class="flex items-end justify-between mb-8 border-b border-white/5 pb-4">
                <div>
                    <h1 class="text-4xl font-black uppercase tracking-tighter flex items-center gap-3">
                        <ShoppingBag :size="32" class="text-primary" stroke-width="2.5"/> 
                        Tu <span class="text-primary">Pedido</span>
                    </h1>
                </div>
                <div class="text-right" v-if="cart">
                    <p class="font-mono text-sm text-foreground/50 font-bold">
                        {{ cart.total_items }} ARTÍCULOS
                    </p>
                    <p class="font-mono text-[10px] text-foreground/30 uppercase tracking-widest">
                        ID: {{ cart.id?.substring(0,8) }}
                    </p>
                </div>
            </div>

            <div v-if="cartItems.length > 0" class="grid lg:grid-cols-12 gap-8 items-start">
                
                <div class="lg:col-span-8 space-y-5">
                    <div v-for="item in cartItems" :key="item.id" 
                         class="bg-white/5 dark:bg-black/20 backdrop-blur-md border rounded-[24px] p-5 flex flex-col gap-4 transition-all relative overflow-hidden group"
                         :class="item.quantity > item.max_stock ? 'border-f1-red/50 bg-f1-red/5' : 'border-white/10'">
                        
                        <div v-if="item.quantity > item.max_stock" class="bg-f1-red text-white text-[10px] font-black uppercase tracking-widest px-3 py-1.5 absolute top-0 inset-x-0 text-center flex items-center justify-center gap-2">
                            <AlertTriangle :size="12" /> Stock insuficiente (Quedan {{ item.max_stock }})
                        </div>

                        <div class="flex gap-5 relative z-10" :class="{'mt-4': item.quantity > item.max_stock}">
                            <div class="w-28 h-28 shrink-0 bg-white/5 rounded-[20px] p-2 border border-white/5 flex items-center justify-center">
                                <img :src="item.image" class="w-full h-full object-contain drop-shadow-md">
                            </div>
                            
                            <div class="flex-1 flex flex-col justify-between">
                                <div>
                                    <div class="flex justify-between items-start gap-4">
                                        <h3 class="font-black text-sm uppercase leading-tight text-foreground/90">{{ item.name }}</h3>
                                        <button @click="removeItem(item.id)" class="text-foreground/30 hover:text-f1-red hover:bg-f1-red/10 p-2 rounded-full transition-colors active:scale-90">
                                            <Trash2 :size="18" stroke-width="2.5"/>
                                        </button>
                                    </div>

                                    <div class="mt-1 flex items-center gap-2 flex-wrap">
                                        <div class="flex items-baseline gap-1.5">
                                            <span v-if="item.list_price > item.unit_price" class="text-[11px] font-mono font-bold text-foreground/40 line-through">
                                                Bs. {{ item.list_price.toFixed(2) }}
                                            </span>
                                            <span class="text-lg font-mono font-black text-foreground">
                                                Bs. {{ item.unit_price.toFixed(2) }} <span class="text-[10px] text-foreground/50 tracking-widest font-sans">C/U</span>
                                            </span>
                                        </div>
                                        
                                        <span v-if="item.price_label !== 'REGULAR'" 
                                              class="text-[9px] font-black px-2 py-0.5 rounded-md flex items-center gap-1 uppercase tracking-widest"
                                              :class="item.price_label === 'OFFER' ? 'bg-warning/20 text-warning border border-warning/30' : 'bg-primary/20 text-primary border border-primary/30'">
                                            <Zap :size="10" v-if="item.price_label === 'OFFER'"/>
                                            <Tag :size="10" v-else/>
                                            {{ item.price_label }}
                                        </span>
                                    </div>
                                </div>

                                <div class="flex items-end justify-between mt-4">
                                    
                                    <div class="flex items-center bg-background/50 border border-white/10 rounded-xl p-1 shadow-inner">
                                        <button @click="updateQuantity(item, item.quantity - 1)" :disabled="processingItem === item.id || item.quantity <= 1" class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-white/10 disabled:opacity-30 transition-colors"><Minus :size="16" stroke-width="3"/></button>
                                        <span class="w-10 text-center font-mono font-black text-lg">
                                            <Loader2 v-if="processingItem === item.id" class="animate-spin inline text-primary" :size="16"/>
                                            <span v-else>{{ item.quantity }}</span>
                                        </span>
                                        <button @click="updateQuantity(item, item.quantity + 1)" :disabled="processingItem === item.id || item.quantity >= item.max_stock" class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-white/10 disabled:opacity-30 transition-colors"><Plus :size="16" stroke-width="3"/></button>
                                    </div>
                                    
                                    <div class="text-right">
                                        <span v-if="item.line_savings > 0" class="block text-[10px] font-black text-emerald-500 uppercase tracking-widest mb-0.5">
                                            Ahorras Bs. {{ item.line_savings.toFixed(2) }}
                                        </span>
                                        <span class="font-mono font-black text-2xl text-primary drop-shadow-sm leading-none">
                                            Bs. {{ item.subtotal.toFixed(2) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-if="item.upsell" class="mt-2 bg-gradient-to-r from-primary/10 to-transparent border-l-2 border-primary p-3 rounded-r-xl">
                            <p class="text-[11px] font-bold text-foreground/80 flex items-center gap-2">
                                <TrendingDown :size="14" class="text-primary"/>
                                <span>Lleva <strong class="text-primary font-black text-[13px]">{{ item.upsell.needed_quantity }}</strong> unidades más y paga solo <strong class="text-primary font-black text-[13px]">Bs. {{ item.upsell.potential_price.toFixed(2) }}</strong> c/u.</span>
                            </p>
                        </div>

                    </div>
                </div>

                <div class="lg:col-span-4">
                    <div class="bg-surface/50 backdrop-blur-2xl border border-white/10 rounded-[32px] p-6 md:sticky md:top-28 shadow-xl">
                        <h2 class="font-black text-lg uppercase tracking-tighter mb-6 flex items-center gap-2 text-foreground/80">
                            <CreditCard :size="20" class="text-primary"/> Resumen de Compra
                        </h2>
                        
                        <div class="space-y-4 mb-6 text-sm font-bold text-foreground/60">
                            <div class="flex justify-between items-center">
                                <span>Valor Original</span>
                                <span class="font-mono">Bs. {{ (cart.total_price + (cart.total_savings || 0)).toFixed(2) }}</span>
                            </div>
                            
                            <div v-if="cart.total_savings > 0" class="flex justify-between items-center text-emerald-500 bg-emerald-500/10 px-3 py-2 rounded-xl border border-emerald-500/20">
                                <span class="flex items-center gap-1 uppercase tracking-widest text-[10px] font-black"><Tag :size="12"/> Descuentos aplicados</span>
                                <span class="font-mono font-black">- Bs. {{ cart.total_savings.toFixed(2) }}</span>
                            </div>
                        </div>

                        <div class="border-t border-white/10 pt-4 mb-8">
                            <div class="flex justify-between items-end">
                                <span class="text-sm font-black uppercase tracking-widest text-foreground/50 mb-1">Total Final</span>
                                <span class="text-4xl font-mono font-black text-primary leading-none drop-shadow-md">
                                    <span class="text-lg text-primary/50 mr-1">Bs.</span>{{ cart.total_price.toFixed(2) }}
                                </span>
                            </div>
                        </div>

                        <button @click="proceedToCheckout" 
                                :disabled="hasStockErrors" 
                                class="w-full h-16 bg-primary text-white font-black text-sm uppercase tracking-widest rounded-[20px] flex items-center justify-center gap-3 transition-all duration-300 hover:scale-[1.02] active:scale-95 disabled:opacity-50 disabled:hover:scale-100 shadow-xl shadow-primary/20">
                            {{ user ? 'Continuar al Pago' : 'Iniciar Sesión para Pagar' }} <ArrowRight :size="18" stroke-width="3"/>
                        </button>
                    </div>
                </div>
            </div>

            <div v-else class="text-center py-24 bg-white/5 dark:bg-black/20 backdrop-blur-md rounded-[40px] border border-dashed border-white/20 mt-8">
                <PackageOpen :size="80" class="mx-auto text-foreground/20 mb-6" stroke-width="1.5"/>
                <h2 class="text-2xl font-black uppercase tracking-tighter mb-3">Tu carrito está vacío</h2>
                <p class="text-foreground/50 text-sm font-bold mb-8 max-w-md mx-auto">Parece que aún no has encontrado lo que buscas. Explora nuestras zonas y descubre los mejores precios.</p>
                <Link :href="route('customer.shop.index')" class="inline-flex items-center gap-2 px-8 h-14 bg-primary text-white font-black text-sm uppercase tracking-widest rounded-full transition-transform active:scale-95 shadow-lg shadow-primary/30">
                    <ShoppingBag :size="18" stroke-width="2.5"/> Ir a la tienda
                </Link>
            </div>

        </div>
    </ShopLayout>
</template>