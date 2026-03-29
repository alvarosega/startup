<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, router, Head } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import { debounce } from 'lodash';
import { 
    UserPlus, Search, MessageCircle, SlidersHorizontal,
    Cpu, Terminal, MoreVertical, Edit, Trash2, Radar, 
    MapPin, Mail, Phone, Lock, CheckCircle2, X 
} from 'lucide-vue-next';

const props = defineProps({
    users: Object, // Data paginada
    branches: Array, // Lista de sucursales para el filtro
    filters: Object
});

const params = ref({
    search: props.filters?.search || '',
    branch_id: props.filters?.branch_id || '',
});

const showMobileFilters = ref(false);
const activeMenuUserId = ref(null);

// Agrupación reactiva por sucursal
const groupedUsers = computed(() => {
    const list = props.users?.data || [];
    return list.reduce((acc, user) => {
        const groupName = user.branch ? user.branch.toUpperCase().replace(/\s+/g, '_') : 'ZONA_DESCONOCIDA';
        if (!acc[groupName]) acc[groupName] = [];
        acc[groupName].push(user);
        return acc;
    }, {});
});

const getWhatsappLink = (phone) => {
    if (!phone) return '#';
    return `https://wa.me/${phone.replace(/\D/g, '')}`; 
};

const toggleMenu = (userId) => {
    activeMenuUserId.value = activeMenuUserId.value === userId ? null : userId;
};

const deleteUser = (user) => {
    if (confirm(`⚠️ ALERTA: ¿ELIMINAR CLIENTE? // ${user.name}`)) {
        router.delete(route('admin.users.destroy', user.id), { preserveScroll: true });
    }
};

// Motor de búsqueda sincronizado con la URL
watch(params, debounce((val) => {
    router.get(route('admin.users.index'), val, { 
        preserveState: true, replace: true, preserveScroll: true 
    });
}, 300), { deep: true });

const clearFilters = () => {
    params.value = { search: '', branch_id: '' };
};
</script>

