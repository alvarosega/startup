<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useForm, Link } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import { 
    User, Shield, ArrowRight, ArrowLeft, 
    Save, Building, Lock, Mail, Phone, AlertTriangle, CheckCircle2, MapPin
} from 'lucide-vue-next';
import BaseInput from '@/Components/Base/BaseInput.vue';
import BaseSelect from '@/Components/Base/BaseSelect.vue';
import ClientLocationPicker from '@/Components/Maps/ClientLocationPicker.vue';
import { VueTelInput } from 'vue-tel-input';
import 'vue-tel-input/vue-tel-input.css';

const props = defineProps({ 
    roles: Array, 
    branches: Array 
});

// --- LÓGICA DE ROLES ---
const isCustomerRole = computed(() => {
    if (!form.role_id) return false;
    const selected = props.roles.find(r => r.id === form.role_id);
    // Asegúrate que el 'name' coincida con tu base de datos ('customer', 'cliente', etc)
    return selected?.name === 'customer'; 
});

const steps = [
    { id: 1, title: 'Datos Personales', icon: User },
    { id: 2, title: 'Roles y Accesos', icon: Shield },
];

const currentStep = ref(1);

const form = useForm({
    first_name: '', 
    last_name: '', 
    phone: '',
    email: '', 
    phone: '',
    password: '', 
    role_id: null, 
    branch_id: null,
    // Campos adicionales para Customer
    latitude: -16.5000, 
    longitude: -68.1500,
    address: ''
});
const onPhoneInput = (phone, obj) => {
    if (obj?.number) {
        form.phone = obj.number; // Guarda el formato +591XXXXXXXX
    }
};

// --- VALIDACIONES CLIENTE ---
const validateStep1 = () => {
    form.clearErrors();
    let isValid = true;
    if (!form.first_name) { form.setError('first_name', 'Nombre requerido'); isValid = false; }
    if (!form.last_name) { form.setError('last_name', 'Apellido requerido'); isValid = false; }
    if (!form.email) { form.setError('email', 'Email requerido'); isValid = false; }
    if (!form.phone) { form.setError('phone', 'Teléfono requerido'); isValid = false; }
    if (!form.password || form.password.length < 6) { 
        form.setError('password', 'Mínimo 6 caracteres'); isValid = false; 
    }
    return isValid;
};

const nextStep = () => {
    if (currentStep.value === 1) {
        if (validateStep1()) currentStep.value = 2;
        else {
            const card = document.getElementById('wizard-card');
            card?.classList.add('shake');
            setTimeout(() => card?.classList.remove('shake'), 400);
        }
    }
};

// --- LIMPIEZA DE DATOS AL CAMBIAR ROL ---
watch(isCustomerRole, (isCustomer) => {
    if (isCustomer) {
        // Si cambia a cliente, limpiamos la sucursal manual (el mapa la definirá)
        form.branch_id = null;
    } else {
        // Si cambia a staff, limpiamos los datos del mapa
        form.latitude = -16.5000;
        form.longitude = -68.1500;
        form.address = '';
        form.branch_id = null; 
    }
});

const submit = () => {
    form.post(route('admin.users.store'), {
        preserveScroll: true,
        onError: (errors) => {
            // Si hay errores en campos del paso 1, volver ahí
            if (errors.first_name || errors.last_name || errors.email || errors.phone || errors.password) {
                currentStep.value = 1;
            }
        }
    });
};
</script>

