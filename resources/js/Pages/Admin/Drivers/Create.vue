<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useForm, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { 
    Save, ArrowLeft, ArrowRight, UserPlus, Truck, ShieldCheck, Building2,
    Cpu, Terminal, Fingerprint, Phone, MapPin, Bike, Car,
    Eye, EyeOff, AlertTriangle, CheckCircle2, Zap, Wifi
} from 'lucide-vue-next';
import { VueTelInput } from 'vue-tel-input';
import 'vue-tel-input/vue-tel-input.css';

const props = defineProps({
    branches: Array
});

const showPassword = ref(false);
const currentStep = ref(1);

const steps = [
    { id: 1, title: 'DATOS_PERSONALES', code: 'SEC_01', icon: UserPlus },
    { id: 2, title: 'CREDENCIALES', code: 'SEC_02', icon: ShieldCheck },
    { id: 3, title: 'DATOS_OPERATIVOS', code: 'SEC_03', icon: Truck },
];

const form = useForm({
    branch_id: '',
    first_name: '',
    last_name: '',
    phone: '',
    password: '',
    license_number: '',
    license_plate: '',
    vehicle_type: 'moto',
});

// REGLA ESTRICTA: Solo Bolivia
const telOptions = { 
    mode: 'international', 
    defaultCountry: 'BO', 
    onlyCountries: ['BO'], 
    dropdownOptions: { showSearchBox: false, showFlags: true }, 
    inputOptions: { placeholder: '77712345' } 
};

// Fuerza el formato E.164 en el payload del formulario
const onInput = (phone, obj) => { 
    if(obj?.number) form.phone = obj.number; 
};

// Validación por pasos
const validateStep = () => {
    form.clearErrors();
    let isValid = true;
    
    if (currentStep.value === 1) {
        if (!form.first_name) {
            form.setError('first_name', '// NOMBRE REQUERIDO');
            isValid = false;
        }
        if (!form.last_name) {
            form.setError('last_name', '// APELLIDO REQUERIDO');
            isValid = false;
        }
    }
    
    if (currentStep.value === 2) {
        if (!form.phone) {
            form.setError('phone', '// TELÉFONO REQUERIDO');
            isValid = false;
        }
        if (!form.password || form.password.length < 6) {
            form.setError('password', '// MÍNIMO 6 CARACTERES');
            isValid = false;
        }
    }
    
    if (currentStep.value === 3) {
        if (!form.license_number) {
            form.setError('license_number', '// LICENCIA REQUERIDA');
            isValid = false;
        }
        if (!form.license_plate) {
            form.setError('license_plate', '// PLACA REQUERIDA');
            isValid = false;
        }
    }
    
    return isValid;
};

const nextStep = () => {
    if (validateStep()) {
        if (currentStep.value < steps.length) {
            currentStep.value++;
        }
    } else {
        const card = document.getElementById('wizard-card');
        card?.classList.add('shake');
        setTimeout(() => card?.classList.remove('shake'), 400);
    }
};

const prevStep = () => {
    if (currentStep.value > 1) {
        currentStep.value--;
    }
};

const submit = () => {
    if (validateStep()) {
        form.post(route('admin.drivers.store'), {
            preserveScroll: true,
            onError: (errors) => {
                // Retroceder automáticamente al paso donde ocurrió el error del backend
                if (errors.first_name || errors.last_name || errors.branch_id) {
                    currentStep.value = 1;
                } else if (errors.phone || errors.password) {
                    currentStep.value = 2;
                } else if (errors.license_number || errors.license_plate || errors.vehicle_type) {
                    currentStep.value = 3;
                }
                
                // Hacer vibrar la tarjeta para notificar al usuario
                const card = document.getElementById('wizard-card');
                card?.classList.add('shake');
                setTimeout(() => card?.classList.remove('shake'), 400);
            }
        });
    }
};

