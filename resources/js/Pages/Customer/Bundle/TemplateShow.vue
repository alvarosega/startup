<script setup>
import { computed } from 'vue'; // ELIMINAMOS ref y usePage que ya no se usan
import { Head } from '@inertiajs/vue3';
import ShopLayout from '@/Layouts/ShopLayout.vue';
import { Zap } from 'lucide-vue-next'; // ELIMINAMOS iconos sobrantes
import EditableBundleCarousel from '@/Components/Customer/Bundle/EditableBundleCarousel.vue';
import SkuCard from '@/Components/Customer/Product/SkuCard.vue';

const props = defineProps({
    bundle: Object,
    templateBundles: Object, 
    // ELIMINAMOS currentCart (la SkuCard lee el carrito global)
});

const bundleData = computed(() => props.bundle?.data || props.bundle || {});

// REORDENAMIENTO: Pack actual primero
const carouselBundles = computed(() => {
    const all = props.templateBundles?.data || [];
    const currentId = bundleData.value.id;
    if (!currentId) return all;
    const current = all.find(b => b.id === currentId);
    const others = all.filter(b => b.id !== currentId);
    return current ? [current, ...others] : all;
});

// ¡BASURA ELIMINADA! 
// Borré itemsWithState, processingItems y updateItem. 
// SkuCard hace ese trabajo ahora.
</script>

<template>
    <ShopLayout>
        <Head :title="bundleData.name || 'Configurar Pack'" /> 

        <div v-if="carouselBundles.length > 0" class="w-full pt-0 pb-10">
            <div class="max-w-7xl mx-auto px-6 md:px-12 mb-6">
                <div class="header-standard">
                    <div class="title-block-wrapper">
                        <Zap :size="16" class="text-black dark:text-primary" />
                        <h2 class="text-black dark:text-white">Menú de Packs</h2>
                    </div>
                </div>
            </div>
            <EditableBundleCarousel :bundles="carouselBundles" :current-id="bundleData.id" />
        </div>

        <div v-if="bundleData.items?.length > 0" class="max-w-7xl mx-auto px-6 pb-32 section-reveal">
            <div class="header-standard mb-10 pb-4 border-b border-black/5 dark:border-white/5">
                <div class="title-block-wrapper">
                    <Zap :size="16" class="text-black dark:text-primary fill-current" />
                    <h2 class="text-xl md:text-2xl text-black dark:text-white uppercase tracking-tighter italic">
                        Configuración del Pack
                    </h2>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-[9px] font-black font-mono text-foreground/40 uppercase bg-black/5 dark:bg-white/5 px-2 py-1 rounded">
                        Cod: {{ bundleData.slug }}
                    </span>
                    <span class="text-[9px] font-black font-mono text-primary uppercase bg-primary/10 px-2 py-1 rounded">
                        {{ bundleData.items.length }} Unidades
                    </span>
                </div>
            </div>
            
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-4 md:gap-6">
                <SkuCard 
                    v-for="item in bundleData.items" 
                    :key="item.id" 
                    :sku="item" 
                    :suggested-quantity="item.suggested_quantity"
                />
            </div>
        </div>
    </ShopLayout>
</template>

<style scoped>
/* (Mantén tus estilos CSS originales aquí) */
.section-reveal {
    animation: slideUpReveal 0.8s cubic-bezier(0.22, 1, 0.36, 1) backwards;
}

@keyframes slideUpReveal {
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