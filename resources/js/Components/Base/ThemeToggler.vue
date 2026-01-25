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
            class="btn btn-outline p-3 rounded-full shadow-lg hover:shadow-xl active:scale-95 transition-all duration-base ease-smooth"
            :class="[
                isDark 
                    ? 'border-border/50 text-yellow-400 hover:bg-yellow-400/10' 
                    : 'border-border/50 text-amber-500 hover:bg-amber-500/10'
            ]"
            :title="isDark ? 'Cambiar a tema claro' : 'Cambiar a tema oscuro'"
            aria-label="Toggle theme"
        >
            <transition
                name="theme-toggle"
                mode="out-in"
                enter-active-class="animate-scale-in"
                leave-active-class="animate-scale-out"
            >
                <Sun v-if="!isDark" :size="20" key="sun" class="transition-transform duration-fast" />
                <Moon v-else :size="20" key="moon" class="transition-transform duration-fast" />
            </transition>
        </button>
    </template>