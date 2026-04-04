<script setup>
import { computed, ref, watch, onMounted, onUnmounted } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { PackageOpen, Search, Loader2 } from 'lucide-vue-next';
import debounce from 'lodash/debounce';

import ShopLayout from '@/Layouts/ShopLayout.vue';
import CategoryCarousel from '@/Components/Customer/Category/CategoryCarousel.vue';
import SkuCard from '@/Components/Customer/Product/SkuCard.vue';

const props = defineProps({
    categoryData: Object, 
    products: Object,
    banners: Object,
    filters: Object
});

const page = usePage();

// --- DATA BINDING ---
const category = computed(() => props.categoryData?.data || props.categoryData || {});
const globalCategories = computed(() => page.props.categories_menu?.data || page.props.categories_menu || []);
const categoryBanners = computed(() => props.banners?.data || props.banners || []);

// --- ESTADO DE SCROLL ---
const allProducts = ref([...(props.products?.data || [])]);
const nextCursorUrl = ref(props.products?.next_page_url || null);
const isFetching = ref(false);
const observerTarget = ref(null);

const isMounted = ref(false);
onMounted(() => { isMounted.value = true; });
onUnmounted(() => { isMounted.value = false; });

// --- LÓGICA UNIFICADA DE BANNERS (Antes RetailBannerSlot) ---
const handleBannerNavigate = (banner) => {
    if (!banner.target) return;
    const type = banner.target.type?.toLowerCase();
    const id = banner.target.id;

    if (type === 'sku') {
        router.visit(route('customer.product', { id }));
    } else if (type === 'bundle') {
        router.visit(route('customer.bundle', { slug: banner.target.slug || id }));
    }
};

// Cambiar el watch de productos por este:
watch(() => category.value.slug, () => {
    // Si cambia la categoría, reseteamos todo el array
    allProducts.value = [];
    nextCursorUrl.value = null;
    isFetching.value = false;
});

watch(() => props.products, (newData) => {
    if (!isMounted.value) return;

    // Si es una nueva búsqueda o filtro, reemplazamos. Si no, concatenamos.
    const isResetAction = searchQuery.value !== '' || sortBy.value !== 'relevance';
    
    if (isResetAction) {
        allProducts.value = [...(newData?.data || [])];
    } else {
        const existingIds = new Set(allProducts.value.map(p => p.id));
        const uniqueItems = (newData?.data || []).filter(p => !existingIds.has(p.id));
        allProducts.value.push(...uniqueItems);
    }
    nextCursorUrl.value = newData?.next_page_url || null;
}, { deep: true });

const searchQuery = ref(props.filters?.search || '');
const sortBy = ref(props.filters?.sort || 'relevance');

const updateFilters = debounce(() => {
    if (!isMounted.value) return;
    isFetching.value = true;
    router.get(route('customer.category', { category: category.value.slug }), 
        { search: searchQuery.value, sort: sortBy.value }, 
        { 
            preserveState: true, 
            replace: true, 
            preserveScroll: true, 
            only: ['products', 'filters'],
            onFinish: () => { if (isMounted.value) isFetching.value = false; }
        }
    );
}, 400);

watch([searchQuery, sortBy], () => updateFilters());

const loadNextPage = (entries) => {
    const target = entries[0];
    if (!isMounted.value || !target.isIntersecting || !nextCursorUrl.value || isFetching.value) return;

    isFetching.value = true;
    router.get(nextCursorUrl.value, {}, {
        preserveState: true,
        preserveScroll: true,
        only: ['products'],
        onFinish: () => { if (isMounted.value) isFetching.value = false; }
    });
};

let observer = null;
onMounted(() => {
    observer = new IntersectionObserver(loadNextPage, { threshold: 0.1, rootMargin: '300px' });
    if (observerTarget.value) observer.observe(observerTarget.value);
});

onUnmounted(() => {
    if (observer) observer.disconnect();
});
</script>

