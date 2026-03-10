<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useForm, Link } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import { 
    User, ArrowRight, ArrowLeft, Save, Lock, 
    RefreshCw, X, Mail, Phone, AlertTriangle, CheckCircle2, 
    ChevronRight, Fingerprint, Cpu, Terminal, Eye, EyeOff,
    MapPin, Building, Crosshair, Loader2
} from 'lucide-vue-next';
import axios from 'axios';
import ClientLocationPicker from '@/Components/Maps/ClientLocationPicker.vue';
import { VueTelInput } from 'vue-tel-input';
import 'vue-tel-input/vue-tel-input.css';

const props = defineProps({ 
    user: Object, 
    branches: Array 
});

const steps = [
    { id: 1, title: 'IDENTIDAD', icon: User, code: 'SEC_01' },
    { id: 2, title: 'UBICACIÓN', icon: MapPin, code: 'SEC_02' },
];

const currentStep = ref(1);
const isChangingPassword = ref(false);
const showPassword = ref(false);
const isValidatingGeo = ref(false);

const userCode = computed(() => {
    // Si tienes UUID, mostramos los últimos 8 caracteres para mantener la estética
    const shortId = props.user.data?.id ? props.user.data.id.split('-')[0] : '0000';
    return `CUST_${shortId.toUpperCase()}`;
});

// Hidratación directa desde el Resource
const form = useForm({
    first_name: props.user.data?.first_name || '',
    last_name:  props.user.data?.last_name || '',
    email:      props.user.data?.email || '',
    phone:      props.user.data?.phone || '',
    password:   '',
    branch_id:  props.user.data?.branch_id || null,
    is_active:  !!props.user.data?.is_active,
    latitude:   props.user.data?.latitude || -16.5000,
    longitude:  props.user.data?.longitude || -68.1500,
    address:    props.user.data?.address || ''
});

const onPhoneInput = (phone, obj) => {
    if (obj?.number) form.phone = obj.number;
};

// Lógica de Identificación de Sucursal Automática (Igual que Create)
const hasSelectedLocation = computed(() => {
    return form.latitude !== -16.5000 || form.longitude !== -68.1500;
});

const getBranchName = (id) => {
    const branch = props.branches.find(b => b.id === id);
    return branch ? branch.name : 'SUCURSAL DESCONOCIDA';
};

watch(() => [form.latitude, form.longitude], async ([newLat, newLng]) => {
    if (newLat && newLng && hasSelectedLocation.value) {
        isValidatingGeo.value = true;
        try {
            const { data } = await axios.post(route('admin.users.identify-branch'), {
                latitude: newLat,
                longitude: newLng
            });
            form.branch_id = data.branch_id;
        } catch (e) {
            form.branch_id = null;
        } finally {
            isValidatingGeo.value = false;
        }
    }
}, { deep: true });

