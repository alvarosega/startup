<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';

const props = defineProps({
    banners: { type: Array, default: () => [] },
    loading: { type: Boolean, default: false }
});

const scrollContainer = ref(null);

const scroll = (direction) => {
    if (!scrollContainer.value) return;
    const offset = direction === 'left' ? -500 : 500;
    scrollContainer.value.scrollBy({ left: offset, behavior: 'smooth' });
};
</script>

<template>
    <div class="relative w-full group/carousel">
        <button @click="scroll('left')" 
                class="hidden lg:flex absolute left-4 top-1/2 -translate-y-1/2 z-40 w-12 h-12 items-center justify-center rounded-full bg-background/20 backdrop-blur-xl border border-white/10 text-white opacity-0 group-hover/carousel:opacity-100 transition-all hover:bg-background/40 shadow-xl">
            <ChevronLeft :size="24" stroke-width="3" />
        </button>
        <button @click="scroll('right')" 
                class="hidden lg:flex absolute right-4 top-1/2 -translate-y-1/2 z-40 w-12 h-12 items-center justify-center rounded-full bg-background/20 backdrop-blur-xl border border-white/10 text-white opacity-0 group-hover/carousel:opacity-100 transition-all hover:bg-background/40 shadow-xl">
            <ChevronRight :size="24" stroke-width="3" />
        </button>

        <div ref="scrollContainer" 
            class="flex overflow-x-auto snap-x snap-mandatory gap-4 px-6 pb-8 no-scrollbar scroll-smooth">
            
             <template v-if="loading || banners.length === 0">
                <div v-for="n in 2" :key="n" 
                class="flex-none w-[85%] md:w-[85%] lg:w-[85%] aspect-[21/9] md:aspect-[3/1] rounded-3xl skeleton p-8 flex flex-col justify-end">
                    <div class="h-8 w-1/3 bg-white/20 rounded-lg"></div>
                </div>
            </template>

            <template v-else>
                <Link 
                    v-for="banner in banners" 
                    :key="banner.id"
                    :href="route('customer.brand.show', { slug: banner.brand.slug })"
                    class="relative flex-none w-[85%] md:w-[85%] lg:w-[85%] aspect-[21/9] md:aspect-[3/1] snap-start group/item rounded-3xl overflow-hidden glass-chassis border transition-all duration-500 ease-ios outline-none active:scale-[0.98] cursor-pointer hover:border-white/20 block shadow-apple-soft"
                >
                    <div class="absolute inset-0 z-0">
                        <img 
                            :src="banner.image_desktop" 
                            :alt="banner.brand?.name || 'Marca'"
                            class="hidden md:block w-full h-full object-cover transition-transform duration-[3s] group-hover/item:scale-105"
                            @error="(e) => e.target.src = '/assets/img/brand_banner_placeholder.jpg'" 
                        />
                        <img 
                            :src="banner.image_mobile" 
                            :alt="banner.brand?.name || 'Marca'"
                            class="block md:hidden w-full h-full object-cover transition-transform duration-[3s] group-hover/item:scale-105"
                            @error="(e) => e.target.src = '/assets/img/brand_banner_placeholder.jpg'"
                        />
                    </div>

                    <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/20 to-transparent z-10 pointer-events-none"></div>

                    <div class="absolute inset-0 p-6 md:p-8 flex flex-col justify-end z-20">
                        <h3 class="text-2xl md:text-3xl font-black text-white uppercase italic leading-none tracking-tighter drop-shadow-lg group-hover/item:translate-x-3 transition-transform duration-500">
                            {{ banner.brand?.name || banner.name }}
                        </h3>
                    </div>
                </Link>
            </template>

            <div class="w-12 shrink-0 invisible"></div>
        </div>
    </div>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

/* CHASIS DE CRISTAL (Marco sutil sobre el panorámico) */
.glass-chassis {
    /* Fondo translúcido mínimo para proteger el contraste del texto */
    background: linear-gradient(to top, rgba(0,0,0,0.4) 0%, transparent 100%);
    backdrop-filter: blur(2px);
    border: 1px solid rgba(255, 255, 255, 0.1);
}
/* SKELETON ESTRUCTURAL (Alta densidad) */
.glass-chassis-skeleton {
    background: rgba(255, 255, 255, 0.05);
    backdrop-filter: blur(40px);
    border: 1px solid rgba(255, 255, 255, 0.1);
}
</style>