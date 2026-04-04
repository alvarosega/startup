<script setup>
import { usePage, Link } from '@inertiajs/vue3';

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
    <section class="w-full py-8 bg-transparent relative z-10 overflow-visible select-none animate-in fade-in duration-700">
        <div v-show="!loading && categories.length > 0" class="px-6 lg:px-10 max-w-7xl mx-auto mb-8">
            <div class="flex items-center gap-3">
                <div class="w-1 h-4 bg-primary rounded-full"></div>
                <h2 class="text-[10px] font-black uppercase tracking-[0.4em] text-neutral-900 dark:text-neutral-100">
                    Categorías Destacadas
                </h2>
            </div>
        </div>

        <div class="flex overflow-x-auto snap-x snap-mandatory no-scrollbar px-6 lg:px-10 gap-6 pb-4">
            
            <template v-if="!loading && categories.length > 0">
                <Link v-for="cat in categories" :key="cat.id"
                     :href="route('customer.category', { category: cat.slug })"
                     :style="getCategoryStyle(cat.bg_color)"
                     class="group flex flex-col items-center gap-6 snap-start shrink-0 cursor-pointer w-[100px] transition-all duration-500 ease-ios outline-none"
                >
                     
                    <div class="relative w-24 h-24 flex items-center justify-center">
                        <div class="absolute inset-0 rounded-full transition-all duration-700 pointer-events-none layer-gpu"
                            :class="[
                                String(activeId) === String(cat.id) 
                                ? 'opacity-100 scale-[1.3] blur-[25px]' 
                                /* En móviles (opacity-25 permanente), en desktop (0 y 40 en hover) */
                                : 'opacity-25 md:opacity-0 md:group-hover:opacity-40 scale-[1.1] md:scale-[1.2] blur-[20px]'
                            ]"
                            :style="{ background: `radial-gradient(circle, var(--cat-glow-color) 0%, transparent 70%)` }">
                        </div>

                        <div v-if="String(activeId) === String(cat.id)" 
                             class="absolute inset-0 rounded-full border-2 border-primary animate-in zoom-in-75 duration-300">
                        </div>

                        <img :src="cat.image_url" 
                             class="relative z-10 w-20 h-20 object-contain transition-all duration-500 group-hover:-translate-y-4 layer-gpu"
                             :alt="cat.name"
                             @error="(e) => e.target.src = '/assets/img/category_placeholder.png'">
                    </div>

                    <span class="text-[10px] font-black tracking-[0.15em] uppercase text-center leading-tight line-clamp-2 w-full px-1 transition-all duration-300"
                          :class="[
                              String(activeId) === String(cat.id) 
                              ? 'text-primary-aaa dark:text-primary scale-105' 
                              : 'text-neutral-600 dark:text-neutral-400 group-hover:text-neutral-900 dark:group-hover:text-neutral-100'
                          ]">
                        {{ cat.name }}
                    </span>
                </Link>
            </template>

            <template v-else>
                <div v-for="n in 8" :key="n" class="flex flex-col items-center gap-6 shrink-0 w-[100px] animate-pulse">
                    <div class="w-20 h-20 rounded-full bg-neutral-200 dark:bg-neutral-800 border border-border/50 shadow-inner"></div>
                    <div class="h-2 w-16 bg-neutral-200 dark:bg-neutral-800 rounded-full"></div>
                </div>
            </template>

            <div class="w-20 shrink-0 invisible"></div> 
        </div>
    </section>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
.layer-gpu { will-change: transform, opacity, filter; transform: translateZ(0); }
.ease-ios { transition-timing-function: cubic-bezier(0.32, 0.72, 0, 1); }

img { filter: drop-shadow(0 8px 12px rgba(0,0,0,0.15)); }
.dark img { filter: drop-shadow(0 12px 20px rgba(0,0,0,0.4)); }
</style>