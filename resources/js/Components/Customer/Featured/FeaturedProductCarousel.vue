<script setup>
import { router } from '@inertiajs/vue3';

const props = defineProps({
    products: { type: Array, default: () => [] },
    loading: { type: Boolean, default: false }
});

const navigateToProduct = (slug) => {
    if (props.loading) return;
    router.visit(route('customer.featured.show', { product: slug }), { 
        preserveScroll: true,
        preserveState: true,
    });
};

const getStaticGlowStyle = () => {
    // Matriz de resplandor estática (Fallback al carecer de bg_color en DB)
    return {
        '--prod-glow-core': `rgba(var(--primary), 0.8)`,
        '--prod-glow-outer': `rgba(var(--primary), 0.2)`,
    };
};
</script>

<template>
    <section class="w-full py-4 bg-transparent relative z-10 overflow-visible">
        <div class="flex overflow-x-auto snap-x snap-mandatory no-scrollbar px-6 gap-8 pb-4">
            
            <template v-if="!loading && products.length > 0">
                <div v-for="product in products" :key="product.id"
                     @click="navigateToProduct(product.slug)"
                     :style="getStaticGlowStyle()"
                     class="group flex flex-col items-center gap-5 snap-start shrink-0 cursor-pointer w-[90px] transition-all duration-500">
                     
                    <div class="relative w-20 h-20 flex items-center justify-center">
                        <div class="absolute inset-0 rounded-full transition-all duration-700 blur-[35px] pointer-events-none opacity-0 group-hover:opacity-80 group-hover:scale-[2]"
                             :style="{ background: 'radial-gradient(circle, var(--prod-glow-core) 0%, var(--prod-glow-outer) 40%, transparent 75%)' }">
                        </div>

                        <img :src="product.image_url" 
                             class="relative z-10 w-16 h-16 object-contain transition-all duration-500 group-hover:-translate-y-3 filter drop-shadow-[0_0_15px_rgba(0,0,0,0.2)]"
                             :alt="product.name"
                             @error="(e) => e.target.src = '/assets/img/product_placeholder.png'">
                    </div>

                    <span class="text-[10px] font-black tracking-widest uppercase text-center leading-tight line-clamp-2 w-full px-1 transition-all duration-300 text-foreground/70 group-hover:text-foreground">
                        {{ product.name }}
                    </span>
                </div>
            </template>

            <template v-else>
                <div v-for="n in 5" :key="n" class="flex flex-col items-center gap-5 shrink-0 w-[90px] animate-pulse">
                    <div class="w-16 h-16 rounded-full bg-foreground/10"></div>
                    <div class="h-2 w-16 bg-foreground/10 rounded-full"></div>
                </div>
            </template>

            <div class="w-12 shrink-0"></div> 
        </div>
    </section>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

.blur-\[35px\] {
    will-change: transform, opacity;
    transform: translateZ(0);
}
</style>