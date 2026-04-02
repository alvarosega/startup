<script setup>
import { computed, ref, onMounted, onUnmounted } from 'vue';
import { usePage, Link, router } from '@inertiajs/vue3';
import { Heart, ArrowRight } from 'lucide-vue-next';

const props = defineProps({
    favorites: { type: [Array, Object], default: () => [] }
});

const page = usePage();
const isAuth = computed(() => !!page.props.auth?.customer);
const guestFavs = ref([]);

// --- 1. NORMALIZACIÓN DE DATOS DEL SERVIDOR ---
const serverFavorites = computed(() => {
    return Array.isArray(props.favorites) ? props.favorites : props.favorites?.data || [];
});

// --- 2. GESTIÓN DE INVITADOS ---
const updateLocalFavs = () => {
    guestFavs.value = JSON.parse(localStorage.getItem('guest_favorites') || '[]');
};

onMounted(() => {
    updateLocalFavs();
    window.addEventListener('local-favorites-updated', updateLocalFavs);
});

onUnmounted(() => window.removeEventListener('local-favorites-updated', updateLocalFavs));

// --- 3. LÓGICA DE VISIBILIDAD UNIFICADA ---
const displayList = computed(() => {
    // Si está logueado, manda la DB. Si no, manda el localStorage.
    return isAuth.value ? serverFavorites.value : guestFavs.value;
});

const hasFavs = computed(() => displayList.value.length > 0);

const navigateToFavorite = (productId) => {
    router.get(route('customer.favorites.index'), { active_id: productId });
};
</script>

<template>
    <section v-if="hasFavs" class="mt-12 mb-16 px-6 lg:px-8 max-w-7xl mx-auto">
        <div class="flex items-center justify-between mb-8">
            <div class="flex flex-col">
                <div class="flex items-center gap-2 mb-1">
                    <Heart :size="12" class="text-primary fill-primary animate-pulse" />
                    <span class="text-[9px] font-black uppercase tracking-[0.3em] text-primary/70 font-mono">
                        [ SECTOR: TU_SELECCIÓN ]
                    </span>
                </div>
                <h2 class="text-3xl font-black uppercase tracking-tighter italic">Favoritos</h2>
            </div>

            <Link :href="route('customer.favorites.index')" 
                  class="flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-primary/60 hover:text-primary transition-all">
                GESTIONAR_ARCHIVO
                <ArrowRight :size="14" />
            </Link>
        </div>

        <div class="flex gap-6 overflow-x-auto pb-6 scrollbar-hide snap-x">
            <button v-for="product in displayList" :key="product.id"
                @click="navigateToFavorite(product.id)"
                class="group flex flex-col items-center gap-3 snap-start">
                
                <div class="w-20 h-20 md:w-24 md:h-24 rounded-3xl overflow-hidden border border-border/50 p-2 bg-card shadow-lg group-hover:border-primary group-hover:shadow-neon-primary transition-all duration-500">
                    <img :src="product.image" class="w-full h-full object-contain group-hover:scale-110 transition-transform" />
                </div>
                
                <span class="text-[9px] font-black uppercase tracking-tighter text-center w-24 line-clamp-1 opacity-60 group-hover:opacity-100">
                    {{ product.name }}
                </span>
            </button>
        </div>
    </section>
</template>