<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';

defineProps({
    forceShow: { type: Boolean, default: false }
});

const isPageLoading = ref(false);

// Interceptores de Inertia para automatizar la carga
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
</script>

<template>
    <Transition name="loader-fade">
        <div v-if="isPageLoading || forceShow" 
             class="fixed inset-0 z-[10000] flex items-center justify-center bg-background/40 backdrop-blur-md pointer-events-auto">
            
            <div class="nucleus-container">
                <div class="nucleus">
                    <div class="nucleus-core"></div>
                </div>
                
                <div class="loader-orbits">
                    <div v-for="n in 6" :key="n" class="electron-orbit"></div>
                </div>
            </div>

            <div class="absolute bottom-12 text-center animate-pulse">
                <span class="text-[10px] font-black uppercase tracking-[0.4em] text-primary drop-shadow-[0_0_8px_rgba(0,240,255,0.5)]">
                    Sincronizando Red
                </span>
            </div>
        </div>
    </Transition>
</template>

<style scoped>
.nucleus-container {
    --primary-color: hsl(var(--primary));
    --radius: 2.2rem;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Núcleo Estilo Esfera de Energía */
.nucleus {
    width: var(--radius);
    height: var(--radius);
    background: var(--primary-color);
    border-radius: 50%;
    position: relative;
    z-index: 10;
    box-shadow: 
        0 0 40px var(--primary-color),
        inset -5px -5px 15px rgba(0,0,0,0.4),
        inset 5px 5px 15px rgba(255,255,255,0.4);
    animation: nucleus-throb 2s infinite ease-in-out;
}

.nucleus-core {
    position: absolute;
    inset: 20%;
    background: white;
    border-radius: 50%;
    filter: blur(4px);
    opacity: 0.8;
}

/* Órbitas */
.loader-orbits {
    position: absolute;
    width: 600%;
    height: 600%;
    perspective: 1000px;
}

.electron-orbit {
    position: absolute;
    inset: 0;
    border-radius: 50%;
    border: 1.5px solid rgba(255, 255, 255, 0.1);
    box-shadow: inset 0 0 10px var(--primary-color);
    animation: orbit-rotate 1.5s infinite linear;
}

/* Distribución geométrica de 6 órbitas (30° de diferencia) */
.electron-orbit:nth-child(1) { transform: rotateX(60deg) rotateY(20deg) rotateZ(0deg); animation-delay: 0s; }
.electron-orbit:nth-child(2) { transform: rotateX(60deg) rotateY(20deg) rotateZ(60deg); animation-delay: -0.25s; }
.electron-orbit:nth-child(3) { transform: rotateX(60deg) rotateY(20deg) rotateZ(120deg); animation-delay: -0.5s; }
.electron-orbit:nth-child(4) { transform: rotateX(60deg) rotateY(20deg) rotateZ(180deg); animation-delay: -0.75s; }
.electron-orbit:nth-child(5) { transform: rotateX(60deg) rotateY(20deg) rotateZ(240deg); animation-delay: -1s; }
.electron-orbit:nth-child(6) { transform: rotateX(60deg) rotateY(20deg) rotateZ(300deg); animation-delay: -1.25s; }

/* Animaciones */
@keyframes nucleus-throb {
    0%, 100% { transform: scale(1); filter: brightness(1); }
    50% { transform: scale(1.1); filter: brightness(1.3); }
}

@keyframes orbit-rotate {
    0% { transform: inherit rotateZ(0deg); border-top-color: white; }
    100% { transform: inherit rotateZ(360deg); border-top-color: var(--primary-color); }
}

/* Transición */
.loader-fade-enter-active, .loader-fade-leave-active { transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1); }
.loader-fade-enter-from, .loader-fade-leave-to { opacity: 0; filter: blur(10px); }
</style>