<script setup>
import { ref, computed } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    branches: {
        type: Array,
        required: true
    }
});

const searchQuery = ref('');
const pausingBranchId = ref(null);

const filteredBranches = computed(() => {
    if (!searchQuery.value) return props.branches;
    const query = searchQuery.value.toLowerCase().trim();
    return props.branches.filter(b => 
        b.name?.toLowerCase().includes(query) || 
        b.city?.toLowerCase().includes(query) ||
        b.address?.toLowerCase().includes(query)
    );
});

// Métricas de telemetría operativa
const totalBranches = computed(() => props.branches.length);
const activeBranches = computed(() => props.branches.filter(b => b.is_active && !b.is_operational_paused).length);
const totalStaff = computed(() => props.branches.reduce((acc, b) => acc + (b.staff_count || 0), 0));

const togglePause = (id) => {
    pausingBranchId.value = id;
    router.patch(route('admin.operations.branches.toggle-pause', id), {}, {
        preserveScroll: true,
        onFinish: () => {
            pausingBranchId.value = null;
        }
    });
};

const deleteBranch = (branch) => {
    if (branch.is_default) {
        alert('No es posible eliminar el nodo principal del sistema.');
        return;
    }
    if (window.confirm(`¿Confirmar extracción de "${branch.name}" de la circulación logística?`)) {
        router.delete(route('admin.operations.branches.destroy', branch.id), {
            preserveScroll: true
        });
    }
};
</script>

