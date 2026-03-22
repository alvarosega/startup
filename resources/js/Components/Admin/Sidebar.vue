<script setup>
import { computed, ref } from 'vue';
import { usePage, Link } from '@inertiajs/vue3';
import { 
    LayoutDashboard, ShoppingCart, Package, Truck, AlertTriangle, 
    RefreshCw, Tag, Layers, Factory, LogOut,
    Banknote, Gift, ClipboardList, Settings, X, 
    Building2, FolderTree, UserCog, Map, 
    ArrowLeftRight, Store, Home, Megaphone, Radar
} from 'lucide-vue-next';

const activeMobileMenu = ref(null);

const page = usePage();
const user = computed(() => page.props.auth?.user);
const roles = computed(() => user.value?.roles || []);

const isSuperAdmin = computed(() => roles.value.includes('super_admin'));
const isAdmin = isSuperAdmin;
const canManageAds = isSuperAdmin;
const canManageUsers = isSuperAdmin;
const canManageDrivers = isSuperAdmin;
const canManageCatalog = isSuperAdmin;
const canManageStock = isSuperAdmin;

const toggleMobileMenu = (menu) => {
    activeMobileMenu.value = activeMobileMenu.value === menu ? null : menu;
};

const closeMobileMenu = () => {
    activeMobileMenu.value = null;
};
</script>

