<script setup>
    import { onMounted } from 'vue';
    import { useTheme } from '@/Composables/useTheme';
    import { Moon, Sun } from 'lucide-vue-next';
    
    const { isDark, toggleTheme, initTheme } = useTheme();
    
    // Aseguramos que el tema correcto se cargue al montar este componente
    onMounted(() => {
        initTheme();
    });
    </script>
    
    <template>
        <button 
            @click="toggleTheme"
            class="fixed bottom-6 right-6 z-50 p-3 rounded-full shadow-lg border border-skin-border transition-all duration-300 transform hover:scale-110 active:scale-95"
            :class="isDark 
                ? 'bg-skin-fill-card text-yellow-400 shadow-yellow-900/20' 
                : 'bg-skin-fill text-skin-primary shadow-indigo-500/20'"
            title="Cambiar Tema"
        >
            <Sun v-if="!isDark" :size="24" class="animate-in fade-in zoom-in duration-300" />
            
            <Moon v-else :size="24" class="animate-in fade-in zoom-in duration-300" />
        </button>
    </template>