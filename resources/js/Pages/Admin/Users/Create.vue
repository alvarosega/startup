<script setup>
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { useForm, Link } from '@inertiajs/vue3';
    import { ref, computed, watch } from 'vue';
    import { User, Shield, CheckCircle, ArrowRight, ArrowLeft, Save, Building, ChevronRight } from 'lucide-vue-next';
    import BaseInput from '@/Components/Base/BaseInput.vue';
    
    const props = defineProps({ roles: Array, branches: Array });
    
    const steps = [
        { id: 1, title: 'Datos Personales', icon: User },
        { id: 2, title: 'Permisos y Acceso', icon: Shield },
    ];
    
    const currentStep = ref(1);
    
    // El email ya estaba definido aquí, solo faltaba el input visual
    const form = useForm({
        first_name: '', last_name: '', email: '', phone: '', password: '', role_id: null, branch_id: ''
    });

    // --- LÓGICA DINÁMICA DE SUCURSAL ---
    const selectedRole = computed(() => props.roles.find(r => r.id === form.role_id));
    
    const requiresBranch = computed(() => {
        if (!selectedRole.value) return true; 
        const globalRoles = ['super_admin', 'client', 'customer'];
        return !globalRoles.includes(selectedRole.value.name);
    });

    watch(requiresBranch, (val) => {
        if (!val) form.branch_id = ''; 
    });
    
    const progress = computed(() => ((currentStep.value - 1) / (steps.length - 1)) * 100);
    
    const next = () => {
        if (currentStep.value === 1) {
            // El email es opcional según el backend, así que no lo obligamos aquí
            if (!form.first_name || !form.last_name || !form.password || !form.phone) return alert('Completa los datos obligatorios (*).');
            currentStep.value++;
        }
    };
    
    const submit = () => form.post(route('admin.users.store'));
</script>
    
<template>
    <AdminLayout>
        <div class="max-w-3xl mx-auto py-10">
            
            <div class="mb-12 text-center">
                <h1 class="text-3xl font-black text-gray-800">Nuevo Usuario</h1>
                <p class="text-gray-500 font-medium mt-2">Configura el acceso al sistema en 2 pasos</p>
            </div>
    
            <div class="mb-8 flex justify-center gap-4">
                <span :class="currentStep >= 1 ? 'text-blue-600 font-bold' : 'text-gray-400'">1. Datos</span>
                <span class="text-gray-300">></span>
                <span :class="currentStep >= 2 ? 'text-blue-600 font-bold' : 'text-gray-400'">2. Acceso</span>
            </div>
    
            <div class="bg-white border border-gray-100 rounded-2xl shadow-xl overflow-hidden flex flex-col min-h-[450px]">
                <form @submit.prevent class="flex-1 flex flex-col">
                    <div class="p-10 flex-1">
                        
                        <div v-if="currentStep === 1" class="space-y-6 max-w-lg mx-auto">
                            <div class="grid grid-cols-2 gap-5">
                                <BaseInput v-model="form.first_name" label="Nombre *" placeholder="Ej: Ana" :error="form.errors.first_name" />
                                <BaseInput v-model="form.last_name" label="Apellido *" placeholder="Ej: Lopez" :error="form.errors.last_name" />
                            </div>

                            <BaseInput 
                                v-model="form.email" 
                                type="email" 
                                label="Correo Electrónico" 
                                placeholder="ana@empresa.com" 
                                :error="form.errors.email" 
                            />

                            <BaseInput v-model="form.phone" label="Celular (Login) *" placeholder="70012345" :error="form.errors.phone" />
                            <BaseInput v-model="form.password" type="password" label="Contraseña *" placeholder="******" :error="form.errors.password" />
                        </div>
    
                        <div v-else class="space-y-8 max-w-xl mx-auto">
                            
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Rol del Usuario *</label>
                                <div class="grid grid-cols-2 gap-3">
                                    <div v-for="role in roles" :key="role.id" 
                                         @click="form.role_id = role.id"
                                         :class="['cursor-pointer border rounded-xl p-4 transition-all hover:shadow-md', 
                                                  form.role_id === role.id ? 'border-blue-500 bg-blue-50 ring-2 ring-blue-200' : 'border-gray-200 bg-gray-50']">
                                        <div class="font-bold text-sm" :class="form.role_id === role.id ? 'text-blue-700' : 'text-gray-700'">
                                            {{ role.display_name || role.name }}
                                        </div>
                                    </div>
                                </div>
                                <p v-if="form.errors.role_id" class="text-xs text-red-500 mt-1">{{ form.errors.role_id }}</p>
                            </div>
                            
                            <div v-if="requiresBranch && branches.length > 0" class="space-y-2 animate-in fade-in slide-in-from-top-2">
                                <label class="block text-xs font-bold text-gray-500 uppercase">Asignar Sucursal <span class="text-red-500">*</span></label>
                                <div class="relative group">
                                    <Building class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400" :size="18" />
                                    <select v-model="form.branch_id" 
                                            class="w-full pl-11 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-gray-700 font-medium focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all appearance-none cursor-pointer">
                                        <option value="" disabled>Selecciona ubicación...</option>
                                        <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }}</option>
                                    </select>
                                    <ChevronRight class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 rotate-90 pointer-events-none" :size="16"/>
                                </div>
                                <p v-if="form.errors.branch_id" class="text-xs text-red-500 font-bold mt-1">{{ form.errors.branch_id }}</p>
                            </div>
                            <div v-else-if="form.role_id" class="p-3 bg-green-50 text-green-700 text-xs font-bold rounded-lg border border-green-100 text-center">
                                ✨ Este rol tiene acceso global (Sin sucursal).
                            </div>

                        </div>
                    </div>
    
                    <div class="px-8 py-5 bg-gray-50 border-t border-gray-100 flex justify-between items-center">
                        <button type="button" @click="currentStep--" class="text-sm font-bold text-gray-500 hover:text-gray-800 disabled:opacity-0" :disabled="currentStep === 1">
                            Atrás
                        </button>
    
                        <button v-if="currentStep === 1" type="button" @click="next" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-xl font-bold shadow-lg flex items-center gap-2 transition active:scale-95">
                            Continuar <ArrowRight :size="18" />
                        </button>
                        
                        <button v-else @click="submit" :disabled="form.processing" class="bg-green-600 hover:bg-green-700 text-white px-8 py-3 rounded-xl font-bold shadow-lg flex items-center gap-2 transition active:scale-95">
                            <Save :size="18" /> Guardar Usuario
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>