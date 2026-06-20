<script setup>
import { computed, ref, onMounted, onUnmounted } from 'vue';

const props = defineProps({ zones: Object });
const emit = defineEmits(['select-zone']);
const dynamicZones = computed(() => props.zones ? Object.values(props.zones) : []);

const scrollProgress = ref(0);
const containerRef = ref(null);

// Distancia entre portales en el eje Z
const Z_GAP = 800; 

const handleScroll = () => {
    if (!containerRef.value) return;
    const { scrollTop, scrollHeight, clientHeight } = containerRef.value;
    // Calculamos cuánto hemos scrolleado (0.0 a 1.0)
    scrollProgress.value = scrollTop / (scrollHeight - clientHeight);
};

// Calculamos el estilo de cada portal en tiempo real
const getPortalStyle = (index) => {
    const totalDepth = (dynamicZones.value.length - 1) * Z_GAP;
    // Posición base del portal en el fondo
    const baseZ = index * -Z_GAP; 
    // Cuánto lo traemos hacia adelante basado en el scroll
    const currentZ = baseZ + (scrollProgress.value * (totalDepth + Z_GAP)); // +Z_GAP extra para que el último pase la cámara
    
    // Opacidad y escala basadas en la distancia a la cámara (Z=0)
    let opacity = 1;
    let scale = 1;

    if (currentZ > 200) { // Ya pasó la cámara
        opacity = 0;
    } else if (currentZ < -2000) { // Muy lejos en el fondo
        opacity = 0;
        scale = 0.5;
    } else {
        // Mapear distancia a opacidad (Fade in desde la niebla)
        opacity = 1 - (Math.abs(currentZ) / 2000);
    }

    return {
        transform: `translateZ(${currentZ}px) scale(${scale})`,
        opacity: opacity,
        zIndex: Math.round(opacity * 100) // Asegurar que los cercanos estén encima
    };
};
</script>

<template>
    <div ref="containerRef" class="w-full h-screen bg-black overflow-y-auto no-scrollbar" @scroll="handleScroll">
        <div class="h-[500vh] relative">
            
            <div class="sticky top-0 h-screen w-full perspective-scene flex items-center justify-center overflow-hidden">
                
                <div class="tunnel-structure" :style="{ transform: `translateZ(${scrollProgress * Z_GAP}px)` }">
                    <div class="wall wall-top"></div>
                    <div class="wall wall-bottom"></div>
                    <div class="wall wall-left"></div>
                    <div class="wall wall-right"></div>
                </div>

                <div class="portals-container">
                    <div v-for="(zone, index) in dynamicZones" :key="zone.id"
                         class="portal-item absolute cursor-pointer group will-change-transform"
                         @click="emit('select-zone', zone)"
                         :style="getPortalStyle(index)">
                        
                        <div class="relative w-[300px] h-[400px] bg-slate-900/80 border-[4px] rounded-2xl overflow-hidden transition-all duration-300 group-hover:scale-105"
                             :style="{ borderColor: zone.color || '#10b981', boxShadow: `0 0 50px ${zone.color || '#10b981'}55` }">
                            
                            <img v-if="zone.aisles?.[0]?.image_url" :src="zone.aisles[0].image_url" class="absolute inset-0 w-full h-full object-cover opacity-50 mix-blend-screen grayscale group-hover:grayscale-0 transition-all">
                            
                            <div class="absolute inset-0 bg-[url('https://media.giphy.com/media/3o7TKSjRrfIPjeiVyM/giphy.gif')] opacity-10 mix-blend-overlay pointer-events-none"></div>

                            <div class="absolute inset-0 bg-gradient-to-t from-black via-transparent to-black/50"></div>

                            <div class="absolute bottom-10 left-0 w-full text-center">
                                <p class="text-xs font-mono uppercase tracking-[0.3em] mb-2" :style="{ color: zone.color }">Destino 0{{index + 1}}</p>
                                <h2 class="text-4xl font-black text-white uppercase italic tracking-tighter drop-shadow-[0_5px_15px_rgba(255,255,255,0.5)]">
                                    {{ zone.name }}
                                </h2>
                            </div>

                            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-20 h-20 rounded-full border-2 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-500 group-hover:scale-110 animate-pulse"
                                 :style="{ borderColor: zone.color }">
                                <span class="text-white text-2xl">GO</span>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

.perspective-scene {
    /* Perspectiva extrema para sensación de velocidad */
    perspective: 600px;
    background: #000;
}

/* ESTRUCTURA DEL TÚNEL */
.tunnel-structure {
    position: absolute;
    width: 100%; height: 100%;
    transform-style: preserve-3d;
}
.wall {
    position: absolute;
    /* Patrón de rejilla futurista */
    background-image: 
        linear-gradient(rgba(16, 185, 129, 0.2) 1px, transparent 1px),
        linear-gradient(90deg, rgba(16, 185, 129, 0.2) 1px, transparent 1px);
    background-size: 50px 50px;
    backface-visibility: hidden;
    opacity: 0.3;
}
.wall-top {
    width: 2000px; height: 2000px;
    left: 50%; top: 50%;
    transform: translate(-50%, -50%) translateY(-300px) rotateX(90deg);
    background-image: linear-gradient(to bottom, #000, transparent); /* Se desvanece al fondo */
}
.wall-bottom {
    width: 2000px; height: 2000px;
    left: 50%; top: 50%;
    transform: translate(-50%, -50%) translateY(300px) rotateX(-90deg);
}
.wall-left {
    width: 2000px; height: 600px;
    left: 50%; top: 50%;
    transform: translate(-50%, -50%) translateX(-300px) rotateY(90deg);
}
.wall-right {
    width: 2000px; height: 600px;
    left: 50%; top: 50%;
    transform: translate(-50%, -50%) translateX(300px) rotateY(-90deg);
}

.portals-container {
    position: relative;
    transform-style: preserve-3d;
}
.portal-item {
    /* Centrado absoluto antes de aplicar transformaciones 3D */
    top: 50%; left: 50%;
    margin-top: -200px; margin-left: -150px; /* Mitad de ancho/alto para centrar */
}
</style>