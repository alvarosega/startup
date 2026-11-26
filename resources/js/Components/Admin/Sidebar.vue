<script setup>
import { computed, ref, onMounted, onUnmounted } from 'vue';
import { usePage, Link } from '@inertiajs/vue3';
import SidebarLink from '@/Components/Admin/SidebarLink.vue';
import ThemeToggler from '@/Components/Base/ThemeToggler.vue';

const activeMobileMenu = ref(null);
const page = usePage();
const user = computed(() => page.props.auth?.user);
const roles = computed(() => user.value?.roles || []);
const isSuperAdmin = computed(() => user.value?.is_super_admin || roles.value.includes('super_admin'));
const permissions = computed(() => user.value?.permissions || {});

const can = (perm) => isSuperAdmin.value || !!permissions.value[perm];

// Motor de Telemetría para Tooltips Flotantes Apple Glass
const hoveredTitle = ref(null);
const tooltipTop = ref(0);
const navContainerRef = ref(null);

const resolveRoute = (r) => {
    if (typeof r === 'function') return r();
    if (typeof r === 'object' && r !== null && 'value' in r) return r.value;
    return r;
};

const showTooltip = (event, title) => {
    const rect = event.currentTarget.getBoundingClientRect();
    tooltipTop.value = rect.top + rect.height / 2;
    hoveredTitle.value = title;
};

const hideTooltip = () => {
    hoveredTitle.value = null;
};

const handleNavScroll = () => {
    if (hoveredTitle.value) {
        hideTooltip();
    }
};

const isDevelopment = ref(false);

onMounted(() => {
    isDevelopment.value = ['localhost', '127.0.0.1', 'test', 'dev'].some(host => 
        window.location.hostname.includes(host)
    );
    
    if (navContainerRef.value) {
        navContainerRef.value.addEventListener('scroll', handleNavScroll, { passive: true });
    }
});

onUnmounted(() => {
    if (navContainerRef.value) {
        navContainerRef.value.removeEventListener('scroll', handleNavScroll);
    }
    hideTooltip();
});

// Estructura del menú de navegación
const navigationMenu = [
    { name: 'Stock Base', route: 'admin.inventory.index', pattern: 'admin.inventory.*', icon: 'warehouse', group: 'stock', permission: computed(() => can('manage_inventory')) },
    { name: 'Precios Masivos', route: 'admin.prices.index', pattern: 'admin.prices.*', icon: 'payments', group: 'stock', permission: computed(() => can('manage_prices')) },
    { name: 'Ingresos', route: 'admin.purchases.index', pattern: 'admin.purchases.*', icon: 'move_to_inbox', group: 'stock', permission: computed(() => can('manage_purchases')) },
    
    { name: 'Órdenes', route: 'admin.orders.index', pattern: 'admin.orders.*', icon: 'receipt_long', group: 'logistica', permission: computed(() => can('manage_orders')) },
    { name: 'Radar (Vivo)', route: 'admin.logistics.monitor', pattern: 'admin.logistics.*', icon: 'radar', group: 'logistica', permission: computed(() => can('view_logistics_radar')) },
    { name: 'Transferencias', route: 'admin.transfers.index', pattern: 'admin.transfers.*', icon: 'local_shipping', group: 'logistica', permission: computed(() => can('manage_inventory')) },
    { name: 'Bajas', route: 'admin.removals.index', pattern: 'admin.removals.*', icon: 'delete_forever', group: 'logistica', permission: computed(() => can('manage_removals')) },
    
    { name: 'Catálogo', route: 'admin.catalog.products.index', pattern: ['admin.catalog.products.*', 'admin.catalog.categories.*', 'admin.catalog.brands.*'], icon: 'category', group: 'catalogo', permission: computed(() => can('manage_catalog')) },
    { name: 'Proveedores', route: 'admin.operations.providers.index', pattern: 'admin.operations.providers.*', icon: 'factory', group: 'catalogo', permission: computed(() => can('manage_catalog')) },
    
    { name: 'Sucursales', route: 'admin.operations.branches.index', pattern: 'admin.operations.branches.*', icon: 'store', group: 'operativa', permission: computed(() => can('manage_branches')) },
    { name: 'Usuarios', route: computed(() => can('manage_users') ? 'staff.index' : 'drivers.index'), pattern: ['staff.*', 'customers.*', 'drivers.*'], icon: 'manage_accounts', group: 'operativa', permission: computed(() => can('manage_users') || can('manage_drivers')) },

    { name: 'Combos', route: 'admin.bundles.index', pattern: 'admin.bundles.*', icon: 'widgets', group: 'marketing', permission: computed(() => can('manage_catalog')) },
];

