<script setup>
import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { Plus, Minus, Loader2, Trash2 } from 'lucide-vue-next';

const props = defineProps({
    sku: { type: Object, required: true },
    cartItem: { type: Object, default: null }
});

const quantity = computed(() => props.cartItem ? props.cartItem.quantity : 0);
const isProcessing = ref(false);

const handleAdd = (e) => {
    e.preventDefault();
    e.stopPropagation();
    if (isProcessing.value || props.sku.stock <= 0) return;
    
    isProcessing.value = true;
    router.post(route('customer.cart.upsert'), {
        target_id: props.sku.id,
        target_type: 'sku',
        quantity: 1
    }, {
        preserveScroll: true, 
        preserveState: true,
        only: ['cart', 'flash'],
        onFinish: () => isProcessing.value = false
    });
};

const updateQty = (e, delta) => {
    e.preventDefault();
    e.stopPropagation();
    if (isProcessing.value) return;

    const newQty = quantity.value + delta;
    if (delta > 0 && newQty > props.sku.stock) return;

    isProcessing.value = true;
    const options = {
        preserveScroll: true, 
        preserveState: true,
        only: ['cart', 'flash'],
        onFinish: () => isProcessing.value = false
    };

    if (newQty < 1) {
        router.delete(route('customer.cart.remove', props.cartItem.id), options);
    } else {
        router.patch(route('customer.cart.update', props.cartItem.id), { quantity: newQty }, options);
    }
};
</script>

<template>
    <div class="w-full h-12 mt-auto border-t border-[#32323b] bg-[#15151f] rounded-b-xl overflow-hidden z-20">
        
        <button v-if="quantity === 0" 
                @click="handleAdd"
                :disabled="sku.stock <= 0 || isProcessing"
                class="w-full h-full flex items-center justify-center gap-2 bg-transparent text-white hover:bg-primary transition-colors disabled:opacity-50 disabled:hover:bg-transparent outline-none group focus:outline-none">
            <Loader2 v-if="isProcessing" :size="16" class="animate-spin" />
            <Plus v-else :size="18" :stroke-width="2" class="group-hover:scale-110 transition-transform" />
            <span class="text-[11px] font-black uppercase tracking-[0.2em] pt-0.5">Añadir</span>
        </button>

        <div v-else class="w-full h-full flex items-center justify-between px-2 bg-primary">
            <button @click="(e) => updateQty(e, -1)" :disabled="isProcessing"
                    class="w-10 h-8 flex items-center justify-center bg-black/20 hover:bg-black/40 rounded transition-colors active:scale-95 outline-none focus:outline-none">
                <Trash2 v-if="quantity === 1" :size="14" :stroke-width="2" class="text-white" />
                <Minus v-else :size="16" :stroke-width="2" class="text-white" />
            </button>

            <div class="flex items-center justify-center w-12">
                <Loader2 v-if="isProcessing" :size="14" class="animate-spin text-white" />
                <span v-else class="font-mono font-black text-white text-base leading-none">{{ quantity }}</span>
            </div>

            <button @click="(e) => updateQty(e, 1)" :disabled="isProcessing || quantity >= sku.stock"
                    class="w-10 h-8 flex items-center justify-center bg-black/20 hover:bg-black/40 rounded transition-colors active:scale-95 disabled:opacity-40 outline-none focus:outline-none">
                <Plus :size="16" :stroke-width="2" class="text-white" />
            </button>
        </div>
    </div>
</template>

<style scoped>
.font-mono { font-family: 'JetBrains Mono', monospace; }
</style>