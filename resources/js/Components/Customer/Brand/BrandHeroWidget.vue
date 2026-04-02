<script setup>
import { Link } from '@inertiajs/vue3';
import { ChevronRight } from 'lucide-vue-next';

const props = defineProps({
    banners: { type: Array, default: () => [] }
});
</script>

<template>
    <section v-if="banners.length > 0" class="w-full">
        <div class="flex items-end justify-between mb-6 px-2">
            <div class="flex flex-col">
                <h2 class="text-2xl font-black uppercase tracking-tighter italic">
                    Marcas Destacadas
                </h2>
            </div>
            <div class="flex gap-2">
                <div class="hidden md:flex items-center gap-1 text-[8px] font-black text-muted-foreground uppercase tracking-widest">
                    Scroll para explorar <ChevronRight :size="10" />
                </div>
            </div>
        </div>

        <div class="flex gap-5 overflow-x-auto pb-8 scrollbar-hide snap-x snap-mandatory">
            <Link 
                v-for="banner in banners" 
                :key="banner.id"
                :href="route('customer.brand.show', { slug: banner.brand.slug })"
                class="relative flex-none w-[85vw] md:w-[65vw] lg:w-[45vw] aspect-[21/9] md:aspect-[3/1] bg-card border-2 border-border rounded-[2rem] overflow-hidden snap-center group shadow-sm hover:shadow-2xl hover:border-primary/40 transition-all duration-500 ease-ios block"
            >
                <img 
                    :src="banner.image_desktop" 
                    :alt="banner.name"
                    class="hidden md:block w-full h-full object-cover transform group-hover:scale-105 transition-transform duration-1000 ease-ios"
                    @error="(e) => e.target.src = '/assets/img/brand_banner_placeholder.jpg'" 
                />
                
                <img 
                    :src="banner.image_mobile" 
                    :alt="banner.name"
                    class="block md:hidden w-full h-full object-cover transform group-hover:scale-105 transition-transform duration-1000 ease-ios"
                    @error="(e) => e.target.src = '/assets/img/brand_banner_placeholder.jpg'"
                />

                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent flex items-end p-8">
                    <div class="flex items-center justify-between w-full">
                        <div class="flex flex-col">
                            <span class="text-[10px] font-bold text-white/60 uppercase tracking-widest mb-1">Tienda Oficial</span>
                            <span class="text-white text-xl font-black uppercase tracking-tighter italic group-hover:translate-x-2 transition-transform duration-500">
                                {{ banner.brand.name }}
                            </span>
                        </div>
                        
                        <div class="w-10 h-10 rounded-full bg-white text-black flex items-center justify-center shadow-xl opacity-0 group-hover:opacity-100 translate-y-4 group-hover:translate-y-0 transition-all duration-500">
                            <ChevronRight :size="20" stroke-width="3" />
                        </div>
                    </div>
                </div>
            </Link>
        </div>
    </section>
</template>

<style scoped>
/* Utilidades de Scroll y Curva iOS */
.scrollbar-hide::-webkit-scrollbar { 
    display: none; 
}
.scrollbar-hide { 
    -ms-overflow-style: none; 
    scrollbar-width: none; 
}

/* Curva de aceleración tipo Apple para transiciones suaves */
.ease-ios { 
    transition-timing-function: cubic-bezier(0.32, 0.72, 0, 1); 
}

/* Soporte para snap en navegadores antiguos si fuera necesario */
.snap-x {
    scroll-snap-type: x mandatory;
    scroll-behavior: smooth;
    -webkit-overflow-scrolling: touch;
}
</style>