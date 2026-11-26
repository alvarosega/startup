<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const props = defineProps({
    branch: {
        type: Object,
        required: true,
    },
});

const page = usePage();
const user = computed(() => page.props.auth?.user);
const roles = computed(() => user.value?.roles || []);
const isSuperAdmin = computed(() => user.value?.is_super_admin || roles.value.includes('super_admin'));
const permissions = computed(() => user.value?.permissions || {});

const can = (perm) => isSuperAdmin.value || !!permissions.value[perm];

const tabs = computed(() => [
    {
        name: 'Parámetros & Cobertura',
        href: route('admin.operations.branches.edit', props.branch.id),
        pattern: 'admin.operations.branches.edit',
        icon: 'tune',
        permission: can('manage_branches'),
    },
    {
        name: 'Caja Diaria',
        href: route('admin.operations.branches.cash.index', props.branch.id),
        pattern: 'admin.operations.branches.cash.*',
        icon: 'point_of_sale',
        permission: can('manage_branch_cash') || can('manage_branches'),
    },
    {
        name: 'Horarios Semanales',
        href: route('admin.operations.branches.schedules.index', props.branch.id),
        pattern: 'admin.operations.branches.schedules.*',
        icon: 'schedule',
        permission: can('manage_branch_schedules') || can('manage_branches'),
    },
    {
        name: 'Feriados & Excepciones',
        href: route('admin.operations.branches.schedule-exceptions.index', props.branch.id),
        pattern: 'admin.operations.branches.schedule-exceptions.*',
        icon: 'event_busy',
        permission: can('manage_branch_schedules') || can('manage_branches'),
    },
    {
        name: 'Personal Asignado',
        href: route('staff.index', { branch_id: props.branch.id }),
        pattern: 'staff.*',
        icon: 'badge',
        permission: can('manage_users'),
    },
].filter(tab => tab.permission));

const isActive = (pattern) => route().current(pattern);
</script>

<template>
    <div class="glass-panel p-1.5 rounded-2xl mb-5 w-fit max-w-full overflow-x-auto select-none shadow-sm flex items-center gap-1.5 backdrop-blur-xl border border-white/60 dark:border-white/10">
        <!-- Retorno al listado maestro -->
        <Link
            :href="route('admin.operations.branches.index')"
            class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold text-muted-foreground hover:text-foreground hover:bg-foreground/[0.04] transition-all"
            title="Volver a la lista de sucursales"
        >
            <span class="material-symbols-rounded text-[18px]">arrow_back</span>
            <span class="hidden sm:inline">Sucursales</span>
        </Link>

        <div class="h-4 w-[1px] bg-border/60 mx-0.5"></div>

        <!-- Pestañas contextuales de la sucursal -->
        <Link
            v-for="tab in tabs"
            :key="tab.name"
            :href="tab.href"
            class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold transition-all duration-200 whitespace-nowrap"
            :class="[
                isActive(tab.pattern)
                    ? 'bg-primary/12 text-primary border border-primary/25 shadow-sm font-bold scale-[1.01]'
                    : 'text-muted-foreground hover:text-foreground hover:bg-foreground/[0.04]'
            ]"
        >
            <span
                class="material-symbols-rounded text-[18px]"
                :style="isActive(tab.pattern) ? { fontVariationSettings: `'FILL' 1` } : {}"
                :class="isActive(tab.pattern) ? 'text-primary' : 'text-muted-foreground'"
            >
                {{ tab.icon }}
            </span>
            <span>{{ tab.name }}</span>
        </Link>
    </div>
</template>