<template>
    <!-- Desktop Sidebar -->
    <aside class="hidden md:block fixed top-0 left-0 h-full z-50 w-[250px] pointer-events-none">
        
        <!-- Sidebar background -->
        <div class="absolute inset-y-0 left-0 w-[72px] bg-card border-r border-border pointer-events-auto shadow-sm"></div>

        <!-- Logo -->
        <div class="absolute top-0 left-0 w-[72px] h-16 flex items-center justify-center z-50 pointer-events-auto">
            <span class="font-sans font-bold text-2xl text-primary">DU</span>
        </div>

        <!-- Navigation -->
        <div class="absolute top-16 bottom-0 left-0 w-[250px] overflow-y-auto overflow-x-hidden pointer-events-none [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
            
            <nav class="flex flex-col py-6 gap-2 w-[72px] pointer-events-auto">
                
                <!-- Dashboard -->
                <Link :href="route('admin.dashboard')" class="group relative flex items-center justify-center w-full px-3 py-2 rounded-lg transition-all duration-150 hover:bg-primary/10">
                    <LayoutDashboard :size="20" class="text-muted-foreground transition-colors duration-150 group-hover:text-primary" />
                    <span class="absolute left-[80px] px-3 py-1.5 bg-card border border-border rounded-md text-xs font-medium text-foreground shadow-sm opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-150 whitespace-nowrap z-50">
                        Dashboard
                    </span>
                </Link>

                <!-- Stock Management Section -->
                <template v-if="canManageStock">
                    <div class="w-6 h-px bg-border/50 mx-auto my-2"></div>
                    
                    <Link :href="route('admin.purchases.index')" class="group relative flex items-center justify-center w-full px-3 py-2 rounded-lg transition-all duration-150 hover:bg-primary/10">
                        <ShoppingCart :size="20" class="text-muted-foreground transition-colors duration-150 group-hover:text-primary" />
                        <span class="absolute left-[80px] px-3 py-1.5 bg-card border border-border rounded-md text-xs font-medium text-foreground shadow-sm opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-150 whitespace-nowrap z-50">Ingresos</span>
                    </Link>
                    
                    <Link :href="route('admin.inventory.index')" class="group relative flex items-center justify-center w-full px-3 py-2 rounded-lg transition-all duration-150 hover:bg-primary/10">
                        <Package :size="20" class="text-muted-foreground transition-colors duration-150 group-hover:text-primary" />
                        <span class="absolute left-[80px] px-3 py-1.5 bg-card border border-border rounded-md text-xs font-medium text-foreground shadow-sm opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-150 whitespace-nowrap z-50">Stock Base</span>
                    </Link>

                    <Link :href="route('admin.transfers.index')" class="group relative flex items-center justify-center w-full px-3 py-2 rounded-lg transition-all duration-150 hover:bg-primary/10">
                        <Truck :size="20" class="text-muted-foreground transition-colors duration-150 group-hover:text-primary" />
                        <span class="absolute left-[80px] px-3 py-1.5 bg-card border border-border rounded-md text-xs font-medium text-foreground shadow-sm opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-150 whitespace-nowrap z-50">Transferencias</span>
                    </Link>

                    <Link :href="route('admin.removals.index')" class="group relative flex items-center justify-center w-full px-3 py-2 rounded-lg transition-all duration-150 hover:bg-primary/10">
                        <AlertTriangle :size="20" class="text-muted-foreground transition-colors duration-150 group-hover:text-primary" />
                        <span class="absolute left-[80px] px-3 py-1.5 bg-card border border-border rounded-md text-xs font-medium text-foreground shadow-sm opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-150 whitespace-nowrap z-50">Bajas</span>
                    </Link>

                    <Link :href="route('admin.transformations.index')" class="group relative flex items-center justify-center w-full px-3 py-2 rounded-lg transition-all duration-150 hover:bg-primary/10">
                        <RefreshCw :size="20" class="text-muted-foreground transition-colors duration-150 group-hover:text-primary" />
                        <span class="absolute left-[80px] px-3 py-1.5 bg-card border border-border rounded-md text-xs font-medium text-foreground shadow-sm opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-150 whitespace-nowrap z-50">Transformaciones</span>
                    </Link>

                    <Link :href="route('admin.orders.index')" class="group relative flex items-center justify-center w-full px-3 py-2 rounded-lg transition-all duration-150 hover:bg-primary/10">
                        <ClipboardList :size="20" class="text-muted-foreground transition-colors duration-150 group-hover:text-primary" />
                        <span class="absolute left-[80px] px-3 py-1.5 bg-card border border-border rounded-md text-xs font-medium text-foreground shadow-sm opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-150 whitespace-nowrap z-50">Órdenes</span>
                    </Link>

                    <!-- Radar destacado -->
                    <Link :href="route('admin.logistics.monitor')" class="group relative flex items-center justify-center w-full px-3 py-2 rounded-lg transition-all duration-150 bg-primary/5 hover:bg-primary/10">
                        <Radar :size="20" class="text-primary" />
                        <span class="absolute left-[80px] px-3 py-1.5 bg-card border border-border rounded-md text-xs font-medium text-foreground shadow-sm opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-150 whitespace-nowrap z-50">Radar (Vivo)</span>
                    </Link>
                </template>

                <!-- Catalog Management Section -->
                <template v-if="canManageCatalog">
                    <div class="w-6 h-px bg-border/50 mx-auto my-2"></div>
                    
                    <Link :href="route('admin.products.index')" class="group relative flex items-center justify-center w-full px-3 py-2 rounded-lg transition-all duration-150 hover:bg-primary/10">
                        <Tag :size="20" class="text-muted-foreground transition-colors duration-150 group-hover:text-primary" />
                        <span class="absolute left-[80px] px-3 py-1.5 bg-card border border-border rounded-md text-xs font-medium text-foreground shadow-sm opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-150 whitespace-nowrap z-50">Productos</span>
                    </Link>
                    
                    <Link :href="route('admin.market-zones.index')" class="group relative flex items-center justify-center w-full px-3 py-2 rounded-lg transition-all duration-150 hover:bg-primary/10">
                        <Map :size="20" class="text-muted-foreground transition-colors duration-150 group-hover:text-primary" />
                        <span class="absolute left-[80px] px-3 py-1.5 bg-card border border-border rounded-md text-xs font-medium text-foreground shadow-sm opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-150 whitespace-nowrap z-50">Zonas</span>
                    </Link>

                    <Link :href="route('admin.bundles.index')" class="group relative flex items-center justify-center w-full px-3 py-2 rounded-lg transition-all duration-150 hover:bg-primary/10">
                        <Gift :size="20" class="text-muted-foreground transition-colors duration-150 group-hover:text-primary" />
                        <span class="absolute left-[80px] px-3 py-1.5 bg-card border border-border rounded-md text-xs font-medium text-foreground shadow-sm opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-150 whitespace-nowrap z-50">Packs</span>
                    </Link>

                    <Link :href="route('admin.prices.index')" class="group relative flex items-center justify-center w-full px-3 py-2 rounded-lg transition-all duration-150 hover:bg-primary/10">
                        <Banknote :size="20" class="text-muted-foreground transition-colors duration-150 group-hover:text-primary" />
                        <span class="absolute left-[80px] px-3 py-1.5 bg-card border border-border rounded-md text-xs font-medium text-foreground shadow-sm opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-150 whitespace-nowrap z-50">Precios</span>
                    </Link>

                    <Link :href="route('admin.brands.index')" class="group relative flex items-center justify-center w-full px-3 py-2 rounded-lg transition-all duration-150 hover:bg-primary/10">
                        <Layers :size="20" class="text-muted-foreground transition-colors duration-150 group-hover:text-primary" />
                        <span class="absolute left-[80px] px-3 py-1.5 bg-card border border-border rounded-md text-xs font-medium text-foreground shadow-sm opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-150 whitespace-nowrap z-50">Marcas</span>
                    </Link>

                    <Link :href="route('admin.categories.index')" class="group relative flex items-center justify-center w-full px-3 py-2 rounded-lg transition-all duration-150 hover:bg-primary/10">
                        <FolderTree :size="20" class="text-muted-foreground transition-colors duration-150 group-hover:text-primary" />
                        <span class="absolute left-[80px] px-3 py-1.5 bg-card border border-border rounded-md text-xs font-medium text-foreground shadow-sm opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-150 whitespace-nowrap z-50">Categorías</span>
                    </Link>

                    <Link :href="route('admin.providers.index')" class="group relative flex items-center justify-center w-full px-3 py-2 rounded-lg transition-all duration-150 hover:bg-primary/10">
                        <Factory :size="20" class="text-muted-foreground transition-colors duration-150 group-hover:text-primary" />
                        <span class="absolute left-[80px] px-3 py-1.5 bg-card border border-border rounded-md text-xs font-medium text-foreground shadow-sm opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-150 whitespace-nowrap z-50">Proveedores</span>
                    </Link>
                </template>
                <template v-if="canManageAds">
                    <div class="w-6 h-px bg-border/50 mx-auto my-2"></div>
                    
                    <Link :href="route('admin.retail-media.ad-creatives.index')" class="group relative flex items-center justify-center w-full px-3 py-2 rounded-lg transition-all duration-150 hover:bg-primary/10">
                        <Megaphone :size="20" class="text-muted-foreground transition-colors duration-150 group-hover:text-primary" />
                        <span class="absolute left-[80px] px-3 py-1.5 bg-card border border-border rounded-md text-xs font-medium text-foreground shadow-sm opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-150 whitespace-nowrap z-50">
                            Retail Media
                        </span>
                    </Link>
                </template>
                <!-- User Management Section -->
                <template v-if="canManageUsers">
                    <div class="w-6 h-px bg-border/50 mx-auto my-2"></div>
                    
                    <Link :href="route('admin.branches.index')" class="group relative flex items-center justify-center w-full px-3 py-2 rounded-lg transition-all duration-150 hover:bg-primary/10">
                        <Building2 :size="20" class="text-muted-foreground transition-colors duration-150 group-hover:text-primary" />
                        <span class="absolute left-[80px] px-3 py-1.5 bg-card border border-border rounded-md text-xs font-medium text-foreground shadow-sm opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-150 whitespace-nowrap z-50">Sucursales</span>
                    </Link>
                    
                    <Link :href="route('admin.drivers.index')" class="group relative flex items-center justify-center w-full px-3 py-2 rounded-lg transition-all duration-150 hover:bg-primary/10">
                        <Truck :size="20" class="text-muted-foreground transition-colors duration-150 group-hover:text-primary" />
                        <span class="absolute left-[80px] px-3 py-1.5 bg-card border border-border rounded-md text-xs font-medium text-foreground shadow-sm opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-150 whitespace-nowrap z-50">Conductores</span>
                    </Link>

                    <Link :href="route('admin.users.index')" class="group relative flex items-center justify-center w-full px-3 py-2 rounded-lg transition-all duration-150 hover:bg-primary/10">
                        <UserCog :size="20" class="text-muted-foreground transition-colors duration-150 group-hover:text-primary" />
                        <span class="absolute left-[80px] px-3 py-1.5 bg-card border border-border rounded-md text-xs font-medium text-foreground shadow-sm opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-150 whitespace-nowrap z-50">Equipo</span>
                    </Link>
                </template>
                
                <div class="pb-12"></div>
            </nav>
        </div>

        <!-- Logout Button -->
        <div class="absolute bottom-0 left-0 w-[72px] h-16 border-t border-border bg-card z-50 pointer-events-auto">
            <Link :href="route('admin.logout')" method="post" as="button" class="flex items-center justify-center h-full w-full transition-colors duration-150 hover:bg-destructive/10 group">
                <LogOut :size="20" class="text-destructive transition-transform duration-150 group-hover:scale-105" />
                <span class="absolute left-[80px] px-3 py-1.5 bg-card border border-destructive/30 rounded-md text-xs font-medium text-destructive shadow-sm opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-150 whitespace-nowrap z-50">
                    Cerrar Sesión
                </span>
            </Link>
        </div>
    </aside>

    <!-- Mobile Navigation -->
    <div class="md:hidden">
        <!-- Overlay -->
        <div v-if="activeMobileMenu" @click="closeMobileMenu" class="fixed inset-0 bg-background/80 z-40 cursor-pointer backdrop-blur-sm transition-opacity duration-200"></div>

        <!-- Mobile Menu Panel -->
        <div v-if="activeMobileMenu" class="fixed bottom-[90px] left-4 right-4 bg-card z-50 p-6 flex flex-col max-h-[70vh] rounded-xl border border-border shadow-lg">
            
            <!-- Menu Header -->
            <div class="flex justify-between items-center mb-4 pb-4 border-b border-border">
                <h3 class="font-sans font-bold text-lg text-foreground">
                    <span v-if="activeMobileMenu === 'inv'">Stock</span>
                    <span v-else-if="activeMobileMenu === 'mov'">Flujos</span>
                    <span v-else-if="activeMobileMenu === 'com'">Catálogo</span>
                    <span v-else>Gestión</span>
                </h3>
                <button @click="closeMobileMenu" class="p-1 rounded-lg hover:bg-muted/10 transition-colors">
                    <X :size="18" class="text-muted-foreground" />
                </button>
            </div>

            <!-- Menu Content -->
            <div class="flex-1 overflow-y-auto pr-2">
                 <!-- Inventory Menu -->
                 <div v-if="activeMobileMenu === 'inv'" class="grid grid-cols-2 gap-3">
                    <Link @click="closeMobileMenu" :href="route('admin.purchases.index')" class="bg-background border border-border p-3 rounded-lg flex flex-col items-center justify-center gap-2 transition-all duration-150 hover:border-primary/30 hover:bg-primary/5">
                        <ShoppingCart size="20" class="text-muted-foreground" />
                        <span class="text-xs font-medium text-center">Ingresos</span>
                    </Link>
                    <Link @click="closeMobileMenu" :href="route('admin.inventory.index')" class="bg-background border border-border p-3 rounded-lg flex flex-col items-center justify-center gap-2 transition-all duration-150 hover:border-primary/30 hover:bg-primary/5">
                        <Package size="20" class="text-muted-foreground" />
                        <span class="text-xs font-medium text-center">Stock Base</span>
                    </Link>
                    <Link @click="closeMobileMenu" :href="route('admin.transformations.index')" class="bg-background border border-border p-3 rounded-lg flex flex-col items-center justify-center gap-2 transition-all duration-150 hover:border-primary/30 hover:bg-primary/5">
                        <RefreshCw size="20" class="text-muted-foreground" />
                        <span class="text-xs font-medium text-center">Transform.</span>
                    </Link>
                </div>

                <!-- Movements Menu -->
                <div v-if="activeMobileMenu === 'mov'" class="grid grid-cols-2 gap-3">
                    <Link @click="closeMobileMenu" :href="route('admin.transfers.index')" class="bg-background border border-border p-3 rounded-lg flex flex-col items-center justify-center gap-2 transition-all duration-150 hover:border-primary/30 hover:bg-primary/5">
                        <Truck size="20" class="text-muted-foreground" />
                        <span class="text-xs font-medium text-center">Transf.</span>
                    </Link>
                    <Link @click="closeMobileMenu" :href="route('admin.removals.index')" class="bg-background border border-border p-3 rounded-lg flex flex-col items-center justify-center gap-2 transition-all duration-150 hover:border-primary/30 hover:bg-primary/5">
                        <AlertTriangle size="20" class="text-muted-foreground" />
                        <span class="text-xs font-medium text-center">Bajas</span>
                    </Link>
                    <Link @click="closeMobileMenu" :href="route('admin.orders.index')" class="bg-background border border-border p-3 rounded-lg flex flex-col items-center justify-center gap-2 transition-all duration-150 hover:border-primary/30 hover:bg-primary/5">
                        <ClipboardList size="20" class="text-muted-foreground" />
                        <span class="text-xs font-medium text-center">Ordenes</span>
                    </Link>
                    <Link @click="closeMobileMenu" :href="route('admin.logistics.monitor')" class="bg-background border border-primary/30 p-3 rounded-lg flex flex-col items-center justify-center gap-2 transition-all duration-150 hover:bg-primary/5">
                        <Radar size="20" class="text-primary" />
                        <span class="text-xs font-medium text-center text-primary">Radar</span>
                    </Link>
                </div>

                <!-- Catalog Menu -->
                <div v-if="activeMobileMenu === 'com'" class="grid grid-cols-2 gap-3">
                    <Link @click="closeMobileMenu" :href="route('admin.products.index')" class="bg-background border border-border p-3 rounded-lg flex flex-col items-center justify-center gap-2 transition-all duration-150 hover:border-primary/30 hover:bg-primary/5">
                        <Tag size="20" class="text-muted-foreground" />
                        <span class="text-xs font-medium text-center">Prod.</span>
                    </Link>
                    <Link @click="closeMobileMenu" :href="route('admin.market-zones.index')" class="bg-background border border-border p-3 rounded-lg flex flex-col items-center justify-center gap-2 transition-all duration-150 hover:border-primary/30 hover:bg-primary/5">
                        <Map size="20" class="text-muted-foreground" />
                        <span class="text-xs font-medium text-center">Zonas</span>
                    </Link>
                    <Link @click="closeMobileMenu" :href="route('admin.bundles.index')" class="bg-background border border-border p-3 rounded-lg flex flex-col items-center justify-center gap-2 transition-all duration-150 hover:border-primary/30 hover:bg-primary/5">
                        <Gift size="20" class="text-muted-foreground" />
                        <span class="text-xs font-medium text-center">Packs</span>
                    </Link>
                    <Link @click="closeMobileMenu" :href="route('admin.prices.index')" class="bg-background border border-border p-3 rounded-lg flex flex-col items-center justify-center gap-2 transition-all duration-150 hover:border-primary/30 hover:bg-primary/5">
                        <Banknote size="20" class="text-muted-foreground" />
                        <span class="text-xs font-medium text-center">Precios</span>
                    </Link>
                    <Link @click="closeMobileMenu" :href="route('admin.brands.index')" class="bg-background border border-border p-3 rounded-lg flex flex-col items-center justify-center gap-2 transition-all duration-150 hover:border-primary/30 hover:bg-primary/5">
                        <Layers size="20" class="text-muted-foreground" />
                        <span class="text-xs font-medium text-center">Marcas</span>
                    </Link>
                    <Link @click="closeMobileMenu" :href="route('admin.categories.index')" class="bg-background border border-border p-3 rounded-lg flex flex-col items-center justify-center gap-2 transition-all duration-150 hover:border-primary/30 hover:bg-primary/5">
                        <FolderTree size="20" class="text-muted-foreground" />
                        <span class="text-xs font-medium text-center">Categ.</span>
                    </Link>
                    <Link @click="closeMobileMenu" :href="route('admin.providers.index')" class="bg-background border border-border p-3 rounded-lg flex flex-col items-center justify-center gap-2 transition-all duration-150 hover:border-primary/30 hover:bg-primary/5">
                        <Factory size="20" class="text-muted-foreground" />
                        <span class="text-xs font-medium text-center">Prov.</span>
                    </Link>
                    <Link @click="closeMobileMenu" :href="route('admin.retail-media.ad-creatives.index')" class="bg-background border border-primary/30 p-3 rounded-lg flex flex-col items-center justify-center gap-2 transition-all duration-150 hover:bg-primary/5 col-span-2">
                        <Megaphone size="20" class="text-primary" />
                        <span class="text-xs font-medium text-center text-primary">Retail Media (Ads)</span>
                    </Link>
                </div>

                <!-- Management Menu -->
                <div v-if="activeMobileMenu === 'ges'" class="flex flex-col gap-3">
                    <div class="grid grid-cols-2 gap-3">
                        <Link @click="closeMobileMenu" :href="route('admin.users.index')" class="bg-background border border-border p-3 rounded-lg flex flex-col items-center justify-center gap-2 transition-all duration-150 hover:border-primary/30 hover:bg-primary/5">
                            <UserCog size="20" class="text-muted-foreground" />
                            <span class="text-xs font-medium text-center">Equipo</span>
                        </Link>
                        <Link @click="closeMobileMenu" :href="route('admin.branches.index')" class="bg-background border border-border p-3 rounded-lg flex flex-col items-center justify-center gap-2 transition-all duration-150 hover:border-primary/30 hover:bg-primary/5">
                            <Building2 size="20" class="text-muted-foreground" />
                            <span class="text-xs font-medium text-center">Sucursales</span>
                        </Link>
                        <Link @click="closeMobileMenu" :href="route('admin.drivers.index')" class="bg-background border border-border p-3 rounded-lg flex flex-col items-center justify-center gap-2 transition-all duration-150 hover:border-primary/30 hover:bg-primary/5">
                            <Truck size="20" class="text-muted-foreground" />
                            <span class="text-xs font-medium text-center">Drivers</span>
                        </Link>
                    </div>
                    
                    <!-- Logout in mobile -->
                    <div class="mt-4 pt-4 border-t border-border">
                        <Link :href="route('admin.logout')" method="post" as="button" class="w-full bg-destructive/10 border border-destructive/30 p-3 rounded-lg flex items-center justify-center gap-2 transition-all duration-150 hover:bg-destructive/20">
                            <LogOut :size="16" class="text-destructive" />
                            <span class="font-medium text-sm text-destructive">Cerrar Sesión</span>
                        </Link>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Mobile Bottom Navigation -->
    <nav class="md:hidden fixed bottom-0 left-0 right-0 h-[72px] bg-card/95 backdrop-blur-sm border-t border-border z-40 grid grid-cols-5 px-2 items-center shadow-md">
        
        <!-- Management button -->
        <button tabindex="0" @click="toggleMobileMenu('ges')" v-if="isAdmin" class="flex flex-col items-center justify-center h-full transition-colors duration-150 hover:text-primary">
            <Settings size="20" class="text-muted-foreground" />
            <span class="text-[10px] font-medium mt-1 text-muted-foreground">Gestión</span>
        </button>
        
        <!-- Inventory button -->
        <button tabindex="0" @click="toggleMobileMenu('inv')" v-if="isAdmin" class="flex flex-col items-center justify-center h-full transition-colors duration-150 hover:text-primary">
            <Package size="20" class="text-muted-foreground" />
            <span class="text-[10px] font-medium mt-1 text-muted-foreground">Stock</span>
        </button>
        
        <!-- Home button -->
        <div class="flex items-center justify-center -mt-4">
            <Link :href="route('admin.dashboard')" @click="closeMobileMenu" class="w-12 h-12 bg-primary text-primary-foreground rounded-full flex items-center justify-center shadow-md transition-transform duration-150 hover:scale-105 active:scale-95">
                <Home size="22" />
            </Link>
        </div>

        <!-- Movements button -->
        <button tabindex="0" @click="toggleMobileMenu('mov')" v-if="isAdmin" class="flex flex-col items-center justify-center h-full transition-colors duration-150 hover:text-primary">
            <ArrowLeftRight size="20" class="text-muted-foreground" />
            <span class="text-[10px] font-medium mt-1 text-muted-foreground">Flujos</span>
        </button>
        
        <!-- Catalog button -->
        <button tabindex="0" @click="toggleMobileMenu('com')" v-if="isAdmin" class="flex flex-col items-center justify-center h-full transition-colors duration-150 hover:text-primary">
            <Store size="20" class="text-muted-foreground" />
            <span class="text-[10px] font-medium mt-1 text-muted-foreground">Catálogo</span>
        </button>
    </nav>
</template>

<style scoped>
/* Scrollbar personalizada */
.scrollbar-thin::-webkit-scrollbar {
    width: 2px;
}

.scrollbar-thin::-webkit-scrollbar-track {
    background: hsl(var(--border) / 0.2);
}

.scrollbar-thin::-webkit-scrollbar-thumb {
    background: hsl(var(--primary));
    border-radius: 9999px;
}

.scrollbar-thin::-webkit-scrollbar-thumb:hover {
    background: hsl(var(--primary) / 0.8);
}
</style>