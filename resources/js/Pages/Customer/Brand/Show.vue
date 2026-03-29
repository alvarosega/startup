<script setup>
import { computed, ref, watch } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { Search, PackageOpen } from 'lucide-vue-next';
import debounce from 'lodash/debounce';

// Layouts & Silos
import ShopLayout from '@/Layouts/ShopLayout.vue';
import BrandCarousel from '@/Components/Customer/Brand/BrandCarousel.vue';
import BrandHeroWidget from '@/Components/Customer/Brand/BrandHeroWidget.vue';
import SkuCard from '@/Components/Customer/Product/SkuCard.vue';

const props = defineProps({
    currentBrand: Object,  // Resource de la marca actual
    brandNav: Object,      // Lista global para el carrusel superior
    brandHero: Object,     // Banner específico (puede ser null)
    products: Object,      // SKUs filtrados
    filters: Object
});

const page = usePage();

// --- DATA BINDING ---
const brand = computed(() => props.currentBrand?.data || {});
const navBrands = computed(() => props.brandNav?.data || []);
const skus = computed(() => props.products?.data || []);
const heroBanner = computed(() => props.brandHero?.data ? [props.brandHero.data] : []);

// --- FILTRADO REACTIVO ---
const searchQuery = ref(props.filters?.search || '');
const sortBy = ref(props.filters?.sort || 'relevance');

const updateFilters = debounce(() => {
    router.get(route('customer.shop.brand.show', { slug: brand.value.slug }), 
        { search: searchQuery.value, sort: sortBy.value }, 
        { preserveState: true, replace: true, preserveScroll: true, only: ['products', 'filters'] }
    );
}, 400);

watch([searchQuery, sortBy], () => updateFilters());
</script>

<template>
    <ShopLayout>
        <Head :title="brand.name" />

        <div class="w-full min-h-screen bg-background pb-32">
            
            <div class="sticky top-16 z-40 bg-background/90 backdrop-blur-xl border-b border-border/10 pb-2">
                <BrandCarousel :brands="navBrands" :active-id="brand.id" />
                
                <div class="px-4 lg:px-8 max-w-7xl mx-auto flex items-center gap-4 mt-2">
                    <div class="flex items-center gap-2 bg-foreground/[0.03] border border-border/40 p-1 rounded-2xl flex-1 md:flex-none">
                        <Search class="ml-3 text-muted-foreground" :size="14" />
                        <input v-model="searchQuery" type="text" placeholder="BUSCAR EN ESTA MARCA..."
                               class="bg-transparent border-none text-[10px] font-black uppercase tracking-widest outline-none focus:ring-0 w-full md:w-64" />
                    </div>
                </div>
            </div>

            <main class="max-w-7xl mx-auto px-4 lg:px-8 mt-6">
                
                <section v-if="heroBanner.length > 0" class="mb-10">
                    <BrandHeroWidget :banners="heroBanner" />
                </section>

                <div class="flex items-center gap-4 mb-8">
                    <div class="w-2 h-10 rounded-full" :style="{ backgroundColor: brand.bg_color || 'var(--primary)' }"></div>
                    <h1 class="text-4xl font-black uppercase tracking-tighter italic">{{ brand.name }}</h1>
                </div>

                <div v-if="skus.length > 0" class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                    <SkuCard v-for="sku in skus" :key="sku.id" :sku="sku" />
                </div>

                <div v-else class="py-40 text-center opacity-30">
                    <PackageOpen :size="48" class="mx-auto mb-4" />
                    <span class="text-[10px] font-black uppercase tracking-widest">Sin productos disponibles</span>
                </div>
            </main>
        </div>
    </ShopLayout>
</template>