<script setup>
import { computed } from 'vue';
// ERROR CORREGIDO: Se añade Link al import
import { Head, router, usePage, Link } from '@inertiajs/vue3'; 

// Layouts & Global Components
import ShopLayout from '@/Layouts/ShopLayout.vue';
import HeroCarousel from '@/Components/Shop/HeroCarousel.vue';
import CategoryCarousel from '@/Components/Customer/Category/CategoryCarousel.vue';
import BundlePromoModal from '@/Components/Customer/Bundle/BundlePromoModal.vue';
import BundleCarousel from '@/Components/Customer/Bundle/BundleCarousel.vue';
import SkuCard from '@/Components/Customer/Product/SkuCard.vue';

const props = defineProps({ 
    heroBanners: { type: Object, default: () => ({ data: [] }) },
    bundleBanners: { type: Object, default: () => ({ data: [] }) },
    zonesData: { type: Array, default: () => [] },
    bundlesData: { type: Array, default: () => [] }
});

const page = usePage();

const categories = computed(() => page.props.categories_menu || []);
const banners = computed(() => props.heroBanners?.data || []);
const promoBanners = computed(() => props.bundleBanners?.data || []);

const handleOpenBundle = (slug) => {
    router.visit(route('customer.shop.bundle', { slug: slug }));
};
</script>

<template>
    <ShopLayout>
        <Head title="CyberMarket | Supermercado Digital" />

        <div class="w-full min-h-screen bg-background pb-32 overflow-x-hidden">
            
            <CategoryCarousel 
                v-if="categories.length > 0"
                :categories="categories" 
            />

            <HeroCarousel 
                v-if="banners.length > 0"
                :banners="banners"
                @open-bundle="handleOpenBundle"
            />

            <BundlePromoModal :banners="promoBanners" />

            <BundleCarousel 
                v-if="bundlesData.length > 0"
                :bundles="bundlesData" 
            />

            <section v-for="zone in zonesData" :key="zone.id" class="px-6 md:px-12 py-10">
                <div class="flex items-baseline gap-4 mb-8">
                    <h2 class="text-3xl font-black uppercase italic tracking-tighter text-foreground">
                        {{ zone.name }}
                    </h2>
                    <div class="h-px flex-1 bg-border/50"></div>
                    <Link :href="route('customer.shop.category', zone.slug)" 
                          class="text-[9px] font-black uppercase tracking-widest text-muted-foreground hover:text-primary transition-colors">
                        Ver Selección
                    </Link>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6">
                    <SkuCard 
                        v-for="sku in zone.skus" 
                        :key="sku.id" 
                        :sku="sku" 
                    />
                </div>
            </section>

            <div v-if="banners.length === 0 && categories.length === 0" 
                 class="flex flex-col items-center justify-center p-32 text-center">
                <div class="w-12 h-12 border-4 border-primary/20 border-t-primary rounded-full animate-spin mb-6"></div>
                <h2 class="text-lg font-black uppercase italic tracking-tighter text-foreground mb-2">
                    Iniciando Sistemas
                </h2>
                <p class="text-muted-foreground font-medium italic max-w-xs mx-auto text-xs uppercase tracking-widest leading-relaxed">
                    Sincronizando stock y sucursales disponibles en tiempo real...
                </p>
            </div>
            
        </div>
    </ShopLayout>
</template>

<style scoped>
.overflow-x-hidden {
    -webkit-overflow-scrolling: touch;
}

/* Animaciones de entrada por secciones para suavizar la carga de datos */
section {
    animation: fadeInUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) both;
}

@keyframes fadeInUp {
    from { 
        opacity: 0; 
        transform: translateY(30px); 
    }
    to { 
        opacity: 1; 
        transform: translateY(0); 
    }
}
</style>