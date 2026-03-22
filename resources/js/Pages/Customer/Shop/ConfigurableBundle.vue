<script setup>
import { computed, ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { 
    ChevronLeft, 
    ShoppingBag, 
    Zap, 
    CheckCircle2, 
    AlertCircle,
    Plus,
    Minus
} from 'lucide-vue-next';
import ShopLayout from '@/Layouts/ShopLayout.vue';

const props = defineProps({
    // Regla 3.C: Recibe el objeto del Resource { data: { ... } }
    bundle: { type: Object, required: true },
    promo_banner: { type: String, default: '' }
});

/**
 * ESTADO ATÓMICO
 * selections: { [sku_id: string]: number }
 */
const selections = ref({});

/**
 * PILAR 3.C: UNWRAPPING SEGURO
 * Extraemos los datos del Resource para evitar colapsos si la prop viene vacía.
 */
const bundleData = computed(() => props.bundle?.data || {});
const items = computed(() => bundleData.value.items || []);

const totalSelected = computed(() => {
    return Object.values(selections.value).reduce((sum, qty) => sum + qty, 0);
});

// Regla de Negocio: El botón se activa solo si hay selección
const isReadyToOrder = computed(() => totalSelected.value > 0);

/**
 * LÓGICA DE CONTROL DE CANTIDADES (ZERO-TRUST)
 * Valida Stock real e Item Limits definidos en el Bundle.
 */
const updateQty = (skuId, change, stock, maxAllowed) => {
    const currentQty = selections.value[skuId] || 0;
    const newQty = currentQty + change;

    // 1. Límite inferior
    if (newQty < 0) return;

    // 2. Validación de Stock Real (Inyectado por la Acción)
    if (change > 0 && newQty > stock) {
        alert(`Lo sentimos, solo quedan ${stock} unidades disponibles.`);
        return;
    }

    // 3. Validación de Regla del Combo (Dato del Pivote)
    if (change > 0 && newQty > maxAllowed) {
        alert(`Este combo solo permite hasta ${maxAllowed} unidades de este producto.`);
        return;
    }

    // 4. Actualización Atómica
    if (newQty === 0) {
        delete selections.value[skuId];
    } else {
        selections.value[skuId] = newQty;
    }
};

/**
 * FLUJO TRANSACCIONAL
 * Envía la selección al endpoint unificado de Cart.
 */
const handleConfirm = () => {
    if (!isReadyToOrder.value) return;

    router.post(route('customer.cart.bundle.add'), {
        bundle_id: bundleData.value.id,
        items: selections.value // Enviamos el mapa { sku_id: cantidad }
    }, {
        preserveScroll: true,
        onStart: () => { /* Loader si es necesario */ },
        onSuccess: () => {
            // Regla de Oro: No redirigir si el usuario quiere seguir explorando
            // El layout ya actualizará el contador del carrito automáticamente
        }
    });
};

const getImageUrl = (path) => path ? `/storage/${path}` : '/assets/img/placeholder.png';
</script>

<template>
    <ShopLayout>
        <Head :title="`Armar ${bundleData.name || 'Combo'}`" />

        <div class="w-full min-h-screen bg-background pb-44">
            
            <div class="relative w-full aspect-[16/8] md:aspect-[21/6] overflow-hidden bg-card border-b border-border">
                <img 
                    :src="getImageUrl(promo_banner || bundleData.image_path)" 
                    class="w-full h-full object-cover brightness-[0.8] transition-all duration-700"
                    alt="Promo Banner"
                >
                
                <div class="absolute inset-0 bg-gradient-to-t from-background via-transparent to-black/20"></div>

                <button @click="router.visit(route('customer.shop.index'))" 
                        class="absolute top-6 left-6 p-3 bg-background/40 backdrop-blur-xl rounded-full text-white hover:bg-primary hover:text-white transition-all shadow-2xl active:scale-95">
                    <ChevronLeft :size="24" stroke-width="3" />
                </button>

                <div class="absolute bottom-8 left-8 right-8 animate-in slide-in-from-bottom-4 duration-500">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="bg-primary text-white text-[10px] font-black px-2.5 py-1 rounded-md uppercase tracking-widest flex items-center gap-1 shadow-f1-glow">
                            <Zap :size="12" fill="currentColor" /> Pack Configurable
                        </span>
                    </div>
                    <h1 class="text-4xl md:text-6xl font-black text-white italic uppercase tracking-tighter leading-none mb-3 drop-shadow-2xl">
                        {{ bundleData.name }}
                    </h1>
                    <p class="text-white/90 text-sm md:text-lg font-medium max-w-3xl line-clamp-2 drop-shadow-md">
                        {{ bundleData.description }}
                    </p>
                </div>
            </div>

            <div class="max-w-7xl mx-auto px-6 py-12">
                <div v-if="items.length > 0" class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-8">
                    <div v-for="item in items" :key="item.sku_id" 
                         class="relative group bg-card rounded-[2.5rem] border-2 transition-all duration-500 p-5 flex flex-col items-center text-center shadow-apple-soft"
                         :class="selections[item.sku_id] ? 'border-primary scale-[1.03] bg-primary/5' : 'border-transparent hover:border-border'">
                        
                        <div v-if="selections[item.sku_id]" 
                             class="absolute -top-3 -right-3 bg-primary text-white rounded-full p-1.5 shadow-f1-glow animate-in zoom-in-50">
                            <CheckCircle2 :size="20" stroke-width="3" />
                        </div>

                        <div class="w-full aspect-square mb-6 overflow-hidden flex items-center justify-center p-2">
                            <img :src="getImageUrl(item.image)" 
                                 class="w-full h-full object-contain transition-transform duration-500 group-hover:scale-110 drop-shadow-xl"
                                 :alt="item.name">
                        </div>

                        <h3 class="text-sm font-extrabold text-foreground leading-snug mb-2 min-h-[40px] px-2">
                            {{ item.name }}
                        </h3>

                        <div class="text-[10px] text-muted-foreground uppercase font-bold tracking-widest mb-6 flex items-center gap-1 justify-center">
                            <Info :size="10" /> Max {{ item.max_qty }} por combo
                        </div>

                        <div class="flex items-center justify-between w-full bg-gray-100 dark:bg-white/5 rounded-3xl p-1.5 border border-border">
                            <button @click="updateQty(item.sku_id, -1, item.stock, item.max_qty)"
                                    class="w-12 h-12 flex items-center justify-center rounded-2xl bg-background shadow-sm hover:bg-red-50 dark:hover:bg-red-950/20 active:scale-90 transition-all text-primary">
                                <Minus :size="18" stroke-width="3" />
                            </button>
                            
                            <span class="font-black text-xl tabular-nums">{{ selections[item.sku_id] || 0 }}</span>
                            
                            <button @click="updateQty(item.sku_id, 1, item.stock, item.max_qty)"
                                    class="w-12 h-12 flex items-center justify-center rounded-2xl bg-background shadow-sm hover:bg-green-50 dark:hover:bg-green-950/20 active:scale-90 transition-all text-primary"
                                    :class="{ 'opacity-20 grayscale pointer-events-none': (selections[item.sku_id] || 0) >= item.stock }">
                                <Plus :size="18" stroke-width="3" />
                            </button>
                        </div>
                    </div>
                </div>

                <div v-else class="flex flex-col items-center justify-center py-20 text-center">
                    <AlertCircle :size="48" class="text-muted-foreground mb-4" />
                    <p class="text-muted-foreground font-medium italic">
                        Este combo no tiene productos disponibles actualmente.
                    </p>
                </div>
            </div>

            <div class="fixed bottom-0 inset-x-0 bg-background/80 backdrop-blur-2xl border-t border-border p-6 md:p-8 z-50 shadow-2xl-up">
                <div class="max-w-5xl mx-auto flex flex-col md:flex-row items-center justify-between gap-8">
                    
                    <div class="flex items-center gap-5">
                        <div class="w-16 h-16 rounded-[1.5rem] bg-primary/10 flex items-center justify-center text-primary shadow-inner">
                            <ShoppingBag :size="32" stroke-width="2.5" />
                        </div>
                        <div>
                            <p class="text-[10px] font-black uppercase text-muted-foreground tracking-[0.2em] mb-1">Tu Configuración</p>
                            <p class="text-2xl font-black text-foreground tracking-tighter tabular-nums">
                                {{ totalSelected }} <span class="text-lg font-bold text-muted-foreground">Productos</span>
                            </p>
                        </div>
                    </div>

                    <button @click="handleConfirm"
                            :disabled="!isReadyToOrder"
                            class="w-full md:w-auto px-16 py-6 rounded-[1.5rem] font-black text-lg uppercase tracking-tighter transition-all duration-500 shadow-f1-glow flex items-center justify-center gap-4 group"
                            :class="isReadyToOrder ? 'bg-primary text-white hover:bg-primary/90 active:scale-95' : 'bg-muted text-muted-foreground cursor-not-allowed'">
                        {{ isReadyToOrder ? 'Confirmar Selección' : 'Selecciona al menos 1' }}
                        <Zap v-if="isReadyToOrder" :size="20" class="group-hover:animate-pulse" fill="currentColor" />
                    </button>
                </div>
            </div>
        </div>
    </ShopLayout>
</template>

<style scoped>
/* Animación suave para el sticky footer al aparecer */
.shadow-2xl-up {
    box-shadow: 0 -25px 50px -12px rgba(0, 0, 0, 0.15);
}
</style>