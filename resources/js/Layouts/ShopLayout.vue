<script setup>
import { ref, computed } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import {
    LayoutDashboard,
    Compass,
    Gauge,
    ShoppingBag,
    Fingerprint,
    Menu, X, LogOut, Bell, Tag, ChevronRight, Loader2, MessageCircle, 
    Instagram, Facebook, Youtube, Twitter, Linkedin, Send, Disc, Tv, 
    AtSign, Ghost, Music2, MapPin, Receipt, ShieldCheck
} from 'lucide-vue-next';
import ThemeToggler from '@/Components/Base/ThemeToggler.vue';
import FullScreenToggler from '@/Components/Base/FullScreenToggler.vue';

const socialNetworks = [
    { name: 'WhatsApp', icon: MessageCircle, link: '#' },
    { name: 'Instagram', icon: Instagram, link: '#' },
    { name: 'Facebook', icon: Facebook, link: '#' },
    { name: 'TikTok', icon: Music2, link: '#' },
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

const user = computed(() => page.props.auth?.customer);
const location = computed(() => page.props.location_context || { label: 'LOCALIZANDO...', type: 'branch' });
const cartCount = computed(() => page.props.cart?.total_items || 0);

const userInitials = computed(() => {
    const name = user.value?.name;
    if (!name) return '??';
    return name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2);
});
const navigation = [
    { name: 'Inicio',      icon: Gauge,           route: 'customer.index' },
    { name: 'Promociones',  icon: Tag,             route: 'customer.index' },
    { name: 'Pedidos',      icon: Receipt,         route: 'customer.order.index' },
    { name: 'Direcciones',  icon: MapPin,          route: 'customer.profile.addresses.index' }, 
    { name: 'Seguridad',    icon: ShieldCheck,     route: 'customer.profile.security' },
];

const filteredNavigation = computed(() => {
    return navigation.filter(item => ['customer.index'].includes(item.route) || !!user.value);
});

const logout = () => router.post(route('customer.logout'));
const toggleSearch = () => {
    isSearchActive.value = !isSearchActive.value;
    if (isSearchActive.value) {
        setTimeout(() => document.getElementById('global-search')?.focus(), 75);
    }
};
</script>

