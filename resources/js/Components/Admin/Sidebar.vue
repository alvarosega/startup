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
    // Grupo: Stock
    { name: 'Stock Base', route: 'admin.inventory.index', pattern: 'admin.inventory.*', icon: 'warehouse', group: 'stock', permission: isSuperAdmin },
    { name: 'Precios Masivos', route: 'admin.prices.index', pattern: 'admin.prices.*', icon: 'payments', group: 'stock', permission: isSuperAdmin },
    { name: 'Ingresos', route: 'admin.purchases.index', pattern: 'admin.purchases.*', icon: 'move_to_inbox', group: 'stock', permission: isSuperAdmin },
    { name: 'Transformaciones', route: 'admin.transformations.index', pattern: 'admin.transformations.*', icon: 'precision_manufacturing', group: 'stock', permission: isSuperAdmin },
    
    // Grupo: Logística
    { name: 'Órdenes', route: 'admin.orders.index', pattern: 'admin.orders.*', icon: 'receipt_long', group: 'logistica', permission: isSuperAdmin },
    { name: 'Radar (Vivo)', route: 'admin.logistics.monitor', pattern: 'admin.logistics.*', icon: 'radar', group: 'logistica', permission: isSuperAdmin },
    { name: 'Transferencias', route: 'admin.transfers.index', pattern: 'admin.transfers.*', icon: 'local_shipping', group: 'logistica', permission: isSuperAdmin },
    { name: 'Bajas', route: 'admin.removals.index', pattern: 'admin.removals.*', icon: 'delete_forever', group: 'logistica', permission: isSuperAdmin },
    
    // Grupo: Catálogo
    { name: 'Productos', route: 'admin.catalog.products.index', pattern: 'admin.catalog.products.*', icon: 'label', group: 'catalogo', permission: isSuperAdmin },
    { name: 'Categorías', route: 'admin.catalog.categories.index', pattern: 'admin.catalog.categories.*', icon: 'account_tree', group: 'catalogo', permission: isSuperAdmin },
    { name: 'Marcas', route: 'admin.catalog.brands.index', pattern: 'admin.catalog.brands.*', icon: 'branding_watermark', group: 'catalogo', permission: isSuperAdmin },
    { name: 'Proveedores', route: 'admin.operations.providers.index', pattern: 'admin.operations.providers.*', icon: 'factory', group: 'catalogo', permission: isSuperAdmin },
    
    // Grupo: Operativa
    { name: 'Sucursales', route: 'admin.operations.branches.index', pattern: 'admin.operations.branches.*', icon: 'store', group: 'operativa', permission: isSuperAdmin },
    { name: 'Conductores', route: 'admin.drivers.index', pattern: 'admin.drivers.*', icon: 'badge', group: 'operativa', permission: isSuperAdmin },
    { name: 'Equipo', route: 'admin.users.index', pattern: 'admin.users.*', icon: 'group', group: 'operativa', permission: isSuperAdmin },

    // Grupo: Marketing
    { name: 'Combos', route: 'admin.bundles.index', pattern: 'admin.bundles.*', icon: 'widgets', group: 'marketing', permission: isSuperAdmin },
    { name: 'Retail Media', route: 'admin.retail-media.ad-creatives.index', pattern: 'admin.retail-media.*', icon: 'campaign', group: 'marketing', permission: isSuperAdmin }
];

const filteredMenu = computed(() => {
    return navigationMenu.filter(item => {
        if (typeof item.permission === 'boolean') return item.permission;
        return item.permission.value;
    });
});

