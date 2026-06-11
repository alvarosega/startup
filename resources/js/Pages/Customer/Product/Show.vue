<script setup>
import { computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { Plus, ChevronLeft, Zap } from 'lucide-vue-next';
import ShopLayout from '@/Layouts/ShopLayout.vue';

const props = defineProps({
    product: Object,
    variants: Array,
    activeSkuId: String
});

const addToCart = (skuId) => {
    router.post(route('customer.cart.upsert'), {
        target_id: skuId,
        target_type: 'sku',
        quantity: 1
    }, { preserveScroll: true });
};

// Algoritmo de Degradado F1 (Vertical - 15% Luminosidad en la base del producto)
const getVariantStyle = (hex) => {
    let cleanHex = hex ? hex.replace('#', '') : '32323b';
    
    if (cleanHex.length === 3) {
        cleanHex = cleanHex.split('').map(c => c + c).join('');
    }

    const r = parseInt(cleanHex.substring(0, 2), 16) || 0;
    const g = parseInt(cleanHex.substring(2, 4), 16) || 0;
    const b = parseInt(cleanHex.substring(4, 6), 16) || 0;

    const dr = Math.floor(r * 0.15);
    const dg = Math.floor(g * 0.15);
    const db = Math.floor(b * 0.15);

    const darkHex = [dr, dg, db].map(x => x.toString(16).padStart(2, '0')).join('');

    return {
        background: `linear-gradient(to bottom, #${cleanHex} 0%, #${darkHex} 100%)`
    };
};

const goBack = () => {
    window.history.back();
};
</script>

<template>
    <ShopLayout>
        <Head :title="product.name" />

        <div class="max-w-7xl mx-auto px-4 lg:px-8 py-12 relative z-10 text-foreground">
            <div class="flex flex-col md:flex-row gap-12 mb-16 items-start">
                
                <div class="w-full md:w-1/3 aspect-square bg-[#15151f] rounded-xl border border-[#32323b] p-8 flex items-center justify-center relative">
                    <img :src="product.main_image" class="w-full h-full object-contain drop-shadow-hardware z-10" :alt="product.name">
                </div>

                <div class="flex-1 space-y-6">
                    <button @click="goBack" class="flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-neutral-500 hover:text-primary transition-colors focus:outline-none outline-none">
                        <ChevronLeft :size="14" :stroke-width="2" /> Volver
                    </button>
                    
                    <div class="space-y-1">
                        <p class="text-primary font-black uppercase tracking-[0.2em] text-xs">{{ product.brand || 'DIGITAL UNIT' }}</p>
                        <h1 class="text-4xl md:text-6xl font-black uppercase italic tracking-tighter leading-none text-foreground">{{ product.name }}</h1>
                    </div>

                    <p class="text-neutral-500 dark:text-neutral-400 text-sm leading-relaxed max-w-2xl">
                        {{ product.description }}
                    </p>

                    <div class="flex items-center gap-4 pt-2">
                        <div class="flex items-center gap-2 px-3 py-1.5 bg-[#15151f] border border-[#32323b] rounded text-emerald-500 text-[9px] font-black uppercase tracking-widest">
                            <Zap :size="12" fill="currentColor" /> Disponibilidad inmediata
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <h2 class="text-[10px] font-black uppercase tracking-[0.4em] text-neutral-500 border-b border-[#32323b] pb-3">Opciones Disponibles</h2>
                
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    <div v-for="sku in variants" :key="sku.id"
                         :style="getVariantStyle(sku.bg_color)"
                         :class="[
                             'group rounded-xl border overflow-hidden relative flex flex-col justify-between min-h-[360px] transition-transform duration-150 ease-f1 active:scale-95',
                             String(sku.id) === String(activeSkuId) 
                                 ? 'border-primary ring-2 ring-primary ring-offset-2 ring-offset-[#15151f] z-10' 
                                 : 'border-[#32323b]'
                         ]">
                        
                        <div v-if="String(sku.id) === String(activeSkuId)" class="absolute top-3 left-3 bg-primary text-white px-2 py-0.5 rounded text-[8px] font-black uppercase tracking-widest whitespace-nowrap z-30">
                            Seleccionado
                        </div>

                        <div class="p-4 flex flex-col flex-1">
                            <div class="relative w-full h-[160px] mb-4 flex items-center justify-center p-2 rounded-lg">
                                <img :src="sku.image" class="absolute inset-0 w-full h-full object-contain p-2 transition-transform duration-500 ease-f1 group-hover:scale-110 drop-shadow-hardware z-20">
                            </div>

                            <div class="flex-1 flex flex-col justify-end">
                                <h3 class="font-black text-xs leading-tight uppercase text-white line-clamp-2 mb-1">{{ sku.name }}</h3>
                                <p class="text-xl font-black tracking-tighter text-white font-mono">Bs. {{ sku.final_price.toFixed(2) }}</p>
                            </div>
                        </div>

                        <div class="w-full h-12 border-t border-[#32323b] bg-[#15151f] flex items-center justify-between px-4 z-20">
                            <div class="flex items-center gap-1.5">
                                <div class="w-1.5 h-1.5 rounded-full" :class="sku.stock > 0 ? 'bg-emerald-500' : 'bg-red-500'"></div>
                                <span class="text-[9px] font-black uppercase tracking-widest text-white/70">{{ sku.stock > 0 ? 'En Stock' : 'Sin Stock' }}</span>
                            </div>
                            
                            <button @click="addToCart(sku.id)"
                                    :disabled="sku.stock <= 0"
                                    class="w-8 h-8 bg-primary text-white rounded flex items-center justify-center shadow-sm active:scale-90 transition-transform disabled:opacity-30 focus:outline-none outline-none">
                                <Plus :size="16" :stroke-width="3" />
                            </button>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </ShopLayout>
</template>

<style scoped>
.font-mono { font-family: 'JetBrains Mono', monospace; }
.ease-f1 { transition-timing-function: cubic-bezier(0.16, 1, 0.3, 1); }
.drop-shadow-hardware { filter: drop-shadow(0 15px 15px rgba(0,0,0,0.5)); }
</style>