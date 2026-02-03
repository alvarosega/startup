<script setup>
import { computed, ref } from 'vue';

const props = defineProps({ zones: Object });
const emit = defineEmits(['select-zone']);

// Convertimos a array
const sourceZones = computed(() => props.zones ? Object.values(props.zones) : []);

// --- ESTADO ---
const virtualIndex = ref(1000); // Índice alto para permitir loop infinito atrás
const isDragging = ref(false);
const startY = ref(0);
const currentDragY = ref(0);

// CONFIGURACIÓN VISUAL
const CARD_HEIGHT = 450; 
const PEEK_HEIGHT = 85; // Altura del footer que asoma (Zona cliqueable)

// --- LÓGICA LOOP INFINITO ---
const getZoneData = (vIndex) => {
    if (sourceZones.value.length === 0) return null;
    const realIndex = vIndex % sourceZones.value.length;
    return sourceZones.value[realIndex < 0 ? realIndex + sourceZones.value.length : realIndex];
};

// Renderizamos la actual + 3 siguientes
const renderedCards = computed(() => {
    const cards = [];
    for (let i = -1; i < 3; i++) {
        cards.push({
            offset: i, 
            data: getZoneData(virtualIndex.value + i),
            key: virtualIndex.value + i
        });
    }
    return cards;
});

// --- INTERACCIÓN TÁCTIL ---
const handleTouchStart = (e) => {
    isDragging.value = true;
    startY.value = e.touches[0].clientY;
    currentDragY.value = 0;
};

const handleTouchMove = (e) => {
    if (!isDragging.value) return;
    const deltaY = e.touches[0].clientY - startY.value;
    // Limitamos el arrastre visual para que no se rompa la ilusión
    currentDragY.value = deltaY; 
};

const handleTouchEnd = () => {
    isDragging.value = false;
    // Umbral de 60px para cambiar
    if (currentDragY.value < -60) virtualIndex.value++; // Swipe Arriba -> Siguiente
    else if (currentDragY.value > 60) virtualIndex.value--; // Swipe Abajo -> Anterior
    currentDragY.value = 0;
};

// --- SALTO DIRECTO ---
const jumpToCard = (offset) => {
    if (offset !== 0) {
        virtualIndex.value += offset;
    }
};

// --- ESTILOS 3D ---
const getCardStyle = (offset) => {
    const drag = isDragging.value ? currentDragY.value : 0;
    
    let yPos = 0;
    let scale = 1;
    let opacity = 1;
    let brightness = 1;
    let zIndex = 50 - offset;

    if (offset === -1) {
        // La que se va hacia arriba
        yPos = -CARD_HEIGHT + drag;
        opacity = drag > 0 ? (drag / 200) : 0; 
    }
    else if (offset === 0) {
        // ACTIVA
        yPos = drag * 0.4; // Resistencia
        scale = 1;
        brightness = 1;
    }
    else {
        // LAS DE ATRÁS (Asomando por abajo)
        // Fórmula: Posición base + Offset * Altura visible
        yPos = (offset * PEEK_HEIGHT) + (drag * 0.2);
        scale = 1 - (offset * 0.04); // Se hacen un 4% más pequeñas
        brightness = 1 - (offset * 0.15); // Se oscurecen un poco
    }

    return {
        transform: `translate3d(0, ${yPos}px, ${-offset * 20}px) scale(${scale})`,
        opacity: opacity,
        zIndex: zIndex,
        filter: `brightness(${brightness})`,
        transition: isDragging.value ? 'none' : 'all 0.4s cubic-bezier(0.2, 0.8, 0.2, 1)'
    };
};
</script>

