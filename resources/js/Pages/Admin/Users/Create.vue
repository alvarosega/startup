<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { 
    User, Shield, ArrowRight, ArrowLeft, 
    Save, Building, Lock, Mail, Phone, AlertTriangle, CheckCircle2
} from 'lucide-vue-next';
import BaseInput from '@/Components/Base/BaseInput.vue';
import BaseSelect from '@/Components/Base/BaseSelect.vue';

const props = defineProps({ roles: Array, branches: Array });

const steps = [
    { id: 1, title: 'Datos Personales', icon: User },
    { id: 2, title: 'Roles y Accesos', icon: Shield },
];

const currentStep = ref(1);

const form = useForm({
    first_name: '', last_name: '', email: '', phone: '',
    password: '', role_id: null, branch_id: null
});

// --- CLIENT VALIDATION ---
const validateStep1 = () => {
    form.clearErrors();
    let isValid = true;
    if (!form.first_name) { form.setError('first_name', 'Nombre requerido'); isValid = false; }
    if (!form.last_name) { form.setError('last_name', 'Apellido requerido'); isValid = false; }
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
            // Visual feedback for error
            const card = document.getElementById('wizard-card');
            card?.classList.add('shake');
            setTimeout(() => card?.classList.remove('shake'), 400);
        }
    }
};

const submit = () => {
    form.post(route('admin.users.store'), {
        preserveScroll: true,
        onError: (errors) => {
            if (errors.first_name || errors.last_name || errors.email || errors.password) {
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

            <div class="bg-card rounded-3xl border border-border shadow-xl overflow-hidden flex flex-col min-h-[400px]">
                
                <div class="p-6 md:p-8 flex-1">
                    
                    <div v-show="currentStep === 1" class="space-y-6 animate-in fade-in slide-in-from-left-4 duration-300">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <BaseInput v-model="form.first_name" label="Nombres" placeholder="Ej: Juan" :icon="User" :error="form.errors.first_name" />
                            <BaseInput v-model="form.last_name" label="Apellidos" placeholder="Ej: Perez" :error="form.errors.last_name" />
                        </div>
                        
                        <BaseInput v-model="form.email" label="Email Corporativo" type="email" placeholder="usuario@empresa.com" :icon="Mail" :error="form.errors.email" />
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <BaseInput v-model="form.phone" label="Celular" placeholder="70012345" :icon="Phone" :error="form.errors.phone" />
                            <BaseInput v-model="form.password" label="Contraseña" type="password" placeholder="••••••" :icon="Lock" :error="form.errors.password" />
                        </div>
                    </div>

                    <div v-show="currentStep === 2" class="space-y-6 animate-in fade-in slide-in-from-right-4 duration-300">
                        
                        <div v-if="form.hasErrors && (form.errors.first_name || form.errors.phone)" 
                             class="bg-destructive/10 border border-destructive/20 p-3 rounded-xl flex items-center gap-3 cursor-pointer hover:bg-destructive/20 transition-colors"
                             @click="currentStep = 1">
                            <AlertTriangle class="text-destructive shrink-0" :size="18"/>
                            <p class="text-xs font-bold text-destructive">Datos incompletos en el paso anterior.</p>
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
                                            {{ role.description || 'Acceso estándar al sistema.' }}
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
                            <BaseSelect 
                                v-model="form.branch_id"
                                label="Sucursal Asignada"
                                :options="branches"
                                placeholder="Acceso Global (Todas)"
                                :icon="Building"
                                :error="form.errors.branch_id"
                            />
                            <p class="text-[10px] text-muted-foreground mt-2 px-2 bg-muted/30 py-1.5 rounded-lg inline-block">
                                <span class="font-bold">Nota:</span> Si se deja vacío, el usuario tendrá visión global.
                            </p>
                        </div>
                    </div>

                </div>

                <div class="p-4 border-t border-border bg-background/80 backdrop-blur-md sticky bottom-0 z-20 flex justify-between items-center gap-4">
                    <button v-if="currentStep === 2" @click="currentStep = 1" 
                            class="btn btn-ghost text-muted-foreground hover:text-foreground">
                        <ArrowLeft :size="18" class="mr-2"/> Atrás
                    </button>
                    <div v-else></div> <button v-if="currentStep === 1" @click="nextStep" 
                            class="btn btn-primary shadow-lg shadow-primary/25 w-full md:w-auto px-8">
                        Siguiente <ArrowRight :size="18" class="ml-2"/>
                    </button>
                    
                    <button v-else @click="submit" :disabled="form.processing"
                            class="btn btn-primary shadow-lg shadow-primary/25 w-full md:w-auto px-8">
                        <span v-if="form.processing">Guardando...</span>
                        <span v-else class="flex items-center gap-2">
                            <Save :size="18"/> Crear Usuario
                        </span>
                    </button>
                </div>
            </div>

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