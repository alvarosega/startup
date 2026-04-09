<script setup>
import { ref, computed, onMounted } from 'vue'; 
import { useForm, Link, Head } from '@inertiajs/vue3';
import { VueTelInput } from 'vue-tel-input';
import 'vue-tel-input/vue-tel-input.css';
import axios from 'axios';

import LocationWorkflow from '@/Components/Customer/Maps/LocationWorkflow.vue'; 

import { 
    Smartphone, Lock, Mail, User, ArrowRight, CheckCircle 
} from 'lucide-vue-next';

const props = defineProps({ activeBranches: Array });

const currentStep = ref(1);
const steps = [
    { id: 1, title: 'CUENTA' },
    { id: 2, title: 'LOGÍSTICA' },
    { id: 3, title: 'PERFIL' },
];

const validatingStep1 = ref(false);
const isPhoneValid = ref(false);
const registrationId = ref(null);

const form = useForm({
    first_name: '',
    last_name: '',  
    phone: '', 
    email: '', 
    password: '', 
    password_confirmation: '', 
    country_code: 'BO',
    alias: '', 
    address: '', 
    details: '', 
    latitude: null, 
    longitude: null,
    branch_id: null, 
    avatar_type: 'icon', 
    avatar_source: null, 
    avatar_file: null,
    guest_client_uuid: null
});

onMounted(() => {
    registrationId.value = crypto.randomUUID();
    form.guest_client_uuid = localStorage.getItem('guest_client_uuid') || localStorage.getItem('guest_id');
    
    if (form.errors.address || form.errors.latitude) {
        currentStep.value = 2;
    } else if (form.errors.avatar_source) {
        currentStep.value = 3;
    }
});

const onInput = (phone, obj) => { 
    isPhoneValid.value = obj?.valid ?? false; 
    if (obj?.country?.iso2) form.country_code = obj.country.iso2.toUpperCase();
    if (obj?.number) form.phone = obj.number; 
    form.clearErrors('phone');
};

const handleStep1Validation = async () => {
    if (!isPhoneValid.value && form.phone.length > 0) {
        form.setError('phone', 'Formato de número inválido.');
        return;
    }
    
    validatingStep1.value = true;
    form.clearErrors();
    
    try {
        await axios.post(route('customer.register.validate-step-1'), {
            first_name: form.first_name,
            last_name: form.last_name,
            phone: form.phone,
            country_code: form.country_code,
            email: form.email,
            password: form.password,
            password_confirmation: form.password_confirmation
        });
        currentStep.value = 2; 
    } catch (error) {
        if (error.response?.status === 422) {
            const validationErrors = error.response.data.errors;
            for (const field in validationErrors) {
                form.setError(field, validationErrors[field][0]);
            }
        }
    } finally {
        validatingStep1.value = false;
    }
};

const submit = () => {
    if (form.processing || !form.avatar_source) return;
    form.post(route('customer.register'), {
        preserveScroll: true,
        forceFormData: true, 
        headers: { 'X-Idempotency-Key': registrationId.value }
    });
};

const progressWidth = computed(() => (currentStep.value / steps.length) * 100);
</script>

