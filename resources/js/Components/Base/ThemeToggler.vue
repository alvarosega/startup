<script setup>
import { onMounted } from 'vue';
import { useTheme } from '@/Composables/useTheme';
import { Moon, Sun } from 'lucide-vue-next';

const { isDark, toggleTheme, initTheme } = useTheme();

onMounted(() => {
    initTheme();
});
</script>

<template>
    <button 
        @click="toggleTheme"
        class="relative flex items-center justify-center w-10 h-10 rounded-full transition-all duration-300 active:scale-90 focus:outline-none overflow-hidden group touch-manipulation"
        :class="[
            isDark 
                ? 'bg-white/5 text-primary hover:bg-white/10 ring-1 ring-white/10' 
                : 'bg-muted text-foreground hover:bg-muted/80 shadow-sm'
        ]"
        :title="isDark ? 'Encender Luces' : 'Modo Neón'"
        aria-label="Toggle theme"
    >
        <div v-if="isDark" class="absolute inset-0 bg-primary/20 blur-md rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>

        <transition
            mode="out-in"
            enter-active-class="transition duration-500 cubic-bezier(0.34, 1.56, 0.64, 1)"
            enter-from-class="opacity-0 rotate-[-120deg] scale-0"
            enter-to-class="opacity-100 rotate-0 scale-100"
            leave-active-class="transition duration-300 ease-in"
            leave-from-class="opacity-100 rotate-0 scale-100"
            leave-to-class="opacity-0 rotate-[120deg] scale-0"
        >
            <Sun v-if="!isDark" :size="20" stroke-width="2.5" class="relative z-10" />
            
            <Moon v-else :size="20" stroke-width="2.5" class="relative z-10 drop-shadow-[0_0_8px_rgba(0,240,255,0.8)]" />
        </transition>
    </button>
</template>