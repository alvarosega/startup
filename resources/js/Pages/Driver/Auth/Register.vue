<script setup>
import { ref, computed } from 'vue';
import { useForm, Link, Head } from '@inertiajs/vue3';
import axios from 'axios';
import { VueTelInput } from 'vue-tel-input';
import 'vue-tel-input/vue-tel-input.css';

// Componentes Base
import BaseInput from '@/Components/Base/BaseInput.vue';
import BaseCheckbox from '@/Components/Base/BaseCheckbox.vue';
import ImageUploader from '@/Components/Maps/ImageUploader.vue'; 

import { 
    Truck, Bike, Car, User, FileText, 
    ArrowRight, ArrowLeft, CheckCircle, Lock, 
    Smartphone, Mail, Shield, Upload
} from 'lucide-vue-next';

// --- ESTADO ---
const currentStep = ref(1);
const step1Errors = ref({});
const validatingStep1 = ref(false);

const steps = [
    { id: 1, title: 'Cuenta', icon: User },
    { id: 2, title: 'Perfil', icon: Truck },
    { id: 3, title: 'Avatar', icon: User },
];

const form = useForm({
    phone: '',country_code: 'BO', email: '', password: '', password_confirmation: '', terms: false,
    first_name: '', last_name: '', license_number: '', license_plate: '', vehicle_type: 'moto',
    avatar_type: 'icon', avatar_source: 'avatar_1.svg', avatar_file: null,
    role: 'driver'
});

const telOptions = { 
    mode: 'international', 
    defaultCountry: 'BO', 
    dropdownOptions: { showSearchBox: true, showFlags: true }, 
    inputOptions: { placeholder: '77712345', autofocus: true } 
};

const onInput = (phone, obj) => { 
    if(obj?.number) {
        form.phone = obj.number; // Guarda el número completo: +5178710820
        form.country_code = obj.country?.iso2 || 'BO'; // Guarda el ISO: PE, BO, etc.
    }
    if (step1Errors.value.phone) delete step1Errors.value.phone;
    
};