const progressPercentage = computed(() => ((currentStep.value - 1) / (steps.length - 1)) * 100);

// Código temporal para nuevo conductor
const tempCode = computed(() => {
    return `DRV_NEW_${String(Math.floor(Math.random() * 1000)).padStart(3, '0')}`;
});

// Icono de vehículo según tipo seleccionado
const getVehicleIcon = (type) => {
    if (type === 'moto') return Bike;
    if (type === 'car') return Car;
    return Truck;
};

// Tipo de vehículo en texto
const vehicleTypeLabel = (type) => {
    const types = {
        moto: 'MOTOCICLETA',
        car: 'AUTOMÓVIL',
        truck: 'CAMIONETA'
    };
    return types[type] || type.toUpperCase();
};
</script>

<template>
    <AdminLayout>
        <div class="px-4 md:px-6 lg:px-8 py-6 max-w-4xl mx-auto">
            
            <!-- Header -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8 border-b border-primary/30 pb-6 relative group/header">
                <!-- Efecto de escaneo -->
                <div class="absolute inset-0 bg-gradient-to-r from-transparent via-primary/5 to-transparent translate-x-[-100%] group-hover/header:translate-x-[100%] transition-transform duration-1000"></div>
                
                <div class="flex items-center gap-4 relative z-10">
                    <Link :href="route('admin.drivers.index')" 
                          class="p-2 border border-border hover:border-primary hover:shadow-neon-primary transition-all relative group/back">
                        <ArrowLeft :size="20" class="group-hover/back:text-primary transition-colors" />
                        <!-- Esquinas decorativas -->
                        <span class="absolute top-0 left-0 w-1 h-1 border-t border-l border-primary opacity-0 group-hover/back:opacity-100"></span>
                        <span class="absolute top-0 right-0 w-1 h-1 border-t border-r border-primary opacity-0 group-hover/back:opacity-100"></span>
                        <span class="absolute bottom-0 left-0 w-1 h-1 border-b border-l border-primary opacity-0 group-hover/back:opacity-100"></span>
                        <span class="absolute bottom-0 right-0 w-1 h-1 border-b border-r border-primary opacity-0 group-hover/back:opacity-100"></span>
                    </Link>
                    
                    <div class="relative group/title">
                        <h1 class="text-2xl font-display font-black tracking-widest text-primary uppercase glitch-text drop-shadow-[0_0_12px_hsl(var(--primary)/0.6)] leading-none"
                            data-text="REGISTRAR CONDUCTOR">
                            REGISTRAR CONDUCTOR
                        </h1>
                        <p class="text-[10px] font-mono text-muted-foreground mt-1 flex items-center gap-2">
                            <Cpu :size="12" class="text-primary animate-pulse" />
                            <span>{{ tempCode }} // CREACIÓN MANUAL</span>
                            <Terminal :size="12" class="text-primary animate-pulse" />
                        </p>
                        <!-- Línea de escaneo -->
                        <div class="absolute -bottom-2 left-0 w-0 h-[1px] bg-primary group-hover/title:w-full transition-all duration-700"></div>
                    </div>
                </div>
                
                <button @click="submit" :disabled="form.processing" 
                        class="px-6 py-3 bg-primary text-primary-foreground font-mono text-xs border border-primary/50 relative overflow-hidden group/save">
                    <span v-if="form.processing" class="flex items-center gap-2">
                        <Cpu :size="16" class="animate-spin" /> GUARDANDO...
                    </span>
                    <span v-else class="flex items-center gap-2 relative z-10">
                        <Save :size="16" /> GUARDAR CONDUCTOR
                    </span>
                    <!-- Efecto scan -->
                    <span class="absolute inset-0 bg-primary-foreground/10 translate-y-full group-hover/save:translate-y-0 transition-transform duration-500"></span>
                    <!-- Esquinas -->
                    <span class="absolute top-0 left-0 w-1 h-1 border-t border-l border-primary-foreground/50"></span>
                    <span class="absolute top-0 right-0 w-1 h-1 border-t border-r border-primary-foreground/50"></span>
                    <span class="absolute bottom-0 left-0 w-1 h-1 border-b border-l border-primary-foreground/50"></span>
                    <span class="absolute bottom-0 right-0 w-1 h-1 border-b border-r border-primary-foreground/50"></span>
                </button>
            </div>

            <!-- Progress Bar -->
            <div class="mb-8 px-4 font-mono">
                <div class="flex justify-between items-end mb-2 text-[10px] font-black uppercase">
                    <span class="text-primary">FASE_{{ String(currentStep).padStart(2, '0') }} // {{ steps[currentStep-1].code }}</span>
                    <span class="text-muted-foreground">{{ steps[currentStep-1].title }}</span>
                </div>
                <div class="h-[2px] bg-border relative">
                    <div class="absolute h-full bg-primary shadow-neon-primary transition-all duration-500" :style="{ width: `${progressPercentage}%` }"></div>
                </div>
            </div>

            <!-- Wizard Card -->
            <div id="wizard-card" class="border border-border/50 bg-background shadow-2xl relative group/card">
                
                <!-- Scanline superior -->
                <div class="absolute top-0 left-0 w-full h-[2px] bg-gradient-to-r from-transparent via-primary to-transparent translate-x-[-100%] group-hover/card:translate-x-[100%] transition-transform duration-1000"></div>
                
                <!-- Step Tabs -->
                <div class="flex border-b border-border/50 bg-background/80 backdrop-blur-sm">
                    <div v-for="step in steps" :key="step.id" 
                         class="flex-1 flex items-center justify-center p-3 border-r border-border/50 transition-all relative group/tab"
                         :class="currentStep >= step.id ? 'text-primary' : 'text-muted-foreground'">
                        
                        <div class="flex items-center gap-2">
                            <component :is="step.icon" :size="16" :class="currentStep >= step.id ? 'text-primary' : 'text-muted-foreground'" />
                            <span class="text-[9px] font-mono font-bold uppercase hidden md:inline">{{ step.title }}</span>
                            <span class="text-[8px] font-mono md:hidden">{{ step.code }}</span>
                        </div>
                        
                        <!-- Indicador de paso completado -->
                        <span v-if="currentStep > step.id" 
                              class="absolute -top-1 -right-1 w-2 h-2 bg-primary rounded-full animate-pulse"></span>
                    </div>
                </div>

                <form @submit.prevent="submit" class="p-6 md:p-8">
                    
                    <!-- Step 1: Datos Personales -->
                    <div v-if="currentStep === 1" class="space-y-6 animate-in fade-in slide-in-from-left-4 duration-500">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Nombres -->
                            <div>
                                <label class="text-[10px] font-mono font-bold text-primary uppercase tracking-widest mb-2 block flex items-center gap-1">
                                    <Terminal :size="12" /> // NOMBRES
                                </label>
                                <input v-model="form.first_name" 
                                       type="text" 
                                       class="w-full bg-background border border-border/50 px-4 py-3 font-mono text-sm focus:border-primary focus:shadow-neon-primary outline-none transition-all"
                                       placeholder="INGRESAR NOMBRES" />
                                <p v-if="form.errors.first_name" class="text-[10px] font-mono text-destructive mt-1 flex items-center gap-1">
                                    <AlertTriangle :size="10" /> {{ form.errors.first_name }}
                                </p>
                            </div>
                            
                            <!-- Apellidos -->
                            <div>
                                <label class="text-[10px] font-mono font-bold text-primary uppercase tracking-widest mb-2 block flex items-center gap-1">
                                    <Terminal :size="12" /> // APELLIDOS
                                </label>
                                <input v-model="form.last_name" 
                                       type="text" 
                                       class="w-full bg-background border border-border/50 px-4 py-3 font-mono text-sm focus:border-primary focus:shadow-neon-primary outline-none transition-all"
                                       placeholder="INGRESAR APELLIDOS" />
                                <p v-if="form.errors.last_name" class="text-[10px] font-mono text-destructive mt-1 flex items-center gap-1">
                                    <AlertTriangle :size="10" /> {{ form.errors.last_name }}
                                </p>
                            </div>
                        </div>

                        <!-- Base/Sucursal -->
                        <div>
                            <label class="text-[10px] font-mono font-bold text-primary uppercase tracking-widest mb-2 block flex items-center gap-1">
                                <Building2 :size="12" /> // BASE / SUCURSAL
                            </label>
                            <select v-model="form.branch_id" 
                                    class="w-full bg-background border border-border/50 px-4 py-3 font-mono text-sm focus:border-primary focus:shadow-neon-primary outline-none transition-all">
                                <option value="" disabled selected>SELECCIONAR BASE</option>
                                <option v-for="branch in branches" :key="branch.id" :value="branch.id">
                                    {{ branch.name }}
                                </option>
                            </select>
                            <p v-if="form.errors.branch_id" class="text-[10px] font-mono text-destructive mt-1 flex items-center gap-1">
                                <AlertTriangle :size="10" /> {{ form.errors.branch_id }}
                            </p>
                        </div>

                        <!-- Info Box -->
                        <div class="border border-dashed border-primary/30 p-4 bg-primary/5">
                            <p class="text-[8px] font-mono text-muted-foreground flex items-center gap-2">
                                <Zap :size="12" class="text-primary" />
                                EL CONDUCTOR NACERÁ VERIFICADO Y ACTIVO POR DEFECTO EN EL SISTEMA.
                            </p>
                        </div>
                    </div>

                    <!-- Step 2: Credenciales -->
                    <div v-if="currentStep === 2" class="space-y-6 animate-in fade-in slide-in-from-right-4 duration-500">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Teléfono -->
                            <div class="space-y-2 relative group/phone">
                                <label class="text-[10px] font-mono font-bold text-primary uppercase tracking-widest mb-2 block flex items-center gap-1">
                                    <Phone :size="12" /> // TELÉFONO (BO)
                                </label>
                                <div class="relative">
                                    <vue-tel-input 
                                        v-model="form.phone" 
                                        @on-input="onInput"
                                        v-bind="telOptions"
                                        class="cyber-tel-input w-full"
                                        :class="{ 'error': form.errors.phone }"
                                    />
                                    <!-- Esquinas decorativas -->
                                    <span class="absolute top-0 left-0 w-1 h-1 border-t border-l border-primary opacity-0 group-focus-within/phone:opacity-100"></span>
                                    <span class="absolute top-0 right-0 w-1 h-1 border-t border-r border-primary opacity-0 group-focus-within/phone:opacity-100"></span>
                                    <span class="absolute bottom-0 left-0 w-1 h-1 border-b border-l border-primary opacity-0 group-focus-within/phone:opacity-100"></span>
                                    <span class="absolute bottom-0 right-0 w-1 h-1 border-b border-r border-primary opacity-0 group-focus-within/phone:opacity-100"></span>
                                </div>
                                <p v-if="form.errors.phone" class="text-[10px] font-mono text-destructive mt-1 flex items-center gap-1">
                                    <AlertTriangle :size="10" /> {{ form.errors.phone }}
                                </p>
                            </div>

                            <!-- Contraseña -->
                            <div class="relative group/password">
                                <label class="text-[10px] font-mono font-bold text-primary uppercase tracking-widest mb-2 block flex items-center gap-1">
                                    <ShieldCheck :size="12" /> // CONTRASEÑA INICIAL
                                </label>
                                <div class="relative">
                                    <input v-model="form.password" 
                                           :type="showPassword ? 'text' : 'password'"
                                           class="w-full bg-background border border-border/50 px-4 py-3 font-mono text-sm focus:border-primary focus:shadow-neon-primary outline-none transition-all pr-10"
                                           placeholder="··········" />
                                    <button @click="showPassword = !showPassword" 
                                            type="button"
                                            class="absolute right-2 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-primary transition-colors">
                                        <Eye v-if="!showPassword" :size="16" />
                                        <EyeOff v-else :size="16" />
                                    </button>
                                </div>
                                <p v-if="form.errors.password" class="text-[10px] font-mono text-destructive mt-1 flex items-center gap-1">
                                    <AlertTriangle :size="10" /> {{ form.errors.password }}
                                </p>
                                <!-- Requisitos -->
                                <div class="text-[8px] font-mono text-muted-foreground mt-2 px-1">
                                    <span class="text-primary">//</span> MÍNIMO 6 CARACTERES
                                </div>
                            </div>
                        </div>

                        <!-- Info Box -->
                        <div class="border border-dashed border-primary/30 p-4 bg-primary/5">
                            <p class="text-[8px] font-mono text-muted-foreground flex items-center gap-2">
                                <Wifi :size="12" class="text-primary" />
                                EL TELÉFONO SERÁ EL ID DE ACCESO DEL CONDUCTOR A LA APLICACIÓN MÓVIL.
                            </p>
                        </div>
                    </div>

                    <!-- Step 3: Datos Operativos -->
                    <div v-if="currentStep === 3" class="space-y-6 animate-in fade-in slide-in-from-right-4 duration-500">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <!-- Número de Licencia -->
                            <div>
                                <label class="text-[10px] font-mono font-bold text-primary uppercase tracking-widest mb-2 block flex items-center gap-1">
                                    <Fingerprint :size="12" /> // LICENCIA
                                </label>
                                <input v-model="form.license_number" 
                                       type="text" 
                                       class="w-full bg-background border border-border/50 px-4 py-3 font-mono text-sm focus:border-primary focus:shadow-neon-primary outline-none transition-all"
                                       placeholder="NÚMERO DE LICENCIA" />
                                <p v-if="form.errors.license_number" class="text-[10px] font-mono text-destructive mt-1 flex items-center gap-1">
                                    <AlertTriangle :size="10" /> {{ form.errors.license_number }}
                                </p>
                            </div>

                            <!-- Placa del Vehículo -->
                            <div>
                                <label class="text-[10px] font-mono font-bold text-primary uppercase tracking-widest mb-2 block flex items-center gap-1">
                                    <MapPin :size="12" /> // PLACA
                                </label>
                                <input v-model="form.license_plate" 
                                       type="text" 
                                       class="w-full bg-background border border-border/50 px-4 py-3 font-mono text-sm focus:border-primary focus:shadow-neon-primary outline-none transition-all"
                                       placeholder="PLACA DEL VEHÍCULO" />
                                <p v-if="form.errors.license_plate" class="text-[10px] font-mono text-destructive mt-1 flex items-center gap-1">
                                    <AlertTriangle :size="10" /> {{ form.errors.license_plate }}
                                </p>
                            </div>

                            <!-- Tipo de Vehículo -->
                            <div>
                                <label class="text-[10px] font-mono font-bold text-primary uppercase tracking-widest mb-2 block flex items-center gap-1">
                                    <Truck :size="12" /> // TIPO
                                </label>
                                <select v-model="form.vehicle_type" 
                                        class="w-full bg-background border border-border/50 px-4 py-3 font-mono text-sm focus:border-primary focus:shadow-neon-primary outline-none transition-all">
                                    <option value="moto">MOTOCICLETA</option>
                                    <option value="car">AUTOMÓVIL</option>
                                    <option value="truck">CAMIONETA</option>
                                </select>
                                <p v-if="form.errors.vehicle_type" class="text-[10px] font-mono text-destructive mt-1 flex items-center gap-1">
                                    <AlertTriangle :size="10" /> {{ form.errors.vehicle_type }}
                                </p>
                            </div>
                        </div>

                        <!-- Resumen del vehículo -->
                        <div v-if="form.vehicle_type" class="border border-primary/30 p-4 bg-primary/5">
                            <div class="flex items-center gap-4">
                                <div class="p-2 border border-primary/30 bg-background">
                                    <component :is="getVehicleIcon(form.vehicle_type)" :size="32" class="text-primary" />
                                </div>
                                <div>
                                    <p class="text-sm font-mono text-primary">{{ vehicleTypeLabel(form.vehicle_type) }}</p>
                                    <p class="text-[8px] font-mono text-muted-foreground mt-1">
                                        {{ form.license_plate || 'SIN PLACA' }} // {{ form.license_number || 'SIN LICENCIA' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                </form>

                <!-- Footer Actions -->
                <div class="border-t border-border/50 bg-background/80 backdrop-blur-sm p-4 flex justify-between items-center">
                    <button type="button" @click="prevStep" :disabled="currentStep === 1" 
                            class="px-6 py-2 border border-border text-[10px] font-mono font-bold uppercase hover:border-primary hover:text-primary transition-all disabled:opacity-30 disabled:pointer-events-none relative group/prev">
                        <span class="flex items-center gap-2">
                            <ArrowLeft :size="14" class="group-hover/prev:-translate-x-1 transition-transform" />
                            ANTERIOR
                        </span>
                        <span class="absolute top-0 left-0 w-1 h-1 border-t border-l border-primary opacity-0 group-hover/prev:opacity-100"></span>
                        <span class="absolute top-0 right-0 w-1 h-1 border-t border-r border-primary opacity-0 group-hover/prev:opacity-100"></span>
                        <span class="absolute bottom-0 left-0 w-1 h-1 border-b border-l border-primary opacity-0 group-hover/prev:opacity-100"></span>
                        <span class="absolute bottom-0 right-0 w-1 h-1 border-b border-r border-primary opacity-0 group-hover/prev:opacity-100"></span>
                    </button>

                    <button v-if="currentStep < steps.length" type="button" @click="nextStep" 
                            class="px-8 py-2 bg-primary text-primary-foreground text-[10px] font-mono font-black uppercase shadow-neon-primary hover:bg-primary/90 transition-all relative group/next overflow-hidden">
                        <span class="flex items-center gap-2 relative z-10">
                            SIGUIENTE <ArrowRight :size="14" class="group-hover/next:translate-x-1 transition-transform" />
                        </span>
                        <span class="absolute inset-0 bg-primary-foreground/10 translate-y-full group-hover/next:translate-y-0 transition-transform duration-500"></span>
                        <span class="absolute top-0 left-0 w-1 h-1 border-t border-l border-primary-foreground/50"></span>
                        <span class="absolute top-0 right-0 w-1 h-1 border-t border-r border-primary-foreground/50"></span>
                        <span class="absolute bottom-0 left-0 w-1 h-1 border-b border-l border-primary-foreground/50"></span>
                        <span class="absolute bottom-0 right-0 w-1 h-1 border-b border-r border-primary-foreground/50"></span>
                    </button>

                    <button v-else type="button" @click="submit" :disabled="form.processing"
                            class="px-8 py-2 bg-primary text-primary-foreground text-[10px] font-mono font-black uppercase shadow-neon-primary hover:bg-primary/90 transition-all relative group/submit overflow-hidden">
                        <span v-if="form.processing" class="flex items-center gap-2">
                            <Cpu :size="14" class="animate-spin" /> PROCESANDO...
                        </span>
                        <span v-else class="flex items-center gap-2 relative z-10">
                            <Save :size="14" /> CREAR CONDUCTOR
                        </span>
                        <span class="absolute inset-0 bg-primary-foreground/10 translate-y-full group-hover/submit:translate-y-0 transition-transform duration-500"></span>
                    </button>
                </div>
            </div>

            <!-- Session ID -->
            <div class="mt-4 text-center">
                <p class="text-[8px] font-mono text-muted-foreground">
                    SESSION_ID // {{ tempCode }}_CREATE_{{ String(currentStep).padStart(2, '0') }} // {{ new Date().toISOString().slice(0,10) }}
                </p>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
