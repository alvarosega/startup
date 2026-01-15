<script setup>
    import { computed, ref, onMounted, onUnmounted, watch } from 'vue';
    import { usePage, Link } from '@inertiajs/vue3';
    import SidebarLink from '@/Components/Admin/SidebarLink.vue';
    import { 
        LayoutDashboard, ShoppingCart, Package, Truck, AlertTriangle, 
        RefreshCw, Tag, Barcode, Layers, Box, Factory, MapPin, Users, LogOut,
        Menu, ChevronLeft 
    } from 'lucide-vue-next';
    
    // Definimos los eventos que este componente enviará al Layout padre
    const emit = defineEmits(['width-change', 'resizing-state']);
    
    // --- CONFIGURACIÓN DE GEOMETRÍA ---
    const MIN_WIDTH = 80;
    const MAX_WIDTH = 450;
    const DEFAULT_WIDTH = 260;
    
    // --- ESTADO INTERNO ---
    const savedWidth = parseInt(localStorage.getItem('sidebarWidth'));
    const width = ref(savedWidth && savedWidth >= MIN_WIDTH ? savedWidth : DEFAULT_WIDTH);
    const isResizing = ref(false);
    const isCollapsed = computed(() => width.value <= MIN_WIDTH + 10);
    
    // --- PERMISOS Y USUARIO (Movido desde el Layout) ---
    const page = usePage();
    const user = computed(() => page.props.auth.user);
    const roles = computed(() => user.value.roles || []);
