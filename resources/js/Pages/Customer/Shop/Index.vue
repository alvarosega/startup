<script setup>
import { computed } from 'vue';
import { Head, usePage, Link } from '@inertiajs/vue3'; 
import { ChevronRight, PackageSearch } from 'lucide-vue-next';

// Layouts & Silos de Componentes
import ShopLayout from '@/Layouts/ShopLayout.vue';
import CategoryCarousel from '@/Components/Customer/Category/CategoryCarousel.vue';
import RetailBannerSlot from '@/Components/Customer/RetailMedia/RetailBannerSlot.vue';
import BundlePromoModal from '@/Components/Customer/Bundle/BundlePromoModal.vue';
import BundleCarousel from '@/Components/Customer/Bundle/BundleCarousel.vue';
import SkuCard from '@/Components/Customer/Product/SkuCard.vue';

const props = defineProps({ 
    // Silos Publicitarios (Retail Media)
    heroBanners: { type: Object, default: () => ({ data: [] }) },
    bundleBanners: { type: Object, default: () => ({ data: [] }) },
    
    // Silos de Catálogo (Data Atómica)
    zonesData: { type: Array, default: () => [] },
    bundlesData: { type: [Array, Object], default: () => [] }
});

const page = usePage();

// --- RESOLUCIÓN DE DATA ---
const categories = computed(() => page.props.categories_menu || []);
const heroAds = computed(() => props.heroBanners?.data || []);
const promoAds = computed(() => props.bundleBanners?.data || []);
const bundlesList = computed(() => Array.isArray(props.bundlesData) ? props.bundlesData : props.bundlesData?.data || []);

// Estado de hidratación
const isLoading = computed(() => props.zonesData.length === 0);
</script>

<template>
    <ShopLayout>
        <Head title="CyberMarket | High-Density Retail" />

        <div class="w-full min-h-screen bg-background pb-32 transition-colors duration-500">
            
            <CategoryCarousel 
                v-if="categories.length > 0"
                :categories="categories" 
                class="mt-4"
            />

            <section class="px-4 lg:px-8 mt-6">
                <div class="max-w-7xl mx-auto">
                    <RetailBannerSlot 
                        v-if="heroAds.length > 0"
                        :banners="heroAds" 
                    />
                    <div v-else class="w-full aspect-[21/9] lg:aspect-[3/1] bg-muted rounded-[2rem] animate-pulse opacity-20"></div>
                </div>
            </section>

            <BundlePromoModal :banners="promoAds" />

            <section v-if="bundlesList.length > 0" class="mt-12">
                <div class="px-6 lg:px-8 max-w-7xl mx-auto mb-6">
                    <h2 class="text-[10px] font-black uppercase tracking-[0.4em] text-primary flex items-center gap-2">
                        <span class="w-2 h-2 bg-primary rounded-full animate-ping"></span>
                        Opciones de Ahorro / Packs
                    </h2>
                </div>
                <BundleCarousel :bundles="bundlesList" />
            </section>

            <div v-if="!isLoading" class="space-y-16 mt-16">
                <section v-for="zone in zonesData" :key="zone.id" class="px-4 lg:px-8 max-w-7xl mx-auto section-animate">
                    
                    <div class="flex items-end justify-between gap-4 mb-8 group">
                        <div class="flex flex-col">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-primary shadow-f1-glow"></span>
                                <span class="text-[9px] font-black uppercase tracking-[0.3em] text-primary/70 font-mono">
                                    [ ZONE_SEC: {{ zone.slug.toUpperCase() }} ]
                                </span>
                            </div>
                            <h2 class="text-3xl md:text-4xl font-black uppercase tracking-tighter text-foreground leading-none">
                                {{ zone.name }}
                            </h2>
                        </div>
                        
                        <div class="hidden md:block h-px flex-1 bg-border/20 mb-2 relative overflow-hidden">
                            <div class="absolute inset-0 bg-gradient-to-r from-transparent via-primary/30 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-1000 ease-ios"></div>
                        </div>

                        <Link :href="route('customer.shop.category', { category: zone.slug })" 
                              class="group/link flex items-center gap-3 px-5 py-2.5 rounded-full bg-foreground/[0.03] hover:bg-primary transition-all duration-500 ease-ios">
                            <span class="text-[10px] font-black uppercase tracking-widest text-foreground group-hover/link:text-white">
                                EXPLORAR
                            </span>
                            <div class="w-5 h-5 rounded-full bg-foreground/10 flex items-center justify-center group-hover/link:bg-white/20">
                                <ChevronRight :size="12" class="group-hover/link:text-white transition-colors" />
                            </div>
                        </Link>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-4 md:gap-6">
                        <SkuCard 
                            v-for="sku in zone.skus" 
                            :key="sku.id" 
                            :sku="sku" 
                        />
                    </div>
                </section>
            </div>

            <div v-else class="px-4 lg:px-8 max-w-7xl mx-auto mt-16 space-y-20">
                <div v-for="i in 2" :key="i" class="space-y-8 animate-pulse">
                    <div class="flex flex-col gap-2">
                        <div class="h-3 w-32 bg-muted rounded-full"></div>
                        <div class="h-10 w-64 bg-muted rounded-xl"></div>
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-6">
                        <SkuCard v-for="j in 6" :key="j" :loading="true" />
                    </div>
                </div>
            </div>

            <div v-if="!isLoading && zonesData.length === 0" class="py-40 text-center">
                <PackageSearch :size="48" class="mx-auto text-muted-foreground/20 mb-4" />
                <h3 class="text-xs font-black uppercase tracking-widest text-muted-foreground">Radar Despejado</h3>
                <p class="text-[10px] text-muted-foreground/60">No hay existencias activas en esta sucursal.</p>
            </div>
            
        </div>
    </ShopLayout>
</template>

<style scoped>
/* Curva de inercia Apple/F1 */
.ease-ios { transition-timing-function: cubic-bezier(0.32, 0.72, 0, 1); }

.section-animate {
    animation: reveal 0.8s cubic-bezier(0.32, 0.72, 0, 1) both;
}

@keyframes reveal {
    from { 
        opacity: 0; 
        transform: translateY(20px); 
    }
    to { 
        opacity: 1; 
        transform: translateY(0); 
    }
}

.bg-background { will-change: background-color; }

/* Efecto de profundidad para Modo Oscuro */
.dark .bg-background {
    background-image: 
        radial-gradient(circle at 50% -10%, rgba(225, 6, 0, 0.03) 0%, transparent 40%),
        radial-gradient(circle at 0% 100%, rgba(0, 122, 255, 0.01) 0%, transparent 30%);
}
</style>