/* Animaciones */
@keyframes shake {
    10%, 90% { transform: translate3d(-1px, 0, 0); }
    20%, 80% { transform: translate3d(2px, 0, 0); }
    30%, 50%, 70% { transform: translate3d(-4px, 0, 0); }
    40%, 60% { transform: translate3d(4px, 0, 0); }
}

.shake {
    animation: shake 0.4s cubic-bezier(.36,.07,.19,.97) both;
}

@keyframes scanline {
    0% { transform: translateY(-100%); }
    100% { transform: translateY(1000%); }
}

.animate-scanline {
    animation: scanline 8s linear infinite;
}

/* Efecto glitch */
.glitch-text {
    position: relative;
    animation: glitch-skew 4s infinite linear alternate-reverse;
}

.glitch-text::before,
.glitch-text::after {
    content: attr(data-text);
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0.8;
}

.glitch-text::before {
    color: #0ff;
    z-index: -1;
    animation: glitch-anim-1 0.4s infinite linear alternate-reverse;
}

.glitch-text::after {
    color: #f0f;
    z-index: -2;
    animation: glitch-anim-2 0.4s infinite linear alternate-reverse;
}

@keyframes glitch-skew {
    0% { transform: skew(0deg); }
    20% { transform: skew(0deg); }
    21% { transform: skew(2deg); }
    22% { transform: skew(0deg); }
    80% { transform: skew(0deg); }
    81% { transform: skew(-2deg); }
    82% { transform: skew(0deg); }
    100% { transform: skew(0deg); }
}

