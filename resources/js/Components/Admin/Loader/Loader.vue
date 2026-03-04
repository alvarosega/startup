<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';

const isLoading = ref(false);
let timeout = null;

onMounted(() => {
    // Interceptores de estado de Inertia.js
    router.on('start', () => { 
        clearTimeout(timeout);
        isLoading.value = true; 
    });
    router.on('finish', () => { 
        // Ligero delay para asegurar que la animación se perciba en cargas instantáneas
        timeout = setTimeout(() => { isLoading.value = false; }, 400); 
    });
});

onUnmounted(() => {
    clearTimeout(timeout);
});
</script>

<template>
    <transition name="hologram-fade">
        <div v-if="isLoading" class="fixed inset-0 z-[9999] flex items-center justify-center bg-background/60 backdrop-blur-md border-y border-primary/30 shadow-[inset_0_0_150px_hsl(var(--primary)/0.1)]">
            
            <div class="loader-container relative w-[200px] h-[50px] scale-125">
                <div class="loader">
                    <span>
                        <span></span>
                        <span></span>
                        <span></span>
                        <span></span>
                    </span>
                    <div class="base">
                        <span></span>
                        <div class="face"></div>
                    </div>
                </div>
                <div class="longfazers">
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
                
                <div class="absolute -bottom-4 left-0 w-full h-[1px] bg-primary shadow-neon-primary animate-[scan_1s_ease-in-out_infinite_alternate]"></div>
            </div>

        </div>
    </transition>
</template>

<style scoped>
.hologram-fade-enter-active,
.hologram-fade-leave-active {
    transition: all 0.3s ease;
}
.hologram-fade-enter-from,
.hologram-fade-leave-to {
    opacity: 0;
    backdrop-filter: blur(0px);
}

/* =========================================
   GEOMETRÍA DE NAVE CYBERPUNK (0px RADIOS)
   ========================================= */
.loader-container {
    filter: drop-shadow(0 0 10px hsl(var(--primary) / 0.8));
}

.loader {
    position: absolute;
    top: 50%;
    margin-left: -50px;
    left: 50%;
    animation: speeder 0.4s linear infinite;
}

.loader > span {
    height: 5px;
    width: 35px;
    background: hsl(var(--primary));
    position: absolute;
    top: -19px;
    left: 60px;
}

.base span {
    position: absolute;
    width: 0;
    height: 0;
    border-top: 6px solid transparent;
    border-right: 100px solid hsl(var(--primary));
    border-bottom: 6px solid transparent;
}

.base span:before {
    content: "";
    height: 22px;
    width: 22px;
    background: hsl(var(--primary));
    position: absolute;
    right: -110px;
    top: -16px;
    /* Geometría afilada */
    clip-path: polygon(25% 0%, 100% 0%, 75% 100%, 0% 100%);
}

.base span:after {
    content: "";
    position: absolute;
    width: 0;
    height: 0;
    border-top: 0 solid transparent;
    border-right: 55px solid hsl(var(--primary));
    border-bottom: 16px solid transparent;
    top: -16px;
    right: -98px;
}

.face {
    position: absolute;
    height: 12px;
    width: 20px;
    background: hsl(var(--primary));
    transform: rotate(-40deg);
    right: -125px;
    top: -15px;
}

.face:after {
    content: "";
    height: 12px;
    width: 12px;
    background: hsl(var(--primary));
    right: 4px;
    top: 7px;
    position: absolute;
    transform: rotate(40deg);
    transform-origin: 50% 50%;
}

/* Trazadoras Láser */
.loader > span > span:nth-child(1),
.loader > span > span:nth-child(2),
.loader > span > span:nth-child(3),
.loader > span > span:nth-child(4) {
    width: 40px;
    height: 1px;
    background: hsl(var(--secondary));
    position: absolute;
    animation: fazer1 0.2s linear infinite;
    box-shadow: 0 0 5px hsl(var(--secondary));
}

.loader > span > span:nth-child(2) {
    top: 3px;
    animation: fazer2 0.4s linear infinite;
}

.loader > span > span:nth-child(3) {
    top: 1px;
    animation: fazer3 0.4s linear infinite;
    animation-delay: -1s;
}

.loader > span > span:nth-child(4) {
    top: 4px;
    animation: fazer4 1s linear infinite;
    animation-delay: -1s;
}

@keyframes fazer1 { 0% { left: 0; } 100% { left: -80px; opacity: 0; } }
@keyframes fazer2 { 0% { left: 0; } 100% { left: -100px; opacity: 0; } }
@keyframes fazer3 { 0% { left: 0; } 100% { left: -50px; opacity: 0; } }
@keyframes fazer4 { 0% { left: 0; } 100% { left: -150px; opacity: 0; } }

@keyframes speeder {
    0% { transform: translate(2px, 1px) rotate(0deg); }
    10% { transform: translate(-1px, -3px) rotate(-1deg); }
    20% { transform: translate(-2px, 0px) rotate(1deg); }
    30% { transform: translate(1px, 2px) rotate(0deg); }
    40% { transform: translate(1px, -1px) rotate(1deg); }
    50% { transform: translate(-1px, 3px) rotate(-1deg); }
    60% { transform: translate(-1px, 1px) rotate(0deg); }
    70% { transform: translate(3px, 1px) rotate(-1deg); }
    80% { transform: translate(-2px, -1px) rotate(1deg); }
    90% { transform: translate(2px, 1px) rotate(0deg); }
    100% { transform: translate(1px, -2px) rotate(-1deg); }
}

/* Partículas del entorno (Background Fakers) */
.longfazers {
    position: absolute;
    width: 100%;
    height: 100%;
}

.longfazers span {
    position: absolute;
    height: 1px;
    width: 20%;
    background: hsl(var(--primary) / 0.5);
}

.longfazers span:nth-child(1) { top: 20%; animation: lf 0.6s linear infinite; animation-delay: -5s; }
.longfazers span:nth-child(2) { top: 40%; animation: lf2 0.8s linear infinite; animation-delay: -1s; }
.longfazers span:nth-child(3) { top: 60%; animation: lf3 0.6s linear infinite; }
.longfazers span:nth-child(4) { top: 80%; animation: lf4 0.5s linear infinite; animation-delay: -3s; }

@keyframes lf { 0% { left: 200%; } 100% { left: -200%; opacity: 0; } }
@keyframes lf2 { 0% { left: 200%; } 100% { left: -200%; opacity: 0; } }
@keyframes lf3 { 0% { left: 200%; } 100% { left: -100%; opacity: 0; } }
@keyframes lf4 { 0% { left: 200%; } 100% { left: -100%; opacity: 0; } }

@keyframes scan {
    from { opacity: 0.2; width: 80%; left: 10%; }
    to { opacity: 1; width: 100%; left: 0; }
}
</style>