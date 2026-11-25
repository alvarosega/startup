<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const user = computed(() => page.props.auth?.user);
const roles = computed(() => user.value?.roles || []);
const isSuperAdmin = computed(() => user.value?.is_super_admin || roles.value.includes('super_admin'));
const permissions = computed(() => user.value?.permissions || {});

const can = (perm) => isSuperAdmin.value || !!permissions.value[perm];

const tabs = computed(() => [
    {
        name: 'Personal y Operadores',
        route: 'staff.index',
        pattern: 'staff.*',
        icon: 'shield_person',
        permission: can('manage_users'),
    },
    {
        name: 'Clientes (B2C)',
        route: 'customers.index',
        pattern: 'customers.*',
        icon: 'group',
        permission: can('manage_users'),
    },
    {
        name: 'Repartidores',
        route: 'drivers.index',
        pattern: 'drivers.*',
        icon: 'two_wheeler',
        permission: can('manage_drivers') || can('manage_users'),
    },
].filter(tab => tab.permission));

const isActive = (pattern) => route().current(pattern);
</script>

<template>
    <div class="flex items-center gap-1.5 p-1 bg-neutral-100/80 dark:bg-neutral-900/80 border border-border rounded-lg mb-4 w-fit max-w-full overflow-x-auto select-none">
        <Link
            v-for="tab in tabs"
            :key="tab.route"
            :href="route(tab.route)"
            class="inline-flex items-center gap-2 px-3 py-1.5 rounded-md text-xs font-semibold transition-all duration-150 whitespace-nowrap"
            :class="[
                isActive(tab.pattern)
                    ? 'bg-card text-foreground shadow-sm border border-border/80 font-bold'
                    : 'text-muted-foreground hover:text-foreground hover:bg-neutral-200/50 dark:hover:bg-neutral-800/50'
            ]"
        >
            <span
                class="material-symbols-rounded text-[18px]"
                :class="isActive(tab.pattern) ? 'text-primary' : 'text-muted-foreground'"
            >
                {{ tab.icon }}
            </span>
            <span>{{ tab.name }}</span>
        </Link>
    </div>
</template>
