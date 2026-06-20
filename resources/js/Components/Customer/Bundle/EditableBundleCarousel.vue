<script setup>
import { ref, computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { Plus, ChevronLeft, ChevronRight, Loader2 } from 'lucide-vue-next';

const props = defineProps({
    bundles: { type: Array, default: () => [] },
    currentId: { type: [String, Number], default: null },
    loading: { type: Boolean, default: false }
});

const scrollContainer = ref(null);
const processingId = ref(null);
const page = usePage();

const scroll = (direction) => {
    if (!scrollContainer.value) return;
    const offset = direction === 'left' ? -400 : 400;
    scrollContainer.value.scrollBy({ left: offset, behavior: 'smooth' });
};

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

// MODIFICAR: Añadir 'only' para evitar parpadeo global
const handleQuickAdd = (bundleId) => {
    if (processingId.value) return;
    processingId.value = bundleId;
    router.post(route('customer.cart.add-template'), { bundle_id: bundleId }, {
        preserveScroll: true,
        preserveState: true,
        only: ['cart', 'flash'], // Solo actualizamos lo necesario
        onFinish: () => processingId.value = null
    });
};
</script>

<template>
    <div class="relative w-full group/carousel">
        <button @click="scroll('left')" 
                class="hidden lg:flex absolute left-4 top-1/2 -translate-y-1/2 z-40 w-12 h-12 items-center justify-center rounded-full bg-background/40 backdrop-blur-xl border border-white/10 text-white opacity-0 group-hover/carousel:opacity-100 transition-all hover:scale-110 active:scale-95 shadow-2xl">
            <ChevronLeft :size="24" stroke-width="3" />
        </button>
        <button @click="scroll('right')" 
                class="hidden lg:flex absolute right-4 top-1/2 -translate-y-1/2 z-40 w-12 h-12 items-center justify-center rounded-full bg-background/20 backdrop-blur-xl border border-white/10 text-white opacity-0 group-hover/carousel:opacity-100 transition-all hover:bg-background/40">
            <ChevronRight :size="24" stroke-width="3" />
        </button>

        <div ref="scrollContainer" 
             class="flex overflow-x-auto snap-x snap-mandatory gap-6 px-6 md:px-12 pb-8 no-scrollbar scroll-smooth">
            
             <template v-if="loading || bundles.length === 0">
                <div v-for="n in 3" :key="n" 
                    class="flex-none w-[85vw] md:w-[400px] h-[340px] rounded-3xl skeleton p-8 flex flex-col justify-end gap-4">
                    <div class="h-8 w-2/3 bg-white/20 rounded-lg"></div>
                    <div class="h-4 w-full bg-white/10 rounded-md"></div>
                </div>
            </template>

            <template v-else>
                <div v-for="bundle in bundles" :key="bundle.id" 
                     @click="handleNavigate(bundle.slug)"
                     class="flex-none w-[85vw] md:w-[400px] h-[340px] snap-start group relative rounded-[2.5rem] overflow-hidden glass-chassis border transition-all duration-500 cursor-pointer"
                     :class="bundle.id === currentId ? 'ring-2 ring-primary ring-offset-4 ring-offset-transparent' : 'hover:border-white/20'">
                    
                    <div class="absolute inset-0 z-0">
                        <img :src="bundle.image_url || getPlaceholder(bundle.is_editable)" 
                             class="w-full h-full object-cover transition-transform duration-[3s] group-hover:scale-105"
                             @error="(e) => e.target.src = getPlaceholder(bundle.is_editable)">
                    </div>

                    <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/20 to-transparent z-10 pointer-events-none"></div>
                    
                    <div class="absolute inset-0 p-8 flex flex-col justify-end z-20">
                        <h3 class="text-3xl font-black text-white uppercase italic leading-none mb-3 tracking-tighter drop-shadow-lg">
                            {{ bundle.name }}
                        </h3>
                        
                        <p class="text-white/80 text-xs font-bold line-clamp-2 mb-6 max-w-[80%] leading-relaxed">
                            {{ bundle.description }}
                        </p>
                        
                        <div class="flex items-center justify-between mt-auto pt-4 border-t border-white/10">
                            <span class="text-xs font-black text-white uppercase tracking-[0.2em]">Suministro Pack</span>
                            <button @click.stop="handleQuickAdd(bundle.id)" 
                                    :disabled="processingId === bundle.id"
                                    class="w-14 h-12 bg-white text-black rounded-2xl flex items-center justify-center hover:bg-neutral-200 transition-all active:scale-90 shadow-2xl disabled:opacity-50">
                                <Loader2 v-if="processingId === bundle.id" class="animate-spin" :size="20" />
                                <Plus v-else :size="24" stroke-width="4" />
                            </button>
                        </div>
                    </div>
                </div>
            </template>

            <div class="w-12 shrink-0 invisible"></div>
        </div>
    </div>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

/* CHASIS DE CRISTAL (Marco sutil sobre la imagen) */
.glass-chassis {
    background: rgba(255, 255, 255, 0.03);
    backdrop-filter: blur(2px); /* Blur mínimo para no afectar la imagen pero dar sensación de cristal */
    border: 1px solid rgba(255, 255, 255, 0.1);
    box-shadow: 0 20px 40px -15px rgba(0,0,0,0.5);
}

/* SKELETON ESTRUCTURAL */
.glass-chassis-skeleton {
    background: rgba(255, 255, 255, 0.05);
    backdrop-filter: blur(40px);
    border: 1px solid rgba(255, 255, 255, 0.1);
}

/* EFECTO HARDWARE PARA EL BOTÓN */
button {
    cursor: pointer;
}
</style>