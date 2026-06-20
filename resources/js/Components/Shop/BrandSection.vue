<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import { ChevronRight, Loader2, PackageX } from 'lucide-vue-next';

const props = defineProps({ brand: Object });
const emit = defineEmits(['open-modal']);

// REGLA 1: Estado aislado por marca
const data = ref(null);
const loading = ref(false);
let observer = null;

// REGLA 6: Fallback de imagen
const getImageUrl = (path) => {
    if (!path) return '/assets/img/placeholder.png';
    if (path.startsWith('http')) return path;
    return `/storage/${path.replace(/^\/+/, '')}`;
};

const load = () => {
    if (data.value || loading.value) return;
    loading.value = true;
    
    router.reload({
        data: { brand_id: props.brand.id },
        only: ['brandContent'],
        preserveScroll: true,
        onSuccess: (page) => {
            if (page.props.brandContent && page.props.brandContent.id === props.brand.id) {
                data.value = page.props.brandContent;
            }
        },
        onFinish: () => loading.value = false
    });
};

onMounted(() => {
    observer = new IntersectionObserver(([entry]) => {
        if (entry.isIntersecting) load();
    }, { rootMargin: '300px 0px' });
    
    const el = document.getElementById(`brand-section-${props.brand.id}`);
    if (el) observer.observe(el);
});

onUnmounted(() => {
    if (observer) observer.disconnect();
});
</script>

<template>
    <div :id="`brand-section-${brand.id}`" class="brand-section-container"> 
        
        <h2 class="sticky top-[152px] z-30 bg-[#FFFFFF] dark:bg-[#050505] py-2 text-lg font-bold tracking-[-0.02em] border-l-4 border-[#007AFF] dark:border-[#E10600] pl-3 text-gray-900 dark:text-white border-b border-gray-100 dark:border-[#262626] shadow-sm dark:shadow-none ml-4">
            {{ brand.name }}
        </h2>
        
        <div v-if="data">
            <template v-for="category in data.categories" :key="category.id">
                <div v-if="category.products && category.products.length > 0" class="mb-6 mt-4">
                    <h3 class="text-xs font-semibold tracking-[-0.01em] mb-3 px-4 flex items-center gap-2 text-gray-500 dark:text-gray-400">
                        <span class="w-2 h-2 rounded-full" :style="{ backgroundColor: category.bg_color || '#007AFF' }"></span> 
                        {{ category.name }}
                    </h3>
                    
                    <div class="flex overflow-x-auto gap-3 px-4 pb-2 snap-x snap-mandatory no-scrollbar scroll-smooth">
                        
                        <div v-for="product in category.products" :key="product.id" 
                            @click="emit('open-modal', product)"
                            class="w-[40%] shrink-0 snap-start bg-[#FFFFFF] dark:bg-[#121217] rounded-xl border border-transparent dark:border-[#262626] p-3 pb-3 relative flex flex-col active:scale-[0.98] transition-all overflow-hidden cursor-pointer group shadow-[0_4px_20px_rgba(0,0,0,0.05)] hover:shadow-[0_8px_30px_rgba(0,0,0,0.12)] dark:shadow-none dark:hover:border-[#E10600]/50">
                            
                            <div v-if="product.available_stock <= 0" class="absolute inset-0 z-30 bg-white/60 dark:bg-black/80 flex items-center justify-center backdrop-blur-[2px]">
                                <span class="bg-gray-900 dark:bg-white text-white dark:text-black text-[10px] font-bold px-2 py-1 rounded-lg tracking-[-0.01em]">Agotado</span>
                            </div>

                            <div class="relative w-full h-24 flex items-center justify-center mb-2 mt-1">
                                <div class="absolute inset-0 blur-[20px] rounded-full opacity-5 dark:opacity-10" :style="{ backgroundColor: category.bg_color || '#ffffff' }"></div>
                                <img :src="getImageUrl(product.image_url)" class="relative z-10 w-full h-full object-contain drop-shadow-md dark:drop-shadow-none group-hover:scale-105 transition-transform duration-300">
                            </div>
                            
                            <h4 class="text-xs font-semibold tracking-[-0.01em] leading-tight line-clamp-2 mb-2 h-8 text-gray-900 dark:text-white">{{ product.name }}</h4>

                            <div class="mt-auto border-t border-gray-50 dark:border-[#262626] pt-2 flex items-center justify-between">
                                <span class="text-[9px] font-black text-[#007AFF] dark:text-[#E10600] uppercase tracking-widest">Configurar</span>
                                <ChevronRight :size="12" class="text-gray-300 dark:text-gray-600 group-hover:text-[#E10600]" />
                            </div>
                        </div>
                        
                        <template v-if="category.products.length < 3">
                            <div v-for="n in (3 - category.products.length)" :key="'empty-'+n" 
                                 class="w-[40%] shrink-0 snap-start bg-[#FFFFFF] dark:bg-[#121217] rounded-xl border border-dashed border-gray-200 dark:border-[#262626] p-3 pb-3 relative flex flex-col items-center justify-center opacity-60 pointer-events-none shadow-none min-h-[160px]">
                                 <div class="w-10 h-10 mb-3 rounded-full bg-gray-50 dark:bg-[#050505] border border-gray-100 dark:border-[#262626] flex items-center justify-center">
                                     <PackageX class="w-4 h-4 text-gray-300 dark:text-gray-600" />
                                 </div>
                                 <span class="text-[10px] font-bold text-gray-400 dark:text-gray-600 uppercase tracking-[-0.01em]">Pronto...</span>
                            </div>
                        </template>
                        
                    </div>
                </div>
            </template>
        </div>

        <div v-else class="flex overflow-x-auto gap-3 px-4 pb-2 mt-4 no-scrollbar">
            <div v-for="i in 3" :key="i" class="w-[40%] h-36 shrink-0 rounded-xl bg-[#FFFFFF] dark:bg-[#121217] border border-transparent dark:border-[#262626] shadow-[0_4px_20px_rgba(0,0,0,0.05)] dark:shadow-none flex flex-col items-center justify-center gap-3 animate-pulse">
                <Loader2 v-if="loading" class="animate-spin text-[#007AFF]/40 dark:text-[#E10600]/40" :size="20" stroke-width="2.5" />
            </div>
        </div>
    </div>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
.brand-section-container { content-visibility: auto; contain-intrinsic-size: 1px 400px; }
</style>