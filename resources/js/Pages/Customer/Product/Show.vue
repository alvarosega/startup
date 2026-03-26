<script setup>
import { computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { Plus, ChevronLeft, Zap, Info } from 'lucide-vue-next';
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
</script>

<template>
    <ShopLayout>
        <Head :title="product.name" />

        <div class="max-w-7xl mx-auto px-6 py-12">
            <div class="flex flex-col md:flex-row gap-12 mb-20 items-start">
                <div class="w-full md:w-1/3 aspect-square bg-card rounded-[3rem] border border-border/50 p-12 flex items-center justify-center">
                    <img :src="product.main_image" class="w-full h-full object-contain" :alt="product.name">
                </div>

                <div class="flex-1 space-y-6">
                    <button @click="window.history.back()" class="flex items-center gap-2 text-xs font-black uppercase tracking-widest text-muted-foreground hover:text-primary transition-colors">
                        <ChevronLeft :size="16" stroke-width="3" /> Volver
                    </button>
                    
                    <div class="space-y-2">
                        <p class="text-primary font-black uppercase tracking-[0.3em] text-xs">{{ product.brand }}</p>
                        <h1 class="text-5xl md:text-7xl font-black uppercase italic tracking-tighter leading-none">{{ product.name }}</h1>
                    </div>

                    <p class="text-muted-foreground text-lg leading-relaxed italic max-w-2xl">
                        {{ product.description }}
                    </p>

                    <div class="flex items-center gap-4 py-4">
                        <div class="flex items-center gap-2 px-4 py-2 bg-accent/10 border border-accent/20 rounded-full text-accent text-[10px] font-black uppercase tracking-widest">
                            <Zap :size="14" fill="currentColor" /> Disponibilidad inmediata
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-8">
                <h2 class="text-xs font-black uppercase tracking-[0.5em] text-muted-foreground/60 border-b border-border/50 pb-4">Opciones Disponibles</h2>
                
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                    <div v-for="sku in variants" :key="sku.id"
                         :class="[
                            'group bg-card rounded-[2.5rem] p-6 border transition-all duration-500 relative flex flex-col',
                            sku.id === activeSkuId 
                                ? 'ring-4 ring-primary border-transparent shadow-2xl scale-105 z-10' 
                                : 'border-border/50 hover:border-primary/50 shadow-sm'
                         ]">
                        
                        <div v-if="sku.id === activeSkuId" class="absolute -top-3 left-1/2 -translate-x-1/2 bg-primary text-white px-4 py-1 rounded-full text-[9px] font-black uppercase tracking-widest whitespace-nowrap">
                            Seleccionado
                        </div>

                        <div class="aspect-square bg-muted/20 rounded-[2rem] p-6 mb-6 flex items-center justify-center">
                            <img :src="sku.image" class="w-full h-full object-contain group-hover:scale-110 transition-transform">
                        </div>

                        <div class="flex-1 space-y-4">
                            <div>
                                <h3 class="font-black text-lg leading-tight uppercase">{{ sku.name }}</h3>
                                <p class="text-2xl font-black tracking-tighter mt-1">Bs. {{ sku.final_price.toFixed(2) }}</p>
                            </div>

                            <div class="flex items-center justify-between gap-4">
                                <div class="flex items-center gap-2">
                                    <div class="w-2 h-2 rounded-full" :class="sku.stock > 0 ? 'bg-accent' : 'bg-destructive'"></div>
                                    <span class="text-[9px] font-black uppercase tracking-widest text-muted-foreground">{{ sku.stock > 0 ? 'En Stock' : 'Sin Stock' }}</span>
                                </div>
                                
                                <button @click="addToCart(sku.id)"
                                        :disabled="sku.stock <= 0"
                                        class="w-12 h-12 bg-primary text-white rounded-full flex items-center justify-center shadow-lg active:scale-90 transition-transform disabled:opacity-30">
                                    <Plus :size="20" stroke-width="4" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </ShopLayout>
</template>