<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, router, Head } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import { debounce } from 'lodash';

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
            <div class="flex items-center justify-between w-full select-none">
                <div>
                    <h1 class="text-xl md:text-2xl font-black tracking-tight text-foreground uppercase italic">Clientes</h1>
                </div>
                <Link :href="route('admin.users.create')" 
                      class="admin-btn-primary inline-flex items-center gap-2 text-xs font-bold uppercase tracking-wider">
                    <span class="material-symbols-rounded text-sm">person_add</span>
                    <span>NUEVO_CLIENTE</span>
                </Link>
            </div>
        </template>

        <div class="space-y-6">
            <div class="bg-card border border-border rounded-md p-2 flex flex-wrap gap-2 shadow-flat select-none">
                <div class="relative flex-1 min-w-[240px]">
                    <span class="material-symbols-rounded text-muted-foreground absolute left-3 top-1/2 -translate-y-1/2 text-lg">search</span>
                    <input v-model="params.search" type="text" placeholder="BUSCAR POR NOMBRE, EMAIL, TEL..." 
                           class="admin-input pl-9 font-mono uppercase text-xs">
                </div>
                
                <select v-model="params.branch_id" 
                        class="admin-input hidden md:block text-xs font-bold uppercase py-1.5 px-3 min-w-[200px] w-auto">
                    <option value="">TODAS_LAS_SUCURSALES</option>
                    <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }}</option>
                </select>

                <button @click="showMobileFilters = true" class="md:hidden p-2 border border-border bg-card hover:bg-neutral-100 dark:hover:bg-neutral-800 rounded-md text-foreground transition-colors duration-75 flex items-center justify-center">
                    <span class="material-symbols-rounded text-xl">sliders_horizontal</span>
                </button>
            </div>

            <div v-if="Object.keys(groupedUsers).length > 0" class="space-y-8">
                <div v-for="(groupUsers, groupName) in groupedUsers" :key="groupName" class="space-y-3">
                    
                    <div class="flex items-center gap-2.5 select-none">
                        <span class="material-symbols-rounded text-primary text-lg">radar</span>
                        <h3 class="text-xs font-mono font-black text-foreground tracking-wider uppercase">[{{ groupName }}]</h3>
                        <div class="h-px bg-border/60 flex-1"></div>
                        <span class="badge-info text-[10px] font-mono font-semibold px-2 py-0.5">
                            {{ String(groupUsers.length).padStart(2, '0') }}_UNIDADES
                        </span>
                    </div>

                    <div class="w-full overflow-x-auto border border-border rounded-md bg-card shadow-flat">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th class="admin-table-th w-14 text-center select-none">AVATAR</th>
                                    <th class="admin-table-th min-w-[200px]">IDENTIFICADOR / OPERADOR</th>
                                    <th class="admin-table-th min-w-[220px]">CANAL DE COMUNICACIÓN</th>
                                    <th class="admin-table-th w-32 text-center select-none">ESTADO</th>
                                    <th class="admin-table-th w-28 text-center select-none">ACCIONES</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="user in groupUsers" :key="user.id" class="admin-table-tr">
                                    <td class="admin-table-td text-center">
                                        <div class="w-8 h-8 rounded-md overflow-hidden border border-border bg-neutral-100 dark:bg-neutral-800 mx-auto select-none">
                                            <img :src="user.avatar.type === 'image' ? user.avatar.source : `/assets/avatars/${user.avatar.source}`" 
                                                 class="w-full h-full object-cover">
                                        </div>
                                    </td>
                                    
                                    <td class="admin-table-td">
                                        <div class="font-bold text-foreground truncate uppercase tracking-tight">{{ user.name }}</div>
                                        <div class="text-[10px] text-muted-foreground font-mono mt-0.5">UUID: {{ user.id }}</div>
                                    </td>
                                    
                                    <td class="admin-table-td">
                                        <div class="text-xs font-mono text-foreground select-all">{{ user.email }}</div>
                                        <div v-if="user.phone" class="text-[11px] text-muted-foreground font-mono mt-0.5 select-all">{{ user.phone }}</div>
                                    </td>
                                    
                                    <td class="admin-table-td text-center select-none">
                                        <span :class="user.is_active ? 'badge-success' : 'badge-error'">
                                            {{ user.is_active ? 'ONLINE' : 'LOCKED' }}
                                        </span>
                                    </td>
                                    
                                    <td class="admin-table-td text-center select-none">
                                        <div class="flex items-center justify-center gap-1">
                                            <Link :href="route('admin.users.edit', user.id)" 
                                                  class="p-1.5 text-neutral-400 hover:text-foreground rounded-md hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors duration-75 flex items-center justify-center">
                                                <span class="material-symbols-rounded text-lg">edit</span>
                                            </Link>
                                            
                                            <a v-if="user.phone" :href="getWhatsappLink(user.phone)" target="_blank" 
                                               class="p-1.5 text-success hover:bg-success/10 rounded-md transition-colors duration-75 flex items-center justify-center">
                                                <span class="material-symbols-rounded text-lg">chat</span>
                                            </a>
                                            
                                            <button @click="deleteUser(user)" 
                                                    class="p-1.5 text-neutral-300 hover:text-destructive rounded-md hover:bg-destructive/10 transition-colors duration-75 flex items-center justify-center">
                                                <span class="material-symbols-rounded text-lg">delete</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>

            <div v-else class="text-center py-20 bg-card rounded-md border border-dashed border-border select-none">
                <span class="material-symbols-rounded text-4xl text-neutral-300 block mb-2">search_off</span>
                <h3 class="text-muted-foreground font-mono text-xs font-bold uppercase tracking-wider">Sin registros encontrados en este nodo</h3>
                <button @click="clearFilters" class="mt-3 text-xs font-black text-primary hover:opacity-80 uppercase tracking-wider font-mono">Reiniciar Protocolo</button>
            </div>
        </div>

        <div v-if="showMobileFilters" class="fixed inset-0 z-[100] flex justify-end mobile-filter-layer select-none">
            <div class="absolute inset-0 bg-neutral-950/40" @click="showMobileFilters = false"></div>
            <div class="relative w-80 bg-card border-l border-border h-full p-6 flex flex-col justify-between shadow-flat">
                <div class="space-y-6">
                    <div class="flex justify-between items-center pb-3 border-b border-border">
                        <h2 class="font-bold uppercase tracking-wide text-sm italic">Filtros Tácticos</h2>
                        <button @click="showMobileFilters = false" class="p-1 rounded-md hover:bg-neutral-100 dark:hover:bg-neutral-800 text-foreground flex items-center justify-center border border-border">
                            <span class="material-symbols-rounded text-lg">close</span>
                        </button>
                    </div>
                    
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold uppercase text-muted-foreground tracking-widest ml-1">Sucursal Base</label>
                        <select v-model="params.branch_id" class="admin-input text-xs font-bold uppercase py-2">
                            <option value="">TODAS_LAS_AREAS</option>
                            <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }}</option>
                        </select>
                    </div>
                </div>

                <div class="space-y-2">
                    <button @click="showMobileFilters = false" class="admin-btn-primary w-full py-2.5 font-bold uppercase text-xs tracking-wider">Ver Unidades</button>
                    <button @click="clearFilters" class="w-full text-muted-foreground hover:text-foreground font-mono font-bold uppercase text-[10px] tracking-wider py-2">Limpiar Todo</button>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>