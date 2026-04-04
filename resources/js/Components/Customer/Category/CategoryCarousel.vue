<script setup>
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    categories: { type: Array, default: () => [] },
    activeId: { type: [String, Number], default: null },
    loading: { type: Boolean, default: false }
});

const getCategoryStyle = (hex) => {
    const cleanHex = hex ? hex.replace('#', '') : 'f97316';
    return {
        '--cat-glow-color': `#${cleanHex}`,
    };
};
</script>

<template>
    <div class="w-full relative z-10 overflow-visible select-none animate-in fade-in duration-700">
        
        <div class="flex overflow-x-auto snap-x snap-mandatory no-scrollbar px-6 lg:px-8 gap-6 md:gap-10 pb-6 pt-2">
            
            <template v-if="!loading && categories.length > 0">
                <Link v-for="cat in categories" :key="cat.id"
                     :href="route('customer.category', { category: cat.slug })"
                     :style="getCategoryStyle(cat.bg_color)"
                     class="group relative flex flex-col items-center snap-start shrink-0 cursor-pointer w-[80px] md:w-[100px] transition-all duration-500 ease-ios outline-none"
                >
                     
                    <div class="relative w-20 h-20 md:w-24 md:h-24 flex items-center justify-center mb-3">
                        
                        <div class="absolute inset-0 rounded-full transition-all duration-700 pointer-events-none layer-gpu"
                            :class="[
                                String(activeId) === String(cat.id) 
                                ? 'opacity-100 scale-[1.6] blur-[35px]' 
                                : 'opacity-40 md:opacity-20 md:group-hover:opacity-80 scale-[1.2] md:group-hover:scale-[1.5] blur-[25px] md:group-hover:blur-[40px]'
                            ]"
                            :style="{ background: `radial-gradient(circle, var(--cat-glow-color) 20%, transparent 75%)` }">
                        </div>

                        <div v-if="String(activeId) === String(cat.id)" 
                             class="absolute inset-0 rounded-full border-[3px] border-primary animate-in zoom-in-75 duration-300 shadow-[0_0_20px_rgba(var(--primary-rgb),0.4)]">
                        </div>

                        <img :src="cat.image_url" 
                             class="relative z-10 w-16 h-16 md:w-20 md:h-20 object-contain transition-all duration-500 group-hover:scale-110 group-hover:-translate-y-3 layer-gpu"
                             :alt="cat.name">
                    </div>

                    <span class="text-[9px] md:text-[10px] font-black tracking-[0.1em] uppercase text-center leading-tight line-clamp-2 w-full transition-all duration-300"
                          :class="[
                              String(activeId) === String(cat.id) 
                              ? 'text-primary' 
                              : 'text-black dark:text-neutral-400 group-hover:text-primary'
                          ]">
                        {{ cat.name }}
                    </span>
                </Link>
            </template>

            <template v-else>
                <div v-for="n in 8" :key="n" class="flex flex-col items-center shrink-0 w-[80px] animate-pulse gap-3">
                    <div class="w-16 h-16 rounded-full bg-neutral-200 dark:bg-neutral-800 border border-white/5"></div>
                    <div class="h-2 w-12 bg-neutral-200 dark:bg-neutral-800 rounded-full"></div>
                </div>
            </template>

            <div class="w-12 shrink-0 invisible"></div> 
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

img { 
    filter: drop-shadow(0 12px 15px rgba(0,0,0,0.25)); 
}
.dark img { 
    filter: drop-shadow(0 15px 25px rgba(0,0,0,0.6)); 
}
</style>