<script setup>
import { ref, watch } from 'vue';
import { router, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/Admin/Pagination.vue';
import UserNavTabs from '@/Components/Admin/Users/UserNavTabs.vue';
import axios from 'axios';

const props = defineProps({
    drivers: {
        type: Object,
        required: true,
    },
    branches: {
        type: Array,
        required: true,
    },
    filters: {
        type: Object,
        required: true,
    },
});

const search = ref(props.filters.search || '');
const branchId = ref(props.filters.branch_id || '');
const status = ref(props.filters.status || '');

let debounceTimeout = null;

const applyFilters = () => {
    router.get(
        route('drivers.index'),
        {
            search: search.value || undefined,
            branch_id: branchId.value || undefined,
            status: status.value || undefined,
        },
        {
            preserveState: true,
            replace: true,
        }
    );
};

const handleSearchInput = () => {
    clearTimeout(debounceTimeout);
    debounceTimeout = setTimeout(() => {
        applyFilters();
    }, 300);
};

watch([branchId, status], () => {
    applyFilters();
});

const resetFilters = () => {
    search.value = '';
    branchId.value = '';
    status.value = '';
    applyFilters();
};

// Estados de Control para Drawers / Modales
const isRecoveryOpen = ref(false);
const isStatusOpen = ref(false);
const selectedDriver = ref(null);

const searchPhone = ref('');
const recoveryUser = ref(null);
const recoveryError = ref('');
const isSearching = ref(false);

// Formulario de mutación de estatus para el panel lateral de gobernanza
const statusForm = useForm({
    status: '',
    rejection_reason: '',
    branch_id: '',
});

// Estado de previsualización de documentos fotográficos
const previewImage = ref(null);
const openPreview = (url, title) => {
    previewImage.value = { url, title };
};
const closePreview = () => {
    previewImage.value = null;
};

const openStatusDrawer = (driver) => {
    selectedDriver.value = driver;
    statusForm.status = driver.status;
    statusForm.rejection_reason = driver.profile?.rejection_reason || '';
    statusForm.branch_id = driver.branch_id || driver.branch?.id || '';
    isStatusOpen.value = true;
};

const updateDriverStatus = () => {
    if (!selectedDriver.value) return;

    statusForm.patch(route('drivers.change-status', selectedDriver.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            isStatusOpen.value = false;
        },
    });
};

const searchDeletedDriver = async () => {
    if (!searchPhone.value) return;
    isSearching.value = true;
    recoveryError.value = '';
    recoveryUser.value = null;

    try {
        const response = await axios.post(route('drivers.search-deleted'), {
            phone: searchPhone.value,
        });
        recoveryUser.value = response.data.user;
    } catch (error) {
        recoveryError.value = error.response?.data?.message || 'Error en la búsqueda del conductor.';
    } finally {
        isSearching.value = false;
    }
};

const executeRestore = (id) => {
    router.post(route('drivers.restore', id), {}, {
        onSuccess: () => {
            isRecoveryOpen.value = false;
            searchPhone.value = '';
            recoveryUser.value = null;
            recoveryError.value = '';
        },
    });
};

const closeRecoveryModal = () => {
    isRecoveryOpen.value = false;
    searchPhone.value = '';
    recoveryUser.value = null;
    recoveryError.value = '';
};

