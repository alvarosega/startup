<script setup>
import { ref, computed, watch, nextTick } from 'vue'; 
import { useForm, Link, Head } from '@inertiajs/vue3';
import { VueTelInput } from 'vue-tel-input';
import 'vue-tel-input/vue-tel-input.css';
import BaseInput from '@/Components/Base/BaseInput.vue';
import ClientLocationPicker from '@/Components/Maps/ClientLocationPicker.vue';

// Importamos componentes de Leaflet para la previsualización estática del Paso 3
import { LMap, LTileLayer, LMarker } from "@vue-leaflet/vue-leaflet";
import L from 'leaflet';

import { 
    UserPlus, Lock, Mail, Upload, ArrowRight, ArrowLeft,
    CheckCircle, Home, Briefcase, Users, MoreHorizontal, Navigation2, MapPin
} from 'lucide-vue-next';

const props = defineProps({ activeBranches: Array });

// --- REFERENCIAS ---
const mapComponentRef = ref(null);
const previewMapRef = ref(null); 
const currentStep = ref(1);

// --- PASOS Y PRESETS ---
const steps = [
    { id: 1, title: 'CUENTA' },
    { id: 2, title: 'UBICACIÓN' },
    { id: 3, title: 'DETALLES' },
    { id: 4, title: 'PERFIL' },
];

const presets = [
    { id: 'casa', label: 'Casa', icon: Home },
    { id: 'trabajo', label: 'Trabajo', icon: Briefcase },
    { id: 'amigos', label: 'Amigos', icon: Users },
    { id: 'otro', label: 'Otro', icon: MoreHorizontal },
];

const validatingStep1 = ref(false);
const customPreview = ref(null);
const isPhoneValid = ref(false);

// --- FORMULARIO ESTATAL ---
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
    avatar_source: 'avatar_1.svg', 
    avatar_file: null,
    guest_client_uuid: localStorage.getItem('guest_client_uuid')
});

// Icono personalizado para el "print" de ubicación
const previewIcon = L.divIcon({
    className: 'preview-pin',
    html: `<div class="w-8 h-8 bg-primary rounded-full border-[3px] border-white shadow-2xl flex items-center justify-center text-white"><div class="w-2 h-2 bg-white rounded-full animate-ping"></div></div>`,
    iconSize: [32, 32],
    iconAnchor: [16, 16]
});

// --- EVENTOS ---
const onInput = (phone, obj) => { 
    isPhoneValid.value = obj?.valid ?? false; 
    if (obj?.country?.iso2) form.country_code = obj.country.iso2.toUpperCase();
    if (obj?.number) form.phone = obj.number; 
    form.clearErrors('phone');
};

const nextStep = () => {
    if (currentStep.value === 1) {
        if (!isPhoneValid.value && form.phone.length > 0) {
            form.setError('phone', 'Formato de número inválido.');
            return;
        }
        if (validatingStep1.value) return;
        validatingStep1.value = true;
        
        // VALIDACIÓN BACKEND PASO 1
        form.post(route('register.validate-step-1'), {
            preserveScroll: true,
            only: ['first_name', 'last_name', 'phone', 'email', 'password', 'password_confirmation'],
            onSuccess: () => {
                form.clearErrors();
                currentStep.value = 2;
                validatingStep1.value = false;
                // PARCHE: Sincronizar dimensiones del mapa interactivo
                setTimeout(() => mapComponentRef.value?.fixMapLayout(), 400);
            },
            onError: () => { validatingStep1.value = false; }
        });
    } else if (currentStep.value === 2) {
        if (form.address === 'Calculando...' || !form.address) return;

        // ASEGURAR QUE LOS DATOS SEAN NUMÉRICOS PARA EL "PRINT"
        form.latitude = parseFloat(form.latitude);
        form.longitude = parseFloat(form.longitude);
        
        currentStep.value = 3;

        // REFRESCAR EL MINI-MAPA DE CONFIRMACIÓN
        nextTick(() => {
            setTimeout(() => {
                if (previewMapRef.value?.leafletObject) {
                    const map = previewMapRef.value.leafletObject;
                    map.invalidateSize(); // Corrige el tamaño (evita cuadros grises)
                    map.setView([form.latitude, form.longitude], 17); // Enfoca la captura
                }
            }, 300); // Esperamos a que termine la animación de entrada del paso 3
        });
    } else {
        currentStep.value++;
    }
};

