<script setup>
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { useForm, Link } from '@inertiajs/vue3';
    
    const props = defineProps({
        user: Object,
        roles: Array,
        branches: Array
    });
    
    const form = useForm({
        first_name: props.user.first_name,
        last_name: props.user.last_name,
        phone: props.user.phone,
        password: '',
        role_id: props.user.role_id,
        branch_id: props.user.branch_id,
        is_active: !!props.user.is_active
    });
    
    const submit = () => {
        form.put(route('admin.users.update', props.user.id));
    };
    </script>
    
    <template>
        <AdminLayout>
            <div class="max-w-4xl mx-auto">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-bold text-white">Editar Usuario: {{ user.first_name }}</h1>
                    <Link :href="route('admin.users.index')" class="text-gray-400 hover:text-white transition">Cancelar</Link>
                </div>
    
                <form @submit.prevent="submit" class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    
                    <div class="bg-gray-800 p-6 rounded-lg border border-gray-700 shadow-lg">
                        <h2 class="text-lg font-bold text-blue-400 mb-6 border-b border-gray-700 pb-2">Datos de Identidad</h2>
                        
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-gray-400 text-xs uppercase font-bold mb-2">Nombre</label>
                                <input v-model="form.first_name" type="text" class="w-full bg-gray-900 border border-gray-700 text-white rounded p-2 focus:border-blue-500 outline-none">
                                <p v-if="form.errors.first_name" class="text-red-500 text-xs mt-1">{{ form.errors.first_name }}</p>
                            </div>
                            <div>
                                <label class="block text-gray-400 text-xs uppercase font-bold mb-2">Apellido</label>
                                <input v-model="form.last_name" type="text" class="w-full bg-gray-900 border border-gray-700 text-white rounded p-2 focus:border-blue-500 outline-none">
                                <p v-if="form.errors.last_name" class="text-red-500 text-xs mt-1">{{ form.errors.last_name }}</p>
                            </div>
                        </div>
    
                        <div class="mb-4">
                            <label class="block text-gray-400 text-xs uppercase font-bold mb-2">Celular</label>
                            <input v-model="form.phone" type="text" class="w-full bg-gray-900 border border-gray-700 text-white rounded p-2 focus:border-blue-500 outline-none">
                            <p v-if="form.errors.phone" class="text-red-500 text-xs mt-1">{{ form.errors.phone }}</p>
                        </div>
    
                        <div class="mb-4">
                            <label class="block text-gray-400 text-xs uppercase font-bold mb-2">Nueva Contraseña (Opcional)</label>
                            <input v-model="form.password" type="text" class="w-full bg-gray-900 border border-gray-700 text-white rounded p-2 focus:border-blue-500 outline-none" placeholder="Dejar en blanco para mantener">
                            <p v-if="form.errors.password" class="text-red-500 text-xs mt-1">{{ form.errors.password }}</p>
                        </div>
    
                        <div>
                             <label class="flex items-center space-x-3 cursor-pointer">
                                <input v-model="form.is_active" type="checkbox" class="w-5 h-5 bg-gray-900 border-gray-700 rounded text-blue-600 focus:ring-blue-500">
                                <span class="text-gray-300 font-bold text-sm select-none">Usuario Activo</span>
                            </label>
                        </div>
                    </div>
    
                    <div class="bg-gray-800 p-6 rounded-lg border border-gray-700 shadow-lg h-fit">
                        <h2 class="text-lg font-bold text-blue-400 mb-6 border-b border-gray-700 pb-2">Asignación Operativa</h2>
    
                        <div class="mb-4">
                            <label class="block text-gray-400 text-xs uppercase font-bold mb-2">Rol del Sistema</label>
                            <select v-model="form.role_id" class="w-full bg-gray-900 border border-gray-700 text-white rounded p-2 focus:border-blue-500 outline-none">
                                <option v-for="role in roles" :key="role.id" :value="role.id">{{ role.display_name }}</option>
                            </select>
                            <p v-if="form.errors.role_id" class="text-red-500 text-xs mt-1">{{ form.errors.role_id }}</p>
                        </div>
    
                        <div>
                            <label class="block text-gray-400 text-xs uppercase font-bold mb-2">Sucursal Base</label>
                            <select v-model="form.branch_id" class="w-full bg-gray-900 border border-gray-700 text-white rounded p-2 focus:border-blue-500 outline-none">
                                <option v-for="branch in branches" :key="branch.id" :value="branch.id">{{ branch.name }}</option>
                            </select>
                            <p v-if="form.errors.branch_id" class="text-red-500 text-xs mt-1">{{ form.errors.branch_id }}</p>
                        </div>
                    </div>
    
                    <div class="md:col-span-2 flex justify-end">
                        <button type="submit" :disabled="form.processing" class="bg-blue-600 hover:bg-blue-500 text-white font-bold py-3 px-10 rounded-lg shadow-lg transition disabled:opacity-50">
                            Actualizar Usuario
                        </button>
                    </div>
                </form>
            </div>
        </AdminLayout>
    </template>