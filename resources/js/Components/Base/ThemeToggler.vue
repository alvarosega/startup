<script setup>
import { useTheme } from '@/Composables/useTheme';
import { Sun, Moon } from 'lucide-vue-next';

const { isDark, toggleTheme } = useTheme();
</script>

<template>
    <button 
        @click="toggleTheme" 
        class="p-2.5 bg-transparent text-foreground hover:text-primary active:scale-95 transition-all duration-75 focus:outline-none"
        aria-label="Alternar Tema"
    >
        <Moon v-if="!isDark" :size="20" :stroke-width="1.5" />
        <Sun v-else :size="20" :stroke-width="1.5" />
    </button>
</template>