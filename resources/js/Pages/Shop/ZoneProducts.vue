<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ShoppingCart, ArrowLeft, Plus, Minus } from 'lucide-vue-next';
import { ref, computed } from 'vue';

const props = defineProps({
    zone: Object,
    groupedCategories: {
        type: Array,
        default: () => [] // Evita error si viene nulo
    }
});

const cart = ref({}); 

// Helper seguro para cantidad total
const totalProductsCount = computed(() => {
    if (!props.groupedCategories) return 0;
    return props.groupedCategories.reduce((acc, cat) => acc + (cat.products ? cat.products.length : 0), 0);
});

// Lógica de Carrito
const getQuantity = (productId) => cart.value[productId] || 0;
const cartTotal = () => Object.values(cart.value).reduce((a, b) => a + b, 0);

const addToCart = (product) => {
    if (!cart.value[product.id]) cart.value[product.id] = 0;
    // Validación visual simple de stock máximo
    if (cart.value[product.id] < product.total_stock) {
        cart.value[product.id]++;
    }
};

const removeFromCart = (product) => {
    if (cart.value[product.id] > 0) {
        cart.value[product.id]--;
        if (cart.value[product.id] === 0) delete cart.value[product.id];
    }
};
</script>

<template>
    <div class="min-h-screen bg-slate-900 pb-24 font-sans text-slate-100">
        <Head :title="zone?.name || 'Zona'" />

        <div class="sticky top-0 z-30 bg-slate-900/90 backdrop-blur-md border-b border-white/10 px-4 py-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <Link :href="route('shop.index')" class="p-2 -ml-2 rounded-full hover:bg-white/10 transition active:scale-95">
                        <ArrowLeft class="w-5 h-5 text-white" />
                    </Link>
                    <div>
                        <h1 class="font-bold text-lg leading-none tracking-tight text-white">{{ zone?.name }}</h1>
                        <span class="text-[10px] text-emerald-400 font-bold tracking-wide uppercase">
                            {{ totalProductsCount }} Productos
                        </span>
                    </div>
                </div>
                
                <button class="relative p-2.5 bg-white/10 rounded-full hover:bg-white/20 transition active:scale-95">
                    <ShoppingCart class="w-5 h-5 text-white" />
                    <span v-if="cartTotal() > 0" 
                          class="absolute -top-1 -right-1 w-5 h-5 bg-emerald-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center animate-in zoom-in">
                        {{ cartTotal() }}
                    </span>
                </button>
            </div>
        </div>

        <div class="pt-6 pb-10 space-y-8">
            
            <div v-if="groupedCategories.length === 0" class="py-20 text-center px-6">
                <div class="inline-block p-4 rounded-full bg-white/5 mb-4">
                    <ShoppingCart class="w-8 h-8 text-slate-500" />
                </div>
                <h3 class="text-white font-bold text-lg">Zona sin stock disponible</h3>
                <p class="text-slate-400 text-sm mt-2">No encontramos productos activos en esta zona para tu sucursal.</p>
                <Link :href="route('shop.index')" class="mt-6 inline-block px-6 py-2 bg-white/10 hover:bg-white/20 rounded-full text-sm font-bold transition">
                    Volver al Mapa
                </Link>
            </div>

            <div v-for="category in groupedCategories" :key="category.id" class="animate-in fade-in slide-in-from-bottom-4 duration-700">
                
                <div class="px-4 mb-3">
                    <h2 class="text-lg font-bold text-white tracking-wide border-l-4 border-emerald-500 pl-3">
                        {{ category.name }}
                    </h2>
                </div>

                <div class="flex overflow-x-auto gap-3 px-4 pb-4 snap-x scrollbar-hide scroll-smooth">
                    
                    <div v-for="product in category.products" :key="product.id" 
                         class="snap-start shrink-0 w-40 flex flex-col bg-slate-800 rounded-xl overflow-hidden border border-white/5 shadow-lg group relative">
                        
                        <div class="aspect-[3/4] bg-white p-3 relative flex items-center justify-center">
                            <img :src="product.image_url" 
                                 class="w-full h-full object-contain transition-transform duration-300 group-hover:scale-110 drop-shadow-xl" 
                                 loading="lazy" 
                                 alt="Producto"
                            />
                            
                            <div v-if="!product.has_stock" class="absolute inset-0 bg-slate-900/60 flex items-center justify-center backdrop-blur-[1px]">
                                <span class="bg-red-500 text-white text-[10px] font-bold px-2 py-1 rounded">AGOTADO</span>
                            </div>
                        </div>

                        <div class="p-3 flex flex-col flex-1">
                            <h3 class="text-xs font-medium text-slate-300 leading-snug line-clamp-2 min-h-[2.5em] mb-2">
                                {{ product.name }}
                            </h3>
                            
                            <div class="mt-auto">
                                <p class="text-emerald-400 font-bold text-sm mb-3">
                                    Bs {{ product.price_display }}
                                </p>

                                <div v-if="product.has_stock" class="flex items-center justify-between bg-slate-900/50 rounded-lg p-1 border border-white/10">
                                    <button 
                                        @click="removeFromCart(product)"
                                        :disabled="getQuantity(product.id) === 0"
                                        class="w-7 h-7 flex items-center justify-center rounded-md bg-white/5 hover:bg-white/10 text-white disabled:opacity-30 transition"
                                    >
                                        <Minus class="w-3.5 h-3.5" />
                                    </button>
                                    
                                    <span class="font-bold text-sm w-6 text-center tabular-nums text-white">
                                        {{ getQuantity(product.id) }}
                                    </span>

                                    <button 
                                        @click="addToCart(product)"
                                        class="w-7 h-7 flex items-center justify-center rounded-md bg-emerald-600 text-white hover:bg-emerald-500 active:scale-95 transition shadow-lg shadow-emerald-900/20"
                                    >
                                        <Plus class="w-3.5 h-3.5" />
                                    </button>
                                </div>
                                <div v-else class="text-center py-1.5 bg-white/5 rounded text-[10px] text-slate-500 font-bold">
                                    No disponible
                                </div>
                            </div>
                        </div>

                    </div>
                    
                    <div class="w-2 shrink-0"></div>
                </div>
            </div>

        </div>
    </div>
</template>

<style scoped>
/* Ocultar barra de scroll pero mantener funcionalidad */
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>