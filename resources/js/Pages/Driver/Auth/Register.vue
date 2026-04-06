<script setup>
import { ref, computed } from 'vue';
import { useForm, Link, Head } from '@inertiajs/vue3';
import { VueTelInput } from 'vue-tel-input';
import 'vue-tel-input/vue-tel-input.css';
import ShopLayout from '@/Layouts/ShopLayout.vue';
import BaseInput from '@/Components/Base/BaseInput.vue';
import { 
    Truck, Bike, Car, User, ArrowRight, ArrowLeft, 
    CheckCircle, Lock, Mail, FileText, Upload, Camera
} from 'lucide-vue-next';

// --- ESTADO DE NAVEGACIÓN ---
const currentStep = ref(1);
const validatingStep1 = ref(false);
const ciPreview = ref(null);
const licensePreview = ref(null);

const steps = [
    { id: 1, title: 'Cuenta', icon: Lock },
    { id: 2, title: 'Perfil', icon: User },
    { id: 3, title: 'Documentos', icon: FileText },
    { id: 4, title: 'Identidad', icon: CheckCircle },
];

// --- FORMULARIO INERTIA ---
const form = useForm({
    phone: '', 
    email: '', 
    password: '', 
    password_confirmation: '', 
    first_name: '', 
    last_name: '', 
    license_number: '', 
    license_plate: '', 
    vehicle_type: 'moto',
    ci_front: null,      // Debe coincidir con RegisterRequest
    license_photo: null, // Debe coincidir con RegisterRequest
    avatar_type: 'icon', 
    avatar_source: 'avatar_1.png'
});

// --- LÓGICA DE ARCHIVOS ---
const handleFileUpload = (event, field) => {
    form.clearErrors(field); // Limpia error previo al intentar subir de nuevo
    const file = event.target.files[0];
    if (!file) return;

    // Validación de peso frontend (4MB = 4 * 1024 * 1024)
    if (file.size > 4194304) {
        form.setError(field, 'La imagen supera los 4MB permitidos.');
        return;
    }

    form[field] = file;
    
    // Generar vista previa
    const reader = new FileReader();
    reader.onload = (e) => {
        if (field === 'ci_front') ciPreview.value = e.target.result;
        if (field === 'license_photo') licensePreview.value = e.target.result;
    };
    reader.readAsDataURL(file);
};

// --- VALIDACIÓN Y FLUJO ---
const nextStep = () => {
    form.clearErrors();

    if (currentStep.value === 1) {
        // Validación local básica
        if (!form.phone || !form.email || !form.password) {
            form.setError('email', 'Completa los campos obligatorios.');
            return;
        }
        
        validatingStep1.value = true;
        // Backend valida unicidad y contraseñas
        form.post(route('driver.register.validate-step-1'), {
            preserveScroll: true,
            onSuccess: () => {
                currentStep.value = 2;
                validatingStep1.value = false;
            },
            onError: () => validatingStep1.value = false,
        });

    } else if (currentStep.value === 2) {
        if (form.first_name && form.last_name && form.license_number && form.license_plate) {
            currentStep.value = 3;
        } else {
            form.setError('first_name', 'Todos los campos son obligatorios.');
        }

    } else if (currentStep.value === 3) {
        if (form.ci_front && form.license_photo) {
            currentStep.value = 4;
        } else {
            form.setError('ci_front', 'Debes cargar ambos documentos.');
        }
    }
};

const submit = () => {
    form.post(route('driver.register.store'), { 
        preserveScroll: true,
        forceFormData: true, // Habilita el multipart/form-data
    });
};

// --- HELPERS ---
const vehicleTypes = [
    { id: 'moto', label: 'Moto', icon: Bike },
    { id: 'car', label: 'Auto', icon: Car },
    { id: 'truck', label: 'Camión', icon: Truck }
];

const progressPercentage = computed(() => (currentStep.value / steps.length) * 100);
</script>

