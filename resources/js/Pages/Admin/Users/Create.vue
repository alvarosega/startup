<script setup>
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { useForm, Link } from '@inertiajs/vue3';
    import { ref, computed, onMounted } from 'vue';
    
    // Iconos
    import { 
        User, Shield, Key, Phone, Save, Mail,
        ArrowLeft, ArrowRight, CheckCircle, Briefcase, Building 
    } from 'lucide-vue-next';

    const props = defineProps({
        roles: Array,
        branches: Array
    });

    // Estado de la navegación por pasos
    const currentStep = ref(1);
    
    const steps = [
        { id: 1, title: 'Datos Personales', icon: User },
        { id: 2, title: 'Acceso y Rol', icon: Shield },
    ];

    // Formulario de Inertia
    const form = useForm({
        first_name: '',
        last_name: '',
        email: '',       // Campo opcional nuevo
        phone: '',
        password: '',
        role_id: null,   // Inicializamos como null
        branch_id: ''
    });

    // --- LÓGICA DE AUTOMATIZACIÓN (BLINDAJE) ---
    onMounted(() => {
        // Si el usuario (Branch Admin) solo tiene 1 sucursal disponible, 
        // la asignamos automáticamente sin necesidad de que él la seleccione.
        if (props.branches.length === 1) {
            form.branch_id = props.branches[0].id;
        }
    });

    // --- HELPERS VISUALES ---
    // Convierte el "name" de la BD (ej: branch_admin) en algo bonito para leer
    const formatRoleName = (rawName) => {
        const map = {
            'super_admin': 'Super Administrador',
            'branch_admin': 'Gerente de Sucursal',
            'logistics_manager': 'Jefe de Logística Global',
            'finance_manager': 'Gerente Financiero',
            'inventory_manager': 'Jefe de Almacén / Inventario',
            'growth_specialist': 'Analista de Datos',
            'identity_auditor': 'Auditor de Identidad (KYC)',
            'logistics_operator': 'Operador Logístico / Chofer',
            'customer': 'Cliente'
        };
        return map[rawName] || rawName; // Si no está en la lista, muestra el original
    };

    // --- UTILS ---
    const progressPercentage = computed(() => ((currentStep.value - 1) / (steps.length - 1)) * 100);
    const showPassword = ref(false);

    // --- NAVEGACIÓN Y VALIDACIÓN FRONTEND ---
    const nextStep = () => {
        if (currentStep.value === 1) {
            // Validación manual rápida antes de pasar de paso
            if (!form.first_name || !form.last_name) { 
                alert('Por favor completa el nombre y apellido.'); return; 
            }
            if (!form.phone || !form.password) {
                alert('El teléfono y contraseña son obligatorios para el acceso.'); return;
            }
            // El email es opcional, no lo bloqueamos
        }
        if (currentStep.value < steps.length) currentStep.value++;
    };

    const prevStep = () => {
        if (currentStep.value > 1) currentStep.value--;
    };

    const selectRole = (id) => {
        form.role_id = id;
    };

    const submit = () => {
        // Validaciones finales antes de enviar
        if (!form.role_id) {
            alert('Debes asignar un rol al usuario.'); return;
        }
        if (!form.branch_id) {
            // Esto no debería pasar si el onMounted funcionó, pero por seguridad:
            alert('Error de seguridad: Sucursal no asignada.'); return;
        }
        
        form.post(route('admin.users.store'));
    };
</script>

