<script setup>
import { computed, ref, watch } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { Search, PackageOpen, LayoutGrid, Zap } from 'lucide-vue-next';
import debounce from 'lodash/debounce';

// Layouts & Components
import ShopLayout from '@/Layouts/ShopLayout.vue';
import BrandCarousel from '@/Components/Customer/Brand/BrandCarousel.vue';
import BrandHeroWidget from '@/Components/Customer/Brand/BrandHeroWidget.vue';
import SkuCard from '@/Components/Customer/Product/SkuCard.vue';

const props = defineProps({
    currentBrand: Object,
    brandNav: Object,
    brandHero: Object,
    products: Object,
    filters: Object,
    loading: { type: Boolean, default: false }
});

const brand = computed(() => props.currentBrand?.data || {});
const navBrands = computed(() => props.brandNav?.data || []);
const skus = computed(() => props.products?.data || []);
const heroBanner = computed(() => props.brandHero?.data ? [props.brandHero.data] : []);

// --- FILTRADO REACTIVO ---
const searchQuery = ref(props.filters?.search || '');
const sortBy = ref(props.filters?.sort || 'relevance');

const updateFilters = debounce(() => {
    router.get(route('customer.brand.show', { slug: brand.value.slug }), 
        { search: searchQuery.value, sort: sortBy.value }, 
        { 
            preserveState: true, 
            replace: true, 
            preserveScroll: true, 
            only: ['products', 'filters'] 
        }
    );
}, 400);

watch([searchQuery, sortBy], () => updateFilters());
</script>

<template>
    <ShopLayout>
        <Head :title="brand.name" />

        <div class="w-full min-h-screen bg-transparent pb-32 relative z-10">
            
            <div class="sticky top-[72px] lg:top-[80px] z-40 glass-titanium border-b border-white/5 pb-4 transition-all duration-500">
                <div class="max-w-7xl mx-auto pt-0">
                    <BrandCarousel :brands="navBrands" :active-id="brand.id" />
                    
                    <div class="px-6 lg:px-8 mt-4">
                        <div class="flex items-center gap-2 bg-foreground/[0.05] border border-border/40 p-1 rounded-2xl w-full md:w-max backdrop-blur-md">
                            <Search class="ml-3 text-primary" :size="14" />
                            <input v-model="searchQuery" type="text" placeholder="FILTRAR EN ESTA MARCA..."
                                class="bg-transparent border-none text-xs font-black uppercase tracking-widest text-black dark:text-white outline-none focus:ring-0 w-full md:w-64 placeholder:text-foreground/40" />

                        </div>
                    </div>
                </div>
            </div>

            <main class="max-w-7xl mx-auto px-6 lg:px-8 mt-10">
                
                <section v-if="heroBanner.length > 0" class="mb-12 section-reveal">
                    <BrandHeroWidget :banners="heroBanner" />
                </section>

                <header class="mb-12 section-reveal" :style="{ '--brand-color': brand.bg_color || 'var(--primary)' }">
                    <div class="header-standard">
                        <div class="title-block-wrapper">
                            <div class="w-2 h-8 rounded-full bg-[var(--brand-color)] shadow-[0_0_15px_var(--brand-color)]"></div>
                            <h1 class="text-3xl md:text-5xl font-black uppercase tracking-tighter italic text-black dark:text-white">
                                {{ brand.name }}
                            </h1>
                        </div>
                    </div>
                </header>

                <div v-if="props.products === undefined" 
                    class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-4 md:gap-6">
                    <div v-for="n in 12" :key="n" class="product-card h-[400px] skeleton"></div>
                </div>

                <div v-else-if="skus.length > 0" 
                    class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-4 md:gap-6">
                    <SkuCard v-for="sku in skus" :key="sku.id" :sku="sku" class="section-reveal" />
                </div>

                <div v-else class="py-40 text-center opacity-30 flex flex-col items-center">
                    <PackageOpen :size="64" stroke-width="1" class="text-foreground mb-4" />
                    <span class="text-xs font-black uppercase tracking-[0.3em]">Radar Despejado en {{ brand.name }}</span>
                </div>
            </main>
        </div>
    </ShopLayout>
</template>

<style scoped>
/* REPETIMOS EL ESTÁNDAR PRISMÁTICO */
.header-standard {
    display: flex;
    align-items: flex-end;
    gap: 1rem;
}

.title-block-wrapper {
    position: relative;
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-grow: 1;
    padding-bottom: 12px;
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

/* GLASS TITANIUM PARA EL STICKY */
.glass-titanium {
    background-color: hsl(var(--background) / 0.6);
    backdrop-filter: blur(20px) saturate(180%);
    -webkit-backdrop-filter: blur(20px) saturate(180%);
}

.dark .glass-titanium {
    background-color: hsl(var(--background) / 0.8);
}

/* SKELETONS */
.glass-chassis-skeleton {
    background: rgba(255, 255, 255, 0.05);
    backdrop-filter: blur(40px);
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.section-reveal {
    animation: reveal 0.8s cubic-bezier(0.32, 0.72, 0, 1) both;
}

@keyframes reveal {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>