<template>
    <Head title="Registro de Conductor" />

    <ShopLayout>
        <div class="min-h-screen flex items-center justify-center p-4 bg-muted/30 pt-24 pb-12">
            <div class="w-full max-w-xl bg-card border rounded-[2.5rem] shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-500">
                
                <div class="p-8 bg-muted/20 border-b relative">
                    <div class="flex justify-between items-center mb-6">
                        <div class="space-y-1">
                            <h2 class="text-3xl font-black italic uppercase tracking-tighter">{{ steps[currentStep-1].title }}</h2>
                            <p class="text-[10px] font-bold uppercase tracking-widest text-amber-600">Paso {{ currentStep }} de 4</p>
                        </div>
                        <div class="w-12 h-12 bg-amber-500 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-amber-500/20">
                            <component :is="steps[currentStep-1].icon" :size="24" />
                        </div>
                    </div>
                    <div class="h-1.5 w-full bg-muted rounded-full overflow-hidden">
                        <div class="h-full bg-amber-500 transition-all duration-500" :style="{ width: progressPercentage + '%' }" />
                    </div>
                </div>

                <div class="p-8">
                    <div v-show="currentStep === 1" class="space-y-5 animate-in slide-in-from-right-4">
                        <div class="space-y-1">
                            <label class="text-[10px] font-black uppercase text-muted-foreground ml-1">Celular (WhatsApp)</label>
                            <vue-tel-input v-model="form.phone" mode="international" :defaultCountry="'BO'" class="driver-tel-input" />
                            <p v-if="form.errors.phone" class="text-[10px] text-destructive font-bold uppercase ml-1">{{ form.errors.phone }}</p>
                        </div>
                        
                        <BaseInput v-model="form.email" label="Correo Electrónico" type="email" :error="form.errors.email" :icon="Mail" />
                        
                        <div class="grid grid-cols-2 gap-4">
                            <BaseInput v-model="form.password" label="Clave" type="password" :error="form.errors.password" :icon="Lock" />
                            <BaseInput v-model="form.password_confirmation" label="Confirmar Clave" type="password" :icon="Lock" />
                        </div>
                    </div>

                    <div v-show="currentStep === 2" class="space-y-6 animate-in slide-in-from-right-4">
                        <div v-if="form.errors.first_name" class="p-3 bg-destructive/10 border border-destructive/20 text-destructive text-[10px] font-black uppercase rounded-xl">
                            {{ form.errors.first_name }}
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <BaseInput v-model="form.first_name" label="Nombres Legales" :error="form.errors.first_name" />
                            <BaseInput v-model="form.last_name" label="Apellidos" :error="form.errors.last_name" />
                        </div>

                        <div class="space-y-3">
                            <label class="text-[10px] font-black uppercase text-muted-foreground ml-1">Unidad de Transporte</label>
                            <div class="grid grid-cols-3 gap-3">
                                <button v-for="v in vehicleTypes" :key="v.id" type="button" @click="form.vehicle_type = v.id"
                                    class="flex flex-col items-center p-4 rounded-3xl border-2 transition-all"
                                    :class="form.vehicle_type === v.id ? 'border-amber-500 bg-amber-500/5 text-amber-600 shadow-sm' : 'border-border text-muted-foreground opacity-60'">
                                    <component :is="v.icon" :size="24" />
                                    <span class="text-[9px] font-black uppercase mt-2">{{ v.label }}</span>
                                </button>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <BaseInput v-model="form.license_number" label="Nº Licencia" :error="form.errors.license_number" />
                            <BaseInput v-model="form.license_plate" label="Placa" :error="form.errors.license_plate" />
                        </div>
                    </div>

                    <div v-show="currentStep === 3" class="space-y-6 animate-in slide-in-from-right-4">
                        <div v-if="form.errors.ci_front" class="p-3 bg-destructive/10 border border-destructive/20 text-destructive text-[10px] font-black uppercase rounded-xl">
                            {{ form.errors.ci_front }}
                        </div>

                        <div class="grid grid-cols-1 gap-6">
                            <div class="space-y-2">
                                <label class="text-[10px] font-black uppercase text-muted-foreground ml-1">Cédula de Identidad (Anverso)</label>
                                <div @click="$refs.ciInput.click()" class="relative h-40 rounded-[2rem] border-2 border-dashed border-border flex flex-col items-center justify-center cursor-pointer hover:bg-muted/50 transition-all overflow-hidden group">
                                    <img v-if="ciPreview" :src="ciPreview" class="absolute inset-0 w-full h-full object-cover" />
                                    <template v-else>
                                        <Camera class="text-muted-foreground mb-2 group-hover:text-amber-500 transition-colors" />
                                        <span class="text-[10px] font-black uppercase opacity-60 group-hover:text-amber-500 transition-colors">Subir Imagen JPG/PNG</span>
                                    </template>
                                </div>
                                <input type="file" ref="ciInput" class="hidden" @change="e => handleFileUpload(e, 'ci_front')" accept="image/jpeg, image/png, image/jpg" />
                            </div>

                            <div class="space-y-2">
                                <label class="text-[10px] font-black uppercase text-muted-foreground ml-1">Licencia de Conducir (Vigente)</label>
                                <div @click="$refs.licenseInput.click()" class="relative h-40 rounded-[2rem] border-2 border-dashed border-border flex flex-col items-center justify-center cursor-pointer hover:bg-muted/50 transition-all overflow-hidden group">
                                    <img v-if="licensePreview" :src="licensePreview" class="absolute inset-0 w-full h-full object-cover" />
                                    <template v-else>
                                        <Upload class="text-muted-foreground mb-2 group-hover:text-amber-500 transition-colors" />
                                        <span class="text-[10px] font-black uppercase opacity-60 group-hover:text-amber-500 transition-colors">Subir Imagen JPG/PNG</span>
                                    </template>
                                </div>
                                <input type="file" ref="licenseInput" class="hidden" @change="e => handleFileUpload(e, 'license_photo')" accept="image/jpeg, image/png, image/jpg" />
                            </div>
                        </div>
                    </div>

                    <div v-show="currentStep === 4" class="space-y-8 animate-in slide-in-from-right-4 text-center">
                        <div class="w-32 h-32 rounded-[2.5rem] bg-amber-500/10 border-4 border-amber-500/20 mx-auto p-2">
                            <img :src="`/assets/avatars/${form.avatar_source}`" class="w-full h-full object-cover rounded-2xl shadow-inner" />
                        </div>
                        <div class="grid grid-cols-4 gap-3 max-w-sm mx-auto">
                            <button v-for="i in 8" :key="i" type="button" 
                                @click="() => { form.avatar_source=`avatar_${i}.svg`; }"
                                class="aspect-square rounded-2xl border-2 transition-all p-2 flex items-center justify-center"
                                :class="form.avatar_source === `avatar_${i}.svg` ? 'border-amber-500 bg-amber-500/10 scale-105 shadow-md' : 'border-transparent bg-muted opacity-40 hover:opacity-100'">
                                <img :src="`/assets/avatars/avatar_${i}.svg`" class="w-full h-full" />
                            </button>
                        </div>
                        <p class="text-[10px] font-black uppercase text-muted-foreground tracking-widest">Este será tu avatar en la plataforma</p>
                    </div>
                </div>

                <div class="p-8 border-t bg-muted/10">
                    <div class="flex gap-4">
                        <button v-if="currentStep > 1" @click="currentStep--" type="button" class="flex-1 h-14 rounded-2xl border font-black uppercase text-xs hover:bg-muted transition-colors">Atrás</button>
                        <Link v-else :href="route('driver.login')" class="flex-1 h-14 rounded-2xl border font-black uppercase text-xs flex items-center justify-center hover:bg-muted transition-colors">Login</Link>

                        <button v-if="currentStep < 4" @click="nextStep" :disabled="validatingStep1" type="button"
                            class="flex-[2] h-14 bg-amber-500 text-white rounded-2xl font-black uppercase text-xs tracking-widest shadow-xl flex items-center justify-center gap-2 active:scale-95 transition-all disabled:opacity-50">
                            <span v-if="validatingStep1" class="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                            <span v-else class="flex items-center gap-2">Siguiente <ArrowRight :size="16" /></span>
                        </button>

                        <button v-else @click="submit" :disabled="form.processing" type="button"
                            class="flex-[2] h-14 bg-amber-500 text-white rounded-2xl font-black uppercase text-xs tracking-widest shadow-xl flex items-center justify-center gap-2 active:scale-95 transition-all">
                            <span v-if="form.processing" class="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                            <span v-else class="flex items-center gap-2">Enviar Solicitud <CheckCircle :size="16" /></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </ShopLayout>
</template>

<style scoped>
.driver-tel-input {
    height: 56px !important;
    background: hsla(var(--muted), 0.3) !important;
    border: 2px solid transparent !important;
    border-radius: 1rem !important;
    transition: all 0.3s ease;
}
.driver-tel-input:focus-within {
    border-color: hsl(var(--amber-500)) !important;
    background: transparent !important;
}
</style>