<script setup>
import { computed, ref } from 'vue';

const props = defineProps({ zones: Object });
const emit = defineEmits(['select-zone']);

// Convertir objeto a array
const sourceZones = computed(() => props.zones ? Object.values(props.zones) : []);

// --- CONFIGURACIÓN VISUAL ---
const CARD_HEIGHT = 460; // Altura total de la tarjeta
const PEEK_HEIGHT = 85;  // Altura exacta de la barra inferior (Zona cliqueable)
const VISIBLE_COUNT = 4; // Cuántas tarjetas se renderizan

// --- ESTADO ---
const virtualIndex = ref(1000); // Índice infinito
const isDragging = ref(false);
const startY = ref(0);
const currentDragY = ref(0);
const isClick = ref(true); // Flag para distinguir click de scroll

// --- LÓGICA DE DATOS ---
const getZoneData = (vIndex) => {
    if (sourceZones.value.length === 0) return null;
    const realIndex = vIndex % sourceZones.value.length;
    return sourceZones.value[realIndex < 0 ? realIndex + sourceZones.value.length : realIndex];
};

const renderedCards = computed(() => {
    const cards = [];
    // Renderizamos desde la anterior (-1) hasta las siguientes (+3)
    for (let i = -1; i < VISIBLE_COUNT; i++) {
        cards.push({
            offset: i, 
            data: getZoneData(virtualIndex.value + i),
            key: virtualIndex.value + i
        });
    }
    return cards;
});

// --- INTERACCIÓN ---
const handleTouchStart = (e) => {
    isDragging.value = true;
    isClick.value = true; // Asumimos que es click hasta que se demuestre lo contrario
    startY.value = e.touches ? e.touches[0].clientY : e.clientY;
    currentDragY.value = 0;
};

const handleTouchMove = (e) => {
    if (!isDragging.value) return;
    
    const clientY = e.touches ? e.touches[0].clientY : e.clientY;
    const deltaY = clientY - startY.value;

    // Si se mueve más de 10px, ya no es un clic, es un arrastre
    if (Math.abs(deltaY) > 10) {
        isClick.value = false;
    }

    currentDragY.value = deltaY;
};

const handleTouchEnd = () => {
    isDragging.value = false;

    // Si fue un arrastre significativo, cambiamos de zona paso a paso
    if (!isClick.value) {
        if (currentDragY.value < -60) virtualIndex.value++; // Swipe Arriba
        else if (currentDragY.value > 60) virtualIndex.value--; // Swipe Abajo
    }
    
    currentDragY.value = 0;
};

// --- SALTO DIRECTO (CLIC) ---
const handleCardClick = (offset) => {
    // Solo ejecutamos si el flag isClick sigue siendo true (no hubo arrastre)
    if (isClick.value && offset !== 0) {
        virtualIndex.value += offset; // Saltamos N posiciones de golpe
    }
};

// --- ESTILOS 3D ---
const getCardStyle = (offset) => {
    const drag = isDragging.value ? currentDragY.value : 0;
    
    let yPos = 0;
    let scale = 1;
    let opacity = 1;
    let zIndex = 50 - offset; // La activa (0) tiene 50, la siguiente (1) 49...
    let brightness = 1;

    if (offset === -1) {
        // La tarjeta que se fue hacia arriba
        yPos = -CARD_HEIGHT + drag;
        opacity = drag > 0 ? (drag / 300) : 0; // Solo visible si jalamos hacia abajo
        scale = 1;
    }
    else if (offset === 0) {
        // TARJETA ACTIVA
        yPos = drag * 0.3; 
        scale = 1;
    }
    else {
        // TARJETAS SIGUIENTES (ABAJO)
        // Posición = Altura de la tarjeta activa + (offset-1) * Altura de la pestaña
        // Esto asegura que la tarjeta 1 empiece justo donde termina el cuerpo de la 0
        
        // Ajuste matemático: 
        // offset 1 debe estar en PEEK_HEIGHT * 1
        yPos = (offset * PEEK_HEIGHT) + (drag * 0.15); 
        
        scale = 1 - (offset * 0.03); 
        brightness = 1 - (offset * 0.1); 
    }

    return {
        transform: `translate3d(0, ${yPos}px, ${-offset * 30}px) scale(${scale})`,
        opacity: opacity,
        zIndex: zIndex,
        filter: `brightness(${brightness})`,
        transition: isDragging.value ? 'none' : 'all 0.5s cubic-bezier(0.2, 0.9, 0.1, 1)'
    };
};
</script>

