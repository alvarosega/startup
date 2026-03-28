<script setup>
import { ref, computed, watch, nextTick, onMounted } from 'vue'; 
import { useForm, Link, Head } from '@inertiajs/vue3';
import { VueTelInput } from 'vue-tel-input';
import 'vue-tel-input/vue-tel-input.css';

import ClientLocationPicker from '@/Components/Customer/Maps/ClientLocationPicker.vue'; 

// Componentes de Leaflet
import { LMap, LTileLayer, LMarker } from "@vue-leaflet/vue-leaflet";
import L from 'leaflet';

import { 
    UserPlus, Lock, Mail, Upload, ArrowRight, ArrowLeft,
    CheckCircle, Home, Briefcase, Users, MoreHorizontal, Navigation2, MapPin, Smartphone
} from 'lucide-vue-next';

const props = defineProps({ activeBranches: Array });

const mapComponentRef = ref(null);
const previewMapRef = ref(null); 
const currentStep = ref(1);

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

// --- SEGURIDAD: LLAVE DE IDEMPOTENCIA ---
const registrationId = ref(null);
onMounted(() => {
    registrationId.value = crypto.randomUUID();
});

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
    avatar_source: null, // REGLA: Sin predefinición para obligar selección
    avatar_file: null,
    guest_client_uuid: localStorage.getItem('guest_client_uuid')
});

const previewIcon = L.divIcon({
    className: 'preview-pin',
    html: `<div class="w-8 h-8 bg-primary rounded-full border-[3px] border-white shadow-2xl flex items-center justify-center text-white"><div class="w-2 h-2 bg-white rounded-full animate-ping"></div></div>`,
    iconSize: [32, 32],
    iconAnchor: [16, 16]
});

const onInput = (phone, obj) => { 
    isPhoneValid.value = obj?.valid ?? false; 
    if (obj?.country?.iso2) form.country_code = obj.country.iso2.toUpperCase();
    if (obj?.number) form.phone = obj.number; 
    form.clearErrors('phone');
};
const nextStep = async () => { // <--- Convertir a async
    if (currentStep.value === 1) {
        if (!isPhoneValid.value && form.phone.length > 0) {
            form.setError('phone', 'Formato de número inválido.');
            return;
        }
        
        if (validatingStep1.value) return;
        validatingStep1.value = true;
        form.clearErrors(); // Limpiar errores antes de intentar de nuevo
        
        try {
            // EJECUCIÓN DE VALIDACIÓN DE IDENTIDAD GLOBAL vía AXIOS
            await axios.post(route('customer.register.validate-step-1'), {
                first_name: form.first_name,
                last_name: form.last_name,
                phone: form.phone,
                country_code: form.country_code,
                email: form.email,
                password: form.password,
                password_confirmation: form.password_confirmation
            });

            // Si Axios no lanza excepción, significa que Laravel devolvió HTTP 200 OK.
            currentStep.value = 2; // Avance autorizado
            
            // Recalcular mapa después de la transición
            nextTick(() => {
                setTimeout(() => {
                    if (mapComponentRef.value) mapComponentRef.value.fixMapLayout();
                }, 500);
            });

        } catch (error) {
            // Si Laravel detecta un error de validación, devuelve HTTP 422
            if (error.response && error.response.status === 422) {
                // Inyectar los errores de Laravel de vuelta al objeto form de Inertia
                const validationErrors = error.response.data.errors;
                for (const field in validationErrors) {
                    form.setError(field, validationErrors[field][0]);
                }
            } else {
                console.error("Fallo crítico de validación:", error);
            }
        } finally {
            validatingStep1.value = false;
        }
        
    } else if (currentStep.value === 2) {
        if (form.address === 'Calculando...' || !form.address) return;
        form.latitude = parseFloat(form.latitude);
        form.longitude = parseFloat(form.longitude);
        currentStep.value = 3;
        nextTick(() => {
            setTimeout(() => {
                if (previewMapRef.value?.leafletObject) {
                    const map = previewMapRef.value.leafletObject;
                    map.invalidateSize(); 
                    map.setView([form.latitude, form.longitude], 17);
                }
            }, 300); 
        });
    } else {
        currentStep.value++;
    }
};

