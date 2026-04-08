<script setup>
import { ref, computed, onMounted } from 'vue'; 
import { useForm, Link, Head } from '@inertiajs/vue3';
import { VueTelInput } from 'vue-tel-input';
import 'vue-tel-input/vue-tel-input.css';
import axios from 'axios';

// COMPONENTE DE FLUJO LOGÍSTICO
import LocationWorkflow from '@/Components/Customer/Maps/LocationWorkflow.vue'; 

import { 
    UserPlus, Lock, Mail, Upload, ArrowRight, ArrowLeft,
    CheckCircle, User, Smartphone // User añadido para resolver advertencia
} from 'lucide-vue-next';

const props = defineProps({ activeBranches: Array });

const currentStep = ref(1);
const steps = [
    { id: 1, title: 'CUENTA' },
    { id: 2, title: 'LOGÍSTICA' }, // El componente maneja Ubicación y Detalles
    { id: 3, title: 'PERFIL' },
];

const validatingStep1 = ref(false);
const isPhoneValid = ref(false);
const registrationId = ref(null);

onMounted(() => {
    registrationId.value = crypto.randomUUID();
    
    // Si regresamos con errores de ubicación, saltamos al paso 2
    if (form.errors.address || form.errors.latitude) {
        currentStep.value = 2;
    }
    // Si regresamos con errores de avatar, saltamos al paso 3
    else if (form.errors.avatar_source) {
        currentStep.value = 3;
    }
});

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
    guest_client_uuid: localStorage.getItem('guest_client_uuid')
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
        currentStep.value = 2; // Avanza al componente de ubicación
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

    <div class="min-h-[100svh] bg-background text-foreground flex flex-col overflow-hidden selection:bg-primary/20 transition-colors duration-500">
        
        <div class="fixed top-0 left-0 w-full h-1 z-[1000] bg-foreground/5">
            <div class="h-full bg-primary shadow-[0_0_20px_rgba(var(--primary-rgb),0.6)] transition-all duration-1000 ease-out"
                 :style="{ width: progressWidth + '%' }"></div>
        </div>

        <main class="flex-1 flex flex-col relative h-full">
            
            <div v-if="currentStep === 1" class="flex-1 flex flex-col items-center justify-center p-8 max-w-xl mx-auto w-full animate-in fade-in slide-in-from-bottom-4 duration-700">
                <div class="text-center mb-10">
                    <h1 class="text-5xl font-black tracking-tighter uppercase italic mb-2 leading-none text-foreground">Registro</h1>
                    <p class="text-primary text-[10px] font-black uppercase tracking-[0.3em] opacity-80">[ DATA_SENSITIVE: PASO_01 ]</p>
                </div>

                <div class="w-full space-y-6">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black uppercase tracking-[0.2em] text-muted-foreground/60 ml-1">Nombres</label>
                            <input v-model="form.first_name" type="text" class="w-full h-14 bg-foreground/[0.03] border border-border/40 rounded-[1.5rem] px-5 text-sm font-bold uppercase focus:ring-2 focus:ring-primary/20 transition-all outline-none" placeholder="JUAN">
                            <p v-if="form.errors.first_name" class="text-[9px] text-destructive font-bold uppercase ml-1 tracking-tighter">{{ form.errors.first_name }}</p>
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black uppercase tracking-[0.2em] text-muted-foreground/60 ml-1">Apellidos</label>
                            <input v-model="form.last_name" type="text" class="w-full h-14 bg-foreground/[0.03] border border-border/40 rounded-[1.5rem] px-5 text-sm font-bold uppercase focus:ring-2 focus:ring-primary/20 transition-all outline-none" placeholder="PEREZ">
                            <p v-if="form.errors.last_name" class="text-[9px] text-destructive font-bold uppercase ml-1 tracking-tighter">{{ form.errors.last_name }}</p>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase tracking-[0.2em] text-muted-foreground/60 ml-1">Terminal Móvil</label>
                        <vue-tel-input v-model="form.phone" @on-input="onInput" mode="international" :defaultCountry="'BO'" 
                                       class="custom-tel-input-full" :class="{ 'error-border': form.errors.phone }" />
                        <div v-if="form.errors.phone" class="text-[10px] text-destructive font-black uppercase mt-1 ml-1 tracking-tighter">{{ form.errors.phone }}</div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black uppercase tracking-[0.2em] text-muted-foreground/60 ml-1">Email</label>
                        <input v-model="form.email" type="email" class="w-full h-14 bg-foreground/[0.03] border border-border/40 rounded-[1.5rem] px-5 text-sm font-bold uppercase focus:ring-2 focus:ring-primary/20 transition-all outline-none" placeholder="SISTEMA@DOMINIO.COM">
                        <p v-if="form.errors.email" class="text-[9px] text-destructive font-bold uppercase ml-1 tracking-tighter">{{ form.errors.email }}</p>
                    </div>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black uppercase tracking-[0.2em] text-muted-foreground/60 ml-1">Llave_Acceso</label>
                            <input v-model="form.password" type="password" class="w-full h-14 bg-foreground/[0.03] border border-border/40 rounded-[1.5rem] px-5 text-sm font-bold focus:ring-2 focus:ring-primary/20 transition-all outline-none">
                            <p v-if="form.errors.password" class="text-[9px] text-destructive font-bold uppercase ml-1 tracking-tighter">{{ form.errors.password }}</p>
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black uppercase tracking-[0.2em] text-muted-foreground/60 ml-1">Confirmar</label>
                            <input v-model="form.password_confirmation" type="password" class="w-full h-14 bg-foreground/[0.03] border border-border/40 rounded-[1.5rem] px-5 text-sm font-bold outline-none focus:ring-2 focus:ring-primary/20 transition-all">
                        </div>
                    </div>

                    <div class="pt-4 flex gap-4">
                        <Link :href="route('customer.login')" class="h-16 px-8 rounded-[1.8rem] bg-foreground/5 text-foreground font-black uppercase text-[10px] tracking-widest flex items-center hover:bg-foreground/10 transition-colors">Login</Link>
                        <button @click="handleStep1Validation" :disabled="validatingStep1" 
                                class="flex-1 h-16 bg-primary text-white rounded-[1.8rem] font-black uppercase text-[11px] tracking-[0.2em] shadow-xl shadow-primary/20 flex items-center justify-center gap-3 active:scale-95 transition-all">
                            <span v-if="validatingStep1" class="w-6 h-6 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                            <template v-else>Siguiente <ArrowRight :size="18" stroke-width="3" /></template>
                        </button>
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

            <div v-if="currentStep === 3" class="flex-1 flex flex-col items-center justify-center p-8 max-w-xl mx-auto w-full animate-in fade-in slide-in-from-right-8 duration-700">
                <div class="text-center mb-10">
                    <h1 class="text-4xl font-black tracking-tighter uppercase italic leading-none mb-2 text-foreground">Avatar</h1>
                    <p class="text-primary text-[10px] font-black uppercase tracking-[0.3em] opacity-80">[ IDENTITY_VISUAL ]</p>
                </div>

                <div class="relative mb-12">
                    <div class="w-48 h-48 rounded-[3.5rem] border-[8px] border-primary/5 p-2 bg-foreground/[0.03] overflow-hidden shadow-2xl flex items-center justify-center transition-all group">
                        <img v-if="form.avatar_source" 
                             :src="`/assets/avatars/${form.avatar_source}`" 
                             class="w-full h-full object-cover rounded-[2.8rem]" />
                        <User v-else :size="64" class="text-foreground/10" />
                    </div>
                </div>

                <div class="grid grid-cols-4 gap-4 w-full mb-10">
                    <button v-for="i in 8" :key="i" type="button" 
                            @click="form.avatar_source = `avatar_${i}.png`" 
                            class="aspect-square rounded-2xl border-2 flex items-center justify-center p-2 transition-all duration-300 hover:scale-110 shadow-sm" 
                            :class="form.avatar_source === `avatar_${i}.png` ? 'border-primary bg-primary/10' : 'border-transparent bg-foreground/5 opacity-40 hover:opacity-100'">
                        <img :src="`/assets/avatars/avatar_${i}.png`" class="w-full h-full object-contain" />
                    </button>
                </div>

                <div class="flex gap-4 w-full">
                    <button @click="currentStep = 2" class="h-16 px-8 rounded-[1.8rem] bg-foreground/5 text-foreground font-black uppercase text-[10px] tracking-widest hover:bg-foreground/10 transition-colors">Atrás</button>
                    <button @click="submit" :disabled="form.processing || !form.avatar_source" 
                            class="flex-1 h-16 bg-primary text-white rounded-[1.8rem] font-black uppercase text-[11px] tracking-[0.2em] shadow-xl shadow-primary/20 flex items-center justify-center gap-3 active:scale-95 transition-all disabled:opacity-30 disabled:cursor-not-allowed">
                        <span v-if="form.processing" class="w-6 h-6 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                        <template v-else>Finalizar <CheckCircle :size="18" stroke-width="3" /></template>
                    </button>
                </div>
            </div>

        </main>
    </div>
</template>

<style>
/* Reset de Tel Input para alta densidad */
.custom-tel-input-full { 
    height: 60px !important; 
    background: rgba(var(--foreground), 0.03) !important; 
    border: 1px solid rgba(var(--border), 0.4) !important; 
    border-radius: 1.5rem !important; 
    transition: all 0.3s ease; 
}
.custom-tel-input-full:focus-within { 
    border-color: rgba(var(--primary), 0.4) !important; 
    background: rgba(var(--foreground), 0.05) !important;
    box-shadow: 0 0 0 4px rgba(var(--primary), 0.05) !important;
}
.custom-tel-input-full .vti__input { 
    background: transparent !important; 
    font-weight: 700 !important; 
    font-size: 14px !important; 
    color: inherit !important;
}
.custom-tel-input-full .vti__dropdown { 
    border-radius: 1.5rem 0 0 1.5rem !important; 
}
.custom-tel-input-full .vti__dropdown:hover { 
    background: rgba(var(--foreground), 0.05) !important; 
}

.error-border { border-color: hsl(var(--destructive)) !important; }

::-webkit-scrollbar { display: none; }
</style>