<template>
    <Head title="Registro de Socio" />

    <div class="du-cyber-canvas min-h-[100svh] flex flex-col overflow-hidden selection:bg-primary/20 transition-colors duration-500">
        
        <div class="fixed top-0 left-0 w-full h-1.5 z-[1000] bg-background/40 backdrop-blur-md">
            <div class="h-full bg-primary shadow-[0_0_20px_rgba(var(--primary-rgb),0.8)] transition-all duration-1000 ease-ios"
                 :style="{ width: progressWidth + '%' }"></div>
        </div>

        <main class="flex-1 flex flex-col relative h-full w-full justify-center">
            
            <div v-if="currentStep === 1" class="flex-1 flex flex-col items-center justify-center p-4 lg:p-8 w-full animate-in fade-in zoom-in-95 duration-700">
                <div class="w-full max-w-xl glass-chassis rounded-[2.5rem] p-6 md:p-10 shadow-2xl relative z-10">
                    
                    <div class="text-center mb-8">
                        <h1 class="text-4xl md:text-5xl font-black tracking-tighter uppercase italic leading-none text-foreground">Registro</h1>
                    </div>

                    <div class="w-full space-y-5">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-1.5">
                                <label class="text-[10px] font-black uppercase tracking-[0.2em] text-foreground/60 ml-1">Nombres</label>
                                <input v-model="form.first_name" type="text" class="w-full h-[56px] bg-foreground/5 border border-border/30 rounded-2xl px-5 text-sm font-bold uppercase text-foreground focus:border-primary/50 focus:ring-1 focus:ring-primary/50 transition-all outline-none autofill-shield shadow-inner" placeholder="JUAN">
                                <p v-if="form.errors.first_name" class="text-[9px] text-destructive font-bold uppercase ml-1 tracking-tighter">{{ form.errors.first_name }}</p>
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-[10px] font-black uppercase tracking-[0.2em] text-foreground/60 ml-1">Apellidos</label>
                                <input v-model="form.last_name" type="text" class="w-full h-[56px] bg-foreground/5 border border-border/30 rounded-2xl px-5 text-sm font-bold uppercase text-foreground focus:border-primary/50 focus:ring-1 focus:ring-primary/50 transition-all outline-none autofill-shield shadow-inner" placeholder="PEREZ">
                                <p v-if="form.errors.last_name" class="text-[9px] text-destructive font-bold uppercase ml-1 tracking-tighter">{{ form.errors.last_name }}</p>
                            </div>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black uppercase tracking-[0.2em] text-foreground/60 ml-1 flex items-center gap-2">
                                <Smartphone :size="12" stroke-width="3" /> Terminal Móvil
                            </label>
                            <vue-tel-input 
                                v-model="form.phone" 
                                @on-input="onInput" 
                                mode="international" 
                                :defaultCountry="'BO'"
                                :preferredCountries="['BO']"
                                :dropdownOptions="{ showDialCodeInSelection: false, showFlags: true, showSearchBox: true }"
                                class="hardware-tel-input transition-colors duration-300" 
                                :class="{ '!border-destructive focus-within:!border-destructive': form.errors.phone }" 
                            />
                            <p v-if="form.errors.phone" class="text-[9px] text-destructive font-bold uppercase ml-1 tracking-tighter">{{ form.errors.phone }}</p>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black uppercase tracking-[0.2em] text-foreground/60 ml-1 flex items-center gap-2">
                                <Mail :size="12" stroke-width="3" /> Email
                            </label>
                            <input v-model="form.email" type="email" class="w-full h-[56px] bg-foreground/5 border border-border/30 rounded-2xl px-5 text-sm font-bold uppercase text-foreground focus:border-primary/50 focus:ring-1 focus:ring-primary/50 transition-all outline-none autofill-shield shadow-inner" placeholder="SISTEMA@DOMINIO.COM">
                            <p v-if="form.errors.email" class="text-[9px] text-destructive font-bold uppercase ml-1 tracking-tighter">{{ form.errors.email }}</p>
                        </div>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="space-y-1.5">
                                <label class="text-[10px] font-black uppercase tracking-[0.2em] text-foreground/60 ml-1 flex items-center gap-2">
                                    <Lock :size="12" stroke-width="3" /> Contraseña
                                </label>
                                <input v-model="form.password" type="password" class="w-full h-[56px] bg-foreground/5 border border-border/30 rounded-2xl px-5 text-sm font-mono font-bold text-foreground focus:border-primary/50 focus:ring-1 focus:ring-primary/50 transition-all outline-none autofill-shield shadow-inner" placeholder="••••••••">
                                <p v-if="form.errors.password" class="text-[9px] text-destructive font-bold uppercase ml-1 tracking-tighter">{{ form.errors.password }}</p>
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-[10px] font-black uppercase tracking-[0.2em] text-foreground/60 ml-1">Confirmar</label>
                                <input v-model="form.password_confirmation" type="password" class="w-full h-[56px] bg-foreground/5 border border-border/30 rounded-2xl px-5 text-sm font-mono font-bold text-foreground focus:border-primary/50 focus:ring-1 focus:ring-primary/50 transition-all outline-none autofill-shield shadow-inner" placeholder="••••••••">
                            </div>
                        </div>

                        <div class="pt-4 flex gap-4">
                            <Link :href="route('customer.login')" class="h-14 px-6 md:px-8 rounded-2xl bg-foreground/5 border border-border/40 text-foreground font-black uppercase text-[10px] tracking-widest flex items-center hover:bg-foreground/10 transition-colors">
                                Login
                            </Link>
                            <button @click="handleStep1Validation" :disabled="validatingStep1" 
                                    class="flex-1 h-14 bg-foreground text-background rounded-2xl font-black uppercase text-xs tracking-[0.2em] shadow-xl flex items-center justify-center gap-3 active:scale-95 transition-all outline-none disabled:opacity-50">
                                <span v-if="validatingStep1" class="w-5 h-5 border-2 border-background/30 border-t-background rounded-full animate-spin"></span>
                                <template v-else>Siguiente <ArrowRight :size="16" stroke-width="3" /></template>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <LocationWorkflow 
                v-if="currentStep === 2"
                :form="form"
                :activeBranches="activeBranches"
                submitLabel="Siguiente"
                @complete="currentStep = 3"
                @next="currentStep = 3"
                @back="currentStep = 1"
            />

            <div v-if="currentStep === 3" class="flex-1 flex flex-col items-center justify-center p-4 lg:p-8 w-full animate-in fade-in slide-in-from-right-8 duration-700">
                <div class="w-full max-w-xl glass-chassis rounded-[2.5rem] p-6 md:p-10 shadow-2xl relative z-10 flex flex-col items-center">
                    
                    <div class="text-center mb-8">
                        <h1 class="text-4xl font-black tracking-tighter uppercase italic leading-none mb-2 text-foreground">Avatar</h1>
                        <p class="text-primary text-[10px] font-black uppercase tracking-[0.3em] opacity-80">[ IDENTITY_VISUAL ]</p>
                    </div>

                    <div class="relative mb-10">
                        <div class="w-40 h-40 md:w-48 md:h-48 rounded-[3.5rem] border-[6px] border-foreground/5 p-2 bg-foreground/5 overflow-hidden shadow-inner flex items-center justify-center transition-all group">
                            <img v-if="form.avatar_source" 
                                 :src="`/assets/avatars/${form.avatar_source}`" 
                                 class="w-full h-full object-cover rounded-[2.8rem]" />
                            <User v-else :size="48" class="text-foreground/20" />
                        </div>
                    </div>

                    <div class="grid grid-cols-4 gap-3 md:gap-4 w-full mb-10">
                        <button v-for="i in 8" :key="i" type="button" 
                                @click="form.avatar_source = `avatar_${i}.png`" 
                                class="aspect-square rounded-2xl border-2 flex items-center justify-center p-2 transition-all duration-300 hover:scale-110 shadow-sm" 
                                :class="form.avatar_source === `avatar_${i}.png` ? 'border-primary bg-primary/10' : 'border-transparent bg-foreground/5 opacity-40 hover:opacity-100'">
                            <img :src="`/assets/avatars/avatar_${i}.png`" class="w-full h-full object-contain" />
                        </button>
                    </div>

                    <div class="flex gap-4 w-full">
                        <button @click="currentStep = 2" class="h-14 px-6 md:px-8 rounded-2xl bg-foreground/5 border border-border/40 text-foreground font-black uppercase text-[10px] tracking-widest hover:bg-foreground/10 transition-colors">
                            Atrás
                        </button>
                        <button @click="submit" :disabled="form.processing || !form.avatar_source" 
                                class="flex-1 h-14 bg-primary text-white rounded-2xl font-black uppercase text-xs tracking-[0.2em] shadow-xl shadow-primary/20 flex items-center justify-center gap-3 active:scale-95 transition-all disabled:opacity-30 disabled:cursor-not-allowed">
                            <span v-if="form.processing" class="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                            <template v-else>Finalizar <CheckCircle :size="18" stroke-width="3" /></template>
                        </button>
                    </div>
                </div>
            </div>

        </main>
    </div>
