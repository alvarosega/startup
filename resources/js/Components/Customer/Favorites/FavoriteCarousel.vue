<script setup>
import { computed, ref, onMounted, onUnmounted } from 'vue';
import { usePage, Link, router } from '@inertiajs/vue3';
import { Heart, ChevronLeft, ChevronRight, ChevronRight as ArrowIcon } from 'lucide-vue-next';

const props = defineProps({
    favorites: { type: [Array, Object], default: () => [] },
    loading: { type: Boolean, default: false }
});

const page = usePage();
const scrollContainer = ref(null);
const isAuth = computed(() => !!page.props.auth?.customer);
const guestFavs = ref([]);

// --- 1. NORMALIZACIÓN Y UNIFICACIÓN ---
const updateLocalFavs = () => {
    guestFavs.value = JSON.parse(localStorage.getItem('guest_favorites') || '[]');
};

onMounted(() => {
    updateLocalFavs();
    window.addEventListener('local-favorites-updated', updateLocalFavs);
});

onUnmounted(() => window.removeEventListener('local-favorites-updated', updateLocalFavs));

const displayList = computed(() => {
    const serverData = Array.isArray(props.favorites) ? props.favorites : props.favorites?.data || [];
    return isAuth.value ? serverData : guestFavs.value;
});

// Clave: El componente solo es visible si hay items
const hasData = computed(() => displayList.value.length > 0);

const scroll = (direction) => {
    if (!scrollContainer.value) return;
    scrollContainer.value.scrollBy({ left: direction === 'left' ? -300 : 300, behavior: 'smooth' });
};

const navigateToFavorite = (productId) => {
    // Redirigir a la vista de gestión o al producto
    router.get(route('customer.favorites.index'), { active_id: productId });
};
</script>

<template>
    <div v-if="hasData" class="w-full section-reveal">
        
        <div class="header-standard mb-8">
            <div class="title-block-wrapper">
                <Heart :size="16" class="text-black dark:text-white fill-current" />
                <h2>Tus Favoritos</h2>
            </div>
            <Link :href="route('customer.favorites.index')" class="link-all">
                Gestionar <ArrowIcon :size="12" />
            </Link>
        </div>

        <div class="relative group/carousel overflow-visible">
            <button @click="scroll('left')" 
                    class="hidden lg:flex absolute -left-4 top-10 z-50 w-10 h-10 items-center justify-center rounded-full bg-background/40 backdrop-blur-xl border border-border/50 text-foreground opacity-0 group-hover/carousel:opacity-100 transition-all hover:scale-110 active:scale-90 shadow-xl">
                <ChevronLeft :size="20" stroke-width="3" />
            </button>
            
            <button @click="scroll('right')" 
                    class="hidden lg:flex absolute -right-4 top-10 z-50 w-10 h-10 items-center justify-center rounded-full bg-background/40 backdrop-blur-xl border border-border/50 text-foreground opacity-0 group-hover/carousel:opacity-100 transition-all hover:scale-110 active:scale-90 shadow-xl">
                <ChevronRight :size="20" stroke-width="3" />
            </button>

            <div ref="scrollContainer" 
                 class="flex overflow-x-auto snap-x snap-center no-scrollbar gap-8 md:gap-12 pb-6 pt-2 scroll-smooth">
                
                <button v-for="product in displayList" :key="product.id"
                        @click="navigateToFavorite(product.id)"
                        class="group/item relative flex flex-col items-center snap-center shrink-0 cursor-pointer w-[80px] md:w-[100px] transition-all duration-500 ease-ios outline-none active:scale-90">
                    
                    <div class="relative w-20 h-20 md:w-24 md:h-24 flex items-center justify-center mb-4">
                        <div class="absolute inset-0 transition-all duration-700 pointer-events-none layer-gpu opacity-20 md:group-hover/item:opacity-60 scale-[1.1] md:group-hover/item:scale-[1.5] blur-[25px]"
                             style="background: radial-gradient(circle, #ef4444 10%, transparent 70%)">
                        </div>

                        <img :src="product.image" 
                             class="relative z-10 w-16 h-16 md:w-20 md:h-20 object-contain transition-all duration-500 group-hover/item:scale-110 group-hover/item:-translate-y-3 layer-gpu drop-shadow-hardware"
                             :alt="product.name">
                    </div>

                    <span class="text-[9px] md:text-[10px] font-black tracking-[0.1em] uppercase text-center leading-tight line-clamp-2 w-full transition-all duration-300 text-foreground/60 group-hover/item:text-foreground">
                        {{ product.name }}
                    </span>
                </button>

                <div class="w-20 shrink-0 invisible"></div> 
            </div>
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

.drop-shadow-hardware {
    filter: drop-shadow(0 10px 12px rgba(0,0,0,0.2));
}

.dark .drop-shadow-hardware {
    filter: drop-shadow(0 15px 20px rgba(0,0,0,0.6));
}

/* Estilos de cabecera repetidos para autonomía */
.header-standard { display: flex; align-items: flex-end; gap: 1rem; }
.title-block-wrapper { position: relative; display: flex; align-items: center; gap: 0.85rem; flex-grow: 1; padding-bottom: 8px; }
.title-block-wrapper h2 { font-size: 11px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.4em; color: theme('colors.black'); }
.dark .title-block-wrapper h2 { color: theme('colors.white'); }
.title-block-wrapper::after { content: ''; position: absolute; bottom: 0; left: 0; width: 100%; height: 1px; background: linear-gradient(to right, #ff0000, #ff7f00, #ffff00, #00ff00, #0000ff, #4b0082, #8b00ff); opacity: 0.8; }
.link-all { padding-bottom: 8px; font-size: 10px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.15em; color: theme('colors.black'); display: flex; align-items: center; gap: 4px; transition: all 0.3s ease; }
.dark .link-all { color: theme('colors.neutral.400'); }
</style>