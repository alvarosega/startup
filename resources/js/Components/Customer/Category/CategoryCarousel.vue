<script setup>
import { ref, onMounted, watch, nextTick } from 'vue';
import { Link } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';

const props = defineProps({
    categories: { type: Array, default: () => [] },
    activeId: { type: [String, Number], default: null },
    loading: { type: Boolean, default: false }
});

const scrollContainer = ref(null);

const scroll = (direction) => {
    if (!scrollContainer.value) return;
    const offset = direction === 'left' ? -300 : 300;
    scrollContainer.value.scrollBy({ left: offset, behavior: 'smooth' });
};

const centerActiveCategory = () => {
    nextTick(() => {
        const activeElement = scrollContainer.value?.querySelector('.is-active');
        if (activeElement) {
            activeElement.scrollIntoView({
                behavior: 'smooth',
                block: 'nearest',
                inline: 'center'
            });
        }
    });
};

onMounted(centerActiveCategory);
watch(() => props.activeId, centerActiveCategory);
const getCategoryStyle = (hex) => {
    let cleanHex = hex ? hex.replace('#', '') : '32323b';
    
    if (cleanHex.length === 3) {
        cleanHex = cleanHex.split('').map(char => char + char).join('');
    }

    const r = parseInt(cleanHex.substring(0, 2), 16) || 0;
    const g = parseInt(cleanHex.substring(2, 4), 16) || 0;
    const b = parseInt(cleanHex.substring(4, 6), 16) || 0;

    // ALGORITMO DE MÁXIMO CONTRASTE: Caída radical al 15% de luminosidad
    const dr = Math.floor(r * 0.15);
    const dg = Math.floor(g * 0.15);
    const db = Math.floor(b * 0.15);

    const darkHex = [dr, dg, db].map(x => {
        const hexVal = x.toString(16);
        return hexVal.length === 1 ? '0' + hexVal : hexVal;
    }).join('');

    return { 
        '--cat-primary': `#${cleanHex}`,
        '--cat-dark': `#${darkHex}`
    };
};
</script>

<template>
    <div class="w-full relative z-10 select-none group/carousel group/main transition-all duration-150 overflow-visible">
        
        <button @click="scroll('left')" 
                class="hidden lg:flex absolute -left-4 top-12 z-50 w-10 h-10 items-center justify-center rounded-none bg-background border border-[#32323b] text-foreground opacity-0 group-hover/main:opacity-100 transition-all active:scale-95 shadow-sm focus:outline-none outline-none">
            <ChevronLeft :size="20" :stroke-width="1.5" />
        </button>
        
        <button @click="scroll('right')" 
                class="hidden lg:flex absolute -right-4 top-12 z-50 w-10 h-10 items-center justify-center rounded-none bg-background border border-[#32323b] text-foreground opacity-0 group-hover/main:opacity-100 transition-all active:scale-95 shadow-sm focus:outline-none outline-none">
            <ChevronRight :size="20" :stroke-width="1.5" />
        </button>

        <div ref="scrollContainer" 
             class="flex overflow-x-auto snap-x snap-center no-scrollbar px-4 lg:px-8 gap-6 md:gap-10 pb-6 pt-2 scroll-smooth">
            
            <template v-if="!loading && categories.length > 0">
                <Link v-for="cat in categories" :key="cat.id"
                    :href="route('customer.category', { category: cat.slug })"
                    :style="getCategoryStyle(cat.bg_color)"
                    :preserve-scroll="true"
                    :preserve-state="true"
                    :only="['products', 'categoryData', 'banners', 'filters']"
                    class="group/item relative flex flex-col items-center snap-center shrink-0 cursor-pointer w-[80px] md:w-[100px] transition-all duration-150 ease-f1 outline-none active:scale-95"
                    :class="{ 'is-active': String(activeId) === String(cat.id) }"
                >
                    
                    <div class="relative w-20 h-20 md:w-24 md:h-24 flex items-center justify-center mb-4">
                        <div class="absolute inset-0 transition-all duration-150 ease-f1 layer-gpu hexagon-pointy"
                             :class="[
                                 String(activeId) === String(cat.id) 
                                 ? 'scale-100 opacity-100 shadow-f1-glow' 
                                 : 'scale-95 opacity-50 group-hover/item:opacity-80'
                             ]"
                             style="background: linear-gradient(135deg, var(--cat-primary) 0%, var(--cat-dark) 100%);">
                        </div>

                        <img :src="cat.image_url" 
                            class="relative z-10 w-14 h-14 md:w-16 md:h-16 object-contain transition-transform duration-150 ease-f1 group-hover:scale-110 layer-gpu drop-shadow-hardware"
                            :alt="cat.name">
                    </div>

                    <span class="text-[10px] font-black tracking-tight uppercase text-center leading-tight line-clamp-2 w-full transition-colors duration-150"
                        :class="[
                            String(activeId) === String(cat.id) 
                            ? 'text-primary' 
                            : 'text-[#15151f] dark:text-white group-hover:text-primary'
                        ]">
                        {{ cat.name }}
                    </span>
                </Link>
            </template>

            <template v-else>
                <div v-for="n in 8" :key="n" class="flex flex-col items-center shrink-0 w-[80px] md:w-[100px] gap-4 skeleton-group">
                    <div class="w-20 h-20 md:w-24 md:h-24 hexagon-pointy skeleton"></div>
                    <div class="h-2 w-16 skeleton rounded-none"></div>
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

/* Curva de aceleración mecánica */
.ease-f1 {
    transition-timing-function: cubic-bezier(0.16, 1, 0.3, 1);
}

/* GEOMETRÍA HEXAGONAL PERFECTA (Pointy-topped) */
.hexagon-pointy {
    clip-path: polygon(50% 0%, 100% 25%, 100% 75%, 50% 100%, 0% 75%, 0% 25%);
}

/* DROP SHADOW INDUSTRIAL (Depth) */
.drop-shadow-hardware {
    filter: drop-shadow(0 10px 12px rgba(0,0,0,0.2));
}

.dark .drop-shadow-hardware {
    filter: drop-shadow(0 15px 20px rgba(0,0,0,0.6));
}

/* ESTADO ACTIVO: Brillo de telemetría inyectado mediante filter */
.shadow-f1-glow {
    filter: drop-shadow(0 0 10px var(--cat-primary));
}
</style>