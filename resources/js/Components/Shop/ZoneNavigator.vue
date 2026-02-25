<script setup>
import { computed, ref, onMounted, onUnmounted, nextTick } from 'vue';

const props = defineProps({ zones: Array });
const emit = defineEmits(['select-item', 'select-zone']);

// --- CONFIGURACIÓN VISUAL DINÁMICA ---
const CARD_HEIGHT = window.innerHeight * 0.58; 
const PEEK_HEIGHT = window.innerHeight * 0.10; 
const VISIBLE_COUNT = 4;
const AUTO_SCROLL_SPEED = 0.6;
const ITEM_WIDTH = 150; 
const ITEM_GAP = 16;     
const TOTAL_ITEM_WIDTH = ITEM_WIDTH + ITEM_GAP;

// --- ESTADO ---
const virtualIndex = ref(1000);
const horizOffset = ref(0);
const baseHorizOffset = ref(0);
const isDragging = ref(false);
const gestureAxis = ref(null); // 'x' o 'y'
const startX = ref(0);
const startY = ref(0);
const currentDragY = ref(0);
const isClick = ref(true);

let animationFrameId = null;

const getZoneData = (vIndex) => {
    if (!props.zones.length) return null;
    const len = props.zones.length;
    const realIndex = ((vIndex % len) + len) % len;
    return props.zones[realIndex];
};

const renderedCards = computed(() => {
    const cards = [];
    for (let i = -1; i < VISIBLE_COUNT; i++) {
        cards.push({
            offset: i, 
            data: getZoneData(virtualIndex.value + i),
            key: virtualIndex.value + i
        });
    }
    return cards;
});

// MODIFICAR: Función startAutoScroll corregida
const startAutoScroll = () => {
    // 1. Limpieza preventiva
    cancelAnimationFrame(animationFrameId);
    
    const currentZone = getZoneData(virtualIndex.value);
    if (!currentZone?.aisles?.length) return;

    const singleSetWidth = currentZone.aisles.length * TOTAL_ITEM_WIDTH;
    
    // Inicialización segura del infinito
    if (horizOffset.value === 0) horizOffset.value = -singleSetWidth;

    const animate = () => {
        // INTERRUPTOR DE MUERTE: 
        // Si el usuario está tocando, DETENEMOS el bucle completamente.
        // No pedimos el siguiente frame. Esto asegura que el manual scroll tenga control total.
        if (isDragging.value) return; 

        horizOffset.value -= AUTO_SCROLL_SPEED;
        
        // Lógica infinito
        if (horizOffset.value <= -(singleSetWidth * 2)) horizOffset.value += singleSetWidth;
        if (horizOffset.value >= 0) horizOffset.value -= singleSetWidth;
        
        // Solicitamos el siguiente frame SOLO si no estamos arrastrando
        animationFrameId = requestAnimationFrame(animate);
    };
    
    // Arrancamos
    animationFrameId = requestAnimationFrame(animate);
};

// --- INTERACCIÓN TÁCTIL ---
const handleTouchStart = (e) => {
    isDragging.value = true;
    isClick.value = true;
    gestureAxis.value = null;
    const p = e.touches ? e.touches[0] : e;
    startX.value = p.clientX;
    startY.value = p.clientY;
    baseHorizOffset.value = horizOffset.value;
    cancelAnimationFrame(animationFrameId);
};

const handleTouchMove = (e) => {
    if (!isDragging.value) return;
    const p = e.touches ? e.touches[0] : e;
    const dx = p.clientX - startX.value;
    const dy = p.clientY - startY.value;

    if (!gestureAxis.value) {
        if (Math.abs(dx) > Math.abs(dy) && Math.abs(dx) > 8) gestureAxis.value = 'x';
        else if (Math.abs(dy) > Math.abs(dx) && Math.abs(dy) > 8) gestureAxis.value = 'y';
    }

    if (gestureAxis.value === 'x') {
        if (e.cancelable) e.preventDefault();
        horizOffset.value = baseHorizOffset.value + dx;
    } else if (gestureAxis.value === 'y') {
        currentDragY.value = dy;
    }

    if (Math.abs(dx) > 5 || Math.abs(dy) > 5) isClick.value = false;
};

const handleTouchEnd = () => {
    isDragging.value = false;
    if (!isClick.value) {
        if (gestureAxis.value === 'y') {
            if (currentDragY.value < -60) {
                virtualIndex.value++;
                horizOffset.value = 0;
            } else if (currentDragY.value > 60) {
                virtualIndex.value--;
                horizOffset.value = 0;
            }
        }
    }
    currentDragY.value = 0;
    gestureAxis.value = null;
    startAutoScroll();
};

const handleCardClick = (offset) => {
    if (isClick.value && offset !== 0) {
        virtualIndex.value += offset;
        horizOffset.value = 0;
    }
};

const getCardStyle = (offset) => {
    const dragY = isDragging.value && gestureAxis.value === 'y' ? currentDragY.value : 0;
    let yPos = offset === -1 ? -CARD_HEIGHT + dragY : offset === 0 ? dragY * 0.3 : (offset * PEEK_HEIGHT) + (dragY * 0.1);
    let scale = offset <= 0 ? 1 : 1 - (offset * 0.04);
    
    return {
        transform: `translate3d(0, ${yPos}px, ${-offset * 40}px) scale(${scale})`,
        opacity: offset === -1 ? (dragY > 0 ? dragY / 300 : 0) : 1,
        zIndex: 50 - offset,
        filter: offset > 0 ? `brightness(${1 - (offset * 0.15)})` : 'none',
        transition: isDragging.value ? 'none' : 'all 0.5s cubic-bezier(0.2, 0.9, 0.1, 1)'
    };
};