@keyframes glitch-anim-1 {
    0% { clip-path: inset(20% 0 30% 0); }
    20% { clip-path: inset(50% 0 10% 0); }
    40% { clip-path: inset(10% 0 60% 0); }
    60% { clip-path: inset(80% 0 5% 0); }
    80% { clip-path: inset(30% 0 40% 0); }
    100% { clip-path: inset(40% 0 20% 0); }
}

@keyframes glitch-anim-2 {
    0% { clip-path: inset(60% 0 10% 0); }
    20% { clip-path: inset(20% 0 50% 0); }
    40% { clip-path: inset(10% 0 60% 0); }
    60% { clip-path: inset(80% 0 5% 0); }
    80% { clip-path: inset(40% 0 30% 0); }
    100% { clip-path: inset(30% 0 40% 0); }
}

/* Sombras neón */
.shadow-neon-primary {
    box-shadow: 0 0 20px hsl(var(--primary) / 0.3);
}

/* Estilos para el tel input */
:deep(.vue-tel-input) {
    font-family: 'JetBrains Mono', monospace;
    border-color: hsl(var(--border) / 0.5);
    background-color: hsl(var(--background));
    border-radius: 0;
    height: 48px;
}

:deep(.vue-tel-input:focus-within) {
    border-color: hsl(var(--primary));
    box-shadow: 0 0 0 2px hsl(var(--primary) / 0.1), 0 0 20px hsl(var(--primary) / 0.2);
}

:deep(.vti__dropdown) {
    background-color: hsl(var(--background));
    border-right-color: hsl(var(--border) / 0.5);
}

:deep(.vti__input) {
    font-family: 'JetBrains Mono', monospace;
    background-color: hsl(var(--background));
}

:deep(.error.vue-tel-input) {
    border-color: hsl(var(--destructive));
}

:deep(.vti__flag) {
    transform: scale(1.2);
}
</style>