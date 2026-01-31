<script setup>
import { useForm } from '@inertiajs/vue3';
import { KeyRound, CheckCircle, Loader2, ArrowLeft, ShieldCheck } from 'lucide-vue-next';
import BaseInput from '@/Components/Base/BaseInput.vue';

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
        preserveScroll: true,
        preserveState: true, // <--- IMPORTANTE: Evita que el modal se cierre/reinicie
        onSuccess: () => {
            form.reset();
            // Esto le dice al padre (Layout o Login.vue) que cambie al formulario de Login
            emit('switchToLogin'); 
        },
        onError: (errors) => {
            console.error("Error al resetear:", errors);
        }
    });
};
</script>

<template>
    <div class="p-6 md:p-8 h-full flex flex-col justify-center animate-in slide-in-from-right-8 duration-500">
        
        <div class="text-center mb-6">
            <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-primary/20 to-accent/20 flex items-center justify-center mx-auto mb-4 shadow-sm text-primary animate-scale-in">
                <ShieldCheck :size="32" class="drop-shadow-sm" />
            </div>
            <h2 class="text-2xl font-display font-black text-foreground tracking-tight">
                Nueva Contraseña
            </h2>
            
            <div class="mt-3 inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-muted/40 border border-border text-xs text-muted-foreground">
                <span>Código enviado a:</span>
                <span class="font-bold text-foreground">{{ form.email }}</span>
            </div>
        </div>

        <form @submit.prevent="submit" class="space-y-5">
            <input type="hidden" v-model="form.email">

            <div class="space-y-2">
                <label class="form-label text-center block w-full text-[10px] font-black uppercase tracking-widest text-primary/80 flex justify-center items-center gap-1">
                    <Hash :size="12"/> Código de Verificación
                </label>
                <div class="relative group">
                    <input 
                        v-model="form.code" 
                        type="text" 
                        maxlength="6" 
                        class="w-full text-center text-3xl font-mono font-bold tracking-[0.5em] h-16 bg-background border-2 border-border rounded-2xl focus:outline-none focus:border-primary focus:ring-4 focus:ring-primary/10 transition-all placeholder:text-muted/20 placeholder:tracking-normal placeholder:text-sm text-foreground"
                        placeholder="000000"
                        :class="{'border-error ring-4 ring-error/10 focus:border-error focus:ring-error/10': form.errors.code}"
                        autofocus
                    />
                    <div class="absolute inset-0 rounded-2xl border-primary/0 pointer-events-none transition-all duration-300 group-focus-within:border-primary/0"></div>
                </div>
                <p v-if="form.errors.code" class="text-center text-xs text-error font-bold animate-pulse">
                    {{ form.errors.code }}
                </p>
            </div>

            <div class="flex items-center gap-4 py-2">
                <div class="h-px bg-border flex-1"></div>
                <span class="text-[10px] text-muted-foreground uppercase font-bold tracking-wider">Seguridad</span>
                <div class="h-px bg-border flex-1"></div>
            </div>

            <div class="space-y-4">
                <BaseInput 
                    v-model="form.password" 
                    type="password" 
                    label="Nueva Contraseña" 
                    placeholder="Mínimo 8 caracteres"
                    :icon="KeyRound"
                    :error="form.errors.password"
                />
                
                <BaseInput 
                    v-model="form.password_confirmation" 
                    type="password" 
                    label="Confirmar Contraseña"
                    placeholder="Repite la contraseña"
                    :icon="KeyRound"
                />
            </div>

            <div class="pt-2">
                <button type="submit" 
                        :disabled="form.processing"
                        class="btn btn-primary w-full py-3 shadow-lg shadow-primary/20 hover:shadow-primary/30 transition-all active:scale-[0.98]">
                    <span v-if="form.processing" class="spinner spinner-sm"></span>
                    <span v-else class="flex items-center justify-center gap-2 font-bold text-base">
                        <CheckCircle :size="20" /> Actualizar Contraseña
                    </span>
                </button>
            </div>
        </form>

        <div class="mt-6 text-center">
            <button @click="$emit('switchToLogin')" class="text-xs font-bold text-muted-foreground hover:text-error transition-colors underline decoration-dotted underline-offset-4">
                Cancelar operación
            </button>
        </div>
    </div>
</template>