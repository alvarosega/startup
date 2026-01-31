<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useForm, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { 
    User, Shield, ArrowRight, ArrowLeft, Save, Building, Lock, 
    RefreshCw, X, Mail, Phone, AlertTriangle, CheckCircle 
} from 'lucide-vue-next';
import BaseInput from '@/Components/Base/BaseInput.vue';
import BaseSelect from '@/Components/Base/BaseSelect.vue';

const props = defineProps({ 
    user: Object, 
    roles: Array, 
    branches: Array 
});

const steps = [
    { id: 1, title: 'Identidad', icon: User },
    { id: 2, title: 'Permisos', icon: Shield },
];

const currentStep = ref(1);
const isChangingPassword = ref(false);

const form = useForm({
    first_name: props.user.first_name,
    last_name: props.user.last_name,
    email: props.user.email,
    phone: props.user.phone,
    password: '', // Vacío por defecto
    role_id: props.user.role_id,
    branch_id: props.user.branch_id,
    is_active: !!props.user.is_active
});

// --- Validación Cliente para Editar ---
const validateStep1 = () => {
    form.clearErrors();
    let isValid = true;

    if (!form.first_name) { form.setError('first_name', 'El nombre es obligatorio'); isValid = false; }
    if (!form.last_name) { form.setError('last_name', 'El apellido es obligatorio'); isValid = false; }
    if (!form.phone) { form.setError('phone', 'El teléfono es obligatorio'); isValid = false; }
    
    // Solo validamos password si el usuario decidió cambiarla
    if (isChangingPassword.value && (!form.password || form.password.length < 6)) {
        form.setError('password', 'La nueva contraseña debe tener mín. 6 caracteres');
        isValid = false;
    }

    return isValid;
};

const nextStep = () => {
    if (currentStep.value === 1) {
        if (validateStep1()) {
            currentStep.value = 2;
        } else {
            const card = document.querySelector('.card-content');
            card?.classList.add('animate-shake');
            setTimeout(() => card?.classList.remove('animate-shake'), 400);
        }
    }
};

