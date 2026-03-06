<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useForm, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { 
    User, Shield, ArrowRight, ArrowLeft, Save, Building, Lock, 
    RefreshCw, X, Mail, Phone, AlertTriangle, CheckCircle2, 
    ChevronRight, Fingerprint, Cpu, Terminal, Zap, Eye, EyeOff
} from 'lucide-vue-next';
import BaseInput from '@/Components/Base/BaseInput.vue';
import BaseSelect from '@/Components/Base/BaseSelect.vue';

const props = defineProps({ 
    user: Object, 
    roles: Array, 
    branches: Array 
});

const steps = [
    { id: 1, title: 'IDENTIDAD', icon: User, code: 'SEC_01' },
    { id: 2, title: 'PERMISOS', icon: Shield, code: 'SEC_02' },
];

const currentStep = ref(1);
const isChangingPassword = ref(false);
const showPassword = ref(false);

// Código de identificación del usuario
const userCode = computed(() => {
    return `USR_${String(props.user.id).padStart(4, '0')}`;
});

const form = useForm({
    // Verificamos que usemos los nombres exactos definidos en el Resource
    first_name: props.user.data?.first_name || props.user.first_name || '',
    last_name:  props.user.data?.last_name  || props.user.last_name  || '',
    email:      props.user.data?.email      || props.user.email      || '',
    phone:      props.user.data?.phone      || props.user.phone      || '',
    password:   '', // Siempre vacío por seguridad
    branch_id:  props.user.data?.branch_id  || props.user.branch_id  || null,
    is_active:  props.user.data ? !!props.user.data.is_active : !!props.user.is_active
});
const validateStep1 = () => {
    form.clearErrors();
    let isValid = true;
    if (!form.first_name) { 
        form.setError('first_name', '// NOMBRE REQUERIDO'); 
        isValid = false; 
    }
    if (!form.last_name) { 
        form.setError('last_name', '// APELLIDO REQUERIDO'); 
        isValid = false; 
    }
    if (!form.phone) { 
        form.setError('phone', '// TELÉFONO REQUERIDO'); 
        isValid = false; 
    }
    if (isChangingPassword.value && (!form.password || form.password.length < 6)) {
        form.setError('password', '// MÍNIMO 6 CARACTERES');
        isValid = false;
    }
    return isValid;
};

const nextStep = () => {
    if (currentStep.value === 1) {
        if (validateStep1()) {
            currentStep.value = 2;
        } else {
            const card = document.getElementById('wizard-card');
            card?.classList.add('shake');
            setTimeout(() => card?.classList.remove('shake'), 400);
        }
    }
};

const submit = () => {
    form.put(route('admin.users.update', props.user.id), {
        preserveScroll: true,
        onError: (errors) => {
            if (errors.first_name || errors.last_name || errors.email || errors.phone || errors.password) {
                currentStep.value = 1;
            }
        },
        onSuccess: () => { 
            isChangingPassword.value = false; 
            form.password = ''; 
        }
    });
};

// <--- AÑADIR ESTO JUSTO DESPUÉS DE SUBMIT --->
const getError = (field) => {
    const error = form.errors[field];
    return Array.isArray(error) ? error[0] : error;
};
// Determinar color de estado
const statusColor = computed(() => {
    return form.is_active ? 'text-cyan-500' : 'text-magenta-500';
});

const statusText = computed(() => {
    return form.is_active ? 'ACTIVE' : 'LOCKED';
});
</script>

