<script setup>
    import { ref } from 'vue';
    import { useForm } from '@inertiajs/vue3';
    import axios from 'axios';
    import { VueTelInput } from 'vue-tel-input';
    import 'vue-tel-input/vue-tel-input.css';
    import BaseInput from '@/Components/Base/BaseInput.vue';
    import BaseButton from '@/Components/Base/BaseButton.vue';
    import { Truck, Bike, Car, User, FileText, ArrowRight } from 'lucide-vue-next';
    
    // Emits para integración con modales (si se usa en modal)
    const emit = defineEmits(['close', 'switchToLogin']);
    
    // --- ESTADO ---
    const currentStep = ref(1);
    const step1Errors = ref({});
    const validatingStep1 = ref(false);
    const customPreview = ref(null);
    
    const form = useForm({
        // Paso 1: Cuenta
        phone: '', 
        password: '', 
        password_confirmation: '', 
        terms: false,
        // Paso 2: Perfil y Vehículo
        first_name: '',
        last_name: '',
        license_number: '',
        license_plate: '',
        vehicle_type: 'moto',
        // Paso 3: Avatar
        avatar_type: 'icon',
        avatar_source: 'avatar_1.svg',
        avatar_file: null,
        // Rol Fijo
        role: 'driver'
    });
    
    // Configuración Teléfono
    const telOptions = { 
        mode: 'international', 
        defaultCountry: 'BO', 
        dropdownOptions: { showSearchBox: true, showFlags: true, showDialCodeInSelection: true }, 
        inputOptions: { placeholder: '77712345', required: true, autofocus: true } 
    };
    
    const onInput = (phone, obj) => { 
        if(obj?.number) form.phone = obj.number; 
        if (step1Errors.value.phone) delete step1Errors.value.phone;
    };
    
    // --- NAVEGACIÓN ---
    const nextStep = async () => {
        if (currentStep.value === 1) {
            // Validar Credenciales
            step1Errors.value = {};
            validatingStep1.value = true;
            try {
                await axios.post(route('register.validate-step-1'), {
                    phone: form.phone, password: form.password, password_confirmation: form.password_confirmation
                });
                currentStep.value = 2; 
            } catch (error) {
                if (error.response?.status === 422) step1Errors.value = error.response.data.errors;
            } finally {
                validatingStep1.value = false;
            }
        } else if (currentStep.value === 2) {
            // Validar Datos Personales (Simple client-side validation)
            if (!form.first_name || !form.last_name || !form.license_number || !form.license_plate) {
                return alert('Por favor completa todos los datos del conductor y vehículo.');
            }
            currentStep.value = 3; 
        }
    };
    
    const prevStep = () => {
        if (currentStep.value > 1) currentStep.value--;
    };
    
    // --- LÓGICA AVATAR ---
    const selectIcon = (iconName) => { form.avatar_type = 'icon'; form.avatar_source = iconName; form.avatar_file = null; customPreview.value = null; };
    const uploadCustom = (e) => { const file = e.target.files[0]; if (file) { form.avatar_type = 'custom'; form.avatar_file = file; customPreview.value = URL.createObjectURL(file); } };
    
    // --- ENVÍO ---
    const submit = () => {
        form.post(route('register'), {
            preserveScroll: true,
            onSuccess: () => emit('close'),
        });
    };
    </script>
    
    <template>
        <div class="h-full flex flex-col p-6 bg-white w-full relative">
            
            <div class="absolute top-0 left-0 w-full h-1.5 bg-gray-100">
                <div class="h-full bg-blue-600 transition-all duration-500 ease-out" 
                     :style="{ width: currentStep === 1 ? '33%' : (currentStep === 2 ? '66%' : '100%') }"></div>
            </div>
    
            <div class="text-center mb-6 mt-2">
                <h2 class="text-xl font-black text-blue-900 uppercase italic leading-none">
                    <span v-if="currentStep === 1">Únete al Equipo</span>
                    <span v-else-if="currentStep === 2">Tu Vehículo</span>
                    <span v-else>Tu Identidad</span>
                </h2>
                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-1">
                    Registro de Conductor - Paso {{ currentStep }} de 3
                </p>
            </div>
    
            <div class="flex-1 overflow-y-auto custom-scrollbar px-1">
                <form @submit.prevent="submit" class="h-full flex flex-col">
                    
                    <div v-show="currentStep === 1" class="space-y-5 animate-in fade-in slide-in-from-right-4 duration-300">
                        <div>
                            <label class="block text-xs font-bold uppercase text-gray-500 mb-1 ml-1">Celular</label>
                            <vue-tel-input 
                                v-bind="telOptions"
                                v-model="form.phone" 
                                @on-input="onInput" 
                                class="border border-gray-300 rounded-xl custom-tel-input shadow-sm" 
                                :class="{'border-red-500': step1Errors.phone}"
                            />
                            <p v-if="step1Errors.phone" class="text-red-500 text-[10px] mt-1 font-bold ml-1">{{ step1Errors.phone[0] }}</p>
                        </div>
    
                        <div class="space-y-4">
                            <div>
                                <BaseInput v-model="form.password" type="password" label="Contraseña" placeholder="Mínimo 8 caracteres" />
                                <p v-if="step1Errors.password" class="text-red-500 text-[10px] mt-1 font-bold ml-1">{{ step1Errors.password[0] }}</p>
                            </div>
                            <BaseInput v-model="form.password_confirmation" type="password" label="Confirmar Contraseña" />
                        </div>
    
                        <div class="bg-blue-50 p-3 rounded-xl border border-blue-100">
                             <label class="flex items-center text-xs text-gray-600 cursor-pointer select-none">
                                <input type="checkbox" v-model="form.terms" class="mr-2 text-blue-600 rounded focus:ring-blue-500"> 
                                <span>Acepto trabajar bajo los <a href="#" class="font-bold text-blue-600 hover:underline">Términos de Conductor</a></span>
                            </label>
                        </div>
                    </div>
    
                    <div v-show="currentStep === 2" class="space-y-6 animate-in fade-in slide-in-from-right-4 duration-300">
                        
                        <div class="grid grid-cols-2 gap-4">
                            <BaseInput v-model="form.first_name" label="Nombre" placeholder="Juan" />
                            <BaseInput v-model="form.last_name" label="Apellido" placeholder="Pérez" />
                        </div>
    
                        <div>
                            <label class="block text-xs font-bold uppercase text-gray-500 mb-2 ml-1">Tipo de Vehículo</label>
                            <div class="grid grid-cols-3 gap-3">
                                <div @click="form.vehicle_type = 'moto'" 
                                     :class="['cursor-pointer flex flex-col items-center justify-center p-3 rounded-xl border-2 transition-all', form.vehicle_type === 'moto' ? 'border-blue-600 bg-blue-50 text-blue-700' : 'border-gray-200 text-gray-400 hover:border-blue-200']">
                                    <Bike :size="24" />
                                    <span class="text-[10px] font-bold mt-1">Moto</span>
                                </div>
                                <div @click="form.vehicle_type = 'car'" 
                                     :class="['cursor-pointer flex flex-col items-center justify-center p-3 rounded-xl border-2 transition-all', form.vehicle_type === 'car' ? 'border-blue-600 bg-blue-50 text-blue-700' : 'border-gray-200 text-gray-400 hover:border-blue-200']">
                                    <Car :size="24" />
                                    <span class="text-[10px] font-bold mt-1">Auto</span>
                                </div>
                                <div @click="form.vehicle_type = 'truck'" 
                                     :class="['cursor-pointer flex flex-col items-center justify-center p-3 rounded-xl border-2 transition-all', form.vehicle_type === 'truck' ? 'border-blue-600 bg-blue-50 text-blue-700' : 'border-gray-200 text-gray-400 hover:border-blue-200']">
                                    <Truck :size="24" />
                                    <span class="text-[10px] font-bold mt-1">Camión</span>
                                </div>
                            </div>
                        </div>
    
                        <div class="bg-gray-50 p-4 rounded-xl border border-gray-200 space-y-4">
                            <div class="flex items-center gap-2 mb-1 text-gray-400">
                                <FileText :size="16"/>
                                <span class="text-[10px] font-bold uppercase tracking-wider">Documentación</span>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <BaseInput v-model="form.license_number" label="Nº Licencia" placeholder="12345 LP" />
                                <BaseInput v-model="form.license_plate" label="Placa" placeholder="2020-XXX" />
                            </div>
                        </div>
                    </div>
    
                    <div v-show="currentStep === 3" class="space-y-6 animate-in fade-in slide-in-from-right-4 duration-300">
                        <div class="text-center">
                            <p class="text-xs text-gray-500 mb-4">Elige una foto clara para tu perfil de conductor</p>
                            
                            <div class="grid grid-cols-3 gap-4 mb-6 justify-items-center px-4">
                                <div v-for="i in 6" :key="i" @click="selectIcon(`avatar_${i}.svg`)" 
                                     :class="['cursor-pointer w-16 h-16 rounded-full border-2 transition-all flex justify-center items-center bg-gray-50', (form.avatar_type === 'icon' && form.avatar_source === `avatar_${i}.svg`) ? 'border-blue-500 ring-4 ring-blue-100 scale-105' : 'border-transparent hover:border-gray-200']">
                                    <img :src="`/assets/avatars/avatar_${i}.svg`" class="w-12 h-12" />
                                </div>
                            </div>
    
                            <div class="relative group cursor-pointer inline-block">
                                <input type="file" @change="uploadCustom" class="hidden" id="driver_avatar_upload" accept="image/*" />
                                <label for="driver_avatar_upload" class="flex flex-col items-center gap-2 cursor-pointer">
                                    <div class="w-20 h-20 rounded-full bg-gray-100 border-2 border-dashed border-gray-300 flex items-center justify-center overflow-hidden group-hover:border-blue-400 transition">
                                        <img v-if="customPreview" :src="customPreview" class="w-full h-full object-cover" />
                                        <span v-else class="text-2xl text-gray-400">+</span>
                                    </div>
                                    <span class="text-[10px] font-black uppercase text-blue-600 group-hover:underline">Subir Foto</span>
                                </label>
                            </div>
                        </div>
    
                        <div class="bg-yellow-50 p-3 rounded-lg flex gap-2 items-start">
                            <div class="mt-0.5 text-yellow-600"><User :size="16"/></div>
                            <p class="text-[10px] text-yellow-800 leading-tight">
                                <strong>Nota:</strong> Tu cuenta deberá ser verificada por administración antes de poder tomar pedidos.
                            </p>
                        </div>
                    </div>
    
                    <div class="mt-auto pt-4 flex gap-3">
                        <button v-if="currentStep > 1" type="button" @click="prevStep" 
                                class="px-5 py-3 text-gray-400 font-bold text-xs hover:text-gray-600 hover:bg-gray-50 rounded-xl transition">
                            Atrás
                        </button>
        
                        <BaseButton v-if="currentStep < 3" type="button" @click="nextStep" class="flex-1 shadow-lg"
                                    :isLoading="validatingStep1" :disabled="currentStep === 1 && !form.terms">
                            Siguiente Paso
                        </BaseButton>
        
                        <BaseButton v-else type="submit" :isLoading="form.processing" 
                                    class="flex-1 shadow-lg bg-green-600 hover:bg-green-700 text-white">
                            Enviar Solicitud
                        </BaseButton>
                    </div>
    
                </form>
            </div>
    
            <div class="mt-4 pt-4 border-t border-gray-100 text-center">
                <button @click="$emit('switchToLogin')" class="text-xs text-blue-600 font-bold uppercase hover:underline tracking-wide">
                    ¿Ya tienes cuenta? Inicia Sesión
                </button>
            </div>
    
        </div>
    </template>
    
    <style scoped>
    .custom-tel-input { border: 1px solid #d1d5db; border-radius: 0.75rem; padding: 2px 0; }
    .custom-tel-input:focus-within { border-color: #3b82f6; box-shadow: 0 0 0 1px #3b82f6; }
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 10px; }
    </style>