<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useForm, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { 
    User, Shield, ArrowRight, ArrowLeft, 
    Save, Building, Lock, Mail, Phone, AlertTriangle 
} from 'lucide-vue-next';
import BaseInput from '@/Components/Base/BaseInput.vue';
import BaseSelect from '@/Components/Base/BaseSelect.vue';

const props = defineProps({ roles: Array, branches: Array });

const steps = [
    { id: 1, title: 'Identidad', icon: User },
    { id: 2, title: 'Permisos', icon: Shield },
];

const currentStep = ref(1);

const form = useForm({
    first_name: '', 
    last_name: '', 
    email: '', 
    phone: '',
    password: '', 
    role_id: null, 
    branch_id: null
});

// --- Lógica de Validación Cliente ---
const validateStep1 = () => {
    form.clearErrors(); // Limpiar errores previos visuales
    let isValid = true;

    if (!form.first_name) { form.setError('first_name', 'El nombre es obligatorio'); isValid = false; }
    if (!form.last_name) { form.setError('last_name', 'El apellido es obligatorio'); isValid = false; }
    if (!form.phone) { form.setError('phone', 'El teléfono es obligatorio'); isValid = false; }
    if (!form.password || form.password.length < 6) { 
        form.setError('password', 'La contraseña debe tener mín. 6 caracteres'); 
        isValid = false; 
    }

    return isValid;
};

const nextStep = () => {
    if (currentStep.value === 1) {
        if (validateStep1()) {
            currentStep.value = 2;
        } else {
            // Feedback vibrante si hay error
            const card = document.querySelector('.card-content');
            card.classList.add('animate-shake');
            setTimeout(() => card.classList.remove('animate-shake'), 400);
        }
    }
};

const submit = () => {
    form.post(route('admin.users.store'), {
        preserveScroll: true,
        onError: (errors) => {
            // SI HAY ERRORES EN EL PASO 1, VOLVER AUTOMÁTICAMENTE
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
            <div class="animate-slide-up">
                <h1 class="text-3xl font-display font-black text-gradient-primary">Nuevo Usuario</h1>
                <p class="text-muted-foreground text-sm">Alta de personal y configuración de accesos.</p>
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

            <div class="card glass shadow-xl min-h-[480px] flex flex-col">
                <div class="card-content p-5 md:p-8 flex-1">
                    
                    <div v-show="currentStep === 1" class="space-y-6 animate-in fade-in">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 md:gap-6">
                            <BaseInput v-model="form.first_name" label="Nombre" placeholder="Ej: Juan" :icon="User" required :error="form.errors.first_name" />
                            <BaseInput v-model="form.last_name" label="Apellido" placeholder="Ej: Perez" required :error="form.errors.last_name" />
                        </div>
                        
                        <BaseInput v-model="form.email" label="Email Corporativo" type="email" placeholder="usuario@empresa.com" :icon="Mail" :error="form.errors.email" />
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 md:gap-6">
                            <BaseInput v-model="form.phone" label="Celular (Login ID)" placeholder="70012345" :icon="Phone" required :error="form.errors.phone" />
                            <BaseInput v-model="form.password" label="Contraseña Inicial" type="password" placeholder="••••••" :icon="Lock" required :error="form.errors.password" />
                        </div>
                    </div>

                    <div v-show="currentStep === 2" class="space-y-6 animate-in slide-in-from-right-4">
                        
                        <div v-if="form.hasErrors && (form.errors.first_name || form.errors.phone)" 
                             class="alert alert-error mb-4 cursor-pointer" @click="currentStep = 1">
                            <AlertTriangle :size="18"/>
                            <span class="text-xs font-bold">Hay errores en los datos personales. Click para corregir.</span>
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
                        
                        <div v-if="form.role_id" class="animate-scale-in">
                            <BaseSelect 
                                v-model="form.branch_id"
                                label="Asignar Sucursal Operativa"
                                :options="branches"
                                placeholder="Seleccionar Sucursal..."
                                :icon="Building"
                                :error="form.errors.branch_id"
                            />
                            <p class="text-xs text-muted-foreground mt-2 ml-1">
                                * Déjalo vacío si el usuario necesita acceso global (Super Admin).
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
                            Continuar <ArrowRight :size="18"/>
                        </button>
                        
                        <button @click="submit" v-else 
                                class="btn btn-primary w-full md:w-auto gap-2 shadow-lg shadow-primary/20" 
                                :disabled="form.processing">
                            <span v-if="form.processing" class="spinner spinner-sm"></span>
                            <Save v-else :size="18"/> 
                            {{ form.processing ? 'Guardando...' : 'Finalizar Registro' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
/* Animación de error (vibración) */
@keyframes shake {
  0%, 100% { transform: translateX(0); }
  25% { transform: translateX(-5px); }
  75% { transform: translateX(5px); }
}
.animate-shake {
  animation: shake 0.4s ease-in-out;
}
</style>