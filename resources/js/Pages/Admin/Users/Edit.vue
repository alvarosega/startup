<script setup>
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { useForm, Link } from '@inertiajs/vue3';
    import BaseCheckbox from '@/Components/Base/BaseCheckbox.vue';
    import { ref } from 'vue';
    import { Save, UserCheck, KeyRound, Building, History, X, ArrowLeft } from 'lucide-vue-next';
    import BaseInput from '@/Components/Base/BaseInput.vue';
    import RoleSelector from '@/Components/Base/RoleSelector.vue';
    
    const props = defineProps({ 
        user: Object, 
        roles: Array, 
        branches: Array 
        // NOTA: No hay prop 'filters' aquí - eliminado
    });
    
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
            <!-- HEADER -->
            <template #header>
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div>
                        <div class="flex items-center gap-3 mb-2">
                            <Link :href="route('admin.users.index')" 
                                  class="btn btn-ghost btn-sm">
                                <ArrowLeft :size="16" />
                            </Link>
                            <h1 class="text-3xl font-display font-black text-foreground">
                                Editar Perfil
                            </h1>
                        </div>
                        <p class="text-muted-foreground font-medium text-sm">
                            Actualizando datos de <span class="text-primary font-bold">{{ user.first_name }} {{ user.last_name }}</span>
                        </p>
                    </div>
                </div>
            </template>
    
            <div class="max-w-5xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    
                    <div class="lg:col-span-2 space-y-8">
                        
                        <!-- INFORMACIÓN PERSONAL -->
                        <div class="card">
                            <div class="card-header">
                                <div class="flex items-center gap-2">
                                    <div class="p-2 bg-primary/10 rounded-lg text-primary">
                                        <UserCheck :size="20" />
                                    </div>
                                    <h2 class="text-sm font-bold text-foreground uppercase tracking-wider">
                                        Información Personal
                                    </h2>
                                </div>
                            </div>
                            
                            <div class="card-content">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                    <BaseInput v-model="form.first_name" 
                                             label="Nombre" 
                                             :error="form.errors.first_name" 
                                             required />
                                    <BaseInput v-model="form.last_name" 
                                             label="Apellido" 
                                             :error="form.errors.last_name" 
                                             required />
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <BaseInput v-model="form.email" 
                                             type="email"
                                             label="Email Corporativo" 
                                             :error="form.errors.email" />
                                    <BaseInput v-model="form.phone" 
                                             label="Celular (ID Login)" 
                                             :error="form.errors.phone" 
                                             required />
                                </div>
                            </div>
                        </div>
    
                        <!-- CREDENCIALES -->
                        <div class="card">
                            <div class="card-header">
                                <div class="flex items-center gap-2">
                                    <div class="p-2 bg-primary/10 rounded-lg text-primary">
                                        <KeyRound :size="20" />
                                    </div>
                                    <h2 class="text-sm font-bold text-foreground uppercase tracking-wider">
                                        Credenciales
                                    </h2>
                                </div>
                            </div>
    
                            <div class="card-content">
                                <div v-if="!isChangingPassword" class="flex items-center justify-between p-5 bg-muted/20 rounded-xl border border-border">
                                    <div>
                                        <span class="block font-bold text-foreground text-sm mb-1">
                                            Contraseña Encriptada
                                        </span>
                                        <p class="text-xs text-muted-foreground">
                                            La contraseña actual es segura y no es visible.
                                        </p>
                                    </div>
                                    <button @click="isChangingPassword = true" type="button" 
                                            class="btn btn-outline btn-sm">
                                        Restablecer
                                    </button>
                                </div>
    
                                <div v-else class="bg-muted/10 p-5 rounded-xl border border-border animate-in">
                                    <div class="flex items-center justify-between mb-4">
                                        <label class="text-xs font-bold text-foreground uppercase tracking-wider">
                                            Nueva Contraseña
                                        </label>
                                        <button @click="isChangingPassword = false; form.password = ''" type="button" 
                                                class="btn btn-ghost btn-sm text-error">
                                            <X :size="12" /> Cancelar
                                        </button>
                                    </div>
                                    <BaseInput v-model="form.password" 
                                             type="password" 
                                             placeholder="Escribe la nueva contraseña..." 
                                             :error="form.errors.password" 
                                             autofocus />
                                    <p class="text-xs text-muted-foreground mt-3 flex items-center gap-1">
                                        <History :size="12" />
                                        Si dejas este campo vacío o cancelas, se mantendrá la contraseña anterior.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
    
                    <!-- PANEL LATERAL: ACCESO Y ROL -->
                    <div class="space-y-8">
                        <div class="card">
                            <div class="card-header">
                                <div class="flex items-center gap-2">
                                    <div class="p-2 bg-primary/10 rounded-lg text-primary">
                                        <Building :size="20" />
                                    </div>
                                    <h2 class="text-sm font-bold text-foreground uppercase tracking-wider">
                                        Acceso & Rol
                                    </h2>
                                </div>
                            </div>
    
                            <div class="card-content space-y-6">
                                <!-- ESTADO DE CUENTA -->
                                <BaseCheckbox 
                                    v-model="form.is_active" 
                                    label="Estado de Cuenta"
                                    :true-value="1" 
                                    :false-value="0"
                                    class="p-4 bg-muted/10 rounded-xl border border-border"
                                >
                                    <div class="flex flex-col">
                                        <span class="text-sm font-bold text-foreground">Estado de Cuenta</span>
                                        <span class="text-xs font-medium" :class="form.is_active ? 'text-success' : 'text-error'">
                                            {{ form.is_active ? '● Activo' : '● Acceso Revocado' }}
                                        </span>
                                    </div>
                                </BaseCheckbox>
    
                                <!-- SELECTOR DE ROL -->
                                <div>
                                    <RoleSelector v-model="form.role_id" 
                                                :roles="roles" 
                                                :error="form.errors.role_id" 
                                                required />
                                </div>
    
                                <!-- SELECTOR DE SUCURSAL -->
                                <div v-if="branches && branches.length > 0">
                                    <label class="form-label">
                                        Sucursal Base
                                    </label>
                                    <select v-model="form.branch_id" 
                                            class="form-input">
                                        <option :value="null">Sin sucursal</option>
                                        <option v-for="b in branches" 
                                                :key="b.id" 
                                                :value="b.id">
                                            {{ b.name }}
                                        </option>
                                    </select>
                                    <p v-if="form.errors.branch_id" class="form-error">
                                        {{ form.errors.branch_id }}
                                    </p>
                                </div>
    
                                <!-- BOTONES DE ACCIÓN -->
                                <div class="flex flex-col sm:flex-row gap-3 pt-4 border-t border-border/50">
                                    <Link :href="route('admin.users.index')" 
                                          class="btn btn-outline btn-md flex-1">
                                        Cancelar
                                    </Link>
                                    <button @click="submit" 
                                            :disabled="form.processing" 
                                            class="btn btn-primary btn-md flex-1 flex items-center justify-center gap-2">
                                        <Save :size="18" /> 
                                        <span>Guardar Cambios</span>
                                    </button>
                                </div>
                            </div>
                        </div>
    
                        <!-- INFORMACIÓN ADICIONAL -->
                        <div class="card">
                            <div class="card-content">
                                <h3 class="text-sm font-bold text-foreground mb-3">Información Adicional</h3>
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-muted-foreground">ID:</span>
                                        <span class="font-mono text-foreground">{{ user.id }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-muted-foreground">Creado:</span>
                                        <span class="text-foreground">{{ new Date(user.created_at).toLocaleDateString() }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-muted-foreground">Última actualización:</span>
                                        <span class="text-foreground">{{ new Date(user.updated_at).toLocaleDateString() }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AdminLayout>
    </template>