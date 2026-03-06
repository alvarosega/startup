<script setup>
import { ref, computed, watch } from 'vue';
import { useForm, Link, Head } from '@inertiajs/vue3';
import axios from 'axios';
import { VueTelInput } from 'vue-tel-input';
import 'vue-tel-input/vue-tel-input.css';
import ShopLayout from '@/Layouts/ShopLayout.vue';
import BaseInput from '@/Components/Base/BaseInput.vue';
import { 
    Truck, Bike, Car, User, ArrowRight, ArrowLeft, 
    CheckCircle, Lock, Smartphone, Mail, Image as ImageIcon, Upload 
} from 'lucide-vue-next';

const props = defineProps({ activeBranches: Array });

// --- ESTADO DE NAVEGACIÓN ---
const currentStep = ref(1);
const validatingStep1 = ref(false);

const steps = [
    { id: 1, title: 'Cuenta', icon: User },
    { id: 2, title: 'Operación', icon: Truck },
    { id: 3, title: 'Identidad', icon: ImageIcon },
];

// --- FORMULARIO DE REGISTRO ---
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
    avatar_type: 'icon', 
    avatar_source: 'avatar_1.svg'
});

// --- CONFIGURACIÓN DE TELÉFONO (STRICT BO) ---
const telOptions = { 
    mode: 'international', 
    defaultCountry: 'BO', 
    onlyCountries: ['BO'], 
    inputOptions: { placeholder: '77712345', autofocus: true } 
};

const onInput = (phone, obj) => { 
    if(obj?.number) form.phone = obj.number; 
    if (form.errors.phone) form.clearErrors('phone');
};

// --- LÓGICA DE PASOS ---
const nextStep = async () => {
    if (currentStep.value === 1) {
        form.clearErrors();
        validatingStep1.value = true;
        try {
            // Validación parcial en el servidor
            await axios.post(route('driver.register.validate-step-1'), {
                phone: form.phone, 
                email: form.email,
                password: form.password, 
                password_confirmation: form.password_confirmation
            });
            currentStep.value = 2; 
        } catch (error) {
            if (error.response?.status === 422) {
                form.setError(error.response.data.errors);
            }
        } finally {
            validatingStep1.value = false;
        }
    } else if (currentStep.value === 2) {
        // Validación local básica para el paso 2
        if (!form.first_name || !form.last_name || !form.license_number || !form.license_plate) {
            return alert('Complete todos los campos del perfil operativo.');
        }
        currentStep.value = 3; 
    }
};

const submit = () => {
    form.post(route('driver.register.store'), { 
        preserveScroll: true,
        onError: (errors) => {
            console.error("❌ Fallo en Registro:", errors);
        }
    });
};

// --- HELPERS VISUALES ---
const progressPercentage = computed(() => (currentStep.value / steps.length) * 100);
const vehicleTypes = [
    { id: 'moto', label: 'Moto', icon: Bike },
    { id: 'car', label: 'Auto', icon: Car },
    { id: 'truck', label: 'Camión', icon: Truck }
];

// Función para limpiar errores de Array a String para BaseInput
const getError = (field) => {
    const error = form.errors[field];
    return Array.isArray(error) ? error[0] : error;
};
</script>