<template>
    <div class="w-full h-[calc(100svh-64px)] bg-[#0f172a] overflow-hidden relative flex flex-col items-center justify-start pt-6 touch-none select-none"
         @touchstart="handleTouchStart" @touchmove="handleTouchMove" @touchend="handleTouchEnd">

        <div class="absolute inset-0 pointer-events-none transition-colors duration-700 opacity-20"
             :style="{ backgroundColor: getZoneData(virtualIndex)?.color || '#3b82f6' }"></div>

        <div class="z-50 mb-4 flex flex-col items-center pointer-events-none">
            <h2 class="text-[9px] font-black tracking-[0.3em] text-white/40 uppercase mb-2">EXPLORAR ZONAS</h2>
            <div class="flex gap-1">
                <div v-for="(_, idx) in sourceZones" :key="idx"
                     class="h-1 rounded-full transition-all duration-300"
                     :class="(virtualIndex % sourceZones.length) === idx ? 'w-4 bg-white' : 'w-1 bg-white/20'">
                </div>
            </div>
        </div>

        <div class="relative w-full max-w-[360px] h-full perspective-container">
            
            <div v-for="card in renderedCards" :key="card.key"
                 class="absolute left-0 right-0 w-full h-[420px] rounded-[30px] shadow-2xl will-change-transform origin-top"
                 :style="getCardStyle(card.offset)">
                
                <div class="relative w-full h-full rounded-[30px] overflow-hidden bg-slate-800 border border-white/10 flex flex-col"
                     @click.stop="jumpToCard(card.offset)">
                    
                    <div class="flex-1 bg-slate-900/60 backdrop-blur-md relative overflow-hidden transition-colors"
                         :class="{ 'pointer-events-none': card.offset !== 0 }">
                        
                        <div v-if="card.offset !== 0" class="absolute inset-0">
                            <img v-if="card.data.aisles?.[0]?.image_url" :src="card.data.aisles[0].image_url" class="w-full h-full object-cover opacity-60">
                            <div class="absolute inset-0 bg-black/30"></div>
                        </div>

                        <div v-else class="h-full flex flex-col">
                             <div class="px-6 pt-5 pb-2 flex justify-between items-center">
                                <span class="text-[10px] text-emerald-400 font-bold uppercase tracking-widest">
                                    {{ card.data.aisles?.length || 0 }} Categorías
                                </span>
                             </div>

                             <div class="flex-1 flex gap-3 overflow-x-auto snap-x snap-mandatory px-4 pb-4 items-center no-scrollbar"
                                  @touchstart.stop>
                                
                                <div v-for="aisle in card.data.aisles" :key="aisle.id"
                                     @click.stop="emit('select-zone', card.data)"
                                     class="snap-center shrink-0 w-[130px] h-[180px] bg-slate-800 rounded-2xl overflow-hidden border border-white/10 relative active:scale-95 transition-transform">
                                    <img v-if="aisle.image_url" :src="aisle.image_url" class="w-full h-[120px] object-cover bg-white">
                                    <div class="p-2 h-[60px] flex items-center justify-center text-center">
                                        <span class="text-xs font-bold text-white leading-tight line-clamp-2">{{ aisle.name }}</span>
                                    </div>
                                </div>
                             </div>
                        </div>
                    </div>

                    <div class="h-[85px] shrink-0 relative flex items-center px-6 transition-colors duration-300 overflow-hidden"
                         :style="{ backgroundColor: card.offset === 0 ? (card.data.color || '#334155') : '#1e293b' }">
                        
                        <img v-if="card.data.aisles?.[0]?.image_url" :src="card.data.aisles[0].image_url" 
                             class="absolute inset-0 w-full h-full object-cover opacity-10 mix-blend-overlay pointer-events-none">

                        <div class="relative z-10 w-full flex justify-between items-center">
                            <div>
                                <p class="text-[9px] font-black text-white/60 uppercase tracking-widest mb-1">
                                    0{{ (card.key % sourceZones.length) + 1 }}
                                </p>
                                <h2 class="text-2xl font-black text-white uppercase italic leading-none truncate w-[200px] drop-shadow-sm">
                                    {{ card.data.name }}
                                </h2>
                            </div>

                            <div class="w-10 h-10 rounded-full flex items-center justify-center transition-all shadow-lg"
                                 :class="card.offset === 0 ? 'bg-white text-black scale-100 rotate-0' : 'bg-white/10 text-white/50 scale-75 rotate-90'">
                                <span class="font-bold text-lg">&rarr;</span>
                            </div>
                        </div>
                        
                        <div class="absolute top-0 left-0 w-full h-[1px] bg-white/15"></div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</template>

<style scoped>
/* Evitar problemas de scroll en móviles */
.touch-none {
    touch-action: none; 
}
.perspective-container {
    perspective: 1000px;
    transform-style: preserve-3d;
}
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>