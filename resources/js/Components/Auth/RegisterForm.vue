<script setup>
import { ref, watch, computed } from 'vue'; 
import { useForm } from '@inertiajs/vue3';
import axios from 'axios';
import { VueTelInput } from 'vue-tel-input';
import 'vue-tel-input/vue-tel-input.css';
import BaseInput from '@/Components/Base/BaseInput.vue';
import BaseCheckbox from '@/Components/Base/BaseCheckbox.vue';
import ClientLocationPicker from '@/Components/Maps/ClientLocationPicker.vue';
import { 
    MapPin, UserPlus, Smartphone, Lock, Mail, 
    CheckCircle, Upload, Truck, ArrowRight, ArrowLeft, Crosshair, Image 
} from 'lucide-vue-next';

const emit = defineEmits(['close', 'switchToLogin', 'switchToDriver']);
const props = defineProps({
    activeBranches: { type: Array, default: () => [] }
});

// Estados
const currentStep = ref(1);
const step1Errors = ref({});
const mapComponentRef = ref(null); 
const validatingStep1 = ref(false);
const customPreview = ref(null);
const locating = ref(false);

const steps = [
    { id: 1, title: 'Cuenta', icon: UserPlus },
    { id: 2, title: 'Avatar', icon: Image },
    { id: 3, title: 'Ubicación', icon: MapPin },
];

const form = useForm({
    phone: '', email: '', password: '', password_confirmation: '', terms: false,
    avatar_type: 'icon', avatar_source: 'avatar_1.svg', avatar_file: null,
    alias: 'Mi Ubicación', address: '', details: '', latitude: -16.5000, longitude: -68.1500, branch_id: null, role: 'client'
});

const onInput = (phone, obj) => { 
    if(obj?.number) form.phone = obj.number; 
    if (step1Errors.value.phone) delete step1Errors.value.phone;
};

// Navegación y Validación
const nextStep = async () => {
    if (currentStep.value === 1) {
        if (!form.terms) return alert('Debes aceptar los términos y condiciones');
        
        step1Errors.value = {};
        validatingStep1.value = true;
        try {
            await axios.post(route('register.validate-step-1'), {
                phone: form.phone, email: form.email, 
                password: form.password, password_confirmation: form.password_confirmation
            });
            currentStep.value = 2;
        } catch (error) {
            if (error.response?.status === 422) step1Errors.value = error.response.data.errors;
        } finally {
            validatingStep1.value = false;
        }
    } else if (currentStep.value === 2) {
        currentStep.value = 3;
    }
};

const prevStep = () => { if (currentStep.value > 1) currentStep.value--; };

// Avatar Logic
const selectIcon = (iconName) => { 
    form.avatar_type = 'icon'; form.avatar_source = iconName; 
    form.avatar_file = null; customPreview.value = null; 
};
const uploadCustom = (e) => { 
    const file = e.target.files[0]; 
    if (file) { 
        form.avatar_type = 'custom'; form.avatar_file = file; 
        customPreview.value = URL.createObjectURL(file); 
    } 
};

// Mapa Logic
watch(currentStep, (val) => {
    if (val === 3) setTimeout(() => mapComponentRef.value?.refreshMap(), 350);
});

const getMyLocation = () => {
    if (!navigator.geolocation) return alert('Tu navegador no soporta geolocalización.');
    locating.value = true; form.address = "Obteniendo ubicación...";
    navigator.geolocation.getCurrentPosition(
        (pos) => {
            form.latitude = pos.coords.latitude; form.longitude = pos.coords.longitude;
            locating.value = false;
        },
        (err) => { 
            console.error(err); locating.value = false; form.address = "";
            alert('No se pudo obtener tu ubicación.');
        },
        { enableHighAccuracy: true, timeout: 5000, maximumAge: 0 }
    );
};

const submit = () => {
    if (!form.address?.trim()) form.address = `Ubicación GPS (${form.latitude.toFixed(5)}, ${form.longitude.toFixed(5)})`;
    form.post(route('register'), {
        preserveScroll: true,
        onSuccess: () => emit('close'),
        onError: (errors) => { if (errors.branch_id) alert('Selecciona una ubicación válida.'); }
    });
};

const progressPercentage = computed(() => ((currentStep.value) / steps.length) * 100);
</script>

