<script setup>
    import { computed, ref, watch } from 'vue';
    import { usePage, Link } from '@inertiajs/vue3';
    import SidebarLink from '@/Components/Admin/SidebarLink.vue';
    import { 
        LayoutDashboard, ShoppingCart, Package, Truck, AlertTriangle, 
        RefreshCw, Tag, Layers, Box, Factory, MapPin, Users, LogOut,
        Menu, ChevronLeft, ChevronRight, Banknote, Gift, ClipboardList, Home,
        Settings, X, Building2, FolderTree, UserCog
    } from 'lucide-vue-next';
    
    const emit = defineEmits(['toggle-collapse']);
    
    // --- ESTADO INTERNO ---
    const isCollapsed = ref(localStorage.getItem('sidebarCollapsed') === 'true');
    const activeMobileMenu = ref(null);
    
    // --- PERMISOS Y USUARIO ---
    const page = usePage();
    const user = computed(() => page.props.auth.user);
    const roles = computed(() => user.value.roles || []);
    
    // ROLES
    const isSuperAdmin = computed(() => roles.value.includes('super_admin'));
    const isBranchAdmin = computed(() => roles.value.includes('branch_admin'));
    const isLogistics = computed(() => roles.value.includes('logistics_manager'));
    const isInventoryManager = computed(() => roles.value.includes('inventory_manager'));
    const isFinanceManager = computed(() => roles.value.includes('finance_manager'));
    
    // PERMISOS COMPUTADOS
    const canManageCatalog = computed(() => isSuperAdmin.value || isLogistics.value);
    const canViewProducts = computed(() => canManageCatalog.value || isBranchAdmin.value || isInventoryManager.value || isFinanceManager.value);
    const canManagePrices = computed(() => isSuperAdmin.value || isFinanceManager.value);
    const canViewBrands = computed(() => canManageCatalog.value || isBranchAdmin.value || isInventoryManager.value || isFinanceManager.value);
    const canViewCategories = computed(() => canManageCatalog.value || isBranchAdmin.value || isInventoryManager.value || isFinanceManager.value);
    const canViewProviders = computed(() => canManageCatalog.value || isBranchAdmin.value || isInventoryManager.value || isFinanceManager.value);
    const showCatalogSection = computed(() => canManageCatalog.value || canViewProviders.value || canViewCategories.value || canViewBrands.value || canViewProducts.value);
    const canManageStock = computed(() => isSuperAdmin.value || isLogistics.value || isBranchAdmin.value || isInventoryManager.value);
    const canManageUsers = computed(() => isSuperAdmin.value || isBranchAdmin.value);
    
    // --- ACCIONES ---
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
        <!-- SIDEBAR DESKTOP -->
        <aside 
            class="hidden md:flex flex-col fixed h-full z-30 bg-card border-r border-border shadow-md transition-all duration-base ease-elastic will-change-[width]"
            :class="isCollapsed ? 'w-[88px]' : 'w-[280px]'"
        >
            <!-- HEADER -->
            <div class="h-20 flex items-center justify-between px-6 border-b border-border/50 shrink-0">
                <div class="font-display font-black tracking-tighter italic text-2xl transition-all duration-base overflow-hidden whitespace-nowrap text-foreground"
                     :class="isCollapsed ? 'opacity-0 w-0 scale-95' : 'opacity-100 scale-100'">
                    <span class="text-gradient-primary">BOLIVIA</span><span class="text-primary">LOGISTICS</span>
                </div>
                
                <button @click="toggleSidebar" 
                        class="btn btn-ghost btn-sm p-2 rounded-full"
                        :class="isCollapsed ? 'mx-auto' : ''"
                        aria-label="Toggle sidebar">
                    <ChevronRight v-if="isCollapsed" :size="20" class="transition-transform duration-fast" />
                    <ChevronLeft v-else :size="20" class="transition-transform duration-fast" />
                </button>
            </div>
    
            <!-- NAVIGATION -->
            <nav class="flex-1 px-3 py-6 space-y-1 overflow-y-auto overflow-x-hidden scrollbar-hide">
                <!-- Dashboard -->
                <SidebarLink :href="route('admin.dashboard')" :active="$page.url.startsWith('/admin/dashboard')" :collapsed="isCollapsed">
                    <template #icon><LayoutDashboard :size="20" class="transition-transform group-hover:scale-110" /></template>
                    Dashboard
                </SidebarLink>
    
                <!-- OPERACIONES -->
                <template v-if="canManageStock">
                    <div v-if="!isCollapsed" class="mt-8 mb-3 px-3 text-xs font-bold text-muted-foreground font-display uppercase tracking-widest whitespace-nowrap overflow-hidden">
                        Operaciones
                    </div>
                    <div v-else class="my-4 border-t border-border/30"></div>
                    
                    <SidebarLink :href="route('admin.purchases.index')" :active="$page.url.startsWith('/admin/purchases')" :collapsed="isCollapsed">
                        <template #icon><ShoppingCart :size="20" class="transition-transform group-hover:scale-110" /></template>
                        Ingresos
                    </SidebarLink>
                    <SidebarLink :href="route('admin.inventory.index')" :active="$page.url.startsWith('/admin/inventory')" :collapsed="isCollapsed">
                        <template #icon><Package :size="20" class="transition-transform group-hover:scale-110" /></template>
                        Kardex
                    </SidebarLink>
                    <SidebarLink :href="route('admin.transfers.index')" :active="$page.url.startsWith('/admin/transfers')" :collapsed="isCollapsed">
                        <template #icon><Truck :size="20" class="transition-transform group-hover:scale-110" /></template>
                        Transferencias
                    </SidebarLink>
                    <SidebarLink :href="route('admin.removals.index')" :active="$page.url.startsWith('/admin/removals')" :collapsed="isCollapsed">
                        <template #icon><AlertTriangle :size="20" class="transition-transform group-hover:scale-110" /></template>
                        Bajas
                    </SidebarLink>
                    <SidebarLink :href="route('admin.transformations.index')" :active="$page.url.startsWith('/admin/transformations')" :collapsed="isCollapsed">
                        <template #icon><RefreshCw :size="20" class="transition-transform group-hover:scale-110" /></template>
                        Transformaciones
                    </SidebarLink>
                    <SidebarLink :href="route('admin.orders.kanban')" :active="$page.url.startsWith('/admin/orders/kanban')" :collapsed="isCollapsed">
                        <template #icon><ClipboardList :size="20" class="transition-transform group-hover:scale-110" /></template>
                        Tablero Kanban
                    </SidebarLink>
                </template>
    
                <!-- CATÁLOGO -->
                <template v-if="showCatalogSection">
                    <div v-if="!isCollapsed" class="mt-8 mb-3 px-3 text-xs font-bold text-muted-foreground font-display uppercase tracking-widest whitespace-nowrap overflow-hidden">
                        Catálogo
                    </div>
                    <div v-else class="my-4 border-t border-border/30"></div>
    
                    <SidebarLink v-if="canViewProducts" :href="route('admin.products.index')" :active="$page.url.startsWith('/admin/products')" :collapsed="isCollapsed">
                        <template #icon><Tag :size="20" class="transition-transform group-hover:scale-110" /></template>
                        Productos
                    </SidebarLink>
                    <SidebarLink v-if="isSuperAdmin" :href="route('admin.bundles.index')" :active="$page.url.startsWith('/admin/bundles')" :collapsed="isCollapsed">
                        <template #icon><Gift :size="20" class="transition-transform group-hover:scale-110" /></template>
                        Packs
                    </SidebarLink>
                    <SidebarLink v-if="canManagePrices" :href="route('admin.prices.index')" :active="$page.url.startsWith('/admin/prices')" :collapsed="isCollapsed">
                        <template #icon><Banknote :size="20" class="transition-transform group-hover:scale-110" /></template>
                        Precios
                    </SidebarLink>
                    <SidebarLink v-if="canViewBrands" :href="route('admin.brands.index')" :active="$page.url.startsWith('/admin/brands')" :collapsed="isCollapsed">
                        <template #icon><Layers :size="18" class="transition-transform group-hover:scale-110" /></template>
                        Marcas
                    </SidebarLink>
                    <SidebarLink v-if="canViewCategories" :href="route('admin.categories.index')" :active="$page.url.startsWith('/admin/categories')" :collapsed="isCollapsed">
                        <template #icon><FolderTree :size="18" class="transition-transform group-hover:scale-110" /></template>
                        Categorías
                    </SidebarLink>
                    <SidebarLink v-if="canViewProviders" :href="route('admin.providers.index')" :active="$page.url.startsWith('/admin/providers')" :collapsed="isCollapsed">
                        <template #icon><Factory :size="18" class="transition-transform group-hover:scale-110" /></template>
                        Proveedores
                    </SidebarLink>
                </template>
    
                <!-- GESTIÓN -->
                <template v-if="canManageUsers">
                    <div v-if="!isCollapsed" class="mt-8 mb-3 px-3 text-xs font-bold text-muted-foreground font-display uppercase tracking-widest whitespace-nowrap overflow-hidden">
                        Gestión
                    </div>
                    <div v-else class="my-4 border-t border-border/30"></div>
    
                    <SidebarLink v-if="isSuperAdmin" :href="route('admin.branches.index')" :active="$page.url.startsWith('/admin/branches')" :collapsed="isCollapsed">
                        <template #icon><Building2 :size="20" class="transition-transform group-hover:scale-110" /></template>
                        Sucursales
                    </SidebarLink>
                    <SidebarLink v-if="isSuperAdmin || isBranchAdmin" :href="route('admin.drivers.index')" :active="$page.url.startsWith('/admin/drivers')" :collapsed="isCollapsed">
                        <template #icon><Truck :size="20" class="transition-transform group-hover:scale-110" /></template>
                        Conductores
                    </SidebarLink>
                    <SidebarLink :href="route('admin.users.index')" :active="$page.url.startsWith('/admin/users')" :collapsed="isCollapsed">
                        <template #icon><UserCog :size="20" class="transition-transform group-hover:scale-110" /></template>
                        Equipo
                    </SidebarLink>
                </template>
            </nav>
    
            <!-- FOOTER / USER INFO -->
            <div class="p-4 border-t border-border/50 bg-card/50 shrink-0">
                <div v-if="!isCollapsed" class="flex items-center mb-4 transition-all duration-base">
                    <div class="avatar avatar-md bg-gradient-to-r from-primary to-secondary text-primary-foreground shadow-lg shadow-primary/25">
                        {{ user.first_name ? user.first_name[0] : 'U' }}
                    </div>
                    <div class="ml-3 overflow-hidden">
                        <p class="text-sm font-bold text-foreground truncate">{{ user.first_name }} {{ user.last_name }}</p>
                        <p class="text-xs text-muted-foreground font-medium tracking-wide">
                            {{ roles[0]?.replace('_', ' ') || 'Usuario' }}
                        </p>
                    </div>
                </div>
                <Link :href="route('logout')" method="post" as="button" 
                      class="btn btn-outline btn-sm w-full text-error hover:text-error-foreground hover:bg-error border-error/30"
                      :class="isCollapsed ? 'aspect-square p-0 rounded-full' : ''">
                    <LogOut :size="isCollapsed ? 18 : 16" class="transition-transform duration-fast group-hover:-translate-x-0.5" />
                    <span v-if="!isCollapsed" class="transition-opacity duration-fast">Cerrar Sesión</span>
                </Link>
            </div>
        </aside>
    
        <!-- MOBILE OVERLAY -->
        <div class="md:hidden">
            <div v-if="activeMobileMenu" 
                 @click="closeMobileMenu"
                 class="modal-backdrop animate-in">
            </div>
    
            <!-- MOBILE MENU MODAL -->
            <div v-if="activeMobileMenu" 
                 class="fixed bottom-20 left-4 right-4 bg-card rounded-xl shadow-xl z-50 p-5 border border-border backdrop-blur-sm max-h-[70vh] overflow-y-auto scrollbar-hide animate-scale-in">
                    
                <div class="flex justify-between items-center mb-5 pb-3 border-b border-border/50">
                    <h3 class="text-sm font-black text-foreground font-display uppercase tracking-wider">
                        {{ activeMobileMenu === 'ops' ? '📦 Operaciones' : activeMobileMenu === 'cat' ? '🏷️ Catálogo' : '⚙️ Gestión' }}
                    </h3>
                    <button @click="closeMobileMenu" class="btn btn-ghost btn-sm">
                        <X :size="18"/>
                    </button>
                </div>
    
                <!-- OPERACIONES MOBILE -->
                <div v-if="activeMobileMenu === 'ops'" class="grid grid-cols-3 gap-3">
                    <Link @click="closeMobileMenu" :href="route('admin.purchases.index')" class="card hover:shadow-md active:scale-95 transition-all duration-fast">
                        <div class="mobile-icon-box bg-info/10 text-info border-info/20">
                            <ShoppingCart :size="20" />
                        </div>
                        <span class="mobile-label">Ingresos</span>
                    </Link>
                    <Link @click="closeMobileMenu" :href="route('admin.inventory.index')" class="card hover:shadow-md active:scale-95 transition-all duration-fast">
                        <div class="mobile-icon-box bg-gradient-to-r from-primary to-secondary text-primary-foreground border-primary/30 shadow-md">
                            <Package :size="20" />
                        </div>
                        <span class="mobile-label font-bold text-primary">Kardex</span>
                    </Link>
                    <Link @click="closeMobileMenu" :href="route('admin.transfers.index')" class="card hover:shadow-md active:scale-95 transition-all duration-fast">
                        <div class="mobile-icon-box bg-secondary/10 text-secondary border-secondary/20">
                            <Truck :size="20" />
                        </div>
                        <span class="mobile-label">Transferencias</span>
                    </Link>
                    <Link @click="closeMobileMenu" :href="route('admin.removals.index')" class="card hover:shadow-md active:scale-95 transition-all duration-fast">
                        <div class="mobile-icon-box bg-warning/10 text-warning border-warning/20">
                            <AlertTriangle :size="20" />
                        </div>
                        <span class="mobile-label">Bajas</span>
                    </Link>
                    <Link @click="closeMobileMenu" :href="route('admin.transformations.index')" class="card hover:shadow-md active:scale-95 transition-all duration-fast">
                        <div class="mobile-icon-box bg-accent/10 text-accent border-accent/20">
                            <RefreshCw :size="20" />
                        </div>
                        <span class="mobile-label">Transformaciones</span>
                    </Link>
                    <Link @click="closeMobileMenu" :href="route('admin.orders.kanban')" class="card hover:shadow-md active:scale-95 transition-all duration-fast">
                        <div class="mobile-icon-box bg-muted text-muted-foreground border-border">
                            <ClipboardList :size="20" />
                        </div>
                        <span class="mobile-label">Tablero Kanban</span>
                    </Link>
                </div>
    
                <!-- CATÁLOGO MOBILE -->
                <div v-if="activeMobileMenu === 'cat'" class="grid grid-cols-3 gap-3">
                    <Link @click="closeMobileMenu" :href="route('admin.products.index')" class="card hover:shadow-md active:scale-95 transition-all duration-fast">
                        <div class="mobile-icon-box bg-gradient-to-r from-primary to-secondary text-primary-foreground border-primary/30">
                            <Tag :size="20" />
                        </div>
                        <span class="mobile-label font-bold">Productos</span>
                    </Link>
                    <Link v-if="isSuperAdmin" @click="closeMobileMenu" :href="route('admin.bundles.index')" class="card hover:shadow-md active:scale-95 transition-all duration-fast">
                        <div class="mobile-icon-box bg-success/10 text-success border-success/20">
                            <Gift :size="20" />
                        </div>
                        <span class="mobile-label">Packs</span>
                    </Link>
                    <Link v-if="canManagePrices" @click="closeMobileMenu" :href="route('admin.prices.index')" class="card hover:shadow-md active:scale-95 transition-all duration-fast">
                        <div class="mobile-icon-box bg-primary/10 text-primary border-primary/20">
                            <Banknote :size="20" />
                        </div>
                        <span class="mobile-label">Precios</span>
                    </Link>
                    <Link @click="closeMobileMenu" :href="route('admin.brands.index')" class="card hover:shadow-md active:scale-95 transition-all duration-fast">
                        <div class="mobile-icon-box bg-warning/10 text-warning border-warning/20">
                            <Layers :size="20" />
                        </div>
                        <span class="mobile-label">Marcas</span>
                    </Link>
                    <Link @click="closeMobileMenu" :href="route('admin.categories.index')" class="card hover:shadow-md active:scale-95 transition-all duration-fast">
                        <div class="mobile-icon-box bg-success/10 text-success border-success/20">
                            <FolderTree :size="20" />
                        </div>
                        <span class="mobile-label">Categorías</span>
                    </Link>
                    <Link @click="closeMobileMenu" :href="route('admin.providers.index')" class="card hover:shadow-md active:scale-95 transition-all duration-fast">
                        <div class="mobile-icon-box bg-muted text-muted-foreground border-border">
                            <Factory :size="20" />
                        </div>
                        <span class="mobile-label">Proveedores</span>
                    </Link>
                </div>
    
                <!-- GESTIÓN MOBILE -->
                <div v-if="activeMobileMenu === 'ges'" class="grid grid-cols-2 gap-4">
                    <Link v-if="isSuperAdmin" @click="closeMobileMenu" :href="route('admin.branches.index')" class="card hover:shadow-md active:scale-95 transition-all duration-fast">
                        <div class="mobile-icon-box bg-secondary/10 text-secondary border-secondary/20">
                            <Building2 :size="20" />
                        </div>
                        <span class="mobile-label">Sucursales</span>
                    </Link>
                    <Link @click="closeMobileMenu" :href="route('admin.users.index')" class="card hover:shadow-md active:scale-95 transition-all duration-fast">
                        <div class="mobile-icon-box bg-accent/10 text-accent border-accent/20">
                            <UserCog :size="20" />
                        </div>
                        <span class="mobile-label">Equipo</span>
                    </Link>
                </div>
    
                <!-- USER INFO EN MÓVIL -->
                <div class="mt-6 pt-4 border-t border-border/30">
                    <div class="flex items-center gap-3">
                        <div class="avatar avatar-md bg-gradient-to-r from-primary to-secondary text-primary-foreground shadow-lg">
                            {{ user.first_name ? user.first_name[0] : 'U' }}
                        </div>
                        <div>
                            <p class="text-sm font-bold text-foreground">{{ user.first_name }} {{ user.last_name }}</p>
                            <p class="text-xs text-muted-foreground font-medium">
                                {{ roles[0]?.replace('_', ' ') || 'Usuario' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    
        <!-- BOTTOM NAVIGATION MOBILE -->
        <nav class="md:hidden fixed bottom-0 left-0 right-0 h-16 glass border-t border-border flex items-center justify-between px-2 z-50 pb-safe">
            
            <Link :href="route('admin.dashboard')" @click="closeMobileMenu"
                  class="mobile-nav-item"
                  :class="$page.url.startsWith('/admin/dashboard') ? 'text-primary' : 'text-muted-foreground'">
                <LayoutDashboard :size="22" :stroke-width="$page.url.startsWith('/admin/dashboard') ? 2.5 : 2" />
                <span class="text-[10px] font-semibold mt-0.5">Dashboard</span>
            </Link>
    
            <button @click="toggleMobileMenu('ops')" v-if="canManageStock"
                  class="mobile-nav-item"
                  :class="activeMobileMenu === 'ops' ? 'text-primary' : 'text-muted-foreground'">
                <ClipboardList :size="22" :stroke-width="activeMobileMenu === 'ops' ? 2.5 : 2" class="transition-transform duration-fast"/>
                <span class="text-[10px] font-semibold mt-0.5">Operaciones</span>
            </button>
    
            <button @click="toggleMobileMenu('cat')" v-if="showCatalogSection"
                  class="mobile-nav-item"
                  :class="activeMobileMenu === 'cat' ? 'text-primary' : 'text-muted-foreground'">
                <Tag :size="22" :stroke-width="activeMobileMenu === 'cat' ? 2.5 : 2" class="transition-transform duration-fast"/>
                <span class="text-[10px] font-semibold mt-0.5">Catálogo</span>
            </button>
    
            <button @click="toggleMobileMenu('ges')" v-if="canManageUsers"
                  class="mobile-nav-item"
                  :class="activeMobileMenu === 'ges' ? 'text-primary' : 'text-muted-foreground'">
                <Settings :size="22" :stroke-width="activeMobileMenu === 'ges' ? 2.5 : 2" class="transition-transform duration-fast"/>
                <span class="text-[10px] font-semibold mt-0.5">Gestión</span>
            </button>
    
            <Link :href="route('logout')" method="post" as="button"
                  class="mobile-nav-item text-muted-foreground hover:text-error active:text-error">
                <LogOut :size="22" class="transition-transform duration-fast" />
                <span class="text-[10px] font-semibold mt-0.5">Salir</span>
            </Link>
    
        </nav>
    </template>
    
    <style scoped>
    .pb-safe {
        padding-bottom: env(safe-area-inset-bottom, 0);
    }
    
    /* Grid Móvil Mejorado */
    .mobile-grid-item {
        @apply flex flex-col items-center justify-center p-3 rounded-xl bg-card border border-border hover:border-primary/30 active:scale-95 transition-all duration-fast ease-smooth hover:shadow-sm cursor-pointer;
    }
    
    .mobile-icon-box {
        @apply w-12 h-12 rounded-full flex items-center justify-center mb-2 shadow-sm border transition-transform duration-fast group-hover:scale-110;
    }
    
    .mobile-label {
        @apply text-xs font-semibold text-center text-foreground leading-tight line-clamp-1;
    }
    
    /* Bottom Navigation Items */
    .mobile-nav-item {
        @apply flex flex-col items-center justify-center gap-0.5 p-1.5 rounded-lg transition-all duration-fast ease-smooth active:scale-95 cursor-pointer flex-1 h-full min-w-0 hover:bg-accent/10;
    }
    
    .mobile-nav-item.active {
        @apply text-primary bg-primary/5;
    }
    </style>