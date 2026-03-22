<script setup>
import { computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import ShopLayout from '@/Layouts/ShopLayout.vue';
import HeroCarousel from '@/Components/Shop/HeroCarousel.vue';
import CategoryCarousel from '@/Components/Shop/CategoryCarousel.vue';

const props = defineProps({ 
    heroBanners: Object,
    categories: Object
});

const banners = computed(() => props.heroBanners?.data || []);

const handleOpenBundle = (slug) => {
    router.visit(route('customer.shop.bundle', { bundle: slug }));
};
</script>

<template>
    <ShopLayout>
        <Head title="Explorar Tienda" />

        <div class="w-full min-h-screen bg-background pb-20 overflow-x-hidden">
            
            <HeroCarousel 
                v-if="banners.length > 0"
                :banners="banners"
                @open-bundle="handleOpenBundle"
            />

            <CategoryCarousel 
                :categories="props.categories" 
            />

            <div v-if="banners.length === 0 && !props.categories" class="flex items-center justify-center p-20 text-muted-foreground italic">
                Cargando tu experiencia de compra...
            </div>
            
        </div>
    </ShopLayout>
</template>