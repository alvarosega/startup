<script setup>
import { useForm } from '@inertiajs/vue3';
import { Mail, ArrowLeft, Send, AlertCircle } from 'lucide-vue-next';
import BaseInput from '@/Components/Base/BaseInput.vue';

const emit = defineEmits(['close', 'switchToLogin', 'switchToReset']);

const form = useForm({ email: '' });

const submit = () => {
    form.post(route('password.email'), {
        preserveScroll: true,
        onSuccess: () => {
            // Transición suave hacia la vista de ingresar código
            emit('switchToReset', form.email);
        },
    });
};
</script>

<template>
    <div class="p-6 md:p-8 h-full flex flex-col justify-center animate-in fade-in slide-in-from-bottom-4 duration-500">
        
        <div class="text-center mb-8">
            <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-primary/20 to-secondary/20 flex items-center justify-center mx-auto mb-4 shadow-sm animate-scale-in">
                <Mail :size="32" class="text-primary drop-shadow-sm" />
            </div>
            <h2 class="text-2xl font-display font-black text-foreground tracking-tight">
                Recuperar Acceso
            </h2>
            <p class="text-sm text-muted-foreground mt-2 max-w-[280px] mx-auto leading-relaxed">
                Ingresa tu correo asociado y te enviaremos un código de seguridad de 6 dígitos.
            </p>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <div class="space-y-1">
                <BaseInput 
                    v-model="form.email" 
                    type="email" 
                    label="Correo Electrónico" 
                    placeholder="ejemplo@correo.com" 
                    :icon="Mail"
                    :error="form.errors.email"
                    autofocus
                    required
                />
            </div>

            <button type="submit" 
                    :disabled="form.processing"
                    class="btn btn-primary w-full py-3 shadow-lg shadow-primary/25 hover:shadow-primary/40 transition-all active:scale-[0.98] group">
                <span v-if="form.processing" class="spinner spinner-sm"></span>
                <span v-else class="flex items-center justify-center gap-2 font-bold text-base">
                    Enviar Código <Send :size="18" class="group-hover:translate-x-1 group-hover:-translate-y-1 transition-transform" />
                </span>
            </button>

            <div v-if="form.status" class="p-3 rounded-xl bg-success/10 border border-success/20 text-success text-xs font-bold text-center">
                {{ form.status }}
            </div>
        </form>

        <div class="mt-8 pt-6 border-t border-border text-center">
            <button @click="$emit('switchToLogin')" 
                    class="text-sm text-muted-foreground hover:text-foreground font-medium flex items-center justify-center gap-2 mx-auto transition-colors group px-4 py-2 rounded-lg hover:bg-muted/50">
                <ArrowLeft :size="16" class="group-hover:-translate-x-1 transition-transform" /> Volver al Login
            </button>
        </div>
    </div>
</template>