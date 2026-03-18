<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Clock, AlertCircle, Zap } from 'lucide-vue-next';

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
    if (!endsAt) return { isExpired: false, isExpiringSoon: false, h: '--', m: '--', s: '--' };
    const endTime = new Date(endsAt).getTime();
    const diff = endTime - currentTime.value;
    
    if (diff <= 0) return { isExpired: true, isExpiringSoon: false, h: '00', m: '00', s: '00' };

    const h = Math.floor(diff / (1000 * 60 * 60)).toString().padStart(2, '0');
    const m = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60)).toString().padStart(2, '0');
    const s = Math.floor((diff % (1000 * 60)) / 1000).toString().padStart(2, '0');
    
    // Si queda menos de 1 hora, se considera que expira pronto (para el modo oscuro)
    const isExpiringSoon = diff < (1000 * 60 * 60);
    
    return { isExpired: false, isExpiringSoon, h, m, s };
};

// Limitamos a 4 imágenes exactas para el efecto de abanico
const getSkuImages = (bundle) => {
    if (bundle.items && bundle.items.length > 0) {
        return bundle.items.map(sku => sku.image_path || sku.image_url || null).slice(0, 4);
    }
    return [];
};

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
        class="w-full sticky top-[64px] z-[45] bg-background/80 backdrop-blur-xl border-b border-border/50 dark:border-card-border pt-2 pb-4 transition-colors duration-500">
        
        <div class="flex overflow-x-auto snap-x snap-mandatory no-scrollbar px-4 gap-6 pb-2">
            
            <div v-for="bundle in bundles" :key="bundle.id" 
                 class="relative w-[85vw] md:w-[360px] h-36 shrink-0 snap-start group"
                 :class="getTimerData(bundle.ends_at).isExpired ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'"
                 @click="handleBundleClick(bundle, getTimerData(bundle.ends_at).isExpired)">
                 
                <div class="absolute inset-0 bg-card rounded-2xl transform translate-y-3 scale-[0.90] opacity-40 shadow-[0_10px_30px_rgba(0,0,0,0.1)] dark:shadow-none dark:border dark:border-card-border transition-transform group-hover:translate-y-4"></div>
                <div class="absolute inset-0 bg-card rounded-2xl transform translate-y-1.5 scale-[0.95] opacity-70 shadow-[0_15px_40px_rgba(0,0,0,0.12)] dark:shadow-none dark:border dark:border-card-border transition-transform group-hover:translate-y-2"></div>

                <div class="absolute inset-0 bg-card rounded-2xl border border-border/40 dark:border-card-border shadow-[0_20px_60px_-15px_rgba(0,0,0,0.18)] dark:shadow-none flex overflow-hidden transition-transform duration-300 group-hover:-translate-y-1 group-active:translate-y-0">
                    
                    <div v-if="getTimerData(bundle.ends_at).isExpired" 
                         class="absolute inset-0 z-40 bg-background/80 backdrop-blur-sm flex items-center justify-center">
                        <div class="bg-foreground text-background font-black uppercase px-4 py-1 flex items-center gap-2 text-[10px] tracking-widest rounded-md">
                            <AlertCircle :size="14" strokeWidth="3" /> OFFLINE
                        </div>
                    </div>

                    <div class="flex flex-col justify-between p-4 w-[60%] z-20">
                        <div>
                            <div v-if="bundle.is_editable" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-sm bg-primary/10 text-primary border border-primary/20 mb-2">
                                <Zap :size="10" class="fill-current" />
                                <span class="text-[9px] font-black tracking-widest uppercase">MODULAR</span>
                            </div>
                            <div v-else class="inline-flex items-center gap-1 px-2 py-0.5 rounded-sm bg-white/5 text-muted-foreground border border-white/10 mb-2">
                                <AlertCircle :size="10" />
                                <span class="text-[9px] font-black tracking-widest uppercase">PRESET</span>
                            </div>
                            <h3 class="font-extrabold text-foreground text-sm line-clamp-2 tracking-tight pr-2 leading-tight">
                                {{ bundle.name }}
                            </h3>
                        </div>
                        
                        <div class="flex items-center gap-1.5 font-mono bg-muted dark:bg-transparent px-2 py-1 rounded-md w-fit"
                             :class="{
                                 'dark:text-primary dark:drop-shadow-[0_0_8px_rgba(225,6,0,0.6)]': getTimerData(bundle.ends_at).isExpiringSoon,
                                 'dark:text-accent dark:drop-shadow-[0_0_8px_rgba(0,255,0,0.4)]': !getTimerData(bundle.ends_at).isExpiringSoon
                             }">
                            <Clock :size="12" class="text-muted-foreground dark:text-inherit mb-0.5" />
                            <span class="text-xs font-black tracking-tight">{{ getTimerData(bundle.ends_at).h }}<span class="text-[8px] text-muted-foreground ml-[1px]">H</span></span>
                            <span class="text-xs font-black tracking-tight text-muted-foreground/30 dark:text-inherit">:</span>
                            <span class="text-xs font-black tracking-tight">{{ getTimerData(bundle.ends_at).m }}<span class="text-[8px] text-muted-foreground ml-[1px]">M</span></span>
                            <span class="text-xs font-black tracking-tight text-muted-foreground/30 dark:text-inherit">:</span>
                            <span class="text-xs font-black tracking-tight">{{ getTimerData(bundle.ends_at).s }}<span class="text-[8px] text-muted-foreground ml-[1px]">S</span></span>
                        </div>
                    </div>

                    <div class="relative w-[40%] h-full flex items-center justify-center pr-4 overflow-hidden bg-muted/30 dark:bg-transparent z-10">
                        <div class="relative w-24 h-24">
                            <template v-for="(img, idx) in getSkuImages(bundle)" :key="idx">
                                <img :src="getImageUrl(img)" 
                                     class="absolute w-14 h-14 object-contain filter drop-shadow-md transition-all duration-300 group-hover:scale-110"
                                     :style="{
                                         right: `${idx * 14}px`,
                                         top: `${16 + (idx % 2 === 0 ? 4 : -4)}px`,
                                         zIndex: 10 - idx,
                                         transform: `rotate(${(idx * 10) - 15}deg)`
                                     }">
                            </template>
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
</style>