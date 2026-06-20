<script setup>
import { computed, ref } from 'vue';

const props = defineProps({ zones: Object });
const emit = defineEmits(['select-zone']);
const dynamicZones = computed(() => props.zones ? Object.values(props.zones) : []);

// Configuración del Cilindro
const RADIUS = 300; // Qué tan ancho es el cilindro

// Estado de rotación táctil
const currentRotation = ref(0);
const startX = ref(0);
const isDragging = ref(false);
const lastRotation = ref(0);

const handleTouchStart = (e) => {
    isDragging.value = true;
    startX.value = e.touches[0].clientX;
    lastRotation.value = currentRotation.value;
};

const handleTouchMove = (e) => {
    if (!isDragging.value) return;
    const deltaX = e.touches[0].clientX - startX.value;
    // Velocidad de rotación: 0.5 grados por pixel movido
    currentRotation.value = lastRotation.value + (deltaX * 0.5);
};

const handleTouchEnd = () => {
    isDragging.value = false;
};

// Calcula la posición 3D de cada tarjeta en el círculo
const getCardTransform = (index) => {
    const total = dynamicZones.value.length;
    const anglePerCard = 360 / total;
    const angle = anglePerCard * index;
    // Girar sobre su eje Y, y luego empujar hacia afuera (radio)
    return `rotateY(${angle}deg) translateZ(${RADIUS}px)`;
};
</script>

<template>
    <div class="w-full h-screen bg-[#050505] overflow-hidden relative touch-none flex flex-col items-center justify-center"
         @touchstart="handleTouchStart" @touchmove="handleTouchMove" @touchend="handleTouchEnd">
        
        <div class="absolute top-10 text-center z-10 pointer-events-none">
            <h1 class="text-2xl font-black text-white uppercase tracking-[0.5em] mb-2 text-glow">HOLODECK</h1>
            <p class="text-[10px] text-cyan-400 uppercase tracking-widest animate-pulse">← Desliza para girar →</p>
        </div>

        <div class="scene">
            <div class="cylinder" :style="{ transform: `rotateY(${currentRotation}deg)` }">
                
                <div v-for="(zone, index) in dynamicZones" :key="zone.id"
                     class="card-container absolute cursor-pointer"
                     @click="emit('select-zone', zone)"
                     :style="{ transform: getCardTransform(index) }">
                    
                    <div class="holo-card w-[180px] h-[280px] relative rounded-xl overflow-hidden border border-cyan-500/30 transition-all duration-300 group hover:border-cyan-400/80 hover:scale-105">
                        
                        <div class="absolute inset-0 bg-cyan-950/40 backdrop-blur-sm"></div>
                        <div class="absolute inset-0 bg-[url('https://media.giphy.com/media/3o7TKSjRrfIPjeiVyM/giphy.gif')] opacity-10 mix-blend-overlay pointer-events-none"></div>

                        <div class="relative z-10 h-full flex flex-col p-4">
                            <div class="w-full aspect-square rounded-full border-2 border-cyan-500/50 p-1 mb-4 relative overflow-hidden group-hover:rotate-[360deg] transition-transform duration-[5s] linear">
                                <div class="w-full h-full rounded-full overflow-hidden relative">
                                    <img v-if="zone.aisles?.[0]?.image_url" :src="zone.aisles[0].image_url" class="w-full h-full object-cover opacity-70 group-hover:opacity-100 transition-opacity">
                                    <div class="absolute inset-0 bg-cyan-500/20 mix-blend-overlay"></div>
                                </div>
                            </div>

                            <div class="mt-auto text-center">
                                <h3 class="text-white font-bold uppercase text-sm mb-1 group-hover:text-cyan-300 transition-colors">{{ zone.name }}</h3>
                                <div class="h-[1px] w-full bg-gradient-to-r from-transparent via-cyan-500 to-transparent"></div>
                                <p class="text-[9px] text-cyan-400 mt-1">{{ zone.aisles?.length || 0 }} SECCIONES</p>
                            </div>
                        </div>

                    </div>

                    <div class="reflection absolute top-full left-0 w-full h-full scale-y-[-1] opacity-20 blur-sm pointer-events-none">
                        <div class="w-[180px] h-[280px] bg-gradient-to-b from-cyan-500/50 to-transparent rounded-xl"></div>
                    </div>

                </div>

            </div>
        </div>
        
        <div class="absolute bottom-[-200px] w-[800px] h-[800px] bg-cyan-500/10 rounded-full blur-[100px] pointer-events-none transform rotateX(70deg)"></div>

    </div>
</template>

<style scoped>
.text-glow { text-shadow: 0 0 10px rgba(255,255,255,0.5), 0 0 20px rgba(6,182,212,0.5); }

.scene {
    perspective: 1200px;
    width: 200px; height: 300px;
    position: relative;
    transform: translateY(-50px); /* Ajuste de altura visual */
}

.cylinder {
    width: 100%; height: 100%;
    position: absolute;
    transform-style: preserve-3d;
    /* Inercia suave cuando se suelta */
    transition: transform 0.1s linear;
}
/* Si no estamos arrastrando, usamos una transición más larga para inercia */
.cylinder:not(:active) {
     transition: transform 0.8s cubic-bezier(0.1, 0.6, 0.2, 1);
}

.card-container {
    /* Centramos el punto de origen para que giren sobre el centro del cilindro */
    top: 0; left: 0;
    /* backface-visibility: hidden; /* Opcional: ocultar las de atrás para rendimiento */
}
</style>