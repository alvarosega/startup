<script setup>
import { ref, watch, computed } from 'vue'; 
import { useForm, Link, Head } from '@inertiajs/vue3';
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

const props = defineProps({
    activeBranches: { type: Array, default: () => [] }
});

const currentStep = ref(1);
const step1Errors = ref({});
const mapComponentRef = ref(null); 
const validatingStep1 = ref(false);
const customPreview = ref(null);
const locating = ref(false);
const form = useForm({
    first_name: '',
    last_name: '',  
    phone: '', email: '', password: '', password_confirmation: '', terms: false,
    avatar_type: 'icon', avatar_source: 'avatar_1.svg', avatar_file: null,
    alias: 'Mi Ubicación', address: '', details: '', latitude: -16.5000, longitude: -68.1500, branch_id: null, role: 'client'
});

// Añade estos watchers debajo de tus refs
watch(() => form.first_name, () => { 
    if (step1Errors.value.first_name) delete step1Errors.value.first_name; 
});

watch(() => form.last_name, () => { 
    if (step1Errors.value.last_name) delete step1Errors.value.last_name; 
});

watch(() => form.email, () => { 
    if (step1Errors.value.email) delete step1Errors.value.email; 
});


const steps = [
    { id: 1, title: 'Cuenta', icon: UserPlus },
    { id: 2, title: 'Avatar', icon: Image },
    { id: 3, title: 'Ubicación', icon: MapPin },
];



const onInput = (phone, obj) => { 
    if(obj?.number) form.phone = obj.number; 
    if (step1Errors.value.phone) delete step1Errors.value.phone;
};

const nextStep = async () => {
    if (currentStep.value === 1) {
        if (!form.terms) return alert('Debes aceptar los términos y condiciones');
        step1Errors.value = {};
        validatingStep1.value = true;
        try {
            await axios.post(route('register.validate-step-1'), form.data());
            currentStep.value = 2;
        } catch (error) {
            if (error.response?.status === 422) step1Errors.value = error.response.data.errors;
            else alert('Error en el servidor.');
        } finally {
            validatingStep1.value = false;
        }
    } else if (currentStep.value === 2) {
        currentStep.value = 3;
    }
};

const submit = () => {
    if (form.processing) return;

    // Ya no bloqueamos si branch_id es null, 
    // dejamos que el backend lo reciba como nulo según tu lógica.
    form.post(route('register'), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            // Limpiar estados si es necesario
        },
        onError: (errors) => {
            console.error('Fallo en el registro:', errors);
            // Si el error es de cobertura y tú quieres que sea obligatorio, 
            // aquí es donde avisarías al usuario.
        }
    });
};

const progressPercentage = computed(() => ((currentStep.value) / steps.length) * 100);
</script>