<template>
    <div class="du-cyber-canvas flex min-h-[100svh] text-foreground font-sans transition-colors duration-150 overflow-x-hidden selection:bg-primary/30">
        
        <aside 
            @mouseenter="isSidebarExpanded = true"
            @mouseleave="isSidebarExpanded = false"
            class="hidden lg:flex fixed left-0 top-0 h-full z-[100] flex-col bg-card border-r border-border transition-all duration-150 ease-f1"
            :class="isSidebarExpanded ? 'w-64' : 'w-[76px]'"
        >
            <div class="h-16 flex items-center px-6 shrink-0 border-b border-border">
                <div class="w-9 h-9 bg-primary flex items-center justify-center shrink-0">
                    <span class="text-primary-foreground font-black text-sm tracking-tighter">DU</span>
                </div>
                <div class="ml-4 flex flex-col transition-opacity duration-150 ease-f1" 
                     :class="isSidebarExpanded ? 'opacity-100' : 'opacity-0 pointer-events-none'">
                    <span class="font-black text-lg leading-none tracking-tighter uppercase">De</span>
                    <span class="text-primary-aaa dark:text-primary font-bold text-xs tracking-[0.3em] uppercase">Una</span>
                </div>
            </div>

            <nav class="flex-1 px-3 space-y-1.5 mt-4 overflow-y-auto no-scrollbar">
                <Link v-for="item in filteredNavigation" :key="item.name" :href="route().has(item.route) ? route(item.route) : '#'" 
                    class="flex items-center h-11 transition-all duration-150 ease-f1 group relative focus:ring-1 focus:ring-primary focus:outline-none"
                    :class="route().current(item.route) 
                        ? 'bg-neutral-100 dark:bg-neutral-800 text-primary-aaa dark:text-primary' 
                        : 'text-foreground hover:bg-neutral-100 dark:hover:bg-neutral-800'">
                    
                    <div class="w-[52px] flex justify-center shrink-0">
                        <component :is="item.icon" :size="20" />
                    </div>
                    
                    <span class="font-bold text-xs uppercase tracking-[0.15em] whitespace-nowrap transition-all duration-150 ease-f1"
                          :class="isSidebarExpanded ? 'opacity-100 translate-x-0' : 'opacity-0 -translate-x-4'">
                        {{ item.name }}
                    </span>

                    <div v-if="route().current(item.route)" 
                         class="absolute left-0 w-1 h-full bg-primary animate-in fade-in slide-in-from-left-1 duration-150">
                    </div>
                </Link>
            </nav>

            <div class="p-3 border-t border-border bg-card">
                <div class="flex items-center w-full h-12 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors duration-150 overflow-hidden group cursor-pointer">
                    <div class="w-[52px] flex justify-center shrink-0">
                        <div class="w-8 h-8 border border-border overflow-hidden bg-background flex items-center justify-center">
                            <img v-if="user?.profile?.avatar_url" :src="user.profile.avatar_url" class="w-full h-full object-cover" />
                            <span v-else class="text-xs font-black text-foreground">{{ userInitials }}</span>
                        </div>
                    </div>
                    <div class="flex flex-col items-start transition-opacity duration-150 ease-f1" :class="isSidebarExpanded ? 'opacity-100' : 'opacity-0 pointer-events-none'">
                        <span class="text-xs font-extrabold truncate w-32 uppercase tracking-tighter text-foreground">{{ user?.name || 'Invitado' }}</span>
                        
                        <div v-if="!user" class="flex gap-2 items-center leading-none">
                            <Link :href="route('customer.login')" class="text-[10px] font-black text-primary uppercase hover:underline tracking-widest">
                                Entrar
                            </Link>
                            <span class="text-[10px] text-neutral-500">|</span>
                            <Link :href="route('customer.register')" class="text-[10px] font-black text-foreground uppercase hover:underline tracking-widest">
                                Unirse
                            </Link>
                        </div>
                        
                        <button v-else @click="logout" class="text-[10px] font-black text-primary-aaa dark:text-primary hover:underline uppercase tracking-widest">
                            Cerrar Sesión
                        </button>
                    </div>
                </div>
            </div>
        </aside>

        <main class="flex-1 flex flex-col w-full pt-16 lg:pt-20 transition-all duration-150 ease-f1 relative z-10" 
              :class="isSidebarExpanded ? 'lg:pl-64' : 'lg:pl-[76px]'">
            
              <header class="fixed top-0 left-0 lg:left-[76px] right-0 z-[60] w-full lg:w-auto transition-all duration-150 ease-f1">
                <div class="h-16 flex items-center jusimport {tify-between px-4 lg:px-8 max-w-7xl mx-auto w-full bg-background bg-f1-lines-random border-b border-[#32323b]">
                
                        
                        <div v-if="!isSearchActive" class="flex items-center gap-3 animate-in fade-in slide-in-from-left-2 duration-150">
                            <button @click="isMobileMenuOpen = true" class="lg:hidden p-2 bg-transparent text-foreground active:scale-95 transition-transform duration-75">
                                <Menu :size="22" :stroke-width="1.5" />
                            </button>

                            <button @click="router.visit(route('customer.profile.addresses.index'))" class="flex items-center gap-3 px-2 py-1.5 bg-transparent transition-colors duration-150 group focus:outline-none">
                                <div class="w-8 h-8 bg-transparent flex items-center justify-center">
                                    <MapPin :size="16" :stroke-width="1.5" class="text-primary" />
                                </div>
                                <div class="flex flex-col items-start leading-none">
                                    <span class="text-[9px] font-black text-neutral-500 uppercase tracking-widest">Entregar en</span>
                                    <span class="text-xs font-bold text-foreground truncate max-w-[150px] uppercase tracking-wider">{{ location.label }}</span>
                                </div>
                            </button>
                        </div>

                        <div class="flex-1 flex justify-end items-center gap-2">
                            <div v-if="isSearchActive" class="w-full flex items-center gap-2 animate-in slide-in-from-right-2 duration-150">
                                <div class="flex-1 relative">
                                    <Search class="absolute left-4 top-1/2 -translate-y-1/2 text-primary" :size="16" :stroke-width="1.5" />
                                    <input 
                                        id="global-search" 
                                        v-model="searchQuery" 
                                        type="text" 
                                        placeholder="¿QUÉ BUSCAS HOY?"
                                        class="w-full h-10 bg-transparent border-b border-[#32323b] border-t-0 border-x-0 pl-11 pr-4 text-xs font-bold tracking-widest focus:ring-0 focus:border-primary text-foreground placeholder:text-neutral-500 uppercase transition-colors duration-150"
                                    />
                                </div>
                                <button @click="toggleSearch" class="p-2.5 bg-transparent text-foreground active:scale-95 transition-transform duration-75"><X :size="18" :stroke-width="1.5" /></button>
                            </div>

                            <div v-else class="flex items-center gap-2">
                                <button @click="toggleSearch" class="p-2.5 bg-transparent text-foreground active:scale-95 transition-all duration-75">
                                    <Search :size="20" :stroke-width="1.5" />
                                </button>
                                
                                <div class="hidden sm:flex items-center gap-2 px-2 bg-transparent">
                                    <ThemeToggler />
                                    <FullScreenToggler />
                                </div>
                                
                                <Link :href="route('customer.cart.index')" 
                                        class="relative flex items-center justify-center p-2 bg-transparent transition-colors duration-150 group focus:outline-none active:scale-95">
                                    <ShoppingCart :size="20" :stroke-width="1.5" class="text-foreground group-hover:text-primary transition-colors" />
                                    <span v-if="cartCount > 0" 
                                            class="absolute -top-1 -right-1 bg-primary text-primary-foreground text-[9px] font-black w-4 h-4 flex items-center justify-center rounded-none animate-in zoom-in duration-150">
                                        {{ cartCount }}
                                    </span>
                                </Link>
                            </div>
                        </div>
                    </div>
                </header>

            <section class="flex-1 w-full mx-auto pb-8 pt-4" :class="route().current('customer.index') ? 'max-w-full' : 'max-w-7xl px-4 lg:px-8'">
                <slot />
            </section>

            <footer class="w-full border-t border-[#32323b] py-12 px-6 lg:px-8 bg-[#ffffff] dark:bg-[#15151f] mt-auto relative z-10 text-[#15151f] dark:text-white select-none">
                <div class="max-w-7xl mx-auto space-y-12">
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-y-8 gap-x-4">
                        <a v-for="social in socialNetworks" :key="social.name" :href="social.link"
                        class="group flex items-center gap-3 transition-colors duration-75 outline-none hover:text-primary text-[#15151f] dark:text-white">
                            <div class="w-8 h-8 bg-transparent flex items-center justify-center border border-[#32323b] group-hover:border-primary transition-colors duration-75">
                                <component :is="social.icon" :size="16" :stroke-width="2.5" class="text-[#15151f] dark:text-white group-hover:text-primary transition-colors" />
                            </div>
                            <div class="flex flex-col leading-none">
                                <span class="text-[10px] font-black uppercase tracking-widest group-hover:text-primary transition-colors">
                                    {{ social.name }}
                                </span>
                            </div>
                        </a>
                    </div>

                    <div class="h-[1px] w-full bg-[#32323b]"></div>

                    <div class="flex flex-col md:flex-row justify-between items-center gap-6 text-[10px] font-black uppercase tracking-[0.2em] text-[#15151f] dark:text-white">
                        <div class="flex items-center gap-3">
                            <div class="w-4 h-4 bg-primary flex items-center justify-center text-primary-foreground text-[8px] font-black leading-none">DU</div>
                            <span class="tracking-[0.3em]">De Una Digital</span>
                        </div>
                        <div class="flex gap-8 opacity-90">
                            <a href="#" class="hover:text-primary transition-colors duration-75">Términos</a>
                            <a href="#" class="hover:text-primary transition-colors duration-75">Privacidad</a>
                            <a href="#" class="hover:text-primary transition-colors duration-75">Soporte</a>
                        </div>
                    </div>
                </div>
            </footer>
        </main>

        <nav class="lg:hidden fixed bottom-0 left-0 right-0 h-[64px] bg-[#ffffff] dark:bg-[#15151f] border-t border-[#32323b] z-[100] flex justify-around items-center px-1 pb-safe select-none">
    
            <Link :href="route('customer.order.index')" class="flex flex-col items-center justify-center w-16 h-full transition-colors duration-75" :class="route().current('customer.order.*') ? 'text-primary' : 'text-[#15151f] dark:text-white'">
                <LayoutDashboard :size="20" :stroke-width="2.5" />
                <span class="text-[9px] font-black uppercase tracking-tight mt-0.5">Pedidos</span>
            </Link>

            <button @click="toggleSearch" class="flex flex-col items-center justify-center w-16 h-full text-[#15151f] dark:text-white active:scale-95 duration-75">
                <Compass :size="20" :stroke-width="2.5" />
                <span class="text-[9px] font-black uppercase tracking-tight mt-0.5">Buscar</span>
            </button>

            <Link :href="route('customer.index')" class="flex flex-col items-center justify-center w-16 h-full border-b-4 transition-colors duration-75" :class="route().current('customer.index') ? 'border-primary text-primary' : 'border-transparent text-[#15151f] dark:text-white'">
                <Gauge :size="22" :stroke-width="2.5" />
                <span class="text-[9px] font-black uppercase tracking-tight mt-0.5">Inicio</span>
            </Link>

            <Link :href="route('customer.cart.index')" class="flex flex-col items-center justify-center w-16 h-full text-[#15151f] dark:text-white relative transition-colors duration-75" :class="route().current('customer.cart.*') ? 'text-primary' : ''">
                <div class="relative">
                    <ShoppingBag :size="20" :stroke-width="2.5" />
                    <span v-if="cartCount > 0" class="absolute -top-1.5 -right-2 bg-primary text-primary-foreground text-[8px] font-black w-4 h-4 flex items-center justify-center border border-[#15151f] dark:border-white">
                        {{ cartCount }}
                    </span>
                </div>
                <span class="text-[9px] font-black uppercase tracking-tight mt-0.5">Carrito</span>
            </Link>

            <Link :href="user ? route('customer.profile.index') : route('customer.login')" class="flex flex-col items-center justify-center w-16 h-full transition-colors duration-75" :class="route().current('customer.profile.*') ? 'text-primary' : 'text-[#15151f] dark:text-white'">
                <div v-if="user" class="w-5 h-5 border-2 overflow-hidden bg-background" :class="route().current('customer.profile.*') ? 'border-primary' : 'border-[#15151f] dark:border-white'">
                    <img v-if="user?.profile?.avatar_url" :src="user.profile.avatar_url" class="w-full h-full object-cover" />
                    <span v-else class="text-[9px] font-black h-full flex items-center justify-center text-foreground">{{ userInitials }}</span>
                </div>
                <Fingerprint v-else :size="20" :stroke-width="2.5" />
                <span class="text-[9px] font-black uppercase tracking-tight mt-0.5">{{ user ? 'Perfil' : 'Entrar' }}</span>
            </Link>
        </nav>

        <Transition name="drawer">
            <div v-if="isMobileMenuOpen" class="fixed inset-0 z-[110] lg:hidden">
                <div class="absolute inset-0 bg-neutral-950/80" @click="isMobileMenuOpen = false"></div>
                <div class="absolute left-0 top-0 h-full w-[85%] max-w-[320px] bg-card border-r border-border flex flex-col shadow-none">
                    <div class="h-16 px-4 flex justify-between items-center border-b border-border bg-background">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-primary flex items-center justify-center">
                                <span class="text-primary-foreground text-xs font-black">DU</span>
                            </div>
                            <span class="font-black text-xs uppercase tracking-widest text-foreground">Menú</span>
                        </div>
                        <div class="flex items-center gap-1 ml-auto mr-2 border-r border-border pr-2">
                            <ThemeToggler />
                            <FullScreenToggler />
                        </div>
                        <button @click="isMobileMenuOpen = false" class="p-2 hover:bg-neutral-100 dark:hover:bg-neutral-800 text-foreground transition-colors duration-150">
                            <X :size="20" />
                        </button>
                    </div>
                    
                    <nav class="flex-1 p-4 space-y-1 overflow-y-auto no-scrollbar">
                        <Link v-for="item in filteredNavigation" :key="item.name" 
                            :href="route().has(item.route) ? route(item.route) : '#'" 
                            @click="isMobileMenuOpen = false"
                            class="flex items-center gap-4 p-4 text-xs font-black uppercase tracking-[0.15em] transition-colors duration-150"
                            :class="route().current(item.route) ? 'bg-neutral-100 dark:bg-neutral-800 text-primary border-l-2 border-primary' : 'hover:bg-neutral-100 dark:hover:bg-neutral-800 text-foreground border-l-2 border-transparent'"
                        >
                            <component :is="item.icon" :size="18" />
                            {{ item.name }}
                        </Link>
                    </nav>

                    <div class="p-4 border-t border-border bg-background space-y-4">
                        <div v-if="user">
                            <button @click="logout" class="w-full h-10 border border-border text-[10px] font-black text-foreground hover:text-primary hover:border-primary uppercase tracking-widest active:scale-95 transition-all duration-75">
                                Cerrar Sesión
                            </button>
                        </div>
                        <div v-else class="grid grid-cols-2 gap-2">
                            <Link :href="route('customer.login')" class="flex items-center justify-center h-10 bg-primary text-primary-foreground text-[10px] font-black uppercase tracking-widest hover:bg-primary-600 active:scale-95 transition-all duration-75">
                                Entrar
                            </Link>
                            <Link :href="route('customer.register')" class="flex items-center justify-center h-10 border border-border text-foreground text-[10px] font-black uppercase tracking-widest hover:bg-neutral-100 dark:hover:bg-neutral-800 active:scale-95 transition-all duration-75">
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
:deep(svg) { stroke-width: 2 !important; }
.no-scrollbar::-webkit-scrollbar { display: none; }
.pb-safe { padding-bottom: env(safe-area-inset-bottom); }

/* Transición ultrarrápida del cajón móvil */
.drawer-enter-active, .drawer-leave-active {
    transition: transform 0.15s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.15s ease;
}
.drawer-enter-from, .drawer-leave-to .absolute.bg-card {
    transform: translateX(-100%);
}
.drawer-enter-from .absolute.inset-0, .drawer-leave-to .absolute.inset-0 {
    opacity: 0;
}

/* Fondo técnico de la F1 (Asfalto/Carbono con malla paramétrica oscura) */
.du-cyber-canvas {
    position: relative;
    background-color: hsl(var(--background));
}

.du-cyber-canvas::before {
    content: "";
    position: absolute;
    inset: 0;
    z-index: 0;
    background-image: 
        linear-gradient(90deg, hsl(var(--border) / 0.5) 1px, transparent 1px),
        linear-gradient(180deg, hsl(var(--border) / 0.5) 1px, transparent 1px);
    background-size: 40px 40px;
    pointer-events: none;
    mask-image: linear-gradient(to bottom, rgba(0, 0, 0, 1) 0%, rgba(0, 0, 0, 0) 60%);
    -webkit-mask-image: linear-gradient(to bottom, rgba(0, 0, 0, 1) 0%, rgba(0, 0, 0, 0) 60%);
}
</style>