<template>
    <div class="h-full flex flex-col bg-card w-full relative">
        
        <div class="px-6 pt-6 pb-4 border-b border-border/40 shrink-0">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-display font-black text-foreground tracking-tight">
                    Crea tu Cuenta
                </h2>
                <span class="text-xs font-bold text-muted-foreground bg-muted px-2 py-1 rounded-md">
                    Paso {{ currentStep }} de {{ steps.length }}
                </span>
            </div>
            <div class="flex items-center gap-2 mb-2">
                <div v-for="step in steps" :key="step.id" 
                     class="h-1.5 rounded-full flex-1 transition-all duration-500"
                     :class="currentStep >= step.id ? 'bg-primary' : 'bg-muted'">
                </div>
            </div>
            <p class="text-xs text-muted-foreground font-medium">
                {{ steps[currentStep - 1].title }}
            </p>
        </div>

        <div class="flex-1 overflow-y-auto scrollbar-thin p-6 relative">
            <form @submit.prevent="submit" class="space-y-6 h-full flex flex-col">
                
                <div v-show="currentStep === 1" class="space-y-5 animate-in slide-in-from-right-4">
                    <div class="space-y-1.5">
                        <label class="form-label flex items-center gap-1.5 ml-1">
                            <Smartphone :size="14" class="text-muted-foreground" /> Celular *
                        </label>
                        <vue-tel-input 
                            v-model="form.phone" @on-input="onInput" mode="international"
                            :preferredCountries="['BO']" :defaultCountry="'BO'"
                            :inputOptions="{ placeholder: 'Ej: 77712345' }"
                            class="custom-tel-input"
                            :class="{ '!border-error focus-within:!ring-error/10': step1Errors.phone }"
                        />
                        <p v-if="step1Errors.phone" class="form-error ml-1">{{ step1Errors.phone[0] }}</p>
                    </div>
                    <BaseInput v-model="form.email" type="email" label="Correo Electrónico" placeholder="ejemplo@email.com" :icon="Mail" :error="step1Errors.email ? step1Errors.email[0] : ''" />
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <BaseInput v-model="form.password" type="password" label="Contraseña *" placeholder="Min. 8 caracteres" :icon="Lock" :error="step1Errors.password ? step1Errors.password[0] : ''" />
                        <BaseInput v-model="form.password_confirmation" type="password" label="Confirmar *" :icon="Lock" />
                    </div>
                    <div class="alert alert-info py-3">
                        <BaseCheckbox v-model="form.terms">
                            <span class="text-xs">Acepto <a href="#" class="font-bold hover:underline text-primary">Términos</a> y <a href="#" class="font-bold hover:underline text-primary">Privacidad</a></span>
                        </BaseCheckbox>
                    </div>
                    <button type="button" @click="$emit('switchToDriver')" class="w-full flex items-center justify-center gap-2 text-xs font-bold text-muted-foreground hover:text-primary transition-colors py-2 border border-dashed border-border rounded-xl hover:border-primary/50 hover:bg-primary/5">
                        <Truck :size="14" /> ¿Eres conductor? Regístrate aquí
                    </button>
                </div>

                <div v-show="currentStep === 2" class="space-y-6 animate-in slide-in-from-right-4">
                     <div class="text-center space-y-4">
                        <div class="inline-block relative">
                            <div class="w-24 h-24 rounded-full border-4 border-primary/20 p-1 mx-auto overflow-hidden bg-muted">
                                <img v-if="form.avatar_type === 'custom' && customPreview" :src="customPreview" class="w-full h-full object-cover rounded-full" />
                                <img v-else :src="`/assets/avatars/${form.avatar_source}`" class="w-full h-full object-cover rounded-full" />
                            </div>
                            <label for="avatar_upload" class="absolute bottom-0 right-0 p-1.5 bg-primary text-white rounded-full cursor-pointer hover:bg-primary-dark shadow-md transition-transform hover:scale-110">
                                <Upload :size="14" />
                            </label>
                            <input type="file" id="avatar_upload" class="hidden" accept="image/*" @change="uploadCustom">
                        </div>
                        <p class="text-sm text-muted-foreground">Elige un avatar o sube tu foto</p>
                    </div>
                    <div class="grid grid-cols-4 gap-3">
                        <button type="button" v-for="i in 8" :key="i" @click="selectIcon(`avatar_${i}.svg`)"
                                class="aspect-square rounded-2xl border-2 flex items-center justify-center p-2 transition-all hover:scale-105 active:scale-95 bg-muted/20"
                                :class="form.avatar_type === 'icon' && form.avatar_source === `avatar_${i}.svg` ? 'border-primary bg-primary/10 ring-2 ring-primary/20' : 'border-transparent hover:border-border'">
                            <img :src="`/assets/avatars/avatar_${i}.svg`" class="w-full h-full" />
                        </button>
                    </div>
                </div>

                <div v-show="currentStep === 3" class="flex flex-col h-full animate-in slide-in-from-right-4 relative">
                    
                    <label class="form-label mb-2 flex items-center justify-between">
                        <span>¿Dónde te enviamos tus pedidos?</span>
                        <span class="text-xs text-primary font-bold animate-pulse" v-if="locating">Buscando satélites...</span>
                    </label>

                    <div class="relative rounded-3xl overflow-hidden border border-border shadow-inner bg-muted/30 shrink-0 h-64 group">
                        
                        <ClientLocationPicker ref="mapComponentRef" 
                            v-model:modelValueLat="form.latitude" v-model:modelValueLng="form.longitude"
                            v-model:modelValueAddress="form.address" v-model:modelValueBranchId="form.branch_id"
                            :activeBranches="props.activeBranches" />
                        
                        <div v-if="locating" class="absolute inset-0 bg-primary/10 backdrop-blur-[2px] z-[1001] flex flex-col items-center justify-center animate-in fade-in">
                            <div class="relative">
                                <div class="w-16 h-16 border-4 border-primary rounded-full animate-ping absolute inset-0 opacity-75"></div>
                                <div class="w-16 h-16 bg-primary text-primary-foreground rounded-full flex items-center justify-center shadow-2xl relative z-10">
                                    <Crosshair :size="32" class="animate-spin-slow" />
                                </div>
                            </div>
                            <p class="mt-4 font-black text-primary text-sm uppercase tracking-widest animate-pulse">Localizando...</p>
                        </div>

                        <div v-else class="absolute bottom-4 left-0 right-0 flex justify-center z-[1000] pointer-events-none">
                            <button type="button" @click="getMyLocation" 
                                    class="pointer-events-auto btn bg-gradient-to-r from-primary to-secondary text-white shadow-xl shadow-primary/40 rounded-full px-6 py-3 flex items-center gap-3 transition-all hover:scale-105 active:scale-95 group/btn">
                                <span class="relative flex h-3 w-3">
                                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-white opacity-75"></span>
                                  <span class="relative inline-flex rounded-full h-3 w-3 bg-white"></span>
                                </span>
                                <span class="font-bold text-sm tracking-wide">Usar mi GPS Actual</span>
                            </button>
                        </div>
                    </div>

                    <div class="mt-6 space-y-4 pb-2 flex-1">
                        <div class="grid grid-cols-2 gap-4">
                            <BaseInput v-model="form.alias" label="Alias" placeholder="Ej: Casa, Oficina" />
                            <BaseInput v-model="form.details" label="Referencia (Portón, color...)" placeholder="Ej: Reja negra" />
                        </div>
                        
                        <div v-if="form.errors.branch_id" class="p-3 rounded-xl bg-error/10 border border-error/20 text-error text-xs font-bold flex items-center gap-2 animate-bounce-subtle">
                            <MapPin :size="16"/> {{ form.errors.branch_id }}
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="p-6 border-t border-border bg-muted/10 shrink-0">
            <div class="flex gap-3">
                <button type="button" v-if="currentStep > 1" @click="prevStep" class="btn btn-outline flex-1">Atrás</button>
                <div v-else class="flex-1 flex justify-center items-center">
                    <button @click="$emit('switchToLogin')" class="text-xs font-bold text-primary hover:underline">¿Ya tienes cuenta?</button>
                </div>

                <button v-if="currentStep < 3" type="button" @click="nextStep" :disabled="currentStep === 1 && !form.terms" class="btn btn-primary flex-1 shadow-lg shadow-primary/20">
                    <span v-if="validatingStep1" class="spinner spinner-sm"></span>
                    <span v-else class="flex items-center justify-center gap-2">Siguiente <ArrowRight :size="16"/></span>
                </button>
                <button v-else @click="submit" :disabled="form.processing" class="btn btn-primary flex-1 shadow-lg shadow-primary/20">
                    <span v-if="form.processing" class="spinner spinner-sm"></span>
                    <span v-else class="flex items-center justify-center gap-2">Finalizar <CheckCircle :size="16"/></span>
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Overrides para vue-tel-input */
.vue-tel-input:focus-within { box-shadow: none !important; }
.custom-tel-input { 
    @apply w-full rounded-xl border-input bg-background text-sm h-[46px] transition-all duration-200;
}
.custom-tel-input:hover { @apply border-primary/50; }
.custom-tel-input:focus-within { @apply border-primary ring-4 ring-primary/10; }
.vti__dropdown { border-top-left-radius: 0.75rem !important; border-bottom-left-radius: 0.75rem !important; background-color: transparent !important; }
.vti__input { border-radius: 0.75rem !important; background-color: transparent !important; }

/* Animación Radar */
.animate-spin-slow { animation: spin 3s linear infinite; }
@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
</style>