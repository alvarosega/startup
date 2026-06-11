<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import { Head, usePage, Link, router } from '@inertiajs/vue3'; 
import { 
    ChevronRight, Sparkles, Tag, Star, 
    LayoutGrid, Zap, Bookmark, PackageSearch
} from 'lucide-vue-next';

import ShopLayout from '@/Layouts/ShopLayout.vue';
import CategoryCarousel from '@/Components/Customer/Category/CategoryCarousel.vue';
import FeaturedProductCarousel from '@/Components/Customer/Featured/FeaturedProductCarousel.vue'; 
import BrandHeroWidget from '@/Components/Customer/Brand/BrandHeroWidget.vue';
import BundleCarousel from '@/Components/Customer/Bundle/BundleCarousel.vue';
import FavoritesSection from '@/Components/Customer/Favorites/FavoriteCarousel.vue';
import EditableBundleCarousel from '@/Components/Customer/Bundle/EditableBundleCarousel.vue';

const props = defineProps({ 
    featuredProducts: Object,
    brandBanners: Object,
    bundleBanners: Object, 
    templateBundles: Object, 
    atomicBundles: Object,
    favorites: Array,
});
const page = usePage();

const categories = computed(() => {
    const menu = page.props.categories_menu;
    return menu?.data ? menu.data : (Array.isArray(menu) ? menu : []);
});
const isMounted = ref(false);
onMounted(() => {
    isMounted.value = true;
});
const featuredList = computed(() => props.featuredProducts?.data || []); 
const brandAds = computed(() => props.brandBanners?.data || []);
const bundlesList = computed(() => props.atomicBundles?.data || []);
const editablePacks = computed(() => props.templateBundles?.data || []);

watch(() => props.brandBanners, (newBanners) => {
    console.group('▼ DEFER: BANNERS DE MARCA RECIBIDOS');
    console.log('Estado:', newBanners === undefined ? 'PENDING' : 'RESOLVED');
    console.groupEnd();
}, { deep: true, immediate: true });

watch(() => props.featuredProducts, (newFeatured) => {
    console.group('▼ DEFER: PRODUCTOS DESTACADOS RECIBIDOS');
    console.log('Cantidad:', newFeatured?.data?.length || newFeatured?.length || 0);
    console.groupEnd();
}, { deep: true, immediate: true });
</script>

<template>
    <ShopLayout>
        <div class="w-full min-h-screen pb-20 relative z-10">
            
            <Head title="Digital Unit | Abastecimiento" />

            <div class="relative space-y-10 pt-4"> 
                
                <section v-show="editablePacks.length > 0 || !isMounted" class="section-reveal">
                    <div class="px-4 lg:px-8 max-w-7xl mx-auto">
                        <div class="header-standard">
                            <div class="title-block-wrapper">
                                <Sparkles :size="14" class="text-neutral-500" />
                                <h2>Packs</h2>
                            </div>
                            <Link :href="route('customer.index')" class="link-all group">
                                Todo <ChevronRight :size="12" class="group-hover:translate-x-1 transition-transform duration-150 ease-f1" />
                            </Link>
                        </div>
                        <div class="w-full">
                            <EditableBundleCarousel :bundles="editablePacks" :loading="props.templateBundles === undefined" />
                        </div>
                    </div>
                </section>

                <section v-show="categories.length > 0 || !isMounted" class="section-reveal" style="animation-delay: 50ms;">
                    <div class="px-4 lg:px-8 max-w-7xl mx-auto">
                        <div class="header-standard">
                            <div class="title-block-wrapper">
                                <LayoutGrid :size="14" class="text-neutral-500" />
                                <h2>Categorías</h2>
                            </div>
                        </div>
                        <div class="w-full">
                            <CategoryCarousel :categories="categories" :loading="page.props.categories_menu === undefined" />
                        </div>
                    </div>
                </section>

                <section v-show="featuredList.length > 0 || !isMounted" class="section-reveal" style="animation-delay: 100ms;">
                    <div class="px-4 lg:px-8 max-w-7xl mx-auto">
                        <div class="header-standard">
                            <div class="title-block-wrapper">
                                <Star :size="14" class="text-neutral-500" />
                                <h2>Destacados</h2>
                            </div>
                        </div>
                        <div class="w-full">
                            <FeaturedProductCarousel :products="featuredList" :loading="props.featuredProducts === undefined" />
                        </div>
                    </div>
                </section>

                <section v-show="props.brandBanners === undefined || brandAds.length > 0" class="section-reveal" style="animation-delay: 150ms;">
                    <div class="px-4 lg:px-8 max-w-7xl mx-auto">
                        <div class="header-standard">
                            <div class="title-block-wrapper">
                                <Tag :size="14" class="text-neutral-500" />
                                <h2>Marcas</h2>
                            </div>
                        </div>
                        <div class="w-full">
                            <BrandHeroWidget :banners="brandAds" :loading="props.brandBanners === undefined" />
                        </div>
                    </div>
                </section>

                <section v-if="isMounted" class="section-reveal py-4" style="animation-delay: 200ms;">
                    <div class="px-4 lg:px-8 max-w-7xl mx-auto">
                        <FavoritesSection :favorites="favorites" />
                    </div>
                </section>

            </div>
        </div>
    </ShopLayout>
</template>

<style scoped>
.header-standard {
    display: flex;
    align-items: baseline; 
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 1rem;
}

.title-block-wrapper {
    position: relative;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex: 1; 
    min-width: 0; 
    padding-bottom: 8px;
}

.title-block-wrapper h2 {
    font-size: 14px;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: 0.25em;
    color: theme('colors.foreground');
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Subrayado técnico sólido (rojo F1 o neutral) sin neones */
.title-block-wrapper::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 1px;
    background-color: theme('colors.border');
}

.title-block-wrapper::before {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 40px;
    height: 2px;
    background-color: hsl(var(--primary));
    z-index: 1;
}

.link-all {
    flex-shrink: 0; 
    font-size: 10px;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: 0.2em;
    color: theme('colors.neutral.500');
    display: flex;
    align-items: center;
    gap: 4px;
    transition: color 0.15s cubic-bezier(0.16, 1, 0.3, 1);
}

.link-all:hover {
    color: hsl(var(--primary));
}

/* Animación rápida y mecánica */
.section-reveal {
    animation: reveal 0.3s cubic-bezier(0.16, 1, 0.3, 1) both;
}

@keyframes reveal {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>