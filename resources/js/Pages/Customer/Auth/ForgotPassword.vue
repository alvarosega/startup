<script setup>
import { useForm, Link, Head } from '@inertiajs/vue3';
import { Mail, ArrowLeft, Send } from 'lucide-vue-next';
import BaseInput from '@/Components/Base/BaseInput.vue';

const form = useForm({ email: '' });

const submit = () => {
    form.post(route('password.email'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Recuperar Acceso" />
    <div class="min-h-screen flex items-center justify-center bg-slate-50 p-4">
        <div class="w-full max-w-md bg-white rounded-3xl shadow-xl border border-slate-100 p-6 md:p-8">
            <div class="text-center mb-8">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-primary/20 to-secondary/20 flex items-center justify-center mx-auto mb-4">
                    <Mail :size="32" class="text-primary" />
                </div>
                <h2 class="text-2xl font-display font-black text-foreground tracking-tight">Recuperar Acceso</h2>
                <p class="text-sm text-muted-foreground mt-2 leading-relaxed">
                    Ingresa tu correo y te enviaremos un código de 6 dígitos.
                </p>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
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

                <button type="submit" :disabled="form.processing" class="btn btn-primary w-full py-3 shadow-lg shadow-primary/25 group">
                    <span v-if="form.processing" class="spinner spinner-sm"></span>
                    <span v-else class="flex items-center justify-center gap-2 font-bold text-base">
                        Enviar Código <Send :size="18" class="group-hover:translate-x-1 group-hover:-translate-y-1 transition-transform" />
                    </span>
                </button>

                <div v-if="$page.props.flash?.status" class="p-3 rounded-xl bg-green-500/10 border border-green-500/20 text-green-500 text-xs font-bold text-center">
                    {{ $page.props.flash.status }}
                </div>
            </form>

            <div class="mt-8 pt-6 border-t border-border text-center">
                <Link :href="route('login')" class="text-sm text-muted-foreground hover:text-foreground font-medium flex items-center justify-center gap-2 mx-auto transition-colors group">
                    <ArrowLeft :size="16" class="group-hover:-translate-x-1 transition-transform" /> Volver al Login
                </Link>
            </div>
        </div>
    </div>
</template>