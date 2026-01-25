<script setup>
    import { ref, computed } from 'vue';
    import { useForm } from '@inertiajs/vue3';
    import axios from 'axios';
    import { VueTelInput } from 'vue-tel-input';
    import 'vue-tel-input/vue-tel-input.css';
    import { Mail } from 'lucide-vue-next';
    
    // Componentes Base
    import BaseInput from '@/Components/Base/BaseInput.vue';
    import BaseCheckbox from '@/Components/Base/BaseCheckbox.vue';
    import ImageUploader from '@/Components/Maps/ImageUploader.vue'; // Ajusta la ruta si es necesario

    import { 
        Truck, Bike, Car, User, FileText, 
        ArrowRight, ArrowLeft, CheckCircle, Lock, 
        Smartphone, AlertCircle, Shield
    } from 'lucide-vue-next';
    
    // --- EMITS ---
    // Agregamos 'switchToClient' para volver al otro modal sin recargar
    const emit = defineEmits(['close', 'switchToLogin', 'switchToClient']);
    
    // --- ESTADO ---
    const currentStep = ref(1);
    const step1Errors = ref({});
    const validatingStep1 = ref(false);
    
    // Configuración de Pasos
    const steps = [
        { id: 1, title: 'Cuenta', icon: User },
        { id: 2, title: 'Perfil', icon: Truck },
        { id: 3, title: 'Avatar', icon: User },
    ];
    
    const form = useForm({
        // Paso 1: Cuenta
        phone: '', 
        email: '',
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
            if (!form.terms) {
                alert('Debes aceptar los términos y condiciones del conductor');
                return;
            }
    
            step1Errors.value = {};
            validatingStep1.value = true;
            try {
                // Validamos solo el paso 1 con el backend antes de avanzar
                await axios.post(route('register.validate-step-1'), {
                    phone: form.phone, 
                    email: form.email,
                    password: form.password, 
                    password_confirmation: form.password_confirmation
                });
                currentStep.value = 2; 
            } catch (error) {
                if (error.response?.status === 422) {
                    step1Errors.value = error.response.data.errors;
                }
            } finally {
                validatingStep1.value = false;
            }
        } else if (currentStep.value === 2) {
            // Validación simple del lado del cliente para paso 2
            if (!form.first_name || !form.last_name || !form.license_number || !form.license_plate) {
                alert('Por favor completa todos los datos del conductor y vehículo.');
                return;
            }
            currentStep.value = 3; 
        }
    };
    
    const prevStep = () => {
        if (currentStep.value > 1) currentStep.value--;
    };
    
    // --- LÓGICA AVATAR ---
    const selectIcon = (iconName) => { 
        form.avatar_type = 'icon'; 
        form.avatar_source = iconName; 
        form.avatar_file = null; 
    };
    
    // --- ENVÍO ---
    const submit = () => {
        form.post(route('register.driver.store'), { 
            forceFormData: true, 
            preserveScroll: true,
            onSuccess: () => emit('close'),
            onError: (errors) => {
                console.error("Errores de registro:", errors);
            }
        });
    };
    
    const progressPercentage = computed(() => ((currentStep.value - 1) / (steps.length - 1)) * 100);
    
    const vehicleTypes = [
        { id: 'moto', label: 'Moto', icon: Bike, description: 'Motocicleta' },
        { id: 'car', label: 'Auto', icon: Car, description: 'Automóvil' },
        { id: 'truck', label: 'Camión', icon: Truck, description: 'Carga pesada' }
    ];
</script>
    
