<script setup>
import { useForm, Link, Head } from '@inertiajs/vue3';
import { Mail, ArrowLeft, Send, ShieldQuestion } from 'lucide-vue-next';
import BaseInput from '@/Components/Base/BaseInput.vue';

const form = useForm({ email: '' });

const submit = () => {
    form.post(route('password.email'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Recuperar Acceso - Electric Luxury" />

    <div class="min-h-screen flex items-center justify-center bg-background relative overflow-hidden p-4">
        
        <div class="fixed inset-0 z-0">
            <div class="absolute inset-0 bg-gradient-to-br from-background via-background to-primary/5" />
            <div class="absolute inset-0 bg-dots opacity-30 [mask-image:radial-gradient(ellipse_at_center,black_30%,transparent_70%)]" />
        </div>

        <div class="w-full max-w-md relative z-10 animate-in fade-in zoom-in-95 duration-500">
            <div class="card bg-card/80 backdrop-blur-xl border-primary/10 shadow-2xl overflow-hidden">
                <div class="p-8 md:p-10">
                    
                    <div class="text-center mb-8">
                        <div class="w-20 h-20 rounded-3xl bg-gradient-to-tr from-primary/20 to-primary/5 flex items-center justify-center mx-auto mb-6 border border-primary/20 shadow-[0_0_20px_rgba(0,240,255,0.1)]">
                            <ShieldQuestion :size="40" class="text-primary animate-neon-float" />
                        </div>
                        <h2 class="text-3xl font-display font-black text-navy tracking-tighter leading-none">
                            Recuperar Acceso
                        </h2>
                        <p class="text-sm text-muted-foreground mt-4 font-medium leading-relaxed">
                            Ingresa tu correo y te enviaremos un código de seguridad de 6 dígitos.
                        </p>
                    </div>

                    <form @submit.prevent="submit" class="space-y-8">
                        <BaseInput 
                            v-model="form.email" 
                            type="email" 
                            label="Correo de Recuperación" 
                            placeholder="ejemplo@correo.com" 
                            :icon="Mail"
                            :error="form.errors.email"
                            autofocus
                            required
                            class="h-[52px] focus:shadow-[0_0_20px_rgba(0,240,255,0.15)]"
                        />

                        <button 
                            type="submit" 
                            :disabled="form.processing" 
                            class="btn btn-primary w-full h-14 shadow-lg shadow-primary/25 group predictive-aura transition-all active:scale-95"
                        >
                            <span v-if="form.processing" class="spinner spinner-md border-navy/30 border-t-navy"></span>
                            <span v-else class="flex items-center justify-center gap-3 font-black text-lg uppercase tracking-tighter">
                                Enviar Código <Send :size="18" class="group-hover:translate-x-1 group-hover:-translate-y-1 transition-transform" />
                            </span>
                        </button>

                        <Transition name="fade">
                            <div v-if="$page.props.flash?.status" class="p-4 rounded-2xl bg-success/10 border border-success/20 text-success text-xs font-black text-center uppercase tracking-widest animate-pulse">
                                {{ $page.props.flash.status }}
                            </div>
                        </Transition>
                    </form>

                    <div class="mt-10 pt-8 border-t border-border/50 text-center">
                        <Link :href="route('login')" class="text-xs font-black text-muted-foreground hover:text-primary uppercase tracking-widest flex items-center justify-center gap-2 transition-all group">
                            <ArrowLeft :size="16" class="group-hover:-translate-x-2 transition-transform" /> Volver al Login
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>