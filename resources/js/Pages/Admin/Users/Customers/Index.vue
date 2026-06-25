<script setup>
import { ref, computed, watch } from 'vue';
import { router, useForm, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    users: {
        type: Array,
        required: true
    },
    pagination: {
        type: Object,
        required: true
    },
    branches: {
        type: Array,
        required: true
    },
    filters: {
        type: Object,
        required: true
    }
});

// Inicialización de filtros reactivos basados en las props del servidor
const searchFilter = ref(props.filters.search || '');
const branchFilter = ref(props.filters.branch_id || '');
const statusFilter = ref(props.filters.is_active !== undefined && props.filters.is_active !== null ? String(props.filters.is_active) : '');

// Instancia de formulario para la mutación de estado inline (Status Toggle)
const statusForm = useForm({
    is_active: false
});

// Instancia de formulario para el procesamiento de restauración de cuentas
const restoreForm = useForm({});

// Estado del modal de recuperación de cuentas eliminadas
const isModalOpen = ref(false);
const searchPhone = ref('');
const foundUser = ref(null);
const searchError = ref('');
const isSearching = ref(false);

/**
 * Ejecuta el re-fetch de datos aplicando los filtros reactivos seleccionados.
 */
const applyFilters = () => {
    router.get(route('customers.index'), {
        search: searchFilter.value || undefined,
        branch_id: branchFilter.value || undefined,
        is_active: statusFilter.value !== '' ? statusFilter.value : undefined
    }, {
        preserveState: true,
        replace: true
    });
};

// Observadores para la ejecución automática de filtros ante cambios de estado
watch([branchFilter, statusFilter], () => {
    applyFilters();
});

/**
 * Conmuta el estado de actividad de un cliente utilizando useForm nativo.
 */
const toggleStatus = (user) => {
    statusForm.is_active = !user.is_active;
    statusForm.patch(route('customers.change-status', user.id), {
        preserveScroll: true
    });
};

/**
 * Realiza una consulta asíncrona al endpoint de borrados para validar existencia por teléfono.
 */
const searchDeletedCustomer = async () => {
    if (!searchPhone.value) {
        searchError.value = 'El número de teléfono es obligatorio.';
        return;
    }

    isSearching.value = true;
    searchError.value = '';
    foundUser.value = null;

    try {
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const response = await window.fetch(route('customers.search-deleted'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Inertia': 'true',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': token
            },
            body: JSON.stringify({ phone: searchPhone.value })
        });

        const data = await response.json();

        if (response.status === 404) {
            searchError.value = data.message;
        } else if (!response.ok) {
            searchError.value = 'Ocurrió un error inesperado en la terminal.';
        } else {
            foundUser.value = data.user;
        }
    } catch (error) {
        searchError.value = 'Fallo de conexión con el servidor.';
    } finally {
        isSearching.value = false;
    }
};

/**
 * Despacha la petición POST de restauración e invalida el estado local del modal.
 */
const submitRestore = () => {
    if (!foundUser.value) return;

    restoreForm.post(route('customers.restore', foundUser.value.id), {
        onSuccess: () => {
            isModalOpen.value = false;
            searchPhone.value = '';
            foundUser.value = null;
            searchError.value = '';
        }
    });
};

/**
 * Resetea por completo los campos operativos al cerrar el modal de recuperación.
 */
const closeRestoreModal = () => {
    isModalOpen.value = false;
    searchPhone.value = '';
    foundUser.value = null;
    searchError.value = '';
};
</script>

