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

// --- AUTORIZACIÓN UNIFICADA (ROL: admin) ---
const page = usePage();
const user = computed(() => page.props.auth?.user);
const roles = computed(() => user.value?.roles || []);

// Lógica cruda: Si el rol es admin, tiene acceso total a las secciones
const isSuperAdmin = computed(() => roles.value.includes('super_admin'));
const isAdmin = isSuperAdmin;
const canManageUsers = isSuperAdmin;
const canManageDrivers = isSuperAdmin;
const canManageCatalog = isSuperAdmin;
const canManageStock = isSuperAdmin;

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
            <SidebarLink :href="route('admin.dashboard')" :active="$page.url.startsWith('/adm/dashboard')" :collapsed="isCollapsed">
                <template #icon><LayoutDashboard :size="20" /></template>
                Dashboard
            </SidebarLink>
            
            <template v-if="canManageStock">
                <div v-if="!isCollapsed" class="section-header">Operaciones</div>
                <div v-else class="section-divider"></div>
                
                <SidebarLink :href="route('admin.purchases.index')" :active="$page.url.includes('/purchases')" :collapsed="isCollapsed"><template #icon><ShoppingCart :size="20" /></template>Ingresos</SidebarLink>
                <SidebarLink :href="route('admin.inventory.index')" :active="$page.url.includes('/inventory')" :collapsed="isCollapsed"><template #icon><Package :size="20" /></template>Kardex</SidebarLink>
                <SidebarLink :href="route('admin.transfers.index')" :active="$page.url.includes('/transfers')" :collapsed="isCollapsed"><template #icon><Truck :size="20" /></template>Transferencias</SidebarLink>
                <SidebarLink :href="route('admin.removals.index')" :active="$page.url.includes('/removals')" :collapsed="isCollapsed"><template #icon><AlertTriangle :size="20" /></template>Bajas</SidebarLink>
                <SidebarLink :href="route('admin.transformations.index')" :active="$page.url.includes('/transformations')" :collapsed="isCollapsed"><template #icon><RefreshCw :size="20" /></template>Transformaciones</SidebarLink>
                <SidebarLink :href="route('admin.orders.kanban')" :active="$page.url.includes('/orders/kanban')" :collapsed="isCollapsed"><template #icon><ClipboardList :size="20" /></template>Kanban</SidebarLink>
            </template>

            <template v-if="canManageCatalog">
                <div v-if="!isCollapsed" class="section-header">Catálogo</div>
                <div v-else class="section-divider"></div>
                
                <SidebarLink :href="route('admin.products.index')" :active="$page.url.includes('/products')" :collapsed="isCollapsed"><template #icon><Tag :size="20" /></template>Productos</SidebarLink>
                <SidebarLink :href="route('admin.market-zones.index')" :active="$page.url.includes('/market-zones')" :collapsed="isCollapsed"><template #icon><Map :size="20" /></template>Zonas</SidebarLink>
                <SidebarLink :href="route('admin.bundles.index')" :active="$page.url.includes('/bundles')" :collapsed="isCollapsed"><template #icon><Gift :size="20" /></template>Packs</SidebarLink>
                <SidebarLink :href="route('admin.prices.index')" :active="$page.url.includes('/prices')" :collapsed="isCollapsed"><template #icon><Banknote :size="20" /></template>Precios</SidebarLink>
                <SidebarLink :href="route('admin.brands.index')" :active="$page.url.includes('/brands')" :collapsed="isCollapsed"><template #icon><Layers :size="18" /></template>Marcas</SidebarLink>
                <SidebarLink :href="route('admin.categories.index')" :active="$page.url.includes('/categories')" :collapsed="isCollapsed"><template #icon><FolderTree :size="18" /></template>Categorías</SidebarLink>
                <SidebarLink :href="route('admin.providers.index')" :active="$page.url.includes('/providers')" :collapsed="isCollapsed"><template #icon><Factory :size="18" /></template>Proveedores</SidebarLink>
            </template>

            <template v-if="canManageUsers">
                <div v-if="!isCollapsed" class="section-header">Gestión</div>
                <div v-else class="section-divider"></div>
                
                <SidebarLink :href="route('admin.branches.index')" :active="$page.url.includes('/branches')" :collapsed="isCollapsed"><template #icon><Building2 :size="20" /></template>Sucursales</SidebarLink>
                <SidebarLink :href="route('admin.drivers.index')" :active="$page.url.includes('/drivers')" :collapsed="isCollapsed"><template #icon><Truck :size="20" /></template>Conductores</SidebarLink>
                <SidebarLink :href="route('admin.users.index')" :active="$page.url.includes('/users')" :collapsed="isCollapsed"><template #icon><UserCog :size="20" /></template>Equipo</SidebarLink>
            </template>
        </nav>

        <div class="p-4 border-t border-border/40 bg-muted/10 shrink-0">
            <Link :href="route('admin.logout')" method="post" as="button" 
                class="group flex items-center justify-center gap-2 w-full rounded-lg border border-border bg-background p-2 text-sm font-medium transition-all hover:bg-destructive/10 hover:border-destructive/30 hover:text-destructive"
                :class="isCollapsed ? 'aspect-square p-0' : ''">
                <LogOut :size="16" />
                <span v-if="!isCollapsed">Cerrar Sesión</span>
            </Link>
        </div>
    </aside>

    <div class="md:hidden">
        <div v-if="activeMobileMenu" @click="closeMobileMenu" class="fixed inset-0 bg-background/80 backdrop-blur-sm z-40"></div>

        <div v-if="activeMobileMenu" class="fixed bottom-[90px] left-4 right-4 bg-card/95 backdrop-blur-xl z-50 p-6 rounded-3xl shadow-2xl border border-border/50 flex flex-col max-h-[60vh]">
            <div class="w-12 h-1.5 rounded-full bg-muted-foreground/20 mx-auto mb-6 shrink-0"></div>
                
            <div class="flex justify-between items-center mb-6 pb-4 border-b border-border/50 shrink-0">
                <h3 class="font-black text-xl text-foreground flex items-center gap-3">
                    <span v-if="activeMobileMenu === 'inv'" class="flex items-center gap-2 text-primary"><Package /> Stock</span>
                    <span v-else-if="activeMobileMenu === 'mov'" class="flex items-center gap-2 text-primary"><ArrowLeftRight /> Flujos</span>
                    <span v-else class="flex items-center gap-2 text-primary"><Settings /> Gestión</span>
                </h3>
                <button @click="closeMobileMenu" class="p-2 rounded-full bg-muted/50"><X :size="20"/></button>
            </div>

            <div class="flex-1 overflow-y-auto">
                 <div v-if="activeMobileMenu === 'inv'" class="grid grid-cols-3 gap-4">
                    <Link @click="closeMobileMenu" :href="route('admin.inventory.index')" class="mobile-item"><div class="mobile-icon"><Package /></div><span>Kardex</span></Link>
                    <Link @click="closeMobileMenu" :href="route('admin.products.index')" class="mobile-item"><div class="mobile-icon"><Tag /></div><span>Prod.</span></Link>
                    <Link @click="closeMobileMenu" :href="route('admin.categories.index')" class="mobile-item"><div class="mobile-icon"><FolderTree /></div><span>Categ.</span></Link>
                </div>

                <div v-if="activeMobileMenu === 'mov'" class="grid grid-cols-3 gap-4">
                    <Link @click="closeMobileMenu" :href="route('admin.transfers.index')" class="mobile-item"><div class="mobile-icon"><ArrowLeftRight /></div><span>Transf.</span></Link>
                    <Link @click="closeMobileMenu" :href="route('admin.removals.index')" class="mobile-item"><div class="mobile-icon"><AlertTriangle /></div><span>Bajas</span></Link>
                    <Link @click="closeMobileMenu" :href="route('admin.orders.kanban')" class="mobile-item"><div class="mobile-icon"><ClipboardList /></div><span>Kanban</span></Link>
                </div>

                <div v-if="activeMobileMenu === 'ges'" class="space-y-6">
                    <div class="grid grid-cols-3 gap-4">
                        <Link @click="closeMobileMenu" :href="route('admin.users.index')" class="mobile-item"><div class="mobile-icon"><UserCog /></div><span>Equipo</span></Link>
                        <Link @click="closeMobileMenu" :href="route('admin.branches.index')" class="mobile-item"><div class="mobile-icon"><Building2 /></div><span>Sucurs.</span></Link>
                        <Link @click="closeMobileMenu" :href="route('admin.drivers.index')" class="mobile-item"><div class="mobile-icon"><Truck /></div><span>Drivers</span></Link>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <nav class="md:hidden fixed bottom-0 left-0 right-0 h-[80px] bg-card/90 backdrop-blur-xl border-t border-border z-40 pb-safe grid grid-cols-5 px-2">
        <button @click="toggleMobileMenu('ges')" v-if="isAdmin" class="mobile-nav-btn" :class="{'active': activeMobileMenu === 'ges'}">
            <div class="icon-wrapper"><Settings :size="22" /></div>
            <span class="label">Gestión</span>
        </button>
        <button @click="toggleMobileMenu('inv')" v-if="isAdmin" class="mobile-nav-btn" :class="{'active': activeMobileMenu === 'inv'}">
            <div class="icon-wrapper"><Package :size="22" /></div>
            <span class="label">Stock</span>
        </button>
        
        <div class="flex items-start justify-center -mt-6 relative z-50">
            <Link :href="route('admin.dashboard')" @click="closeMobileMenu"
                  class="flex items-center justify-center w-16 h-16 rounded-full bg-primary text-primary-foreground border-[4px] border-background shadow-lg"
            >
                <Home :size="26" />
            </Link>
        </div>

        <button @click="toggleMobileMenu('mov')" v-if="isAdmin" class="mobile-nav-btn" :class="{'active': activeMobileMenu === 'mov'}">
            <div class="icon-wrapper"><ArrowLeftRight :size="22" /></div>
            <span class="label">Flujos</span>
        </button>
        <button @click="toggleMobileMenu('com')" v-if="isAdmin" class="mobile-nav-btn" :class="{'active': activeMobileMenu === 'com'}">
            <div class="icon-wrapper"><Store :size="22" /></div>
            <span class="label">Catálogo</span>
        </button>
    </nav>
</template>

<style scoped>
.section-header {
    @apply mt-6 mb-2 px-3 text-[10px] font-bold text-muted-foreground uppercase tracking-widest opacity-80;
}
.section-divider {
    @apply my-4 border-t border-border/40 mx-2;
}
.mobile-item {
    @apply flex flex-col items-center justify-center gap-2 p-2 transition-all active:scale-95;
}
.mobile-icon {
    @apply w-14 h-14 rounded-2xl flex items-center justify-center bg-muted/50 text-foreground border border-border;
}
.mobile-item span {
    @apply text-[11px] text-muted-foreground font-medium text-center;
}
.mobile-nav-btn {
    @apply flex flex-col items-center justify-center gap-1 h-full text-muted-foreground transition-all duration-200 pt-2;
}
.mobile-nav-btn.active {
    @apply text-primary;
}
.mobile-nav-btn.active .icon-wrapper {
    @apply bg-primary/10 -translate-y-1;
}
.mobile-nav-btn .label {
    @apply text-[10px] font-medium;
}
.pb-safe {
    padding-bottom: env(safe-area-inset-bottom, 20px);
}
</style>