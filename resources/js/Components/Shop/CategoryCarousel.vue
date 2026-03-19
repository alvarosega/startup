<script setup>
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    categories: { type: Array, default: () => [] }
});

const safeCategories = computed(() => props.categories?.data || props.categories || []);

const getImageUrl = (path) => {
    if (!path) return '/assets/img/placeholder.png';
    if (path.startsWith('http')) return path;
    return `/storage/${path.replace(/^\/+/, '')}`;
};

const navigateToCategory = (slug) => {
    router.visit(route('customer.shop.category', { category: slug }));
};

/**
 * Variables de color para la Aurora
 */
const getCategoryStyle = (hex) => {
    if (!hex) return {};
    const color = hex.startsWith('#') ? hex : '#' + hex;
    
    return {
        '--cat-color': color,
        '--cat-glow-strong': `${color}99`, // 60%
        '--cat-glow-soft': `${color}33`,   // 20%
    };
};
</script>

<template>
    <section v-if="safeCategories.length > 0" class="w-full py-8 bg-transparent relative z-10 overflow-hidden">
        <div class="px-6 mb-6">
            <h2 class="text-xs font-bold uppercase tracking-[-0.02em] text-gray-400 dark:text-gray-500">
                Departamentos
            </h2>
        </div>

        <div class="flex overflow-x-auto snap-x snap-mandatory no-scrollbar px-6 gap-8 pb-4">
            <div v-for="cat in safeCategories" :key="cat.id"
                 @click="navigateToCategory(cat.slug)"
                 :style="getCategoryStyle(cat.bg_color)"
                 class="group flex flex-col items-center gap-4 snap-start shrink-0 cursor-pointer w-[100px]">
                
                <div class="relative w-[100px] h-[100px] flex items-center justify-center transition-all duration-500 overflow-visible">
                    
                    <div class="absolute inset-0 rounded-full transition-all duration-700 
                                blur-[25px] scale-110
                                opacity-50 dark:opacity-70
                                md:opacity-0 md:group-hover:opacity-70 
                                group-hover:blur-[35px] group-hover:scale-125"
                         :style="{ 
                             background: 'radial-gradient(circle at center, var(--cat-glow-strong) 0%, var(--cat-glow-soft) 50%, transparent 85%)' 
                         }"></div>

                    <img :src="getImageUrl(cat.image_path)" 
                         class="relative z-10 w-16 h-16 object-contain transition-all duration-500 
                                drop-shadow-[0_10px_20px_rgba(0,0,0,0.15)]
                                group-hover:scale-110 group-hover:-translate-y-2"
                         :alt="cat.name">
                </div>

                <span class="text-xs font-semibold tracking-[-0.01em] text-gray-800 dark:text-gray-200 
                             group-hover:text-[var(--cat-color)] transition-colors text-center leading-tight line-clamp-2 w-full">
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

/* Animación sutil de respiración solo para móviles */
@keyframes mobile-aurora-pulse {
    0%, 100% { opacity: 0.5; transform: scale(1.1) blur(25px); }
    50% { opacity: 0.7; transform: scale(1.15) blur(30px); }
}

@media (max-width: 768px) {
    div[style*="radial-gradient"] {
        animation: mobile-aurora-pulse 5s infinite ease-in-out;
    }
}
</style>