const submit = () => {
    if (form.processing || !form.avatar_source) return; // Guarda de seguridad final
    
    form.post(route('customer.register'), {
        preserveScroll: true,
        forceFormData: true, 
        headers: { 'X-Idempotency-Key': registrationId.value }
    });
};

const progressWidth = computed(() => (currentStep.value / steps.length) * 100);

watch(() => form.latitude, (val) => { if(val) form.latitude = parseFloat(val); });
watch(() => form.longitude, (val) => { if(val) form.longitude = parseFloat(val); });
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
                    <h1 class="text-4xl font-black tracking-tighter uppercase italic mb-2 italic">Registro</h1>
                    <p class="text-muted-foreground text-[10px] font-bold uppercase tracking-[0.2em]">Paso 1: Identidad y Seguridad</p>
                </div>

                <div class="w-full space-y-5">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground ml-1">Nombres</label>
                            <input v-model="form.first_name" type="text" class="w-full h-14 bg-foreground/5 border-none rounded-2xl px-5 text-sm font-bold focus:ring-2 focus:ring-primary/20 transition-all" placeholder="Juan">
                            <p v-if="form.errors.first_name" class="text-[9px] text-destructive font-bold uppercase ml-1">{{ form.errors.first_name }}</p>
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground ml-1">Apellidos</label>
                            <input v-model="form.last_name" type="text" class="w-full h-14 bg-foreground/5 border-none rounded-2xl px-5 text-sm font-bold focus:ring-2 focus:ring-primary/20 transition-all" placeholder="Pérez">
                            <p v-if="form.errors.last_name" class="text-[9px] text-destructive font-bold uppercase ml-1">{{ form.errors.last_name }}</p>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground ml-1">Celular</label>
                        <vue-tel-input v-model="form.phone" @on-input="onInput" mode="international" :defaultCountry="'BO'" 
                                       class="custom-tel-input-full" :class="{ 'error-border': form.errors.phone }" />
                        <div v-if="form.errors.phone" class="text-[10px] text-destructive font-black uppercase mt-1 ml-1">{{ form.errors.phone }}</div>
                    </div>

                    <div class="space-y-1">
                        <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground ml-1">Email</label>
                        <input v-model="form.email" type="email" class="w-full h-14 bg-foreground/5 border-none rounded-2xl px-5 text-sm font-bold focus:ring-2 focus:ring-primary/20 transition-all" placeholder="usuario@dominio.com">
                        <p v-if="form.errors.email" class="text-[9px] text-destructive font-bold uppercase ml-1">{{ form.errors.email }}</p>
                    </div>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground ml-1">Contraseña</label>
                            <input v-model="form.password" type="password" class="w-full h-14 bg-foreground/5 border-none rounded-2xl px-5 text-sm font-bold focus:ring-2 focus:ring-primary/20 transition-all">
                            <p v-if="form.errors.password" class="text-[9px] text-destructive font-bold uppercase ml-1">{{ form.errors.password }}</p>
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground ml-1">Confirmar</label>
                            <input v-model="form.password_confirmation" type="password" class="w-full h-14 bg-foreground/5 border-none rounded-2xl px-5 text-sm font-bold focus:ring-2 focus:ring-primary/20 transition-all">
                        </div>
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
                    <h1 class="text-4xl font-black tracking-tighter uppercase italic mb-2 italic">Logística</h1>
                    <p class="text-muted-foreground text-[10px] font-bold uppercase tracking-[0.2em]">Paso 3: Detalles de entrega</p>
                </div>

                <div class="w-full space-y-6">
                    <div class="w-full rounded-[2.5rem] border-4 border-foreground/5 overflow-hidden bg-foreground/5 shadow-lg group">
                        <div class="relative w-full h-40 border-b border-foreground/5">
                            <l-map ref="previewMapRef" :key="`preview-${form.latitude}-${form.longitude}`" :zoom="17" :center="[form.latitude || -16.5, form.longitude || -68.15]" :options="{ zoomControl: false, attributionControl: false, dragging: false, scrollWheelZoom: false, touchZoom: false }" class="h-full w-full grayscale-[0.3] contrast-[1.1]">
                                <l-tile-layer url="https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png" />
                                <l-marker :lat-lng="[form.latitude || -16.5, form.longitude || -68.15]" :icon="previewIcon" />
                            </l-map>
                            <div class="absolute top-3 right-3 bg-primary text-white px-3 py-1 rounded-full text-[8px] font-black uppercase tracking-tighter shadow-lg z-[1000]">GPS_OK</div>
                        </div>
                        <div class="p-5 flex items-start gap-4 bg-background/40 backdrop-blur-md">
                            <div class="w-10 h-10 rounded-2xl bg-primary/10 flex items-center justify-center text-primary shrink-0 shadow-inner">
                                <Navigation2 :size="20" fill="currentColor" />
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-primary mb-1 leading-none">Dirección Confirmada</p>
                                <p class="text-sm font-bold text-foreground leading-snug">{{ form.address || 'Ubicación seleccionada' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <label class="text-[10px] font-black uppercase text-muted-foreground ml-1">Etiquetar lugar como:</label>
                        <div class="grid grid-cols-4 gap-3">
                            <button v-for="p in presets" :key="p.id" type="button" @click="form.alias = p.label" class="flex flex-col items-center justify-center gap-2 p-4 rounded-2xl border-2 transition-all" :class="form.alias === p.label ? 'border-primary bg-primary/10 text-primary shadow-lg scale-105' : 'border-foreground/5 bg-foreground/5 text-muted-foreground opacity-60 hover:opacity-100'">
                                <component :is="p.icon" :size="20" stroke-width="2.5" />
                                <span class="text-[9px] font-black uppercase">{{ p.label }}</span>
                            </button>
                        </div>
                        <p v-if="form.errors.alias" class="text-[10px] text-destructive font-bold uppercase ml-1">⚠️ {{ form.errors.alias }}</p>
                    </div>

                    <div class="space-y-4">
                        <div class="space-y-1">
                            <label class="text-[10px] font-black uppercase text-muted-foreground ml-1">Nombre Personalizado</label>
                            <input v-model="form.alias" type="text" class="w-full h-14 bg-foreground/5 border-none rounded-2xl px-5 text-sm font-bold" placeholder="Ej: Oficina de la esquina">
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-black uppercase text-muted-foreground ml-1">Notas de Acceso</label>
                            <input v-model="form.details" type="text" class="w-full h-14 bg-foreground/5 border-none rounded-2xl px-5 text-sm font-bold" placeholder="Piso 4, puerta azul...">
                        </div>
                    </div>
                </div>
            </div>

            <div v-show="currentStep === 4" class="flex-1 flex flex-col items-center justify-center p-8 max-w-xl mx-auto w-full animate-in fade-in slide-in-from-right-8 duration-700">
                <div class="text-center mb-10">
                    <h1 class="text-4xl font-black tracking-tighter uppercase italic mb-2 italic">Perfil</h1>
                    <p class="text-muted-foreground text-[10px] font-bold uppercase tracking-[0.2em]">Paso 4: Tu identidad visual</p>
                </div>

                <div class="relative mb-12">
                    <div class="w-44 h-44 rounded-[3rem] border-[6px] border-foreground/5 p-2 bg-foreground/5 overflow-hidden shadow-2xl flex items-center justify-center">
                        <template v-if="form.avatar_source">
                            <img v-if="form.avatar_type === 'custom' && customPreview" :src="customPreview" class="w-full h-full object-cover rounded-[2.2rem]" />
                            <img v-else :src="`/assets/avatars/${form.avatar_source}`" class="w-full h-full object-cover rounded-[2.2rem]" />
                        </template>
                        <div v-else class="text-foreground/20 flex flex-col items-center gap-2">
                            <UserPlus :size="48" stroke-width="1.5" />
                            <span class="text-[8px] font-black uppercase tracking-widest">Selecciona un icono</span>
                        </div>
                    </div>
                    <label for="reg_avatar_upload" class="absolute -bottom-2 -right-2 p-4 bg-primary text-white rounded-[20px] cursor-pointer shadow-xl border-4 border-background hover:scale-110 transition-transform">
                        <Upload :size="20" stroke-width="3" />
                    </label>
                    <input type="file" id="reg_avatar_upload" class="hidden" @change="(e) => { const file = e.target.files[0]; if(file) { form.avatar_type='custom'; form.avatar_file=file; customPreview = URL.createObjectURL(file); form.avatar_source = file.name; } }">
                </div>

                <div class="grid grid-cols-4 gap-4 w-full">
                    <button type="button" v-for="i in 8" :key="i" @click="() => { form.avatar_type = 'icon'; form.avatar_source = `avatar_${i}.png`; customPreview = null; }" class="aspect-square rounded-2xl border-2 flex items-center justify-center p-3 transition-all" :class="form.avatar_source === `avatar_${i}.png` ? 'border-primary bg-primary/10 scale-105 shadow-lg' : 'border-transparent bg-foreground/5 opacity-40 hover:opacity-100'">
                        <img :src="`/assets/avatars/avatar_${i}.png`" class="w-full h-full" alt="Avatar option" />
                    </button>
                </div>
            </div>

            <div v-if="currentStep !== 2" class="p-8 pb-12 flex gap-4 max-w-xl mx-auto w-full relative z-50">
                <button v-if="currentStep > 1" @click="currentStep--" class="h-16 px-8 rounded-3xl bg-foreground/5 text-foreground font-black uppercase text-xs hover:bg-foreground/10 transition-colors">Atrás</button>
                <Link v-else :href="route('customer.login')" class="h-16 px-8 rounded-3xl bg-foreground/5 text-foreground font-black uppercase text-xs flex items-center">Login</Link>

                <button v-if="currentStep < steps.length" @click="nextStep" :disabled="validatingStep1" 
                        class="flex-1 h-16 bg-primary text-white rounded-3xl font-black uppercase text-xs tracking-widest shadow-xl flex items-center justify-center gap-3 active:scale-95 transition-all">
                    <span v-if="validatingStep1" class="w-6 h-6 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                    <template v-else>Siguiente <ArrowRight :size="18" stroke-width="3" /></template>
                </button>

                <button v-if="currentStep === 4" @click="submit" :disabled="form.processing || !form.avatar_source" 
                        class="flex-1 h-16 bg-primary text-white rounded-3xl font-black uppercase text-xs tracking-widest shadow-xl flex items-center justify-center gap-3 active:scale-95 transition-all disabled:opacity-30 disabled:cursor-not-allowed">
                    <span v-if="form.processing" class="w-6 h-6 border-2 border-white/30 border-t-black rounded-full animate-spin"></span>
                    <template v-else>Finalizar <CheckCircle :size="18" stroke-width="3" /></template>
                </button>
            </div>

        </main>
    </div>
</template>

<style>
.error-border { border-color: hsl(var(--destructive)) !important; background-color: hsla(var(--destructive), 0.05) !important; }
.custom-tel-input-full { height: 60px !important; background: rgba(var(--foreground), 0.05) !important; border: 2px solid transparent !important; border-radius: 20px !important; transition: all 0.3s ease; }
.custom-tel-input-full:focus-within { border-color: rgba(var(--primary), 0.5) !important; }
.custom-tel-input-full .vti__input { background: transparent !important; font-weight: 800 !important; font-size: 16px !important; color: inherit !important;}
.custom-tel-input-full .vti__dropdown { border-radius: 20px 0 0 20px !important; }
::-webkit-scrollbar { display: none; }
</style>