<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3'; // Importamos router para la redirección al registro
import LoginForm from '@/Components/Auth/LoginForm.vue';
import ForgotPasswordForm from '@/Components/Auth/ForgotPasswordForm.vue';
import ResetPasswordForm from '@/Components/Auth/ResetPasswordForm.vue';

// Estado para controlar qué vista mostramos: 'login', 'forgot', 'reset'
const currentView = ref('login');

// Estado para guardar el email y pasarlo del paso 1 al paso 2
const recoveryEmail = ref('');

// --- MANEJADORES DE ESTADO ---

const showForgot = () => {
    currentView.value = 'forgot';
};

const showLogin = () => {
    currentView.value = 'login';
    recoveryEmail.value = ''; 
};

// Navegar a la página de registro (Ruta de Laravel)
const handleRegister = () => {
    // Asumiendo que tu ruta se llama 'register' como en el controlador
    router.get(route('register'));
};

// ESTE ES EL MANEJADOR QUE ESTABA FALLANDO POR EL ERROR DE SINTAXIS
const handleSwitchToReset = (email) => {
    console.log("Padre recibiendo email:", email); // Para verificar en consola
    recoveryEmail.value = email; 
    currentView.value = 'reset'; 
};
</script>

<template>
    <div class="min-h-screen flex items-center justify-center bg-slate-50 p-4">
        <Head title="Acceso Seguro" />

        <div class="w-full max-w-md bg-white rounded-3xl shadow-xl border border-slate-100 overflow-hidden relative">
            
            <LoginForm 
                v-if="currentView === 'login'" 
                @switchToForgot="showForgot"
                @switchToRegister="handleRegister"
            />

            <ForgotPasswordForm 
                v-if="currentView === 'forgot'"
                @switchToLogin="showLogin"
                @switchToReset="handleSwitchToReset" 
            />
            
            <ResetPasswordForm 
                v-if="currentView === 'reset'"
                :email="recoveryEmail"
                @switchToLogin="showLogin"
            />

        </div>
    </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>