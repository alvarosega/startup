<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import { ShoppingBag, ChevronRight, Zap } from 'lucide-vue-next';

const props = defineProps({
    banners: { type: Array, required: true }
});

const emit = defineEmits(['open-bundle']);

const handleAction = (banner) => {
    // Seguridad: Si por algún motivo el target no cargó, no hacer nada.
    if (!banner.target) return;

    if (banner.action_type === 'NAVIGATE') {
        const routeName = banner.target_type === 'bundle' ? 'customer.shop.bundle' : 'customer.shop.product';
        router.visit(route(routeName, banner.target.slug || banner.target_id));
        return;
    }

    if (banner.target_type === 'bundle') {
        // Usamos el slug del bundle para el modal o la carga
        if (banner.target.is_editable) {
            emit('open-bundle', banner.target.slug);
        } else {
            router.post(route('customer.cart.add-bundle'), { 
                bundle_id: banner.target_id 
            }, { preserveScroll: true });
        }
    } else {
        router.post(route('customer.cart.add'), { 
            sku_id: banner.target_id, 
            quantity: 1 
        }, { preserveScroll: true });
    }
};
const getImageUrl = (path) => `/storage/${path}`;
</script>

<template>
    <section v-if="banners.length > 0" class="w-full pt-4 pb-2">
        <div class="flex overflow-x-auto snap-x snap-mandatory no-scrollbar px-4 gap-4">
            <div v-for="banner in banners" :key="banner.id" 
                 @click="handleAction(banner)"
                 class="relative w-[92vw] md:w-[800px] aspect-[16/9] md:aspect-[21/9] shrink-0 snap-start group cursor-pointer rounded-2xl overflow-hidden shadow-lg border border-border">
                
                <picture class="absolute inset-0 w-full h-full">
                    <source :srcset="getImageUrl(banner.image_desktop_path)" media="(min-width: 768px)">
                    <img :src="getImageUrl(banner.image_mobile_path)" :alt="banner.name" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                </picture>

                <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent flex flex-col justify-end p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <div v-if="banner.target_type === 'bundle'" class="flex items-center gap-1.5 mb-2">
                                <span class="bg-primary text-white text-[10px] font-black px-2 py-0.5 rounded uppercase tracking-tighter flex items-center gap-1">
                                    <Zap :size="10" fill="currentColor" /> COMBO EXCLUSIVO
                                </span>
                            </div>
                            <h3 class="text-white text-xl md:text-2xl font-black leading-tight drop-shadow-md uppercase italic">
                                {{ banner.name }}
                            </h3>
                        </div>
                        
                        <div class="bg-white text-black p-3 rounded-full shadow-xl transform translate-y-2 opacity-0 group-hover:translate-y-0 group-hover:opacity-100 transition-all duration-300">
                            <ShoppingBag :size="20" stroke-width="3" />
                        </div>
                    </div>
                </div>
            </div>
            <div class="w-4 shrink-0"></div> </div>
    </section>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>