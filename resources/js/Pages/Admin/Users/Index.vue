<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import UserFilters from '@/Components/Admin/Users/UserFilters.vue';
import UserGroup from '@/Components/Admin/Users/UserGroup.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import { debounce } from 'lodash';
import { 
    UserPlus, SearchX, Users, Shield, Building2, UserCog, 
    Briefcase, Truck, Filter, RefreshCw, ChevronRight, X 
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

// Estado de UI
const showMobileFilters = ref(false);

// --- LÓGICA DE AGRUPACIÓN SEMÁNTICA ---
const groupedUsers = computed(() => {
    if (!props.users.data) return {};

    return props.users.data.reduce((acc, user) => {
        let groupName = '';
        let groupIcon = Users;
        let colorVariant = 'primary'; // Mapeo a tus variables CSS

        if (user.role_key === 'super_admin') {
            groupName = 'Administración Global';
            groupIcon = Shield;
            colorVariant = 'accent';
        } else if (user.role_key === 'branch_admin') {
            groupName = `Gestión - ${user.branch || 'Sin Sucursal'}`;
            groupIcon = Building2;
            colorVariant = 'primary';
        } else if (['logistics_manager', 'inventory_manager', 'finance_manager'].includes(user.role_key)) {
            groupName = `Operaciones - ${user.branch || 'General'}`;
            groupIcon = Briefcase;
            colorVariant = 'success';
        } else if (user.role_key === 'driver') {
            groupName = 'Flota de Conductores';
            groupIcon = Truck;
            colorVariant = 'warning';
        } else {
            groupName = user.branch || 'Sin Asignar';
            groupIcon = Users;
            colorVariant = 'muted';
        }

        if (!acc[groupName]) {
            acc[groupName] = { users: [], icon: groupIcon, variant: colorVariant };
        }
        acc[groupName].users.push(user);
        return acc;
    }, {});
});

watch(params, debounce((val) => {
    router.get(route('admin.users.index'), val, { 
        preserveState: true, replace: true, preserveScroll: true 
    });
}, 300), { deep: true });

const clearFilters = () => {
    params.value = { search: '', role_id: '', branch_id: '' };
};
</script>

<template>
    <AdminLayout>
        <template #header>
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                <div class="animate-slide-up">
                    <div class="flex items-center gap-4">
                        <div class="avatar avatar-lg bg-gradient-to-br from-primary to-secondary text-primary-foreground shadow-lg shadow-primary/20">
                            <Users :size="24" />
                        </div>
                        <div>
                            <h1 class="text-3xl font-display font-black tracking-tight text-gradient-primary">
                                Gestión de Equipo
                            </h1>
                            <p class="text-muted-foreground font-medium text-sm">Control centralizado de identidades y permisos</p>
                        </div>
                    </div>
                </div>
                
                <div class="flex items-center gap-3 w-full md:w-auto">
                    <button @click="showMobileFilters = true" class="md:hidden btn btn-outline btn-md flex-1 gap-2 glass">
                        <Filter :size="18" /> Filtros
                    </button>
                    <Link :href="route('admin.users.create')" class="btn btn-primary btn-md flex-1 md:flex-none gap-2 shadow-lg shadow-primary/25">
                        <UserPlus :size="18" /> <span>Nuevo Usuario</span>
                    </Link>
                </div>
            </div>
        </template>

        <div class="space-y-8 pb-20">
            <div class="flex overflow-x-auto pb-4 -mx-4 px-4 md:mx-0 md:px-0 md:grid md:grid-cols-4 gap-4 scrollbar-hide snap-x">
                <div v-for="(stat, idx) in [
                    { label: 'Total', value: users.total, icon: Users, color: 'primary' },
                    { label: 'Admins', value: users.data.filter(u => u.role_key === 'super_admin').length, icon: Shield, color: 'accent' },
                    { label: 'Operativos', value: users.data.filter(u => ['logistics_manager', 'inventory_manager'].includes(u.role_key)).length, icon: Briefcase, color: 'success' },
                    { label: 'Sedes', value: new Set(users.data.map(u => u.branch)).size, icon: Building2, color: 'info' }
                ]" :key="idx" class="min-w-[240px] md:min-w-0 snap-center card p-5 border-l-4 transition-all hover:shadow-md" :class="`border-${stat.color}`">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-bold text-muted-foreground uppercase tracking-wider">{{ stat.label }}</p>
                            <p class="text-2xl font-black mt-1">{{ stat.value }}</p>
                        </div>
                        <div :class="`w-10 h-10 rounded-xl bg-${stat.color}/10 text-${stat.color} flex items-center justify-center`">
                            <component :is="stat.icon" :size="20" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="hidden md:block card glass p-2 border-border/40">
                <UserFilters v-model="params" :roles="roles" :branches="branches" @clear="clearFilters" />
            </div>

            <div v-if="users.data.length > 0" class="space-y-6 animate-in fade-in duration-500">
                <UserGroup 
                    v-for="(groupData, groupName) in groupedUsers" 
                    :key="groupName" 
                    :title="groupName" 
                    :data="groupData"
                />
            </div>

            <div v-else class="py-24 text-center glass rounded-3xl border-dashed border-2 border-border/60">
                <div class="w-20 h-20 bg-muted/50 rounded-full flex items-center justify-center mx-auto mb-6">
                    <SearchX :size="32" class="text-muted-foreground" />
                </div>
                <h3 class="text-xl font-display font-bold">Sin coincidencias</h3>
                <p class="text-muted-foreground max-w-xs mx-auto mt-2">Ajusta los filtros para encontrar al equipo que buscas.</p>
                <button @click="clearFilters" class="btn btn-outline btn-sm mt-6">Limpiar todos los filtros</button>
            </div>
        </div>

        <Transition name="slide-right">
            <div v-if="showMobileFilters" class="fixed inset-0 z-[100] md:hidden">
                <div class="absolute inset-0 bg-background/80 backdrop-blur-sm" @click="showMobileFilters = false"></div>
                <div class="absolute inset-y-0 right-0 w-[85%] max-w-sm bg-card shadow-2xl border-l border-border p-6 flex flex-col">
                    <div class="flex items-center justify-between mb-8">
                        <h2 class="text-lg font-display font-bold">Filtros de Equipo</h2>
                        <button @click="showMobileFilters = false" class="btn btn-ghost btn-sm btn-circle"><X :size="20"/></button>
                    </div>
                    <div class="flex-1 space-y-6">
                        <UserFilters v-model="params" :roles="roles" :branches="branches" layout="vertical" />
                    </div>
                    <div class="pt-6 border-t border-border mt-auto">
                        <button @click="showMobileFilters = false" class="btn btn-primary w-full">Aplicar Filtros</button>
                        <button @click="clearFilters(); showMobileFilters = false" class="btn btn-ghost w-full mt-2 text-muted-foreground text-xs">Restablecer todo</button>
                    </div>
                </div>
            </div>
        </Transition>
    </AdminLayout>
</template>

<style scoped>
.slide-right-enter-active, .slide-right-leave-active { transition: transform 0.3s var(--ease-smooth); }
.slide-right-enter-from, .slide-right-leave-to { transform: translateX(100%); }
</style>