const filteredMenu = computed(() => {
    return navigationMenu.filter(item => {
        if (typeof item.permission === 'boolean') return item.permission;
        return item.permission.value;
    });
});

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

const isActiveRoute = (pattern) => {
    if (Array.isArray(pattern)) {
        return pattern.some(p => route().current(p));
    }
    return route().current(pattern);
};

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
    <!-- Desktop Sidebar: Apple Glass Dock -->
    <aside class="hidden md:flex flex-col fixed top-0 left-0 h-full w-[74px] glass-dock z-50 overflow-visible justify-between select-none">
        
        <div class="flex flex-col w-full items-center overflow-visible">
            <!-- Brand Mark Minimalista Apple -->
            <Link :href="route('dashboard.index')" 
                  @mouseenter="showTooltip($event, 'Dashboard Central')"
                  @mouseleave="hideTooltip"
                  class="w-11 h-11 rounded-2xl flex items-center justify-center shrink-0 my-3 transition-all duration-200 active:scale-95 shadow-sm group"
                  :class="[isActiveRoute('dashboard.*') ? 'bg-primary text-primary-foreground shadow-primary/25' : 'bg-primary/10 text-primary border border-primary/20 hover:bg-primary/20']">
                <span class="text-xs font-black tracking-widest group-hover:scale-105 transition-transform">DU</span>
            </Link>

            <!-- Ficha de Usuario Minimalista -->
            <div v-if="user" class="relative group flex items-center justify-center w-full h-11 mb-2 shrink-0">
                <div class="w-9 h-9 bg-foreground/5 text-foreground border border-border/70 rounded-full flex items-center justify-center font-bold text-xs cursor-default backdrop-blur-sm group-hover:border-primary/40 transition-colors">
                    <span>{{ user?.first_name?.[0]?.toUpperCase() || 'U' }}</span>
                </div>
                
                <div class="fixed left-[82px] hidden group-hover:flex flex-col p-3 glass-card border border-white/60 dark:border-white/10 rounded-2xl shadow-xl z-50 pointer-events-none min-w-[170px] backdrop-blur-2xl">
                    <span class="text-xs font-bold text-foreground leading-tight">
                        {{ user?.full_name }}
                    </span>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-primary mt-1 leading-none">
                        {{ user?.is_super_admin ? 'Administrador Maestro' : (user?.roles?.[0]?.replace(/_/g, ' ') || 'Staff') }}
                    </span>
                    <span v-if="user?.branch" class="text-[10px] font-medium text-muted-foreground mt-1.5 leading-none flex items-center gap-1">
                        <span class="material-symbols-rounded text-[13px] text-primary">store</span>
                        {{ user.branch.name }}
                    </span>
                </div>
            </div>

            <!-- Navegación Apple Dock -->
            <nav ref="navContainerRef" class="w-full h-[calc(100vh-215px)] overflow-y-auto overflow-x-visible no-scrollbar py-1 flex flex-col items-center">
                <template v-for="(groupKey, gIndex) in ['stock', 'logistica', 'catalogo', 'operativa', 'marketing']" :key="groupKey">
                    
                    <div v-if="gIndex > 0 && filteredMenu.some(i => i.group === groupKey)" class="w-6 h-[1px] bg-border/50 my-2 shrink-0"></div>
                    
                    <SidebarLink 
                        v-for="item in filteredMenu.filter(i => i.group === groupKey)" 
                        :key="resolveRoute(item.route)"
                        :href="route(resolveRoute(item.route))"
                        :active="isActiveRoute(item.pattern)"
                        :icon="item.icon"
                        @mouseenter="showTooltip($event, item.name)"
                        @mouseleave="hideTooltip"
                        @click="hideTooltip"
                    />
                </template>
            </nav>
        </div>

        <!-- Controles Inferiores Apple Dock -->
        <div class="flex flex-col w-full items-center border-t border-border/40 shrink-0 z-10 pb-3 pt-2 gap-1.5">
            <div class="w-10 h-10 flex items-center justify-center text-muted-foreground hover:text-foreground transition-colors duration-150">
                <ThemeToggler class="p-2 rounded-xl hover:bg-foreground/[0.06] transition-colors" />
            </div>

            <Link 
                :href="route('admin.logout')" 
                method="post" 
                as="button" 
                @mouseenter="showTooltip($event, 'Cerrar Sesión')"
                @mouseleave="hideTooltip"
                @click="hideTooltip"
                class="relative flex items-center justify-center w-10 h-10 rounded-xl text-destructive hover:bg-destructive/10 transition-all duration-150 active:scale-95"
            >
                <span class="material-symbols-rounded text-[20px]">logout</span>
            </Link>

            <div v-if="isDevelopment" class="text-[8px] font-bold tracking-widest text-muted-foreground/70 bg-muted/60 border border-border/60 px-1.5 py-0.5 rounded-full mt-0.5 select-none font-mono">
                DEV
            </div>
        </div>

        <!-- Tooltip Flotante Estilo Apple Capsule -->
        <div v-if="hoveredTitle" 
             :style="{ top: tooltipTop + 'px' }"
             class="fixed left-[84px] -translate-y-1/2 px-3 py-1 rounded-full text-xs font-semibold text-foreground bg-card/90 border border-border/70 shadow-lg backdrop-blur-2xl whitespace-nowrap z-50 pointer-events-none select-none tracking-wide">
            {{ hoveredTitle }}
        </div>
    </aside>

    <!-- Mobile Navigation: Glass Bottom Sheet & Bar -->
    <div class="md:hidden">
        <div v-if="activeMobileMenu" @click="closeMobileMenu" class="fixed inset-0 bg-neutral-950/40 backdrop-blur-sm z-40 cursor-pointer transition-opacity duration-150"></div>

        <div v-if="activeMobileMenu" class="fixed bottom-[86px] left-3 right-3 glass-panel z-50 p-5 flex flex-col max-h-[60vh] animate-in fade-in slide-in-from-bottom-4 duration-200">
            <div class="flex justify-between items-center mb-3 pb-2 border-b border-border/40">
                <h3 class="text-xs font-bold text-foreground tracking-wide flex items-center gap-2">
                    <span class="w-1.5 h-3.5 bg-primary rounded-full"></span>
                    {{ mobileGroups[activeMobileMenu]?.label }}
                </h3>
                <button @click="closeMobileMenu" class="p-1.5 rounded-full hover:bg-foreground/[0.06] text-muted-foreground transition-colors">
                    <span class="material-symbols-rounded text-[18px] block">close</span>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto grid grid-cols-2 gap-2.5 no-scrollbar">
                <Link 
                    v-for="subItem in mobileGroups[activeMobileMenu]?.items"
                    :key="resolveRoute(subItem.route)"
                    @click="closeMobileMenu" 
                    :href="route(resolveRoute(subItem.route))" 
                    :class="[isActiveRoute(subItem.pattern) ? 'border-primary/40 bg-primary/10 text-primary font-bold shadow-sm' : 'border-border/60 bg-card/40 text-muted-foreground hover:text-foreground']"
                    class="border p-3.5 rounded-xl flex flex-col items-center justify-center gap-1.5 transition-all active:scale-95 backdrop-blur-sm"
                >
                    <span class="material-symbols-rounded text-[21px]" :style="{ fontVariationSettings: isActiveRoute(subItem.pattern) ? `'FILL' 1` : `'FILL' 0` }">
                        {{ subItem.icon }}
                    </span>
                    <span class="text-xs text-center font-medium">{{ subItem.name }}</span>
                </Link>

                <Link :href="route('admin.logout')" method="post" as="button" class="border border-destructive/20 bg-destructive/5 text-destructive p-3 rounded-xl flex flex-col items-center justify-center gap-1 col-span-2 transition-colors active:scale-95">
                    <span class="material-symbols-rounded text-[20px]">logout</span>
                    <span class="text-xs font-semibold text-center">Cerrar Sesión</span>
                </Link>
            </div>
        </div>
    </div>

    <nav class="md:hidden fixed bottom-0 left-0 right-0 h-[74px] glass-header z-40 flex items-center justify-around px-2 select-none">
        <button v-if="mobileGroups['stock']?.items.length" @click="toggleMobileMenu('stock')" class="flex-1 flex flex-col items-center justify-center h-full transition-colors" :class="[activeMobileMenu === 'stock' || isMobileGroupActive('stock') ? 'text-primary' : 'text-muted-foreground']">
            <span class="material-symbols-rounded text-[20px]" :style="(activeMobileMenu === 'stock' || isMobileGroupActive('stock')) ? { fontVariationSettings: `'FILL' 1` } : {}">inventory</span>
            <span class="text-[9px] font-semibold mt-1">Stock</span>
        </button>
        
        <button v-if="mobileGroups['logistica']?.items.length" @click="toggleMobileMenu('logistica')" class="flex-1 flex flex-col items-center justify-center h-full transition-colors" :class="[activeMobileMenu === 'logistica' || isMobileGroupActive('logistica') ? 'text-primary' : 'text-muted-foreground']">
            <span class="material-symbols-rounded text-[20px]" :style="(activeMobileMenu === 'logistica' || isMobileGroupActive('logistica')) ? { fontVariationSettings: `'FILL' 1` } : {}">local_shipping</span>
            <span class="text-[9px] font-semibold mt-1">Logística</span>
        </button>
        
        <div class="flex items-center justify-center -mt-4 px-2">
            <Link :href="route('dashboard.index')" @click="closeMobileMenu" :class="[isActiveRoute('dashboard.*') ? 'bg-primary text-primary-foreground shadow-lg shadow-primary/30' : 'bg-card text-foreground border border-border/80 shadow-md']" class="w-12 h-12 rounded-2xl flex items-center justify-center transition-transform active:scale-90 backdrop-blur-xl">
                <span class="material-symbols-rounded text-[22px]" :style="isActiveRoute('dashboard.*') ? { fontVariationSettings: `'FILL' 1` } : {}">home</span>
            </Link>
        </div>

        <button v-if="mobileGroups['catalogo']?.items.length" @click="toggleMobileMenu('catalogo')" class="flex-1 flex flex-col items-center justify-center h-full transition-colors" :class="[activeMobileMenu === 'catalogo' || isMobileGroupActive('catalogo') ? 'text-primary' : 'text-muted-foreground']">
            <span class="material-symbols-rounded text-[20px]" :style="(activeMobileMenu === 'catalogo' || isMobileGroupActive('catalogo')) ? { fontVariationSettings: `'FILL' 1` } : {}">storefront</span>
            <span class="text-[9px] font-semibold mt-1">Catálogo</span>
        </button>
        
        <button v-if="mobileGroups['operativa']?.items.length" @click="toggleMobileMenu('operativa')" class="flex-1 flex flex-col items-center justify-center h-full transition-colors" :class="[activeMobileMenu === 'operativa' || isMobileGroupActive('operativa') ? 'text-primary' : 'text-muted-foreground']">
            <span class="material-symbols-rounded text-[20px]" :style="(activeMobileMenu === 'operativa' || isMobileGroupActive('operativa')) ? { fontVariationSettings: `'FILL' 1` } : {}">settings</span>
            <span class="text-[9px] font-semibold mt-1">Operativa</span>
        </button>
    </nav>
</template>
