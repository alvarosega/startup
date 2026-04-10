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

// --- NAVEGACIÓN POR HARDWARE ---
const scroll = (direction) => {
    if (!scrollContainer.value) return;
    const offset = direction === 'left' ? -300 : 300;
    scrollContainer.value.scrollBy({ left: offset, behavior: 'smooth' });
};

// --- ANCLAJE DINÁMICO (Auto-Center Active) ---
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
    const cleanHex = hex ? hex.replace('#', '') : 'f97316';
    return { '--cat-glow-color': `#${cleanHex}` };
};
</script>

<template>
    <div class="w-full relative z-10 select-none group/carousel group/main transition-all duration-700 overflow-visible">
        
        <button @click="scroll('left')" 
                class="hidden lg:flex absolute -left-4 top-12 z-50 w-10 h-10 items-center justify-center rounded-full bg-background/40 backdrop-blur-xl border border-border/50 text-foreground opacity-0 group-hover/main:opacity-100 transition-all hover:scale-110 active:scale-90 shadow-xl">
            <ChevronLeft :size="20" stroke-width="3" />
        </button>
        
        <button @click="scroll('right')" 
                class="hidden lg:flex absolute -right-4 top-12 z-50 w-10 h-10 items-center justify-center rounded-full bg-background/40 backdrop-blur-xl border border-border/50 text-foreground opacity-0 group-hover/main:opacity-100 transition-all hover:scale-110 active:scale-90 shadow-xl">
            <ChevronRight :size="20" stroke-width="3" />
        </button>

        <div ref="scrollContainer" 
             class="flex overflow-x-auto snap-x snap-center no-scrollbar px-6 lg:px-8 gap-6 md:gap-10 pb-6 pt-2 scroll-smooth">
            
            <template v-if="!loading && categories.length > 0">
                <Link v-for="cat in categories" :key="cat.id"
                     :href="route('customer.category', { category: cat.slug })"
                     :style="getCategoryStyle(cat.bg_color)"
                     class="group/item relative flex flex-col items-center snap-center shrink-0 cursor-pointer w-[80px] md:w-[100px] transition-all duration-500 ease-ios outline-none active:scale-90"
                     :class="{ 'is-active': String(activeId) === String(cat.id) }"
                >
                     
                    <div class="relative w-20 h-20 md:w-24 md:h-24 flex items-center justify-center mb-4">
                        
                        <div class="absolute inset-0 rounded-full transition-all duration-700 pointer-events-none layer-gpu"
                            :class="[
                                String(activeId) === String(cat.id) 
                                ? 'opacity-100 scale-[1.5] blur-[30px]' 
                                : 'opacity-20 md:group-hover/item:opacity-60 scale-[1.1] md:group-hover/item:scale-[1.4] blur-[20px] md:group-hover/item:blur-[35px]'
                            ]"
                            :style="{ background: `radial-gradient(circle, var(--cat-glow-color) 20%, transparent 75%)` }">
                        </div>

                        <div v-if="String(activeId) === String(cat.id)" 
                             class="absolute inset-0 rounded-full p-[3px] prismatic-ring animate-in zoom-in-75 duration-500 shadow-f1-glow">
                            <div class="w-full h-full rounded-full bg-background/80 backdrop-blur-sm"></div>
                        </div>

                        <img :src="cat.image_url" 
                             class="relative z-10 w-16 h-16 md:w-20 md:h-20 object-contain transition-all duration-500 group-hover/item:scale-110 group-hover/item:-translate-y-2 layer-gpu drop-shadow-hardware"
                             :alt="cat.name">
                    </div>

                    <span class="text-[9px] md:text-[10px] font-black tracking-[0.1em] uppercase text-center leading-tight line-clamp-2 w-full transition-all duration-300"
                          :class="[
                              String(activeId) === String(cat.id) 
                              ? 'text-primary' 
                              : 'text-foreground/60 group-hover/item:text-foreground'
                          ]">
                        {{ cat.name }}
                    </span>
                </Link>
            </template>

            <template v-else>
                <div v-for="n in 8" :key="n" class="flex flex-col items-center shrink-0 w-[80px] md:w-[100px] gap-4">
                    <div class="w-20 h-20 md:w-24 md:h-24 rounded-full skeleton shadow-inner"></div>
                    <div class="h-3 w-16 skeleton rounded-full"></div>
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

/* ANILLO PRISMÁTICO DINÁMICO */
.prismatic-ring {
    background: linear-gradient(
        45deg, 
        #ff0000, #ff7f00, #ffff00, #00ff00, #0000ff, #4b0082, #8b00ff
    );
    background-size: 200% 200%;
    animation: prismatic-flow 3s linear infinite;
}

@keyframes prismatic-flow {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

/* DROP SHADOW INDUSTRIAL */
.drop-shadow-hardware {
    filter: drop-shadow(0 10px 12px rgba(0,0,0,0.2));
}

.dark .drop-shadow-hardware {
    filter: drop-shadow(0 15px 20px rgba(0,0,0,0.6));
}

.shadow-f1-glow {
    box-shadow: 0 0 15px -3px hsla(var(--primary) / 0.5);
}
</style>