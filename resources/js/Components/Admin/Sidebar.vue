<script setup>
import { computed, ref, watch } from 'vue';
import { usePage, Link } from '@inertiajs/vue3';
import SidebarLink from '@/Components/Admin/SidebarLink.vue';
import { 
    LayoutDashboard, ShoppingCart, Package, Truck, AlertTriangle, 
    RefreshCw, Tag, Layers, Factory, LogOut,
    ChevronLeft, Banknote, Gift, ClipboardList,
    Settings, X, Building2, FolderTree, UserCog, Map, 
    ArrowLeftRight, ArrowDownToLine, Store, Home
} from 'lucide-vue-next';

const emit = defineEmits(['toggle-collapse']);

// --- ESTADO ---
const isCollapsed = ref(localStorage.getItem('sidebarCollapsed') === 'true');
const activeMobileMenu = ref(null);

// --- PERMISOS (Lógica de Negocio Intacta) ---
const page = usePage();
const user = computed(() => page.props.auth.user);
const roles = computed(() => user.value.roles || []);

const isSuperAdmin = computed(() => roles.value.includes('super_admin'));
const isBranchAdmin = computed(() => roles.value.includes('branch_admin'));
const isLogistics = computed(() => roles.value.includes('logistics_manager'));
const isInventoryManager = computed(() => roles.value.includes('inventory_manager'));
const isFinanceManager = computed(() => roles.value.includes('finance_manager'));

const canManageCatalog = computed(() => isSuperAdmin.value || isLogistics.value);
const canViewProducts = computed(() => canManageCatalog.value || isBranchAdmin.value || isInventoryManager.value || isFinanceManager.value);
const canManagePrices = computed(() => isSuperAdmin.value || isFinanceManager.value);
const canViewBrands = computed(() => canManageCatalog.value || isBranchAdmin.value || isInventoryManager.value || isFinanceManager.value);
const canViewCategories = computed(() => canManageCatalog.value || isBranchAdmin.value || isInventoryManager.value || isFinanceManager.value);
const canViewProviders = computed(() => canManageCatalog.value || isBranchAdmin.value || isInventoryManager.value || isFinanceManager.value);
const showCatalogSection = computed(() => canManageCatalog.value || canViewProviders.value || canViewCategories.value || canViewBrands.value || canViewProducts.value);
const canManageStock = computed(() => isSuperAdmin.value || isLogistics.value || isBranchAdmin.value || isInventoryManager.value);
const canManageUsers = computed(() => isSuperAdmin.value || isBranchAdmin.value);

// --- GRUPOS MÓVILES ---
const canViewInventory = computed(() => showCatalogSection.value);
const canViewMovements = computed(() => canManageStock.value || isSuperAdmin.value);
const canViewCommercial = computed(() => canManagePrices.value || canViewProviders.value || canManageStock.value);

// --- MÉTODOS ---
const toggleSidebar = () => {
    isCollapsed.value = !isCollapsed.value;
    localStorage.setItem('sidebarCollapsed', isCollapsed.value);
    emit('toggle-collapse', isCollapsed.value);
};

const toggleMobileMenu = (menu) => {
    activeMobileMenu.value = activeMobileMenu.value === menu ? null : menu;
};

const closeMobileMenu = () => {
    activeMobileMenu.value = null;
};

watch(isCollapsed, (val) => emit('toggle-collapse', val), { immediate: true });
</script>

