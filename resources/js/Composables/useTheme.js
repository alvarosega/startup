import { ref } from 'vue';

// Estado global único
const isDark = ref(localStorage.getItem('theme') !== 'light');

export function useTheme() {
    
    /**
     * Aplica las clases al HTML y persiste en Storage
     */
    const applyTheme = (dark) => {
        const html = document.documentElement;
        if (dark) {
            html.classList.add('dark');
            localStorage.setItem('theme', 'dark');
        } else {
            html.classList.remove('dark');
            localStorage.setItem('theme', 'light');
        }
    };

    /**
     * Inicialización Táctica
     * Llama esto en el onMounted de tu Layout principal
     */
    const initTheme = () => {
        const savedTheme = localStorage.getItem('theme');
        
        // Si es la primera vez o está guardado como dark, isDark es true
        if (savedTheme === 'light') {
            isDark.value = false;
            applyTheme(false);
        } else {
            isDark.value = true;
            applyTheme(true);
        }
    };

    /**
     * Alternar Tema
     */
    const toggleTheme = () => {
        isDark.value = !isDark.value;
        applyTheme(isDark.value);
    };

    return {
        isDark,
        initTheme,
        toggleTheme
    };
}