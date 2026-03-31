<script setup>
import { computed } from 'vue';
import { Head, usePage, Link } from '@inertiajs/vue3'; 
import { ChevronRight, PackageSearch } from 'lucide-vue-next';

// Layouts & Silos de Componentes
import ShopLayout from '@/Layouts/ShopLayout.vue';
import CategoryCarousel from '@/Components/Customer/Category/CategoryCarousel.vue';
import FeaturedProductCarousel from '@/Components/Customer/Featured/FeaturedProductCarousel.vue'; // NUEVO
import BrandHeroWidget from '@/Components/Customer/Brand/BrandHeroWidget.vue';
import BundleCarousel from '@/Components/Customer/Bundle/BundleCarousel.vue';
import SkuCard from '@/Components/Customer/Product/SkuCard.vue';
import FavoritesSection from '@/Components/Customer/Favorites/FavoriteCarousel.vue';



const props = defineProps({ 
    // NUEVO: Silo de Productos Destacados (Top 5)
    featuredProducts: { type: Object, default: () => ({ data: [] }) },

    // Silo de Media: Banners de Marca
    brandBanners: { type: Object, default: () => ({ data: [] }) },
    
    // Silo de Media: Banners de Packs/Bundles
    bundleBanners: { type: Object, default: () => ({ data: [] }) },
    
    // Silo de Catálogo: Zonas y Productos
    zonesData: { type: Array, default: () => [] },
    
    // Silo de Catálogo: Listado de Packs
    bundlesData: { type: Object, default: () => ({ data: [] }) },
    favorites: { type: Array, default: () => [] },
});

const page = usePage();

// --- RESOLUCIÓN DE DATA (RESILIENTE) ---
const categories = computed(() => page.props.categories_menu || []);
const featuredList = computed(() => props.featuredProducts?.data || []); // RESOLUCIÓN NUEVA
const brandAds = computed(() => props.brandBanners?.data || []);
const promoAds = computed(() => props.bundleBanners?.data || []);
const bundlesList = computed(() => Array.isArray(props.bundlesData) ? props.bundlesData : props.bundlesData?.data || []);
</script>

<template>
    <ShopLayout>
        <Head title="CyberMarket | High-Density Retail" />

        <BundlePromoModal :banners="promoAds" />

        <div class="w-full min-h-screen bg-background pb-32 transition-colors duration-500">
            
            <CategoryCarousel 
                v-if="categories.length > 0"
                :categories="categories" 
                class="mt-4"
            />

            <section v-if="featuredList.length > 0" class="mt-2">
                <FeaturedProductCarousel :products="featuredList" />
            </section>

            <section v-if="brandAds.length > 0" class="px-4 lg:px-8 mt-6">
                <div class="max-w-7xl mx-auto">
                    <BrandHeroWidget :banners="brandAds" />
                </div>
            </section>

            <section v-if="bundlesList.length > 0" class="mt-12 mb-16">
                <div class="px-6 lg:px-8 max-w-7xl mx-auto mb-6">
                    <h2 class="text-[10px] font-black uppercase tracking-[0.4em] text-primary flex items-center gap-2">
                        <span class="w-2 h-2 bg-primary rounded-full animate-ping"></span>
                        Opciones de Ahorro / Packs
                    </h2>
                </div>
                <BundleCarousel :bundles="bundlesList" />
            </section>

            <div v-else class="py-40 text-center">
                <PackageSearch :size="48" class="mx-auto text-muted-foreground/20 mb-4" />
                <h3 class="text-xs font-black uppercase tracking-widest text-muted-foreground italic">Radar Despejado</h3>
                <p class="text-[10px] text-muted-foreground/60 mt-2">No hay existencias activas para el mercado seleccionado.</p>
            </div>
            <FavoritesSection :favorites="favorites" />
        </div>
    </ShopLayout>
</template>

<style scoped>
/* Estilos existentes preservados */
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