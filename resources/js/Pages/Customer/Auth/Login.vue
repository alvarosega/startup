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
    <Head title="Ingresar - BoliviaLogistics" />

    <div class="min-h-screen flex items-center justify-center bg-slate-50 p-4">
        <div class="w-full max-w-md bg-white rounded-3xl shadow-xl border border-slate-100 overflow-hidden relative">
            <div class="p-6 md:p-8">
                <div class="text-center mb-8">
                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-primary/20 to-secondary/20 flex items-center justify-center mx-auto mb-4">
                        <LogIn :size="32" class="text-primary" />
                    </div>
                    <h2 class="text-2xl font-display font-black text-foreground tracking-tight">¡Hola de nuevo!</h2>
                    <p class="text-sm text-muted-foreground mt-1">Ingresa tus credenciales para continuar.</p>
                </div>

                <form @submit.prevent="submit" class="space-y-5">
                    <div class="space-y-1.5">
                        <label class="form-label flex items-center gap-1.5 ml-1 text-xs font-bold uppercase tracking-wider text-muted-foreground">
                            <Smartphone :size="14" /> Celular
                        </label>
                        <vue-tel-input 
                            v-model="form.phone" 
                            @on-input="onInput"
                            mode="international"
                            :preferredCountries="['BO']" 
                            :defaultCountry="'BO'"
                            class="custom-tel-input"
                            :class="{ '!border-error': form.errors.phone }"
                        />
                        <p v-if="form.errors.phone" class="text-xs text-error font-bold mt-1 ml-1">{{ form.errors.phone }}</p>
                    </div>

                    <div class="space-y-1">
                        <div class="flex justify-between items-center mb-1 ml-1">
                            <label class="form-label flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-muted-foreground">
                                <Lock :size="14" /> Contraseña
                            </label>
                            <Link :href="route('password.request')" class="text-xs font-bold text-primary hover:text-primary/80 transition-colors">
                                ¿Olvidaste tu clave?
                            </Link>
                        </div>
                        <BaseInput v-model="form.password" type="password" placeholder="••••••••" :error="form.errors.password" />
                    </div>

                    <div class="flex items-center ml-1">
                        <BaseCheckbox v-model="form.remember" label="Mantener sesión iniciada" />
                    </div>

                    <button type="submit" :disabled="form.processing" class="btn btn-primary w-full py-3 shadow-lg shadow-primary/25 active:scale-[0.98] group">
                        <span v-if="form.processing" class="spinner spinner-sm"></span>
                        <span v-else class="flex items-center justify-center gap-2 font-bold text-base">
                            Ingresar <ArrowRight :size="18" class="group-hover:translate-x-1 transition-transform" />
                        </span>
                    </button>
                </form>

                <div class="mt-8 pt-6 border-t border-border space-y-4">
                    <p class="text-sm text-muted-foreground text-center">
                        ¿Aún no tienes cuenta? 
                        <Link :href="route('register')" class="font-bold text-primary hover:underline ml-1 inline-flex items-center gap-1">
                            Regístrate gratis <UserPlus :size="14" />
                        </Link>
                    </p>

                    <div class="bg-muted/30 rounded-2xl p-4 border border-dashed border-border/60">
                        <div class="flex items-center justify-between gap-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-warning/10 text-warning flex items-center justify-center shrink-0">
                                    <Truck :size="20" />
                                </div>
                                <div class="text-left">
                                    <p class="text-[10px] font-black text-foreground uppercase tracking-tight leading-none">¿Eres Conductor?</p>
                                    <p class="text-[9px] text-muted-foreground leading-none mt-1">Panel logístico</p>
                                </div>
                            </div>
                            <Link :href="route('driver.login')" class="px-3 py-1.5 bg-warning text-warning-foreground text-[10px] font-black uppercase rounded-lg hover:scale-105 active:scale-95 transition-all">
                                Entrar
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.custom-tel-input { 
    @apply w-full rounded-xl border-input bg-background text-sm h-[46px] transition-all duration-200 border;
}
.custom-tel-input:focus-within { @apply border-primary ring-4 ring-primary/10; }
:deep(.vti__dropdown) { border-radius: 0.75rem 0 0 0.75rem !important; }
:deep(.vti__input) { border-radius: 0.75rem !important; }
</style>