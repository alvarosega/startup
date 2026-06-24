<script setup>
import { ref, watch } from 'vue';
import { router, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import axios from 'axios';

const props = defineProps({
    users: Object,
    pagination: Object,
    branches: Array,
    filters: Object
});

const search = ref(props.filters.search || '');
const branchId = ref(props.filters.branch_id || '');
const status = ref(props.filters.status || '');

// Estados de Control para Drawers
const isRecoveryOpen = ref(false);
const isStatusOpen = ref(false);
const selectedDriver = ref(null);

const searchPhone = ref('');
const recoveryUser = ref(null);
const recoveryError = ref('');
const isSearching = ref(false);

const applyFilters = () => {
    router.get(route('admin.users.drivers.index'), {
        search: search.value,
        branch_id: branchId.value,
        status: status.value
    }, { preserveState: true, replace: true });
};

watch([branchId, status], () => applyFilters());

// Formulario de mutación de estado para el panel lateral derecho
const statusForm = useForm({
    status: '',
    rejection_reason: ''
});

const openStatusDrawer = (driver) => {
    selectedDriver.value = driver;
    statusForm.status = driver.status;
    statusForm.rejection_reason = driver.profile?.rejection_reason || '';
    isStatusOpen.value = true;
};

const updateDriverStatus = () => {
    statusForm.patch(route('admin.users.drivers.change-status', selectedDriver.value.id), {
        onSuccess: () => isStatusOpen.value = false
    });
};

const searchDeletedDriver = async () => {
    if (!searchPhone.value) return;
    isSearching.value = true;
    recoveryError.value = '';
    recoveryUser.value = null;

    try {
        const response = await axios.post(route('admin.users.drivers.search-deleted'), {
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
    router.post(route('admin.users.drivers.restore', id), {}, {
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
                    <h1 class="text-xl font-bold tracking-tight text-foreground">Gestión de Repartidores</h1>
                    <p class="text-xs text-muted-foreground mt-0.5">Control de estatus operativo del parque de conductores.</p>
                </div>
                <div class="flex gap-2 w-full sm:w-auto">
                    <button @click="isRecoveryOpen = true" class="px-3 py-2 bg-neutral-100 dark:bg-neutral-800 border border-border rounded-md text-xs font-semibold hover:bg-neutral-200 text-foreground transition-colors">
                        Recuperar Eliminado
                    </button>
                    <Link :href="route('admin.users.drivers.create')" class="px-3 py-2 bg-primary text-white rounded-md text-xs font-semibold hover:bg-primary/90 transition-colors">
                        Registrar Conductor
                    </Link>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 bg-card p-4 rounded-md border border-border shadow-sm">
                <input v-model="search" @keyup.enter="applyFilters" type="text" placeholder="Buscar por placa, licencia, nombre..." class="w-full bg-background border border-border rounded-md px-3 py-1.5 text-xs focus:outline-none focus:border-primary" />
                <select v-model="branchId" class="w-full bg-background border border-border rounded-md px-3 py-1.5 text-xs focus:outline-none focus:border-primary">
                    <option value="">Todas las sucursales</option>
                    <option v-for="branch in branches" :key="branch.id" :value="branch.id">{{ branch.name }}</option>
                </select>
                <select v-model="status" class="w-full bg-background border border-border rounded-md px-3 py-1.5 text-xs focus:outline-none focus:border-primary">
                    <option value="">Todos los estatus</option>
                    <option value="pending">Pendientes</option>
                    <option value="approved">Aprobados</option>
                    <option value="rejected">Rechazados</option>
                    <option value="suspended">Suspendidos</option>
                </select>
            </div>

            <div class="bg-card border border-border rounded-md overflow-hidden shadow-sm">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-neutral-50 dark:bg-neutral-900 border-b border-border text-[11px] font-bold uppercase tracking-wider text-muted-foreground">
                            <th class="p-3">Conductor</th>
                            <th class="p-3">Vehículo / Matrícula</th>
                            <th class="p-3">Sucursal</th>
                            <th class="p-3">Estatus</th>
                            <th class="p-3 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border text-xs text-foreground">
                        <tr v-for="item in users.data" :key="item.id" class="hover:bg-neutral-50/50 dark:hover:bg-neutral-900/30 transition-colors">
                            <td class="p-3 font-medium">
                                <div class="flex flex-col">
                                    <span>{{ item.profile?.first_name }} {{ item.profile?.last_name }}</span>
                                    <span class="text-[10px] text-muted-foreground font-mono">{{ item.phone }}</span>
                                </div>
                            </td>
                            <td class="p-3">
                                <div class="flex flex-col">
                                    <span class="font-semibold text-foreground uppercase">{{ item.profile?.license_plate }}</span>
                                    <span class="text-[10px] text-muted-foreground">{{ item.profile?.vehicle_type }}</span>
                                </div>
                            </td>
                            <td class="p-3 text-muted-foreground">{{ item.branch?.name }}</td>
                            <td class="p-3">
                                <span :class="{
                                    'bg-neutral-100 text-neutral-800 dark:bg-neutral-800 dark:text-neutral-300': item.status === 'pending',
                                    'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300': item.status === 'approved',
                                    'bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300': item.status === 'rejected',
                                    'bg-orange-100 text-orange-800 dark:bg-orange-900/40 dark:text-orange-300': item.status === 'suspended'
                                }" class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide">
                                    {{ item.status }}
                                </span>
                            </td>
                            <td class="p-3">
                                <div class="flex justify-center">
                                    <button @click="openStatusDrawer(item)" class="px-2 py-1 bg-neutral-100 dark:bg-neutral-800 hover:bg-neutral-200 border border-border text-[11px] font-semibold rounded">
                                        Gestionar
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="isStatusOpen" @click="isStatusOpen = false" class="fixed inset-0 bg-neutral-950/40 z-50"></div>
        </Transition>
        <Transition enter-active-class="transition duration-300 ease-out" enter-from-class="translate-x-full" enter-to-class="translate-x-0" leave-active-class="transition duration-200 ease-in" leave-from-class="translate-x-0" leave-to-class="translate-x-full">
            <div v-if="isStatusOpen" class="fixed inset-y-0 right-0 max-w-md w-full bg-card border-l border-border shadow-2xl z-50 p-5 flex flex-col justify-between">
                <form @submit.prevent="updateDriverStatus" class="space-y-5 flex-1 flex flex-col justify-between">
                    <div>
                        <div class="flex justify-between items-center border-b border-border pb-3 mb-4">
                            <h3 class="text-sm font-bold uppercase tracking-wide text-foreground">Gobernanza de Repartidor</h3>
                            <button type="button" @click="isStatusOpen = false" class="text-muted-foreground hover:text-foreground">
                                <span class="material-symbols-rounded text-lg block">close</span>
                            </button>
                        </div>

                        <div class="space-y-4">
                            <div class="bg-neutral-50 dark:bg-neutral-900/40 p-3 rounded border border-border space-y-1">
                                <p class="text-xs font-semibold text-foreground">{{ selectedDriver?.profile?.first_name }} {{ selectedDriver?.profile?.last_name }}</p>
                                <p class="text-[10px] text-muted-foreground font-mono">Licencia de conducir: {{ selectedDriver?.profile?.license_number }}</p>
                            </div>

                            <div class="space-y-1">
                                <label class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground">Estatus de Cuenta</label>
                                <select v-model="statusForm.status" class="w-full bg-background border border-border rounded-md px-3 py-2 text-xs focus:outline-none focus:border-primary">
                                    <option value="pending">Pendiente (Revisión Inicial)</option>
                                    <option value="approved">Aprobado (Alta Operativa)</option>
                                    <option value="rejected">Rechazado (Re-subida de Archivos)</option>
                                    <option value="suspended">Suspendido (Baja del Sistema)</option>
                                </select>
                            </div>

                            <div v-if="statusForm.status === 'rejected'" class="space-y-1">
                                <label class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground">Motivo de Rechazo Documental</label>
                                <textarea v-model="statusForm.rejection_reason" rows="4" class="w-full bg-background border border-border rounded-md px-3 py-2 text-xs focus:outline-none focus:border-primary placeholder:text-muted-foreground" placeholder="Especifique qué documentos no cumplen con los estándares corporativos..."></textarea>
                                <span v-if="statusForm.errors.rejection_reason" class="text-[10px] text-destructive block">{{ statusForm.errors.rejection_reason }}</span>
                            </div>

                            <div v-if="statusForm.status === 'suspended'" class="p-3 bg-orange-50 border border-orange-200 text-orange-800 rounded text-[11px]">
                                <strong>Advertencia de Seguridad:</strong> Al suspender esta cuenta, el servidor forzará inmediatamente la desconexión del conductor anulando el token activo en el silo API móvil.
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-border pt-4 flex gap-2">
                        <button type="button" @click="isStatusOpen = false" class="w-full py-2 border border-border text-xs font-semibold rounded hover:bg-neutral-50 text-foreground">Cancelar</button>
                        <button type="submit" :disabled="statusForm.processing" class="w-full py-2 bg-primary text-white text-xs font-semibold rounded hover:bg-primary/90 disabled:opacity-50">
                            {{ statusForm.processing ? 'Modificando Estatus...' : 'Confirmar Mutación' }}
                        </button>
                    </div>
                </form>
            </div>
        </Transition>

        <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="isRecoveryOpen" @click="isRecoveryOpen = false" class="fixed inset-0 bg-neutral-950/40 z-50"></div>
        </Transition>
        <Transition enter-active-class="transition duration-300 ease-out" enter-from-class="translate-x-full" enter-to-class="translate-x-0" leave-active-class="transition duration-200 ease-in" leave-from-class="translate-x-0" leave-to-class="translate-x-full">
            <div v-if="isRecoveryOpen" class="fixed inset-y-0 right-0 max-w-md w-full bg-card border-l border-border shadow-2xl z-50 p-5 flex flex-col justify-between">
                <div>
                    <div class="flex justify-between items-center border-b border-border pb-3 mb-4">
                        <h3 class="text-sm font-bold uppercase tracking-wide text-foreground">Recuperar Historial Conductor</h3>
                        <button @click="isRecoveryOpen = false" class="text-muted-foreground hover:text-foreground">
                            <span class="material-symbols-rounded text-lg block">close</span>
                        </button>
                    </div>

                    <div class="space-y-4">
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground">Número de Teléfono</label>
                            <div class="flex gap-2">
                                <input v-model="searchPhone" type="text" placeholder="+591XXXXXXXX" class="w-full bg-background border border-border rounded-md px-3 py-2 text-xs focus:outline-none focus:border-primary" />
                                <button @click="searchDeletedDriver" :disabled="isSearching" class="px-3 py-2 bg-primary text-white text-xs font-semibold rounded-md hover:bg-primary/90 disabled:opacity-50">
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
                                <span class="text-muted-foreground">Nombre de Conductor:</span>
                                <span class="font-medium text-foreground text-right">{{ recoveryUser.profile?.first_name }} {{ recoveryUser.profile?.last_name }}</span>
                                <span class="text-muted-foreground">Matrícula:</span>
                                <span class="font-mono text-foreground text-right uppercase">{{ recoveryUser.profile?.license_plate }}</span>
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