<script setup>
import { ref, watch } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import axios from 'axios';

const props = defineProps({
    users: Object,
    pagination: Object,
    branches: Array,
    filters: Object
});

// Estados de Filtros reactivos
const search = ref(props.filters.search || '');
const branchId = ref(props.filters.branch_id || '');
const isActive = ref(props.filters.is_active || '');

// Control de carga para Toggles individuales
const loadingStates = ref({});

// Estados de los Drawers
const isRecoveryOpen = ref(false);
const searchPhone = ref('');
const recoveryUser = ref(null);
const recoveryError = ref('');
const isSearching = ref(false);

// Ejecución de filtros
const applyFilters = () => {
    router.get(route('admin.users.customers.index'), {
        search: search.value,
        branch_id: branchId.value,
        is_active: isActive.value
    }, { preserveState: true, replace: true });
};

watch([branchId, isActive], () => applyFilters());

// Mutación atómica de activación/desactivación
const toggleStatus = (id, currentStatus) => {
    loadingStates.value[id] = true;
    router.patch(route('admin.users.customers.change-status', id), {
        is_active: !currentStatus
    }, {
        preserveScroll: true,
        onFinish: () => loadingStates.value[id] = false
    });
};

// Lógica asíncrona de búsqueda en el Drawer de recuperación
const searchDeletedUser = async () => {
    if (!searchPhone.value) return;
    isSearching.value = true;
    recoveryError.value = '';
    recoveryUser.value = null;

    try {
        const response = await axios.post(route('admin.users.customers.search-deleted'), {
            phone: searchPhone.value
        });
        recoveryUser.value = response.data.user;
    } catch (error) {
        recoveryError.value = error.response?.data?.message || 'Error en la búsqueda.';
    } finally {
        isSearching.value = false;
    }
};

const executeRestore = (id) => {
    router.post(route('admin.users.customers.restore', id), {}, {
        onSuccess: () => {
            isRecoveryOpen.value = false;
            searchPhone.value = '';
            recoveryUser.value = null;
        }
    });
};
</script>