const statusLabels = {
    pending: { label: 'Pendiente', class: 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/20' },
    approved: { label: 'Aprobado', class: 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20' },
    rejected: { label: 'Rechazado', class: 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border-rose-500/20' },
    suspended: { label: 'Suspendido', class: 'bg-neutral-500/10 text-neutral-600 dark:text-neutral-400 border-neutral-500/20' },
};
</script>

<template>
    <AdminLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-xl md:text-2xl font-black tracking-tight text-foreground uppercase">
                        Gestión de Repartidores
                    </h1>
                    <p class="text-xs text-muted-foreground mt-0.5">
                        Flota logística, control de estatus operativo, licencias y trazabilidad de vehículos
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        @click="isRecoveryOpen = true"
                        class="px-3 py-2 bg-secondary text-secondary-foreground font-semibold rounded-md text-xs transition-colors hover:bg-neutral-200 dark:hover:bg-neutral-800 inline-flex items-center gap-1.5 border border-border"
                    >
                        <span class="material-symbols-rounded text-[18px]">history</span>
                        <span>Recuperar Eliminado</span>
                    </button>

                    <Link
                        :href="route('drivers.create')"
                        class="admin-btn-primary inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider py-2 px-3.5"
                    >
                        <span class="material-symbols-rounded text-[18px]">person_add</span>
                        <span>Registrar Conductor</span>
                    </Link>
                </div>
            </div>
        </template>

        <!-- Navegación Unificada del Ecosistema de Usuarios -->
        <UserNavTabs />

        <!-- Barra de Filtros Operativos -->
        <div class="bg-card border border-border p-3 rounded-md shadow-flat mb-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2.5 items-center">
                <!-- Buscador de Texto -->
                <div class="relative lg:col-span-2">
                    <span class="material-symbols-rounded absolute left-2.5 top-1/2 -translate-y-1/2 text-muted-foreground text-[18px]">search</span>
                    <input
                        v-model="search"
                        @input="handleSearchInput"
                        type="text"
                        placeholder="Buscar por nombre, placa, licencia, teléfono..."
                        class="admin-input pl-8 py-1.5 text-xs w-full"
                    />
                </div>

                <!-- Filtro Sucursal -->
                <div>
                    <select v-model="branchId" class="admin-input py-1.5 text-xs w-full">
                        <option value="">Todas las Sucursales</option>
                        <option v-for="branch in branches" :key="branch.id" :value="branch.id">
                            {{ branch.name }}
                        </option>
                    </select>
                </div>

                <!-- Filtro Estatus & Reset -->
                <div class="flex items-center gap-2">
                    <select v-model="status" class="admin-input py-1.5 text-xs w-full">
                        <option value="">Todos los Estatus</option>
                        <option value="pending">Pendientes</option>
                        <option value="approved">Aprobados</option>
                        <option value="rejected">Rechazados</option>
                        <option value="suspended">Suspendidos</option>
                    </select>

                    <button
                        v-if="search || branchId || status"
                        @click="resetFilters"
                        title="Limpiar filtros"
                        class="p-1.5 text-muted-foreground hover:text-foreground border border-border rounded hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors shrink-0"
                    >
                        <span class="material-symbols-rounded text-[18px] block">filter_alt_off</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Tabla Densa de Repartidores -->
        <div class="bg-card border border-border rounded-md shadow-flat overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-border bg-neutral-100/60 dark:bg-neutral-900/60 select-none">
                            <th class="py-2.5 px-3.5 font-bold uppercase tracking-wider text-[10px] text-muted-foreground">Conductor</th>
                            <th class="py-2.5 px-3.5 font-bold uppercase tracking-wider text-[10px] text-muted-foreground">Vehículo / Placa</th>
                            <th class="py-2.5 px-3.5 font-bold uppercase tracking-wider text-[10px] text-muted-foreground">Sucursal Asignada</th>
                            <th class="py-2.5 px-3.5 font-bold uppercase tracking-wider text-[10px] text-muted-foreground text-center">Estatus Operativo</th>
                            <th class="py-2.5 px-3.5 font-bold uppercase tracking-wider text-[10px] text-muted-foreground text-center">Conexión</th>
                            <th class="py-2.5 px-3.5 font-bold uppercase tracking-wider text-[10px] text-muted-foreground text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border/60">
                        <tr v-if="!drivers.data || drivers.data.length === 0">
                            <td colspan="6" class="py-8 text-center text-muted-foreground text-xs font-normal">
                                No se encontraron repartidores registrados bajo los parámetros seleccionados.
                            </td>
                        </tr>
                        <tr
                            v-for="item in drivers.data"
                            :key="item.id"
                            class="hover:bg-neutral-50 dark:hover:bg-neutral-800/40 transition-colors"
                        >
                            <!-- Conductor -->
                            <td class="py-2.5 px-3.5">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-7 h-7 rounded bg-primary/10 border border-primary/20 text-primary flex items-center justify-center font-bold text-xs shrink-0 select-none">
                                        {{ item.first_name?.[0]?.toUpperCase() || 'R' }}
                                    </div>
                                    <div class="min-w-0">
                                        <span class="font-bold text-foreground block truncate">{{ item.full_name }}</span>
                                        <span class="text-[11px] font-mono text-muted-foreground block truncate">{{ item.phone }}</span>
                                        <span v-if="item.profile?.license_number" class="text-[10px] text-muted-foreground/80 block font-mono">
                                            Lic: {{ item.profile.license_number }}
                                        </span>
                                    </div>
                                </div>
                            </td>

                            <!-- Vehículo / Placa -->
                            <td class="py-2.5 px-3.5">
                                <div class="flex flex-col gap-0.5">
                                    <span v-if="item.license_plate" class="inline-block font-mono font-bold text-[11px] text-foreground uppercase bg-neutral-100 dark:bg-neutral-800 px-1.5 py-0.5 rounded border border-border w-fit">
                                        {{ item.license_plate }}
                                    </span>
                                    <span v-else class="text-[11px] text-muted-foreground/60 italic">Sin placa</span>
                                    <span class="text-[10px] text-muted-foreground capitalize">
                                        {{ item.vehicle_type || 'No especificado' }}
                                    </span>
                                </div>
                            </td>

                            <!-- Sucursal Asignada -->
                            <td class="py-2.5 px-3.5">
                                <div v-if="item.branch">
                                    <span class="font-semibold text-foreground block">{{ item.branch.name }}</span>
                                    <span v-if="item.branch.city" class="text-[10px] text-muted-foreground block">{{ item.branch.city }}</span>
                                </div>
                                <span v-else class="text-muted-foreground/60 italic text-xs">Sin base asignada</span>
                            </td>

                            <!-- Estatus Operativo -->
                            <td class="py-2.5 px-3.5 text-center">
                                <span
                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold border uppercase tracking-wider"
                                    :class="statusLabels[item.status]?.class || 'bg-neutral-500/10 text-neutral-600 border-neutral-500/20'"
                                >
                                    {{ statusLabels[item.status]?.label || item.status }}
                                </span>
                            </td>

                            <!-- Conexión -->
                            <td class="py-2.5 px-3.5 text-center">
                                <div class="inline-flex flex-col items-center gap-0.5">
                                    <span
                                        class="inline-flex items-center gap-1 text-[10px] font-bold"
                                        :class="item.is_online ? 'text-emerald-600 dark:text-emerald-400' : 'text-muted-foreground'"
                                    >
                                        <span class="w-1.5 h-1.5 rounded-full" :class="item.is_online ? 'bg-emerald-500' : 'bg-muted-foreground/40'"></span>
                                        <span>{{ item.is_online ? 'En línea' : 'Desconectado' }}</span>
                                    </span>
                                    <span v-if="item.last_seen_at" class="text-[9px] text-muted-foreground/70">
                                        {{ item.last_seen_at }}
                                    </span>
                                </div>
                            </td>

                            <!-- Acciones -->
                            <td class="py-2.5 px-3.5 text-right">
                                <button
                                    type="button"
                                    @click="openStatusDrawer(item)"
                                    class="px-2.5 py-1 text-xs font-semibold rounded border border-border bg-secondary text-secondary-foreground hover:bg-neutral-200 dark:hover:bg-neutral-800 transition-colors inline-flex items-center gap-1"
                                >
                                    <span class="material-symbols-rounded text-[14px]">tune</span>
                                    <span>Gestionar</span>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <Pagination :meta="drivers" />
        </div>

        <!-- Panel Lateral / Drawer de Gobernanza de Estatus -->
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="isStatusOpen" @click="isStatusOpen = false" class="fixed inset-0 bg-neutral-950/40 z-50"></div>
        </Transition>
        <Transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="translate-x-full"
            enter-to-class="translate-x-0"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="translate-x-0"
            leave-to-class="translate-x-full"
        >
            <div v-if="isStatusOpen" class="fixed inset-y-0 right-0 max-w-md w-full bg-card border-l border-border shadow-2xl z-50 p-5 flex flex-col justify-between overflow-y-auto">
                <form @submit.prevent="updateDriverStatus" class="space-y-5 flex-1 flex flex-col justify-between">
                    <div>
                        <div class="flex justify-between items-center border-b border-border pb-3 mb-4">
                            <h3 class="text-sm font-bold uppercase tracking-wide text-foreground flex items-center gap-1.5">
                                <span class="material-symbols-rounded text-lg text-primary">verified_user</span>
                                <span>Gobernanza de Repartidor</span>
                            </h3>
                            <button type="button" @click="isStatusOpen = false" class="text-muted-foreground hover:text-foreground">
                                <span class="material-symbols-rounded text-lg block">close</span>
                            </button>
                        </div>

                        <div class="space-y-4">
                            <div class="bg-neutral-100 dark:bg-neutral-900/50 p-3 rounded border border-border space-y-1">
                                <p class="text-xs font-bold text-foreground">{{ selectedDriver?.full_name }}</p>
                                <p class="text-[11px] text-muted-foreground font-mono">{{ selectedDriver?.phone }} &bull; {{ selectedDriver?.email }}</p>
                                <p v-if="selectedDriver?.profile?.license_number" class="text-[10px] text-muted-foreground font-mono">
                                    Licencia: {{ selectedDriver.profile.license_number }}
                                </p>
                            </div>

                            <div class="space-y-1">
                                <label class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground">
                                    Base Operativa (Sucursal Asignada)
                                </label>
                                <select v-model="statusForm.branch_id" class="admin-input py-2 text-xs w-full">
                                    <option value="">Seleccione Sucursal</option>
                                    <option v-for="branch in branches" :key="branch.id" :value="branch.id">
                                        {{ branch.name }} {{ branch.city ? `(${branch.city})` : '' }}
                                    </option>
                                </select>
                                <span v-if="statusForm.errors.branch_id" class="text-[10px] text-rose-600 block font-medium">
                                    {{ statusForm.errors.branch_id }}
                                </span>
                            </div>

                            <div class="space-y-1">
                                <label class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground">Estatus de Cuenta</label>
                                <select v-model="statusForm.status" class="admin-input py-2 text-xs w-full">
                                    <option value="pending">Pendiente (Revisión Inicial)</option>
                                    <option value="approved">Aprobado (Alta Operativa)</option>
                                    <option value="rejected">Rechazado (Re-subida de Archivos)</option>
                                    <option value="suspended">Suspendido (Baja del Sistema)</option>
                                </select>
                            </div>

                            <div v-if="statusForm.status === 'rejected'" class="space-y-1">
                                <label class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground">Motivo de Rechazo Documental</label>
                                <textarea
                                    v-model="statusForm.rejection_reason"
                                    rows="4"
                                    class="admin-input py-2 text-xs w-full"
                                    placeholder="Especifique qué documentos no cumplen con los estándares corporativos..."
                                ></textarea>
                                <span v-if="statusForm.errors.rejection_reason" class="text-[10px] text-rose-600 block font-medium">
                                    {{ statusForm.errors.rejection_reason }}
                                </span>
                            </div>

                            <div v-if="statusForm.status === 'suspended'" class="p-3 bg-amber-500/10 border border-amber-500/20 text-amber-600 dark:text-amber-400 rounded text-[11px] space-y-1">
                                <div class="font-bold flex items-center gap-1">
                                    <span class="material-symbols-rounded text-sm">warning</span>
                                    <span>Advertencia de Seguridad</span>
                                </div>
                                <p>Al suspender esta cuenta, el servidor invalidará inmediatamente todas las sesiones y tokens móviles activos.</p>
                            </div>

                            <!-- Inspección de Documentación Fotográfica -->
                            <div class="space-y-2 pt-2 border-t border-border">
                                <label class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground flex items-center justify-between">
                                    <span>Expediente de Documentos</span>
                                    <span class="text-[9px] font-normal text-muted-foreground/70">Click para ampliar</span>
                                </label>

                                <div class="grid grid-cols-3 gap-2">
                                    <!-- CI -->
                                    <div class="border border-border rounded p-2 bg-neutral-50 dark:bg-neutral-900/40 flex flex-col items-center justify-between text-center gap-1.5">
                                        <span class="text-[10px] font-bold text-foreground truncate w-full">Cédula (CI)</span>
                                        <div v-if="selectedDriver?.profile?.ci_url" class="relative group cursor-pointer w-full" @click="openPreview(selectedDriver.profile.ci_url, 'Cédula de Identidad')">
                                            <img :src="selectedDriver.profile.ci_url" class="w-full h-14 object-cover rounded border border-border" alt="CI" />
                                            <div class="absolute inset-0 bg-neutral-950/40 opacity-0 group-hover:opacity-100 transition-opacity rounded flex items-center justify-center text-white">
                                                <span class="material-symbols-rounded text-sm">visibility</span>
                                            </div>
                                        </div>
                                        <div v-else class="h-14 w-full flex flex-col items-center justify-center text-muted-foreground/50 border border-dashed border-border rounded text-[9px]">
                                            <span class="material-symbols-rounded text-sm">badge</span>
                                            <span>Sin cargar</span>
                                        </div>
                                        <a v-if="selectedDriver?.profile?.ci_url" :href="selectedDriver.profile.ci_url" target="_blank" class="text-[9px] text-primary hover:underline font-mono">Abrir</a>
                                    </div>

                                    <!-- Licencia -->
                                    <div class="border border-border rounded p-2 bg-neutral-50 dark:bg-neutral-900/40 flex flex-col items-center justify-between text-center gap-1.5">
                                        <span class="text-[10px] font-bold text-foreground truncate w-full">Licencia</span>
                                        <div v-if="selectedDriver?.profile?.license_url" class="relative group cursor-pointer w-full" @click="openPreview(selectedDriver.profile.license_url, 'Licencia de Conducir')">
                                            <img :src="selectedDriver.profile.license_url" class="w-full h-14 object-cover rounded border border-border" alt="Licencia" />
                                            <div class="absolute inset-0 bg-neutral-950/40 opacity-0 group-hover:opacity-100 transition-opacity rounded flex items-center justify-center text-white">
                                                <span class="material-symbols-rounded text-sm">visibility</span>
                                            </div>
                                        </div>
                                        <div v-else class="h-14 w-full flex flex-col items-center justify-center text-muted-foreground/50 border border-dashed border-border rounded text-[9px]">
                                            <span class="material-symbols-rounded text-sm">id_card</span>
                                            <span>Sin cargar</span>
                                        </div>
                                        <a v-if="selectedDriver?.profile?.license_url" :href="selectedDriver.profile.license_url" target="_blank" class="text-[9px] text-primary hover:underline font-mono">Abrir</a>
                                    </div>

                                    <!-- Vehículo -->
                                    <div class="border border-border rounded p-2 bg-neutral-50 dark:bg-neutral-900/40 flex flex-col items-center justify-between text-center gap-1.5">
                                        <span class="text-[10px] font-bold text-foreground truncate w-full">Vehículo</span>
                                        <div v-if="selectedDriver?.profile?.vehicle_url" class="relative group cursor-pointer w-full" @click="openPreview(selectedDriver.profile.vehicle_url, 'Fotografía del Vehículo')">
                                            <img :src="selectedDriver.profile.vehicle_url" class="w-full h-14 object-cover rounded border border-border" alt="Vehículo" />
                                            <div class="absolute inset-0 bg-neutral-950/40 opacity-0 group-hover:opacity-100 transition-opacity rounded flex items-center justify-center text-white">
                                                <span class="material-symbols-rounded text-sm">visibility</span>
                                            </div>
                                        </div>
                                        <div v-else class="h-14 w-full flex flex-col items-center justify-center text-muted-foreground/50 border border-dashed border-border rounded text-[9px]">
                                            <span class="material-symbols-rounded text-sm">directions_car</span>
                                            <span>Sin cargar</span>
                                        </div>
                                        <a v-if="selectedDriver?.profile?.vehicle_url" :href="selectedDriver.profile.vehicle_url" target="_blank" class="text-[9px] text-primary hover:underline font-mono">Abrir</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-border pt-4 flex gap-2">
                        <button
                            type="button"
                            @click="isStatusOpen = false"
                            class="w-full py-2 border border-border text-xs font-semibold rounded hover:bg-neutral-100 dark:hover:bg-neutral-800 text-foreground transition-colors"
                        >
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            :disabled="statusForm.processing"
                            class="w-full py-2 bg-primary text-white text-xs font-semibold rounded hover:bg-primary/90 disabled:opacity-50 transition-colors"
                        >
                            {{ statusForm.processing ? 'Modificando...' : 'Confirmar Cambios' }}
                        </button>
                    </div>
                </form>
            </div>
        </Transition>

        <!-- Modal Flotante de Previsualización de Documento -->
        <div v-if="previewImage" class="fixed inset-0 z-[250] flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-neutral-950/80 backdrop-blur-sm" @click="closePreview"></div>
            <div class="relative max-w-2xl w-full bg-card border border-border rounded-lg shadow-2xl p-4 z-10 animate-in fade-in zoom-in-95 duration-100 flex flex-col gap-3">
                <div class="flex items-center justify-between border-b border-border pb-2">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-foreground flex items-center gap-1.5">
                        <span class="material-symbols-rounded text-base text-primary">image</span>
                        <span>{{ previewImage.title }}</span>
                    </h3>
                    <div class="flex items-center gap-2">
                        <a :href="previewImage.url" target="_blank" class="text-xs text-primary hover:underline flex items-center gap-1 font-mono">
                            <span class="material-symbols-rounded text-sm">open_in_new</span>
                            <span>Nueva pestaña</span>
                        </a>
                        <button @click="closePreview" class="p-1 rounded hover:bg-neutral-100 dark:hover:bg-neutral-800 text-muted-foreground hover:text-foreground">
                            <span class="material-symbols-rounded text-base block">close</span>
                        </button>
                    </div>
                </div>
                <div class="flex items-center justify-center bg-neutral-950/50 rounded overflow-hidden max-h-[70vh]">
                    <img :src="previewImage.url" :alt="previewImage.title" class="max-h-[68vh] w-auto object-contain rounded" />
                </div>
            </div>
        </div>

        <!-- Drawer / Modal de Recuperación de Conductor Eliminado -->
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="isRecoveryOpen" @click="closeRecoveryModal" class="fixed inset-0 bg-neutral-950/40 z-50"></div>
        </Transition>
        <Transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="translate-x-full"
            enter-to-class="translate-x-0"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="translate-x-0"
            leave-to-class="translate-x-full"
        >
            <div v-if="isRecoveryOpen" class="fixed inset-y-0 right-0 max-w-md w-full bg-card border-l border-border shadow-2xl z-50 p-5 flex flex-col justify-between">
                <div>
                    <div class="flex justify-between items-center border-b border-border pb-3 mb-4">
                        <h3 class="text-sm font-bold uppercase tracking-wide text-foreground flex items-center gap-1.5">
                            <span class="material-symbols-rounded text-lg text-primary">history</span>
                            <span>Recuperar Conductor Eliminado</span>
                        </h3>
                        <button @click="closeRecoveryModal" class="text-muted-foreground hover:text-foreground">
                            <span class="material-symbols-rounded text-lg block">close</span>
                        </button>
                    </div>

                    <div class="space-y-4">
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground">Número de Teléfono</label>
                            <div class="flex gap-2">
                                <input
                                    v-model="searchPhone"
                                    type="text"
                                    placeholder="+591XXXXXXXX"
                                    class="admin-input py-1.5 text-xs flex-1 font-mono"
                                    :disabled="isSearching"
                                    @keyup.enter="searchDeletedDriver"
                                />
                                <button
                                    @click="searchDeletedDriver"
                                    :disabled="isSearching"
                                    class="px-3 py-1.5 bg-primary text-white text-xs font-semibold rounded hover:bg-primary/90 disabled:opacity-50"
                                >
                                    {{ isSearching ? 'Buscando...' : 'Buscar' }}
                                </button>
                            </div>
                        </div>

                        <div v-if="recoveryError" class="p-3 bg-rose-500/10 border border-rose-500/20 text-rose-600 dark:text-rose-400 rounded text-xs font-medium">
                            {{ recoveryError }}
                        </div>

                        <div v-if="recoveryUser" class="bg-neutral-100 dark:bg-neutral-900/50 border border-border rounded p-4 space-y-3">
                            <h4 class="text-xs font-bold text-foreground border-b border-border pb-1.5 uppercase tracking-wider text-[10px]">
                                Registro Localizado
                            </h4>
                            <div class="grid grid-cols-2 gap-2 text-xs">
                                <span class="text-muted-foreground">Nombre:</span>
                                <span class="font-bold text-foreground text-right">{{ recoveryUser.full_name }}</span>
                                
                                <span class="text-muted-foreground">Teléfono:</span>
                                <span class="font-mono text-foreground text-right">{{ recoveryUser.phone }}</span>

                                <span class="text-muted-foreground">Matrícula:</span>
                                <span class="font-mono text-foreground text-right uppercase">{{ recoveryUser.license_plate || 'Sin placa' }}</span>

                                <span class="text-muted-foreground">Tipo de Vehículo:</span>
                                <span class="text-foreground text-right capitalize">{{ recoveryUser.vehicle_type || 'No especificado' }}</span>
                            </div>

                            <div class="mt-2 border-t border-border pt-3">
                                <button
                                    @click="executeRestore(recoveryUser.id)"
                                    class="w-full admin-btn-primary py-2 text-xs font-bold uppercase tracking-wider inline-flex items-center justify-center gap-1.5"
                                >
                                    <span class="material-symbols-rounded text-sm">settings_backup_restore</span>
                                    <span>Confirmar Restauración en Servidor</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </AdminLayout>
</template>
