<script setup>
    import { ref, watch, nextTick } from 'vue';
    import { useForm } from '@inertiajs/vue3';
    import axios from 'axios';
    import { VueTelInput } from 'vue-tel-input';
    import 'vue-tel-input/vue-tel-input.css';
    import BaseInput from '@/Components/Base/BaseInput.vue';
    import BaseButton from '@/Components/Base/BaseButton.vue';
    import ClientLocationPicker from '@/Components/Maps/ClientLocationPicker.vue';
    import { Crosshair, MapPin } from 'lucide-vue-next';
    
    // Definir Emits para comunicarse con el ShopLayout
    const emit = defineEmits(['close', 'switchToLogin']);
    
    // Props recibidas del Layout
    const props = defineProps({
        activeBranches: { type: Array, default: () => [] }
    });
    
    // --- ESTADO ---
    const currentStep = ref(1);
    const step1Errors = ref({});
    const mapComponentRef = ref(null); 
    const validatingStep1 = ref(false);
    const customPreview = ref(null);
    const locating = ref(false);
    
    const form = useForm({
        // Datos Cuenta
        phone: '', 
        password: '', 
        password_confirmation: '', 
        terms: false,
        // Avatar
        avatar_type: 'icon',
        avatar_source: 'avatar_1.svg',
        avatar_file: null,
        // Ubicación
        alias: 'Mi Ubicación', 
        address: '', 
        details: '', 
        latitude: -16.5000, 
        longitude: -68.1500, 
        branch_id: null, 
        role: 'client'
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
    
    // Validar Paso 1 (Backend)
    const nextStep = async () => {
        if (currentStep.value === 1) {
            step1Errors.value = {};
            validatingStep1.value = true;
            try {
                await axios.post(route('register.validate-step-1'), {
                    phone: form.phone, password: form.password, password_confirmation: form.password_confirmation
                });
                currentStep.value = 2; // Ir a Avatar
            } catch (error) {
                if (error.response?.status === 422) step1Errors.value = error.response.data.errors;
            } finally {
                validatingStep1.value = false;
            }
        } else if (currentStep.value === 2) {
            currentStep.value = 3; // Ir a Mapa
        }
    };
    
    const prevStep = () => {
        if (currentStep.value > 1) currentStep.value--;
    };
    
    // --- LOGICA AVATAR ---
    const selectIcon = (iconName) => { form.avatar_type = 'icon'; form.avatar_source = iconName; form.avatar_file = null; customPreview.value = null; };
    const uploadCustom = (e) => { const file = e.target.files[0]; if (file) { form.avatar_type = 'custom'; form.avatar_file = file; customPreview.value = URL.createObjectURL(file); } };
    
    // --- LOGICA MAPA ---
    // Refrescar mapa al entrar al paso 3
    watch(currentStep, (val) => {
        if (val === 3) {
            nextTick(() => {
                if (mapComponentRef.value && typeof mapComponentRef.value.refreshMap === 'function') {
                    mapComponentRef.value.refreshMap();
                }
            });
        }
    });
    
    const getMyLocation = () => {
        if (!navigator.geolocation) return alert('Tu navegador no soporta geolocalización.');
        locating.value = true;
        navigator.geolocation.getCurrentPosition(
            (pos) => {
                form.latitude = pos.coords.latitude;
                form.longitude = pos.coords.longitude;
                locating.value = false;
            },
            (err) => { console.error(err); locating.value = false; },
            { enableHighAccuracy: true, timeout: 5000, maximumAge: 0 }
        );
    };
    
    // --- ENVÍO ---
    const submit = () => {
        form.post(route('register'), {
            preserveScroll: true,
            onSuccess: () => emit('close'),
            onError: (errors) => {
                if (errors.branch_id) alert('Por favor selecciona una ubicación válida (dentro de cobertura).');
            }
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
                    <span v-if="currentStep === 1">Crea tu Cuenta</span>
                    <span v-else-if="currentStep === 2">Elige tu Avatar</span>
                    <span v-else>Tu Ubicación</span>
                </h2>
                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-1">
                    Paso {{ currentStep }} de 3
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
                                <span>Acepto <a href="#" class="font-bold text-blue-600 hover:underline">Términos</a> y <a href="#" class="font-bold text-blue-600 hover:underline">Privacidad</a></span>
                            </label>
                        </div>
    
                        <div class="text-center pt-2">
                            <a :href="route('register.driver')" class="text-[10px] text-gray-400 font-bold uppercase hover:text-blue-600 transition flex items-center justify-center gap-1">
                                <span>🛵</span> ¿Eres conductor? Regístrate aquí
                            </a>
                        </div>
                    </div>
    
                    <div v-show="currentStep === 2" class="space-y-6 animate-in fade-in slide-in-from-right-4 duration-300">
                        <div class="text-center">
                            <p class="text-xs text-gray-500 mb-4">Selecciona un icono o sube tu foto</p>
                            
                            <div class="grid grid-cols-3 gap-4 mb-6 justify-items-center px-4">
                                <div v-for="i in 6" :key="i" @click="selectIcon(`avatar_${i}.svg`)" 
                                     :class="['cursor-pointer w-16 h-16 rounded-full border-2 transition-all flex justify-center items-center bg-gray-50', (form.avatar_type === 'icon' && form.avatar_source === `avatar_${i}.svg`) ? 'border-blue-500 ring-4 ring-blue-100 scale-105' : 'border-transparent hover:border-gray-200']">
                                    <img :src="`/assets/avatars/avatar_${i}.svg`" class="w-12 h-12" />
                                </div>
                            </div>
    
                            <div class="relative group cursor-pointer inline-block">
                                <input type="file" @change="uploadCustom" class="hidden" id="avatar_upload" accept="image/*" />
                                <label for="avatar_upload" class="flex flex-col items-center gap-2 cursor-pointer">
                                    <div class="w-20 h-20 rounded-full bg-gray-100 border-2 border-dashed border-gray-300 flex items-center justify-center overflow-hidden group-hover:border-blue-400 transition">
                                        <img v-if="customPreview" :src="customPreview" class="w-full h-full object-cover" />
                                        <span v-else class="text-2xl text-gray-400">+</span>
                                    </div>
                                    <span class="text-[10px] font-black uppercase text-blue-600 group-hover:underline">Subir Foto</span>
                                </label>
                            </div>
                        </div>
                    </div>
    
                    <div v-show="currentStep === 3" class="flex-1 flex flex-col h-full animate-in fade-in slide-in-from-right-4 duration-300">
                    
                    <div class="flex justify-between items-end mb-2 shrink-0">
                        <label class="block text-xs font-bold text-gray-700 uppercase ml-1">Ubicación de Entrega</label>
                        <button type="button" @click="getMyLocation" 
                                class="text-[10px] bg-blue-50 hover:bg-blue-100 text-blue-600 px-3 py-1 rounded-full font-bold flex items-center gap-1 transition border border-blue-100">
                            <Crosshair v-if="!locating" :size="12" />
                            <span v-if="locating">Buscando...</span>
                            <span v-else>Usar mi GPS</span>
                        </button>
                    </div>

                    <div class="w-full h-64 rounded-xl overflow-hidden border border-gray-300 relative shadow-inner bg-gray-100 shrink-0">
                        <ClientLocationPicker
                            ref="mapComponentRef" 
                            v-model:modelValueLat="form.latitude"
                            v-model:modelValueLng="form.longitude"
                            v-model:modelValueAddress="form.address"
                            v-model:modelValueBranchId="form.branch_id"
                            :activeBranches="props.activeBranches"
                        />
                    </div>

                    <div class="mt-4 space-y-3 pb-2 flex-1">
                        <div class="grid grid-cols-2 gap-3">
                            <BaseInput v-model="form.alias" label="Alias" placeholder="Ej: Casa" />
                            <BaseInput v-model="form.details" label="Referencia" placeholder="Ej: Portón rojo" />
                        </div>
                        
                        <p v-if="form.errors.branch_id" class="text-red-500 text-[10px] text-center font-bold bg-red-50 p-2 rounded border border-red-100">
                            ⚠️ Mueve el pin a una zona con cobertura azul.
                        </p>

                        <div class="flex gap-3 pt-2 mt-auto">
                            <button type="button" @click="currentStep = 2" class="px-4 py-3 text-gray-400 font-bold text-xs hover:text-gray-600 hover:bg-gray-50 rounded-lg transition">Atrás</button>
                            <BaseButton type="submit" :isLoading="form.processing" class="flex-1 shadow-lg">Finalizar Registro</BaseButton>
                        </div>
                    </div>
                </div>
    
                </form>
            </div>
    
            <div class="mt-4 pt-4 border-t border-gray-100">
                <div class="flex gap-3 mb-4">
                    
                    <button v-if="currentStep > 1" type="button" @click="prevStep" 
                            class="px-5 py-3 text-gray-400 font-bold text-xs hover:text-gray-600 hover:bg-gray-50 rounded-xl transition">
                        Atrás
                    </button>
    
                    <BaseButton 
                        v-if="currentStep < 3"
                        type="button" 
                        @click="nextStep" 
                        class="flex-1 shadow-lg"
                        :isLoading="validatingStep1"
                        :disabled="currentStep === 1 && !form.terms"
                    >
                        Siguiente Paso
                    </BaseButton>
    
                    <BaseButton 
                        v-else
                        type="submit" 
                        @click="submit"
                        :isLoading="form.processing" 
                        class="flex-1 shadow-lg bg-green-600 hover:bg-green-700 text-white"
                    >
                        Finalizar Registro
                    </BaseButton>
                </div>
    
                <div class="text-center">
                    <button @click="$emit('switchToLogin')" class="text-xs text-blue-600 font-bold uppercase hover:underline tracking-wide">
                        ¿Ya tienes cuenta? Inicia Sesión
                    </button>
                </div>
            </div>
    
        </div>
    </template>
    
    <style scoped>
    .custom-tel-input { 
        border: 1px solid #d1d5db; 
        border-radius: 0.75rem; 
        padding: 2px 0;
    }
    .custom-tel-input:focus-within {
        border-color: #3b82f6; 
        box-shadow: 0 0 0 1px #3b82f6;
    }
    /* Scrollbar fino para el contenido interno */
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #9ca3af; }
    </style>