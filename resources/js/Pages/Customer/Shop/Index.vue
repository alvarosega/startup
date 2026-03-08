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
    // Ahora enviamos brand_id al catálogo
    router.visit(route('customer.shop.zone', { zone: zone.slug }), {
        data: { brand: item.id } // Cambiado de category a brand
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

        <div class="w-full flex flex-col relative">
            
            <BundleList 
                :bundles="props.bundlesData" 
                @select-bundle="openBundleModal" 
            />

            <div class="flex-1">
                <ZoneNavigator 
                    v-if="processedZones.length > 0"
                    :zones="processedZones" 
                    @select-item="navigateToAisle"
                    @select-zone="navigateToZone"
                />

                <div v-else class="flex items-center justify-center p-6 mt-10">
                    <div class="w-full max-w-sm cyber-glass border-l-4 border-f1-red p-6 shadow-neon-red clip-f1-br transition-all duration-300">
                        <div class="flex items-center gap-3 mb-3 text-f1-red">
                            <AlertTriangle :size="24" />
                            <span class="text-[10px] font-mono font-black uppercase tracking-[0.2em]">Aviso de Sistema</span>
                        </div>
                        <h2 class="text-xl font-sans font-black uppercase text-foreground mb-2 tracking-tight">Sin Cobertura</h2>
                        <p class="text-xs font-mono text-muted uppercase leading-relaxed">
                            La ubicación de telemetría actual no cuenta con catálogo activo. Verifique sus coordenadas.
                        </p>
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