<template>
    <div class="w-full h-[calc(100svh-64px)] bg-[#0f172a] overflow-hidden relative flex flex-col items-center pt-8 touch-none select-none"
         @mousedown="handleTouchStart" @mousemove="handleTouchMove" @mouseup="handleTouchEnd" @mouseleave="handleTouchEnd"
         @touchstart="handleTouchStart" @touchmove="handleTouchMove" @touchend="handleTouchEnd">

        <div class="absolute inset-0 pointer-events-none transition-colors duration-700 opacity-20"
             :style="{ backgroundColor: getZoneData(virtualIndex)?.color || '#3b82f6' }"></div>
        
        <div class="z-50 mb-2 flex flex-col items-center pointer-events-none">
            <h2 class="text-[9px] font-black tracking-[0.3em] text-white/40 uppercase mb-2">NAVEGAR</h2>
            <div class="flex gap-1">
                <div v-for="(_, idx) in sourceZones" :key="idx"
                     class="h-1 rounded-full transition-all duration-300"
                     :class="(virtualIndex % sourceZones.length) === idx ? 'w-4 bg-white' : 'w-1 bg-white/20'">
                </div>
            </div>
        </div>

        <div class="relative w-full max-w-[360px] h-full perspective-container">
            
            <div v-for="card in renderedCards" :key="card.key"
                 class="absolute left-0 right-0 w-full rounded-[24px] shadow-2xl will-change-transform origin-top"
                 :style="{ 
                     height: `${CARD_HEIGHT}px`,
                     ...getCardStyle(card.offset) 
                 }">
                
                <div class="relative w-full h-full rounded-[24px] overflow-hidden bg-slate-800 border border-white/10 flex flex-col transition-colors duration-300 hover:border-white/30"
                     @click="handleCardClick(card.offset)">
                    
                    <div class="flex-1 relative overflow-hidden bg-slate-900/50 backdrop-blur-md"
                         :class="{ 'pointer-events-none': card.offset !== 0 }">
                        
                        <div v-if="card.offset !== 0" class="absolute inset-0 z-20">
                            <div class="absolute inset-0 bg-black/40"></div>
                            <img v-if="card.data.aisles?.[0]?.image_url" :src="card.data.aisles[0].image_url" class="w-full h-full object-cover opacity-30">
                        </div>

                        <div v-else class="h-full flex flex-col relative z-10">
                            <div class="px-6 pt-5 pb-2">
                                <span class="text-[10px] text-emerald-400 font-bold uppercase tracking-widest">
                                    {{ card.data.aisles?.length || 0 }} Categorías
                                </span>
                            </div>
                            
                            <div class="flex-1 flex gap-3 overflow-x-auto snap-x snap-mandatory px-4 pb-4 items-center no-scrollbar"
                                 @mousedown.stop @touchstart.stop>
                                
                                <div v-for="aisle in card.data.aisles" :key="aisle.id"
                                     @click.stop="emit('select-zone', card.data)"
                                     class="snap-center shrink-0 w-[140px] h-[190px] bg-slate-800 rounded-xl overflow-hidden border border-white/10 relative active:scale-95 transition-transform group">
                                    <img v-if="aisle.image_url" :src="aisle.image_url" class="w-full h-[130px] object-cover bg-white">
                                    <div class="p-2 h-[60px] flex items-center justify-center text-center bg-slate-800">
                                        <span class="text-xs font-bold text-white leading-tight line-clamp-2">{{ aisle.name }}</span>
                                    </div>
                                    <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                        <span class="text-white font-bold tracking-widest uppercase text-[10px] border border-white px-2 py-1 rounded">Ver</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="relative shrink-0 flex items-center px-6 transition-colors duration-300 overflow-hidden"
                         :style="{ 
                             height: `${PEEK_HEIGHT}px`,
                             backgroundColor: card.offset === 0 ? (card.data.color || '#334155') : '#1e293b' 
                         }">
                        
                        <img v-if="card.data.aisles?.[0]?.image_url" :src="card.data.aisles[0].image_url" 
                             class="absolute inset-0 w-full h-full object-cover opacity-10 mix-blend-overlay pointer-events-none">

                        <div class="relative z-10 w-full flex justify-between items-center">
                            <div>
                                <p class="text-[9px] font-black text-white/50 uppercase tracking-widest mb-0.5">
                                    0{{ (card.key % sourceZones.length) + 1 }}
                                </p>
                                <h2 class="text-2xl font-black text-white uppercase italic leading-none truncate w-[180px] drop-shadow-sm">
                                    {{ card.data.name }}
                                </h2>
                            </div>

                            <div class="w-10 h-10 rounded-full flex items-center justify-center transition-all shadow-lg"
                                 :class="card.offset === 0 ? 'bg-white text-black scale-100' : 'bg-white/5 text-white/30 scale-90 border border-white/10'">
                                <span v-if="card.offset === 0" class="font-bold text-lg">&rarr;</span>
                                <span v-else class="text-[10px] uppercase font-bold">Ir</span>
                            </div>
                        </div>
                        
                        <div class="absolute top-0 left-0 w-full h-[1px] bg-white/10"></div>
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