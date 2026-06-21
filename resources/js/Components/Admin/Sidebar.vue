<script setup>
import { computed, ref, onMounted } from 'vue';
import { usePage, Link } from '@inertiajs/vue3';
import SidebarLink from '@/Components/Admin/SidebarLink.vue';
import ThemeToggler from '@/Components/Base/ThemeToggler.vue';

const activeMobileMenu = ref(null);
const page = usePage();
const user = computed(() => page.props.auth?.user);
const roles = computed(() => user.value?.roles || []);
const isSuperAdmin = computed(() => roles.value.includes('super_admin'));

const isDevelopment = ref(false);
onMounted(() => {
    isDevelopment.value = ['localhost', '127.0.0.1', 'test', 'dev'].some(host => 
        window.location.hostname.includes(host)
    );
});

const navigationMenu = [
    { name: 'Dashboard', route: 'admin.dashboard.index', pattern: 'admin.dashboard.*', icon: 'dashboard', group: 'root', permission: true },
    
    // Grupo: Stock
    { name: 'Ingresos', route: 'admin.purchases.index', pattern: 'admin.purchases.*', icon: 'move_to_inbox', group: 'inv', permission: isSuperAdmin },
    { name: 'Stock Base', route: 'admin.inventory.index', pattern: 'admin.inventory.*', icon: 'warehouse', group: 'inv', permission: isSuperAdmin },
    { name: 'Transformaciones', route: 'admin.transformations.index', pattern: 'admin.transformations.*', icon: 'precision_manufacturing', group: 'inv', permission: isSuperAdmin },
    
    // Grupo: Flujos
    { name: 'Transferencias', route: 'admin.transfers.index', pattern: 'admin.transfers.*', icon: 'local_shipping', group: 'mov', permission: isSuperAdmin },
    { name: 'Bajas', route: 'admin.removals.index', pattern: 'admin.removals.*', icon: 'delete_forever', group: 'mov', permission: isSuperAdmin },
    { name: 'Órdenes', route: 'admin.orders.index', pattern: 'admin.orders.*', icon: 'receipt_long', group: 'mov', permission: isSuperAdmin },
    { name: 'Radar (Vivo)', route: 'admin.logistics.monitor', pattern: 'admin.logistics.*', icon: 'radar', group: 'mov', permission: isSuperAdmin },
    
    // Grupo: Catálogo
    { name: 'Productos', route: 'admin.products.index', pattern: 'admin.products.*', icon: 'label', group: 'com', permission: isSuperAdmin },
    { name: 'Zonas', route: 'admin.market-zones.index', pattern: 'admin.market-zones.*', icon: 'map', group: 'com', permission: isSuperAdmin },
    { name: 'Combos', route: 'admin.bundles.index', pattern: 'admin.bundles.*', icon: 'widgets', group: 'com', permission: isSuperAdmin },
    { name: 'Marcas', route: 'admin.brands.index', pattern: 'admin.brands.*', icon: 'branding_watermark', group: 'com', permission: isSuperAdmin },
    { name: 'Categorías', route: 'admin.categories.index', pattern: 'admin.categories.*', icon: 'account_tree', group: 'com', permission: isSuperAdmin },
    { name: 'Proveedores', route: 'admin.providers.index', pattern: 'admin.providers.*', icon: 'factory', group: 'com', permission: isSuperAdmin },
    { name: 'Retail Media', route: 'admin.retail-media.ad-creatives.index', pattern: 'admin.retail-media.*', icon: 'campaign', group: 'com', permission: isSuperAdmin },
    
    // Grupo: Gestión
    { name: 'Sucursales', route: 'admin.branches.index', pattern: 'admin.branches.*', icon: 'store', group: 'ges', permission: isSuperAdmin },
    { name: 'Conductores', route: 'admin.drivers.index', pattern: 'admin.drivers.*', icon: 'badge', group: 'ges', permission: isSuperAdmin },
    { name: 'Equipo', route: 'admin.users.index', pattern: 'admin.users.*', icon: 'group', group: 'ges', permission: isSuperAdmin }
];

const filteredMenu = computed(() => {
    return navigationMenu.filter(item => {
        if (typeof item.permission === 'boolean') return item.permission;
        return item.permission.value;
    });
});

const mobileGroups = computed(() => ({
    ges: { label: 'Gestión', items: filteredMenu.value.filter(i => i.group === 'ges') },
    inv: { label: 'Stock', items: filteredMenu.value.filter(i => i.group === 'inv') },
    mov: { label: 'Flujos', items: filteredMenu.value.filter(i => i.group === 'mov') },
    com: { label: 'Catálogo', items: filteredMenu.value.filter(i => i.group === 'com') }
}));

const toggleMobileMenu = (groupKey) => {
    activeMobileMenu.value = activeMobileMenu.value === groupKey ? null : groupKey;
};

