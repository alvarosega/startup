<script setup>
import { computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { Plus, Minus, Loader2, Zap } from 'lucide-vue-next';

const props = defineProps({
    sku: { type: Object, required: true },
    isActive: { type: Boolean, default: false } // Para el borde de "Seleccionado"
});

const page = usePage();

const cartItem = computed(() => {
    // page.props.cart ahora contiene la estructura de CartResource gracias al middleware
    const items = page.props.cart?.items || [];
    return items.find(item => item.sku_id === props.sku.id);
});

const quantity = computed(() => cartItem.value ? cartItem.value.quantity : 0);

// --- ESTADOS DE CARGA (Local al componente) ---
const isProcessing = computed(() => page.component === 'Processing...'); // Opcional: manejar via estado global

/**
 * ACCIONES TRANSACCIONALES
 */
const handleAddFirstTime = () => {
    router.post(route('customer.cart.upsert'), {
        target_id: props.sku.id,
        target_type: 'sku',
        quantity: 1
    }, { preserveScroll: true });
};

const handleUpdateQuantity = (newQty) => {
    if (newQty < 1) {
        // Si baja de 1, eliminamos la línea
        router.delete(route('customer.cart.remove', cartItem.value.id), { preserveScroll: true });
    } else {
        // Si cambia cantidad, disparamos la acción que recalcula Tiers
        router.patch(route('customer.cart.update', cartItem.value.id), { 
            quantity: newQty 
        }, { preserveScroll: true });
    }
};

const navigateToVariants = () => {
    router.visit(route('customer.product.show', { 
        id: props.sku.product_id, 
        active_sku: props.sku.id 
    }));
};
</script>

<template>
    <div @click="navigateToVariants"
         :class="[
            'group bg-card rounded-[2.5rem] p-4 transition-all duration-500 hover:shadow-2xl flex flex-col h-full border overflow-hidden cursor-pointer active:scale-[0.98]',
            isActive ? 'ring-4 ring-primary border-transparent' : 'border-border/40'
         ]">
        
        <div class="aspect-square mb-5 rounded-[2rem] bg-muted/30 flex items-center justify-center p-8 relative overflow-hidden">
            <img :src="sku.image" class="w-full h-full object-contain group-hover:scale-110 transition-transform duration-700" :alt="sku.name">

            <div v-if="sku.discount_percentage > 0" 
                 class="absolute top-4 left-4 bg-primary text-primary-foreground px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest">
                -{{ sku.discount_percentage }}%
            </div>

            <div class="absolute bottom-4 right-4 z-20">
                <button v-if="quantity === 0"
                        @click.stop="handleAddFirstTime"
                        class="w-12 h-12 bg-card rounded-full flex items-center justify-center shadow-xl border border-border hover:bg-primary hover:text-white transition-all active:scale-90">
                    <Plus :size="24" stroke-width="3" />
                </button>

                <div v-else 
                     @click.stop
                     class="flex items-center bg-card rounded-full border border-primary/30 shadow-2xl p-1 overflow-hidden animate-in zoom-in duration-300">
                    <button @click="handleUpdateQuantity(quantity - 1)" 
                            class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-muted text-primary transition-colors">
                        <Minus :size="16" stroke-width="3"/>
                    </button>
                    
                    <span class="w-8 text-center font-mono font-black text-sm text-foreground">
                        {{ quantity }}
                    </span>

                    <button @click="handleUpdateQuantity(quantity + 1)" 
                            class="w-8 h-8 flex items-center justify-center rounded-full bg-primary text-white shadow-lg transition-transform active:scale-95">
                        <Plus :size="16" stroke-width="3"/>
                    </button>
                </div>
            </div>
        </div>

        <div class="px-3 pb-2 flex-1 flex flex-col">
            <div class="mb-1">
                <span v-if="sku.list_price > sku.final_price" class="text-[11px] font-bold text-muted-foreground/40 line-through block leading-none">
                    Bs. {{ sku.list_price.toFixed(2) }}
                </span>
                <span class="text-2xl font-black tracking-tighter text-foreground leading-tight">
                    Bs. {{ sku.final_price.toFixed(2) }}
                </span>
            </div>

            <div class="space-y-0.5">
                <h3 class="font-bold text-[15px] leading-snug text-foreground line-clamp-2 min-h-[40px]">
                    {{ sku.name }}
                </h3>
                <p class="text-[10px] font-black uppercase tracking-[0.15em] text-muted-foreground/60">
                    {{ sku.brand_name }}
                </p>
            </div>

            <div class="mt-4 flex items-center gap-2">
                <div class="w-2 h-2 rounded-full" :class="sku.stock > 0 ? 'bg-accent animate-pulse' : 'bg-destructive'"></div>
                <p class="text-[9px] font-black uppercase tracking-widest" :class="sku.stock > 0 ? 'text-accent' : 'text-destructive'">
                    {{ sku.stock > 0 ? 'Disponible' : 'Agotado' }}
                </p>
            </div>
        </div>
    </div>
</template>