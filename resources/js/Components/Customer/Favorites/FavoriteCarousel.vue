<script setup>
import { computed, ref, onMounted, onUnmounted } from 'vue';
import { usePage, Link } from '@inertiajs/vue3';
import { Heart, Lock, HelpCircle, ArrowRight } from 'lucide-vue-next';
import SkuCard from '@/Components/Customer/Product/SkuCard.vue';

const props = defineProps({
    favorites: { type: Array, default: () => [] } // Datos desde DB (Auth)
});

const page = usePage();
const isAuth = computed(() => !!page.props.auth?.customer);

// --- LÓGICA DE PERSISTENCIA HÍBRIDA ---
const guestFavs = ref([]);

const updateLocalFavs = () => {
    // Leemos los objetos completos guardados en SkuCard
    guestFavs.value = JSON.parse(localStorage.getItem('guest_favorites') || '[]');
};

onMounted(() => {
    updateLocalFavs();
    window.addEventListener('local-favorites-updated', updateLocalFavs);
});

onUnmounted(() => {
    window.removeEventListener('local-favorites-updated', updateLocalFavs);
});

// Determina si hay algo que mostrar (de DB o de LocalStorage)
const hasFavs = computed(() => {
    return isAuth.value ? props.favorites.length > 0 : guestFavs.value.length > 0;
});

// Lista final a renderizar
const displayList = computed(() => {
    return isAuth.value ? props.favorites : guestFavs.value;
});
</script>

<template>
    <section class="mt-12 mb-16 px-6 lg:px-8 max-w-7xl mx-auto">
        <div class="flex items-center justify-between mb-8">
            <div class="flex flex-col">
                <div class="flex items-center gap-2 mb-1">
                    <Heart :size="12" class="text-primary fill-primary animate-pulse" />
                    <span class="text-[9px] font-black uppercase tracking-[0.3em] text-primary/70 font-mono">
                        [ SECTOR: MIS_FAVORITOS ]
                    </span>
                </div>
                <h2 class="text-3xl font-black uppercase tracking-tighter italic">Tu Selección</h2>
            </div>

            <Link v-if="hasFavs" 
                  :href="isAuth ? route('customer.favorites.index') : route('customer.login')"
                  class="flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-primary/60 hover:text-primary transition-all">
                {{ isAuth ? 'GESTIONAR_ARCHIVO' : 'CONECTAR_PARA_GUARDAR' }}
                <ArrowRight :size="14" />
            </Link>
        </div>

        <div class="relative">
            <div v-if="hasFavs">
                <div class="grid grid-cols-2 md:grid-cols-5 gap-4 md:gap-6"
                     :class="{ 'grayscale-[0.8] opacity-80 pointer-events-none': !isAuth }">
                    <SkuCard v-for="sku in displayList" :key="sku.id" :sku="sku" />
                </div>
                
                <div v-if="!isAuth" class="absolute inset-x-0 -bottom-6 flex justify-center">
                    <div class="bg-primary text-background px-4 py-1 rounded-full text-[9px] font-black uppercase tracking-tighter shadow-xl">
                        Modo Invitado: Inicia sesión para sincronizar permanentemente
                    </div>
                </div>
            </div>

            <div v-else class="group relative py-20 border border-dashed border-primary/20 rounded-[3rem] overflow-hidden bg-primary/[0.02]">
                <div class="absolute inset-0 grid grid-cols-5 gap-6 px-10 opacity-10 blur-xl grayscale pointer-events-none">
                    <div v-for="n in 5" :key="n" class="aspect-[3/4] bg-primary/40 rounded-3xl"></div>
                </div>

                <div class="relative z-10 flex flex-col items-center text-center px-4">
                    <div class="w-16 h-16 bg-background rounded-2xl border border-primary/20 flex items-center justify-center mb-4 shadow-2xl group-hover:scale-110 transition-transform duration-500">
                        <HelpCircle :size="32" class="text-primary/40" />
                    </div>
                    <h3 class="text-xs font-black uppercase tracking-widest mb-2">Radar Desocupado</h3>
                    <p class="text-[10px] text-muted-foreground uppercase max-w-xs mb-8 leading-relaxed font-mono">
                        Aún no has marcado activos. Interactúa con el icono de <Heart :size="10" class="inline text-primary" /> en los productos para construir tu catálogo personal.
                    </p>
                    <Link :href="route('customer.shop.index')" class="px-8 py-3 bg-primary text-background font-black text-[10px] uppercase tracking-widest hover:shadow-neon-primary transition-all active:scale-95">
                        Explorar Góndolas
                    </Link>
                </div>
            </div>
        </div>
    </section>
</template>