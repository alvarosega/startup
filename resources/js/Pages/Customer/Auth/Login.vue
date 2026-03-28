<script setup>
import { useForm, Link, Head } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import { VueTelInput } from 'vue-tel-input';
import 'vue-tel-input/vue-tel-input.css';
import BaseInput from '@/Components/Base/BaseInput.vue'; 
import { Lock, Smartphone, ArrowRight, Truck, UserCircle } from 'lucide-vue-next';


const isPhoneValid = ref(false);

const form = useForm({
    phone: '',
    password: '',
    remember: false,
    guest_client_uuid: null // Se inicializa nulo
});

onMounted(() => {
    // Captura de identidad persistente antes del montaje
    form.guest_client_uuid = localStorage.getItem('guest_client_uuid') || localStorage.getItem('guest_id');
});

const onInput = (phone, phoneObject) => {
    isPhoneValid.value = phoneObject?.valid || false;
    if (phoneObject?.number) {
        form.phone = phoneObject.number;
    }
};

const canSubmit = computed(() => {
    return isPhoneValid.value && form.password.length > 0 && !form.processing;
});

const submit = () => {
    if (!canSubmit.value) return;
    form.clearErrors();

    // Sincronización final de Guest UUID justo antes del despacho
    form.transform((data) => ({
        ...data,
        guest_client_uuid: localStorage.getItem('guest_client_uuid')
    })).post(route('customer.login'), { // CORRECCIÓN: Prefijo de silo
        preserveScroll: true,
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Acceso de Socio" />

        <div class="flex-1 flex items-center justify-center p-4 min-h-[calc(100svh-144px)]">
            <div class="w-full max-w-md py-6 animate-in fade-in zoom-in-95 duration-500">
                
                <div class="bg-surface/20 backdrop-blur-3xl border border-white/10 rounded-[40px] shadow-2xl overflow-hidden flex flex-col">
                    
                    <div class="p-8 md:p-10">
                        <div class="text-center mb-10">
                            <div class="w-20 h-20 rounded-3xl border-4 border-primary/20 flex items-center justify-center mx-auto mb-6">
                                <UserCircle :size="36" class="text-primary" />
                            </div>
                            <h2 class="text-3xl font-black text-foreground tracking-tighter uppercase italic">Acceso</h2>
                            <p class="text-[10px] uppercase font-black tracking-[0.2em] text-foreground/40 mt-3">Identidad de Cliente</p>
                        </div>

                        <form @submit.prevent="submit" class="space-y-6">
                            <div class="space-y-2">
                                <label class="text-[10px] font-black uppercase tracking-widest text-foreground/60 ml-1 flex items-center gap-2">
                                    <Smartphone :size="12" /> Celular
                                </label>
                                <vue-tel-input 
                                    v-model="form.phone" 
                                    @on-input="onInput"
                                    mode="international"
                                    :preferredCountries="['BO']" 
                                    :defaultCountry="'BO'"
                                    class="custom-tel-input-dark"
                                    :class="{ 'border-red-500': form.errors.phone }"
                                />
                                <p v-if="form.errors.phone" class="text-[9px] text-red-500 font-bold uppercase ml-1">{{ form.errors.phone }}</p>
                            </div>

                            <div class="space-y-2">
                                <div class="flex justify-between items-center ml-1">
                                    <label class="text-[10px] font-black uppercase tracking-widest text-foreground/60 flex items-center gap-2">
                                        <Lock :size="12" /> Contraseña
                                    </label>
                                    <Link :href="route('customer.password.request')" class="text-[9px] font-black text-primary hover:underline uppercase italic">¿Olvidada?</Link>
                                </div>
                                <BaseInput 
                                    v-model="form.password" 
                                    type="password" 
                                    placeholder="••••••••" 
                                    class="!bg-foreground/5 !border-white/5 focus:!border-primary/50 !rounded-2xl h-[56px] !text-foreground font-mono font-bold shadow-inner"
                                    :error="form.errors.password" 
                                />
                            </div>

                            <div class="flex items-center ml-1">
                                <BaseCheckbox v-model="form.remember" label="Mantener Sesión" class="font-black text-[10px] text-foreground/60 uppercase" />
                            </div>

                            <button 
                                type="submit" 
                                :disabled="!canSubmit" 
                                class="w-full h-14 rounded-2xl font-black text-xs uppercase tracking-widest transition-all duration-300 flex items-center justify-center gap-3 mt-4"
                                :class="canSubmit ? 'bg-primary text-white shadow-xl shadow-primary/20 active:scale-95' : 'bg-white/5 text-white/20 cursor-not-allowed'"
                            >
                                <span v-if="form.processing" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                                <template v-else>Entrar <ArrowRight :size="16" /></template>
                            </button>
                        </form>

                        <div class="mt-10 text-center">
                            <p class="text-[10px] text-foreground/40 font-black uppercase tracking-widest">
                                ¿Sin cuenta? 
                                <Link :href="route('customer.register')" class="text-primary hover:underline ml-1">Crear Registro</Link>
                            </p>
                        </div>
                    </div>

                    <div class="bg-foreground/5 border-t border-white/5 p-6">
                        <Link :href="route('driver.login')" class="flex items-center justify-between group p-2 rounded-2xl hover:bg-foreground/5 transition-colors">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-xl bg-white/5 border border-white/10 text-foreground/40 flex items-center justify-center">
                                    <Truck :size="18" />
                                </div>
                                <div class="text-left">
                                    <p class="text-[10px] font-black uppercase text-foreground/60">Silo Logístico</p>
                                    <p class="text-[8px] text-foreground/30 font-bold uppercase tracking-widest">Panel Conductor</p>
                                </div>
                            </div>
                            <ArrowRight :size="14" class="text-foreground/20 group-hover:text-primary transition-colors" />
                        </Link>
                    </div>

                </div>
            </div>
        </div>
</template>

<style>
/* Estilos tácticos para el input de teléfono */
.custom-tel-input-dark {
    background: rgba(var(--foreground), 0.05) !important;
    border: 1px solid rgba(255, 255, 255, 0.1) !important;
    border-radius: 1rem !important;
    height: 56px !important;
}
.custom-tel-input-dark .vti__input {
    background: transparent !important;
    color: currentColor !important;
    font-weight: 800 !important;
    font-size: 14px !important;
}
</style>