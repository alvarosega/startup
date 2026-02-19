<script setup>
import { ref, watch, computed } from 'vue'; 
import { useForm, Link, Head } from '@inertiajs/vue3';
import axios from 'axios';
import { VueTelInput } from 'vue-tel-input';
import 'vue-tel-input/vue-tel-input.css';
import BaseInput from '@/Components/Base/BaseInput.vue';
import ClientLocationPicker from '@/Components/Maps/ClientLocationPicker.vue';
import { 
    MapPin, UserPlus, Smartphone, Lock, Mail, 
    CheckCircle, Upload, Truck, ArrowRight, ArrowLeft, Image, ShieldCheck
} from 'lucide-vue-next';

const props = defineProps({
    activeBranches: { type: Array, default: () => [] }
});

const currentStep = ref(1);
const step1Errors = ref({});
const mapComponentRef = ref(null); 
const validatingStep1 = ref(false);
const customPreview = ref(null);

const form = useForm({
    first_name: '',
    last_name: '',  
    phone: '', 
    country_code: '',
    email: '', 
    password: '', 
    password_confirmation: '', 
    terms: true, // Siempre true por política de registro implícito
    avatar_type: 'icon', 
    avatar_source: 'avatar_1.svg', 
    avatar_file: null,
    alias: 'Mi Ubicación', 
    address: '', 
    details: '', 
    latitude: -16.5000, 
    longitude: -68.1500, 
    branch_id: null, 
    role: 'client'
});

// Limpieza de errores al escribir (Reactividad Quirúrgica)
watch(() => form.first_name, () => { if (step1Errors.value.first_name) delete step1Errors.value.first_name; });
watch(() => form.last_name, () => { if (step1Errors.value.last_name) delete step1Errors.value.last_name; });
watch(() => form.email, () => { if (step1Errors.value.email) delete step1Errors.value.email; });

const steps = [
    { id: 1, title: 'Cuenta', icon: UserPlus },
    { id: 2, title: 'Avatar', icon: Image },
    { id: 3, title: 'Ubicación', icon: MapPin },
];
const isPhoneValid = ref(false);
// resources/js/Pages/Customer/Auth/Register.vue

// Esta función se dispara cada vez que el usuario elige una bandera diferente
const onCountryChanged = (countryObj) => {
    if (countryObj && countryObj.iso2) {
        form.country_code = countryObj.iso2.toUpperCase();
        console.log("🏳️ País cambiado manualmente a:", form.country_code);
    }
};

const onInput = (phone, obj) => { 
    isPhoneValid.value = obj?.valid ?? false; 

    // Sincronización de seguridad del país
    if (obj?.country?.iso2) {
        form.country_code = obj.country.iso2.toUpperCase();
    }

    // Sincronización del número
    if(obj?.number) {
        form.phone = obj.number; 
    }
    
    if (step1Errors.value.phone) delete step1Errors.value.phone;
};

// REFUERZO: Si por alguna razón onInput no basta, este watch asegura la integridad
watch(() => form.phone, (newVal) => {
    if (!newVal) form.country_code = ''; 
});

const nextStep = async () => {
    if (currentStep.value === 1) {
        // BLOQUEO DE SEGURIDAD: Cantidad de números incorrecta
        if (!isPhoneValid.value) {
            step1Errors.value = { phone: ['El número no tiene la cantidad de dígitos correcta para el país seleccionado.'] };
            return;
        }
        try {
            await axios.post(route('register.validate-step-1'), form.data());
            currentStep.value = 2;
        } catch (error) {
            if (error.response?.status === 422) step1Errors.value = error.response.data.errors;
            else alert('Error de conexión con la central.');
        } finally {
            validatingStep1.value = false;
        }
    } else if (currentStep.value === 2) {
        currentStep.value = 3;
    }
};

const submit = () => {
    console.log("DATOS ENVIADOS AL BACKEND:", form.data());
    if (form.processing) return;
    form.post(route('register'), {
        preserveScroll: true,
        forceFormData: true,
        onError: (errors) => {
            console.error('Fallo en el registro:', errors);
        }
    });
};
</script>