<template>
    <AdminLayout>
        
        <template #header>
            <div class="flex items-center gap-3 pt-1 mb-6">
                <Link :href="route('admin.users.index')" 
                      class="p-2 border border-border hover:border-primary hover:shadow-neon-primary transition-all relative group/back">
                    <ArrowLeft :size="20" class="group-hover/back:text-primary transition-colors" />
                    <!-- Esquinas decorativas -->
                    <span class="absolute top-0 left-0 w-1 h-1 border-t border-l border-primary opacity-0 group-hover/back:opacity-100 transition-opacity"></span>
                    <span class="absolute top-0 right-0 w-1 h-1 border-t border-r border-primary opacity-0 group-hover/back:opacity-100 transition-opacity"></span>
                    <span class="absolute bottom-0 left-0 w-1 h-1 border-b border-l border-primary opacity-0 group-hover/back:opacity-100 transition-opacity"></span>
                    <span class="absolute bottom-0 right-0 w-1 h-1 border-b border-r border-primary opacity-0 group-hover/back:opacity-100 transition-opacity"></span>
                </Link>
                <div class="relative group/title">
                    <h1 class="text-2xl font-display font-black tracking-tight text-foreground leading-none glitch-text" 
                        data-text="EDITAR USUARIO">
                        EDITAR USUARIO
                    </h1>
                    <p class="text-[10px] font-mono text-primary mt-0.5 flex items-center gap-2">
                        <Cpu :size="12" class="animate-pulse" />
                        <span>{{ userCode }} // {{ props.user.first_name }} {{ props.user.last_name }}</span>
                        <Terminal :size="12" class="animate-pulse" />
                    </p>
                    <!-- Línea de escaneo -->
                    <div class="absolute -bottom-2 left-0 w-0 h-[1px] bg-primary group-hover/title:w-full transition-all duration-700"></div>
                </div>
            </div>
        </template>

        <div id="wizard-card" class="max-w-2xl mx-auto pb-32 md:pb-12">
            
            <!-- Progress Steps estilo ciberpunk -->
            <div class="relative mb-8">
                <!-- Línea base -->
                <div class="absolute top-5 left-0 right-0 h-[2px] bg-border"></div>
                <!-- Línea de progreso -->
                <div class="absolute top-5 left-0 h-[2px] bg-primary transition-all duration-500 shadow-neon-primary"
                     :style="{ width: currentStep === 2 ? '100%' : '50%' }"></div>

                <div class="relative flex justify-between">
                    <div v-for="(step, index) in steps" :key="step.id" 
                         class="flex flex-col items-center">
                        <!-- Círculo del paso -->
                        <button @click="currentStep >= step.id ? currentStep = step.id : null"
                                :disabled="currentStep < step.id"
                                class="relative group/step">
                            <div class="w-10 h-10 flex items-center justify-center border-2 transition-all duration-500 relative overflow-hidden"
                                 :class="currentStep >= step.id 
                                    ? 'border-primary bg-primary/10 text-primary shadow-neon-primary' 
                                    : 'border-border bg-card text-muted-foreground'">
                                <component :is="step.icon" :size="18" class="relative z-10" />
                                <!-- Efecto de escaneo -->
                                <div class="absolute inset-0 bg-gradient-to-b from-transparent via-primary/20 to-transparent translate-y-[-100%] group-hover/step:translate-y-[100%] transition-transform duration-700"></div>
                            </div>
                            <!-- Código del paso (tooltip) -->
                            <span class="absolute -top-6 left-1/2 -translate-x-1/2 text-[8px] font-mono text-primary opacity-0 group-hover/step:opacity-100 transition-opacity">
                                {{ step.code }}
                            </span>
                        </button>
                        
                        <!-- Título del paso -->
                        <span class="text-[10px] font-mono font-bold mt-2 transition-colors duration-500"
                              :class="currentStep >= step.id ? 'text-primary' : 'text-muted-foreground'">
                            {{ step.title }}
                        </span>
                        
                        <!-- Indicador de paso completado -->
                        <span v-if="currentStep > step.id" 
                              class="absolute -right-1 -top-1 w-3 h-3 bg-primary rounded-full border-2 border-background">
                        </span>
                    </div>
                </div>
            </div>

            <!-- Main Card -->
            <div class="bg-card border border-border/50 shadow-2xl overflow-hidden relative group/card">
                <!-- Efecto de scanline en el borde -->
                <div class="absolute inset-0 pointer-events-none opacity-0 group-hover/card:opacity-100 transition-opacity duration-500">
                    <div class="absolute top-0 left-0 w-full h-[1px] bg-primary/30 animate-scanline"></div>
                </div>
                
                <!-- Esquinas decorativas grandes -->
                <div class="absolute top-0 left-0 w-4 h-4 border-t-2 border-l-2 border-primary/30"></div>
                <div class="absolute top-0 right-0 w-4 h-4 border-t-2 border-r-2 border-primary/30"></div>
                <div class="absolute bottom-0 left-0 w-4 h-4 border-b-2 border-l-2 border-primary/30"></div>
                <div class="absolute bottom-0 right-0 w-4 h-4 border-b-2 border-r-2 border-primary/30"></div>

                <div class="p-6 md:p-8">
                    
                    <!-- Step 1: Identidad -->
                    <div v-show="currentStep === 1" class="space-y-6 animate-in fade-in slide-in-from-left-4 duration-500">
                        
                        <!-- Status Card -->
                        <div class="border border-border/50 p-4 relative group/status">
                            <!-- Scanline interna -->
                            <div class="absolute inset-0 bg-gradient-to-r from-transparent via-primary/5 to-transparent translate-x-[-100%] group-hover/status:translate-x-[100%] transition-transform duration-1000"></div>
                            
                            <div class="flex items-center justify-between relative z-10">
                                <div class="flex items-center gap-3">
                                    <div class="relative">
                                        <div class="w-2.5 h-2.5" :class="statusColor"></div>
                                        <div class="absolute inset-0 animate-ping" :class="statusColor"></div>
                                    </div>
                                    <div>
                                        <p class="text-sm font-mono font-bold text-foreground flex items-center gap-2">
                                            [STATUS] // {{ statusText }}
                                            <span class="text-[8px] px-1 py-0.5 border" :class="statusColor">
                                                v1.0
                                            </span>
                                        </p>
                                        <p class="text-[10px] font-mono text-muted-foreground">
                                            {{ form.is_active ? 'ACCESO_AUTORIZADO' : 'ACCESO_BLOQUEADO' }}
                                        </p>
                                    </div>
                                </div>
                                
                                <!-- Toggle Switch estilo terminal -->
                                <label class="relative inline-flex items-center cursor-pointer group/toggle">
                                    <input type="checkbox" v-model="form.is_active" class="sr-only peer">
                                    <div class="w-12 h-6 bg-muted border border-border peer-checked:border-primary transition-all relative overflow-hidden">
                                        <div class="absolute inset-0 bg-primary/20 translate-x-[-100%] peer-checked:translate-x-0 transition-transform duration-300"></div>
                                    </div>
                                    <div class="absolute left-1 top-1 w-4 h-4 bg-muted-foreground peer-checked:bg-primary peer-checked:translate-x-6 transition-all duration-300"></div>
                                    <!-- Esquinas del toggle -->
                                    <span class="absolute top-0 left-0 w-1 h-1 border-t border-l border-primary opacity-0 peer-checked:opacity-100"></span>
                                    <span class="absolute top-0 right-0 w-1 h-1 border-t border-r border-primary opacity-0 peer-checked:opacity-100"></span>
                                    <span class="absolute bottom-0 left-0 w-1 h-1 border-b border-l border-primary opacity-0 peer-checked:opacity-100"></span>
                                    <span class="absolute bottom-0 right-0 w-1 h-1 border-b border-r border-primary opacity-0 peer-checked:opacity-100"></span>
                                </label>
                            </div>
                        </div>

                        <!-- Campos de nombre -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <BaseInput 
                                v-model="form.first_name" 
                                label="// NOMBRES" 
                                :icon="User" 
                                :error="form.errors.first_name"
                                class="cyber-input"
                            />
                            <BaseInput 
                                v-model="form.last_name" 
                                label="// APELLIDOS" 
                                :error="form.errors.last_name"
                                class="cyber-input"
                            />
                        </div>

                        <!-- Email y Teléfono -->
                        <BaseInput 
                            v-model="form.email" 
                            label="// EMAIL" 
                            :icon="Mail" 
                            type="email" 
                            :error="form.errors.email"
                            class="cyber-input"
                        />
                        
                        <BaseInput 
                            v-model="form.phone" 
                            label="// CELULAR" 
                            :icon="Phone" 
                            :error="form.errors.phone"
                            class="cyber-input"
                        />

                        <!-- Password Section -->
                        <div class="border border-border/50 overflow-hidden transition-all duration-300 relative group/password"
                             :class="isChangingPassword ? 'shadow-neon-primary' : ''">
                            
                            <!-- Modo cerrado -->
                            <div v-if="!isChangingPassword" 
                                 @click="isChangingPassword = true"
                                 class="p-4 cursor-pointer hover:bg-primary/5 transition-colors relative">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="p-2 border border-border/50 text-muted-foreground group-hover/password:text-primary transition-colors">
                                            <Lock :size="16"/>
                                        </div>
                                        <div>
                                            <p class="text-sm font-mono font-bold text-foreground">// CREDENCIALES</p>
                                            <p class="text-[10px] font-mono text-muted-foreground flex items-center gap-1">
                                                <Fingerprint :size="10" />
                                                HASH_ACTIVO // ********
                                            </p>
                                        </div>
                                    </div>
                                    <span class="text-xs font-mono text-primary flex items-center gap-1">
                                        MODIFICAR <ChevronRight :size="14"/>
                                    </span>
                                </div>
                                <!-- Efecto hover -->
                                <div class="absolute inset-0 bg-gradient-to-r from-transparent via-primary/5 to-transparent translate-x-[-100%] group-hover/password:translate-x-[100%] transition-transform duration-700"></div>
                            </div>

                            <!-- Modo abierto -->
                            <div v-else class="p-5 space-y-4 bg-card relative">
                                <!-- Header -->
                                <div class="flex justify-between items-center pb-2 border-b border-primary/30">
                                    <span class="text-xs font-mono font-bold text-primary uppercase tracking-wider flex items-center gap-2">
                                        <RefreshCw :size="12" class="animate-spin-slow" />
                                        NUEVA_CLAVE // INGRESAR
                                    </span>
                                    <button @click="isChangingPassword = false; form.password = ''" 
                                            class="text-muted-foreground hover:text-destructive transition-colors group/close">
                                        <X :size="16" class="group-hover/close:scale-110 transition-transform" />
                                    </button>
                                </div>

                                <!-- Password input con toggle -->
                                <div class="relative">
                                    <BaseInput 
                                        v-model="form.password" 
                                        :type="showPassword ? 'text' : 'password'"
                                        label="// PASSWORD" 
                                        placeholder="··········" 
                                        :error="form.errors.password"
                                        class="cyber-input"
                                    />
                                    <button @click="showPassword = !showPassword"
                                            class="absolute right-2 bottom-2 p-1 text-muted-foreground hover:text-primary transition-colors">
                                        <Eye v-if="!showPassword" :size="16" />
                                        <EyeOff v-else :size="16" />
                                    </button>
                                </div>

                                <!-- Requisitos -->
                                <div class="text-[8px] font-mono text-muted-foreground border-t border-border/30 pt-2 mt-2">
                                    <span class="text-primary">//</span> MIN 6 CARACTERES
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Permisos -->
                    <div v-show="currentStep === 2" class="space-y-6 animate-in fade-in slide-in-from-right-4 duration-500">
                        
                        <!-- Alerta de errores -->
                        <div v-if="form.hasErrors && (form.errors.first_name || form.errors.phone)" 
                             class="border border-destructive/30 bg-destructive/10 p-4 relative group/error"
                             @click="currentStep = 1">
                            <div class="flex items-center gap-3 cursor-pointer">
                                <AlertTriangle class="text-destructive shrink-0 animate-pulse" :size="18"/>
                                <p class="text-xs font-mono font-bold text-destructive">// ERROR // DATOS INCOMPLETOS EN PASO ANTERIOR</p>
                                <ArrowLeft :size="14" class="text-destructive ml-auto group-hover/error:-translate-x-1 transition-transform"/>
                            </div>
                            <!-- Esquinas -->
                            <span class="absolute top-0 left-0 w-1 h-1 border-t border-l border-destructive"></span>
                            <span class="absolute top-0 right-0 w-1 h-1 border-t border-r border-destructive"></span>
                            <span class="absolute bottom-0 left-0 w-1 h-1 border-b border-l border-destructive"></span>
                            <span class="absolute bottom-0 right-0 w-1 h-1 border-b border-r border-destructive"></span>
                        </div>

                        <!-- Roles Section -->
                        <div>
                            <label class="text-xs font-mono font-bold text-primary uppercase tracking-wider mb-3 block flex items-center gap-2">
                                <Zap :size="12" class="text-primary" />
                                // ROL DE SISTEMA
                            </label>
                            <div class="grid grid-cols-1 gap-3">
                                <div v-for="role in roles" :key="role.id" 
                                     @click="form.role_id = role.id"
                                     class="relative p-4 border-2 cursor-pointer transition-all duration-300 group/role overflow-hidden"
                                     :class="form.role_id === role.id 
                                        ? 'border-primary bg-primary/5 shadow-neon-primary' 
                                        : 'border-border/50 hover:border-primary/30 hover:bg-primary/5'">
                                    
                                    <!-- Scanline -->
                                    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-primary/10 to-transparent translate-x-[-100%] group-hover/role:translate-x-[100%] transition-transform duration-700"></div>
                                    
                                    <div class="flex items-center gap-4 relative z-10">
                                        <div class="w-10 h-10 flex items-center justify-center transition-all duration-300 border-2"
                                             :class="form.role_id === role.id 
                                                ? 'border-primary bg-primary text-primary-foreground' 
                                                : 'border-border bg-card text-muted-foreground'">
                                            <Shield :size="18" />
                                        </div>
                                        <div class="flex-1">
                                            <p class="font-mono font-bold text-sm text-foreground group-hover/role:text-primary transition-colors">
                                                {{ role.name.toUpperCase() }}
                                            </p>
                                            <p class="text-[10px] font-mono text-muted-foreground mt-0.5">
                                                {{ role.description || 'ACCESO_ESTÁNDAR' }}
                                            </p>
                                        </div>
                                        <div v-if="form.role_id === role.id" class="text-primary animate-in zoom-in">
                                            <CheckCircle2 :size="18" />
                                        </div>
                                    </div>

                                    <!-- Esquinas -->
                                    <span class="absolute top-0 left-0 w-2 h-2 border-t border-l border-primary/30"></span>
                                    <span class="absolute top-0 right-0 w-2 h-2 border-t border-r border-primary/30"></span>
                                    <span class="absolute bottom-0 left-0 w-2 h-2 border-b border-l border-primary/30"></span>
                                    <span class="absolute bottom-0 right-0 w-2 h-2 border-b border-r border-primary/30"></span>
                                </div>
                            </div>
                            <p v-if="form.errors.role_id" class="text-destructive text-xs font-mono mt-2 flex items-center gap-1">
                                <AlertTriangle :size="12" /> {{ form.errors.role_id }}
                            </p>
                        </div>

                        <!-- Sucursal Section -->
                        <div class="relative group/branch">
                            <BaseSelect 
                                v-model="form.branch_id"
                                label="// SUCURSAL ASIGNADA"
                                :options="branches"
                                placeholder="ACCESO_GLOBAL (TODAS)"
                                :icon="Building"
                                :error="form.errors.branch_id"
                                class="cyber-select"
                            />
                            <p class="text-[8px] font-mono text-muted-foreground mt-2 px-3 py-1.5 border border-dashed border-border/50 inline-block">
                                <span class="text-primary">// NOTA</span> VACÍO = VISIÓN GLOBAL
                            </p>
                        </div>
                    </div>

                </div>

                <!-- Footer con acciones -->
                <div class="p-4 border-t border-border/50 bg-background/80 backdrop-blur-md sticky bottom-0 z-20">
                    <div class="flex justify-between items-center gap-4">
                        <button v-if="currentStep === 2" @click="currentStep = 1" 
                                class="px-6 py-2 border border-border hover:border-primary hover:text-primary transition-all duration-300 relative group/prev">
                            <span class="flex items-center gap-2 text-sm font-mono">
                                <ArrowLeft :size="16" class="group-hover/prev:-translate-x-1 transition-transform" />
                                ANTERIOR
                            </span>
                            <!-- Esquinas -->
                            <span class="absolute top-0 left-0 w-1 h-1 border-t border-l border-primary opacity-0 group-hover/prev:opacity-100"></span>
                            <span class="absolute top-0 right-0 w-1 h-1 border-t border-r border-primary opacity-0 group-hover/prev:opacity-100"></span>
                            <span class="absolute bottom-0 left-0 w-1 h-1 border-b border-l border-primary opacity-0 group-hover/prev:opacity-100"></span>
                            <span class="absolute bottom-0 right-0 w-1 h-1 border-b border-r border-primary opacity-0 group-hover/prev:opacity-100"></span>
                        </button>
                        <div v-else></div>
                        
                        <!-- Botón Siguiente o Guardar -->
                        <button v-if="currentStep === 1" @click="nextStep" 
                                class="px-8 py-2 bg-primary text-primary-foreground font-mono text-sm border border-primary/50 relative overflow-hidden group/next">
                            <span class="flex items-center gap-2 relative z-10">
                                SIGUIENTE
                                <ArrowRight :size="16" class="group-hover/next:translate-x-1 transition-transform" />
                            </span>
                            <!-- Efecto scan -->
                            <span class="absolute inset-0 bg-primary-foreground/10 translate-y-full group-hover/next:translate-y-0 transition-transform duration-500"></span>
                            <!-- Esquinas -->
                            <span class="absolute top-0 left-0 w-1 h-1 border-t border-l border-primary-foreground/50"></span>
                            <span class="absolute top-0 right-0 w-1 h-1 border-t border-r border-primary-foreground/50"></span>
                            <span class="absolute bottom-0 left-0 w-1 h-1 border-b border-l border-primary-foreground/50"></span>
                            <span class="absolute bottom-0 right-0 w-1 h-1 border-b border-r border-primary-foreground/50"></span>
                        </button>
                        
                        <button v-else @click="submit" :disabled="form.processing"
                                class="px-8 py-2 bg-primary text-primary-foreground font-mono text-sm border border-primary/50 relative overflow-hidden group/save">
                            <span v-if="form.processing" class="flex items-center gap-2">
                                <RefreshCw :size="16" class="animate-spin" />
                                GUARDANDO...
                            </span>
                            <span v-else class="flex items-center gap-2 relative z-10">
                                <Save :size="16" />
                                GUARDAR CAMBIOS
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
                </div>
            </div>

            <!-- Código de sesión -->
            <div class="mt-4 text-center">
                <p class="text-[8px] font-mono text-muted-foreground">
                    SESSION_ID // {{ userCode }}_EDIT_{{ String(currentStep).padStart(2, '0') }} // {{ new Date().toISOString().slice(0,10) }}
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