<template>
    <div class="h-full flex flex-col p-6 bg-card w-full relative">
        
        <div class="absolute top-0 left-0 w-full h-1.5 bg-muted">
            <div class="h-full bg-gradient-to-r from-warning to-orange-500 transition-all duration-base ease-smooth" 
                    :style="{ width: progressPercentage + '%' }">
            </div>
        </div>

        <div class="text-center mb-6 mt-2">
            <div class="avatar avatar-lg bg-gradient-to-br from-warning to-orange-500 text-white mx-auto mb-4 shadow-lg ring-4 ring-warning/10">
                <component :is="steps[currentStep - 1].icon" :size="24" />
            </div>
            <h2 class="text-xl font-display font-black text-foreground uppercase italic leading-none">
                <span v-if="currentStep === 1">Únete como Conductor</span>
                <span v-else-if="currentStep === 2">Tu Vehículo</span>
                <span v-else>Tu Identidad</span>
            </h2>
            <p class="text-xs text-muted-foreground font-bold uppercase tracking-wider mt-1">
                Paso {{ currentStep }} de {{ steps.length }}
            </p>
        </div>

        <div class="flex-1 overflow-y-auto scrollbar-thin px-1">
            <form @submit.prevent="submit" class="h-full flex flex-col">
                
                <div v-show="currentStep === 1" class="space-y-5 animate-in">
                    <div class="form-group">
                        <label class="form-label flex items-center gap-2">
                            <Smartphone :size="14" /> Celular *
                        </label>
                        <vue-tel-input 
                            v-bind="telOptions"
                            v-model="form.phone" 
                            @on-input="onInput" 
                            class="custom-tel-input" 
                            :class="{'form-input-error': step1Errors.phone}"
                        />
                        <p v-if="step1Errors.phone" class="form-error">
                            {{ step1Errors.phone[0] }}
                        </p>
                    </div>
                    <div class="form-group">
                        <BaseInput v-model="form.email" 
                                   type="email" 
                                   label="Correo Electrónico *" 
                                   placeholder="conductor@ejemplo.com"
                                   :error="step1Errors.email ? step1Errors.email[0] : ''">
                            <template #icon>
                                <Mail :size="14" />
                            </template>
                        </BaseInput>
                    </div>

                    <div class="space-y-4">
                        <BaseInput v-model="form.password" 
                                    type="password" 
                                    label="Contraseña *" 
                                    placeholder="Mínimo 8 caracteres"
                                    :error="step1Errors.password ? step1Errors.password[0] : ''">
                            <template #icon><Lock :size="14" /></template>
                        </BaseInput>

                        <BaseInput v-model="form.password_confirmation" 
                                    type="password" 
                                    label="Confirmar Contraseña *">
                            <template #icon><Lock :size="14" /></template>
                        </BaseInput>
                    </div>

                    <div class="alert alert-warning">
                        <BaseCheckbox v-model="form.terms" class="w-full">
                            <template #default>
                                Acepto los 
                                <a href="#" class="font-bold text-warning hover:underline">Términos de Conductor</a>
                            </template>
                        </BaseCheckbox>
                    </div>

                    <div class="text-center pt-2">
                        <button type="button" 
                                @click="$emit('switchToClient')" 
                                class="text-xs text-muted-foreground font-bold uppercase hover:text-primary transition flex items-center justify-center gap-2 w-full">
                            <User :size="14" />
                            ¿Eres cliente? Regístrate aquí
                        </button>
                    </div>
                </div>

                <div v-show="currentStep === 2" class="space-y-5 animate-in">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <BaseInput v-model="form.first_name" label="Nombre *" placeholder="Juan" />
                        <BaseInput v-model="form.last_name" label="Apellido *" placeholder="Pérez" />
                    </div>

                    <div class="form-group">
                        <label class="form-label">Tipo de Vehículo *</label>
                        <div class="grid grid-cols-3 gap-3">
                            <div v-for="vehicle in vehicleTypes" 
                                    :key="vehicle.id"
                                    @click="form.vehicle_type = vehicle.id" 
                                    :class="[
                                        'cursor-pointer flex flex-col items-center justify-center p-3 rounded-xl border-2 transition-all hover-lift',
                                        form.vehicle_type === vehicle.id 
                                            ? 'border-warning bg-warning/10 text-warning shadow-sm ring-2 ring-warning/20' 
                                            : 'border-border hover:border-warning/50 text-muted-foreground hover:text-foreground'
                                    ]">
                                <component :is="vehicle.icon" :size="24" />
                                <span class="text-xs font-bold mt-2">{{ vehicle.label }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-muted/30 p-4 rounded-xl border border-border">
                        <div class="flex items-center gap-2 mb-3">
                            <FileText :size="16" class="text-warning" />
                            <h3 class="text-xs font-bold uppercase tracking-wider text-muted-foreground">
                                Documentación
                            </h3>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <BaseInput v-model="form.license_number" label="Nº Licencia *" placeholder="12345 LP" class="font-mono" />
                            <BaseInput v-model="form.license_plate" label="Placa *" placeholder="2020-XXX" class="font-mono uppercase" />
                        </div>
                    </div>

                    <div class="alert alert-warning">
                        <div class="flex gap-3">
                            <Shield :size="18" class="mt-0.5 shrink-0" />
                            <div>
                                <p class="text-sm font-bold">Verificación requerida</p>
                                <p class="text-xs opacity-90 leading-relaxed mt-0.5">
                                    Tu cuenta será verificada por administración (aprox. 24hrs).
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-show="currentStep === 3" class="space-y-6 animate-in">
                    <div class="text-center">
                        <p class="text-sm text-muted-foreground mb-4">
                            Elige una imagen para que los clientes te reconozcan.
                        </p>
                        
                        <div class="grid grid-cols-3 gap-4 mb-6 justify-items-center px-2">
                            <div v-for="i in 6" :key="i" 
                                    @click="selectIcon(`avatar_${i}.svg`)" 
                                    :class="[
                                        'cursor-pointer w-16 h-16 rounded-full border-2 transition-all flex justify-center items-center bg-muted/30 hover-lift',
                                        (form.avatar_type === 'icon' && form.avatar_source === `avatar_${i}.svg`) 
                                            ? 'border-warning ring-4 ring-warning/20 scale-105' 
                                            : 'border-transparent hover:border-border'
                                    ]">
                                <img :src="`/assets/avatars/avatar_${i}.svg`" class="w-12 h-12" :alt="`Avatar ${i}`" />
                            </div>
                        </div>

                        <ImageUploader v-model="form.avatar_file" 
                                        aspect-ratio="square">
                            <template #label>
                                <span class="text-xs font-bold uppercase text-warning hover:underline">
                                    Subir Foto Personal
                                </span>
                            </template>
                        </ImageUploader>
                    </div>
                </div>

                <div class="mt-auto pt-6 flex gap-3">
                    <button v-if="currentStep > 1" 
                            type="button" 
                            @click="prevStep" 
                            class="btn btn-outline btn-md flex-1">
                        <ArrowLeft :size="16" /> Atrás
                    </button>

                    <button v-if="currentStep < 3" 
                            type="button" 
                            @click="nextStep" 
                            :disabled="currentStep === 1 && !form.terms"
                            class="btn btn-warning btn-md flex-1 hover-lift shadow-lg shadow-warning/20">
                        <span v-if="validatingStep1" class="spinner spinner-sm mr-2"></span>
                        <span v-else>Siguiente <ArrowRight :size="16" class="ml-1 inline" /></span>
                    </button>

                    <button v-else 
                            type="submit" 
                            :disabled="form.processing"
                            class="btn btn-warning btn-md flex-1 hover-lift shadow-lg shadow-warning/20">
                        <span v-if="form.processing" class="spinner spinner-sm mr-2"></span>
                        <span v-else class="flex items-center justify-center gap-2">
                            <CheckCircle :size="16" /> Enviar Solicitud
                        </span>
                    </button>
                </div>
            </form>
        </div>

        <div class="mt-4 pt-4 border-t border-border text-center">
            <button @click="$emit('switchToLogin')" 
                    class="btn btn-ghost btn-sm text-muted-foreground hover:text-foreground">
                ¿Ya tienes cuenta? Inicia Sesión
            </button>
        </div>
    </div>
</template>

<style scoped>
.custom-tel-input { 
    border: 1px solid hsl(var(--input));
    border-radius: var(--radius-lg);
    padding: 2px 0;
    background: hsl(var(--background));
    transition: all var(--duration-fast) var(--ease-smooth);
}

.custom-tel-input:focus-within {
    border-color: hsl(var(--warning));
    box-shadow: 0 0 0 2px hsl(var(--warning) / 0.2);
}

.custom-tel-input.form-input-error {
    border-color: hsl(var(--error));
}

.custom-tel-input.form-input-error:focus-within {
    border-color: hsl(var(--error));
    box-shadow: 0 0 0 2px hsl(var(--error) / 0.2);
}
</style>