<script setup>
import { useForm, Link, Head } from '@inertiajs/vue3';
import { VueTelInput } from 'vue-tel-input';
import 'vue-tel-input/vue-tel-input.css';
import BaseCheckbox from '@/Components/Base/BaseCheckbox.vue'; 
import BaseInput from '@/Components/Base/BaseInput.vue'; 
import { LogIn, Lock, Smartphone, UserPlus, ArrowRight, Truck } from 'lucide-vue-next';

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
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Ingresar - Electric Luxury Logistics" />

    <div class="min-h-screen flex items-center justify-center bg-background relative overflow-hidden p-4">
        
        <div class="fixed inset-0 z-0">
            <div class="absolute inset-0 bg-gradient-to-br from-background via-background to-primary/5" />
            <div class="absolute inset-0 bg-dots opacity-40 [mask-image:radial-gradient(ellipse_at_center,black_30%,transparent_70%)]" />
        </div>

        <div class="w-full max-w-md relative z-10 animate-in fade-in zoom-in-95 duration-500">
            
            <div class="card bg-card/80 backdrop-blur-xl border-primary/10 shadow-xl overflow-hidden">
                <div class="p-8 md:p-10">
                    
                    <div class="text-center mb-10">
                        <div class="w-20 h-20 rounded-3xl bg-gradient-to-tr from-primary/20 to-primary/5 flex items-center justify-center mx-auto mb-6 border border-primary/20 shadow-[0_0_20px_rgba(0,240,255,0.1)]">
                            <LogIn :size="40" class="text-primary animate-neon-float" />
                        </div>
                        <h2 class="text-3xl font-display font-black text-navy tracking-tighter leading-none">
                            ¡Hola de nuevo!
                        </h2>
                        <p class="text-sm text-muted-foreground mt-3 font-medium uppercase tracking-widest opacity-70">
                            Bóveda de Acceso
                        </p>
                    </div>

                    <form @submit.prevent="submit" class="space-y-6">
                        
                        <div class="space-y-2">
                            <label class="flex items-center gap-2 ml-1 text-[10px] font-black uppercase tracking-[0.2em] text-navy/60">
                                <Smartphone :size="14" class="text-primary" /> Celular de Contacto
                            </label>
                            <vue-tel-input 
                                v-model="form.phone" 
                                @on-input="onInput"
                                mode="international"
                                :preferredCountries="['BO']" 
                                :defaultCountry="'BO'"
                                class="custom-tel-input"
                                :class="{ 'border-error shadow-[0_0_15px_rgba(255,0,100,0.2)]': form.errors.phone }"
                            />
                            <p v-if="form.errors.phone" class="text-[10px] text-error font-black mt-1 ml-1 uppercase animate-pulse">
                                {{ form.errors.phone }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <div class="flex justify-between items-center mb-1 ml-1">
                                <label class="flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.2em] text-navy/60">
                                    <Lock :size="14" class="text-primary" /> Contraseña
                                </label>
                                <Link :href="route('password.request')" class="text-[10px] font-black text-primary hover:brightness-125 transition-all uppercase tracking-wider">
                                    ¿Olvidaste tu clave?
                                </Link>
                            </div>
                            <BaseInput 
                                v-model="form.password" 
                                type="password" 
                                placeholder="••••••••" 
                                class="h-[52px] text-lg font-mono focus:shadow-[0_0_20px_rgba(0,240,255,0.15)]"
                                :error="form.errors.password" 
                            />
                        </div>

                        <div class="flex items-center ml-1 py-2">
                            <BaseCheckbox v-model="form.remember" label="Mantener sesión activa" class="font-bold text-navy/70" />
                        </div>

                        <button 
                            type="submit" 
                            :disabled="form.processing" 
                            class="btn btn-primary w-full h-14 shadow-lg shadow-primary/30 active:scale-95 group predictive-aura overflow-hidden"
                        >
                            <span v-if="form.processing" class="spinner spinner-md border-navy/30 border-t-navy"></span>
                            <span v-else class="flex items-center justify-center gap-3 font-black text-lg uppercase tracking-tighter">
                                Ingresar al Sistema <ArrowRight :size="20" class="group-hover:translate-x-2 transition-transform duration-300" />
                            </span>
                        </button>
                    </form>

                    <div class="mt-10 pt-8 border-t border-border/50 space-y-6">
                        <p class="text-sm text-muted-foreground text-center font-medium">
                            ¿Aún no tienes cuenta? 
                            <Link :href="route('register')" class="font-black text-primary hover:underline underline-offset-4 ml-1">
                                Regístrate ahora
                            </Link>
                        </p>

                        <div class="bg-navy rounded-2xl p-5 border border-white/5 shadow-2xl relative group cursor-pointer overflow-hidden">
                            <div class="absolute inset-0 bg-gradient-to-tr from-warning/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity" />
                            <div class="flex items-center justify-between gap-4 relative z-10">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-xl bg-warning/20 text-warning flex items-center justify-center shrink-0 border border-warning/20">
                                        <Truck :size="24" />
                                    </div>
                                    <div class="text-left">
                                        <p class="text-[11px] font-black text-white uppercase tracking-widest leading-none">Acceso Profesional</p>
                                        <p class="text-[10px] text-white/50 leading-none mt-1.5 font-medium">Panel para Conductores</p>
                                    </div>
                                </div>
                                <Link :href="route('driver.login')" class="px-4 py-2 bg-warning text-navy text-[11px] font-black uppercase rounded-xl hover:scale-105 active:scale-95 transition-all shadow-lg shadow-warning/20">
                                    Entrar
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* PERSONALIZACIÓN QUIRÚRGICA DEL INPUT TELEFÓNICO */
.custom-tel-input { 
    @apply w-full rounded-xl border-input bg-background/50 text-base h-[52px] transition-all duration-300 border font-bold;
}

.custom-tel-input:focus-within { 
    @apply border-primary ring-0 shadow-[0_0_20px_rgba(0,240,255,0.15)];
}

:deep(.vti__dropdown) { 
    @apply bg-transparent px-3 hover:bg-muted transition-colors !important;
    border-radius: 0.75rem 0 0 0.75rem !important; 
}

:deep(.vti__input) { 
    @apply bg-transparent placeholder:text-muted-foreground/40 !important;
    border-radius: 0 0.75rem 0.75rem 0 !important; 
}

:deep(.vti__dropdown-list) {
    @apply bg-card border-border shadow-2xl rounded-xl z-50 !important;
}

:deep(.vti__dropdown-item.highlighted) {
    @apply bg-primary/10 text-navy !important;
}
</style>