<template>
    <AdminLayout>
        
        <template #header>
            <div class="flex items-center gap-3 pt-1 mb-6">
                <Link :href="route('admin.users.index')" class="p-2 rounded-xl bg-card border border-border text-muted-foreground hover:text-foreground transition-colors">
                    <ArrowLeft :size="20" />
                </Link>
                <div>
                    <h1 class="text-2xl font-display font-black tracking-tight text-foreground leading-none">
                        Nuevo Usuario
                    </h1>
                    <p class="text-[10px] text-muted-foreground font-medium mt-0.5">
                        Alta de personal y permisos
                    </p>
                </div>
            </div>
        </template>

        <div id="wizard-card" class="max-w-2xl mx-auto pb-32 md:pb-12">
            
            <div class="flex items-center justify-between px-8 mb-8 relative">
                <div class="absolute top-1/2 left-10 right-10 h-0.5 bg-border -z-10"></div>
                <div class="absolute top-1/2 left-10 h-0.5 bg-primary -z-10 transition-all duration-500"
                     :style="{ width: currentStep === 2 ? 'calc(100% - 5rem)' : '0%' }"></div>

                <div v-for="step in steps" :key="step.id" class="flex flex-col items-center gap-2 relative bg-background p-1">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center transition-all duration-300 border-2"
                         :class="currentStep >= step.id 
                            ? 'bg-primary border-primary text-primary-foreground shadow-lg shadow-primary/20 scale-110' 
                            : 'bg-card border-border text-muted-foreground'">
                        <component :is="step.icon" :size="18" />
                    </div>
                    <span class="text-[10px] font-black uppercase tracking-wider transition-colors"
                          :class="currentStep >= step.id ? 'text-primary' : 'text-muted-foreground'">
                        {{ step.title }}
                    </span>
                </div>
            </div>

            <form @submit.prevent="submit" novalidate class="bg-card rounded-3xl border border-border shadow-xl overflow-hidden flex flex-col min-h-[450px]">
                
                <div class="p-6 md:p-8 flex-1">
                    
                    <div v-show="currentStep === 1" class="space-y-6 animate-in fade-in slide-in-from-left-4 duration-300">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <BaseInput v-model="form.first_name" label="Nombres" placeholder="Ej: Juan" :icon="User" :error="form.errors.first_name" />
                            <BaseInput v-model="form.last_name" label="Apellidos" placeholder="Ej: Perez" :error="form.errors.last_name" />
                        </div>
                        
                        <BaseInput v-model="form.email" label="Email" type="email" placeholder="usuario@empresa.com" :icon="Mail" :error="form.errors.email" autocomplete="username"/>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div class="space-y-1.5">
                <label class="text-xs font-black text-muted-foreground uppercase tracking-wider ml-1 flex items-center gap-1.5">
                    <Phone :size="14" /> Celular
                </label>
                <vue-tel-input 
                    v-model="form.phone" 
                    @on-input="onPhoneInput"
                    mode="international"
                    :preferredCountries="['BO']" 
                    :defaultCountry="'BO'"
                    :inputOptions="{ placeholder: '70012345' }"
                    class="admin-tel-input"
                    :class="{ '!border-destructive': form.errors.phone }"
                />
                <p v-if="form.errors.phone" class="text-destructive text-[10px] font-bold ml-1">{{ form.errors.phone }}</p>
            </div>
                            <BaseInput v-model="form.password" label="Contraseña" type="password" placeholder="••••••" :icon="Lock" :error="form.errors.password" autocomplete="new-password"/>
                        </div>
                    </div>

                    <div v-show="currentStep === 2" class="space-y-6 animate-in fade-in slide-in-from-right-4 duration-300">
                        
                        <div v-if="form.hasErrors && (form.errors.first_name || form.errors.phone || form.errors.email)" 
                             class="bg-destructive/10 border border-destructive/20 p-3 rounded-xl flex items-center gap-3 cursor-pointer hover:bg-destructive/20 transition-colors"
                             @click="currentStep = 1">
                            <AlertTriangle class="text-destructive shrink-0" :size="18"/>
                            <p class="text-xs font-bold text-destructive">Faltan datos en el paso anterior.</p>
                            <ArrowLeft :size="14" class="text-destructive ml-auto"/>
                        </div>

                        <div>
                            <label class="text-xs font-black text-muted-foreground uppercase tracking-wider mb-3 block ml-1">
                                Rol de Sistema
                            </label>
                            <div class="grid grid-cols-1 gap-3">
                                <div v-for="role in roles" :key="role.id" 
                                     @click="form.role_id = role.id"
                                     class="relative p-4 rounded-xl border-2 cursor-pointer transition-all flex items-center gap-4 group"
                                     :class="form.role_id === role.id 
                                        ? 'border-primary bg-primary/5 shadow-md' 
                                        : 'border-border bg-card hover:border-primary/30'">
                                    
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center transition-colors shrink-0"
                                         :class="form.role_id === role.id ? 'bg-primary text-primary-foreground' : 'bg-muted text-muted-foreground'">
                                        <Shield :size="18" />
                                    </div>
                                    <div>
                                        <p class="font-bold text-sm text-foreground group-hover:text-primary transition-colors">
                                            {{ role.name }}
                                        </p>
                                        <p class="text-[10px] text-muted-foreground mt-0.5">
                                            {{ role.description || 'Acceso al sistema.' }}
                                        </p>
                                    </div>
                                    <div v-if="form.role_id === role.id" class="absolute top-4 right-4 text-primary">
                                        <CheckCircle2 :size="18" />
                                    </div>
                                </div>
                            </div>
                            <p v-if="form.errors.role_id" class="text-destructive text-xs mt-2 font-bold">{{ form.errors.role_id }}</p>
                        </div>

                        <div v-if="form.role_id" class="animate-in fade-in slide-in-from-bottom-2">
                            
                            <div v-if="!isCustomerRole">
                                <BaseSelect 
                                    v-model="form.branch_id"
                                    label="Sucursal Asignada (Lugar de Trabajo)"
                                    :options="branches"
                                    placeholder="Acceso Global (Todas)"
                                    :icon="Building"
                                    :error="form.errors.branch_id"
                                />
                                <p class="text-[10px] text-muted-foreground mt-2 px-2 bg-muted/30 py-1.5 rounded-lg inline-block">
                                    <span class="font-bold">Nota:</span> Si se deja vacío, el usuario tendrá visión global.
                                </p>
                            </div>

                            <div v-else class="space-y-4 pt-2 border-t border-border">
                                <div class="bg-blue-500/10 border border-blue-500/20 p-4 rounded-xl flex gap-3 items-center">
                                    <MapPin class="text-blue-500" :size="20"/>
                                    <div>
                                        <p class="text-xs font-bold text-blue-600 dark:text-blue-400">Ubicación del Cliente</p>
                                        <p class="text-[10px] text-muted-foreground mt-0.5">La sucursal se asignará automáticamente según la cobertura.</p>
                                    </div>
                                </div>

                                <div class="h-72 w-full rounded-2xl overflow-hidden border-2 border-border shadow-inner relative z-0">
                                    <ClientLocationPicker
                                        v-model:modelValueLat="form.latitude"
                                        v-model:modelValueLng="form.longitude"
                                        v-model:modelValueAddress="form.address"
                                        v-model:modelValueBranchId="form.branch_id"
                                        :activeBranches="branches"
                                    />
                                </div>

                                <BaseInput 
                                    v-model="form.address" 
                                    label="Dirección Detectada" 
                                    readonly 
                                    class="opacity-75 cursor-not-allowed bg-muted/20"
                                    :error="form.errors.address"
                                />
                                
                                <div v-if="form.address">
                                    <p v-if="!form.branch_id" class="text-xs text-destructive font-bold flex items-center gap-1.5 bg-destructive/10 p-2 rounded-lg border border-destructive/20">
                                        <AlertTriangle :size="14"/> Ubicación fuera de zona de cobertura.
                                    </p>
                                    <p v-else class="text-xs text-success font-bold flex items-center gap-1.5 bg-success/10 p-2 rounded-lg border border-success/20">
                                        <CheckCircle2 :size="14"/> Cobertura confirmada.
                                    </p>
                                </div>
                            </div>

                        </div>
                    </div>

                </div>

                <div class="p-4 border-t border-border bg-background/80 backdrop-blur-md sticky bottom-0 z-20 flex justify-between items-center gap-4">
                    <button type="button" v-if="currentStep === 2" @click="currentStep = 1" 
                            class="btn btn-ghost text-muted-foreground hover:text-foreground">
                        <ArrowLeft :size="18" class="mr-2"/> Atrás
                    </button>
                    <div v-else></div> 
                    
                    <button type="button" v-if="currentStep === 1" @click="nextStep" 
                            class="btn btn-primary shadow-lg shadow-primary/25 w-full md:w-auto px-8">
                        Siguiente <ArrowRight :size="18" class="ml-2"/>
                    </button>
                    
                    <button type="submit" v-else :disabled="form.processing"
                            class="btn btn-primary shadow-lg shadow-primary/25 w-full md:w-auto px-8">
                        <span v-if="form.processing">Guardando...</span>
                        <span v-else class="flex items-center gap-2">
                            <Save :size="18"/> {{ isCustomerRole ? 'Registrar Cliente' : 'Crear Usuario' }}
                        </span>
                    </button>
                </div>
            </form>

        </div>
    </AdminLayout>
</template>

<style scoped>
.shake {
    animation: shake 0.4s cubic-bezier(.36,.07,.19,.97) both;
}
@keyframes shake {
    10%, 90% { transform: translate3d(-1px, 0, 0); }
    20%, 80% { transform: translate3d(2px, 0, 0); }
    30%, 50%, 70% { transform: translate3d(-4px, 0, 0); }
    40%, 60% { transform: translate3d(4px, 0, 0); }
}
</style>