<template>
    <AdminLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 w-full">
                <div>
                    <h1 class="text-xl md:text-2xl font-black tracking-tight text-foreground uppercase">
                        Sucursales y Nodos Logísticos
                    </h1>
                    <p class="text-xs text-muted-foreground mt-0.5">
                        Centros de despacho, geocercas poligonales, tarifas zonales y control operativo
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <Link 
                        :href="route('admin.operations.branches.create')"
                        class="admin-btn-primary inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider py-2 px-3.5 shadow-sm"
                    >
                        <span class="material-symbols-rounded text-[18px]">add_location_alt</span>
                        <span>Nueva Sucursal</span>
                    </Link>
                </div>
            </div>
        </template>

        <!-- Tarjetas de Telemetría Operativa Apple Glass -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-5">
            <div class="glass-card p-4 rounded-2xl border border-white/60 dark:border-white/10 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground block">Nodos en Red</span>
                    <span class="text-2xl font-black text-foreground mt-0.5 block font-mono">{{ totalBranches }}</span>
                </div>
                <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
                    <span class="material-symbols-rounded text-[22px]">store</span>
                </div>
            </div>

            <div class="glass-card p-4 rounded-2xl border border-white/60 dark:border-white/10 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground block">Despacho Online Activo</span>
                    <span class="text-2xl font-black text-foreground mt-0.5 block font-mono text-success">{{ activeBranches }}</span>
                </div>
                <div class="w-10 h-10 rounded-xl bg-success/10 text-success flex items-center justify-center">
                    <span class="material-symbols-rounded text-[22px]">wifi_tethering</span>
                </div>
            </div>

            <div class="glass-card p-4 rounded-2xl border border-white/60 dark:border-white/10 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground block">Personal Total Asignado</span>
                    <span class="text-2xl font-black text-foreground mt-0.5 block font-mono text-info">{{ totalStaff }}</span>
                </div>
                <div class="w-10 h-10 rounded-xl bg-info/10 text-info flex items-center justify-center">
                    <span class="material-symbols-rounded text-[22px]">group</span>
                </div>
            </div>
        </div>

        <!-- Barra de Búsqueda y Filtro Rápido -->
        <div class="glass-panel p-3.5 rounded-2xl shadow-sm border border-white/60 dark:border-white/10 mb-5">
            <div class="relative max-w-md">
                <span class="material-symbols-rounded absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground text-[18px]">search</span>
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Filtrar por nombre de sucursal, ciudad o dirección..."
                    class="admin-input pl-9 py-2 text-xs w-full"
                />
            </div>
        </div>

        <!-- Matriz de Sucursales Logísticas -->
        <div class="glass-panel rounded-2xl shadow-sm border border-white/60 dark:border-white/10 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th class="admin-table-th">Sucursal / Nodo</th>
                            <th class="admin-table-th">Ciudad</th>
                            <th class="admin-table-th">Equipo & Gerencia</th>
                            <th class="admin-table-th text-center">Capacidad</th>
                            <th class="admin-table-th text-center">Recepción Online</th>
                            <th class="admin-table-th text-center">Estado</th>
                            <th class="admin-table-th text-right">Ajustes & Operaciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="filteredBranches.length === 0">
                            <td colspan="7" class="admin-table-td text-center text-muted-foreground py-10 font-normal">
                                <div class="flex flex-col items-center justify-center gap-1.5">
                                    <span class="material-symbols-rounded text-[32px] text-muted-foreground/60">storefront</span>
                                    <span>No se encontraron sucursales operativas coincidentes.</span>
                                </div>
                            </td>
                        </tr>
                        <tr v-for="branch in filteredBranches" :key="branch.id" class="admin-table-tr">
                            <td class="admin-table-td">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-xl bg-primary/10 text-primary border border-primary/20 flex items-center justify-center shrink-0">
                                        <span class="material-symbols-rounded text-[18px]">store</span>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-bold text-foreground text-xs flex items-center gap-1.5">
                                            <span>{{ branch.name }}</span>
                                            <span v-if="branch.is_default" class="badge-info text-[9px] py-0 px-1.5">Principal</span>
                                        </div>
                                        <div v-if="branch.address" class="text-[11px] text-muted-foreground truncate max-w-[200px]" :title="branch.address">
                                            {{ branch.address }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <td class="admin-table-td">
                                <span class="text-xs font-semibold text-foreground/90">{{ branch.city }}</span>
                                <span v-if="branch.phone" class="block text-[11px] text-muted-foreground font-mono">{{ branch.phone }}</span>
                            </td>

                            <td class="admin-table-td">
                                <Link 
                                    :href="route('staff.index', { branch_id: branch.id })"
                                    class="group flex flex-col hover:opacity-85 transition-opacity"
                                    title="Ver personal asignado a esta sucursal"
                                >
                                    <div class="flex items-center gap-1.5 font-semibold text-xs text-foreground group-hover:text-primary">
                                        <span class="material-symbols-rounded text-[15px] text-muted-foreground group-hover:text-primary">badge</span>
                                        <span>{{ branch.staff_count }} {{ branch.staff_count === 1 ? 'operador' : 'operadores' }}</span>
                                    </div>
                                    <div v-if="branch.managers && branch.managers.length > 0" class="mt-1 flex flex-wrap gap-1">
                                        <span 
                                            v-for="(manager, idx) in branch.managers" 
                                            :key="idx" 
                                            class="inline-flex items-center text-[10px] font-medium bg-primary/10 text-primary border border-primary/20 px-1.5 py-0.5 rounded-full"
                                        >
                                            <span class="material-symbols-rounded text-[11px] mr-0.5">manage_accounts</span>
                                            {{ manager }}
                                        </span>
                                    </div>
                                    <div v-else class="text-[10px] text-muted-foreground italic mt-0.5">
                                        Sin gerente asignado
                                    </div>
                                </Link>
                            </td>

                            <td class="admin-table-td text-center font-mono text-xs">
                                <span v-if="branch.max_active_orders" class="font-bold text-foreground">
                                    {{ branch.max_active_orders }} <span class="text-[10px] text-muted-foreground font-sans font-normal">pedidos</span>
                                </span>
                                <span v-else class="text-muted-foreground text-[11px]">Ilimitada</span>
                            </td>

                            <td class="admin-table-td text-center">
                                <button
                                    type="button"
                                    @click="togglePause(branch.id)"
                                    :disabled="pausingBranchId === branch.id"
                                    class="inline-flex items-center gap-1 px-2.5 py-1 text-[11px] font-bold rounded-full transition-all cursor-pointer active:scale-95 border"
                                    :class="branch.is_operational_paused 
                                        ? 'bg-warning/15 text-warning border-warning/30 hover:bg-warning/25' 
                                        : 'bg-success/15 text-success border-success/30 hover:bg-success/25'"
                                    :title="branch.is_operational_paused ? 'Clic para reactivar recepción online' : 'Clic para pausar recepción de pedidos'"
                                >
                                    <span class="w-1.5 h-1.5 rounded-full" :class="branch.is_operational_paused ? 'bg-warning animate-pulse' : 'bg-success'"></span>
                                    <span>{{ branch.is_operational_paused ? 'Pausado' : 'Recibiendo' }}</span>
                                </button>
                            </td>

                            <td class="admin-table-td text-center">
                                <span :class="branch.is_active ? 'badge-success' : 'badge-error'">
                                    {{ branch.is_active ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>

                            <td class="admin-table-td text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <Link
                                        :href="route('admin.operations.branches.cash.index', branch.id)"
                                        title="Arqueos y Caja Diaria"
                                        class="p-1.5 rounded-xl border border-border/80 text-muted-foreground hover:text-foreground hover:bg-foreground/[0.05] transition-all inline-flex items-center"
                                    >
                                        <span class="material-symbols-rounded text-[17px]">point_of_sale</span>
                                    </Link>

                                    <Link
                                        :href="route('admin.operations.branches.schedules.index', branch.id)"
                                        title="Horarios Semanales"
                                        class="p-1.5 rounded-xl border border-border/80 text-muted-foreground hover:text-foreground hover:bg-foreground/[0.05] transition-all inline-flex items-center"
                                    >
                                        <span class="material-symbols-rounded text-[17px]">schedule</span>
                                    </Link>

                                    <Link
                                        :href="route('admin.operations.branches.schedule-exceptions.index', branch.id)"
                                        title="Feriados y Excepciones"
                                        class="p-1.5 rounded-xl border border-border/80 text-muted-foreground hover:text-foreground hover:bg-foreground/[0.05] transition-all inline-flex items-center"
                                    >
                                        <span class="material-symbols-rounded text-[17px]">event_busy</span>
                                    </Link>

                                    <Link
                                        :href="route('admin.operations.branches.edit', branch.id)"
                                        title="Configurar Parámetros y Geocerca"
                                        class="px-2.5 py-1 bg-primary text-primary-foreground text-xs font-semibold rounded-xl hover:brightness-110 transition-all inline-flex items-center gap-1"
                                    >
                                        <span class="material-symbols-rounded text-[15px]">tune</span>
                                        <span>Editar</span>
                                    </Link>

                                    <button
                                        v-if="!branch.is_default"
                                        type="button"
                                        @click="deleteBranch(branch)"
                                        title="Extraer de circulación"
                                        class="p-1.5 rounded-xl text-destructive hover:bg-destructive/10 transition-all inline-flex items-center border border-destructive/20"
                                    >
                                        <span class="material-symbols-rounded text-[17px]">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>