@keyframes spin-slow {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.animate-spin-slow {
    animation: spin-slow 3s linear infinite;
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
    40% { clip-path: inset(70% 0 5% 0); }
    60% { clip-path: inset(10% 0 70% 0); }
    80% { clip-path: inset(40% 0 30% 0); }
    100% { clip-path: inset(30% 0 40% 0); }
}

/* Sombras neón */
.shadow-neon-primary {
    box-shadow: 0 0 20px hsl(var(--primary) / 0.3);
}

/* Estilos para inputs ciberpunk (afecta a BaseInput) */
:deep(.cyber-input input) {
    font-family: 'JetBrains Mono', monospace;
    border-color: hsl(var(--border));
    background-color: hsl(var(--background));
}

:deep(.cyber-input input:focus) {
    border-color: hsl(var(--primary));
    box-shadow: 0 0 0 2px hsl(var(--primary) / 0.1), 0 0 20px hsl(var(--primary) / 0.2);
}

:deep(.cyber-input label) {
    font-family: 'JetBrains Mono', monospace;
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: hsl(var(--primary));
}

/* Estilos para select */
:deep(.cyber-select select) {
    font-family: 'JetBrains Mono', monospace;
}

:deep(.cyber-select label) {
    font-family: 'JetBrains Mono', monospace;
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: hsl(var(--primary));
}

/* Colores personalizados */
.text-cyan-500 { color: #00ffff; }
.text-magenta-500 { color: #ff00ff; }
.bg-cyan-500 { background-color: #00ffff; }
.bg-magenta-500 { background-color: #ff00ff; }
</style>