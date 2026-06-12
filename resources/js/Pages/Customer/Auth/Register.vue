<script setup>
import { ref, computed, onMounted } from 'vue'; 
import { useForm, Link, Head } from '@inertiajs/vue3';
import { VueTelInput } from 'vue-tel-input';
import 'vue-tel-input/vue-tel-input.css';
import axios from 'axios';

import LocationWorkflow from '@/Components/Customer/Maps/LocationWorkflow.vue'; 
import { Smartphone, Lock, Mail, User, ArrowRight, CheckCircle } from 'lucide-vue-next';

const props = defineProps({ activeBranches: Array });

const currentStep = ref(1);
const steps = [
    { id: 1, title: 'CUENTA', code: 'ACCOUNT' },
    { id: 2, title: 'LOGÍSTICA', code: 'LOGISTICS' },
    { id: 3, title: 'PERFIL', code: 'VISUAL' },
];

const validatingStep1 = ref(false);
const isPhoneValid = ref(false);
const registrationId = ref(null);
const formattedPhone = ref(''); 

const form = useForm({
    first_name: '', last_name: '', phone: '', email: '', 
    password: '', password_confirmation: '', country_code: 'BO',
    alias: '', address: '', details: '', latitude: null, longitude: null,
    branch_id: null, avatar_type: 'icon', avatar_source: null, 
    avatar_file: null, guest_client_uuid: null
});

onMounted(() => {
    registrationId.value = crypto.randomUUID();
    form.guest_client_uuid = localStorage.getItem('guest_client_uuid') || localStorage.getItem('guest_id');
});

const onInput = (phone, obj) => { 
    isPhoneValid.value = obj?.valid ?? false; 
    if (obj?.country?.iso2) form.country_code = obj.country.iso2.toUpperCase();
    formattedPhone.value = obj?.number || phone; 
};

const handleStep1Validation = async () => {
    form.clearErrors();

    if (!isPhoneValid.value && form.phone.length > 0) {
        form.setError('phone', 'El formato del número de teléfono no es válido.');
        return;
    }

    validatingStep1.value = true;
    
    try {
        await axios.post(route('customer.register.validate-step-1'), {
            first_name: form.first_name, 
            last_name: form.last_name,
            phone: formattedPhone.value, 
            country_code: form.country_code,
            email: form.email, 
            password: form.password,
            password_confirmation: form.password_confirmation
        });
        currentStep.value = 2; 
    } catch (error) {
        if (error.response?.status === 422) {
            const errs = error.response.data.errors;
            for (const f in errs) { 
                form.setError(f, errs[f][0]); 
            }
        }
    } finally { 
        validatingStep1.value = false; 
    }
};

const submit = () => {
    if (form.processing || !form.avatar_source) return;
    
    form.transform((data) => ({
        ...data,
        phone: formattedPhone.value
    })).post(route('customer.register'), {
        preserveScroll: true,
        forceFormData: true, 
        headers: { 'X-Idempotency-Key': registrationId.value }
    });
};

const containerTransform = computed(() => {
    const offset = (currentStep.value - 1) * -(100 / steps.length);
    return `translateX(${offset}%)`;
});
</script>