const validateStep1 = () => {
    form.clearErrors();
    let isValid = true;
    if (!form.first_name) { form.setError('first_name', '// NOMBRE REQUERIDO'); isValid = false; }
    if (!form.last_name) { form.setError('last_name', '// APELLIDO REQUERIDO'); isValid = false; }
    if (!form.email) { form.setError('email', '// EMAIL REQUERIDO'); isValid = false; }
    if (!form.phone) { form.setError('phone', '// TELÉFONO REQUERIDO'); isValid = false; }
    if (isChangingPassword.value && (!form.password || form.password.length < 6)) {
        form.setError('password', '// MÍNIMO 6 CARACTERES'); isValid = false;
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
    form.put(route('admin.users.update', props.user.data.id), {
        preserveScroll: true,
        onError: (errors) => {
            if (errors.first_name || errors.last_name || errors.email || errors.phone || errors.password) {
                currentStep.value = 1;
            } else {
                currentStep.value = 2;
            }
        },
        onSuccess: () => { 
            isChangingPassword.value = false; 
            form.password = ''; 
        }
    });
};

const getError = (field) => {
    const error = form.errors[field];
    return Array.isArray(error) ? error[0] : error;
};

const statusColor = computed(() => form.is_active ? 'text-cyan-500' : 'text-magenta-500');
const statusText = computed(() => form.is_active ? 'ACTIVE' : 'LOCKED');
</script>

<template>
    <AdminLayout>
        <template #header>
            <div class="flex items-center gap-3 pt-1 mb-6">
                <Link :href="route('admin.users.index')" 
                      class="p-2 border border-border hover:border-primary hover:shadow-neon-primary transition-all relative group/back">
                    <ArrowLeft :size="20" class="group-hover/back:text-primary transition-colors" />
                    <span class="absolute top-0 left-0 w-1 h-1 border-t border-l border-primary opacity-0 group-hover/back:opacity-100 transition-opacity"></span>
                    <span class="absolute top-0 right-0 w-1 h-1 border-t border-r border-primary opacity-0 group-hover/back:opacity-100 transition-opacity"></span>
                    <span class="absolute bottom-0 left-0 w-1 h-1 border-b border-l border-primary opacity-0 group-hover/back:opacity-100 transition-opacity"></span>
                    <span class="absolute bottom-0 right-0 w-1 h-1 border-b border-r border-primary opacity-0 group-hover/back:opacity-100 transition-opacity"></span>
                </Link>
                <div class="relative group/title">
                    <h1 class="text-2xl font-display font-black tracking-tight text-foreground leading-none glitch-text" 
                        data-text="EDITAR CLIENTE">
                        EDITAR CLIENTE
                    </h1>
                    <p class="text-[10px] font-mono text-primary mt-0.5 flex items-center gap-2">
                        <Cpu :size="12" class="animate-pulse" />
                        <span>{{ userCode }} // {{ form.first_name }} {{ form.last_name }}</span>
                        <Terminal :size="12" class="animate-pulse" />
                    </p>
                    <div class="absolute -bottom-2 left-0 w-0 h-[1px] bg-primary group-hover/title:w-full transition-all duration-700"></div>
                </div>
            </div>
        </template>

        <div id="wizard-card" class="max-w-2xl mx-auto pb-32 md:pb-12">
            
            <div class="relative mb-8">
                <div class="absolute top-5 left-0 right-0 h-[2px] bg-border"></div>
                <div class="absolute top-5 left-0 h-[2px] bg-primary transition-all duration-500 shadow-neon-primary"
                     :style="{ width: currentStep === 2 ? '100%' : '50%' }"></div>

                <div class="relative flex justify-between">
                    <div v-for="(step, index) in steps" :key="step.id" class="flex flex-col items-center">
                        <button @click="currentStep >= step.id ? currentStep = step.id : null"
                                :disabled="currentStep < step.id"
                                class="relative group/step">
                            <div class="w-10 h-10 flex items-center justify-center border-2 transition-all duration-500 relative overflow-hidden"
                                 :class="currentStep >= step.id ? 'border-primary bg-primary/10 text-primary shadow-neon-primary' : 'border-border bg-card text-muted-foreground'">
                                <component :is="step.icon" :size="18" class="relative z-10" />
                                <div class="absolute inset-0 bg-gradient-to-b from-transparent via-primary/20 to-transparent translate-y-[-100%] group-hover/step:translate-y-[100%] transition-transform duration-700"></div>
                            </div>
                            <span class="absolute -top-6 left-1/2 -translate-x-1/2 text-[8px] font-mono text-primary opacity-0 group-hover/step:opacity-100 transition-opacity">
                                {{ step.code }}
                            </span>
                        </button>
                        <span class="text-[10px] font-mono font-bold mt-2 transition-colors duration-500"
                              :class="currentStep >= step.id ? 'text-primary' : 'text-muted-foreground'">
                            {{ step.title }}
                        </span>
                    </div>
                </div>
            </div>

            <form @submit.prevent="submit" novalidate>
                <div class="bg-card border border-border/50 shadow-2xl overflow-hidden relative group/card">
                    <div class="absolute inset-0 pointer-events-none opacity-0 group-hover/card:opacity-100 transition-opacity duration-500">
                        <div class="absolute top-0 left-0 w-full h-[1px] bg-primary/30 animate-scanline"></div>
                    </div>
                    
                    <div class="absolute top-0 left-0 w-4 h-4 border-t-2 border-l-2 border-primary/30"></div>
                    <div class="absolute top-0 right-0 w-4 h-4 border-t-2 border-r-2 border-primary/30"></div>
                    <div class="absolute bottom-0 left-0 w-4 h-4 border-b-2 border-l-2 border-primary/30"></div>
                    <div class="absolute bottom-0 right-0 w-4 h-4 border-b-2 border-r-2 border-primary/30"></div>

                    <div class="p-6 md:p-8">
                        
                        <div v-show="currentStep === 1" class="space-y-6 animate-in fade-in slide-in-from-left-4 duration-500">
                            
                            <div class="border border-border/50 p-4 relative group/status">
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
                                            </p>
                                            <p class="text-[10px] font-mono text-muted-foreground">
                                                {{ form.is_active ? 'ACCESO_AUTORIZADO' : 'ACCESO_BLOQUEADO' }}
                                            </p>
                                        </div>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer group/toggle">
                                        <input type="checkbox" v-model="form.is_active" class="sr-only peer">
                                        <div class="w-12 h-6 bg-muted border border-border peer-checked:border-primary transition-all relative overflow-hidden">
                                            <div class="absolute inset-0 bg-primary/20 translate-x-[-100%] peer-checked:translate-x-0 transition-transform duration-300"></div>
                                        </div>
                                        <div class="absolute left-1 top-1 w-4 h-4 bg-muted-foreground peer-checked:bg-primary peer-checked:translate-x-6 transition-all duration-300"></div>
                                    </label>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div class="flex flex-col gap-1.5 w-full group/field">
                                    <label class="text-[10px] font-mono font-black tracking-widest text-primary uppercase transition-colors group-focus-within/field:text-primary">// NOMBRES</label>
                                    <div class="relative overflow-hidden">
                                        <User :size="18" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within/field:text-primary transition-colors z-10" />
                                        <input v-model="form.first_name" type="text" placeholder="INGRESAR NOMBRE"
                                            class="w-full bg-background font-mono text-sm border-2 border-border focus:border-primary focus:shadow-neon-primary px-10 py-2.5 outline-none transition-all duration-300 text-foreground"
                                            :class="{ 'border-destructive text-destructive shadow-[0_0_10px_rgba(255,0,255,0.2)]': getError('first_name') }" />
                                    </div>
                                    <p v-if="getError('first_name')" class="text-[10px] font-mono font-bold text-destructive uppercase flex items-center gap-1">
                                        <span class="animate-pulse">>></span> {{ getError('first_name') }}
                                    </p>
                                </div>

                                <div class="flex flex-col gap-1.5 w-full group/field">
                                    <label class="text-[10px] font-mono font-black tracking-widest text-primary uppercase transition-colors group-focus-within/field:text-primary">// APELLIDOS</label>
                                    <div class="relative overflow-hidden">
                                        <User :size="18" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within/field:text-primary transition-colors z-10" />
                                        <input v-model="form.last_name" type="text" placeholder="INGRESAR APELLIDO"
                                            class="w-full bg-background font-mono text-sm border-2 border-border focus:border-primary focus:shadow-neon-primary px-10 py-2.5 outline-none transition-all duration-300 text-foreground"
                                            :class="{ 'border-destructive text-destructive shadow-[0_0_10px_rgba(255,0,255,0.2)]': getError('last_name') }" />
                                    </div>
                                    <p v-if="getError('last_name')" class="text-[10px] font-mono font-bold text-destructive uppercase flex items-center gap-1">
                                        <span class="animate-pulse">>></span> {{ getError('last_name') }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex flex-col gap-1.5 w-full group/field">
                                <label class="text-[10px] font-mono font-black tracking-widest text-primary uppercase transition-colors group-focus-within/field:text-primary">// EMAIL</label>
                                <div class="relative overflow-hidden">
                                    <Mail :size="18" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within/field:text-primary transition-colors z-10" />
                                    <input v-model="form.email" type="email" placeholder="USUARIO@SISTEMA.COM"
                                        class="w-full bg-background font-mono text-sm border-2 border-border focus:border-primary focus:shadow-neon-primary px-10 py-2.5 outline-none transition-all duration-300 text-foreground"
                                        :class="{ 'border-destructive text-destructive shadow-[0_0_10px_rgba(255,0,255,0.2)]': getError('email') }" />
                                </div>
                                <p v-if="getError('email')" class="text-[10px] font-mono font-bold text-destructive uppercase flex items-center gap-1">
                                    <span class="animate-pulse">>></span> {{ getError('email') }}
                                </p>
                            </div>

                            <div class="flex flex-col gap-1.5 w-full group/field">
                                <label class="text-[10px] font-mono font-black tracking-widest text-primary uppercase transition-colors group-focus-within/field:text-primary">// CELULAR / MÓVIL</label>
                                <div class="relative">
                                    <vue-tel-input 
                                        v-model="form.phone" 
                                        @on-input="onPhoneInput"
                                        mode="international"
                                        :preferredCountries="['BO']" 
                                        :defaultCountry="'BO'"
                                        :inputOptions="{ placeholder: '70012345', class: 'cyber-tel-input' }"
                                        :class="{ 'error': getError('phone') }"
                                        class="admin-tel-input"
                                    />
                                </div>
                                <p v-if="getError('phone')" class="text-[10px] font-mono font-bold text-destructive uppercase flex items-center gap-1">
                                    <span class="animate-pulse">>></span> {{ getError('phone') }}
                                </p>
                            </div>

                            <div class="border border-border/50 overflow-hidden transition-all duration-300 relative group/password"
                                 :class="isChangingPassword ? 'shadow-neon-primary' : ''">
                                
                                <div v-if="!isChangingPassword" @click="isChangingPassword = true" class="p-4 cursor-pointer hover:bg-primary/5 transition-colors relative">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <div class="p-2 border border-border/50 text-muted-foreground group-hover/password:text-primary transition-colors">
                                                <Lock :size="16"/>
                                            </div>
                                            <div>
                                                <p class="text-sm font-mono font-bold text-foreground">// CREDENCIALES</p>
                                                <p class="text-[10px] font-mono text-muted-foreground flex items-center gap-1">
                                                    <Fingerprint :size="10" /> HASH_ACTIVO // ********
                                                </p>
                                            </div>
                                        </div>
                                        <span class="text-xs font-mono text-primary flex items-center gap-1">
                                            MODIFICAR <ChevronRight :size="14"/>
                                        </span>
                                    </div>
                                </div>

                                <div v-else class="p-5 space-y-4 bg-card relative">
                                    <div class="flex justify-between items-center pb-2 border-b border-primary/30">
                                        <span class="text-xs font-mono font-bold text-primary uppercase tracking-wider flex items-center gap-2">
                                            <RefreshCw :size="12" class="animate-spin-slow" /> NUEVA_CLAVE // INGRESAR
                                        </span>
                                        <button type="button" @click="isChangingPassword = false; form.password = ''" 
                                                class="text-muted-foreground hover:text-destructive transition-colors group/close">
                                            <X :size="16" class="group-hover/close:scale-110 transition-transform" />
                                        </button>
                                    </div>

                                    <div class="flex flex-col gap-1.5 w-full group/field">
                                        <div class="relative overflow-hidden">
                                            <Lock :size="18" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within/field:text-primary transition-colors z-10" />
                                            <input 
                                                v-model="form.password" 
                                                :type="showPassword ? 'text' : 'password'" 
                                                placeholder="··········"
                                                autocomplete="new-password" 
                                                class="w-full bg-background font-mono text-sm border-2 border-border focus:border-primary focus:shadow-neon-primary px-10 py-2.5 outline-none transition-all duration-300 text-foreground"
                                                :class="{ 'border-destructive text-destructive shadow-[0_0_10px_rgba(255,0,255,0.2)]': getError('password') }" 
                                            />
                                            <button type="button" @click="showPassword = !showPassword" 
                                                class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-primary transition-colors z-20">
                                                <Eye v-if="!showPassword" :size="16" />
                                                <EyeOff v-else :size="16" />
                                            </button>
                                        </div>
                                        <p v-if="getError('password')" class="text-[10px] font-mono font-bold text-destructive uppercase flex items-center gap-1">
                                            <span class="animate-pulse">>></span> {{ getError('password') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-show="currentStep === 2" class="space-y-6 animate-in fade-in slide-in-from-right-4 duration-500">
                            
                            <div class="flex flex-col gap-1.5 w-full group/field">
                                <label class="text-[10px] font-mono font-black tracking-widest text-primary uppercase">// SUCURSAL DE OPERACIÓN (ASIGNACIÓN GPS)</label>
                                
                                <div class="relative overflow-hidden border-2 transition-all duration-500 flex items-center px-4 py-3"
                                     :class="{
                                         'border-destructive bg-destructive/5 text-destructive': !form.branch_id && hasSelectedLocation,
                                         'border-cyan-500 bg-cyan-500/5 text-cyan-500 shadow-neon-cyan': form.branch_id,
                                         'border-border bg-muted/20 text-muted-foreground': !form.branch_id && !hasSelectedLocation
                                     }">
                                     
                                    <Building :size="18" class="mr-3 shrink-0" />
                                    
                                    <div class="flex-1 min-w-0">
                                        <p v-if="!form.branch_id && !hasSelectedLocation" class="font-mono text-sm truncate">ESPERANDO SELECCIÓN EN MAPA...</p>
                                        <p v-else-if="!form.branch_id && hasSelectedLocation" class="font-mono text-sm font-bold truncate">FUERA DE ZONA DE COBERTURA</p>
                                        <p v-else class="font-mono text-sm font-bold truncate">{{ getBranchName(form.branch_id) }}</p>
                                    </div>

                                    <div class="shrink-0 ml-3">
                                        <Loader2 v-if="isValidatingGeo" :size="18" class="animate-spin text-primary" />
                                        <CheckCircle2 v-else-if="form.branch_id" :size="18" class="text-cyan-500" />
                                        <AlertTriangle v-else-if="!form.branch_id && hasSelectedLocation" :size="18" class="text-destructive animate-pulse" />
                                        <Crosshair v-else :size="18" class="opacity-50" />
                                    </div>
                                </div>
                                <p v-if="getError('branch_id')" class="text-[10px] font-mono font-bold text-destructive uppercase tracking-tighter mt-1">
                                    >> {{ getError('branch_id') }}
                                </p>
                            </div>

                            <div class="space-y-4 pt-2 border-t border-primary/30 relative">
                                <div class="border border-primary/30 bg-primary/5 p-4 relative">
                                    <div class="flex gap-3 items-center">
                                        <div class="p-2 bg-primary/10 text-primary border border-primary/30">
                                            <MapPin :size="20" />
                                        </div>
                                        <div>
                                            <p class="text-xs font-mono font-bold text-primary">// PUNTO DE ENTREGA LOGÍSTICO</p>
                                            <p class="text-[8px] font-mono text-muted-foreground mt-0.5 tracking-tighter uppercase">MAPA DE COBERTURA ACTIVO // DEFINA LA UBICACIÓN EXACTA</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="h-80 w-full border-2 border-primary/30 overflow-hidden relative group/map">
                                    <ClientLocationPicker
                                        v-model:modelValueLat="form.latitude"
                                        v-model:modelValueLng="form.longitude"
                                        v-model:modelValueAddress="form.address"
                                        v-model:modelValueBranchId="form.branch_id"
                                        :activeBranches="branches"
                                    />
                                    <div class="absolute inset-0 pointer-events-none">
                                        <div class="absolute top-0 left-0 w-full h-[2px] bg-primary/30 animate-scanline"></div>
                                    </div>
                                </div>

                                <div class="flex flex-col gap-1.5 w-full group/field opacity-75">
                                    <label class="text-[10px] font-mono font-black tracking-widest text-primary uppercase">// DIRECCIÓN IDENTIFICADA POR GPS</label>
                                    <div class="relative overflow-hidden">
                                        <MapPin :size="18" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground z-10" />
                                        <input :value="form.address" type="text" readonly
                                            class="w-full bg-muted/20 font-mono text-[11px] border-2 border-border px-10 py-2.5 outline-none text-foreground cursor-default"
                                            :class="{ 'border-destructive text-destructive': getError('address') }" />
                                    </div>
                                    <p v-if="getError('address')" class="text-[10px] font-mono font-bold text-destructive uppercase tracking-tighter">>> {{ getError('address') }}</p>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="p-4 border-t border-border/50 bg-background/80 backdrop-blur-md sticky bottom-0 z-20">
                        <div class="flex justify-between items-center gap-4">
                            <button type="button" v-if="currentStep === 2" @click="currentStep = 1" 
                                    class="px-6 py-2 border border-border hover:border-primary hover:text-primary transition-all duration-300 relative group/prev">
                                <span class="flex items-center gap-2 text-sm font-mono">
                                    <ArrowLeft :size="16" class="group-hover/prev:-translate-x-1 transition-transform" />
                                    ANTERIOR
                                </span>
                            </button>
                            <div v-else></div>
                            
                            <button type="button" v-if="currentStep === 1" @click="nextStep" 
                                    class="px-8 py-2 bg-primary text-primary-foreground font-mono text-sm border border-primary/50 relative overflow-hidden group/next">
                                <span class="flex items-center gap-2 relative z-10">
                                    SIGUIENTE
                                    <ArrowRight :size="16" class="group-hover/next:translate-x-1 transition-transform" />
                                </span>
                                <span class="absolute inset-0 bg-primary-foreground/10 translate-y-full group-hover/next:translate-y-0 transition-transform duration-500"></span>
                            </button>
                            
                            <button type="submit" v-else :disabled="form.processing"
                                    class="px-8 py-2 bg-primary text-primary-foreground font-mono text-sm border border-primary/50 relative overflow-hidden group/save">
                                <span v-if="form.processing" class="flex items-center gap-2">
                                    <RefreshCw :size="16" class="animate-spin" />
                                    GUARDANDO...
                                </span>
                                <span v-else class="flex items-center gap-2 relative z-10">
                                    <Save :size="16" />
                                    GUARDAR CAMBIOS
                                </span>
                                <span class="absolute inset-0 bg-primary-foreground/10 translate-y-full group-hover/save:translate-y-0 transition-transform duration-500"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </form>

            <div class="mt-4 text-center">
                <p class="text-[8px] font-mono text-muted-foreground">
                    SESSION_ID // {{ userCode }}_EDIT_{{ String(currentStep).padStart(2, '0') }}
                </p>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
@keyframes shake {
    10%, 90% { transform: translate3d(-1px, 0, 0); }
    20%, 80% { transform: translate3d(2px, 0, 0); }
    30%, 50%, 70% { transform: translate3d(-4px, 0, 0); }
    40%, 60% { transform: translate3d(4px, 0, 0); }
}
.shake { animation: shake 0.4s cubic-bezier(.36,.07,.19,.97) both; }

@keyframes scanline {
    0% { transform: translateY(-100%); }
    100% { transform: translateY(1000%); }
}
.animate-scanline { animation: scanline 8s linear infinite; }

@keyframes spin-slow {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
.animate-spin-slow { animation: spin-slow 3s linear infinite; }

.glitch-text { position: relative; animation: glitch-skew 4s infinite linear alternate-reverse; }
.glitch-text::before, .glitch-text::after {
    content: attr(data-text); position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0.8;
}
.glitch-text::before { color: #0ff; z-index: -1; animation: glitch-anim-1 0.4s infinite linear alternate-reverse; }
.glitch-text::after { color: #f0f; z-index: -2; animation: glitch-anim-2 0.4s infinite linear alternate-reverse; }

@keyframes glitch-skew { 0%, 100% { transform: skew(0deg); } }
@keyframes glitch-anim-1 { 0% { clip-path: inset(20% 0 30% 0); } }
@keyframes glitch-anim-2 { 0% { clip-path: inset(60% 0 10% 0); } }

.shadow-neon-primary { box-shadow: 0 0 20px hsl(var(--primary) / 0.3); }
.shadow-neon-cyan { box-shadow: 0 0 20px #00ffff80; }

input:focus { box-shadow: 0 0 0 2px hsl(var(--primary) / 0.2), 0 0 20px hsl(var(--primary) / 0.3); }

.text-cyan-500 { color: #00ffff; }
.text-magenta-500 { color: #ff00ff; }
.bg-cyan-500 { background-color: #00ffff; }
.bg-magenta-500 { background-color: #ff00ff; }

/* Tel Input Global Styling */
:deep(.admin-tel-input) {
    background-color: hsl(var(--background)) !important;
    border: 2px solid hsl(var(--border)) !important;
    border-radius: 0 !important;
    font-family: 'JetBrains Mono', monospace !important;
    transition: all 0.3s ease;
}
:deep(.admin-tel-input:focus-within) {
    border-color: hsl(var(--primary)) !important;
    box-shadow: 0 0 15px hsl(var(--primary) / 0.2) !important;
}
:deep(.admin-tel-input.error) { border-color: hsl(var(--destructive)) !important; }
:deep(.vti__input) { background-color: transparent !important; color: hsl(var(--foreground)) !important; font-size: 0.875rem !important; }
:deep(.vti__dropdown) { border-right: 1px solid hsl(var(--border)) !important; background-color: hsl(var(--muted) / 0.1) !important; }
</style>