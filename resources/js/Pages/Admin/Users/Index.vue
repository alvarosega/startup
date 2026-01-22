<script setup>
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import UserFilters from '@/Components/Admin/Users/UserFilters.vue';
    import UserGroup from '@/Components/Admin/Users/UserGroup.vue';
    import { Link, router } from '@inertiajs/vue3';
    import { ref, watch, computed } from 'vue';
    import { debounce } from 'lodash';
    import { UserPlus, SearchX, Users, Shield, Building2, UserCog, Briefcase } from 'lucide-vue-next';
    
    const props = defineProps({ 
        users: Object,
        filters: Object,
        roles: Array,
        branches: Array
    });
    
    const params = ref({
        search: props.filters.search || '',
        role_id: props.filters.role_id || '',
        branch_id: props.filters.branch_id || '',
    });
    
    // --- LÓGICA DE AGRUPACIÓN MEJORADA ---
    const groupedUsers = computed(() => {
        if (!props.users.data) return {};
    
        const groups = props.users.data.reduce((acc, user) => {
            let groupName = '';
            let groupIcon = Users;
            let groupColor = 'primary';
    
            // 1. Administración Global
            if (user.role_key === 'super_admin') {
                groupName = 'Administración Global';
                groupIcon = Shield;
                groupColor = 'violet';
            } 
            // 2. Gestión de Sucursal
            else if (user.role_key === 'branch_admin') {
                groupName = `Gestión - ${user.branch || 'Sin Sucursal'}`;
                groupIcon = Building2;
                groupColor = 'blue';
            }
            // 3. Equipo Operativo
            else if (['logistics_manager', 'inventory_manager', 'finance_manager'].includes(user.role_key)) {
                groupName = `Operaciones - ${user.branch || 'General'}`;
                groupIcon = Briefcase;
                groupColor = 'emerald';
            }
            // 4. Clientes
            else if (user.role_key === 'client' || user.role_key === 'customer') {
                groupName = 'Clientes Registrados';
                groupIcon = UserCog;
                groupColor = 'amber';
            }
            // 5. Sin categoría específica
            else {
                groupName = user.branch || 'Sin Asignar';
                groupIcon = Users;
                groupColor = 'slate';
            }
    
            if (!acc[groupName]) {
                acc[groupName] = {
                    users: [],
                    icon: groupIcon,
                    color: groupColor
                };
            }
            acc[groupName].users.push(user);
            return acc;
        }, {});
    
        return groups;
    });
    
    // Búsqueda reactiva con debounce
    watch(params, debounce((val) => {
        router.get(route('admin.users.index'), val, { 
            preserveState: true, 
            replace: true, 
            preserveScroll: true 
        });
    }, 300), { deep: true });
    
    const handleDelete = (userId) => {
        if (confirm('¿Estás seguro de eliminar este usuario? Esta acción no se puede deshacer.')) {
            router.delete(route('admin.users.destroy', userId));
        }
    };
    </script>
    
    <template>
        <AdminLayout>
            <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
                
                <!-- HEADER SECTION -->
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-10">
                    <div class="animate-slide-up">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-12 h-12 rounded-xl gradient-primary flex items-center justify-center shadow-lg shadow-primary/20">
                                <Users :size="24" class="text-white" />
                            </div>
                            <div>
                                <h1 class="text-3xl lg:text-4xl font-black text-foreground font-display tracking-tight">
                                    Gestión de Equipo
                                </h1>
                                <p class="text-muted-foreground font-medium text-sm mt-1">
                                    Administra usuarios, permisos y accesos
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <Link :href="route('admin.users.create')" 
                          class="flex items-center gap-3 bg-gradient-to-r from-primary to-secondary text-white px-6 py-3.5 rounded-xl font-bold shadow-lg shadow-primary/25 hover:shadow-primary/40 transition-all duration-300 ease-elastic hover:scale-[1.02] active:scale-[0.98] cursor-pointer group animate-slide-up">
                        <UserPlus :size="20" class="transition-transform duration-300 group-hover:scale-110" /> 
                        <span class="font-semibold">Nuevo Usuario</span>
                    </Link>
                </div>
    
                <!-- FILTERS SECTION -->
                <div class="mb-10 sticky top-4 z-20 animate-fade-in">
                    <div class="glass rounded-2xl p-1 border border-border/50">
                        <UserFilters v-model="params" :roles="roles" :branches="branches" />
                    </div>
                </div>
    
                <!-- USERS CONTENT -->
                <div v-if="users.data.length > 0" class="space-y-6 animate-in">
                    <!-- STATS BAR -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
                        <div class="card p-5">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-muted-foreground font-medium">Total Usuarios</p>
                                    <p class="text-3xl font-bold text-foreground mt-2">{{ users.total }}</p>
                                </div>
                                <div class="w-12 h-12 rounded-full bg-blue-50 flex items-center justify-center">
                                    <Users :size="20" class="text-blue-600" />
                                </div>
                            </div>
                        </div>
                        
                        <div class="card p-5">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-muted-foreground font-medium">Administradores</p>
                                    <p class="text-3xl font-bold text-foreground mt-2">
                                        {{ users.data.filter(u => u.role_key === 'super_admin').length }}
                                    </p>
                                </div>
                                <div class="w-12 h-12 rounded-full bg-violet-50 flex items-center justify-center">
                                    <Shield :size="20" class="text-violet-600" />
                                </div>
                            </div>
                        </div>
                        
                        <div class="card p-5">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-muted-foreground font-medium">Equipo Operativo</p>
                                    <p class="text-3xl font-bold text-foreground mt-2">
                                        {{ users.data.filter(u => ['logistics_manager', 'inventory_manager', 'finance_manager'].includes(u.role_key)).length }}
                                    </p>
                                </div>
                                <div class="w-12 h-12 rounded-full bg-emerald-50 flex items-center justify-center">
                                    <Briefcase :size="20" class="text-emerald-600" />
                                </div>
                            </div>
                        </div>
                        
                        <div class="card p-5">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-muted-foreground font-medium">Sucursales Activas</p>
                                    <p class="text-3xl font-bold text-foreground mt-2">
                                        {{ new Set(users.data.map(u => u.branch)).size }}
                                    </p>
                                </div>
                                <div class="w-12 h-12 rounded-full bg-amber-50 flex items-center justify-center">
                                    <Building2 :size="20" class="text-amber-600" />
                                </div>
                            </div>
                        </div>
                    </div>
    
                    <!-- USER GROUPS -->
                    <div class="space-y-8">
                        <div v-for="(groupData, groupName) in groupedUsers" :key="groupName" 
                             class="card overflow-hidden border-border hover:border-primary/30 transition-all duration-300 hover:shadow-lg group">
                            
                            <!-- GROUP HEADER -->
                            <div class="px-6 py-4 bg-gradient-to-r from-white to-gray-50/50 border-b border-border/50 flex justify-between items-center">
                                <div class="flex items-center gap-3">
                                    <div :class="`w-10 h-10 rounded-full bg-${groupData.color}-50 flex items-center justify-center border border-${groupData.color}-100`">
                                        <component :is="groupData.icon" :size="18" :class="`text-${groupData.color}-600`" />
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-foreground font-display text-lg">{{ groupName }}</h3>
                                        <p class="text-xs text-muted-foreground font-medium">
                                            {{ groupData.users.length }} miembro{{ groupData.users.length !== 1 ? 's' : '' }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-bold bg-white border border-border px-3 py-1.5 rounded-full text-muted-foreground">
                                        {{ groupData.users.length }} usuarios
                                    </span>
                                </div>
                            </div>
                            
                            <!-- USER LIST -->
                            <UserGroup :users="groupData.users" @delete-user="handleDelete" />
                        </div>
                    </div>
    
                    <!-- PAGINATION -->
                    <div v-if="users.links && users.links.length > 3" class="mt-10">
                        <div class="flex justify-center">
                            <div class="flex items-center gap-1 bg-white rounded-xl border border-border p-2 shadow-sm">
                                <template v-for="(link, index) in users.links" :key="index">
                                    <Link 
                                        v-if="link.url"
                                        :href="link.url"
                                        :class="[
                                            'px-4 py-2 rounded-lg font-medium text-sm transition-all duration-200',
                                            link.active 
                                                ? 'bg-primary text-white shadow-md shadow-primary/20' 
                                                : 'text-foreground hover:bg-muted hover:text-foreground'
                                        ]"
                                        v-html="link.label"
                                    />
                                    <span v-else class="px-4 py-2 text-muted-foreground" v-html="link.label" />
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
    
                <!-- EMPTY STATE -->
                <div v-else class="py-20 text-center animate-fade-in">
                    <div class="max-w-md mx-auto">
                        <div class="w-24 h-24 bg-gradient-to-br from-gray-50 to-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-inner">
                            <SearchX :size="40" class="text-gray-300" />
                        </div>
                        <h3 class="text-2xl font-bold text-foreground font-display mb-3">No se encontraron usuarios</h3>
                        <p class="text-muted-foreground mb-8">
                            No hay usuarios que coincidan con los filtros actuales.
                            Intenta ajustar los criterios de búsqueda o crea un nuevo usuario.
                        </p>
                        <div class="flex flex-col sm:flex-row gap-4 justify-center">
                            <button @click="params = { search: '', role_id: '', branch_id: '' }"
                                    class="px-6 py-3 bg-white border border-border rounded-xl font-medium text-foreground hover:bg-muted transition-all duration-200 hover:border-primary/30">
                                Limpiar filtros
                            </button>
                            <Link :href="route('admin.users.create')"
                                  class="px-6 py-3 gradient-primary text-white rounded-xl font-medium shadow-md shadow-primary/25 hover:shadow-primary/40 transition-all duration-200">
                                Crear primer usuario
                            </Link>
                        </div>
                    </div>
                </div>
    
            </div>
        </AdminLayout>
    </template>
    
    <style scoped>
    .gradient-primary {
        background: linear-gradient(135deg, hsl(var(--primary)) 0%, hsl(var(--secondary)) 100%);
    }
    
    .glass {
        background: rgba(255, 255, 255, 0.8);
        backdrop-filter: blur(12px);
    }
    
    .animate-slide-up {
        animation: slideUp 0.4s cubic-bezier(0.16, 1, 0.3, 1);
    }
    
    .animate-fade-in {
        animation: fadeIn 0.3s ease-out;
    }
    
    .animate-in {
        animation: enter 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }
    
    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    
    @keyframes enter {
        from {
            opacity: 0;
            transform: translateY(10px) scale(0.98);
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }
    </style>