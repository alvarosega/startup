<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'; // RECTIFICADO: Importaciones completas
import { Head, router } from '@inertiajs/vue3';
import ShopLayout from '@/Layouts/ShopLayout.vue';
import SkuCard from '@/Components/Customer/Product/SkuCard.vue';
import FeaturedProductCarousel from '@/Components/Customer/Featured/FeaturedProductCarousel.vue';

const props = defineProps({
    showcase: { type: Object, required: true },
    featuredProducts: { type: Array, default: () => [] } // RECIBIR PARA EL CARRUSEL
});
// --- 1. RESOLUCIÓN DE DATA (RESILIENTE) ---
// Normaliza el acceso si el Resource viene con o sin 'data' wrapping
const showcaseData = computed(() => props.showcase?.data || props.showcase || {});

// --- 2. ESTADO REACTIVO ---
const otherSkus = ref([]);
const nextCursor = ref(null);
const isLoadingMore = ref(false);

// RECTIFICACIÓN: Solo resetear si cambiamos de producto destacado, no por paginación.
watch(() => showcaseData.value.product?.id, (newId, oldId) => {
    if (newId !== oldId) {
        const paginated = showcaseData.value.others_paginated;
        otherSkus.value = paginated?.data || [];
        nextCursor.value = paginated?.next_cursor || null;
    }
}, { immediate: true });

// --- 4. MOTOR DE INFINITE SCROLL ---
const handleScroll = () => {
    if (isLoadingMore.value || !nextCursor.value) return;

    const scrollHeight = document.documentElement.scrollHeight;
    const scrollTop = document.documentElement.scrollTop;
    const clientHeight = document.documentElement.clientHeight;

    // Trigger a 800px del final para garantizar fluidez
    if (scrollTop + clientHeight >= scrollHeight - 800) {
        loadMore();
    }
};

const loadMore = () => {
    if (isLoadingMore.value || !nextCursor.value) return;
    isLoadingMore.value = true;

    router.get(route('customer.featured.show', { product: showcaseData.value.product.slug }), 
        { cursor: nextCursor.value }, 
        {
            preserveState: true,
            preserveScroll: true,
            only: ['showcase'], 
            onSuccess: (page) => {
                const newData = page.props.showcase?.data?.others_paginated?.data 
                               || page.props.showcase?.others_paginated?.data 
                               || [];
                
                const nextC = page.props.showcase?.data?.others_paginated?.next_cursor 
                              || page.props.showcase?.others_paginated?.next_cursor;

                otherSkus.value = [...otherSkus.value, ...newData];
                nextCursor.value = nextC;
                isLoadingMore.value = false;
            }
        }
    );
};

onMounted(() => window.addEventListener('scroll', handleScroll));
onUnmounted(() => window.removeEventListener('scroll', handleScroll));
</script>

<template>
    <ShopLayout>

        <Head :title="showcaseData.product?.name || 'Cargando...'" />
        <div class="bg-card/20 border-b border-border/10 backdrop-blur-md">
            <FeaturedProductCarousel :products="featuredProducts" />
        </div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            
            <header v-if="showcaseData.product" class="mb-12">
                <h1 class="text-4xl md:text-6xl font-black uppercase italic tracking-tighter text-foreground leading-none">
                    {{ showcaseData.product.name }}
                </h1>
                <p class="mt-4 text-muted-foreground text-sm max-w-2xl font-medium leading-relaxed">
                    {{ showcaseData.product.description }}
                </p>
            </header>

            <section v-if="showcaseData.skus?.length > 0 || props.showcase === undefined" class="space-y-4 mb-20">
                <div class="flex items-center gap-4 mb-6">
                    <span class="h-px flex-1 bg-primary/20"></span>
                    <h2 class="text-xs font-black uppercase tracking-[0.4em] text-primary">
                        [ VARIANTES_EN_GÓNDOLA ]
                    </h2>
                    <span class="h-px flex-1 bg-primary/20"></span>
                </div>

                <div v-if="props.showcase === undefined" 
                    class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-4 md:gap-6">
                    <div v-for="n in 5" :key="n" class="product-card h-[400px] skeleton"></div>
                </div>

                <div v-else class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-4 md:gap-6">
                    <div v-for="sku in showcaseData.skus" :key="sku.id">
                        <SkuCard :sku="sku" :isActive="true" />
                    </div>
                </div>
            </section>
            <section>
                <div class="flex items-center gap-4 mb-8">
                    <h2 class="text-2xl font-black uppercase tracking-tighter italic">Explorar más productos</h2>
                    <div class="h-px flex-1 bg-border/40"></div>
                </div>

                <div v-if="otherSkus.length > 0" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 md:gap-6">
                    <SkuCard 
                        v-for="sku in otherSkus" 
                        :key="sku.id" 
                        :sku="sku" 
                    />
                </div>

                <div v-if="isLoadingMore" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-4 md:gap-6 mt-6">
                    <div v-for="n in 6" :key="n" class="product-card h-[400px] skeleton !rounded-3xl"></div>
                </div>
            </section>
        </div>
    </ShopLayout>
</template>

<style scoped>
section { will-change: transform; }

/* Optimización de renderizado para listas largas */
.grid { contain: content; }
</style>