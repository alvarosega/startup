<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { 
    ShoppingBag, 
    Loader2, 
    Plus, 
    MessageSquareQuote, 
    ArrowRight 
} from 'lucide-vue-next';

const props = defineProps({
    bundles: { type: Array, default: () => [] }
});

const processingId = ref(null);

const navigateToBundle = (slug) => {
    router.visit(route('customer.bundle', { slug }));
};

const quickAddBundle = (bundleId) => {
    if (processingId.value) return;
    processingId.value = bundleId;
    router.post(route('customer.cart.add-template'), { bundle_id: bundleId }, {
        preserveScroll: true,
        onFinish: () => processingId.value = null
    });
};
</script>

<template>
    <div class="tray-container relative w-full overflow-hidden bg-neutral-200/30 dark:bg-black/20 shadow-inner-industrial py-4 md:py-6">
        
        <div class="flex overflow-x-auto snap-x snap-mandatory gap-4 md:gap-6 px-4 md:px-8 pb-4 no-scrollbar scroll-smooth">
            
            <div v-for="bundle in bundles" :key="bundle.id"
                 class="flex-none w-[85vw] md:w-[45vw] lg:w-[32vw] max-w-[580px] snap-start group relative">
                
                 <div class="relative h-full chassis-neumorphic-primary rounded-[2.5rem] md:rounded-[3rem] overflow-hidden flex flex-col transition-all duration-500 ease-ios group-hover:-translate-y-2 group-active:scale-[0.97]">
    
                    <div class="absolute inset-0 bg-gradient-to-br from-primary/10 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-1000 pointer-events-none z-0"></div>

                    <div class="relative w-full aspect-[2/1] overflow-hidden cursor-pointer z-10 p-3 md:p-4" @click="navigateToBundle(bundle.slug)">
                        <img :src="bundle.image_url" :alt="bundle.name"
                            class="w-full h-full object-cover transition-transform duration-[1.5s] ease-ios group-hover:scale-105 rounded-[1.8rem] md:rounded-[2.2rem] shadow-inner-industrial">
                        
                        <div class="absolute inset-3 md:inset-4 rounded-[1.8rem] md:rounded-[2.2rem] bg-gradient-to-t from-black/80 via-black/20 to-transparent opacity-90"></div>

                        <div class="absolute bottom-6 md:bottom-8 left-8 md:left-10 right-8 md:right-10 z-30">
                            <h3 class="text-2xl md:text-3xl lg:text-4xl font-black text-white uppercase italic tracking-tighter leading-[0.85] drop-shadow-lg">
                                {{ bundle.name }}
                            </h3>
                        </div>
                    </div>

                    <div class="p-5 md:p-8 relative z-20 flex items-center justify-between gap-4 md:gap-6 bg-transparent flex-1">
                        <div class="flex-1 flex items-start gap-3">
                            <MessageSquareQuote :size="18" class="text-primary shrink-0 mt-0.5" />
                            <p class="text-[9px] md:text-[10px] font-bold text-neutral-800 dark:text-neutral-300 italic line-clamp-2 leading-tight">
                                "{{ bundle.description || 'Configuración optimizada para abastecimiento de alta densidad.' }}"
                            </p>
                        </div>

                        <button @click.stop="quickAddBundle(bundle.id)" 
                                :disabled="processingId === bundle.id"
                                class="shrink-0 w-12 h-12 md:w-16 md:h-16 bg-primary text-primary-foreground rounded-2xl md:rounded-[1.8rem] flex items-center justify-center shadow-f1-glow hover:scale-105 active:scale-90 transition-all duration-300 disabled:opacity-50">
                            
                            <Loader2 v-if="processingId === bundle.id" class="animate-spin" :size="20" />
                            <div v-else class="relative">
                                <ShoppingBag :size="22" stroke-width="2.5" class="md:hidden" />
                                <ShoppingBag :size="26" stroke-width="2.5" class="hidden md:block" />
                                <div class="absolute -top-1 -right-1 bg-white text-primary rounded-md p-0.5 shadow-sm">
                                    <Plus :size="10" stroke-width="4" />
                                </div>
                            </div>
                        </button>
                    </div>
                </div>
            </div>

            <div class="flex-none w-[10vw] h-1"></div>
        </div>
    </div>
</template>

<style scoped>
/* ==========================================================
   INGENIERÍA NEUMÓRFICA AFILADA (Variants of Primary)
   ========================================================== */

/* 1. EL CHASIS: Base en Primario con Relieves Técnicos */
.chassis-neumorphic-primary {
    /* Base Color: Variante sutilmente más clara que el Primario puro (hsl 24 95% 55%) */
    background-color: hsla(24, 95%, 60%, 1);
    
    /* Sombras Neumórficas Afiladas (4px/8px en lugar de 15px/30px) */
    box-shadow: 
        /* Relieve Inferior (Sombrado denso del Primario) */
        6px 6px 12px hsla(24, 95%, 40%, 0.4),
        /* Relieve Superior (Luz casi blanca para Modo Claro) */
        -6px -6px 12px hsla(24, 95%, 90%, 0.6);
}

/* Modo Oscuro: Sincronización cromática con Secundario (Cian) */
.dark .chassis-neumorphic-primary {
    /* Base Color en Modo Oscuro (Neutro casi negro para que descanse) */
    background-color: hsla(24, 0%, 5%, 1);
    
    /* Sombras Neumórficas Técnicas para Modo Oscuro */
    box-shadow: 
        /* Relieve Inferior (Negro puro para profundidad) */
        6px 6px 12px hsla(0, 0%, 0%, 0.8),
        /* Relieve Superior (Resplandor sutil del Secundario/Cian para look hardware) */
        -6px -6px 12px hsla(180, 95%, 40%, 0.15); /* Variante del color secundario */
}

/* 2. LA IMAGEN ENCASTRADA (Efecto Tray-Integrated) */
.aspect-\[2\/1\] img.shadow-inner-industrial {
    /* Efecto de hundimiento dentro del chasis */
    box-shadow: 
        inset 6px 6px 10px hsla(24, 95%, 35%, 0.5),
        inset -6px -6px 10px hsla(24, 95%, 70%, 0.2);
}

.dark .aspect-\[2\/1\] img.shadow-inner-industrial {
    /* Efecto de hundimiento para Modo Oscuro */
    box-shadow: 
        inset 6px 6px 10px hsla(0, 0%, 0%, 0.9),
        inset -1px -1px 0px hsla(180, 95%, 40%, 0.1);
}

/* ==========================================================
   UTILIDADES COMPLEMENTARIAS
   ========================================================== */

/* Efecto de Bandeja (Inset Depth) */
.tray-container.shadow-inner-industrial {
    box-shadow: inset 0 10px 30px -10px rgba(0, 0, 0, 0.15),
                inset 0 -10px 30px -10px rgba(255, 255, 255, 0.05);
}

.dark .tray-container.shadow-inner-industrial {
    box-shadow: inset 0 15px 40px -10px rgba(0, 0, 0, 0.6);
}

.ease-ios { transition-timing-function: cubic-bezier(0.32, 0.72, 0, 1); }
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

.shadow-f1-glow {
    box-shadow: 0 0 15px -3px hsl(var(--primary) / 0.5);
}
</style>