<template>
    <ShopLayout>
        <div class="w-full min-h-screen bg-transparent pb-32 relative z-10">
            
            <Head :title="category.name || 'Cargando...'" />
            
            <header class="px-4 lg:px-8 max-w-7xl mx-auto pt-0">
                <div v-show="category.name" class="header-standard">
                    <div class="title-block-wrapper">
                        <LayoutGrid :size="16" class="text-neutral-400" />
                        <h1 class="text-3xl md:text-4xl font-black uppercase tracking-tighter text-foreground italic">
                            {{ category.name }}
                        </h1>
                    </div>
                </div>
            </header>

            <div class="sticky top-[72px] z-40 glass-titanium border-b border-white/5">
                <div class="max-w-7xl mx-auto">
                    <CategoryCarousel :categories="globalCategories" :active-id="category.id" />  
                    
                    <div class="px-4 lg:px-8 pb-3">
                        <div class="flex items-center gap-2 bg-foreground/[0.03] border border-border/40 p-1 rounded-xl w-full md:w-max">
                            <div class="relative group flex-1 md:flex-none">
                                <Search class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground" :size="12" />
                                <input v-model="searchQuery" type="text" placeholder="BUSCAR..."
                                       class="w-full md:w-60 pl-8 pr-4 py-1.5 bg-transparent border-none text-[10px] font-black uppercase tracking-widest outline-none focus:ring-0" />
                            </div>
                            <select v-model="sortBy" class="bg-transparent border-none text-[9px] font-black uppercase tracking-widest focus:ring-0 cursor-pointer pr-8">
                                <option value="relevance">RELEVANCIA</option>
                                <option value="price_asc">PRECIO: MENOR</option>
                                <option value="price_desc">PRECIO: MAYOR</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <main class="px-4 lg:px-8 max-w-7xl mx-auto mt-6">
                <section v-show="categoryBanners.length > 0" class="mb-8 space-y-4">
                    <div v-for="banner in categoryBanners" :key="banner.id" 
                        @click="handleBannerNavigate(banner)"
                        class="relative w-full overflow-hidden rounded-[2rem] cursor-pointer group border border-white/10 transition-all duration-700 aspect-[21/9] lg:aspect-[3/1]">
                        <img :src="banner.image_desktop_url" :alt="banner.name"
                            class="w-full h-full object-cover transition-transform duration-1000 group-hover:scale-105" />
                        <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                    </div>
                </section>

                <div v-show="allProducts.length > 0" 
                     class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-3 md:gap-4">
                    <SkuCard v-for="sku in allProducts" :key="sku.id" :sku="sku" class="section-animate" />
                </div>

                <div v-show="allProducts.length === 0 && !isFetching" class="py-40 flex flex-col items-center text-center">
                    <PackageOpen :size="48" class="text-muted-foreground/20 mb-4" />
                    <h2 class="text-xs font-black uppercase tracking-[0.4em] text-muted-foreground/40">Sin Existencias</h2>
                </div>

                <div ref="observerTarget" class="w-full py-12 flex justify-center">
                    <div v-show="isFetching" class="flex flex-col items-center gap-2">
                        <Loader2 class="animate-spin text-primary/40" :size="24" />
                    </div>
                </div>
            </main>
        </div>
    </ShopLayout>
</template>

<style scoped>
.section-animate { animation: reveal 0.5s cubic-bezier(0.23, 1, 0.32, 1) both; }
@keyframes reveal { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }
select option { background-color: #0b1221; color: white; }
/* 1. SISTEMA PRISMÁTICO (COHERENCIA CON INDEX) */
.header-standard {
    display: flex;
    align-items: flex-end;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.title-block-wrapper {
    position: relative;
    display: flex;
    align-items: center;
    gap: 0.85rem;
    flex-grow: 1;
    padding-bottom: 8px;
}

.title-block-wrapper::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 1px;
    background: linear-gradient(to right, #ff0000, #ff7f00, #ffff00, #00ff00, #0000ff, #4b0082, #8b00ff);
    opacity: 0.8;
}

/* 2. ACABADO GLASS PARA FILTROS STICKY */
.glass-titanium {
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.05) 0%, rgba(255, 255, 255, 0.01) 100%);
    backdrop-filter: blur(20px) saturate(180%);
    -webkit-backdrop-filter: blur(20px) saturate(180%);
}

/* 3. PURGA DE SELECTORES */
select { background: transparent !important; }
select option { background-color: #080808; color: white; }

/* 4. ANIMACIONES AFILADAS */
.section-animate { 
    animation: reveal 0.4s cubic-bezier(0.32, 0.72, 0, 1) both; 
}

@keyframes reveal { 
    from { opacity: 0; transform: translateY(15px); } 
    to { opacity: 1; transform: translateY(0); } 
}

</style>