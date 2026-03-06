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

// --- LÓGICA DEL MOTOR DE TIEMPO ---
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
    if (!endsAt) return { isExpired: false, text: '--:--:--' };
    
    // Convertimos la fecha ISO del backend a Timestamp
    const endTime = new Date(endsAt).getTime();
    const diff = endTime - currentTime.value;

    if (diff <= 0) {
        return { isExpired: true, text: '00:00:00' };
    }

    const hours = Math.floor(diff / (1000 * 60 * 60)).toString().padStart(2, '0');
    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60)).toString().padStart(2, '0');
    const seconds = Math.floor((diff % (1000 * 60)) / 1000).toString().padStart(2, '0');

    return { isExpired: false, text: `${hours}:${minutes}:${seconds}` };
};

const handleBundleClick = (bundle, isExpired) => {
    if (isExpired) return;
    emit('select-bundle', bundle.slug);
};

// Función para asegurar la URL correcta de la imagen
const getImageUrl = (path) => {
    if (!path) return '/images/placeholder.png';
    if (path.startsWith('http')) return path;
    return `/storage/${path.replace(/^\/+/, '')}`;
};
</script>

<template>
    <div v-if="bundles.length > 0" class="w-full pt-4 pb-2 relative">
        <div class="px-4 mb-3 flex items-center justify-between">
            <h2 class="text-xs font-mono font-black uppercase tracking-widest text-f1-red flex items-center gap-2">
                <Clock :size="14" /> Ofertas Activas
            </h2>
        </div>

        <div class="flex overflow-x-auto snap-x snap-mandatory no-scrollbar px-4 gap-4 pb-4">
            
            <div v-for="bundle in bundles" :key="bundle.id" 
                 class="w-[88%] shrink-0 snap-start flex flex-col relative transition-all duration-300 border border-tech clip-f1-br bg-surface group"
                 :class="getTimerData(bundle.ends_at).isExpired ? 'opacity-60 grayscale cursor-not-allowed' : 'cursor-pointer hover:border-f1-red'"
                 @click="handleBundleClick(bundle, getTimerData(bundle.ends_at).isExpired)">
                
                <div v-if="getTimerData(bundle.ends_at).isExpired" class="absolute inset-0 z-30 bg-background/80 backdrop-blur-[2px] flex items-center justify-center">
                    <div class="bg-primary text-background font-mono font-black uppercase px-4 py-2 clip-f1-br flex items-center gap-2 shadow-tech text-sm">
                        <AlertCircle :size="16" /> FINALIZADO
                    </div>
                </div>

                <div class="flex h-32 relative z-10">
                    <div class="flex-1 p-4 flex flex-col justify-between relative overflow-hidden bg-gradient-to-r from-transparent to-background/50">
                        <div class="relative z-10 w-[70%]">
                            <h3 class="text-sm font-sans font-black uppercase text-primary leading-tight line-clamp-2 mb-1">
                                {{ bundle.name }}
                            </h3>
                            <p v-if="bundle.fixed_price" class="text-lg font-mono font-bold text-telemetry-green">
                                Bs. {{ bundle.fixed_price }}
                            </p>
                        </div>
                        
                        <img :src="getImageUrl(bundle.image_path || bundle.image_url)" 
                             class="absolute right-0 top-1/2 -translate-y-1/2 w-28 h-28 object-contain tech-shadow opacity-90 group-hover:scale-110 transition-transform duration-300 pointer-events-none z-0">
                    </div>

                    <div class="w-[85px] bg-background border-l border-tech flex flex-col items-center justify-center p-2 z-20 shrink-0">
                        <span class="text-[8px] font-mono text-muted uppercase tracking-widest mb-1 text-center">Tiempo</span>
                        <div class="bg-surface border border-tech px-1.5 py-3 w-full flex justify-center shadow-inner">
                            <span class="text-[11px] font-mono font-black text-f1-red tracking-wider" style="writing-mode: vertical-rl; transform: rotate(180deg);">
                                {{ getTimerData(bundle.ends_at).text }}
                            </span>
                        </div>
                    </div>
                </div>

            </div>
            
            <div class="w-[10%] shrink-0"></div>
        </div>
    </div>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>