<script setup>
import { ref, computed } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import {
    Home, ShoppingCart, User, Receipt, ShieldCheck, MapPin,
    Search, Menu, X, LogOut, Bell, Tag, ChevronRight, PackageCheck, 
    Loader2, MessageCircle, Instagram, Facebook, Youtube, Twitter, Linkedin, 
    Send, Disc, Tv, Pin, AtSign, Ghost, Music2
} from 'lucide-vue-next';

// Componentes Base
import ThemeToggler from '@/Components/Base/ThemeToggler.vue';
import FullScreenToggler from '@/Components/Base/FullScreenToggler.vue';
const socialNetworks = [
    { name: 'WhatsApp', icon: MessageCircle, link: '#' },
    { name: 'Instagram', icon: Instagram, link: '#' },
    { name: 'Facebook', icon: Facebook, link: '#' },
    { name: 'TikTok', icon: Music2, link: '#' }, // Verifica que sea Music2
    { name: 'YouTube', icon: Youtube, link: '#' },
    { name: 'X / Twitter', icon: Twitter, link: '#' },
    { name: 'LinkedIn', icon: Linkedin, link: '#' },
    { name: 'Telegram', icon: Send, link: '#' },
    { name: 'Discord', icon: Disc, link: '#' },
    { name: 'Twitch', icon: Tv, link: '#' },
    { name: 'Threads', icon: AtSign, link: '#' },
    { name: 'Snapchat', icon: Ghost, link: '#' },
];
const page = usePage();
const isSearchActive = ref(false);
const searchQuery = ref('');
const isSidebarExpanded = ref(false);
const isMobileMenuOpen = ref(false);

// --- ESTADOS Y DATOS ---
const user = computed(() => page.props.auth?.customer);
const location = computed(() => page.props.location_context || { label: 'LOCALIZANDO...', type: 'branch' });
const cartCount = computed(() => page.props.cart?.total_items || 0);
const activeOrder = computed(() => page.props.active_order || null);

const userInitials = computed(() => {
    const name = user.value?.name;
    if (!name) return '??';
    return name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2);
});
// --- NAVEGACIÓN ---
const navigation = [
    { name: 'Inicio', icon: Home, route: 'customer.index' },
    { name: 'Promociones', icon: Tag, route: 'customer.index' },
    { name: 'Pedidos', icon: Receipt, route: 'customer.orders.index' },
    { name: 'Direcciones', icon: MapPin, route: 'customer.profile.addresses' },
    { name: 'Seguridad', icon: ShieldCheck, route: 'customer.profile.security' },
];

const filteredNavigation = computed(() => {
    return navigation.filter(item => ['customer.index'].includes(item.route) || !!user.value);
});

// --- ACCIONES ---
const logout = () => router.post(route('customer.logout'));
const toggleSearch = () => {
    isSearchActive.value = !isSearchActive.value;
    if (isSearchActive.value) {
        setTimeout(() => document.getElementById('global-search')?.focus(), 150);
    }
};
</script>

