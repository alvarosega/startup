<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import UserFilters from '@/Components/Admin/Users/UserFilters.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import { debounce } from 'lodash';
import { 
    UserPlus, Search, Users, Shield, Building2, 
    Briefcase, Truck, Filter, X, SlidersHorizontal, 
    MessageCircle, MoreVertical, Phone, Edit, Trash2 
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

// --- ESTILOS SEMÁNTICOS POR ROL ---
const getRoleStyle = (roleKey) => {
    const key = roleKey || 'unknown';
    switch (key) {
        case 'super_admin': return { bg: 'bg-violet-600', text: 'text-violet-600', border: 'border-violet-200', label: 'Global Admin' };
        case 'branch_admin': return { bg: 'bg-blue-600', text: 'text-blue-600', border: 'border-blue-200', label: 'Gerente Sucursal' };
        case 'driver': return { bg: 'bg-amber-500', text: 'text-amber-600', border: 'border-amber-200', label: 'Conductor' };
        default: return { bg: 'bg-slate-600', text: 'text-slate-600', border: 'border-slate-200', label: 'Operativo' };
    }
};

// --- HELPER WHATSAPP ---
const getWhatsappLink = (phone) => {
    if (!phone) return '#';
    const cleanPhone = phone.replace(/\D/g, '');
    return `https://wa.me/${cleanPhone}`; 
};

// --- AGRUPACIÓN INTELIGENTE ---
const groupedUsers = computed(() => {
    if (!props.users.data) return {};
    return props.users.data.reduce((acc, user) => {
        let groupName = user.role_key === 'super_admin' ? 'Administración Global' : (user.branch || 'Sin Asignar');
        if (!acc[groupName]) acc[groupName] = [];
        acc[groupName].push(user);
        return acc;
    }, {});
});

// --- ACCIONES DE MENÚ ---
const toggleMenu = (userId) => {
    activeMenuUserId.value = activeMenuUserId.value === userId ? null : userId;
};

const closeMenu = () => {
    activeMenuUserId.value = null;
};

const deleteUser = (user) => {
    if (confirm(`¿Estás seguro de eliminar a ${user.name}?`)) {
        router.delete(route('admin.users.destroy', user.id), {
            preserveScroll: true,
            onSuccess: () => closeMenu()
        });
    }
};

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
                        Equipo
                    </h1>
                    <p class="text-[10px] text-muted-foreground font-medium mt-0.5">
                        {{ users.total }} miembros activos
                    </p>
                </div>
                <Link :href="route('admin.users.create')" class="hidden md:flex btn btn-primary btn-sm gap-2 shadow-lg shadow-primary/20">
                    <UserPlus :size="16" /> <span>Nuevo Usuario</span>
                </Link>
            </div>
        </template>

        <div class="space-y-5 pb-32 md:pb-12 relative">
            
            <div class="sticky top-0 z-30 -mx-4 px-4 py-2 bg-background/80 backdrop-blur-xl border-b border-border/40 transition-all">
                <div class="flex gap-2">
                    <div class="relative flex-1 group">
                        <Search class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground transition-colors group-focus-within:text-primary" :size="16" />
                        <input 
                            v-model="params.search" 
                            type="text" 
                            placeholder="Buscar por nombre, cargo..." 
                            class="w-full pl-9 pr-4 py-2.5 bg-card/50 border border-border rounded-xl text-sm focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all shadow-sm placeholder:text-muted-foreground/50"
                        >
                    </div>
                    <button @click="showMobileFilters = true" 
                            class="flex items-center justify-center w-10 h-10 bg-card border border-border rounded-xl text-muted-foreground hover:text-primary hover:border-primary active:scale-95 transition-all shadow-sm relative">
                        <SlidersHorizontal :size="18" />
                        <span v-if="params.role_id || params.branch_id" class="absolute top-2 right-2 w-2 h-2 bg-primary rounded-full animate-pulse"></span>
                    </button>
                </div>
            </div>

            <div v-if="users.data.length > 0" class="space-y-6 animate-in fade-in slide-in-from-bottom-2 duration-500">
                
                <div v-for="(groupUsers, groupName) in groupedUsers" :key="groupName">
                    
                    <div class="flex items-center gap-2 mb-3 px-1 opacity-80">
                        <Building2 :size="14" class="text-muted-foreground" />
                        <h3 class="text-[10px] font-black text-muted-foreground uppercase tracking-widest">{{ groupName }}</h3>
                        <div class="h-px bg-border flex-1"></div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                        <div v-for="user in groupUsers" :key="user.id" 
                             class="group relative bg-card rounded-2xl p-3 border border-border shadow-sm hover:shadow-md hover:border-primary/30 transition-all flex items-center gap-3">
                            
                            <div class="relative shrink-0">
                                <div class="w-12 h-12 rounded-xl flex items-center justify-center text-white font-black text-lg shadow-md"
                                     :class="getRoleStyle(user.role_key).bg">
                                    {{ (user.name || '?').charAt(0).toUpperCase() }}
                                </div>
                                <div class="absolute -bottom-1 -right-1 bg-card rounded-full p-0.5 ring-2 ring-card text-[10px]" :class="getRoleStyle(user.role_key).text">
                                    <Shield v-if="user.role_key ==='super_admin'" :size="12" fill="currentColor" class="opacity-20"/>
                                    <Shield v-else :size="12" />
                                </div>
                            </div>

                            <div class="flex-1 min-w-0 relative">
                                <div class="flex justify-between items-start">
                                    <h4 class="font-bold text-foreground text-sm truncate leading-tight pr-6">
                                        {{ user.name || 'Sin Nombre' }}
                                    </h4>
                                    
                                    <div class="absolute -right-2 -top-2">
                                        <button @click.stop="toggleMenu(user.id)" 
                                                class="p-1.5 rounded-full text-muted-foreground hover:bg-muted hover:text-foreground transition-colors relative z-10">
                                            <MoreVertical :size="16" />
                                        </button>

                                        <div v-if="activeMenuUserId === user.id" 
                                             class="absolute right-0 top-8 z-50 w-32 bg-popover/95 backdrop-blur-xl border border-border rounded-xl shadow-xl overflow-hidden animate-in fade-in zoom-in-95 origin-top-right ring-1 ring-white/10">
                                            <Link :href="route('admin.users.edit', user.id)" 
                                                  class="flex items-center gap-2 w-full px-4 py-2.5 text-xs font-bold text-foreground hover:bg-primary/10 hover:text-primary transition-colors">
                                                <Edit :size="13" /> Editar
                                            </Link>
                                            <div class="h-px bg-border/50 mx-2"></div>
                                            <button @click="deleteUser(user)" 
                                                    class="flex items-center gap-2 w-full px-4 py-2.5 text-xs font-bold text-destructive hover:bg-destructive/10 transition-colors text-left">
                                                <Trash2 :size="13" /> Eliminar
                                            </button>
                                        </div>
                                        
                                        <div v-if="activeMenuUserId === user.id" @click="closeMenu" class="fixed inset-0 z-0 cursor-default"></div>
                                    </div>
                                </div>
                                
                                <p class="text-[10px] text-muted-foreground font-medium truncate mt-0.5">{{ user.email }}</p>
                                
                                <div class="flex items-center gap-2 mt-2">
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-black uppercase tracking-wider bg-muted/50 text-muted-foreground border border-border">
                                        {{ getRoleStyle(user.role_key).label }}
                                    </span>
                                </div>
                            </div>

                            <div v-if="user.phone" class="shrink-0 pl-2 border-l border-border/50">
                                <a :href="getWhatsappLink(user.phone)" target="_blank"
                                   class="w-9 h-9 rounded-full bg-emerald-500/10 text-emerald-600 border border-emerald-500/20 flex items-center justify-center transition-all active:scale-90 hover:bg-emerald-500 hover:text-white shadow-sm"
                                   title="Chat en WhatsApp">
                                    <MessageCircle :size="18" stroke-width="2.5" />
                                </a>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <div v-else class="flex flex-col items-center justify-center py-20 text-center opacity-60">
                <Search :size="48" class="text-muted-foreground/30 mb-4" />
                <h3 class="text-lg font-bold text-foreground">Sin resultados</h3>
                <p class="text-xs text-muted-foreground">Intenta ajustar los filtros.</p>
                <button @click="clearFilters" class="mt-4 text-xs font-bold text-primary hover:underline">
                    Limpiar todo
                </button>
            </div>
        </div>

        <Link :href="route('admin.users.create')" 
              class="md:hidden fixed bottom-[100px] right-4 z-40 w-14 h-14 bg-primary text-primary-foreground rounded-2xl shadow-xl shadow-primary/30 flex items-center justify-center hover:scale-110 active:scale-95 transition-all duration-300 border border-white/20 ring-1 ring-black/5">
            <UserPlus :size="26" stroke-width="2.5" />
        </Link>

        <div v-if="showMobileFilters" class="fixed inset-0 z-[100] md:hidden flex items-end">
            <div class="absolute inset-0 bg-background/80 backdrop-blur-md transition-opacity" @click="showMobileFilters = false"></div>
            
            <div class="relative w-full bg-card rounded-t-[32px] shadow-2xl border-t border-border flex flex-col max-h-[85vh] animate-in slide-in-from-bottom-full duration-300">
                <div class="w-12 h-1.5 rounded-full bg-muted mx-auto mt-3 mb-5 shrink-0"></div>

                <div class="px-6 pb-4 flex items-center justify-between border-b border-border/50">
                    <h2 class="text-lg font-black text-foreground">Filtros Avanzados</h2>
                    <button @click="clearFilters" class="text-xs font-bold text-primary bg-primary/10 px-3 py-1.5 rounded-lg active:scale-95 transition-transform">
                        Limpiar
                    </button>
                </div>

                <div class="p-6 overflow-y-auto">
                    <UserFilters v-model="params" :roles="roles" :branches="branches" layout="vertical" />
                </div>

                <div class="p-6 border-t border-border bg-muted/10 pb-safe">
                    <button @click="showMobileFilters = false" class="w-full btn btn-primary py-3.5 rounded-xl shadow-lg font-bold text-sm">
                        Ver {{ users.total }} Resultados
                    </button>
                </div>
            </div>
        </div>

    </AdminLayout>
</template>

<style scoped>
.pb-safe {
    padding-bottom: env(safe-area-inset-bottom, 20px);
}
</style>