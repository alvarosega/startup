<script setup>
import { computed, ref, watch } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { ChevronLeft, PackageOpen, ArrowDownUp, Search } from 'lucide-vue-next';
import ShopLayout from '@/Layouts/ShopLayout.vue';
import CategoryCarousel from '@/Components/Customer/Category/CategoryCarousel.vue';
import RetailBannerSlot from '@/Components/Customer/RetailMedia/RetailBannerSlot.vue';
import SkuCard from '@/Components/Customer/Product/SkuCard.vue';
import debounce from 'lodash/debounce';

const props = defineProps({
    categoryData: Object, // Trae banners, id, name, bg_color
    products: Object, 
    filters: Object
});

const page = usePage();

// --- STATE MANAGEMENT ---
const category = computed(() => props.categoryData?.data || {});
const productsList = computed(() => props.products?.data || []);
const globalCategories = computed(() => page.props.categories_menu || []);

const searchQuery = ref(props.filters?.search || '');
const sortBy = ref(props.filters?.sort || 'relevance');

// --- FILTRADO QUIRÚRGICO ---
const updateServerFilters = debounce(() => {
    router.get(route('customer.shop.category', { category: category.value.slug }), 
        { search: searchQuery.value, sort: sortBy.value }, 
        { 
            preserveState: true, 
            replace: true, 
            preserveScroll: true,
            only: ['products', 'filters'] // Solo refrescamos la grilla
        }
    );
}, 400);

watch([searchQuery, sortBy], () => updateServerFilters());
</script>

<template>
    <ShopLayout>
        <Head :title="category.name" />

        <div class="w-full min-h-screen bg-background pb-32">
            
            <CategoryCarousel 
                :categories="globalCategories" 
                :active-id="category.id" 
            />
            
            <section v-if="category.banners?.length > 0" class="px-4 lg:px-8 mt-4 max-w-7xl mx-auto">
                <RetailBannerSlot :banners="category.banners" />
            </section>

            <div class="px-6 py-12 md:px-12">
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-8 mb-12">
                    <div class="space-y-4">
                        <div class="flex items-center gap-4">
                            <button @click="router.visit(route('customer.shop.index'))" 
                                    class="p-3 hover:bg-muted rounded-2xl transition-all border border-border/50 shadow-sm active:scale-90">
                                <ChevronLeft :size="24" stroke-width="3" />
                            </button>
                            <h1 class="text-5xl md:text-7xl font-black italic uppercase tracking-tighter leading-none transition-colors duration-500"
                                :style="{ color: category.bg_color }">
                                {{ category.name }}
                            </h1>
                        </div>
                        <p class="text-[10px] font-black uppercase tracking-[0.4em] text-muted-foreground ml-16 opacity-60">
                            Radar: {{ productsList.length }} Activos detectados en pasillo
                        </p>
                    </div>

                    <div class="bg-card/40 backdrop-blur-xl border border-border/50 rounded-[2.5rem] p-2 flex flex-col md:flex-row items-center gap-2 shadow-2xl w-full md:w-auto">
                        <div class="relative w-full md:w-80 group">
                            <Search class="absolute left-6 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within:text-primary transition-colors" :size="18" />
                            <input v-model="searchQuery" type="text" placeholder="BUSCAR EN ESTE SILO..."
                                   class="w-full pl-14 pr-6 py-4 bg-background/60 border-none rounded-3xl text-xs font-black uppercase tracking-widest outline-none focus:ring-2 focus:ring-primary/20 transition-all" />
                        </div>
                        <div class="hidden md:block w-px h-8 bg-border"></div>
                        <div class="flex items-center gap-3 px-6 py-2">
                            <ArrowDownUp :size="16" class="text-primary" />
                            <select v-model="sortBy" class="bg-transparent border-none text-[10px] font-black uppercase tracking-widest focus:ring-0 cursor-pointer">
                                <option value="relevance">RELEVANCIA</option>
                                <option value="price_asc">MENOR PRECIO</option>
                                <option value="price_desc">MAYOR PRECIO</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div v-if="productsList.length > 0" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6">
                    <SkuCard v-for="sku in productsList" :key="sku.id" :sku="sku" />
                </div>

                <div v-else class="py-40 text-center">
                    <div class="w-24 h-24 bg-muted rounded-full flex items-center justify-center mx-auto mb-6 opacity-20">
                        <PackageOpen :size="48" />
                    </div>
                    <h2 class="text-2xl font-black uppercase italic tracking-tighter opacity-40">Sin Existencias</h2>
                    <p class="text-muted-foreground text-xs uppercase tracking-widest mt-2">El pasillo está siendo reabastecido o no hay stock en esta sucursal.</p>
                </div>
            </div>
        </div>
    </ShopLayout>
</template>