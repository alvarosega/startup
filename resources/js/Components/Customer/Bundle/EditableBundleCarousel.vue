<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { Sparkles, ArrowRight, ShoppingBag, Loader2, Plus } from 'lucide-vue-next';

const props = defineProps({
    bundles: { type: Array, default: () => [] }
});

const processingId = ref(null);

/**
 * NAVEGACIÓN: Lleva a la vista de configuración del template.
 */
const navigateToBundle = (slug) => {
    router.visit(route('customer.bundle', { slug }));
};

/**
 * ADICIÓN MASIVA: Dispara el motor AddTemplateToCartAction.
 * Se usa el bundle_id (UUID) para la transacción atómica.
 */
const quickAddBundle = (bundleId) => {
    if (processingId.value) return;
    
    processingId.value = bundleId;
    router.post(route('customer.cart.add-template'), {
        bundle_id: bundleId
    }, {
        preserveScroll: true,
        onFinish: () => processingId.value = null
    });
};
</script>

<template>
    <div class="relative w-full overflow-hidden">
        <div class="flex overflow-x-auto snap-x snap-mandatory gap-6 px-6 md:px-12 pb-10 no-scrollbar">
            
            <div v-for="bundle in bundles" :key="bundle.id"
                 class="flex-none w-[85vw] md:w-[400px] snap-center group relative">
                
                <div class="absolute -inset-1 bg-gradient-to-r from-primary/20 to-accent/20 rounded-[3rem] blur-2xl opacity-0 group-hover:opacity-100 transition duration-700"></div>

                <div class="relative h-[480px] bg-card/40 backdrop-blur-xl border border-white/10 rounded-[3rem] overflow-hidden flex flex-col shadow-2xl transition-all duration-500 group-hover:-translate-y-2">
                    
                    <div @click="navigateToBundle(bundle.slug)" class="relative h-2/3 w-full overflow-hidden cursor-pointer">
                        <img :src="bundle.image_url" 
                             :alt="bundle.name"
                             class="w-full h-full object-cover transition-transform duration-1000 group-hover:scale-110">
                        
                        <div class="absolute inset-0 bg-gradient-to-t from-card via-card/20 to-transparent"></div>

                        <div class="absolute top-6 left-6 flex flex-col gap-2">
                            <span class="px-4 py-1.5 bg-primary text-white rounded-full text-[9px] font-black uppercase tracking-widest flex items-center gap-2 shadow-lg">
                                <Sparkles :size="10" fill="currentColor"/> Pack Personalizable
                            </span>
                        </div>

                        <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                            <div class="bg-white/10 backdrop-blur-md border border-white/20 p-4 rounded-full text-white">
                                <ArrowRight :size="24" stroke-width="3" />
                            </div>
                        </div>
                    </div>

                    <div class="flex-1 p-8 flex flex-col justify-between -mt-12 relative z-10">
                        <div @click="navigateToBundle(bundle.slug)" class="cursor-pointer">
                            <h3 class="text-3xl font-black text-foreground uppercase italic leading-none tracking-tighter mb-2 group-hover:text-primary transition-colors">
                                {{ bundle.name }}
                            </h3>
                            <p class="text-foreground/50 text-xs font-medium line-clamp-2 leading-relaxed">
                                {{ bundle.description }}
                            </p>
                        </div>

                        <div class="flex items-center justify-between mt-4">
                            <div class="flex flex-col">
                                <span class="text-[9px] font-black text-primary uppercase tracking-[0.2em] leading-none mb-1">Carga Rápida</span>
                                <span class="text-[10px] font-bold text-foreground/40 italic">
                                    Añadir todo el pack
                                </span>
                            </div>

                            <button @click="quickAddBundle(bundle.id)" 
                                    :disabled="processingId === bundle.id"
                                    class="w-16 h-16 bg-primary text-white rounded-[1.8rem] flex items-center justify-center shadow-xl shadow-primary/30 hover:scale-110 active:scale-95 transition-all disabled:opacity-50">
                                <Loader2 v-if="processingId === bundle.id" class="animate-spin" :size="24" />
                                <template v-else>
                                    <ShoppingCart v-if="false" /> <div class="relative">
                                        <ShoppingBag :size="24" stroke-width="2.5" />
                                        <Plus :size="12" stroke-width="4" class="absolute -top-1 -right-1 bg-white text-primary rounded-full" />
                                    </div>
                                </template>
                            </button>
                        </div>
                    </div>

                    <div class="absolute bottom-0 left-0 h-1 bg-gradient-to-r from-primary to-accent w-0 group-hover:w-full transition-all duration-700"></div>
                </div>
            </div>

            <div class="flex-none w-12 h-1"></div>
        </div>
    </div>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

.group {
    will-change: transform;
    backface-visibility: hidden;
}
</style>