onMounted(startAutoScroll);
onUnmounted(() => cancelAnimationFrame(animationFrameId));

// Lógica de Matrix
const matrixChars = "アカサタナハマヤラワ".split("");
const matrixColumns = Array.from({ length: 35 }, () => ({
    chars: Array.from({ length: 20 }, () => matrixChars[Math.floor(Math.random() * matrixChars.length)]).join('\n'),
    left: Math.random() * 100,
    delay: Math.random() * -15,
    duration: 6 + Math.random() * 10
}));
</script>

<template>
    <div class="w-full h-full bg-background overflow-hidden relative flex flex-col items-center pt-8 touch-none select-none"
         @mousedown="handleTouchStart" @mousemove="handleTouchMove" @mouseup="handleTouchEnd" @mouseleave="handleTouchEnd"
         @touchstart="handleTouchStart" @touchmove="handleTouchMove" @touchend="handleTouchEnd">
        
        <div class="absolute inset-0 pointer-events-none opacity-[0.2] dark:opacity-[0.4] overflow-hidden jp-matrix-rain"
             :style="{ '--matrix-color': getZoneData(virtualIndex)?.color || '#3b82f6' }">
            <div v-for="(col, i) in matrixColumns" :key="i" class="matrix-col"
                 :style="{ left: col.left + '%', animationDelay: col.delay + 's', animationDuration: col.duration + 's' }">
                {{ col.chars }}
            </div>
        </div>

        <div class="z-50 mb-4 flex flex-col items-center pointer-events-none">
            <h2 class="text-[9px] font-black tracking-[0.3em] text-foreground/40 uppercase mb-2">Explorar Zonas</h2>
            <div class="flex gap-1.5">
                <div v-for="(_, idx) in props.zones" :key="idx"
                     class="h-1 rounded-full transition-all duration-300"
                     :class="(virtualIndex % props.zones.length) === idx ? 'w-6 bg-primary' : 'w-1.5 bg-foreground/10'">
                </div>
            </div>
        </div>

        <div class="relative w-full max-w-[360px] h-full perspective-container">
            <div v-for="card in renderedCards" :key="card.key"
                 class="absolute left-0 right-0 w-full rounded-[32px] shadow-2xl will-change-transform origin-top overflow-hidden"
                 :style="{ height: `${CARD_HEIGHT}px`, ...getCardStyle(card.offset) }">
                
                <div class="relative w-full h-full bg-slate-800/90 backdrop-blur-sm border border-white/10 flex flex-col"
                     @click="handleCardClick(card.offset)">
                    
                    <div class="flex-1 relative overflow-hidden">
                        <div v-if="card.offset !== 0" class="absolute inset-0 z-20 bg-black/60 flex items-center justify-center p-8">
                             <img v-if="card.data.aisles?.[0]?.image_url" :src="card.data.aisles[0].image_url" class="w-32 h-32 object-contain opacity-40 grayscale">
                        </div>

                        <div v-else class="h-full flex flex-col">
                            <div class="px-6 pt-6 pb-2"><span class="text-[10px] text-primary font-black uppercase">{{ card.data.aisles?.length }} Pasillos</span></div>
                            <div class="relative flex-1 overflow-hidden">
                                <div class="absolute inset-0 flex items-center px-4 will-change-transform"
                                     :style="{ width: 'max-content', transform: `translate3d(${horizOffset}px, 0, 0)` }"
                                     @mousedown.stop @touchstart.stop>
                                    <div v-for="(aisle, idx) in [...card.data.aisles, ...card.data.aisles, ...card.data.aisles]" 
                                         :key="idx" @click.stop="emit('select-item', { item: aisle, zone: card.data })"
                                         class="shrink-0 px-2" :style="{ width: `${TOTAL_ITEM_WIDTH}px` }">
                                        <div class="w-full bg-slate-900/50 rounded-2xl overflow-hidden border border-white/5 shadow-xl active:scale-95 transition-transform h-[240px] flex flex-col">
                                            <img :src="aisle.image_url" class="w-full h-36 object-contain bg-white/10 p-4 pointer-events-none">
                                            <div class="p-3 flex-1 flex items-center justify-center text-center">
                                                <span class="text-[11px] font-bold text-white leading-tight line-clamp-2 uppercase">{{ aisle.name }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="h-[85px] shrink-0 flex items-center px-6 border-t border-white/5"
                         :style="{ backgroundColor: card.offset === 0 ? (card.data.color || '#334155') : '#1e293b' }">
                        <div class="flex-1 min-w-0">
                            <p class="text-[8px] font-black text-white/40 uppercase mb-1">0{{ (card.key % props.zones.length) + 1 }}</p>
                            <h3 class="text-xl font-black text-white uppercase italic truncate">{{ card.data.name }}</h3>
                        </div>
                        <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center shadow-lg transition-transform"
                             :class="card.offset === 0 ? 'scale-100' : 'scale-75 opacity-20'">
                            <span class="text-black font-black text-lg">→</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.perspective-container { perspective: 1200px; transform-style: preserve-3d; }
.jp-matrix-rain { font-family: monospace; font-size: 18px; line-height: 1; white-space: pre; color: var(--matrix-color); }
.matrix-col { position: absolute; top: -100%; text-shadow: 0 0 8px var(--matrix-color); animation: matrix-fall linear infinite; mask-image: linear-gradient(to bottom, transparent, black 20%, black 80%, transparent); }
.matrix-col::first-letter { color: #fff; text-shadow: 0 0 10px #fff, 0 0 20px var(--matrix-color); font-weight: bold; }
@keyframes matrix-fall { 0% { transform: translateY(0); } 100% { transform: translateY(2500px); } }
</style>