<script setup>
import { computed, ref, watch, onMounted } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { ChevronLeft, PackageOpen, ArrowDownUp, Search } from 'lucide-vue-next';
import debounce from 'lodash/debounce';

// Layouts & Components
import ShopLayout from '@/Layouts/ShopLayout.vue';
import CategoryCarousel from '@/Components/Customer/Category/CategoryCarousel.vue';
import RetailBannerSlot from '@/Components/Customer/RetailMedia/RetailBannerSlot.vue';
import SkuCard from '@/Components/Customer/Product/SkuCard.vue';

const props = defineProps({
    categoryData: Object, 
    products: Object, 
    filters: Object,
    loading: { type: Boolean, default: false }
});

const page = usePage();

// --- DATA BINDING ---
const category = computed(() => props.categoryData?.data || props.categoryData || {});
const fullProductsList = computed(() => props.products?.data || []);
const globalCategories = computed(() => page.props.categories_menu || []);

// --- INFINITE RENDER LOGIC ---
const itemsPerPage = 12; // Aumentado para mejor matriz inicial
const visibleCount = ref(itemsPerPage);
const observerTarget = ref(null);

const displayedProducts = computed(() => {
    return fullProductsList.value.slice(0, visibleCount.value);
});

const loadMore = (entries) => {
    if (entries[0].isIntersecting && visibleCount.value < fullProductsList.value.length) {
        visibleCount.value += itemsPerPage;
    }
};

onMounted(() => {
    const observer = new IntersectionObserver(loadMore, { threshold: 0.1 });
    if (observerTarget.value) observer.observe(observerTarget.value);
});

// --- FILTRADO & ESTADO ---
const searchQuery = ref(props.filters?.search || '');
const sortBy = ref(props.filters?.sort || 'relevance');

const updateServerFilters = debounce(() => {
    visibleCount.value = itemsPerPage;
    router.get(route('customer.shop.category', { category: category.value.slug }), 
        { search: searchQuery.value, sort: sortBy.value }, 
        { preserveState: true, replace: true, preserveScroll: true, only: ['products', 'filters'] }
    );
}, 400);

watch([searchQuery, sortBy], () => updateServerFilters());
</script>

<template>
    <ShopLayout>
        <Head :title="category.name" />

        <div class="w-full min-h-screen bg-background pb-32">
            
            <div class="-mt-4 relative z-20">
                <CategoryCarousel 
                    :categories="globalCategories" 
                    :active-id="category.id" 
                />
            </div>
            
            <section v-if="category.banners?.length > 0" class="px-4 lg:px-8 mt-2 max-w-7xl mx-auto">
                <RetailBannerSlot :banners="category.banners" />
            </section>

            <div class="px-4 lg:px-8 max-w-7xl mx-auto mt-6">
                
                <header class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-8">
                    <div v-if="category.name" class="flex items-center gap-4">
                        <div class="w-1.5 h-10 rounded-full" :style="{ backgroundColor: category.bg_color || 'var(--primary)' }"></div>
                        <h1 class="text-4xl md:text-5xl font-black uppercase tracking-tighter leading-none text-foreground italic">
                            {{ category.name }}
                        </h1>
                    </div>
                    <div v-else class="h-12 w-64 bg-foreground/5 animate-pulse rounded-xl"></div>

                    <div v-if="!loading" class="flex items-center gap-2 bg-card/40 backdrop-blur-xl border border-border/40 p-1.5 rounded-3xl shadow-f1-glow/5 w-full md:w-auto">
                        <div class="relative group flex-1 md:flex-none">
                            <Search class="absolute left-4 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within:text-primary transition-colors" :size="14" />
                            <input v-model="searchQuery" type="text" placeholder="BUSCAR ASSET..."
                                   class="w-full md:w-60 pl-10 pr-4 py-2 bg-background/50 border-none rounded-2xl text-[10px] font-black uppercase tracking-widest outline-none focus:ring-1 focus:ring-primary/30 transition-all" />
                        </div>
                        <div class="h-6 w-px bg-border/50 hidden md:block"></div>
                        <select v-model="sortBy" class="bg-transparent border-none text-[10px] font-black uppercase tracking-widest focus:ring-0 cursor-pointer pr-8">
                            <option value="relevance">RELEVANCIA</option>
                            <option value="price_asc">MIN_COST</option>
                            <option value="price_desc">MAX_COST</option>
                        </select>
                    </div>
                    <div v-else class="h-12 w-full md:w-80 bg-foreground/5 animate-pulse rounded-3xl border border-border/40"></div>
                </header>

                <div v-if="displayedProducts.length > 0" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3 md:gap-6">
                    <SkuCard 
                        v-for="sku in displayedProducts" 
                        :key="sku.id" 
                        :sku="sku" 
                        class="section-animate"
                    />
                </div>

                <div v-else-if="!loading" class="py-32 flex flex-col items-center text-center border border-dashed border-border/50 rounded-[3rem] bg-card/10">
                    <PackageOpen :size="32" class="text-muted-foreground/20 mb-4" />
                    <h2 class="text-xs font-black uppercase tracking-[0.5em] text-muted-foreground/40">Silo_Vaciado</h2>
                    <p class="text-[9px] uppercase tracking-widest text-muted-foreground/30 mt-2 italic">No se detectaron productos en esta frecuencia.</p>
                </div>

                <div ref="observerTarget" class="w-full h-20 mt-10">
                    <div v-if="visibleCount < fullProductsList.length || loading" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3 md:gap-6 opacity-40">
                        <div v-for="n in 4" :key="n" class="aspect-[3/4] rounded-[2rem] bg-card animate-pulse border border-border/20"></div>
                    </div>
                </div>
            </div>
        </div>
    </ShopLayout>
</template>

<style scoped>
.section-animate {
    animation: slideUp 0.6s cubic-bezier(0.23, 1, 0.32, 1) both;
}

@keyframes slideUp {
    from { opacity: 0; transform: translateY(15px); }
    to { opacity: 1; transform: translateY(0); }
}

select option {
    background-color: #1a1a1a;
    color: white;
}

/* Scroll más suave para alta densidad */
.no-scrollbar {
    -webkit-overflow-scrolling: touch;
}
</style>