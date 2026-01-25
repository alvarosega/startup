<!-- resources/js/Components/Auth/ResetPasswordForm.vue -->
<script setup>
import { useForm } from '@inertiajs/vue3';
import { KeyRound, CheckCircle, Loader2 } from 'lucide-vue-next';

const props = defineProps({ email: String });
const emit = defineEmits(['close', 'switchToLogin']);

const form = useForm({
    email: props.email || '',
    code: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.update'), {
        onSuccess: () => {
            form.reset();
            emit('switchToLogin');
        }
    });
};
</script>

<template>
    <div class="p-6">
        <div class="text-center mb-6">
            <div class="inline-flex bg-success/10 p-3 rounded-full mb-4 text-success">
                <KeyRound :size="32" />
            </div>
            <h2 class="text-xl md:text-2xl font-display font-semibold text-foreground">
                Nueva Contraseña
            </h2>
            <p class="text-sm text-muted-foreground mt-2">
                Revisa tu correo <strong class="text-primary">{{ form.email }}</strong> e ingresa el código.
            </p>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <!-- Email (readonly) -->
            <div class="form-group opacity-70">
                <label class="form-label">Correo Electrónico</label>
                <input v-model="form.email" 
                       readonly 
                       class="bg-muted cursor-not-allowed">
            </div>

            <!-- Código de verificación -->
            <div class="form-group">
                <label class="form-label">Código de Verificación</label>
                <input v-model="form.code" 
                       type="text" 
                       maxlength="6" 
                       class="text-center text-2xl tracking-[0.5em] font-mono font-bold placeholder:tracking-normal placeholder:text-base placeholder:text-muted-foreground"
                       placeholder="000000"
                       :class="{'border-error': form.errors.code}">
                <p v-if="form.errors.code" class="form-error">{{ form.errors.code }}</p>
            </div>

            <!-- Nueva contraseña -->
            <div class="grid grid-cols-1 gap-4">
                <div class="form-group">
                    <label class="form-label">Nueva Contraseña</label>
                    <input v-model="form.password" 
                           type="password"
                           :class="{'border-error': form.errors.password}">
                    <p v-if="form.errors.password" class="form-error">{{ form.errors.password }}</p>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Confirmar Contraseña</label>
                    <input v-model="form.password_confirmation" 
                           type="password">
                </div>
            </div>

            <!-- Botón de envío -->
            <div class="pt-2">
                <button type="submit" 
                        :disabled="form.processing"
                        class="btn btn-success w-full flex justify-center items-center gap-2">
                    <Loader2 v-if="form.processing" class="animate-spin" :size="20" />
                    <template v-else>
                        <span>Cambiar Contraseña</span>
                        <CheckCircle :size="18" />
                    </template>
                </button>
            </div>
        </form>
    </div>
</template>