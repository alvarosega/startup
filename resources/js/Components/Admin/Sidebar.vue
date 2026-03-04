<script setup>
import { computed, ref } from 'vue';
import { usePage, Link } from '@inertiajs/vue3';
import { 
    LayoutDashboard, ShoppingCart, Package, Truck, AlertTriangle, 
    RefreshCw, Tag, Layers, Factory, LogOut,
    Banknote, Gift, ClipboardList, Settings, X, 
    Building2, FolderTree, UserCog, Map, 
    ArrowLeftRight, Store, Home, Radar
} from 'lucide-vue-next';

const activeMobileMenu = ref(null);

const page = usePage();
const user = computed(() => page.props.auth?.user);
const roles = computed(() => user.value?.roles || []);

const isSuperAdmin = computed(() => roles.value.includes('super_admin'));
const isAdmin = isSuperAdmin;
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
<aside class="hidden md:block fixed top-0 left-0 h-full z-50 w-[250px] pointer-events-none">
        
    <div class="absolute inset-y-0 left-0 w-[72px] bg-background pointer-events-auto shadow-[5px_0_20px_rgba(0,0,0,0.4)]"></div>

        <div class="absolute top-0 left-0 w-[72px] h-16 flex items-center justify-center z-50 pointer-events-auto">
            <span class="font-display font-black text-2xl text-primary drop-shadow-[0_0_10px_hsl(var(--primary))] glitch-text">DU</span>
        </div>

        <div class="absolute top-16 bottom-0 left-0 w-[250px] overflow-y-auto overflow-x-hidden pointer-events-none [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
            
            <nav class="flex flex-col py-6 gap-6 w-[72px] pointer-events-auto">
                
                <Link :href="route('admin.dashboard')" class="group relative flex items-center justify-center w-full outline-none">
                    <LayoutDashboard :size="24" class="icon-glow text-muted-foreground group-hover:text-primary transition-colors duration-300" />
                    <span class="absolute left-[80px] px-3 py-2 bg-background/95 backdrop-blur-md border border-primary shadow-neon-primary font-display font-black text-[10px] tracking-widest text-primary uppercase glitch-text whitespace-nowrap opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50 pointer-events-none">
                        Dashboard
                    </span>
                </Link>

                <template v-if="canManageStock">
                    <div class="w-6 border-t border-border/40 mx-auto my-1"></div>
                    
                    <Link :href="route('admin.purchases.index')" class="group relative flex items-center justify-center w-full outline-none">
                        <ShoppingCart :size="24" class="icon-glow text-muted-foreground group-hover:text-primary transition-colors duration-300" />
                        <span class="absolute left-[80px] px-3 py-2 bg-background/95 backdrop-blur-md border border-primary shadow-neon-primary font-display font-black text-[10px] tracking-widest text-primary uppercase glitch-text whitespace-nowrap opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50 pointer-events-none">Ingresos</span>
                    </Link>
                    
                    <Link :href="route('admin.inventory.index')" class="group relative flex items-center justify-center w-full outline-none">
                        <Package :size="24" class="icon-glow text-muted-foreground group-hover:text-primary transition-colors duration-300" />
                        <span class="absolute left-[80px] px-3 py-2 bg-background/95 backdrop-blur-md border border-primary shadow-neon-primary font-display font-black text-[10px] tracking-widest text-primary uppercase glitch-text whitespace-nowrap opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50 pointer-events-none">Stock Base</span>
                    </Link>

                    <Link :href="route('admin.transfers.index')" class="group relative flex items-center justify-center w-full outline-none">
                        <Truck :size="24" class="icon-glow text-muted-foreground group-hover:text-primary transition-colors duration-300" />
                        <span class="absolute left-[80px] px-3 py-2 bg-background/95 backdrop-blur-md border border-primary shadow-neon-primary font-display font-black text-[10px] tracking-widest text-primary uppercase glitch-text whitespace-nowrap opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50 pointer-events-none">Transferencias</span>
                    </Link>

                    <Link :href="route('admin.removals.index')" class="group relative flex items-center justify-center w-full outline-none">
                        <AlertTriangle :size="24" class="icon-glow text-muted-foreground group-hover:text-primary transition-colors duration-300" />
                        <span class="absolute left-[80px] px-3 py-2 bg-background/95 backdrop-blur-md border border-primary shadow-neon-primary font-display font-black text-[10px] tracking-widest text-primary uppercase glitch-text whitespace-nowrap opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50 pointer-events-none">Bajas</span>
                    </Link>

                    <Link :href="route('admin.transformations.index')" class="group relative flex items-center justify-center w-full outline-none">
                        <RefreshCw :size="24" class="icon-glow text-muted-foreground group-hover:text-primary transition-colors duration-300" />
                        <span class="absolute left-[80px] px-3 py-2 bg-background/95 backdrop-blur-md border border-primary shadow-neon-primary font-display font-black text-[10px] tracking-widest text-primary uppercase glitch-text whitespace-nowrap opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50 pointer-events-none">Transformaciones</span>
                    </Link>

                    <Link :href="route('admin.orders.index')" class="group relative flex items-center justify-center w-full outline-none">
                        <ClipboardList :size="24" class="icon-glow text-muted-foreground group-hover:text-primary transition-colors duration-300" />
                        <span class="absolute left-[80px] px-3 py-2 bg-background/95 backdrop-blur-md border border-primary shadow-neon-primary font-display font-black text-[10px] tracking-widest text-primary uppercase glitch-text whitespace-nowrap opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50 pointer-events-none">Órdenes</span>
                    </Link>

                    <Link :href="route('admin.logistics.monitor')" class="group relative flex items-center justify-center w-full outline-none">
                        <Radar :size="24" class="icon-glow text-primary drop-shadow-[0_0_8px_hsl(var(--primary))] transition-colors duration-300" />
                        <span class="absolute left-[80px] px-3 py-2 bg-background/95 backdrop-blur-md border border-primary shadow-neon-primary font-display font-black text-[10px] tracking-widest text-primary uppercase glitch-text whitespace-nowrap opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50 pointer-events-none">Radar (Vivo)</span>
                    </Link>
                </template>

                <template v-if="canManageCatalog">
                    <div class="w-6 border-t border-border/40 mx-auto my-1"></div>
                    
                    <Link :href="route('admin.products.index')" class="group relative flex items-center justify-center w-full outline-none">
                        <Tag :size="24" class="icon-glow text-muted-foreground group-hover:text-primary transition-colors duration-300" />
                        <span class="absolute left-[80px] px-3 py-2 bg-background/95 backdrop-blur-md border border-primary shadow-neon-primary font-display font-black text-[10px] tracking-widest text-primary uppercase glitch-text whitespace-nowrap opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50 pointer-events-none">Productos</span>
                    </Link>
                    
                    <Link :href="route('admin.market-zones.index')" class="group relative flex items-center justify-center w-full outline-none">
                        <Map :size="24" class="icon-glow text-muted-foreground group-hover:text-primary transition-colors duration-300" />
                        <span class="absolute left-[80px] px-3 py-2 bg-background/95 backdrop-blur-md border border-primary shadow-neon-primary font-display font-black text-[10px] tracking-widest text-primary uppercase glitch-text whitespace-nowrap opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50 pointer-events-none">Zonas</span>
                    </Link>

                    <Link :href="route('admin.bundles.index')" class="group relative flex items-center justify-center w-full outline-none">
                        <Gift :size="24" class="icon-glow text-muted-foreground group-hover:text-primary transition-colors duration-300" />
                        <span class="absolute left-[80px] px-3 py-2 bg-background/95 backdrop-blur-md border border-primary shadow-neon-primary font-display font-black text-[10px] tracking-widest text-primary uppercase glitch-text whitespace-nowrap opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50 pointer-events-none">Packs</span>
                    </Link>

                    <Link :href="route('admin.prices.index')" class="group relative flex items-center justify-center w-full outline-none">
                        <Banknote :size="24" class="icon-glow text-muted-foreground group-hover:text-primary transition-colors duration-300" />
                        <span class="absolute left-[80px] px-3 py-2 bg-background/95 backdrop-blur-md border border-primary shadow-neon-primary font-display font-black text-[10px] tracking-widest text-primary uppercase glitch-text whitespace-nowrap opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50 pointer-events-none">Precios</span>
                    </Link>

                    <Link :href="route('admin.brands.index')" class="group relative flex items-center justify-center w-full outline-none">
                        <Layers :size="24" class="icon-glow text-muted-foreground group-hover:text-primary transition-colors duration-300" />
                        <span class="absolute left-[80px] px-3 py-2 bg-background/95 backdrop-blur-md border border-primary shadow-neon-primary font-display font-black text-[10px] tracking-widest text-primary uppercase glitch-text whitespace-nowrap opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50 pointer-events-none">Marcas</span>
                    </Link>

                    <Link :href="route('admin.categories.index')" class="group relative flex items-center justify-center w-full outline-none">
                        <FolderTree :size="24" class="icon-glow text-muted-foreground group-hover:text-primary transition-colors duration-300" />
                        <span class="absolute left-[80px] px-3 py-2 bg-background/95 backdrop-blur-md border border-primary shadow-neon-primary font-display font-black text-[10px] tracking-widest text-primary uppercase glitch-text whitespace-nowrap opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50 pointer-events-none">Categorías</span>
                    </Link>

                    <Link :href="route('admin.providers.index')" class="group relative flex items-center justify-center w-full outline-none">
                        <Factory :size="24" class="icon-glow text-muted-foreground group-hover:text-primary transition-colors duration-300" />
                        <span class="absolute left-[80px] px-3 py-2 bg-background/95 backdrop-blur-md border border-primary shadow-neon-primary font-display font-black text-[10px] tracking-widest text-primary uppercase glitch-text whitespace-nowrap opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50 pointer-events-none">Proveedores</span>
                    </Link>
                </template>

                <template v-if="canManageUsers">
                    <div class="w-6 border-t border-border/40 mx-auto my-1"></div>
                    
                    <Link :href="route('admin.branches.index')" class="group relative flex items-center justify-center w-full outline-none">
                        <Building2 :size="24" class="icon-glow text-muted-foreground group-hover:text-primary transition-colors duration-300" />
                        <span class="absolute left-[80px] px-3 py-2 bg-background/95 backdrop-blur-md border border-primary shadow-neon-primary font-display font-black text-[10px] tracking-widest text-primary uppercase glitch-text whitespace-nowrap opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50 pointer-events-none">Sucursales</span>
                    </Link>
                    
                    <Link :href="route('admin.drivers.index')" class="group relative flex items-center justify-center w-full outline-none">
                        <Truck :size="24" class="icon-glow text-muted-foreground group-hover:text-primary transition-colors duration-300" />
                        <span class="absolute left-[80px] px-3 py-2 bg-background/95 backdrop-blur-md border border-primary shadow-neon-primary font-display font-black text-[10px] tracking-widest text-primary uppercase glitch-text whitespace-nowrap opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50 pointer-events-none">Conductores</span>
                    </Link>

                    <Link :href="route('admin.users.index')" class="group relative flex items-center justify-center w-full outline-none">
                        <UserCog :size="24" class="icon-glow text-muted-foreground group-hover:text-primary transition-colors duration-300" />
                        <span class="absolute left-[80px] px-3 py-2 bg-background/95 backdrop-blur-md border border-primary shadow-neon-primary font-display font-black text-[10px] tracking-widest text-primary uppercase glitch-text whitespace-nowrap opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50 pointer-events-none">Equipo</span>
                    </Link>
                </template>
                
                <div class="pb-12"></div>
            </nav>
        </div>

        <div class="absolute bottom-0 left-0 w-[72px] h-16 border-t border-border bg-background z-50 pointer-events-auto">
            <Link :href="route('admin.logout')" method="post" as="button" class="group relative flex items-center justify-center h-full w-full outline-none">
                <LogOut :size="24" class="icon-glow text-destructive drop-shadow-[0_0_8px_hsl(var(--destructive))]" />
                <span class="absolute left-[80px] px-3 py-2 bg-destructive/10 backdrop-blur-md border border-destructive shadow-neon-destructive font-display uppercase font-black text-[10px] tracking-widest text-destructive glitch-text whitespace-nowrap opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50 pointer-events-none">
                    Cerrar Sesión
                </span>
            </Link>
        </div>
    </aside>

    <div class="md:hidden">
        <div v-if="activeMobileMenu" @click="closeMobileMenu" class="fixed inset-0 bg-background/90 z-40 cursor-pointer backdrop-blur-sm"></div>

        <div v-if="activeMobileMenu" class="fixed bottom-[90px] left-4 right-4 bg-background/95 backdrop-blur-xl z-50 p-6 flex flex-col max-h-[70vh] shadow-[0_0_30px_rgba(0,0,0,0.5),0_0_1px_1px_hsl(var(--primary)/0.2)]">
            
            <div class="flex justify-between items-center mb-4 pb-4 border-b border-border shrink-0 bg-background/50">
                <h3 class="font-display font-black text-lg text-foreground uppercase flex items-center gap-3">
                    <span v-if="activeMobileMenu === 'inv'" class="flex items-center gap-2 text-primary"><Package size="20" class="icon-glow" /> <span class="glitch-text">Stock</span></span>
                    <span v-else-if="activeMobileMenu === 'mov'" class="flex items-center gap-2 text-primary"><ArrowLeftRight size="20" class="icon-glow" /> <span class="glitch-text">Flujos</span></span>
                    <span v-else-if="activeMobileMenu === 'com'" class="flex items-center gap-2 text-primary"><Store size="20" class="icon-glow" /> <span class="glitch-text">Catálogo</span></span>
                    <span v-else class="flex items-center gap-2 text-primary"><Settings size="20" class="icon-glow" /> <span class="glitch-text">Gestión</span></span>
                </h3>
                <button @click="closeMobileMenu" class="btn btn-outline p-2 group border-border hover:border-primary hover:shadow-neon-primary transition-colors"><X :size="16" class="icon-glow group-hover:text-primary" /></button>
            </div>

            <div class="flex-1 overflow-y-auto pr-2 scrollbar-thin">
                 <div v-if="activeMobileMenu === 'inv'" class="grid grid-cols-2 gap-3">
                    <Link @click="closeMobileMenu" :href="route('admin.purchases.index')" class="card bg-background/80 border border-border p-3 flex flex-col items-center justify-center gap-2 group hover:border-primary hover:shadow-neon-primary transition-colors"><ShoppingCart size="20" class="icon-glow" /><span class="font-mono text-[10px] font-bold uppercase text-center glitch-text text-foreground group-hover:text-primary">Ingresos</span></Link>
                    <Link @click="closeMobileMenu" :href="route('admin.inventory.index')" class="card bg-background/80 border border-border p-3 flex flex-col items-center justify-center gap-2 group hover:border-primary hover:shadow-neon-primary transition-colors"><Package size="20" class="icon-glow" /><span class="font-mono text-[10px] font-bold uppercase text-center glitch-text text-foreground group-hover:text-primary">Stock Base</span></Link>
                    <Link @click="closeMobileMenu" :href="route('admin.transformations.index')" class="card bg-background/80 border border-border p-3 flex flex-col items-center justify-center gap-2 group hover:border-primary hover:shadow-neon-primary transition-colors"><RefreshCw size="20" class="icon-glow" /><span class="font-mono text-[10px] font-bold uppercase text-center glitch-text text-foreground group-hover:text-primary">Transform.</span></Link>
                </div>

                <div v-if="activeMobileMenu === 'mov'" class="grid grid-cols-2 gap-3">
                    <Link @click="closeMobileMenu" :href="route('admin.transfers.index')" class="card bg-background/80 border border-border p-3 flex flex-col items-center justify-center gap-2 group hover:border-primary hover:shadow-neon-primary transition-colors"><Truck size="20" class="icon-glow" /><span class="font-mono text-[10px] font-bold uppercase text-center glitch-text text-foreground group-hover:text-primary">Transf.</span></Link>
                    <Link @click="closeMobileMenu" :href="route('admin.removals.index')" class="card bg-background/80 border border-border p-3 flex flex-col items-center justify-center gap-2 group hover:border-primary hover:shadow-neon-primary transition-colors"><AlertTriangle size="20" class="icon-glow" /><span class="font-mono text-[10px] font-bold uppercase text-center glitch-text text-foreground group-hover:text-primary">Bajas</span></Link>
                    <Link @click="closeMobileMenu" :href="route('admin.orders.index')" class="card bg-background/80 border border-border p-3 flex flex-col items-center justify-center gap-2 group hover:border-primary hover:shadow-neon-primary transition-colors"><ClipboardList size="20" class="icon-glow" /><span class="font-mono text-[10px] font-bold uppercase text-center glitch-text text-foreground group-hover:text-primary">Ordenes</span></Link>
                    <Link @click="closeMobileMenu" :href="route('admin.logistics.monitor')" class="card bg-background/80 border border-primary p-3 flex flex-col items-center justify-center gap-2 group hover:shadow-neon-primary transition-colors"><Radar size="20" class="icon-glow text-primary drop-shadow-[0_0_8px_hsl(var(--primary))]" /><span class="font-mono text-[10px] font-bold uppercase text-center text-primary glitch-text drop-shadow-[0_0_8px_hsl(var(--primary))]">Radar</span></Link>
                </div>

                <div v-if="activeMobileMenu === 'com'" class="grid grid-cols-2 gap-3">
                    <Link @click="closeMobileMenu" :href="route('admin.products.index')" class="card bg-background/80 border border-border p-3 flex flex-col items-center justify-center gap-2 group hover:border-primary hover:shadow-neon-primary transition-colors"><Tag size="20" class="icon-glow" /><span class="font-mono text-[10px] font-bold uppercase text-center glitch-text text-foreground group-hover:text-primary">Prod.</span></Link>
                    <Link @click="closeMobileMenu" :href="route('admin.market-zones.index')" class="card bg-background/80 border border-border p-3 flex flex-col items-center justify-center gap-2 group hover:border-primary hover:shadow-neon-primary transition-colors"><Map size="20" class="icon-glow" /><span class="font-mono text-[10px] font-bold uppercase text-center glitch-text text-foreground group-hover:text-primary">Zonas</span></Link>
                    <Link @click="closeMobileMenu" :href="route('admin.bundles.index')" class="card bg-background/80 border border-border p-3 flex flex-col items-center justify-center gap-2 group hover:border-primary hover:shadow-neon-primary transition-colors"><Gift size="20" class="icon-glow" /><span class="font-mono text-[10px] font-bold uppercase text-center glitch-text text-foreground group-hover:text-primary">Packs</span></Link>
                    <Link @click="closeMobileMenu" :href="route('admin.prices.index')" class="card bg-background/80 border border-border p-3 flex flex-col items-center justify-center gap-2 group hover:border-primary hover:shadow-neon-primary transition-colors"><Banknote size="20" class="icon-glow" /><span class="font-mono text-[10px] font-bold uppercase text-center glitch-text text-foreground group-hover:text-primary">Precios</span></Link>
                    <Link @click="closeMobileMenu" :href="route('admin.brands.index')" class="card bg-background/80 border border-border p-3 flex flex-col items-center justify-center gap-2 group hover:border-primary hover:shadow-neon-primary transition-colors"><Layers size="20" class="icon-glow" /><span class="font-mono text-[10px] font-bold uppercase text-center glitch-text text-foreground group-hover:text-primary">Marcas</span></Link>
                    <Link @click="closeMobileMenu" :href="route('admin.categories.index')" class="card bg-background/80 border border-border p-3 flex flex-col items-center justify-center gap-2 group hover:border-primary hover:shadow-neon-primary transition-colors"><FolderTree size="20" class="icon-glow" /><span class="font-mono text-[10px] font-bold uppercase text-center glitch-text text-foreground group-hover:text-primary">Categ.</span></Link>
                    <Link @click="closeMobileMenu" :href="route('admin.providers.index')" class="card bg-background/80 border border-border p-3 flex flex-col items-center justify-center gap-2 group hover:border-primary hover:shadow-neon-primary transition-colors"><Factory size="20" class="icon-glow" /><span class="font-mono text-[10px] font-bold uppercase text-center glitch-text text-foreground group-hover:text-primary">Prov.</span></Link>
                </div>

                <div v-if="activeMobileMenu === 'ges'" class="flex flex-col gap-3">
                    <div class="grid grid-cols-2 gap-3">
                        <Link @click="closeMobileMenu" :href="route('admin.users.index')" class="card bg-background/80 border border-border p-3 flex flex-col items-center justify-center gap-2 group hover:border-primary hover:shadow-neon-primary transition-colors"><UserCog size="20" class="icon-glow" /><span class="font-mono text-[10px] font-bold uppercase text-center glitch-text text-foreground group-hover:text-primary">Equipo</span></Link>
                        <Link @click="closeMobileMenu" :href="route('admin.branches.index')" class="card bg-background/80 border border-border p-3 flex flex-col items-center justify-center gap-2 group hover:border-primary hover:shadow-neon-primary transition-colors"><Building2 size="20" class="icon-glow" /><span class="font-mono text-[10px] font-bold uppercase text-center glitch-text text-foreground group-hover:text-primary">Sucursales</span></Link>
                        <Link @click="closeMobileMenu" :href="route('admin.drivers.index')" class="card bg-background/80 border border-border p-3 flex flex-col items-center justify-center gap-2 group hover:border-primary hover:shadow-neon-primary transition-colors"><Truck size="20" class="icon-glow" /><span class="font-mono text-[10px] font-bold uppercase text-center glitch-text text-foreground group-hover:text-primary">Drivers</span></Link>
                    </div>
                    
                    <div class="mt-4 pt-4 border-t border-border">
                        <Link :href="route('admin.logout')" method="post" as="button" class="btn btn-destructive w-full flex items-center justify-center gap-2 group">
                            <LogOut :size="16" class="icon-glow" />
                            <span class="font-display uppercase font-black text-xs glitch-text">Cerrar Sesión</span>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <nav class="md:hidden fixed bottom-0 left-0 right-0 h-[80px] bg-background/95 backdrop-blur-lg z-40 grid grid-cols-5 px-1 items-center shadow-[0_-5px_20px_rgba(0,0,0,0.3)]">
        
        <button tabindex="0" @click="toggleMobileMenu('ges')" v-if="isAdmin" class="group relative flex flex-col items-center justify-center h-full outline-none focus:outline-none w-full">
            <Settings size="20" class="icon-glow text-muted-foreground group-focus:text-primary group-hover:text-primary transition-colors" />
            <div class="absolute -top-14 left-1/2 -translate-x-1/2 opacity-0 invisible group-focus:opacity-100 group-focus:visible group-focus:-translate-y-2 transition-all duration-200 bg-transparent border-primary shadow-neon-primary px-3 py-2 z-50 pointer-events-none">
                <span class="font-display font-black text-[10px] text-primary uppercase tracking-widest whitespace-nowrap glitch-text">Gestión</span>
            </div>
        </button>
        
        <button tabindex="0" @click="toggleMobileMenu('inv')" v-if="isAdmin" class="group relative flex flex-col items-center justify-center h-full outline-none focus:outline-none w-full">
            <Package size="20" class="icon-glow text-muted-foreground group-focus:text-primary group-hover:text-primary transition-colors" />
            <div class="absolute -top-14 left-1/2 -translate-x-1/2 opacity-0 invisible group-focus:opacity-100 group-focus:visible group-focus:-translate-y-2 transition-all duration-200 bbg-transparent border-primary shadow-neon-primary px-3 py-2 z-50 pointer-events-none">
                <span class="font-display font-black text-[10px] text-primary uppercase tracking-widest whitespace-nowrap glitch-text">Stock</span>
            </div>
        </button>
        
        <div class="flex items-start justify-center -mt-6 relative z-50">
            <Link :href="route('admin.dashboard')" @click="closeMobileMenu" class="w-14 h-14 bg-transparent border-primary flex items-center justify-center group hover:shadow-neon-primary transition-colors focus:shadow-neon-primary focus:bg-primary/20 outline-none">
                <div class="w-10 h-10 bg-primary/20 text-primary flex items-center justify-center group-hover:bg-primary group-hover:text-primary-foreground group-focus:bg-primary group-focus:text-primary-foreground transition-colors">
                    <Home size="24" class="icon-glow" />
                </div>
            </Link>
        </div>

        <button tabindex="0" @click="toggleMobileMenu('mov')" v-if="isAdmin" class="group relative flex flex-col items-center justify-center h-full outline-none focus:outline-none w-full">
            <ArrowLeftRight size="20" class="icon-glow text-muted-foreground group-focus:text-primary group-hover:text-primary transition-colors" />
            <div class="absolute -top-14 left-1/2 -translate-x-1/2 opacity-0 invisible group-focus:opacity-100 group-focus:visible group-focus:-translate-y-2 transition-all duration-200 bg-transparent border-primary shadow-neon-primary px-3 py-2 z-50 pointer-events-none">
                <span class="font-display font-black text-[10px] text-primary uppercase tracking-widest whitespace-nowrap glitch-text">Flujos</span>
            </div>
        </button>
        
        <button tabindex="0" @click="toggleMobileMenu('com')" v-if="isAdmin" class="group relative flex flex-col items-center justify-center h-full outline-none focus:outline-none w-full">
            <Store size="20" class="icon-glow text-muted-foreground group-focus:text-primary group-hover:text-primary transition-colors" />
            <div class="absolute -top-14 left-1/2 -translate-x-1/2 opacity-0 invisible group-focus:opacity-100 group-focus:visible group-focus:-translate-y-2 transition-all duration-200 bg-transparent border-primary shadow-neon-primary px-3 py-2 z-50 pointer-events-none">
                <span class="font-display font-black text-[10px] text-primary uppercase tracking-widest whitespace-nowrap glitch-text">Catálogo</span>
            </div>
        </button>
    </nav>
</template>