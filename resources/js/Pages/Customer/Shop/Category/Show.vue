<script setup>
import { computed, ref, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { 
    ChevronLeft, PackageOpen, ArrowDownUp, 
    Search, ArrowUp, Zap 
} from 'lucide-vue-next';
import ShopLayout from '@/Layouts/ShopLayout.vue';
import HeroCarousel from '@/Components/Shop/HeroCarousel.vue';
import CategoryCarousel from '@/Components/Shop/CategoryCarousel.vue';
import debounce from 'lodash/debounce';

const props = defineProps({
    categoryData: Object, // Trae { data: { id, name, slug, banners: [], products: [] } }
    categories: Object,
    filters: Object 
});

// --- DESEMPAQUETADO ATÓMICO ---
const category = computed(() => props.categoryData?.data || {});
const banners = computed(() => category.value.banners || []);
const products = computed(() => category.value.products || []);

const searchQuery = ref(props.filters?.search || '');
const sortBy = ref('relevance');

// --- MOTOR DE PROCESAMIENTO (Búsqueda local + Ordenamiento) ---
const displayProducts = computed(() => {
    let result = [...products.value];

    // 1. Filtro local instantáneo
    if (searchQuery.value) {
        const term = searchQuery.value.toLowerCase();
        result = result.filter(p => 
            p.name.toLowerCase().includes(term) || 
            p.brand_name.toLowerCase().includes(term)
        );
    }

    // 2. Ordenamiento Financiero
    if (sortBy.value === 'price_asc') {
        result.sort((a, b) => a.price.final - b.price.final);
    } else if (sortBy.value === 'price_desc') {
        result.sort((a, b) => b.price.final - a.price.final);
    }
    
    return result;
});

// --- SINCRONIZACIÓN REDIS (Server Side) ---
const updateServerSearch = debounce((value) => {
    router.get(route('customer.shop.category', { category: category.value.slug }), 
        { search: value }, 
        { preserveState: true, replace: true, preserveScroll: true }
    );
}, 500);

watch(searchQuery, (val) => {
    if (val.length > 2 || val.length === 0) updateServerSearch(val);
});

const addToCart = (product) => {
    router.post(route('customer.cart.upsert'), {
        target_id: product.id,
        target_type: 'sku',
        quantity: 1
    }, { preserveScroll: true });
};
</script>

<template>
    <ShopLayout>
        <Head :title="category.name" />

        <div class="w-full min-h-screen bg-background pb-32">
            <CategoryCarousel :categories="props.categories" :active-id="category.id" />
            <HeroCarousel v-if="banners.length > 0" :banners="banners" />

            <div class="px-6 py-10 md:px-12">
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-8 mb-12">
                    <div class="space-y-4">
                        <div class="flex items-center gap-4">
                            <button @click="router.visit(route('customer.shop.index'))" 
                                    class="p-3 hover:bg-muted rounded-2xl transition-all border border-border">
                                <ChevronLeft :size="24" stroke-width="3" />
                            </button>
                            <h1 class="text-5xl md:text-7xl font-black italic uppercase tracking-tighter leading-none text-foreground">
                                {{ category.name }}
                            </h1>
                        </div>
                        <p class="text-[10px] font-black uppercase tracking-[0.4em] text-muted-foreground ml-16">
                            {{ displayProducts.length }} OPCIONES ENCONTRADAS
                        </p>
                    </div>

                    <div class="bg-card/40 backdrop-blur-xl border border-border rounded-[2.5rem] p-2 flex flex-col md:flex-row items-center gap-2 shadow-2xl w-full md:w-auto">
                        <div class="relative w-full md:w-80 group">
                            <Search class="absolute left-6 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within:text-primary transition-colors" :size="18" />
                            <input v-model="searchQuery" type="text" placeholder="BUSCAR..."
                                   class="w-full pl-14 pr-6 py-4 bg-background/60 border-none rounded-3xl text-xs font-black uppercase tracking-widest outline-none focus:ring-2 focus:ring-primary/20 transition-all" />
                        </div>
                        <div class="hidden md:block w-px h-8 bg-border"></div>
                        <div class="flex items-center gap-3 px-6 py-2">
                            <ArrowDownUp :size="16" class="text-primary" />
                            <select v-model="sortBy" class="bg-transparent border-none text-[10px] font-black uppercase tracking-widest focus:ring-0 cursor-pointer text-foreground">
                                <option value="relevance">RELEVANCIA</option>
                                <option value="price_asc">PRECIO: BAJO</option>
                                <option value="price_desc">PRECIO: ALTO</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div v-if="displayProducts.length > 0" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6">
                    <div v-for="product in displayProducts" :key="product.id"
                         class="group bg-card rounded-[2.5rem] p-4 transition-all duration-500 hover:shadow-2xl flex flex-col h-full border border-border/40 overflow-hidden">
                        
                        <div class="aspect-square mb-5 rounded-[2rem] bg-[#F5F5F7] dark:bg-background/50 flex items-center justify-center p-8 relative overflow-hidden">
                            <img :src="product.image" class="w-full h-full object-contain group-hover:scale-110 transition-transform duration-700">

                            <div v-if="product.price.discount > 0" 
                                 class="absolute top-4 left-4 bg-primary text-primary-foreground px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest shadow-sm">
                                -{{ product.price.discount }}%
                            </div>

                            <button @click="addToCart(product)"
                                    :disabled="product.stock <= 0"
                                    class="absolute bottom-4 right-4 w-12 h-12 bg-card rounded-full flex items-center justify-center shadow-xl border border-border hover:bg-primary hover:text-primary-foreground transition-all active:scale-90 disabled:opacity-40 group/btn z-10">
                                <ArrowUp :size="24" stroke-width="3" class="group-hover/btn:-translate-y-0.5 transition-transform" />
                            </button>
                        </div>

                        <div class="px-3 pb-2 flex-1 flex flex-col">
                            <div class="mb-1">
                                <span v-if="product.price.list > product.price.final" class="text-[11px] font-bold text-muted-foreground/50 line-through block leading-none">
                                    Bs. {{ product.price.list.toFixed(2) }}
                                </span>
                                <span class="text-2xl font-black tracking-tighter text-foreground leading-tight">
                                    Bs. {{ product.price.final.toFixed(2) }}
                                </span>
                            </div>

                            <div class="space-y-0.5">
                                <h3 class="font-bold text-[15px] leading-snug text-foreground line-clamp-2 min-h-[40px]">
                                    {{ product.name }}
                                </h3>
                                <p class="text-[10px] font-black uppercase tracking-[0.15em] text-muted-foreground/60">
                                    {{ product.brand_name }}
                                </p>
                            </div>

                            <div class="mt-4 flex items-center gap-2">
                                <div class="w-2 h-2 rounded-full" :class="product.stock > 0 ? 'bg-accent animate-pulse' : 'bg-destructive'"></div>
                                <p class="text-[9px] font-black uppercase tracking-widest" :class="product.stock > 0 ? 'text-accent' : 'text-destructive'">
                                    {{ product.stock > 0 ? 'En Stock' : 'Agotado' }}
                                </p>
                            </div>

                            <div v-if="product.price.next_tier" class="mt-4 bg-primary/5 border border-primary/10 rounded-xl p-2.5">
                                <p class="text-[9px] font-bold uppercase text-primary text-center leading-tight">
                                    Lleva {{ product.price.next_tier.min_qty }} a Bs. {{ product.price.next_tier.price.toFixed(2) }} c/u
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-else class="py-40 text-center">
                    <div class="w-24 h-24 bg-muted rounded-full flex items-center justify-center mx-auto mb-6 opacity-40">
                        <PackageOpen :size="48" class="text-muted-foreground" />
                    </div>
                    <h2 class="text-2xl font-black uppercase italic tracking-tighter text-foreground mb-2">Sin coincidencias</h2>
                    <p class="text-muted-foreground font-medium italic max-w-xs mx-auto">No se detectaron variantes que coincidan con tu radar de búsqueda.</p>
                </div>
            </div>
        </div>
    </ShopLayout>
</template>

<style scoped>
.bg-card\/40 {
    background-color: hsl(var(--card) / 0.4);
}
.shadow-apple-soft {
    box-shadow: 0 20px 40px -10px rgba(0,0,0,0.05);
}
/* Transición suave para el cambio de modo claro/oscuro */
* {
    transition: background-color 0.3s ease, border-color 0.3s ease;
}
</style>