<template>
    <div class="du-cyber-canvas flex min-h-[100svh] text-foreground font-sans transition-colors duration-500 overflow-x-hidden selection:bg-primary/30">
        
        <aside 
            @mouseenter="isSidebarExpanded = true"
            @mouseleave="isSidebarExpanded = false"
            class="hidden lg:flex fixed left-0 top-0 h-full z-[100] flex-col glass-titanium border-r border-border transition-all duration-500 ease-ios"
            :class="isSidebarExpanded ? 'w-64 shadow-2xl' : 'w-[76px]'"
        >
            <div class="h-20 flex items-center px-6 shrink-0">
                <div class="w-9 h-9 bg-primary rounded-xl flex items-center justify-center shrink-0 shadow-f1-glow">
                    <span class="text-primary-foreground font-black text-sm tracking-tighter">DU</span>
                </div>
                <div class="ml-4 flex flex-col transition-opacity duration-300" 
                     :class="isSidebarExpanded ? 'opacity-100' : 'opacity-0 pointer-events-none'">
                    <span class="font-black text-lg leading-none tracking-tighter uppercase">De</span>
                    <span class="text-primary-aaa dark:text-primary font-bold text-[10px] tracking-[0.3em] uppercase">Una</span>
                </div>
            </div>

            <nav class="flex-1 px-3 space-y-1.5 mt-4 overflow-y-auto no-scrollbar">
                <Link v-for="item in filteredNavigation" :key="item.name" :href="route().has(item.route) ? route(item.route) : '#'" 
                    class="flex items-center h-11 rounded-xl transition-all duration-200 group relative focus:ring-2 focus:ring-ring focus:outline-none"
                    :class="route().current(item.route) 
                        ? 'bg-primary/10 text-primary-aaa dark:text-primary shadow-sm' 
                        : 'text-black dark:text-neutral-400 hover:bg-neutral-100 dark:hover:bg-neutral-800 hover:text-foreground'">
                    
                    <div class="w-[52px] flex justify-center shrink-0">
                        <component :is="item.icon" :size="20" />
                    </div>
                    
                    <span class="font-bold text-[10px] uppercase tracking-[0.15em] whitespace-nowrap transition-all"
                          :class="isSidebarExpanded ? 'opacity-100 translate-x-0' : 'opacity-0 -translate-x-4'">
                        {{ item.name }}
                    </span>

                    <div v-if="route().current(item.route)" 
                         class="absolute left-0 w-1 h-5 bg-primary rounded-r-full animate-in fade-in slide-in-from-left-1">
                    </div>
                </Link>
            </nav>

            <div class="p-3 border-t border-border bg-neutral-50/50 dark:bg-neutral-950/50">
                <div class="flex items-center w-full h-12 rounded-xl hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-all overflow-hidden group">
                    <div class="w-[52px] flex justify-center shrink-0">
                        <div class="w-8 h-8 rounded-lg border border-border overflow-hidden bg-background flex items-center justify-center shadow-sm">
                            <img v-if="user?.profile?.avatar_url" :src="user.profile.avatar_url" class="w-full h-full object-cover" />
                            <span v-else class="text-[10px] font-black text-foreground">{{ userInitials }}</span>
                        </div>
                    </div>
                    <div class="flex flex-col items-start transition-all" :class="isSidebarExpanded ? 'opacity-100' : 'opacity-0 pointer-events-none'">
                        <span class="text-[11px] font-extrabold truncate w-32 uppercase tracking-tighter text-black dark:text-white">{{ user?.name || 'Invitado' }}</span>
                        
                        <div v-if="!user" class="flex gap-2 items-center leading-none">
                            <Link :href="route('customer.login')" class="text-[9px] font-black text-primary uppercase hover:underline tracking-widest">
                                Entrar
                            </Link>
                            <span class="text-[9px] text-black/30 dark:text-white/30">|</span>
                            <Link :href="route('customer.register')" class="text-[9px] font-black text-black dark:text-white/60 uppercase hover:underline tracking-widest">
                                Unirse
                            </Link>
                        </div>
                        
                        <button v-else @click="logout" class="text-[9px] font-black text-primary-aaa dark:text-primary hover:underline uppercase tracking-widest">
                            Cerrar Sesión
                        </button>
                    </div>
                </div>
            </div>
        </aside>

        <main class="flex-1 flex flex-col w-full pt-[88px] lg:pt-[96px] transition-all duration-500 ease-ios relative z-10" 
            :class="isSidebarExpanded ? 'lg:pl-64' : 'lg:pl-[76px]'">
            
            <header class="fixed top-0 lg:top-4 left-0 lg:left-auto right-0 z-[60] w-full lg:w-[calc(100%-theme(spacing.20))] px-2 lg:px-8 pointer-events-none">
                <div class="mx-auto max-w-7xl h-16 lg:h-14 glass-titanium border border-border rounded-none lg:rounded-3xl flex items-center justify-between px-4 pointer-events-auto shadow-apple-soft">
                    
                    <div v-if="!isSearchActive" class="flex items-center gap-3 animate-in fade-in slide-in-from-left-4">
                        <button @click="isMobileMenuOpen = true" class="lg:hidden p-2.5 hover:bg-neutral-100 dark:hover:bg-neutral-800 rounded-xl text-foreground active:scale-95 transition-transform">
                            <Menu :size="20" />
                        </button>

                        <button @click="router.visit(route('customer.profile.addresses'))" class="flex items-center gap-3 px-3 py-1.5 hover:bg-neutral-100 dark:hover:bg-neutral-800 rounded-2xl transition-all group focus:outline-none">
                            <div class="w-8 h-8 bg-primary/10 rounded-lg flex items-center justify-center border border-primary/20">
                                <MapPin :size="14" class="text-primary-aaa dark:text-primary" />
                            </div>
                            <div class="flex flex-col items-start leading-none">
                                <span class="text-[8px] font-black text-black/50 dark:text-white/50 uppercase tracking-widest">Entregar en</span>
                                <span class="text-[10px] font-extrabold text-black dark:text-white truncate max-w-[150px] uppercase">{{ location.label }}</span>
                            </div>
                        </button>
                    </div>

                    <div class="flex-1 flex justify-end items-center gap-2">
                        <div v-if="isSearchActive" class="w-full flex items-center gap-2 animate-in slide-in-from-right-4">
                            <div class="flex-1 relative">
                                <Search class="absolute left-4 top-1/2 -translate-y-1/2 text-primary" :size="16" />
                                <input 
                                    id="global-search" 
                                    v-model="searchQuery" 
                                    type="text" 
                                    placeholder="¿QUÉ BUSCAS HOY?"
                                    class="w-full h-11 bg-neutral-100 dark:bg-neutral-800 border-none rounded-2xl pl-11 pr-4 text-[11px] font-bold tracking-widest focus:ring-2 focus:ring-ring focus:ring-offset-2 text-foreground placeholder:text-neutral-500 uppercase transition-all"
                                />
                            </div>
                            <button @click="toggleSearch" class="p-3 bg-neutral-200 dark:bg-neutral-700 rounded-2xl text-foreground active:scale-90"><X :size="18" /></button>
                        </div>

                        <div v-else class="flex items-center gap-2">
                            <button @click="toggleSearch" class="p-3 hover:bg-neutral-100 dark:hover:bg-neutral-800 rounded-2xl text-foreground active:scale-95 transition-all">
                                <Search :size="18" />
                            </button>
                            
                            <div class="hidden sm:flex items-center gap-1 border-x border-border px-2">
                                <ThemeToggler />
                                <FullScreenToggler />
                            </div>
                            
                            <Link :href="route('customer.cart.index')" 
                                  class="relative flex items-center justify-center p-1.5 bg-neutral-100 dark:bg-neutral-800 rounded-2xl border border-border transition-all group focus:outline-none active:scale-95 shadow-inner">
                                <div class="w-10 h-10 bg-primary text-primary-foreground rounded-xl flex items-center justify-center shadow-f1-glow">
                                    <ShoppingCart :size="20" />
                                </div>
                                <span v-if="cartCount > 0" 
                                      class="absolute -top-1.5 -right-1.5 bg-white text-black text-[10px] font-black w-6 h-6 rounded-full flex items-center justify-center border-[2.5px] border-black shadow-lg animate-in zoom-in duration-300">
                                    {{ cartCount }}
                                </span>
                            </Link>
                        </div>
                    </div>
                </div>
            </header>

            <section class="flex-1 w-full mx-auto py-8 transition-all"
                :class="route().current('customer.index') ? 'max-w-full' : 'max-w-7xl px-4 lg:px-8'">
                <slot />
            </section>
            <footer class="w-full border-t border-border py-16 px-6 lg:px-8 bg-transparent mt-auto relative z-10">
                <div class="max-w-7xl mx-auto space-y-16">
                    
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-y-10 gap-x-4">
                        <a v-for="social in socialNetworks" :key="social.name" :href="social.link"
                        class="group flex items-center gap-4 transition-all duration-300 outline-none">
                            
                            <div class="w-10 h-10 rounded-xl bg-neutral-100 dark:bg-neutral-900 flex items-center justify-center border border-border group-hover:border-primary/50 group-hover:shadow-f1-glow transition-all duration-500">
                                <component :is="social.icon" :size="18" class="text-neutral-500 group-hover:text-primary transition-colors" />
                            </div>

                            <div class="flex flex-col leading-tight">
                                <span class="text-[10px] font-black uppercase tracking-widest text-foreground group-hover:text-primary transition-colors">
                                    {{ social.name }}
                                </span>
                                <span class="text-[8px] font-bold text-neutral-400 uppercase tracking-[0.2em]">
                                    De Una
                                </span>
                            </div>
                        </a>
                    </div>

                    <div class="h-[1px] w-full bg-gradient-to-r from-transparent via-neutral-200 dark:via-neutral-800 to-transparent relative">
                        <div class="absolute inset-x-1/4 bottom-0 h-[1px] bg-gradient-to-r from-[#ff0000] via-[#00ff00] to-[#8b00ff] opacity-40"></div>
                    </div>

                    <div class="flex flex-col md:flex-row justify-between items-center gap-8 text-[10px] font-black uppercase tracking-[0.3em] text-neutral-500">
                        <div class="flex items-center gap-4">
                            <div class="w-5 h-5 rounded-md bg-gradient-to-br from-[#0ed2da] to-[#5f29c7] shadow-lg animate-pulse"></div>
                            <span class="text-foreground text-xs italic">De Una</span>
                        </div>
                        
                        <div class="flex gap-10 opacity-70">
                            <a href="#" class="hover:text-primary transition-colors underline-offset-4 hover:underline">Términos</a>
                            <a href="#" class="hover:text-primary transition-colors underline-offset-4 hover:underline">Privacidad</a>
                            <a href="#" class="hover:text-primary transition-colors underline-offset-4 hover:underline">Soporte</a>
                        </div>
                    </div>
                </div>
            </footer>
        </main>

        <nav class="lg:hidden fixed bottom-0 left-0 right-0 h-[76px] glass-titanium border-t border-border z-[100] flex justify-around items-center px-4 pb-safe shadow-2xl">
            <Link :href="route('customer.orders.index')" 
                class="flex flex-col items-center gap-1 transition-all"
                :class="route().current('customer.orders.*') ? 'text-primary' : 'text-black dark:text-neutral-400'">
                <PackageCheck :size="24" :stroke-width="route().current('customer.orders.*') ? 2.5 : 2" />
                <span class="text-[9px] font-black uppercase tracking-tighter">Pedidos</span>
            </Link>

            <button @click="toggleSearch" 
                    class="flex flex-col items-center gap-1 text-black dark:text-neutral-400 transition-all active:scale-90">
                <Search :size="22" />
                <span class="text-[9px] font-black uppercase tracking-tighter">Buscar</span>
            </button>

            <Link :href="route('customer.index')" class="relative -mt-10">
                <div class="w-14 h-14 bg-primary rounded-2xl border-4 border-background shadow-f1-glow flex items-center justify-center active:scale-90 transition-transform">
                    <Home :size="26" class="text-primary-foreground" />
                </div>
            </Link>

            <Link :href="route('customer.cart.index')" 
                class="flex flex-col items-center gap-1 text-black dark:text-neutral-400 relative transition-all">
                <ShoppingCart :size="24" />
                <span v-if="cartCount > 0" 
                    class="absolute -top-1 -right-1 bg-primary text-primary-foreground text-[8px] font-black w-4 h-4 rounded-full flex items-center justify-center border-2 border-background">
                    {{ cartCount }}
                </span>
                <span class="text-[9px] font-black uppercase tracking-tighter">Carrito</span>
            </Link>

            <Link :href="user ? route('customer.profile.index') : route('customer.login')" 
                class="flex flex-col items-center gap-1 transition-all"
                :class="route().current('customer.profile.*') ? 'text-primary' : 'text-black dark:text-neutral-400'">
                <div v-if="user" class="w-6 h-6 rounded-lg border border-border overflow-hidden bg-neutral-100 shadow-sm">
                    <img v-if="user?.profile?.avatar_url" :src="user.profile.avatar_url" class="w-full h-full object-cover" />
                    <span v-else class="text-[9px] font-black h-full flex items-center justify-center text-black">{{ userInitials }}</span>
                </div>
                <User v-else :size="24" />
                <span class="text-[9px] font-black uppercase tracking-tighter">{{ user ? 'Perfil' : 'Entrar' }}</span>
            </Link>
        </nav>

        <Transition name="drawer">
            <div v-if="isMobileMenuOpen" class="fixed inset-0 z-[110] lg:hidden">
                <div class="absolute inset-0 bg-background/40 backdrop-blur-sm" @click="isMobileMenuOpen = false"></div>
                
                <div class="absolute left-0 top-0 h-full w-[85%] max-w-[320px] glass-titanium flex flex-col shadow-2xl animate-in slide-in-from-left duration-300">
                    <div class="p-6 flex justify-between items-center border-b border-border bg-neutral-50/50 dark:bg-neutral-900/50">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-primary rounded-lg flex items-center justify-center shadow-f1-glow">
                                <span class="text-primary-foreground text-xs font-black">DU</span>
                            </div>
                            <span class="font-black text-xs uppercase tracking-widest">Menú</span>
                        </div>

                        <div class="flex items-center gap-1 ml-auto mr-2 border-r border-border pr-2">
                            <ThemeToggler />
                            <FullScreenToggler />
                        </div>

                        <button @click="isMobileMenuOpen = false" class="p-2 hover:bg-neutral-100 dark:hover:bg-neutral-800 rounded-xl transition-colors text-foreground">
                            <X :size="20" />
                        </button>
                    </div>
                    
                    <nav class="flex-1 p-4 space-y-1 overflow-y-auto no-scrollbar">
                        <Link v-for="item in filteredNavigation" :key="item.name" 
                            :href="route().has(item.route) ? route(item.route) : '#'" 
                            @click="isMobileMenuOpen = false"
                            class="flex items-center gap-4 p-4 rounded-2xl text-[10px] font-black uppercase tracking-[0.15em] transition-all"
                            :class="route().current(item.route) 
                                ? 'bg-primary/10 text-primary-aaa dark:text-primary shadow-sm' 
                                : 'hover:bg-neutral-50 dark:hover:bg-neutral-900 text-neutral-600 dark:text-neutral-400'"
                        >
                            <component :is="item.icon" :size="20" />
                            {{ item.name }}
                        </Link>
                    </nav>

                    <div class="p-6 border-t border-border bg-neutral-50/50 dark:bg-neutral-900/50 space-y-4">
                        <div v-if="user">
                            <button @click="logout" class="w-full h-12 rounded-2xl border-2 border-primary/20 text-[10px] font-black text-primary-aaa dark:text-primary uppercase tracking-widest active:scale-95 transition-all">
                                Cerrar Sesión
                            </button>
                        </div>
                        <div v-else class="grid grid-cols-2 gap-3">
                            <Link :href="route('customer.login')" 
                                class="flex items-center justify-center h-12 rounded-2xl bg-primary text-primary-foreground text-[10px] font-black uppercase tracking-widest shadow-f1-glow active:scale-95 transition-all">
                                Entrar
                            </Link>
                            <Link :href="route('customer.register')" 
                                class="flex items-center justify-center h-12 rounded-2xl border-2 border-border text-foreground text-[10px] font-black uppercase tracking-widest active:scale-95 transition-all">
                                Registro
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
/* 1. ACABADO TITANIUM MATTE */
.glass-titanium {
    /* Fondo con transparencia táctica */
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0.05) 100%);
    /* El alma del efecto: desenfoque de alta densidad */
    backdrop-filter: blur(20px) saturate(180%);
    -webkit-backdrop-filter: blur(20px) saturate(180%);
}

