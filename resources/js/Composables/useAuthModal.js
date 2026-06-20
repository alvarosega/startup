import { ref } from 'vue';

// Estados globales
const showLogin = ref(false);
const showRegister = ref(false);
const showRegisterDriver = ref(false);
const showForgotPassword = ref(false); // Nuevo
const showResetPassword = ref(false);  // Nuevo
const showProfileWizard = ref(false);  // Nuevo

export function useAuthModal() {
    
    const closeModals = () => {
        showLogin.value = false;
        showRegister.value = false;
        showRegisterDriver.value = false;
        showForgotPassword.value = false;
        showResetPassword.value = false;
        showProfileWizard.value = false;
    };

    const openLogin = () => { closeModals(); showLogin.value = true; };
    const openRegister = () => { closeModals(); showRegister.value = true; };
    const openRegisterDriver = () => { closeModals(); showRegisterDriver.value = true; };
    
    // Nuevas funciones de apertura
    const openForgotPassword = () => { closeModals(); showForgotPassword.value = true; };
    const openResetPassword = () => { closeModals(); showResetPassword.value = true; };
    const openProfileWizard = () => { closeModals(); showProfileWizard.value = true; };

    return {
        showLogin, showRegister, showRegisterDriver,
        showForgotPassword, showResetPassword, showProfileWizard,
        openLogin, openRegister, openRegisterDriver,
        openForgotPassword, openResetPassword, openProfileWizard,
        closeModals
    };
}