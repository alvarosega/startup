<script setup>
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { useForm, Link } from '@inertiajs/vue3';
    import BaseCheckbox from '@/Components/Base/BaseCheckbox.vue'
    import { ref, computed, watch } from 'vue';
    import { 
        User, Shield, CheckCircle, ArrowRight, ArrowLeft, 
        Save, Building, ChevronRight, UserPlus, Lock 
    } from 'lucide-vue-next';
    import BaseInput from '@/Components/Base/BaseInput.vue';
    
    const props = defineProps({ 
        roles: Array, 
        branches: Array 
    });
    
    const steps = [
        { id: 1, title: 'Datos Personales', icon: User, description: 'Información básica del usuario' },
        { id: 2, title: 'Permisos y Acceso', icon: Shield, description: 'Configuración de rol y acceso' },
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
    
    // --- LÓGICA DINÁMICA DE SUCURSAL ---
    const selectedRole = computed(() => props.roles.find(r => r.id === form.role_id));
    
    const requiresBranch = computed(() => {
        if (!selectedRole.value) return true;
        const globalRoles = ['super_admin', 'client', 'customer'];
        return !globalRoles.includes(selectedRole.value.name);
    });
    
    watch(requiresBranch, (val) => {
        if (!val) form.branch_id = null;
    });
    
    const progress = computed(() => ((currentStep.value - 1) / (steps.length - 1)) * 100);
    
    const validateStep1 = () => {
        if (!form.first_name.trim()) {
            alert('El nombre es obligatorio');
            return false;
        }
        if (!form.last_name.trim()) {
            alert('El apellido es obligatorio');
            return false;
        }
        if (!form.phone.trim()) {
            alert('El celular es obligatorio para el login');
            return false;
        }
        if (!form.password.trim()) {
            alert('La contraseña es obligatoria');
            return false;
        }
        if (form.password.length < 6) {
            alert('La contraseña debe tener al menos 6 caracteres');
            return false;
        }
        return true;
    };
    
    const next = () => {
        if (currentStep.value === 1 && validateStep1()) {
            currentStep.value++;
        }
    };
    
    const previous = () => {
        if (currentStep.value > 1) {
            currentStep.value--;
        }
    };
    
    const submit = () => {
        if (!form.role_id) {
            alert('Debe seleccionar un rol para el usuario');
            return;
        }
        
        if (requiresBranch.value && !form.branch_id && props.branches.length > 0) {
            alert('Este rol requiere asignar una sucursal');
            return;
        }
        
        form.post(route('admin.users.store'), {
            onSuccess: () => {
                form.reset();
                currentStep.value = 1;
            }
        });
    };
    </script>
    
    <template>
        <AdminLayout>
            <!-- HEADER -->
            <template #header>
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div class="animate-slide-up">
                        <div class="flex items-center gap-4 mb-3">
                            <div class="avatar avatar-lg bg-gradient-to-br from-primary to-secondary text-primary-foreground shadow-lg">
                                <UserPlus :size="24" />
                            </div>
                            <div>
                                <h1 class="text-3xl lg:text-4xl font-display font-black text-foreground tracking-tight">
                                    Nuevo Usuario
                                </h1>
                                <p class="text-muted-foreground font-medium text-sm mt-1">
                                    Configura el acceso al sistema en {{ steps.length }} pasos
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
    
            <div class="max-w-4xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <!-- PROGRESS BAR -->
                <div class="mb-10">
                    <div class="flex justify-between items-center mb-4">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center">
                                <span class="text-sm font-bold text-primary">{{ currentStep }}/{{ steps.length }}</span>
                            </div>
                            <span class="text-sm font-bold text-foreground">
                                {{ steps[currentStep - 1].title }}
                            </span>
                        </div>
                        <div class="text-sm text-muted-foreground font-medium">
                            Paso {{ currentStep }} de {{ steps.length }}
                        </div>
                    </div>
                    <div class="h-2 bg-muted rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-primary to-secondary rounded-full transition-all duration-base ease-smooth"
                             :style="{ width: `${progress}%` }">
                        </div>
                    </div>
                </div>
    
                <!-- FORM STEPS -->
                <div class="card min-h-[500px] flex flex-col">
                    <!-- STEP INDICATORS -->
                    <div class="card-header border-b border-border/50">
                        <div class="flex items-center justify-center gap-8">
                            <div v-for="step in steps" :key="step.id" 
                                 class="flex flex-col items-center gap-2">
                                <div :class="[
                                    'w-10 h-10 rounded-full flex items-center justify-center transition-all duration-base',
                                    currentStep >= step.id 
                                        ? 'bg-gradient-to-r from-primary to-secondary text-primary-foreground shadow-md' 
                                        : 'bg-muted text-muted-foreground'
                                ]">
                                    <component :is="step.icon" :size="18" />
                                </div>
                                <span :class="[
                                    'text-xs font-bold transition-colors',
                                    currentStep >= step.id ? 'text-primary' : 'text-muted-foreground'
                                ]">
                                    {{ step.title }}
                                </span>
                            </div>
                        </div>
                    </div>
    
                    <!-- STEP CONTENT -->
                    <div class="card-content flex-1 p-8">
                        <div class="max-w-2xl mx-auto">
                            <!-- STEP 1: DATOS PERSONALES -->
                            <div v-if="currentStep === 1" class="space-y-6 animate-in">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <BaseInput v-model="form.first_name" 
                                             label="Nombre *" 
                                             placeholder="Ej: Ana" 
                                             :error="form.errors.first_name" 
                                             required />
                                    <BaseInput v-model="form.last_name" 
                                             label="Apellido *" 
                                             placeholder="Ej: Lopez" 
                                             :error="form.errors.last_name" 
                                             required />
                                </div>
    
                                <BaseInput v-model="form.email" 
                                         type="email" 
                                         label="Correo Electrónico" 
                                         placeholder="ana@empresa.com" 
                                         :error="form.errors.email" />
    
                                <BaseInput v-model="form.phone" 
                                         label="Celular (Login) *" 
                                         placeholder="70012345" 
                                         :error="form.errors.phone" 
                                         required />
    
                                <div class="relative">
                                    <BaseInput v-model="form.password" 
                                             type="password" 
                                             label="Contraseña *" 
                                             placeholder="Mínimo 6 caracteres" 
                                             :error="form.errors.password" 
                                             required />
                                    <div class="absolute right-3 top-9 text-muted-foreground">
                                        <Lock :size="16" />
                                    </div>
                                    <p class="text-xs text-muted-foreground mt-2">
                                        La contraseña debe tener al menos 6 caracteres
                                    </p>
                                </div>
                            </div>
    
                            <!-- STEP 2: PERMISOS Y ACCESO -->
                            <div v-else class="space-y-8 animate-in">
                                <!-- ROLES -->
                                <div>
                                    <label class="form-label mb-3">
                                        Rol del Usuario *
                                    </label>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <div v-for="role in roles" :key="role.id" 
                                             @click="form.role_id = role.id"
                                             :class="[
                                                 'cursor-pointer card p-4 transition-all duration-fast hover-lift',
                                                 form.role_id === role.id 
                                                     ? 'border-primary bg-primary/5 ring-2 ring-primary/20' 
                                                     : 'border-border'
                                             ]">
                                            <div class="flex items-center gap-3">
                                                <div :class="[
                                                    'w-10 h-10 rounded-lg flex items-center justify-center',
                                                    form.role_id === role.id 
                                                        ? 'bg-primary text-primary-foreground' 
                                                        : 'bg-muted text-muted-foreground'
                                                ]">
                                                    <Shield :size="16" />
                                                </div>
                                                <div>
                                                    <div class="font-bold text-sm" 
                                                         :class="form.role_id === role.id ? 'text-primary' : 'text-foreground'">
                                                        {{ role.display_name || role.name }}
                                                    </div>
                                                    <div class="text-xs text-muted-foreground mt-1">
                                                        {{ role.description || 'Sin descripción' }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <p v-if="form.errors.role_id" class="form-error mt-2">
                                        {{ form.errors.role_id }}
                                    </p>
                                </div>
                                
                                <!-- SUCURSAL -->
                                <div v-if="requiresBranch && branches && branches.length > 0" 
                                     class="space-y-3 animate-in">
                                    <label class="form-label">
                                        Asignar Sucursal <span class="text-error">*</span>
                                    </label>
                                    <div class="relative group">
                                        <Building class="absolute left-4 top-1/2 -translate-y-1/2 text-muted-foreground" 
                                                 :size="18" />
                                        <select v-model="form.branch_id" 
                                                class="form-input pl-12 appearance-none cursor-pointer">
                                            <option :value="null" disabled>
                                                Selecciona ubicación...
                                            </option>
                                            <option v-for="b in branches" 
                                                    :key="b.id" 
                                                    :value="b.id">
                                                {{ b.name }}
                                            </option>
                                        </select>
                                        <ChevronRight class="absolute right-4 top-1/2 -translate-y-1/2 text-muted-foreground rotate-90 pointer-events-none" 
                                                     :size="16"/>
                                    </div>
                                    <p v-if="form.errors.branch_id" class="form-error">
                                        {{ form.errors.branch_id }}
                                    </p>
                                </div>
    
                                <div v-else-if="form.role_id" 
                                     class="alert alert-success animate-in">
                                    <CheckCircle :size="16" />
                                    <span class="text-sm font-medium">
                                        Este rol tiene acceso global (Sin sucursal específica).
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
    
                    <!-- STEP NAVIGATION -->
                    <div class="card-footer border-t border-border/50">
                        <div class="flex justify-between items-center">
                            <button type="button" 
                                    @click="previous" 
                                    :disabled="currentStep === 1"
                                    class="btn btn-outline btn-md flex items-center gap-2"
                                    :class="{ 'opacity-0': currentStep === 1 }">
                                <ArrowLeft :size="16" />
                                <span>Atrás</span>
                            </button>
    
                            <button v-if="currentStep === 1" 
                                    type="button" 
                                    @click="next" 
                                    class="btn btn-primary btn-md flex items-center gap-2 group">
                                <span>Continuar</span>
                                <ArrowRight :size="16" class="transition-transform duration-fast group-hover:translate-x-1" />
                            </button>
                            
                            <button v-else 
                                    @click="submit" 
                                    :disabled="form.processing"
                                    class="btn btn-primary btn-md flex items-center gap-2">
                                <Save :size="18" />
                                <span>Crear Usuario</span>
                            </button>
                        </div>
                    </div>
                </div>
    
                <!-- QUICK NAVIGATION -->
                <div class="mt-8 text-center">
                    <Link :href="route('admin.users.index')" 
                          class="btn btn-ghost btn-sm inline-flex items-center gap-2">
                        <ArrowLeft :size="16" />
                        <span>Volver al listado</span>
                    </Link>
                </div>
            </div>
        </AdminLayout>
    </template>