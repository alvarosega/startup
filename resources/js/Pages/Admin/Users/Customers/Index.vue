<script setup>
import { ref, watch } from 'vue';
import { router, useForm, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/Admin/Pagination.vue';
import UserNavTabs from '@/Components/Admin/Users/UserNavTabs.vue';

const props = defineProps({
    customers: {
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

const searchFilter = ref(props.filters.search || '');
const branchFilter = ref(props.filters.branch_id || '');
const statusFilter = ref(
    props.filters.is_active !== undefined && props.filters.is_active !== null
        ? String(props.filters.is_active)
        : ''
);

let debounceTimeout = null;

const applyFilters = () => {
    router.get(
        route('customers.index'),
        {
            search: searchFilter.value || undefined,
            branch_id: branchFilter.value || undefined,
            is_active: statusFilter.value !== '' ? statusFilter.value : undefined,
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

watch([branchFilter, statusFilter], () => {
    applyFilters();
});

const resetFilters = () => {
    searchFilter.value = '';
    branchFilter.value = '';
    statusFilter.value = '';
    applyFilters();
};

// Formulario reactivo para conmutación de estado
const statusForm = useForm({
    is_active: false,
});

const toggleStatus = (user) => {
    statusForm.is_active = !user.is_active;
    statusForm.patch(route('customers.change-status', user.id), {
        preserveScroll: true,
    });
};

// Formulario y estado modal de recuperación de cuentas eliminadas
const restoreForm = useForm({});
const isModalOpen = ref(false);
const searchPhone = ref('');
const foundUser = ref(null);
const searchError = ref('');
const isSearching = ref(false);

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
                'X-CSRF-TOKEN': token,
            },
            body: JSON.stringify({ phone: searchPhone.value }),
        });

        const data = await response.json();

        if (response.status === 404) {
            searchError.value = data.message;
        } else if (!response.ok) {
            searchError.value = 'Ocurrió un error inesperado al consultar la cuenta.';
        } else {
            foundUser.value = data.user;
        }
    } catch {
        searchError.value = 'Fallo de conexión con el servidor.';
    } finally {
        isSearching.value = false;
    }
};

const submitRestore = () => {
    if (!foundUser.value) return;

    restoreForm.post(route('customers.restore', foundUser.value.id), {
        onSuccess: () => {
            isModalOpen.value = false;
            searchPhone.value = '';
            foundUser.value = null;
            searchError.value = '';
        },
    });
};

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
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-xl md:text-2xl font-black tracking-tight text-foreground uppercase">
                        Gestión de Clientes (B2C)
                    </h1>
                    <p class="text-xs text-muted-foreground mt-0.5">
                        Cartera de clientes, control de acceso, scoring y recuperación de cuentas
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        @click="isModalOpen = true"
                        class="px-3.5 py-2 glass-panel border border-white/60 dark:border-white/10 rounded-xl text-xs font-semibold text-foreground hover:bg-foreground/[0.05] transition-all inline-flex items-center gap-1.5 shadow-sm active:scale-95"
                    >
                        <span class="material-symbols-rounded text-[18px]">history</span>
                        <span>Restaurar Cuenta</span>
                    </button>

                    <Link
                        :href="route('customers.create')"
                        class="admin-btn-primary inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider py-2 px-3.5 shadow-sm"
                    >
                        <span class="material-symbols-rounded text-[18px]">person_add</span>
                        <span>Nuevo Cliente</span>
                    </Link>
                </div>
            </div>
        </template>

        <!-- Navegación Unificada del Ecosistema de Usuarios -->
        <UserNavTabs />

        <!-- Barra de Filtros Operativos Apple Glass -->
        <div class="glass-panel p-3.5 md:p-4 rounded-2xl shadow-sm border border-white/60 dark:border-white/10 mb-5">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2.5 items-center">
                <!-- Buscador de Texto -->
                <div class="relative lg:col-span-2">
                    <span class="material-symbols-rounded absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground text-[18px]">search</span>
                    <input
                        v-model="searchFilter"
                        @input="handleSearchInput"
                        type="text"
                        placeholder="Buscar por nombre, correo o teléfono..."
                        class="admin-input pl-9 py-2 text-xs w-full"
                    />
                </div>

                <!-- Filtro Sucursal -->
                <div>
                    <select v-model="branchFilter" class="admin-input py-2 text-xs w-full">
                        <option value="">Todas las Sucursales</option>
                        <option v-for="branch in branches" :key="branch.id" :value="branch.id">
                            {{ branch.name }} {{ branch.city ? `(${branch.city})` : '' }}
                        </option>
                    </select>
                </div>

                <!-- Filtro Estado & Reset -->
                <div class="flex items-center gap-2">
                    <select v-model="statusFilter" class="admin-input py-2 text-xs w-full">
                        <option value="">Todos los Estados</option>
                        <option value="1">Activos</option>
                        <option value="0">Inactivos</option>
                    </select>

                    <button
                        v-if="searchFilter || branchFilter || statusFilter !== ''"
                        @click="resetFilters"
                        title="Limpiar filtros"
                        class="p-2 text-muted-foreground hover:text-foreground border border-border/80 rounded-xl hover:bg-foreground/[0.06] transition-colors shrink-0"
                    >
                        <span class="material-symbols-rounded text-[18px] block">filter_alt_off</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Tabla Densa de Clientes Apple Glass -->
        <div class="glass-card rounded-2xl overflow-hidden shadow-sm border border-white/60 dark:border-white/10">
            <div class="overflow-x-auto">
                <table class="admin-table w-full text-left border-collapse text-xs">
                    <thead>
                        <tr>
                            <th class="admin-table-th">Cliente</th>
                            <th class="admin-table-th">Teléfono</th>
                            <th class="admin-table-th">Sucursal Asignada</th>
                            <th class="admin-table-th text-center">Score Confianza</th>
                            <th class="admin-table-th text-center">Estado</th>
                            <th class="admin-table-th text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!customers.data || customers.data.length === 0">
                            <td colspan="6" class="py-12 text-center text-muted-foreground">
                                <span class="material-symbols-rounded text-4xl mb-2 text-muted-foreground/40 block">person_off</span>
                                <p class="text-xs font-medium">No se encontraron registros de clientes bajo los parámetros seleccionados.</p>
                            </td>
                        </tr>
                        <tr
                            v-for="user in customers.data"
                            :key="user.id"
                            class="admin-table-tr hover:bg-foreground/[0.03] transition-colors"
                        >
                            <!-- Cliente -->
                            <td class="admin-table-td">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-xl bg-primary/10 border border-primary/20 text-primary flex items-center justify-center font-bold text-xs shrink-0 select-none shadow-xs">
                                        {{ user.first_name?.[0]?.toUpperCase() || 'C' }}
                                    </div>
                                    <div class="min-w-0">
                                        <span class="font-bold text-foreground block truncate">{{ user.full_name }}</span>
                                        <span class="text-[11px] font-mono text-muted-foreground block truncate">{{ user.email }}</span>
                                        <span class="text-[10px] text-muted-foreground/70 block">Registrado: {{ user.created_at || 'Reciente' }}</span>
                                    </div>
                                </div>
                            </td>

                            <!-- Teléfono -->
                            <td class="admin-table-td font-mono text-xs">
                                <span class="bg-card/70 px-2.5 py-1 rounded-lg border border-border/70 text-[11px] text-foreground font-mono">
                                    {{ user.phone }}
                                </span>
                            </td>

                            <!-- Sucursal Asignada -->
                            <td class="admin-table-td">
                                <div v-if="user.branch" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl border border-border/70 bg-card/60 text-[11px] font-medium text-foreground backdrop-blur-sm">
                                    <span class="material-symbols-rounded text-[14px] text-primary">store</span>
                                    <span>{{ user.branch.name }}</span>
                                    <span v-if="user.branch.city" class="text-[10px] text-muted-foreground">({{ user.branch.city }})</span>
                                </div>
                                <span v-else class="text-muted-foreground/60 italic text-xs">Sin asignar (Global)</span>
                            </td>

                            <!-- Score Confianza -->
                            <td class="admin-table-td text-center">
                                <span
                                    class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold border shadow-xs"
                                    :class="(user.trust_score ?? 100) >= 80 
                                        ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20' 
                                        : (user.trust_score ?? 100) >= 50 
                                            ? 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/20' 
                                            : 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border-rose-500/20'"
                                >
                                    <span class="material-symbols-rounded text-[13px]">verified_user</span>
                                    <span>{{ user.trust_score ?? 100 }} pts</span>
                                </span>
                            </td>

                            <!-- Estado -->
                            <td class="admin-table-td text-center">
                                <span
                                    class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold border shadow-xs"
                                    :class="user.is_active 
                                        ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20' 
                                        : 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border-rose-500/20'"
                                >
                                    <span class="w-1.5 h-1.5 rounded-full" :class="user.is_active ? 'bg-emerald-500' : 'bg-rose-500'"></span>
                                    <span>{{ user.is_active ? 'Activo' : 'Inactivo' }}</span>
                                </span>
                            </td>

                            <!-- Acciones -->
                            <td class="admin-table-td text-right whitespace-nowrap">
                                <button
                                    type="button"
                                    @click="toggleStatus(user)"
                                    :disabled="statusForm.processing"
                                    class="px-2.5 py-1 text-xs font-semibold rounded-xl border transition-all disabled:opacity-50 active:scale-95 shadow-xs"
                                    :class="user.is_active 
                                        ? 'border-rose-500/30 text-rose-600 hover:bg-rose-500/10 dark:text-rose-400' 
                                        : 'border-emerald-500/30 text-emerald-600 hover:bg-emerald-500/10 dark:text-emerald-400'"
                                >
                                    {{ user.is_active ? 'Desactivar' : 'Activar' }}
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <div class="border-t border-border/40 p-3 bg-card/30">
                <Pagination :meta="customers" />
            </div>
        </div>

        <!-- Modal de Recuperación de Cuenta Eliminada (Frosted Glass) -->
        <div v-if="isModalOpen" class="fixed inset-0 z-[200] flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-neutral-950/60 backdrop-blur-md" @click="closeRestoreModal"></div>

            <div class="relative w-full max-w-md glass-card backdrop-blur-2xl border border-white/60 dark:border-white/10 rounded-2xl shadow-2xl p-6 z-10 animate-in fade-in zoom-in-95 duration-150">
                <div class="flex items-center justify-between mb-4 pb-3 border-b border-border/50">
                    <h2 class="text-sm font-bold text-foreground uppercase tracking-wide flex items-center gap-1.5">
                        <span class="material-symbols-rounded text-lg text-primary">history</span>
                        <span>Restaurar Cuenta Eliminada</span>
                    </h2>
                    <button @click="closeRestoreModal" class="w-7 h-7 rounded-lg hover:bg-foreground/[0.06] text-muted-foreground hover:text-foreground flex items-center justify-center transition-colors">
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
                                placeholder="+591XXXXXXXX"
                                class="admin-input flex-1 font-mono text-xs py-2"
                                :disabled="isSearching || restoreForm.processing"
                                @keyup.enter="searchDeletedCustomer"
                            />
                            <button
                                type="button"
                                @click="searchDeletedCustomer"
                                :disabled="isSearching || restoreForm.processing"
                                class="px-3.5 glass-panel border border-white/60 dark:border-white/10 rounded-xl text-xs font-bold uppercase tracking-wider hover:bg-foreground/[0.06] disabled:opacity-50 inline-flex items-center justify-center shrink-0 active:scale-95"
                            >
                                {{ isSearching ? 'Buscando...' : 'Buscar' }}
                            </button>
                        </div>
                        <p v-if="searchError" class="text-xs text-rose-600 font-medium mt-1 flex items-center gap-1">
                            <span class="material-symbols-rounded text-sm shrink-0">error</span>
                            <span>{{ searchError }}</span>
                        </p>
                    </div>

                    <div v-if="foundUser" class="p-3.5 bg-card/60 border border-border/60 rounded-xl space-y-3 animate-in fade-in duration-75">
                        <div class="text-xs space-y-1">
                            <div class="text-muted-foreground uppercase tracking-wider font-semibold text-[10px]">Registro Localizado</div>
                            <div class="text-foreground font-bold text-sm">{{ foundUser.full_name || `${foundUser.first_name || ''} ${foundUser.last_name || ''}` }}</div>
                            <div class="text-muted-foreground font-medium font-mono text-[11px]">{{ foundUser.email }}</div>
                            <div class="text-muted-foreground font-mono text-[11px]">{{ foundUser.phone }}</div>
                        </div>

                        <div class="pt-2 border-t border-border/40">
                            <button
                                type="button"
                                @click="submitRestore"
                                :disabled="restoreForm.processing"
                                class="w-full admin-btn-primary inline-flex items-center justify-center gap-1.5 text-xs font-bold uppercase tracking-wider py-2.5 rounded-xl shadow-sm active:scale-95"
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