<template>
    <AdminLayout>
        <div class="p-6 max-w-7xl mx-auto space-y-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-b border-border pb-5">
                <div>
                    <h1 class="text-xl font-bold tracking-tight text-foreground">Gestión de Clientes</h1>
                    <p class="text-xs text-muted-foreground mt-0.5">Silo administrativo y control de estados.</p>
                </div>
                <div class="flex gap-2 w-full sm:w-auto">
                    <button @click="isRecoveryOpen = true" class="px-3 py-2 bg-neutral-100 dark:bg-neutral-800 border border-border rounded-md text-xs font-semibold hover:bg-neutral-200 text-foreground transition-colors">
                        Recuperar Eliminado
                    </button>
                    <Link :href="route('admin.users.customers.create')" class="px-3 py-2 bg-primary text-white rounded-md text-xs font-semibold hover:bg-primary/90 transition-colors">
                        Crear Cliente
                    </Link>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 bg-card p-4 rounded-md border border-border shadow-sm">
                <input v-model="search" @keyup.enter="applyFilters" type="text" placeholder="Buscar por nombre, correo o teléfono..." class="w-full bg-background border border-border rounded-md px-3 py-1.5 text-xs focus:outline-none focus:border-primary" />
                <select v-model="branchId" class="w-full bg-background border border-border rounded-md px-3 py-1.5 text-xs focus:outline-none focus:border-primary">
                    <option value="">Todas las sucursales</option>
                    <option v-for="branch in branches" :key="branch.id" :value="branch.id">{{ branch.name }}</option>
                </select>
                <select v-model="isActive" class="w-full bg-background border border-border rounded-md px-3 py-1.5 text-xs focus:outline-none focus:border-primary">
                    <option value="">Todos los estados</option>
                    <option value="true">Activos</option>
                    <option value="false">Inactivos</option>
                </select>
            </div>

            <div class="bg-card border border-border rounded-md overflow-hidden shadow-sm">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-neutral-50 dark:bg-neutral-900 border-b border-border text-[11px] font-bold uppercase tracking-wider text-muted-foreground">
                            <th class="p-3">Cliente</th>
                            <th class="p-3">Teléfono</th>
                            <th class="p-3">Sucursal</th>
                            <th class="p-3">Alertas</th>
                            <th class="p-3 text-center">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border text-xs text-foreground">
                        <tr v-for="item in users.data" :key="item.id" class="hover:bg-neutral-50/50 dark:hover:bg-neutral-900/30 transition-colors">
                            <td class="p-3 font-medium">
                                <div class="flex flex-col">
                                    <span>{{ item.profile?.first_name }} {{ item.profile?.last_name }}</span>
                                    <span class="text-[10px] text-muted-foreground">{{ item.email }}</span>
                                </div>
                            </td>
                            <td class="p-3 font-mono text-[11px]">{{ item.phone }}</td>
                            <td class="p-3 text-muted-foreground">{{ item.branch?.name || 'Global' }}</td>
                            <td class="p-3">
                                <span v-if="item.was_previously_deleted" class="px-2 py-0.5 bg-amber-100 dark:bg-amber-900/40 text-amber-800 dark:text-amber-300 rounded text-[10px] font-bold uppercase tracking-wide">
                                    Re-registrado
                                </span>
                            </td>
                            <td class="p-3">
                                <div class="flex justify-center items-center">
                                    <button @click="toggleStatus(item.id, item.is_active)" :disabled="loadingStates[item.id]" :class="[item.is_active ? 'bg-primary' : 'bg-neutral-300 dark:bg-neutral-700']" class="w-11 h-6 flex items-center rounded-full p-1 transition-colors duration-200 focus:outline-none relative">
                                        <div :class="[item.is_active ? 'translate-x-5' : 'translate-x-0']" class="bg-white w-4 h-4 rounded-full shadow-md transform transition-transform duration-200"></div>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="users.data.length === 0">
                            <td colspan="5" class="p-8 text-center text-muted-foreground">No se encontraron clientes bajo los filtros aplicados.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="isRecoveryOpen" @click="isRecoveryOpen = false" class="fixed inset-0 bg-neutral-950/40 z-50"></div>
        </Transition>
        <Transition enter-active-class="transition duration-300 ease-out" enter-from-class="translate-x-full" enter-to-class="translate-x-0" leave-active-class="transition duration-200 ease-in" leave-from-class="translate-x-0" leave-to-class="translate-x-full">
            <div v-if="isRecoveryOpen" class="fixed inset-y-0 right-0 max-w-md w-full bg-card border-l border-border shadow-2xl z-50 p-5 flex flex-col justify-between">
                <div>
                    <div class="flex justify-between items-center border-b border-border pb-3 mb-4">
                        <h3 class="text-sm font-bold uppercase tracking-wide text-foreground">Recuperar Historial Cliente</h3>
                        <button @click="isRecoveryOpen = false" class="text-muted-foreground hover:text-foreground">
                            <span class="material-symbols-rounded text-lg block">close</span>
                        </button>
                    </div>

                    <div class="space-y-4">
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground">Número de Teléfono</label>
                            <div class="flex gap-2">
                                <input v-model="searchPhone" type="text" placeholder="+591XXXXXXXX" class="w-full bg-background border border-border rounded-md px-3 py-2 text-xs focus:outline-none focus:border-primary" />
                                <button @click="searchDeletedUser" :disabled="isSearching" class="px-3 py-2 bg-primary text-white text-xs font-semibold rounded-md hover:bg-primary/90 disabled:opacity-50">
                                    {{ isSearching ? 'Buscando...' : 'Buscar' }}
                                </button>
                            </div>
                        </div>

                        <div v-if="recoveryError" class="p-3 bg-rose-50 border border-rose-200 rounded text-rose-700 text-xs font-medium">
                            {{ recoveryError }}
                        </div>

                        <div v-if="recoveryUser" class="bg-background border border-border rounded p-4 space-y-3">
                            <h4 class="text-xs font-bold text-foreground border-b border-border pb-1.5">Registro Encontrado en Papelera</h4>
                            <div class="grid grid-cols-2 gap-2 text-[11px]">
                                <span class="text-muted-foreground">Nombre completo:</span>
                                <span class="font-medium text-foreground text-right">{{ recoveryUser.profile?.first_name }} {{ recoveryUser.profile?.last_name }}</span>
                                <span class="text-muted-foreground">Correo electrónico:</span>
                                <span class="font-medium text-foreground text-right overflow-hidden text-ellipsis">{{ recoveryUser.email }}</span>
                            </div>
                            <div class="mt-2 border-t border-dashed border-border pt-2">
                                <button @click="executeRestore(recoveryUser.id)" class="w-full py-2 bg-amber-600 text-white rounded text-xs font-bold hover:bg-amber-700 transition-colors">
                                    Confirmar Restauración en Servidor
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </AdminLayout>
</template>