<template>
    <Head title="Registro de Cliente" />
    <div class="min-h-screen bg-slate-50 flex items-center justify-center p-4">
        <div class="w-full max-w-xl bg-white rounded-3xl shadow-2xl overflow-hidden flex flex-col h-[700px]">
            
            <div class="px-8 pt-8 pb-4 border-b border-border/40 shrink-0">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-2xl font-display font-black text-foreground tracking-tight">Crea tu Cuenta</h2>
                    <div class="bg-black text-green-400 p-2 text-[10px] font-mono rounded mb-4">
                        DEBUG: BranchID: {{ form.branch_id || 'NULL' }} | 
                        Branches en Props: {{ props.activeBranches.length }}
                    </div>
                    <span class="text-xs font-bold text-primary bg-primary/10 px-3 py-1.5 rounded-full">
                        Paso {{ currentStep }} de {{ steps.length }}
                    </span>
                </div>
                <div class="flex items-center gap-2 mb-2">
                    <div v-for="step in steps" :key="step.id" 
                         class="h-2 rounded-full flex-1 transition-all duration-500"
                         :class="currentStep >= step.id ? 'bg-primary' : 'bg-slate-100'">
                    </div>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto p-8">
                <form id="registerForm" @submit.prevent="submit" class="h-full">
                    
                    <div v-show="currentStep === 1" class="space-y-5 animate-in slide-in-from-right-4">
                        <div class="grid grid-cols-2 gap-4">
                            <BaseInput 
                                v-model="form.first_name" 
                                label="Nombre" 
                                placeholder="Ej: Álvaro" 
                                :icon="UserPlus" 
                                :error="step1Errors.first_name ? step1Errors.first_name[0] : ''" 
                            />
                            <BaseInput 
                                v-model="form.last_name" 
                                label="Apellido" 
                                placeholder="Ej: Segovia" 
                                :icon="UserPlus" 
                                :error="step1Errors.last_name ? step1Errors.last_name[0] : ''" 
                            />
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-muted-foreground ml-1 flex items-center gap-2">
                                <Smartphone :size="14" /> Celular *
                            </label>
                            <vue-tel-input v-model="form.phone" @on-input="onInput" mode="international" :preferredCountries="['BO']" class="custom-tel-input" :class="{ '!border-error': step1Errors.phone }" />
                            <p v-if="step1Errors.phone" class="text-xs text-error font-bold mt-1">{{ step1Errors.phone[0] }}</p>
                        </div>
                        <BaseInput v-model="form.email" type="email" label="Correo Electrónico" placeholder="ejemplo@email.com" :icon="Mail" :error="step1Errors.email ? step1Errors.email[0] : ''" />
                        <div class="grid grid-cols-2 gap-4">
                            <BaseInput v-model="form.password" type="password" label="Contraseña" :icon="Lock" :error="step1Errors.password ? step1Errors.password[0] : ''" />
                            <BaseInput v-model="form.password_confirmation" type="password" label="Confirmar" :icon="Lock" />
                        </div>
                        <BaseCheckbox v-model="form.terms">
                            <span class="text-xs">Acepto los <a href="#" class="font-bold text-primary">Términos y Condiciones</a></span>
                        </BaseCheckbox>
                    </div>

                    <div v-show="currentStep === 2" class="space-y-6 animate-in slide-in-from-right-4 text-center">
                        <div class="relative inline-block">
                            <div class="w-32 h-32 rounded-full border-4 border-slate-100 p-1 mx-auto bg-slate-50 overflow-hidden shadow-inner">
                                <img v-if="form.avatar_type === 'custom' && customPreview" :src="customPreview" class="w-full h-full object-cover rounded-full" />
                                <img v-else :src="`/assets/avatars/${form.avatar_source}`" class="w-full h-full object-cover rounded-full" />
                            </div>
                            <label for="reg_avatar_upload" class="absolute bottom-1 right-1 p-2 bg-primary text-white rounded-full cursor-pointer shadow-lg hover:scale-110 transition-transform">
                                <Upload :size="16" />
                            </label>
                            <input type="file" id="reg_avatar_upload" class="hidden" @change="(e) => { const file = e.target.files[0]; if(file) { form.avatar_type='custom'; form.avatar_file=file; customPreview = URL.createObjectURL(file); } }">
                        </div>
                        <div class="grid grid-cols-4 gap-4">
                            <button type="button" v-for="i in 8" :key="i" @click="() => { form.avatar_type='icon'; form.avatar_source=`avatar_${i}.svg`; customPreview=null; }"
                                    class="aspect-square rounded-2xl border-2 flex items-center justify-center p-2 transition-all hover:bg-slate-50"
                                    :class="form.avatar_type === 'icon' && form.avatar_source === `avatar_${i}.svg` ? 'border-primary bg-primary/5' : 'border-transparent'">
                                <img :src="`/assets/avatars/avatar_${i}.svg`" class="w-8 h-8 md:w-12 md:h-12" />
                            </button>
                        </div>
                    </div>

                    <div v-show="currentStep === 3" class="h-full flex flex-col space-y-4 animate-in slide-in-from-right-4">
                        <div class="h-64 rounded-3xl overflow-hidden border border-slate-200 shadow-inner relative">
                            <ClientLocationPicker ref="mapComponentRef" v-model:modelValueLat="form.latitude" v-model:modelValueLng="form.longitude" v-model:modelValueAddress="form.address" v-model:modelValueBranchId="form.branch_id" :activeBranches="props.activeBranches" />
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <BaseInput v-model="form.alias" label="Alias" placeholder="Ej: Casa" />
                            <BaseInput v-model="form.details" label="Referencia" placeholder="Ej: Portón rojo" />
                        </div>
                    </div>
                </form>
            </div>

            <div class="p-8 border-t border-slate-100 bg-slate-50/50 flex gap-4">
                <button v-if="currentStep > 1" @click="currentStep--" class="btn bg-white border border-slate-200 text-slate-600 flex-1">Atrás</button>
                <Link v-else :href="route('login')" class="btn bg-white border border-slate-200 text-slate-600 flex-1 flex items-center justify-center">¿Ya tienes cuenta?</Link>

                <button v-if="currentStep < 3" @click="nextStep" :disabled="validatingStep1" class="btn btn-primary flex-1 shadow-lg shadow-primary/20">
                    <span v-if="validatingStep1" class="spinner spinner-sm"></span>
                    <span v-else class="flex items-center justify-center gap-2">Siguiente <ArrowRight :size="18" /></span>
                </button>
                <button 
                    v-else 
                    type="submit" 
                    form="registerForm" 
                    :disabled="form.processing" 
                    class="btn btn-primary flex-1 shadow-lg shadow-primary/20"
                >
                    <span v-if="form.processing" class="spinner spinner-sm"></span>
                    <span v-else class="flex items-center justify-center gap-2">
                        Finalizar <CheckCircle :size="18" />
                    </span>
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
.custom-tel-input { @apply w-full rounded-xl border-slate-200 bg-white text-sm h-[46px] border; }
:deep(.vti__dropdown) { border-radius: 0.75rem 0 0 0.75rem !important; }
</style>