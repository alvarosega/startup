<script setup>
import { useForm, Link, Head } from '@inertiajs/vue3';
import { KeyRound, CheckCircle, ShieldCheck, Hash, XCircle } from 'lucide-vue-next';
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
    <Head title="Nueva Contraseña - Electric Luxury"/>
    
    <div class="min-h-screen flex items-center justify-center bg-background relative overflow-hidden p-4">
        
        <div class="fixed inset-0 z-0">
            <div class="absolute inset-0 bg-gradient-to-br from-background via-background to-primary/5" />
            <div class="absolute inset-0 bg-dots opacity-30 [mask-image:radial-gradient(ellipse_at_center,black_30%,transparent_70%)]" />
        </div>

        <div class="w-full max-w-md relative z-10 animate-in fade-in zoom-in-95 duration-500">
            <div class="card bg-card/80 backdrop-blur-xl border-primary/10 shadow-2xl overflow-hidden">
                <div class="p-8 md:p-10">
                    
                    <div class="text-center mb-8">
                        <div class="w-20 h-20 rounded-3xl bg-gradient-to-tr from-primary/20 to-primary/5 flex items-center justify-center mx-auto mb-6 border border-primary/20 shadow-[0_0_20px_rgba(0,240,255,0.1)] text-primary">
                            <ShieldCheck :size="40" class="animate-pulse" />
                        </div>
                        <h2 class="text-3xl font-display font-black text-navy tracking-tighter leading-none">Nueva Contraseña</h2>
                        <div class="mt-4 inline-flex items-center gap-2 px-4 py-2 rounded-full bg-muted/60 border border-border text-[10px] uppercase font-black tracking-wider text-muted-foreground shadow-inner">
                            <span class="opacity-60">Código para:</span>
                            <span class="text-navy">{{ props.email }}</span>
                        </div>
                    </div>

                    <form @submit.prevent="submit" class="space-y-6">
                        
                        <div class="space-y-3">
                            <label class="block w-full text-[10px] font-black uppercase tracking-[0.3em] text-primary text-center">
                                <Hash :size="12" class="inline mb-0.5"/> Validación de 6 Dígitos
                            </label>
                            <input 
                                v-model="form.code" 
                                type="text" 
                                maxlength="6" 
                                class="w-full text-center text-4xl font-mono font-black tracking-[0.6em] h-20 bg-background/50 border-2 border-border rounded-2xl focus:outline-none focus:border-primary focus:ring-0 focus:shadow-[0_0_30px_rgba(0,240,255,0.2)] transition-all placeholder:text-muted/10 text-navy"
                                placeholder="000000"
                                :class="{'border-error shadow-[0_0_20px_rgba(255,0,100,0.2)]': form.errors.code}"
                                autofocus
                            />
                            <p v-if="form.errors.code" class="text-center text-[10px] text-error font-black uppercase tracking-widest animate-bounce mt-2">{{ form.errors.code }}</p>
                        </div>

                        <div class="space-y-4 pt-4 border-t border-border/50">
                            <BaseInput 
                                v-model="form.password" 
                                type="password" 
                                label="Nueva Contraseña" 
                                :icon="KeyRound" 
                                :error="form.errors.password" 
                                class="h-[52px]"
                            />
                            <BaseInput 
                                v-model="form.password_confirmation" 
                                type="password" 
                                label="Confirmar Nueva Clave" 
                                :icon="KeyRound" 
                                class="h-[52px]"
                            />
                        </div>

                        <button 
                            type="submit" 
                            :disabled="form.processing" 
                            class="btn btn-primary w-full h-14 shadow-lg shadow-primary/25 group predictive-aura transition-all active:scale-95 mt-4"
                        >
                            <span v-if="form.processing" class="spinner spinner-md border-navy/30 border-t-navy"></span>
                            <span v-else class="flex items-center justify-center gap-3 font-black text-lg uppercase tracking-tighter">
                                Actualizar Acceso <CheckCircle :size="20" />
                            </span>
                        </button>
                    </form>

                    <div class="mt-8 text-center">
                        <Link :href="route('login')" class="text-[10px] font-black text-muted-foreground hover:text-error transition-all uppercase tracking-widest flex items-center justify-center gap-2 group">
                            <XCircle :size="14" class="group-hover:rotate-90 transition-transform" /> Cancelar Operación
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>