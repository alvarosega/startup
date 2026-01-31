<script setup>
import { useForm } from '@inertiajs/vue3';
import { VueTelInput } from 'vue-tel-input';
import 'vue-tel-input/vue-tel-input.css';
import BaseCheckbox from '@/Components/Base/BaseCheckbox.vue'; 
import BaseInput from '@/Components/Base/BaseInput.vue'; // Reutilización clave
import { useAuthModal } from '@/Composables/useAuthModal';
import { LogIn, Lock, Smartphone, UserPlus, ArrowRight } from 'lucide-vue-next';

const emit = defineEmits(['close', 'switchToRegister', 'switchToForgot']);
const { closeModals } = useAuthModal();

const form = useForm({
    phone: '',
    password: '',
    remember: false,
    device_name: 'Web Browser',
});

const onInput = (phone, phoneObject) => {
    if (phoneObject?.number) {
        form.phone = phoneObject.number;
    }
};

const submit = () => {
    form.clearErrors();
    form.post(route('login'), {
        preserveScroll: true,
        onSuccess: () => {
            closeModals();
            emit('close');
        },
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <div class="p-6 md:p-8">
        <div class="text-center mb-8">
            <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-primary/20 to-secondary/20 flex items-center justify-center mx-auto mb-4 animate-scale-in">
                <LogIn :size="32" class="text-primary drop-shadow-sm" />
            </div>
            <h2 class="text-2xl font-display font-black text-foreground tracking-tight">
                ¡Hola de nuevo!
            </h2>
            <p class="text-sm text-muted-foreground mt-1">
                Ingresa tus credenciales para continuar.
            </p>
        </div>

        <form @submit.prevent="submit" class="space-y-5">
            
            <div class="space-y-1.5">
                <label class="form-label flex items-center gap-1.5 ml-1">
                    <Smartphone :size="14" class="text-muted-foreground" /> Celular
                </label>
                <div class="relative group">
                    <vue-tel-input 
                        v-model="form.phone" 
                        @on-input="onInput"
                        mode="international"
                        :preferredCountries="['BO']" 
                        :defaultCountry="'BO'"
                        :inputOptions="{ placeholder: 'Ej: 77712345', autofocus: true }"
                        class="!w-full !rounded-xl !border-input !bg-background !text-sm !h-[46px] hover:!border-primary/50 focus-within:!border-primary focus-within:!ring-4 focus-within:!ring-primary/10 transition-all duration-200"
                        :class="{ '!border-error focus-within:!ring-error/10': form.errors.phone }"
                    />
                </div>
                <p v-if="form.errors.phone" class="form-error ml-1">{{ form.errors.phone }}</p>
            </div>

            <div class="space-y-1">
                <div class="flex justify-between items-center mb-1 ml-1">
                    <label class="form-label flex items-center gap-1.5">
                        <Lock :size="14" class="text-muted-foreground" /> Contraseña
                    </label>
                    <button type="button" @click="$emit('switchToForgot')" class="text-xs font-bold text-primary hover:text-primary/80 transition-colors">
                        ¿Olvidaste tu clave?
                    </button>
                </div>
                <BaseInput 
                    v-model="form.password" 
                    type="password" 
                    placeholder="••••••••" 
                    :error="form.errors.password"
                />
            </div>

            <div class="flex items-center ml-1">
                <BaseCheckbox v-model="form.remember" label="Mantener sesión iniciada" />
            </div>

            <button type="submit" 
                    :disabled="form.processing"
                    class="btn btn-primary w-full py-3 shadow-lg shadow-primary/25 hover:shadow-primary/40 transition-all active:scale-[0.98] group">
                <span v-if="form.processing" class="spinner spinner-sm"></span>
                <span v-else class="flex items-center justify-center gap-2 font-bold text-base">
                    Ingresar <ArrowRight :size="18" class="group-hover:translate-x-1 transition-transform" />
                </span>
            </button>
        </form>

        <div class="mt-8 pt-6 border-t border-border text-center">
            <p class="text-sm text-muted-foreground">
                ¿Aún no tienes cuenta? 
                <button @click="$emit('switchToRegister')" class="font-bold text-primary hover:underline ml-1 inline-flex items-center gap-1">
                    Regístrate gratis <UserPlus :size="14" />
                </button>
            </p>
        </div>
    </div>
</template>

<style>
/* Overrides globales para vue-tel-input dentro de este componente */
.vue-tel-input:focus-within {
    box-shadow: none !important; /* Manejado por Tailwind ring */
}
.vti__dropdown {
    border-top-left-radius: 0.75rem !important; /* radius-xl */
    border-bottom-left-radius: 0.75rem !important;
    background-color: transparent !important;
}
.vti__input {
    border-radius: 0.75rem !important;
    background-color: transparent !important;
}
</style>