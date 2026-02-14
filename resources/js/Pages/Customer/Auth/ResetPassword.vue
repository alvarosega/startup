<script setup>
import { useForm, Link, Head } from '@inertiajs/vue3';
import { KeyRound, CheckCircle, ShieldCheck, Hash } from 'lucide-vue-next';
import BaseInput from '@/Components/Base/BaseInput.vue';

const props = defineProps({ email: String });

const form = useForm({
    email: props.email || '',
    code: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <Head title="Nueva Contraseña" />
    <div class="min-h-screen flex items-center justify-center bg-slate-50 p-4">
        <div class="w-full max-w-md bg-white rounded-3xl shadow-xl border border-slate-100 p-6 md:p-8">
            <div class="text-center mb-6">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-primary/20 to-accent/20 flex items-center justify-center mx-auto mb-4 text-primary">
                    <ShieldCheck :size="32" />
                </div>
                <h2 class="text-2xl font-display font-black text-foreground tracking-tight">Nueva Contraseña</h2>
                <div class="mt-3 inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-muted/40 border border-border text-xs text-muted-foreground">
                    <span>Código enviado a:</span>
                    <span class="font-bold text-foreground">{{ props.email }}</span>
                </div>
            </div>

            <form @submit.prevent="submit" class="space-y-5">
                <div class="space-y-2">
                    <label class="form-label text-center block w-full text-[10px] font-black uppercase tracking-widest text-primary/80 flex justify-center items-center gap-1">
                        <Hash :size="12"/> Código de Verificación
                    </label>
                    <input 
                        v-model="form.code" 
                        type="text" 
                        maxlength="6" 
                        class="w-full text-center text-3xl font-mono font-bold tracking-[0.5em] h-16 bg-background border-2 border-border rounded-2xl focus:outline-none focus:border-primary focus:ring-4 focus:ring-primary/10 transition-all placeholder:text-muted/20"
                        placeholder="000000"
                        :class="{'border-error': form.errors.code}"
                        autofocus
                    />
                    <p v-if="form.errors.code" class="text-center text-xs text-error font-bold">{{ form.errors.code }}</p>
                </div>

                <div class="space-y-4">
                    <BaseInput v-model="form.password" type="password" label="Nueva Contraseña" :icon="KeyRound" :error="form.errors.password" />
                    <BaseInput v-model="form.password_confirmation" type="password" label="Confirmar Contraseña" :icon="KeyRound" />
                </div>

                <button type="submit" :disabled="form.processing" class="btn btn-primary w-full py-3 shadow-lg shadow-primary/20">
                    <span v-if="form.processing" class="spinner spinner-sm"></span>
                    <span v-else class="flex items-center justify-center gap-2 font-bold text-base">
                        <CheckCircle :size="20" /> Actualizar Contraseña
                    </span>
                </button>
            </form>

            <div class="mt-6 text-center">
                <Link :href="route('login')" class="text-xs font-bold text-muted-foreground hover:text-error transition-colors underline decoration-dotted underline-offset-4">
                    Cancelar operación
                </Link>
            </div>
        </div>
    </div>
</template>