<template>
    <Head title="Registro de Cliente - Electric Luxury" />
    
    <div class="min-h-screen bg-background relative flex items-center justify-center p-4 overflow-hidden">
        
        <div class="fixed inset-0 z-0">
            <div class="absolute inset-0 bg-gradient-to-br from-background via-background to-primary/5" />
            <div class="absolute inset-0 bg-dots opacity-30 [mask-image:radial-gradient(ellipse_at_center,black_40%,transparent_80%)]" />
        </div>

        <div class="w-full max-w-xl bg-card border border-border/50 rounded-[2.5rem] shadow-2xl relative z-10 flex flex-col h-full max-h-[850px] overflow-hidden animate-in fade-in zoom-in-95 duration-500">
            
            <div class="px-8 pt-8 pb-4 border-b border-border/40 shrink-0 bg-card/50 backdrop-blur-md">
                <div class="flex justify-between items-end mb-6">
                    <div>
                        <h2 class="text-3xl font-display font-black text-navy tracking-tighter leading-none">Únete a la Red</h2>
                        <p class="text-[10px] uppercase font-bold tracking-[0.2em] text-muted-foreground mt-2">Membresía Premium</p>
                    </div>
                    <div class="flex flex-col items-end">
                        <span class="text-[10px] font-black text-primary bg-primary/10 px-3 py-1 rounded-full uppercase tracking-wider border border-primary/20">
                            Paso {{ currentStep }} / {{ steps.length }}
                        </span>
                    </div>
                </div>
                
                <div class="flex items-center gap-2 h-1.5">
                    <div v-for="step in steps" :key="step.id" 
                         class="h-full rounded-full transition-all duration-700 ease-smooth"
                         :class="currentStep >= step.id ? 'bg-primary shadow-[0_0_10px_rgba(0,240,255,0.6)] flex-[2]' : 'bg-muted flex-1'">
                    </div>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto p-8 custom-scrollbar">
                <form id="registerForm" @submit.prevent="submit">
                    
                    <div v-show="currentStep === 1" class="space-y-6 animate-in slide-in-from-right-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <BaseInput v-model="form.first_name" label="Nombre" placeholder="Ej: Álvaro" :icon="UserPlus" :error="step1Errors.first_name?.[0]" />
                            <BaseInput v-model="form.last_name" label="Apellido" placeholder="Ej: Segovia" :icon="UserPlus" :error="step1Errors.last_name?.[0]" />
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase tracking-widest text-navy/60 ml-1 flex items-center gap-2">
                                <Smartphone :size="14" class="text-primary" /> Celular de Contacto *
                            </label>
                            <vue-tel-input 
                                v-model="form.phone" 
                                @on-input="onInput"
                                @country-changed="onCountryChanged" 
                                mode="international" 
                                :preferredCountries="['BO', 'PE', 'US']" 
                                class="custom-tel-input" 
                                :class="{ '!border-error': step1Errors.phone }" 
                            />
                            <p v-if="step1Errors.phone" class="text-[10px] text-error font-black mt-1 ml-1 uppercase">{{ step1Errors.phone[0] }}</p>
                        </div>

                        <BaseInput v-model="form.email" type="email" label="Correo Corporativo / Personal" placeholder="ejemplo@email.com" :icon="Mail" :error="step1Errors.email?.[0]" />
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <BaseInput v-model="form.password" type="password" label="Contraseña" :icon="Lock" :error="step1Errors.password?.[0]" />
                            <BaseInput v-model="form.password_confirmation" type="password" label="Confirmar" :icon="Lock" />
                        </div>
                    </div>

                    <div v-show="currentStep === 2" class="space-y-8 animate-in slide-in-from-right-4 text-center">
                        <div class="relative inline-block group">
                            <div class="w-36 h-36 rounded-[2.5rem] border-4 border-card p-1.5 mx-auto bg-muted/30 overflow-hidden shadow-2xl transition-transform group-hover:scale-105 duration-500">
                                <img v-if="form.avatar_type === 'custom' && customPreview" :src="customPreview" class="w-full h-full object-cover rounded-[2rem]" />
                                <img v-else :src="`/assets/avatars/${form.avatar_source}`" class="w-full h-full object-cover rounded-[2rem]" />
                            </div>
                            <label for="reg_avatar_upload" class="absolute -bottom-2 -right-2 p-3 bg-primary text-navy rounded-2xl cursor-pointer shadow-xl hover:scale-110 active:scale-95 transition-all border-4 border-card">
                                <Upload :size="20" stroke-width="3" />
                            </label>
                            <input type="file" id="reg_avatar_upload" class="hidden" @change="(e) => { const file = e.target.files[0]; if(file) { form.avatar_type='custom'; form.avatar_file=file; customPreview = URL.createObjectURL(file); } }">
                        </div>

                        <div class="grid grid-cols-4 gap-3 max-w-sm mx-auto">
                            <button type="button" v-for="i in 8" :key="i" 
                                    @click="() => { form.avatar_type='icon'; form.avatar_source=`avatar_${i}.svg`; customPreview=null; }"
                                    class="aspect-square rounded-2xl border-2 flex items-center justify-center p-2 transition-all duration-300"
                                    :class="form.avatar_type === 'icon' && form.avatar_source === `avatar_${i}.svg` ? 'border-primary bg-primary/5 shadow-lg shadow-primary/10 scale-110' : 'border-transparent bg-muted/20 opacity-60 hover:opacity-100'">
                                <img :src="`/assets/avatars/avatar_${i}.svg`" class="w-full h-full" />
                            </button>
                        </div>
                        <p class="text-xs font-bold text-muted-foreground uppercase tracking-widest">Selecciona tu Identidad</p>
                    </div>

                    <div v-show="currentStep === 3" class="h-full flex flex-col space-y-6 animate-in slide-in-from-right-4">
                        <div class="h-72 rounded-[2rem] overflow-hidden border border-border shadow-inner relative group">
                            <ClientLocationPicker ref="mapComponentRef" v-model:modelValueLat="form.latitude" v-model:modelValueLng="form.longitude" v-model:modelValueAddress="form.address" v-model:modelValueBranchId="form.branch_id" :activeBranches="props.activeBranches" />
                            <div class="absolute top-4 right-4 pointer-events-none">
                                <span class="bg-navy/80 backdrop-blur px-3 py-1.5 rounded-xl text-[10px] font-black text-white uppercase tracking-widest shadow-xl">Precisión Satelital</span>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <BaseInput v-model="form.alias" label="Alias del Punto" placeholder="Ej: Oficina Central" :icon="MapPin" />
                            <BaseInput v-model="form.details" label="Detalles de Acceso" placeholder="Ej: Piso 4, timbre A" :icon="ShieldCheck" />
                        </div>
                    </div>
                </form>
            </div>

            <div class="p-8 border-t border-border/40 bg-card/80 backdrop-blur-md shrink-0">
                <div class="flex gap-4">
                    <button v-if="currentStep > 1" @click="currentStep--" 
                            class="btn btn-outline flex-1 h-14 font-black uppercase tracking-tighter text-navy hover:bg-muted">
                        <ArrowLeft :size="18" /> Atrás
                    </button>
                    <Link v-else :href="route('login')" 
                          class="btn btn-ghost flex-1 h-14 font-black uppercase tracking-tighter text-muted-foreground border border-border">
                        ¿Ya eres socio?
                    </Link>

                    <button v-if="currentStep < 3" @click="nextStep" :disabled="validatingStep1" 
                            class="btn btn-primary flex-[2] h-14 shadow-lg shadow-primary/25 group predictive-aura">
                        <span v-if="validatingStep1" class="spinner spinner-sm border-navy/30 border-t-navy"></span>
                        <span v-else class="flex items-center justify-center gap-3 font-black text-lg uppercase tracking-tighter">
                            Continuar <ArrowRight :size="20" class="group-hover:translate-x-2 transition-transform" />
                        </span>
                    </button>

                    <div v-else class="flex-[2] flex flex-col gap-3">
                        <button type="submit" form="registerForm" :disabled="form.processing" 
                                class="btn btn-primary w-full h-14 shadow-lg shadow-primary/25 group predictive-aura">
                            <span v-if="form.processing" class="spinner spinner-sm border-navy/30 border-t-navy"></span>
                            <span v-else class="flex items-center justify-center gap-3 font-black text-lg uppercase tracking-tighter">
                                Crear Cuenta <CheckCircle :size="20" />
                            </span>
                        </button>
                        <p class="text-[9px] text-center text-muted-foreground font-medium px-4 leading-tight">
                            Al registrarse, usted declara estar de acuerdo con nuestros 
                            <a href="#" class="text-primary font-black hover:underline">Términos y Condiciones</a> 
                            y la Política de Privacidad de la Red.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.custom-tel-input { 
    @apply w-full rounded-2xl border-input bg-background/50 text-base h-[52px] transition-all duration-300 border font-bold;
}
.custom-tel-input:focus-within { 
    @apply border-primary ring-0 shadow-[0_0_20px_rgba(0,240,255,0.15)];
}
:deep(.vti__dropdown) { 
    @apply bg-transparent px-3 hover:bg-muted transition-colors !important;
    border-radius: 1rem 0 0 1rem !important; 
}
:deep(.vti__input) { 
    @apply bg-transparent !important;
}

.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-thumb { @apply bg-muted-foreground/20 rounded-full; }
</style>