<template>
    <Head title="Silo de Clientes" />
    <AdminLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-black tracking-tight text-zinc-900 uppercase italic">Silo de Clientes</h1>
                    <p class="text-[10px] text-primary font-mono tracking-[0.3em] flex items-center gap-2">
                        <Cpu :size="12" /> RED_LOGÍSTICA_ACTIVA
                    </p>
                </div>
                <Link :href="route('admin.users.create')" 
                      class="flex items-center gap-2 px-5 py-2.5 bg-zinc-900 text-white font-mono text-xs font-bold rounded-xl hover:bg-zinc-800 transition-all shadow-lg active:scale-95">
                    <UserPlus :size="16" /> NUEVO_CLIENTE
                </Link>
            </div>
        </template>

        <div class="space-y-6">
            <div class="bg-white border border-zinc-200 rounded-2xl p-2 flex flex-wrap gap-2 shadow-sm">
                <div class="relative flex-1 min-w-[200px]">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 text-zinc-400" :size="16" />
                    <input v-model="params.search" type="text" placeholder="BUSCAR POR NOMBRE, EMAIL, TEL..." 
                           class="w-full pl-10 pr-4 py-2 bg-zinc-50 border-transparent rounded-xl text-sm font-mono focus:ring-zinc-900 focus:bg-white transition-all">
                </div>
                
                <select v-model="params.branch_id" 
                        class="hidden md:block bg-zinc-50 border-transparent rounded-xl text-xs font-bold uppercase px-4 py-2 focus:ring-zinc-900 min-w-[180px]">
                    <option value="">TODAS_LAS_SUCURSALES</option>
                    <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }}</option>
                </select>

                <button @click="showMobileFilters = true" class="md:hidden p-2 bg-zinc-100 rounded-xl text-zinc-600">
                    <SlidersHorizontal :size="18" />
                </button>
            </div>

            <div v-if="Object.keys(groupedUsers).length > 0" class="space-y-10">
                <div v-for="(groupUsers, groupName) in groupedUsers" :key="groupName" class="space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="p-1.5 bg-zinc-900 text-white rounded-lg"><Radar :size="14" /></div>
                        <h3 class="text-[11px] font-mono font-black text-zinc-900 tracking-[0.2em] uppercase">[{{ groupName }}]</h3>
                        <div class="h-px bg-zinc-200 flex-1"></div>
                        <span class="text-[10px] font-mono text-zinc-500 bg-zinc-100 px-2 py-0.5 rounded-md border border-zinc-200">
                            {{ String(groupUsers.length).padStart(2, '0') }}_UNIDADES
                        </span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div v-for="user in groupUsers" :key="user.id" 
                             class="bg-white border border-zinc-200 rounded-3xl p-5 flex items-center gap-4 transition-all hover:border-zinc-900 hover:shadow-md relative group">
                            
                            <div class="w-12 h-12 shrink-0 rounded-2xl overflow-hidden border-2 border-zinc-100 bg-zinc-50">
                                <img :src="user.avatar.type === 'image' ? user.avatar.source : `/assets/avatars/${user.avatar.source}`" 
                                     class="w-full h-full object-cover grayscale group-hover:grayscale-0 transition-all">
                            </div>

                            <div class="flex-1 min-w-0">
                                <h4 class="font-bold text-zinc-900 text-sm truncate uppercase tracking-tight">{{ user.name }}</h4>
                                <div class="flex flex-col gap-0.5 mt-1">
                                    <span class="text-[10px] text-zinc-500 font-mono truncate">{{ user.email }}</span>
                                    <span v-if="user.phone" class="text-[10px] text-zinc-900 font-bold font-mono">{{ user.phone }}</span>
                                </div>
                                <div class="mt-2 flex items-center gap-2">
                                    <span class="text-[8px] font-black px-1.5 py-0.5 rounded bg-zinc-100 border border-zinc-200 uppercase tracking-tighter"
                                          :class="user.is_active ? 'text-emerald-600' : 'text-red-500 animate-pulse'">
                                        {{ user.is_active ? 'ONLINE' : 'LOCKED' }}
                                    </span>
                                    <span class="text-[8px] text-zinc-400 font-mono">ID: {{ user.id.split('-')[0] }}</span>
                                </div>
                            </div>

                            <div class="flex flex-col gap-2 border-l border-zinc-100 pl-3">
                                <Link :href="route('admin.users.edit', user.id)" class="p-2 text-zinc-400 hover:text-zinc-900 transition-colors">
                                    <Edit :size="16" />
                                </Link>
                                <button v-if="user.phone" @click="window.open(getWhatsappLink(user.phone))" class="p-2 text-emerald-500 hover:scale-110 transition-transform">
                                    <MessageCircle :size="16" />
                                </button>
                                <button @click="deleteUser(user)" class="p-2 text-zinc-300 hover:text-red-600 transition-colors">
                                    <Trash2 :size="16" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-else class="text-center py-24 bg-zinc-50 rounded-[3rem] border border-dashed border-zinc-200">
                <Search :size="48" class="mx-auto text-zinc-200 mb-4" />
                <h3 class="text-zinc-500 font-mono font-bold uppercase">Sin registros encontrados</h3>
                <button @click="clearFilters" class="mt-4 text-xs font-black text-primary hover:underline uppercase">Reiniciar Protocolo</button>
            </div>
        </div>

        <div v-if="showMobileFilters" class="fixed inset-0 z-[100] flex justify-end">
            <div class="absolute inset-0 bg-zinc-900/40 backdrop-blur-sm" @click="showMobileFilters = false"></div>
            <div class="relative w-80 bg-white h-full p-8 shadow-2xl animate-in slide-in-from-right duration-300">
                <div class="flex justify-between items-center mb-10">
                    <h2 class="font-black uppercase tracking-tighter text-lg italic">Filtros Tácticos</h2>
                    <button @click="showMobileFilters = false" class="p-2 bg-zinc-100 rounded-full"><X :size="20"/></button>
                </div>
                
                <div class="space-y-6">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase text-zinc-400 tracking-widest ml-1">Sucursal Base</label>
                        <select v-model="params.branch_id" class="w-full bg-zinc-50 border-zinc-200 rounded-2xl p-4 text-sm font-bold uppercase">
                            <option value="">TODAS_LAS_AREAS</option>
                            <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }}</option>
                        </select>
                    </div>
                    <button @click="showMobileFilters = false" class="w-full bg-zinc-900 text-white py-4 rounded-2xl font-black uppercase text-xs tracking-widest shadow-xl">Ver Unidades</button>
                    <button @click="clearFilters" class="w-full text-zinc-400 font-bold uppercase text-[10px] tracking-widest">Limpiar Todo</button>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>