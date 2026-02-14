<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import UserFilters from '@/Components/Admin/Users/UserFilters.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import { debounce } from 'lodash';
import { 
    UserPlus, Search, Users, Shield, Building2, 
    Briefcase, Truck, MoreVertical, MessageCircle, 
    Phone, Edit, Trash2, MapPin, SlidersHorizontal 
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

const showMobileFilters = ref(false);
const activeMenuUserId = ref(null);

/**
 * ESTILOS SEMÁNTICOS POR ROL
 * Mapea la clave del rol a colores y etiquetas específicas
 */
const getRoleStyle = (roleKey) => {
    const key = roleKey || 'unknown';
    switch (key) {
        case 'super_admin': 
            return { bg: 'bg-violet-600', text: 'text-violet-600', border: 'border-violet-200', label: 'Global Admin' };
        case 'branch_admin': 
            return { bg: 'bg-blue-600', text: 'text-blue-600', border: 'border-blue-200', label: 'Gerente' };
        case 'driver': 
            return { bg: 'bg-amber-500', text: 'text-amber-600', border: 'border-amber-200', label: 'Conductor' };
        case 'customer': 
            return { bg: 'bg-emerald-500', text: 'text-emerald-600', border: 'border-emerald-200', label: 'Cliente' };
        default: 
            return { bg: 'bg-slate-600', text: 'text-slate-600', border: 'border-slate-200', label: 'Staff' };
    }
};

/**
 * AGRUPACIÓN POR SUCURSAL
 * Clasifica a los usuarios según su asignación geográfica o funcional
 */
const groupedUsers = computed(() => {
    if (!props.users.data) return {};
    return props.users.data.reduce((acc, user) => {
        let groupName = 'Sin Sucursal Asignada';
        
        if (user.role_key === 'super_admin') {
            groupName = 'Administración Global';
        } else if (user.type === 'driver') {
            groupName = 'Logística / Delivery';
        } else if (user.branch) {
            groupName = user.branch;
        }

        if (!acc[groupName]) acc[groupName] = [];
        acc[groupName].push(user);
        return acc;
    }, {});
});

const getWhatsappLink = (phone) => {
    if (!phone) return '#';
    const cleanPhone = phone.replace(/\D/g, '');
    return `https://wa.me/${cleanPhone}`; 
};

const toggleMenu = (userId) => {
    activeMenuUserId.value = activeMenuUserId.value === userId ? null : userId;
};

const closeMenu = () => activeMenuUserId.value = null;

const deleteUser = (user) => {
    if (confirm(`¿Estás seguro de eliminar a ${user.name}?`)) {
        router.delete(route('admin.users.destroy', user.id), {
            preserveScroll: true,
            onSuccess: () => closeMenu()
        });
    }
};

// Sincronización de filtros con el servidor
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
            <div class="flex items-center justify-between pt-1 pb-1">
                <div>
                    <h1 class="text-2xl font-display font-black tracking-tight text-foreground leading-none">
                        Equipo y Clientes
                    </h1>
                    <p class="text-[10px] text-muted-foreground font-medium mt-1 uppercase tracking-widest">
                        Gestión Unificada de Silos
                    </p>
                </div>
                <Link :href="route('admin.users.create')" 
                      class="hidden md:flex items-center gap-2 px-4 py-2 bg-primary text-primary-foreground rounded-xl text-xs font-bold shadow-lg shadow-primary/20 hover:scale-105 active:scale-95 transition-all">
                    <UserPlus :size="16" /> <span>Nuevo Usuario</span>
                </Link>
            </div>
        </template>

        <div class="space-y-5 pb-32 md:pb-12 relative">
            
            <div class="sticky top-0 z-30 -mx-4 px-4 py-2 bg-background/80 backdrop-blur-xl border-b border-border/40 transition-all">
                <div class="flex gap-2">
                    <div class="relative flex-1 group">
                        <Search class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within:text-primary transition-colors" :size="16" />
                        <input 
                            v-model="params.search" 
                            type="text" 
                            placeholder="Buscar en todos los silos (Nombre, Email, Tel...)" 
                            class="w-full pl-9 pr-4 py-2.5 bg-card/50 border border-border rounded-xl text-sm focus:ring-2 focus:ring-primary/40 focus:border-primary transition-all"
                        >
                    </div>
                    <button @click="showMobileFilters = true" 
                            class="flex items-center justify-center w-10 h-10 bg-card border border-border rounded-xl text-muted-foreground hover:text-primary hover:border-primary transition-all relative">
                        <SlidersHorizontal :size="18" />
                        <span v-if="params.role_id || params.branch_id" class="absolute top-2 right-2 w-2 h-2 bg-primary rounded-full animate-pulse"></span>
                    </button>
                </div>
            </div>

            <div v-if="users.data.length > 0" class="space-y-10 animate-in fade-in slide-in-from-bottom-2 duration-500">
                
                <div v-for="(groupUsers, groupName) in groupedUsers" :key="groupName" class="space-y-4">
                    
                    <div class="flex items-center gap-3 px-1">
                        <div class="p-1.5 rounded-lg bg-muted text-muted-foreground">
                            <Building2 v-if="groupName !== 'Sin Sucursal Asignada' && groupName !== 'Logística / Delivery'" :size="14" />
                            <Truck v-else-if="groupName === 'Logística / Delivery'" :size="14" />
                            <MapPin v-else :size="14" />
                        </div>
                        <h3 class="text-[11px] font-black text-foreground uppercase tracking-[0.2em]">{{ groupName }}</h3>
                        <div class="h-px bg-gradient-to-r from-border to-transparent flex-1"></div>
                        <span class="text-[10px] font-black text-muted-foreground bg-muted/50 px-2 py-0.5 rounded-full border border-border">
                            {{ groupUsers.length }}
                        </span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div v-for="user in groupUsers" :key="user.id" 
                             class="group relative bg-card rounded-2xl p-4 border border-border shadow-sm hover:shadow-md hover:border-primary/30 transition-all flex items-center gap-4">
                            
                            <div class="relative shrink-0">
                                <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-white font-black text-xl shadow-lg transition-transform group-hover:rotate-3"
                                     :class="getRoleStyle(user.role_key).bg">
                                    {{ (user.name || '?').charAt(0).toUpperCase() }}
                                </div>
                                <div class="absolute -bottom-1 -right-1 bg-background rounded-full p-1.5 ring-2 ring-card shadow-sm"
                                     :class="getRoleStyle(user.role_key).text">
                                    <Shield v-if="user.type === 'admin'" :size="12" fill="currentColor" />
                                    <Truck v-else-if="user.type === 'driver'" :size="12" />
                                    <Users v-else :size="12" />
                                </div>
                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="flex justify-between items-start mb-0.5">
                                    <h4 class="font-bold text-foreground text-sm truncate pr-4 group-hover:text-primary transition-colors">
                                        {{ user.name }}
                                    </h4>
                                    
                                    <div class="relative">
                                        <button @click.stop="toggleMenu(user.id)" class="p-1.5 rounded-full text-muted-foreground hover:bg-muted hover:text-foreground transition-colors">
                                            <MoreVertical :size="16" />
                                        </button>
                                        
                                        <div v-if="activeMenuUserId === user.id" 
                                             class="absolute right-0 top-8 z-50 w-36 bg-popover/95 backdrop-blur-xl border border-border rounded-xl shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 origin-top-right">
                                            <Link :href="route('admin.users.edit', user.id)" 
                                                  class="flex items-center gap-2 w-full px-4 py-2.5 text-xs font-bold text-foreground hover:bg-primary/10 hover:text-primary transition-colors">
                                                <Edit :size="13" /> Editar
                                            </Link>
                                            <div class="h-px bg-border/50 mx-2"></div>
                                            <button @click="deleteUser(user)" 
                                                    class="flex items-center gap-2 w-full px-4 py-2.5 text-xs font-bold text-destructive hover:bg-destructive/10 transition-colors">
                                                <Trash2 :size="13" /> Eliminar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <p class="text-[10px] text-muted-foreground truncate mb-3">{{ user.email }}</p>
                                
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[9px] font-black uppercase tracking-wider border shadow-sm"
                                          :class="[getRoleStyle(user.role_key).text, getRoleStyle(user.role_key).border, 'bg-card']">
                                        {{ user.role_label }}
                                    </span>
                                    
                                    <span v-if="user.type === 'customer'" class="text-[8px] font-bold text-emerald-600/40 uppercase italic">
                                        Silo Cliente
                                    </span>
                                </div>
                            </div>

                            <div v-if="user.phone" class="shrink-0 pl-3 border-l border-border/50">
                                <a :href="getWhatsappLink(user.phone)" target="_blank"
                                   class="w-9 h-9 rounded-full bg-emerald-500/10 text-emerald-600 border border-emerald-500/20 flex items-center justify-center transition-all hover:bg-emerald-500 hover:text-white shadow-sm active:scale-90"
                                   title="Contactar por WhatsApp">
                                    <MessageCircle :size="18" stroke-width="2.5" />
                                </a>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <div v-else class="flex flex-col items-center justify-center py-24 text-center opacity-40">
                <div class="w-20 h-20 bg-muted rounded-full flex items-center justify-center mb-4">
                    <Search :size="40" />
                </div>
                <h3 class="text-lg font-bold text-foreground">Sin registros encontrados</h3>
                <p class="text-xs text-muted-foreground max-w-[200px] mx-auto mt-1">
                    No hay usuarios que coincidan con los filtros aplicados en los silos.
                </p>
                <button @click="clearFilters" class="mt-6 text-xs font-bold text-primary hover:underline">
                    Limpiar todos los filtros
                </button>
            </div>
        </div>

        <Link :href="route('admin.users.create')" 
              class="md:hidden fixed bottom-[100px] right-6 z-40 w-14 h-14 bg-primary text-primary-foreground rounded-2xl shadow-2xl flex items-center justify-center active:scale-90 transition-transform border border-white/20">
            <UserPlus :size="28" stroke-width="2.5" />
        </Link>

        <div v-if="showMobileFilters" class="fixed inset-0 z-[100] md:hidden flex items-end">
            <div class="absolute inset-0 bg-background/80 backdrop-blur-md transition-opacity animate-in fade-in" @click="showMobileFilters = false"></div>
            
            <div class="relative w-full bg-card rounded-t-[40px] shadow-2xl border-t border-border p-8 pb-12 max-h-[90vh] overflow-y-auto animate-in slide-in-from-bottom-full duration-500">
                <div class="w-16 h-1.5 rounded-full bg-muted mx-auto mb-8"></div>
                
                <div class="flex items-center justify-between mb-8">
                    <h2 class="text-xl font-black text-foreground uppercase tracking-tighter">Filtrar Equipo</h2>
                    <button @click="clearFilters" class="text-[10px] font-black text-primary bg-primary/10 px-3 py-1.5 rounded-full uppercase">
                        Reiniciar
                    </button>
                </div>

                <UserFilters v-model="params" :roles="roles" :branches="branches" layout="vertical" />

                <button @click="showMobileFilters = false" 
                        class="w-full bg-primary text-primary-foreground py-4 rounded-2xl font-black text-sm uppercase tracking-widest mt-10 shadow-xl shadow-primary/20 active:scale-95 transition-transform">
                    Ver {{ users.total }} Resultados
                </button>
            </div>
        </div>

        <div v-if="activeMenuUserId" @click="closeMenu" class="fixed inset-0 z-40 bg-transparent"></div>

    </AdminLayout>
</template>

<style scoped>
.font-display { font-family: 'Space Grotesk', sans-serif; }
.shadow-xs { shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); }
</style>