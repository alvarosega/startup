<script setup>
import { defineProps, defineEmits } from 'vue';

const props = defineProps({
    title: { type: String, required: true },
    products: { type: Array, required: true, default: () => [] }
});

const emit = defineEmits(['product-click']);
</script>

<template>
    <div v-if="products && products.length > 0" class="mb-14 pl-6 animate-in fade-in duration-700 slide-in-from-bottom-4">
        
        <div class="flex items-center gap-3 mb-6">
            <div class="h-px w-8 bg-gray-300"></div>
            <h3 class="text-lg font-black text-gray-800 uppercase tracking-widest">{{ title }}</h3>
            <div class="h-px flex-1 bg-gray-100"></div>
        </div>

        <div class="flex gap-6 overflow-x-auto pb-10 pt-2 px-2 scrollbar-hide snap-x">
            
            <div v-for="product in products" :key="product.id" 
                 class="snap-center shrink-0 w-[180px] h-[280px] relative group cursor-pointer"
                 @click="$emit('product-click', product)">
                
                <div class="absolute bottom-0 w-full h-[88%] bg-white rounded-xl shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] border border-gray-100 transition-all duration-300 group-hover:shadow-[0_20px_40px_-10px_rgba(0,0,0,0.15)] group-hover:-translate-y-1 group-hover:border-blue-100 overflow-hidden">
                    
                    <div class="h-16 bg-gradient-to-b from-gray-50 to-white"></div>

                    <div class="absolute bottom-0 w-full p-4">
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">{{ product.brand }}</p>
                        <h4 class="text-sm font-bold text-gray-800 leading-tight mb-3 line-clamp-2 h-9">{{ product.name }}</h4>
                        
                        <div class="flex items-center justify-between border-t border-gray-50 pt-3">
                            <div>
                                <span class="text-xs text-gray-400 block">Precio</span>
                                <span class="text-lg font-black text-gray-900">Bs {{ product.price_display }}</span>
                            </div>
                            <button class="w-8 h-8 rounded-full bg-gray-900 text-white flex items-center justify-center hover:bg-blue-600 transition shadow-md group-hover:scale-110">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="absolute top-0 left-0 w-full h-[55%] z-10 flex items-center justify-center transition-transform duration-500 group-hover:-translate-y-3 group-hover:scale-105">
                    <div class="absolute bottom-2 w-16 h-2 bg-black/20 blur-md rounded-full transform scale-0 group-hover:scale-100 transition-transform duration-500"></div>
                    <img :src="product.image_url" :alt="product.name" class="w-[80%] h-full object-contain drop-shadow-xl">
                </div>

            </div>
        </div>
    </div>
</template>

<style scoped>
.scrollbar-hide::-webkit-scrollbar { display: none; }
.scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
</style>