// Agrupación y mapeo simétrico para la botonera del Dock Móvil
const mobileGroups = computed(() => ({
    stock: { label: 'Stock e Inventarios', items: filteredMenu.value.filter(i => i.group === 'stock') },
    logistica: { label: 'Logística y Flujos', items: filteredMenu.value.filter(i => i.group === 'logistica') },
    catalogo: { label: 'Matriz de Catálogo', items: filteredMenu.value.filter(i => i.group === 'catalogo') },
    operativa: { label: 'Operaciones & Marketing', items: filteredMenu.value.filter(i => i.group === 'operativa' || i.group === 'marketing') }
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

const isMobileGroupActive = (groupKey) => {
    if (groupKey === 'operativa') {
        return isGroupActive('operativa') || isGroupActive('marketing');
    }
    return isGroupActive(groupKey);
};
</script>

<template>
    <aside class="hidden md:flex flex-col fixed top-0 left-0 h-full w-[72px] bg-card border-r border-border z-50 overflow-visible justify-between select-none">
        
        <div class="flex flex-col w-full items-center overflow-visible">
            <Link :href="route('admin.dashboard.index')" 
                  :class="[isActiveRoute('admin.dashboard.*') ? 'bg-neutral-100 dark:bg-neutral-800 text-primary border-b border-primary' : 'border-b border-border/60 hover:bg-neutral-100 dark:hover:bg-neutral-900']"
                  class="w-full h-14 flex flex-col items-center justify-center shrink-0 mb-3 transition-colors duration-75 relative group">
                <span class="text-base font-black italic tracking-wider text-primary">DU</span>
                <span class="fixed left-[76px] hidden group-hover:block px-2.5 py-1 bg-card border border-border rounded-md text-[10px] font-bold uppercase tracking-wider text-foreground shadow-flat whitespace-nowrap z-50 pointer-events-none">
                    Dashboard Central
                </span>
            </Link>

            <div v-if="user" class="relative group flex items-center justify-center w-full h-12 mb-2 shrink-0">
                <div class="w-9 h-9 bg-primary/10 text-primary border border-primary/20 rounded-md flex items-center justify-center font-bold text-sm cursor-default">
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

            <nav class="w-full h-[calc(100vh-210px)] overflow-y-auto overflow-x-visible no-scrollbar py-1 border-t border-border/40 flex flex-col items-center">
                <template v-for="(groupKey, gIndex) in ['stock', 'logistica', 'catalogo', 'operativa', 'marketing']" :key="groupKey">
                    
                    <div v-if="gIndex > 0" class="w-8 border-t border-border/50 my-2 shrink-0"></div>
                    
                    <SidebarLink 
                        v-for="item in filteredMenu.filter(i => i.group === groupKey)" 
                        :key="item.route"
                        :href="route(item.route)"
                        :active="isActiveRoute(item.pattern)"
                        :title="item.name"
                        :icon="item.icon"
                    />
                </template>
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

            <div v-if="activeMobileMenu === 'operativa' && user" class="flex items-center justify-between p-2 mb-3 bg-neutral-100/60 dark:bg-neutral-800/40 border border-border/60 rounded-md">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 bg-primary/10 text-primary border border-primary/20 rounded-md flex items-center justify-center font-bold text-xs">
                        {{ user?.first_name?.[0]?.toUpperCase() || 'U' }}
                    </div>
                    <div class="flex flex-col">
                        <span class="text-xs font-semibold text-foreground leading-none">{{ user?.first_name }}</span>
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
                    <span class="material-symbols-rounded text-[20px]" :style="{ fontVariationSettings: isActiveRoute(subItem.pattern) ? `'FILL' 1` : `'FILL' 0` }">
                        {{ subItem.icon }}
                    </span>
                    <span class="text-xs font-medium text-center leading-none">{{ subItem.name }}</span>
                </Link>

                <Link 
                    v-if="activeMobileMenu === 'operativa'"
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
        
        <button @click="toggleMobileMenu('stock')" class="flex flex-col items-center justify-center h-full transition-colors duration-75" :class="[activeMobileMenu === 'stock' || isMobileGroupActive('stock') ? 'text-primary' : 'text-muted-foreground']">
            <span class="material-symbols-rounded text-[20px]" :style="(activeMobileMenu === 'stock' || isMobileGroupActive('stock')) ? { fontVariationSettings: `'FILL' 1` } : {}">inventory</span>
            <span class="text-[9px] font-medium mt-1">Stock</span>
        </button>
        
        <button @click="toggleMobileMenu('logistica')" class="flex flex-col items-center justify-center h-full transition-colors duration-75" :class="[activeMobileMenu === 'logistica' || isMobileGroupActive('logistica') ? 'text-primary' : 'text-muted-foreground']">
            <span class="material-symbols-rounded text-[20px]" :style="(activeMobileMenu === 'logistica' || isMobileGroupActive('logistica')) ? { fontVariationSettings: `'FILL' 1` } : {}">local_shipping</span>
            <span class="text-[9px] font-medium mt-1">Logística</span>
        </button>
        
        <div class="flex items-center justify-center -mt-3">
            <Link :href="route('admin.dashboard.index')" @click="closeMobileMenu" :class="[isActiveRoute('admin.dashboard.*') ? 'bg-primary text-white' : 'bg-neutral-200 text-neutral-700 dark:bg-neutral-800 dark:text-neutral-300']" class="w-11 h-11 rounded-md flex items-center justify-center shadow-flat transition-transform duration-75 active:scale-95">
                <span class="material-symbols-rounded text-[20px]" :style="isActiveRoute('admin.dashboard.*') ? { fontVariationSettings: `'FILL' 1` } : {}">home</span>
            </Link>
        </div>

        <button @click="toggleMobileMenu('catalogo')" class="flex flex-col items-center justify-center h-full transition-colors duration-75" :class="[activeMobileMenu === 'catalogo' || isMobileGroupActive('catalogo') ? 'text-primary' : 'text-muted-foreground']">
            <span class="material-symbols-rounded text-[20px]" :style="(activeMobileMenu === 'catalogo' || isMobileGroupActive('catalogo')) ? { fontVariationSettings: `'FILL' 1` } : {}">storefront</span>
            <span class="text-[9px] font-medium mt-1">Catálogo</span>
        </button>
        
        <button @click="toggleMobileMenu('operativa')" class="flex flex-col items-center justify-center h-full transition-colors duration-75" :class="[activeMobileMenu === 'operativa' || isMobileGroupActive('operativa') ? 'text-primary' : 'text-muted-foreground']">
            <span class="material-symbols-rounded text-[20px]" :style="(activeMobileMenu === 'operativa' || isMobileGroupActive('operativa')) ? { fontVariationSettings: `'FILL' 1` } : {}">settings</span>
            <span class="text-[9px] font-medium mt-1">Operativa</span>
        </button>
    </nav>
</template>