<template>
    <AdminLayout>
        <div class="max-w-4xl mx-auto py-6">
            
            <div class="mb-8">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h1 class="text-2xl font-black text-white tracking-tight">Registrar Personal</h1>
                        <p class="text-gray-400 text-sm mt-1">Alta de nuevo usuario en el sistema</p>
                    </div>
                    <Link :href="route('admin.users.index')" class="text-sm font-bold text-gray-500 hover:text-red-400 transition-colors">Cancelar</Link>
                </div>

                <div class="relative px-4">
                    <div class="absolute top-5 left-0 w-full h-1 bg-gray-700 -z-10 rounded-full"></div>
                    <div class="absolute top-5 left-0 h-1 bg-blue-600 -z-10 rounded-full transition-all duration-500 ease-out" :style="{ width: progressPercentage + '%' }"></div>

                    <div class="flex justify-between">
                        <div v-for="step in steps" :key="step.id" 
                             class="flex flex-col items-center gap-2 cursor-pointer group"
                             @click="currentStep >= step.id ? currentStep = step.id : null">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center border-2 transition-all bg-gray-800"
                                 :class="[currentStep === step.id ? 'border-blue-500 text-blue-500 scale-110 shadow-lg shadow-blue-900/50' : currentStep > step.id ? 'border-green-500 bg-green-900/20 text-green-500' : 'border-gray-600 text-gray-500']">
                                <CheckCircle v-if="currentStep > step.id" :size="20" />
                                <component v-else :is="step.icon" :size="18" />
                            </div>
                            <span class="text-[10px] font-bold uppercase tracking-wider bg-gray-900 px-2 rounded" :class="currentStep >= step.id ? 'text-white' : 'text-gray-500'">{{ step.title }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-gray-800 border border-gray-700 rounded-xl shadow-xl overflow-hidden min-h-[450px] flex flex-col">
                <form class="flex-1 flex flex-col" @submit.prevent>
                    <div class="p-8 flex-1">
                        <Transition name="fade" mode="out-in">
                            
                            <div v-if="currentStep === 1" key="1" class="space-y-6 max-w-2xl mx-auto">
                                <h2 class="text-blue-400 font-bold text-sm uppercase border-b border-gray-700 pb-2 flex items-center gap-2">
                                    <User :size="16" /> Identidad del Colaborador
                                </h2>

                                <div class="grid grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Nombre *</label>
                                        <input v-model="form.first_name" type="text" class="w-full bg-gray-900 border border-gray-600 text-white rounded-lg p-3 focus:ring-2 focus:ring-blue-500 outline-none placeholder-gray-600" placeholder="Ej: Juan">
                                        <p v-if="form.errors.first_name" class="text-red-500 text-xs mt-1">{{ form.errors.first_name }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Apellido *</label>
                                        <input v-model="form.last_name" type="text" class="w-full bg-gray-900 border border-gray-600 text-white rounded-lg p-3 focus:ring-2 focus:ring-blue-500 outline-none placeholder-gray-600" placeholder="Ej: Pérez">
                                        <p v-if="form.errors.last_name" class="text-red-500 text-xs mt-1">{{ form.errors.last_name }}</p>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Correo Electrónico <span class="text-gray-600 normal-case">(Opcional)</span></label>
                                    <div class="relative">
                                        <Mail :size="16" class="absolute left-3 top-3.5 text-gray-500" />
                                        <input v-model="form.email" type="email" class="w-full bg-gray-900 border border-gray-600 text-white rounded-lg p-3 pl-10 focus:ring-2 focus:ring-blue-500 outline-none placeholder-gray-600" placeholder="juan.perez@empresa.com">
                                    </div>
                                    <p v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</p>
                                </div>

                                <div class="grid grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Celular (Login) *</label>
                                        <div class="relative">
                                            <Phone :size="16" class="absolute left-3 top-3.5 text-gray-500" />
                                            <input v-model="form.phone" type="text" class="w-full bg-gray-900 border border-gray-600 text-white rounded-lg p-3 pl-10 focus:ring-2 focus:ring-blue-500 outline-none font-mono" placeholder="70012345">
                                        </div>
                                        <p v-if="form.errors.phone" class="text-red-500 text-xs mt-1">{{ form.errors.phone }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Contraseña Inicial *</label>
                                        <div class="relative">
                                            <Key :size="16" class="absolute left-3 top-3.5 text-gray-500" />
                                            <input v-model="form.password" :type="showPassword ? 'text' : 'password'" class="w-full bg-gray-900 border border-gray-600 text-white rounded-lg p-3 pl-10 focus:ring-2 focus:ring-blue-500 outline-none font-mono" placeholder="******">
                                            <button type="button" @click="showPassword = !showPassword" class="absolute right-3 top-3.5 text-xs font-bold text-gray-500 hover:text-white uppercase">
                                                {{ showPassword ? 'Ocultar' : 'Ver' }}
                                            </button>
                                        </div>
                                        <p v-if="form.errors.password" class="text-red-500 text-xs mt-1">{{ form.errors.password }}</p>
                                    </div>
                                </div>
                            </div>

                            <div v-else key="2" class="space-y-6 max-w-2xl mx-auto">
                                <h2 class="text-green-400 font-bold text-sm uppercase border-b border-gray-700 pb-2 flex items-center gap-2">
                                    <Shield :size="16" /> Permisos Operativos
                                </h2>

                                <div class="space-y-6">
                                    
                                    <div>
                                        <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Rol del Sistema *</label>
                                        
                                        <div v-if="roles.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                            <div v-for="role in roles" :key="role.id" 
                                                 @click="selectRole(role.id)"
                                                 class="cursor-pointer border rounded-lg p-3 flex items-center gap-3 transition-all hover:bg-gray-700"
                                                 :class="form.role_id === role.id ? 'bg-blue-900/30 border-blue-500 ring-1 ring-blue-500' : 'bg-gray-900 border-gray-600 hover:border-gray-500'">
                                                
                                                <div class="p-2 rounded-full" :class="form.role_id === role.id ? 'bg-blue-600 text-white' : 'bg-gray-700 text-gray-400'">
                                                    <Briefcase :size="16" />
                                                </div>
                                                <div>
                                                    <span class="block text-sm font-bold text-white">{{ formatRoleName(role.name) }}</span>
                                                    <span class="block text-[10px] text-gray-400">Nivel de acceso</span>
                                                </div>
                                                <CheckCircle v-if="form.role_id === role.id" :size="16" class="ml-auto text-blue-400" />
                                            </div>
                                        </div>
                                        
                                        <div v-else class="p-4 bg-red-900/20 border border-red-800 text-red-400 rounded-lg text-sm text-center">
                                            <p class="font-bold">No hay roles disponibles.</p>
                                            <p class="text-xs mt-1">Tu nivel de usuario no permite asignar roles inferiores o no existen.</p>
                                        </div>

                                        <p v-if="form.errors.role_id" class="text-red-500 text-xs mt-1">{{ form.errors.role_id }}</p>
                                    </div>

                                    <div v-if="branches.length > 1">
                                        <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Sucursal Asignada *</label>
                                        
                                        <div class="relative">
                                            <Building :size="16" class="absolute left-3 top-3.5 text-gray-500 z-10" />
                                            <select v-model="form.branch_id" class="w-full bg-gray-900 border border-gray-600 text-white rounded-lg p-3 pl-10 outline-none focus:border-blue-500 appearance-none">
                                                <option value="" disabled>Selecciona el lugar de trabajo...</option>
                                                <option v-for="branch in branches" :key="branch.id" :value="branch.id">
                                                    {{ branch.name }} - {{ branch.city }}
                                                </option>
                                            </select>
                                        </div>
                                        
                                        <p class="text-[10px] text-gray-500 mt-1">El usuario solo verá data relacionada a esta sucursal.</p>
                                        <p v-if="form.errors.branch_id" class="text-red-500 text-xs mt-1">{{ form.errors.branch_id }}</p>
                                    </div>
                                </div>
                            </div>

                        </Transition>
                    </div>

                    <div class="px-8 py-4 bg-gray-900 border-t border-gray-700 flex justify-between items-center">
                        <button type="button" @click="prevStep" class="flex items-center gap-2 px-4 py-2 text-sm font-bold text-gray-500 hover:text-white disabled:opacity-0" :disabled="currentStep === 1">
                            <ArrowLeft :size="16" /> Atrás
                        </button>

                        <button v-if="currentStep < steps.length" type="button" @click="nextStep" class="flex items-center gap-2 px-6 py-2.5 bg-gray-700 border border-gray-600 hover:border-gray-500 text-white rounded-lg text-sm font-bold shadow-sm transition-all">
                            Siguiente <ArrowRight :size="16" />
                        </button>

                        <button v-else type="button" @click="submit" :disabled="form.processing" class="flex items-center gap-2 px-8 py-2.5 bg-blue-600 hover:bg-blue-500 text-white rounded-lg text-sm font-bold shadow-lg shadow-blue-900/30 transition-all disabled:opacity-50 hover:scale-105">
                            <Save :size="18" /> {{ form.processing ? 'Guardando...' : 'Crear Usuario' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>