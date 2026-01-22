<script setup>
import { computed } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import BaseInput from '@/Components/Base/BaseInput.vue';
import BaseButton from '@/Components/Base/BaseButton.vue';

const props = defineProps({
    step: Number, // Recibido desde el ProfileWizardController
});

const user = usePage().props.auth.user;

// Formulario para el Paso 1: Identidad
const formStep1 = useForm({
    first_name: '',
    last_name: '',
    birth_date: '',
});

// Formulario para el Paso 2: Email
const formStep2 = useForm({
    email: '',
});

const progress = computed(() => {
    if (props.step === 1) return 40;
    if (props.step === 2) return 70;
    return 100;
});

const submitStep1 = () => {
    formStep1.post(route('profile.step1'));
};

const submitStep2 = () => {
    formStep2.post(route('profile.step2'));
};
</script>

<template>
    <Head title="Completar Perfil" />

    <div class="min-h-screen bg-gray-50 py-12 px-4">
        <div class="max-w-md mx-auto bg-white rounded-2xl shadow-xl overflow-hidden">
            
            <div class="bg-gray-100 h-2 w-full">
                <div 
                    class="bg-blue-600 h-full transition-all duration-500"
                    :style="{ width: progress + '%' }"
                ></div>
            </div>

            <div class="p-8">
                <div class="text-center mb-8">
                    <h1 class="text-2xl font-bold text-gray-900">Validación de Identidad</h1>
                    <p class="text-gray-500 text-sm mt-2">Paso {{ step }} de 2: {{ step === 1 ? 'Datos Personales' : 'Seguridad' }}</p>
                </div>

                <form v-if="step === 1" @submit.prevent="submitStep1">
                    <BaseInput 
                        v-model="formStep1.first_name"
                        label="Nombres"
                        :error="formStep1.errors.first_name"
                        required
                    />
                    <BaseInput 
                        v-model="formStep1.last_name"
                        label="Apellidos"
                        :error="formStep1.errors.last_name"
                        required
                    />
                    <BaseInput 
                        v-model="formStep1.birth_date"
                        label="Fecha de Nacimiento"
                        type="date"
                        :error="formStep1.errors.birth_date"
                        required
                    />
                    
                    <p class="text-xs text-gray-400 mb-6">
                        * Debes ser mayor de 18 años para utilizar este servicio en La Paz.
                    </p>

                    <BaseButton :isLoading="formStep1.processing">
                        Guardar y Continuar
                    </BaseButton>
                </form>

                <form v-if="step === 2" @submit.prevent="submitStep2">
                    <div class="bg-blue-50 p-4 rounded-lg mb-6 border border-blue-100">
                        <p class="text-sm text-blue-800">
                            <strong>Importante:</strong> Este correo será el único medio para recuperar tu contraseña si olvidas tu acceso por celular.
                        </p>
                    </div>

                    <BaseInput 
                        v-model="formStep2.email"
                        label="Correo Electrónico"
                        type="email"
                        placeholder="ejemplo@correo.com"
                        :error="formStep2.errors.email"
                        required
                    />

                    <BaseButton :isLoading="formStep2.processing">
                        Verificar Correo
                    </BaseButton>
                </form>
            </div>
        </div>

        <div class="mt-8 text-center">
            <button @click="$inertia.post(route('logout'))" class="text-sm text-gray-500 hover:text-red-600 underline">
                Cerrar sesión y completar después
            </button>
        </div>
    </div>
</template>