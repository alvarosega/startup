<script setup>
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    categories: { type: [Object, Array], default: () => [] },
    activeId: { type: String, default: null } // Para resaltar la actual
});

const safeCategories = computed(() => props.categories?.data || props.categories || []);

const navigateToCategory = (slug) => {
    router.visit(route('customer.shop.category', { category: slug }), {
        preserveScroll: false,
        only: ['categoryData', 'categories'] // Carga parcial para velocidad
    });
};

const getCategoryStyle = (hex) => {
    if (!hex) return {};
    const color = hex.startsWith('#') ? hex : '#' + hex;
    return {
        '--cat-color': color,
        '--cat-glow-strong': `${color}99`, 
        '--cat-glow-soft': `${color}33`,   
    };
};
</script>

<template>
    <section v-if="safeCategories.length > 0" class="w-full py-8 bg-transparent relative z-10 overflow-hidden">
        <div class="px-6 mb-6 flex items-center justify-between">
            <h2 class="text-xs font-bold uppercase tracking-widest text-gray-400 dark:text-gray-500">
                Departamentos
            </h2>
        </div>

        <div class="flex overflow-x-auto snap-x snap-mandatory no-scrollbar px-6 gap-8 pb-4">
            <div v-for="cat in safeCategories" :key="cat.id"
                 @click="navigateToCategory(cat.slug)"
                 :style="getCategoryStyle(cat.bg_color)"
                 class="group flex flex-col items-center gap-4 snap-start shrink-0 cursor-pointer w-[100px] transition-all duration-300"
                 :class="{ 'opacity-100 scale-110': activeId === cat.id, 'opacity-60 grayscale-[0.5] hover:grayscale-0 hover:opacity-100': activeId && activeId !== cat.id }">
                
                <div class="relative w-[100px] h-[100px] flex items-center justify-center transition-all duration-500 overflow-visible">
                    <div class="absolute inset-0 rounded-full transition-all duration-700 blur-[25px] scale-110"
                         :class="activeId === cat.id ? 'opacity-100 scale-125' : 'opacity-40 md:opacity-0 md:group-hover:opacity-70 group-hover:blur-[35px] group-hover:scale-125'"
                         :style="{ background: 'radial-gradient(circle at center, var(--cat-glow-strong) 0%, var(--cat-glow-soft) 50%, transparent 85%)' }">
                    </div>

                    <img :src="cat.image_url" 
                         class="relative z-10 w-16 h-16 object-contain transition-all duration-500 drop-shadow-[0_10px_20px_rgba(0,0,0,0.15)] group-hover:scale-110 group-hover:-translate-y-2"
                         :alt="cat.name">
                </div>

                <span class="text-[10px] font-black tracking-tighter uppercase text-gray-800 dark:text-gray-200 group-hover:text-[var(--cat-color)] transition-colors text-center leading-tight line-clamp-2 w-full"
                      :class="{ 'text-primary font-black': activeId === cat.id }">
                    {{ cat.name }}
                </span>
            </div>
            <div class="w-6 shrink-0"></div> 
        </div>
    </section>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>