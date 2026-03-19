<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { Clock, AlertCircle, Zap, ChevronRight } from 'lucide-vue-next';

const props = defineProps({
    bundles: { type: Array, required: true, default: () => [] }
});

const emit = defineEmits(['select-bundle']);
const currentTime = ref(Date.now());
let intervalId = null;

onMounted(() => {
    intervalId = setInterval(() => currentTime.value = Date.now(), 1000);
});

onUnmounted(() => { if (intervalId) clearInterval(intervalId); });

const getTimerData = (endsAt) => {
    if (!endsAt) return { isExpired: false, isExpiringSoon: false, h: '00', m: '00', s: '00' };
    const diff = new Date(endsAt).getTime() - currentTime.value;
    if (diff <= 0) return { isExpired: true, isExpiringSoon: false, h: '00', m: '00', s: '00' };

    const h = Math.floor(diff / 3600000).toString().padStart(2, '0');
    const m = Math.floor((diff % 3600000) / 60000).toString().padStart(2, '0');
    const s = Math.floor((diff % 60000) / 1000).toString().padStart(2, '0');
    return { isExpired: false, isExpiringSoon: diff < 3600000, h, m, s };
};

const getImageUrl = (path) => {
    if (!path) return '/assets/img/placeholder.png';
    return path.startsWith('http') ? path : `/storage/${path.replace(/^\/+/, '')}`;
};
</script>

<template>
    <section v-if="bundles.length > 0" class="w-full sticky top-[64px] z-[45] bg-background/80 backdrop-blur-xl border-b border-border pt-3 pb-6">
        
        <div class="flex overflow-x-auto snap-x snap-mandatory no-scrollbar px-6 gap-6">
            
            <div v-for="bundle in bundles" :key="bundle.id" 
                 @click="emit('select-bundle', bundle.slug)"
                 class="relative w-[88vw] md:w-[400px] h-44 shrink-0 snap-start group cursor-pointer">
                
                <div class="absolute inset-0 bg-card border border-border rounded-xl shadow-lg overflow-hidden flex transition-all duration-300 group-hover:border-primary/50 group-active:scale-[0.98]">
                    
                    <div v-if="getTimerData(bundle.ends_at).isExpired" class="absolute inset-0 z-50 bg-background/90 backdrop-blur-sm flex items-center justify-center">
                        <span class="bg-foreground text-background text-[10px] font-bold px-4 py-1 rounded-full tracking-[0.2em]">OFERTA FINALIZADA</span>
                    </div>

                    <div class="w-[65%] p-4 flex flex-col justify-between relative z-10">
                        
                        <div class="flex flex-col gap-1">
                            <div class="flex items-center gap-1.5 mb-1">
                                <div class="w-1.5 h-1.5 rounded-full bg-primary animate-ping"></div>
                                <span class="text-[9px] font-bold uppercase tracking-widest text-primary">Termina en:</span>
                            </div>
                            
                            <div class="flex items-baseline font-mono text-3xl font-bold tracking-tighter text-foreground"
                                 :class="{'animate-pulse-urgency text-primary': getTimerData(bundle.ends_at).isExpiringSoon}">
                                <span>{{ getTimerData(bundle.ends_at).h }}</span>
                                <span class="text-lg opacity-30 mx-0.5">:</span>
                                <span>{{ getTimerData(bundle.ends_at).m }}</span>
                                <span class="text-lg opacity-30 mx-0.5">:</span>
                                <span class="text-primary">{{ getTimerData(bundle.ends_at).s }}</span>
                            </div>
                        </div>

                        <div class="mt-4">
                            <h3 class="text-base font-bold leading-tight tracking-tight text-foreground line-clamp-2 mb-2">
                                {{ bundle.name }}
                            </h3>
                            
                            <div v-if="bundle.is_editable" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-primary/10 text-primary border border-primary/20">
                                <Zap :size="10" class="fill-current" />
                                <span class="text-[9px] font-bold uppercase tracking-wider">A tu gusto</span>
                            </div>
                            <div v-else class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-gray-100 dark:bg-white/5 text-muted-foreground border border-border">
                                <span class="text-[9px] font-bold uppercase tracking-wider">Combo Listo</span>
                            </div>
                        </div>
                    </div>

                    <div class="w-[35%] relative bg-gradient-to-l from-primary/5 to-transparent flex items-center justify-center overflow-hidden">
                        <div class="absolute inset-0 blur-[30px] opacity-20 bg-primary scale-150 transition-transform group-hover:scale-110 duration-700"></div>
                        
                        <div class="relative w-20 h-20">
                            <template v-for="(item, idx) in bundle.items?.slice(0, 3)" :key="idx">
                                <img :src="getImageUrl(item.image_path || item.image_url)" 
                                     class="absolute w-16 h-16 object-contain drop-shadow-xl transition-all duration-500 group-hover:scale-110"
                                     :style="{
                                         right: `${idx * 12}px`,
                                         top: `${idx * 4}px`,
                                         zIndex: 10 - idx,
                                         transform: `rotate(${(idx * 15) - 10}deg)`
                                     }">
                            </template>
                        </div>

                        <div class="absolute bottom-3 right-3 w-7 h-7 bg-primary text-white rounded-lg flex items-center justify-center shadow-lg transform translate-y-12 group-hover:translate-y-0 transition-transform duration-300">
                            <ChevronRight :size="16" stroke-width="3" />
                        </div>
                    </div>
                </div>

                <div class="absolute -bottom-2 inset-x-4 h-4 bg-foreground/5 dark:bg-white/5 rounded-xl -z-10 blur-[2px]"></div>
            </div>
            
            <div class="w-10 shrink-0"></div>
        </div>
    </section>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

/* Animación de Palpito de Urgencia (Primary Color) */
@keyframes pulse-urgency {
    0%, 100% { transform: scale(1); filter: brightness(1); drop-shadow: 0 0 0px var(--primary); }
    50% { transform: scale(1.02); filter: brightness(1.2); drop-shadow: 0 0 15px var(--primary); }
}

.animate-pulse-urgency {
    animation: pulse-urgency 0.8s ease-in-out infinite;
}

/* Transición de colores suave */
.transition-colors {
    transition-property: background-color, border-color, color, fill, stroke;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 500ms;
}
</style>