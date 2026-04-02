<script setup>
import { ref, computed, onMounted } from 'vue'; // <--- IMPORTACIONES CORREGIDAS
import { router, usePage } from '@inertiajs/vue3';
import ShopLayout from '@/Layouts/ShopLayout.vue';
import SkuCard from '@/Components/Customer/Product/SkuCard.vue';
import { PackageSearch, Heart, Loader2 } from 'lucide-vue-next';

const props = defineProps({
    favoriteProducts: Array, // Array plano gracias al .resolve() del controlador
    activeSkus: Array,
    selectedId: String
});

const page = usePage();
const isAuth = computed(() => !!page.props.auth?.customer);
const isChanging = ref(false);

// --- LÓGICA DE HIDRATACIÓN PARA INVITADOS ---
onMounted(() => {
    // Si no está logueado y la URL no tiene los IDs de productos, los inyectamos desde LocalStorage
    if (!isAuth.value && !page.url.includes('ids')) {
        const localFavs = JSON.parse(localStorage.getItem('guest_favorites') || '[]');
        if (localFavs.length > 0) {
            const ids = localFavs.map(f => f.product_id);
            router.get(route('customer.favorites.index'), { ids }, { 
                preserveState: true, 
                replace: true 
            });
        }
    }
});

const selectProduct = (id) => {
    if (id === props.selectedId || isChanging.value) return;
    
    isChanging.value = true;
    router.get(route('customer.favorites.index'), { 
        active_id: id,
        // Si es invitado, mantenemos los IDs en la URL para no perder la vista
        ids: !isAuth.value ? props.favoriteProducts.map(p => p.id) : undefined
    }, {
        preserveState: true,
        preserveScroll: true,
        onFinish: () => isChanging.value = false
    });
};
</script>

<template>
    <ShopLayout>
        <div class="max-w-7xl mx-auto py-8 px-4 lg:px-8">
            
            <div class="mb-12">
                <div class="flex items-center gap-2 mb-6">
                    <Heart :size="12" class="text-primary fill-primary animate-pulse" />
                    <h2 class="text-[10px] font-black uppercase tracking-[0.4em] text-primary">
                        Archivo de Selección
                    </h2>
                </div>
                
                <div v-if="favoriteProducts.length > 0" class="flex gap-6 overflow-x-auto pb-6 scrollbar-hide snap-x">
                    <button v-for="product in favoriteProducts" :key="product.id"
                        @click="selectProduct(product.id)"
                        class="group flex flex-col items-center gap-3 snap-start transition-all"
                        :class="selectedId === product.id ? 'scale-110' : 'opacity-40 grayscale hover:opacity-100 hover:grayscale-0'">
                        
                        <div class="w-20 h-20 md:w-24 md:h-24 rounded-3xl overflow-hidden border-2 transition-all p-2 bg-card shadow-lg"
                             :class="selectedId === product.id ? 'border-primary shadow-neon-primary' : 'border-border/50'">
                            <img :src="product.image" class="w-full h-full object-contain group-hover:scale-110 transition-transform" />
                        </div>
                        
                        <span class="text-[9px] font-black uppercase tracking-tighter text-center w-24 line-clamp-1">
                            {{ product.name }}
                        </span>
                    </button>
                </div>

                <div v-else class="py-12 border border-dashed border-border/50 rounded-3xl flex flex-col items-center text-center">
                    <Heart :size="24" class="text-muted-foreground/20 mb-2" />
                    <p class="text-[10px] uppercase font-black text-muted-foreground italic">No hay productos en el radar</p>
                </div>
            </div>

            <div class="relative min-h-[400px]">
                <div v-if="isChanging" class="absolute inset-0 z-20 bg-background/40 backdrop-blur-sm flex items-center justify-center rounded-3xl">
                    <Loader2 class="w-8 h-8 text-primary animate-spin" />
                </div>

                <div v-if="activeSkus.length > 0">
                    <div class="flex items-baseline justify-between mb-8 border-b border-border/50 pb-4">
                        <h3 class="text-xl font-black italic uppercase tracking-tighter">Variantes Disponibles</h3>
                        <span class="text-[10px] font-mono text-muted-foreground uppercase">
                            [ Existencias: {{ activeSkus.length }} ]
                        </span>
                    </div>
                    
                    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-4 md:gap-6">
                        <SkuCard v-for="sku in activeSkus" :key="sku.id" :sku="sku" />
                    </div>
                </div>

                <div v-else-if="favoriteProducts.length > 0" class="flex flex-col items-center justify-center py-32 text-center">
                    <PackageSearch :size="48" class="text-muted-foreground/20 mb-4" />
                    <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground italic">Selecciona un producto superior para ver sus variantes</p>
                </div>
            </div>
        </div>
    </ShopLayout>
</template>

<style scoped>
.scrollbar-hide::-webkit-scrollbar { display: none; }
.shadow-neon-primary { box-shadow: 0 0 20px -5px hsl(var(--primary)); }
</style>