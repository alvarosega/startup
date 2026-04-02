<script setup>
import { router, Link } from '@inertiajs/vue3';
import { Zap, ArrowRight, Sparkles } from 'lucide-vue-next';
import { onMounted } from 'vue';

const props = defineProps({
    bundles: { type: Array, default: () => [] }
});

const handleOpenBundle = (slug) => {
    router.visit(route('customer.bundle', { slug: slug }));
};

// HELPER: Garantiza que siempre retorne un string válido
const getPlaceholder = (isEditable) => {
    // Si isEditable es undefined (porque el backend no lo envió), asume no editable por seguridad
    return (isEditable === true || isEditable === 1) 
        ? '/assets/img/bundle_banner_editable.webp' 
        : '/assets/img/bundle_banner_noeditable.webp';
};

// DIAGNÓSTICO: Esto imprimirá en la consola de tu navegador los datos reales
onMounted(() => {
    console.log("Datos recibidos en el Carrusel:", props.bundles);
});
</script>

<template>
    <div class="flex overflow-x-auto snap-x snap-mandatory gap-6 px-6 md:px-12 pb-8 hide-scrollbar">
        <div v-for="bundle in bundles" :key="bundle.id" @click="handleOpenBundle(bundle.slug)"
             class="flex-none w-[85vw] md:w-[450px] h-[320px] snap-center group relative rounded-[2.5rem] overflow-hidden bg-muted border border-border/50 shadow-sm transition-all hover:shadow-2xl cursor-pointer">
            
            <img 
                :src="bundle.image_url || getPlaceholder(bundle.is_editable)" 
                :alt="bundle.name"
                class="absolute inset-0 w-full h-full object-cover transition-transform duration-1000 group-hover:scale-110 opacity-70 z-0"
                @error="(e) => e.target.src = getPlaceholder(bundle.is_editable)"
            />
            
            <div class="absolute inset-0 bg-gradient-to-t from-black via-black/40 to-transparent p-8 flex flex-col justify-end z-10 pointer-events-none">
                <div class="flex items-center gap-2 mb-3">
                    <span class="px-3 py-1 bg-white/10 backdrop-blur-md border border-white/20 rounded-full text-[9px] font-black text-white uppercase tracking-widest">
                        {{ bundle.is_editable ? 'Pack Editable' : 'Combo Fijo' }}
                    </span>
                    <div v-if="bundle.is_editable" class="text-accent animate-pulse">
                        <Sparkles :size="14" fill="currentColor" />
                    </div>
                </div>
                
                <h3 class="text-3xl font-black text-white uppercase italic leading-none mb-2 tracking-tighter">{{ bundle.name }}</h3>
                <p class="text-white/80 text-xs font-medium line-clamp-2 mb-6 max-w-[80%]">{{ bundle.description }}</p>
                
                <div class="flex items-center justify-between mt-auto">
                    <span v-if="bundle.fixed_price" class="text-2xl font-black text-white tracking-tighter italic drop-shadow-md">
                        Bs. {{ Number(bundle.fixed_price).toFixed(2) }}
                    </span>
                    <span v-else class="text-[10px] font-black text-white/60 uppercase tracking-widest">
                        Precio según selección
                    </span>
                    
                    <div class="w-12 h-12 bg-primary text-white rounded-2xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform pointer-events-auto">
                        <ArrowRight :size="20" stroke-width="3" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.hide-scrollbar::-webkit-scrollbar { display: none; }
.hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>