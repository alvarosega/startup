<script setup>
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { useForm, Link } from '@inertiajs/vue3';
    import { ref } from 'vue';
    import { Save, UserCheck, KeyRound, Building, History, X } from 'lucide-vue-next';
    import BaseInput from '@/Components/Base/BaseInput.vue';
    import RoleSelector from '@/Components/Base/RoleSelector.vue';
    
    const props = defineProps({ user: Object, roles: Array, branches: Array });
    
    // Toggle de Seguridad
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
    
    const submit = () => form.put(route('admin.users.update', props.user.id));
    </script>
    
    <template>
        <AdminLayout>
            <div class="max-w-5xl mx-auto py-8">
                
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
                    <div>
                        <h1 class="text-3xl font-display font-black text-content">Editar Perfil</h1>
                        <p class="text-sm text-content-light font-medium mt-1">
                            Actualizando datos de <span class="text-primary font-bold">{{ user.first_name }} {{ user.last_name }}</span>
                        </p>
                    </div>
                    <div class="flex gap-3 w-full sm:w-auto">
                        <Link :href="route('admin.users.index')" 
                              class="px-5 py-2.5 text-sm font-bold text-content-light bg-surface border border-line rounded-xl hover:bg-base hover:text-content transition-colors flex-1 sm:flex-none text-center">
                            Cancelar
                        </Link>
                        <button @click="submit" :disabled="form.processing" 
                                class="flex items-center justify-center gap-2 bg-primary text-white px-6 py-2.5 rounded-xl font-bold shadow-lg shadow-primary/25 hover:brightness-110 active:scale-95 transition-all flex-1 sm:flex-none disabled:opacity-50">
                            <Save :size="18" /> Guardar Cambios
                        </button>
                    </div>
                </div>
    
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    
                    <div class="lg:col-span-2 space-y-8">
                        
                        <div class="bg-surface rounded-2xl p-6 border border-line shadow-sm">
                            <div class="flex items-center gap-2 mb-6 pb-4 border-b border-line">
                                <div class="p-2 bg-base rounded-lg text-primary"><UserCheck :size="20" /></div>
                                <h2 class="text-sm font-black text-content uppercase tracking-widest">Información Personal</h2>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <BaseInput v-model="form.first_name" label="Nombre" :error="form.errors.first_name" />
                                <BaseInput v-model="form.last_name" label="Apellido" :error="form.errors.last_name" />
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <BaseInput v-model="form.email" label="Email Corporativo" :error="form.errors.email" />
                                <BaseInput v-model="form.phone" label="Celular (ID Login)" :error="form.errors.phone" />
                            </div>
                        </div>
    
                        <div class="bg-surface rounded-2xl p-6 border border-line shadow-sm">
                            <div class="flex items-center gap-2 mb-6 pb-4 border-b border-line">
                                <div class="p-2 bg-base rounded-lg text-primary"><KeyRound :size="20" /></div>
                                <h2 class="text-sm font-black text-content uppercase tracking-widest">Credenciales</h2>
                            </div>
    
                            <div v-if="!isChangingPassword" class="flex items-center justify-between p-5 bg-base rounded-xl border border-line">
                                <div>
                                    <span class="block font-bold text-content text-sm mb-1">Contraseña Encriptada</span>
                                    <p class="text-xs text-content-light">La contraseña actual es segura y no es visible.</p>
                                </div>
                                <button @click="isChangingPassword = true" type="button" 
                                        class="px-4 py-2 text-xs font-bold text-primary bg-white border border-line rounded-lg hover:border-primary transition-colors shadow-sm">
                                    Restablecer
                                </button>
                            </div>
    
                            <div v-else class="bg-base/50 p-5 rounded-xl border border-line animate-in fade-in slide-in-from-top-2 duration-300">
                                <div class="flex items-center justify-between mb-4">
                                    <label class="text-xs font-bold text-content uppercase tracking-wider">Nueva Contraseña</label>
                                    <button @click="isChangingPassword = false; form.password = ''" type="button" 
                                            class="text-xs font-bold text-red-500 hover:text-red-600 flex items-center gap-1 bg-white px-2 py-1 rounded border border-line">
                                        <X :size="12" /> Cancelar
                                    </button>
                                </div>
                                <BaseInput v-model="form.password" type="password" placeholder="Escribe la nueva contraseña..." :error="form.errors.password" autofocus />
                                <p class="text-[11px] text-content-light mt-3 flex items-center gap-1">
                                    <History :size="12" />
                                    Si dejas este campo vacío o cancelas, se mantendrá la contraseña anterior.
                                </p>
                            </div>
                        </div>
                    </div>
    
                    <div class="space-y-8">
                        <div class="bg-surface rounded-2xl p-6 border border-line shadow-sm h-full">
                            <div class="flex items-center gap-2 mb-6 pb-4 border-b border-line">
                                <div class="p-2 bg-base rounded-lg text-primary"><Building :size="20" /></div>
                                <h2 class="text-sm font-black text-content uppercase tracking-widest">Acceso & Rol</h2>
                            </div>
    
                            <div class="mb-8 p-4 bg-base rounded-xl border border-line flex items-center gap-4 hover:border-primary/30 transition-colors cursor-pointer" @click="form.is_active = !form.is_active">
                                <div class="relative flex items-center">
                                    <input type="checkbox" v-model="form.is_active" class="checkbox checkbox-primary rounded-md w-6 h-6 border-line data-[state=checked]:bg-primary data-[state=checked]:border-primary" />
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-sm font-bold text-content">Estado de Cuenta</span>
                                    <span class="text-xs font-medium" :class="form.is_active ? 'text-green-600' : 'text-red-500'">
                                        {{ form.is_active ? '● Activo' : '● Acceso Revocado' }}
                                    </span>
                                </div>
                            </div>
    
                            <div class="mb-8">
                                <RoleSelector v-model="form.role_id" :roles="roles" :error="form.errors.role_id" />
                            </div>
    
                            <div v-if="branches.length > 1">
                                 <label class="block text-xs font-bold text-content-light uppercase tracking-wider mb-2">Sucursal Base</label>
                                 <select v-model="form.branch_id" class="w-full bg-base border border-line rounded-xl p-3 text-content text-sm font-medium outline-none focus:border-primary focus:ring-4 focus:ring-primary/10 transition-all">
                                    <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }}</option>
                                 </select>
                                 <p v-if="form.errors.branch_id" class="text-xs text-red-500 font-bold mt-1">{{ form.errors.branch_id }}</p>
                            </div>
                        </div>
                    </div>
    
                </div>
            </div>
        </AdminLayout>
    </template>
    