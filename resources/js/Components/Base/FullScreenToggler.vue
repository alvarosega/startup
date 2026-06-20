<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Maximize, Minimize } from 'lucide-vue-next';

const isFullscreen = ref(false);

const toggleFullscreen = () => {
    if (!document.fullscreenElement) {
        document.documentElement.requestFullscreen().catch(() => {});
    } else {
        document.exitFullscreen().catch(() => {});
    }
};

const updateFullscreenState = () => {
    isFullscreen.value = !!document.fullscreenElement;
};

// Asegurar que el estado es reactivo incluso si el usuario sale usando la tecla ESC
onMounted(() => {
    document.addEventListener('fullscreenchange', updateFullscreenState);
});

onUnmounted(() => {
    document.removeEventListener('fullscreenchange', updateFullscreenState);
});
</script>

<template>
    <button 
        @click="toggleFullscreen" 
        class="p-2.5 bg-transparent text-foreground hover:text-primary active:scale-95 transition-all duration-75 focus:outline-none"
        aria-label="Pantalla Completa"
    >
        <Minimize v-if="isFullscreen" :size="20" :stroke-width="1.5" />
        <Maximize v-else :size="20" :stroke-width="1.5" />
    </button>
</template>