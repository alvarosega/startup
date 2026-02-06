<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Scan, Minimize2 } from 'lucide-vue-next';

const isFullscreen = ref(false);

const toggleFullscreen = async () => {
    try {
        const root = document.documentElement; // USAR ESTO SIEMPRE

        if (!document.fullscreenElement) {
            // Solicitamos fullscreen al ROOT del documento
            await root.requestFullscreen({ navigationUI: "hide" });
        } else {
            await document.exitFullscreen();
        }
    } catch (err) {
        console.error(`Error fullscreen: ${err.message}`);
    }
};

const updateState = () => {
    // La fuente de la verdad es SIEMPRE el documento, no una variable local anterior
    isFullscreen.value = !!document.fullscreenElement;
};

onMounted(() => {
    // 1. Suscribirse a cambios
    document.addEventListener('fullscreenchange', updateState);
    // 2. IMPORTANTE: Verificar el estado INMEDIATAMENTE al cargar la página
    // Esto arregla el bug de que el icono se reinicie al navegar
    updateState();
});

onUnmounted(() => document.removeEventListener('fullscreenchange', updateState));
</script>

<template>
    <button 
        @click="toggleFullscreen"
        class="relative flex items-center justify-center w-10 h-10 rounded-full transition-all duration-300 focus:outline-none group overflow-visible"
        :class="isFullscreen ? 'bg-primary/10 text-primary' : 'text-foreground hover:bg-muted/50'"
        :title="isFullscreen ? 'Salir del modo inmersivo' : 'Pantalla completa'"
    >
        <span v-if="!isFullscreen" class="absolute -inset-1 rounded-full border border-primary/40 opacity-50 animate-pulse pointer-events-none"></span>
        
        <transition
            mode="out-in"
            enter-active-class="transition duration-300 cubic-bezier(0.34, 1.56, 0.64, 1)"
            enter-from-class="opacity-0 scale-50 rotate-45"
            enter-to-class="opacity-100 scale-100 rotate-0"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="opacity-100 scale-100 rotate-0"
            leave-to-class="opacity-0 scale-50 -rotate-45"
        >
            <Scan v-if="!isFullscreen" :size="20" stroke-width="2" class="group-hover:text-primary transition-colors" />
            <Minimize2 v-else :size="20" stroke-width="2" />
        </transition>
    </button>
</template>