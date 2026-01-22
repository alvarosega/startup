import { ref } from 'vue';

// Creamos la variable reactiva fuera de la función para que el estado se comparta
// entre todos los componentes que usen este composable (Singleton pattern).
const isDark = ref(false);

export function useTheme() {
    
    // Función para inicializar al cargar la app
    const initTheme = () => {
        // Verifica si hay preferencia guardada O si el sistema operativo está en modo oscuro
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            isDark.value = true;
            document.documentElement.classList.add('dark');
        } else {
            isDark.value = false;
            document.documentElement.classList.remove('dark');
        }
    };

    // Función para alternar
    const toggleTheme = () => {
        isDark.value = !isDark.value;
        
        if (isDark.value) {
            document.documentElement.classList.add('dark');
            localStorage.theme = 'dark';
        } else {
            document.documentElement.classList.remove('dark');
            localStorage.theme = 'light';
        }
    };

    return {
        isDark,
        initTheme,
        toggleTheme
    };
}