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
    const activeMobileMenu = ref(null); // 'ops', 'cat', 'ges'
    
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
            class="hidden md:flex flex-col fixed h-full z-30 bg-card border-r border-border shadow-md transition-all duration-300 ease-[cubic-bezier(0.16,1,0.3,1)] will-change-[width] backdrop-blur-sm bg-white/90"
            :class="isCollapsed ? 'w-[88px]' : 'w-[280px]'"
        >
            <!-- HEADER -->
            <div class="h-20 flex items-center justify-between px-6 border-b border-border/50 shrink-0">
                <div class="font-display font-black tracking-tighter italic text-2xl transition-all duration-300 overflow-hidden whitespace-nowrap text-foreground"
                     :class="isCollapsed ? 'opacity-0 w-0 scale-95' : 'opacity-100 scale-100'">
                    <span class="bg-gradient-to-r from-primary to-secondary bg-clip-text text-transparent">BOLIVIA</span><span class="text-primary">LOGISTICS</span>
                </div>
                
                <button @click="toggleSidebar" 
                        class="p-2 rounded-full hover:bg-muted text-muted-foreground hover:text-primary transition-all duration-150 ease-out active:scale-95 cursor-pointer"
                        :class="isCollapsed ? 'mx-auto' : ''"
                        aria-label="Toggle sidebar">
                    <ChevronRight v-if="isCollapsed" :size="20" class="transition-transform duration-150" />
                    <ChevronLeft v-else :size="20" class="transition-transform duration-150" />
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
                    <SidebarLink :href="route('admin.users.index')" :active="$page.url.startsWith('/admin/users')" :collapsed="isCollapsed">
                        <template #icon><UserCog :size="20" class="transition-transform group-hover:scale-110" /></template>
                        Equipo
                    </SidebarLink>
                </template>
            </nav>
    
            <!-- FOOTER / USER INFO -->
            <div class="p-4 border-t border-border/50 bg-background/50 shrink-0">
                <div v-if="!isCollapsed" class="flex items-center mb-4 transition-all duration-300">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-r from-primary to-secondary text-white flex items-center justify-center font-bold text-sm shadow-lg shadow-primary/25 shrink-0 ring-2 ring-primary/20">
                        {{ user.first_name ? user.first_name[0] : 'U' }}
                    </div>
                    <div class="ml-3 overflow-hidden">
                        <p class="text-sm font-bold text-foreground truncate">{{ user.first_name }} {{ user.last_name }}</p>
                        <p class="text-xs text-muted-foreground font-medium tracking-wide">{{ roles[0]?.replace('_', ' ') || 'Usuario' }}</p>
                    </div>
                </div>
                <Link :href="route('logout')" method="post" as="button" 
                      class="flex items-center justify-center gap-2 w-full py-2.5 text-sm font-semibold text-red-600 hover:text-white bg-red-50 hover:bg-red-600 border border-red-200 hover:border-red-600 rounded-lg transition-all duration-150 ease-out active:scale-95 cursor-pointer"
                      :class="isCollapsed ? 'aspect-square p-0 rounded-full' : ''">
                    <LogOut :size="isCollapsed ? 18 : 16" class="transition-transform duration-150 group-hover:-translate-x-0.5" />
                    <span v-if="!isCollapsed" class="transition-opacity duration-150">Cerrar Sesión</span>
                </Link>
            </div>
        </aside>
    
        <!-- MOBILE OVERLAY -->
        <div class="md:hidden">
            <div v-if="activeMobileMenu" 
                 @click="closeMobileMenu"
                 class="fixed inset-0 bg-black/60 backdrop-blur-md z-40">
            </div>
    
            <!-- MOBILE MENU MODAL -->
            <div v-if="activeMobileMenu" 
                 class="fixed bottom-20 left-4 right-4 bg-white rounded-2xl shadow-2xl z-50 p-5 border border-border/50 backdrop-blur-sm max-h-[70vh] overflow-y-auto scrollbar-hide">
                    
                <div class="flex justify-between items-center mb-5 pb-3 border-b border-border/50">
                    <h3 class="text-sm font-black text-foreground font-display uppercase tracking-wider">
                        {{ activeMobileMenu === 'ops' ? '📦 Operaciones' : activeMobileMenu === 'cat' ? '🏷️ Catálogo' : '⚙️ Gestión' }}
                    </h3>
                    <button @click="closeMobileMenu" class="p-1.5 rounded-full bg-gray-100 text-gray-600 hover:bg-gray-200 transition-all duration-150 active:scale-95 cursor-pointer">
                        <X :size="18"/>
                    </button>
                </div>
    
                <!-- OPERACIONES MOBILE -->
                <div v-if="activeMobileMenu === 'ops'" class="grid grid-cols-3 gap-3">
                    <Link @click="closeMobileMenu" :href="route('admin.purchases.index')" class="mobile-grid-item">
                        <div class="mobile-icon-box bg-blue-50 text-blue-600 border-blue-200">
                            <ShoppingCart :size="20" />
                        </div>
                        <span class="mobile-label">Ingresos</span>
                    </Link>
                    <Link @click="closeMobileMenu" :href="route('admin.inventory.index')" class="mobile-grid-item">
                        <div class="mobile-icon-box bg-gradient-to-r from-primary to-secondary text-white border-primary/30 shadow-md shadow-primary/20">
                            <Package :size="20" />
                        </div>
                        <span class="mobile-label font-bold text-primary">Kardex</span>
                    </Link>
                    <Link @click="closeMobileMenu" :href="route('admin.transfers.index')" class="mobile-grid-item">
                        <div class="mobile-icon-box bg-purple-50 text-purple-600 border-purple-200">
                            <Truck :size="20" />
                        </div>
                        <span class="mobile-label">Transferencias</span>
                    </Link>
                    <Link @click="closeMobileMenu" :href="route('admin.removals.index')" class="mobile-grid-item">
                        <div class="mobile-icon-box bg-orange-50 text-orange-600 border-orange-200">
                            <AlertTriangle :size="20" />
                        </div>
                        <span class="mobile-label">Bajas</span>
                    </Link>
                    <Link @click="closeMobileMenu" :href="route('admin.transformations.index')" class="mobile-grid-item">
                        <div class="mobile-icon-box bg-cyan-50 text-cyan-600 border-cyan-200">
                            <RefreshCw :size="20" />
                        </div>
                        <span class="mobile-label">Transformaciones</span>
                    </Link>
                    <Link @click="closeMobileMenu" :href="route('admin.orders.kanban')" class="mobile-grid-item">
                        <div class="mobile-icon-box bg-indigo-50 text-indigo-600 border-indigo-200">
                            <ClipboardList :size="20" />
                        </div>
                        <span class="mobile-label">Tablero Kanban</span>
                    </Link>
                </div>
    
                <!-- CATÁLOGO MOBILE -->
                <div v-if="activeMobileMenu === 'cat'" class="grid grid-cols-3 gap-3">
                    <Link @click="closeMobileMenu" :href="route('admin.products.index')" class="mobile-grid-item">
                        <div class="mobile-icon-box bg-gradient-to-r from-primary to-secondary text-white border-primary/30">
                            <Tag :size="20" />
                        </div>
                        <span class="mobile-label font-bold">Productos</span>
                    </Link>
                    <Link v-if="isSuperAdmin" @click="closeMobileMenu" :href="route('admin.bundles.index')" class="mobile-grid-item">
                        <div class="mobile-icon-box bg-pink-50 text-pink-600 border-pink-200">
                            <Gift :size="20" />
                        </div>
                        <span class="mobile-label">Packs</span>
                    </Link>
                    <Link v-if="canManagePrices" @click="closeMobileMenu" :href="route('admin.prices.index')" class="mobile-grid-item">
                        <div class="mobile-icon-box bg-green-50 text-green-600 border-green-200">
                            <Banknote :size="20" />
                        </div>
                        <span class="mobile-label">Precios</span>
                    </Link>
                    <Link @click="closeMobileMenu" :href="route('admin.brands.index')" class="mobile-grid-item">
                        <div class="mobile-icon-box bg-amber-50 text-amber-600 border-amber-200">
                            <Layers :size="20" />
                        </div>
                        <span class="mobile-label">Marcas</span>
                    </Link>
                    <Link @click="closeMobileMenu" :href="route('admin.categories.index')" class="mobile-grid-item">
                        <div class="mobile-icon-box bg-emerald-50 text-emerald-600 border-emerald-200">
                            <FolderTree :size="20" />
                        </div>
                        <span class="mobile-label">Categorías</span>
                    </Link>
                    <Link @click="closeMobileMenu" :href="route('admin.providers.index')" class="mobile-grid-item">
                        <div class="mobile-icon-box bg-slate-50 text-slate-600 border-slate-200">
                            <Factory :size="20" />
                        </div>
                        <span class="mobile-label">Proveedores</span>
                    </Link>
                </div>
    
                <!-- GESTIÓN MOBILE -->
                <div v-if="activeMobileMenu === 'ges'" class="grid grid-cols-2 gap-4">
                    <Link v-if="isSuperAdmin" @click="closeMobileMenu" :href="route('admin.branches.index')" class="mobile-grid-item">
                        <div class="mobile-icon-box bg-violet-50 text-violet-600 border-violet-200">
                            <Building2 :size="20" />
                        </div>
                        <span class="mobile-label">Sucursales</span>
                    </Link>
                    <Link @click="closeMobileMenu" :href="route('admin.users.index')" class="mobile-grid-item">
                        <div class="mobile-icon-box bg-rose-50 text-rose-600 border-rose-200">
                            <UserCog :size="20" />
                        </div>
                        <span class="mobile-label">Equipo</span>
                    </Link>
                </div>
    
                <!-- USER INFO EN MÓVIL -->
                <div class="mt-6 pt-4 border-t border-border/30">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-r from-primary to-secondary text-white flex items-center justify-center font-bold text-sm shadow-lg shadow-primary/25 ring-2 ring-primary/20">
                            {{ user.first_name ? user.first_name[0] : 'U' }}
                        </div>
                        <div>
                            <p class="text-sm font-bold text-foreground">{{ user.first_name }} {{ user.last_name }}</p>
                            <p class="text-xs text-gray-600 font-medium">{{ roles[0]?.replace('_', ' ') || 'Usuario' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    
        <!-- BOTTOM NAVIGATION MOBILE -->
        <nav class="md:hidden fixed bottom-0 left-0 right-0 h-16 bg-white/95 backdrop-blur-xl border-t border-border flex items-center justify-between px-2 z-50 pb-safe">
            
            <Link :href="route('admin.dashboard')" @click="closeMobileMenu"
                  class="mobile-nav-item"
                  :class="$page.url.startsWith('/admin/dashboard') ? 'text-primary' : 'text-gray-600'">
                <LayoutDashboard :size="22" :stroke-width="$page.url.startsWith('/admin/dashboard') ? 2.5 : 2" />
                <span class="text-[10px] font-semibold mt-0.5">Dashboard</span>
            </Link>
    
            <button @click="toggleMobileMenu('ops')" v-if="canManageStock"
                  class="mobile-nav-item"
                  :class="activeMobileMenu === 'ops' ? 'text-primary' : 'text-gray-600'">
                <ClipboardList :size="22" :stroke-width="activeMobileMenu === 'ops' ? 2.5 : 2" class="transition-transform duration-150"/>
                <span class="text-[10px] font-semibold mt-0.5">Operaciones</span>
            </button>
    
            <button @click="toggleMobileMenu('cat')" v-if="showCatalogSection"
                  class="mobile-nav-item"
                  :class="activeMobileMenu === 'cat' ? 'text-primary' : 'text-gray-600'">
                <Tag :size="22" :stroke-width="activeMobileMenu === 'cat' ? 2.5 : 2" class="transition-transform duration-150"/>
                <span class="text-[10px] font-semibold mt-0.5">Catálogo</span>
            </button>
    
            <button @click="toggleMobileMenu('ges')" v-if="canManageUsers"
                  class="mobile-nav-item"
                  :class="activeMobileMenu === 'ges' ? 'text-primary' : 'text-gray-600'">
                <Settings :size="22" :stroke-width="activeMobileMenu === 'ges' ? 2.5 : 2" class="transition-transform duration-150"/>
                <span class="text-[10px] font-semibold mt-0.5">Gestión</span>
            </button>
    
            <Link :href="route('logout')" method="post" as="button"
                  class="mobile-nav-item text-gray-600 active:text-red-500 hover:text-red-500/80">
                <LogOut :size="22" class="transition-transform duration-150" />
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
        @apply flex flex-col items-center justify-center p-3 rounded-xl bg-white border border-gray-200 hover:border-primary/30 active:scale-95 transition-all duration-150 ease-out hover:shadow-sm cursor-pointer;
    }
    
    .mobile-icon-box {
        @apply w-12 h-12 rounded-full flex items-center justify-center mb-2 shadow-sm border transition-transform duration-150 group-hover:scale-110;
    }
    
    .mobile-label {
        @apply text-xs font-semibold text-center text-gray-800 leading-tight line-clamp-1;
    }
    
    /* Bottom Navigation Items */
    .mobile-nav-item {
        @apply flex flex-col items-center justify-center gap-0.5 p-1.5 rounded-lg transition-all duration-150 ease-out active:scale-95 cursor-pointer flex-1 h-full min-w-0;
    }
    
    .mobile-nav-item.active {
        @apply text-primary bg-blue-50;
    }
    
    /* Scrollbar Personalizado */
    .scrollbar-hide {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
    
    .scrollbar-hide::-webkit-scrollbar {
        display: none;
    }
    </style>