<template>
    <aside 
        class="hidden md:flex flex-col fixed h-full z-30 bg-card border-r border-border/60 shadow-xl transition-[width] duration-300 ease-[cubic-bezier(0.25,0.8,0.25,1)] will-change-[width]"
        :class="isCollapsed ? 'w-[72px]' : 'w-[260px]'"
    >
        <div class="h-16 flex items-center px-4 border-b border-border/40 shrink-0 relative overflow-hidden bg-muted/10 backdrop-blur-sm">
            <div class="flex items-center gap-3 transition-all duration-300 absolute left-5"
                 :class="isCollapsed ? 'opacity-0 translate-x-[-10px] pointer-events-none' : 'opacity-100 translate-x-0 delay-75'">
                <div class="w-8 h-8 rounded-lg bg-primary text-primary-foreground flex items-center justify-center shadow-lg shadow-primary/20">
                    <Factory :size="16" /> 
                </div>
                <div class="flex flex-col justify-center">
                    <span class="font-display font-black text-sm tracking-wide text-foreground leading-none">
                        BOLIVIA<span class="text-primary">LOGISTICS</span>
                    </span>
                    <span class="text-[9px] text-muted-foreground font-medium tracking-wider uppercase leading-none mt-0.5">
                        Enterprise v2.0
                    </span>
                </div>
            </div>

            <div class="absolute inset-0 flex items-center justify-center transition-all duration-300 z-20 pointer-events-none"
                 :class="isCollapsed ? 'opacity-100 scale-100 delay-75' : 'opacity-0 scale-50'">
                <div class="w-9 h-9 rounded-xl bg-primary text-primary-foreground flex items-center justify-center font-black text-xs shadow-lg">
                    BL
                </div>
            </div>

            <button @click="toggleSidebar" 
                    class="ml-auto z-30 p-1.5 rounded-lg text-muted-foreground hover:text-foreground hover:bg-muted/50 transition-all"
                    :class="isCollapsed ? 'hidden' : 'block'">
                <ChevronLeft :size="18" />
            </button>
            
            <button v-if="isCollapsed" @click="toggleSidebar" class="absolute inset-0 z-40 w-full h-full cursor-pointer opacity-0"></button>
        </div>

        <nav class="flex-1 px-3 py-6 space-y-1 overflow-y-auto overflow-x-hidden scrollbar-thin">
            <SidebarLink :href="route('admin.dashboard')" :active="$page.url.startsWith('/admin/dashboard')" :collapsed="isCollapsed">
                <template #icon><LayoutDashboard :size="20" /></template>
                Dashboard
            </SidebarLink>
            
            <template v-if="canManageStock">
                <div v-if="!isCollapsed" class="section-header">Operaciones</div>
                <div v-else class="section-divider"></div>
                
                <SidebarLink :href="route('admin.purchases.index')" :active="$page.url.startsWith('/admin/purchases')" :collapsed="isCollapsed"><template #icon><ShoppingCart :size="20" /></template>Ingresos</SidebarLink>
                <SidebarLink :href="route('admin.inventory.index')" :active="$page.url.startsWith('/admin/inventory')" :collapsed="isCollapsed"><template #icon><Package :size="20" /></template>Kardex</SidebarLink>
                <SidebarLink :href="route('admin.transfers.index')" :active="$page.url.startsWith('/admin/transfers')" :collapsed="isCollapsed"><template #icon><Truck :size="20" /></template>Transferencias</SidebarLink>
                <SidebarLink :href="route('admin.removals.index')" :active="$page.url.startsWith('/admin/removals')" :collapsed="isCollapsed"><template #icon><AlertTriangle :size="20" /></template>Bajas</SidebarLink>
                <SidebarLink :href="route('admin.transformations.index')" :active="$page.url.startsWith('/admin/transformations')" :collapsed="isCollapsed"><template #icon><RefreshCw :size="20" /></template>Transformaciones</SidebarLink>
                <SidebarLink :href="route('admin.orders.kanban')" :active="$page.url.startsWith('/admin/orders/kanban')" :collapsed="isCollapsed"><template #icon><ClipboardList :size="20" /></template>Kanban</SidebarLink>
            </template>

            <template v-if="showCatalogSection">
                <div v-if="!isCollapsed" class="section-header">Catálogo</div>
                <div v-else class="section-divider"></div>
                
                <SidebarLink v-if="canViewProducts" :href="route('admin.products.index')" :active="$page.url.startsWith('/admin/products')" :collapsed="isCollapsed"><template #icon><Tag :size="20" /></template>Productos</SidebarLink>
                <SidebarLink v-if="isSuperAdmin" :href="route('admin.market-zones.index')" :active="$page.url.startsWith('/admin/market-zones')" :collapsed="isCollapsed"><template #icon><Map :size="20" /></template>Zonas</SidebarLink>
                <SidebarLink v-if="isSuperAdmin" :href="route('admin.bundles.index')" :active="$page.url.startsWith('/admin/bundles')" :collapsed="isCollapsed"><template #icon><Gift :size="20" /></template>Packs</SidebarLink>
                <SidebarLink v-if="canManagePrices" :href="route('admin.prices.index')" :active="$page.url.startsWith('/admin/prices')" :collapsed="isCollapsed"><template #icon><Banknote :size="20" /></template>Precios</SidebarLink>
                <SidebarLink v-if="canViewBrands" :href="route('admin.brands.index')" :active="$page.url.startsWith('/admin/brands')" :collapsed="isCollapsed"><template #icon><Layers :size="18" /></template>Marcas</SidebarLink>
                <SidebarLink v-if="canViewCategories" :href="route('admin.categories.index')" :active="$page.url.startsWith('/admin/categories')" :collapsed="isCollapsed"><template #icon><FolderTree :size="18" /></template>Categorías</SidebarLink>
                <SidebarLink v-if="canViewProviders" :href="route('admin.providers.index')" :active="$page.url.startsWith('/admin/providers')" :collapsed="isCollapsed"><template #icon><Factory :size="18" /></template>Proveedores</SidebarLink>
            </template>

            <template v-if="canManageUsers">
                <div v-if="!isCollapsed" class="section-header">Gestión</div>
                <div v-else class="section-divider"></div>
                
                <SidebarLink v-if="isSuperAdmin" :href="route('admin.branches.index')" :active="$page.url.startsWith('/admin/branches')" :collapsed="isCollapsed"><template #icon><Building2 :size="20" /></template>Sucursales</SidebarLink>
                <SidebarLink v-if="isSuperAdmin || isBranchAdmin" :href="route('admin.drivers.index')" :active="$page.url.startsWith('/admin/drivers')" :collapsed="isCollapsed"><template #icon><Truck :size="20" /></template>Conductores</SidebarLink>
                <SidebarLink :href="route('admin.users.index')" :active="$page.url.startsWith('/admin/users')" :collapsed="isCollapsed"><template #icon><UserCog :size="20" /></template>Equipo</SidebarLink>
            </template>
        </nav>

        <div class="p-4 border-t border-border/40 bg-muted/10 shrink-0">
            <Link :href="route('logout')" method="post" as="button" 
                  class="group flex items-center justify-center gap-2 w-full rounded-lg border border-border bg-background p-2 text-sm font-medium transition-all hover:bg-destructive/10 hover:border-destructive/30 hover:text-destructive"
                  :class="isCollapsed ? 'aspect-square p-0' : ''">
                <LogOut :size="16" />
                <span v-if="!isCollapsed">Cerrar Sesión</span>
            </Link>
        </div>
    </aside>

    <div class="md:hidden">
        
        <div v-if="activeMobileMenu" @click="closeMobileMenu" class="fixed inset-0 bg-background/80 backdrop-blur-sm z-40 animate-in fade-in duration-200"></div>

        <div v-if="activeMobileMenu" class="fixed bottom-[90px] left-4 right-4 bg-card/95 backdrop-blur-xl z-50 p-6 rounded-3xl shadow-2xl border border-border/50 animate-in slide-in-from-bottom-10 duration-300 flex flex-col max-h-[60vh] ring-1 ring-white/10">
            
            <div class="w-12 h-1.5 rounded-full bg-muted-foreground/20 mx-auto mb-6 shrink-0"></div>
                
            <div class="flex justify-between items-center mb-6 pb-4 border-b border-border/50 shrink-0">
                <h3 class="font-black text-xl text-foreground flex items-center gap-3">
                    <span v-if="activeMobileMenu === 'inv'" class="flex items-center gap-2 text-primary"><Package /> Inventario</span>
                    <span v-else-if="activeMobileMenu === 'mov'" class="flex items-center gap-2 text-primary"><ArrowLeftRight /> Movimientos</span>
                    <span v-else-if="activeMobileMenu === 'com'" class="flex items-center gap-2 text-primary"><Store /> Comercial</span>
                    <span v-else class="flex items-center gap-2 text-primary"><Settings /> Gestión</span>
                </h3>
                <button @click="closeMobileMenu" class="p-2 rounded-full bg-muted/50 text-muted-foreground hover:text-foreground">
                    <X :size="20"/>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto -mx-2 px-2 pb-2">
                 <div v-if="activeMobileMenu === 'inv'" class="grid grid-cols-3 gap-4">
                    <Link @click="closeMobileMenu" :href="route('admin.inventory.index')" class="mobile-item group"><div class="mobile-icon"><Package :size="24" /></div><span>Kardex</span></Link>
                    <Link v-if="canViewProducts" @click="closeMobileMenu" :href="route('admin.products.index')" class="mobile-item group"><div class="mobile-icon"><Tag :size="24" /></div><span>Productos</span></Link>
                    <Link v-if="canViewBrands" @click="closeMobileMenu" :href="route('admin.brands.index')" class="mobile-item group"><div class="mobile-icon"><Layers :size="24" /></div><span>Marcas</span></Link>
                    <Link v-if="canViewCategories" @click="closeMobileMenu" :href="route('admin.categories.index')" class="mobile-item group"><div class="mobile-icon"><FolderTree :size="24" /></div><span>Categ.</span></Link>
                    <Link v-if="isSuperAdmin" @click="closeMobileMenu" :href="route('admin.bundles.index')" class="mobile-item group"><div class="mobile-icon"><Gift :size="24" /></div><span>Packs</span></Link>
                </div>

                <div v-if="activeMobileMenu === 'mov'" class="grid grid-cols-3 gap-4">
                    <Link @click="closeMobileMenu" :href="route('admin.transfers.index')" class="mobile-item group"><div class="mobile-icon"><ArrowLeftRight :size="24" /></div><span>Transfer</span></Link>
                    <Link @click="closeMobileMenu" :href="route('admin.removals.index')" class="mobile-item group"><div class="mobile-icon text-destructive bg-destructive/10 border-destructive/20"><AlertTriangle :size="24" /></div><span>Bajas</span></Link>
                    <Link @click="closeMobileMenu" :href="route('admin.transformations.index')" class="mobile-item group"><div class="mobile-icon"><RefreshCw :size="24" /></div><span>Transf.</span></Link>
                    <Link v-if="isSuperAdmin" @click="closeMobileMenu" :href="route('admin.market-zones.index')" class="mobile-item group"><div class="mobile-icon"><Map :size="24" /></div><span>Zonas</span></Link>
                </div>

                <div v-if="activeMobileMenu === 'com'" class="grid grid-cols-3 gap-4">
                    <Link @click="closeMobileMenu" :href="route('admin.orders.kanban')" class="mobile-item group"><div class="mobile-icon"><ClipboardList :size="24" /></div><span class="font-bold">Kanban</span></Link>
                    <Link @click="closeMobileMenu" :href="route('admin.purchases.index')" class="mobile-item group"><div class="mobile-icon"><ArrowDownToLine :size="24" /></div><span>Ingresos</span></Link>
                    <Link v-if="canViewProviders" @click="closeMobileMenu" :href="route('admin.providers.index')" class="mobile-item group"><div class="mobile-icon"><Factory :size="24" /></div><span>Prov.</span></Link>
                    <Link v-if="canManagePrices" @click="closeMobileMenu" :href="route('admin.prices.index')" class="mobile-item group"><div class="mobile-icon"><Banknote :size="24" /></div><span>Precios</span></Link>
                </div>

                <div v-if="activeMobileMenu === 'ges'" class="space-y-6">
                    <div class="grid grid-cols-3 gap-4">
                        <Link @click="closeMobileMenu" :href="route('admin.users.index')" class="mobile-item group"><div class="mobile-icon"><UserCog :size="24" /></div><span>Equipo</span></Link>
                        <Link v-if="isSuperAdmin" @click="closeMobileMenu" :href="route('admin.branches.index')" class="mobile-item group"><div class="mobile-icon"><Building2 :size="24" /></div><span>Sucursales</span></Link>
                        <Link v-if="isSuperAdmin || isBranchAdmin" @click="closeMobileMenu" :href="route('admin.drivers.index')" class="mobile-item group"><div class="mobile-icon"><Truck :size="24" /></div><span>Drivers</span></Link>
                    </div>
                    
                    <Link :href="route('logout')" method="post" as="button" class="w-full flex items-center justify-center gap-3 p-4 rounded-2xl bg-destructive/10 text-destructive border border-destructive/20 hover:bg-destructive/20 transition-all font-semibold tracking-wide">
                        <LogOut :size="20" />
                        Cerrar Sesión
                    </Link>
                </div>
            </div>
        </div>
    </div>

    <nav class="md:hidden fixed bottom-0 left-0 right-0 h-[80px] bg-card/90 backdrop-blur-xl border-t border-border z-40 pb-safe shadow-[0_-5px_20px_rgba(0,0,0,0.1)] grid grid-cols-5 px-2">
        <button @click="toggleMobileMenu('ges')" v-if="canManageUsers" class="mobile-nav-btn" :class="{'active': activeMobileMenu === 'ges'}">
            <div class="icon-wrapper"><Settings :size="22" /></div>
            <span class="label">Gestión</span>
        </button>
        <button @click="toggleMobileMenu('inv')" v-if="canViewInventory" class="mobile-nav-btn" :class="{'active': activeMobileMenu === 'inv'}">
            <div class="icon-wrapper"><Package :size="22" /></div>
            <span class="label">Stock</span>
        </button>
        
        <div class="flex items-start justify-center -mt-6 relative z-50">
            <Link :href="route('admin.dashboard')" @click="closeMobileMenu"
                  class="flex items-center justify-center w-16 h-16 rounded-full bg-primary text-primary-foreground transition-all duration-300 active:scale-90 border-[4px] border-background shadow-lg hover:-translate-y-1"
            >
                <Home :size="26" class="drop-shadow-sm" />
            </Link>
        </div>

        <button @click="toggleMobileMenu('mov')" v-if="canViewMovements" class="mobile-nav-btn" :class="{'active': activeMobileMenu === 'mov'}">
            <div class="icon-wrapper"><ArrowLeftRight :size="22" /></div>
            <span class="label">Flujos</span>
        </button>
        <button @click="toggleMobileMenu('com')" v-if="canViewCommercial" class="mobile-nav-btn" :class="{'active': activeMobileMenu === 'com'}">
            <div class="icon-wrapper"><Store :size="22" /></div>
            <span class="label">Ventas</span>
        </button>
    </nav>