const closeMobileMenu = () => {
    activeMobileMenu.value = null;
};

const isActiveRoute = (pattern) => route().current(pattern);

const isGroupActive = (groupKey) => {
    return filteredMenu.value.some(item => item.group === groupKey && isActiveRoute(item.pattern));
};
</script>

<template>
    <aside class="hidden md:flex flex-col fixed top-0 left-0 h-full w-[72px] bg-card border-r border-border z-50 overflow-visible justify-between select-none">
        
    <div class="flex flex-col w-full items-center overflow-visible">
        <div class="w-full h-14 flex items-center justify-center border-b border-border/60 shrink-0 mb-3">
            <span class="text-base font-black italic tracking-wider text-primary">DU</span>
        </div>

            <div v-if="user" class="relative group flex items-center justify-center w-full h-12 mb-2">
                <div class="w-9 h-9 bg-primary/10 text-primary border border-primary/20 rounded-md flex items-center justify-center font-bold text-sm transition-colors duration-100 hover:bg-primary/20 cursor-default">
                    <span>{{ user?.first_name?.[0]?.toUpperCase() || 'U' }}</span>
                </div>
                
                <div class="fixed left-[76px] hidden group-hover:flex flex-col p-2.5 bg-card border border-border rounded-md shadow-flat z-50 pointer-events-none min-w-[140px]">
                    <span class="text-xs font-semibold text-foreground leading-tight">
                        {{ user?.first_name }} {{ user?.last_name || '' }}
                    </span>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-primary mt-1 leading-none">
                        {{ user?.roles?.[0]?.replace('_', ' ') || 'Staff' }}
                    </span>
                </div>
            </div>

            <nav class="w-full h-[calc(100vh-190px)] overflow-y-auto overflow-x-visible no-scrollbar py-1 border-t border-border/40">
                <SidebarLink 
                    v-for="item in filteredMenu" 
                    :key="item.route"
                    :href="route(item.route)"
                    :active="isActiveRoute(item.pattern)"
                    :title="item.name"
                    :icon="item.icon"
                />
            </nav>
        </div>

        <div class="flex flex-col w-full items-center border-t border-border shrink-0 bg-card z-10 pb-2">
            <div class="w-full h-12 flex items-center justify-center text-foreground/80 hover:text-primary transition-colors duration-100">
                <ThemeToggler class="p-2 rounded-md hover:bg-neutral-100 dark:hover:bg-neutral-800" />
            </div>

            <Link 
                :href="route('admin.logout')" 
                method="post" 
                as="button" 
                class="group relative flex items-center justify-center h-12 w-full text-destructive hover:bg-destructive/10 transition-colors duration-75"
            >
                <span class="material-symbols-rounded text-[20px]">logout</span>
                <span class="fixed left-[76px] hidden group-hover:block px-2.5 py-1 bg-card border border-destructive/30 rounded-md text-xs font-medium text-destructive shadow-flat whitespace-nowrap z-50 pointer-events-none">
                    Cerrar Sesión
                </span>
            </Link>

            <div v-if="isDevelopment" class="text-[8px] font-bold tracking-widest text-neutral-400 bg-neutral-100 dark:bg-neutral-800/60 border border-border px-1 rounded-sm mt-1 select-none">
                DEV
            </div>
        </div>
    </aside>

    <div class="md:hidden">
        <div 
            v-if="activeMobileMenu" 
            @click="closeMobileMenu" 
            class="fixed inset-0 bg-neutral-950/40 z-40 cursor-pointer transition-opacity duration-75"
        ></div>

        <div 
            v-if="activeMobileMenu" 
            class="fixed bottom-[82px] left-3 right-3 bg-card z-50 p-4 flex flex-col max-h-[60vh] rounded-md border border-border shadow-flat"
        >
            <div class="flex justify-between items-center mb-3 pb-2 border-b border-border">
                <h3 class="text-xs font-bold text-foreground uppercase tracking-wide">
                    {{ mobileGroups[activeMobileMenu]?.label }}
                </h3>
                <button @click="closeMobileMenu" class="p-1 rounded hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors duration-75">
                    <span class="material-symbols-rounded text-[18px] text-muted-foreground block">close</span>
                </button>
            </div>

            <div v-if="activeMobileMenu === 'ges' && user" class="flex items-center justify-between p-2 mb-3 bg-neutral-100/60 dark:bg-neutral-800/40 border border-border/60 rounded-md">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 bg-primary/10 text-primary border border-primary/20 rounded-md flex items-center justify-center font-bold text-xs">
                        {{ user?.first_name?.[0]?.toUpperCase() || 'U' }}
                    </div>
                    <div class="flex flex-col">
                        <span class="text-xs font-semibold text-foreground leading-none">
                            {{ user?.first_name }}
                        </span>
                        <span class="text-[9px] font-bold uppercase tracking-wider text-primary mt-1 leading-none">
                            {{ user?.roles?.[0]?.replace('_', ' ') || 'Staff' }}
                        </span>
                    </div>
                </div>
                <ThemeToggler class="p-1.5 text-foreground border border-border bg-card rounded-md shadow-sm" />
            </div>

            <div class="flex-1 overflow-y-auto grid grid-cols-2 gap-2 no-scrollbar">
                <Link 
                    v-for="subItem in mobileGroups[activeMobileMenu]?.items"
                    :key="subItem.route"
                    @click="closeMobileMenu" 
                    :href="route(subItem.route)" 
                    :class="[isActiveRoute(subItem.pattern) ? 'border-primary bg-primary/5 text-foreground' : 'border-border bg-background text-muted-foreground']"
                    class="border p-3 rounded-md flex flex-col items-center justify-center gap-1.5 transition-colors duration-75"
                >
                    <span 
                        class="material-symbols-rounded text-[20px]"
                        :style="{ fontVariationSettings: isActiveRoute(subItem.pattern) ? `'FILL' 1` : `'FILL' 0` }"
                    >
                        {{ subItem.icon }}
                    </span>
                    <span class="text-xs font-medium text-center leading-none">{{ subItem.name }}</span>
                </Link>

                <Link 
                    v-if="activeMobileMenu === 'ges'"
                    :href="route('admin.logout')" 
                    method="post" 
                    as="button" 
                    class="border border-destructive/30 bg-destructive/5 text-destructive p-3 rounded-md flex flex-col items-center justify-center gap-1.5 col-span-2 transition-colors duration-75"
                >
                    <span class="material-symbols-rounded text-[20px]">logout</span>
                    <span class="text-xs font-medium text-center leading-none">Cerrar Sesión</span>
                </Link>
            </div>
        </div>
    </div>

    <nav class="md:hidden fixed bottom-0 left-0 right-0 h-[72px] bg-card border-t border-border z-40 grid grid-cols-5 px-1 items-center shadow-flat select-none">
        <button @click="toggleMobileMenu('ges')" class="flex flex-col items-center justify-center h-full transition-colors duration-75" :class="[activeMobileMenu === 'ges' || isGroupActive('ges') ? 'text-primary' : 'text-muted-foreground']">
            <span class="material-symbols-rounded text-[20px]" :style="(activeMobileMenu === 'ges' || isGroupActive('ges')) ? { fontVariationSettings: `'FILL' 1` } : {}">settings</span>
            <span class="text-[9px] font-medium mt-1">Gestión</span>
        </button>
        
        <button @click="toggleMobileMenu('inv')" class="flex flex-col items-center justify-center h-full transition-colors duration-75" :class="[activeMobileMenu === 'inv' || isGroupActive('inv') ? 'text-primary' : 'text-muted-foreground']">
            <span class="material-symbols-rounded text-[20px]" :style="(activeMobileMenu === 'inv' || isGroupActive('inv')) ? { fontVariationSettings: `'FILL' 1` } : {}">inventory</span>
            <span class="text-[9px] font-medium mt-1">Stock</span>
        </button>
        
        <div class="flex items-center justify-center -mt-3">
            <Link :href="route('admin.dashboard.index')" @click="closeMobileMenu" :class="[isActiveRoute('admin.dashboard.*') ? 'bg-primary text-white' : 'bg-neutral-200 text-neutral-700 dark:bg-neutral-800 dark:text-neutral-300']" class="w-11 h-11 rounded-md flex items-center justify-center shadow-flat transition-transform duration-75 active:scale-95">
                <span class="material-symbols-rounded text-[20px]" :style="isActiveRoute('admin.dashboard.*') ? { fontVariationSettings: `'FILL' 1` } : {}">home</span>
            </Link>
        </div>

        <button @click="toggleMobileMenu('mov')" class="flex flex-col items-center justify-center h-full transition-colors duration-75" :class="[activeMobileMenu === 'mov' || isGroupActive('mov') ? 'text-primary' : 'text-muted-foreground']">
            <span class="material-symbols-rounded text-[20px]" :style="(activeMobileMenu === 'mov' || isGroupActive('mov')) ? { fontVariationSettings: `'FILL' 1` } : {}">swap_horiz</span>
            <span class="text-[9px] font-medium mt-1">Flujos</span>
        </button>
        
        <button @click="toggleMobileMenu('com')" class="flex flex-col items-center justify-center h-full transition-colors duration-75" :class="[activeMobileMenu === 'com' || isGroupActive('com') ? 'text-primary' : 'text-muted-foreground']">
            <span class="material-symbols-rounded text-[20px]" :style="(activeMobileMenu === 'com' || isGroupActive('com')) ? { fontVariationSettings: `'FILL' 1` } : {}">storefront</span>
            <span class="text-[9px] font-medium mt-1">Catálogo</span>
        </button>
    </nav>
</template>