</template>

<style>
/* 1. CHASIS DE IDENTIDAD */
.glass-chassis {
    background-color: hsl(var(--background) / 0.6);
    backdrop-filter: blur(40px) saturate(200%);
    -webkit-backdrop-filter: blur(40px) saturate(200%);
    border: 1px solid hsl(var(--border) / 0.4);
}
.dark .glass-chassis {
    background-color: hsl(var(--background) / 0.8);
    border-color: hsl(var(--border) / 0.3);
}

/* 2. LIENZO CYBER CANVAS */
.du-cyber-canvas {
    background-color: hsl(var(--background));
    background-image: 
        linear-gradient(to bottom, hsl(var(--background)) 0%, hsl(var(--background)) 40%, transparent 100%),
        linear-gradient(to right, #0ed2da, #5f29c7);
}
.du-cyber-canvas::before {
    content: "";
    position: absolute;
    inset: 0;
    z-index: 0;
    background-image: linear-gradient(90deg, hsl(var(--border) / 0.4) 1px, transparent 1px);
    background-size: 50px 100%;
    pointer-events: none;
    mask-image: linear-gradient(to bottom, rgba(0, 0, 0, 1) 0%, rgba(0, 0, 0, 0) 80%);
    -webkit-mask-image: linear-gradient(to bottom, rgba(0, 0, 0, 1) 0%, rgba(0, 0, 0, 0) 80%);
}
.dark .du-cyber-canvas {
    background-image: 
        linear-gradient(to bottom, hsl(var(--background)) 0%, hsl(var(--background)) 50%, transparent 100%),
        linear-gradient(to right, #0ed2da33, #5f29c733);
}

/* 3. BLINDAJE AUTOFILL */
.autofill-shield:-webkit-autofill,
.autofill-shield:-webkit-autofill:hover, 
.autofill-shield:-webkit-autofill:focus, 
.autofill-shield:-webkit-autofill:active {
    -webkit-text-fill-color: hsl(var(--foreground)) !important;
    transition: background-color 5000s ease-in-out 0s !important;
    background-color: transparent !important;
    box-shadow: inset 0 0 20px 20px hsl(var(--foreground) / 0.05) !important;
    font-family: 'JetBrains Mono', monospace !important;
}

/* 4. TEL INPUT INDUSTRIAL */
.hardware-tel-input {
    background: hsl(var(--foreground) / 0.05) !important;
    border: 1px solid hsl(var(--border) / 0.3) !important;
    border-radius: 1rem !important;
    height: 56px !important;
    box-shadow: inset 0 2px 4px 0 rgba(0, 0, 0, 0.05);
}
.hardware-tel-input:focus-within {
    border-color: hsl(var(--primary) / 0.5) !important;
    box-shadow: 0 0 0 2px hsl(var(--primary) / 0.1) !important;
}
.hardware-tel-input .vti__input {
    background: transparent !important;
    color: hsl(var(--foreground)) !important;
    font-weight: 800 !important;
    font-size: 14px !important;
    font-family: 'JetBrains Mono', monospace;
}
.hardware-tel-input .vti__selection .vti__country-code { display: none !important; }
.hardware-tel-input .vti__dropdown {
    padding: 0 12px !important;
    background: transparent !important;
    border-right: 1px solid hsl(var(--border) / 0.2) !important;
}
.hardware-tel-input .vti__dropdown-list {
    background-color: hsl(var(--background)) !important;
    border: 1px solid hsl(var(--border) / 0.5) !important;
    border-radius: 1rem !important;
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.5) !important;
    color: hsl(var(--foreground)) !important;
    width: 280px !important;
    max-width: 90vw !important;
}
.hardware-tel-input .vti__dropdown-item.highlighted {
    background-color: hsl(var(--foreground) / 0.1) !important;
}
.ease-ios { transition-timing-function: cubic-bezier(0.32, 0.72, 0, 1); }
</style>