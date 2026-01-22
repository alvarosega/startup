<script setup>
    import { ref, computed } from 'vue';
    import { Head, useForm, Link } from '@inertiajs/vue3';
    import { VueTelInput } from 'vue-tel-input';
    import 'vue-tel-input/vue-tel-input.css';
    import axios from 'axios';
    import BaseInput from '@/Components/Base/BaseInput.vue';
    import BaseButton from '@/Components/Base/BaseButton.vue';
    import ClientLocationPicker from '@/Components/Maps/ClientLocationPicker.vue'; // <--- Importar Mapa
    import { MapPin, Crosshair } from 'lucide-vue-next';

    // Props que vienen del AuthController
    const props = defineProps({
        activeBranches: Array
    });
    
    // --- ESTADO ---
    const currentStep = ref(1);
    const showDriverModal = ref(false);
    const displayPhone = ref('');
    const customPreview = ref(null);
    const step1Errors = ref({});
    const locating = ref(false); // Estado de carga del GPS

    const form = useForm({
        // Paso 1
        phone: '',      
        password: '',
        password_confirmation: '',
        terms: false,
        // Paso 2
        avatar_type: 'icon',
        avatar_source: 'avatar_1.svg',
        avatar_file: null,
        // Paso 3 (Dirección)
        alias: 'Mi Ubicación',
        address: '',
        details: '',
        latitude: -16.5000, 
        longitude: -68.1500,
        branch_id: null,
        role: 'client'
    });
    
    // ... (Configuración Teléfono onInput igual que antes) ...
    const telOptions = { mode: 'international', defaultCountry: 'BO', dropdownOptions: { showSearchBox: true, showFlags: true, showDialCodeInSelection: true }, inputOptions: { placeholder: '77712345', required: true, autofocus: true } };
    const onInput = (phone, phoneObject) => { if (phoneObject?.number) form.phone = phoneObject.number; else form.phone = phone; if(step1Errors.value.phone) delete step1Errors.value.phone; };


    // --- NAVEGACIÓN WIZARD ---
    const nextStep = async () => {
        if (currentStep.value === 1) {
            step1Errors.value = {};
            try {
                await axios.post(route('register.validate-step-1'), {
                    phone: form.phone, password: form.password, password_confirmation: form.password_confirmation
                });
                currentStep.value = 2;
            } catch (error) {
                if (error.response?.status === 422) step1Errors.value = error.response.data.errors;
            }
        } else if (currentStep.value === 2) {
            // Validar avatar si es necesario, o simplemente pasar
            currentStep.value = 3;
        }
    };

    const prevStep = () => {
        if (currentStep.value > 1) currentStep.value--;
    };

    // --- LÓGICA AVATAR (Igual que antes) ---
    const selectIcon = (iconName) => { form.avatar_type = 'icon'; form.avatar_source = iconName; form.avatar_file = null; customPreview.value = null; };
    const uploadCustom = (e) => { const file = e.target.files[0]; if (file) { form.avatar_type = 'custom'; form.avatar_file = file; customPreview.value = URL.createObjectURL(file); } };

    // --- LÓGICA GEOLOCALIZACIÓN (NUEVO) ---
    const getMyLocation = () => {
        if (!navigator.geolocation) {
            alert('Tu navegador no soporta geolocalización.');
            return;
        }
        locating.value = true;
        navigator.geolocation.getCurrentPosition(
            (position) => {
                form.latitude = position.coords.latitude;
                form.longitude = position.coords.longitude;
                // El ClientLocationPicker detectará el cambio de props y centrará el mapa
                locating.value = false;
            },
            (error) => {
                console.error(error);
                alert('No pudimos obtener tu ubicación. Por favor selecciona en el mapa.');
                locating.value = false;
            },
            { enableHighAccuracy: true, timeout: 5000, maximumAge: 0 }
        );
    };

    const submit = () => {
        form.post(route('register'), {
            onFinish: () => form.reset('password', 'password_confirmation'),
        });
    };
</script>
    
