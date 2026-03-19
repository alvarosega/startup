<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import ShopLayout from '@/Layouts/ShopLayout.vue';
import ProductConfigurationSheet from '@/Components/Shop/ProductConfigurationSheet.vue';
import { ArrowLeft, ChevronRight, Ban } from 'lucide-vue-next';

const props = defineProps({
    category: Object,
    products: { type: Array, default: () => [] }
});

const selectedProductForModal = ref(null);

// Regla 6: Fallback de imagen estandarizado
const getImageUrl = (path) => {
    if (!path) return '/assets/img/placeholder.png';
    if (path.startsWith('http')) return path;
    return `/storage/${path.replace(/^\/+/, '')}`;
};

const goBack = () => router.visit(route('customer.shop.index'));
</script>

<template>
    <ShopLayout>
        <Head :title="category?.name" />
        
        <div class="min-h-screen pb-32 bg-[#FFFFFF] dark:bg-[#050505]">
            <header class="sticky top-[64px] z-50 bg-[#FFFFFF]/80 dark:bg-[#050505]/80 backdrop-blur-xl border-b border-gray-100 dark:border-[#262626] px-4 py-3">
                <div class="flex items-center justify-center relative w-full h-8">
                    <button @click="goBack" class="absolute left-0 w-8 h-8 flex items-center justify-center rounded-lg bg-gray-50 dark:bg-[#121217] border border-transparent dark:border-[#262626] active:scale-95 transition-transform">
                        <ArrowLeft class="w-4 text-gray-900 dark:text-white" stroke-width="2.5" />
                    </button>
                    
                    <div class="flex items-center gap-2">
                        <div class="w-2.5 h-2.5 rounded-full shadow-lg" :style="{ backgroundColor: category?.bg_color || '#007AFF' }"></div>
                        <h1 class="font-bold text-lg tracking-[-0.02em] text-gray-900 dark:text-white truncate max-w-[200px]">
                            {{ category?.name }}
                        </h1>
                    </div>
                </div>
            </header>

            <div class="container mx-auto py-6 px-4">
                <div v-if="products.length > 0" class="grid grid-cols-2 gap-4">
                    <div v-for="product in products" :key="product.id" 
                         @click="selectedProductForModal = product"
                         class="bg-[#FFFFFF] dark:bg-[#121217] rounded-2xl border border-gray-100 dark:border-[#262626] p-4 pb-4 relative flex flex-col active:scale-[0.97] transition-all cursor-pointer group shadow-sm dark:shadow-none hover:border-[#007AFF] dark:hover:border-[#E10600]/50">
                        
                        <div v-if="product.skus.every(s => s.available_stock <= 0)" class="absolute inset-0 z-10 bg-white/40 dark:bg-black/60 backdrop-blur-[1px] flex items-center justify-center rounded-2xl">
                            <span class="bg-gray-900 dark:bg-white text-white dark:text-black text-[9px] font-black px-2 py-0.5 rounded uppercase tracking-tighter">Agotado</span>
                        </div>

                        <div class="relative w-full h-32 flex items-center justify-center mb-4">
                            <div class="absolute inset-0 blur-[25px] rounded-full opacity-5 dark:opacity-10" :style="{ backgroundColor: category?.bg_color || '#007AFF' }"></div>
                            <img :src="getImageUrl(product.image_url)" class="relative z-10 w-full h-full object-contain drop-shadow-xl group-hover:scale-110 transition-transform duration-500">
                        </div>
                        
                        <h4 class="text-xs font-bold tracking-tight leading-tight line-clamp-2 mb-3 h-8 text-gray-800 dark:text-gray-100">
                            {{ product.name }}
                        </h4>

                        <div class="mt-auto pt-3 border-t border-gray-50 dark:border-[#262626] flex items-center justify-between">
                            <div class="flex flex-col">
                                <span class="text-[7px] font-black text-gray-400 uppercase tracking-widest">Desde</span>
                                <span class="text-xs font-mono font-black text-[#007AFF] dark:text-[#E10600]">
                                    Bs {{ product.min_price?.toFixed(2) }}
                                </span>
                            </div>
                            <ChevronRight :size="12" class="text-gray-300 dark:text-gray-600 group-hover:text-[#E10600] transition-colors" />
                        </div>
                    </div>
                </div>

                <div v-else class="flex flex-col items-center justify-center py-24 text-center opacity-40">
                    <Ban :size="48" stroke-width="1.5" class="mb-4" />
                    <h2 class="text-sm font-black uppercase tracking-widest">Sin Productos</h2>
                </div>
            </div>
        </div>

        <ProductConfigurationSheet 
            :product="selectedProductForModal" 
            @close="selectedProductForModal = null" 
        />
    </ShopLayout>
</template>