</template>

<style scoped>
/* Desktop Utils */
.section-header {
    @apply mt-6 mb-2 px-3 text-[10px] font-bold text-muted-foreground uppercase tracking-widest opacity-80;
}
.section-divider {
    @apply my-4 border-t border-border/40 mx-2;
}

/* Mobile Utils */
.mobile-item {
    @apply flex flex-col items-center justify-center gap-2 p-2 rounded-xl transition-all active:scale-95 cursor-pointer;
}
.mobile-icon {
    @apply w-14 h-14 rounded-2xl flex items-center justify-center bg-muted/50 text-foreground border border-border shadow-sm transition-all duration-200;
}
.mobile-item:hover .mobile-icon {
    @apply bg-primary/10 text-primary border-primary/20 scale-105;
}
.mobile-item span {
    @apply text-[11px] text-muted-foreground font-medium text-center leading-tight;
}

/* Mobile Nav Bar Buttons */
.mobile-nav-btn {
    @apply flex flex-col items-center justify-center gap-1 h-full text-muted-foreground transition-all duration-200 relative pt-2;
}
.mobile-nav-btn .icon-wrapper {
    @apply p-1.5 rounded-xl transition-all duration-200;
}
.mobile-nav-btn.active {
    @apply text-primary;
}
.mobile-nav-btn.active .icon-wrapper {
    @apply bg-primary/10 -translate-y-1;
}
.mobile-nav-btn .label {
    @apply text-[10px] font-medium transition-all;
}
.pb-safe {
    padding-bottom: env(safe-area-inset-bottom, 20px);
}
</style>