// --- NAVEGACIÓN ---
const nextStep = async () => {
    if (currentStep.value === 1) {
        if (!form.terms) return alert('Debes aceptar los términos');
        step1Errors.value = {};
        validatingStep1.value = true;
        try {
            await axios.post(route('driver.register.validate-step-1'), {
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
        if (!form.first_name || !form.last_name || !form.license_number || !form.license_plate) {
            return alert('Completa todos los datos del vehículo.');
        }
        currentStep.value = 3; 
    }
};

const submit = () => {
    form.post(route('driver.register.store'), { 
        forceFormData: true, 
        preserveScroll: true,
        onError: (errors) => {
            // Esto te dirá exactamente qué campo está fallando (ej: license_number)
            console.error("Errores de validación final:", errors);
            alert("Error de validación: " + Object.values(errors).flat().join(", "));
        }
    });
};
const progressPercentage = computed(() => ((currentStep.value - 1) / (steps.length - 1)) * 100);

const vehicleTypes = [
    { id: 'moto', label: 'Moto', icon: Bike },
    { id: 'car', label: 'Auto', icon: Car },
    { id: 'truck', label: 'Camión', icon: Truck }
];
</script>

<template>
    <Head title="Registro de Conductor - BoliviaLogistics" />

    <div class="min-h-screen bg-slate-950 flex items-center justify-center p-4">
        <div class="w-full max-w-xl bg-white rounded-3xl shadow-2xl overflow-hidden flex flex-col h-[750px]">
            
            <div class="relative pt-8 px-8 pb-4 bg-slate-50 border-b border-slate-200">
                <div class="absolute top-0 left-0 w-full h-1.5 bg-slate-200">
                    <div class="h-full bg-amber-500 transition-all duration-500" :style="{ width: progressPercentage + '%' }"></div>
                </div>

                <div class="flex justify-between items-end">
                    <div>
                        <h2 class="text-2xl font-black text-slate-900 tracking-tighter uppercase italic">
                            {{ steps[currentStep - 1].title }}
                        </h2>
                        <p class="text-xs font-bold text-amber-600 uppercase tracking-widest">Paso {{ currentStep }} de {{ steps.length }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-amber-500 text-white flex items-center justify-center shadow-lg shadow-amber-500/30">
                        <component :is="steps[currentStep - 1].icon" :size="24" />
                    </div>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto p-8 scrollbar-thin">
                <form @submit.prevent="submit" class="h-full flex flex-col">
                    
                    <div v-show="currentStep === 1" class="space-y-5 animate-in slide-in-from-right-4">
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest flex items-center gap-2 ml-1">
                                <Smartphone :size="12" /> Número de Celular
                            </label>
                            <vue-tel-input v-bind="telOptions" v-model="form.phone" @on-input="onInput" class="custom-tel-input" :class="{'!border-red-500': step1Errors.phone}" />
                            <p v-if="step1Errors.phone" class="text-[10px] text-red-500 font-bold mt-1 ml-1">{{ step1Errors.phone[0] }}</p>
                        </div>

                        <BaseInput v-model="form.email" type="email" label="Correo Electrónico *" :error="step1Errors.email ? step1Errors.email[0] : ''">
                            <template #icon><Mail :size="14" /></template>
                        </BaseInput>

                        <div class="grid grid-cols-2 gap-4">
                            <BaseInput v-model="form.password" type="password" label="Contraseña" :error="step1Errors.password ? step1Errors.password[0] : ''"><template #icon><Lock :size="14" /></template></BaseInput>
                            <BaseInput v-model="form.password_confirmation" type="password" label="Confirmar"><template #icon><Lock :size="14" /></template></BaseInput>
                        </div>

                        <div class="p-4 bg-amber-50 rounded-2xl border border-amber-100">
                            <BaseCheckbox v-model="form.terms">
                                <span class="text-xs font-bold text-slate-700">Acepto los <a href="#" class="text-amber-600 underline">Términos de Conductor</a></span>
                            </BaseCheckbox>
                        </div>
                    </div>

                    <div v-show="currentStep === 2" class="space-y-6 animate-in slide-in-from-right-4">
                        <div class="grid grid-cols-2 gap-4">
                            <BaseInput v-model="form.first_name" label="Nombres *" />
                            <BaseInput v-model="form.last_name" label="Apellidos *" />
                        </div>

                        <div class="space-y-3">
                            <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest ml-1">Selecciona tu Vehículo</label>
                            <div class="grid grid-cols-3 gap-3">
                                <button v-for="vehicle in vehicleTypes" :key="vehicle.id" type="button" @click="form.vehicle_type = vehicle.id"
                                    class="flex flex-col items-center justify-center p-4 rounded-2xl border-2 transition-all"
                                    :class="form.vehicle_type === vehicle.id ? 'border-amber-500 bg-amber-50 text-amber-600 shadow-inner' : 'border-slate-100 text-slate-400 hover:border-slate-200'">
                                    <component :is="vehicle.icon" :size="24" />
                                    <span class="text-[10px] font-black uppercase mt-2">{{ vehicle.label }}</span>
                                </button>
                            </div>
                        </div>

                        <div class="bg-slate-900 p-5 rounded-2xl space-y-4 shadow-xl">
                            <div class="flex items-center gap-2 text-amber-500 mb-2">
                                <Shield :size="16" /> <span class="text-[10px] font-black uppercase">Seguridad Vial</span>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <BaseInput v-model="form.license_number" label="Nº Licencia" class="dark-input" />
                                <BaseInput v-model="form.license_plate" label="Placa" class="dark-input" />
                            </div>
                        </div>
                    </div>

                    <div v-show="currentStep === 3" class="space-y-8 animate-in slide-in-from-right-4 text-center">
                        <div class="grid grid-cols-3 gap-4 px-4">
                            <button v-for="i in 6" :key="i" type="button" @click="() => { form.avatar_type='icon'; form.avatar_source=`avatar_${i}.svg`; }"
                                class="aspect-square rounded-full border-4 transition-all p-1"
                                :class="form.avatar_type === 'icon' && form.avatar_source === `avatar_${i}.svg` ? 'border-amber-500 bg-amber-50' : 'border-transparent bg-slate-50'">
                                <img :src="`/assets/avatars/avatar_${i}.svg`" class="w-full h-full" />
                            </button>
                        </div>
                        <div class="pt-4 border-t border-dashed">
                            <ImageUploader v-model="form.avatar_file" aspect-ratio="square">
                                <template #label><span class="text-xs font-black text-amber-600 uppercase underline cursor-pointer">O sube una foto real</span></template>
                            </ImageUploader>
                        </div>
                    </div>
                </form>
            </div>

            <div class="p-8 bg-slate-50 border-t border-slate-200 flex gap-4">
                <button v-if="currentStep > 1" @click="currentStep--" class="btn bg-white border border-slate-200 text-slate-600 flex-1 font-bold">Atrás</button>
                <Link v-else :href="route('driver.login')" class="btn bg-white border border-slate-200 text-slate-600 flex-1 font-bold flex items-center justify-center">¿Ya tienes cuenta?</Link>

                <button v-if="currentStep < 3" @click="nextStep" :disabled="validatingStep1" class="btn bg-slate-900 text-white flex-1 font-bold shadow-xl shadow-slate-900/20">
                    <span v-if="validatingStep1" class="spinner border-white mr-2"></span>
                    <span v-else class="flex items-center justify-center gap-2 uppercase tracking-widest text-xs">Siguiente <ArrowRight :size="16" /></span>
                </button>
                <button v-else @click="submit" :disabled="form.processing" class="btn bg-amber-500 text-white flex-1 font-bold shadow-xl shadow-amber-500/20">
                    <span v-if="form.processing" class="spinner border-white mr-2"></span>
                    <span v-else class="flex items-center justify-center gap-2 uppercase tracking-widest text-xs">Finalizar Registro <CheckCircle :size="16" /></span>
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
.custom-tel-input { @apply w-full rounded-xl border-slate-200 bg-white text-sm h-[48px] border; }
:deep(.dark-input label) { @apply text-slate-400; }
:deep(.dark-input input) { @apply bg-slate-800 border-slate-700 text-white focus:border-amber-500; }
</style>