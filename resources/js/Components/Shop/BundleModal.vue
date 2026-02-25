<script setup>
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { X, ShoppingCart, Plus, Minus, Loader2 } from 'lucide-vue-next';
import axios from 'axios';

const props = defineProps({ show: Boolean, bundleSlug: String });
const emit = defineEmits(['close']);

const bundle = ref(null);
const loading = ref(false);
const quantity = ref(1);

watch(() => props.show, async (newVal) => {
    if (newVal && props.bundleSlug) {
        loading.value = true;
        const res = await axios.get(route('customer.cart.bundle.show', props.bundleSlug));
        bundle.value = res.data;
        loading.value = false;
    }
});

const submit = () => {
    router.post(route('customer.cart.bundle.add'), {
        bundle_id: bundle.value.id,
        quantity: quantity.value,
        guest_client_uuid: localStorage.getItem('guest_client_uuid')
    }, {
        onSuccess: () => emit('close'),
        preserveScroll: true
    });
};
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
        <div class="bg-card w-full max-w-md rounded-[32px] overflow-hidden border border-white/10 shadow-2xl">
            <div v-if="loading" class="p-20 flex justify-center"><Loader2 class="animate-spin text-primary" /></div>
            
            <div v-else-if="bundle" class="relative">
                <button @click="$emit('close')" class="absolute top-4 right-4 z-10 bg-black/20 rounded-full p-1"><X /></button>
                
                <div class="h-40 bg-gradient-to-br from-primary/20 to-transparent flex items-center justify-center p-6">
                    <img :src="bundle.image_path" class="h-full object-contain">
                </div>

                <div class="p-6">
                    <h3 class="text-2xl font-black uppercase italic">{{ bundle.name }}</h3>
                    <p class="text-sm text-muted-foreground mt-1">{{ bundle.description }}</p>

                    <div class="mt-6 space-y-3">
                        <div v-for="sku in bundle.skus" :key="sku.id" class="flex items-center gap-3 bg-white/5 p-2 rounded-xl border border-white/5">
                            <span class="w-8 h-8 rounded-lg bg-primary/10 text-primary flex items-center justify-center font-black text-xs">
                                x{{ sku.pivot.quantity }}
                            </span>
                            <span class="text-xs font-bold flex-1">{{ sku.product.name }}</span>
                        </div>
                    </div>

                    <div class="mt-8 flex items-center justify-between">
                        <div class="flex items-center bg-muted rounded-xl p-1">
                            <button @click="quantity > 1 && quantity--" class="w-10 h-10 flex items-center justify-center"><Minus :size="16" /></button>
                            <span class="w-10 text-center font-mono font-bold">{{ quantity }}</span>
                            <button @click="quantity++" class="w-10 h-10 flex items-center justify-center"><Plus :size="16" /></button>
                        </div>
                        <div class="text-right">
                            <p class="text-[10px] uppercase font-black text-primary">Precio Pack</p>
                            <p class="text-2xl font-black">Bs {{ (bundle.fixed_price * quantity).toFixed(2) }}</p>
                        </div>
                    </div>

                    <button @click="submit" class="w-full mt-6 py-4 bg-primary text-black font-black uppercase rounded-2xl flex items-center justify-center gap-3 shadow-lg shadow-primary/20">
                        <ShoppingCart :size="20" /> Añadir todo al pedido
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>