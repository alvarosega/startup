import { ref } from 'vue';

// Definimos las variables FUERA de la función para que sean GLOBALES (Singleton)
const showLogin = ref(false);
const showRegister = ref(false);

export function useAuthModal() {
    
    const openLogin = () => {
        showRegister.value = false;
        showLogin.value = true;
    };

    const openRegister = () => {
        showLogin.value = false;
        showRegister.value = true;
    };

    const closeModals = () => {
        showLogin.value = false;
        showRegister.value = false;
    };

    return {
        showLogin,
        showRegister,
        openLogin,
        openRegister,
        closeModals
    };
}