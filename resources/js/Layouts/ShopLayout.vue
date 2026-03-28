<script setup>
import { ref, computed } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
const searchQuery = ref(''); 
import {
    Home, ShoppingCart, User, Receipt, ShieldCheck, MapPin,
    Search, Menu, X, LogOut, Bell, Tag, ChevronRight, PackageCheck // Asegurar PackageCheck
} from 'lucide-vue-next';
// Componentes Base (Asegúrate de que las rutas sean correctas)
import ThemeToggler from '@/Components/Base/ThemeToggler.vue';
import FullScreenToggler from '@/Components/Base/FullScreenToggler.vue';
import Toast from '@/Components/Base/Toast.vue';

const props = defineProps({ isProfileSection: { type: Boolean, default: false } });
const page = usePage();
const isSearchActive = ref(false);
const toggleSearch = () => { isSearchActive.value = !isSearchActive.value; };

// --- ESTADOS DE INTERFAZ ---
const isSidebarExpanded = ref(false);
const isMobileMenuOpen = ref(false);

// --- DATA COMPARTIDA (Inertia + Mocks para Telemetría) ---
const user = computed(() => page.props.auth?.user);
const location = computed(() => page.props.location_context || { label: 'LOCALIZANDO...', type: 'branch' });
const cartCount = computed(() => page.props.cart?.total_items || 0);
const activeOrder = computed(() => page.props.active_order || { progress: 75, status: 'preparing' }); // Mock para UI

// --- NAVEGACIÓN UNIFICADA ---
const navigation = [
    { name: 'Inicio', icon: Home, route: 'customer.shop.index' },
    { name: 'Promos', icon: Tag, route: 'customer.shop.index' }, // Ajustar ruta real
    { name: 'Pedidos', icon: Receipt, route: 'customer.orders.history' },
    { name: 'Direcciones', icon: MapPin, route: 'customer.profile.addresses' },
    { name: 'Seguridad', icon: ShieldCheck, route: 'customer.profile.security' },
];
// Actualización del array navigation (Surgical Fix)
const filteredNavigation = computed(() => {
    return navigation.filter(item => {
        // Rutas que SIEMPRE son visibles
        const publicRoutes = ['customer.shop.index'];
        if (publicRoutes.includes(item.route)) return true;
        
        // Rutas que requieren autoridad (USER != NULL)
        return !!user.value;
    });
});
const logout = () => { 
    router.post(route('customer.logout')); 
};
</script>