// ... (después de const roles...)
        // DEFINICIÓN DE ROLES
    
    
    // AGREGAR: Nuevo rol necesario para lógica visual
    const isFinanceManager = computed(() => roles.value.includes('finance_manager'));

    // PERMISOS COMPUTADOS VISUALES
    
    // 1. Gestión de Stock (Movimientos)

    
    // 4. Ver Proveedores - (Se suman Branch y Finanzas)
    const canViewProviders = computed(() => 
        canManageCatalog.value || isBranchAdmin.value || isInventoryManager.value || isFinanceManager.value
    );

    // 5. ¿Mostrar la sección Catálogo en el menú? (Si puede hacer la 3 o la 4)
    const showCatalogSection = computed(() => canManageCatalog.value || canViewProviders.value);

    // Agrega esta computada junto a las otras
    const canManageUsers = computed(() => isSuperAdmin.value || isBranchAdmin.value);
    
    const isSuperAdmin = computed(() => roles.value.includes('super_admin'));
    const isBranchAdmin = computed(() => roles.value.includes('branch_admin'));
    const isLogistics = computed(() => roles.value.includes('logistics_manager'));
    const isInventoryManager = computed(() => roles.value.includes('inventory_manager'));
    
    const canManageCatalog = computed(() => isSuperAdmin.value || isLogistics.value);
    const canManageStock = computed(() => isSuperAdmin.value || isLogistics.value || isBranchAdmin.value || isInventoryManager.value);
    
    // --- COMUNICACIÓN CON EL PADRE ---
    // Cada vez que cambia el ancho o el estado de resizing, avisamos al Layout
    watch(width, (val) => emit('width-change', val), { immediate: true });
    watch(isResizing, (val) => emit('resizing-state', val));
    
    // --- LÓGICA DE ARRASTRE ---
    const startResize = () => {
        isResizing.value = true;
        document.body.style.cursor = 'col-resize';
        document.body.style.userSelect = 'none';
        window.addEventListener('mousemove', handleMouseMove);
        window.addEventListener('mouseup', stopResize);
    };
    
    const handleMouseMove = (e) => {
        let newWidth = e.clientX;
        if (newWidth < MIN_WIDTH) newWidth = MIN_WIDTH;
        if (newWidth > MAX_WIDTH) newWidth = MAX_WIDTH;
        width.value = newWidth;
    };
    
    const stopResize = () => {
        isResizing.value = false;
        document.body.style.cursor = '';
        document.body.style.userSelect = '';
        localStorage.setItem('sidebarWidth', width.value);
        window.removeEventListener('mousemove', handleMouseMove);
        window.removeEventListener('mouseup', stopResize);
    };
    
    const toggleSidebar = () => {
        width.value = isCollapsed.value ? DEFAULT_WIDTH : MIN_WIDTH;
        localStorage.setItem('sidebarWidth', width.value);
    };
    </script>
    
    <template>
        <aside 
            class="bg-skin-fill-card border-r border-skin-border flex flex-col fixed h-full z-20 shadow-sm group/sidebar overflow-hidden"
            :class="{ 'transition-all duration-300 ease-out': !isResizing }"
            :style="{ width: width + 'px' }"
        >
            <div 
                @mousedown.prevent="startResize"
                class="absolute top-0 right-0 w-1.5 h-full cursor-col-resize hover:bg-skin-primary/50 z-50 transition-colors delay-75 active:bg-skin-primary"
                title="Arrastra para cambiar tamaño"
            ></div>
    
            <div class="h-16 flex items-center border-b border-skin-border shrink-0 whitespace-nowrap overflow-hidden"
                 :class="isCollapsed ? 'justify-center px-0' : 'justify-between px-6'">
                <div class="font-black tracking-tighter italic text-skin-base text-xl">
                    <span v-if="!isCollapsed">BOLIVIA<span class="text-skin-primary">LOGISTICS</span></span>
                    <span v-else class="text-skin-primary">BL</span>
                </div>
                <button v-if="!isCollapsed" @click="toggleSidebar" class="text-skin-muted hover:text-skin-primary transition-colors">
                    <ChevronLeft :size="20" />
                </button>
            </div>
    
            <div v-if="isCollapsed" class="flex justify-center py-2 border-b border-skin-border bg-skin-fill/50 shrink-0">
                <button @click="toggleSidebar" class="p-1 rounded hover:bg-skin-fill-hover text-skin-muted hover:text-skin-primary">
                    <Menu :size="20" />
                </button>
            </div>
    
            <nav class="flex-1 px-3 py-6 space-y-1 overflow-y-auto custom-scrollbar overflow-x-hidden">
                <SidebarLink :href="route('admin.dashboard')" :active="$page.url.startsWith('/admin/dashboard')" :collapsed="isCollapsed">
                    <template #icon><LayoutDashboard :size="20" /></template>
                    Dashboard
                </SidebarLink>
    
                <template v-if="canManageStock">
                    <div v-if="!isCollapsed" class="mt-6 mb-2 px-3 text-[10px] font-bold text-skin-muted/70 uppercase tracking-widest fade-in whitespace-nowrap overflow-hidden">
                        Operaciones
                    </div>
                    <div v-else class="my-4 border-t border-skin-border"></div>
                    
                    <SidebarLink :href="route('admin.purchases.index')" :active="$page.url.startsWith('/admin/purchases')" :collapsed="isCollapsed">
                        <template #icon><ShoppingCart :size="20" /></template>
                        Ingresos
                    </SidebarLink>
                    <SidebarLink :href="route('admin.inventory.index')" :active="$page.url.startsWith('/admin/inventory')" :collapsed="isCollapsed">
                        <template #icon><Package :size="20" /></template>
                        Kardex
                    </SidebarLink>
                    <SidebarLink :href="route('admin.transfers.index')" :active="$page.url.startsWith('/admin/transfers')" :collapsed="isCollapsed">
                        <template #icon><Truck :size="20" /></template>
                        Transferencias
                    </SidebarLink>
                    <SidebarLink :href="route('admin.removals.index')" :active="$page.url.startsWith('/admin/removals')" :collapsed="isCollapsed">
                        <template #icon><AlertTriangle :size="20" /></template>
                        Bajas
                    </SidebarLink>
                    <SidebarLink :href="route('admin.transformations.index')" :active="$page.url.startsWith('/admin/transformations')" :collapsed="isCollapsed">
                        <template #icon><RefreshCw :size="20" /></template>
                        Transformaciones
                    </SidebarLink>
                </template>
    
                <template v-if="showCatalogSection">
                    <div v-if="!isCollapsed" class="mt-6 mb-2 px-3 text-[10px] font-bold text-skin-muted/70 uppercase tracking-widest fade-in whitespace-nowrap overflow-hidden">
                        Catálogo
                    </div>
                    <div v-else class="my-4 border-t border-skin-border"></div>

                    <template v-if="canManageCatalog">
                        <SidebarLink :href="route('admin.products.index')" :active="$page.url.startsWith('/admin/products')" :collapsed="isCollapsed">
                            <template #icon><Tag :size="20" /></template>
                            Productos
                        </SidebarLink>
                        
                        <div class="pt-2 mt-2 border-t border-skin-border space-y-0.5">
                            <SidebarLink :href="route('admin.brands.index')" :active="$page.url.startsWith('/admin/brands')" :collapsed="isCollapsed">
                                <template #icon><Layers :size="18" /></template>
                                Marcas
                            </SidebarLink>
                            <SidebarLink :href="route('admin.categories.index')" :active="$page.url.startsWith('/admin/categories')" :collapsed="isCollapsed">
                                <template #icon><Box :size="18" /></template>
                                Categorías
                            </SidebarLink>
                        </div>
                    </template>
                    
                    <SidebarLink v-if="canViewProviders" :href="route('admin.providers.index')" :active="$page.url.startsWith('/admin/providers')" :collapsed="isCollapsed">
                        <template #icon><Factory :size="18" /></template>
                        Proveedores
                    </SidebarLink>
                </template>
    
                <template v-if="canManageUsers">
                    <div v-if="!isCollapsed" class="mt-6 mb-2 px-3 text-[10px] font-bold text-skin-muted/70 uppercase tracking-widest fade-in whitespace-nowrap overflow-hidden">
                        Gestión
                    </div>
                    <div v-else class="my-4 border-t border-skin-border"></div>

                    <SidebarLink v-if="isSuperAdmin" :href="route('admin.branches.index')" :active="$page.url.startsWith('/admin/branches')" :collapsed="isCollapsed">
                        <template #icon><MapPin :size="20" /></template>
                        Sucursales
                    </SidebarLink>
                    
                    <SidebarLink :href="route('admin.users.index')" :active="$page.url.startsWith('/admin/users')" :collapsed="isCollapsed">
                        <template #icon><Users :size="20" /></template>
                        Equipo
                    </SidebarLink>
                </template>
            </nav>
    
            <div class="p-4 border-t border-skin-border bg-skin-fill-card shrink-0"
                 :class="isCollapsed ? 'flex justify-center' : ''">
                 
                 <div v-if="!isCollapsed" class="flex items-center mb-3 fade-in whitespace-nowrap overflow-hidden">
                    <div class="w-8 h-8 rounded-full bg-skin-primary text-skin-primary-text flex items-center justify-center font-bold text-xs shadow-sm shrink-0">
                        {{ user.first_name ? user.first_name[0] : 'U' }}
                    </div>
                    <div class="ml-3 overflow-hidden">
                        <p class="text-sm font-bold text-skin-base truncate">{{ user.first_name }}</p>
                        <p class="text-[10px] text-skin-muted uppercase tracking-wider truncate font-semibold">{{ roles[0] || 'Staff' }}</p>
                    </div>
                </div>
    
                <Link :href="route('logout')" method="post" as="button" 
                      class="flex items-center justify-center gap-2 py-1.5 text-xs font-bold text-skin-danger bg-skin-danger/10 border border-transparent hover:border-skin-danger rounded-global transition-all duration-300 overflow-hidden"
                      :class="isCollapsed ? 'w-10 h-10 p-0 rounded-full' : 'w-full'">
                    <LogOut :size="isCollapsed ? 18 : 14" />
                    <span v-if="!isCollapsed">Cerrar Sesión</span>
                </Link>
            </div>
        </aside>
    </template>
    
    <style scoped>
    .fade-in { animation: fadeIn 0.2s ease-in-out; }
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    </style>