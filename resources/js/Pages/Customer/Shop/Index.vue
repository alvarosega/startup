<script setup>
import { computed, ref, watch } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { AlertTriangle } from 'lucide-vue-next';
import ShopLayout from '@/Layouts/ShopLayout.vue';
import ZoneNavigator from '@/Components/Shop/ZoneNavigator.vue';
import BundleList from '@/Components/Shop/BundleList.vue';
import BundleModal from '@/Components/Shop/BundleModal.vue';

const props = defineProps({ 
    zonesData: Object, 
    bundlesData: Array
});

const page = usePage();

const showBundleModal = ref(false);
const activeBundleSlug = ref(null);

const openBundleModal = (slug) => {
    activeBundleSlug.value = slug;
    showBundleModal.value = true;
};

const processedZones = computed(() => {
    const rawZones = props.zonesData ? (Array.isArray(props.zonesData) ? props.zonesData : Object.values(props.zonesData)) : [];
    
    const getUrl = (path) => {
        if (!path) return '/images/placeholder.png';
        if (path.startsWith('http')) return path;
        return `/storage/${path.replace(/^\/+/, '')}`;
    };

    return rawZones.map(zone => ({
        ...zone,
        aisles: zone.aisles ? zone.aisles.map(aisle => ({
            ...aisle,
            image_url: getUrl(aisle.image_url || aisle.image_path)
        })) : []
    }));
});

const navigateToAisle = ({ item, zone }) => {
    router.visit(route('customer.shop.zone', { zone: zone.slug }), {
        data: { brand: item.id } 
    });
};

const navigateToZone = (zone) => {
    router.visit(route('customer.shop.zone', { zone: zone.slug }));
};

watch(() => page.props.cart_summary?.count, () => {}, { immediate: true });
</script>

<template>
    <ShopLayout>
        <Head title="Explorar Catálogo" />

        <div class="w-full flex flex-col min-h-full">
            
            <BundleList 
                :bundles="props.bundlesData" 
                @select-bundle="openBundleModal" 
            />

            <div class="flex-1 flex flex-col relative w-full">
                <ZoneNavigator 
                    v-if="processedZones.length > 0"
                    :zones="processedZones" 
                    @select-item="navigateToAisle"
                    @select-zone="navigateToZone"
                />

                <div v-else class="flex-1 flex items-center justify-center p-6 mt-10 animate-in fade-in zoom-in-95 duration-500">
                    <div class="w-full max-w-sm bg-surface/20 backdrop-blur-2xl border border-white/10 dark:border-white/5 rounded-[40px] shadow-[0_20px_40px_-15px_rgba(0,0,0,0.5)] p-10 text-center flex flex-col items-center">
                        
                        <div class="w-20 h-20 rounded-3xl bg-transparent border-4 border-f1-red/20 flex items-center justify-center mb-6 shadow-inner">
                            <AlertTriangle :size="36" class="text-f1-red" stroke-width="2.5" />
                        </div>
                        
                        <h2 class="text-3xl font-sans font-black text-foreground tracking-tighter leading-none mb-4">
                            Fuera de Zona
                        </h2>
                        
                        <p class="text-[11px] font-black text-foreground/60 uppercase tracking-[0.1em] leading-relaxed">
                            No hay tiendas ni productos disponibles para tu ubicación actual.
                        </p>
                        
                        <button @click="router.visit(route('customer.profile.addresses'))" class="mt-8 h-12 px-6 bg-foreground/5 backdrop-blur-lg border border-foreground/10 rounded-[20px] font-black uppercase text-[11px] text-foreground hover:bg-foreground/10 transition-colors active:scale-95 shadow-inner">
                            Cambiar Dirección
                        </button>
                    </div>
                </div>
            </div>
            
        </div>

        <BundleModal 
            :show="showBundleModal" 
            :bundle-slug="activeBundleSlug" 
            @close="showBundleModal = false" 
        />
    </ShopLayout>
</template>