<template>
    <Head title="Registro" />
    <div class="min-h-screen flex flex-col justify-center items-center bg-gray-50 px-4 py-8 font-sans">
        
        <div class="w-full max-w-lg bg-white p-6 rounded-2xl shadow-xl border border-gray-100 relative overflow-hidden flex flex-col max-h-[90vh]">
            
            <div class="absolute top-0 left-0 w-full h-1 bg-gray-100">
                <div class="h-full bg-blue-600 transition-all duration-300" 
                     :style="{ width: currentStep === 1 ? '33%' : (currentStep === 2 ? '66%' : '100%') }"></div>
            </div>

            <h1 class="text-xl font-black text-blue-900 text-center mb-1 uppercase tracking-tighter mt-4">
                <span v-if="currentStep === 1">Crea tu Cuenta</span>
                <span v-else-if="currentStep === 2">Personaliza tu Perfil</span>
                <span v-else>¿Dónde te enviamos?</span>
            </h1>
            <p class="text-xs text-gray-400 text-center mb-4 uppercase font-bold tracking-widest">
                Paso {{ currentStep }} de 3
            </p>
            
            <form @submit.prevent="submit" class="flex-1 overflow-y-auto custom-scrollbar px-2">
                
                <div v-show="currentStep === 1">
                    <div class="mb-5">
                        <label class="block text-gray-700 text-xs font-bold mb-2 uppercase">Celular</label>
                        <vue-tel-input v-bind="telOptions" v-model="displayPhone" @on-input="onInput" class="custom-tel-input h-11" :class="{ 'is-invalid': step1Errors.phone }" />
                        <p v-if="step1Errors.phone" class="text-red-500 text-[10px] mt-1 font-bold">{{ step1Errors.phone[0] }}</p>
                    </div>
                    <div class="grid grid-cols-1 gap-4 mb-6">
                        <div>
                            <BaseInput v-model="form.password" label="Contraseña" type="password" placeholder="Mínimo 8 caracteres" />
                            <p v-if="step1Errors.password" class="text-red-500 text-[10px] mt-1 font-bold">{{ step1Errors.password[0] }}</p>
                        </div>
                        <BaseInput v-model="form.password_confirmation" label="Confirmar" type="password" placeholder="Repite la contraseña" />
                    </div>
                    
                    <div class="mb-6 bg-gray-50 p-3 rounded-lg border border-gray-100">
                        <label class="flex items-start text-xs text-gray-500 cursor-pointer select-none">
                            <input type="checkbox" v-model="form.terms" class="rounded border-gray-300 mr-2 mt-0.5 text-blue-600 focus:ring-blue-500">
                            <span>Acepto <a href="#" class="text-blue-600 font-bold">Términos</a> y <a href="#" class="text-blue-600 font-bold">Privacidad</a>.</span>
                        </label>
                        <p v-if="form.errors.terms" class="text-red-600 text-[10px] mt-1 font-bold">{{ form.errors.terms }}</p>
                    </div>

                    <BaseButton type="button" @click="nextStep" class="w-full mb-4">Siguiente</BaseButton>
                </div>

                <div v-show="currentStep === 2">
                    <div class="mb-6 text-center">
                        <div class="grid grid-cols-3 gap-3 mb-4 justify-items-center">
                            <div v-for="i in 6" :key="i" @click="selectIcon(`avatar_${i}.svg`)" 
                                 :class="['cursor-pointer w-16 h-16 rounded-full border-2 transition-all flex justify-center items-center bg-gray-50', (form.avatar_type === 'icon' && form.avatar_source === `avatar_${i}.svg`) ? 'border-blue-500 ring-2 ring-blue-200 scale-105' : 'border-transparent hover:border-gray-300']">
                                <img :src="`/assets/avatars/avatar_${i}.svg`" class="w-12 h-12" />
                            </div>
                        </div>
                        <input type="file" @change="uploadCustom" class="hidden" id="avatar_upload" accept="image/*" />
                        <label for="avatar_upload" class="text-[10px] font-black uppercase text-blue-600 cursor-pointer hover:underline">O sube tu foto</label>
                        <div v-if="customPreview" class="w-20 h-20 rounded-full overflow-hidden border-2 border-green-500 shadow-sm mx-auto mt-2">
                            <img :src="customPreview" class="w-full h-full object-cover" />
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <button type="button" @click="prevStep" class="px-4 py-2 text-gray-400 font-bold text-sm">Atrás</button>
                        <BaseButton type="button" @click="nextStep" class="flex-1">Siguiente</BaseButton>
                    </div>
                </div>

                <div v-show="currentStep === 3">
                    
                    <div class="mb-4">
                        <div class="flex justify-between items-end mb-2">
                            <label class="block text-xs font-bold text-gray-700 uppercase">Selecciona tu ubicación</label>
                            
                            <button type="button" @click="getMyLocation" 
                                    class="text-[10px] bg-blue-100 hover:bg-blue-200 text-blue-700 px-3 py-1 rounded-full font-bold flex items-center gap-1 transition">
                                <Crosshair v-if="!locating" :size="12" />
                                <span v-if="locating">Buscando...</span>
                                <span v-else>Usar mi ubicación actual</span>
                            </button>
                        </div>

                        <div class="rounded-xl overflow-hidden border border-gray-300 shadow-inner h-64 relative z-0">
                            <ClientLocationPicker
                                v-model:modelValueLat="form.latitude"
                                v-model:modelValueLng="form.longitude"
                                v-model:modelValueAddress="form.address"
                                v-model:modelValueBranchId="form.branch_id"
                                :activeBranches="props.activeBranches"
                            />
                        </div>
                        <p v-if="form.errors.branch_id" class="text-red-500 text-[10px] mt-1 text-center font-bold">
                            ⚠️ Debes seleccionar una ubicación dentro de nuestra zona de cobertura.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 gap-4 mb-4">
                        <BaseInput v-model="form.alias" label="Nombre (Alias)" placeholder="Ej: Casa, Oficina" />
                        <BaseInput v-model="form.details" label="Referencia" placeholder="Portón negro, timbre roto" />
                    </div>

                    <div class="flex gap-3 mt-4">
                        <button type="button" @click="prevStep" class="px-4 py-2 text-gray-400 font-bold text-sm">Atrás</button>
                        <BaseButton class="flex-1" :isLoading="form.processing" :disabled="!form.terms">
                            Finalizar Registro
                        </BaseButton>
                    </div>
                </div>

            </form>

            <div class="mt-6 pt-4 border-t border-gray-50 text-center">
                <p class="text-xs text-gray-500">¿Ya tienes una cuenta?</p>
                <Link :href="route('login')" class="text-sm font-bold text-blue-600 hover:text-blue-800 uppercase tracking-wide">
                    Inicia Sesión Aquí
                </Link>
            </div>

        </div>
    </div>
</template>

<style scoped>
/* Estilos VueTelInput y Scrollbar */
.custom-tel-input { border: 1px solid #e5e7eb; border-radius: 0.5rem; padding: 0; background: #ffffff; }
.custom-tel-input:focus-within { border-color: #2563eb; box-shadow: 0 0 0 1px #2563eb; }
.is-invalid { border-color: #ef4444 !important; }
</style>