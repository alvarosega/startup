<script setup>
import { computed } from 'vue';
import { router, usePage, Link } from '@inertiajs/vue3';
import { Plus, Minus, Package, Zap, ChevronRight } from 'lucide-vue-next';

const props = defineProps({
    sku: { type: Object, default: null }, // Ahora es opcional para permitir el modo loading
    loading: { type: Boolean, default: false },
    isActive: { type: Boolean, default: false }
});

const page = usePage();

// --- PROTECCIÓN DE COMPUTED ---
const cartItem = computed(() => {
    if (!props.sku) return null;
    const items = page.props.cart?.items || [];
    return items.find(item => item.sku_id === props.sku.id);
});

const quantity = computed(() => cartItem.value ? cartItem.value.quantity : 0);

/**
 * ACCIONES (Solo ejecutables si no está cargando)
 */
const handleAddFirstTime = () => {
    if (props.loading) return;
    router.post(route('customer.cart.upsert'), {
        target_id: props.sku.id,
        target_type: 'sku',
        quantity: 1
    }, { preserveScroll: true });
};

const handleUpdateQuantity = (newQty) => {
    if (props.loading) return;
    if (newQty < 1) {
        router.delete(route('customer.cart.remove', cartItem.value.id), { preserveScroll: true });
    } else {
        router.patch(route('customer.cart.update', cartItem.value.id), { 
            quantity: newQty 
        }, { preserveScroll: true });
    }
};

const navigateToVariants = () => {
    if (props.loading || !props.sku) return;
    router.visit(route('customer.product.show', { 
        id: props.sku.product_id, 
        active_sku: props.sku.id 
    }));
};
</script>

<template>
    <div @click="navigateToVariants"
         :class="[
            'product-card group p-3 flex flex-col h-full cursor-pointer active:scale-[0.97]',
            isActive ? 'ring-2 ring-primary border-transparent' : 'border-card-border',
            loading ? 'pointer-events-none' : ''
         ]">
        
        <div class="product-image-container relative bg-foreground/[0.03] dark:bg-foreground/[0.01] rounded-[1.5rem] mb-4 overflow-hidden">
            <template v-if="!loading && sku">
                <img :src="sku.image" 
                     class="product-img group-hover:scale-105" 
                     :alt="sku.name">

                <div v-if="sku.discount_percentage > 0" 
                     class="absolute top-3 left-3 bg-primary text-white px-2 py-0.5 rounded-md text-[9px] font-black uppercase tracking-tighter shadow-f1-glow">
                    -{{ sku.discount_percentage }}%
                </div>

                <div class="absolute bottom-3 right-3 z-20">
                    <button v-if="quantity === 0"
                            @click.stop="handleAddFirstTime"
                            class="w-10 h-10 bg-card text-foreground rounded-full flex items-center justify-center shadow-xl border border-border/50 hover:bg-primary hover:text-white transition-all active:scale-90">
                        <Plus :size="20" stroke-width="3" />
                    </button>

                    <div v-else @click.stop class="flex items-center bg-card rounded-full border border-primary/30 shadow-2xl p-1 animate-in zoom-in duration-300">
                        <button @click="handleUpdateQuantity(quantity - 1)" class="w-7 h-7 flex items-center justify-center rounded-full hover:bg-muted text-primary"><Minus :size="14" stroke-width="3"/></button>
                        <span class="w-6 text-center font-mono font-black text-xs">{{ quantity }}</span>
                        <button @click="handleUpdateQuantity(quantity + 1)" class="w-7 h-7 flex items-center justify-center rounded-full bg-primary text-white shadow-lg"><Plus :size="14" stroke-width="3"/></button>
                    </div>
                </div>
            </template>
            
            <div v-else class="w-full h-full flex items-center justify-center">
                <div class="w-2/3 h-2/3 skeleton rounded-full opacity-20"></div>
            </div>
        </div>

        <div class="flex-1 flex flex-col px-1">
            <template v-if="!loading && sku">
                <div class="mb-2">
                    <span v-if="sku.list_price > sku.final_price" class="text-[10px] font-bold text-muted-foreground/30 line-through block leading-none">
                        {{ sku.list_price.toFixed(2) }}
                    </span>
                    <div class="flex items-baseline gap-1">
                        <span class="text-[10px] font-black text-primary uppercase">Bs</span>
                        <span class="text-xl font-black tracking-tighter text-foreground font-mono">
                            {{ sku.final_price.toFixed(2) }}
                        </span>
                    </div>
                </div>

                <div class="space-y-1">
                    <h3 class="font-bold text-[13px] leading-tight text-foreground line-clamp-2 min-h-[32px] group-hover:text-primary transition-colors">
                        {{ sku.name }}
                    </h3>
                    <div class="flex items-center justify-between">
                        <p class="text-[9px] font-black uppercase tracking-widest text-muted-foreground/50 italic">
                            {{ sku.brand_name }}
                        </p>
                        <div class="flex items-center gap-1.5">
                            <div class="w-1.5 h-1.5 rounded-full" :class="sku.stock > 0 ? 'bg-accent shadow-[0_0_5px_hsl(var(--accent))]' : 'bg-destructive'"></div>
                            <span class="text-[8px] font-black uppercase tracking-tighter" :class="sku.stock > 0 ? 'text-accent' : 'text-destructive'">
                                {{ sku.stock > 0 ? 'ONLINE' : 'OUT' }}
                            </span>
                        </div>
                    </div>
                </div>
            </template>

            <div v-else class="space-y-3">
                <div class="h-6 w-24 skeleton rounded-md"></div>
                <div class="space-y-2">
                    <div class="h-4 w-full skeleton rounded-md"></div>
                    <div class="h-4 w-2/3 skeleton rounded-md"></div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Las clases .product-card, .skeleton y .ease-ios ya están en tu main.css */
.product-card {
    min-height: 340px; /* Previene saltos de layout */
}
</style>