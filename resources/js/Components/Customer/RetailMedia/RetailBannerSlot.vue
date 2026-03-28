<script setup>
import { router } from '@inertiajs/vue3';
import { Monitor, Smartphone } from 'lucide-vue-next';

const props = defineProps({
    banners: { type: Array, default: () => [] },
    aspectRatio: { type: String, default: 'aspect-[21/9] lg:aspect-[3/1]' }
});

const handleNavigate = (banner) => {
    if (!banner.target) return;
    
    const type = banner.target.type?.toLowerCase();
    const id = banner.target.id;

    if (type === 'sku') {
        router.visit(route('customer.shop.product', { id }));
    } else if (type === 'bundle') {
        router.visit(route('customer.shop.bundle', { slug: banner.target.slug || id }));
    }
};
</script>

<template>
    <div v-if="banners.length > 0" class="w-full">
        <div v-for="banner in banners" :key="banner.id" 
             @click="handleNavigate(banner)"
             class="relative w-full overflow-hidden rounded-[2rem] cursor-pointer group shadow-apple-soft border border-border/40 transition-all duration-700 hover:shadow-primary/10"
             :class="aspectRatio">
            
            <picture>
                <source :srcset="banner.image_desktop_url" media="(min-width: 1024px)">
                <img :src="banner.image_mobile_url || banner.image_desktop_url" 
                     :alt="banner.name"
                     class="w-full h-full object-cover transition-transform duration-1000 group-hover:scale-105" />
            </picture>

            <div class="absolute inset-0 bg-gradient-to-t from-black/40 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
        </div>
    </div>
</template>