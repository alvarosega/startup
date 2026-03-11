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
        return bundle.items.map(sku => sku.image_path || sku.image_url || null);
    }
    return [];
};

const getImageUrl = (path) => {
    if (!path) return '/assets/img/placeholder.png'; // Asegura PNG transparente de placeholder
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
         class="w-full sticky top-[64px] z-[45] bg-surface/30 backdrop-blur-xl border-b border-tech pt-3 pb-2 transition-colors duration-500">
        
        <div class="flex overflow-x-auto snap-x snap-mandatory no-scrollbar px-4 gap-4 pb-2">
            
            <div v-for="bundle in bundles" :key="bundle.id" 
                 class="w-[85vw] md:w-[340px] h-20 shrink-0 snap-start relative transition-all duration-300 cyber-border-card rounded-[16px]"
                 :class="getTimerData(bundle.ends_at).isExpired ? 'is-offline opacity-50 cursor-not-allowed' : 'cursor-pointer active:scale-95'"
                 @click="handleBundleClick(bundle, getTimerData(bundle.ends_at).isExpired)">
                 
                <div class="cyber-border-content bg-surface/60 backdrop-blur-xl">
                    
                    <div v-if="getTimerData(bundle.ends_at).isExpired" 
                         class="absolute inset-0 z-30 bg-black/80 backdrop-blur-sm flex items-center justify-center">
                        <div class="bg-foreground text-background font-mono font-black uppercase px-4 py-1 flex items-center gap-2 text-[10px] tracking-widest shadow-lg rounded-full">
                            <AlertCircle :size="14" strokeWidth="3" /> OFFLINE
                        </div>
                    </div>

                    <div class="flex w-full h-full items-center justify-between px-5 relative z-10">
                    
                        <div class="flex flex-col justify-center gap-1 w-[60%]">
                            <h3 class="font-sans font-black uppercase text-foreground text-xs line-clamp-1 tracking-tight pr-2">
                                {{ bundle.name }}
                            </h3>
                            
                            <div class="flex items-baseline gap-1.5 font-mono text-f1-red group-hover:drop-shadow-[0_0_8px_rgba(225,6,0,0.6)]">
                                <span class="text-[14px] font-black tracking-tight">{{ getTimerData(bundle.ends_at).h }}<span class="text-[9px] text-muted-foreground ml-0.5">H</span></span>
                                <span class="text-[14px] font-black tracking-tight">{{ getTimerData(bundle.ends_at).m }}<span class="text-[9px] text-muted-foreground ml-0.5">M</span></span>
                                <span class="text-[14px] font-black tracking-tight">{{ getTimerData(bundle.ends_at).s }}<span class="text-[9px] text-muted-foreground ml-0.5">S</span></span>
                            </div>
                        </div>

                        <div class="flex items-center justify-end w-[40%] h-full py-3 overflow-hidden relative" @click.stop>
                            <div class="absolute right-0 top-0 bottom-0 w-6 bg-gradient-to-l from-surface/80 to-transparent z-20 pointer-events-none"></div>
                            
                            <div class="flex overflow-x-auto snap-x snap-mandatory no-scrollbar h-full items-center gap-2 w-full pr-4">
                                
                                <template v-if="getSkuImages(bundle).length === 0">
                                    <div class="w-11 h-11 shrink-0 snap-start bg-transparent flex items-center justify-center overflow-hidden">
                                        <img :src="getImageUrl(null)" class="w-full h-full object-contain opacity-40">
                                    </div>
                                </template>
                                
                                <template v-else>
                                    <div v-for="(img, index) in getSkuImages(bundle)" :key="index"
                                         class="w-11 h-11 shrink-0 snap-start bg-transparent flex items-center justify-center overflow-hidden">
                                        <img :src="getImageUrl(img)" class="w-full h-full object-contain filter drop-shadow-[0_4px_6px_rgba(0,0,0,0.3)] group-hover:scale-110 transition-transform duration-300">
                                    </div>
                                </template>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="w-[10vw] md:w-0 shrink-0"></div>
        </div>
    </div>
</template>
<style scoped>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

/* FÍSICA DE BORDE NEÓN SUTIL (PULSO) v3.1 */
.cyber-border-card {
    background: transparent;
    border: 1.5px solid hsl(var(--primary) / 0.5);
    animation: neon_pulse 2.5s infinite alternate ease-in-out;
}

/* Capa interior aislante (Se ajusta al nuevo borde) */
.cyber-border-content {
    position: absolute;
    inset: 0px; /* Ocupa todo el espacio interno sin dejar huecos */
    border-radius: 15px; /* Respeta el borde exterior redondeado */
    z-index: 10;
    overflow: hidden;
}

/* Si la oferta expira, el borde pierde energía (se apaga) */
.cyber-border-card.is-offline {
    border-color: rgba(255, 255, 255, 0.1) !important;
    animation: none;
    box-shadow: none !important;
}

/* Animación matemática del parpadeo (Glow) */
@keyframes neon_pulse {
    0% {
        box-shadow: 0 0 2px hsl(var(--primary) / 0.1), inset 0 0 2px hsl(var(--primary) / 0.1);
        border-color: hsl(var(--primary) / 0.3);
    }
    100% {
        box-shadow: 0 0 10px hsl(var(--primary) / 0.5), inset 0 0 5px hsl(var(--primary) / 0.2);
        border-color: hsl(var(--primary) / 0.9);
    }
}
</style>