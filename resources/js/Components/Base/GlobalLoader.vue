<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';

defineProps({
    forceShow: { type: Boolean, default: false }
});

const isPageLoading = ref(false);

// Interceptores de Inertia
let unregisterStart = null;
let unregisterFinish = null;
let unregisterError = null;

onMounted(() => {
    unregisterStart = router.on('start', () => (isPageLoading.value = true));
    unregisterFinish = router.on('finish', () => (isPageLoading.value = false));
    unregisterError = router.on('error', () => (isPageLoading.value = false));
});

onUnmounted(() => {
    if (unregisterStart) unregisterStart();
    if (unregisterFinish) unregisterFinish();
    if (unregisterError) unregisterError();
});

// --- CONFIGURACIÓN TÉCNICA DEL LOADER ---
const totalDots = 60; // Cantidad de segmentos para una estela fluida
const duration = 1.2; // Velocidad F1: 1.2s por vuelta completa
const path = "M 23.8 0.5 C 17.5 -1.4 1.4 20.9 9.9 27.3 C 14.2 30.5 21.9 22.9 23.8 17.8 C 28 7 2.1 3.3 0.4 11.6 C -0.4 15.9 10 18.3 12.6 18.7 C 25.2 20.5 31.5 2.9 23.8 0.5 Z";

/**
 * Calcula el retraso de inicio para cada punto para crear la estela.
 * Basado en la proporción original del SVG.
 */
const getBeginDelay = (index) => {
    const step = 2.827 / 100; // Factor de desfase original
    return -(index * step * (duration / 5)) + 's';
};

/**
 * Calcula el radio progresivo para que la estela termine en punta.
 */
const getRadius = (index) => {
    return (index / totalDots).toFixed(3);
};
</script>

<template>
    <Transition name="loader-fade">
        <div v-if="isPageLoading || forceShow" 
             class="fixed inset-0 z-[10000] flex items-center justify-center bg-background/60 backdrop-blur-md pointer-events-auto">
            
            <div class="relative flex flex-col items-center">
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="-2 -2 32 32"
                    class="w-32 h-32 md:w-40 md:h-40 drop-shadow-[0_0_15px_rgba(var(--primary),0.5)]"
                >
                    <circle 
                        v-for="n in totalDots" 
                        :key="n"
                        :r="getRadius(n)" 
                        fill="hsl(var(--primary))"
                        :opacity="n / totalDots"
                    >
                        <animateMotion
                            :dur="duration + 's'"
                            :begin="getBeginDelay(n)"
                            repeatCount="indefinite"
                            :path="path"
                        ></animateMotion>
                    </circle>
                </svg>

                <div class="mt-8 text-center">
                    <span class="text-[11px] font-bold uppercase tracking-[0.3em] text-primary animate-pulse">
                        Sincronizando Sistema
                    </span>
                </div>
            </div>
        </div>
    </Transition>
</template>

<style scoped>
/* Transición Apple-Style: Suave y con ligero desenfoque */
.loader-fade-enter-active, .loader-fade-leave-active { 
    transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1); 
}
.loader-fade-enter-from, .loader-fade-leave-to { 
    opacity: 0; 
    filter: blur(8px);
}

svg {
    /* Optimización de renderizado para animaciones SVG complejas */
    shape-rendering: geometricPrecision;
}
</style>