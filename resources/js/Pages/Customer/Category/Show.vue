<script setup>
import { computed, ref, watch, onMounted, onUnmounted } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { PackageOpen, Search, Loader2, LayoutGrid } from 'lucide-vue-next';
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

const category = computed(() => props.categoryData?.data || props.categoryData || {});
const globalCategories = computed(() => page.props.categories_menu?.data || page.props.categories_menu || []);
const categoryBanners = computed(() => props.banners?.data || props.banners || []);

const allProducts = ref([...(props.products?.data || [])]);
const nextCursorUrl = ref(props.products?.next_page_url || null);
const isFetching = ref(false);
const isPaginating = ref(false);
const observerTarget = ref(null);

const isMounted = ref(false);
onMounted(() => { isMounted.value = true; });
onUnmounted(() => { isMounted.value = false; });

const searchQuery = ref(props.filters?.search || '');
const sortBy = ref(props.filters?.sort || 'relevance');

watch(() => category.value.slug, () => {
    allProducts.value = [];
    nextCursorUrl.value = null;
    isFetching.value = false;
    isPaginating.value = false;
    searchQuery.value = ''; 
    sortBy.value = 'relevance'; 
});

watch(() => props.products, (newData) => {
    if (!isMounted.value) return;

    if (!isPaginating.value) {
        allProducts.value = [...(newData?.data || [])];
    } else {
        const existingIds = new Set(allProducts.value.map(p => p.id));
        const uniqueItems = (newData?.data || []).filter(p => !existingIds.has(p.id));
        allProducts.value.push(...uniqueItems);
    }
    
    nextCursorUrl.value = newData?.next_page_url || null;
    isPaginating.value = false; 
}, { deep: true });

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
    isPaginating.value = true; 
    
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
            
            <header class="px-4 lg:px-8 max-w-7xl mx-auto pt-4">
                <div v-show="category.name" class="header-standard">
                    <div class="title-block-wrapper">
                        <LayoutGrid :size="16" :stroke-width="2" class="text-neutral-500" />
                        <h1 class="text-3xl md:text-4xl font-black uppercase tracking-tighter text-foreground italic">
                            {{ category.name }}
                        </h1>
                    </div>
                </div>
            </header>

            <div class="sticky top-[64px] lg:top-[64px] z-40 bg-background border-b border-[#32323b] shadow-sm transition-colors duration-150">
                <div class="max-w-7xl mx-auto">
                    <CategoryCarousel :categories="globalCategories" :active-id="category.id" />  
                    
                    <div class="px-4 lg:px-8 pb-3">
                        <div class="flex items-center gap-2 bg-transparent border border-[#32323b] p-1 rounded-xl w-full md:w-max">
                            <div class="relative group flex-1 md:flex-none">
                                <Search class="absolute left-3 top-1/2 -translate-y-1/2 text-neutral-500" :size="14" :stroke-width="2" />
                                <input v-model="searchQuery" type="text" placeholder="BUSCAR..."
                                       class="w-full md:w-60 pl-8 pr-4 py-1.5 bg-transparent border-none text-[10px] font-black uppercase tracking-widest outline-none focus:ring-0 text-foreground placeholder:text-neutral-500" />
                            </div>
                            <div class="w-[1px] h-4 bg-[#32323b] hidden md:block"></div>
                            <select v-model="sortBy" class="bg-transparent border-none text-[10px] font-black uppercase tracking-widest focus:ring-0 cursor-pointer pr-8 text-foreground outline-none">
                                <option value="relevance" class="bg-background text-foreground">RELEVANCIA</option>
                                <option value="price_asc" class="bg-background text-foreground">PRECIO: MENOR</option>
                                <option value="price_desc" class="bg-background text-foreground">PRECIO: MAYOR</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <main class="px-4 lg:px-8 max-w-7xl mx-auto mt-6">
                <section v-show="categoryBanners.length > 0" class="mb-8 space-y-4">
                    <div v-for="banner in categoryBanners" :key="banner.id" 
                        @click="handleBannerNavigate(banner)"
                        class="relative w-full overflow-hidden rounded-xl cursor-pointer group border border-[#32323b] transition-all duration-150 aspect-[21/9] lg:aspect-[3/1]">
                        <img :src="banner.image_desktop_url" :alt="banner.name"
                            class="w-full h-full object-cover transition-transform duration-500 ease-f1 group-hover:scale-105" />
                        <div class="absolute inset-0 bg-gradient-to-t from-[#15151f]/80 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-150"></div>
                    </div>
                </section>

                <div v-if="props.products === undefined" 
                     class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-3 md:gap-4">
                    <div v-for="i in 12" :key="i" class="product-card h-[320px] skeleton"></div>
                </div>

                <div v-else-if="allProducts.length > 0" 
                     class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-3 md:gap-4">
                    <SkuCard v-for="sku in allProducts" :key="sku.id" :sku="sku" class="section-animate" />
                </div>

                <div v-else-if="allProducts.length === 0 && !isFetching" class="py-40 flex flex-col items-center text-center section-animate">
                    <PackageOpen :size="48" :stroke-width="1.5" class="text-neutral-500 mb-4" />
                    <h2 class="text-xs font-black uppercase tracking-[0.4em] text-neutral-500">Sin Existencias</h2>
                </div>

                <div ref="observerTarget" class="w-full py-12 flex justify-center">
                    <div v-show="isFetching" class="flex flex-col items-center gap-2">
                        <Loader2 class="animate-spin text-primary" :size="24" :stroke-width="2" />
                    </div>
                </div>
            </main>
        </div>
    </ShopLayout>
</template>

<style scoped>
/* ESTRUCTURA DEL TÍTULO F1 */
.header-standard {
    display: flex;
    align-items: flex-end;
    gap: 1rem;
    margin-bottom: 1rem;
}

.title-block-wrapper {
    position: relative;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-grow: 1;
    padding-bottom: 8px;
}

/* Base de la línea: Gris Técnico */
.title-block-wrapper::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 1px;
    background-color: #32323b;
}

/* Acento F1: Rojo puro */
.title-block-wrapper::before {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 60px;
    height: 2px;
    background-color: hsl(var(--primary));
    z-index: 1;
}

select { background: transparent !important; }

/* ANIMACIÓN AFILADA (Curva Mecánica F1) */
.section-animate { 
    animation: reveal 0.25s cubic-bezier(0.16, 1, 0.3, 1) both; 
}

@keyframes reveal { 
    from { opacity: 0; transform: translateY(10px); } 
    to { opacity: 1; transform: translateY(0); } 
}
</style>