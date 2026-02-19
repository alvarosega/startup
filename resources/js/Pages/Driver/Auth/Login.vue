<script setup>
import { useForm, Link, Head } from '@inertiajs/vue3';
import { VueTelInput } from 'vue-tel-input';
import 'vue-tel-input/vue-tel-input.css';
import BaseCheckbox from '@/Components/Base/BaseCheckbox.vue'; 
import BaseInput from '@/Components/Base/BaseInput.vue'; 
import { LogIn, Lock, Smartphone, UserPlus, ArrowRight, Truck, Info } from 'lucide-vue-next';

const form = useForm({
    phone: '',
    country_code: 'BO',
    password: '',
    remember: false,
});

const onInput = (phone, phoneObject) => {
    if(obj?.number) {
        form.phone = obj.number; // Guarda el número completo: +5178710820
        form.country_code = obj.country?.iso2 || 'BO'; // Guarda el ISO: PE, BO, etc.
    }
};

const submit = () => {
    form.post(route('driver.login'), {
        preserveScroll: true,
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Panel de Conductor - BoliviaLogistics" />

    <div class="min-h-screen flex items-center justify-center bg-slate-950 p-4">
        <div class="w-full max-w-md bg-white rounded-3xl shadow-2xl overflow-hidden relative">
            
            <div class="bg-amber-500 px-6 py-2 flex items-center justify-center gap-2">
                <Truck :size="16" class="text-white" />
                <span class="text-[10px] font-black text-white uppercase tracking-widest">Acceso Logístico</span>
            </div>

            <div class="p-8">
                <div class="text-center mb-8">
                    <h2 class="text-3xl font-display font-black text-slate-900 tracking-tighter italic uppercase">
                        Bienvenido <span class="text-amber-500">Driver</span>
                    </h2>
                    <p class="text-sm text-slate-500 mt-1">Ingresa a tu panel de control para gestionar pedidos.</p>
                </div>

                <form @submit.prevent="submit" class="space-y-5">
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest ml-1">
                            Número de Celular
                        </label>
                        <vue-tel-input 
                            v-model="form.phone" 
                            @on-input="onInput"
                            mode="international"
                            :preferredCountries="['BO']" 
                            :defaultCountry="'BO'"
                            class="custom-tel-input"
                            :class="{ '!border-red-500': form.errors.phone }"
                        />
                        <p v-if="form.errors.phone" class="text-xs text-red-500 font-bold mt-1 ml-1">{{ form.errors.phone }}</p>
                    </div>

                    <div class="space-y-1">
                        <div class="flex justify-between items-center mb-1 ml-1">
                            <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest">
                                Contraseña
                            </label>
                            <Link :href="route('driver.password.request')" class="text-xs font-bold text-amber-600 hover:text-amber-700 transition-colors">
                                ¿Recuperar clave?
                            </Link>
                        </div>
                        <BaseInput v-model="form.password" type="password" placeholder="••••••••" :error="form.errors.password" />
                    </div>

                    <div class="flex items-center ml-1">
                        <BaseCheckbox v-model="form.remember" label="Mantener sesión iniciada" />
                    </div>

                    <button type="submit" :disabled="form.processing" class="btn bg-slate-900 hover:bg-slate-800 text-white w-full py-4 shadow-xl shadow-slate-900/20 active:scale-[0.98] group">
                        <span v-if="form.processing" class="spinner border-white spinner-sm"></span>
                        <span v-else class="flex items-center justify-center gap-2 font-black uppercase tracking-widest text-sm">
                            Iniciar Turno <ArrowRight :size="18" class="group-hover:translate-x-1 transition-transform" />
                        </span>
                    </button>
                </form>

                <div class="mt-8 pt-6 border-t border-slate-100 text-center">
                    <p class="text-sm text-slate-500">
                        ¿Quieres conducir con nosotros? 
                        <Link :href="route('driver.register')" class="font-black text-amber-600 hover:underline ml-1 inline-flex items-center gap-1">
                            Regístrate aquí <UserPlus :size="14" />
                        </Link>
                    </p>
                </div>

                <div class="mt-6 p-4 bg-slate-50 rounded-2xl flex gap-3 border border-slate-100">
                    <Info :size="20" class="text-slate-400 shrink-0" />
                    <p class="text-[10px] text-slate-400 leading-relaxed italic">
                        Solo conductores autorizados tienen acceso a este panel. El uso indebido será reportado a las autoridades locales.
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.custom-tel-input { 
    @apply w-full rounded-xl border-slate-200 bg-white text-sm h-[48px] border transition-all;
}
.custom-tel-input:focus-within { @apply border-amber-500 ring-4 ring-amber-500/10; }
:deep(.vti__dropdown) { border-radius: 0.75rem 0 0 0.75rem !important; }
:deep(.vti__input) { border-radius: 0.75rem !important; }
</style>