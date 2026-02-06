<script setup>
import { ref, computed } from 'vue';
import { Head, router, usePage, Link } from '@inertiajs/vue3';
import ShopLayout from '@/Layouts/ShopLayout.vue';
import { useAuthModal } from '@/Composables/useAuthModal';
import { 
    Trash2, Plus, Minus, ArrowRight, AlertTriangle, ShoppingBag, 
    Loader2, LogIn, PackageOpen, CreditCard 
} from 'lucide-vue-next';

const props = defineProps({
    cart: Object,
    items: Array,
});

const page = usePage();
const user = computed(() => page.props.auth?.user);

const { openLogin } = useAuthModal();
const processingItem = ref(null);

const hasStockErrors = computed(() => {
    return props.items.some(item => item.quantity > item.max_stock);
});

const proceedToCheckout = () => {
    if (!props.cart) return;
    
    if (!user.value) {
        openLogin();
        return;
    }

    if (props.cart.is_conflict || hasStockErrors.value) {
        alert('Por favor, corrige los problemas de stock antes de continuar.');
        return; 
    }

    router.visit(route('checkout.index'));
};

const updateQuantity = (item, newQty) => {
    if (newQty < 1) return; 
    if (newQty > item.max_stock) return;

    processingItem.value = item.id;
    // FIX: Usamos PATCH según tu backend
    router.patch(route('cart.update', item.id), { quantity: newQty }, {
        preserveScroll: true,
        onFinish: () => processingItem.value = null,
    });
};

const removeItem = (id) => {
    if(confirm('¿Quitar producto del carrito?')) {
        // FIX: Cambiado a 'cart.remove' que es el nombre probable de la ruta DELETE
        router.delete(route('cart.remove', id), { preserveScroll: true });
    }
};
</script>

