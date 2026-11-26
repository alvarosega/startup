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
    <div class="glass-panel p-1.5 rounded-2xl mb-5 w-fit max-w-full overflow-x-auto select-none shadow-sm flex items-center gap-1.5 backdrop-blur-xl border border-white/60 dark:border-white/10">
        <Link
            v-for="tab in tabs"
            :key="tab.route"
            :href="route(tab.route)"
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