const submit = () => {
    if (form.processing) return;
    form.post(route('register'), {
        preserveScroll: true,
        forceFormData: true 
    });
};

const progressWidth = computed(() => (currentStep.value / steps.length) * 100);

// --- WATCHERS DE SEGURIDAD ---
// Aseguramos que latitude/longitude siempre sean Float para Leaflet
watch(() => form.latitude, (val) => form.latitude = parseFloat(val));
watch(() => form.longitude, (val) => form.longitude = parseFloat(val));
</script>

<template>
    <Head title="Registro de Socio" />

    <div class="min-h-[100svh] bg-background text-foreground flex flex-col overflow-hidden selection:bg-primary/20">
        
        <div class="fixed top-0 left-0 w-full h-1 z-[1000] bg-foreground/5">
            <div class="h-full bg-primary shadow-[0_0_15px_rgba(var(--primary),0.5)] transition-all duration-1000 ease-out"
                 :style="{ width: progressWidth + '%' }"></div>
        </div>

        <main class="flex-1 flex flex-col relative h-full">
            
            <div v-show="currentStep === 1" class="flex-1 flex flex-col items-center justify-center p-8 max-w-xl mx-auto w-full animate-in fade-in slide-in-from-bottom-4 duration-700">
                <div class="text-center mb-8">
                    <h1 class="text-4xl font-black tracking-tighter uppercase italic mb-2">Comienza ahora</h1>
                    <p class="text-muted-foreground text-[10px] font-bold uppercase tracking-[0.2em]">Paso 1: Identidad y Seguridad</p>
                </div>

                <div class="w-full space-y-5">
                    <div class="grid grid-cols-2 gap-4">
                        <BaseInput v-model="form.first_name" label="Nombres" placeholder="Juan" :error="form.errors.first_name" />
                        <BaseInput v-model="form.last_name" label="Apellidos" placeholder="Pérez" :error="form.errors.last_name" />
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground ml-1">Celular</label>
                        <vue-tel-input v-model="form.phone" @on-input="onInput" mode="international" :defaultCountry="'BO'" 
                                       class="custom-tel-input-full" :class="{ 'error-border': form.errors.phone }" />
                        <div v-if="form.errors.phone" class="text-[10px] text-destructive font-black uppercase mt-1 ml-1">{{ form.errors.phone }}</div>
                    </div>

                    <BaseInput v-model="form.email" type="email" label="Email" placeholder="usuario@dominio.com" :error="form.errors.email" />
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <BaseInput v-model="form.password" type="password" label="Contraseña" :error="form.errors.password" />
                        <BaseInput v-model="form.password_confirmation" type="password" label="Confirmar" />
                    </div>
                </div>
            </div>

            <div v-show="currentStep === 2" class="flex-1 relative flex flex-col h-full w-full animate-in fade-in duration-500">
                <div class="absolute inset-0">
                    <ClientLocationPicker 
                        ref="mapComponentRef"
                        v-model:modelValueLat="form.latitude" 
                        v-model:modelValueLng="form.longitude" 
                        v-model:modelValueAddress="form.address" 
                        v-model:modelValueBranchId="form.branch_id" 
                        :activeBranches="props.activeBranches" 
                    />
                </div>
                
                <div class="mt-auto z-[1001] p-6 animate-in slide-in-from-bottom-full duration-700 delay-200">
                    <div class="bg-background/95 backdrop-blur-3xl border border-white/10 rounded-[32px] p-6 shadow-2xl max-w-lg mx-auto pointer-events-auto">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-8 h-8 bg-primary/20 rounded-full flex items-center justify-center text-primary">
                                <Navigation2 :size="16" fill="currentColor" />
                            </div>
                            <span class="text-[10px] font-black uppercase tracking-widest text-primary">Punto de Entrega</span>
                        </div>
                        <p class="text-lg font-bold leading-tight min-h-[3rem] line-clamp-2 mb-4">{{ form.address || 'Esperando ubicación...' }}</p>
                        <button @click="nextStep" :disabled="!form.address || form.address === 'Calculando...'" 
                                class="w-full h-14 bg-foreground text-background rounded-2xl font-black uppercase text-xs tracking-widest active:scale-95 transition-all disabled:opacity-30">
                            Confirmar Ubicación
                        </button>
                    </div>
                </div>
            </div>

            <div v-show="currentStep === 3" class="flex-1 flex flex-col items-center justify-center p-8 max-w-xl mx-auto w-full animate-in fade-in slide-in-from-right-8 duration-700">
                <div class="text-center mb-6">
                    <h1 class="text-4xl font-black tracking-tighter uppercase italic mb-2">Confirmación</h1>
                    <p class="text-muted-foreground text-[10px] font-bold uppercase tracking-[0.2em]">Paso 3: Detalles de entrega</p>
                </div>

                <div class="w-full space-y-6">
                    <div class="w-full rounded-[2.5rem] border-4 border-foreground/5 overflow-hidden bg-foreground/5 shadow-lg group">
                        
                        <div class="relative w-full h-40 border-b border-foreground/5">
                            <l-map ref="previewMapRef"
                                   :key="`preview-${form.latitude}-${form.longitude}`" 
                                   :zoom="17" 
                                   :center="[form.latitude || -16.5, form.longitude || -68.15]"
                                   :options="{ zoomControl: false, attributionControl: false, dragging: false, scrollWheelZoom: false, touchZoom: false }"
                                   class="h-full w-full grayscale-[0.3] contrast-[1.1]">
                                <l-tile-layer url="https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png" />
                                <l-marker :lat-lng="[form.latitude || -16.5, form.longitude || -68.15]" :icon="previewIcon" />
                            </l-map>
                            
                            <div class="absolute top-3 right-3 bg-primary text-white px-3 py-1 rounded-full text-[8px] font-black uppercase tracking-tighter shadow-lg z-[1000]">
                                GPS_OK
                            </div>
                        </div>

                        <div class="p-5 flex items-start gap-4 bg-background/40 backdrop-blur-md">
                            <div class="w-10 h-10 rounded-2xl bg-primary/10 flex items-center justify-center text-primary shrink-0 shadow-inner">
                                <Navigation2 :size="20" fill="currentColor" />
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-primary mb-1 leading-none">
                                    Dirección Confirmada
                                </p>
                                <p class="text-sm font-bold text-foreground leading-snug">
                                    {{ form.address || 'Ubicación seleccionada' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <label class="text-[10px] font-black uppercase text-muted-foreground ml-1">Etiquetar lugar como:</label>
                        <div class="grid grid-cols-4 gap-3">
                            <button v-for="p in presets" :key="p.id" type="button" @click="form.alias = p.label"
                                    class="flex flex-col items-center justify-center gap-2 p-4 rounded-2xl border-2 transition-all"
                                    :class="form.alias === p.label ? 'border-primary bg-primary/10 text-primary shadow-lg scale-105' : 'border-foreground/5 bg-foreground/5 text-muted-foreground opacity-60 hover:opacity-100'">
                                <component :is="p.icon" :size="20" stroke-width="2.5" />
                                <span class="text-[9px] font-black uppercase">{{ p.label }}</span>
                            </button>
                        </div>
                        <p v-if="form.errors.alias" class="text-[10px] text-destructive font-bold uppercase ml-1 animate-bounce">⚠️ {{ form.errors.alias }}</p>
                    </div>

                    <div class="space-y-4">
                        <BaseInput v-model="form.alias" label="Nombre Personalizado" placeholder="Ej: Oficina de la esquina" :error="form.errors.alias" />
                        <BaseInput v-model="form.details" label="Notas de Acceso" placeholder="Piso 4, puerta azul, tocar fuerte..." :error="form.errors.details" />
                    </div>
                </div>
            </div>

            <div v-show="currentStep === 4" class="flex-1 flex flex-col items-center justify-center p-8 max-w-xl mx-auto w-full animate-in fade-in slide-in-from-right-8 duration-700">
                <div class="text-center mb-10">
                    <h1 class="text-4xl font-black tracking-tighter uppercase italic mb-2">Finalizar</h1>
                    <p class="text-muted-foreground text-[10px] font-bold uppercase tracking-[0.2em]">Paso 4: Tu identidad visual</p>
                </div>

                <div class="relative mb-12">
                    <div class="w-44 h-44 rounded-[3rem] border-[6px] border-foreground/5 p-2 bg-foreground/5 overflow-hidden shadow-2xl">
                        <img v-if="form.avatar_type === 'custom' && customPreview" :src="customPreview" class="w-full h-full object-cover rounded-[2.2rem]" />
                        <img v-else :src="`/assets/avatars/${form.avatar_source}`" class="w-full h-full object-cover rounded-[2.2rem]" />
                    </div>
                    <label for="reg_avatar_upload" class="absolute -bottom-2 -right-2 p-4 bg-primary text-white rounded-[20px] cursor-pointer shadow-xl border-4 border-background hover:scale-110 transition-transform">
                        <Upload :size="20" stroke-width="3" />
                    </label>
                    <input type="file" id="reg_avatar_upload" class="hidden" @change="(e) => { const file = e.target.files[0]; if(file) { form.avatar_type='custom'; form.avatar_file=file; customPreview = URL.createObjectURL(file); } }">
                </div>

                <div class="grid grid-cols-4 gap-4 w-full">
                    <button type="button" v-for="i in 8" :key="i" @click="() => { form.avatar_type='icon'; form.avatar_source=`avatar_${i}.svg`; customPreview=null; }"
                            class="aspect-square rounded-2xl border-2 flex items-center justify-center p-3 transition-all"
                            :class="form.avatar_source === `avatar_${i}.svg` ? 'border-primary bg-primary/10 scale-105 shadow-lg' : 'border-transparent bg-foreground/5 opacity-40 hover:opacity-100'">
                        <img :src="`/assets/avatars/avatar_${i}.svg`" class="w-full h-full" />
                    </button>
                </div>
            </div>

            <div v-if="currentStep !== 2" class="p-8 pb-12 flex gap-4 max-w-xl mx-auto w-full relative z-50">
                <button v-if="currentStep > 1" @click="currentStep--" class="h-16 px-8 rounded-3xl bg-foreground/5 text-foreground font-black uppercase text-xs hover:bg-foreground/10 transition-colors">Atrás</button>
                <Link v-else :href="route('login')" class="h-16 px-8 rounded-3xl bg-foreground/5 text-foreground font-black uppercase text-xs flex items-center">Login</Link>

                <button v-if="currentStep < steps.length" @click="nextStep" :disabled="validatingStep1" 
                        class="flex-1 h-16 bg-primary text-white rounded-3xl font-black uppercase text-xs tracking-widest shadow-xl flex items-center justify-center gap-3 active:scale-95 transition-all">
                    <span v-if="validatingStep1" class="w-6 h-6 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                    <template v-else>Siguiente <ArrowRight :size="18" stroke-width="3" /></template>
                </button>

                <button v-if="currentStep === 4" @click="submit" :disabled="form.processing" 
                        class="flex-1 h-16 bg-primary text-white rounded-3xl font-black uppercase text-xs tracking-widest shadow-xl flex items-center justify-center gap-3 active:scale-95 transition-all">
                    <span v-if="form.processing" class="w-6 h-6 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                    <template v-else>Finalizar <CheckCircle :size="18" stroke-width="3" /></template>
                </button>
            </div>

        </main>
    </div>
</template>

<style>
.error-border { border-color: hsl(var(--destructive)) !important; background-color: hsla(var(--destructive), 0.05) !important; }
.custom-tel-input-full {
    height: 60px !important;
    background: rgba(var(--foreground), 0.05) !important;
    border: 2px solid transparent !important;
    border-radius: 20px !important;
    transition: all 0.3s ease;
}
.custom-tel-input-full:focus-within { border-color: rgba(var(--primary), 0.5) !important; }
.custom-tel-input-full .vti__input { background: transparent !important; font-weight: 800 !important; font-size: 16px !important; color: inherit !important;}
.custom-tel-input-full .vti__dropdown { border-radius: 20px 0 0 20px !important; }
::-webkit-scrollbar { width: 0px; }
</style>