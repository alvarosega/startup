<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { 
    User, Shield, ArrowRight, ArrowLeft, Save, Building, Lock, 
    RefreshCw, X, Mail, Phone, AlertTriangle, CheckCircle2, ChevronRight
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
    password: '',
    role_id: props.user.role_id,
    branch_id: props.user.branch_id,
    is_active: !!props.user.is_active
});

const validateStep1 = () => {
    form.clearErrors();
    let isValid = true;
    if (!form.first_name) { form.setError('first_name', 'Nombre requerido'); isValid = false; }
    if (!form.last_name) { form.setError('last_name', 'Apellido requerido'); isValid = false; }
    if (!form.phone) { form.setError('phone', 'Teléfono requerido'); isValid = false; }
    if (isChangingPassword.value && (!form.password || form.password.length < 6)) {
        form.setError('password', 'Mínimo 6 caracteres');
        isValid = false;
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

const submit = () => {
    form.put(route('admin.users.update', props.user.id), {
        preserveScroll: true,
        onError: (errors) => {
            if (errors.first_name || errors.last_name || errors.email || errors.phone || errors.password) {
                currentStep.value = 1;
            }
        },
        onSuccess: () => { isChangingPassword.value = false; form.password = ''; }
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
                        Editar Usuario
                    </h1>
                    <p class="text-[10px] text-muted-foreground font-medium mt-0.5 flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-primary"></span>
                        {{ props.user.first_name }} {{ props.user.last_name }}
                    </p>
                </div>
            </div>
        </template>

        <div id="wizard-card" class="max-w-2xl mx-auto pb-32 md:pb-12">
            
            <div class="flex items-center justify-between px-8 mb-8 relative">
                <div class="absolute top-1/2 left-10 right-10 h-0.5 bg-border -z-10"></div>
                <div class="absolute top-1/2 left-10 h-0.5 bg-primary -z-10 transition-all duration-500"
                     :style="{ width: currentStep === 2 ? 'calc(100% - 5rem)' : '0%' }"></div>

                <button v-for="step in steps" :key="step.id" 
                        @click="currentStep >= step.id ? currentStep = step.id : null"
                        class="flex flex-col items-center gap-2 relative bg-background p-1 disabled:opacity-100"
                        :disabled="currentStep < step.id">
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
                </button>
            </div>

            <div class="bg-card rounded-3xl border border-border shadow-xl overflow-hidden flex flex-col min-h-[400px]">
                
                <div class="p-6 md:p-8 flex-1">
                    
                    <div v-show="currentStep === 1" class="space-y-6 animate-in fade-in slide-in-from-left-4 duration-300">
                        
                        <div class="bg-muted/30 p-4 rounded-2xl border border-border flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-2.5 h-2.5 rounded-full animate-pulse" 
                                     :class="form.is_active ? 'bg-success' : 'bg-destructive'"></div>
                                <div>
                                    <p class="text-sm font-bold text-foreground">{{ form.is_active ? 'Usuario Activo' : 'Acceso Bloqueado' }}</p>
                                    <p class="text-[10px] text-muted-foreground">Permitir inicio de sesión</p>
                                </div>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" v-model="form.is_active" class="sr-only peer">
                                <div class="w-11 h-6 bg-muted peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                            </label>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <BaseInput v-model="form.first_name" label="Nombres" :icon="User" :error="form.errors.first_name" />
                            <BaseInput v-model="form.last_name" label="Apellidos" :error="form.errors.last_name" />
                        </div>

                        <BaseInput v-model="form.email" label="Email" :icon="Mail" type="email" :error="form.errors.email" />
                        <BaseInput v-model="form.phone" label="Celular" :icon="Phone" :error="form.errors.phone" />

                        <div class="border border-border rounded-2xl overflow-hidden transition-all duration-300"
                             :class="isChangingPassword ? 'bg-card shadow-md' : 'bg-muted/10'">
                            
                            <div v-if="!isChangingPassword" 
                                 class="p-4 flex items-center justify-between cursor-pointer hover:bg-muted/20"
                                 @click="isChangingPassword = true">
                                <div class="flex items-center gap-3">
                                    <div class="p-2 bg-background rounded-lg border border-border text-muted-foreground">
                                        <Lock :size="16"/>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-foreground">Contraseña</p>
                                        <p class="text-[10px] text-muted-foreground">••••••••</p>
                                    </div>
                                </div>
                                <span class="text-xs font-bold text-primary flex items-center gap-1">
                                    Cambiar <ChevronRight :size="14"/>
                                </span>
                            </div>

                            <div v-else class="p-5 space-y-4 bg-card animate-in slide-in-from-top-2">
                                <div class="flex justify-between items-center pb-2 border-b border-border/50">
                                    <span class="text-xs font-black uppercase text-primary tracking-wider flex items-center gap-2">
                                        <RefreshCw :size="12"/> Nueva Clave
                                    </span>
                                    <button @click="isChangingPassword = false; form.password = ''" class="text-muted-foreground hover:text-destructive transition-colors">
                                        <X :size="16"/>
                                    </button>
                                </div>
                                <BaseInput 
                                    v-model="form.password" 
                                    type="password" 
                                    placeholder="Mínimo 6 caracteres..." 
                                    :error="form.errors.password"
                                    autofocus
                                />
                            </div>
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

                        <div>
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
                            <Save :size="18"/> Guardar Cambios
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