const submit = () => {
    form.put(route('admin.users.update', props.user.id), {
        preserveScroll: true,
        onError: (errors) => {
            // Si hay errores de validación del backend en el paso 1, volvemos
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
</script>

<template>
    <AdminLayout>
        <template #header>
            <div class="animate-slide-up">
                <div class="flex items-center gap-2 mb-2">
                    <Link :href="route('admin.users.index')" class="btn btn-ghost btn-sm px-2 text-muted-foreground hover:text-primary">
                        <ArrowLeft :size="16" /> Volver
                    </Link>
                </div>
                <h1 class="text-3xl font-display font-black text-gradient-primary">Editar Perfil</h1>
                <p class="text-muted-foreground text-sm">
                    Modificando a: <span class="font-bold text-foreground">{{ props.user.first_name }} {{ props.user.last_name }}</span>
                </p>
            </div>
        </template>

        <div class="max-w-3xl mx-auto pb-24 px-4 md:px-0">
            
            <div class="mb-8 md:mb-12 relative">
                <div class="flex justify-between items-center relative z-10 px-8">
                    <div v-for="step in steps" :key="step.id" class="flex flex-col items-center gap-2">
                        <div :class="[
                            'w-10 h-10 md:w-14 md:h-14 rounded-2xl flex items-center justify-center transition-all duration-500 border-4 border-background shadow-sm',
                            currentStep >= step.id ? 'bg-primary text-primary-foreground scale-110 shadow-primary/20' : 'bg-muted text-muted-foreground'
                        ]">
                            <component :is="step.icon" :size="20" />
                        </div>
                        <span class="text-[10px] font-black uppercase tracking-widest hidden md:block" 
                              :class="currentStep >= step.id ? 'text-primary' : 'text-muted-foreground'">
                            {{ step.title }}
                        </span>
                    </div>
                </div>
                <div class="absolute top-5 md:top-7 left-0 w-full h-1 bg-muted rounded-full -z-0 px-10">
                    <div class="h-full bg-primary transition-all duration-500 rounded-full" 
                         :style="{ width: currentStep === 2 ? '100%' : '0%' }"></div>
                </div>
            </div>

            <div class="card glass shadow-xl min-h-[500px] flex flex-col">
                <div class="card-content p-5 md:p-8 flex-1">
                    
                    <div v-show="currentStep === 1" class="space-y-6 animate-in fade-in">
    
                        <div class="flex items-center justify-between p-4 bg-muted/20 rounded-xl border border-border/50 mb-6">
                            <div class="flex items-center gap-3">
                                <div :class="`w-2 h-2 rounded-full ${form.is_active ? 'bg-success' : 'bg-error'} animate-pulse`"></div>
                                <span class="text-sm font-bold">{{ form.is_active ? 'Cuenta Activa' : 'Cuenta Suspendida' }}</span>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" v-model="form.is_active" class="sr-only peer">
                                <div class="w-11 h-6 bg-muted peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                            </label>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 md:gap-6">
                            <BaseInput v-model="form.first_name" label="Nombre" :icon="User" required :error="form.errors.first_name" />
                            <BaseInput v-model="form.last_name" label="Apellido" required :error="form.errors.last_name" />
                        </div>
                        
                        <BaseInput v-model="form.email" label="Email Corporativo" type="email" :icon="Mail" :error="form.errors.email" />
                        
                        <BaseInput v-model="form.phone" label="Celular (Login ID)" :icon="Phone" required :error="form.errors.phone" />

                        <div class="p-5 rounded-2xl bg-muted/30 border border-border transition-all duration-300">
                            <div v-if="!isChangingPassword" class="flex items-center justify-between">
                                <div class="flex items-center gap-3 text-muted-foreground">
                                    <div class="p-2 bg-background rounded-lg"><Lock :size="18"/></div> 
                                    <div>
                                        <p class="text-sm font-bold text-foreground">Contraseña</p>
                                        <p class="text-[10px] uppercase tracking-wider">●●●●●●●● (Encriptada)</p>
                                    </div>
                                </div>
                                <button @click="isChangingPassword = true" type="button" class="btn btn-outline btn-sm gap-2 hover:border-primary hover:text-primary">
                                    <RefreshCw :size="14"/> Cambiar
                                </button>
                            </div>
                            
                            <div v-else class="space-y-4 animate-in scale-in origin-top">
                                <div class="flex justify-between items-center">
                                    <label class="text-xs font-bold uppercase tracking-wider text-primary flex items-center gap-2">
                                        <Lock :size="14"/> Nueva Contraseña
                                    </label>
                                    <button @click="isChangingPassword = false; form.password = ''; form.clearErrors('password')" type="button" class="text-muted-foreground hover:text-error transition-colors">
                                        <X :size="18"/>
                                    </button>
                                </div>
                                <BaseInput 
                                    v-model="form.password" 
                                    type="password" 
                                    placeholder="Ingresa la nueva contraseña..." 
                                    :error="form.errors.password"
                                    autofocus 
                                />
                                <p class="text-xs text-muted-foreground">Mínimo 6 caracteres. Si cancelas, se mantiene la actual.</p>
                            </div>
                        </div>
                    </div>

                    <div v-show="currentStep === 2" class="space-y-6 animate-in slide-in-from-right-4">
                        
                        <div v-if="form.hasErrors && (form.errors.first_name || form.errors.phone || form.errors.email)" 
                             class="alert alert-error mb-4 cursor-pointer hover:bg-error/20 transition-colors" @click="currentStep = 1">
                            <AlertTriangle :size="18"/>
                            <span class="text-xs font-bold">Error en datos personales. Click para corregir.</span>
                        </div>

                        <div>
                            <label class="form-label mb-3">Rol de Acceso <span class="text-error">*</span></label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div v-for="role in roles" :key="role.id" 
                                     @click="form.role_id = role.id"
                                     :class="['cursor-pointer p-4 rounded-xl border-2 transition-all flex items-center gap-3', 
                                              form.role_id === role.id ? 'border-primary bg-primary/5 ring-2 ring-primary/10' : 'border-border hover:border-primary/40']">
                                    <div :class="['w-8 h-8 rounded-lg flex items-center justify-center transition-colors', form.role_id === role.id ? 'bg-primary text-primary-foreground' : 'bg-muted text-muted-foreground']">
                                        <Shield :size="16" />
                                    </div>
                                    <span class="font-bold text-sm" :class="form.role_id === role.id ? 'text-primary' : 'text-foreground'">{{ role.name }}</span>
                                </div>
                            </div>
                            <p v-if="form.errors.role_id" class="form-error mt-2">{{ form.errors.role_id }}</p>
                        </div>
                        
                        <div class="animate-scale-in">
                            <BaseSelect 
                                v-model="form.branch_id"
                                label="Sucursal Operativa"
                                :options="branches"
                                placeholder="Acceso Global (Sin Sucursal Específica)"
                                :icon="Building"
                                :error="form.errors.branch_id"
                            />
                            <p class="text-xs text-muted-foreground mt-2 ml-1 flex items-center gap-1">
                                <CheckCircle :size="12" class="text-success"/>
                                Los permisos se actualizan inmediatamente al guardar.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="card-footer bg-muted/20 border-t border-border/50 p-6 flex justify-between items-center sticky bottom-0 md:static backdrop-blur-md md:backdrop-blur-none z-20">
                    <button @click="currentStep = 1" v-show="currentStep === 2" class="btn btn-ghost hover:bg-background gap-2 text-muted-foreground">
                        <ArrowLeft :size="18"/> <span class="hidden md:inline">Volver</span>
                    </button>
                    
                    <div class="flex-1 md:flex-none flex justify-end">
                        <button @click="nextStep" v-if="currentStep === 1" class="btn btn-primary w-full md:w-auto gap-2">
                            Siguiente <ArrowRight :size="18"/>
                        </button>
                        
                        <button @click="submit" v-else 
                                class="btn btn-primary w-full md:w-auto gap-2 shadow-lg shadow-primary/20" 
                                :disabled="form.processing">
                            <span v-if="form.processing" class="spinner spinner-sm"></span>
                            <Save v-else :size="18"/> 
                            {{ form.processing ? 'Guardando...' : 'Guardar Cambios' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
@keyframes shake {
  0%, 100% { transform: translateX(0); }
  25% { transform: translateX(-5px); }
  75% { transform: translateX(5px); }
}
.animate-shake {
  animation: shake 0.4s ease-in-out;
}
</style>