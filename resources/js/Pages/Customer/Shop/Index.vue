<script setup>
import { computed, ref, watch } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import ShopLayout from '@/Layouts/ShopLayout.vue';
import ZoneNavigator from '@/Components/Shop/ZoneNavigator.vue';
import BundleModal from '@/Components/Shop/BundleModal.vue';

const props = defineProps({ 
    zonesData: Object, 
    bundlesData: Array
});

const page = usePage();

// --- LÓGICA DE BUNDLES ---
const showBundleModal = ref(false);
const activeBundleSlug = ref(null);

const openBundle = (slug) => {
    activeBundleSlug.value = slug;
    showBundleModal.value = true;
};

// --- NORMALIZACIÓN DE DATOS ---
const sourceZones = computed(() => {
    const rawZones = props.zonesData ? (Array.isArray(props.zonesData) ? props.zonesData : Object.values(props.zonesData)) : [];
    
    // Función para corregir rutas de Laravel Storage
    const getUrl = (path) => {
        if (!path) return '/images/placeholder.png';
        if (path.startsWith('http')) return path;
        return `/storage/${path.replace(/^\/+/, '')}`;
    };

    // Procesar Zonas y sus Categorías (aisles)
    let zones = rawZones.map(zone => ({
        ...zone,
        aisles: zone.aisles ? zone.aisles.map(aisle => ({
            ...aisle,
            image_url: getUrl(aisle.image_url || aisle.image_path)
        })) : []
    }));

    // Insertar zona virtual de Packs al inicio
    if (props.bundlesData?.length > 0) {
        const bundleZone = {
            id: 'virtual-bundles',
            name: 'Packs & Ofertas',
            slug: 'packs-ofertas',
            color: '#F59E0B',
            aisles: props.bundlesData.map(b => ({
                ...b,
                type: 'bundle',
                name: b.name,
                image_url: getUrl(b.image_url || b.image_path)
            })) 
        };
        zones = [bundleZone, ...zones];
    }
    return zones;
});

// --- GESTIÓN DE EVENTOS ---
const handleItemClick = ({ item, zone }) => {
    if (item.type === 'bundle') {
        openBundle(item.slug);
    } else {
        router.visit(route('customer.shop.zone', { zone: zone.slug }), {
            data: { category: item.id }
        });
    }
};

const handleNavigateZone = (zone) => {
    if (zone.id === 'virtual-bundles') return;
    router.visit(route('customer.shop.zone', { zone: zone.slug }));
};

// Asegurar reactividad del carrito
watch(() => page.props.cart_summary?.count, () => {}, { immediate: true });
</script>

<template>
    <ShopLayout>
        <Head title="Explorar Tienda" />

        <div class="w-full h-full overflow-hidden">
            <ZoneNavigator 
                v-if="sourceZones.length > 0"
                :zones="sourceZones" 
                @select-item="handleItemClick"
                @select-zone="handleNavigateZone"
            />

            <div v-else class="flex items-center justify-center h-full opacity-20">
                <h2 class="text-4xl font-black uppercase tracking-widest text-center px-4">Sin Cobertura en esta zona</h2>
            </div>
        </div>

        <BundleModal 
            :show="showBundleModal" 
            :bundle-slug="activeBundleSlug" 
            @close="showBundleModal = false" 
        />
    </ShopLayout>
</template>