<template>
    <div class="flex min-h-[100svh] bg-background text-foreground font-sans transition-colors duration-500 overflow-x-hidden">
        
        <Toast />

        <aside 
            @mouseenter="isSidebarExpanded = true"
            @mouseleave="isSidebarExpanded = false"
            class="hidden lg:flex fixed left-0 top-0 h-full z-[100] flex-col bg-card/40 backdrop-blur-3xl border-r border-border/50 transition-all duration-500 ease-ios overflow-hidden"
            :class="isSidebarExpanded ? 'w-64 shadow-2xl' : 'w-[76px]'"
        >
            <div class="h-20 flex items-center px-6 shrink-0">
                <div class="w-8 h-8 bg-primary rounded-lg flex items-center justify-center shadow-f1-glow shrink-0">
                    <span class="text-white font-black text-xs">C</span>
                </div>
                <span class="ml-4 font-black tracking-tighter text-xl uppercase transition-opacity duration-300"
                      :class="isSidebarExpanded ? 'opacity-100' : 'opacity-0'">
                    Cyber<span class="text-primary">Market</span>
                </span>
            </div>

            <nav class="flex-1 px-3 space-y-2 mt-4">
                <Link v-for="item in navigation" :key="item.name" :href="route().has(item.route) ? route(item.route) : '#'" 
                    class="flex items-center h-12 rounded-xl transition-all duration-300 group relative"
                    :class="route().current(item.route) ? 'bg-primary/10 text-primary shadow-sm' : 'hover:bg-foreground/5 text-foreground/60 hover:text-foreground'">
                    
                    <div class="w-[52px] flex justify-center shrink-0">
                        <component :is="item.icon" :size="20" :stroke-width="2.5" />
                    </div>
                    
                    <span class="font-bold text-[10px] uppercase tracking-[0.15em] whitespace-nowrap transition-all duration-300"
                          :class="isSidebarExpanded ? 'opacity-100 translate-x-0' : 'opacity-0 -translate-x-4'">
                        {{ item.name }}
                    </span>

                    <div v-if="route().current(item.route)" class="absolute left-0 w-1 h-6 bg-primary rounded-r-full"></div>
                </Link>
            </nav>

            <div class="p-3 border-t border-border/50 bg-foreground/[0.02]">
                <div class="flex items-center w-full h-12 rounded-xl hover:bg-foreground/5 transition-all group overflow-hidden">
                    <div class="w-[52px] flex justify-center shrink-0">
                        <div class="w-8 h-8 rounded-full border border-primary/20 overflow-hidden bg-foreground/5 flex items-center justify-center">
                            <img v-if="user" :src="user.profile.avatar_url" class="w-full h-full object-cover" />
                            <User v-else :size="16" class="text-primary" />
                        </div>
                    </div>
                    <div class="flex flex-col items-start transition-all duration-300"
                        :class="isSidebarExpanded ? 'opacity-100' : 'opacity-0 pointer-events-none'">
                        
                        <span class="text-[11px] font-bold truncate w-32">{{ user?.name || 'Invitado' }}</span>
                        
                        <div v-if="!user" class="flex gap-2 items-center">
                            <Link :href="route('customer.login')" class="text-[9px] font-black text-primary uppercase hover:underline">
                                Entrar
                            </Link>
                            <span class="text-[9px] text-foreground/20">|</span>
                            <Link :href="route('customer.register')" class="text-[9px] font-black text-foreground/60 uppercase hover:underline">
                                Unirse
                            </Link>
                        </div>
                                                
                        <button v-else @click="logout" class="text-[9px] font-black text-primary uppercase hover:underline">
                            Cerrar Sesión
                        </button>
                    </div>
                </div>
            </div>
        </aside>

        <main 
            class="flex-1 flex flex-col transition-all duration-500 ease-ios w-full pt-16 lg:pt-20"
            :class="isSidebarExpanded ? 'lg:pl-64' : 'lg:pl-[76px]'"
        >
        <header class="fixed top-0 lg:top-4 left-0 right-0 z-[60] w-full px-2 lg:px-8 pointer-events-none transition-all duration-500">
        <div class="mx-auto max-w-7xl h-16 lg:h-14 bg-transparent backdrop-blur-md border border-foreground/10 rounded-[2rem] lg:rounded-full flex items-center justify-between px-4 pointer-events-auto">
                <div v-if="!isSearchActive" class="flex items-center gap-3 shrink-0 animate-fade-in">
                    <button @click="isMobileMenuOpen = true" class="lg:hidden p-2 hover:bg-foreground/10 rounded-full text-foreground active:scale-95 transition-transform">
                        <Menu :size="20" />
                    </button>

                    <button @click="router.visit(route('customer.profile.addresses'))" class="flex items-center gap-2 px-3 py-1.5 hover:bg-foreground/5 rounded-full transition-all group">
                        <div class="w-7 h-7 bg-primary/20 rounded-full flex items-center justify-center shrink-0 shadow-f1-glow">
                            <MapPin :size="14" class="text-primary" />
                        </div>
                        <div class="flex flex-col items-start leading-none">
                            <span class="text-[9px] font-black text-primary uppercase tracking-tighter">Entregar en:</span>
                            <span class="text-[11px] font-bold text-foreground truncate max-w-[120px] md:max-w-[200px] uppercase">
                                {{ location.label }}
                            </span>
                        </div>
                    </button>
                </div>

                <div class="flex-1 flex justify-end items-center gap-2">
                    <div v-if="isSearchActive" class="w-full flex items-center gap-2 animate-slide-left">
                        <div class="flex-1 relative">
                            <Search class="absolute left-4 top-1/2 -translate-y-1/2 text-primary" :size="16" />
                            <input 
                                v-model="searchQuery"
                                autoFocus
                                type="text" 
                                placeholder="BUSCAR EN EL CATÁLOGO..."
                                class="w-full h-11 bg-foreground/10 border-none rounded-full pl-11 pr-4 text-[11px] font-bold tracking-widest focus:ring-2 focus:ring-primary/30 text-foreground placeholder:text-foreground/40 uppercase"
                            />
                        </div>
                        <button @click="toggleSearch" class="p-2.5 bg-foreground/20 rounded-full text-foreground active:scale-90">
                            <X :size="18" />
                        </button>
                    </div>

                    <button v-else @click="toggleSearch" class="p-3 hover:bg-foreground/10 rounded-full text-foreground transition-all active:scale-90">
                        <Search :size="20" />
                    </button>

                    <div v-if="!isSearchActive" class="flex items-center gap-2 shrink-0">
                        <div class="hidden sm:flex items-center gap-1 mr-2">
                            <ThemeToggler />
                            <FullScreenToggler />
                        </div>
                        
                        <Link :href="route('customer.cart.index')" class="flex items-center gap-2 p-1 pl-3 bg-foreground/10 hover:bg-foreground/20 rounded-full border border-white/10 transition-all group">
                            <span class="text-[10px] font-black uppercase tracking-tighter hidden md:block text-foreground">Items: {{ cartCount }}</span>
                            <div class="w-9 h-9 bg-primary text-white rounded-full flex items-center justify-center relative shadow-f1-glow group-active:scale-90 transition-transform">
                                <ShoppingCart :size="18" />
                                <span v-if="cartCount > 0" class="absolute -top-1 -right-1 bg-foreground text-background text-[9px] font-black w-4 h-4 rounded-full flex items-center justify-center border-2 border-primary">
                                    {{ cartCount }}
                                </span>
                            </div>
                        </Link>
                    </div>
                </div>
                </div>
             </header>
            <section 
                class="flex-1 w-full mx-auto py-8 transition-all"
                :class="route().current('customer.shop.index') ? 'max-w-full px-0' : 'max-w-7xl px-4 lg:px-8'"
            >
                <div v-if="!route().current('customer.shop.index')" class="mb-6 flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-foreground/40">
                    <MapPin :size="12" class="text-primary" />
                    <span>Entrega: {{ location.label }}</span>
                    <ChevronRight :size="12" />
                    <span class="text-foreground">Vista Actual</span>
                </div>

                <slot />
            </section>

            <footer class="w-full border-t border-border/50 py-12 px-8 bg-card/20 mt-auto">
                <div class="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-center gap-8 text-[10px] font-bold uppercase tracking-[0.2em] text-foreground/30">
                    <div class="flex flex-col items-center md:items-start">
                        <span class="text-foreground/60">CyberMarket Core v2.0</span>
                        <span class="mt-1">Bolivia Logistics Engine</span>
                    </div>
                    <div class="flex gap-8">
                        <a href="#" class="hover:text-primary transition-colors">Terminal</a>
                        <a href="#" class="hover:text-primary transition-colors">Soporte</a>
                        <a href="#" class="hover:text-primary transition-colors">Legal</a>
                    </div>
                </div>
            </footer>
        </main>
        
        <nav class="lg:hidden fixed bottom-0 left-0 right-0 h-[76px] bg-card/95 backdrop-blur-2xl border-t border-border/80 z-[100] flex justify-around items-center px-4">
            <Link :href="route('customer.orders.history')" 
                class="flex flex-col items-center gap-1 transition-all active:scale-90"
                :class="route().current('customer.orders.*') ? 'text-primary' : 'text-foreground'">
                <PackageCheck :size="24" :stroke-width="2.5" />
                <span class="text-[9px] font-black uppercase tracking-tighter">Historial</span>
            </Link>

            <Link :href="route('customer.shop.index')" 
                class="flex flex-col items-center gap-1 transition-all"
                :class="route().current('customer.shop.promotions') ? 'text-primary' : 'text-foreground/70'">
                <Tag :size="22" />
                <span class="text-[9px] font-black uppercase tracking-tighter">Promos</span>
            </Link>

            <Link :href="route('customer.shop.index')" class="relative -mt-10">
                <div class="w-14 h-14 bg-card rounded-2xl border-4 border-background shadow-2xl flex items-center justify-center active:scale-95 transition-transform">
                    <Home :size="26" :class="route().current('customer.shop.index') ? 'text-primary' : 'text-foreground'" />
                </div>
            </Link>

            <button @click="toggleSearch" class="flex flex-col items-center gap-1 text-foreground/70">
                <Search :size="22" />
                <span class="text-[9px] font-black uppercase tracking-tighter">Buscar</span>
            </button>

            <component 
                :is="user ? Link : 'div'" 
                :href="user ? route('customer.profile.index') : null" 
                class="flex flex-col items-center gap-1 transition-all"
                :class="user ? 'text-foreground/70 active:scale-95' : 'opacity-30 cursor-default'"
            >
                <div v-if="user" class="w-6 h-6 rounded-lg border border-primary/30 overflow-hidden shadow-f1-glow">
                    <img :src="user.profile.avatar_url" class="w-full h-full object-cover" :alt="user.profile.first_name" />
                </div>
                <User v-else :size="22" />
                
                <span class="text-[9px] font-black uppercase tracking-tighter">
                    {{ user ? 'Perfil' : 'Cuenta' }}
                </span>
            </component>
        </nav>

        <Transition name="drawer">
            <div v-if="isMobileMenuOpen" class="fixed inset-0 z-[110] lg:hidden">
                <div class="absolute inset-0 bg-background/60 backdrop-blur-md" @click="isMobileMenuOpen = false"></div>
                <div class="absolute left-0 top-0 h-full w-[300px] bg-card border-r border-border/50 flex flex-col shadow-2xl">
                    <div class="p-6 flex justify-between items-center border-b border-border/50">
                        <span class="font-black tracking-[0.15em] text-sm uppercase truncate max-w-[180px]">
                            {{ user ? user.profile.first_name : 'Menu_Sistema' }}
                        </span>
                        <button @click="isMobileMenuOpen = false" class="p-2 hover:bg-muted rounded-full transition-colors"><X :size="20" /></button>
                    </div>
                    <nav class="flex-1 p-4 space-y-1">
                        <Link v-for="item in navigation" :key="item.name" :href="route().has(item.route) ? route(item.route) : '#'" 
                            @click="isMobileMenuOpen = false"
                            class="flex items-center gap-4 p-4 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-primary/5 group transition-all">
                            <component :is="item.icon" :size="18" class="group-hover:text-primary transition-colors" />
                            {{ item.name }}
                        </Link>
                    </nav>
                    <div class="p-6 border-t border-border/50 space-y-4">
                        <div v-if="!user" class="grid grid-cols-2 gap-3">
                            <Link :href="route('customer.login')" 
                                class="flex items-center justify-center h-12 rounded-xl bg-primary text-black text-[10px] font-black uppercase tracking-widest active:scale-95 transition-all">
                                Entrar
                            </Link>
                            <Link :href="route('customer.register')" 
                                class="flex items-center justify-center h-12 rounded-xl border border-border text-foreground text-[10px] font-black uppercase tracking-widest active:scale-95 transition-all">
                                Registro
                            </Link>
                        </div>

                        <div v-else class="space-y-4">
                            <div class="flex gap-2">
                                <ThemeToggler class="flex-1" />
                                <FullScreenToggler class="flex-1" />
                            </div>
                            <button @click="logout" class="w-full py-4 rounded-xl border border-primary/20 text-[10px] font-black text-primary uppercase active:scale-95 transition-all">
                                Terminar Sesión Táctica
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>

    </div>
</template>

<style scoped>
/* Transiciones de Curva iOS */
.drawer-enter-active, .drawer-leave-active {
    transition: all 0.5s cubic-bezier(0.32, 0.72, 0, 1);
}
.drawer-enter-from, .drawer-leave-to {
    transform: translateX(-100%);
}

/* Ocultar scrollbar pero mantener funcionalidad */
:deep(body)::-webkit-scrollbar {
    display: none;
}

/* Glassmorphism Extra para F1 Mode */
.dark aside, .dark header {
    background-image: radial-gradient(circle at 2px 2px, rgba(255,255,255,0.02) 1px, transparent 0);
    background-size: 24px 24px;
}
</style>