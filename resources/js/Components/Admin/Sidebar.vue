//ok 

<script setup>
import { computed, ref, watch } from 'vue';
import { usePage, Link } from '@inertiajs/vue3';
import SidebarLink from '@/Components/Admin/SidebarLink.vue';
import { 
    LayoutDashboard, ShoppingCart, Package, Truck, AlertTriangle, 
    RefreshCw, Tag, Layers, Factory, LogOut,
    ChevronLeft, ChevronRight, Banknote, Gift, ClipboardList,
    Settings, X, Building2, FolderTree, UserCog, Map, 
    ArrowLeftRight, ArrowDownToLine, Store, Home
} from 'lucide-vue-next';

const emit = defineEmits(['toggle-collapse']);

// --- ESTADO ---
const isCollapsed = ref(localStorage.getItem('sidebarCollapsed') === 'true');
const activeMobileMenu = ref(null);

// --- ACCESO Y PERMISOS ---
const page = usePage();
const user = computed(() => page.props.auth.user);
const roles = computed(() => user.value.roles || []);

// Helpers de Roles y Permisos (Misma lógica de negocio)
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
        class="hidden md:flex flex-col fixed h-full z-30 bg-card border-r border-border/60 shadow-xl transition-[width] duration-300 ease-smooth will-change-[width]"
        :class="isCollapsed ? 'w-[72px]' : 'w-[260px]'"
    >
        <div class="h-16 flex items-center px-4 border-b border-border/40 shrink-0 relative overflow-hidden bg-card/50 backdrop-blur-sm">
            
            <div 
                class="flex items-center gap-3 transition-all duration-300 absolute left-5"
                :class="isCollapsed ? 'opacity-0 translate-x-[-10px] pointer-events-none' : 'opacity-100 translate-x-0 delay-100'"
            >
                <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center text-primary shadow-sm border border-primary/20">
                    <Factory :size="16" /> </div>
                <div class="flex flex-col justify-center">
                    <span class="font-display font-black text-sm tracking-wide text-foreground leading-none">
                        BOLIVIA<span class="text-primary">LOGISTICS</span>
                    </span>
                    <span class="text-[9px] text-muted-foreground font-medium tracking-wider uppercase leading-none mt-0.5">
                        Enterprise v2.0
                    </span>
                </div>
            </div>

            <div 
                class="absolute inset-0 flex items-center justify-center transition-all duration-300 z-20 pointer-events-none"
                :class="isCollapsed ? 'opacity-100 scale-100 delay-100' : 'opacity-0 scale-50'"
            >
                <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-primary to-secondary flex items-center justify-center text-primary-foreground font-black text-xs shadow-lg ring-1 ring-white/20">
                    BL
                </div>
            </div>

            <button 
                @click="toggleSidebar" 
                class="btn btn-ghost btn-sm p-1.5 rounded-lg hover:bg-muted ml-auto z-30 transition-transform duration-300"
                :class="isCollapsed ? 'translate-x-[40px] opacity-0' : 'opacity-100'"
            >
                <ChevronLeft :size="16" class="text-muted-foreground hover:text-foreground" />
            </button>

            <button 
                v-if="isCollapsed"
                @click="toggleSidebar" 
                class="absolute inset-0 z-40 w-full h-full cursor-pointer opacity-0"
                title="Expandir menú"
            ></button>
        </div>

        <nav class="flex-1 px-3 py-6 space-y-1 overflow-y-auto overflow-x-hidden scrollbar-thin scrollbar-thumb-muted-foreground/20 hover:scrollbar-thumb-muted-foreground/40">
            <SidebarLink :href="route('admin.dashboard')" :active="$page.url.startsWith('/admin/dashboard')" :collapsed="isCollapsed">
                <template #icon><LayoutDashboard :size="20" /></template>
                Dashboard
            </SidebarLink>
            
            <template v-if="canManageStock">
                <div v-if="!isCollapsed" class="section-header">Operaciones</div>
                <div v-else class="my-4 border-t border-border/40 mx-2"></div>
                
                <SidebarLink :href="route('admin.purchases.index')" :active="$page.url.startsWith('/admin/purchases')" :collapsed="isCollapsed"><template #icon><ShoppingCart :size="20" /></template>Ingresos</SidebarLink>
                <SidebarLink :href="route('admin.inventory.index')" :active="$page.url.startsWith('/admin/inventory')" :collapsed="isCollapsed"><template #icon><Package :size="20" /></template>Kardex</SidebarLink>
                <SidebarLink :href="route('admin.transfers.index')" :active="$page.url.startsWith('/admin/transfers')" :collapsed="isCollapsed"><template #icon><Truck :size="20" /></template>Transferencias</SidebarLink>
                <SidebarLink :href="route('admin.removals.index')" :active="$page.url.startsWith('/admin/removals')" :collapsed="isCollapsed"><template #icon><AlertTriangle :size="20" /></template>Bajas</SidebarLink>
                <SidebarLink :href="route('admin.transformations.index')" :active="$page.url.startsWith('/admin/transformations')" :collapsed="isCollapsed"><template #icon><RefreshCw :size="20" /></template>Transformaciones</SidebarLink>
                <SidebarLink :href="route('admin.orders.kanban')" :active="$page.url.startsWith('/admin/orders/kanban')" :collapsed="isCollapsed"><template #icon><ClipboardList :size="20" /></template>Kanban</SidebarLink>
            </template>

            <template v-if="showCatalogSection">
                <div v-if="!isCollapsed" class="section-header">Catálogo</div>
                <div v-else class="my-4 border-t border-border/40 mx-2"></div>
                
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
                <div v-else class="my-4 border-t border-border/40 mx-2"></div>
                
                <SidebarLink v-if="isSuperAdmin" :href="route('admin.branches.index')" :active="$page.url.startsWith('/admin/branches')" :collapsed="isCollapsed"><template #icon><Building2 :size="20" /></template>Sucursales</SidebarLink>
                <SidebarLink v-if="isSuperAdmin || isBranchAdmin" :href="route('admin.drivers.index')" :active="$page.url.startsWith('/admin/drivers')" :collapsed="isCollapsed"><template #icon><Truck :size="20" /></template>Conductores</SidebarLink>
                <SidebarLink :href="route('admin.users.index')" :active="$page.url.startsWith('/admin/users')" :collapsed="isCollapsed"><template #icon><UserCog :size="20" /></template>Equipo</SidebarLink>
            </template>
        </nav>

        <div class="p-4 border-t border-border/40 bg-muted/20 shrink-0">
            <Link :href="route('logout')" method="post" as="button" 
                  class="group flex items-center justify-center gap-2 w-full rounded-lg border border-border bg-background p-2 text-sm font-medium transition-all hover:bg-error/5 hover:border-error/20 hover:text-error"
                  :class="isCollapsed ? 'aspect-square p-0' : ''">
                <LogOut :size="16" class="transition-transform group-hover:-translate-x-0.5" />
                <span v-if="!isCollapsed">Cerrar Sesión</span>
            </Link>
        </div>
    </aside>

    <div class="md:hidden">
        <div v-if="activeMobileMenu" @click="closeMobileMenu" class="fixed inset-0 bg-background/60 backdrop-blur-md z-40 animate-in fade-in duration-300"></div>

        <div v-if="activeMobileMenu" class="fixed bottom-[88px] left-4 right-4 glass z-50 p-6 rounded-3xl shadow-[0_-10px_40px_-15px_rgba(0,0,0,0.1)] border-t border-white/20 animate-in slide-in-from-bottom-10 duration-300 flex flex-col max-h-[70vh]">
            
            <div class="w-12 h-1.5 rounded-full bg-muted-foreground/20 mx-auto mb-6 shrink-0"></div>
                
            <div class="flex justify-between items-center mb-6 pb-4 border-b border-border/30 shrink-0">
                <h3 class="font-display font-black text-xl tracking-tight text-foreground flex items-center gap-3">
                    <span v-if="activeMobileMenu === 'inv'" class="text-gradient-primary flex items-center gap-2"><Package class="text-primary"/> Inventario</span>
                    <span v-else-if="activeMobileMenu === 'mov'" class="text-gradient-accent flex items-center gap-2"><ArrowLeftRight class="text-accent"/> Movimientos</span>
                    <span v-else-if="activeMobileMenu === 'com'" class="text-emerald-500 flex items-center gap-2"><Store /> Comercial</span>
                    <span v-else class="text-foreground flex items-center gap-2"><Settings /> Gestión Principal</span>
                </h3>
                <button @click="closeMobileMenu" class="btn btn-ghost btn-sm btn-circle bg-muted/30 hover:bg-muted/60 text-muted-foreground transition-colors">
                    <X :size="22"/>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto scrollbar-hide -mx-2 px-2">
                 <div v-if="activeMobileMenu === 'inv'" class="grid grid-cols-3 gap-4 py-2">
                    <Link @click="closeMobileMenu" :href="route('admin.inventory.index')" class="mobile-item group"><div class="mobile-icon bg-primary/10 text-primary ring-primary/30 group-hover:shadow-primary/20"><Package :size="24" /></div><span class="font-bold">Kardex</span></Link>
                    <Link v-if="canViewProducts" @click="closeMobileMenu" :href="route('admin.products.index')" class="mobile-item group"><div class="mobile-icon bg-primary/10 text-primary ring-primary/30 group-hover:shadow-primary/20"><Tag :size="24" /></div><span>Productos</span></Link>
                    <Link v-if="canViewBrands" @click="closeMobileMenu" :href="route('admin.brands.index')" class="mobile-item group"><div class="mobile-icon bg-muted text-muted-foreground ring-border group-hover:bg-muted/80"><Layers :size="24" /></div><span>Marcas</span></Link>
                    <Link v-if="canViewCategories" @click="closeMobileMenu" :href="route('admin.categories.index')" class="mobile-item group"><div class="mobile-icon bg-muted text-muted-foreground ring-border group-hover:bg-muted/80"><FolderTree :size="24" /></div><span>Categ.</span></Link>
                    <Link v-if="isSuperAdmin" @click="closeMobileMenu" :href="route('admin.bundles.index')" class="mobile-item group"><div class="mobile-icon bg-accent/10 text-accent ring-accent/30 group-hover:shadow-accent/20"><Gift :size="24" /></div><span>Packs</span></Link>
                </div>

                <div v-if="activeMobileMenu === 'mov'" class="grid grid-cols-3 gap-4 py-2">
                    <Link @click="closeMobileMenu" :href="route('admin.transfers.index')" class="mobile-item group"><div class="mobile-icon bg-accent/10 text-accent ring-accent/30"><ArrowLeftRight :size="24" /></div><span>Transfer</span></Link>
                    <Link @click="closeMobileMenu" :href="route('admin.removals.index')" class="mobile-item group"><div class="mobile-icon bg-error/10 text-error ring-error/30"><AlertTriangle :size="24" /></div><span>Bajas</span></Link>
                    <Link @click="closeMobileMenu" :href="route('admin.transformations.index')" class="mobile-item group"><div class="mobile-icon bg-warning/10 text-warning ring-warning/30"><RefreshCw :size="24" /></div><span>Transf.</span></Link>
                    <Link v-if="isSuperAdmin" @click="closeMobileMenu" :href="route('admin.market-zones.index')" class="mobile-item group"><div class="mobile-icon bg-muted text-muted-foreground ring-border"><Map :size="24" /></div><span>Zonas</span></Link>
                </div>

                <div v-if="activeMobileMenu === 'com'" class="grid grid-cols-3 gap-4 py-2">
                    <Link @click="closeMobileMenu" :href="route('admin.orders.kanban')" class="mobile-item group"><div class="mobile-icon bg-emerald-500/10 text-emerald-600 ring-emerald-500/30"><ClipboardList :size="24" /></div><span class="font-bold">Kanban</span></Link>
                    <Link @click="closeMobileMenu" :href="route('admin.purchases.index')" class="mobile-item group"><div class="mobile-icon bg-emerald-500/10 text-emerald-600 ring-emerald-500/30"><ArrowDownToLine :size="24" /></div><span>Ingresos</span></Link>
                    <Link v-if="canViewProviders" @click="closeMobileMenu" :href="route('admin.providers.index')" class="mobile-item group"><div class="mobile-icon bg-muted text-muted-foreground ring-border"><Factory :size="24" /></div><span>Prov.</span></Link>
                    <Link v-if="canManagePrices" @click="closeMobileMenu" :href="route('admin.prices.index')" class="mobile-item group"><div class="mobile-icon bg-muted text-muted-foreground ring-border"><Banknote :size="24" /></div><span>Precios</span></Link>
                </div>

                <div v-if="activeMobileMenu === 'ges'" class="space-y-8 py-2">
                    <div class="grid grid-cols-3 gap-4">
                        <Link @click="closeMobileMenu" :href="route('admin.users.index')" class="mobile-item group"><div class="mobile-icon bg-primary/10 text-primary ring-primary/30"><UserCog :size="24" /></div><span>Equipo</span></Link>
                        <Link v-if="isSuperAdmin" @click="closeMobileMenu" :href="route('admin.branches.index')" class="mobile-item group"><div class="mobile-icon bg-primary/10 text-primary ring-primary/30"><Building2 :size="24" /></div><span>Sucursales</span></Link>
                        <Link v-if="isSuperAdmin || isBranchAdmin" @click="closeMobileMenu" :href="route('admin.drivers.index')" class="mobile-item group"><div class="mobile-icon bg-primary/10 text-primary ring-primary/30"><Truck :size="24" /></div><span>Drivers</span></Link>
                    </div>
                    
                    <Link :href="route('logout')" method="post" as="button" class="w-full flex items-center justify-center gap-3 p-4 rounded-2xl bg-error/5 text-error border border-error/20 hover:bg-error/10 transition-all active:scale-95 shadow-sm font-semibold tracking-wide">
                        <LogOut :size="22" />
                        Cerrar Sesión Actual
                    </Link>
                </div>
            </div>
        </div>
    </div>

    <nav class="md:hidden fixed bottom-0 left-0 right-0 h-[80px] glass border-t border-white/20 grid grid-cols-5 px-2 z-40 pb-safe shadow-[0_-10px_30px_rgba(0,0,0,0.05)] backdrop-blur-xl">
        <button @click="toggleMobileMenu('ges')" v-if="canManageUsers" class="mobile-nav-btn" :class="{'active': activeMobileMenu === 'ges'}">
            <div class="icon-wrapper"><Settings :size="22" /></div>
            <span class="label">Gestión</span>
        </button>
        <button @click="toggleMobileMenu('inv')" v-if="canViewInventory" class="mobile-nav-btn" :class="{'active': activeMobileMenu === 'inv'}">
            <div class="icon-wrapper"><Package :size="22" /></div>
            <span class="label">Stock</span>
        </button>
        <div class="flex items-start justify-center -mt-8 relative z-50">
            <Link :href="route('admin.dashboard')" @click="closeMobileMenu"
                    class="flex items-center justify-center w-16 h-16 rounded-full bg-gradient-to-tr from-primary via-primary to-secondary text-primary-foreground transition-all duration-300 active:scale-90 border-[3px] border-background ring-[3px] ring-primary/20 shadow-[0_8px_20px_rgba(var(--primary),0.4)] hover:shadow-[0_12px_25px_rgba(var(--primary),0.5)] hover:-translate-y-1"
            >
                <Home :size="28" class="drop-shadow-sm" :class="{'animate-pulse-subtle': $page.url.startsWith('/admin/dashboard')}" />
                <span v-if="$page.url.startsWith('/admin/dashboard')" class="absolute -bottom-1 w-1.5 h-1.5 bg-primary-foreground rounded-full"></span>
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
.section-header {
    @apply mt-8 mb-2 px-3 text-[10px] font-bold text-muted-foreground uppercase tracking-widest opacity-80;
}
.section-divider {
    @apply my-4 border-t border-border/40 mx-2;
}
.mobile-item {
    @apply flex flex-col items-center justify-center gap-3 p-3 rounded-2xl transition-all duration-200 active:scale-95 hover:bg-muted/40 cursor-pointer;
}
.mobile-icon {
    @apply w-14 h-14 rounded-2xl flex items-center justify-center shadow-sm ring-1 ring-inset mb-1 transition-all duration-300;
}
.mobile-item:hover .mobile-icon {
    @apply scale-105 shadow-md;
}
.mobile-item span {
    @apply text-xs text-foreground/90 font-medium tracking-tight text-center leading-tight;
}
.mobile-nav-btn {
    @apply flex flex-col items-center justify-center gap-1 h-full text-muted-foreground transition-all duration-300 relative;
}
.mobile-nav-btn .icon-wrapper {
    @apply p-2 rounded-2xl transition-all duration-300;
}
.mobile-nav-btn.active {
    @apply text-primary;
}
.mobile-nav-btn.active .icon-wrapper {
    @apply bg-primary/10 text-primary scale-110;
}
.mobile-nav-btn .label {
    @apply text-[10px] tracking-wide font-medium uppercase transition-all;
}
.mobile-nav-btn.active .label {
    @apply font-bold;
}
.pb-safe {
    padding-bottom: env(safe-area-inset-bottom, 20px);
}
</style>