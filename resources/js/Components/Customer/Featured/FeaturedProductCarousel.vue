<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';

const props = defineProps({
    products: { type: Array, default: () => [] },
    loading: { type: Boolean, default: false }
});

const scrollContainer = ref(null);

const scroll = (direction) => {
    if (!scrollContainer.value) return;
    const offset = direction === 'left' ? -300 : 300;
    scrollContainer.value.scrollBy({ left: offset, behavior: 'smooth' });
};

const navigateToProduct = (slug) => {
    if (props.loading) return;
    // RECTIFICACIÓN DE RUTA: Ajustado al estándar
    router.visit(route('customer.product', { slug: slug }), { 
        preserveScroll: true,
        preserveState: true,
    });
};
</script>

<template>
    <div class="w-full relative z-10 select-none group/carousel transition-all duration-700 overflow-visible">
        
        <button @click="scroll('left')" 
                class="hidden lg:flex absolute -left-4 top-12 z-50 w-10 h-10 items-center justify-center rounded-full bg-background/40 backdrop-blur-xl border border-border/50 text-foreground opacity-0 group-hover/carousel:opacity-100 transition-all hover:scale-110 active:scale-90 shadow-xl">
            <ChevronLeft :size="20" stroke-width="3" />
        </button>
        
        <button @click="scroll('right')" 
                class="hidden lg:flex absolute -right-4 top-12 z-50 w-10 h-10 items-center justify-center rounded-full bg-background/40 backdrop-blur-xl border border-border/50 text-foreground opacity-0 group-hover/carousel:opacity-100 transition-all hover:scale-110 active:scale-90 shadow-xl">
            <ChevronRight :size="20" stroke-width="3" />
        </button>

        <div ref="scrollContainer" 
             class="flex overflow-x-auto snap-x snap-center no-scrollbar px-6 lg:px-8 gap-6 md:gap-10 pb-6 pt-2 scroll-smooth">
            
            <template v-if="!loading && products.length > 0">
                <div v-for="product in products" :key="product.id"
                     @click="navigateToProduct(product.slug)"
                     class="group/item relative flex flex-col items-center snap-center shrink-0 cursor-pointer w-[80px] md:w-[100px] transition-all duration-500 ease-ios outline-none active:scale-90">
                     
                     <div class="relative w-20 h-20 md:w-24 md:h-24 flex items-center justify-center mb-4">
    
                        <div class="absolute inset-0 transition-all duration-700 pointer-events-none layer-gpu opacity-30 md:group-hover/item:opacity-80 scale-[1.1] md:group-hover/item:scale-[1.7] blur-[35px]"
                            style="background: radial-gradient(circle, hsl(var(--primary)) 10%, transparent 70%)">
                        </div>

                        <img :src="product.image_url" 
                            class="relative z-10 w-18 h-18 md:w-22 md:h-22 object-contain transition-all duration-500 group-hover/item:scale-115 group-hover/item:-translate-y-4 layer-gpu drop-shadow-hardware"
                            :alt="product.name"
                            @error="(e) => e.target.src = '/assets/img/product_placeholder.png'">
                    </div>

                    <span class="text-[9px] md:text-[10px] font-black tracking-[0.1em] uppercase text-center leading-tight line-clamp-2 w-full transition-all duration-300 text-foreground/60 group-hover/item:text-foreground">
                        {{ product.name }}
                    </span>
                </div>
            </template>

            <template v-else>
                <div v-for="n in 8" :key="n" class="flex flex-col items-center shrink-0 w-[80px] md:w-[100px] animate-pulse gap-4">
                    <div class="w-16 h-16 md:w-20 md:h-20 rounded-full bg-primary/10 blur-2xl"></div>
                    <div class="h-2 w-14 bg-foreground/10 rounded-full"></div>
                </div>
            </template>

            <div class="w-20 shrink-0 invisible"></div> 
        </div>
    </div>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

.layer-gpu { 
    will-change: transform, opacity, filter; 
    transform: translateZ(0); 
}

.ease-ios { 
    transition-timing-function: cubic-bezier(0.32, 0.72, 0, 1); 
}

.drop-shadow-hardware {
    /* Sombra técnica que define la base del objeto */
    filter: drop-shadow(0 85px 55px rgba(0,0,0,0.25));
}

.dark .drop-shadow-hardware {
    /* Sombra más profunda para el modo oscuro */
    filter: drop-shadow(0 20px 25px rgba(0,0,0,0.6));
}
</style>