<template>
    <Head title="Postulación Driver - BoliviaLogistics" />

    <ShopLayout>
        <div class="flex-1 relative flex items-center justify-center p-4 py-12 overflow-hidden">
            
            <div class="absolute inset-0 z-0 pointer-events-none">
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_center,hsl(var(--warning)/0.05)_0%,transparent_70%)]" />
                <div class="absolute inset-0 bg-dots opacity-20 [mask-image:radial-gradient(ellipse_at_center,black_40%,transparent_90%)]" />
            </div>

            <div class="w-full max-w-xl bg-card border border-border rounded-[2.5rem] shadow-2xl relative z-10 flex flex-col h-full max-h-[850px] overflow-hidden animate-in fade-in zoom-in-95 duration-500">
                
                <div class="px-8 pt-8 pb-4 border-b border-border shrink-0 bg-muted/20">
                    <div class="flex justify-between items-end mb-6">
                        <div>
                            <h2 class="text-3xl font-display font-black text-foreground tracking-tighter italic uppercase leading-none">
                                {{ steps[currentStep - 1].title }}
                            </h2>
                            <p class="text-[10px] uppercase font-bold tracking-[0.2em] text-amber-500 mt-2">Paso {{ currentStep }} / 3</p>
                        </div>
                        <div class="w-12 h-12 rounded-2xl bg-amber-500 text-white flex items-center justify-center shadow-lg shadow-amber-500/20">
                            <component :is="steps[currentStep - 1].icon" :size="24" />
                        </div>
                    </div>
                    <div class="flex items-center gap-2 h-1.5 bg-muted rounded-full overflow-hidden">
                        <div class="h-full bg-amber-500 transition-all duration-700 ease-in-out" :style="{ width: progressPercentage + '%' }"></div>
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto p-8 custom-scrollbar">
                    <form id="driverRegisterForm" @submit.prevent="submit" class="space-y-6">
                        
                        <div v-show="currentStep === 1" class="space-y-6 animate-in slide-in-from-right-4">
                            <div class="space-y-2">
                                <label class="text-[10px] font-black uppercase text-muted-foreground tracking-widest ml-1 flex items-center gap-2">
                                    <Smartphone :size="14" class="text-amber-500" /> Celular (Bolivia) *
                                </label>
                                <vue-tel-input 
                                    v-bind="telOptions" 
                                    v-model="form.phone" 
                                    @on-input="onInput" 
                                    class="driver-tel-input" 
                                    :class="{'!border-error': form.errors.phone}" 
                                />
                                <p v-if="form.errors.phone" class="text-[10px] text-error font-black mt-1 ml-1 uppercase">{{ getError('phone') }}</p>
                            </div>

                            <BaseInput v-model="form.email" type="email" label="Correo Electrónico" :error="getError('email')" :icon="Mail" placeholder="ejemplo@logistics.com" />
                            
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <BaseInput v-model="form.password" type="password" label="Clave de Acceso" :error="getError('password')" :icon="Lock" />
                                <BaseInput v-model="form.password_confirmation" type="password" label="Confirmar Clave" :icon="Lock" />
                            </div>
                        </div>

                        <div v-show="currentStep === 2" class="space-y-8 animate-in slide-in-from-right-4">
                            <div class="grid grid-cols-2 gap-4">
                                <BaseInput v-model="form.first_name" label="Nombres" :error="getError('first_name')" />
                                <BaseInput v-model="form.last_name" label="Apellidos" :error="getError('last_name')" />
                            </div>

                            <div class="space-y-3">
                                <label class="text-[10px] font-black uppercase text-muted-foreground tracking-widest ml-1">Tipo de Vehículo</label>
                                <div class="grid grid-cols-3 gap-3">
                                    <button v-for="v in vehicleTypes" :key="v.id" type="button" @click="form.vehicle_type = v.id"
                                        class="flex flex-col items-center justify-center p-4 rounded-2xl border-2 transition-all group"
                                        :class="form.vehicle_type === v.id ? 'border-amber-500 bg-amber-500/10 text-amber-500 shadow-sm' : 'border-border text-muted-foreground hover:bg-muted'">
                                        <component :is="v.icon" :size="28" class="group-hover:scale-110 transition-transform" />
                                        <span class="text-[9px] font-black uppercase mt-3">{{ v.label }}</span>
                                    </button>
                                </div>
                            </div>

                            <div class="bg-muted/30 p-6 rounded-[2rem] border border-border grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <BaseInput v-model="form.license_number" label="Nº de Licencia" :error="getError('license_number')" placeholder="12345678" />
                                <BaseInput v-model="form.license_plate" label="Placa del Vehículo" :error="getError('license_plate')" placeholder="4567-ABC" />
                            </div>
                        </div>

                        <div v-show="currentStep === 3" class="space-y-8 animate-in slide-in-from-right-4 text-center">
                            <div class="w-32 h-32 rounded-[2.5rem] bg-amber-500/10 border-2 border-amber-500/30 mx-auto p-2 mb-4">
                                <img :src="`/assets/avatars/${form.avatar_source}`" class="w-full h-full object-cover rounded-2xl shadow-inner" />
                            </div>
                            
                            <div class="grid grid-cols-3 gap-4 max-w-sm mx-auto">
                                <button v-for="i in 6" :key="i" type="button" 
                                    @click="() => { form.avatar_type='icon'; form.avatar_source=`avatar_${i}.svg`; }"
                                    class="aspect-square rounded-3xl border-2 transition-all p-2 flex items-center justify-center group"
                                    :class="form.avatar_source === `avatar_${i}.svg` ? 'border-amber-500 bg-amber-500/10 scale-110 shadow-lg' : 'border-transparent bg-muted opacity-40 hover:opacity-100'">
                                    <img :src="`/assets/avatars/avatar_${i}.svg`" class="w-full h-full group-hover:scale-105 transition-transform" />
                                </button>
                            </div>
                            <p class="text-xs font-bold text-muted-foreground uppercase tracking-widest mt-6">Tu Identidad en la Flota</p>
                        </div>
                    </form>
                </div>

                <div class="p-8 border-t border-border bg-card/80 backdrop-blur-md shrink-0">
                    <div class="flex gap-4">
                        <button v-if="currentStep > 1" @click="currentStep--" 
                                class="btn btn-outline flex-1 h-14 font-black uppercase text-foreground rounded-2xl hover:bg-muted transition-all">
                            <ArrowLeft :size="18" /> Atrás
                        </button>
                        <Link v-else :href="route('driver.login')" 
                              class="btn btn-ghost flex-1 h-14 font-black uppercase text-muted-foreground border border-border rounded-2xl flex items-center justify-center hover:bg-muted transition-all">
                            Login
                        </Link>

                        <button v-if="currentStep < 3" @click="nextStep" :disabled="validatingStep1" 
                                class="btn flex-[2] h-14 bg-amber-500 hover:bg-amber-600 border-none shadow-lg shadow-amber-500/20 text-white rounded-2xl active:scale-95 transition-all">
                            <span v-if="validatingStep1" class="loading loading-spinner"></span>
                            <span v-else class="flex items-center justify-center gap-2 font-black uppercase italic">
                                Siguiente <ArrowRight :size="18"/>
                            </span>
                        </button>

                        <div v-else class="flex-[2] flex flex-col gap-3">
                            <button type="submit" form="driverRegisterForm" :disabled="form.processing" 
                                    class="btn w-full h-14 bg-amber-500 hover:bg-amber-600 border-none shadow-lg shadow-amber-500/20 text-white rounded-2xl active:scale-95 transition-all">
                                <span v-if="form.processing" class="loading loading-spinner"></span>
                                <span v-else class="flex items-center justify-center gap-2 font-black uppercase italic">
                                    Finalizar <CheckCircle :size="20" />
                                </span>
                            </button>
                            <p class="text-[9px] text-center text-muted-foreground font-medium leading-tight">
                                Al finalizar, su cuenta entrará en proceso de <span class="text-amber-600 font-black">Verificación Operativa</span>.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </ShopLayout>
</template>

