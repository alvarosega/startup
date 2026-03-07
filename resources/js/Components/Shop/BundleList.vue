<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Clock, AlertCircle } from 'lucide-vue-next';

const props = defineProps({
    bundles: {
        type: Array,
        required: true,
        default: () => []
    }
});

const emit = defineEmits(['select-bundle']);

const currentTime = ref(Date.now());
let intervalId = null;

// Toda la lógica de mutación de scroll ha sido purgada por eficiencia.

onMounted(() => {
    intervalId = setInterval(() => {
        currentTime.value = Date.now();
    }, 1000);
});

onUnmounted(() => {
    if (intervalId) clearInterval(intervalId);
});

const getTimerData = (endsAt) => {
    if (!endsAt) return { isExpired: false, h: '--', m: '--', s: '--' };
    const endTime = new Date(endsAt).getTime();
    const diff = endTime - currentTime.value;
    
    if (diff <= 0) return { isExpired: true, h: '00', m: '00', s: '00' };

    const h = Math.floor(diff / (1000 * 60 * 60)).toString().padStart(2, '0');
    const m = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60)).toString().padStart(2, '0');
    const s = Math.floor((diff % (1000 * 60)) / 1000).toString().padStart(2, '0');
    
    return { isExpired: false, h, m, s };
};
const getSkuImages = (bundle) => {
    if (bundle.items && bundle.items.length > 0) {
        // Eliminamos el .filter(Boolean) y forzamos un null si no hay imagen.
        // Esto garantiza que el array siempre tenga la misma longitud que la cantidad de SKUs.
        return bundle.items.map(sku => sku.image_path || sku.image_url || null);
    }
    return [];
};

// Nueva regla estricta: si no hay imagen, inyecta la ruta local del placeholder
const getImageUrl = (path) => {
    if (!path) return '/assets/img/placeholder.png';
    if (path.startsWith('http')) return path;
    return `/storage/${path.replace(/^\/+/, '')}`;
};

const handleBundleClick = (bundle, isExpired) => {
    if (isExpired) return;
    emit('select-bundle', bundle.slug);
};
</script>

<template>
    <div v-if="bundles && bundles.length > 0" 
         class="w-full sticky top-[80px] z-[45] cyber-glass border-b border-tech pt-2 pb-2">
        <div class="flex overflow-x-auto snap-x snap-mandatory no-scrollbar px-4 gap-3 pb-1">
            
            <div v-for="bundle in bundles" :key="bundle.id" 
                 class="w-[90vw] md:w-[320px] h-20 shrink-0 snap-start relative transition-all duration-300 cyber-border-card group rounded-[14px]"
                 :class="getTimerData(bundle.ends_at).isExpired ? 'is-offline opacity-60 cursor-not-allowed' : 'cursor-pointer'"
                 @click="handleBundleClick(bundle, getTimerData(bundle.ends_at).isExpired)">
                 <div class="cyber-border-content">

                    <div v-if="getTimerData(bundle.ends_at).isExpired" class="absolute inset-0 z-30 cyber-glass flex items-center justify-center">
                        <div class="bg-foreground text-background font-mono font-black uppercase px-4 py-1 flex items-center gap-2 text-[10px] tracking-widest shadow-lg rounded-md">
                            <AlertCircle :size="14" strokeWidth="3" /> Offline
                        </div>
                    </div>

                    <div class="flex w-full h-full items-center justify-between px-4 relative">
                    
                    <div class="flex flex-col justify-center gap-1 w-[55%] z-10">
                        <h3 class="font-sans font-bold uppercase text-foreground text-xs line-clamp-2 tracking-wide pr-2">
                            {{ bundle.name }}
                        </h3>
                        
                        <div class="flex items-baseline gap-1.5 font-mono text-f1-red drop-shadow-[0_0_2px_rgba(225,6,0,0.8)]">
                            <span class="text-[13px] font-black">{{ getTimerData(bundle.ends_at).h }}<span class="text-[8px] text-muted ml-0.5">H</span></span>
                            <span class="text-[13px] font-black">{{ getTimerData(bundle.ends_at).m }}<span class="text-[8px] text-muted ml-0.5">M</span></span>
                            <span class="text-[13px] font-black">{{ getTimerData(bundle.ends_at).s }}<span class="text-[8px] text-muted ml-0.5">S</span></span>
                        </div>
                    </div>

                    <div class="flex items-center justify-end w-[45%] h-full py-2 z-10 overflow-hidden relative" @click.stop>
                        
                        <div class="absolute right-0 top-0 bottom-0 w-4 bg-gradient-to-l from-surface to-transparent z-20 pointer-events-none"></div>
                        
                        <div class="flex overflow-x-auto snap-x snap-mandatory no-scrollbar h-full items-center gap-2 w-full pr-4">
                            
                            <template v-if="getSkuImages(bundle).length === 0">
                                <div class="w-10 h-10 shrink-0 snap-start rounded border border-tech bg-background flex items-center justify-center overflow-hidden">
                                    <img :src="getImageUrl(null)" class="w-full h-full object-cover opacity-50">
                                </div>
                            </template>
                            
                            <template v-else>
                                <div v-for="(img, index) in getSkuImages(bundle)" :key="index"
                                     class="w-10 h-10 shrink-0 snap-start rounded border border-tech bg-background flex items-center justify-center overflow-hidden">
                                    <img :src="getImageUrl(img)" class="w-full h-full object-cover">
                                </div>
                            </template>

                        </div>
                    </div>
                    </div>
                </div>
            </div>
            
            <div class="w-[5vw] shrink-0"></div>
        </div>
    </div>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

/* FÍSICA DE BORDE ANIMADO CYBER-MINIMALIST */
.cyber-border-card {
    overflow: hidden;
    background: transparent;
    /* Eliminamos el border plano normal para usar la animación */
}

/* Motores de rotación de gradiente (Constante y Lento) */
.cyber-border-card::before,
.cyber-border-card::after {
    content: "";
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    z-index: 0;
    animation: rotation_border 5s infinite linear;
}

/* Haz 1 de Neón Rojo */
.cyber-border-card::before {
    background: linear-gradient(0deg, transparent 40%, hsl(var(--f1-red-base)) 50%, transparent 60%);
}

/* Haz 2 de Neón Verde Telemetría (Opuesto) */
.cyber-border-card::after {
    background: linear-gradient(0deg, transparent 40%, hsl(var(--telemetry-ok)) 50%, transparent 60%);
    animation-delay: -2.5s;
}

/* Si la oferta expira, el borde pierde energía y se detiene */
.cyber-border-card.is-offline::before,
.cyber-border-card.is-offline::after {
    background: linear-gradient(0deg, transparent 40%, hsl(var(--muted)) 50%, transparent 60%);
    animation: none;
}

/* Capa interior aislante que respeta el radio externo */
.cyber-border-content {
    position: absolute;
    inset: 1px; /* Grosor exacto del borde animado */
    background-color: hsl(var(--customer-surface));
    border-radius: 13px; /* 14px outer - 1px inset */
    z-index: 10;
    overflow: hidden;
}

@keyframes rotation_border {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>