<template>
    <AdminLayout>
        <template #header>
            Gestión de Clientes (B2C)
        </template>

        <div class="space-y-4">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 bg-card p-4 border border-border rounded-md shadow-flat">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 flex-1">
                    <div>
                        <input 
                            v-model="searchFilter"
                            type="text"
                            placeholder="Buscar por nombre, correo..."
                            class="admin-input"
                            @keyup.enter="applyFilters"
                        />
                    </div>
                    <div>
                        <select v-model="branchFilter" class="admin-input">
                            <option value="">Todas las sucursales</option>
                            <option v-for="branch in branches" :key="branch.id" :value="branch.id">
                                {{ branch.name }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <select v-model="statusFilter" class="admin-input">
                            <option value="">Todos los estados</option>
                            <option value="1">Activos</option>
                            <option value="0">Inactivos</option>
                        </select>
                    </div>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    <button 
                        type="button" 
                        @click="isModalOpen = true"
                        class="px-3 py-2 bg-secondary text-secondary-foreground font-semibold rounded-md text-sm transition-colors duration-100 hover:bg-neutral-200 dark:hover:bg-neutral-800 inline-flex items-center gap-1.5"
                    >
                        <span class="material-symbols-rounded text-lg">history</span>
                        <span>Restaurar Cuenta</span>
                    </button>

                    <Link 
                        :href="route('customers.create')"
                        class="admin-btn-primary inline-flex items-center gap-1.5"
                    >
                        <span class="material-symbols-rounded text-lg">person_add</span>
                        <span>Nuevo Cliente</span>
                    </Link>
                </div>
            </div>

            <div class="bg-card border border-border rounded-md shadow-flat overflow-x-auto">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th class="admin-table-th">Cliente</th>
                            <th class="admin-table-th">Identificador (Email)</th>
                            <th class="admin-table-th">Teléfono</th>
                            <th class="admin-table-th">Sucursal Asignada</th>
                            <th class="admin-table-th text-center">Estado</th>
                            <th class="admin-table-th text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="users.length === 0">
                            <td colspan="6" class="admin-table-td text-center text-muted-foreground py-6 font-normal">
                                No se encontraron registros de clientes bajo los parámetros seleccionados.
                            </td>
                        </tr>
                        <tr v-for="user in users" :key="user.id" class="admin-table-tr">
                            <td class="admin-table-td">
                                {{ user.first_name }} {{ user.last_name }}
                            </td>
                            <td class="admin-table-td">
                                {{ user.email }}
                            </td>
                            <td class="admin-table-td font-mono text-xs">
                                {{ user.phone }}
                            </td>
                            <td class="admin-table-td">
                                <span v-if="user.branch">{{ user.branch.name }}</span>
                                <span v-else class="text-muted-foreground/60 italic font-normal text-xs">Sin asignar</span>
                            </td>
                            <td class="admin-table-td text-center">
                                <span :class="user.is_active ? 'badge-success' : 'badge-error'">
                                    {{ user.is_active ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td class="admin-table-td text-right">
                                <button 
                                    type="button"
                                    @click="toggleStatus(user)"
                                    :disabled="statusForm.processing"
                                    class="px-2 py-1 bg-secondary text-secondary-foreground text-xs font-semibold rounded border border-border hover:bg-neutral-200 dark:hover:bg-neutral-800 disabled:opacity-50 transition-colors"
                                >
                                    {{ user.is_active ? 'Desactivar' : 'Activar' }}
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="pagination.links && pagination.links.length > 3" class="flex items-center justify-between bg-card p-4 border border-border rounded-md shadow-flat">
                <div class="text-xs text-muted-foreground font-medium">
                    Página {{ pagination.current_page }} de {{ pagination.last_page }}
                </div>
                <div class="flex items-center gap-1">
                    <template v-for="(link, index) in pagination.links" :key="index">
                        <div 
                            v-if="link.url === null" 
                            class="px-2.5 py-1 text-xs text-muted-foreground/40 border border-border/50 rounded-sm cursor-not-allowed select-none"
                            v-html="link.label"
                        />
                        <Link
                            v-else
                            :href="link.url"
                            class="px-2.5 py-1 text-xs font-semibold rounded-sm border transition-colors"
                            :class="[link.active ? 'bg-primary text-primary-foreground border-primary' : 'bg-card text-foreground border-border hover:bg-secondary']"
                            v-html="link.label"
                        />
                    </template>
                </div>
            </div>
        </div>

        <div v-if="isModalOpen" class="fixed inset-0 z-[200] flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-neutral-950/50 backdrop-blur-sm" @click="closeRestoreModal"></div>
            
            <div class="relative w-full max-w-md bg-card border border-border rounded-md shadow-flat p-6 z-10 animate-in fade-in zoom-in-95 duration-100">
                <div class="flex items-center justify-between mb-4 pb-2 border-b border-border">
                    <h2 class="text-sm font-bold text-foreground uppercase tracking-wide flex items-center gap-1.5">
                        <span class="material-symbols-rounded text-lg text-primary">history</span>
                        <span>Restaurar Cuenta Eliminada</span>
                    </h2>
                    <button @click="closeRestoreModal" class="p-1 rounded hover:bg-neutral-100 dark:hover:bg-neutral-800 text-muted-foreground">
                        <span class="material-symbols-rounded text-base block">close</span>
                    </button>
                </div>

                <div class="space-y-4">
                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">
                            Teléfono de la cuenta a buscar
                        </label>
                        <div class="flex gap-2">
                            <input 
                                v-model="searchPhone"
                                type="text"
                                placeholder="+51999888777"
                                class="admin-input flex-1 font-mono"
                                :disabled="isSearching || restoreForm.processing"
                                @keyup.enter="searchDeletedCustomer"
                            />
                            <button 
                                type="button"
                                @click="searchDeletedCustomer"
                                :disabled="isSearching || restoreForm.processing"
                                class="px-3 bg-secondary border border-border rounded-md text-xs font-bold uppercase tracking-wider hover:bg-neutral-200 dark:hover:bg-neutral-800 disabled:opacity-50 inline-flex items-center justify-center shrink-0"
                            >
                                {{ isSearching ? 'Buscando...' : 'Buscar' }}
                            </button>
                        </div>
                        <p v-if="searchError" class="text-xs text-error font-medium mt-1 flex items-center gap-1">
                            <span class="material-symbols-rounded text-sm shrink-0">error</span>
                            <span>{{ searchError }}</span>
                        </p>
                    </div>

                    <div v-if="foundUser" class="p-3 bg-neutral-100 dark:bg-neutral-800/50 border border-border rounded-md space-y-3 animate-in fade-in duration-75">
                        <div class="text-xs space-y-1">
                            <div class="text-muted-foreground uppercase tracking-wider font-semibold text-[10px]">Registro Localizado</div>
                            <div class="text-foreground font-bold text-sm">{{ foundUser.first_name }} {{ foundUser.last_name }}</div>
                            <div class="text-muted-foreground font-medium font-mono text-[11px]">{{ foundUser.email }}</div>
                        </div>

                        <div class="pt-2 border-t border-border/60">
                            <button
                                type="button"
                                @click="submitRestore"
                                :disabled="restoreForm.processing"
                                class="w-full admin-btn-primary inline-flex items-center justify-center gap-1.5 text-xs font-bold uppercase tracking-wider"
                            >
                                <span class="material-symbols-rounded text-base shrink-0">settings_backup_restore</span>
                                <span>{{ restoreForm.processing ? 'Restaurando...' : 'Confirmar Restauración' }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>