<template>
    <Head title="Registro de Socio" />

    <div class="du-cyber-canvas bg-f1-lines-random h-[100svh] flex flex-col overflow-hidden relative">
        
        <div class="absolute top-0 left-0 w-full grid grid-cols-3 gap-1 px-1 pt-1 z-[100]">
            <div v-for="step in steps" :key="step.id" class="h-1.5 transition-colors duration-300"
                 :class="currentStep === step.id ? 'bg-primary shadow-[0_0_10px_rgba(var(--primary-rgb),0.8)]' : (currentStep > step.id ? 'bg-white' : 'bg-[#32323b]')">
            </div>
        </div>

        <main class="flex-1 flex w-[300%] h-full transition-transform duration-500 ease-f1 relative z-10"
              :style="{ transform: containerTransform }">
            
            <div class="w-1/3 h-full shrink-0 flex flex-col items-center justify-center p-4 lg:p-8">
                <div class="w-full max-w-xl bg-background dark:bg-[#15151e] border border-[#32323b] rounded-xl p-6 md:p-8 shadow-2xl">
                    <div class="text-center mb-6">
                        <h1 class="text-3xl font-black tracking-tighter uppercase italic text-foreground">Registro</h1>
                        <p class="text-primary text-[9px] font-black uppercase tracking-[0.3em] mt-1">[ ID_AUTH_SECTOR_01 ]</p>
                    </div>

                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-3">
                            <div class="space-y-1">
                                <label class="text-[9px] font-black uppercase tracking-widest text-neutral-500 ml-0.5">Nombre</label>
                                <input v-model="form.first_name" type="text" class="w-full h-11 bg-transparent border border-[#32323b] rounded-xl px-4 text-sm font-bold text-foreground focus:border-primary/50 transition-all outline-none" :class="{ 'border-red-500': form.errors.first_name }" placeholder="Juan">
                                <p v-if="form.errors.first_name" class="text-[8px] text-red-500 font-bold uppercase tracking-wider mt-0.5">{{ form.errors.first_name }}</p>
                            </div>
                            <div class="space-y-1">
                                <label class="text-[9px] font-black uppercase tracking-widest text-neutral-500 ml-0.5">Apellido</label>
                                <input v-model="form.last_name" type="text" class="w-full h-11 bg-transparent border border-[#32323b] rounded-xl px-4 text-sm font-bold text-foreground focus:border-primary/50 transition-all outline-none" :class="{ 'border-red-500': form.errors.last_name }" placeholder="Perez">
                                <p v-if="form.errors.last_name" class="text-[8px] text-red-500 font-bold uppercase tracking-wider mt-0.5">{{ form.errors.last_name }}</p>
                            </div>
                        </div>

                        <div class="space-y-1">
                            <label class="text-[9px] font-black uppercase tracking-widest text-neutral-500 ml-0.5">Celular</label>
                            <vue-tel-input v-model="form.phone" @on-input="onInput" mode="international" :defaultCountry="'BO'" class="hardware-tel-input" :class="{ '!border-red-500': form.errors.phone }" />
                            <p v-if="form.errors.phone" class="text-[8px] text-red-500 font-bold uppercase tracking-wider mt-0.5">{{ form.errors.phone }}</p>
                        </div>

                        <div class="space-y-1">
                            <label class="text-[9px] font-black uppercase tracking-widest text-neutral-500 ml-0.5">Email Técnico</label>
                            <input v-model="form.email" type="email" class="w-full h-11 bg-transparent border border-[#32323b] rounded-xl px-4 text-sm font-bold text-foreground focus:border-primary/50 transition-all outline-none" :class="{ 'border-red-500': form.errors.email }" placeholder="piloto@du.com">
                            <p v-if="form.errors.email" class="text-[8px] text-red-500 font-bold uppercase tracking-wider mt-0.5">{{ form.errors.email }}</p>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-3">
                            <div class="space-y-1">
                                <label class="text-[9px] font-black uppercase tracking-widest text-neutral-500 ml-0.5">Password</label>
                                <input v-model="form.password" type="password" class="w-full h-11 bg-transparent border border-[#32323b] rounded-xl px-4 text-sm font-mono font-bold text-foreground focus:border-primary/50 transition-all outline-none" :class="{ 'border-red-500': form.errors.password }" placeholder="••••••••">
                                <p v-if="form.errors.password" class="text-[8px] text-red-500 font-bold uppercase tracking-wider mt-0.5">{{ form.errors.password }}</p>
                            </div>
                            <div class="space-y-1">
                                <label class="text-[9px] font-black uppercase tracking-widest text-neutral-500 ml-0.5">Confirmar</label>
                                <input v-model="form.password_confirmation" type="password" class="w-full h-11 bg-transparent border border-[#32323b] rounded-xl px-4 text-sm font-mono font-bold text-foreground focus:border-primary/50 transition-all outline-none" :class="{ 'border-red-500': form.errors.password_confirmation }" placeholder="••••••••">
                            </div>
                        </div>

                        <div class="pt-4 flex gap-3">
                            <Link :href="route('customer.login')" class="h-11 px-6 rounded-xl bg-transparent border border-[#32323b] text-foreground font-black uppercase text-[10px] tracking-widest flex items-center hover:bg-foreground/5 transition-colors">Login</Link>
                            <button @click="handleStep1Validation" :disabled="validatingStep1" class="flex-1 h-11 btn-primary flex items-center justify-center gap-2 disabled:opacity-30">
                                <span v-if="validatingStep1" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                                <template v-else>Siguiente <ArrowRight :size="14" stroke-width="3" /></template>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="w-1/3 h-full shrink-0 relative">
                <LocationWorkflow 
                    :form="form" 
                    :activeBranches="activeBranches" 
                    :isActive="currentStep === 2"
                    submitLabel="Confirmar Sector" 
                    @next="currentStep = 3" 
                    @back="currentStep = 1" 
                />
            </div>

            <div class="w-1/3 h-full shrink-0 flex flex-col items-center justify-center p-4">
                <div class="w-full max-w-xl bg-background dark:bg-[#15151e] border border-[#32323b] rounded-xl p-6 md:p-8 shadow-2xl flex flex-col items-center">
                    <div class="text-center mb-6">
                        <h1 class="text-3xl font-black tracking-tighter uppercase italic text-foreground">Identidad</h1>
                        <p class="text-primary text-[9px] font-black uppercase tracking-[0.3em] mt-1">[ ID_VISUAL_SECTOR_03 ]</p>
                    </div>

                    <div v-if="form.hasErrors" class="mb-4 p-2.5 w-full rounded-xl bg-red-500/10 border border-red-500/20 text-center">
                        <p class="text-[8px] text-red-500 font-black uppercase tracking-wider">Error de validación global</p>
                    </div>

                    <div class="relative mb-8">
                        <div class="w-32 h-32 rounded-xl border border-[#32323b] bg-transparent overflow-hidden flex items-center justify-center p-1">
                            <img v-if="form.avatar_source" :src="`/assets/avatars/${form.avatar_source}`" class="w-full h-full object-cover rounded-lg" />
                            <User v-else :size="40" class="text-neutral-500" />
                        </div>
                    </div>

                    <div class="grid grid-cols-4 gap-2 w-full mb-8">
                        <button v-for="i in 8" :key="i" type="button" @click="form.avatar_source = `avatar_${i}.png`" 
                                class="aspect-square rounded-lg border flex items-center justify-center p-1 transition-all active:scale-95 shadow-sm outline-none" 
                                :class="form.avatar_source === `avatar_${i}.png` ? 'border-primary bg-primary/10 shadow-f1-glow' : 'border-[#32323b] bg-transparent opacity-40 hover:opacity-100'">
                            <img :src="`/assets/avatars/avatar_${i}.png`" class="w-full h-full object-contain" />
                        </button>
                    </div>

                    <div class="flex gap-3 w-full">
                        <button @click="currentStep = 2" class="h-11 px-6 rounded-xl bg-transparent border border-[#32323b] text-foreground font-black uppercase text-[10px] tracking-widest hover:bg-foreground/5 transition-colors">Atrás</button>
                        <button @click="submit" :disabled="form.processing || !form.avatar_source" class="flex-1 h-11 btn-primary flex items-center justify-center gap-2 disabled:opacity-30">
                            <span v-if="form.processing" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                            <template v-else>Finalizar <CheckCircle :size="16" stroke-width="3" /></template>
                        </button>
                    </div>
                </div>
            </div>

        </main>
    </div>
</template>

<style scoped>
.font-mono { font-family: 'JetBrains Mono', monospace; }
.ease-f1 { transition-timing-function: cubic-bezier(0.16, 1, 0.3, 1); }

:deep(.hardware-tel-input) {
    background: transparent !important;
    border: 1px solid #32323b !important;
    border-radius: 0.75rem !important;
    height: 44px !important;
}
:deep(.hardware-tel-input .vti__input) {
    background: transparent !important;
    color: currentColor !important;
    font-weight: 900 !important;
    font-size: 13px !important;
}
:deep(.hardware-tel-input .vti__dropdown-list) {
    background-color: #15151e !important;
    border: 1px solid #32323b !important;
    border-radius: 0.75rem !important;
    color: #ffffff !important;
}
.du-cyber-canvas:not(.dark) :deep(.hardware-tel-input .vti__dropdown-list) {
    background-color: #ffffff !important;
    color: #15151e !important;
}
</style>