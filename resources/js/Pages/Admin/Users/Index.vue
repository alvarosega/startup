<script setup>
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import UserFilters from '@/Components/Admin/Users/UserFilters.vue';
    import UserGroup from '@/Components/Admin/Users/UserGroup.vue';
    import { Link, router } from '@inertiajs/vue3';
    import { ref, watch, computed } from 'vue';
    import { debounce } from 'lodash';
    import { 
        UserPlus, SearchX, Users, Shield, Building2, UserCog, 
        Briefcase, Truck, Filter, RefreshCw 
    } from 'lucide-vue-next';
    
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
    
    // Estado para mostrar/ocultar filtros en móvil
    const showMobileFilters = ref(false);
    
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
            // 4. Conductores
            else if (user.role_key === 'driver') {
                groupName = 'Flota de Conductores';
                groupIcon = Truck;
                groupColor = 'orange';
            }
            // 5. Clientes
            else if (user.role_key === 'client' || user.role_key === 'customer') {
                groupName = 'Clientes Registrados';
                groupIcon = UserCog;
                groupColor = 'amber';
            }
            // 6. Resto
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
    
    const clearFilters = () => {
        params.value = { search: '', role_id: '', branch_id: '' };
    };
    </script>
    
    <template>
        <AdminLayout>
            <!-- HEADER SECTION -->
            <template #header>
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-6">
                    <div class="animate-slide-up">
                        <div class="flex items-center gap-4 mb-3">
                            <div class="avatar avatar-lg bg-gradient-to-br from-primary to-secondary text-primary-foreground shadow-lg">
                                <Users :size="24" />
                            </div>
                            <div>
                                <h1 class="text-3xl lg:text-4xl font-display font-black text-foreground tracking-tight">
                                    Gestión de Equipo
                                </h1>
                                <p class="text-muted-foreground font-medium text-sm mt-1">
                                    Administra usuarios, permisos y accesos
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-3">
                        <!-- Mobile Filter Toggle -->
                        <button @click="showMobileFilters = !showMobileFilters"
                                class="md:hidden btn btn-outline btn-sm flex items-center gap-2">
                            <Filter :size="16" />
                            <span>Filtros</span>
                        </button>
    
                        <Link :href="route('admin.users.create')" 
                              class="btn btn-primary btn-lg flex items-center gap-2 group">
                            <UserPlus :size="18" class="transition-transform duration-fast group-hover:scale-110" /> 
                            <span>Nuevo Usuario</span>
                        </Link>
                    </div>
                </div>
            </template>
    
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <!-- FILTERS SECTION -->
                <div class="mb-8">
                    <!-- Mobile Filters Overlay -->
                    <div v-if="showMobileFilters" 
                         class="md:hidden fixed inset-0 z-40 bg-black/50 backdrop-blur-sm" 
                         @click="showMobileFilters = false">
                    </div>
                    
                    <div :class="[
                        'transition-all duration-300 ease-smooth',
                        showMobileFilters 
                            ? 'md:relative fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 z-50 w-[90vw] max-w-md' 
                            : 'relative'
                    ]">
                        <div class="card p-1 shadow-lg" :class="showMobileFilters ? 'max-h-[80vh] overflow-y-auto' : ''">
                            <div class="flex items-center justify-between mb-4 p-3 border-b border-border/50">
                                <div class="flex items-center gap-2">
                                    <Filter :size="18" class="text-muted-foreground" />
                                    <h3 class="text-sm font-bold text-foreground">Filtros de Búsqueda</h3>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button @click="clearFilters" 
                                            class="btn btn-ghost btn-sm text-xs flex items-center gap-1">
                                        <RefreshCw :size="14" />
                                        <span class="hidden sm:inline">Limpiar</span>
                                    </button>
                                    <button @click="showMobileFilters = false" 
                                            class="md:hidden btn btn-ghost btn-sm">
                                        ✕
                                    </button>
                                </div>
                            </div>
                            <UserFilters v-model="params" :roles="roles" :branches="branches" />
                        </div>
                    </div>
                </div>
    
                <!-- USERS CONTENT -->
                <div v-if="users.data.length > 0" class="space-y-8 animate-in">
                    <!-- STATS BAR -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                        <div class="card p-5 hover-lift">
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
                        
                        <div class="card p-5 hover-lift">
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
                        
                        <div class="card p-5 hover-lift">
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
                        
                        <div class="card p-5 hover-lift">
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
                             class="card overflow-hidden hover-lift-lg">
                            
                            <!-- GROUP HEADER -->
                            <div class="px-6 py-4 bg-gradient-to-r from-background to-muted/10 border-b border-border/50 flex justify-between items-center">
                                <div class="flex items-center gap-3">
                                    <div class="avatar avatar-md bg-gradient-to-br from-primary to-secondary text-primary-foreground shadow-md">
                                        <component :is="groupData.icon" :size="18" />
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-foreground font-display text-lg">{{ groupName }}</h3>
                                        <p class="text-xs text-muted-foreground font-medium">
                                            {{ groupData.users.length }} miembro{{ groupData.users.length !== 1 ? 's' : '' }}
                                        </p>
                                    </div>
                                </div>
                                <div class="badge badge-outline">
                                    {{ groupData.users.length }} usuarios
                                </div>
                            </div>
                            
                            <!-- USER LIST -->
                            <UserGroup :users="groupData.users" @delete-user="handleDelete" />
                        </div>
                    </div>
    
                    <!-- PAGINATION -->
                    <div v-if="users.links && users.links.length > 3" class="mt-10">
                        <div class="flex justify-center">
                            <div class="flex items-center gap-1 bg-card rounded-xl border border-border p-2 shadow-sm">
                                <template v-for="(link, index) in users.links" :key="index">
                                    <Link 
                                        v-if="link.url"
                                        :href="link.url"
                                        :class="[
                                            'px-4 py-2 rounded-lg font-medium text-sm transition-all duration-fast',
                                            link.active 
                                                ? 'btn btn-primary btn-sm' 
                                                : 'btn btn-ghost btn-sm'
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
                        <div class="w-24 h-24 bg-muted rounded-2xl flex items-center justify-center mx-auto mb-6">
                            <SearchX :size="40" class="text-muted-foreground" />
                        </div>
                        <h3 class="text-2xl font-display font-bold text-foreground mb-3">No se encontraron usuarios</h3>
                        <p class="text-muted-foreground mb-8">
                            No hay usuarios que coincidan con los filtros actuales.
                            Intenta ajustar los criterios de búsqueda o crea un nuevo usuario.
                        </p>
                        <div class="flex flex-col sm:flex-row gap-4 justify-center">
                            <button @click="clearFilters"
                                    class="btn btn-outline btn-md">
                                Limpiar filtros
                            </button>
                            <Link :href="route('admin.users.create')"
                                  class="btn btn-primary btn-md">
                                Crear primer usuario
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </AdminLayout>
    </template>