<script setup>
import { ref, onMounted, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { X, Zap, ArrowRight } from 'lucide-vue-next';

const props = defineProps({
    banners: { type: Array, default: () => [] }
});

const isOpen = ref(false);
const activeBanner = computed(() => props.banners.length > 0 ? props.banners[0] : null);

onMounted(() => {
    if (props.banners.length > 0) {
        setTimeout(() => { isOpen.value = true; }, 2500);
    }
});

const handleAction = () => {
    if (!activeBanner.value) return;
    isOpen.value = false;
    const target = activeBanner.value.target;
    
    if (activeBanner.value.target_type.includes('Bundle')) {
        router.visit(route('customer.shop.bundle', { slug: target.slug }));
    } else {
        router.visit(route('customer.product.show', { id: target.product_id, active_sku: target.id }));
    }
};
</script>

<template>
    <Transition name="modal-fade">
        <div v-if="isOpen && activeBanner" class="fixed inset-0 z-[100] flex items-center justify-center p-6">
            <div class="absolute inset-0 bg-background/60 backdrop-blur-xl" @click="isOpen = false"></div>
            <div class="relative w-full max-w-lg bg-card border border-border/40 rounded-[3rem] overflow-hidden shadow-2xl animate-in zoom-in duration-500">
                <button @click="isOpen = false" class="absolute top-6 right-6 z-30 p-3 bg-black/20 hover:bg-black/40 backdrop-blur-md rounded-full text-white transition-all active:scale-90">
                    <X :size="20" stroke-width="3" />
                </button>
                <div class="relative aspect-[4/5] group cursor-pointer" @click="handleAction">
                    <img :src="activeBanner.image_desktop" class="w-full h-full object-cover transition-transform duration-1000 group-hover:scale-110" />
                    <div class="absolute inset-0 bg-gradient-to-t from-black via-black/20 to-transparent flex flex-col justify-end p-10">
                        <div class="flex items-center gap-2 mb-4">
                            <div class="bg-primary p-1.5 rounded-lg"><Zap :size="14" class="text-white fill-current" /></div>
                            <span class="text-[10px] font-black uppercase tracking-[0.4em] text-primary">Radar de Ofertas</span>
                        </div>
                        <h3 class="text-4xl font-black text-white uppercase italic tracking-tighter leading-none mb-6">{{ activeBanner.name }}</h3>
                        <button class="w-full h-16 bg-white text-black font-black uppercase tracking-widest text-[11px] rounded-[1.5rem] flex items-center justify-center gap-4 hover:bg-primary hover:text-white transition-all">
                            Aprovechar Ahora <ArrowRight :size="18" stroke-width="3" />
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Transition>
</template>