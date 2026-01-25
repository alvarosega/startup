<!-- resources/js/Components/Auth/ForgotPasswordForm.vue -->
<script setup>
import { useForm } from '@inertiajs/vue3';
import { Mail, ArrowLeft, Loader2 } from 'lucide-vue-next';

const emit = defineEmits(['close', 'switchToLogin', 'switchToReset']);

const form = useForm({ email: '' });

const submit = () => {
    form.post(route('password.email'), {
        onSuccess: () => {
            emit('switchToReset', form.email);
        }
    });
};
</script>

<template>
    <div class="p-6">
        <div class="text-center mb-6">
            <div class="inline-flex bg-primary/10 p-3 rounded-full mb-4 text-primary">
                <Mail :size="32" />
            </div>
            <h2 class="text-xl md:text-2xl font-display font-semibold text-foreground">
                Recuperar Contraseña
            </h2>
            <p class="text-sm text-muted-foreground mt-2">
                Ingresa tu correo y te enviaremos un código de seguridad.
            </p>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <div class="form-group">
                <label class="form-label">Correo Electrónico</label>
                <input v-model="form.email" 
                       type="email" 
                       placeholder="ejemplo@correo.com" 
                       :class="{'border-error': form.errors.email}"
                       autofocus
                       required>
                <p v-if="form.errors.email" class="form-error">{{ form.errors.email }}</p>
            </div>

            <div class="pt-2">
                <button type="submit" 
                        :disabled="form.processing"
                        class="btn btn-primary w-full flex justify-center items-center gap-2">
                    <Loader2 v-if="form.processing" class="animate-spin" :size="20" />
                    <span>Enviar Código</span>
                </button>
            </div>
        </form>

        <div class="mt-6 text-center">
            <button @click="$emit('switchToLogin')" 
                    class="text-sm text-muted-foreground hover:text-foreground font-medium flex items-center justify-center gap-2 mx-auto">
                <ArrowLeft :size="16" /> Volver al Login
            </button>
        </div>
    </div>
</template>