<template>
    <ShopLayout>
        <Head title="Tu Carga" />

        <div class="container mx-auto px-4 pt-6 pb-48 lg:pb-12 min-h-full flex flex-col relative">
            
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="font-display font-black text-3xl text-foreground uppercase tracking-tight italic">
                        Tu <span class="text-primary">Carga</span>
                    </h1>
                    <p class="text-xs text-muted-foreground font-mono mt-1">
                        {{ items.length }} ITEMS / ID: {{ cart?.id ? String(cart.id).substring(0,8) : '---' }}
                    </p>
                </div>
                <div class="hidden sm:block">
                    <ShoppingBag class="text-primary/20" :size="48" stroke-width="1.5" />
                </div>
            </div>

            <div v-if="cart?.is_conflict" class="mb-8 bg-error/10 border border-error/50 p-4 rounded-2xl relative overflow-hidden group">
                <div class="absolute inset-0 bg-error/5 animate-pulse"></div>
                <div class="flex items-start gap-4 relative z-10">
                    <div class="p-2 bg-error/20 rounded-lg text-error">
                        <AlertTriangle :size="24" />
                    </div>
                    <div>
                        <h3 class="font-black text-error uppercase tracking-widest text-xs mb-1">Error de Sincronización</h3>
                        <p class="text-sm text-foreground/80 leading-relaxed">
                            Conflicto de geolocalización. Estos productos pertenecen a otra sucursal.
                        </p>
                    </div>
                </div>
            </div>

            <div v-if="items.length > 0" class="flex flex-col lg:flex-row gap-8 items-start">
                
                <div class="flex-1 w-full space-y-4">
                    <TransitionGroup 
                        enter-active-class="transition duration-300 ease-out"
                        enter-from-class="opacity-0 -translate-x-4"
                        enter-to-class="opacity-100 translate-x-0"
                        leave-active-class="transition duration-200 ease-in absolute w-full"
                        leave-from-class="opacity-100 translate-x-0"
                        leave-to-class="opacity-0 translate-x-4"
                        move-class="transition duration-300 ease-in-out"
                    >
                        <div v-for="item in items" :key="item.id" 
                             class="group relative bg-card border rounded-2xl overflow-hidden transition-all duration-300 shadow-sm hover:shadow-md"
                             :class="item.max_stock < item.quantity ? 'border-error/50 ring-1 ring-error/50' : 'border-border hover:border-primary/50'">
                            
                            <div class="absolute bottom-0 left-0 h-0.5 bg-primary transition-all duration-500"
                                 :style="{ width: `${Math.min((item.quantity / item.max_stock) * 100, 100)}%` }"></div>

                            <div class="flex p-3 sm:p-4 gap-4">
                                <div class="w-20 h-24 sm:w-24 sm:h-28 bg-muted/30 rounded-xl shrink-0 flex items-center justify-center border border-border relative overflow-hidden">
                                    <img :src="item.image" class="w-full h-full object-contain p-2 transition-transform duration-500 group-hover:scale-110 mix-blend-multiply dark:mix-blend-normal" 
                                         @error="$event.target.src = 'https://placehold.co/100x100?text=No+Image'"
                                         alt="Producto">
                                    
                                    <div v-if="item.max_stock === 0" class="absolute inset-0 bg-background/80 flex items-center justify-center backdrop-blur-sm">
                                        <span class="text-[10px] font-black text-error uppercase tracking-widest border border-error/50 px-2 py-1 rounded bg-background">Agotado</span>
                                    </div>
                                </div>

                                <div class="flex-1 min-w-0 flex flex-col justify-between py-1">
                                    <div>
                                        <div class="flex justify-between items-start gap-2">
                                            <h3 class="font-bold text-foreground text-sm sm:text-base leading-tight line-clamp-2 mb-1">
                                                {{ item.name }}
                                            </h3>
                                            <button @click="removeItem(item.id)" class="text-muted-foreground hover:text-error transition opacity-100 sm:opacity-0 sm:group-hover:opacity-100 p-1">
                                                <Trash2 :size="16" />
                                            </button>
                                        </div>
                                        <p class="text-[10px] sm:text-xs text-muted-foreground font-mono">
                                            Unit: <span class="text-primary font-bold">Bs {{ parseFloat(item.unit_price).toFixed(2) }}</span>
                                        </p>
                                    </div>

                                    <div class="flex items-end justify-between mt-3">
                                        <div class="flex items-center gap-3">
                                            <div v-if="item.quantity > item.max_stock" class="animate-in fade-in zoom-in duration-300">
                                                <button @click="updateQuantity(item, item.max_stock)" 
                                                        class="h-8 px-3 bg-error hover:bg-error/90 text-white rounded-lg text-xs font-black uppercase tracking-wide shadow-lg shadow-error/20 flex items-center gap-2 transition-all active:scale-95">
                                                    <AlertTriangle :size="14" stroke-width="3" />
                                                    <span>Corregir a {{ item.max_stock }}</span>
                                                </button>
                                            </div>

                                            <div v-else class="flex items-center bg-muted/50 rounded-lg border border-border p-0.5 shadow-inner">
                                                <button @click="updateQuantity(item, item.quantity - 1)" 
                                                        class="w-8 h-8 flex items-center justify-center text-muted-foreground hover:text-foreground hover:bg-background rounded-md transition disabled:opacity-30"
                                                        :disabled="item.quantity <= 1 || processingItem === item.id">
                                                    <Minus :size="14" />
                                                </button>
                                                
                                                <div class="w-8 sm:w-10 text-center relative">
                                                    <Loader2 v-if="processingItem === item.id" class="animate-spin mx-auto text-primary" :size="14" />
                                                    <span v-else class="text-sm font-black text-foreground font-mono">{{ item.quantity }}</span>
                                                </div>
                                                
                                                <button @click="updateQuantity(item, item.quantity + 1)" 
                                                        class="w-8 h-8 flex items-center justify-center text-muted-foreground hover:text-foreground hover:bg-background rounded-md transition disabled:opacity-30"
                                                        :disabled="item.quantity >= item.max_stock || processingItem === item.id">
                                                    <Plus :size="14" />
                                                </button>
                                            </div>
                                            
                                            <span v-if="!(item.quantity > item.max_stock) && item.quantity >= item.max_stock && item.max_stock > 0" 
                                                class="hidden sm:inline-flex text-[9px] font-bold text-warning bg-warning/10 px-2 py-1 rounded border border-warning/20">
                                                MAX
                                            </span>
                                        </div>

                                        <div class="text-right">
                                            <p class="text-base sm:text-lg font-black text-foreground tracking-tight">
                                                Bs {{ parseFloat(item.subtotal).toFixed(2) }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </TransitionGroup>
                </div>

                <div class="hidden lg:block lg:w-96 shrink-0">
                    <div class="bg-card backdrop-blur-xl p-6 rounded-2xl border border-border shadow-xl sticky top-24">
                        <h3 class="font-bold text-foreground text-lg mb-6 flex items-center gap-2">
                            <CreditCard :size="20" class="text-primary" /> Resumen de Orden
                        </h3>
                        
                        <div class="space-y-3 mb-6">
                            <div class="flex justify-between text-sm text-muted-foreground">
                                <span>Subtotal</span>
                                <span class="font-mono text-foreground">Bs {{ cart?.total?.toFixed(2) || '0.00' }}</span>
                            </div>
                            <div class="flex justify-between text-sm text-muted-foreground">
                                <span>Impuestos est.</span>
                                <span class="font-mono text-foreground">Bs 0.00</span>
                            </div>
                            <div class="h-px bg-border my-2"></div>
                            <div class="flex justify-between items-end">
                                <span class="font-black text-foreground text-lg">TOTAL</span>
                                <span class="font-black text-3xl text-primary tracking-tighter shadow-glow">
                                    Bs {{ cart?.total?.toFixed(2) || '0.00' }}
                                </span>
                            </div>
                        </div>

                        <button @click="proceedToCheckout" 
                                :disabled="user && (cart?.is_conflict || hasStockErrors)"
                                class="w-full relative group overflow-hidden rounded-xl p-[1px] disabled:opacity-50 disabled:cursor-not-allowed">
                            <div class="absolute inset-0 bg-gradient-to-r from-primary to-blue-600 rounded-xl transition-opacity duration-300 opacity-100 group-hover:opacity-90"></div>
                            <div class="relative bg-background/20 backdrop-blur-sm rounded-xl py-4 flex items-center justify-center gap-3 text-white font-black uppercase tracking-widest text-sm transition-all group-hover:bg-transparent">
                                <template v-if="!user">
                                    <LogIn :size="18" /> Iniciar Sesión
                                </template>
                                <template v-else-if="hasStockErrors">
                                    <AlertTriangle :size="18" /> Revisar Stock
                                </template>
                                <template v-else>
                                    Procesar Pago <ArrowRight :size="18" />
                                </template>
                            </div>
                        </button>

                        <p v-if="!user" class="text-[10px] text-center text-muted-foreground mt-4">
                            Tus productos se guardarán en tu cuenta.
                        </p>
                    </div>
                </div>

            </div>
            
            <div v-else class="flex-1 flex flex-col items-center justify-center py-20 text-center opacity-0 animate-in fade-in slide-in-from-bottom-4 duration-700 fill-mode-forwards">
                <div class="w-32 h-32 bg-muted/20 rounded-full flex items-center justify-center mb-6 border border-border shadow-lg">
                    <PackageOpen class="text-muted-foreground" :size="48" stroke-width="1" />
                </div>
                <h2 class="text-2xl font-black text-foreground mb-2">Tu carga está vacía</h2>
                <p class="text-muted-foreground mb-8 max-w-xs mx-auto">Explora nuestro catálogo y comienza a añadir equipamiento.</p>
                <Link :href="route('shop.index')" class="px-8 py-3 bg-card hover:bg-muted border border-border rounded-full text-foreground font-bold uppercase tracking-widest text-xs transition-all hover:scale-105 shadow-sm">
                    Volver al Catálogo
                </Link>
            </div>

        </div>

        <div v-if="items.length > 0" class="lg:hidden fixed bottom-[60px] left-0 right-0 z-30 px-4 pb-2 pt-4 bg-gradient-to-t from-background via-background/95 to-transparent">
            <div class="bg-card/90 backdrop-blur-xl border border-border p-4 rounded-2xl shadow-[0_0_30px_-5px_rgba(0,0,0,0.2)] flex items-center justify-between gap-4 ring-1 ring-white/5">
                
                <div class="flex flex-col">
                    <span class="text-[10px] text-muted-foreground font-bold uppercase tracking-wider">Total a Pagar</span>
                    <span class="text-xl font-black text-foreground tracking-tight">
                        Bs {{ cart?.total?.toFixed(2) }}
                    </span>
                </div>

                <button @click="proceedToCheckout" 
                        :disabled="user && (cart?.is_conflict || hasStockErrors)"
                        class="flex-1 bg-primary hover:bg-primary/90 text-primary-foreground font-black py-3 rounded-xl shadow-lg shadow-primary/20 flex items-center justify-center gap-2 text-sm uppercase tracking-wide disabled:opacity-50 disabled:grayscale transition-all active:scale-95">
                    <span v-if="!user">Ingresar</span>
                    <span v-else-if="hasStockErrors">Revisar</span>
                    <span v-else>Pagar <ArrowRight :size="16" stroke-width="3"/></span>
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