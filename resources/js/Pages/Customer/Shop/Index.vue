<script setup>
import { computed, ref, watch } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { AlertTriangle, MapPin } from 'lucide-vue-next';
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
                    <div class="w-full max-w-sm bg-card rounded-xl p-10 text-center flex flex-col items-center shadow-apple-soft dark:shadow-none border-none dark:border dark:border-card-border">
                        
                        <div class="w-20 h-20 rounded-2xl bg-primary/10 border border-primary/20 flex items-center justify-center mb-6">
                            <AlertTriangle :size="36" class="text-primary" stroke-width="2.5" />
                        </div>
                        
                        <h2 class="text-2xl font-extrabold text-foreground tracking-tight mb-3">
                            Fuera de Zona
                        </h2>
                        
                        <p class="text-sm font-medium text-muted-foreground leading-relaxed mb-8">
                            No hay tiendas ni productos disponibles para tu ubicación actual. Por favor, selecciona un área de cobertura válida.
                        </p>
                        
                        <button @click="router.visit(route('customer.profile.addresses'))" 
                            class="flex items-center gap-2 px-6 py-3 bg-primary text-primary-foreground font-semibold rounded-lg transition-all duration-200 active:scale-95 hover:bg-primary/90 dark:hover:shadow-f1-glow w-full justify-center">
                            <MapPin :size="18" />
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