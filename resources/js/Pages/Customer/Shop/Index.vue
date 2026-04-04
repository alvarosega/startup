<script setup>
import { computed } from 'vue';
import { Head, usePage, Link, router } from '@inertiajs/vue3'; 
import { 
    ChevronRight, Sparkles, Tag, Star, 
    LayoutGrid, Zap, Bookmark, PackageSearch 
} from 'lucide-vue-next';

// Layout y Componentes
import ShopLayout from '@/Layouts/ShopLayout.vue';
import CategoryCarousel from '@/Components/Customer/Category/CategoryCarousel.vue';
import FeaturedProductCarousel from '@/Components/Customer/Featured/FeaturedProductCarousel.vue'; 
import BrandHeroWidget from '@/Components/Customer/Brand/BrandHeroWidget.vue';
import BundleCarousel from '@/Components/Customer/Bundle/BundleCarousel.vue';
import FavoritesSection from '@/Components/Customer/Favorites/FavoriteCarousel.vue';
import EditableBundleCarousel from '@/Components/Customer/Bundle/EditableBundleCarousel.vue';

const props = defineProps({ 
    featuredProducts: { type: Object, default: () => ({ data: [] }) },
    brandBanners: { type: Object, default: () => ({ data: [] }) },
    bundleBanners: { type: Object, default: () => ({ data: [] }) }, 
    templateBundles: { type: Object, default: () => ({ data: [] }) }, 
    bundlesData: { type: Object, default: () => ({ data: [] }) },
    favorites: { type: Array, default: () => [] },
});

const page = usePage();

const categories = computed(() => {
    const menu = page.props.categories_menu;
    return menu?.data ? menu.data : (Array.isArray(menu) ? menu : []);
});

const featuredList = computed(() => props.featuredProducts?.data || []); 
const brandAds = computed(() => props.brandBanners?.data || []);
const bundlesList = computed(() => Array.isArray(props.bundlesData) ? props.bundlesData : props.bundlesData?.data || []);
const editablePacks = computed(() => props.templateBundles?.data || []);
</script>

<template>
    <ShopLayout>
        <div class="w-full min-h-screen pb-24 transition-colors duration-500 overflow-x-hidden relative z-10">
            
            <Head title="Digital Unit | Abastecimiento" />

            <div class="relative space-y-8 pt-0"> 
                
                <section v-show="editablePacks.length > 0 || !isMounted" class="section-reveal">
                    <div class="px-6 lg:px-8 max-w-7xl mx-auto">
                        <div class="header-standard">
                            <div class="title-block-wrapper">
                                <Sparkles :size="16" class="text-black dark:text-white/60" />
                                <h2>Packs</h2>
                            </div>
                            <Link :href="route('customer.index')" class="link-all">
                                Todo <ChevronRight :size="12" />
                            </Link>
                        </div>
                    </div>
                    <div class="w-full">
                        <EditableBundleCarousel :bundles="editablePacks" :loading="editablePacks.length === 0" />
                    </div>
                </section>

                <section v-show="categories.length > 0 || !isMounted" class="section-reveal">
                    <div class="px-6 lg:px-8 max-w-7xl mx-auto">
                        <div class="header-standard">
                            <div class="title-block-wrapper">
                                <LayoutGrid :size="16" class="text-black dark:text-white/60" />
                                <h2>Categorías</h2>
                            </div>
                        </div>
                        <div class="content-shadow">
                            <CategoryCarousel :categories="categories" :loading="categories.length === 0" />
                        </div>
                    </div>
                </section>

                <section v-show="featuredList.length > 0 || !isMounted" class="section-reveal">
                    <div class="px-6 lg:px-8 max-w-7xl mx-auto">
                        <div class="header-standard">
                            <div class="title-block-wrapper">
                                <Star :size="16" class="text-black dark:text-white/60" />
                                <h2>Destacados</h2>
                            </div>
                        </div>
                        <div class="content-shadow">
                            <FeaturedProductCarousel :products="featuredList" :loading="featuredList.length === 0" />
                        </div>
                    </div>
                </section>

                <section v-show="brandAds.length > 0 || !isMounted" class="section-reveal">
                    <div class="px-6 lg:px-8 max-w-7xl mx-auto">
                        <div class="header-standard">
                            <div class="title-block-wrapper">
                                <Tag :size="16" class="text-black dark:text-white/60" />
                                <h2>Marcas</h2>
                            </div>
                        </div>
                        <div class="content-shadow">
                            <BrandHeroWidget :banners="brandAds" :loading="brandAds.length === 0" />
                        </div>
                    </div>
                </section>

                <section v-show="bundlesList.length > 0 || !isMounted" class="section-reveal">
                    <div class="px-6 lg:px-8 max-w-7xl mx-auto">
                        <div class="header-standard">
                            <div class="title-block-wrapper">
                                <Zap :size="16" class="text-black dark:text-white/60" />
                                <h2>Ahorro</h2>
                            </div>
                        </div>
                        <div class="content-shadow">
                            <BundleCarousel :bundles="bundlesList" :loading="bundlesList.length === 0" />
                        </div>
                    </div>
                </section>

                </div>
        </div>
    </ShopLayout>
</template>

<style scoped>
/* 1. ENCABEZADO PRISMÁTICO (Estandarizado) */
.header-standard {
    display: flex;
    align-items: flex-end;
    gap: 1rem;
    margin-bottom: 2rem; /* Espaciado técnico entre título y contenido */
}

/* 2. TÍTULO Y SUBRAYADO ARCOÍRIS */
.title-block-wrapper {
    position: relative;
    display: flex;
    align-items: center;
    gap: 0.85rem;
    flex-grow: 1;
    padding-bottom: 8px;
}

.title-block-wrapper h2 {
    font-size: 11px;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: 0.4em;
    /* Contraste Crudo: Negro en claro, blanco en oscuro */
    color: theme('colors.black'); 
}

.dark .title-block-wrapper h2 {
    color: theme('colors.white');
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

/* 3. ENLACE "TODO" (Hardware Premium) */
.link-all {
    padding-bottom: 8px;
    font-size: 10px;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: 0.15em;
    color: theme('colors.black');
    display: flex;
    align-items: center;
    gap: 4px;
    transition: all 0.3s ease;
}

.dark .link-all {
    color: theme('colors.neutral.400');
}

.link-all:hover {
    color: hsl(var(--primary));
    transform: translateX(4px);
}

/* 4. ANIMACIÓN DE REVELADO */
.section-reveal {
    animation: reveal 0.8s cubic-bezier(0.32, 0.72, 0, 1) both;
}

@keyframes reveal {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

/* ELIMINADOS: micro-divider, bg-metallic-matte, bg-noise */
</style>