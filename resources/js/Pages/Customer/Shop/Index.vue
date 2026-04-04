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
        <div class="w-full min-h-screen pb-2 transition-colors duration-500 overflow-x-hidden relative z-10">
            <div class="absolute inset-0 opacity-[0.03] pointer-events-none bg-noise mix-blend-overlay"></div>
            
            <Head title="Digital Unit | Abastecimiento" />

            <div class="relative z-10 space-y-2 pt-0"> 
                <section v-show="editablePacks.length > 0" class="section-reveal pb-4 pt-0">
                    <div class="px-6 lg:px-8 max-w-7xl mx-auto">
                        <div class="header-standard">
                            <div class="title-block-wrapper">
                                <Sparkles :size="16" class="text-neutral-400" />
                                <h2>Packs</h2>
                            </div>
                            <Link :href="route('customer.index')" class="link-all">
                                Todo <ChevronRight :size="12" />
                            </Link>
                        </div>
                    </div>
                    <div class="w-full">
                        <EditableBundleCarousel :bundles="editablePacks" />
                    </div>
                </section>

                <div class="px-6 lg:px-8 max-w-7xl mx-auto"><div class="micro-divider"></div></div>

                <section v-show="categories.length > 0" class="section-reveal py-4">
                    <div class="px-6 lg:px-8 max-w-7xl mx-auto">
                        <div class="header-standard">
                            <div class="title-block-wrapper">
                                <LayoutGrid :size="16" class="text-neutral-400" />
                                <h2>Categorías</h2>
                            </div>
                        </div>
                        <div class="content-shadow">
                            <CategoryCarousel :categories="categories" />
                        </div>
                    </div>
                </section>

                <div class="px-6 lg:px-8 max-w-7xl mx-auto"><div class="micro-divider"></div></div>

                <section v-show="featuredList.length > 0" class="section-reveal py-4">
                    <div class="px-6 lg:px-8 max-w-7xl mx-auto">
                        <div class="header-standard">
                            <div class="title-block-wrapper">
                                <Star :size="16" class="text-neutral-400" />
                                <h2>Destacados</h2>
                            </div>
                        </div>
                        <div class="content-shadow">
                            <FeaturedProductCarousel :products="featuredList" />
                        </div>
                    </div>
                </section>

                <div class="px-6 lg:px-8 max-w-7xl mx-auto"><div class="micro-divider"></div></div>

                <section v-show="brandAds.length > 0" class="section-reveal py-4">
                    <div class="px-6 lg:px-8 max-w-7xl mx-auto">
                        <div class="header-standard">
                            <div class="title-block-wrapper">
                                <Tag :size="16" class="text-neutral-400" />
                                <h2>Marcas</h2>
                            </div>
                        </div>
                        <div class="content-shadow">
                            <BrandHeroWidget :banners="brandAds" />
                        </div>
                    </div>
                </section>

                <div class="px-6 lg:px-8 max-w-7xl mx-auto"><div class="micro-divider"></div></div>

                <section v-show="bundlesList.length > 0" class="section-reveal py-4">
                    <div class="px-6 lg:px-8 max-w-7xl mx-auto">
                        <div class="header-standard">
                            <div class="title-block-wrapper">
                                <Zap :size="16" class="text-neutral-400" />
                                <h2>Ahorro</h2>
                            </div>
                        </div>
                        <div class="content-shadow">
                            <BundleCarousel :bundles="bundlesList" />
                        </div>
                    </div>
                </section>

                <div v-show="featuredList.length === 0 && bundlesList.length === 0" class="py-40 text-center">
                    <PackageSearch :size="48" class="mx-auto text-neutral-400/20 mb-4" />
                    <h3 class="text-[10px] font-black uppercase tracking-widest text-neutral-500">Radar Despejado</h3>
                    <button @click="router.reload()" class="mt-4 text-[9px] font-bold uppercase text-primary underline underline-offset-4">Re-escaneo</button>
                </div>
                
                <section v-show="favorites.length > 0" class="section-reveal py-4 border-t border-white/5">
                    <div class="px-6 lg:px-8 max-w-7xl mx-auto">
                        <div class="header-standard">
                            <div class="title-block-wrapper">
                                <Bookmark :size="16" class="text-neutral-400" />
                                <h2>Favoritos</h2>
                            </div>
                        </div>
                        <div class="content-shadow">
                            <FavoritesSection :favorites="favorites" />
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </ShopLayout>
</template>

<style scoped>
/* 1. ENCABEZADO PRISMÁTICO ESTRUCTURAL */
.header-standard {
    display: flex;
    align-items: flex-end;
    gap: 1rem;
    padding-left: 0; /* Eliminamos el espacio que ocupaba la barra vertical */
    margin-bottom: 1.5rem;
    /* Eliminados: border-left y border-image */
}

/* 2. BLOQUE DINÁMICO DEL TÍTULO */
.title-block-wrapper {
    position: relative;
    display: flex;
    align-items: center;
    gap: 0.85rem;
    flex-grow: 1; /* Expande el subrayado a todo el ancho disponible */
    padding-bottom: 8px; /* Espacio exacto para el subrayado */
}

.title-block-wrapper h2 {
    font-size: 10px;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: 0.4em;
    color: hsl(var(--foreground));
}

/* 3. SUBRAYADO ARCOÍRIS ÚNICO Y TOTAL */
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

/* 4. ENLACE "TODO" */
.link-all {
    padding-bottom: 8px;
    font-size: 9px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: hsl(var(--neutral-400));
    display: flex;
    align-items: center;
    gap: 4px;
    transition: all 0.3s ease;
    white-space: nowrap;
}

.link-all:hover {
    color: hsl(var(--primary));
    transform: translateX(4px);
}

/* 5. PURGA DE SOMBRAS Y CONTENEDORES */
.content-shadow {
    box-shadow: none !important;
    background: transparent !important;
    border: none !important;
}

/* 6. DIVISOR INDUSTRIAL */
.micro-divider {
    width: 100%;
    height: 1px;
    background: linear-gradient(to right, 
        transparent 0%, 
        rgba(255,255,255,0.08) 50%, 
        transparent 100%
    );
    margin: 1rem 0;
}

/* 7. FONDO METÁLICO */
.bg-metallic-matte {
    background-color: #f8f8f8;
    background-image: linear-gradient(135deg, rgba(255,255,255,0.4) 0%, transparent 100%);
}

.dark .bg-metallic-matte {
    background-color: #080808;
    background-image: radial-gradient(circle at 50% -20%, hsla(24, 95%, 55%, 0.05) 0%, transparent 50%);
}

.bg-noise {
    background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3BaseFilter id='noiseFilter'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.65' numOctaves='3' stitchTiles='stitch'/%3E%3C/feFilter%3E%3Crect width='100%25' height='100%25' filter='url(%23noiseFilter)'/%3E%3C/svg%3E");
}

.section-reveal {
    animation: reveal 0.6s cubic-bezier(0.32, 0.72, 0, 1) both;
}

@keyframes reveal {
    from { opacity: 0; transform: translateY(15px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>