.dark .glass-titanium {
    /* En modo oscuro usamos una base casi negra pero translúcida */
    background: linear-gradient(135deg, rgba(10, 10, 10, 0.7) 0%, rgba(10, 10, 10, 0.4) 100%);
    border-color: rgba(255, 255, 255, 0.08);
}

/* 2. ICONOS TÁCTICOS */
:deep(svg) {
    stroke-width: 2.5 !important;
}

/* 3. UTILIDADES */
.no-scrollbar::-webkit-scrollbar {
    display: none;
}

.pb-safe {
    padding-bottom: env(safe-area-inset-bottom);
}

.shadow-f1-glow {
    box-shadow: 0 0 15px -3px hsl(var(--primary) / 0.5), 0 0 6px -2px hsl(var(--primary) / 0.3);
}

/* 4. TRANSICIONES DRAWER */
.drawer-enter-active, .drawer-leave-active {
    transition: transform 0.4s cubic-bezier(0.32, 0.72, 0, 1);
}
.drawer-enter-from, .drawer-leave-to {
    transform: translateX(-100%);
}

/* 1. LIENZO MAESTRO DIGITAL UNIT */
.du-cyber-canvas {
    position: relative;
    background-color: hsl(var(--background));
    /* Degradado de profundidad: Blanco/Negro (40%) -> Transparente -> Base Arcoíris */
    background-image: 
        linear-gradient(
            to bottom,
            hsl(var(--background)) 0%,
            hsl(var(--background)) 40%,
            transparent 100%
        ),
        linear-gradient(to right, #0ed2da, #5f29c7);
    background-attachment: fixed;
}

/* 2. MALLA DE PRECISIÓN (GRID VERTICAL) */
.du-cyber-canvas::before {
    content: "";
    position: absolute;
    inset: 0;
    z-index: 0; /* Suelo */
    background-image: linear-gradient(90deg, hsl(var(--border) / 0.4) 1px, transparent 1px);
    background-size: 50px 100%;
    pointer-events: none;

    /* Máscara de desvanecimiento hacia el fondo */
    mask-image: linear-gradient(
        to bottom,
        rgba(0, 0, 0, 1) 0%,
        rgba(0, 0, 0, 0) 70%
    );
    -webkit-mask-image: linear-gradient(
        to bottom,
        rgba(0, 0, 0, 1) 0%,
        rgba(0, 0, 0, 0) 70%
    );
}

/* 3. MODO OSCURO: ATENUACIÓN TÉCNICA */
.dark .du-cyber-canvas {
    /* Reducimos la intensidad de los colores para proteger el contraste del modo oscuro */
    background-image: 
        linear-gradient(
            to bottom,
            hsl(var(--background)) 0%,
            hsl(var(--background)) 50%,
            transparent 100%
        ),
        linear-gradient(to right, #0ed2da33, #5f29c733); /* 20% opacidad */
}
</style>