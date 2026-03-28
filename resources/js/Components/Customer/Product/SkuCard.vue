<script setup>
import { computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { Plus, Minus, Zap } from 'lucide-vue-next';

const props = defineProps({
    sku: { type: Object, default: null },
    loading: { type: Boolean, default: false },
    isActive: { type: Boolean, default: false }
});

const page = usePage();

// --- LÓGICA DE IDENTIDAD VISUAL ---
const dynamicStyle = computed(() => {
    // Si no hay color, usamos var(--primary) como fallback
    const catColor = props.sku?.bg_color || 'var(--primary)';
    
    // Convertimos el valor en una variable CSS local para usarla en el <style>
    return {
        '--local-sku-color': catColor,
    };
});

// --- LÓGICA DE CARRITO ---
const cartItem = computed(() => {
    if (!props.sku) return null;
    const items = page.props.cart?.items || [];
    return items.find(item => item.sku_id === props.sku.id);
});

const quantity = computed(() => cartItem.value ? cartItem.value.quantity : 0);
const hasDiscount = computed(() => props.sku?.list_price > props.sku?.final_price);

const handleAdd = () => {
    if (props.loading) return;
    router.post(route('customer.cart.upsert'), {
        target_id: props.sku.id,
        target_type: 'sku',
        quantity: 1
    }, { preserveScroll: true, preserveState: true });
};

const updateQty = (delta) => {
    const newQty = quantity.value + delta;
    if (newQty < 1) {
        const itemId = cartItem.value?.id;
        if(itemId) router.delete(route('customer.cart.remove', itemId), { preserveScroll: true });
    } else {
        router.patch(route('customer.cart.update', cartItem.value.id), { quantity: newQty }, { preserveScroll: true });
    }
};

const goToProduct = () => {
    if (props.loading || !props.sku) return;
    router.visit(route('customer.shop.product', { id: props.sku.id }));
};
</script>

<template>
    <div v-if="!loading && sku" 
        @click="goToProduct"
        :style="dynamicStyle"
        class="group relative flex flex-col bg-card border border-border/40 rounded-3xl overflow-hidden transition-all duration-500 hover:shadow-2xl hover:shadow-[var(--local-sku-color)]/20 active:scale-[0.98]"
        :class="{ 'ring-2 ring-[var(--local-sku-color)] border-transparent': quantity > 0 || isActive }">
        
        <div class="h-1 w-full relative z-20" :style="{ backgroundColor: 'var(--local-sku-color)' }"></div>

        <div class="p-3 flex flex-col h-full relative z-10">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[9px] font-black uppercase tracking-[0.2em] text-foreground/80 truncate drop-shadow-md">
                    {{ sku.brand_name || 'GENERIC_ASSET' }}
                </span>
                <div v-if="sku.stock <= 5 && sku.stock > 0" class="flex items-center gap-1">
                    <Zap :size="10" class="text-accent fill-accent" />
                    <span class="text-[8px] font-black text-accent uppercase italic">Low_Stock</span>
                </div>
            </div>

            <div class="aspect-square relative rounded-2xl overflow-hidden mb-3 shadow-inner border border-white/10 static-split-bg">
                
                <img :src="sku.image" 
                     class="relative z-20 w-full h-full object-contain p-2 transition-transform duration-700 group-hover:scale-110 drop-shadow-2xl"
                     :alt="sku.name">
                
                <div v-if="hasDiscount" class="absolute top-2 left-2 z-30">
                    <span class="bg-primary text-white text-[8px] font-black px-1.5 py-0.5 rounded-md shadow-lg uppercase italic">
                        -{{ sku.discount_percentage }}%
                    </span>
                </div>

                <div class="absolute bottom-2 right-2 z-30">
                    <button v-if="quantity === 0" @click.stop="handleAdd"
                            class="w-10 h-10 bg-white/20 backdrop-blur-xl text-foreground rounded-full flex items-center justify-center border border-white/20 shadow-xl hover:bg-[var(--local-sku-color)] hover:text-white transition-all">
                        <Plus :size="18" stroke-width="3" />
                    </button>

                    <div v-else @click.stop class="flex items-center bg-[var(--local-sku-color)] text-white rounded-full p-0.5 shadow-lg animate-in zoom-in duration-300">
                        <button @click="updateQty(-1)" class="w-7 h-7 flex items-center justify-center rounded-full hover:bg-white/20 transition-colors">
                            <Minus :size="14" stroke-width="3"/>
                        </button>
                        <span class="w-6 text-center font-mono font-black text-xs">{{ quantity }}</span>
                        <button @click="updateQty(1)" class="w-7 h-7 flex items-center justify-center rounded-full hover:bg-white/20 transition-colors">
                            <Plus :size="14" stroke-width="3"/>
                        </button>
                    </div>
                </div>
            </div>

            <div class="flex-1 flex flex-col justify-between">
                <h3 class="text-[11px] font-black uppercase leading-[1.2] tracking-tight text-foreground line-clamp-2 mb-2 group-hover:text-[var(--local-sku-color)] transition-colors drop-shadow-sm">
                    {{ sku.name }}
                </h3>

                <div class="space-y-0.5">
                    <span v-if="hasDiscount" class="text-[9px] font-bold text-muted-foreground/50 line-through block leading-none font-mono">
                        {{ sku.list_price.toFixed(2) }}
                    </span>
                    <div class="flex items-baseline gap-1">
                        <span class="text-[9px] font-black text-[var(--local-sku-color)] uppercase">Bs</span>
                        <span class="text-xl font-black tracking-tighter text-foreground font-mono leading-none">
                            {{ sku.final_price.toFixed(2) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div v-else class="aspect-[3/4] bg-muted/20 border border-border/10 rounded-3xl animate-pulse overflow-hidden"></div>
</template>

<style scoped>
.font-mono { font-family: 'JetBrains Mono', monospace; }

/* Gradiente Estático Diagonal (Mitad Categoría, Mitad Primario)
  Se usa la técnica de "multi-capa" pura en CSS para evitar que Vue rompa las variables de Tailwind.
*/
.static-split-bg {
    /* Eliminamos el color de fondo sólido para que no interfiera */
    background-color: transparent;

    /* Creamos un flujo suave entre la categoría y el primario */
    /* Usamos 135deg para que el flujo sea de esquina superior izquierda a inferior derecha */
    background-image: linear-gradient(
        135deg,
        var(--local-sku-color) 0%,
        hsl(var(--primary)) 100%
    );
    
    /* Mantenemos la opacidad alta para que el color sea vibrante como en tu imagen */
    opacity: 1;
}

/* En Dark Mode, suavizamos un poco para que el PNG destaque más */
.dark .static-split-bg {
    opacity: 0.7;
    /* Añadimos un pequeño tinte negro para profundizar el degrade */
    background-image: linear-gradient(
        135deg,
        var(--local-sku-color),
        hsl(var(--primary)),
        #000000
    );
}
</style>