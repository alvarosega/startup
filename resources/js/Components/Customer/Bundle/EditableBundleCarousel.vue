<script setup>
import { ref } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { Zap, Loader2, ShoppingBag, Plus } from 'lucide-vue-next';

const props = defineProps({
    bundles: { type: Array, default: () => [] },
    currentId: { type: String, default: null } // Identificador del pack activo
});

const processingId = ref(null);
const page = usePage();

// RECTIFICACIÓN: Formato .png unificado
const getPlaceholder = (isEditable) => {
    return (isEditable === true || isEditable === 1) 
        ? '/assets/img/bundle_banner_editable.png' 
        : '/assets/img/bundle_banner_noeditable.png';
};

const handleNavigate = (slug) => {
    const currentSlug = page.props.bundle?.data?.slug || page.props.bundle?.slug;
    if (currentSlug === slug) return;
    router.visit(route('customer.bundle', { slug }));
};

const handleQuickAdd = (bundleId) => {
    if (processingId.value) return;
    processingId.value = bundleId;
    router.post(route('customer.cart.add-template'), { bundle_id: bundleId }, {
        preserveScroll: true,
        onFinish: () => processingId.value = null
    });
};
</script>

<template>
    <div class="relative w-full overflow-hidden py-4">
        <div class="flex overflow-x-auto snap-x snap-mandatory gap-6 px-6 md:px-12 pb-8 hide-scrollbar">
            
            <div v-for="bundle in bundles" :key="bundle.id" 
                 @click="handleNavigate(bundle.slug)"
                 class="flex-none w-[85vw] md:w-[320px] lg:w-[350px] h-[340px] snap-start group relative rounded-[2.5rem] overflow-hidden bg-card border transition-all duration-500 cursor-pointer"
                 :class="bundle.id === currentId 
                    ? 'border-primary ring-4 ring-primary/10 shadow-2xl scale-[1.02]' 
                    : 'border-white/5 shadow-lg hover:border-primary/30'">
                
                <div v-if="bundle.id === currentId" class="absolute top-6 left-6 z-20 flex items-center gap-2 bg-primary text-white px-3 py-1 rounded-full text-[8px] font-black uppercase tracking-tighter shadow-xl">
                    <Zap :size="10" fill="currentColor" /> Pack Seleccionado
                </div>

                <img :src="bundle.image_url || getPlaceholder(bundle.is_editable)" 
                     class="absolute inset-0 w-full h-full object-cover transition-transform duration-[2s] group-hover:scale-110"
                     :class="bundle.id === currentId ? 'opacity-90' : 'opacity-60 group-hover:opacity-80'"
                     @error="(e) => e.target.src = getPlaceholder(bundle.is_editable)">
                
                <div class="absolute inset-0 bg-gradient-to-t from-black/95 via-black/40 to-transparent p-6 flex flex-col justify-end z-10 pointer-events-none">
                    <h3 class="text-2xl font-black text-white uppercase italic leading-none mb-2 tracking-tighter">{{ bundle.name }}</h3>
                    <p class="text-white/60 text-[10px] font-medium line-clamp-2 mb-6">{{ bundle.description }}</p>
                    
                    <div class="flex items-center justify-between mt-auto border-t border-white/10 pt-4">
                        <span class="text-[9px] font-black text-primary uppercase tracking-widest">{{ bundle.id === currentId ? 'Viendo ahora' : 'Carga Rápida' }}</span>
                        <button @click.stop="handleQuickAdd(bundle.id)" 
                                :disabled="processingId === bundle.id"
                                class="w-12 h-12 bg-white/10 backdrop-blur-md border border-white/20 text-white rounded-2xl flex items-center justify-center hover:bg-primary transition-all pointer-events-auto">
                            <Loader2 v-if="processingId === bundle.id" class="animate-spin" :size="16" />
                            <div v-else class="relative">
                                <ShoppingBag :size="18" />
                                <Plus :size="8" stroke-width="4" class="absolute -top-1 -right-1 bg-white text-primary rounded-full" />
                            </div>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>