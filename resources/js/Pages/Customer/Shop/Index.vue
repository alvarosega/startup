<script setup>
import { computed } from 'vue';
import { Head, usePage, Link } from '@inertiajs/vue3'; 
import { ChevronRight, PackageSearch } from 'lucide-vue-next';

import ShopLayout from '@/Layouts/ShopLayout.vue';
import CategoryCarousel from '@/Components/Customer/Category/CategoryCarousel.vue';
import FeaturedProductCarousel from '@/Components/Customer/Featured/FeaturedProductCarousel.vue'; 
import BrandHeroWidget from '@/Components/Customer/Brand/BrandHeroWidget.vue';
import BundleCarousel from '@/Components/Customer/Bundle/BundleCarousel.vue';
import SkuCard from '@/Components/Customer/Product/SkuCard.vue';
import FavoritesSection from '@/Components/Customer/Favorites/FavoriteCarousel.vue';
import EditableBundleCarousel from '@/Components/Customer/Bundle/EditableBundleCarousel.vue';

const props = defineProps({ 
    featuredProducts: { type: Object, default: () => ({ data: [] }) },
    brandBanners: { type: Object, default: () => ({ data: [] }) },
    zonesData: { type: Array, default: () => [] },
    bundlesData: { type: Object, default: () => ({ data: [] }) },
    favorites: { type: Array, default: () => [] },
    bundleBanners: { type: Object, default: () => ({ data: [] }) }, // Banners de Retail Media
    templateBundles: { type: Object, default: () => ({ data: [] }) }, // NUEVA PROP SEGMENTADA
    atomicBundles: { type: Object, default: () => ({ data: [] }) },
});

const page = usePage();

const categories = computed(() => {
    const menu = page.props.categories_menu;
    return menu?.data ? menu.data : (Array.isArray(menu) ? menu : []);
});
const featuredList = computed(() => props.featuredProducts?.data || []); 
const brandAds = computed(() => props.brandBanners?.data || []);
const promoAds = computed(() => props.bundleBanners?.data || []);
const bundlesList = computed(() => Array.isArray(props.bundlesData) ? props.bundlesData : props.bundlesData?.data || []);
const editablePacks = computed(() => props.templateBundles?.data || []);
</script>

<template>
    <ShopLayout>
        <div class="w-full min-h-screen bg-background pb-32 transition-colors duration-500">
            
            <Head title="CyberMarket | High-Density Retail" />
            <section v-show="editablePacks.length > 0" class="mt-12 mb-16">
                <div v-if="promoAds.length > 0" class="px-4 lg:px-8 mb-8">
                    <div class="max-w-7xl mx-auto rounded-[3rem] overflow-hidden shadow-2xl">
                        <HeroBannerSlider :banners="promoAds" />
                    </div>
                </div>

                <div class="px-6 lg:px-8 max-w-7xl mx-auto mb-6">
                    <div class="flex items-center justify-between">
                        <h2 class="text-[10px] font-black uppercase tracking-[0.4em] text-primary flex items-center gap-2">
                            <Sparkles :size="14" class="text-accent animate-pulse" />
                            Packs Editables / Arma tu Pedido
                        </h2>
                        <Link :href="route('customer.index')" class="text-[9px] font-bold uppercase tracking-widest text-foreground/40 hover:text-primary transition-colors flex items-center gap-1">
                            Ver todos <ChevronRight :size="12" />
                        </Link>
                    </div>
                </div>

                <EditableBundleCarousel :bundles="editablePacks" />
            </section>
            <CategoryCarousel 
                v-show="categories.length > 0"
                :categories="categories" 
                class="mt-4"
            />

            <section v-show="featuredList.length > 0" class="mt-2">
                <FeaturedProductCarousel :products="featuredList" />
            </section>

            <section v-show="brandAds.length > 0" class="px-4 lg:px-8 mt-6">
                <div class="max-w-7xl mx-auto">
                    <BrandHeroWidget :banners="brandAds" />
                </div>
            </section>

            <section v-show="bundlesList.length > 0" class="mt-12 mb-16">
                <div class="px-6 lg:px-8 max-w-7xl mx-auto mb-6">
                    <h2 class="text-[10px] font-black uppercase tracking-[0.4em] text-primary flex items-center gap-2">
                        <span class="w-2 h-2 bg-primary rounded-full animate-ping"></span>
                        Opciones de Ahorro / Packs
                    </h2>
                </div>
                <BundleCarousel :bundles="bundlesList" />
            </section>

            <div v-show="featuredList.length === 0 && bundlesList.length === 0" class="py-40 text-center">
                <PackageSearch :size="48" class="mx-auto text-muted-foreground/20 mb-4" />
                <h3 class="text-xs font-black uppercase tracking-widest text-muted-foreground italic">Radar Despejado</h3>
                <p class="text-[10px] text-muted-foreground/60 mt-2">No hay existencias activas para el mercado seleccionado.</p>
            </div>
            
            <FavoritesSection :favorites="favorites" />
        </div>
    </ShopLayout>
</template>

<style scoped>
.ease-ios { transition-timing-function: cubic-bezier(0.32, 0.72, 0, 1); }
.section-animate { animation: reveal 0.8s cubic-bezier(0.32, 0.72, 0, 1) both; }
@keyframes reveal {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
.bg-background { will-change: background-color; }
.dark .bg-background {
    background-image: radial-gradient(circle at 50% -10%, rgba(225, 6, 0, 0.03) 0%, transparent 40%);
}
</style>